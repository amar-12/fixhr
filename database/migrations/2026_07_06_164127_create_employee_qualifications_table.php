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
        Schema::create('employee_qualifications', function (Blueprint $table) {
            $table->integer('eq_id', true);
            $table->bigInteger('eq_emp_id')->nullable()->index('eq_emp_id');
            $table->integer('eq_qualification_id')->nullable()->index('eq_qualification_id');
            $table->integer('eq_stream_id')->nullable()->index('eq_stream_id');
            $table->integer('eq_course_type_id')->nullable();
            $table->string('eq_specialization')->nullable();
            $table->string('eq_course_nature')->nullable();
            $table->string('eq_qualification_status')->nullable();
            $table->string('eq_institution_name')->nullable();
            $table->string('eq_university_name')->nullable();
            $table->date('eq_edu_from_date')->nullable();
            $table->date('eq_edu_to_date')->nullable();
            $table->date('eq_passing_date')->nullable();
            $table->string('eq_percentage', 10)->nullable();
            $table->string('eq_edu_grade', 10)->nullable();
            $table->string('eq_year', 4)->nullable();
            $table->string('eq_duration')->nullable();
            $table->integer('eq_temp_country')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_qualifications');
    }
};
