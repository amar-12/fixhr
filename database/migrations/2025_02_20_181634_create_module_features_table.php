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
        Schema::create('module_features', function (Blueprint $table) {
            $table->integer('mdf_id')->nullable();
            $table->integer('mdf_mdl_id')->nullable();
            $table->string('mdf_name', 200)->nullable();
            $table->string('mdf_code', 100)->nullable();
            $table->string('mdf_description')->nullable();
            $table->double('mdf_price')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_features');
    }
};
