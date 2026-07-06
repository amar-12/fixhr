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
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('plan_id');
            $table->string('name');
            $table->string('code', 50);
            $table->enum('billing_cycle', ['monthly', 'yearly', 'lifetime'])->nullable()->default('monthly');
            $table->text('description')->nullable();
            $table->json('included_modules')->nullable();
            $table->integer('sort_order')->nullable()->default(0);
            $table->boolean('is_active')->nullable()->default(true);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
