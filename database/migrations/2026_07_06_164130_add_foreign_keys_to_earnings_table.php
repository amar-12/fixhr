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
        Schema::table('earnings', function (Blueprint $table) {
            $table->foreign(['earn_pr_id'], 'fh_earnings_ibfk_1')->references(['pr_id'])->on('payroll_records')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['earn_b_id'], 'fh_earnings_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('earnings', function (Blueprint $table) {
            $table->dropForeign('fh_earnings_ibfk_1');
            $table->dropForeign('fh_earnings_ibfk_2');
        });
    }
};
