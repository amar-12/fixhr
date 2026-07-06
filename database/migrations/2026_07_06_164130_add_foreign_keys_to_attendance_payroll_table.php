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
        Schema::table('attendance_payroll', function (Blueprint $table) {
            $table->foreign(['atdp_emp_id'], 'fh_attendance_payroll_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['atdp_pr_id'], 'fh_attendance_payroll_ibfk_2')->references(['pr_id'])->on('payroll_records')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['atdp_b_id'], 'fh_attendance_payroll_ibfk_3')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_payroll', function (Blueprint $table) {
            $table->dropForeign('fh_attendance_payroll_ibfk_1');
            $table->dropForeign('fh_attendance_payroll_ibfk_2');
            $table->dropForeign('fh_attendance_payroll_ibfk_3');
        });
    }
};
