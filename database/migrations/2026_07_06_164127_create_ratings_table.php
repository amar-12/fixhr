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
        Schema::create('ratings', function (Blueprint $table) {
            $table->bigIncrements('rat_id');
            $table->integer('rat_b_id')->index('fk_ratings_business');
            $table->bigInteger('rat_emp_id')->index('fk_ratings_employee');
            $table->string('rat_type', 100)->nullable();
            $table->integer('rat_count')->nullable()->default(0);
            $table->text('rat_remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
