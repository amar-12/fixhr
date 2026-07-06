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
        Schema::table('emp_course_Degrees', function (Blueprint $table) {
            $table->foreign(['qualification_id'], 'fh_emp_course_Degrees_ibfk_1')->references(['id'])->on('emp_qualifications')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emp_course_Degrees', function (Blueprint $table) {
            $table->dropForeign('fh_emp_course_Degrees_ibfk_1');
        });
    }
};
