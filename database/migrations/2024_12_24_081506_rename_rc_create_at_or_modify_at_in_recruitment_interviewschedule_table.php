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
            $table->renameColumn('rc_created_by_id', 'ris_created_by_id'); // Rename the first column
            $table->renameColumn('rc_modified_by_id', 'ris_modified_by_id'); // Rename the second column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_interviewschedule', function (Blueprint $table) {
            $table->renameColumn('ris_created_by_id', 'rc_created_by_id'); // Rollback the first column rename
            $table->renameColumn('ris_modified_by_id', 'rc_modified_by_id'); // Rollback the second column rename
        });
    }
};
