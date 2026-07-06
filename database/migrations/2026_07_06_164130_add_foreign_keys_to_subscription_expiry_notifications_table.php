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
        Schema::table('subscription_expiry_notifications', function (Blueprint $table) {
            $table->foreign(['subscription_id'], 'fh_subscription_expiry_notifications_ibfk_1')->references(['sub_id'])->on('subscriptions')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_expiry_notifications', function (Blueprint $table) {
            $table->dropForeign('fh_subscription_expiry_notifications_ibfk_1');
        });
    }
};
