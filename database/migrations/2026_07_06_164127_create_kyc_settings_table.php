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
        Schema::create('kyc_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('provider_name', 100)->nullable()->default('DigiLocker');
            $table->string('api_base_url');
            $table->text('access_token')->nullable();
            $table->dateTime('token_expiration')->nullable();
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('client_version');
            $table->string('redirect_uri')->nullable();
            $table->string('auth_url')->nullable();
            $table->string('token_url')->nullable();
            $table->string('user_info_url')->nullable();
            $table->enum('mode', ['sandbox', 'production'])->nullable()->default('sandbox');
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_settings');
    }
};
