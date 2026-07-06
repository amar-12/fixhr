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
        Schema::table('api_call_logs', function (Blueprint $table) {
            $table->foreign(['acl_b_id'], 'fh_api_call_logs_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['acl_apc_id'], 'fh_api_call_logs_ibfk_2')->references(['apc_id'])->on('api_credentials')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_call_logs', function (Blueprint $table) {
            $table->dropForeign('fh_api_call_logs_ibfk_1');
            $table->dropForeign('fh_api_call_logs_ibfk_2');
        });
    }
};
