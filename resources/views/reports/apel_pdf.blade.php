<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Apel Pagi {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</title>
    <style>
        @page {
            size: 330mm 215.9mm; /* F4 Landscape */
            margin: 8mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .kop {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .kop h2 { margin: 0; font-size: 12pt; text-transform: uppercase; }
        .kop h3 { margin: 2px 0; font-size: 10pt; }
        .kop p { margin: 0; font-size: 7pt; color: #444; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #444;
            padding: 3px 2px;
            text-align: center;
        }
        table.data-table th {
            background-color: #fed7aa;
            font-weight: bold;
        }
        .bg-sunday { background-color: #cbd5e1; font-weight: bold; }
        .bg-late { background-color: #fef08a; font-weight: bold; }
        .text-absent { color: #dc2626; font-weight: bold; }
        .text-start { text-align: left !important; padding-left: 4px !important; }
        .ttd-container {
            margin-top: 15px;
            float: right;
            width: 200px;
            text-align: center;
            font-size: 8pt;
        }
    </style>
</head>
<body>

    <div class="kop">
        <h2>{{ $school->school_name ?? 'SMK SYAFI\'I AKROM' }}</h2>
        <h3>LAPORAN REKAPITULASI PRESENSI APEL PAGI GURU DAN KARYAWAN</h3>
        <p>Periode: {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">NO</th>
                <th rowspan="2" style="width: 140px;" class="text-start">NAMA GURU / PEGAWAI</th>
                <th colspan="{{ count($days) }}">TANGGAL APEL PAGI</th>
                <th rowspan="2" style="width: 35px;">TOTAL</th>
            </tr>
            <tr>
                @foreach($days as $dayInfo)
                    <th class="{{ $dayInfo['is_sunday'] ? 'bg-sunday' : '' }}" style="width: 22px;">{{ $dayInfo['day'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $index => $teacher)
                @php $totalApel = 0; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-start">{{ $teacher->full_name }}</td>
                    @foreach($days as $d => $dayInfo)
                        @php
                            $rec = $matrix[$teacher->id][$d] ?? null;
                            $displayText = '';
                            $cellClass = '';

                            if ($dayInfo['is_sunday']) {
                                $displayText = '-';
                                $cellClass = 'bg-sunday';
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
                        <td class="{{ $cellClass }}">{{ $displayText }}</td>
                    @endforeach
                    <td style="font-weight: bold;">{{ $totalApel }}</td>
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