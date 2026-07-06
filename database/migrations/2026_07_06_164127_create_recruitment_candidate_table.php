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
        Schema::create('recruitment_candidate', function (Blueprint $table) {
            $table->bigIncrements('rc_id');
            $table->integer('rc_b_id')->nullable();
            $table->boolean('rc_is_active')->default(true);
            $table->string('rc_name', 100)->nullable();
            $table->string('rc_profile', 100)->nullable();
            $table->string('rc_portfolio')->nullable();
            $table->dateTime('rc_schedule_date', 6)->nullable();
            $table->string('rc_email', 254);
            $table->string('rc_mobile', 15);
            $table->string('rc_resume', 100)->nullable();
            $table->longText('rc_address')->nullable();
            $table->string('rc_country', 30)->nullable();
            $table->date('rc_dob')->nullable();
            $table->string('rc_state', 30)->nullable();
            $table->string('rc_city', 30)->nullable();
            $table->string('rc_zip', 30)->nullable();
            $table->string('rc_gender', 15)->nullable();
            $table->string('rc_source', 20)->nullable();
            $table->boolean('rc_start_onboard')->default(false);
            $table->boolean('rc_hired')->default(false);
            $table->boolean('rc_canceled')->default(false);
            $table->date('rc_joining_date')->nullable();
            $table->integer('rc_sequence')->nullable();
            $table->date('rc_probation_end')->nullable();
            $table->string('rc_offer_letter_status', 10);
            $table->date('rc_last_updated')->nullable();
            $table->bigInteger('rc_converted_employee_id')->nullable();
            $table->bigInteger('rc_created_by_id')->nullable();
            $table->bigInteger('rc_modified_by_id')->nullable();
            $table->bigInteger('rc_referral_id')->nullable();
            $table->integer('rc_dg_id')->nullable();
            $table->unsignedBigInteger('rc_recruitment_id')->nullable();
            $table->integer('rc_stage_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidate');
    }
};
