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
        Schema::create('processed_salaries', function (Blueprint $table) {
            $table->bigInteger('ps_id', true);
            $table->integer('ps_b_id')->nullable();
            $table->integer('ps_br_id')->nullable();
            $table->integer('ps_emp_id');
            $table->integer('ps_payroll_id')->nullable();
            $table->double('ps_monthly_salary')->nullable();
            $table->decimal('ps_basic_salary', 10)->nullable();
            $table->decimal('ps_per_day_salary', 10)->nullable();
            $table->decimal('ps_worked_days_salary', 10)->nullable();
            $table->decimal('ps_earnings', 10)->nullable();
            $table->decimal('ps_employee_deductions', 10)->nullable();
            $table->decimal('ps_employer_deductions', 10)->nullable();
            $table->decimal('ps_rem_allowance', 10)->nullable();
            $table->decimal('ps_monthly_gross', 10)->nullable();
            $table->decimal('ps_monthly_net_salary', 10)->nullable();
            $table->integer('ps_generated_by')->nullable();
            $table->decimal('ps_monthly_ctc', 10)->nullable();
            $table->decimal('ps_total_days_in_month', 10)->nullable();
            $table->decimal('ps_week_off_count', 10)->nullable();
            $table->decimal('ps_total_month_working_days', 10)->nullable();
            $table->decimal('ps_total_days_worked', 10)->nullable();
            $table->decimal('ps_present_days', 10)->nullable();
            $table->decimal('ps_workable_days', 10)->nullable();
            $table->integer('ps_week_id')->nullable();
            $table->decimal('ps_days_late', 10)->nullable();
            $table->decimal('ps_upl_count', 10)->nullable();
            $table->string('ps_currency', 10)->nullable()->default('INR');
            $table->boolean('ps_is_payslip')->nullable()->default(false);
            $table->string('ps_payslip_url')->nullable();
            $table->decimal('ps_tada_payed_amount', 10)->nullable();
            $table->integer('ps_esic_worked_days')->nullable();
            $table->decimal('ps_esic_monthly_gross', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_salaries');
    }
};
