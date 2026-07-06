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
            $table->string('rc_portfolio')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fh_recruitment_candidate', function (Blueprint $table) {
            $table->string('rc_portfolio')->nullable(false)->change();
        });
    }
};
