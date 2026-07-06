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
        Schema::table('policy_tada_travel_type', function (Blueprint $table) {
            $table->foreign(['pttt_b_id'], 'fh_policy_tada_travel_type_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pttt_type_id'], 'fh_policy_tada_travel_type_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pttt_approval_type_id'], 'fh_policy_tada_travel_type_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_tada_travel_type', function (Blueprint $table) {
            $table->dropForeign('fh_policy_tada_travel_type_ibfk_1');
            $table->dropForeign('fh_policy_tada_travel_type_ibfk_2');
            $table->dropForeign('fh_policy_tada_travel_type_ibfk_3');
        });
    }
};
