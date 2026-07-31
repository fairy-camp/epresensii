<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\QrCode;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // =========================================================================
        // 2. CEK VALIDITAS QR CODE ATAU NIP (PERUBAHAN DI SINI)
        // =========================================================================
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
        // =========================================================================

        $teacher = Teacher::with('workSchedule')->find($qr->teacher_id);

        if (!$teacher || !$teacher->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data guru tidak ditemukan atau status akun tidak aktif.'
            ], 404);
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');

        // 3. Cek Data Presensi Hari Ini
        $attendance = AttendanceRecord::where('teacher_id', $teacher->id)
            ->where('date', $today)
            ->first();

        // Ambil Shift Assignment guru (sesuaikan relasinya)
        $shiftAssignment = $teacher->shiftAssignments()->first(); 

        if (!$shiftAssignment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Guru belum memiliki jadwal shift kerja!'
            ], 400);
        }

        // JIKA BELUM PRESENSI MASUK -> CATAT MASUK
        if (!$attendance) {
            $schedule = $teacher->workSchedule;
            $status = 'present';

            if ($schedule && $currentTime > $schedule->check_in_time) {
                $status = 'late';
            }

            // Gunakan Model AttendanceRecord
            $attendance = AttendanceRecord::create([
                'teacher_id'          => $teacher->id,
                'shift_assignment_id' => $shiftAssignment->id, // wajib ada
                'date'                => $today,
                'check_in_time'       => $now, // Carbon::now() untuk mengisi dateTime
                'status'              => $status,
            ]);

            $statusText = $status === 'late' ? 'Terlambat' : 'Tepat Waktu';

            return response()->json([
                'status'   => 'success',
                'type'     => 'check_in',
                'message'  => "Presensi MASUK Berhasil! ({$statusText})",
                'teacher'  => $teacher->full_name,
                'time'     => $currentTime,
                'distance' => round($distance) . ' meter'
            ]);
        }

// JIKA SUDAH PRESENSI MASUK, TAPI BELUM PULANG -> CATAT PULANG
if (is_null($attendance->check_out_time)) {
    $attendance->update([
        'check_out_time' => $now, // Carbon::now() untuk mengisi dateTime
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

        // JIKA SUDAH PRESENSI MASUK, TAPI BELUM PULANG -> CATAT PULANG
        if (is_null($attendance->check_out_time)) {
            $attendance->update([
                'check_out_time'     => $currentTime,
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

        // JIKA SUDAH PRESENSI MASUK & PULANG HARI INI
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