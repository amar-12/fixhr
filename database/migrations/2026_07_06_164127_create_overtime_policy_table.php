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
        Schema::create('overtime_policy', function (Blueprint $table) {
            $table->bigIncrements('ot_id');
            $table->unsignedInteger('ot_b_id');
            $table->unsignedTinyInteger('ot_is_enabled');
            $table->decimal('ot_working_day')->nullable()->comment('Working day OT rate or hours');
            $table->decimal('ot_non_working_day')->nullable()->comment('Non-working day OT rate or hours');
            $table->integer('ot_max_co_per_day')->nullable()->comment('Max compensatory offs per day');
            $table->string('ot_shift_type', 50)->nullable()->comment('Shift type (Fixed/Flexible/etc)');
            $table->string('ot_break_type', 50)->nullable()->comment('Break type (Paid/Unpaid/etc)');
            $table->integer('ot_min_work_per_day')->nullable()->comment('Minimum working minutes per day to qualify for OT');
            $table->integer('ot_min_work_required')->nullable()->comment('Minimum work required for OT eligibility');
            $table->integer('ot_max_work_per_day')->nullable()->comment('Maximum OT minutes allowed per day');
            $table->integer('ot_max_work_per_month')->nullable()->comment('Maximum OT minutes allowed per month');
            $table->enum('ot_calculation_method', ['in_time', 'out_time', 'both_in_out_time'])->nullable()->default('in_time')->comment('OT calculation basis');
            $table->integer('ot_buffer_mins_per_day')->nullable()->default(0)->comment('Buffer minutes before OT applies');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_policy');
    }
};
