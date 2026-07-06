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
        Schema::table('tada_request_plan', function (Blueprint $table) {
            $table->foreign(['trp_b_id'], 'fh_tada_request_plan_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trp_tc_id'], 'fh_tada_request_plan_ibfk_10')->references(['tc_id'])->on('tada_claim')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trp_purpose'], 'fh_tada_request_plan_ibfk_11')->references(['tp_id'])->on('travel_purpose')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trp_br_id'], 'fh_tada_request_plan_ibfk_2')->references(['br_id'])->on('branches')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trp_pttt_id'], 'fh_tada_request_plan_ibfk_4')->references(['pttt_id'])->on('policy_tada_travel_type')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trp_ptc_id'], 'fh_tada_request_plan_ibfk_5')->references(['ptc_id'])->on('policy_tada_categories')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trp_emp_id'], 'fh_tada_request_plan_ibfk_7')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trp_module_id'], 'fh_tada_request_plan_ibfk_8')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trp_am_id'], 'fh_tada_request_plan_ibfk_9')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tada_request_plan', function (Blueprint $table) {
            $table->dropForeign('fh_tada_request_plan_ibfk_1');
            $table->dropForeign('fh_tada_request_plan_ibfk_10');
            $table->dropForeign('fh_tada_request_plan_ibfk_11');
            $table->dropForeign('fh_tada_request_plan_ibfk_2');
            $table->dropForeign('fh_tada_request_plan_ibfk_4');
            $table->dropForeign('fh_tada_request_plan_ibfk_5');
            $table->dropForeign('fh_tada_request_plan_ibfk_7');
            $table->dropForeign('fh_tada_request_plan_ibfk_8');
            $table->dropForeign('fh_tada_request_plan_ibfk_9');
        });
    }
};
