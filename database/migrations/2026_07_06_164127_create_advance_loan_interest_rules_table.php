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
        Schema::create('advance_loan_interest_rules', function (Blueprint $table) {
            $table->bigIncrements('alir_id');
            $table->unsignedBigInteger('alir_b_id')->index('idx_alir_b_id');
            $table->unsignedBigInteger('alir_als_id')->index('idx_alir_als_id');
            $table->string('alir_rule_type', 100);
            $table->string('alir_param1')->nullable();
            $table->string('alir_param2')->nullable();
            $table->decimal('alir_rule_rate', 10)->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_loan_interest_rules');
    }
};
