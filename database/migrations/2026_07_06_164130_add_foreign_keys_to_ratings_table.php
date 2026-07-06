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
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreign(['rat_b_id'], 'fk_ratings_business')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['rat_emp_id'], 'fk_ratings_employee')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign('fk_ratings_business');
            $table->dropForeign('fk_ratings_employee');
        });
    }
};
