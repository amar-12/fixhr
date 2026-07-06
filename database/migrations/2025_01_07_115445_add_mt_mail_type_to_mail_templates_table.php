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
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->integer('mt_mail_type')->after('mt_title');
            $table->foreign('mt_mail_type')
                ->references('m_id')
                ->on('master_table')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->dropForeign(['mt_mail_type']);
            $table->dropColumn('mt_mail_type');
        });
    }
};
