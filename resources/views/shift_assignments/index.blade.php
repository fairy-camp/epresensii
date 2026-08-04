@extends('layouts.main')

@section('title', 'Penugasan Shift Guru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Form Set Shift Permanen -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header"><h3 class="card-title">Set Shift Guru</h3></div>
                <form action="{{ route('shift-assignments.storeBulk') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Pilih Jadwal Kerja</label>
                            <select name="work_schedule_id" class="form-control" required>
                                <option value="">-- Pilih Shift --</option>
                                @foreach($schedules as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->check_in_time }} - {{ $s->check_out_time }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Pilih Guru</label>
                            <select name="teacher_ids[]" class="form-control" multiple style="height: 180px;" required>
                                <option value="all" selected>-- SEMUA GURU AKTIF --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->nip }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Tekan Ctrl untuk memilih beberapa guru spesifik.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info w-100"><i class="fas fa-save me-1"></i> Simpan Shift Permanen</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Penugasan Shift -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Penugasan Shift Guru (Permanen)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>NIP</th>
                                <th>Nama Guru</th>
                                <th>Jadwal Shift</th>
                                <th style="width: 80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $item)
                            <tr>
                                <td>{{ $item->teacher->nip ?? '-' }}</td>
                                <td>{{ $item->teacher->full_name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $item->workSchedule->name ?? 'N/A' }} 
                                        ({{ $item->workSchedule->check_in_time ?? '' }} - {{ $item->workSchedule->check_out_time ?? '' }})
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('shift-assignments.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus penugasan shift guru ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada penugasan shift yang diatur. Silakan atur melalui form di samping.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection