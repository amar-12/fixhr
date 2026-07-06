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
        Schema::table('businesses', function (Blueprint $table) {
            $table->foreign(['b_category_id'], 'fh_businesses_ibfk_2')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['b_type_id'], 'fh_businesses_ibfk_3')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['b_city_id'], 'fh_businesses_ibfk_4')->references(['ct_id'])->on('cities')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['b_state_id'], 'fh_businesses_ibfk_5')->references(['s_id'])->on('states')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['b_country_id'], 'fh_businesses_ibfk_6')->references(['c_id'])->on('countries')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['b_emp_code_type'], 'fh_businesses_ibfk_7')->references(['m_id'])->on('master_table')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['b_currency'], 'fh_businesses_ibfk_8')->references(['c_id'])->on('countries')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign('fh_businesses_ibfk_2');
            $table->dropForeign('fh_businesses_ibfk_3');
            $table->dropForeign('fh_businesses_ibfk_4');
            $table->dropForeign('fh_businesses_ibfk_5');
            $table->dropForeign('fh_businesses_ibfk_6');
            $table->dropForeign('fh_businesses_ibfk_7');
            $table->dropForeign('fh_businesses_ibfk_8');
        });
    }
};
