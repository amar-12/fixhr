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
        Schema::create('shift_calendar', function (Blueprint $table) {
            $table->integer('sc_id', true);
            $table->integer('sc_b_id')->index('fk_fh_shift_calendar_fh_businesses');
            $table->integer('sc_pst_id')->index('fk_fh_shift_calendar_fh_policy_shift_timings');
            $table->bigInteger('sc_emp_id')->index('fk_fh_shift_calendar_fh_employees');
            $table->date('sc_start_date');
            $table->date('sc_end_date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_calendar');
    }
};
