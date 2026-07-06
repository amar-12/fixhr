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
        Schema::create('branches', function (Blueprint $table) {
            $table->integer('br_id', true);
            $table->integer('br_b_id')->nullable()->index('br_b_id');
            $table->string('br_code')->nullable();
            $table->string('br_name', 100)->nullable();
            $table->string('br_email', 100)->nullable();
            $table->boolean('br_is_active')->unsigned()->nullable()->default(false);
            $table->text('br_address')->nullable();
            $table->string('br_longitude', 20)->nullable();
            $table->string('br_latitude', 20)->nullable();
            $table->string('br_range_limit')->nullable();
            $table->boolean('br_is_wifi_restricted')->nullable()->default(false);
            $table->string('br_wifi_address', 121)->nullable();
            $table->integer('br_c_id')->nullable();
            $table->integer('br_s_id')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
