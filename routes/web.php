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

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Scanner Presensi QR Code
    Route::get('/attendance/scan', [AttendanceController::class, 'scanPage'])->name('attendance.scan');
    Route::post('/attendance/process', [AttendanceController::class, 'processScan'])->name('attendance.process');
});

// --------------------------------------------------------------------------
// 3. Admin & SuperAdmin Routes (Khusus Management)
// --------------------------------------------------------------------------
Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    
    // =========================================================================
    // MANAJEMEN GURU
    // =========================================================================
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');

    // 1. ROUTE STATIS (Harus di Atas Parameter {id})
    Route::get('/teachers/print-all-cards', [TeacherController::class, 'printAllCards'])->name('teachers.print-all-cards');

    // 2. ROUTE DINAMIS (Memakai {id}, Taruh di Bawah Route Statis)
    Route::get('/teachers/{id}/print-card', [TeacherController::class, 'printCard'])->name('teachers.print-card');
    Route::post('/teachers/{id}/regenerate-qr', [TeacherController::class, 'regenerateQr'])->name('teachers.regenerate-qr');
    // =========================================================================

    // Master Jadwal Kerja
    Route::resource('work-schedules', WorkScheduleController::class)->only(['index', 'store', 'update', 'destroy']);

    // Penugasan Shift
    Route::get('/shift-assignments', [ShiftAssignmentController::class, 'index'])->name('shift-assignments.index');
    Route::post('/shift-assignments/bulk', [ShiftAssignmentController::class, 'storeBulk'])->name('shift-assignments.storeBulk');
    Route::delete('/shift-assignments/{shiftAssignment}', [ShiftAssignmentController::class, 'destroy'])->name('shift-assignments.destroy');

    // Pengaturan Lokasi Sekolah
    Route::get('/settings/school', [SchoolSettingController::class, 'index'])->name('settings.school.index');
    Route::put('/settings/school', [SchoolSettingController::class, 'update'])->name('settings.school.update');

    // Laporan Presensi
    Route::get('/reports/attendance', [ReportController::class, 'index'])->name('reports.attendance');
    Route::get('/reports/attendance/print', [ReportController::class, 'print'])->name('reports.attendance.print');
});