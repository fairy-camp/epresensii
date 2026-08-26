@extends('layouts.main')

@section('title', 'Laporan Rekap Presensi Apel')
@section('page-title', 'Laporan Rekapitulasi Presensi Apel Pagi Bulanan')

@section('content')
<!-- Card Filter -->
<div class="card card-outline card-warning shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0 font-weight-bold text-warning">
            <i class="fas fa-filter me-1"></i> Filter Laporan Apel Bulanan
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.apel') }}" method="GET">
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
                    <button type="submit" class="btn btn-warning text-dark w-100 fw-bold">
                        <i class="fas fa-search me-1"></i> Tampilkan
                    </button>
                    <a href="{{ route('reports.apel') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Card Laporan Matriks Apel -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-flag me-1 text-warning"></i> Matriks Presensi Apel Pagi {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
        </h5>
        <!-- Tombol Download PDF F4 -->
        <a href="{{ route('reports.apel.pdf', ['month' => $month, 'year' => $year]) }}" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i> Download Laporan Apel
        </a>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="p-2 bg-light border rounded flex-grow-1">
                <span class="fw-bold me-2" style="font-size: 12px;">Keterangan:</span>
                <span class="badge bg-success text-white me-1">Hadir</span>
                <span class="badge bg-warning text-dark me-1">Terlambat</span>
                <span class="badge bg-danger text-white me-1">Tidak Absen</span>
                <span class="badge bg-secondary me-1">- : Minggu</span>
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
            <table class="table table-bordered table-sm text-center align-middle mb-0" id="apelMatrixTable" style="font-size: 10px;">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="align-middle" style="width: 35px;">NO</th>
                        <th rowspan="2" class="align-middle text-start" style="min-width: 170px;">NAMA GURU / KARYAWAN</th>
                        <th colspan="{{ count($days) }}" class="align-middle">TANGGAL APEL PAGI (SENIN & KAMIS)</th>
                        <th rowspan="2" class="align-middle" style="width: 65px;">TOTAL APEL</th>
                    </tr>
                    <tr>
                        @foreach($days as $dayInfo)
                            <th class="{{ $dayInfo['is_sunday'] ? 'bg-secondary text-white' : '' }}" style="min-width: 50px;">
                                {{ $dayInfo['day'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $index => $teacher)
                        @php $totalApel = 0; @endphp
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
                                    } elseif (!$dayInfo['is_apel_day']) {
                                        $displayText = '-';
                                        $cellClass = 'text-muted';
                                    } else {
                                        $todayDate = \Carbon\Carbon::today()->toDateString();
                                        if ($dayInfo['date'] > $todayDate) {
                                            $displayText = '';
                                        } elseif (!$rec) {
                                            $displayText = 'A';
                                            $cellClass = 'text-danger fw-bold';
                                        } else {
                                            if ($rec->status === 'absent') {
                                                $displayText = 'A';
                                                $cellClass = 'text-danger fw-bold';
                                            } else {
                                                $displayText = \Carbon\Carbon::parse($rec->scan_time)->format('H:i');
                                                $totalApel++;

                                                if ($rec->status === 'late') {
                                                    $cellClass = 'bg-warning text-dark fw-bold';
                                                } else {
                                                    $cellClass = 'text-success fw-bold';
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <td class="{{ $cellClass }}" style="white-space: nowrap;">{{ $displayText }}</td>
                            @endforeach
                            
                            <td class="fw-bold bg-light text-warning text-dark">{{ $totalApel }}</td>
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
    const table = document.getElementById('apelMatrixTable');

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