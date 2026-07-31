@extends('layouts.main')

@section('title', 'Data Guru & Pegawai')
@section('page-title', 'Data Guru & Pegawai')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Daftar Guru / Pegawai</h5>
        <div class="card-tools">
            <a href="{{ route('teachers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Tambah Guru Baru
            </a>
        </div>
        
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>Nama & NIP</th>
                        <th>Email</th>
                        <th>Jabatan</th>
                        <th>Jadwal Kerja</th>
                        <th class="text-center">QR Code</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $index => $teacher)
                        <tr>
                            <td class="text-center">{{ $teachers->firstItem() + $index }}</td>
                            <td>
                                <strong class="d-block">{{ $teacher->full_name }}</strong>
                                <small class="text-muted">NIP: {{ $teacher->nip ?? '-' }}</small>
                            </td>
                            <td>{{ $teacher->user->email ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $teacher->position->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <small class="fw-semibold">{{ $teacher->workSchedule->name ?? '-' }}</small>
                                <br>
                                <small class="text-muted">
                                    {{ $teacher->workSchedule->check_in_time ?? '' }} - {{ $teacher->workSchedule->check_out_time ?? '' }}
                                </small>
                            </td>
                            <td class="text-center">
                                @if($teacher->activeQrCode)
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#qrModal-{{ $teacher->id }}">
                                        <i class="fas fa-qrcode me-1"></i> Lihat QR
                                    </button>
                                @else
                                    <span class="badge bg-secondary">Belum ada QR</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('teachers.regenerate-qr', $teacher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin memperbarui QR Code ini? QR Code lama tidak akan berlaku lagi.');">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm" title="Regenerate QR Code">
                                        <i class="fas fa-sync-alt"></i> Reset QR
                                    </button>
                                </form>
                            </td>
                        </tr>

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

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle me-1"></i> Belum ada data guru terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($teachers->hasPages())
        <div class="card-footer bg-white">
            {{ $teachers->links() }}
        </div>
    @endif
</div>
@endsection