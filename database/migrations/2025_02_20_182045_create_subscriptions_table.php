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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->integer('sbc_id', true);
            $table->integer('sbc_b_id')->index('sbc_b_id');
            $table->integer('sbc_no_emp')->default(1);
            $table->decimal('sbc_total_price', 10)->default(0);
            $table->enum('sbc_status', ['active', 'inactive', 'cancelled'])->nullable()->default('active');
            $table->integer('sbc_duration');
            $table->timestamp('sbc_subscribed_at')->useCurrent();
            $table->timestamp('sbc_expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
