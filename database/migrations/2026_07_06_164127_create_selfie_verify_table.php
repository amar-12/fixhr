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
        Schema::create('selfie_verify', function (Blueprint $table) {
            $table->bigIncrements('sv_id');
            $table->string('sv_emp_code', 50)->nullable();
            $table->unsignedBigInteger('sv_emp_id')->index('sv_emp_id');
            $table->unsignedBigInteger('sv_emp_b_id')->nullable()->index('sv_emp_b_id');
            $table->string('sv_emp_name', 150)->nullable();
            $table->string('sv_device_id', 150)->nullable();
            $table->string('sv_device_name', 150)->nullable();
            $table->string('sv_device_modal', 150)->nullable();
            $table->string('sv_status', 1)->nullable()->default('0');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selfie_verify');
    }
};
