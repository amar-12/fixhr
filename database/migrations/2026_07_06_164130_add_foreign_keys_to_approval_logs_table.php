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
        Schema::table('approval_logs', function (Blueprint $table) {
            $table->foreign(['log_am_id'], 'fh_approval_logs_ibfk_1')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['log_status'], 'fh_approval_logs_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['log_user_id'], 'fh_approval_logs_ibfk_3')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['log_user_role_id'], 'fh_approval_logs_ibfk_4')->references(['role_id'])->on('roles')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['log_module_id'], 'fh_approval_logs_ibfk_5')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_logs', function (Blueprint $table) {
            $table->dropForeign('fh_approval_logs_ibfk_1');
            $table->dropForeign('fh_approval_logs_ibfk_2');
            $table->dropForeign('fh_approval_logs_ibfk_3');
            $table->dropForeign('fh_approval_logs_ibfk_4');
            $table->dropForeign('fh_approval_logs_ibfk_5');
        });
    }
};
