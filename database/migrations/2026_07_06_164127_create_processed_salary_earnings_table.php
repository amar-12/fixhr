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
        Schema::create('processed_salary_earnings', function (Blueprint $table) {
            $table->integer('ps_e_id', true);
            $table->integer('ps_id')->nullable();
            $table->string('ps_earning_type_id', 20)->nullable();
            $table->string('ps_earning_type')->nullable();
            $table->decimal('ps_e_amount', 10)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_salary_earnings');
    }
};
