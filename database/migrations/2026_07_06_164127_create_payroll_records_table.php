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
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->bigInteger('pr_id', true);
            $table->integer('pr_b_id')->nullable()->index('pr_b_id');
            $table->bigInteger('pr_emp_id')->index('pr_emp_id');
            $table->string('pr_year_month', 20)->nullable();
            $table->decimal('pr_base_salary', 10)->nullable();
            $table->decimal('pr_total_earnings', 10)->nullable();
            $table->decimal('pr_total_deductions', 10)->nullable();
            $table->decimal('pr_net_salary', 10)->nullable();
            $table->enum('status', ['Pending', 'Processed', 'Error'])->nullable()->default('Pending');
            $table->timestamp('pr_processed_at')->nullable();
            $table->enum('pr_payment_method', ['Direct Deposit', 'Check'])->nullable()->default('Direct Deposit');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
