@extends('layouts.main')

@section('title', 'Riwayat Presensi Saya')
@section('page-title', 'Riwayat Presensi Saya')

@push('styles')
<style>
    /* Responsive Text Alignment Profil Guru */
    .profile-info-text {
        text-align: center;
    }
    @media (min-width: 768px) {
        .profile-info-text {
            text-align: left !important;
        }
    }

    /* Styling Presisi Kartu Presensi di Dalam Modal */
    .id-card-preview {
        width: 58mm;
        height: 82mm;
        position: relative;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        margin: 0 auto;

        /* Menjaga ketajaman rendering gambar di browser */
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }

    /* Tag <img> Background khusus agar ter-render HD oleh html2canvas */
    .id-card-preview .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
    }

    /* Position Overlay 1: Banner Nama & ID */
    .identity-box-modal {
        position: absolute;
        top: 36.6%;
        left: 0;
        width: 100%;
        height: 8%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: #ffffff;
        padding: 0 8px;
        box-sizing: border-box;
        z-index: 2;
    }

    .identity-box-modal .name {
        font-size: 8pt;
        font-weight: 700;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 92%;
        margin-bottom: 2px;
        color: #ffffff;
    }

    .identity-box-modal .id-number {
        font-size: 5.5pt;
        font-weight: 600;
        line-height: 1.1;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 95%;
    }

    /* Position Overlay 2: Container QR Code */
    .qr-container-modal {
        position: absolute;
        top: 50%;
        left: 21%;
        width: 58%;
        height: 38%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .qr-container-modal svg, .qr-container-modal img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain;
    }
</style>
@endpush

@section('content')

{{-- Alert Flash Message --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Terjadi Kesalahan:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Profil Singkat Guru (Sistem Grid Bootstrap Standar) -->
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center g-3">
            
            {{-- Foto Profile Icon --}}
            <div class="col-12 col-md-auto text-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto"
                     style="width: 60px; height: 60px; font-size: 24px;">
                    <i class="fas fa-user"></i>
                </div>
            </div>

            {{-- Info Nama & NIP (Tengah di HP via CSS, Kiri di Desktop via Media Query) --}}
            <div class="col-12 col-md profile-info-text">
                <h4 class="mb-1 font-weight-bold text-dark">{{ $teacher->full_name }}</h4>
                <p class="text-muted mb-0">
                    NIP: <code>{{ $teacher->nip ?? '-' }}</code>
                    <span class="d-none d-sm-inline mx-1">|</span>
                    <br class="d-inline d-sm-none">
                    Jabatan: {{ $teacher->position->name ?? 'Guru / Pegawai' }}
                </p>
            </div>

            {{-- Tombol Aksi (Rapat Kanan di Desktop, Berdampingan/Rapi di HP) --}}
            <div class="col-12 col-md-auto">
                <div class="d-flex flex-row flex-wrap gap-2 justify-content-center justify-content-md-end">
                    <button type="button"
                            class="btn btn-sm btn-outline-primary text-nowrap flex-fill flex-md-grow-0"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCard">
                        <i class="fas fa-id-card me-1"></i> Kartu Presensi
                    </button>

                    <button type="button"
                            class="btn btn-sm btn-outline-warning text-nowrap text-dark fw-semibold flex-fill flex-md-grow-0"
                            data-bs-toggle="modal"
                            data-bs-target="#modalChangePassword">
                        <i class="fas fa-key me-1"></i> Ubah Password
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL DOWNLOAD KARTU PRESENSI              -->
<!-- ========================================== -->
<div class="modal fade" id="modalCard" tabindex="-1" aria-labelledby="modalCardLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-bold text-dark" id="modalCardLabel">
                    <i class="fas fa-id-card me-2 text-primary"></i>Kartu Presensi Guru dan Karyawan
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light py-4">
                <div class="id-card-preview" id="modalCardElement">
                    <!-- Image Tag untuk background agar ter-render tajam oleh html2canvas -->
                    <img src="{{ asset('img/format-kartu.png') }}?v=4" class="card-bg" alt="Kartu Background">
                    
                    <div class="identity-box-modal">
                        <div class="name" title="{{ $teacher->full_name }}">{{ $teacher->full_name }}</div>
                        <div class="id-number">email : {{ $teacher->user->email ?? '-' }}  |  kode : {{ $teacher->nip ?? '-' }}</div>
                    </div>
                    <div class="qr-container-modal">
                        @if($teacher->activeQrCode)
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(0)->generate($teacher->activeQrCode->code) !!}
                        @else
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(0)->generate($teacher->nip ?? $teacher->id) !!}
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 justify-content-between bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success btn-sm fw-bold" id="btnDownloadCard">
                    <i class="fas fa-download me-1"></i> Download PNG
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL UBAH PASSWORD MANDIRI               -->
<!-- ========================================== -->
<div class="modal fade" id="modalChangePassword" tabindex="-1" aria-labelledby="modalChangePasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10 py-3">
                <h6 class="modal-title fw-bold text-dark" id="modalChangePasswordLabel">
                    <i class="fas fa-key me-2 text-warning"></i>Ubah Password Akun
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('password.update-self') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-body py-3 text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password Saat Ini <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" placeholder="Masukkan password lama Anda" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" tabIndex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" tabIndex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru" minlength="6" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" tabIndex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold text-dark">
                        <i class="fas fa-save me-1"></i> Perbarui Password
                    </button>
                </div>
            </form>
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
            <i class="fas fa-clock me-1 text-primary"></i> Presensi
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
                                    @elseif($row->status === 'late')
                                        <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i> Terlambat</span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2"><i class="fas fa-times me-1"></i> Alfa / Tidak Presensi Datang</span>
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

@push('scripts')
<!-- html2canvas untuk Ekspor Gambar HD -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Download Kartu Presensi
        const btnDownload = document.getElementById('btnDownloadCard');
        if (btnDownload) {
            btnDownload.addEventListener('click', function () {
                const cardElement = document.getElementById('modalCardElement');
                const teacherName = "{{ $teacher->full_name }}";
                const slugName = teacherName.toLowerCase().replace(/[^a-z0-9]/g, '_');

                html2canvas(cardElement, {
                    scale: 4,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: null,
                    logging: false,
                    imageTimeout: 0,
                    onclone: (clonedDoc) => {
                        const bg = clonedDoc.querySelector('.card-bg');
                        if (bg) bg.style.display = 'block';
                    }
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'Kartu_Presensi_' + slugName + '.png';
                    link.href = canvas.toDataURL('image/png', 1.0);
                    link.click();
                });
            });
        }

        // Toggle Password Visibility (Mata / Show-Hide Password)
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function () {
                const inputGroup = this.closest('.input-group');
                const passwordInput = inputGroup.querySelector('input');
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>
@endpush