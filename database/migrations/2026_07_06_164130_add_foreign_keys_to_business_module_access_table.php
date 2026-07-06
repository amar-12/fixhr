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
        Schema::table('business_module_access', function (Blueprint $table) {
            $table->foreign(['bma_b_id'], 'fh_business_module_access_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['bma_mdl_id'], 'fh_business_module_access_ibfk_2')->references(['mdl_id'])->on('modules')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_module_access', function (Blueprint $table) {
            $table->dropForeign('fh_business_module_access_ibfk_1');
            $table->dropForeign('fh_business_module_access_ibfk_2');
        });
    }
};
