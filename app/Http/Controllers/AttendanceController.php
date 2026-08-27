<?php

namespace App\Http\Controllers;

use App\Models\ApelAttendance;
use App\Models\AttendanceRecord;
use App\Models\QrCode;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    public function scanPage()
    {
        $school = Cache::remember('school_setting', 86400, function () {
            return SchoolSetting::first();
        });

        if ($school instanceof \__PHP_Incomplete_Class || !$school) {
            Cache::forget('school_setting');
            $school = SchoolSetting::first();
        }

        return view('attendance.scan', compact('school'));
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code'   => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $school = Cache::remember('school_setting', 86400, function () {
            return SchoolSetting::first();
        });

        if ($school instanceof \__PHP_Incomplete_Class) {
            Cache::forget('school_setting');
            $school = SchoolSetting::first();
        }

        if (!$school) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lokasi sekolah belum dikonfigurasi oleh Admin.'
            ], 400);
        }

        // 1. Validasi Radius GPS
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $school->latitude,
            $school->longitude
        );

        if ($distance > $school->geofence_radius) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal Presensi! Anda berada di luar radius sekolah. Jarak Anda: ' . round($distance) . ' meter (Maks: ' . $school->geofence_radius . ' meter).'
            ], 422);
        }

        // 2. Validasi NIP / QR Code
        $qr = QrCode::with(['teacher.shiftAssignments.workSchedule'])
            ->where('code', $request->qr_code)
            ->where('is_active', true)
            ->first();

        if (!$qr) {
            $qr = QrCode::with(['teacher.shiftAssignments.workSchedule'])
                ->whereHas('teacher', function ($q) use ($request) {
                    $q->where('nip', $request->qr_code)->where('is_active', true);
                })
                ->where('is_active', true)
                ->first();
        }

        if (!$qr || !$qr->teacher || !$qr->teacher->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode QR / NIP tidak ditemukan atau akun guru tidak aktif!'
            ], 404);
        }

        $teacher = $qr->teacher;
        $shiftAssignment = $teacher->shiftAssignments->first();

        if (!$shiftAssignment || !$shiftAssignment->workSchedule) {
            return response()->json([
                'status'  => 'error',
                'message' => "{$teacher->full_name} belum memiliki jadwal shift!"
            ], 400);
        }

        $schedule = $shiftAssignment->workSchedule;
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $today = $now->toDateString();

        // Batas waktu dari jadwal kerja
        $startCheckIn  = $schedule->start_check_in_time  ?: '06:30:00';
        $endCheckIn    = $schedule->end_check_in_time    ?: '07:00:00';
        $startCheckOut = $schedule->start_check_out_time ?: '15:00:00';
        $endCheckOut   = $schedule->end_check_out_time   ?: '17:00:00';

        // Cari record presensi khusus HARI INI
        $todayAttendance = AttendanceRecord::where('teacher_id', $teacher->id)
            ->where('shift_assignment_id', $shiftAssignment->id)
            ->whereDate('date', $today)
            ->first();

        // KONDISI 1: Sudah Presensi Datang DAN Sudah Presensi Pulang
        if ($todayAttendance && !is_null($todayAttendance->check_out_time)) {
            return response()->json([
                'status'  => 'warning',
                'teacher' => $teacher->full_name,
                'message' => 'Anda sudah melakukan presensi datang dan pulang hari ini.'
            ], 200);
        }

        // KONDISI 2: Sudah Presensi Datang, Tapi Belum Presensi Pulang
        if ($todayAttendance && is_null($todayAttendance->check_out_time)) {
            // Belum saatnya presensi pulang
            if ($currentTime < $startCheckOut) {
                $checkInFormatted = Carbon::parse($todayAttendance->check_in_time)->format('H:i');
                return response()->json([
                    'status'  => 'warning',
                    'teacher' => $teacher->full_name,
                    'message' => "Anda sudah presensi datang hari ini (jam {$checkInFormatted}). Presensi pulang baru dibuka jam " . substr($startCheckOut, 0, 5) . '.'
                ], 200);
            }

            // Batas jam pulang lewat
            if ($currentTime > $endCheckOut) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Waktu presensi pulang telah berakhir (Batas maksimal jam ' . substr($endCheckOut, 0, 5) . ').'
                ], 422);
            }

            // Di dalam jendela jam pulang -> Eksekusi Presensi Pulang
            $todayAttendance->update([
                'check_out_time' => $now->toDateTimeString(),
            ]);

            return response()->json([
                'status'   => 'success',
                'type'     => 'pulang',
                'message'  => 'Presensi PULANG Berhasil!',
                'teacher'  => $teacher->full_name,
                'time'     => $currentTime,
                'distance' => round($distance) . ' meter'
            ]);
        }

        // KONDISI 3: Belum Presensi Datang Hari Ini
        if ($currentTime < $startCheckIn) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Presensi datang belum dibuka. Silakan presensi jam ' . substr($startCheckIn, 0, 5) . ' - ' . substr($endCheckIn, 0, 5) . '.'
            ], 422);
        }

        if ($currentTime > $endCheckIn) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Waktu presensi datang telah ditutup (Batas maksimal jam ' . substr($endCheckIn, 0, 5) . ').'
            ], 422);
        }

        // Simpan Presensi Datang Baru
        $scheduledCheckIn = Carbon::parse($today . ' ' . $schedule->check_in_time);
        $isLate = $now->greaterThan($scheduledCheckIn);
        $status = $isLate ? 'late' : 'present';

        AttendanceRecord::create([
            'teacher_id'          => $teacher->id,
            'shift_assignment_id' => $shiftAssignment->id,
            'date'                => $today,
            'check_in_time'       => $now->toDateTimeString(),
            'status'              => $status,
        ]);

        $statusText = $isLate ? 'Terlambat' : 'Tepat Waktu';

        return response()->json([
            'status'   => 'success',
            'type'     => 'masuk',
            'is_late'  => $isLate,
            'message'  => "Presensi MASUK Berhasil! ({$statusText})",
            'teacher'  => $teacher->full_name,
            'time'     => $currentTime,
            'distance' => round($distance) . ' meter'
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function myHistory(Request $request)
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->back()->with('error', 'Data profil guru tidak terhubung dengan akun Anda.');
        }

        $month = $request->input('month', Carbon::now()->format('m'));
        $year  = $request->input('year', Carbon::now()->format('Y'));

        $attendances = AttendanceRecord::with('shiftAssignment.workSchedule')
            ->where('teacher_id', $teacher->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $totalPresent = $attendances->where('status', 'present')->count();
        $totalLate    = $attendances->where('status', 'late')->count();
        $totalRecords = $attendances->count();

        $apelAttendances = ApelAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $totalApelPresent = $apelAttendances->where('status', 'present')->count();
        $totalApelLate    = $apelAttendances->where('status', 'late')->count();
        $totalApelRecords = $apelAttendances->count();

        return view('attendance.my_history', compact(
            'teacher', 'attendances', 'month', 'year', 'totalPresent', 'totalLate', 'totalRecords',
            'apelAttendances', 'totalApelPresent', 'totalApelLate', 'totalApelRecords'
        ));
    }

    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));

        $attendances = AttendanceRecord::with(['teacher', 'shiftAssignment.workSchedule'])
            ->whereDate('date', $date)
            ->latest('check_in_time')
            ->get();

        return view('attendance.index', compact('attendances', 'date'));
    }

    public function update(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit data presensi.');
        }

        $attendance = AttendanceRecord::findOrFail($id);

        $request->validate([
            'status'         => 'required|in:present,late,permission,sick,absent',
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'notes'          => 'nullable|string|max:255',
        ]);

        $attendance->update([
            'status'         => $request->status,
            'check_in_time'  => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'notes'          => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Data presensi berhasil diperbarui!');
    }

    public function destroy($id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus data presensi.');
        }

        $attendance = AttendanceRecord::findOrFail($id);
        $attendance->delete();

        return redirect()->back()->with('success', 'Data presensi berhasil dihapus!');
    }
}