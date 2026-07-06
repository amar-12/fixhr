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
        Schema::table('policy_tada_lodging', function (Blueprint $table) {
            $table->foreign(['ptl_b_id'], 'fh_policy_tada_lodging_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ptl_ptc_id'], 'fh_policy_tada_lodging_ibfk_2')->references(['ptc_id'])->on('policy_tada_categories')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ptl_pttt_id'], 'fh_policy_tada_lodging_ibfk_3')->references(['pttt_id'])->on('policy_tada_travel_type')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ptl_ct_type_id'], 'fh_policy_tada_lodging_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_tada_lodging', function (Blueprint $table) {
            $table->dropForeign('fh_policy_tada_lodging_ibfk_1');
            $table->dropForeign('fh_policy_tada_lodging_ibfk_2');
            $table->dropForeign('fh_policy_tada_lodging_ibfk_3');
            $table->dropForeign('fh_policy_tada_lodging_ibfk_4');
        });
    }
};
