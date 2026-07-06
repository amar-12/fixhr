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
        Schema::table('policy_overtime_rules', function (Blueprint $table) {
            $table->foreign(['por_ap_id'], 'fh_policy_overtime_rules_ibfk_1')->references(['ap_id'])->on('policy_attendances')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_overtime_rules', function (Blueprint $table) {
            $table->dropForeign('fh_policy_overtime_rules_ibfk_1');
        });
    }
};
