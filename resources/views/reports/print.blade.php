<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Pegawai - SMK UP RPL CodePelita</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #333; margin: 20px; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-surat h2 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .kop-surat h3 { margin: 2px 0; font-size: 13pt; }
        .kop-surat p { margin: 0; font-size: 9pt; color: #555; }
        
        .info-laporan { margin-bottom: 15px; }
        .info-laporan table { width: 100%; border: none; }
        .info-laporan td { padding: 3px 0; font-size: 10pt; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #444; padding: 6px 8px; font-size: 9.5pt; }
        table.data-table th { background-color: #f2f2f2; text-align: center; }

        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .badge-success { color: green; font-weight: bold; }
        .badge-warning { color: #d35400; font-weight: bold; }

        .ttd-section { margin-top: 40px; float: right; width: 250px; text-align: center; font-size: 10pt; }

        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Tombol Kembali/Print -->
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Cetak Dokumen
        </button>
    </div>

    <!-- Kop Surat Sekolah -->
    <div class="kop-surat">
        <h2>{{ $school->name ?? 'SMK UP RPL CODEPELITA' }}</h2>
        <h3>LAPORAN REKAPITULASI PRESENSI PEGAWAI</h3>
        <p>{{ $school->address ?? 'Jl. Pendidikan No. 1, Kota Pekalongan' }}</p>
    </div>

    <!-- Informasi Filter -->
    <div class="info-laporan">
        <table>
            <tr>
                <td style="width: 120px;"><strong>Periode</strong></td>
                <td>: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</td>
            </tr>
            @if($selectedTeacher)
            <tr>
                <td><strong>Nama Pegawai</strong></td>
                <td>: {{ $selectedTeacher->full_name }} (NIP: {{ $selectedTeacher->nip ?? '-' }})</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Tabel Data Presensi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 90px;">Tanggal</th>
                <th style="width: 100px;">NIP</th>
                <th>Nama Pegawai</th>
                <th style="width: 80px;">Jam Masuk</th>
                <th style="width: 80px;">Jam Pulang</th>
                <th style="width: 90px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d/m/Y') }}</td>
                    <td class="text-center">{{ $row->teacher->nip ?? '-' }}</td>
                    <td>{{ $row->teacher->full_name }}</td>
                    <td class="text-center">{{ $row->check_in_time ?? '-' }}</td>
                    <td class="text-center">{{ $row->check_out_time ?? '-' }}</td>
                    <td class="text-center">
                        @if($row->status === 'present')
                            <span class="badge-success">Tepat Waktu</span>
                        @else
                            <span class="badge-warning">Terlambat</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data presensi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bagian Tanda Tangan -->
    <div class="ttd-section">
        <p>Pekalongan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p>Kepala Sekolah / Admin Presensi,</p>
        <br><br><br>
        <p class="text-bold">__________________________</p>
    </div>

</body>
</html>