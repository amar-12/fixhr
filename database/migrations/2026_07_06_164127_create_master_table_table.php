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
        Schema::create('master_table', function (Blueprint $table) {
            $table->integer('m_id', true);
            $table->string('m_group', 55)->nullable();
            $table->string('m_name', 100)->nullable();
            $table->string('m_alias_name', 100)->nullable();
            $table->string('m_type', 100)->nullable();
            $table->string('m_other', 100)->nullable();
            $table->string('m_description')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_table');
    }
};
