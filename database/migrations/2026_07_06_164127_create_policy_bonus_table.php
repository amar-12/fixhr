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
        Schema::create('policy_bonus', function (Blueprint $table) {
            $table->bigInteger('pb_id', true);
            $table->integer('pb_b_id')->nullable()->index('pb_b_id');
            $table->string('pb_bonus_criteria')->nullable();
            $table->decimal('pb_max_bonus_amount', 10)->nullable();
            $table->date('pb_effective_date')->nullable();
            $table->date('pb_expiration_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_bonus');
    }
};
