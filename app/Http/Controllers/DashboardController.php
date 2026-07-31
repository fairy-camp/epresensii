<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Total Guru/Pegawai Aktif
        $totalTeachers = Teacher::where('is_active', true)->count();

        // 2. Statistik Presensi Hari Ini
        $totalPresent = AttendanceRecord::where('date', $today)
            ->where('status', 'present')
            ->count();

        $totalLate = AttendanceRecord::where('date', $today)
            ->where('status', 'late')
            ->count();

        $totalAbsent = AttendanceRecord::where('date', $today)
            ->where('status', 'absent')
            ->count();

        // 3. Guru dan Karyawan Telat (Jumlah Pegawai - (Hadir Tepat Waktu + Terlambat))
        $totalLateCount = $totalTeachers - ($totalPresent + $totalLate);

        // 4. Waktu Terawal (Hadir Tepat Waktu) dan Waktu Terlama (Terlambat)
        $earliestAttendance = AttendanceRecord::where('date', $today)
            ->where('status', 'present')
            ->orderBy('check_in_time', 'asc')
            ->first();

        $latestAttendance = AttendanceRecord::where('date', $today)
            ->where('status', 'late')
            ->orderBy('check_in_time', 'desc')
            ->first();

        $earliestTime = $earliestAttendance?->check_in_time
            ? Carbon::parse($earliestAttendance->check_in_time)->format('H:i:s')
            : '-';

        $latestTime = $latestAttendance?->check_in_time
            ? Carbon::parse($latestAttendance->check_in_time)->format('H:i:s')
            : '-';

        // 5. Data Presensi Terbaru untuk Monitoring Real-Time (10 Aktivitas Terakhir)
        $recentAttendances = AttendanceRecord::with('teacher')
            ->where('date', $today)
            ->latest('updated_at')
            ->take(10)
            ->get();

        // 6. List Guru yang Terlambat Hari Ini
        $lateAttendances = AttendanceRecord::with('teacher')
            ->where('date', $today)
            ->where('status', 'late')
            ->orderBy('check_in_time', 'desc')
            ->get();

        return view('dashboard', compact(
            'totalTeachers',
            'totalPresent',
            'totalLate',
            'totalAbsent',
            'recentAttendances',
            'lateAttendances'
        ));
    }
}
