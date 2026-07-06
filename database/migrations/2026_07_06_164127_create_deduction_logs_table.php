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
        Schema::create('deduction_logs', function (Blueprint $table) {
            $table->bigInteger('dlog_id', true);
            $table->integer('dlog_log_id')->nullable();
            $table->integer('dlog_am_id')->nullable()->index('dlog_am_id');
            $table->integer('dlog_tc_id')->nullable()->index('dlog_tc_id');
            $table->bigInteger('dlog_user_id')->nullable()->index('dlog_user_id')->comment('updated by');
            $table->integer('dlog_user_role_id')->nullable()->index('dlog_user_role_id');
            $table->tinyInteger('dlog_requester_action')->nullable();
            $table->integer('dlog_deduction_amount')->nullable();
            $table->string('dlog_additional_info', 200)->nullable();
            $table->mediumText('dlog_remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deduction_logs');
    }
};
