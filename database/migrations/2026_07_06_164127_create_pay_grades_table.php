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
        Schema::create('pay_grades', function (Blueprint $table) {
            $table->bigInteger('pg_id', true);
            $table->integer('pg_b_id')->nullable()->index('pg_b_id');
            $table->integer('pg_grade_id')->index('pg_grade_id');
            $table->decimal('pg_min_salary', 10);
            $table->decimal('pg_max_salary', 10);
            $table->boolean('pg_benefits_eligibility')->nullable()->default(true);
            $table->decimal('pg_bonus_percentage', 5)->nullable()->default(0);
            $table->decimal('pg_overtime_rate', 5)->nullable()->default(1.5);
            $table->integer('pg_leave_allowance')->nullable()->default(30);
            $table->text('pg_description')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_grades');
    }
};
