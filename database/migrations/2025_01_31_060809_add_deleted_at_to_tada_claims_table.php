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
        Schema::table('tada_claim', function (Blueprint $table) {
            $table->softDeletes(); // Adds deleted_at column for soft deletes
            $table->string('deleted_at_remark')->nullable(); // Adds deleted_at_remark column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tada_claim', function (Blueprint $table) {
            $table->dropColumn('deleted_at_remark'); // Remove the deleted_at_remark column
            $table->dropSoftDeletes(); // Remove the softDeletes column if you want to roll back
        });
    }
};
