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
        Schema::create('professional_tax_master', function (Blueprint $table) {
            $table->integer('ptm_id', true);
            $table->bigInteger('ptm_s_id')->nullable();
            $table->decimal('ptm_income_from', 10, 0)->nullable();
            $table->decimal('ptm_income_to', 10, 0)->nullable();
            $table->decimal('ptm_tax_amount', 10, 0)->nullable();
            $table->bigInteger('ptm_cycle_id')->nullable();
            $table->bigInteger('ptm_gender_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_tax_master');
    }
};
