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
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id('ar_id');
            $table->integer('ar_b_id')->nullable();
            $table->integer('ar_rule_type')->index(); // Ensure the correct type
            $table->boolean('ar_is_enabled')->default(false);
            $table->integer('ar_occurrences')->nullable();
            $table->integer('ar_mark_absent')->nullable(); // Ensure the correct type
            $table->time('ar_mark_half_day_time')->nullable();
            $table->boolean('ar_allow_overtime_early')->default(false);
            $table->boolean('ar_allow_overtime_late')->default(false);
            $table->time('ar_min_overtime')->nullable();
            $table->time('ar_max_overtime')->nullable();
            $table->integer('ar_apply_before_day')->nullable();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('ar_rule_type')
                  ->references('m_id')
                  ->on('master_table')
                  ->onDelete('restrict');

            $table->foreign('ar_mark_absent')
                  ->references('m_id')
                  ->on('master_table')
                  ->onDelete('restrict');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
