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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->integer('pp_id', true);
            $table->integer('pp_b_id')->nullable();
            $table->integer('pp_quarter_id')->nullable();
            $table->integer('pp_fy_id')->nullable()->index('pp_fy_id');
            $table->integer('pp_month_id')->nullable();
            $table->integer('pp_process_id')->nullable();
            $table->integer('pp_type_id')->nullable();
            $table->string('pp_name')->nullable();
            $table->integer('pp_seq_no')->nullable();
            $table->date('pp_start_date')->nullable();
            $table->date('pp_end_date')->nullable();
            $table->string('pp_description')->nullable();
            $table->date('pp_payment_date')->nullable();
            $table->date('pp_payslip_date')->nullable();
            $table->boolean('pp_is_active')->nullable()->default(true);
            $table->boolean('pp_is_processed')->nullable()->default(false);
            $table->boolean('pp_is_freezed')->nullable()->default(false)->comment('Freezed status');
            $table->string('pp_cheque_no')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->boolean('pp_is_finalized')->nullable()->default(false);
            $table->timestamp('pp_finalized_at')->nullable();
            $table->timestamp('pp_finalized_by')->nullable();
            $table->string('pp_status_code')->default('Open');
            $table->integer('pp_created_by')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedBigInteger('finalized_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
