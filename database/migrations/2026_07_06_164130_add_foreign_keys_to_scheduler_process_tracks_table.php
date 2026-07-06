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
        Schema::table('scheduler_process_tracks', function (Blueprint $table) {
            $table->foreign(['spt_b_id'], 'fh_scheduler_process_tracks_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduler_process_tracks', function (Blueprint $table) {
            $table->dropForeign('fh_scheduler_process_tracks_ibfk_1');
        });
    }
};
