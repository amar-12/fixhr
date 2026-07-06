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
        Schema::create('action_upon_rejection', function (Blueprint $table) {
            $table->integer('aur_id', true);
            $table->integer('aur_b_id')->nullable()->index('aur_b_id_2');
            $table->bigInteger('aur_emp_id')->nullable()->index('aur_b_id');
            $table->integer('aur_am_id')->nullable()->index('aur_module_id');
            $table->string('aur_group_ids', 100)->nullable();
            $table->integer('aur_status_id')->nullable()->default(170)->index('aur_status_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_upon_rejection');
    }
};
