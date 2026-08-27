<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>E-Presensi Apel Pagi - SMKSA</title>

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

        .app-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 10px 12px;
            overflow: hidden;
        }

        /* Banner Clock khusus Tema Apel Pagi (Ramping & Kompak) */
        .clock-card-apel {
            background: linear-gradient(135deg, #d97706, #b45309);
            border-radius: 8px;
            padding: 3px 8px;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.2);
            flex-shrink: 0;
        }

        .scanner-container {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid #fcd34d;
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

        .bottom-card {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 8px 12px;
            flex-shrink: 0;
            text-align: center;
        }

        .btn-camera-toggle {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 500;
            z-index: 10;
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
            background-color: #d97706;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.25);
        }

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

        .scan-popup-card {
            width: 100%;
            max-width: 320px;
            border-radius: 20px;
            padding: 20px 16px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: popupScale 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
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

        @keyframes popupScale {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- Header Mobile App -->
    <header class="mobile-header d-flex align-items-center justify-content-between shadow-sm">
        <a href="{{ route('apel.scan') }}">
            <div class="d-flex align-items-center gap-2">
                <div class="header-logo-box d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Sekolah" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="d-flex flex-column justify-content-center">
                    <h6 class="mb-0 fw-bold text-dark fs-6 lh-1">E-Presensi Apel Pagi</h6>
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

    <!-- Navigation Switcher Mode Presensi (Ukuran Sama Besar w-50) -->
    <div class="px-3 pt-2 bg-white border-bottom">
        <div class="btn-group w-100 shadow-sm mb-2" role="group">
            <a href="{{ route('attendance.scan') }}" class="btn btn-sm btn-outline-primary fw-bold py-1 w-50">
                <i class="fas fa-clock me-1"></i> Presensi Harian
            </a>
            <a href="{{ route('apel.scan') }}" class="btn btn-sm btn-warning active text-dark fw-bold py-1 w-50">
                <i class="fas fa-flag me-1"></i> Apel Pagi
            </a>
        </div>
    </div>

    <!-- Content Area -->
    <main class="app-body">
        <!-- Banner Jam & Jadwal Apel (Tampilan Ramping) -->
        <div class="clock-card-apel text-center">
            <div class="d-flex align-items-center justify-content-center gap-2" style="font-size: 12px;">
                <span id="realtime-clock" class="fw-bold text-white">00:00:00</span>
                <span class="text-white-50">|</span>
                <span id="realtime-date" class="text-white-50 fw-medium">Senin, 1 Jan 2026</span>
            </div>
            <div class="badge bg-light text-dark fw-bold mt-1" style="font-size: 10px;">
                <i class="fas fa-bullhorn text-warning me-1"></i> Jam Apel: 06:45 - 07:00 WIB
            </div>
        </div>

        <!-- Status GPS Badge -->
        <div id="gps-status" class="alert alert-light border text-secondary text-center py-1 px-2 mb-0 rounded-3 small flex-shrink-0 shadow-sm" style="font-size: 11px;">
            <i class="fas fa-spinner fa-spin me-1 text-warning"></i> Mendapatkan koordinat GPS...
        </div>

        <!-- Fallback GPS Manual -->
        <div id="gps-manual" class="d-none flex-shrink-0">
            <div class="card bg-white border p-2 rounded-3 shadow-sm">
                <small class="text-warning d-block mb-1" style="font-size: 11px;"><i class="fas fa-exclamation-triangle me-1"></i> Mode GPS Manual:</small>
                <form id="formManualGps">
                    <div class="row g-1 mb-1">
                        <div class="col-6">
                            <input type="text" id="manual_lat" class="form-control form-control-sm bg-light text-dark border py-1" placeholder="Latitude" style="font-size: 11px;">
                        </div>
                        <div class="col-6">
                            <input type="text" id="manual_lng" class="form-control form-control-sm bg-light text-dark border py-1" placeholder="Longitude" style="font-size: 11px;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-xs btn-warning w-100 py-1 fw-medium" style="font-size: 11px;">Simpan Koordinat</button>
                </form>
            </div>
        </div>

        <!-- Tab Navigasi Scanner vs Manual -->
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

        <!-- Tab Content -->
        <div class="tab-content flex-grow-1 position-relative" id="pills-tabContent" style="min-height: 0;">
            <!-- TAB 1: Scanner Kamera -->
            <div class="tab-pane fade show active h-100 position-relative" id="pills-camera" role="tabpanel">
                <div class="scanner-container">
                    <button type="button" id="btnSwitchCamera" class="btn btn-camera-toggle shadow-sm position-absolute top-0 end-0 m-2">
                        <i class="fas fa-sync-alt me-1 text-warning"></i> <span id="cameraLabel">Kamera Belakang</span>
                    </button>
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
                        <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold shadow-sm">
                            <i class="fas fa-paper-plane me-1"></i> Kirim Presensi Apel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- POPUP NOTIFICATION MODAL OVERLAY -->
    <div id="scan-popup-overlay" class="scan-popup-overlay">
        <div id="scan-popup-card" class="scan-popup-card"></div>
    </div>

    <!-- Footer Mobile -->
    <footer class="bottom-card">
        <small class="text-secondary d-block" style="font-size: 11px;">
            <i class="fas fa-map-marker-alt text-danger me-1"></i> Radius Sekolah: <strong>{{ $school->geofence_radius ?? 50 }} Meter</strong> | E-Presensi &copy; {{ date('Y') }}
        </small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Web Audio API Sintetis untuk Beep Instan
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
                // Ignore audio policy error
            }
        }

        // 2. Text to Speech
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

        // 3. Realtime Clock
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

        document.addEventListener("DOMContentLoaded", function() {
            let currentLat = null;
            let currentLng = null;
            let isProcessing = false;

            const gpsStatus = document.getElementById('gps-status');
            const popupOverlay = document.getElementById('scan-popup-overlay');
            const popupCard = document.getElementById('scan-popup-card');

            const formManual = document.getElementById('formManualPresensi');
            const manualCodeInput = document.getElementById('manual_code');
            const gpsManualBox = document.getElementById('gps-manual');
            const formManualGps = document.getElementById('formManualGps');
            const manualLatInput = document.getElementById('manual_lat');
            const manualLngInput = document.getElementById('manual_lng');
            const btnSwitchCamera = document.getElementById('btnSwitchCamera');
            const cameraLabel = document.getElementById('cameraLabel');

            const CAMERA_STORAGE_KEY = 'presensi_camera_facing';
            let currentFacingMode = localStorage.getItem(CAMERA_STORAGE_KEY) || 'environment';
            let isSwitchingCamera = false;

            function setGpsStatus(className, html) {
                gpsStatus.className = className;
                gpsStatus.innerHTML = html;
            }

            function showGpsError(message) {
                setGpsStatus("alert alert-danger py-1 px-2 mb-0 rounded-3 small flex-shrink-0 shadow-sm", `<i class="fas fa-exclamation-triangle me-1"></i> ${message}`);
                gpsManualBox.classList.remove('d-none');
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

            // GPS Tracking
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    function(position) {
                        currentLat = position.coords.latitude;
                        currentLng = position.coords.longitude;
                        setGpsStatus("alert alert-success py-1 px-2 mb-0 rounded-3 small flex-shrink-0 shadow-sm", `<i class="fas fa-check-circle me-1"></i> GPS Aktif (${currentLat.toFixed(5)}, ${currentLng.toFixed(5)})`);
                        gpsManualBox.classList.add('d-none');
                    },
                    function(error) {
                        showGpsError('Sinyal GPS lemah atau tidak diizinkan.');
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                );
            } else {
                setGpsStatus("alert alert-danger py-1 px-2 mb-0 rounded-3 small flex-shrink-0 shadow-sm", "Browser tidak mendukung Geolocation.");
            }

            // Manual GPS Submit
            if (formManualGps) {
                formManualGps.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const lat = parseFloat(manualLatInput.value);
                    const lng = parseFloat(manualLngInput.value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        currentLat = lat;
                        currentLng = lng;
                        setGpsStatus("alert alert-warning py-1 px-2 mb-0 rounded-3 small flex-shrink-0 shadow-sm", `<i class="fas fa-map-marker-alt me-1"></i> GPS Manual (${lat.toFixed(5)}, ${lng.toFixed(5)})`);
                    }
                });
            }

            // Eksekusi AJAX Presensi APEL PAGI
            const qrCodeSuccessCallback = (decodedText) => {
                if (isProcessing) return;

                if (!currentLat || !currentLng) {
                    showPopup('bg-danger text-white', '<i class="fas fa-location-slash fa-3x"></i>', 'Lokasi GPS Belum Didapatkan!');
                    setTimeout(() => { hidePopup(); }, 1200);
                    return;
                }

                isProcessing = true;
                showPopup('bg-white border text-dark shadow-sm', '<i class="fas fa-spinner fa-spin fa-3x text-warning"></i>', 'Memproses Presensi Apel...');

                // Bunyikan Beep Instan Web Audio API
                playInstantBeep();

                // Target Endpoint Khusus APEL
                fetch("{{ route('apel.scan.process') }}", {
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
                    const teacherName = data.teacher || 'Pengguna';

                    if (data.status === 'success') {
                        speakText(`Presensi Apel Pagi Berhasil. ${teacherName}`);
                        showPopup('bg-success text-white', '<i class="fas fa-check-circle fa-3x"></i>', `${data.message}<br><small class="fw-normal fs-6">${teacherName} (${data.time ?? ''})</small>`);
                    } else if (data.status === 'warning') {
                        speakText(data.message);
                        showPopup('bg-warning text-dark', '<i class="fas fa-exclamation-circle fa-3x"></i>', data.message);
                    } else {
                        speakText('Gagal Presensi Apel');
                        showPopup('bg-danger text-white', '<i class="fas fa-times-circle fa-3x"></i>', data.message);
                    }

                    if (manualCodeInput) manualCodeInput.value = '';

                    setTimeout(() => { 
                        hidePopup();
                        isProcessing = false; 
                    }, 2000);
                })
                .catch(error => {
                    showPopup('bg-danger text-white', '<i class="fas fa-exclamation-triangle fa-3x"></i>', error.message);
                    setTimeout(() => { 
                        hidePopup();
                        isProcessing = false; 
                    }, 2500);
                });
            };

            if (formManual) {
                formManual.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const code = manualCodeInput.value.trim();
                    if (code) qrCodeSuccessCallback(code);
                });
            }

            // Inisialisasi Scanner Kamera (Ukuran Area 90%)
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