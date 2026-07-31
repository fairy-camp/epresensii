@extends('layouts.main')

@section('title', 'Laporan Rekap Presensi')
@section('page-title', 'Laporan Rekapitulasi Presensi')

@section('content')
<!-- Card Filter -->
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0 font-weight-bold text-primary"><i class="fas fa-filter me-1"></i> Filter Laporan</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.attendance') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label class="form-label font-weight-bold">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label font-weight-bold">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label font-weight-bold">Pegawai / Guru</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">-- Semua Guru --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>
                                {{ $t->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label font-weight-bold">Status Kehadiran</label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Tepat Waktu</option>
                        <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('reports.attendance') }}" class="btn btn-secondary">
                    <i class="fas fa-undo me-1"></i> Reset Filter
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cards Ringkasan Statistik -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>{{ $totalRecords }}</h3>
                <p>Total Data Presensi</p>
            </div>
            <div class="icon"><i class="fas fa-clipboard-list"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ $totalPresent }}</h3>
                <p>Tepat Waktu</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-warning shadow-sm">
            <div class="inner text-white">
                <h3 class="text-white">{{ $totalLate }}</h3>
                <p class="text-white">Terlambat</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
</div>

<!-- Tabel Data Rekapitulasi -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 font-weight-bold text-dark">Data Presensi</h5>
        <a href="{{ route('reports.attendance.print', request()->all()) }}" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i> Cetak / Save PDF
        </a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-striped mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>Tanggal</th>
                    <th>NIP</th>
                    <th>Nama Pegawai</th>
                    <th class="text-center">Jam Masuk</th>
                    <th class="text-center">Jam Pulang</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d M Y') }}</td>
                        <td><code>{{ $row->teacher->nip ?? '-' }}</code></td>
                        <td class="fw-bold">{{ $row->teacher->full_name }}</td>
                        <td class="text-center text-success font-monospace">
                            {{ $row->check_in_time ?? '-' }}
                        </td>
                        <td class="text-center text-danger font-monospace">
                            {{ $row->check_out_time ?? '-' }}
                        </td>
                        <td class="text-center">
                            @if($row->status === 'present')
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i> Tepat Waktu</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Terlambat</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                            Tidak ada data presensi ditemukan untuk periode/filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection