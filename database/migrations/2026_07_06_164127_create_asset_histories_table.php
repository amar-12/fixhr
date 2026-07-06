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
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id');
            $table->integer('assets_b_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->enum('action', ['assigned', 'unassigned', 'scrapped', 'replaced', 'created', 'updated', 'deleted', 'sent_to_service', 'returned_from_service', 'restored', 'Duplicate', 'Requested', 'Completed', 'In Progress', 'Returned'])->nullable();
            $table->string('from_status', 191)->nullable();
            $table->string('to_status', 191);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_histories');
    }
};
