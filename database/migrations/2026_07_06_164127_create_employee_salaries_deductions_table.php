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
        Schema::create('employee_salaries_deductions', function (Blueprint $table) {
            $table->integer('es_d_id', true);
            $table->integer('es_d_b_id')->nullable();
            $table->integer('es_d_emp_id')->nullable();
            $table->integer('es_d_type_id')->nullable();
            $table->integer('es_d_cal_type_id')->nullable();
            $table->double('es_d_amount')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries_deductions');
    }
};
