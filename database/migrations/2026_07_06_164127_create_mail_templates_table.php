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
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->bigIncrements('mt_id');
            $table->string('mt_title');
            $table->longText('mt_body');
            $table->string('mt_mail_type')->nullable();
            $table->unsignedBigInteger('mt_module_id')->nullable();
            $table->string('mt_other_module', 100)->nullable();
            $table->integer('mt_send_to')->nullable();
            $table->unsignedBigInteger('mt_b_id');
            $table->boolean('mt_is_enabled')->default(true);
            $table->json('variables')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
