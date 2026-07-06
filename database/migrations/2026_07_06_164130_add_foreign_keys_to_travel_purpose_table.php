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
        Schema::table('travel_purpose', function (Blueprint $table) {
            $table->foreign(['tp_b_id'], 'fh_travel_purpose_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tp_d_id'], 'fh_travel_purpose_ibfk_2')->references(['d_id'])->on('departments')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_purpose', function (Blueprint $table) {
            $table->dropForeign('fh_travel_purpose_ibfk_1');
            $table->dropForeign('fh_travel_purpose_ibfk_2');
        });
    }
};
