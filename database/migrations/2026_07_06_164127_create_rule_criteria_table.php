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
        Schema::create('rule_criteria', function (Blueprint $table) {
            $table->integer('rc_id', true);
            $table->integer('rc_b_id')->nullable()->index('rc_b_id');
            $table->integer('rc_am_id')->nullable()->index('rc_am_id')->comment('primary id of fh_approval_modules table');
            $table->integer('rc_approval_rule_id')->nullable()->index('rc_ar_m_id')->comment('primary d of APPROVAL_RULE from master');
            $table->integer('rc_rule_condition_id')->nullable()->index('rc_rc_m_id')->comment('primary d of RULE_CONDITION from master');
            $table->integer('rc_condition_option_id')->nullable()->index('rc_rv_m_id')->comment('primary d of CONDITION_OPTION  from master');
            $table->string('rc_custom_value', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rule_criteria');
    }
};
