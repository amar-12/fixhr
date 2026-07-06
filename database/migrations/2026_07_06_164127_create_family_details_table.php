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
        Schema::create('family_details', function (Blueprint $table) {
            $table->integer('fd_id', true);
            $table->integer('fd_emp_id');
            $table->integer('fd_b_id');
            $table->string('fd_name', 100);
            $table->string('fd_relation', 50);
            $table->date('fd_dob');
            $table->string('fd_dependency', 100)->nullable();
            $table->string('fd_occupation', 100)->nullable();
            $table->string('fd_contact', 15)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_details');
    }
};
