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
        Schema::create('previous_organization', function (Blueprint $table) {
            $table->integer('po_id', true);
            $table->string('po_company_name');
            $table->integer('po_emp_id');
            $table->string('po_designation_name');
            $table->date('po_from_date');
            $table->date('po_to_date');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('po_serviceduration', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('previous_organization');
    }
};
