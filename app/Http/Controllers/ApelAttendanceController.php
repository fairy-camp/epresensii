<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApelAttendance;
use App\Models\Teacher;
use App\Models\QrCode; // <--- Pastikan Model QrCode Di-import
use App\Models\SchoolSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApelAttendanceController extends Controller
{
    /**
     * Halaman Scanner Kamera Presensi Apel (Khusus Role Petugas)
     */
    public function scanPage()
    {
        $school = SchoolSetting::first();
        return view('apel.scan', compact('school'));
    }

    /**
     * Memproses Scan QR / Input NIP via AJAX
     */
    public function processScan(Request $request)
    {
        try {
            $request->validate([
                'qr_code'   => 'required|string',
                'latitude'  => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $code = trim($request->qr_code);
            $userLat = (float) $request->latitude;
            $userLng = (float) $request->longitude;
            $today = Carbon::today()->toDateString();
            $now = Carbon::now();

            // Dekode jika isi QR Code berupa format JSON
            if (str_starts_with($code, '{') && str_ends_with($code, '}')) {
                $json = json_decode($code, true);
                if (is_array($json)) {
                    $code = $json['code'] ?? $json['nip'] ?? $json['nik'] ?? $json['nuptk'] ?? $json['npy'] ?? $json['id'] ?? $code;
                }
            }

            // 1. Pencarian Guru (Mendukung Tabel QrCode DAN Tabel Teachers)
            $teacher = null;

            // A. Cari dari tabel qr_codes terlebih dahulu (Sama dengan Presensi Utama)
            $qr = QrCode::with('teacher')
                ->where('code', $code)
                ->where('is_active', true)
                ->first();

            if ($qr && $qr->teacher) {
                $teacher = $qr->teacher;
            } else {
                // B. Jika bukan Kode QR, cari langsung berdasarkan NIP/NIK/NUPTK/NPY di tabel teachers
                $teacher = Teacher::where('nip', $code)
                    ->orWhere('nik', $code)
                    ->orWhere('nuptk', $code)
                    ->orWhere('npy', $code)
                    ->orWhere('id', $code)
                    ->first();
            }

            if (!$teacher || !$teacher->is_active) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'NIP / Kartu QR tidak terdaftar dalam sistem!'
                ], 404);
            }

            $teacherName = $teacher->full_name ?? 'Guru / Pegawai';

            // 2. Cek Geofence / Radius Sekolah Menggunakan school_settings
            $school = SchoolSetting::first();
            $distance = 0;

            if ($school && $school->latitude && $school->longitude) {
                $distance = $this->calculateDistance(
                    $userLat,
                    $userLng,
                    (float) $school->latitude,
                    (float) $school->longitude
                );

                $allowedRadius = (float) ($school->geofence_radius ?? 50);

                if ($distance > $allowedRadius) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Di luar radius sekolah! Jarak Anda: " . number_format($distance, 1) . " Meter (Maks: {$allowedRadius}M)"
                    ], 422);
                }
            }

            // 3. Cek Duplikasi Presensi Apel Hari Ini
            $alreadyScanned = ApelAttendance::where('teacher_id', $teacher->id)
                ->where('date', $today)
                ->exists();

            if ($alreadyScanned) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => "{$teacherName} sudah melakukan presensi apel hari ini!",
                    'teacher' => $teacherName
                ], 200);
            }

            // 4. Penentuan Status Apel (Batas Waktu Jam 07:00)
            $apelDeadline = Carbon::createFromTimeString('07:00:00');
            $isLate = $now->gt($apelDeadline);
            $status = $isLate ? 'late' : 'present';

            // 5. Simpan Record Presensi Apel
            DB::beginTransaction();

            ApelAttendance::create([
                'teacher_id' => $teacher->id,
                'date'       => $today,
                'scan_time'  => $now->toTimeString(),
                'status'     => $status,
            ]);

            DB::commit();

            $statusMessage = $isLate 
                ? "Presensi Apel Berhasil (Terlambat)" 
                : "Presensi Apel Berhasil (Tepat Waktu)";

            return response()->json([
                'status'   => 'success',
                'message'  => $statusMessage,
                'teacher'  => $teacherName,
                'time'     => $now->format('H:i:s'),
                'is_late'  => $isLate,
                'distance' => number_format($distance, 1)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Apel Process Scan Error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Halaman Data Presensi Apel Pagi di Panel Admin
     */
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $attendances = ApelAttendance::with('teacher')
            ->whereDate('created_at', $date)
            ->latest()
            ->get();

        $tepatWaktu = $attendances->where('status', 'present')->count();
        $terlambat  = $attendances->where('status', 'late')->count();
        $totalHadir = $tepatWaktu + $terlambat; 
        $totalAlpa  = $attendances->where('status', 'absent')->count();

        return view('apel.index', compact('attendances', 'date', 'totalHadir', 'tepatWaktu', 'terlambat', 'totalAlpa'));
    }

    /**
     * Menghapus Record Presensi Apel
     */
    public function destroy($id)
    {
        try {
            $attendance = ApelAttendance::findOrFail($id);
            $attendance->delete();

            return redirect()->back()->with('success', 'Data presensi apel berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Helper Function: Menghitung Jarak Antara Dua Koordinat GPS (Haversine Formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}