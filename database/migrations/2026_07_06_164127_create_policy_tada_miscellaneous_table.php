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
        Schema::create('policy_tada_miscellaneous', function (Blueprint $table) {
            $table->integer('pm_id', true);
            $table->integer('pm_b_id')->nullable()->index('pm_b_id');
            $table->integer('pm_ptc_id')->nullable()->index('pm_ptc_id');
            $table->integer('pm_miscellaneous_id')->nullable()->index('pm_miscellaneous_id');
            $table->integer('pm_ct_type_id')->nullable()->index('pm_ct_type_id');
            $table->integer('pm_eligibility')->nullable();
            $table->string('pm_remarks')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_miscellaneous');
    }
};
