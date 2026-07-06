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
        Schema::create('payroll_loan_installments', function (Blueprint $table) {
            $table->integer('pli_id', true);
            $table->integer('pli_b_id')->nullable();
            $table->integer('pli_loan_id')->nullable();
            $table->integer('pli_installment_no')->nullable();
            $table->double('pli_amount')->nullable();
            $table->double('pli_rem_bal')->nullable();
            $table->dateTime('pli_due_date')->nullable();
            $table->boolean('pli_month')->nullable();
            $table->integer('pli_year')->nullable();
            $table->decimal('pli_opening_balance', 12)->default(0);
            $table->decimal('pli_principal', 12)->default(0);
            $table->decimal('pli_interest', 12)->default(0);
            $table->string('pli_status', 20)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_loan_installments');
    }
};
