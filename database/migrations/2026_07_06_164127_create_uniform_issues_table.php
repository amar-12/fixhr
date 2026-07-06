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
        Schema::create('uniform_issues', function (Blueprint $table) {
            $table->integer('ui_id', true);
            $table->integer('ui_issued_by');
            $table->integer('ui_b_id');
            $table->decimal('ui_total', 10)->nullable()->default(0);
            $table->decimal('ui_payable', 10)->nullable()->default(0);
            $table->decimal('ui_waived', 10)->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uniform_issues');
    }
};
