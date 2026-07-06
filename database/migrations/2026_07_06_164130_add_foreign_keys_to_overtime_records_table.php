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
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->foreign(['otr_emp_id'], 'fh_overtime_records_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['otr_attendance_id'], 'fh_overtime_records_ibfk_2')->references(['atd_id'])->on('attendance_records')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['otr_approved_by'], 'fh_overtime_records_ibfk_3')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['otr_b_id'], 'fh_overtime_records_ibfk_4')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->dropForeign('fh_overtime_records_ibfk_1');
            $table->dropForeign('fh_overtime_records_ibfk_2');
            $table->dropForeign('fh_overtime_records_ibfk_3');
            $table->dropForeign('fh_overtime_records_ibfk_4');
        });
    }
};
