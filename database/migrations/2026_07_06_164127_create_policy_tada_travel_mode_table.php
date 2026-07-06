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
        Schema::create('policy_tada_travel_mode', function (Blueprint $table) {
            $table->integer('pttm_id', true);
            $table->integer('pttm_b_id')->index('pttm_b_id');
            $table->integer('pttm_pttt_id')->nullable()->index('pttm_pttt_id')->comment('Travel Type (Local, Outstation, International)');
            $table->integer('pttm_by_mode_id')->nullable()->index('pttm_by_mode_id')->comment('By Air, By Train, By Road');
            $table->boolean('pttm_status')->nullable()->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_travel_mode');
    }
};
