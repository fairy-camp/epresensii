<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        SchoolSetting::create([
            'school_name' => 'SMK UP RPL CodePelita',
            'latitude' => -6.17539200, // Contoh Koordinat (Bisa diubah via UI Admin nanti)
            'longitude' => 106.82715300,
            'geofence_radius' => 100, // Radius 100 meter
        ]);
    }
}