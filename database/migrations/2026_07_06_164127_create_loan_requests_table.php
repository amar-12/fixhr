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
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->integer('lnr_id', true);
            $table->integer('lnr_b_id')->nullable()->index('lnr_b_id');
            $table->bigInteger('lnr_emp_id')->nullable()->index('lnr_emp_id');
            $table->string('lnr_advance_type', 100)->nullable();
            $table->string('lnr_unique_id', 100)->nullable();
            $table->string('lnr_request_subject', 100)->nullable();
            $table->double('lnr_requested_amount')->nullable();
            $table->double('lnr_installment_amount')->nullable();
            $table->integer('lnr_installments')->nullable();
            $table->double('lnr_rate')->nullable();
            $table->date('lnr_start_date')->nullable();
            $table->string('lnr_description')->nullable();
            $table->integer('lnr_module_id')->nullable()->default(442)->index('lnr_module_id');
            $table->integer('lnr_am_id')->nullable()->index('lnr_am_id');
            $table->integer('lnr_request_status')->nullable()->default(140)->index('lnr_request_status');
            $table->tinyInteger('lnr_stage_completed')->nullable()->default(0);
            $table->integer('lnr_next_approver')->nullable()->default(1);
            $table->string('lnr_status', 20)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
