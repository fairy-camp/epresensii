<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->time('start_check_in_time')->nullable()->after('check_in_time');
            $table->time('end_check_in_time')->nullable()->after('start_check_in_time');
            $table->time('start_check_out_time')->nullable()->after('check_out_time');
            $table->time('end_check_out_time')->nullable()->after('start_check_out_time');
        });
    }

    public function down(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropColumn(['start_check_in_time', 'end_check_in_time', 'start_check_out_time', 'end_check_out_time']);
        });
    }
};