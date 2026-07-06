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
        Schema::create('salary_master_history', function (Blueprint $table) {
            $table->bigIncrements('sm_id');
            $table->integer('sm_emp_id');
            $table->integer('sm_emp_b_id');
            $table->string('sm_cal_mode', 30)->nullable();
            $table->string('sm_is_employer_deduction', 30)->nullable();
            $table->string('sm_monthly_ctc')->nullable();
            $table->string('sm_annual_ctc')->nullable();
            $table->string('sm_basic')->nullable();
            $table->string('sm_hra')->nullable();
            $table->string('sm_dear_allow')->nullable();
            $table->string('sm_conv_allow')->nullable();
            $table->string('sm_med_allow')->nullable();
            $table->string('sm_edu_allow')->nullable();
            $table->string('sm_spec_allow')->nullable();
            $table->string('sm_other_allow')->nullable();
            $table->string('sm_employee_epf')->nullable();
            $table->string('sm_employee_esic')->nullable();
            $table->string('sm_employee_lwf')->nullable();
            $table->string('sm_employee_total_ded')->nullable();
            $table->string('sm_employer_epf')->nullable();
            $table->string('sm_employer_esic')->nullable();
            $table->string('sm_employer_lwf')->nullable();
            $table->string('sm_employer_total_ded')->nullable();
            $table->string('sm_total_earning')->nullable();
            $table->string('sm_gross_pay')->nullable();
            $table->string('sm_per_day_wage')->nullable();
            $table->string('sm_annual_gross')->nullable();
            $table->string('sm_net_pay')->nullable();
            $table->integer('sm_fy_id')->nullable();
            $table->string('sm_remark')->nullable();
            $table->dateTime('wef')->nullable();
            $table->timestamps();
            $table->boolean('sm_esic_validation_enabled')->nullable()->default(true);
            $table->boolean('sm_pf_validation_enabled')->default(true);
            $table->unsignedTinyInteger('sm_working_days')->nullable();
            $table->decimal('sm_per_day_gross', 15)->nullable();
            $table->decimal('sm_per_day_ctc', 15)->nullable();
            $table->decimal('sm_weekly_ctc', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_master_history');
    }
};
