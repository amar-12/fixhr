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
        Schema::create('recruitment_stage', function (Blueprint $table) {
            $table->id('rsg_id'); // Creates the 'rsg_id' field as BIGINT and AUTO_INCREMENT
            $table->boolean('rsg_is_active'); // 'rsg_is_active' field as TINYINT(1) in SQL
            $table->string('rsg_stage', 50); // 'rsg_stage' field as VARCHAR(50)
            $table->integer('rsg_sequence')->nullable(); // 'rsg_sequence' field as INT(11), nullable
            $table->integer('rsg_stage_type')->nullable(); // Ensure it matches INT(11)
            $table->bigInteger('rsg_created_by_id'); // Foreign key to employees
            $table->bigInteger('rsg_modified_by_id'); // Foreign key to employees

            // Foreign key to 'master_table' for stage type
            $table->foreign('rsg_stage_type')
            ->references('m_id')
            ->on('master_table')
            ->onDelete('set null'); // Handle deletion gracefully


            // Foreign key to 'employees' for created_by
            $table->foreign('rsg_created_by_id')->references('emp_id')->on('employees')->onDelete('cascade');

            // Foreign key to 'employees' for modified_by
            $table->foreign('rsg_modified_by_id')->references('emp_id')->on('employees')->onDelete('cascade');

            // Foreign key to 'recruitment' for recruitment_id
            $table->foreignId('rsg_recruitment_id')->constrained('recruitment', 'r_id')->onDelete('cascade'); // Assumes 'id' is the primary key of 'recruitment'

            $table->timestamps(); // Creates 'created_at' and 'updated_at' with microseconds

            // Define unique key
            $table->unique(['rsg_recruitment_id', 'rsg_stage'], 'recruitment_stage_recruitment_id_stage_41b4d1c0_uniq');

            // Define indexes
            $table->index('rsg_created_by_id', 'recruitment_stage_created_by_id_93d9a4be_fk_employees_id');
            $table->index('rsg_modified_by_id', 'recruitment_stage_modified_by_id_96c190ac_fk_employees_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_stage');
    }
};
