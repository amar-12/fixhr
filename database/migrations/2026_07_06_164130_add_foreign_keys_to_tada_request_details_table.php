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
        Schema::table('tada_request_details', function (Blueprint $table) {
            $table->foreign(['trd_trp_id'], 'fh_tada_request_details_ibfk_1')->references(['trp_id'])->on('tada_request_plan')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['trd_type_id'], 'fh_tada_request_details_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tada_request_details', function (Blueprint $table) {
            $table->dropForeign('fh_tada_request_details_ibfk_1');
            $table->dropForeign('fh_tada_request_details_ibfk_2');
        });
    }
};
