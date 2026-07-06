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
        Schema::create('ot_approval_status', function (Blueprint $table) {
            $table->bigIncrements('ot_id');
            $table->unsignedBigInteger('ot_atd_id');
            $table->unsignedBigInteger('ot_b_id');
            $table->string('ot_atd_type', 20)->nullable();
            $table->date('ot_date');
            $table->unsignedBigInteger('ot_module_id')->nullable();
            $table->unsignedBigInteger('ot_next_approver')->nullable();
            $table->bigInteger('ot_requested_status')->nullable();
            $table->unsignedBigInteger('ot_am_id')->nullable();
            $table->boolean('ot_stage_completed')->nullable()->default(false);
            $table->unsignedBigInteger('ot_approved_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();
            $table->unsignedBigInteger('ot_emp_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ot_approval_status');
    }
};
