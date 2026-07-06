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
        Schema::table('employees', function (Blueprint $table) {
            // Add a new column 'emp_attendance_preference' to the 'employees' table with a default value of 367
            $table->integer('emp_attendance_preference')->default(367)->before('created_at');

            // Set up the foreign key constraint
            $table->foreign('emp_attendance_preference')->references('m_id')->on('master_table')->onDelete('restrict');

            $table->foreign('emp_job_status')->references('m_id')->on('master_table')->onDelete('restrict');

            // Add an index to the 'emp_attendance_preference' column
            $table->index('emp_attendance_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['emp_attendance_preference']);

            // Drop the index for 'emp_attendance_preference'
            $table->dropIndex(['emp_attendance_preference']);

            // Then drop the 'emp_attendance_preference' column
            $table->dropColumn('emp_attendance_preference');
        });
    }
};
