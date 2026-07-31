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
                'description' => 'Kepala Sekolah Utama',
                'is_management' => true,
            ],
            [
                'name' => 'Waka Kurikulum',
                'role_type' => 'waka',
                'description' => 'Wakil Kepala Sekolah Bidang Kurikulum',
                'is_management' => true,
            ],
            [
                'name' => 'Waka Kesiswaan',
                'role_type' => 'waka',
                'description' => 'Wakil Kepala Sekolah Bidang Kesiswaan',
                'is_management' => true,
            ],
            [
                'name' => 'Waka Sarana Prasarana',
                'role_type' => 'waka',
                'description' => 'Wakil Kepala Sekolah Bidang Sarpras',
                'is_management' => true,
            ],
            [
                'name' => 'Waka Humas',
                'role_type' => 'waka',
                'description' => 'Wakil Kepala Sekolah Bidang Hubungan Masyarakat',
                'is_management' => true,
            ],
            [
                'name' => 'Kepala Kompetensi RPL',
                'role_type' => 'kepala_kompetensi',
                'description' => 'Kepala Program Keahlian RPL',
                'is_management' => true,
            ],
            [
                'name' => 'Guru Pengajar',
                'role_type' => 'guru',
                'description' => 'Guru Mata Pelajaran',
                'is_management' => false,
            ],
            [
                'name' => 'Staff Waka Kurikulum',
                'role_type' => 'staff_waka',
                'description' => 'Guru Pembantu Waka Kurikulum',
                'is_management' => false,
            ],
            [
                'name' => 'Satpam / Security',
                'role_type' => 'petugas',
                'description' => 'Petugas Keamanan Sekolah',
                'is_management' => false,
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}