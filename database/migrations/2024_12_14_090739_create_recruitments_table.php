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
            $table->id('r_id'); // Auto-incrementing ID
            $table->integer('r_b_id'); // Foreign key to businesses
            $table->integer('r_br_id'); // Foreign key to branches
            $table->json('r_dg_id'); // JSON column to store multiple designations
            $table->string('r_title', 30)->nullable();
            $table->longText('r_description')->nullable();
            $table->boolean('r_is_event_based')->default(0);
            $table->boolean('r_closed')->default(0);
            $table->boolean('r_is_published')->default(0);
            $table->boolean('r_is_active')->default(0);
            $table->integer('r_vacancy')->nullable();
            $table->date('r_start_date');
            $table->date('r_end_date')->nullable();
            $table->boolean('r_optional_profile_image')->default(0);
            $table->boolean('r_optional_resume')->default(0);
            $table->bigInteger('r_created_by_id'); // Foreign key to employees
            $table->bigInteger('r_modified_by_id'); // Foreign key to employees
            $table->json('r_managers'); // JSON column to store multiple manager IDs
            $table->json('r_skills'); // JSON column to store multiple skill IDs
            $table->timestamps();

            // Adding unique keys
            // $table->unique(['r_dg_id', 'r_start_date'], 'recruitment_dg_id_start_uniq');
            // $table->unique(['r_dg_id', 'r_start_date', 'r_b_id'], 'recruitment_dg_id_start_b_id_uniq');

            // Adding indexes
            $table->index('r_b_id', 'recruitment_b_id_idx');
            $table->index('r_created_by_id', 'recruitment_created_by_id_idx');
            $table->index('r_modified_by_id', 'recruitment_modified_by_id_idx');

            // Define foreign key relationships
            $table->foreign('r_b_id')->references('b_id')->on('businesses')->onDelete('cascade');
            $table->foreign('r_br_id')->references('br_id')->on('branches')->onDelete('cascade');
            $table->foreign('r_created_by_id')->references('emp_id')->on('employees')->onDelete('cascade');
            $table->foreign('r_modified_by_id')->references('emp_id')->on('employees')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitments');
    }
};
