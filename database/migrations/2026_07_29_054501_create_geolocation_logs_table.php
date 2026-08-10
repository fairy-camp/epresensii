<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geolocation_logs', function (Blueprint $table) {
            $table->id();
            // attendance_id dibuat nullable agar tetap mencatat log saat presensi ditolak
            $table->foreignId('attendance_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('accuracy_meters', 6, 2)->nullable();
            $table->decimal('distance_from_school', 10, 2)->nullable();
            $table->boolean('is_within_radius')->default(false);
            $table->enum('permission_status', ['granted', 'denied', 'unavailable']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geolocation_logs');
    }
};