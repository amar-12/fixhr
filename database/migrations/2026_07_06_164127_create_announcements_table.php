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
        Schema::create('announcements', function (Blueprint $table) {
            $table->bigIncrements('ann_id');
            $table->unsignedBigInteger('ann_b_id');
            $table->string('ann_title');
            $table->text('ann_message');
            $table->tinyInteger('ann_is_read')->nullable()->default(0);
            $table->string('ann_user_id')->nullable();
            $table->string('ann_role_id')->nullable();
            $table->string('ann_category', 100)->nullable();
            $table->string('ann_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
