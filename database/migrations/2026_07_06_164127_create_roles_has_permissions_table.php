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
        Schema::create('roles_has_permissions', function (Blueprint $table) {
            $table->integer('rhp_id', true)->index('id');
            $table->integer('rhp_b_id')->index('dealersid');
            $table->integer('rhp_role_id')->index('role_id');
            $table->longText('rhp_permissions')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->primary(['rhp_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles_has_permissions');
    }
};
