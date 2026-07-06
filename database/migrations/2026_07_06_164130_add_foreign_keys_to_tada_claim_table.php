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
        Schema::table('tada_claim', function (Blueprint $table) {
            $table->foreign(['tc_status'], 'fh_tada_claim_ibfk_1')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tc_emp_id'], 'fh_tada_claim_ibfk_3')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tc_module_id'], 'fh_tada_claim_ibfk_6')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tc_b_id'], 'fh_tada_claim_ibfk_7')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tc_am_id'], 'fh_tada_claim_ibfk_8')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tada_claim', function (Blueprint $table) {
            $table->dropForeign('fh_tada_claim_ibfk_1');
            $table->dropForeign('fh_tada_claim_ibfk_3');
            $table->dropForeign('fh_tada_claim_ibfk_6');
            $table->dropForeign('fh_tada_claim_ibfk_7');
            $table->dropForeign('fh_tada_claim_ibfk_8');
        });
    }
};
