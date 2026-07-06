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
        Schema::table('departments', function (Blueprint $table) {
            // Adding foreign key columns with nullable option
            $table->integer('d_ap_id')->nullable()->after('d_name'); // Reference to attendance policies
            $table->integer('d_pst_id')->nullable()->after('d_ap_id'); // Reference to shift policies
            $table->integer('d_phl_id')->nullable()->after('d_pst_id'); // Reference to holiday policies
            $table->integer('d_pl_id')->nullable()->after('d_phl_id'); // Reference to leave policies
            $table->integer('d_pwo_id')->nullable()->after('d_pl_id'); // Reference to weekly policies

            // Adding indexes for foreign key columns
            $table->index('d_ap_id');
            $table->index('d_pst_id');
            $table->index('d_phl_id');
            $table->index('d_pl_id');
            $table->index('d_pwo_id');

            // Add foreign key constraints with desired behavior on delete
            $table->foreign('d_ap_id')->references('ap_id')->on('policy_attendances')->onDelete('restrict'); // Prevent deletion if referenced
            $table->foreign('d_pst_id')->references('pst_id')->on('policy_shift_timings')->onDelete('restrict'); // Prevent deletion if referenced
            $table->foreign('d_phl_id')->references('phl_id')->on('policy_holiday_list')->onDelete('restrict'); // Prevent deletion if referenced
            $table->foreign('d_pl_id')->references('pl_id')->on('policy_leaves')->onDelete('restrict'); // Prevent deletion if referenced
            $table->foreign('d_pwo_id')->references('pwo_id')->on('policy_week_off')->onDelete('restrict'); // Prevent deletion if referenced
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['d_ap_id']); // Drop foreign key for attendance policy
            $table->dropForeign(['d_pst_id']); // Drop foreign key for shift policy
            $table->dropForeign(['d_phl_id']); // Drop foreign key for holiday policy
            $table->dropForeign(['d_pl_id']); // Drop foreign key for leave policy
            $table->dropForeign(['d_pwo_id']); // Drop foreign key for weekly policy

            // Drop the foreign key columns
            $table->dropColumn([
                'd_ap_id',  // Attendance policy column
                'd_pst_id', // Shift policy column
                'd_phl_id', // Holiday policy column
                'd_pl_id',  // Leave policy column
                'd_pwo_id', // Weekly policy column
            ]);
        });
    }
};
