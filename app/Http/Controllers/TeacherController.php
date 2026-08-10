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
    // Menampilkan Daftar Guru + Data Modal
    public function index()
    {
        // Ambil seluruh data guru agar DataTables Client-Side berfungsi penuh
        $teachers = Teacher::with(['user', 'position', 'workSchedule', 'activeQrCode'])
            ->latest()
            ->get();

        $positions = Position::all();
        $schedules = WorkSchedule::where('is_active', true)->get();

        return view('teachers.index', compact('teachers', 'positions', 'schedules'));
    }

    // Menyimpan Data Guru Baru (Via Modal)
    public function store(Request $request)
    {
        $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'role'             => 'required|in:guru,kepala_sekolah,waka,satpam,staff,petugas',
            'nik'              => 'nullable|string|max:16|unique:teachers,nik',
            'nip'              => 'nullable|string|max:30|unique:teachers,nip',
            'gender'           => 'required|in:L,P',
            'position_id'      => 'required|exists:positions,id',
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'phone'            => 'nullable|string|max:20',
        ], [
            'email.required'       => 'Alamat email wajib diisi.',
            'email.unique'         => 'Alamat email tersebut sudah terdaftar!',
            'password.required'    => 'Password login wajib diisi.',
            'password.min'         => 'Password minimal 6 karakter.',
            'role.required'        => 'Silakan pilih role akses sistem.',
            'nik.unique'           => 'NIK tersebut sudah terdaftar!',
            'nip.unique'           => 'NIP tersebut sudah terdaftar!',
            'full_name.required'   => 'Nama lengkap pegawai wajib diisi.',
            'gender.required'      => 'Silakan pilih jenis kelamin.',
            'position_id.required' => 'Silakan pilih jabatan pegawai.',
            'work_schedule_id.required' => 'Silakan pilih jadwal kerja.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'role'      => $request->role,
                    'is_active' => true,
                ]);

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

                QrCode::create([
                    'teacher_id' => $teacher->id,
                    'code'       => 'QR-' . strtoupper(Str::random(10)) . '-' . time(),
                    'is_active'  => true,
                ]);
            });

            return redirect()->route('teachers.index')->with('success', 'Data Pegawai dan Akun berhasil dibuat!');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    // Memperbarui Data Guru (Via Modal Edit)
    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $user = User::findOrFail($teacher->user_id);

        $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'password'         => 'nullable|string|min:6',
            'role'             => 'required|in:guru,kepala_sekolah,waka,satpam,staff,petugas',
            'nik'              => 'nullable|string|max:16|unique:teachers,nik,' . $teacher->id,
            'nip'              => 'nullable|string|max:30|unique:teachers,nip,' . $teacher->id,
            'gender'           => 'required|in:L,P',
            'position_id'      => 'required|exists:positions,id',
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'phone'            => 'nullable|string|max:20',
        ]);

        try {
            DB::transaction(function () use ($request, $teacher, $user) {
                // Update User
                $userData = [
                    'email' => $request->email,
                    'role'  => $request->role,
                ];
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                $user->update($userData);

                // Update Teacher
                $teacher->update([
                    'nik'              => $request->nik,
                    'nip'              => $request->nip,
                    'full_name'        => $request->full_name,
                    'gender'           => $request->gender,
                    'position_id'      => $request->position_id,
                    'work_schedule_id' => $request->work_schedule_id,
                    'phone'            => $request->phone,
                ]);
            });

            return redirect()->route('teachers.index')->with('success', "Data {$teacher->full_name} berhasil diperbarui!");

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    // Menghapus Data Guru & User
    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);

        try {
            DB::transaction(function () use ($teacher) {
                QrCode::where('teacher_id', $teacher->id)->delete();
                $userId = $teacher->user_id;
                $teacher->delete();
                if ($userId) {
                    User::where('id', $userId)->delete();
                }
            });

            return redirect()->route('teachers.index')->with('success', 'Data Pegawai beserta akun login berhasil dihapus!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // Regenerate QR Code
    public function regenerateQr($id)
    {
        $teacher = Teacher::findOrFail($id);

        try {
            DB::transaction(function () use ($teacher) {
                QrCode::where('teacher_id', $teacher->id)->update(['is_active' => false]);

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
    // public function printCard($id)
    // {
    //     $teacher = Teacher::with(['position', 'activeQrCode'])->findOrFail($id);

    //     return view('teachers.print-card', [
    //         'teachers' => collect([$teacher])
    //     ]);
    // }

    // Cetak ID Card Massal
    // public function printAllCards()
    // {
    //     $teachers = Teacher::with(['position', 'activeQrCode'])
    //         ->where('is_active', true)
    //         ->get();

    //     if ($teachers->isEmpty()) {
    //         return back()->with('error', 'Tidak ada data guru aktif untuk dicetak.');
    //     }

    //     return view('teachers.print-card', compact('teachers'));
    // }
}