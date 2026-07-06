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
        Schema::create('policy_week_off', function (Blueprint $table) {
            $table->integer('pwo_id', true);
            $table->integer('pwo_b_id');
            $table->string('pwo_name')->nullable();
            $table->string('pwo_day_ids', 200)->nullable();
            $table->string('pwo_recurrence_day_ids', 200)->nullable()->comment('store value in days and recurence day on key value pair form');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->string('pwo_is_unpaid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_week_off');
    }
};
