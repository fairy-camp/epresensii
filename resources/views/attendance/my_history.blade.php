@extends('layouts.main')

@section('title', 'Riwayat Presensi Saya')
@section('page-title', 'Riwayat Presensi Saya')

@section('content')
<!-- Profil Singkat Guru -->
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 24px;">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h4 class="mb-0 font-weight-bold text-dark">{{ $teacher->full_name }}</h4>
                <p class="text-muted mb-0">NIP: <code>{{ $teacher->nip ?? '-' }}</code> | Jabatan: {{ $teacher->position->name ?? 'Guru / Pegawai' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bulan & Tahun -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('attendance.my-history') }}" method="GET" class="row align-items-end g-3">
            <div class="col-md-4">
                <label class="form-label font-weight-bold">Pilih Bulan</label>
                <select name="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label font-weight-bold">Pilih Tahun</label>
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Tampilkan Riwayat
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs nav-justified mb-3" id="historyTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active font-weight-bold" id="main-tab" data-bs-toggle="tab" data-bs-target="#main-history" type="button" role="tab" aria-controls="main-history" aria-selected="true">
            <i class="fas fa-clock me-1 text-primary"></i> Presensi Harian / Shift
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link font-weight-bold" id="apel-tab" data-bs-toggle="tab" data-bs-target="#apel-history" type="button" role="tab" aria-controls="apel-history" aria-selected="false">
            <i class="fas fa-users me-1 text-info"></i> Presensi Apel
        </button>
    </li>
</ul>

<div class="tab-content" id="historyTabContent">
    <!-- TAB 1: PRESENSI HARIAN -->
    <div class="tab-pane fade show active" id="main-history" role="tabpanel" aria-labelledby="main-tab">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="small-box bg-info shadow-sm">
                    <div class="inner">
                        <h3>{{ $totalRecords }} Hari</h3>
                        <p>Total Kehadiran Harian</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success shadow-sm">
                    <div class="inner">
                        <h3>{{ $totalPresent }} Kali</h3>
                        <p>Tepat Waktu</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning shadow-sm">
                    <div class="inner text-white">
                        <h3 class="text-white">{{ $totalLate }} Kali</h3>
                        <p class="text-white">Terlambat</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-list me-1 text-primary"></i> Detail Log Presensi Harian
                </h5>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Tanggal</th>
                            <th class="text-center">Jam Masuk</th>
                            <th class="text-center">Jam Pulang</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $index => $row)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('l, d F Y') }}</strong>
                                </td>
                                <td class="text-center text-success font-monospace fw-bold">
                                    {{ $row->check_in_time ? \Carbon\Carbon::parse($row->check_in_time)->format('H:i:s') : '-' }}
                                </td>
                                <td class="text-center text-danger font-monospace fw-bold">
                                    {{ $row->check_out_time ? \Carbon\Carbon::parse($row->check_out_time)->format('H:i:s') : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($row->status === 'present')
                                        <span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i> Tepat Waktu</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i> Terlambat</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                    Belum ada catatan presensi harian pada bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: PRESENSI APEL -->
    <div class="tab-pane fade" id="apel-history" role="tabpanel" aria-labelledby="apel-tab">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="small-box bg-info shadow-sm">
                    <div class="inner">
                        <h3>{{ $totalApelRecords }} Kali</h3>
                        <p>Total Mengikuti Apel</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success shadow-sm">
                    <div class="inner">
                        <h3>{{ $totalApelPresent }} Kali</h3>
                        <p>Apel Tepat Waktu</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning shadow-sm">
                    <div class="inner text-white">
                        <h3 class="text-white">{{ $totalApelLate }} Kali</h3>
                        <p class="text-white">Terlambat Apel</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-clock"></i></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-list me-1 text-info"></i> Detail Log Presensi Apel
                </h5>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Tanggal Apel</th>
                            <th class="text-center">Jam Scan</th>
                            <th class="text-center">Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apelAttendances as $index => $apel)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($apel->date)->translatedFormat('l, d F Y') }}</strong>
                                </td>
                                <td class="text-center text-primary font-monospace fw-bold">
                                    {{ $apel->scan_time ?? '-' }}
                                </td>
                                <td class="text-center">
                                    @if($apel->status === 'present')
                                        <span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i> Hadir</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i> Terlambat</span>
                                    @endif
                                </td>
                                <td>{{ $apel->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                    Belum ada catatan presensi apel pada bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection