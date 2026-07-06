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
        Schema::table('policy_attendance_bonuses', function (Blueprint $table) {
            $table->foreign(['pab_ap_id'], 'fh_policy_attendance_bonuses_ibfk_1')->references(['ap_id'])->on('policy_attendances')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pab_b_id'], 'fh_policy_attendance_bonuses_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_attendance_bonuses', function (Blueprint $table) {
            $table->dropForeign('fh_policy_attendance_bonuses_ibfk_1');
            $table->dropForeign('fh_policy_attendance_bonuses_ibfk_2');
        });
    }
};
