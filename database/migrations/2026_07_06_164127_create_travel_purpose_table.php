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
        Schema::create('travel_purpose', function (Blueprint $table) {
            $table->integer('tp_id', true);
            $table->integer('tp_b_id')->nullable()->index('d_b_id');
            $table->integer('tp_d_id')->nullable()->index('tp_d_id');
            $table->mediumText('tp_name');
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_purpose');
    }
};
