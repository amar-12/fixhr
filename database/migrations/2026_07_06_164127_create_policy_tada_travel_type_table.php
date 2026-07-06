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
        Schema::create('policy_tada_travel_type', function (Blueprint $table) {
            $table->integer('pttt_id', true);
            $table->integer('pttt_b_id')->nullable()->index('pttt_b_id');
            $table->integer('pttt_type_id')->index('pttt_type_id');
            $table->integer('pttt_approval_type_id')->nullable()->index('pttt_approval_type_id');
            $table->integer('pttt_status')->nullable()->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_tada_travel_type');
    }
};
