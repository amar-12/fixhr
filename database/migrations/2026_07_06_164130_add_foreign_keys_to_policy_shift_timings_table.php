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
        Schema::table('policy_shift_timings', function (Blueprint $table) {
            $table->foreign(['pst_ap_id'], 'fh_policy_shift_timings_ibfk_1')->references(['ap_id'])->on('policy_attendances')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pst_b_id'], 'fh_policy_shift_timings_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pst_type_id'], 'fh_policy_shift_timings_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_shift_timings', function (Blueprint $table) {
            $table->dropForeign('fh_policy_shift_timings_ibfk_1');
            $table->dropForeign('fh_policy_shift_timings_ibfk_2');
            $table->dropForeign('fh_policy_shift_timings_ibfk_3');
        });
    }
};
