<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\QrCode;
use App\Models\Teacher;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    // Menampilkan Daftar Guru
    public function index()
    {
        $teachers = Teacher::with(['user', 'position', 'workSchedule', 'activeQrCode'])
            ->latest()
            ->paginate(10);

        return view('teachers.index', compact('teachers'));
    }

    // Menampilkan Form Tambah Guru
    public function create()
    {
        $positions = Position::all();
        $schedules = WorkSchedule::where('is_active', true)->get();

        return view('teachers.create', compact('positions', 'schedules'));
    }

    // Menyimpan Data Guru Baru
    public function store(Request $request)
    {
        // 1. Validasi Input dengan Pesan Bahasa Indonesia
        $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'nik'              => 'nullable|string|max:16|unique:teachers,nik',
            'nip'              => 'nullable|string|max:30|unique:teachers,nip',
            'gender'           => 'required|in:L,P',
            'position_id'      => 'required|exists:positions,id',
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'phone'            => 'nullable|string|max:20',
        ], [
            // Pesan Error Kustom untuk Duplikasi & Mandatory Field
            'email.required'           => 'Alamat email wajib diisi.',
            'email.unique'             => 'Alamat email tersebut sudah terdaftar di sistem!',
            'password.required'        => 'Password login wajib diisi.',
            'password.min'             => 'Password minimal terdiri dari 6 karakter.',
            'nik.unique'               => 'NIK tersebut sudah terdaftar! Gunakan NIK yang lain.',
            'nip.unique'               => 'NIP tersebut sudah terdaftar! Gunakan NIP yang lain.',
            'full_name.required'       => 'Nama lengkap pegawai wajib diisi.',
            'gender.required'          => 'Silakan pilih jenis kelamin.',
            'position_id.required'     => 'Silakan pilih jabatan pegawai.',
            'work_schedule_id.required' => 'Silakan pilih jadwal kerja.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Buat Akun User
                $user = User::create([
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'role'      => 'guru',
                    'is_active' => true,
                ]);

                // 2. Buat Data Teacher
                $teacher = Teacher::create([
                    'user_id'          => $user->id,
                    'nik'              => $request->nik,
                    'nip'              => $request->nip,
                    'full_name'        => $request->full_name,
                    'gender'           => $request->gender,
                    'position_id'      => $request->position_id,
                    'work_schedule_id' => $request->work_schedule_id,
                    'phone'            => $request->phone,
                    'is_active'        => true,
                ]);

                // 3. Generate QR Code Unik
                QrCode::create([
                    'teacher_id' => $teacher->id,
                    'code'       => 'QR-' . strtoupper(Str::random(10)) . '-' . time(),
                    'is_active'  => true,
                ]);
            });

            return redirect()->route('teachers.index')->with('success', 'Data Guru dan QR Code berhasil dibuat!');

        } catch (\Exception $e) {
            // Jika ada error database lainnya, kembalikan ke form beserta inputan lama
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    // Regenerate QR Code Baru (Jika QR lama bocor / rusak)
    public function regenerateQr($id)
    {
        $teacher = Teacher::findOrFail($id);

        try {
            DB::transaction(function () use ($teacher) {
                // Nonaktifkan QR lama
                QrCode::where('teacher_id', $teacher->id)->update(['is_active' => false]);

                // Buat QR baru
                QrCode::create([
                    'teacher_id' => $teacher->id,
                    'code'       => 'QR-' . strtoupper(Str::random(10)) . '-' . time(),
                    'is_active'  => true,
                ]);
            });

            return back()->with('success', "QR Code untuk {$teacher->full_name} berhasil diperbarui!");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui QR Code: ' . $e->getMessage());
        }
    }

    // Cetak ID Card Satuan
    public function printCard($id)
    {
        $teacher = Teacher::with(['position', 'activeQrCode'])->findOrFail($id);

        return view('teachers.print-card', [
            'teachers' => collect([$teacher])
        ]);
    }

    // Cetak ID Card Massal (Semua Guru Aktif)
    public function printAllCards()
    {
        $teachers = Teacher::with(['position', 'activeQrCode'])
            ->where('is_active', true)
            ->get();

        if ($teachers->isEmpty()) {
            return back()->with('error', 'Tidak ada data guru aktif untuk dicetak.');
        }

        return view('teachers.print-card', compact('teachers'));
    }
}