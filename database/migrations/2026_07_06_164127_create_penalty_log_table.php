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
        Schema::create('penalty_log', function (Blueprint $table) {
            $table->bigIncrements('pl_id');
            $table->unsignedBigInteger('pl_b_id')->nullable()->comment('Branch ID');
            $table->unsignedBigInteger('pl_ar_id')->nullable()->comment('Attendance Record ID');
            $table->decimal('pl_emp_salary', 10)->nullable()->comment('Employee salary');
            $table->time('pl_late_time1')->nullable()->comment('First late time');
            $table->decimal('pl_late_amount1', 10)->nullable()->comment('First late penalty amount');
            $table->time('pl_late_time2')->nullable()->comment('Second late time');
            $table->decimal('pl_late_amount2', 10)->nullable()->comment('Second late penalty amount');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_log');
    }
};
