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
        Schema::create('plan_price_slabs', function (Blueprint $table) {
            $table->bigIncrements('id')->index('id');
            $table->unsignedBigInteger('plan_id');
            $table->integer('min_employees')->nullable();
            $table->integer('max_employees')->nullable();
            $table->decimal('price_per_user', 10);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_price_slabs');
    }
};
