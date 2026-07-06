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
        Schema::create('payroll_template', function (Blueprint $table) {
            $table->integer('pt_id', true);
            $table->integer('pt_b_id')->nullable();
            $table->text('pt_temp_name')->nullable();
            $table->text('pt_temp_description')->nullable();
            $table->double('pt_an_ctc')->nullable();
            $table->double('pt_m_ctc')->nullable();
            $table->text('pt_component_type')->nullable();
            $table->integer('pt_component_list_id')->nullable();
            $table->decimal('pt_total_earning', 10)->nullable();
            $table->decimal('pt_gross_salary', 10)->nullable();
            $table->decimal('pt_total_deduction', 10)->nullable();
            $table->decimal('pt_net_pay', 10)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_template');
    }
};
