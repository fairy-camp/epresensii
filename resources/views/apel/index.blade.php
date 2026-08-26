@extends('layouts.main')

@section('title', 'Data Presensi Apel Pagi')
@section('page-title', 'Data Presensi Apel Pagi')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1><i class="fas fa-flag text-warning mr-2"></i>Data Presensi Apel Pagi</h1>
        @if(auth()->user()->role === 'petugas')
            <a href="{{ route('apel.scan') }}" class="btn btn-warning btn-sm font-weight-bold">
                <i class="fas fa-qrcode mr-1"></i> Buka Mode Scanner Apel
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Filter Tanggal -->
    <div class="card card-outline card-warning mb-3 shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Pilih Tanggal Presensi</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('apel.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4 col-sm-6">
                    <label for="date" class="form-label font-weight-bold">Tanggal:</label>
                    <input type="date" id="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('apel.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-sync-alt mr-1"></i> Hari Ini
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTables Card -->
    <div class="card shadow-sm">
        <div class="card-header border-transparent">
            <h3 class="card-title">Daftar Kehadiran Apel Tanggal: <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</strong></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="apelTable" class="table table-bordered table-striped align-middle w-100">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>NIP</th>
                            <th>Nama Guru / Pegawai</th>
                            <th>Jam Presensi</th>
                            <th>Status</th>
                            @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                                <th style="width: 60px;" class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $row->teacher->nip ?? '-' }}</code></td>
                                <td class="font-weight-bold">{{ $row->teacher->name ?? $row->teacher->full_name ?? 'Pegawai / Tamu' }}</td>
                                <td>
                                    <span class="badge badge-light border">
                                        <i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($row->scan_time)->format('H:i:s') }} WIB
                                    </span>
                                </td>
                                <td>
                                    @if($row->status === 'present')
                                        <span class="badge bg-success"><i class="fas fa-check mr-1"></i> Tepat Waktu</span>
                                    @elseif($row->status === 'late')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i> Terlambat</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times mr-1"></i> Absen</span>
                                    @endif
                                </td>
                                @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                                    <td class="text-center">
                                        <form action="{{ route('apel.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus record ini?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Hapus Data">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- jQuery & DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function() {
        $('#apelTable').DataTable({
            "responsive": true,
            "autoWidth": false,
            "order": [[ 3, "desc" ]],
            "language": {
                "sEmptyTable":   "Tidak ada data presensi apel pagi pada tanggal ini.",
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ entri",
                "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
                "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "sSearch":       "Cari:",
                "oPaginate": {
                    "sFirst":    "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext":     "Selanjutnya",
                    "sLast":     "Terakhir"
                }
            }
        });
    });
</script>
@endpush