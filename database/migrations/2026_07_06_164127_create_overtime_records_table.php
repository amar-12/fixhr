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
        Schema::create('overtime_records', function (Blueprint $table) {
            $table->integer('otr_id', true);
            $table->integer('otr_b_id')->nullable()->index('otr_b_id');
            $table->bigInteger('otr_emp_id')->index('employee_id');
            $table->integer('otr_attendance_id')->index('attendance_id');
            $table->date('otr_date');
            $table->decimal('otr_hours', 5);
            $table->decimal('otr_amount', 5);
            $table->bigInteger('otr_approved_by')->nullable()->index('overtime_approved_by');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
    }
};
