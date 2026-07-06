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
            $table->integer('rc_b_id')->nullable()->after('rc_id'); // Add business ID column
            $table->foreign('rc_b_id')->references('b_id')->on('businesses')->onDelete('restrict'); // Add foreign key constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_candidate', function (Blueprint $table) {
            $table->dropForeign(['rc_b_id']);
            $table->dropColumn('rc_b_id');
        });
    }
};
