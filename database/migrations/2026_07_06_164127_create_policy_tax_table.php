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
        Schema::create('policy_tax', function (Blueprint $table) {
            $table->bigInteger('pt_id');
            $table->integer('pt_b_id')->nullable();
            $table->string('pt_name', 100);
            $table->decimal('pt_tax_rate', 5);
            $table->year('pt_tax_year');
            $table->decimal('pt_tax_paid', 10);
            $table->decimal('pt_min_income', 10);
            $table->decimal('pt_max_income', 10);
            $table->date('pt_effective_date')->nullable();
            $table->date('pt_expiration_date')->nullable();
            $table->decimal('pt_exemption_amount', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tax');
    }
};
