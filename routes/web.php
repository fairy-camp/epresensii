<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\ShiftAssignmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ApelAttendanceController;
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

    // ROOT ROUTE (/) -> Dynamic Redirect Berdasarkan Role Pengguna
    Route::get('/', function () {
        $role = auth()->user()->role ?? '';

        return match ($role) {
            'petugas' => redirect()->route('attendance.scan'),
            'guru'    => redirect()->route('attendance.my-history'),
            default   => redirect()->route('dashboard'),
        };
    });

    // A. Akses Scanner Presensi Khusus Petugas
    Route::middleware('role:petugas')->group(function () {
        // Scanner Presensi Harian
        Route::get('/attendance/scan', [AttendanceController::class, 'scanPage'])->name('attendance.scan');
        Route::post('/attendance/process', [AttendanceController::class, 'processScan'])->name('attendance.process');

        // Scanner Presensi Apel Pagi
        Route::get('/apel/scan', [ApelAttendanceController::class, 'scanPage'])->name('apel.scan');
        Route::post('/apel/scan/process', [ApelAttendanceController::class, 'processScan'])->name('apel.scan.process');
    });

    // B. Akses Lihat Data & Laporan (Role: Super Admin, Admin, Kepala Sekolah, Waka)
    Route::middleware('role:super_admin,admin,kepala_sekolah,waka')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Data Guru (Hanya Melihat Daftar)
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');

        // Data Presensi Harian (Hanya Melihat Daftar)
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');

        // Data & Laporan Presensi Apel Pagi
        Route::get('/apel/attendances', [ApelAttendanceController::class, 'index'])->name('apel.index');
        
        // Laporan Presensi Utama
        Route::get('/reports/attendance', [ReportController::class, 'index'])->name('reports.attendance');
        Route::get('/reports/attendance/pdf', [ReportController::class, 'exportAttendancePdf'])->name('reports.attendance.pdf');

        // Laporan Presensi Apel
        Route::get('/reports/apel', [ReportController::class, 'apelIndex'])->name('reports.apel');
        Route::get('/reports/apel/pdf', [ReportController::class, 'exportApelPdf'])->name('reports.apel.pdf');
    });

    // C. Akses Manajemen & Master Data (HANYA Super Admin & Admin)
    Route::middleware('role:super_admin,admin')->group(function () {
        // Ubah Password Admin via Navbar
        Route::put('/admin/password/update', [AuthController::class, 'updateAdminPassword'])->name('admin.password.update');

        // Master Teachers CRUD & Import CSV
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::post('/teachers/import-csv', [TeacherController::class, 'importCsv'])->name('teachers.import-csv');
        Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

        // Cetak & QR Guru
        Route::get('/teachers/print-all-cards', [TeacherController::class, 'printAllCards'])->name('teachers.print-all-cards');
        Route::get('/teachers/{id}/print-card', [TeacherController::class, 'printCard'])->name('teachers.print-card');
        Route::post('/teachers/{id}/regenerate-qr', [TeacherController::class, 'regenerateQr'])->name('teachers.regenerate-qr');

        // Edit & Hapus Data Presensi Harian
        Route::put('/attendances/{id}', [AttendanceController::class, 'update'])->name('attendances.update');
        Route::delete('/attendances/{id}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');

        // Edit & Hapus Data Presensi Apel
        Route::delete('/apel/attendances/{id}', [ApelAttendanceController::class, 'destroy'])->name('apel.destroy');

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

    // D. Akses Histori Presensi Mandiri & Ubah Password (Guru & Staf)
    Route::middleware('role:guru,kepala_sekolah,waka,satpam,staff')->group(function () {
        Route::get('/my-attendance-history', [AttendanceController::class, 'myHistory'])->name('attendance.my-history');
        Route::put('/my-history/password', [AttendanceController::class, 'updatePasswordSelf'])->name('password.update-self');
    });
});