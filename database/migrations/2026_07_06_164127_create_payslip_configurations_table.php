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
        Schema::create('payslip_configurations', function (Blueprint $table) {
            $table->bigIncrements('pc_id');
            $table->unsignedBigInteger('pc_b_id');
            $table->boolean('pc_show_employee_code')->nullable()->default(true);
            $table->boolean('pc_show_employee_name')->nullable()->default(true);
            $table->boolean('pc_show_designation')->nullable()->default(true);
            $table->boolean('pc_show_department')->nullable()->default(true);
            $table->boolean('pc_show_branch')->nullable()->default(true);
            $table->boolean('pc_show_ip_uan')->nullable()->default(true);
            $table->boolean('pc_show_bank_details')->nullable()->default(true);
            $table->boolean('pc_show_month')->nullable()->default(true);
            $table->boolean('pc_show_doj')->nullable()->default(true);
            $table->boolean('pc_show_earnings_breakdown')->nullable()->default(true);
            $table->boolean('pc_show_employee_deductions_breakdown')->nullable()->default(true);
            $table->boolean('pc_show_employer_deductions_breakdown')->nullable()->default(true);
            $table->boolean('pc_show_net_pay')->nullable()->default(true);
            $table->boolean('pc_show_total_ctc')->nullable()->default(true);
            $table->boolean('pc_round_off_net_salary')->nullable()->default(true);
            $table->boolean('pc_show_net_salary_in_words')->nullable()->default(true);
            $table->boolean('pc_show_working_days')->nullable()->default(true);
            $table->boolean('pc_show_month_days')->nullable()->default(true);
            $table->boolean('pc_show_days_present')->nullable()->default(true);
            $table->boolean('pc_show_salary_days')->nullable()->default(true);
            $table->boolean('pc_show_lwp_days')->nullable()->default(true);
            $table->boolean('pc_show_leaves_taken')->nullable()->default(true);
            $table->boolean('pc_show_signature')->nullable()->default(true);
            $table->boolean('pc_show_disclaimer')->nullable()->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_configurations');
    }
};
