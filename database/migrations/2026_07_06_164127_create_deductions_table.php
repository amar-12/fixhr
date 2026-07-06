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
        Schema::create('deductions', function (Blueprint $table) {
            $table->bigInteger('deduct_id', true);
            $table->integer('deduct_b_id')->nullable()->index('deduct_b_id');
            $table->bigInteger('deduct_pr_id')->index('deduct_pr_id');
            $table->string('deduct_description');
            $table->decimal('deduct_amount', 10);
            $table->enum('deduct_deduction_type', ['Tax', 'Insurance', 'Loan', 'Other']);
            $table->date('deduct_effective_date');
            $table->boolean('deduct_is_active')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
