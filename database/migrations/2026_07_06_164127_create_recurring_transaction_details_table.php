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
        Schema::create('recurring_transaction_details', function (Blueprint $table) {
            $table->bigIncrements('rtd_id');
            $table->unsignedBigInteger('rtd_recurring_transaction_id')->nullable();
            $table->unsignedBigInteger('rtd_component_id')->nullable();
            $table->decimal('rtd_earning_amount', 12)->nullable();
            $table->decimal('rtd_deduction_amount', 12)->nullable();
            $table->string('rtd_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transaction_details');
    }
};
