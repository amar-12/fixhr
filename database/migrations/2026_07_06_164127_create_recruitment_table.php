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
        Schema::create('recruitment', function (Blueprint $table) {
            $table->bigIncrements('r_id');
            $table->integer('r_b_id');
            $table->integer('r_br_id');
            $table->json('r_dg_id');
            $table->string('r_title', 30)->nullable();
            $table->longText('r_description')->nullable();
            $table->boolean('r_is_event_based')->default(false);
            $table->boolean('r_closed')->default(false);
            $table->boolean('r_is_published')->default(false);
            $table->boolean('r_is_active')->default(false);
            $table->integer('r_vacancy')->nullable();
            $table->date('r_start_date');
            $table->date('r_end_date')->nullable();
            $table->boolean('r_optional_profile_image')->default(false);
            $table->boolean('r_optional_resume')->default(false);
            $table->bigInteger('r_created_by_id');
            $table->bigInteger('r_modified_by_id');
            $table->json('r_managers');
            $table->json('r_skills');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment');
    }
};
