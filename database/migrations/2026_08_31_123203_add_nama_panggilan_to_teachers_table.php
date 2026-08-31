<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Menambahkan kolom nama_panggilan (opsional/nullable) setelah kolom name
            $table->string('nama_panggilan', 50)->nullable()->after('full_name');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Untuk menghapus kolom jika migration di-rollback
            $table->dropColumn('nama_panggilan');
        });
    }
};
