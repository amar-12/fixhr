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
        Schema::create('travel_locations', function (Blueprint $table) {
            $table->bigIncrements('lc_id');
            $table->integer('lc_trp_id')->nullable();
            $table->integer('lc_trd_id')->nullable();
            $table->bigInteger('lc_emp_id')->nullable();
            $table->longText('locations')->nullable();
            $table->double('lc_total_distance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_locations');
    }
};
