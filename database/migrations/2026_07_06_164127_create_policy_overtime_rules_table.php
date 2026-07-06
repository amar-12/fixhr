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
        Schema::create('policy_overtime_rules', function (Blueprint $table) {
            $table->integer('por_id', true);
            $table->integer('por_b_id')->nullable();
            $table->integer('por_ap_id')->index('policy_id');
            $table->decimal('por_threshold_hours', 5)->comment('Hours after which overtime applies');
            $table->decimal('por_rate', 5)->comment('Overtime pay rate multiplier');
            $table->decimal('por_max_overtime_hours_per_day', 5)->nullable()->default(2)->comment('Maximum allowable overtime hours per day');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_overtime_rules');
    }
};
