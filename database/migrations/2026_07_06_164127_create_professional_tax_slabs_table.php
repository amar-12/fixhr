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
        Schema::create('professional_tax_slabs', function (Blueprint $table) {
            $table->integer('pts_id');
            $table->integer('pts_b_id')->nullable();
            $table->integer('pts_br_id')->nullable();
            $table->decimal('pts_income_from', 10)->nullable();
            $table->decimal('pts_income_to', 10)->nullable();
            $table->decimal('pts_tax_amount', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_tax_slabs');
    }
};
