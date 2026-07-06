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
        Schema::create('attendance_exceptions', function (Blueprint $table) {
            $table->integer('ae_id', true);
            $table->integer('ae_b_id')->nullable()->index('fh_attendance_exceptions_ibfk_5');
            $table->integer('ae_ap_id')->nullable()->index('policy_id');
            $table->bigInteger('ae_emp_id')->nullable()->index('employee_id');
            $table->date('ae_date')->nullable();
            $table->integer('ae_type_id')->nullable()->index('ae_type_id');
            $table->bigInteger('ae_approved_by')->nullable()->index('approved_by');
            $table->time('ae_in_time')->nullable();
            $table->time('ae_out_time')->nullable();
            $table->integer('ae_total_working')->nullable()->storedAs('timestampdiff(MINUTE,`ae_in_time`,`ae_out_time`) / 60');
            $table->integer('ae_reason_id')->nullable();
            $table->text('ae_custom_reason')->nullable();
            $table->integer('ae_attendance_status')->nullable();
            $table->integer('ae_am_id')->nullable()->index('ae_am_id');
            $table->integer('ae_status')->nullable()->default(140);
            $table->integer('ae_module_id')->nullable()->default(229)->index('ae_module_id');
            $table->integer('ae_next_approver')->nullable()->default(1)->comment('Approver Sequence');
            $table->tinyInteger('ae_stage_completed')->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();
            $table->string('ae_code', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_exceptions');
    }
};
