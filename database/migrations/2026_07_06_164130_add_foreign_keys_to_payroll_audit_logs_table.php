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
        Schema::table('payroll_audit_logs', function (Blueprint $table) {
            $table->foreign(['pal_pr_id'], 'fh_payroll_audit_logs_ibfk_1')->references(['pr_id'])->on('payroll_records')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pal_created_by'], 'fh_payroll_audit_logs_ibfk_2')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pal_b_id'], 'fh_payroll_audit_logs_ibfk_3')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_audit_logs', function (Blueprint $table) {
            $table->dropForeign('fh_payroll_audit_logs_ibfk_1');
            $table->dropForeign('fh_payroll_audit_logs_ibfk_2');
            $table->dropForeign('fh_payroll_audit_logs_ibfk_3');
        });
    }
};
