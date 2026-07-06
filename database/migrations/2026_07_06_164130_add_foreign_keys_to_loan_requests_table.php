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
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->foreign(['lnr_module_id'], 'fh_loan_requests_ibfk_1')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lnr_am_id'], 'fh_loan_requests_ibfk_2')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lnr_request_status'], 'fh_loan_requests_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lnr_b_id'], 'fh_loan_requests_ibfk_4')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lnr_emp_id'], 'fh_loan_requests_ibfk_5')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->dropForeign('fh_loan_requests_ibfk_1');
            $table->dropForeign('fh_loan_requests_ibfk_2');
            $table->dropForeign('fh_loan_requests_ibfk_3');
            $table->dropForeign('fh_loan_requests_ibfk_4');
            $table->dropForeign('fh_loan_requests_ibfk_5');
        });
    }
};
