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
        Schema::create('gatepass_confirmation', function (Blueprint $table) {
            $table->bigIncrements('gcp_id');
            $table->string('gcp_gtp_id');
            $table->string('gcp_date');
            $table->bigInteger('gcp_emp_id')->index('gatepass_confirmation_gcp_emp_id_foreign');
            $table->unsignedBigInteger('gcp_emp_b_id');
            $table->string('gcp_out_time')->nullable();
            $table->string('gcp_in_time')->nullable();
            $table->boolean('gcp_out_time_confirmation')->default(false);
            $table->boolean('gcp_in_time_confirmation')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gatepass_confirmation');
    }
};
