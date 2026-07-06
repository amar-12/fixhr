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
        Schema::create('salary_tax_slabs', function (Blueprint $table) {
            $table->integer('sts_id');
            $table->integer('sts_b_id')->nullable();
            $table->integer('sts_std_id')->nullable()->comment('primary id of fh_statutory_deductions table');
            $table->integer('sts_state_id')->nullable();
            $table->double('sts_min_income')->nullable();
            $table->double('sts_max_income')->nullable();
            $table->double('sts_tax_percentage_amount')->nullable();
            $table->double('sts_fixed_tax_amount')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_tax_slabs');
    }
};
