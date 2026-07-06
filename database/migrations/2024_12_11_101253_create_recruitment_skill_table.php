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
    public function up(): void
    {
        Schema::create('recruitment_skills', function (Blueprint $table) {
            $table->id('rs_id'); // Auto-incrementing primary key
            $table->integer('rs_b_id'); // Foreign key to businesses
            $table->string('rs_title'); // Skill title
            $table->bigInteger('rs_created_by_id'); // Foreign key to employees (no AUTO_INCREMENT)
            $table->timestamps(); // created_at and updated_at

            // Define foreign key relationships
            $table->foreign('rs_b_id')->references('b_id')->on('businesses')->onDelete('cascade');
            $table->foreign('rs_created_by_id')->references('emp_id')->on('employees')->onDelete('cascade');

            // Ensure the table uses the InnoDB engine
            $table->engine = 'InnoDB';
        });

        // Optional: Change the collation of the entire table after it's created
        // DB::statement('ALTER TABLE recruitment_skills CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // // Drop the foreign key constraints before dropping the table
        Schema::table('recruitment_skills', function (Blueprint $table) {
            $table->dropForeign(['rs_b_id']);
            $table->dropForeign(['rs_created_by_id']);
        });

        // Drop the table
        Schema::dropIfExists('recruitment_skills');
    }
};
