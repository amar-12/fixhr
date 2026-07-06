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
        Schema::create('policy_tada_travel_vehicle', function (Blueprint $table) {
            $table->integer('pttv_id', true);
            $table->integer('pttv_b_id')->nullable()->index('pttv_b_id');
            $table->integer('pttv_ptc_id')->nullable();
            $table->integer('pttv_pttm_id')->nullable()->index('pttv_pttm_id')->comment('from travel mode table');
            $table->integer('pttv_vehicle_id')->nullable()->index('pttv_vehicle_id')->comment('from master (Car, Bus, Train etc)');
            $table->integer('pttv_owner_id')->nullable()->index('pttv_owner_id')->comment('from master (Personal, Office)');
            $table->integer('pttv_class_id')->nullable()->index('pttv_class_id')->comment('from master (economy, 2ac, 3ac)');
            $table->integer('pttv_claim_type_id')->nullable()->index('pttv_claim_type_id');
            $table->double('pttv_eligibility')->nullable()->default(0);
            $table->tinyInteger('pttv_is_conveyance')->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_travel_vehicle');
    }
};
