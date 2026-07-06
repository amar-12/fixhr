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
        Schema::create('businesses', function (Blueprint $table) {
            $table->integer('b_id', true);
            $table->string('b_unique_id', 100)->unique('b_unique_id');
            $table->integer('b_category_id')->nullable()->index('b_category_id');
            $table->integer('b_type_id')->nullable()->index('b_type_id');
            $table->string('b_name')->nullable();
            $table->string('b_tag_line')->nullable();
            $table->integer('b_currency')->nullable()->index('b_currency');
            $table->integer('b_city_id')->nullable()->index('b_city_id');
            $table->integer('b_state_id')->nullable()->index('b_state_id');
            $table->integer('b_country_id')->nullable()->index('b_country_id');
            $table->integer('b_timezone')->nullable();
            $table->string('b_gst_no', 15)->nullable();
            $table->string('b_pan_no', 10)->nullable();
            $table->string('b_pin_code', 6)->nullable();
            $table->string('b_address')->nullable();
            $table->string('b_longitude', 100)->nullable();
            $table->string('b_latitude', 100)->nullable();
            $table->double('b_radius')->nullable();
            $table->string('b_logo')->nullable();
            $table->integer('b_payment_mode')->nullable()->default(440);
            $table->string('b_bank_name')->nullable();
            $table->string('b_bank_acc_no', 50)->nullable();
            $table->string('b_bank_ifsc', 20)->nullable();
            $table->string('b_bank_address')->nullable();
            $table->string('b_cheque_no', 50)->nullable();
            $table->tinyInteger('b_is_verified')->nullable()->default(0);
            $table->tinyInteger('b_status')->nullable()->default(0);
            $table->string('b_emp_code', 10)->nullable();
            $table->integer('b_emp_code_type')->nullable()->index('b_emp_code_type');
            $table->boolean('is_face_detection_active')->nullable()->default(false);
            $table->boolean('demo_setup_completed')->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('b_dashboard_id')->nullable();
            $table->double('b_tada_km_variation')->nullable()->default(0);
            $table->integer('b_lodging_bill_required')->nullable()->default(0);
            $table->integer('b_gps_path')->nullable();
            $table->integer('b_travel_puch_type')->nullable();
            $table->integer('is_reminder')->nullable()->default(0);
            $table->tinyInteger('is_notification')->nullable()->default(0);
            $table->tinyInteger('is_whatsapp')->nullable()->default(0);
            $table->string('switch_business_email')->nullable();
            $table->tinyInteger('is_device_restricted')->nullable()->default(0);
            $table->string('b_fnf_modules')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
