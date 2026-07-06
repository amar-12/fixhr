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
        Schema::create('academic_details', function (Blueprint $table) {
            $table->integer('ad_id', true);
            $table->integer('ad_emp_id');
            $table->integer('ad_b_id');
            $table->integer('ad_qua_id');
            $table->integer('ad_course_degree');
            $table->string('ad_specialization');
            $table->string('ad_university_board')->nullable();
            $table->string('ad_institute_name')->nullable();
            $table->year('ad_year_of_passing')->nullable();
            $table->string('ad_marks_type')->nullable();
            $table->string('ad_marks_obtained', 20)->nullable();
            $table->string('ad_document_upload')->nullable();
            $table->tinyInteger('ad_status')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_details');
    }
};
