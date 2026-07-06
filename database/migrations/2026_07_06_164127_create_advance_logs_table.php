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
        Schema::create('advance_logs', function (Blueprint $table) {
            $table->integer('adl_id', true);
            $table->integer('adl_trp_id')->nullable()->index('adl_trp_id');
            $table->double('adl_requested_amount')->nullable();
            $table->string('adl_remark')->nullable();
            $table->double('adl_reimburse_amount')->nullable();
            $table->bigInteger('adl_approver_id')->nullable()->index('adl_approver_id');
            $table->tinyInteger('adl_stage_completed')->nullable()->default(0);
            $table->integer('adl_module_id')->nullable()->default(199);
            $table->integer('adl_am_id')->nullable();
            $table->integer('adl_request_status')->nullable()->default(140);
            $table->integer('adl_next_approver')->nullable()->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('adl_b_id')->nullable();
            $table->integer('adl_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_logs');
    }
};
