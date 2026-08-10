<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Teacher;
use App\Models\ApelAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // Role yang dikecualikan dari perhitungan guru/karyawan
        $excludedRoles = ['petugas'];

        // 1. Total Guru/Pegawai Aktif (Kecuali Petugas)
        $totalTeachers = Teacher::where('is_active', true)
            ->whereHas('user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->count();

        // 2. Statistik Presensi Harian Hari Ini (Khusus Guru/Karyawan)
        $totalPresent = AttendanceRecord::where('date', $today)
            ->where('status', 'present')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->count();

        $totalLate = AttendanceRecord::where('date', $today)
            ->where('status', 'late')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->count();

        $totalAbsent = AttendanceRecord::where('date', $today)
            ->where('status', 'absent')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->count();

        $totalScanHariIni = AttendanceRecord::where('date', $today)
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->count();

        // 3. Guru dan Karyawan Belum Absen Harian
        $totalLateCount = $totalTeachers - ($totalPresent + $totalLate);

        // 4. Waktu Terawal dan Terlama Presensi Harian
        $earliestAttendance = AttendanceRecord::where('date', $today)
            ->where('status', 'present')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->orderBy('check_in_time', 'asc')
            ->first();

        $latestAttendance = AttendanceRecord::where('date', $today)
            ->where('status', 'late')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->orderBy('check_in_time', 'desc')
            ->first();

        $earliestTime = $earliestAttendance?->check_in_time
            ? Carbon::parse($earliestAttendance->check_in_time)->format('H:i:s')
            : '-';

        $latestTime = $latestAttendance?->check_in_time
            ? Carbon::parse($latestAttendance->check_in_time)->format('H:i:s')
            : '-';

        // 5. Monitoring Real-Time Presensi Harian (10 Aktivitas Terakhir)
        $recentAttendances = AttendanceRecord::with('teacher')
            ->where('date', $today)
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->latest('updated_at')
            ->take(10)
            ->get();

        // 6. List Guru/Karyawan yang Terlambat Presensi Harian
        $lateAttendances = AttendanceRecord::with('teacher')
            ->where('date', $today)
            ->where('status', 'late')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->orderBy('check_in_time', 'desc')
            ->get();

        // 7. Statistik & Monitoring Presensi Apel Pagi Hari Ini
        $totalApelPresent = ApelAttendance::where('date', $today)
            ->where('status', 'present')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->count();

        $totalApelLate = ApelAttendance::where('date', $today)
            ->where('status', 'late')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->count();

        $totalApelExplicitAbsent = ApelAttendance::where('date', $today)
            ->where('status', 'absent')
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->count();

        $totalApelHadir = $totalApelPresent + $totalApelLate;
        $totalApelAbsent = ($totalTeachers - $totalApelHadir) + $totalApelExplicitAbsent;
        if ($totalApelAbsent < 0) $totalApelAbsent = 0;

        $recentApelAttendances = ApelAttendance::with('teacher')
            ->where('date', $today)
            ->whereHas('teacher.user', function ($query) use ($excludedRoles) {
                $query->whereNotIn('role', $excludedRoles);
            })
            ->orderBy('scan_time', 'desc')
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalTeachers',
            'totalPresent',
            'totalLate',
            'totalAbsent',
            'totalScanHariIni',
            'recentAttendances',
            'lateAttendances',
            'totalLateCount',
            'earliestTime',
            'latestTime',
            'totalApelHadir',
            'totalApelPresent',
            'totalApelLate',
            'totalApelAbsent',
            'recentApelAttendances'
        ));
    }
}