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
        Schema::create('offline_device_registrations', function (Blueprint $table) {
            $table->bigIncrements('odr_id');
            $table->unsignedBigInteger('odr_b_id')->nullable();
            $table->string('odr_device_model')->nullable();
            $table->string('odr_mac_address')->nullable();
            $table->string('odr_registered_by')->nullable();
            $table->string('odr_device_pin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_device_registrations');
    }
};
