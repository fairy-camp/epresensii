<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin (Hidden, tidak muncul di UI User Management)
        User::create([
            'email' => 'superadmin@codepelita.sch.id',
            'password' => Hash::make('SuperAdmin123!'),
            'role' => 'super_admin',
            'role_keterangan' => 'Super Admin System',
            'is_hidden' => true,
            'is_active' => true,
        ]);

        // 2. Admin Utama (Tampil di User Management)
        User::create([
            'email' => 'admin@codepelita.sch.id',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin',
            'role_keterangan' => 'Administrator Sekolah',
            'is_hidden' => false,
            'is_active' => true,
        ]);
    }
}