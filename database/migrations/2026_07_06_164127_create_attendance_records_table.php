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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->integer('atd_id', true);
            $table->integer('atd_b_id')->nullable()->index('atd_b_id');
            $table->bigInteger('atd_emp_id')->index('atd_emp_id');
            $table->string('atd_device_id')->nullable();
            $table->date('atd_date')->nullable();
            $table->integer('atd_pst_id')->nullable()->index('atd_pst_id');
            $table->integer('atd_work_mode_type_id')->nullable()->index('atd_work_mode_type_id');
            $table->integer('atd_checkin_method_id')->nullable()->index('atd_checkin_method_id');
            $table->dateTime('atd_check_in_time')->nullable();
            $table->dateTime('atd_check_out_time')->nullable();
            $table->text('atd_segments')->nullable();
            $table->boolean('atd_is_late')->nullable()->default(false);
            $table->boolean('atd_is_early_exit')->nullable()->default(false);
            $table->decimal('atd_early_exit_duration', 10, 0)->nullable();
            $table->decimal('atd_late_duration', 10)->nullable();
            $table->boolean('atd_is_absent')->nullable()->default(false);
            $table->boolean('atd_is_overtime')->nullable()->default(false);
            $table->decimal('atd_overtime_hours', 10)->nullable()->default(0);
            $table->integer('atd_attendance_status')->nullable()->default(228)->index('atd_attendance_status');
            $table->longText('atd_punchin_photo')->nullable();
            $table->longText('atd_punchout_photo')->nullable();
            $table->string('atd_punchin_location')->nullable();
            $table->string('atd_punchout_location')->nullable();
            $table->string('atd_longitude_punchin')->nullable();
            $table->string('atd_latitude_punchin')->nullable();
            $table->string('atd_longitude_punchout')->nullable();
            $table->string('atd_visitor_in_s3_url')->nullable();
            $table->string('atd_visitor_out_s3_url')->nullable();
            $table->bigInteger('atd_updated_by')->nullable()->index('atd_updated_by');
            $table->longText('atd_remark')->nullable();
            $table->string('atd_latitude_punchout')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->tinyInteger('atd_stage_completed')->nullable()->default(0);
            $table->integer('atd_am_id')->nullable();
            $table->integer('atd_module_id')->nullable()->default(249);
            $table->boolean('atd_next_approver')->nullable()->default(true);
            $table->integer('atd_request_status')->nullable()->default(140);
            $table->string('atd_ar_reason')->nullable();
            $table->string('atd_gtp_id', 50)->nullable();
            $table->string('Column16', 50)->nullable();
            $table->string('Column17', 50)->nullable();
            $table->decimal('atd_total_worked_hours', 10)->nullable()->storedAs('timestampdiff(MINUTE,`atd_check_in_time`,`atd_check_out_time`) / 60');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
