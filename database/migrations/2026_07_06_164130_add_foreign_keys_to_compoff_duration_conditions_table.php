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
        Schema::table('compoff_duration_conditions', function (Blueprint $table) {
            $table->foreign(['condition_b_id'], 'fk_fh_compoff_duration_conditions_fh_businesses')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['cop_id'], 'fk_fh_compoff_duration_conditions_fh_compoff_policy')->references(['cop_id'])->on('compoff_policy')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compoff_duration_conditions', function (Blueprint $table) {
            $table->dropForeign('fk_fh_compoff_duration_conditions_fh_businesses');
            $table->dropForeign('fk_fh_compoff_duration_conditions_fh_compoff_policy');
        });
    }
};
