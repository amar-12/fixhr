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
        Schema::create('compoff_policy', function (Blueprint $table) {
            $table->integer('cop_id', true);
            $table->integer('cop_b_id')->index('fk_fh_compoff_policy_fh_businesses');
            $table->string('co_policy_name');
            $table->integer('carry_forward');
            $table->integer('validity');
            $table->date('cop_effective_date');
            $table->boolean('cop_status')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compoff_policy');
    }
};
