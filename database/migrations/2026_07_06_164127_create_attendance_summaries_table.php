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
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->bigInteger('as_id', true);
            $table->integer('as_b_id')->nullable();
            $table->integer('as_br_id')->nullable();
            $table->integer('as_d_id')->nullable();
            $table->bigInteger('as_emp_id');
            $table->integer('as_pp_id')->nullable();
            $table->string('as_year_month', 10)->nullable();
            $table->decimal('as_total_days', 10)->nullable();
            $table->decimal('as_total_present', 5)->nullable()->default(0);
            $table->integer('as_total_missed_punch')->nullable()->default(0);
            $table->decimal('as_total_half_day', 5)->nullable()->default(0);
            $table->decimal('as_total_absent', 5)->nullable();
            $table->decimal('as_total_leave', 5)->nullable();
            $table->decimal('as_total_weekoff', 5)->nullable();
            $table->decimal('as_total_weekoffPresent', 5)->nullable();
            $table->decimal('as_total_holiday', 10)->nullable();
            $table->decimal('as_total_worked_days', 5)->nullable()->default(0);
            $table->integer('as_days_late')->nullable()->default(0);
            $table->integer('as_early_exit')->nullable();
            $table->decimal('as_total_overtime_hours', 10)->nullable()->default(0);
            $table->decimal('as_total_upl_count', 10)->nullable();
            $table->boolean('as_is_sal_processed')->nullable()->default(false);
            $table->integer('as_week_id')->nullable();
            $table->tinyInteger('as_is_frozen')->nullable();
            $table->timestamp('as_frozen_at')->nullable();
            $table->integer('as_frozen_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};
