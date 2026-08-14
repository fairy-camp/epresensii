<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\QrCode;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    // Menampilkan Halaman Scanner QR Code
    public function scanPage()
    {
        // Ambil dari cache
        $school = Cache::remember('school_setting', 86400, function () {
            return SchoolSetting::first();
        });

        // Proteksi: Jika cache rusak (__PHP_Incomplete_Class), reset cache dan ambil ulang dari DB
        if ($school instanceof \__PHP_Incomplete_Class || !$school) {
            Cache::forget('school_setting');
            $school = SchoolSetting::first();
        }

        return view('attendance.scan', compact('school'));
    }

    // Pemrosesan API Presensi (AJAX POST) - DIOPTIMALKAN
    // Pemrosesan API Presensi (AJAX POST) - SUDAH MENDUKUNG SHIFT MALAM
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code'   => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // 1. Ambil Pengaturan Sekolah dari Cache dengan Proteksi
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

        // 2. Validasi Geofencing / Radius
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

        // 3. Eager Loading Data QR, Guru, dan Shift
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
                'message' => "Guru {$teacher->full_name} belum memiliki jadwal shift!"
            ], 400);
        }

        $schedule = $shiftAssignment->workSchedule;
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $today = $now->toDateString();

        // Cek apakah jadwal ini merupakan Shift Malam (Jam Masuk > Jam Keluar, misal 22:00 > 06:00)
        $isOvernightShift = $schedule->check_in_time > $schedule->check_out_time;

        // 4. CEK PRESENSI GANTUNG (Sudah Masuk tapi Belum Pulang)
        $openAttendance = AttendanceRecord::where('teacher_id', $teacher->id)
            ->where('shift_assignment_id', $shiftAssignment->id)
            ->whereNull('check_out_time')
            ->latest()
            ->first();

        // SKENARIO A: CHECK-OUT (PULANG)
        if ($openAttendance) {
            $openAttendance->update([
                'check_out_time'      => $currentTime,
                'check_out_latitude'  => $request->latitude,
                'check_out_longitude' => $request->longitude,
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

        // SKENARIO B: CHECK-IN (MASUK)
        // Penentuan Tanggal Shift (Tgl Acuan Presensi)
        // Jika Shift Malam dan di-scan setelah midnight (00:00 - 12:00), tanggal shift adalah HARI KEMARIN.
        if ($isOvernightShift && $currentTime < '12:00:00') {
            $attendanceDate = $now->copy()->subDay()->toDateString();
        } else {
            $attendanceDate = $today;
        }

        // Cek apakah sudah pernah presensi lengkap pada tanggal shift tersebut
        $existingAttendance = AttendanceRecord::where('teacher_id', $teacher->id)
            ->where('shift_assignment_id', $shiftAssignment->id)
            ->whereDate('date', $attendanceDate)
            ->first();

        if ($existingAttendance && !is_null($existingAttendance->check_out_time)) {
            return response()->json([
                'status'  => 'warning',
                'teacher' => $teacher->full_name,
                'message' => 'Anda sudah melakukan presensi masuk dan pulang untuk shift ini.'
            ], 200);
        }

        // Hitung Keterlambatan Menggunakan Objek Datetime Utuh
        $scheduledCheckIn = Carbon::parse($attendanceDate . ' ' . $schedule->check_in_time);
        $isLate = $now->greaterThan($scheduledCheckIn);
        $status = $isLate ? 'late' : 'present';

        AttendanceRecord::create([
            'teacher_id'          => $teacher->id,
            'shift_assignment_id' => $shiftAssignment->id,
            'work_schedule_id'    => $schedule->id,
            'date'                => $attendanceDate,
            'check_in_time'       => $currentTime,
            'status'              => $status,
            'latitude'            => $request->latitude,
            'longitude'           => $request->longitude,
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

        return view('attendance.my_history', compact(
            'teacher', 'attendances', 'month', 'year', 'totalPresent', 'totalLate', 'totalRecords'
        ));
    }

    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));

        $attendances = AttendanceRecord::with(['teacher', 'workSchedule'])
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