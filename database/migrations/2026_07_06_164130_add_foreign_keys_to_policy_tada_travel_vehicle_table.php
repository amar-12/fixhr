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
        Schema::table('policy_tada_travel_vehicle', function (Blueprint $table) {
            $table->foreign(['pttv_pttm_id'], 'fh_policy_tada_travel_vehicle_ibfk_1')->references(['pttm_id'])->on('policy_tada_travel_mode')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pttv_vehicle_id'], 'fh_policy_tada_travel_vehicle_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pttv_owner_id'], 'fh_policy_tada_travel_vehicle_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pttv_class_id'], 'fh_policy_tada_travel_vehicle_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pttv_b_id'], 'fh_policy_tada_travel_vehicle_ibfk_5')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pttv_claim_type_id'], 'fh_policy_tada_travel_vehicle_ibfk_6')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_tada_travel_vehicle', function (Blueprint $table) {
            $table->dropForeign('fh_policy_tada_travel_vehicle_ibfk_1');
            $table->dropForeign('fh_policy_tada_travel_vehicle_ibfk_2');
            $table->dropForeign('fh_policy_tada_travel_vehicle_ibfk_3');
            $table->dropForeign('fh_policy_tada_travel_vehicle_ibfk_4');
            $table->dropForeign('fh_policy_tada_travel_vehicle_ibfk_5');
            $table->dropForeign('fh_policy_tada_travel_vehicle_ibfk_6');
        });
    }
};
