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
        Schema::create('policy_tada_daily_allowance', function (Blueprint $table) {
            $table->integer('ptda_id', true);
            $table->integer('ptda_b_id')->nullable()->index('ptda_b_id');
            $table->integer('ptda_ptc_id')->nullable()->index('ptda_ptc_id');
            $table->integer('ptda_pttt_id')->nullable()->index('ptda_pttt_id');
            $table->integer('ptda_da_cal_type_id')->nullable()->index('ptda_da_cal_type_id');
            $table->string('ptda_da_cal_limit', 100)->nullable();
            $table->double('ptda_da_amount')->nullable();
            $table->double('ptda_da_amount2')->nullable();
            $table->integer('ptda_per_day')->nullable();
            $table->integer('ptda_distance')->nullable();
            $table->tinyInteger('ptda_lodging')->nullable();
            $table->tinyInteger('ptda_half_da')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_daily_allowance');
    }
};
