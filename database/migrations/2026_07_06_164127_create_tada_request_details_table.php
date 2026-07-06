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
        Schema::create('tada_request_details', function (Blueprint $table) {
            $table->integer('trd_id', true);
            $table->integer('trd_trp_id')->index('trd_trp_id');
            $table->integer('trd_type_id')->nullable()->index('trd_type');
            $table->string('trd_name')->nullable();
            $table->integer('trd_pttm_id')->nullable();
            $table->integer('trd_pttv_id')->nullable();
            $table->string('trd_hotel_location')->nullable();
            $table->string('trd_source')->nullable();
            $table->string('trd_destination')->nullable();
            $table->longText('trd_segments')->nullable();
            $table->date('trd_start_date')->nullable();
            $table->date('trd_end_date')->nullable();
            $table->longText('trd_documents')->nullable();
            $table->time('trd_start_time')->nullable();
            $table->time('trd_end_time')->nullable();
            $table->float('trd_total_distance')->nullable();
            $table->float('trd_total_tolerance')->nullable();
            $table->string('trd_call_id')->nullable();
            $table->integer('trd_status')->nullable()->comment('2 = Save as draft');
            $table->string('trd_remarks')->nullable();
            $table->string('trd_purpose')->nullable();
            $table->integer('trd_ticket_type')->nullable();
            $table->float('trd_net_amount')->nullable();
            $table->float('trd_p_set_amount')->nullable();
            $table->boolean('trd_geo_work_active')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tada_request_details');
    }
};
