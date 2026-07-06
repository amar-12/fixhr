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
        Schema::create('financial_years', function (Blueprint $table) {
            $table->integer('fy_id', true);
            $table->integer('fy_b_id')->nullable();
            $table->string('fy_year', 9)->nullable();
            $table->date('fy_start_date')->nullable();
            $table->date('fy_end_date')->nullable();
            $table->boolean('fy_is_current')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->unique(['fy_year', 'fy_b_id'], 'unique_fy_year_per_business');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};
