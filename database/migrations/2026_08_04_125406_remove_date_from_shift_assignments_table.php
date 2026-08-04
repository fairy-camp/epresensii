<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            // Hapus kolom date jika sudah tidak dipakai
            $table->dropColumn('date');
            
            // Buat teacher_id menjadi unique agar 1 guru hanya punya 1 shift permanen
            $table->unique('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->date('date')->nullable();
            $table->dropUnique(['teacher_id']);
        });
    }
};