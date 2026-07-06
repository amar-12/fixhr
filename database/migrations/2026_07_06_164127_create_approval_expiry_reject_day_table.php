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
        Schema::create('approval_expiry_reject_day', function (Blueprint $table) {
            $table->bigIncrements('aer_id');
            $table->unsignedBigInteger('aer_b_id')->index('idx_aer_b_id');
            $table->unsignedBigInteger('aer_m_id')->index('idx_aer_m_id');
            $table->integer('aer_day');
            $table->integer('aer_noti_day')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_expiry_reject_day');
    }
};
