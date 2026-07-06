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
        Schema::create('attendance_shift_policy', function (Blueprint $table) {
            $table->id('asp_id'); // Primary key
            $table->integer('asp_b_id'); // Foreign key to businesses
            $table->integer('asp_shift_type');
            $table->string('asp_shift_type_name'); // Name of the shift type
            $table->timestamps();

            $table->foreign('asp_b_id')->references('b_id')->on('businesses')->onDelete('restrict');
            $table->foreign('asp_shift_type')->references('m_id')->on('master_table')->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('attendance_shift_policy', function (Blueprint $table) {
        //     $table->dropForeign(['asp_b_id']);
        //     $table->dropForeign(['asp_shift_type']);
        // });

        Schema::dropIfExists('attendance_shift_policy');
    }
};
