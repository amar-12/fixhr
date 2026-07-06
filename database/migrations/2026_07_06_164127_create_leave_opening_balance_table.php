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
        Schema::create('leave_opening_balance', function (Blueprint $table) {
            $table->integer('lob_id', true);
            $table->integer('lob_b_id')->index('fk_fh_leave_opening_balance_fh_businesses');
            $table->bigInteger('lob_emp_id')->index('fk_fh_leave_opening_balance_fh_employees');
            $table->integer('lob_leave_type_id')->index('fk_fh_leave_opening_balance_fh_master_table');
            $table->decimal('lob_previous', 10);
            $table->decimal('lob_updated', 10);
            $table->decimal('lob_total', 10);
            $table->integer('updated_by');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_opening_balance');
    }
};
