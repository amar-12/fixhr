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
            $table->id('rel_id'); // Primary key with 'rel_' prefix
            $table->integer('rel_b_id'); // Company ID with 'rel_' prefix (bigInteger for consistency)
            $table->bigInteger('rel_candidate_id')->unsigned(); // Candidate ID with 'rel_' prefix (bigInteger for consistency)
            $table->bigInteger('rel_mail_template_id')->unsigned(); // Mail Template ID with 'rel_' prefix (bigInteger for consistency)
            $table->string('rel_subject', 255); // Subject with 'rel_' prefix
            $table->longText('rel_body'); // Body with 'rel_' prefix
            $table->string('rel_from_email', 254); // From email with 'rel_' prefix
            $table->string('rel_to', 254); // To email with 'rel_' prefix
            $table->boolean('rel_status')->default(false); // Status with 'rel_' prefix
            $table->string('rel_attachment', 255)->nullable(); // Attachment (file path or URL) with 'rel_' prefix

            $table->timestamps(); // Laravel's created_at and updated_at

            // Foreign Key Constraints
            $table->foreign('rel_b_id')->references('b_id')->on('businesses')->onDelete('restrict');
            $table->foreign('rel_mail_template_id')->references('mt_id')->on('mail_templates')->onDelete('restrict');
            $table->foreign('rel_candidate_id')->references('rc_id')->on('recruitment_candidate')->onDelete('restrict');
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
