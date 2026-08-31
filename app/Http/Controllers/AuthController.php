<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        // 2. Ambil nilai status checkbox "remember" (true/false)
        $remember = $request->boolean('remember');

        // 3. Autentikasi Pengguna dengan parameter $remember
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // 4. Redirect Dinamis Berdasarkan Role
            return match ($user->role) {
                'petugas'                   => redirect()->route('attendance.scan'),
                'guru', 'satpam', 'staff'   => redirect()->route('attendance.my-history'),
                default                     => redirect()->route('dashboard'), // super_admin, admin, kepala_sekolah, waka
            };
        }

        // 5. Kembali ke login jika email / password salah
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

    /**
     * Memproses Perubahan Password Khusus Admin & Super Admin
     */
    public function updateAdminPassword(Request $request)
    {
        $user = Auth::user();

        // 1. Verifikasi Tambahan Hak Akses Role
        if (!$user || !in_array($user->role, ['admin', 'super_admin'])) {
            return redirect()->back()->with('error', 'Akses ditolak! Fitur ini khusus Admin.');
        }

        // 2. Validasi Input Password
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        // 3. Cek Kesesuaian Password Saat Ini
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai!'
            ]);
        }

        // 4. Update Password Baru ke Database
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('success', 'Password admin berhasil diperbarui!');
    }
}