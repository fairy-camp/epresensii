@extends('layouts.main')

@section('title', 'Penugasan Shift')
@section('page-title', 'Penugasan Shift')

@push('styles')
<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Form Set Shift Permanen -->
        <div class="col-md-4 mb-3">
            <div class="card card-info shadow-sm">
                <div class="card-header"><h3 class="card-title fw-bold">Set Shift Guru</h3></div>
                <form action="{{ route('shift-assignments.storeBulk') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Pilih Jadwal Kerja</label>
                            <select name="work_schedule_id" class="form-control" required>
                                <option value="">-- Pilih Shift --</option>
                                @foreach($schedules as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->check_in_time }} - {{ $s->check_out_time }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-semibold">Pilih Guru</label>
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
                        <button type="submit" class="btn btn-info w-100 fw-bold"><i class="fas fa-save me-1"></i> Simpan Shift Permanen</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Penugasan Shift -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title fw-bold">Daftar Penugasan Shift Guru (Permanen)</h3>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="shiftAssignmentsTable" class="table table-hover align-middle mb-0 w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>NIP</th>
                                    <th>Nama Guru</th>
                                    <th>Jabatan & Role</th>
                                    <th>Jadwal Shift</th>
                                    <th class="text-center" style="width: 80px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignments as $item)
                                <tr>
                                    <td><small class="fw-semibold text-muted">{{ $item->teacher->nip ?? '-' }}</small></td>
                                    <td><strong>{{ $item->teacher->full_name ?? '-' }}</strong></td>
                                    <td>
                                        <span class="badge bg-info text-dark mb-1">
                                            {{ $item->teacher->position->name ?? '-' }}
                                        </span>
                                        <br>
                                        <small class="badge bg-secondary">
                                            Role: {{ strtoupper(str_replace('_', ' ', $item->teacher->user->role ?? 'guru')) }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $item->workSchedule->name ?? 'N/A' }} 
                                            ({{ $item->workSchedule->check_in_time ?? '' }} - {{ $item->workSchedule->check_out_time ?? '' }})
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('shift-assignments.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus penugasan shift guru ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" title="Hapus Penugasan"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS & Extension Bootstrap 5 -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#shiftAssignmentsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            "order": [],
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
        });
    });
</script>
@endpush