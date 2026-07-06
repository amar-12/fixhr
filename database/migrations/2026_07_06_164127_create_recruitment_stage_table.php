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
        Schema::create('recruitment_stage', function (Blueprint $table) {
            $table->bigIncrements('rsg_id');
            $table->integer('rsg_b_id');
            $table->boolean('rsg_is_active');
            $table->string('rsg_stage', 50);
            $table->integer('rsg_sequence')->nullable();
            $table->integer('rsg_stage_type')->nullable();
            $table->bigInteger('rsg_created_by_id');
            $table->bigInteger('rsg_modified_by_id');
            $table->unsignedBigInteger('rsg_recruitment_id');
            $table->timestamps();
            $table->json('rsg_managers')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_stage');
    }
};
