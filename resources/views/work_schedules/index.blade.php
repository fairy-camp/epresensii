@extends('layouts.main')

@section('title', 'Master Jadwal Kerja')

@section('content_header')
    <h1>Master Jadwal Kerja</h1>
@endsection

@section('content')
<div class="row">
    <!-- Form Tambah -->
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Tambah Jam Kerja</h3></div>
            <form action="{{ route('work-schedules.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label>Nama Shift / Jadwal</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Reguler Pagi" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Jam Masuk</label>
                        <input type="time" name="check_in_time" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Jam Pulang</label>
                        <input type="time" name="check_out_time" class="form-control" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Daftar Jam Kerja</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Shift</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th style="width: 100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td><span class="badge bg-success">{{ $item->check_in_time }}</span></td>
                            <td><span class="badge bg-danger">{{ $item->check_out_time }}</span></td>
                            <td>
                                <form action="{{ route('work-schedules.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
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
@endsection