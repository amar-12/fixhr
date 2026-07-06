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
        Schema::create('tada_expense_settings', function (Blueprint $table) {
            $table->integer('tes_id', true);
            $table->integer('tes_expense_type_id')->nullable()->index('te_trp_id');
            $table->integer('tes_b_id')->nullable();
            $table->integer('tes_code')->index('te_type_id');
            $table->string('tes_head')->nullable();
            $table->tinyInteger('tes_is_fixed')->nullable()->default(0);
            $table->double('tes_fixed_amount')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tada_expense_settings');
    }
};
