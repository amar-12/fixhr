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
        Schema::table('pay_grades', function (Blueprint $table) {
            $table->foreign(['pg_grade_id'], 'fh_pay_grades_ibfk_1')->references(['g_id'])->on('grades')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['pg_b_id'], 'fh_pay_grades_ibfk_2')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_grades', function (Blueprint $table) {
            $table->dropForeign('fh_pay_grades_ibfk_1');
            $table->dropForeign('fh_pay_grades_ibfk_2');
        });
    }
};
