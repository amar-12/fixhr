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
        Schema::create('policy_attendance_bonuses', function (Blueprint $table) {
            $table->integer('pab_id', true);
            $table->integer('pab_b_id')->nullable()->index('pab_b_id');
            $table->integer('pab_ap_id')->index('policy_id');
            $table->integer('pab_threshold_days')->comment('Days of perfect attendance required for bonus');
            $table->decimal('pab_amount', 10)->comment('Bonus amount');
            $table->string('pab_frequency', 50)->nullable()->default('Monthly')->comment('Frequency of bonus (e.g., Monthly, Quarterly)');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_attendance_bonuses');
    }
};
