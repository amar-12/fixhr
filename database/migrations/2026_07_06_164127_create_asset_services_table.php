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
        Schema::create('asset_services', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->integer('assets_b_id');
            $table->string('asset_tag');
            $table->bigInteger('asset_id');
            $table->string('service_type');
            $table->string('service_location')->nullable()->default('Local');
            $table->text('issue_description')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('service_status')->nullable();
            $table->decimal('service_cost', 10)->nullable();
            $table->dateTime('service_start_date')->nullable();
            $table->dateTime('service_end_date')->nullable();
            $table->string('courier_name', 100)->nullable();
            $table->string('docket_no', 100)->nullable();
            $table->string('service_file')->nullable();
            $table->bigInteger('asset_type_id')->nullable();
            $table->bigInteger('employee_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_services');
    }
};
