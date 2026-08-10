<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apel_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->date('date');                          // Tanggal pelaksanaan apel
            $table->time('scan_time');                     // Waktu/Jam saat scan dilakukan
            $table->enum('status', ['present', 'late', 'absent'])->default('present'); // present: tepat waktu (<= 07:00), late: terlambat
            // $table->string('latitude')->nullable();
            // $table->string('longitude')->nullable();
            $table->text('notes')->nullable();             // Catatan opsional
            $table->timestamps();

            // Mencegah guru/karyawan melakukan scan apel lebih dari 1 kali di hari yang sama
            $table->unique(['teacher_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apel_attendances');
    }
};