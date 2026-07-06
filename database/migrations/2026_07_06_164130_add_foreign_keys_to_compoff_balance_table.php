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
        Schema::table('compoff_balance', function (Blueprint $table) {
            $table->foreign(['cb_b_id'], 'fk_fh_compoff_balance_fh_businesses')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['cb_emp_id'], 'fk_fh_compoff_balance_fh_employees')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compoff_balance', function (Blueprint $table) {
            $table->dropForeign('fk_fh_compoff_balance_fh_businesses');
            $table->dropForeign('fk_fh_compoff_balance_fh_employees');
        });
    }
};
