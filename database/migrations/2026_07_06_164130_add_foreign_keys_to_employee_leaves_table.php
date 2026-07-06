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
        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->foreign(['el_emp_id'], 'fh_employee_leaves_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['el_lvt_id'], 'fh_employee_leaves_ibfk_2')->references(['lvt_id'])->on('leave_types')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['el_b_id'], 'fh_employee_leaves_ibfk_3')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->dropForeign('fh_employee_leaves_ibfk_1');
            $table->dropForeign('fh_employee_leaves_ibfk_2');
            $table->dropForeign('fh_employee_leaves_ibfk_3');
        });
    }
};
