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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->integer('ua_id', true);
            $table->integer('ua_b_id')->nullable()->index('ua_b_id');
            $table->integer('ua_emp_id')->nullable()->index('ua_emp_id');
            $table->string('ua_ip_address', 50)->nullable();
            $table->text('ua_activity')->nullable();
            $table->timestamp('ua_login_at')->nullable();
            $table->timestamp('ua_logout_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
