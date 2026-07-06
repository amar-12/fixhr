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
        Schema::table('policy_tada_categories', function (Blueprint $table) {
            $table->foreign(['ptc_b_id'], 'fh_policy_tada_categories_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ptc_grade_id'], 'fh_policy_tada_categories_ibfk_3')->references(['g_id'])->on('grades')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ptc_d_id'], 'fh_policy_tada_categories_ibfk_4')->references(['d_id'])->on('departments')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_tada_categories', function (Blueprint $table) {
            $table->dropForeign('fh_policy_tada_categories_ibfk_1');
            $table->dropForeign('fh_policy_tada_categories_ibfk_3');
            $table->dropForeign('fh_policy_tada_categories_ibfk_4');
        });
    }
};
