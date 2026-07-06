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
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->integer('apc_id', true);
            $table->string('apc_type', 20)->nullable();
            $table->mediumText('apc_key');
            $table->mediumText('apc_secret');
            $table->string('apc_region', 100);
            $table->string('apc_version', 150);
            $table->boolean('apc_is_active')->nullable()->default(true)->comment('1 = Active, 0 = Inactive');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_credentials');
    }
};
