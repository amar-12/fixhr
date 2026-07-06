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
        Schema::table('advance_logs', function (Blueprint $table) {
            $table->foreign(['adl_trp_id'], 'fh_advance_logs_ibfk_1')->references(['trp_id'])->on('tada_request_plan')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['adl_approver_id'], 'fh_advance_logs_ibfk_2')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advance_logs', function (Blueprint $table) {
            $table->dropForeign('fh_advance_logs_ibfk_1');
            $table->dropForeign('fh_advance_logs_ibfk_2');
        });
    }
};
