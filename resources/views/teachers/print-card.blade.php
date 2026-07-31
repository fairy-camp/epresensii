<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak ID Card Guru - SMK UP RPL CodePelita</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            padding: 20px;
        }

        /* Toolbar Top Action */
        .no-print {
            background: #ffffff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary { background: #007bff; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }

        /* Container Kartu Grid */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, 86mm);
            gap: 15mm;
            justify-content: center;
        }

        /* Desain Fisik Kartu (Ukuran CR80: 86mm x 54mm) */
        .id-card {
            width: 86mm;
            height: 54mm;
            background: #ffffff;
            border-radius: 6mm;
            border: 1px solid #dcdcdc;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        /* Header Kartu */
        .card-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: #ffffff;
            padding: 5px 10px;
            text-align: center;
            border-bottom: 2px solid #ffc107;
        }

        .card-header h2 {
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .card-header p {
            font-size: 6.5pt;
            opacity: 0.9;
        }

        /* Body Kartu */
        .card-body {
            display: flex;
            padding: 6px 10px;
            align-items: center;
            gap: 8px;
            flex-grow: 1;
        }

        .avatar-box {
            width: 22mm;
            height: 28mm;
            border-radius: 4px;
            border: 1px solid #ced4da;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .avatar-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .teacher-info {
            flex-grow: 1;
        }

        .teacher-info .name {
            font-size: 8.5pt;
            font-weight: 700;
            color: #212529;
            margin-bottom: 2px;
            line-height: 1.1;
        }

        .teacher-info .nip {
            font-size: 7.5pt;
            color: #495057;
            margin-bottom: 3px;
        }

        .teacher-info .position-badge {
            display: inline-block;
            font-size: 6pt;
            background: #e7f1ff;
            color: #0d6efd;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }

        /* Container QR Code */
        .qr-box {
            width: 20mm;
            height: 20mm;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #fff;
            padding: 2px;
            border: 1px dashed #adb5bd;
            border-radius: 4px;
        }

        .qr-box svg, .qr-box img {
            max-width: 100%;
            max-height: 100%;
        }

        /* Footer Kartu */
        .card-footer {
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 3px 10px;
            font-size: 5.5pt;
            color: #6c757d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* CSS KHUSUS PRINT */
        @media print {
            body {
                background: none;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .card-container {
                display: grid;
                grid-template-columns: repeat(2, 86mm);
                gap: 10mm 8mm;
                margin: 0 auto;
            }

            .id-card {
                box-shadow: none;
                border: 1px solid #000; /* Border lebih tegas saat diprint */
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Panel Tombol Aksi -->
    <div class="no-print">
        <div>
            <h3 style="margin-bottom: 4px;">Cetak ID Card Presensi</h3>
            <p style="color: #6c757d; font-size: 14px;">Total kartu: <strong>{{ count($teachers) }} kartu</strong></p>
        </div>
        <div>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Grid Kartu ID Card -->
    <div class="card-container">
        @foreach($teachers as $teacher)
            <div class="id-card">
                <!-- Header -->
                <div class="card-header">
                    <h2>KARTU PRESENSI PEGAWAI</h2>
                    <p>SMK UP RPL CODEPELITA</p>
                </div>

                <!-- Body Info -->
                <div class="card-body">
                    <!-- Foto / Avatar Default -->
                    <div class="avatar-box">
                        <i class="fas fa-user fa-2x text-secondary"></i>
                    </div>

                    <!-- Informasi Bio -->
                    <div class="teacher-info">
                        <div class="name">{{ $teacher->full_name }}</div>
                        <div class="nip">NIP: {{ $teacher->nip ?? '-' }}</div>
                        <div class="position-badge">
                            {{ $teacher->position->name ?? 'Pegawai' }}
                        </div>
                    </div>

                    <!-- Scan QR Code -->
                    <div class="qr-box">
                        @if($teacher->activeQrCode)
                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->margin(0)->generate($teacher->activeQrCode->code) !!}
                        @else
                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->margin(0)->generate($teacher->nip ?? $teacher->id) !!}
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer">
                    <span>E-Presensi System</span>
                    <span>Valid QR Code</span>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>