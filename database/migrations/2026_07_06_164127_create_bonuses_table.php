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
        Schema::create('bonuses', function (Blueprint $table) {
            $table->bigInteger('bonus_id', true);
            $table->integer('bonus_b_id')->nullable()->index('bonus_b_id');
            $table->bigInteger('bonus_emp_id')->index('bonus_emp_id');
            $table->decimal('bonus_amount', 10)->nullable();
            $table->string('bonus_type', 50)->nullable();
            $table->date('bonus_date')->nullable();
            $table->text('bonus_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
