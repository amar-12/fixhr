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
        Schema::create('assets_categories', function (Blueprint $table) {
            $table->bigIncrements('ac_id');
            $table->string('ac_code', 20)->unique('ac_code');
            $table->integer('ac_b_id');
            $table->string('ac_name', 100);
            $table->text('ac_description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_categories');
    }
};
