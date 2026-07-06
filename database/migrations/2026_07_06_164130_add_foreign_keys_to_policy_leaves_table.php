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
        Schema::table('policy_leaves', function (Blueprint $table) {
            $table->foreign(['pl_b_id'], 'fh_policy_leaves_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_leaves', function (Blueprint $table) {
            $table->dropForeign('fh_policy_leaves_ibfk_2');
        });
    }
};
