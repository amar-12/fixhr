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
        Schema::table('recruitment_interviewschedule', function (Blueprint $table) {
            $table->json('ris_interviewer')->nullable()->after('ris_is_active'); // Add the new JSON column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_interviewschedule', function (Blueprint $table) {
            $table->dropColumn('ris_interviewer'); // Remove the column if rolling back
        });
    }
};
