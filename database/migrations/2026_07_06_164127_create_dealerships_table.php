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
        Schema::create('dealerships', function (Blueprint $table) {
            $table->integer('dlr_id', true);
            $table->integer('dlr_b_id')->nullable()->index('dg_d_id');
            $table->string('dlr_code')->nullable();
            $table->string('dlr_name', 100)->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealerships');
    }
};
