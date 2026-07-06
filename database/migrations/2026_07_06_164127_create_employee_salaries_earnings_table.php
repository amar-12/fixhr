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
        Schema::create('employee_salaries_earnings', function (Blueprint $table) {
            $table->integer('es_e_id', true);
            $table->integer('es_sa_id')->nullable();
            $table->integer('es_e_b_id')->nullable();
            $table->integer('es_e_emp_id')->nullable();
            $table->integer('es_e_type_id')->nullable();
            $table->integer('es_e_cal_type_id')->nullable();
            $table->decimal('es_e_amount', 10)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries_earnings');
    }
};
