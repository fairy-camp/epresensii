<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // Hapus unique index lama yang bermasalah
            $table->dropUnique('attendance_records_teacher_id_shift_assignment_id_unique');

            // Buat unique index baru yang menyertakan kolom tanggal (date)
            $table->unique(['teacher_id', 'shift_assignment_id', 'date'], 'att_teacher_shift_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropUnique('att_teacher_shift_date_unique');
            $table->unique(['teacher_id', 'shift_assignment_id']);
        });
    }
};