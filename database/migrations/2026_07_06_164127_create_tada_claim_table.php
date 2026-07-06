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
        Schema::create('tada_claim', function (Blueprint $table) {
            $table->integer('tc_id', true);
            $table->string('tc_unique_id', 100)->nullable();
            $table->integer('tc_group_claim')->default(0);
            $table->integer('tc_b_id')->nullable()->index('tc_b_id');
            $table->integer('tc_trp_id')->nullable()->index('tc_trp_id');
            $table->bigInteger('tc_emp_id')->nullable()->index('tc_emp_id');
            $table->double('tc_amount')->nullable();
            $table->double('tc_da_amount')->nullable();
            $table->double('tc_deduction_amount')->nullable();
            $table->double('tc_claimed_amount')->nullable()->default(0);
            $table->double('tc_payed_amount')->nullable()->default(0);
            $table->boolean('tc_is_payed')->nullable()->default(false);
            $table->boolean('tc_deduction_status')->nullable();
            $table->date('tc_approved_date')->nullable();
            $table->date('tc_payment_date')->nullable();
            $table->integer('tc_am_id')->nullable()->index('tc_am_id');
            $table->integer('tc_module_id')->nullable()->default(146)->index('tc_module_id');
            $table->integer('tc_status')->nullable()->default(140)->index('tc_current_status');
            $table->dateTime('transaction_date')->nullable();
            $table->integer('tc_paid_status')->default(0);
            $table->string('reference_no', 100)->nullable();
            $table->integer('tc_next_approver')->nullable()->default(1)->comment('Approver Sequence');
            $table->tinyInteger('tc_stage_completed')->nullable()->default(0);
            $table->longText('tc_deduction_remarks')->nullable();
            $table->text('tc_da_calculation_message')->nullable();
            $table->longText('tc_remarks')->nullable();
            $table->double('tc_custom_amount')->nullable();
            $table->double('remaining_da_amount')->nullable();
            $table->string('deleted_at_remark')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tada_claim');
    }
};
