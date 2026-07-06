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
        Schema::table('businesses', function (Blueprint $table) {
            // Change the column type to match the country table's c_id type
            $table->integer('b_currency')->nullable()->after('b_name'); // Assuming c_id is unsigned big integer

            // Add foreign key constraint
            $table->foreign('b_currency')->references('c_id')->on('countries')->onDelete('restrict'); // Adjust the behavior as needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['b_currency']);
            // Then drop the column
            $table->dropColumn('b_currency');
        });
    }
};
