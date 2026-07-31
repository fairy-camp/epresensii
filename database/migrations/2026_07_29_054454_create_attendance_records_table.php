<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignUuid('shift_assignment_id')->constrained('shift_assignments')->cascadeOnDelete();
            $table->date('date')->index();
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable(); // DATETIME agar shift malam lintas hari akurat
            $table->enum('status', ['present', 'late', 'absent', 'early_leave']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Constraint agar 1 pegawai hanya punya 1 record per shift assignment
            $table->unique(['teacher_id', 'shift_assignment_id']);
            $table->index(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};