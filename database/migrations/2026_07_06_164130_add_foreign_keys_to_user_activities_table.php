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
        Schema::table('user_activities', function (Blueprint $table) {
            $table->foreign(['ua_emp_id'], 'fh_user_activities_ibfk_2')->references(['emp_b_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ua_b_id'], 'fh_user_activities_ibfk_3')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropForeign('fh_user_activities_ibfk_2');
            $table->dropForeign('fh_user_activities_ibfk_3');
        });
    }
};
