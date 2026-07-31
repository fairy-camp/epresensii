@extends('layouts.main')

@section('title', 'Scan QR Code Presensi')
@section('page-title', 'Scanner Presensi Real-Time')

@section('content')
    <!-- HTML5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="row justify-content-center">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="fas fa-qrcode me-2"></i>Scanner Presensi Real-Time</h5>
                </div>
                <div class="card-body text-center p-4">

                    <!-- Alert Info GPS -->
                    <div id="gps-status" class="alert alert-info py-2 mb-3">
                        <i class="fas fa-spinner fa-spin me-1"></i> Mendapatkan lokasi GPS Anda...
                    </div>

                    <!-- Form Koordinat Manual (Fallback saat GPS browser ditolak) -->
                    <div id="gps-manual" class="d-none mb-3">
                        <form id="formManualGps" class="p-2 bg-light rounded border text-start">
                            <small class="text-muted d-block mb-2">Gunakan koordinat manual (Google Maps: klik kanan lokasi
                                & salin koordinat):</small>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="manual_lat" class="form-label small fw-bold mb-1">Latitude</label>
                                    <input type="text" id="manual_lat" class="form-control form-control-sm"
                                        placeholder="-6.208763" autocomplete="off">
                                </div>
                                <div class="col-6">
                                    <label for="manual_lng" class="form-label small fw-bold mb-1">Longitude</label>
                                    <input type="text" id="manual_lng" class="form-control form-control-sm"
                                        placeholder="106.845599" autocomplete="off">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                <i class="fas fa-map-marker-alt me-1"></i> Gunakan Koordinat Manual
                            </button>
                        </form>
                    </div>

                    <!-- Tab Opsi: Kamera vs Input Manual -->
                    <ul class="nav nav-pills nav-justified mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-camera-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-camera" type="button" role="tab">
                                <i class="fas fa-camera me-1"></i> Kamera QR
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-manual-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-manual" type="button" role="tab">
                                <i class="fas fa-keyboard me-1"></i> Input Manual (Testing)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        <!-- Tab 1: Box Frame Kamera -->
                        <div class="tab-pane fade show active" id="pills-camera" role="tabpanel">
                            <div class="d-flex justify-content-center mb-2">
                                <button type="button" id="btnSwitchCamera" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-1"></i> <span id="cameraLabel">Kamera Belakang</span>
                                </button>
                            </div>
                            <div id="reader" style="width: 100%; max-width: 450px; margin: 0 auto;"
                                class="border rounded overflow-hidden shadow-sm"></div>
                        </div>

                        <!-- Tab 2: Form Input Manual Testing -->
                        <div class="tab-pane fade" id="pills-manual" role="tabpanel">
                            <form id="formManualPresensi" class="p-3 bg-light rounded border text-start">
                                <div class="mb-3">
                                    <label for="manual_code" class="form-label fw-bold">Kode QR / NIP Guru</label>
                                    <input type="text" id="manual_code" class="form-control"
                                        placeholder="Masukkan Kode QR atau NIP Guru" required autocomplete="off">
                                    <small class="text-muted">Ketik NIP Guru atau Kode QR untuk simulasi absensi tanpa
                                        kamera.</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold">
                                    <i class="fas fa-paper-plane me-1"></i> Kirim Presensi
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Respon Alert Real-time -->
                    <div id="scan-result" class="mt-3" style="display: none;"></div>

                </div>
                <div class="card-footer bg-white text-center text-muted small py-2">
                    Batas Radius Sekolah: <strong>{{ $school->geofence_radius ?? 50 }} Meter</strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let currentLat = null;
            let currentLng = null;
            let isProcessing = false;

            const gpsStatus = document.getElementById('gps-status');
            const scanResult = document.getElementById('scan-result');
            const formManual = document.getElementById('formManualPresensi');
            const manualCodeInput = document.getElementById('manual_code');
            const gpsManualBox = document.getElementById('gps-manual');
            const formManualGps = document.getElementById('formManualGps');
            const manualLatInput = document.getElementById('manual_lat');
            const manualLngInput = document.getElementById('manual_lng');
            const btnSwitchCamera = document.getElementById('btnSwitchCamera');
            const cameraLabel = document.getElementById('cameraLabel');

            // Preferensi kamera (depan/belakang), diingat lewat localStorage
            const CAMERA_STORAGE_KEY = 'presensi_camera_facing';
            let currentFacingMode = localStorage.getItem(CAMERA_STORAGE_KEY) || 'environment';
            let isSwitchingCamera = false;

            // Helper: update status GPS
            function setGpsStatus(className, html) {
                gpsStatus.className = className;
                gpsStatus.innerHTML = html;
            }

            // Helper: tampilkan fallback koordinat manual
            function showGpsError(message) {
                setGpsStatus(
                    "alert alert-danger py-2 mb-3",
                    `<i class="fas fa-exclamation-triangle me-1"></i> ${message}`
                );
                gpsManualBox.classList.remove('d-none');
            }

            // 1. Ambil Lokasi GPS Perangkat
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    function(position) {
                        currentLat = position.coords.latitude;
                        currentLng = position.coords.longitude;
                        setGpsStatus(
                            "alert alert-success py-2 mb-3",
                            `<i class="fas fa-check-circle me-1"></i> GPS Terhubung (${currentLat.toFixed(5)}, ${currentLng.toFixed(5)})`
                        );
                        gpsManualBox.classList.add('d-none');
                    },
                    function(error) {
                        if (error.code === error.PERMISSION_DENIED) {
                            showGpsError(
                                'Akses GPS Ditolak. Aktifkan izin Lokasi di browser Anda.<br><small class="text-muted">Catatan: Geolokasi browser hanya berjalan di HTTPS (atau localhost). `php artisan serve` memakai HTTP sehingga izin GPS selalu ditolak di HP.</small>'
                                );
                        } else if (error.code === error.POSITION_UNAVAILABLE) {
                            showGpsError(
                                'Posisi GPS tidak tersedia (di dalam ruangan / sinyal lemah). Coba dekat jendela, atau isi koordinat manual di bawah.'
                                );
                        } else if (error.code === error.TIMEOUT) {
                            showGpsError(
                                'Waktu permintaan GPS habis. Coba lagi, atau isi koordinat manual di bawah.'
                                );
                        } else {
                            showGpsError('Terjadi kesalahan GPS: ' + error.message);
                        }
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 30000
                    }
                );
            } else {
                setGpsStatus("alert alert-danger py-2 mb-3", "Browser Anda tidak mendukung Geolocation GPS.");
            }

            // 1b. Fallback: form koordinat manual
            if (formManualGps) {
                formManualGps.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const lat = parseFloat(manualLatInput.value);
                    const lng = parseFloat(manualLngInput.value);

                    if (isNaN(lat) || isNaN(lng)) {
                        alert(
                        "Masukkan Latitude dan Longitude yang valid (contoh: -6.208763, 106.845599).");
                        return;
                    }

                    currentLat = lat;
                    currentLng = lng;
                    setGpsStatus(
                        "alert alert-warning py-2 mb-3",
                        `<i class="fas fa-map-marker-alt me-1"></i> Lokasi manual digunakan (${lat.toFixed(5)}, ${lng.toFixed(5)})`
                    );
                });
            }

            // 2. Fungsi Eksekusi Presensi (Dipakai oleh Kamera & Form Manual)
            const qrCodeSuccessCallback = (decodedText) => {
                if (isProcessing) return;

                if (!currentLat || !currentLng) {
                    alert("Lokasi GPS belum didapatkan! Pastikan izin lokasi diaktifkan pada browser Anda.");
                    return;
                }

                isProcessing = true;
                scanResult.style.display = "block";
                scanResult.className = "alert alert-info";
                scanResult.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> Memproses presensi...`;

                // Audio Beep
                let beep = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                beep.play().catch(() => {});

                // Send Request via AJAX
                fetch("{{ route('attendance.process') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            qr_code: decodedText,
                            latitude: currentLat,
                            longitude: currentLng
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            scanResult.className = "alert alert-success fw-bold";
                            scanResult.innerHTML =
                                `<i class="fas fa-check-circle me-1"></i> ${data.message}<br><small>${data.teacher} - Jam: ${data.time} (Jarak: ${data.distance})</small>`;
                            if (manualCodeInput) manualCodeInput.value = '';
                        } else if (data.status === 'warning') {
                            scanResult.className = "alert alert-warning fw-bold";
                            scanResult.innerHTML =
                                `<i class="fas fa-exclamation-circle me-1"></i> ${data.message}`;
                        } else {
                            scanResult.className = "alert alert-danger fw-bold";
                            scanResult.innerHTML =
                                `<i class="fas fa-times-circle me-1"></i> ${data.message}`;
                        }

                        setTimeout(() => {
                            isProcessing = false;
                        }, 3000);
                    })
                    .catch(error => {
                        scanResult.className = "alert alert-danger fw-bold";
                        scanResult.innerHTML =
                            `<i class="fas fa-times-circle me-1"></i> Terjadi kesalahan jaringan. Coba lagi.`;
                        setTimeout(() => {
                            isProcessing = false;
                        }, 3000);
                    });
            };

            // 3. Listener Form Input Manual Testing
            if (formManual) {
                formManual.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const code = manualCodeInput.value.trim();
                    if (code) {
                        qrCodeSuccessCallback(code);
                    }
                });
            }

            // 4. Inisialisasi Kamera HTML5 QR Scanner
            const html5QrCode = new Html5Qrcode("reader");
            const config = {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            };

            function updateCameraLabel() {
                cameraLabel.textContent = currentFacingMode === 'environment' ? 'Kamera Belakang' : 'Kamera Depan';
            }

            // Mulai kamera sesuai facingMode. Kalau gagal, coba mode sebaliknya sebagai fallback.
            function startScanner(facingMode) {
                const fallbackMode = facingMode === 'environment' ? 'user' : 'environment';

                return html5QrCode.start({
                        facingMode
                    }, config, qrCodeSuccessCallback)
                    .then(() => {
                        currentFacingMode = facingMode;
                        localStorage.setItem(CAMERA_STORAGE_KEY, currentFacingMode);
                        updateCameraLabel();
                    })
                    .catch(err => {
                        console.log(`Kamera (${facingMode}) tidak tersedia, mencoba (${fallbackMode})...`, err);
                        return html5QrCode.start({
                                facingMode: fallbackMode
                            }, config, qrCodeSuccessCallback)
                            .then(() => {
                                currentFacingMode = fallbackMode;
                                localStorage.setItem(CAMERA_STORAGE_KEY, currentFacingMode);
                                updateCameraLabel();
                            })
                            .catch(err2 => {
                                console.log("Kamera tidak ditemukan, berpindah ke mode manual testing.",
                                    err2);
                            });
                    });
            }

            updateCameraLabel();
            startScanner(currentFacingMode);

            // 5. Tombol Switch Kamera Depan / Belakang
            if (btnSwitchCamera) {
                btnSwitchCamera.addEventListener('click', function() {
                    if (isSwitchingCamera) return;
                    isSwitchingCamera = true;
                    btnSwitchCamera.disabled = true;

                    const nextFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';

                    html5QrCode.stop()
                        .then(() => html5QrCode.clear())
                        .catch(() => {}) // aman diabaikan kalau kamera memang belum jalan
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
@endsection
