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
        Schema::table('subscription_features', function (Blueprint $table) {
            $table->foreign(['sbf_sbc_id'], 'fh_subscription_features_ibfk_1')->references(['sbc_id'])->on('subscriptions')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sbf_mdl_id'], 'fh_subscription_features_ibfk_2')->references(['mdl_id'])->on('modules')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sbf_mdf_id'], 'fh_subscription_features_ibfk_3')->references(['mdf_id'])->on('module_features')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_features', function (Blueprint $table) {
            $table->dropForeign('fh_subscription_features_ibfk_1');
            $table->dropForeign('fh_subscription_features_ibfk_2');
            $table->dropForeign('fh_subscription_features_ibfk_3');
        });
    }
};
