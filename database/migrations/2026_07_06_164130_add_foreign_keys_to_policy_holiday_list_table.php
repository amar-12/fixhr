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
        Schema::table('policy_holiday_list', function (Blueprint $table) {
            $table->foreign(['phl_b_id'], 'fh_policy_holiday_list_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['phl_type_id'], 'fh_policy_holiday_list_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_holiday_list', function (Blueprint $table) {
            $table->dropForeign('fh_policy_holiday_list_ibfk_2');
            $table->dropForeign('fh_policy_holiday_list_ibfk_3');
        });
    }
};
