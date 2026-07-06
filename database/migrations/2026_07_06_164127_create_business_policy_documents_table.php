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
        Schema::create('business_policy_documents', function (Blueprint $table) {
            $table->bigInteger('bpd_id', true);
            $table->integer('bpd_folder_id');
            $table->string('bpd_folder_name');
            $table->integer('bpd_b_id');
            $table->string('bpd_version', 100)->nullable();
            $table->string('bpd_file_name');
            $table->date('bpd_with_effect_from')->nullable();
            $table->string('bpd_file_path')->nullable();
            $table->tinyInteger('bpd_status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_policy_documents');
    }
};
