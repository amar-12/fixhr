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
        Schema::create('recruitment_skills', function (Blueprint $table) {
            $table->bigIncrements('rs_id');
            $table->integer('rs_b_id');
            $table->string('rs_title');
            $table->bigInteger('rs_created_by_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_skills');
    }
};
