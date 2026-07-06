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
        Schema::table('gatepasses', function (Blueprint $table) {
            $table->foreign(['gtp_emp_id'], 'fh_gatepasses_ibfk_1')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['gtp_status'], 'fh_gatepasses_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['gtp_module_id'], 'fh_gatepasses_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['gtp_am_id'], 'fh_gatepasses_ibfk_4')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gatepasses', function (Blueprint $table) {
            $table->dropForeign('fh_gatepasses_ibfk_1');
            $table->dropForeign('fh_gatepasses_ibfk_2');
            $table->dropForeign('fh_gatepasses_ibfk_3');
            $table->dropForeign('fh_gatepasses_ibfk_4');
        });
    }
};
