<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nip', 20)->nullable()->unique();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('nuptk', 16)->nullable();
            $table->string('npy', 20)->nullable();
            $table->string('full_name');
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('photo')->nullable();
            $table->string('department', 100)->nullable();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('waka_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->date('tmt')->nullable();
            $table->string('phone', 15)->nullable();
            $table->foreignId('work_schedule_id')->nullable()->constrained('work_schedules')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};