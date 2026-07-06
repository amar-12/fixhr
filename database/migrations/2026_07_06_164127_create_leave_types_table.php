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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->integer('lvt_id', true);
            $table->integer('lvt_pl_id')->nullable()->index('lvt_pl_id');
            $table->integer('lvt_cat_type_id')->nullable()->index('lvt_cat_type_id');
            $table->integer('lvt_leave_cycle_id')->nullable()->index('lvt_leave_cycle_id');
            $table->decimal('lvt_days_per_year', 10)->nullable();
            $table->integer('lvt_unused_leave_rule_id')->nullable();
            $table->double('lvt_leave_accrual_rate')->nullable();
            $table->decimal('lvt_carry_forward', 10)->nullable();
            $table->integer('lvt_applicable_to_id')->nullable()->index('lvt_applicable_to_id');
            $table->boolean('lvt_is_sandwich')->nullable()->default(false);
            $table->double('lvt_el_per_period')->nullable();
            $table->boolean('lvt_encashable')->nullable()->default(false);
            $table->integer('lvt_priority')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
