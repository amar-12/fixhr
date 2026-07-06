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
        Schema::create('attendance_payroll', function (Blueprint $table) {
            $table->bigInteger('atdp_id', true);
            $table->integer('atdp_b_id')->nullable()->index('atdp_b_id');
            $table->bigInteger('atdp_emp_id')->index('atdp_emp_id');
            $table->date('atdp_date');
            $table->decimal('atdp_hours_worked', 5)->nullable();
            $table->decimal('atdp_overtime_hours', 5)->nullable()->default(0);
            $table->enum('status', ['Present', 'Absent', 'Late', 'Early Exit'])->nullable();
            $table->bigInteger('atdp_pr_id')->nullable()->index('atdp_pr_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_payroll');
    }
};
