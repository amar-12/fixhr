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
        Schema::create('adhoc_transaction_details', function (Blueprint $table) {
            $table->integer('atd_id', true);
            $table->integer('adhoc_transaction_id')->nullable();
            $table->integer('component_id')->nullable();
            $table->string('remarks')->nullable();
            $table->decimal('earning_amount', 10)->nullable();
            $table->decimal('deduction_amount', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adhoc_transaction_details');
    }
};
