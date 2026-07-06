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
        Schema::create('email_configurations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('b_id')->index('idx_b_id');
            $table->string('mailer', 50)->default('smtp')->index('idx_mailer');
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('encryption', 50)->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('sendmail_path')->nullable();
            $table->string('mailgun_domain')->nullable();
            $table->text('mailgun_secret')->nullable();
            $table->string('ses_key')->nullable();
            $table->text('ses_secret')->nullable();
            $table->string('ses_region', 100)->nullable();
            $table->text('postmark_token')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_is_active')->comment('1=Active, 0=Inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_configurations');
    }
};
