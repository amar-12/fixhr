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
        Schema::create('policy_salary', function (Blueprint $table) {
            $table->bigInteger('ps_id', true);
            $table->integer('ps_b_id')->nullable();
            $table->string('ps_name', 100);
            $table->text('ps_description')->nullable();
            $table->decimal('ps_basic_salary_percentage', 5)->nullable();
            $table->decimal('ps_hra_allowance_percentage', 5)->nullable();
            $table->double('ps_conveyance_allowance_threshhold')->nullable();
            $table->double('ps_medical_allowance_threshhold')->nullable();
            $table->decimal('ps_employee_pf_percentage', 5)->nullable();
            $table->decimal('ps_employer_pf_percentage', 5)->nullable();
            $table->decimal('ps_employee_esic_percentage', 5)->nullable();
            $table->decimal('ps_employer_esic_percentage', 5)->nullable();
            $table->double('ps_pf_threshhold')->nullable();
            $table->double('ps_esic_threshhold')->nullable();
            $table->decimal('ps_max_bonus_percentage', 5)->nullable();
            $table->decimal('ps_min_salary_increase_percentage', 5)->nullable();
            $table->decimal('ps_tax_deduction_percentage', 5)->nullable();
            $table->text('ps_eligibility_criteria')->nullable();
            $table->enum('ps_adjustment_frequency', ['Annual', 'Semi-Annual', 'Quarterly'])->nullable();
            $table->date('ps_review_date')->nullable();
            $table->string('ps_applicable_locations')->nullable();
            $table->boolean('ps_is_active')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_salary');
    }
};
