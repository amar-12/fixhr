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
        Schema::create('late_coming_automation', function (Blueprint $table) {
            $table->integer('lca_id', true);
            $table->integer('lca_b_id');
            $table->boolean('lca_is_penalty_enabled');
            $table->time('lca_late_till')->nullable();
            $table->float('lca_penalty_amount')->nullable();
            $table->integer('lca_no_late')->nullable();
            $table->float('lca_days_to_deduct')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('late_coming_automation');
    }
};
