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
            $table->integer('rsg_b_id')->after('rsg_id'); // Foreign key to businesses
            $table->foreign('rsg_b_id')->references('b_id')->on('businesses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_stage', function (Blueprint $table) {
            $table->dropForeign(['rsg_b_id']); // Drop the foreign key constraint
            $table->dropColumn('rsg_b_id');   // Drop the column
        });
    }
};
