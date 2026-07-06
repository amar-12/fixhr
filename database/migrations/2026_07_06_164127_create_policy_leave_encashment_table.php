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
        Schema::create('policy_leave_encashment', function (Blueprint $table) {
            $table->bigInteger('lep_id', true);
            $table->integer('lep_b_id')->nullable()->index('lep_b_id');
            $table->decimal('lep_max_encashment_percentage', 5)->nullable();
            $table->integer('lep_min_balance_required')->nullable();
            $table->date('lep_effective_date')->nullable();
            $table->date('lep_expiration_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_leave_encashment');
    }
};
