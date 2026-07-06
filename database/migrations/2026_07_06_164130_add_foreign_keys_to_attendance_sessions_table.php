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
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->foreign(['ads_emp_id'], 'fh_attendance_sessions_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ads_b_id'], 'fh_attendance_sessions_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ads_atd_id'], 'fh_attendance_sessions_ibfk_3')->references(['atd_id'])->on('attendance_records')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign('fh_attendance_sessions_ibfk_1');
            $table->dropForeign('fh_attendance_sessions_ibfk_2');
            $table->dropForeign('fh_attendance_sessions_ibfk_3');
        });
    }
};
