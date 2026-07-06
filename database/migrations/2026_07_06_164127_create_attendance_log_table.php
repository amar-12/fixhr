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
        Schema::create('attendance_log', function (Blueprint $table) {
            $table->integer('al_id', true);
            $table->integer('al_b_id')->nullable();
            $table->integer('al_atd_id')->nullable();
            $table->bigInteger('al_emp_id');
            $table->integer('al_pst_id')->nullable();
            $table->dateTime('al_check_in_time')->nullable();
            $table->dateTime('al_check_out_time')->nullable();
            $table->date('al_date')->nullable();
            $table->boolean('al_is_late')->nullable()->default(false);
            $table->decimal('al_late_duration', 10)->nullable();
            $table->boolean('al_is_early_exit')->nullable()->default(false);
            $table->decimal('al_early_exit_duration', 10)->nullable();
            $table->boolean('al_is_absent')->nullable()->default(false);
            $table->boolean('al_is_overtime')->nullable()->default(false);
            $table->decimal('al_overtime_hours', 10)->nullable();
            $table->integer('al_attendance_status')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->decimal('al_total_worked_hours', 5)->nullable();
            $table->string('al_code', 30)->nullable();
            $table->bigInteger('al_updated_by')->nullable();
            $table->string('al_reason')->nullable();
            $table->integer('al_module_id')->nullable()->default(249);
            $table->integer('al_next_approver')->nullable()->default(1);
            $table->integer('al_request_status')->nullable()->default(171);
            $table->integer('al_am_id')->nullable();
            $table->integer('al_stage_completed')->nullable()->default(1);
            $table->string('al_latitude')->nullable();
            $table->string('al_longitude')->nullable();

            $table->index(['al_emp_id', 'al_date'], 'idx_al_emp_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_log');
    }
};
