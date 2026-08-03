@extends('layouts.main')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Kartu Ringkasan Statistik -->
<div class="row">
    <div class="col-md-3">
        <div class="card bg-primary text-white mb-3">
            <div class="card-body">
                <h6>Total Pegawai</h6>
                <h3>{{ $totalTeachers }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white mb-3">
            <div class="card-body">
                <h6>Hadir Tepat Waktu</h6>
                <h3>{{ $totalPresent }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white mb-3">
            <div class="card-body">
                <h6>Terlambat</h6>
                <h3>{{ $totalLate }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white mb-3">
            <div class="card-body">
                <h6>Tidak Hadir / Alpa</h6>
                <h3>{{ $totalAbsent }}</h3>
            </div>
        </div>
    </div>
</div>



<!-- Tabel Monitoring Presensi Real-Time dan List Guru Telat -->
<div class="row">
    <!-- Presensi Terbaru Hari Ini -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">
                <i class="fas fa-list me-2"></i> Presensi Terbaru Hari Ini
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nama Guru</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $record)
                        <tr>
                            <td>{{ $record->teacher->full_name ?? 'N/A' }}</td>
                            <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i:s') : '-' }}</td>
                            <td>{{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i:s') : '-' }}</td>
                            <td>
                                @if($record->status === 'present')
                                    <span class="badge bg-success">Tepat Waktu</span>
                                @elseif($record->status === 'late')
                                    <span class="badge bg-warning text-dark">Terlambat</span>
                                @elseif($record->status === 'early_leave')
                                    <span class="badge bg-info">Pulang Cepat</span>
                                @else
                                    <span class="badge bg-danger">Absen</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Belum ada data presensi hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- List Guru Telat -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-danger font-weight-bold">
                <i class="fas fa-exclamation-triangle me-2"></i> Guru dan Karyawan Telat
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
                        @forelse($lateAttendances as $record)
                        <tr>
                            <td>{{ $record->teacher->full_name ?? 'N/A' }}</td>
                            <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i:s') : '-' }}</td>
                            <td><span class="badge bg-warning text-dark">Terlambat</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Tidak ada guru yang terlambat hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0 fw-bold">Selamat Datang!</h5>
    </div>
    <div class="card-body">
        <p>Anda berhasil masuk ke sistem <strong>E-Presensi UP RPL CodePelita</strong> sebagai <code>{{ auth()->user()->email }}</code>.</p>
    </div>
</div>
@endsection
