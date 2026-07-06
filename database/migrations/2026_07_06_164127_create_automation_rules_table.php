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
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->bigIncrements('ar_id');
            $table->integer('ar_b_id')->nullable();
            $table->integer('ar_rule_type')->index();
            $table->boolean('ar_is_enabled')->default(false);
            $table->integer('ar_occurrences')->nullable();
            $table->integer('ar_mark_absent')->nullable()->index('fh_automation_rules_ar_mark_absent_foreign');
            $table->time('ar_mark_half_day_time')->nullable();
            $table->boolean('ar_allow_overtime_early')->default(false);
            $table->boolean('ar_allow_overtime_late')->default(false);
            $table->time('ar_min_overtime')->nullable();
            $table->time('ar_max_overtime')->nullable();
            $table->integer('ar_occurrence_limit')->nullable();
            $table->boolean('ar_is_mode_enabled')->nullable()->default(false);
            $table->integer('ar_apply_before_day')->nullable();
            $table->decimal('ar_penalty_amount', 10)->nullable();
            $table->integer('ar_both_time_count')->nullable();
            $table->boolean('ar_apply_gatepass_checkout')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
