<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\ApelAttendance;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Helper untuk menyusun data matriks bulanan & daftar guru
     */
    private function prepareMonthlyData(Request $request)
    {
        $month = (int) $request->input('month', Carbon::now()->month);
        $year  = (int) $request->input('year', Carbon::now()->year);

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $carbonDate = Carbon::parse($dateStr);
            $days[$d] = [
                'day'         => $d,
                'date'        => $dateStr,
                'is_sunday'   => $carbonDate->isSunday(),
                'is_apel_day' => $carbonDate->isMonday() || $carbonDate->isThursday(),
            ];
        }

        $teachers = Teacher::where('is_active', true)
            ->whereHas('user', function ($query) {
                $query->whereNotIn('role', ['admin', 'super_admin', 'petugas']);
            })
            ->orderBy('full_name', 'asc')
            ->get();

        return compact('teachers', 'days', 'month', 'year');
    }

    // ==========================================
    // 1. LAPORAN PRESENSI UTAMA (HARIAN)
    // ==========================================

    public function index(Request $request)
    {
        $data = $this->prepareMonthlyData($request);

        $attendanceRecords = AttendanceRecord::whereYear('date', $data['year'])
            ->whereMonth('date', $data['month'])
            ->get();

        $matrix = [];
        foreach ($attendanceRecords as $record) {
            $dayNum = (int) Carbon::parse($record->date)->format('j');
            $matrix[$record->teacher_id][$dayNum] = $record;
        }

        return view('reports.attendance', array_merge($data, ['matrix' => $matrix]));
    }

    public function exportAttendancePdf(Request $request)
    {
        $data = $this->prepareMonthlyData($request);
        $school = SchoolSetting::first();

        $attendanceRecords = AttendanceRecord::whereYear('date', $data['year'])
            ->whereMonth('date', $data['month'])
            ->get();

        $matrix = [];
        foreach ($attendanceRecords as $record) {
            $dayNum = (int) Carbon::parse($record->date)->format('j');
            $matrix[$record->teacher_id][$dayNum] = $record;
        }

        $pdf = Pdf::loadView('reports.attendance_pdf', array_merge($data, [
            'matrix' => $matrix,
            'school' => $school
        ]))
        ->setPaper([0, 0, 612.00, 936.00], 'landscape');

        $monthName = Carbon::create()->month($data['month'])->translatedFormat('F');
        return $pdf->download("Laporan_Presensi_Harian_{$monthName}_{$data['year']}.pdf");
    }

    // ==========================================
    // 2. LAPORAN PRESENSI APEL PAGI
    // ==========================================

    public function apelIndex(Request $request)
    {
        $data = $this->prepareMonthlyData($request);

        $apelRecords = ApelAttendance::whereYear('date', $data['year'])
            ->whereMonth('date', $data['month'])
            ->get();

        $matrix = [];
        foreach ($apelRecords as $record) {
            $dayNum = (int) Carbon::parse($record->date)->format('j');
            $matrix[$record->teacher_id][$dayNum] = $record;
        }

        return view('reports.apel', array_merge($data, ['matrix' => $matrix]));
    }

    public function exportApelPdf(Request $request)
    {
        $data = $this->prepareMonthlyData($request);
        $school = SchoolSetting::first();

        $apelRecords = ApelAttendance::whereYear('date', $data['year'])
            ->whereMonth('date', $data['month'])
            ->get();

        $matrix = [];
        foreach ($apelRecords as $record) {
            $dayNum = (int) Carbon::parse($record->date)->format('j');
            $matrix[$record->teacher_id][$dayNum] = $record;
        }

        $pdf = Pdf::loadView('reports.apel_pdf', array_merge($data, [
            'matrix' => $matrix,
            'school' => $school
        ]))
        ->setPaper([0, 0, 612.00, 936.00], 'landscape');

        $monthName = Carbon::create()->month($data['month'])->translatedFormat('F');
        return $pdf->download("Laporan_Presensi_Apel_{$monthName}_{$data['year']}.pdf");
    }
}