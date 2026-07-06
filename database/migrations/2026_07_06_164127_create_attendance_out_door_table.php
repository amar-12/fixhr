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
        Schema::create('attendance_out_door', function (Blueprint $table) {
            $table->bigIncrements('atd_od_id');
            $table->integer('atd_od_b_id')->nullable();
            $table->integer('atd_od_emp_id');
            $table->date('atd_od_date')->nullable();
            $table->integer('atd_od_pst_id')->nullable();
            $table->string('atd_od_device_id')->nullable();
            $table->integer('atd_od_work_mode_type_id')->nullable();
            $table->integer('atd_od_checkin_method_id')->nullable();
            $table->timestamp('atd_od_check_in_time')->nullable();
            $table->timestamp('atd_od_check_out_time')->nullable();
            $table->text('atd_od_segments')->nullable();
            $table->boolean('atd_od_is_late')->default(false);
            $table->float('atd_od_late_duration')->nullable();
            $table->boolean('atd_od_is_early_exit')->default(false);
            $table->float('atd_od_early_exit_duration')->nullable();
            $table->boolean('atd_od_is_overtime')->default(false);
            $table->float('atd_od_overtime_hours')->nullable();
            $table->integer('atd_od_attendance_status')->nullable();
            $table->string('atd_od_punchin_photo')->nullable();
            $table->string('atd_od_punchout_photo')->nullable();
            $table->text('atd_od_punchin_location')->nullable();
            $table->text('atd_od_punchout_location')->nullable();
            $table->string('atd_od_longitude_punchin')->nullable();
            $table->string('atd_od_latitude_punchin')->nullable();
            $table->string('atd_od_longitude_punchout')->nullable();
            $table->string('atd_od_latitude_punchout')->nullable();
            $table->integer('atd_od_approved_by')->nullable();
            $table->text('atd_od_remark')->nullable();
            $table->integer('atd_od_stage_completed')->nullable();
            $table->integer('atd_od_request_status')->nullable();
            $table->integer('atd_od_next_approver')->nullable();
            $table->integer('atd_od_module_id')->nullable();
            $table->integer('atd_od_am_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->time('atd_od_total_working_hours')->nullable()->storedAs('timediff(`atd_od_check_out_time`,`atd_od_check_in_time`)');
            $table->integer('atd_od_next_approver_id')->nullable();
            $table->string('atd_od_updated_auth_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_out_door');
    }
};
