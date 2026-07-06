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
        Schema::create('uniform_items', function (Blueprint $table) {
            $table->integer('uit_id', true);
            $table->integer('uit_emp_id');
            $table->integer('uit_b_id');
            $table->integer('uit_issue_id');
            $table->integer('uit_material_id');
            $table->integer('uit_description_id');
            $table->string('uit_color', 50);
            $table->string('uit_size', 50);
            $table->decimal('uit_price', 10);
            $table->integer('uit_quantity');
            $table->enum('uit_payable', ['Yes', 'No']);
            $table->date('uit_issues_date');
            $table->string('uit_image_path')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uniform_items');
    }
};
