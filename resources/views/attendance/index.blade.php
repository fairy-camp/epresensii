@extends('layouts.main')

@section('title', 'Kelola Data Presensi')
@section('page-title', 'Kelola Data Presensi')

@push('styles')
<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-clipboard-list me-2 text-primary"></i>Kelola Data Presensi</h5>
        
        <!-- Filter Tanggal -->
        <form action="{{ route('attendances.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 fw-semibold text-nowrap">Tanggal:</label>
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
        </form>
    </div>

    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="attendancesTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>Nama Pegawai</th>
                        <th class="text-center">Jam Masuk</th>
                        <th class="text-center">Jam Keluar</th>
                        <th class="text-center">Status</th>
                        <th>Catatan / Keterangan</th>
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <th class="text-center" width="120">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $attendance)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <strong class="d-block">{{ $attendance->teacher->full_name ?? '-' }}</strong>
                                <small class="text-muted">NIP: {{ $attendance->teacher->nip ?? '-' }}</small>
                            </td>
                            <td class="text-center fw-semibold text-success">
                                {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}
                            </td>
                            <td class="text-center fw-semibold text-danger">
                                {{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                @switch($attendance->status)
                                    @case('present')
                                        <span class="badge bg-success">Hadir</span>
                                        @break
                                    @case('late')
                                        <span class="badge bg-warning text-dark">Terlambat</span>
                                        @break
                                    @case('permission')
                                        <span class="badge bg-info text-dark">Izin</span>
                                        @break
                                    @case('sick')
                                        <span class="badge bg-secondary">Sakit</span>
                                        @break
                                    @default
                                        <span class="badge bg-danger">Alfa</span>
                                @endswitch
                            </td>
                            <td>{{ $attendance->notes ?? '-' }}</td>

                            @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editAttendanceModal-{{ $attendance->id }}" title="Edit Presensi">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="{{ route('attendances.destroy', $attendance->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data presensi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Presensi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Edit Presensi -->
                                <div class="modal fade text-start" id="editAttendanceModal-{{ $attendance->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning bg-opacity-10">
                                                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-warning"></i>Edit Presensi: {{ $attendance->teacher->full_name ?? '' }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('attendances.update', $attendance->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body py-3">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Status Kehadiran <span class="text-danger">*</span></label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="present" {{ $attendance->status == 'present' ? 'selected' : '' }}>Hadir</option>
                                                            <option value="late" {{ $attendance->status == 'late' ? 'selected' : '' }}>Terlambat</option>
                                                            <option value="permission" {{ $attendance->status == 'permission' ? 'selected' : '' }}>Izin</option>
                                                            <option value="sick" {{ $attendance->status == 'sick' ? 'selected' : '' }}>Sakit</option>
                                                            <option value="absent" {{ $attendance->status == 'absent' ? 'selected' : '' }}>Alfa / Tanpa Keterangan</option>
                                                        </select>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Jam Masuk</label>
                                                            <input type="time" name="check_in_time" class="form-control" value="{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '' }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Jam Keluar</label>
                                                            <input type="time" name="check_out_time" class="form-control" value="{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '' }}">
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Catatan / Alasan</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan opsional...">{{ $attendance->notes }}</textarea>
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
                            </td>
                            @endif
                        </tr>
                    @empty
                        {{-- Biarkan kosong, DataTables yang akan menampilkan pesan "Tidak ada data" secara otomatis --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS & Bootstrap 5 Extension -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#attendancesTable').DataTable({
            // Bahasa Indonesia
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            // Mempertahankan urutan terbaru dari controller (latest check_in_time)
            "order": [],
            // Konfigurasi Halaman
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
        });
    });
</script>
@endpush