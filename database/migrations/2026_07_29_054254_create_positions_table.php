<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->enum('role_type', [
                'kepala_sekolah', 'waka', 'guru', 'petugas', 'satpam', 'staff'
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