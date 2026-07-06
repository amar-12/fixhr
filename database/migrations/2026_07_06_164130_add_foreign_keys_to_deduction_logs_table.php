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
        Schema::table('deduction_logs', function (Blueprint $table) {
            $table->foreign(['dlog_tc_id'], 'fh_deduction_logs_ibfk_1')->references(['tc_id'])->on('tada_claim')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['dlog_user_id'], 'fh_deduction_logs_ibfk_2')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['dlog_user_role_id'], 'fh_deduction_logs_ibfk_3')->references(['role_id'])->on('roles')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['dlog_am_id'], 'fh_deduction_logs_ibfk_4')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deduction_logs', function (Blueprint $table) {
            $table->dropForeign('fh_deduction_logs_ibfk_1');
            $table->dropForeign('fh_deduction_logs_ibfk_2');
            $table->dropForeign('fh_deduction_logs_ibfk_3');
            $table->dropForeign('fh_deduction_logs_ibfk_4');
        });
    }
};
