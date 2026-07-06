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
        Schema::create('business_banks', function (Blueprint $table) {
            $table->increments('bb_id');
            $table->unsignedInteger('bb_b_id');
            $table->string('bb_account_code', 50)->nullable();
            $table->string('bb_ifsc_code', 20)->nullable();
            $table->string('bb_bank_name', 150)->nullable();
            $table->string('bb_branch_name', 150)->nullable();
            $table->string('bb_micr', 20)->nullable();
            $table->string('bb_branch_code', 50)->nullable();
            $table->string('bb_bank_acc_no', 50)->nullable();
            $table->string('bb_account_type', 50)->nullable();
            $table->text('bb_bank_address')->nullable();
            $table->string('bb_cheque_no', 50)->nullable();
            $table->tinyInteger('bb_bank_status')->nullable()->default(1)->comment('1=Active, 0=Inactive');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_banks');
    }
};
