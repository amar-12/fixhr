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
        Schema::create('employee_approval_status', function (Blueprint $table) {
            $table->integer('eas_id', true);
            $table->bigInteger('eas_eam_id')->nullable()->index('fh_employee_approval_status');
            $table->integer('eas_approvel_id');
            $table->integer('eas_approvel_status')->index('eas_approvel_status');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_approval_status');
    }
};
