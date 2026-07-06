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
        Schema::create('compoff_balance', function (Blueprint $table) {
            $table->bigInteger('cb_id', true);
            $table->integer('cb_b_id')->index('fk_fh_compoff_balance_fh_businesses');
            $table->bigInteger('cb_emp_id')->index('fk_fh_compoff_balance_fh_employees');
            $table->integer('cb_year');
            $table->integer('cb_month');
            $table->decimal('cb_alloted', 10);
            $table->decimal('cb_taken', 10)->nullable();
            $table->decimal('cb_expired', 10)->nullable();
            $table->decimal('cb_balance_remaining', 10);
            $table->decimal('cb_carried_forward', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compoff_balance');
    }
};
