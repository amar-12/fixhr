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
        Schema::table('rule_criteria', function (Blueprint $table) {
            $table->foreign(['rc_b_id'], 'fh_rule_criteria_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rc_am_id'], 'fh_rule_criteria_ibfk_2')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rc_approval_rule_id'], 'fh_rule_criteria_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rc_rule_condition_id'], 'fh_rule_criteria_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rc_condition_option_id'], 'fh_rule_criteria_ibfk_5')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rule_criteria', function (Blueprint $table) {
            $table->dropForeign('fh_rule_criteria_ibfk_1');
            $table->dropForeign('fh_rule_criteria_ibfk_2');
            $table->dropForeign('fh_rule_criteria_ibfk_3');
            $table->dropForeign('fh_rule_criteria_ibfk_4');
            $table->dropForeign('fh_rule_criteria_ibfk_5');
        });
    }
};
