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
        Schema::create('form_challans', function (Blueprint $table) {
            $table->bigIncrements('f16ac_id');
            $table->unsignedBigInteger('f16ac_b_id');
            $table->unsignedBigInteger('f16ac_emp_id');
            $table->unsignedBigInteger('f16ac_fy_id');
            $table->string('f16ac_quarter', 3);
            $table->string('f16ac_form_key', 30)->default('FORM16A');
            $table->string('f16ac_receipt_no', 100);
            $table->string('f16ac_bsr_code', 20);
            $table->date('f16ac_challan_date');
            $table->string('f16ac_challan_serial_no', 30)->nullable();
            $table->unsignedBigInteger('f16ac_created_by')->nullable();
            $table->timestamps();

            $table->index(['f16ac_b_id', 'f16ac_fy_id', 'f16ac_emp_id'], 'idx_f16a_challan_lookup');
            $table->index(['f16ac_b_id', 'f16ac_fy_id', 'f16ac_form_key'], 'idx_form_challan_form_fy');
            $table->unique(['f16ac_b_id', 'f16ac_emp_id', 'f16ac_fy_id', 'f16ac_quarter', 'f16ac_form_key'], 'uniq_form_challan_scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_challans');
    }
};
