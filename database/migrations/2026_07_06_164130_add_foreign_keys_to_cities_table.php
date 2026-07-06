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
        Schema::table('cities', function (Blueprint $table) {
            $table->foreign(['ct_s_id'], 'fh_cities_ibfk_1')->references(['s_id'])->on('states')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ct_type_id'], 'fh_cities_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign('fh_cities_ibfk_1');
            $table->dropForeign('fh_cities_ibfk_2');
        });
    }
};
