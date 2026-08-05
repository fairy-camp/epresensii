@extends('layouts.main')

@section('title', 'Laporan Rekap Presensi Matriks')
@section('page-title', 'Laporan Rekapitulasi Presensi Matriks Bulanan')

@section('content')
<!-- Card Filter -->
<div class="card card-outline card-primary shadow-sm mb-4 print-none">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0 font-weight-bold text-primary">
            <i class="fas fa-filter me-1"></i> Filter Laporan Bulanan
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.attendance') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">Bulan</label>
                    <select name="month" class="form-select">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">Tahun</label>
                    <select name="year" class="form-select">
                        @foreach(range(\Carbon\Carbon::now()->year - 2, \Carbon\Carbon::now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Tampilkan
                    </button>
                    <a href="{{ route('reports.attendance') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Card Laporan Matriks -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap print-none">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-table me-1"></i> Matriks Presensi Periode {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
        </h5>
        <button onclick="window.print()" class="btn btn-danger">
            <i class="fas fa-print me-1"></i> Cetak / Save PDF
        </button>
    </div>

    <div class="card-body">
        <!-- Legend Keterangan Status -->
        <div class="mb-3 p-3 bg-light border rounded">
            <span class="fw-bold me-2">Keterangan Kode Presensi:</span>
            <span class="badge bg-success me-1">H : Hadir (Tepat Waktu)</span>
            <span class="badge bg-warning text-dark me-1">T : Terlambat</span>
            <span class="badge bg-info text-dark me-1">TPP : Tidak Absen Pulang</span>
            <span class="badge bg-danger me-1">TA : Tidak Absen</span>
            <span class="badge bg-secondary me-1">- : Libur (Minggu)</span>
        </div>

        <!-- Tabel Matriks -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size: 11px;">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="align-middle" style="width: 35px;">NO</th>
                        <th rowspan="2" class="align-middle text-start" style="min-width: 170px;">NAMA GURU / KARYAWAN</th>
                        <th colspan="{{ count($days) }}" class="align-middle">TANGGAL KEHADIRAN</th>
                        <th rowspan="2" class="align-middle" style="width: 65px;">TOTAL PRESENSI</th>
                    </tr>
                    <tr>
                        @foreach($days as $dayInfo)
                            <th class="{{ $dayInfo['is_sunday'] ? 'bg-secondary text-white' : '' }}" style="width: 22px;">
                                {{ $dayInfo['day'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $index => $teacher)
                        @php $totalPresensi = 0; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-start fw-bold">{{ $teacher->full_name }}</td>
                            
                            @foreach($days as $d => $dayInfo)
                                @php
                                    $rec = $matrix[$teacher->id][$d] ?? null;
                                    $symbol = '';
                                    $cellClass = '';

                                    if ($dayInfo['is_sunday']) {
                                        $symbol = '-';
                                        $cellClass = 'bg-secondary text-white fw-bold';
                                    } else {
                                        $todayDate = \Carbon\Carbon::today()->toDateString();
                                        
                                        // Tanggal di masa mendatang
                                        if ($dayInfo['date'] > $todayDate) {
                                            $symbol = '';
                                        } 
                                        // Tidak ada data presensi / tidak absen datang
                                        elseif (!$rec || is_null($rec->check_in_time)) {
                                            $symbol = 'TA';
                                            $cellClass = 'text-danger fw-bold';
                                        } 
                                        // Absen datang ada, tapi lupa/tidak absen pulang
                                        elseif (is_null($rec->check_out_time)) {
                                            $symbol = 'TPP';
                                            $cellClass = 'text-info fw-bold';
                                            $totalPresensi++;
                                        } 
                                        // Datang terlambat
                                        elseif ($rec->status === 'late') {
                                            $symbol = 'T';
                                            $cellClass = 'text-warning fw-bold';
                                            $totalPresensi++;
                                        } 
                                        // Hadir Tepat Waktu
                                        else {
                                            $symbol = 'H';
                                            $cellClass = 'text-success fw-bold';
                                            $totalPresensi++;
                                        }
                                    }
                                @endphp
                                <td class="{{ $cellClass }}">{{ $symbol }}</td>
                            @endforeach
                            
                            <!-- Total Kedatangan -->
                            <td class="fw-bold bg-light text-primary">{{ $totalPresensi }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($days) + 3 }}" class="text-center py-4 text-muted">
                                Tidak ada data guru aktif ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CSS Spesifik Mode Cetak / Export PDF Browser -->
<style>
@media print {
    body {
        font-size: 9px;
        background-color: #fff !important;
    }
    .print-none, sidebar, nav, header, footer, .main-sidebar, .main-header {
        display: none !important;
    }
    .content-wrapper, .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
    .bg-secondary {
        background-color: #6c757d !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .text-white {
        color: #fff !important;
    }
    @page {
        size: landscape;
        margin: 8mm;
    }
}
</style>
@endsection