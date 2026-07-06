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
        Schema::create('recruitment_email_log', function (Blueprint $table) {
            $table->bigIncrements('rel_id');
            $table->integer('rel_b_id');
            $table->unsignedBigInteger('rel_candidate_id');
            $table->unsignedBigInteger('rel_mail_template_id');
            $table->string('rel_subject');
            $table->longText('rel_body');
            $table->string('rel_from_email', 254);
            $table->string('rel_to', 254);
            $table->boolean('rel_status')->default(false);
            $table->string('rel_attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_email_log');
    }
};
