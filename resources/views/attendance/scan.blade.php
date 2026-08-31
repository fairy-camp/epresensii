<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>E-Presensi SMKSA</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}?v=1">
    <link rel="shortcut icon" type="image/png" href="{{ asset('img/logo.png') }}?v=1">

    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- HTML5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html, body {
            height: 100%;
            height: 100dvh;
            max-height: 100dvh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #f8fafc;
            color: #0f172a;
            display: flex;
            flex-direction: column;
        }

        /* App Bar Header (Light Mode) */
        .mobile-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 14px;
            flex-shrink: 0;
            z-index: 1000;
        }

        .header-logo-box {
            width: 40px;
            height: 40px;
            min-width: 36px;
            border-radius: 10px;
            overflow: hidden;
        }

        /* Main App Body Container */
        .app-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 10px 12px;
            overflow: hidden;
        }

        /* Digital Clock Banner (Ramping & Kompak) */
        .clock-card {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 8px;
            padding: 3px 8px;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);
            flex-shrink: 0;
        }

        /* Camera Viewfinder Box */
        .scanner-container {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid #cbd5e1;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
            background-color: #e2e8f0 !important;
        }

        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 12px;
        }

        /* Bottom Floating Nav */
        .bottom-card {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 8px 12px;
            flex-shrink: 0;
            text-align: center;
        }

        /* Button Customization */
        .btn-camera-toggle {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 500;
            z-index: 10;
        }

        .btn-camera-toggle:hover, .btn-camera-toggle:focus {
            background: #ffffff;
            color: #2563eb;
        }

        .nav-pills {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 3px;
        }

        .nav-pills .nav-link {
            border-radius: 8px;
            color: #64748b;
            font-weight: 500;
            font-size: 12px;
            padding: 6px 12px;
        }

        .nav-pills .nav-link.active {
            background-color: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        }

        /* POPUP NOTIFICATION OVERLAY */
        .scan-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.35);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 20px;
        }

        header a {
            text-decoration: none;
        }

        /* Styling Soft Placeholder Input NIP */
        #manual_code::placeholder {
            font-size: 13px;
            font-weight: 400;
            color: #94a3b8;
            opacity: 1;
        }

        .scan-popup-card {
            width: 100%;
            max-width: 320px;
            border-radius: 20px;
            padding: 20px 16px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: popupScale 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popupScale {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- Header Mobile App (Light Mode) -->
    <header class="mobile-header d-flex align-items-center justify-content-between shadow-sm">
        <a href="{{ route('attendance.scan') }}">
            <div class="d-flex align-items-center gap-2">
                <div class="header-logo-box d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Sekolah" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="d-flex flex-column justify-content-center">
                    <h6 class="mb-0 fw-bold text-dark fs-6 lh-1">E-Presensi</h6>
                    <small class="text-secondary" style="font-size: 11px; margin-top: 2px;">SMK Syafi'i Akrom</small>
                </div>
            </div>
        </a>
        
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-0 d-flex align-items-center justify-content-center" title="Logout" style="width: 32px; height: 32px;">
                <i class="fas fa-power-off fs-6"></i>
            </button>
        </form>
    </header>

    <!-- Tombol Pengalih Mode Presensi (Harian vs Apel) -->
    <div class="px-3 pt-2 bg-white border-bottom">
        <div class="btn-group w-100 shadow-sm mb-2" role="group">
            <a href="{{ route('attendance.scan') }}" class="btn btn-sm btn-primary active fw-bold py-1 w-50">
                <i class="fas fa-clock me-1"></i> Presensi Harian
            </a>
            <a href="{{ route('apel.scan') }}" class="btn btn-sm btn-outline-warning text-dark fw-bold py-1 w-50">
                <i class="fas fa-flag me-1"></i> Apel Pagi
            </a>
        </div>
    </div>

    <!-- Content Area (Fit 1 Screen Height) -->
    <main class="app-body">
        <!-- Jam Digital & Tanggal Banner -->
        <div class="clock-card text-center">
            <div class="d-flex align-items-center justify-content-center gap-2" style="font-size: 12px;">
                <span id="realtime-clock" class="fw-bold text-white">00:00:00</span>
                <span class="text-white-50">|</span>
                <span id="realtime-date" class="text-white-50 fw-medium">Senin, 1 Jan 2026</span>
            </div>
        </div>

        <!-- Status GPS Badge -->
        <div id="gps-status" class="alert alert-light border text-secondary text-center py-1 px-2 mb-0 rounded-3 small flex-shrink-0 shadow-sm" style="font-size: 11px;">
            <i class="fas fa-spinner fa-spin me-1 text-primary"></i> Mendapatkan koordinat GPS...
        </div>

        <!-- Tab Navigasi: Scanner vs Manual -->
        <ul class="nav nav-pills nav-justified p-1 rounded-3 mb-0 flex-shrink-0" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-camera-tab" data-bs-toggle="pill" data-bs-target="#pills-camera" type="button" role="tab">
                    <i class="fas fa-camera me-1"></i> Scan Kamera
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-manual-tab" data-bs-toggle="pill" data-bs-target="#pills-manual" type="button" role="tab">
                    <i class="fas fa-keyboard me-1"></i> Input NIP
                </button>
            </li>
        </ul>

        <!-- Tab Content Flex Area -->
        <div class="tab-content flex-grow-1 position-relative" id="pills-tabContent" style="min-height: 0;">
            <!-- TAB 1: Kamera Scanner -->
            <div class="tab-pane fade show active h-100 position-relative" id="pills-camera" role="tabpanel">
                <div class="scanner-container">
                    <!-- Switch Camera Button -->
                    <button type="button" id="btnSwitchCamera" class="btn btn-camera-toggle shadow-sm position-absolute top-0 end-0 m-2">
                        <i class="fas fa-sync-alt me-1 text-primary"></i> <span id="cameraLabel">Kamera Belakang</span>
                    </button>

                    <!-- HTML5 QR Reader Container -->
                    <div id="reader"></div>
                </div>
            </div>

            <!-- TAB 2: Input Manual NIP -->
            <div class="tab-pane fade h-100" id="pills-manual" role="tabpanel">
                <div class="card bg-white border p-3 rounded-4 h-100 d-flex flex-column justify-content-center shadow-sm">
                    <form id="formManualPresensi">
                        <div class="mb-3">
                            <label for="manual_code" class="form-label text-secondary small fw-medium">NIP Guru / Kode QR Card</label>
                            <input type="text" id="manual_code" class="form-control bg-light text-dark border text-center py-2" placeholder="Ketik NIP / Kode QR" required autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            <i class="fas fa-paper-plane me-1"></i> Kirim Presensi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- POPUP NOTIFICATION MODAL OVERLAY -->
    <div id="scan-popup-overlay" class="scan-popup-overlay">
        <div id="scan-popup-card" class="scan-popup-card">
            <!-- Isi Konten Notifikasi dimasukkan via JavaScript -->
        </div>
    </div>

    <!-- Footer Mobile -->
    <footer class="bottom-card">
        <small class="text-secondary d-block" style="font-size: 11px;">
            <i class="fas fa-map-marker-alt text-danger me-1"></i> Radius Sekolah: <strong>{{ $school->geofence_radius ?? 50 }} Meter</strong> | E-Presensi &copy; {{ date('Y') }}
        </small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Fungsi Web Audio API Sintetis untuk Beep Instan
        function playInstantBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.15);
            } catch (e) {
                // Silent error
            }
        }

        // 2. Fungsi Text-to-Speech
        function speakText(text) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 0.95;
                utterance.pitch = 1.0;
                window.speechSynthesis.speak(utterance);
            }
        }

        // 3. Digital Realtime Clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            document.getElementById('realtime-clock').textContent = `${hours}:${minutes}:${seconds}`;

            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            document.getElementById('realtime-date').textContent = now.toLocaleDateString('id-ID', options);
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 4. Logic Scanner & Geolocation
        document.addEventListener("DOMContentLoaded", function() {
            let currentLat = null;
            let currentLng = null;
            let isProcessing = false;

            const gpsStatus = document.getElementById('gps-status');
            const popupOverlay = document.getElementById('scan-popup-overlay');
            const popupCard = document.getElementById('scan-popup-card');

            const formManual = document.getElementById('formManualPresensi');
            const manualCodeInput = document.getElementById('manual_code');
            const btnSwitchCamera = document.getElementById('btnSwitchCamera');
            const cameraLabel = document.getElementById('cameraLabel');

            const CAMERA_STORAGE_KEY = 'presensi_camera_facing';
            let currentFacingMode = localStorage.getItem(CAMERA_STORAGE_KEY) || 'environment';
            let isSwitchingCamera = false;

            function setGpsStatus(className, html) {
                gpsStatus.className = className;
                gpsStatus.innerHTML = html;
            }

            function showGpsError() {
                setGpsStatus(
                    "alert alert-danger py-1 px-2 mb-0 rounded-3 small flex-shrink-0 shadow-sm", 
                    `<i class="fas fa-exclamation-triangle me-1"></i> Mohon aktifkan GPS`
                );
            }

            function showPopup(cardClass, iconHtml, messageHtml) {
                popupCard.className = `scan-popup-card ${cardClass}`;
                popupCard.innerHTML = `
                    <div class="mb-2">${iconHtml}</div>
                    <div class="fw-bold fs-6 mb-1">${messageHtml}</div>
                `;
                popupOverlay.style.display = "flex";
            }

            function hidePopup() {
                popupOverlay.style.display = "none";
            }

            // A. Geolocation GPS Tracking
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    function(position) {
                        currentLat = position.coords.latitude;
                        currentLng = position.coords.longitude;
                        setGpsStatus(
                            "alert alert-success py-1 px-2 mb-0 rounded-3 small flex-shrink-0 shadow-sm", 
                            `<i class="fas fa-check-circle me-1"></i> GPS Aktif (${currentLat.toFixed(5)}, ${currentLng.toFixed(5)})`
                        );
                    },
                    function(error) {
                        currentLat = null;
                        currentLng = null;
                        showGpsError();
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                );
            } else {
                showGpsError();
            }

            // B. Eksekusi Kirim Presensi via AJAX dengan Suara
            const qrCodeSuccessCallback = (decodedText) => {
                if (isProcessing) return;

                if (!currentLat || !currentLng) {
                    showPopup('bg-danger text-white', '<i class="fas fa-location-slash fa-3x"></i>', 'Mohon aktifkan GPS!');
                    setTimeout(() => { hidePopup(); }, 1500);
                    return;
                }

                isProcessing = true;
                
                // Show Processing Popup
                showPopup('bg-white border text-dark shadow-sm', '<i class="fas fa-spinner fa-spin fa-3x text-primary"></i>', 'Memproses Presensi...');

                // Bunyikan Beep Instan (Web Audio API)
                playInstantBeep();

                fetch("{{ route('attendance.process') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        qr_code: decodedText,
                        latitude: currentLat,
                        longitude: currentLng
                    })
                })
                .then(async (response) => {
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || `Server Error (${response.status})`);
                    }
                    
                    return data;
                })
                .then(data => {
                    if (data.status === 'success' || data.status === 'warning') {
                        const teacherName = data.teacher || 'Pengguna';
                        let voiceText = '';

                        const isLate = data.is_late || (data.message && data.message.toLowerCase().includes('terlambat'));
                        const isPulang = data.type === 'pulang' || (data.message && data.message.toLowerCase().includes('pulang'));

                        if (isLate) {
                            voiceText = `${teacherName}, anda terlambat`;
                        } else if (isPulang) {
                            voiceText = `Sampai jumpa lagi ${teacherName}`;
                        } else {
                            voiceText = `Selamat Datang ${teacherName}`;
                        }

                        speakText(voiceText);

                        if (data.status === 'success') {
                            showPopup('bg-success text-white', '<i class="fas fa-check-circle fa-3x"></i>', `${data.message}<br><small class="fw-normal fs-6">${data.teacher ?? ''} (${data.time ?? ''})</small>`);
                        } else {
                            showPopup('bg-warning text-dark', '<i class="fas fa-exclamation-circle fa-3x"></i>', data.message);
                        }

                        if (manualCodeInput) manualCodeInput.value = '';
                    } else {
                        showPopup('bg-danger text-white', '<i class="fas fa-times-circle fa-3x"></i>', data.message);
                    }

                    setTimeout(() => { 
                        hidePopup();
                        isProcessing = false; 
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error detail:', error);
                    showPopup('bg-danger text-white', '<i class="fas fa-exclamation-triangle fa-3x"></i>', error.message);
                    setTimeout(() => { 
                        hidePopup();
                        isProcessing = false; 
                    }, 2500);
                });
            };

            // C. Listener Form Input Manual
            if (formManual) {
                formManual.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const code = manualCodeInput.value.trim();
                    if (code) qrCodeSuccessCallback(code);
                });
            }

            // D. Inisialisasi Scanner Kamera
            const html5QrCode = new Html5Qrcode("reader");
            const config = { 
                fps: 15, 
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const size = Math.floor(minEdge * 0.90);
                    return { width: size, height: size };
                }
            };

            function updateCameraLabel() {
                cameraLabel.textContent = currentFacingMode === 'environment' ? 'Kamera Belakang' : 'Kamera Depan';
            }

            function startScanner(facingMode) {
                const fallbackMode = facingMode === 'environment' ? 'user' : 'environment';
                return html5QrCode.start({ facingMode }, config, qrCodeSuccessCallback)
                    .then(() => {
                        currentFacingMode = facingMode;
                        localStorage.setItem(CAMERA_STORAGE_KEY, currentFacingMode);
                        updateCameraLabel();
                    })
                    .catch(() => {
                        return html5QrCode.start({ facingMode: fallbackMode }, config, qrCodeSuccessCallback)
                            .then(() => {
                                currentFacingMode = fallbackMode;
                                localStorage.setItem(CAMERA_STORAGE_KEY, currentFacingMode);
                                updateCameraLabel();
                            });
                    });
            }

            updateCameraLabel();
            startScanner(currentFacingMode);

            // E. Tombol Switch Kamera
            if (btnSwitchCamera) {
                btnSwitchCamera.addEventListener('click', function() {
                    if (isSwitchingCamera) return;
                    isSwitchingCamera = true;
                    btnSwitchCamera.disabled = true;

                    const nextFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                    html5QrCode.stop()
                        .then(() => html5QrCode.clear())
                        .catch(() => {})
                        .finally(() => {
                            startScanner(nextFacingMode).finally(() => {
                                isSwitchingCamera = false;
                                btnSwitchCamera.disabled = false;
                            });
                        });
                });
            }
        });
    </script>
</body>
</html>