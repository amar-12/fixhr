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
        Schema::table('recruitment_candidate', function (Blueprint $table) {
            // Remove the unique constraint from the 'rc_email' column
            $table->dropUnique(['rc_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_candidate', function (Blueprint $table) {
            // Re-add the unique constraint (if needed)
            $table->unique('rc_email');
        });
    }
};
