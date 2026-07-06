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
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('kv_b_id')->nullable();
            $table->bigInteger('kv_emp_id')->nullable();
            $table->string('kv_provider_name')->nullable();
            $table->string('kv_reference_id')->nullable();
            $table->string('kv_api_base_url')->nullable();
            $table->string('kv_client_id')->nullable();
            $table->string('kv_client_secret')->nullable();
            $table->string('kv_redirect_uri')->nullable();
            $table->text('kv_auth_url')->nullable();
            $table->string('kv_token_url')->nullable();
            $table->string('kv_user_info_url')->nullable();
            $table->string('kv_mode', 50)->nullable();
            $table->string('kv_status', 100)->nullable();
            $table->json('kv_request_payload')->nullable();
            $table->json('kv_response_payload')->nullable();
            $table->boolean('kv_is_active')->nullable()->default(true);
            $table->timestamp('session_valid_till')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
