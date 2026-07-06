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
        Schema::create('attendance_punch_log', function (Blueprint $table) {
            $table->integer('apl_id', true);
            $table->unsignedInteger('apl_atd_id')->nullable();
            $table->unsignedInteger('apl_b_id')->nullable();
            $table->unsignedInteger('apl_emp_id');
            $table->string('apl_device_id', 50)->nullable();
            $table->unsignedInteger('apl_pst_id')->nullable();
            $table->unsignedInteger('apl_work_mode_type_id')->nullable();
            $table->unsignedInteger('apl_checkin_method_id')->nullable();
            $table->date('apl_date')->nullable();
            $table->dateTime('apl_check_in_time')->nullable();
            $table->dateTime('apl_check_out_time')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_punch_log');
    }
};
