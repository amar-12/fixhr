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
        Schema::create('business_module_access', function (Blueprint $table) {
            $table->integer('bma_id', true);
            $table->integer('bma_b_id')->index('bma_b_id')->comment('Foreign Key to Businesses Table');
            $table->integer('bma_mdl_id')->index('bma_mdl_id')->comment('Foreign Key to Modules Table');
            $table->boolean('bma_access')->nullable()->default(false)->comment('Access Permission (0: No Access, 1: Access Granted)');
            $table->dateTime('created_at')->nullable()->useCurrent()->comment('Creation Timestamp');
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent()->comment('Update Timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_module_access');
    }
};
