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
        Schema::create('attendance_shift_policy', function (Blueprint $table) {
            $table->bigIncrements('asp_id');
            $table->integer('asp_b_id')->index('fh_attendance_shift_policy_asp_b_id_foreign');
            $table->integer('asp_shift_type')->index('fh_attendance_shift_policy_asp_shift_type_foreign');
            $table->string('asp_shift_type_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_shift_policy');
    }
};
