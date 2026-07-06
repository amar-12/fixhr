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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->unsignedBigInteger('ffd_id');
            $table->unsignedBigInteger('ffd_b_id');
            $table->unsignedBigInteger('ffd_frm_id');
            $table->string('ffd_field_name');
            $table->string('ffd_field_type');
            $table->boolean('ffd_is_required')->default(false);
            $table->string('ffd_field_placeholder');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
