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
        Schema::create('departments', function (Blueprint $table) {
            $table->integer('d_id', true);
            $table->integer('d_b_id')->nullable()->index('d_b_id');
            $table->string('d_name', 100)->nullable();
            $table->tinyInteger('d_status');
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->integer('d_ap_id')->nullable();
            $table->string('d_pst_id')->nullable();
            $table->integer('d_phl_id')->nullable();
            $table->integer('d_pl_id')->nullable();
            $table->integer('d_pwo_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
