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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->integer('ads_id', true);
            $table->integer('ads_atd_id')->nullable()->index('ads_atd_id');
            $table->integer('ads_b_id')->nullable()->index('atd_b_id');
            $table->bigInteger('ads_emp_id')->index('atd_emp_id');
            $table->string('ads_device_id')->nullable();
            $table->date('ads_date')->nullable();
            $table->dateTime('ads_time')->nullable();
            $table->string('ads_latitude', 20)->nullable();
            $table->string('ads_longitude', 20)->nullable();
            $table->string('ads_location', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
