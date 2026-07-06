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
        Schema::create('employee_approval_mappings', function (Blueprint $table) {
            $table->bigInteger('eam_id', true);
            $table->integer('eam_b_id')->index('eam_b_id');
            $table->integer('eam_module_id')->index('eam_module_id');
            $table->bigInteger('eam_emp_id')->index('eam_emp_id');
            $table->bigInteger('eam_approver_manager_1')->nullable()->index('eam_approver_manager_1');
            $table->bigInteger('eam_approver_manager_2')->nullable()->index('eam_approver_manager_2');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_approval_mappings');
    }
};
