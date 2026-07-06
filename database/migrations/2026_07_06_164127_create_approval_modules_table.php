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
        Schema::create('approval_modules', function (Blueprint $table) {
            $table->integer('am_id', true);
            $table->integer('am_b_id')->nullable()->index('am_b_id');
            $table->integer('am_module_id')->nullable()->index('am_module_id');
            $table->string('am_name', 100)->nullable();
            $table->integer('am_exp_rej_day')->nullable();
            $table->integer('am_noti_before_days')->nullable();
            $table->string('am_description')->nullable();
            $table->string('am_exe_on', 100)->nullable();
            $table->boolean('am_status')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_modules');
    }
};
