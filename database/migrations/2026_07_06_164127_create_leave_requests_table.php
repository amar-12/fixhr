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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->integer('lvr_id', true);
            $table->integer('lvr_b_id')->nullable();
            $table->integer('lvr_p_id')->nullable();
            $table->integer('lvr_emp_id')->index('lvr_employee_id');
            $table->integer('lvr_pl_id')->index('lvr_pl_id');
            $table->integer('lvr_cat_type_id')->nullable()->index('lvr_cat_type_id');
            $table->date('lvr_start_date');
            $table->date('lvr_end_date');
            $table->string('lvr_reason')->nullable();
            $table->integer('lvr_day_segment_id')->nullable()->index('lvr_shift_time_type_id');
            $table->integer('lvr_leave_day_type_id')->nullable()->index('lvr_leave_day_type_id');
            $table->longText('lvr_documents')->nullable();
            $table->integer('lvr_approved_by')->nullable();
            $table->float('lvr_total_leave_days', 5)->nullable();
            $table->boolean('lvr_is_comp_off')->default(false);
            $table->integer('lvr_am_id')->nullable();
            $table->integer('lvr_status')->nullable()->default(140);
            $table->integer('lvr_module_id')->nullable()->default(250);
            $table->integer('lvr_next_approver')->nullable()->default(1);
            $table->tinyInteger('lvr_stage_completed')->nullable()->default(0);
            $table->boolean('lvr_is_sandwich')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->tinyInteger('is_reverted')->nullable()->default(0);
            $table->string('revert_remark')->nullable();

            $table->index(['lvr_pl_id'], 'lvr_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
