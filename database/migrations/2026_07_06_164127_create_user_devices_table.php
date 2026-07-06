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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->bigIncrements('ud_id');
            $table->string('ud_emp_code');
            $table->integer('ud_emp_id')->nullable();
            $table->integer('ud_emp_b_id')->nullable();
            $table->string('ud_emp_name')->nullable();
            $table->string('ud_device_id')->nullable();
            $table->string('ud_status')->nullable()->default('varchar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
