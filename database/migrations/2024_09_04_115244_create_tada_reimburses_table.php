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
        Schema::create('tada_reimburses', function (Blueprint $table) {
            $table->increments('tr_id'); // Auto-incrementing primary key
            $table->integer('tr_b_id');
            $table->json('tr_claims_id'); // Store multiple values as JSON
            $table->decimal('tr_amount', 8, 2); // Assuming you want to store a decimal amount
            $table->timestamp('created_at')->useCurrent(); // Default to CURRENT_TIMESTAMP
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Default to CURRENT_TIMESTAMP and update on row change
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tada_reimburses');
    }
};
