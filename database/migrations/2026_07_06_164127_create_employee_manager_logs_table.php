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
        Schema::create('employee_manager_logs', function (Blueprint $table) {
            $table->bigIncrements('eml_id');
            $table->integer('eml_b_id')->nullable();
            $table->string('eml_form_type', 50)->nullable();
            $table->integer('eml_old_manager_id')->nullable();
            $table->integer('eml_new_manager_id')->nullable();
            $table->date('eml_wef_date')->nullable();
            $table->string('eml_reason')->nullable();
            $table->tinyInteger('eml_applied')->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_manager_logs');
    }
};
