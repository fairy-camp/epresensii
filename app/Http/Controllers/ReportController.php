<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Menampilkan Halaman Filter & Matriks Laporan Presensi Bulanan
     */
    public function index(Request $request)
    {
        // Filter Bulan & Tahun (Default: Bulan dan Tahun saat ini)
        $month = (int) $request->input('month', Carbon::now()->month);
        $year  = (int) $request->input('year', Carbon::now()->year);

        // Hitung jumlah hari dalam bulan terpilih (28, 29, 30, atau 31 hari)
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // Menyusun daftar tanggal 1 hingga akhir bulan serta penandaan hari Minggu
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $carbonDate = Carbon::parse($dateStr);
            $days[$d] = [
                'day'       => $d,
                'date'      => $dateStr,
                'is_sunday' => $carbonDate->isSunday(),
            ];
        }

        // Ambil seluruh data guru aktif
        $teachers = Teacher::where('is_active', true)
            ->orderBy('full_name', 'asc')
            ->get();

        // Ambil seluruh data presensi pada bulan & tahun terpilih
        $attendanceRecords = AttendanceRecord::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        // Petakan data presensi ke dalam array matriks [teacher_id][nomor_hari]
        $matrix = [];
        foreach ($attendanceRecords as $record) {
            $dayNum = (int) Carbon::parse($record->date)->format('j');
            $matrix[$record->teacher_id][$dayNum] = $record;
        }

        return view('reports.attendance', compact(
            'teachers',
            'days',
            'month',
            'year',
            'matrix'
        ));
    }

    /**
     * Mengarahkan ke tampilan cetak / cetak PDF
     */
    public function print(Request $request)
    {
        $school = SchoolSetting::first();

        // Menggunakan logika query matriks yang sama dengan index
        $month = (int) $request->input('month', Carbon::now()->month);
        $year  = (int) $request->input('year', Carbon::now()->year);

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $carbonDate = Carbon::parse($dateStr);
            $days[$d] = [
                'day'       => $d,
                'date'      => $dateStr,
                'is_sunday' => $carbonDate->isSunday(),
            ];
        }

        $teachers = Teacher::where('is_active', true)
            ->orderBy('full_name', 'asc')
            ->get();

        $attendanceRecords = AttendanceRecord::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $matrix = [];
        foreach ($attendanceRecords as $record) {
            $dayNum = (int) Carbon::parse($record->date)->format('j');
            $matrix[$record->teacher_id][$dayNum] = $record;
        }

        return view('reports.attendance', compact(
            'teachers',
            'days',
            'month',
            'year',
            'matrix',
            'school'
        ));
    }
}