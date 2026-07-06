<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('recruitment_stage', function (Blueprint $table) {
            $table->json('rsg_managers')->nullable()->change(); // Making the column nullable
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('recruitment_stage', function (Blueprint $table) {
            $table->json('rsg_managers')->nullable(false)->change(); // Reverting back if needed
        });
    }
};
