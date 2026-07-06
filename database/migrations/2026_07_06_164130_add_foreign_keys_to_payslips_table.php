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
        Schema::table('payslips', function (Blueprint $table) {
            $table->foreign(['p_pr_id'], 'fh_payslips_ibfk_1')->references(['pr_id'])->on('payroll_records')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['p_b_id'], 'fh_payslips_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropForeign('fh_payslips_ibfk_1');
            $table->dropForeign('fh_payslips_ibfk_2');
        });
    }
};
