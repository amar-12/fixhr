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
        Schema::table('policy_attendances', function (Blueprint $table) {
            // Add the new column 'ap_attendance_regularization'
            $table->integer('ap_attendance_regularization')->nullable(); // Use nullable if you want to allow null values

            // Set up the foreign key constraint
            $table->foreign('ap_attendance_regularization')->references('m_id')->on('master_table')->onDelete('restrict');

            // Add an index to the 'ap_ATTENDANCE_REGULARIZATION' column
            $table->index('ap_attendance_regularization');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_attendances', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['ap_attendance_regularization']);

            // Then drop the 'ap_attendance_regularization' column
            $table->dropColumn('ap_attendance_regularization');
        });
    }
};
