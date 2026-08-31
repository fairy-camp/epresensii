@extends('layouts.auth')

@section('title', 'Login')

@push('styles')
<style>
    /* 1. Transisi halus untuk semua elemen input group */
    .form-control, 
    .input-group-text, 
    .btn-outline-secondary {
        transition: border-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out, background-color 0.3s ease-in-out, color 0.3s ease-in-out !important;
    }

    /* 2. Efek Soft Glow saat input diklik / fokus */
    .form-control:focus {
        border-color: #86b7fe !important;
        box-shadow: 0 0 12px rgba(13, 110, 253, 0.18) !important; /* Soft Glow Blue */
        z-index: 3;
    }

    /* 3. Menyorot ikon di sebelah kiri secara halus saat input aktif */
    .input-group:focus-within .input-group-text {
        border-color: #86b7fe !important;
        background-color: #e7f1ff !important; /* Background ikon berubah soft blue */
        color: #0d6efd !important;             /* Warna ikon berubah ke warna utama */
    }

    /* 4. Menyesuaikan border tombol mata di sebelah kanan saat input aktif */
    .input-group:focus-within .btn-outline-secondary {
        border-color: #86b7fe !important;
    }

    /* 5. Transisi gerakan label floating agar lebih mulus */
    .form-floating > label {
        transition: opacity 0.25s ease-in-out, transform 0.25s ease-in-out !important;
    }

    /* 6. Penyesuaian khusus mode HP (Layar <= 576px) */
    @media (max-width: 576px) {
        .login-card {
            margin-left: 16px !important;
            margin-right: 16px !important;
            width: calc(100% - 32px) !important;
            padding-top: 16px !important;
            padding-bottom: 28px !important;
        }

        .login-logo {
            height: 110px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-lg pt-3 pb-4 px-3 px-md-4 login-card" style="width: 100%; max-width: 400px; border-radius: 12px;">
    <div class="card-body p-0">
        <!-- Brand / Header Logo -->
        <div class="text-center mb-4">
            <img src="{{ asset('img/logo.png') }}" alt="Logo Sekolah" class="mb-0 login-logo" style="height: 140px; width: auto; max-width: 100%; object-fit: contain;">
            <h4 class="fw-bold mb-0 fs-4">E-Presensi</h4>
            <p class="text-muted small mb-0">SMK Syafi'i Akrom</p>
        </div>

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show small" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('login.perform') }}" method="POST">
            @csrf
            
            <!-- Email Input (Form Floating + Input Group) -->
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                    <label for="email">Email</label>
                </div>
            </div>

            <!-- Password Input (Form Floating + Input Group + Toggle Button) -->
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                <button class="btn btn-outline-secondary" type="button" id="btnTogglePassword" tabindex="-1">
                    <i class="fas fa-eye" id="iconTogglePassword"></i>
                </button>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Ingat Saya</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fas fa-sign-in-alt me-1"></i> Masuk
            </button>
        </form>

        <!-- Footer Card (Powered By) -->
        <div class="text-center mt-4 pt-1">
            <small class="text-muted" style="font-size: 11px;">
                Powered by <a href="https://rplsmksa.com/" target="_blank" class="text-decoration-none fw-semibold text-danger">Codepelita RPL SMKSA</a>
            </small>
        </div>
    </div>
</div>

<!-- JavaScript Toggle Password -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const btnToggle = document.getElementById('btnTogglePassword');
        const inputPassword = document.getElementById('password');
        const iconToggle = document.getElementById('iconTogglePassword');

        if (btnToggle && inputPassword && iconToggle) {
            btnToggle.addEventListener('click', function () {
                // Toggle tipe atribut input
                const type = inputPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                inputPassword.setAttribute('type', type);

                // Toggle ikon mata / mata terdeteksi
                iconToggle.classList.toggle('fa-eye');
                iconToggle.classList.toggle('fa-eye-slash');
            });
        }
    });
</script>
@endsection