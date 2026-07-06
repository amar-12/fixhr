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
        Schema::table('subscription_modules', function (Blueprint $table) {
            $table->foreign(['sbm_sbc_id'], 'fh_subscription_modules_ibfk_1')->references(['sbc_id'])->on('subscriptions')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sbm_mdl_id'], 'fh_subscription_modules_ibfk_2')->references(['mdl_id'])->on('modules')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_modules', function (Blueprint $table) {
            $table->dropForeign('fh_subscription_modules_ibfk_1');
            $table->dropForeign('fh_subscription_modules_ibfk_2');
        });
    }
};
