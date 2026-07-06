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
        Schema::table('menu_modules', function (Blueprint $table) {
            $table->foreign(['mm_menu_id'], 'fh_menu_modules_ibfk_1')->references(['menu_id'])->on('menus')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['mm_mdl_id'], 'fh_menu_modules_ibfk_2')->references(['mdl_id'])->on('modules')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_modules', function (Blueprint $table) {
            $table->dropForeign('fh_menu_modules_ibfk_1');
            $table->dropForeign('fh_menu_modules_ibfk_2');
        });
    }
};
