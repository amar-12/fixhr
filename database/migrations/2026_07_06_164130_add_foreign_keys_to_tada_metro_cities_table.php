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
        Schema::table('tada_metro_cities', function (Blueprint $table) {
            $table->foreign(['ctm_b_id'], 'fh_tada_metro_cities_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tada_metro_cities', function (Blueprint $table) {
            $table->dropForeign('fh_tada_metro_cities_ibfk_1');
        });
    }
};
