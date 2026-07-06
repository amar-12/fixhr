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
        Schema::table('attendance_shift_policy_item', function (Blueprint $table) {
            $table->foreign(['aspi_asp_id'])->references(['asp_id'])->on('attendance_shift_policy')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['aspi_break_type'])->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['aspi_b_id'])->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['aspi_partial_day_on'])->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_shift_policy_item', function (Blueprint $table) {
            $table->dropForeign('fh_attendance_shift_policy_item_aspi_asp_id_foreign');
            $table->dropForeign('fh_attendance_shift_policy_item_aspi_break_type_foreign');
            $table->dropForeign('fh_attendance_shift_policy_item_aspi_b_id_foreign');
            $table->dropForeign('fh_attendance_shift_policy_item_aspi_partial_day_on_foreign');
        });
    }
};
