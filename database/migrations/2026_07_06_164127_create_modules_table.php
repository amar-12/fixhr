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
        Schema::create('modules', function (Blueprint $table) {
            $table->integer('mdl_id', true);
            $table->string('mdl_name')->comment('Module Name');
            $table->string('mdl_code', 50)->unique('mdl_code')->comment('Unique Code for Module');
            $table->text('mdl_description')->nullable()->comment('Description of the Module');
            $table->dateTime('created_at')->nullable()->useCurrent()->comment('Creation Timestamp');
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent()->comment('Update Timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
