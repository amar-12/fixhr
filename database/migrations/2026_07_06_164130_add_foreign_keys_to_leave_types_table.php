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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->foreign(['lvt_pl_id'], 'fh_leave_types_ibfk_1')->references(['pl_id'])->on('policy_leaves')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lvt_cat_type_id'], 'fh_leave_types_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lvt_leave_cycle_id'], 'fh_leave_types_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lvt_applicable_to_id'], 'fh_leave_types_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropForeign('fh_leave_types_ibfk_1');
            $table->dropForeign('fh_leave_types_ibfk_2');
            $table->dropForeign('fh_leave_types_ibfk_3');
            $table->dropForeign('fh_leave_types_ibfk_4');
        });
    }
};
