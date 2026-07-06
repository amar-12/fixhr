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
        Schema::table('attendance_exceptions', function (Blueprint $table) {
            $table->foreign(['ae_emp_id'], 'fh_attendance_exceptions_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ae_ap_id'], 'fh_attendance_exceptions_ibfk_2')->references(['ap_id'])->on('policy_attendances')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ae_approved_by'], 'fh_attendance_exceptions_ibfk_3')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ae_type_id'], 'fh_attendance_exceptions_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ae_b_id'], 'fh_attendance_exceptions_ibfk_5')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ae_module_id'], 'fh_attendance_exceptions_ibfk_6')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ae_am_id'], 'fh_attendance_exceptions_ibfk_7')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_exceptions', function (Blueprint $table) {
            $table->dropForeign('fh_attendance_exceptions_ibfk_1');
            $table->dropForeign('fh_attendance_exceptions_ibfk_2');
            $table->dropForeign('fh_attendance_exceptions_ibfk_3');
            $table->dropForeign('fh_attendance_exceptions_ibfk_4');
            $table->dropForeign('fh_attendance_exceptions_ibfk_5');
            $table->dropForeign('fh_attendance_exceptions_ibfk_6');
            $table->dropForeign('fh_attendance_exceptions_ibfk_7');
        });
    }
};
