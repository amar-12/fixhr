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
        Schema::table('policy_attendances', function (Blueprint $table) {
            $table->foreign(['ap_attendance_regularization'])->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['ap_b_id'], 'fh_policy_attendances_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_attendances', function (Blueprint $table) {
            $table->dropForeign('fh_policy_attendances_ap_attendance_regularization_foreign');
            $table->dropForeign('fh_policy_attendances_ibfk_1');
        });
    }
};
