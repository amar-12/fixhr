<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Start a transaction to ensure everything is rolled back in case of failure

            // Create the table
            Schema::create('recruitment_interviewschedule', function (Blueprint $table) {
                // Auto-increment primary key
                $table->bigIncrements('ris_id');

                // Other columns
                $table->boolean('ris_is_active');
                $table->date('ris_interview_date');
                $table->time('ris_interview_time');
                $table->longText('ris_description');
                $table->boolean('ris_completed');
                $table->bigInteger('ris_candidate_id')->unsigned();
                $table->bigInteger('rc_created_by_id')->nullable();
                $table->bigInteger('rc_modified_by_id')->nullable();
                $table->timestamps();

                // Indexes
                $table->primary('ris_id');
            });

            // Adding foreign key constraints (after the table is created)
            Schema::table('recruitment_interviewschedule', function (Blueprint $table) {
                // Check if the related tables exist and then add foreign keys
                if (Schema::hasTable('recruitment_candidate')) {
                    $table->foreign('ris_candidate_id')->references('rc_id')->on('recruitment_candidate');
                }

                if (Schema::hasTable('employees')) {
                    $table->foreign('rc_created_by_id')->references('emp_id')->on('employees')->nullOnDelete();
                    $table->foreign('rc_modified_by_id')->references('emp_id')->on('employees')->nullOnDelete();
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_interviewschedule');
    }
};
