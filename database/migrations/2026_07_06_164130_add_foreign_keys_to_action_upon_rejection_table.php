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
        Schema::table('action_upon_rejection', function (Blueprint $table) {
            $table->foreign(['aur_am_id'], 'fh_action_upon_rejection_ibfk_2')->references(['am_id'])->on('approval_modules')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['aur_status_id'], 'fh_action_upon_rejection_ibfk_4')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['aur_emp_id'], 'fh_action_upon_rejection_ibfk_5')->references(['emp_id'])->on('employees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['aur_b_id'], 'fh_action_upon_rejection_ibfk_6')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('action_upon_rejection', function (Blueprint $table) {
            $table->dropForeign('fh_action_upon_rejection_ibfk_2');
            $table->dropForeign('fh_action_upon_rejection_ibfk_4');
            $table->dropForeign('fh_action_upon_rejection_ibfk_5');
            $table->dropForeign('fh_action_upon_rejection_ibfk_6');
        });
    }
};
