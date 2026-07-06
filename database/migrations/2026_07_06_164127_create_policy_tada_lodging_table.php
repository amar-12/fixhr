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
        Schema::create('policy_tada_lodging', function (Blueprint $table) {
            $table->integer('ptl_id', true);
            $table->integer('ptl_b_id')->nullable()->index('ptl_b_id');
            $table->integer('ptl_ptc_id')->nullable()->index('ptl_ptc_id');
            $table->integer('ptl_pttt_id')->nullable()->index('ptl_pttt_id');
            $table->integer('ptl_ct_type_id')->nullable()->index('ptl_ct_type_id');
            $table->double('ptl_sngl_w_bill')->nullable();
            $table->double('ptl_sngl_wo_bill')->nullable();
            $table->double('ptl_dbl_w_bill')->nullable();
            $table->double('ptl_dbl_wo_bill')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_lodging');
    }
};
