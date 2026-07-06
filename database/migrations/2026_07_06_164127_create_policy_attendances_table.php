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
        Schema::create('policy_attendances', function (Blueprint $table) {
            $table->integer('ap_id', true);
            $table->integer('ap_b_id')->nullable()->index('ap_b_id');
            $table->string('ap_name', 100);
            $table->text('ap_description')->nullable();
            $table->integer('ap_punch_duration')->nullable()->default(5);
            $table->integer('ap_grace_period_minutes')->nullable()->default(0)->comment('Allowable minutes late before being considered late');
            $table->decimal('ap_max_daily_working_hours', 5)->nullable()->default(8)->comment('Maximum hours an employee should work in a day');
            $table->decimal('ap_max_overtime_hours', 5)->nullable()->default(2)->comment('Maximum allowable overtime hours per day');
            $table->boolean('ap_overtime_requires_approval')->nullable()->default(true);
            $table->decimal('ap_overtime_rate', 5)->nullable()->default(1.5)->comment('Overtime pay rate multiplier');
            $table->decimal('ap_late_penalty_rate', 5)->nullable()->default(0)->comment('Penalty applied for lateness (e.g., deduction rate)');
            $table->decimal('ap_early_leaving_penalty_rate', 5)->nullable()->default(0)->comment('Penalty for leaving early');
            $table->decimal('ap_attendance_bonus_rate', 5)->nullable()->default(0)->comment('Bonus for perfect attendance');
            $table->decimal('ap_holiday_overtime_rate', 5)->nullable()->default(2)->comment('Overtime pay rate for working on holidays');
            $table->date('ap_effective_date')->nullable();
            $table->date('ap_expiration_date')->nullable();
            $table->integer('ap_attendance_regularization')->nullable()->index();
            $table->integer('ap_limit_day')->nullable();
            $table->integer('ap_mispunch_regularization')->nullable();
            $table->integer('ap_mispunch_limit_day')->nullable();
            $table->boolean('ap_status')->nullable()->default(true);
            $table->string('ap_checkin_method_ids', 100)->nullable();
            $table->tinyInteger('ap_mark_absent_check')->nullable()->default(0);
            $table->tinyInteger('ap_is_selfie_restricted')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_attendances');
    }
};
