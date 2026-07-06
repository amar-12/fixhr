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
        Schema::create('trip_distances_calculation', function (Blueprint $table) {
            $table->integer('tdc_id', true);
            $table->integer('tdc_trd_id')->nullable();
            $table->integer('tdc_emp_id')->nullable();
            $table->integer('tdc_trp_id')->nullable();
            $table->integer('tdc_b_id')->nullable();
            $table->string('tdc_emp_name')->nullable();
            $table->string('tdc_vehicle_type')->nullable();
            $table->double('tdc_avg_distance')->nullable();
            $table->double('tdc_weighted_avg_cal')->nullable();
            $table->text('tdc_route_1_km')->nullable();
            $table->text('tdc_route_2_km')->nullable();
            $table->text('tdc_route_3_km')->nullable();
            $table->text('tdc_route_4_km')->nullable();
            $table->double('tdc_distance_difference_km')->nullable();
            $table->json('tdc_route_leg_distances')->nullable();
            $table->text('tdc_segment')->nullable();
            $table->json('tdc_directions_response')->nullable();
            $table->text('tdc_routes')->nullable();
            $table->json('tdc_route_legs')->nullable();
            $table->string('tdc_weightage')->nullable();
            $table->string('tdc_punch_type')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_distances_calculation');
    }
};
