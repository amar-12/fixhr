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
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('ev_id');
            $table->unsignedBigInteger('ev_emp_id')->nullable()->index('idx_ev_emp_id');
            $table->unsignedInteger('ev_b_id')->nullable()->index('idx_ev_b_id');
            $table->string('ev_title');
            $table->text('ev_description')->nullable();
            $table->text('ev_images')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
