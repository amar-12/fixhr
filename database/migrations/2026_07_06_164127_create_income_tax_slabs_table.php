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
        Schema::create('income_tax_slabs', function (Blueprint $table) {
            $table->bigIncrements('its_id');
            $table->integer('its_b_id')->nullable()->index();
            $table->decimal('its_income_from', 15);
            $table->decimal('its_income_to', 15)->nullable();
            $table->decimal('its_tax_rate', 5);
            $table->unsignedBigInteger('its_fy_id')->nullable()->index();
            $table->string('its_regime', 10)->default('new');
            $table->boolean('its_is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_tax_slabs');
    }
};
