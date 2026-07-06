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
        Schema::create('recruitment_interviewschedule', function (Blueprint $table) {
            $table->bigIncrements('ris_id');
            $table->boolean('ris_is_active');
            $table->json('ris_interviewer')->nullable();
            $table->date('ris_interview_date');
            $table->time('ris_interview_time');
            $table->longText('ris_description');
            $table->boolean('ris_completed');
            $table->unsignedBigInteger('ris_candidate_id');
            $table->bigInteger('ris_created_by_id')->nullable();
            $table->bigInteger('ris_modified_by_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_interviewschedule');
    }
};
