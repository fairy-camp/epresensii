<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Menampilkan Halaman Filter & Rekap Laporan
    public function index(Request $request)
    {
        // Default Filter: Tanggal awal bulan ini s/d hari ini
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());
        $teacherId = $request->input('teacher_id');
        $status    = $request->input('status');

        // Query Dasar Presensi
        $query = AttendanceRecord::with(['teacher.position', 'shiftAssignment.workSchedule'])
            ->whereBetween('date', [$startDate, $endDate]);

        // Filter Spesifik Guru
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        // Filter Spesifik Status (present / late)
        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->get();

        // Hitung Ringkasan Statistik
        $totalRecords = $attendances->count();
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalLate    = $attendances->where('status', 'late')->count();

        // Data Master Guru untuk Dropdown Filter
        $teachers = Teacher::where('is_active', true)->orderBy('full_name')->get();

        return view('reports.attendance', compact(
            'attendances',
            'teachers',
            'startDate',
            'endDate',
            'teacherId',
            'status',
            'totalRecords',
            'totalPresent',
            'totalLate'
        ));
    }

    // Mencetak Laporan (Print / PDF View)
    public function print(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());
        $teacherId = $request->input('teacher_id');
        $status    = $request->input('status');

        $query = AttendanceRecord::with(['teacher.position', 'shiftAssignment.workSchedule'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('date', 'asc')
            ->orderBy('check_in_time', 'asc')
            ->get();

        $school = SchoolSetting::first();
        $selectedTeacher = $teacherId ? Teacher::find($teacherId) : null;

        return view('reports.print', compact(
            'attendances',
            'school',
            'startDate',
            'endDate',
            'selectedTeacher'
        ));
    }
}