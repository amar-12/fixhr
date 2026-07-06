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
        Schema::create('benefits', function (Blueprint $table) {
            $table->bigInteger('benefit_id', true);
            $table->integer('benefit_b_id')->nullable()->index('benefit_b_id');
            $table->bigInteger('benefit_emp_id')->index('benefit_emp_id');
            $table->string('benefit_type', 100)->nullable();
            $table->decimal('benefit_amount', 10)->nullable();
            $table->date('benefit_effective_date')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefits');
    }
};
