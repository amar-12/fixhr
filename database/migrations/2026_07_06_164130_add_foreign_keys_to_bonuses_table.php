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
        Schema::table('bonuses', function (Blueprint $table) {
            $table->foreign(['bonus_emp_id'], 'fh_bonuses_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['bonus_b_id'], 'fh_bonuses_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropForeign('fh_bonuses_ibfk_1');
            $table->dropForeign('fh_bonuses_ibfk_2');
        });
    }
};
