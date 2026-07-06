<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('recruitment_candidate', function (Blueprint $table) {
            $table->id('rc_id'); // Primary key
            $table->boolean('rc_is_active')->default(true);
            $table->string('rc_name', 100)->nullable();
            $table->string('rc_profile', 100)->nullable();
            $table->string('rc_portfolio', 200);
            $table->dateTime('rc_schedule_date', 6)->nullable();
            $table->string('rc_email', 254)->unique();
            $table->string('rc_mobile', 15);
            $table->string('rc_resume', 100);
            $table->longText('rc_address')->nullable();
            $table->string('rc_country', 30)->nullable();
            $table->date('rc_dob')->nullable();
            $table->string('rc_state', 30)->nullable();
            $table->string('rc_city', 30)->nullable();
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

            // Create the 'rc_converted_employee_id' column as BIGINT
            $table->bigInteger('rc_converted_employee_id')->nullable();
            $table->bigInteger('rc_created_by_id')->nullable();
            $table->bigInteger('rc_modified_by_id')->nullable();
            $table->bigInteger('rc_referral_id')->nullable();
            $table->integer('rc_dg_id')->nullable();

            // Change rc_recruitment_id to bigInteger and unsigned to match r_id (which should be unsigned in the `recruitment` table)
            $table->bigInteger('rc_recruitment_id')->unsigned()->nullable();
            $table->integer('rc_stage_id')->nullable();

            // Foreign Keys
            $table->foreign('rc_converted_employee_id')->references('emp_id')->on('employees')->nullOnDelete();
            $table->foreign('rc_created_by_id')->references('emp_id')->on('employees')->nullOnDelete();
            $table->foreign('rc_modified_by_id')->references('emp_id')->on('employees')->nullOnDelete();
            $table->foreign('rc_dg_id')->references('dg_id')->on('designations')->nullOnDelete();
            $table->foreign('rc_recruitment_id')->references('r_id')->on('recruitment')->nullOnDelete();
            $table->foreign('rc_referral_id')->references('emp_id')->on('employees')->nullOnDelete();
            $table->foreign('rc_stage_id')->references('m_id')->on('master_table')->nullOnDelete();

            // Add indexes
            $table->index('rc_converted_employee_id');
            $table->index('rc_created_by_id');
            $table->index('rc_modified_by_id');
            $table->index('rc_referral_id');
            $table->index('rc_dg_id');
            $table->index('rc_recruitment_id');
            $table->index('rc_stage_id');

            $table->timestamps();
        });
    }



    public function down()
    {
        Schema::dropIfExists('recruitment_candidate');
    }
};
