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
        Schema::table('payroll_master_settings', function (Blueprint $table) {
            $table->foreign(['pms_b_id'], 'fh_payroll_master_settings_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pms_payroll_cycle'], 'fh_payroll_master_settings_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_master_settings', function (Blueprint $table) {
            $table->dropForeign('fh_payroll_master_settings_ibfk_1');
            $table->dropForeign('fh_payroll_master_settings_ibfk_2');
        });
    }
};
