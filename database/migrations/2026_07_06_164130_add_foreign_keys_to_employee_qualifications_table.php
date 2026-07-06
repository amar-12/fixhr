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
        Schema::table('employee_qualifications', function (Blueprint $table) {
            $table->foreign(['eq_emp_id'], 'fh_employee_qualifications_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['eq_qualification_id'], 'fh_employee_qualifications_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['eq_stream_id'], 'fh_employee_qualifications_ibfk_3')->references(['stm_id'])->on('streams')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_qualifications', function (Blueprint $table) {
            $table->dropForeign('fh_employee_qualifications_ibfk_1');
            $table->dropForeign('fh_employee_qualifications_ibfk_2');
            $table->dropForeign('fh_employee_qualifications_ibfk_3');
        });
    }
};
