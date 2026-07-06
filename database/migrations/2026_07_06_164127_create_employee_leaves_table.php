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
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->bigInteger('el_id', true);
            $table->integer('el_b_id')->nullable()->index('el_b_id');
            $table->bigInteger('el_emp_id')->index('el_emp_id');
            $table->integer('el_lvt_id')->index('el_lvt_id');
            $table->decimal('el_total_leaves', 5)->nullable();
            $table->decimal('el_used_leaves', 5)->nullable();
            $table->decimal('el_encashment_amount', 10)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leaves');
    }
};
