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
        Schema::create('adhoc_components', function (Blueprint $table) {
            $table->integer('ac_id', true);
            $table->integer('ac_adhoc_heading_id')->nullable();
            $table->integer('ac_adhoc_business_id')->nullable();
            $table->string('ac_adhoc_component_name', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adhoc_components');
    }
};
