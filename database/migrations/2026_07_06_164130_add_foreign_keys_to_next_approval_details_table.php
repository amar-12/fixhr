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
        Schema::table('next_approval_details', function (Blueprint $table) {
            $table->foreign(['nxt_tc_id'], 'fh_next_approval_details_ibfk_1')->references(['tc_id'])->on('tada_claim')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('next_approval_details', function (Blueprint $table) {
            $table->dropForeign('fh_next_approval_details_ibfk_1');
        });
    }
};
