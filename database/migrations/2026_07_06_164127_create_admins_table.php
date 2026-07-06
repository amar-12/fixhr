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
        Schema::create('admins', function (Blueprint $table) {
            $table->integer('a_id', true);
            $table->string('a_name')->nullable();
            $table->string('a_email')->nullable();
            $table->string('a_password')->nullable();
            $table->string('a_phone', 20)->nullable();
            $table->string('a_otp', 6)->nullable();
            $table->text('a_auth_token')->nullable();
            $table->text('a_fcm_token')->nullable();
            $table->integer('a_role_id')->nullable()->default(1)->index('u_role_id');
            $table->text('a_profile_photo')->nullable();
            $table->timestamp('a_otp_created_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
