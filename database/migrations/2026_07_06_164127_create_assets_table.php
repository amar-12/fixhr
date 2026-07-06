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
        Schema::create('assets', function (Blueprint $table) {
            $table->string('mac_address')->nullable();
            $table->bigIncrements('id');
            $table->string('asset_tag', 191);
            $table->integer('assets_b_id');
            $table->unsignedBigInteger('asset_type_id');
            $table->string('serial_number', 191)->nullable();
            $table->string('model_number', 191)->nullable();
            $table->longText('specifications');
            $table->string('status')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('replaced_by')->nullable();
            $table->date('assigned_at')->nullable();
            $table->bigInteger('assigned_by')->nullable();
            $table->date('scrapped_at')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('purchase_value', 10)->nullable();
            $table->date('purchase_date')->nullable();
            $table->integer('warranty_months')->nullable();
            $table->string('invoice_no', 50)->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('invoice_file')->nullable();
            $table->decimal('scrap_value', 10)->nullable();
            $table->date('replace_date')->nullable();
            $table->date('service_date')->nullable();
            $table->date('amc_expiry_end_date')->nullable();
            $table->date('amc_expiry_date')->nullable();
            $table->date('service_return_date')->nullable();
            $table->text('service_notes')->nullable();
            $table->string('po_no')->nullable();
            $table->string('vendor_name')->nullable();
            $table->text('scrap_reason')->nullable();
            $table->bigInteger('scraped_by')->nullable();
            $table->integer('issue_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
