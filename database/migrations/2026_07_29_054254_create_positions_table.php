<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->enum('role_type', [
                'guru', 'waka', 'kepala_sekolah', 'petugas', 'kepala_kompetensi', 'staff_waka'
            ]);
            $table->text('description')->nullable();
            $table->boolean('is_management')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};