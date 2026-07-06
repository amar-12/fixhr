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
        Schema::create('policy_leaves', function (Blueprint $table) {
            $table->integer('pl_id', true);
            $table->integer('pl_b_id')->nullable()->index('plt_b_id');
            $table->string('pl_name', 50);
            $table->unsignedInteger('pl_ap_id')->nullable();
            $table->date('pl_effective_date')->nullable();
            $table->date('pl_expire_date')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->boolean('pl_upl_applicable')->nullable();
            $table->boolean('pl_limit_check')->nullable();
            $table->integer('pl_limit_before')->nullable();
            $table->integer('pl_limit_after')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_leaves');
    }
};
