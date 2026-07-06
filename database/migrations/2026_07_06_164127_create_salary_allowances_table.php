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
        Schema::create('salary_allowances', function (Blueprint $table) {
            $table->integer('sa_id', true);
            $table->integer('sa_b_id')->nullable();
            $table->integer('sa_earning_type_id')->nullable();
            $table->integer('sa_payroll_heading_id')->nullable();
            $table->string('sa_title', 200);
            $table->mediumText('sa_description')->nullable();
            $table->integer('sa_calculation_type')->nullable();
            $table->decimal('sa_threshold_value', 10)->nullable();
            $table->boolean('sa_consider_for_pf')->nullable()->default(false);
            $table->integer('sa_consider_for_pf_condition')->nullable();
            $table->boolean('sa_consider_for_esic')->nullable()->default(false);
            $table->boolean('sa_calculate_on_prorata_basis')->nullable()->default(false);
            $table->boolean('sa_is_taxable')->nullable()->default(false);
            $table->string('sa_name_in_payslip', 200)->nullable();
            $table->boolean('sa_show_in_payslip')->nullable()->default(true);
            $table->boolean('sa_is_active')->nullable()->default(true);
            $table->integer('sa_sequence_valu')->nullable();
            $table->timestamp('sa_created_at')->useCurrent();
            $table->timestamp('sa_updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_allowances');
    }
};
