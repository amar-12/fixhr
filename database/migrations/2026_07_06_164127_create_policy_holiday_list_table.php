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
        Schema::create('policy_holiday_list', function (Blueprint $table) {
            $table->integer('phl_id', true);
            $table->integer('phl_b_id')->nullable()->index('ph1_b_id');
            $table->integer('phl_type_id')->nullable()->index('phl_type_id');
            $table->integer('phl_day_type_id')->default(201);
            $table->integer('phl_day_segment_id')->nullable();
            $table->string('phl_name', 100);
            $table->date('phl_start_date');
            $table->date('phl_end_date')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_holiday_list');
    }
};
