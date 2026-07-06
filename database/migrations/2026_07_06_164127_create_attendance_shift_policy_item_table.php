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
        Schema::create('attendance_shift_policy_item', function (Blueprint $table) {
            $table->bigIncrements('aspi_id');
            $table->integer('aspi_b_id')->index();
            $table->unsignedBigInteger('aspi_asp_id')->index();
            $table->string('aspi_shift_name');
            $table->time('aspi_shift_start')->nullable();
            $table->time('aspi_shift_end')->nullable();
            $table->integer('aspi_break_minute');
            $table->integer('aspi_break_type')->index();
            $table->integer('aspi_punch_begin_before')->nullable();
            $table->integer('aspi_punch_end_after')->nullable();
            $table->integer('aspi_grace_time')->nullable();
            $table->integer('aspi_partial_day_on')->nullable()->index('fh_attendance_shift_policy_item_aspi_partial_day_on_foreign');
            $table->time('aspi_begins_at')->nullable();
            $table->time('aspi_end_at')->nullable();
            $table->integer('aspi_shift_hour')->nullable();
            $table->integer('aspi_shift_minutes')->nullable();
            $table->time('aspi_working_duration')->nullable();
            $table->boolean('aspi_is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_shift_policy_item');
    }
};
