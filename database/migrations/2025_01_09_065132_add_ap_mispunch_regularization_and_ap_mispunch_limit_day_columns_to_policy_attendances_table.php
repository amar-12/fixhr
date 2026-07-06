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
        Schema::table('policy_attendances', function (Blueprint $table) {
            $table->integer('ap_mispunch_regularization')->nullable()->after('ap_limit_day'); // Use nullable if you want to allow null values
            $table->integer('ap_mispunch_limit_day')->nullable()->after('ap_mispunch_regularization'); // New column

            // Set up the foreign key constraint
            $table->foreign('ap_mispunch_regularization')->references('m_id')->on('master_table')->onDelete('restrict');

            // Add an index to the 'ap_mispunch_REGULARIZATION' column
            $table->index('ap_mispunch_regularization');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_attendances', function (Blueprint $table) {
             // Drop the foreign key constraint first
             $table->dropForeign(['ap_mispunch_regularization']);

             // Then drop the 'ap_mispunch_regularization' column
             $table->dropColumn('ap_mispunch_regularization');
             $table->dropColumn('ap_mispunch_limit_day'); // Drop new column

        });
    }
};
