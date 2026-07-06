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
        Schema::create('statutory_deductions', function (Blueprint $table) {
            $table->integer('std_id', true);
            $table->integer('std_b_id');
            $table->integer('std_deduction_type_id')->nullable();
            $table->integer('std_deduction_cycle_id')->nullable();
            $table->decimal('std_employee_contri_rate_amount', 10)->nullable();
            $table->decimal('std_employer_contri_rate_amount', 10)->nullable();
            $table->decimal('std_threshold', 10)->nullable();
            $table->boolean('std_status')->nullable()->default(true);
            $table->dateTime('std_created_at')->nullable()->useCurrent();
            $table->dateTime('std_updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_deductions');
    }
};
