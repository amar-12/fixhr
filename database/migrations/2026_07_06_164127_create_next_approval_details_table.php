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
        Schema::create('next_approval_details', function (Blueprint $table) {
            $table->integer('nxt_id', true)->index('nxt_id');
            $table->integer('nxt_tc_id')->nullable()->index('nxt_tc_id');
            $table->enum('nxt_approval_type', ['single', 'and', 'anyone'])->nullable();
            $table->integer('nxt_approver_sequence')->nullable();
            $table->tinyInteger('nxt_is_last')->nullable();
            $table->integer('nxt_am_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('next_approval_details');
    }
};
