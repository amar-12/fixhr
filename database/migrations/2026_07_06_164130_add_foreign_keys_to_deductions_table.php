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
        Schema::table('deductions', function (Blueprint $table) {
            $table->foreign(['deduct_pr_id'], 'fh_deductions_ibfk_1')->references(['pr_id'])->on('payroll_records')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['deduct_b_id'], 'fh_deductions_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deductions', function (Blueprint $table) {
            $table->dropForeign('fh_deductions_ibfk_1');
            $table->dropForeign('fh_deductions_ibfk_2');
        });
    }
};
