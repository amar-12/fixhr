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
        Schema::create('attendance_shift_type', function (Blueprint $table) {
            $table->integer('ast_id', true);
            $table->integer('ast_b_id')->nullable();
            $table->string('ast_name')->nullable();
            $table->integer('ast_type_id')->nullable();
            $table->time('ast_start_time')->nullable();
            $table->time('ast_end_time')->nullable();
            $table->double('ast_total_work_hour')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_shift_type');
    }
};
