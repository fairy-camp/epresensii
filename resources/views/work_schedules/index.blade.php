@extends('layouts.main')

@section('title', 'Jam Kerja & Shift')
@section('page-title', 'Jam Kerja & Shift')

@section('content_header')
    <h1>Master Jadwal Kerja</h1>
@endsection

@section('content')
<div class="row">
    <!-- Form Tambah -->
    <div class="col-md-5">
        <div class="card card-primary shadow-sm">
            <div class="card-header"><h3 class="card-title fw-bold">Tambah Jam Kerja</h3></div>
            <form action="{{ route('work-schedules.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label class="fw-semibold">Nama Shift / Jadwal <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Reguler Pagi" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-semibold">Tipe Jadwal <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed (Tetap)</option>
                            <option value="shift" {{ old('type') == 'shift' ? 'selected' : '' }}>Shift</option>
                        </select>
                    </div>

                    <p class="fw-bold text-primary mb-2"><i class="fas fa-sign-in-alt me-1"></i> Jendela Presensi Masuk</p>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label class="small text-muted">Buka</label>
                            <input type="time" name="start_check_in_time" class="form-control form-control-sm" value="{{ old('start_check_in_time', '06:30') }}" required>
                        </div>
                        <div class="col-4">
                            <label class="small text-muted">Target</label>
                            <input type="time" name="check_in_time" class="form-control form-control-sm" value="{{ old('check_in_time', '07:00') }}" required>
                        </div>
                        <div class="col-4">
                            <label class="small text-muted">Tutup</label>
                            <input type="time" name="end_check_in_time" class="form-control form-control-sm" value="{{ old('end_check_in_time', '07:00') }}" required>
                        </div>
                    </div>

                    <p class="fw-bold text-danger mb-2"><i class="fas fa-sign-out-alt me-1"></i> Jendela Presensi Pulang</p>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label class="small text-muted">Buka</label>
                            <input type="time" name="start_check_out_time" class="form-control form-control-sm" value="{{ old('start_check_out_time', '15:00') }}" required>
                        </div>
                        <div class="col-4">
                            <label class="small text-muted">Standard</label>
                            <input type="time" name="check_out_time" class="form-control form-control-sm" value="{{ old('check_out_time', '15:00') }}" required>
                        </div>
                        <div class="col-4">
                            <label class="small text-muted">Tutup</label>
                            <input type="time" name="end_check_out_time" class="form-control form-control-sm" value="{{ old('end_check_out_time', '17:00') }}" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="fas fa-plus me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header"><h3 class="card-title fw-bold">Daftar Jam Kerja</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Shift</th>
                                <th>Jendela Masuk</th>
                                <th>Jendela Pulang</th>
                                <th class="text-center" style="width: 100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->name }}</strong><br>
                                    <span class="badge bg-secondary">{{ strtoupper($item->type) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ date('H:i', strtotime($item->check_in_time)) }}</span>
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-clock text-info me-1"></i>{{ date('H:i', strtotime($item->start_check_in_time)) }} - {{ date('H:i', strtotime($item->end_check_in_time)) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-danger">{{ date('H:i', strtotime($item->check_out_time)) }}</span>
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-clock text-warning me-1"></i>{{ date('H:i', strtotime($item->start_check_out_time)) }} - {{ date('H:i', strtotime($item->end_check_out_time)) }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-warning btn-sm me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal-{{ $item->id }}" 
                                                title="Edit Jam Kerja">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form action="{{ route('work-schedules.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Jadwal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3">Belum ada data jadwal kerja.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT JAM KERJA -->
@foreach($schedules as $item)
<div class="modal fade" id="editModal-{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title fw-bold text-dark" id="editModalLabel-{{ $item->id }}">
                    <i class="fas fa-edit me-2 text-warning"></i>Edit Jam Kerja: {{ $item->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('work-schedules.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body text-start py-3">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Shift / Jadwal <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipe Jadwal <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="fixed" {{ old('type', $item->type) == 'fixed' ? 'selected' : '' }}>Fixed (Tetap)</option>
                                <option value="shift" {{ old('type', $item->type) == 'shift' ? 'selected' : '' }}>Shift</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <p class="fw-bold text-primary mb-2"><i class="fas fa-sign-in-alt me-1"></i> Jendela Presensi Masuk</p>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Buka Absen Masuk</label>
                            <input type="time" name="start_check_in_time" class="form-control" value="{{ old('start_check_in_time', date('H:i', strtotime($item->start_check_in_time))) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Target Jam Masuk</label>
                            <input type="time" name="check_in_time" class="form-control" value="{{ old('check_in_time', date('H:i', strtotime($item->check_in_time))) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tutup Absen Masuk</label>
                            <input type="time" name="end_check_in_time" class="form-control" value="{{ old('end_check_in_time', date('H:i', strtotime($item->end_check_in_time))) }}" required>
                        </div>
                    </div>

                    <hr>
                    <p class="fw-bold text-danger mb-2"><i class="fas fa-sign-out-alt me-1"></i> Jendela Presensi Pulang</p>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Buka Absen Pulang</label>
                            <input type="time" name="start_check_out_time" class="form-control" value="{{ old('start_check_out_time', date('H:i', strtotime($item->start_check_out_time))) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jam Pulang Standard</label>
                            <input type="time" name="check_out_time" class="form-control" value="{{ old('check_out_time', date('H:i', strtotime($item->check_out_time))) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tutup Absen Pulang</label>
                            <input type="time" name="end_check_out_time" class="form-control" value="{{ old('end_check_out_time', date('H:i', strtotime($item->end_check_out_time))) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection