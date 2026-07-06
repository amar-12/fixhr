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
        Schema::table('roles_has_permissions', function (Blueprint $table) {
            $table->foreign(['rhp_b_id'], 'fh_roles_has_permissions_ibfk_1')->references(['b_id'])->on('businesses')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['rhp_role_id'], 'fh_roles_has_permissions_ibfk_2')->references(['role_id'])->on('roles')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles_has_permissions', function (Blueprint $table) {
            $table->dropForeign('fh_roles_has_permissions_ibfk_1');
            $table->dropForeign('fh_roles_has_permissions_ibfk_2');
        });
    }
};
