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
        Schema::table('leave_balance', function (Blueprint $table) {
            $table->foreign(['lb_b_id'], 'fh_leave_balance_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lb_emp_id'], 'fh_leave_balance_ibfk_2')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lb_cat_type_id'], 'fh_leave_balance_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balance', function (Blueprint $table) {
            $table->dropForeign('fh_leave_balance_ibfk_1');
            $table->dropForeign('fh_leave_balance_ibfk_2');
            $table->dropForeign('fh_leave_balance_ibfk_3');
        });
    }
};
