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
        Schema::table('employee_approval_status', function (Blueprint $table) {
            $table->foreign(['eas_eam_id'], 'fh_employee_approval_status')->references(['eam_id'])->on('employee_approval_mappings')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['eas_approvel_status'], 'fh_employee_approval_status_ibfk_1')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_approval_status', function (Blueprint $table) {
            $table->dropForeign('fh_employee_approval_status');
            $table->dropForeign('fh_employee_approval_status_ibfk_1');
        });
    }
};
