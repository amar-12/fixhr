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
        Schema::create('scheduler_process_tracks', function (Blueprint $table) {
            $table->integer('spt_id', true);
            $table->integer('spt_b_id')->index('fh_scheduler_process_tracks_ibfk_1');
            $table->string('spt_process_type', 100);
            $table->integer('spt_total_items');
            $table->integer('spt_processed_count')->nullable()->default(0);
            $table->enum('spt_status', ['pending', 'processing', 'completed'])->nullable()->default('pending');
            $table->date('spt_process_date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduler_process_tracks');
    }
};
