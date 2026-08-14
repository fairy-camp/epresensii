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

    // Import Massal Data Guru dari CSV (Native fgetcsv)
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ], [
            'csv_file.required' => 'Pilih file CSV terlebih dahulu!',
            'csv_file.mimes'    => 'Format file harus berupa CSV (.csv).'
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();

        // Deteksi Delimiter (Koma ',' atau Titik Koma ';')
        $delimiter = ',';
        $firstLine = file_get_contents($filePath, false, null, 0, 1000);
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        }

        $handle = fopen($filePath, "r");

        // Abaikan BOM (Byte Order Mark) jika ada pada file CSV dari Windows/Excel
        fseek($handle, 0);
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($handle, 0);
        }

        // Baca Header Baris Pertama
        $header = fgetcsv($handle, 1000, $delimiter);

        // Mapping Jabatan dan Jadwal Kerja ke Array (Case-Insensitive)
        $positions = Position::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])->toArray();
        $schedules = WorkSchedule::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])->toArray();

        $firstPositionId = array_key_first($positions) ? reset($positions) : null;
        $firstScheduleId = array_key_first($schedules) ? reset($schedules) : null;

        $importedCount = 0;
        $errors = [];
        $rowNumber = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $rowNumber++;

                // Abaikan baris kosong
                if (array_filter($row) == null) continue;

                // Pemetaan Kolom Berdasarkan Susunan CSV
                $fullName = trim($row[0] ?? '');
                $email    = trim($row[1] ?? '');
                $password = !empty($row[2]) ? trim($row[2]) : '12345678';
                $role     = !empty($row[3]) ? strtolower(trim($row[3])) : 'guru';
                $nip      = !empty($row[4]) ? trim($row[4]) : null;
                $nik      = !empty($row[5]) ? trim($row[5]) : null;
                $gender   = strtoupper(trim($row[6] ?? 'L'));
                $posName  = strtolower(trim($row[7] ?? ''));
                $schedName= strtolower(trim($row[8] ?? ''));
                $phone    = !empty($row[9]) ? trim($row[9]) : null;

                // Validasi Kolom Wajib
                if (empty($fullName) || empty($email)) {
                    $errors[] = "Baris #{$rowNumber}: Nama Lengkap dan Email tidak boleh kosong.";
                    continue;
                }

                // Cek Email Duplikat
                if (User::where('email', $email)->exists()) {
                    $errors[] = "Baris #{$rowNumber}: Email '{$email}' sudah terdaftar.";
                    continue;
                }

                // Cari ID Position & Schedule
                $posId   = $positions[$posName] ?? $firstPositionId;
                $schedId = $schedules[$schedName] ?? $firstScheduleId;

                // 1. Buat User Account
                $user = User::create([
                    'email'     => $email,
                    'password'  => Hash::make($password),
                    'role'      => in_array($role, ['guru', 'kepala_sekolah', 'waka', 'satpam', 'staff', 'petugas']) ? $role : 'guru',
                    'is_active' => true,
                ]);

                // 2. Buat Data Teacher
                $teacher = Teacher::create([
                    'user_id'          => $user->id,
                    'full_name'        => $fullName,
                    'nip'              => $nip,
                    'nik'              => $nik,
                    'gender'           => in_array($gender, ['L', 'P']) ? $gender : 'L',
                    'phone'            => $phone,
                    'position_id'      => $posId,
                    'work_schedule_id' => $schedId,
                    'is_active'        => true,
                ]);

                // 3. Generate QR Code Otomatis
                QrCode::create([
                    'teacher_id' => $teacher->id,
                    'code'       => 'QR-' . strtoupper(Str::random(10)) . '-' . time(),
                    'is_active'  => true,
                ]);

                $importedCount++;
            }

            fclose($handle);

            if ($importedCount === 0 && !empty($errors)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal mengimpor data: <br>' . implode('<br>', array_slice($errors, 0, 5)));
            }

            DB::commit();

            $message = "Berhasil mengimpor {$importedCount} data pegawai beserta QR Code!";
            if (!empty($errors)) {
                $message .= "<br>Beberapa baris dilewati karena duplikasi/data kosong: <br>" . implode('<br>', array_slice($errors, 0, 3));
            }

            return redirect()->route('teachers.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle)) fclose($handle);
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat membaca file CSV: ' . $e->getMessage());
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
}