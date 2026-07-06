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
        Schema::create('payroll_loan_account', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('pla_b_id')->nullable();
            $table->bigInteger('pla_emp_id')->nullable()->index('payroll_loanaccount_employee_id_id_aa0f9eef_fk_employee_');
            $table->boolean('is_active')->nullable();
            $table->string('type', 15)->nullable();
            $table->string('title', 20)->nullable();
            $table->double('loan_amount')->nullable();
            $table->date('provided_date')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_fixed')->nullable();
            $table->double('rate')->nullable();
            $table->double('installment_amount')->nullable();
            $table->integer('installments')->nullable();
            $table->date('installment_start_date')->nullable();
            $table->string('apply_on', 20)->nullable();
            $table->boolean('settled')->nullable();
            $table->dateTime('settled_date', 6)->nullable();
            $table->bigInteger('allowance_id_id')->nullable()->index('payroll_loanaccount_allowance_id_id_d01d19ed_fk_payroll_a');
            $table->bigInteger('asset_id_id')->nullable()->index('payroll_loanaccount_asset_id_id_b1a82434_fk_asset_asset_id');
            $table->integer('created_by_id')->nullable()->index('payroll_loanaccount_created_by_id_c44cc155_fk_auth_user_id');
            $table->integer('modified_by_id')->nullable()->index('payroll_loanaccount_modified_by_id_4b5d7154_fk_auth_user_id');
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_loan_account');
    }
};
