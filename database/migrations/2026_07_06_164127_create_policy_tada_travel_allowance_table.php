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
        Schema::create('policy_tada_travel_allowance', function (Blueprint $table) {
            $table->integer('ptta_id', true);
            $table->integer('ptta_b_id')->nullable()->index('ptta_b_id');
            $table->integer('ptta_ptc_id')->nullable()->index('ptta_ptc_id');
            $table->integer('ptta_pttt_id')->nullable()->index('ptta_pttt_id');
            $table->integer('ptta_pttm_id')->nullable()->index('ptta_pttm_id');
            $table->integer('ptta_pttv_id')->nullable()->index('ptta_pttv_id');
            $table->integer('ptta_claim_type_id')->nullable()->index('ptta_claim_type_id');
            $table->double('ptta_eligibility')->nullable()->default(0);
            $table->double('ptta_other_eligibility')->nullable()->default(0);
            $table->string('ptta_remarks')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_travel_allowance');
    }
};
