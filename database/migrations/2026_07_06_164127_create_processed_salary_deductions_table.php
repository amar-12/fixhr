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
        Schema::create('processed_salary_deductions', function (Blueprint $table) {
            $table->integer('ps_d_id', true);
            $table->integer('ps_id')->nullable();
            $table->integer('ps_deduction_type_id')->nullable();
            $table->string('ps_deduction_type')->nullable();
            $table->string('ps_d_category')->nullable();
            $table->string('ps_d_amount')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_salary_deductions');
    }
};
