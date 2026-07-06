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
        Schema::create('policy_deduction', function (Blueprint $table) {
            $table->bigInteger('pd_id', true);
            $table->integer('pd_b_id')->nullable()->index('pd_b_id');
            $table->string('pd_type', 100)->nullable();
            $table->decimal('pd_max_deduction_percentage', 5)->nullable();
            $table->date('pd_effective_date')->nullable();
            $table->date('pd_expiration_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_deduction');
    }
};
