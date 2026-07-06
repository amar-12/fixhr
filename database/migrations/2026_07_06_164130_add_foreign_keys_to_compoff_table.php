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
        Schema::table('compoff', function (Blueprint $table) {
            $table->foreign(['cop_id'], 'fk_fh_compoff_fh_compoff_policy')->references(['cop_id'])->on('compoff_policy')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compoff', function (Blueprint $table) {
            $table->dropForeign('fk_fh_compoff_fh_compoff_policy');
        });
    }
};
