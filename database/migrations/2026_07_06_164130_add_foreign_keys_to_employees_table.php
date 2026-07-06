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
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign(['emp_attendance_preference'])->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_b_id'], 'fh_employees_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_cast_id'], 'fh_employees_ibfk_10')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_blood_group_id'], 'fh_employees_ibfk_11')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_religion_id'], 'fh_employees_ibfk_13')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_br_id'], 'fh_employees_ibfk_2')->references(['br_id'])->on('branches')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_grade_id'], 'fh_employees_ibfk_20')->references(['g_id'])->on('grades')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_shift_type_id'], 'fh_employees_ibfk_21')->references(['pst_id'])->on('policy_shift_timings')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_work_mode_id'], 'fh_employees_ibfk_22')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_reporting_manager_id'], 'fh_employees_ibfk_23')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_gov_doc_type_id'], 'fh_employees_ibfk_24')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_type_id'], 'fh_employees_ibfk_25')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_contractual_type_id'], 'fh_employees_ibfk_26')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_ssp_id'], 'fh_employees_ibfk_27')->references(['ssp_id'])->on('policy_salary_structure')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_pb_id'], 'fh_employees_ibfk_29')->references(['pb_id'])->on('policy_bonus')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_d_id'], 'fh_employees_ibfk_3')->references(['d_id'])->on('departments')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_pd_id'], 'fh_employees_ibfk_30')->references(['pd_id'])->on('policy_deduction')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_ap_id'], 'fh_employees_ibfk_32')->references(['ap_id'])->on('policy_attendances')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_ap_id'], 'fh_employees_ibfk_33')->references(['ap_id'])->on('policy_attendances')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_work_mode_id'], 'fh_employees_ibfk_34')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_pl_id'], 'fh_employees_ibfk_35')->references(['pl_id'])->on('policy_leaves')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_prefix'], 'fh_employees_ibfk_36')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_dg_id'], 'fh_employees_ibfk_4')->references(['dg_id'])->on('designations')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_role_id'], 'fh_employees_ibfk_5')->references(['role_id'])->on('roles')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_type_id'], 'fh_employees_ibfk_6')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_contractual_type_id'], 'fh_employees_ibfk_7')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_gender_id'], 'fh_employees_ibfk_8')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['emp_marital_status_id'], 'fh_employees_ibfk_9')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign('fh_employees_emp_attendance_preference_foreign');
            $table->dropForeign('fh_employees_ibfk_1');
            $table->dropForeign('fh_employees_ibfk_10');
            $table->dropForeign('fh_employees_ibfk_11');
            $table->dropForeign('fh_employees_ibfk_13');
            $table->dropForeign('fh_employees_ibfk_2');
            $table->dropForeign('fh_employees_ibfk_20');
            $table->dropForeign('fh_employees_ibfk_21');
            $table->dropForeign('fh_employees_ibfk_22');
            $table->dropForeign('fh_employees_ibfk_23');
            $table->dropForeign('fh_employees_ibfk_24');
            $table->dropForeign('fh_employees_ibfk_25');
            $table->dropForeign('fh_employees_ibfk_26');
            $table->dropForeign('fh_employees_ibfk_27');
            $table->dropForeign('fh_employees_ibfk_29');
            $table->dropForeign('fh_employees_ibfk_3');
            $table->dropForeign('fh_employees_ibfk_30');
            $table->dropForeign('fh_employees_ibfk_32');
            $table->dropForeign('fh_employees_ibfk_33');
            $table->dropForeign('fh_employees_ibfk_34');
            $table->dropForeign('fh_employees_ibfk_35');
            $table->dropForeign('fh_employees_ibfk_36');
            $table->dropForeign('fh_employees_ibfk_4');
            $table->dropForeign('fh_employees_ibfk_5');
            $table->dropForeign('fh_employees_ibfk_6');
            $table->dropForeign('fh_employees_ibfk_7');
            $table->dropForeign('fh_employees_ibfk_8');
            $table->dropForeign('fh_employees_ibfk_9');
        });
    }
};
