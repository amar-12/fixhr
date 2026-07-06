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
        Schema::create('payroll_audit_logs', function (Blueprint $table) {
            $table->bigInteger('pal_id', true);
            $table->integer('pal_b_id')->nullable()->index('pal_b_id');
            $table->bigInteger('pal_pr_id')->index('pal_pr_id');
            $table->text('pal_description');
            $table->bigInteger('pal_created_by')->index('pal_created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_audit_logs');
    }
};
