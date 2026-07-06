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
        Schema::create('attendance_devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('device_name');
            $table->string('short_name', 100)->nullable();
            $table->string('serial_name');
            $table->string('device_location')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('company')->nullable();
            $table->string('com_key', 100)->nullable();
            $table->string('machine_id', 20)->nullable();
            $table->integer('br_id')->comment('branch id');
            $table->integer('b_id')->comment('business id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->boolean('auto_sync_enabled')->nullable()->default(false);
            $table->string('sync_schedule_type', 20)->nullable()->default('daily');
            $table->time('sync_time')->nullable();
            $table->json('sync_days')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('last_sync_result')->nullable();
            $table->timestamp('next_sync_at')->nullable();

            $table->unique(['serial_name', 'b_id'], 'unique_serial_per_business');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_devices');
    }
};
