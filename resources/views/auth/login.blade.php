@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="card border-0 shadow-lg p-4" style="width: 100%; max-width: 400px; border-radius: 12px;">
    <div class="card-body">
        <div class="text-center mb-4">
            <i class="fas fa-qrcode fa-3x text-primary mb-2"></i>
            <h4 class="fw-bold">E-Presensi</h4>
            <p class="text-muted small">UP RPL CodePelita</p>
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
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="masukkan email" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="masukkan password" required>
                    <button class="btn btn-outline-secondary" type="button" id="btnTogglePassword" tabindex="-1">
                        <i class="fas fa-eye" id="iconTogglePassword"></i>
                    </button>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Ingat Saya</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fas fa-sign-in-alt me-1"></i> Masuk
            </button>
        </form>
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