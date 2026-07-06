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
        Schema::create('report_mail_settings', function (Blueprint $table) {
            $table->bigIncrements('rms_id');
            $table->unsignedBigInteger('rms_b_id')->comment('Business ID');
            $table->string('rms_report_slug', 100)->comment('travel-attendance-report');
            $table->boolean('rms_is_enabled')->default(true);
            $table->text('rms_to_email');
            $table->text('rms_cc_email')->nullable();
            $table->text('rms_bcc_email')->nullable();
            $table->string('rms_subject');
            $table->enum('rms_frequency', ['daily', 'weekly', 'monthly'])->nullable()->default('daily');
            $table->time('rms_schedule_time')->default('19:00:00');
            $table->enum('rms_report_duration', ['today', 'yesterday', 'custom'])->nullable()->default('yesterday');
            $table->date('rms_custom_from_date')->nullable();
            $table->date('rms_custom_to_date')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_mail_settings');
    }
};
