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
        Schema::create('policy_tada_categories', function (Blueprint $table) {
            $table->integer('ptc_id', true);
            $table->integer('ptc_b_id')->nullable()->index('ptc_b_id');
            $table->string('ptc_name')->nullable();
            $table->integer('ptc_d_id')->nullable()->index('ptc_d_id');
            $table->string('ptc_dg_id')->nullable()->index('ptc_dg_id');
            $table->integer('ptc_grade_id')->nullable()->index('ptc_grade_id');
            $table->string('ptc_pttt_id', 100)->nullable();
            $table->tinyInteger('ptc_status')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_categories');
    }
};
