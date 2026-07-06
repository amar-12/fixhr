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
        Schema::table('tada_expense_settings', function (Blueprint $table) {
            $table->foreign(['tes_expense_type_id'], 'fh_tada_expense_settings_ibfk_1')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tada_expense_settings', function (Blueprint $table) {
            $table->dropForeign('fh_tada_expense_settings_ibfk_1');
        });
    }
};
