<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Apel Pagi {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</title>
    <style>
        @page {
            size: 330mm 215.9mm; /* F4 Landscape */
            margin: 5mm 5mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .kop {
            text-align: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }
        .kop h2 { margin: 0; font-size: 11pt; text-transform: uppercase; }
        .kop h3 { margin: 1px 0; font-size: 8.5pt; }
        .kop p { margin: 0; font-size: 7pt; color: #444; }

        table.data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        table.data-table th, table.data-table td {
            border: 0.5px solid #333;
            padding: 3px 0px;
            text-align: center;
            font-size: 6pt;
            line-height: 1;
            word-wrap: break-word;
        }
        table.data-table th {
            background-color: #fed7aa;
            font-weight: bold;
            font-size: 5.8pt;
        }
        
        /* Pengaturan Lebar Kolom Presisi */
        .col-no { width: 2%; }
        .col-nama { 
            width: 11%; 
            text-align: left !important; 
            padding-left: 2px !important; 
            white-space: nowrap;
            overflow: hidden;
            font-size: 6pt;
        }
        .col-tgl { width: 2.3%; }
        .col-total { width: 2.3%; font-weight: bold; font-size: 6pt; }

        .bg-sunday { background-color: #cbd5e1; font-weight: bold; }
        .bg-late { background-color: #fef08a; font-weight: bold; }
        .text-absent { color: #dc2626; font-weight: bold; }
        
        .ttd-container {
            margin-top: 8px;
            float: right;
            width: 180px;
            text-align: center;
            font-size: 7.5pt;
        }
    </style>
</head>
<body>

    <div class="kop">
        <h2>{{ $school->school_name ?? 'SMK SYAFI\'I AKROM' }}</h2>
        <h3>LAPORAN REKAPITULASI PRESENSI APEL PAGI GURU DAN KARYAWAN</h3>
        <p>Periode: {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }} (Pelaksanaan Apel: Senin & Kamis)</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2" class="col-nama">NAMA GURU / PEGAWAI</th>
                <th colspan="{{ count($days) }}">TANGGAL APEL PAGI</th>
                <th rowspan="2" class="col-total">TOT</th>
            </tr>
            <tr>
                @foreach($days as $dayInfo)
                    <th class="col-tgl {{ $dayInfo['is_sunday'] ? 'bg-sunday' : '' }}">{{ $dayInfo['day'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $index => $teacher)
                @php $totalApel = 0; @endphp
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-nama">{{ $teacher->full_name }}</td>
                    @foreach($days as $d => $dayInfo)
                        @php
                            $rec = $matrix[$teacher->id][$d] ?? null;
                            $displayText = '';
                            $cellClass = '';

                            if ($dayInfo['is_sunday']) {
                                $displayText = '-';
                                $cellClass = 'bg-sunday';
                            } elseif (!$dayInfo['is_apel_day']) {
                                $displayText = '-';
                                $cellClass = '';
                            } else {
                                $todayDate = \Carbon\Carbon::today()->toDateString();
                                if ($dayInfo['date'] > $todayDate) {
                                    $displayText = '';
                                } elseif (!$rec) {
                                    $displayText = 'A';
                                    $cellClass = 'text-absent';
                                } else {
                                    if ($rec->status === 'absent') {
                                        $displayText = 'A';
                                        $cellClass = 'text-absent';
                                    } else {
                                        $displayText = \Carbon\Carbon::parse($rec->scan_time)->format('H:i');
                                        $totalApel++;

                                        if ($rec->status === 'late') {
                                            $cellClass = 'bg-late';
                                        }
                                    }
                                }
                            }
                        @endphp
                        <td class="col-tgl {{ $cellClass }}">{{ $displayText }}</td>
                    @endforeach
                    <td class="col-total">{{ $totalApel }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd-container">
        <p>Pekalongan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p>Kepala Sekolah / Admin,</p>
        <br><br><br>
        <p><strong>__________________________</strong></p>
    </div>

</body>
</html>