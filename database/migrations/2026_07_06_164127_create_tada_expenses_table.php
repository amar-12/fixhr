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
        Schema::create('tada_expenses', function (Blueprint $table) {
            $table->integer('te_id', true);
            $table->integer('te_trp_id')->nullable()->index('te_trp_id');
            $table->integer('te_type_id')->index('te_type_id');
            $table->string('te_country_code', 100)->nullable();
            $table->integer('te_sub_expense_id')->nullable();
            $table->date('te_date')->nullable();
            $table->string('te_name', 200)->nullable();
            $table->integer('te_pttm_id')->nullable()->index('te_pttm_id');
            $table->integer('te_pttv_id')->nullable()->index('te_pttv_id');
            $table->string('te_from_location')->nullable();
            $table->string('te_to_location')->nullable();
            $table->date('te_from_date')->nullable();
            $table->time('te_from_time')->nullable();
            $table->date('te_to_date')->nullable();
            $table->time('te_to_time')->nullable();
            $table->decimal('te_total_km_driven', 10, 0)->nullable();
            $table->string('te_hotel_name', 200)->nullable();
            $table->double('te_amount')->nullable()->default(0);
            $table->double('te_foreign_amount')->nullable();
            $table->double('te_conversion_rate')->nullable();
            $table->double('te_deviation')->nullable()->default(0);
            $table->longText('te_document')->nullable();
            $table->double('te_taxes')->nullable()->default(0);
            $table->enum('te_occupancy', ['single', 'shared', ''])->nullable();
            $table->enum('te_paid_by', ['company', 'self', ''])->nullable();
            $table->mediumText('te_remarks')->nullable();
            $table->float('te_p_set_amount')->nullable();
            $table->time('te_standard_checkout_time')->nullable();
            $table->boolean('te_round_trip')->nullable();
            $table->mediumText('te_additional_info')->nullable();
            $table->text('calculation_message')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->double('te_tolerance_km')->nullable();
            $table->string('te_loading_bill_no', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tada_expenses');
    }
};
