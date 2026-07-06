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
        Schema::create('payslips', function (Blueprint $table) {
            $table->bigInteger('p_id', true);
            $table->integer('p_b_id')->nullable()->index('p_b_id');
            $table->integer('p_emp_id')->nullable();
            $table->bigInteger('p_pr_id')->index('p_pr_id');
            $table->string('p_payslip_url')->nullable();
            $table->timestamp('p_generated_at')->nullable()->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
