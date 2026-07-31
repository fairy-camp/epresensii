@extends('layouts.main')

@section('title', 'Pengaturan Lokasi Sekolah')
@section('page-title', 'Pengaturan Lokasi & Radius Sekolah')

@section('content')
<!-- Leaflet CSS & JS for Free OpenStreetMap -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-school me-2 text-primary"></i>Profil & Koordinat</h5>
            </div>
            <form action="{{ route('settings.school.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Sekolah / Instansi <span class="text-danger">*</span></label>
                        <input type="text" name="school_name" class="form-control @error('school_name') is-invalid @enderror" value="{{ old('school_name', $setting->school_name ?? 'SMK UP RPL CodePelita') }}" required>
                        @error('school_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="text" id="latitude" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $setting->latitude ?? -6.8898363) }}" required readonly>
                            @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="text" id="longitude" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $setting->longitude ?? 109.6745917) }}" required readonly>
                            @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Radius Toleransi Presensi (Meter) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="geofence_radius" name="geofence_radius" class="form-control @error('geofence_radius') is-invalid @enderror" value="{{ old('geofence_radius', $setting->geofence_radius ?? 50) }}" min="5" max="5000" required>
                            <span class="input-group-text">Meter</span>
                        </div>
                        @error('geofence_radius') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <small class="text-muted d-block mt-1">Jarak maksimal guru dari titik sekolah saat melakukan scan QR.</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="getLocation()">
                            <i class="fas fa-crosshairs me-1"></i> Gunakan Lokasi Saya Saat Ini (GPS)
                        </button>
                    </div>
                </div>
                <div class="card-footer bg-white text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-7 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Peta Titik & Radius Lokasi</h5>
                <small class="text-muted">Klik/Geser marker di peta untuk ubah titik lokasi</small>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 420px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let lat = parseFloat(document.getElementById('latitude').value) || -6.8898363;
        let lng = parseFloat(document.getElementById('longitude').value) || 109.6745917;
        let radius = parseInt(document.getElementById('geofence_radius').value) || 50;

        // Init Leaflet Map
        let map = L.map('map').setView([lat, lng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Marker & Radius Circle
        let marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        let circle = L.circle([lat, lng], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.2,
            radius: radius
        }).addTo(map);

        function updatePosition(newLat, newLng) {
            document.getElementById('latitude').value = newLat.toFixed(8);
            document.getElementById('longitude').value = newLng.toFixed(8);
            marker.setLatLng([newLat, newLng]);
            circle.setLatLng([newLat, newLng]);
            map.panTo([newLat, newLng]);
        }

        // Drag marker event
        marker.on('dragend', function (e) {
            let position = marker.getLatLng();
            updatePosition(position.lat, position.lng);
        });

        // Click map event
        map.on('click', function (e) {
            updatePosition(e.latlng.lat, e.latlng.lng);
        });

        // Update radius circle dynamically
        document.getElementById('geofence_radius').addEventListener('input', function (e) {
            let newRadius = parseInt(e.target.value) || 0;
            circle.setRadius(newRadius);
        });

        // Global function GPS
        window.getLocation = function () {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    updatePosition(position.coords.latitude, position.coords.longitude);
                    map.setZoom(18);
                }, function (error) {
                    alert('Gagal mengambil lokasi GPS: ' + error.message);
                });
            } else {
                alert('Browser Anda tidak mendukung fitur Geolocation GPS.');
            }
        }
    });
</script>
@endsection