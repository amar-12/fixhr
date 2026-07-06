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
        Schema::create('advance_loan_settings', function (Blueprint $table) {
            $table->bigIncrements('als_id');
            $table->unsignedBigInteger('als_b_id');
            $table->string('als_loan_advance_name', 150)->nullable();
            $table->enum('als_limit_type', ['FIXED', 'PERCENTAGE'])->nullable();
            $table->decimal('als_fixed_limit', 12)->nullable();
            $table->decimal('als_percentage_limit', 5)->nullable();
            $table->boolean('als_permanent_only')->nullable()->default(false);
            $table->integer('als_min_employment')->nullable();
            $table->boolean('als_enable_age_criteria')->nullable()->default(false);
            $table->integer('als_max_age')->nullable();
            $table->boolean('als_apply_interest')->nullable()->default(false);
            $table->decimal('als_interest_rate', 5)->nullable();
            $table->enum('als_interest_scope', ['LOAN', 'ADVANCE', 'BOTH'])->nullable()->default('BOTH');
            $table->boolean('als_interest_exceed_installments')->nullable()->default(false);
            $table->decimal('als_interest_exceed_rate', 5)->nullable();
            $table->integer('als_interest_if_loan_multiplier')->nullable();
            $table->integer('als_interest_if_loan_months')->nullable();
            $table->decimal('als_interest_if_loan_rate', 5)->nullable();
            $table->integer('als_interest_if_loan_greater_multiplier')->nullable();
            $table->integer('als_interest_if_loan_greater_months')->nullable();
            $table->decimal('als_interest_if_loan_greater_rate', 5)->nullable();
            $table->integer('als_max_concurrent')->nullable();
            $table->integer('als_max_repayment')->nullable();
            $table->integer('als_min_repayment')->nullable();
            $table->integer('als_min_repaymnt')->nullable();
            $table->boolean('als_status')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_loan_settings');
    }
};
