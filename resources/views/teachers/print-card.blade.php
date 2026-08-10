<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download ID Card Presensi - SMK Syafi'i Akrom</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- html2canvas untuk Ekspor Gambar HD -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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

        /* Top Action Toolbar */
        .toolbar {
            background: #ffffff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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
            font-size: 13px;
        }

        .btn-primary { background: #0d6efd; color: #fff; }
        .btn-success { background: #198754; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        /* Container Grid Kartu */
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }

        .card-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        /* Dimensi Kartu Tegak */
        .id-card {
            width: 58mm;
            height: 82mm;
            position: relative;
            /* Path disesuaikan ke folder public/img/ */
            background-image: url("{{ asset('img/format-kartu.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 4px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        /* Position Overlay 1: Banner Nama & ID */
        .identity-box {
            position: absolute;
            top: 31.2%;
            left: 0;
            width: 100%;
            height: 10.2%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #ffffff;
            padding: 0 5px;
        }

        .identity-box .name {
            font-size: 8.5pt;
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 95%;
        }

        .identity-box .id-number {
            font-size: 7.5pt;
            font-weight: 600;
            margin-top: 1px;
        }

        /* Position Overlay 2: Container QR Code */
        .qr-container {
            position: absolute;
            top: 43.1%;
            left: 20.2%;
            width: 59.6%;
            height: 42.1%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-container svg, .qr-container img {
            width: 88% !important;
            height: 88% !important;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <!-- Bar Aksi Atas -->
    <div class="toolbar">
        <div>
            <h3 style="margin-bottom: 2px; color: #212529;">Download Kartu Presensi</h3>
            <p style="color: #6c757d; font-size: 13px;">Total kartu: <strong>{{ count($teachers) }} Pegawai</strong></p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <!-- Tombol selalu ditampilkan tanpa kondisi if -->
            <button onclick="downloadAllCards()" class="btn btn-primary">
                <i class="fas fa-download"></i> Download Kartu
            </button>
        </div>
    </div>

    <!-- Daftar Kartu Presensi -->
    <div class="card-container">
        @foreach($teachers as $teacher)
            <div class="card-wrapper">
                <!-- Fisik Kartu ID Card -->
                <div class="id-card" id="card-{{ $teacher->id }}">
                    
                    <!-- Overlay Nama & ID/NIP -->
                    <div class="identity-box">
                        <div class="name" title="{{ $teacher->full_name }}">{{ $teacher->full_name }}</div>
                        <div class="id-number">id : {{ $teacher->id }}</div>
                    </div>

                    <!-- Overlay QR Code -->
                    <div class="qr-container">
                        @if($teacher->activeQrCode)
                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(0)->generate($teacher->activeQrCode->code) !!}
                        @else
                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(0)->generate($teacher->nip ?? $teacher->id) !!}
                        @endif
                    </div>

                </div>

                <!-- Tombol Download Satuan per Orang -->
                <button class="btn btn-success btn-sm" onclick="downloadSingleCard('card-{{ $teacher->id }}', 'Kartu_Presensi_{{ \Illuminate\Support\Str::slug($teacher->full_name) }}')">
                    <i class="fas fa-image"></i> Download PNG
                </button>
            </div>
        @endforeach
    </div>

    <script>
        function downloadSingleCard(elementId, fileName) {
            const cardElement = document.getElementById(elementId);

            html2canvas(cardElement, {
                scale: 4,
                useCORS: true,
                backgroundColor: null,
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = fileName + '.png';
                link.href = canvas.toDataURL('image/png', 1.0);
                link.click();
            });
        }

        async function downloadAllCards() {
            const wrappers = document.querySelectorAll('.card-wrapper');
            
            for (let index = 0; index < wrappers.length; index++) {
                const card = wrappers[index].querySelector('.id-card');
                const cardId = card.getAttribute('id');
                const nameText = card.querySelector('.name').innerText.trim().replace(/[^a-zA-Z0-9]/g, '_');
                
                downloadSingleCard(cardId, 'Kartu_Presensi_' + nameText);
                
                await new Promise(resolve => setTimeout(resolve, 400));
            }
        }
    </script>
</body>
</html>