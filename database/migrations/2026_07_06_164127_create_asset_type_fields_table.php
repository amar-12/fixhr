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
        Schema::create('asset_type_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_type_id');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->unsignedBigInteger('field_type_id');
            $table->string('options_category', 50)->nullable();
            $table->json('dropdown_options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('attributes')->nullable();
            $table->string('condition_field_slug', 100)->nullable();
            $table->string('condition_operator', 50)->nullable();
            $table->string('condition_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_type_fields');
    }
};
