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
        Schema::table('approval_modules', function (Blueprint $table) {
            $table->foreign(['am_module_id'], 'fh_approval_modules_ibfk_1')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['am_b_id'], 'fh_approval_modules_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_modules', function (Blueprint $table) {
            $table->dropForeign('fh_approval_modules_ibfk_1');
            $table->dropForeign('fh_approval_modules_ibfk_2');
        });
    }
};
