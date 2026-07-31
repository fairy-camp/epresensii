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

        $totalScanHariIni = AttendanceRecord::where('date', $today)->count();

        // 3. Data Presensi Terbaru untuk Monitoring Real-Time (10 Aktivitas Terakhir)
        $recentAttendances = AttendanceRecord::with('teacher')
            ->where('date', $today)
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalTeachers',
            'totalPresent',
            'totalLate',
            'totalAbsent',
            'totalScanHariIni',
            'recentAttendances'
        ));
    }
}