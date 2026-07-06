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
        Schema::create('compoff_duration_conditions', function (Blueprint $table) {
            $table->integer('condition_id', true);
            $table->integer('condition_b_id')->index('fk_fh_compoff_duration_conditions_fh_businesses');
            $table->integer('cop_id')->index('fk_fh_compoff_duration_conditions_fh_compoff_policy');
            $table->decimal('work_duration', 10);
            $table->enum('operator', ['=', '>', '<', '>=', '<=']);
            $table->decimal('co_quantity', 10);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compoff_duration_conditions');
    }
};
