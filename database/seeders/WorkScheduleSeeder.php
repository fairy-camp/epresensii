<?php

namespace Database\Seeders;

use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            [
                'name' => 'Reguler (Guru & Staff)',
                'type' => 'fixed',
                'check_in_time' => '06:45:00',
                'check_out_time' => '14:00:00',
                'late_tolerance_minutes' => 15, // Toleransi 15 menit
                'is_active' => true,
            ],
            [
                'name' => 'Shift Pagi (Satpam)',
                'type' => 'shift',
                'check_in_time' => '06:00:00',
                'check_out_time' => '14:00:00',
                'late_tolerance_minutes' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Shift Siang (Satpam)',
                'type' => 'shift',
                'check_in_time' => '14:00:00',
                'check_out_time' => '22:00:00',
                'late_tolerance_minutes' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Shift Malam (Satpam)',
                'type' => 'shift',
                'check_in_time' => '22:00:00',
                'check_out_time' => '06:00:00', // Lintas hari
                'late_tolerance_minutes' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($schedules as $schedule) {
            WorkSchedule::create($schedule);
        }
    }
}