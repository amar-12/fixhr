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
        Schema::table('attendance_shift_policy', function (Blueprint $table) {
            $table->foreign(['asp_b_id'])->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['asp_shift_type'])->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_shift_policy', function (Blueprint $table) {
            $table->dropForeign('fh_attendance_shift_policy_asp_b_id_foreign');
            $table->dropForeign('fh_attendance_shift_policy_asp_shift_type_foreign');
        });
    }
};
