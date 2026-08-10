@extends('layouts.main')

@section('title', 'Master Jadwal Kerja')

@section('content_header')
    <h1>Master Jadwal Kerja</h1>
@endsection

@section('content')
<div class="row">
    <!-- Form Tambah -->
    <div class="col-md-4">
        <div class="card card-primary shadow-sm">
            <div class="card-header"><h3 class="card-title fw-bold">Tambah Jam Kerja</h3></div>
            <form action="{{ route('work-schedules.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label class="fw-semibold">Nama Shift / Jadwal <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Reguler Pagi" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-semibold">Jam Masuk <span class="text-danger">*</span></label>
                        <input type="time" name="check_in_time" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-semibold">Jam Pulang <span class="text-danger">*</span></label>
                        <input type="time" name="check_out_time" class="form-control" required>
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
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header"><h3 class="card-title fw-bold">Daftar Jam Kerja</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Shift</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th class="text-center" style="width: 120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td><span class="badge bg-success">{{ date('H:i', strtotime($item->check_in_time)) }}</span></td>
                            <td><span class="badge bg-danger">{{ date('H:i', strtotime($item->check_out_time)) }}</span></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <!-- Tombol Pemicu Modal Edit -->
                                    <button type="button" 
                                            class="btn btn-warning btn-sm me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal-{{ $item->id }}" 
                                            title="Edit Jam Kerja">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Tombol Hapus -->
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

<!-- ========================================== -->
<!-- MODAL EDIT JAM KERJA                       -->
<!-- ========================================== -->
@foreach($schedules as $item)
<div class="modal fade" id="editModal-{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Nama Shift / Jadwal <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Jam Masuk <span class="text-danger">*</span></label>
                        <input type="time" name="check_in_time" class="form-control" value="{{ old('check_in_time', date('H:i', strtotime($item->check_in_time))) }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Jam Pulang <span class="text-danger">*</span></label>
                        <input type="time" name="check_out_time" class="form-control" value="{{ old('check_out_time', date('H:i', strtotime($item->check_out_time))) }}" required>
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