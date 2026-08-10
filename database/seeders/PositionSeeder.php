<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            [
                'name' => 'Kepala Sekolah',
                'role_type' => 'kepala_sekolah',
                'description' => 'Kepala Sekolah',
                'is_management' => true,
            ],
            [
                'name' => 'Waka',
                'role_type' => 'waka',
                'description' => 'Wakil Kepala Sekolah',
                'is_management' => true,
            ],
            [
                'name' => 'Guru Pengajar',
                'role_type' => 'guru',
                'description' => 'Guru Mata Pelajaran',
                'is_management' => false,
            ],
            [
                'name' => 'Staff dan Karyawan',
                'role_type' => 'staff',
                'description' => 'Staff dan Karyawan',
                'is_management' => false,
            ],
            [
                'name' => 'Satpam / Security',
                'role_type' => 'satpam',
                'description' => 'Petugas Keamanan Sekolah',
                'is_management' => false,
            ],
            [
                'name' => 'Petugas Presensi',
                'role_type' => 'petugas',
                'description' => 'Petugas Presensi',
                'is_management' => false,
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}