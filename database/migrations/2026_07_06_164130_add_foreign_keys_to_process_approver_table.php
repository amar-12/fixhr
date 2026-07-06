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
        Schema::table('process_approver', function (Blueprint $table) {
            $table->foreign(['pa_am_id'], 'fh_process_approver_ibfk_1')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pa_role_id'], 'fh_process_approver_ibfk_2')->references(['role_id'])->on('roles')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pa_emp_id'], 'fh_process_approver_ibfk_3')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pa_status_id'], 'fh_process_approver_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pa_d_id'], 'fh_process_approver_ibfk_5')->references(['d_id'])->on('departments')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_approver', function (Blueprint $table) {
            $table->dropForeign('fh_process_approver_ibfk_1');
            $table->dropForeign('fh_process_approver_ibfk_2');
            $table->dropForeign('fh_process_approver_ibfk_3');
            $table->dropForeign('fh_process_approver_ibfk_4');
            $table->dropForeign('fh_process_approver_ibfk_5');
        });
    }
};
