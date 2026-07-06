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
        Schema::create('opening_balances', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('eob_emp_id')->index('fk_eob_emp');
            $table->integer('eob_b_id');
            $table->decimal('eob_cl', 5)->nullable()->default(0);
            $table->decimal('eob_sl', 5)->nullable()->default(0);
            $table->decimal('eob_el', 5)->nullable()->default(0);
            $table->decimal('eob_total_leave', 6)->nullable()->storedAs('`eob_cl` + `eob_sl` + `eob_el`');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opening_balances');
    }
};
