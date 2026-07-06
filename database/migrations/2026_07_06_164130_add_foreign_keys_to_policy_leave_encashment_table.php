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
        Schema::table('policy_leave_encashment', function (Blueprint $table) {
            $table->foreign(['lep_b_id'], 'fh_policy_leave_encashment_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_leave_encashment', function (Blueprint $table) {
            $table->dropForeign('fh_policy_leave_encashment_ibfk_1');
        });
    }
};
