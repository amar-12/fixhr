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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreign(['atd_emp_id'], 'fh_attendance_records_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['atd_pst_id'], 'fh_attendance_records_ibfk_2')->references(['pst_id'])->on('policy_shift_timings')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['atd_b_id'], 'fh_attendance_records_ibfk_3')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['atd_attendance_status'], 'fh_attendance_records_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['atd_work_mode_type_id'], 'fh_attendance_records_ibfk_5')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['atd_checkin_method_id'], 'fh_attendance_records_ibfk_6')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['atd_updated_by'], 'fh_attendance_records_ibfk_7')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign('fh_attendance_records_ibfk_1');
            $table->dropForeign('fh_attendance_records_ibfk_2');
            $table->dropForeign('fh_attendance_records_ibfk_3');
            $table->dropForeign('fh_attendance_records_ibfk_4');
            $table->dropForeign('fh_attendance_records_ibfk_5');
            $table->dropForeign('fh_attendance_records_ibfk_6');
            $table->dropForeign('fh_attendance_records_ibfk_7');
        });
    }
};
