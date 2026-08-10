@extends('layouts.main')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Ringkasan Statistik Presensi Harian -->
<h5 class="mb-3 text-secondary font-weight-bold"><i class="fas fa-clock text-primary mr-1"></i> Rekap Presensi Harian Hari Ini</h5>
<div class="row">
    <div class="col-md-3 col-6">
        <div class="card bg-primary text-white mb-3 shadow-sm">
            <div class="card-body">
                <h6>Total Guru & Pegawai</h6>
                <h3>{{ $totalTeachers }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-success text-white mb-3 shadow-sm">
            <div class="card-body">
                <h6>Hadir Tepat Waktu</h6>
                <h3>{{ $totalPresent }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-warning text-white mb-3 shadow-sm">
            <div class="card-body">
                <h6>Terlambat</h6>
                <h3>{{ $totalLate }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-danger text-white mb-3 shadow-sm">
            <div class="card-body">
                <h6>Belum Absen / Alpa</h6>
                <h3>{{ $totalLateCount < 0 ? 0 : $totalLateCount }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Ringkasan Statistik Presensi Apel Pagi -->
<h5 class="mb-3 mt-3 text-secondary font-weight-bold"><i class="fas fa-flag text-warning mr-1"></i> Rekap Presensi Apel Pagi Hari Ini</h5>
<div class="row">
    <div class="col-md-3 col-6">
        <div class="card bg-info text-white mb-3 shadow-sm">
            <div class="card-body">
                <h6>Total Hadir Apel</h6>
                <h3>{{ $totalApelHadir }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-success text-white mb-3 shadow-sm">
            <div class="card-body">
                <h6>Tepat Waktu (<= 07:00)</h6>
                <h3>{{ $totalApelPresent }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-warning text-dark mb-3 shadow-sm">
            <div class="card-body">
                <h6>Terlambat (> 07:00)</h6>
                <h3>{{ $totalApelLate }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-danger text-white mb-3 shadow-sm">
            <div class="card-body">
                <h6>Belum Scan / Alpa</h6>
                <h3>{{ $totalApelAbsent }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Monitoring Presensi Harian & Apel -->
<div class="row mt-2">
    <!-- Presensi Harian Terbaru -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold">
                <i class="fas fa-list me-2 text-primary"></i> Presensi Harian Terbaru
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nama Guru</th>
                            <th>Jam Masuk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $record)
                        <tr>
                            <td>{{ $record->teacher->name ?? $record->teacher->full_name ?? 'N/A' }}</td>
                            <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i:s') : '-' }}</td>
                            <td>
                                @if($record->status === 'present')
                                    <span class="badge bg-success">Tepat Waktu</span>
                                @elseif($record->status === 'late')
                                    <span class="badge bg-warning text-dark">Terlambat</span>
                                @else
                                    <span class="badge bg-danger">Absen</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Belum ada data presensi harian hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Presensi Apel Pagi Terbaru Hari Ini -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold">
                <i class="fas fa-flag me-2 text-warning"></i> Presensi Apel Pagi Terbaru
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nama Guru</th>
                            <th>Jam Apel</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentApelAttendances as $record)
                        <tr>
                            <td>{{ $record->teacher->name ?? $record->teacher->full_name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->scan_time)->format('H:i:s') }} WIB</td>
                            <td>
                                @if($record->status === 'present')
                                    <span class="badge bg-success">Tepat Waktu</span>
                                @elseif($record->status === 'late')
                                    <span class="badge bg-warning text-dark">Terlambat</span>
                                @else
                                    <span class="badge bg-danger">Absen</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Belum ada data presensi apel hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection