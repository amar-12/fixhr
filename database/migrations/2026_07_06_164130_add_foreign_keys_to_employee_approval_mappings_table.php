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
        Schema::table('employee_approval_mappings', function (Blueprint $table) {
            $table->foreign(['eam_b_id'], 'fh_employee_approval_mappings_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['eam_module_id'], 'fh_employee_approval_mappings_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['eam_emp_id'], 'fh_employee_approval_mappings_ibfk_3')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['eam_approver_manager_1'], 'fh_employee_approval_mappings_ibfk_4')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['eam_approver_manager_2'], 'fh_employee_approval_mappings_ibfk_5')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_approval_mappings', function (Blueprint $table) {
            $table->dropForeign('fh_employee_approval_mappings_ibfk_1');
            $table->dropForeign('fh_employee_approval_mappings_ibfk_2');
            $table->dropForeign('fh_employee_approval_mappings_ibfk_3');
            $table->dropForeign('fh_employee_approval_mappings_ibfk_4');
            $table->dropForeign('fh_employee_approval_mappings_ibfk_5');
        });
    }
};
