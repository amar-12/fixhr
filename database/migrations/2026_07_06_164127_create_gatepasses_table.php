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
        Schema::create('gatepasses', function (Blueprint $table) {
            $table->integer('gtp_id', true);
            $table->integer('gtp_b_id')->nullable();
            $table->bigInteger('gtp_emp_id')->nullable()->index('gtp_emp_id');
            $table->date('gtp_date');
            $table->string('gtp_in_time', 20);
            $table->string('gtp_out_time', 20);
            $table->string('gtp_reason');
            $table->string('gtp_destination');
            $table->integer('gtp_am_id')->nullable()->index('gtp_am_id');
            $table->integer('gtp_status')->default(140)->index('gtp_approval_status_id');
            $table->integer('gtp_module_id')->nullable()->default(339)->index('gtp_module_id');
            $table->integer('gtp_next_approver')->nullable()->default(1)->comment('Approver Sequence');
            $table->integer('gtp_stage_completed')->nullable()->default(0);
            $table->integer('gtp_approved_by')->nullable();
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gatepasses');
    }
};
