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
        Schema::create('policy_salary_structure', function (Blueprint $table) {
            $table->bigInteger('ssp_id', true);
            $table->integer('ssp_b_id')->nullable()->index('ssp_b_id');
            $table->bigInteger('ssp_pg_id')->index('ssp_pg_id');
            $table->decimal('ssp_min_salary', 10)->nullable();
            $table->decimal('ssp_max_salary', 10)->nullable();
            $table->string('ssp_currency', 10)->nullable()->default('USD');
            $table->date('ssp_effective_date')->nullable();
            $table->date('ssp_expiration_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_salary_structure');
    }
};
