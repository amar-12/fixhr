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
        Schema::create('policy_tada_daily_allowance_lodging', function (Blueprint $table) {
            $table->integer('ptdal_id', true);
            $table->integer('ptdal_b_id')->nullable()->index('ptdal_b_id');
            $table->integer('ptdal_pttt_id')->nullable();
            $table->integer('ptdal_ptc_id')->nullable()->index('ptdal_ptc_id');
            $table->integer('ptdal_ct_type_id')->nullable()->index('fh_policy_tada_daily_allowance_lodging_ibfk_2');
            $table->string('ptdal_same_day_remark')->nullable();
            $table->double('ptdal_da_per_day_elig')->nullable();
            $table->double('ptdal_da_same_day_ret_elig')->nullable();
            $table->double('ptdal_lodg_sngl_w_bill_elig')->nullable();
            $table->double('ptdal_lodg_sngl_wo_bill_elig')->nullable();
            $table->double('ptdal_lodg_dbl_w_bill_elig')->nullable();
            $table->double('ptdal_lodg_dbl_wo_bill_elig')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_daily_allowance_lodging');
    }
};
