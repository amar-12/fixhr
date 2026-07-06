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
        Schema::create('payroll_master_settings', function (Blueprint $table) {
            $table->integer('pms_id', true);
            $table->integer('pms_b_id')->index('pms_b_id');
            $table->boolean('pms_is_locked')->nullable()->default(false);
            $table->integer('pms_payroll_cycle')->nullable()->default(440)->index('pms_payroll_cycle');
            $table->string('pms_payroll_mode')->nullable();
            $table->integer('pms_year_type')->nullable();
            $table->string('pms_phone', 20)->nullable();
            $table->boolean('pms_include_with_Salary')->nullable()->default(false);
            $table->boolean('pms_include_tada_with_salary')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_master_settings');
    }
};
