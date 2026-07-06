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
        Schema::create('tada_request_plan', function (Blueprint $table) {
            $table->integer('trp_id', true);
            $table->string('trp_unique_id', 100)->nullable();
            $table->integer('trp_tc_id')->nullable()->index('trp_tc_id');
            $table->integer('trp_b_id')->nullable()->index('trp_b_id');
            $table->integer('trp_br_id')->nullable()->index('trp_br_id');
            $table->bigInteger('trp_emp_id')->nullable()->index('trp_emp_id');
            $table->integer('trp_pttt_id')->nullable()->index('trp_pttt_id');
            $table->integer('trp_ptc_id')->nullable()->index('trp_ptc_id');
            $table->string('trp_name', 100)->nullable();
            $table->integer('trp_purpose')->nullable()->index('trp_purpose');
            $table->string('trp_destination')->nullable();
            $table->date('trp_start_date')->nullable();
            $table->time('trp_start_time')->nullable();
            $table->date('trp_end_date')->nullable();
            $table->time('trp_end_time')->nullable();
            $table->integer('trp_advance_allowance')->nullable();
            $table->mediumText('trp_remarks')->nullable();
            $table->longText('trp_document')->nullable();
            $table->tinyInteger('trp_is_expense_added')->nullable()->default(0);
            $table->tinyInteger('trp_is_details_added')->nullable()->default(0);
            $table->integer('trp_module_id')->nullable()->default(145)->index('trp_module_id');
            $table->integer('trp_am_id')->nullable()->index('trp_am_id');
            $table->integer('trp_request_status')->nullable()->default(139);
            $table->integer('trp_next_approver')->default(1)->comment('Approver Sequence');
            $table->tinyInteger('trp_stage_completed')->nullable()->default(0);
            $table->string('trp_call_id', 155)->nullable();
            $table->integer('trp_is_claimed')->nullable()->default(0);
            $table->boolean('trp_adv_payment_processed')->nullable()->default(false);
            $table->boolean('trp_tada_payment_processed')->nullable()->default(false);
            $table->boolean('trp_exp_payment_processed')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();
            $table->integer('trp_is_converted')->nullable();
            $table->longText('trp_previous_snapshot')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tada_request_plan');
    }
};
