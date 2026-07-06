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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->bigIncrements('rt_id');
            $table->unsignedBigInteger('rt_emp_id');
            $table->unsignedBigInteger('rt_emp_d_id')->nullable();
            $table->unsignedBigInteger('rt_b_id');
            $table->string('rt_type', 50)->nullable();
            $table->unsignedBigInteger('rt_component_id')->nullable();
            $table->decimal('rt_e_amount', 10)->nullable();
            $table->decimal('rt_d_amount', 10)->nullable();
            $table->decimal('rt_amount', 12)->nullable()->default(0);
            $table->string('rt_start_month')->nullable();
            $table->string('rt_end_month')->nullable();
            $table->boolean('rt_is_active')->nullable()->default(true);
            $table->unsignedBigInteger('rt_created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
