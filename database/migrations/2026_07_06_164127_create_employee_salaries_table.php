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
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->bigInteger('es_id', true);
            $table->integer('es_b_id')->nullable();
            $table->bigInteger('es_emp_id');
            $table->bigInteger('es_ps_id')->nullable();
            $table->double('es_annual_ctc')->nullable();
            $table->double('es_monthly_ctc')->nullable();
            $table->double('es_perday_salary')->nullable();
            $table->double('es_base_salary')->nullable();
            $table->decimal('es_earnings', 10)->nullable()->default(0);
            $table->decimal('es_deductions', 10)->nullable()->default(0);
            $table->decimal('es_rem_allowance', 10)->nullable()->default(0);
            $table->double('es_monthly_gross')->nullable();
            $table->double('es_annual_gross')->nullable();
            $table->double('es_monthly_net_salary')->nullable();
            $table->string('es_currency', 10)->nullable()->default('INR');
            $table->string('es_salary_grade', 50)->nullable();
            $table->boolean('es_is_current')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->boolean('es_esic_validation_enabled')->default(true);
            $table->boolean('es_pf_validation_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
