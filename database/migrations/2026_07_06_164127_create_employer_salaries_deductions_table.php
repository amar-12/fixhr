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
        Schema::create('employer_salaries_deductions', function (Blueprint $table) {
            $table->integer('employer_sd_id', true);
            $table->integer('employer_sd_b_id')->nullable();
            $table->integer('employer_sd_emp_id')->nullable();
            $table->integer('employer_sd_type_id')->nullable();
            $table->integer('employer_sd_cal_type_id')->nullable();
            $table->double('employer_sd_amount')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_salaries_deductions');
    }
};
