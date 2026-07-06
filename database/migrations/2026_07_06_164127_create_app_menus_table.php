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
        Schema::create('app_menus', function (Blueprint $table) {
            $table->integer('menu_id', true);
            $table->integer('menu_p_id')->nullable();
            $table->integer('menu_route_type_id')->nullable();
            $table->string('menu_name', 127)->nullable();
            $table->string('menu_icon', 50)->nullable();
            $table->boolean('menu_status')->nullable();
            $table->integer('menu_sub_status')->nullable();
            $table->string('menu_route')->nullable();
            $table->string('menu_group', 55)->nullable();
            $table->integer('menu_sequence')->nullable();
            $table->text('menu_description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_menus');
    }
};
