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
        Schema::table('leave_opening_balance', function (Blueprint $table) {
            $table->foreign(['lob_b_id'], 'fk_fh_leave_opening_balance_fh_businesses')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lob_emp_id'], 'fk_fh_leave_opening_balance_fh_employees')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lob_leave_type_id'], 'fk_fh_leave_opening_balance_fh_master_table')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_opening_balance', function (Blueprint $table) {
            $table->dropForeign('fk_fh_leave_opening_balance_fh_businesses');
            $table->dropForeign('fk_fh_leave_opening_balance_fh_employees');
            $table->dropForeign('fk_fh_leave_opening_balance_fh_master_table');
        });
    }
};
