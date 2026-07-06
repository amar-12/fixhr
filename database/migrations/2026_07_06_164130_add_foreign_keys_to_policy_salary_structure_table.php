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
        Schema::table('policy_salary_structure', function (Blueprint $table) {
            $table->foreign(['ssp_pg_id'], 'fh_policy_salary_structure_ibfk_1')->references(['pg_id'])->on('pay_grades')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ssp_b_id'], 'fh_policy_salary_structure_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_salary_structure', function (Blueprint $table) {
            $table->dropForeign('fh_policy_salary_structure_ibfk_1');
            $table->dropForeign('fh_policy_salary_structure_ibfk_2');
        });
    }
};
