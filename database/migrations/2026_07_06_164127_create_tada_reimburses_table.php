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
        Schema::create('tada_reimburses', function (Blueprint $table) {
            $table->increments('tr_id');
            $table->integer('tr_b_id');
            $table->integer('tr_status')->nullable();
            $table->string('tr_unique_id', 10)->nullable();
            $table->longText('tr_claims_id');
            $table->decimal('tr_amount');
            $table->string('tr_group_id', 50)->nullable();
            $table->date('tr_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tada_reimburses');
    }
};
