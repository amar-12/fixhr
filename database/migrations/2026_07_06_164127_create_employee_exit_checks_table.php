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
        Schema::create('employee_exit_checks', function (Blueprint $table) {
            $table->integer('b_id');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('eec_er_id');
            $table->boolean('handover_documents')->nullable()->default(false);
            $table->boolean('id_card')->nullable()->default(false);
            $table->boolean('insurance_card')->nullable()->default(false);
            $table->boolean('helmet')->nullable()->default(false);
            $table->boolean('exit_interview')->nullable()->default(false);
            $table->boolean('vehicle')->nullable()->default(false);
            $table->boolean('petrol_card')->nullable()->default(false);
            $table->string('hr_others')->nullable();
            $table->boolean('laptop')->nullable()->default(false);
            $table->boolean('mouse')->nullable()->default(false);
            $table->boolean('email')->nullable()->default(false);
            $table->boolean('mobile')->nullable()->default(false);
            $table->boolean('storage')->nullable()->default(false);
            $table->boolean('access')->nullable()->default(false);
            $table->boolean('whatsapp')->nullable()->default(false);
            $table->boolean('github')->nullable()->default(false);
            $table->boolean('sheet')->nullable()->default(false);
            $table->boolean('credentials')->nullable()->default(false);
            $table->string('it_others')->nullable();
            $table->boolean('signatory')->nullable()->default(false);
            $table->boolean('lease')->nullable()->default(false);
            $table->boolean('loan')->nullable()->default(false);
            $table->boolean('salary_adv')->nullable()->default(false);
            $table->boolean('travel')->nullable()->default(false);
            $table->boolean('deduction')->nullable()->default(false);
            $table->boolean('bank_loan')->nullable()->default(false);
            $table->boolean('pf')->nullable()->default(false);
            $table->boolean('notice')->nullable()->default(false);
            $table->boolean('buyback')->nullable()->default(false);
            $table->boolean('accessories')->nullable()->default(false);
            $table->string('finance_others')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_exit_checks');
    }
};
