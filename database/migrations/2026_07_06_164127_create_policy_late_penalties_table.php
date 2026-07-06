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
        Schema::create('policy_late_penalties', function (Blueprint $table) {
            $table->integer('plp_id', true);
            $table->integer('plp_ap_id')->index('policy_id');
            $table->integer('plp_threshold_minutes')->comment('Minutes after which penalty applies');
            $table->decimal('plp_rate', 5)->comment('Rate of penalty, could be a percentage or fixed amount');
            $table->string('plp_type', 50)->nullable()->default('Deduction')->comment('Penalty type (e.g., Deduction, Warning)');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_late_penalties');
    }
};
