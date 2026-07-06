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
        Schema::table('tada_expenses', function (Blueprint $table) {
            $table->foreign(['te_trp_id'], 'fh_tada_expenses_ibfk_1')->references(['trp_id'])->on('tada_request_plan')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['te_type_id'], 'fh_tada_expenses_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['te_pttm_id'], 'fh_tada_expenses_ibfk_3')->references(['pttm_id'])->on('policy_tada_travel_mode')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['te_pttv_id'], 'fh_tada_expenses_ibfk_4')->references(['pttv_id'])->on('policy_tada_travel_vehicle')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tada_expenses', function (Blueprint $table) {
            $table->dropForeign('fh_tada_expenses_ibfk_1');
            $table->dropForeign('fh_tada_expenses_ibfk_2');
            $table->dropForeign('fh_tada_expenses_ibfk_3');
            $table->dropForeign('fh_tada_expenses_ibfk_4');
        });
    }
};
