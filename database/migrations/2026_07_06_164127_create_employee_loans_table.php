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
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->bigInteger('el_id', true);
            $table->integer('el_b_id')->nullable()->index('el_b_id');
            $table->bigInteger('el_emp_id')->index('el_emp_id');
            $table->decimal('el_loan_amount', 10)->nullable();
            $table->decimal('el_loan_balance', 10)->nullable();
            $table->decimal('el_interest_rate', 5)->nullable();
            $table->decimal('el_monthly_installment', 10)->nullable();
            $table->enum('el_loan_status', ['Pending', 'Approved', 'Rejected', 'Closed'])->nullable()->default('Pending');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};
