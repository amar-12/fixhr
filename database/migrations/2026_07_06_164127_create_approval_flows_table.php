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
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->bigIncrements('afc_id');
            $table->unsignedBigInteger('afc_b_id')->index('idx_afc_b_id');
            $table->integer('afc_approval_id')->index('idx_afc_approval_id');
            $table->json('afc_approval_status_id');
            $table->boolean('afc_status')->default(false)->comment('0 = inactive, 1 = active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flows');
    }
};
