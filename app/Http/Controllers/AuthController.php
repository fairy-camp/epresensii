<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/'); // Redirect dinamis via routes/web.php
        }
        return view('auth.login');
    }

    // Proses login (Murni Menggunakan Email)
    public function login(Request $request)
    {
        // 1. Validasi Input Email & Password
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Autentikasi Pengguna
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // 3. Redirect Dinamis Berdasarkan Role
            return match ($user->role) {
                'petugas' => redirect()->route('attendance.scan'),
                'guru'    => redirect()->route('attendance.my-history'),
                default   => redirect()->route('dashboard'), // super_admin, admin, kepsek, wakakur
            };
        }

        // 4. Kembali ke login jika email / password salah
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Proses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }
}