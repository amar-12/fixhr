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
        Schema::create('early_going_automation', function (Blueprint $table) {
            $table->integer('ega_id', true);
            $table->integer('ega_b_id');
            $table->boolean('ega_is_penalty_enabled');
            $table->time('ega_exit_before')->nullable();
            $table->float('ega_penalty_amount')->nullable();
            $table->integer('ega_no_early')->nullable();
            $table->float('ega_days_to_deduct')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('early_going_automation');
    }
};
