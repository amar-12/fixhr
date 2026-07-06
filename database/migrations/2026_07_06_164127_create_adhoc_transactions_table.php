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
        Schema::create('adhoc_transactions', function (Blueprint $table) {
            $table->integer('at_id', true);
            $table->integer('at_b_id')->nullable();
            $table->integer('at_emp_id')->nullable();
            $table->integer('at_emp_d_id')->nullable();
            $table->integer('at_fy_id')->nullable();
            $table->integer('at_pp_id')->nullable();
            $table->string('at_e_amount')->default('0');
            $table->string('at_d_amount')->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adhoc_transactions');
    }
};
