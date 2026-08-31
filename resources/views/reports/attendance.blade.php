@extends('layouts.main')

@section('title', 'Laporan Rekap Presensi Harian')
@section('page-title', 'Laporan Rekapitulasi Presensi Harian Bulanan')

@section('content')
<!-- Card Filter -->
<div class="card card-outline card-primary shadow-sm mb-4">
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
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-table me-1"></i> Matriks Presensi Harian {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
        </h5>
        <!-- Tombol Download PDF F4 -->
        <a href="{{ route('reports.attendance.pdf', ['month' => $month, 'year' => $year]) }}" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i> Download Laporan Presensi
        </a>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="p-2 bg-light border rounded flex-grow-1">
                <span class="fw-bold me-2" style="font-size: 12px;">Keterangan:</span>
                <span class="badge bg-white text-dark border me-1">Hadir Tepat Waktu</span>
                <span class="badge bg-warning text-dark me-1">Terlambat</span>
                <span class="badge bg-danger me-1">Tidak Absen Pagi</span>
                <span class="badge bg-secondary me-1">- : Libur (Minggu)</span>
            </div>

            <!-- Input Pencarian Nama Guru / Karyawan -->
            <div style="min-width: 260px;">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchTeacherName" class="form-control border-start-0 ps-0" placeholder="Cari nama guru / pegawai..." autocomplete="off">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle mb-0" id="attendanceMatrixTable" style="font-size: 10px;">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="align-middle" style="width: 35px;">NO</th>
                        <th rowspan="2" class="align-middle text-start" style="min-width: 170px;">NAMA GURU / KARYAWAN</th>
                        <th colspan="{{ count($days) }}" class="align-middle">TANGGAL KEHADIRAN</th>
                        <th rowspan="2" class="align-middle" style="width: 65px;">TOTAL PRESENSI</th>
                    </tr>
                    <tr>
                        @foreach($days as $dayInfo)
                            <th class="{{ $dayInfo['is_sunday'] ? 'bg-secondary text-white' : '' }}" style="min-width: 80px;">
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
                            <td class="text-start fw-bold teacher-name">{{ $teacher->full_name }}</td>
                            
                            @foreach($days as $d => $dayInfo)
                                @php
                                    $rec = $matrix[$teacher->id][$d] ?? null;
                                    $displayText = '';
                                    $cellClass = '';

                                    if ($dayInfo['is_sunday']) {
                                        $displayText = '-';
                                        $cellClass = 'bg-secondary text-white fw-bold';
                                    } else {
                                        $todayDate = \Carbon\Carbon::today()->toDateString();
                                        if ($dayInfo['date'] > $todayDate) {
                                            $displayText = '';
                                        } elseif (!$rec) {
                                            // Jika tidak ada record presensi sama sekali pada tanggal tersebut
                                            $displayText = '-';
                                        } else {
                                            // Ambil jam masuk & jam pulang (gunakan '-' jika nilainya null)
                                            $inTime  = $rec->check_in_time ? \Carbon\Carbon::parse($rec->check_in_time)->format('H:i') : '-';
                                            $outTime = $rec->check_out_time ? \Carbon\Carbon::parse($rec->check_out_time)->format('H:i') : '-';
                                            $displayText = "{$inTime} - {$outTime}";

                                            // Penyesuaian warna sel dan perhitungan Total Presensi
                                            if ($rec->status === 'late') {
                                                $cellClass = 'bg-warning text-dark fw-bold';
                                                $totalPresensi++;
                                            } elseif ($rec->status === 'absent') {
                                                $cellClass = 'bg-danger text-white fw-bold'; // Memberi warna merah untuk status Alfa
                                            } else {
                                                $totalPresensi++;
                                            }
                                        }
                                    }
                                @endphp
                                <td class="{{ $cellClass }}" style="white-space: nowrap;">{{ $displayText }}</td>
                            @endforeach
                            
                            <td class="fw-bold bg-light text-primary">{{ $totalPresensi }}</td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
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

<!-- Script Filter Pencarian Nama Guru secara Real-time -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTeacherName');
    const table = document.getElementById('attendanceMatrixTable');

    if (searchInput && table) {
        searchInput.addEventListener('keyup', function() {
            const keyword = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr:not(#emptyRow)');

            rows.forEach(row => {
                const nameCell = row.querySelector('.teacher-name');
                if (nameCell) {
                    const teacherName = nameCell.textContent.toLowerCase();
                    if (teacherName.includes(keyword)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    }
});
</script>
@endsection