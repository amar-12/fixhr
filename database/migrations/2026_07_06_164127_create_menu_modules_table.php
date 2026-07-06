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
        Schema::create('menu_modules', function (Blueprint $table) {
            $table->integer('mm_id', true);
            $table->integer('mm_menu_id')->nullable()->index('mm_menu_id');
            $table->integer('mm_mdl_id')->nullable()->index('mm_mdl_id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_modules');
    }
};
