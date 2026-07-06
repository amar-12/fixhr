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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreign(['lvr_pl_id'], 'fh_leave_requests_ibfk_2')->references(['pl_id'])->on('policy_leaves')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lvr_leave_day_type_id'], 'fh_leave_requests_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lvr_day_segment_id'], 'fh_leave_requests_ibfk_5')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lvr_cat_type_id'], 'fh_leave_requests_ibfk_6')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign('fh_leave_requests_ibfk_2');
            $table->dropForeign('fh_leave_requests_ibfk_4');
            $table->dropForeign('fh_leave_requests_ibfk_5');
            $table->dropForeign('fh_leave_requests_ibfk_6');
        });
    }
};
