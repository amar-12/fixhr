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
        Schema::create('payroll_period_weeks', function (Blueprint $table) {
            $table->bigIncrements('ppw_id');
            $table->unsignedBigInteger('ppw_pp_id')->index('idx_ppw_pp_id');
            $table->unsignedBigInteger('ppw_b_id')->index('idx_ppw_b_id');
            $table->unsignedBigInteger('ppw_fy_id');
            $table->unsignedBigInteger('ppw_month_id')->nullable();
            $table->integer('ppw_week_number');
            $table->string('ppw_week_name', 100);
            $table->date('ppw_start_date');
            $table->date('ppw_end_date');
            $table->string('ppw_status', 50)->default('pending')->index('idx_ppw_status');
            $table->text('ppw_description')->nullable();
            $table->json('ppw_metadata')->nullable();
            $table->integer('ppw_employee_count')->default(0);
            $table->integer('ppw_processed_count')->default(0);
            $table->boolean('ppw_is_frozen')->default(false);
            $table->boolean('ppw_is_processed')->default(false);
            $table->timestamp('ppw_processed_at')->nullable();
            $table->unsignedBigInteger('ppw_created_by')->nullable();
            $table->unsignedBigInteger('ppw_updated_by')->nullable();
            $table->bigInteger('ppw_processed_by')->nullable();
            $table->timestamp('ppw_frozen_at')->nullable();
            $table->bigInteger('ppw_frozen_by')->nullable();
            $table->timestamps();

            $table->unique(['ppw_pp_id', 'ppw_week_number'], 'unique_week_per_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_period_weeks');
    }
};
