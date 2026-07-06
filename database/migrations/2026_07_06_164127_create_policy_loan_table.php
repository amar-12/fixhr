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
        Schema::create('policy_loan', function (Blueprint $table) {
            $table->bigInteger('pl_id', true);
            $table->integer('pl_b_id')->nullable()->index('pl_b_id');
            $table->decimal('pl_max_loan_amount', 10)->nullable();
            $table->integer('pl_max_loan_duration')->nullable();
            $table->decimal('pl_interest_rate', 5)->nullable();
            $table->boolean('pl_requires_approval')->nullable()->default(true);
            $table->date('pl_effective_date')->nullable();
            $table->date('pl_expiration_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_loan');
    }
};
