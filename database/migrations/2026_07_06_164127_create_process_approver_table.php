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
        Schema::create('process_approver', function (Blueprint $table) {
            $table->integer('pa_id', true);
            $table->integer('pa_b_id')->nullable();
            $table->integer('pa_am_id')->nullable()->index('pa_module_id')->comment('primary id of fh_approval_modules table');
            $table->enum('pa_flow', ['business', 'department'])->nullable();
            $table->integer('pa_d_id')->nullable()->index('pa_d_id');
            $table->enum('pa_type', ['single', 'and', 'anyone'])->nullable();
            $table->tinyInteger('pa_sequence')->nullable();
            $table->integer('pa_role_id')->nullable()->index('pa_role_id');
            $table->bigInteger('pa_emp_id')->nullable()->index('fh_process_approver_ibfk_3');
            $table->tinyInteger('pa_last')->nullable()->default(0);
            $table->integer('pa_status_id')->nullable()->index('pa_message_id')->comment('approval message from master table');
            $table->string('pa_message', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_approver');
    }
};
