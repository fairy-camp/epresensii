@extends('layouts.main')

@section('title', 'Data Guru & Pegawai')
@section('page-title', 'Data Guru & Pegawai')

@push('styles')
<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
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
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Daftar Guru / Pegawai</h5>
        <div class="card-tools">
            @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
            <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                <i class="fas fa-file-csv me-1"></i> Import CSV
            </button>
            <button type="button" class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#createTeacherModal">
                <i class="fas fa-plus me-1"></i> Tambah Pegawai
            </button>
            @endif
        </div>
    </div>

    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="teachersTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>Nama & NIP</th>
                        <th>Email</th>
                        <th>Jabatan & Role</th>
                        <th>Jadwal Kerja</th>
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <th class="text-center">QR Code</th>
                        <th class="text-center" width="180">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $index => $teacher)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <strong class="d-block">{{ $teacher->full_name }}</strong>
                                <small class="text-muted">NIP: {{ $teacher->nip ?? '-' }}</small>
                            </td>
                            <td>{{ $teacher->user->email ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info text-dark mb-1">
                                    {{ $teacher->position->name ?? '-' }}
                                </span>
                                <br>
                                <small class="badge bg-secondary">
                                    Role: {{ strtoupper(str_replace('_', ' ', $teacher->user->role ?? 'guru')) }}
                                </small>
                            </td>
                            <td>
                                <small class="fw-semibold">{{ $teacher->workSchedule->name ?? '-' }}</small>
                                <br>
                                <small class="text-muted">
                                    {{ $teacher->workSchedule->check_in_time ?? '' }} - {{ $teacher->workSchedule->check_out_time ?? '' }}
                                </small>
                            </td>

                            @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                            <td class="text-center">
                                @if($teacher->activeQrCode)
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#qrModal-{{ $teacher->id }}">
                                        <i class="fas fa-qrcode me-1"></i> Lihat QR
                                    </button>
                                @else
                                    <span class="badge bg-secondary">Belum ada QR</span>
                                @endif

                                <!-- SVG QR Code Tersembunyi -->
                                <div id="qr-svg-{{ $teacher->id }}" class="d-none">
                                    @if($teacher->activeQrCode)
                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(0)->generate($teacher->activeQrCode->code) !!}
                                    @else
                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(0)->generate($teacher->nip ?? $teacher->id) !!}
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <!-- Tombol Edit Modal -->
                                    <button type="button" class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editTeacherModal-{{ $teacher->id }}" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Tombol Modal Kartu Presensi -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info me-1 btn-card" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalCard" 
                                            data-id="{{ $teacher->id }}" 
                                            data-name="{{ $teacher->full_name }}" 
                                            data-email="{{ $teacher->user->email ?? '-' }}"
                                            title="Download Kartu Presensi">
                                        <i class="fas fa-id-card"></i>
                                    </button>

                                    <!-- Tombol Regenerate QR -->
                                    <form action="{{ route('teachers.regenerate-qr', $teacher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Regenerate QR Code baru?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary me-1" title="Regenerate QR">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </form>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $teacher->full_name }} beserta akun login-nya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Data">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL IMPORT CSV                          -->
<!-- ========================================== -->
@if(in_array(auth()->user()->role, ['super_admin', 'admin']))
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-file-csv me-2 text-success"></i>Import Data Pegawai dari CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teachers.import-csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-3">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle me-1"></i> Format file harus berupa <strong>CSV (Comma Delimited) (*.csv)</strong> dengan susunan kolom:
                        <br><code class="d-block mt-1 bg-white p-2 border rounded text-dark">nama_lengkap, email, password, role, nip, nik, jenis_kelamin, jabatan, jadwal_kerja, telepon</code>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih File CSV <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv, .txt" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold"><i class="fas fa-upload me-1"></i> Upload & Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- ========================================== -->
<!-- MODAL DOWNLOAD KARTU PRESENSI              -->
<!-- ========================================== -->
<div class="modal fade" id="modalCard" tabindex="-1" aria-labelledby="modalCardLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-bold text-dark" id="modalCardLabel">
                    <i class="fas fa-id-card me-2 text-primary"></i>Kartu Presensi Pegawai
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light py-4">
                <div class="id-card-preview" id="modalCardElement">
                    <!-- Image Tag untuk background agar ter-render tajam oleh html2canvas -->
                    <img src="{{ asset('img/format-kartu.png') }}?v=4" class="card-bg" alt="Kartu Background">
                    
                    <div class="identity-box-modal">
                        <div class="name" id="cardTeacherName">-</div>
                        <div class="id-number" id="cardTeacherId">email : - | id : -</div>
                    </div>
                    <div class="qr-container-modal" id="cardQrContainer">
                        <!-- QR Code di-inject lewat JS -->
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
<!-- MODAL QR & EDIT DILETAKKAN DI LUAR TABEL   -->
<!-- ========================================== -->
@foreach($teachers as $teacher)
    <!-- Modal QR Code -->
    @if($teacher->activeQrCode)
        <div class="modal fade" id="qrModal-{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content text-center">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold">QR Code Presensi</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="p-3 bg-white d-inline-block border rounded shadow-sm mb-3">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($teacher->activeQrCode->code) !!}
                        </div>
                        <h6 class="fw-bold mb-1">{{ $teacher->full_name }}</h6>
                        <code class="text-muted small">{{ $teacher->activeQrCode->code }}</code>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Edit Pegawai -->
    @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
        <div class="modal fade text-start" id="editTeacherModal-{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning bg-opacity-10">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2 text-warning"></i>Edit Data Pegawai: {{ $teacher->full_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body py-3">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-lock me-1"></i> Akun Login</h6>
                            <div class="row mb-3">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $teacher->user->email ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Password Baru <small class="text-muted">(Opsional)</small></label>
                                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ubah">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Role Akses <span class="text-danger">*</span></label>
                                    <select name="role" class="form-select" required>
                                        @php $currentRole = $teacher->user->role ?? 'guru'; @endphp
                                        <option value="guru" {{ $currentRole == 'guru' ? 'selected' : '' }}>Guru</option>
                                        <option value="kepala_sekolah" {{ $currentRole == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                                        <option value="waka" {{ $currentRole == 'waka' ? 'selected' : '' }}>Wakil Kepala</option>
                                        <option value="satpam" {{ $currentRole == 'satpam' ? 'selected' : '' }}>Satpam</option>
                                        <option value="staff" {{ $currentRole == 'staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="petugas" {{ $currentRole == 'petugas' ? 'selected' : '' }}>Petugas Presensi</option>
                                    </select>
                                </div>
                            </div>

                            <hr>

                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-id-card me-1"></i> Data Bio & Jabatan</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $teacher->full_name) }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">NIK</label>
                                    <input type="text" name="nik" class="form-control" value="{{ old('nik', $teacher->nik) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control" value="{{ old('nip', $teacher->nip) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="L" {{ $teacher->gender == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ $teacher->gender == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                    <select name="position_id" class="form-select" required>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ $teacher->position_id == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jadwal Kerja <span class="text-danger">*</span></label>
                                    <select name="work_schedule_id" class="form-select" required>
                                        @foreach($schedules as $schedule)
                                            <option value="{{ $schedule->id }}" {{ $teacher->work_schedule_id == $schedule->id ? 'selected' : '' }}>
                                                {{ $schedule->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telepon / WA</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $teacher->phone) }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning btn-sm fw-bold"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

<!-- Modal Tambah Pegawai -->
@if(in_array(auth()->user()->role, ['super_admin', 'admin']))
<div class="modal fade" id="createTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-user-plus me-2"></i>Tambah Pegawai Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teachers.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-lock me-1"></i> Informasi Akun Login</h6>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="contoh: pegawai@sekolah.sch.id">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Role Akses Sistem <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="kepala_sekolah" {{ old('role') == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                                <option value="waka" {{ old('role') == 'waka' ? 'selected' : '' }}>Wakil Kepala</option>
                                <option value="satpam" {{ old('role') == 'satpam' ? 'selected' : '' }}>Satpam</option>
                                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="petugas" {{ old('role') == 'petugas' ? 'selected' : '' }}>Petugas Presensi</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-id-card me-1"></i> Data Bio & Jabatan</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required placeholder="Nama dan gelar">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">NIK (Opsional)</label>
                            <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" placeholder="16 digit NIK">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">NIP (Opsional)</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip') }}" placeholder="NIP Pegawai">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <select name="position_id" class="form-select" required>
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jadwal Kerja <span class="text-danger">*</span></label>
                            <select name="work_schedule_id" class="form-select" required>
                                <option value="">-- Pilih Jadwal --</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ old('work_schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->name }} ({{ $schedule->check_in_time }} - {{ $schedule->check_out_time }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor Telepon / WA (Opsional)</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-save me-1"></i> Simpan Data & Generate QR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<!-- DataTables JS & Bootstrap 5 Extension -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- html2canvas untuk Ekspor Gambar HD -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    let currentTeacherName = '';

    $(document).ready(function() {
        // Init DataTables
        $('#teachersTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            "order": [],
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
        });

        // 1. Tangkap Klik Tombol Kartu Presensi
        $(document).on('click', '.btn-card', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const qrSvg = $('#qr-svg-' + id).html();

            currentTeacherName = name;

            $('#cardTeacherName').text(name).attr('title', name);
            $('#cardTeacherId').text('email : ' + email + '  |  id : ' + id);
            $('#cardQrContainer').html(qrSvg);
        });

        // 2. Tangkap Klik Tombol Download PNG dalam Modal
        $('#btnDownloadCard').on('click', function () {
            const cardElement = document.getElementById('modalCardElement');
            const slugName = currentTeacherName.toLowerCase().replace(/[^a-z0-9]/g, '_');

            html2canvas(cardElement, {
                scale: 4,               // Render 4x lipat resolusi tinggi
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
    });
</script>
@endpush