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
        Schema::create('salary_holds', function (Blueprint $table) {
            $table->bigIncrements('sh_id');
            $table->unsignedBigInteger('sh_emp_id');
            $table->bigInteger('sh_b_id')->nullable();
            $table->unsignedBigInteger('sh_dept_id')->nullable();
            $table->unsignedBigInteger('sh_pp_id');
            $table->enum('sh_status', ['held', 'released'])->nullable()->default('held');
            $table->text('sh_reason')->nullable();
            $table->unsignedBigInteger('sh_held_by')->nullable();
            $table->unsignedBigInteger('sh_released_by')->nullable();
            $table->timestamp('sh_held_at')->nullable();
            $table->timestamp('sh_released_at')->nullable();
            $table->tinyInteger('sh_hold_until_release')->nullable();
            $table->integer('sh_week_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_holds');
    }
};
