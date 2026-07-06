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
            $table->string('rc_zip', 30)->nullable()->after('rc_city'); // Add the column after 'rc_city'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_candidate', function (Blueprint $table) {
            $table->dropColumn('rc_zip');
        });
    }
};
