<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\ShiftAssignment;
use App\Models\QrCode;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    // Menampilkan Halaman Scanner QR Code
    public function scanPage()
    {
        $school = SchoolSetting::first();
        return view('attendance.scan', compact('school'));
    }

    // Pemrosesan API Presensi (AJAX POST)
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code'   => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $school = SchoolSetting::first();

        if (!$school) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lokasi sekolah belum dikonfigurasi oleh Admin.'
            ], 400);
        }

        // 1. Validasi Radius / Geofencing (Rumus Haversine)
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

        // 2. CEK VALIDITAS QR CODE ATAU NIP GURU
        $qr = QrCode::where('code', $request->qr_code)->where('is_active', true)->first();

        if (!$qr) {
            // Jika tidak ditemukan berdasarkan Kode QR, coba cari via NIP Guru
            $teacherByNip = Teacher::where('nip', $request->qr_code)->first();
            if ($teacherByNip) {
                $qr = QrCode::where('teacher_id', $teacherByNip->id)->where('is_active', true)->first();
            }
        }

        if (!$qr) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode QR atau NIP tidak ditemukan / tidak aktif!'
            ], 404);
        }

        $teacher = Teacher::find($qr->teacher_id);

        if (!$teacher || !$teacher->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data guru tidak ditemukan atau status akun tidak aktif.'
            ], 404);
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');

        // 3. Cari Shift Assignment Guru KHUSUS HARI INI
        $shiftAssignment = ShiftAssignment::with('workSchedule')
            ->where('teacher_id', $teacher->id)
            ->where('date', $today)
            ->first();

        if (!$shiftAssignment || !$shiftAssignment->workSchedule) {
            return response()->json([
                'status'  => 'error',
                'message' => "Guru {$teacher->full_name} belum memiliki jadwal shift untuk hari ini!"
            ], 400);
        }

        $schedule = $shiftAssignment->workSchedule;

        // 4. Cek Data Presensi Hari Ini
        $attendance = AttendanceRecord::where('teacher_id', $teacher->id)
            ->where('date', $today)
            ->first();

        // =========================================================================
        // SKENARIO A: BELUM PRESENSI MASUK -> CATAT MASUK (CHECK-IN)
        // =========================================================================
        if (!$attendance) {
            // Cek keterlambatan berdasarkan jam masuk di WorkSchedule
            $status = ($currentTime > $schedule->check_in_time) ? 'late' : 'present';

            $attendance = AttendanceRecord::create([
                'id'                  => Str::uuid(),
                'teacher_id'          => $teacher->id,
                'shift_assignment_id' => $shiftAssignment->id,
                'work_schedule_id'    => $schedule->id,
                'date'                => $today,
                'check_in_time'       => $currentTime,
                'status'              => $status,
                'latitude'            => $request->latitude,
                'longitude'           => $request->longitude,
            ]);

            $statusText = ($status === 'late') ? 'Terlambat' : 'Tepat Waktu';

            return response()->json([
                'status'   => 'success',
                'type'     => 'check_in',
                'message'  => "Presensi MASUK Berhasil! ({$statusText})",
                'teacher'  => $teacher->full_name,
                'time'     => $currentTime,
                'distance' => round($distance) . ' meter'
            ]);
        }

        // =========================================================================
        // SKENARIO B: SUDAH MASUK, TAPI BELUM PULANG -> CATAT PULANG (CHECK-OUT)
        // =========================================================================
        if (is_null($attendance->check_out_time)) {
            $attendance->update([
                'check_out_time'      => $currentTime,
                'check_out_latitude'  => $request->latitude,
                'check_out_longitude' => $request->longitude,
            ]);

            return response()->json([
                'status'   => 'success',
                'type'     => 'check_out',
                'message'  => 'Presensi PULANG Berhasil!',
                'teacher'  => $teacher->full_name,
                'time'     => $currentTime,
                'distance' => round($distance) . ' meter'
            ]);
        }

        // =========================================================================
        // SKENARIO C: SUDAH PRESENSI MASUK & PULANG HARI INI
        // =========================================================================
        return response()->json([
            'status'  => 'warning',
            'message' => 'Anda sudah melakukan presensi masuk dan pulang untuk hari ini.'
        ], 400);
    }

    /**
     * Menghitung jarak antara dua koordinat (dalam satuan meter)
     * Menggunakan Rumus Haversine
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

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
}