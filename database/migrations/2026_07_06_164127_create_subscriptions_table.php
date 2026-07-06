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
            $table->bigIncrements('sub_id');
            $table->unsignedInteger('business_id')->index('idx_business');
            $table->unsignedBigInteger('plan_id')->index('idx_plan');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('trial_ends_at')->nullable();
            $table->enum('status', ['demo', 'active', 'suspended', 'deactivated'])->nullable()->default('demo');
            $table->unsignedBigInteger('price_slab_id');
            $table->decimal('price_per_user', 10, 0);
            $table->string('billing_cycle');
            $table->string('payment_status', 50)->nullable();
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
