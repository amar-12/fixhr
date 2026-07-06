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
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->bigInteger('log_id', true);
            $table->integer('log_am_id')->nullable()->index('log_module_id');
            $table->integer('log_module_id')->nullable()->index('log_module_id_2');
            $table->integer('log_request_id')->nullable();
            $table->bigInteger('log_user_id')->nullable()->index('log_user_id')->comment('updated by');
            $table->integer('log_user_role_id')->nullable()->index('log_user_role_id');
            $table->integer('log_status')->nullable()->index('log_status');
            $table->mediumText('log_description')->nullable();
            $table->string('log_other', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
