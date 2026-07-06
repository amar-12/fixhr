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
        Schema::table('recruitment_stage', function (Blueprint $table) {
            $table->json('rsg_managers'); // JSON column to store multiple designations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_stage', function (Blueprint $table) {
            $table->dropColumn('rsg_managers'); // Remove the column
        });
    }
};
