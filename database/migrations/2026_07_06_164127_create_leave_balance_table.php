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
        Schema::create('leave_balance', function (Blueprint $table) {
            $table->bigInteger('lb_id', true);
            $table->integer('lb_b_id')->index('lb_b_id');
            $table->bigInteger('lb_emp_id')->index('lb_emp_id');
            $table->integer('lb_cat_type_id')->index('fh_leave_balance_ibfk_3');
            $table->integer('lb_year')->nullable();
            $table->integer('lb_month')->nullable();
            $table->decimal('lb_alloted_leave', 5)->nullable();
            $table->decimal('lb_taken_leave', 5)->nullable();
            $table->decimal('lb_balance_remaining_leave', 5)->nullable();
            $table->decimal('lb_carried_forward', 5)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance');
    }
};
