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
        Schema::table('miscellaneous', function (Blueprint $table) {
            $table->foreign(['mis_b_id'], 'fh_miscellaneous_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('miscellaneous', function (Blueprint $table) {
            $table->dropForeign('fh_miscellaneous_ibfk_1');
        });
    }
};
