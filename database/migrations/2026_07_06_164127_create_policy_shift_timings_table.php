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
        Schema::create('policy_shift_timings', function (Blueprint $table) {
            $table->integer('pst_id', true);
            $table->integer('pst_b_id')->nullable()->index('pst_b_id');
            $table->integer('pst_ap_id')->index('policy_id');
            $table->integer('pst_type_id')->nullable()->index('pst_type_id');
            $table->string('pst_name');
            $table->string('pst_code', 100)->nullable();
            $table->time('pst_start_time');
            $table->time('pst_end_time');
            $table->integer('pst_shift_duration')->nullable();
            $table->boolean('pst_end_next_day')->nullable()->default(false);
            $table->time('pst_end_by')->nullable();
            $table->boolean('pst_allow_break1')->nullable()->default(false);
            $table->time('pst_break_begin_time1')->nullable();
            $table->time('pst_break_end_time1')->nullable();
            $table->integer('pst_break1_duration')->nullable();
            $table->boolean('pst_allow_break2')->nullable()->default(false);
            $table->time('pst_break_begin_time2')->nullable();
            $table->time('pst_break_end_time2')->nullable();
            $table->time('pst_min_work_hour')->nullable();
            $table->integer('pst_work_hour_penalty_status_id')->nullable();
            $table->integer('pst_break_duration_minutes')->nullable()->default(60)->comment('Default break time during the shift');
            $table->tinyInteger('pst_is_break_paid')->nullable();
            $table->boolean('pst_allow_break')->nullable();
            $table->time('pst_break_begin_time')->nullable();
            $table->time('pst_break_end_time')->nullable();
            $table->boolean('pst_allow_punch_begin_before')->nullable();
            $table->integer('pst_mins_punch_begin_before')->nullable();
            $table->boolean('pst_allow_punch_end_after')->nullable();
            $table->integer('pst_mins_punch_end_after')->nullable();
            $table->boolean('pst_allow_grace_time')->nullable();
            $table->integer('pst_grace_time')->nullable();
            $table->boolean('pst_allow_partial_day')->nullable();
            $table->integer('pst_partial_day_type_id')->nullable();
            $table->time('pst_partial_day_begin_time')->nullable();
            $table->time('pst_partial_day_end_time')->nullable();
            $table->string('week_off', 50)->nullable();
            $table->boolean('pst_allow_partial_day2')->nullable()->default(false);
            $table->integer('pst_partial_day_type_id2')->nullable();
            $table->time('pst_partial_day_begin_time2')->nullable();
            $table->time('pst_partial_day_end_time2')->nullable();
            $table->string('week_off2', 50)->nullable();
            $table->boolean('pst_hd_office_report_after')->nullable()->default(false);
            $table->time('pst_hd_office_report_after_time')->nullable();
            $table->time('pst_session1_end_by')->nullable();
            $table->integer('pst_session2_grace_time')->nullable();
            $table->boolean('pst_hd_office_report_before')->nullable()->default(false);
            $table->time('pst_hd_office_report_before_time')->nullable();
            $table->boolean('pst_is_high')->nullable()->default(false);
            $table->boolean('pst_is_face_track_yes')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->boolean('pst_auto_assign_shift')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_shift_timings');
    }
};
