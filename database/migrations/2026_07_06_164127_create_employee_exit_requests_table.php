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
        Schema::create('employee_exit_requests', function (Blueprint $table) {
            $table->bigIncrements('er_id')->index('er_id');
            $table->unsignedBigInteger('er_emp_id');
            $table->unsignedBigInteger('er_b_id');
            $table->unsignedBigInteger('er_exit_type_id')->nullable();
            $table->text('er_reason')->nullable();
            $table->date('er_resignation_date')->nullable();
            $table->integer('er_notice_period_days')->nullable();
            $table->date('er_last_working_day')->nullable();
            $table->string('er_manager_status', 20)->default('PENDING');
            $table->string('er_hr_status', 20)->default('PENDING');
            $table->string('er_overall_status', 50)->default('DRAFT');
            $table->text('er_remark')->nullable();
            $table->unsignedBigInteger('er_created_by')->nullable();
            $table->unsignedBigInteger('er_updated_by')->nullable();
            $table->timestamps();
            $table->text('er_manager_remark')->nullable();
            $table->dateTime('er_manager_action_at')->nullable();
            $table->text('er_hr_remark')->nullable();
            $table->dateTime('er_hr_action_at')->nullable();
            $table->text('er_finance_remark')->nullable();
            $table->dateTime('er_finance_action_at')->nullable();
            $table->text('er_documentation_notes')->nullable();
            $table->string('relieving_letter_path')->nullable();
            $table->string('experience_letter_path')->nullable();
            $table->string('noc_form_path')->nullable();
            $table->text('settlement_docs_paths')->nullable();
            $table->integer('er_am_id')->nullable();
            $table->integer('er_module_id')->nullable();
            $table->integer('er_status')->nullable();
            $table->integer('er_next_approver')->nullable();
            $table->tinyInteger('er_stage_completed')->default(0);
            $table->tinyInteger('er_module_stage')->default(0);
            $table->string('er_request_status', 100)->nullable();
            $table->string('er_ref_no', 100);
            $table->text('er_revert_remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_exit_requests');
    }
};
