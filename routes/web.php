<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\ShiftAssignmentController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------------
// 1. Guest Routes (Pengguna yang BELUM login)
// --------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
});

// --------------------------------------------------------------------------
// 2. Authenticated Routes (Semua Pengguna yang SUDAH Login)
// --------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ----------------------------------------------------------------------
    // ROOT ROUTE (/) -> Dynamic Redirect Berdasarkan Role Pengguna
    // ----------------------------------------------------------------------
    Route::get('/', function () {
        $role = auth()->user()->role ?? '';

        return match ($role) {
            'petugas' => redirect()->route('attendance.scan'),
            'guru'    => redirect()->route('attendance.my-history'),
            default   => redirect()->route('dashboard'),
        };
    });

    // ----------------------------------------------------------------------
    // A. Akses Scanner Presensi QR Code (Role: Super Admin, Admin, Petugas)
    // ----------------------------------------------------------------------
    Route::middleware('role:super_admin,admin,petugas')->group(function () {
        Route::get('/attendance/scan', [AttendanceController::class, 'scanPage'])->name('attendance.scan');
        Route::post('/attendance/process', [AttendanceController::class, 'processScan'])->name('attendance.process');
    });

    // ----------------------------------------------------------------------
    // B. Akses Dashboard, Laporan, & Data Guru Read-Only
    //    (Role: Super Admin, Admin, Kepala Sekolah, Wakil Kurikulum)
    // ----------------------------------------------------------------------
    Route::middleware('role:super_admin,admin,kepala_sekolah,waka')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Data Guru (Hanya Melihat Daftar / Read-Only)
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');

        // Laporan Presensi
        Route::get('/reports/attendance', [ReportController::class, 'index'])->name('reports.attendance');
        Route::get('/reports/attendance/print', [ReportController::class, 'print'])->name('reports.attendance.print');
    });

    // ----------------------------------------------------------------------
    // C. Akses Manajemen Full, Master Data, & Cetak (Role: Super Admin, Admin)
    // ----------------------------------------------------------------------
    Route::middleware('role:super_admin,admin')->group(function () {
        // Tambah Guru & Aksi Cetak ID Card / Regenerate QR
        Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/print-all-cards', [TeacherController::class, 'printAllCards'])->name('teachers.print-all-cards');
        Route::get('/teachers/{id}/print-card', [TeacherController::class, 'printCard'])->name('teachers.print-card');
        Route::post('/teachers/{id}/regenerate-qr', [TeacherController::class, 'regenerateQr'])->name('teachers.regenerate-qr');

        // Master Jadwal Kerja
        Route::resource('work-schedules', WorkScheduleController::class)->only(['index', 'store', 'update', 'destroy']);

        // Penugasan Shift
        Route::get('/shift-assignments', [ShiftAssignmentController::class, 'index'])->name('shift-assignments.index');
        Route::post('/shift-assignments/bulk', [ShiftAssignmentController::class, 'storeBulk'])->name('shift-assignments.storeBulk');
        Route::delete('/shift-assignments/{shiftAssignment}', [ShiftAssignmentController::class, 'destroy'])->name('shift-assignments.destroy');

        // Pengaturan Lokasi Sekolah
        Route::get('/settings/school', [SchoolSettingController::class, 'index'])->name('settings.school.index');
        Route::put('/settings/school', [SchoolSettingController::class, 'update'])->name('settings.school.update');
    });

    // ----------------------------------------------------------------------
    // D. Akses Khusus Guru (Role: Guru)
    // ----------------------------------------------------------------------
    Route::middleware('role:guru,kepala_sekolah,waka,satpam,staff,petugas')->group(function () {
        Route::get('/my-attendance-history', [AttendanceController::class, 'myHistory'])->name('attendance.my-history');
    });
});