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
        Schema::create('labour_welfare_fund_master', function (Blueprint $table) {
            $table->integer('lwf_id', true);
            $table->integer('state_id')->nullable();
            $table->integer('cycle_id')->nullable();
            $table->integer('lwf_employee_contri')->nullable();
            $table->integer('lwf_employer_contri')->nullable();
            $table->integer('total_contribution')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labour_welfare_fund_master');
    }
};
