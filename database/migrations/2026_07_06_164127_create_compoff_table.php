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
        Schema::create('compoff', function (Blueprint $table) {
            $table->bigInteger('co_id', true);
            $table->integer('co_b_id')->index('co_b_id');
            $table->integer('cop_id')->index('fk_fh_compoff_fh_compoff_policy');
            $table->bigInteger('co_emp_id')->index('co_emp_id');
            $table->integer('co_record_id')->index('fk_fh_compoff_fh_attendance_records');
            $table->string('co_code', 50)->nullable();
            $table->string('co_reason')->nullable();
            $table->date('co_request_date');
            $table->date('co_credit_date')->nullable();
            $table->boolean('co_is_expiry')->nullable();
            $table->decimal('co_alloted', 5)->nullable();
            $table->boolean('co_carry_forward')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compoff');
    }
};
