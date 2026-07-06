<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExitChecks extends Model
{
    use HasFactory;

    // 🔹 Table name (optional if it follows convention)
    protected $table = 'employee_exit_checks';

    // 🔹 Mass assignable columns
    protected $fillable = [
        // Manager
        'eec_er_id',
        'b_id',
        'handover_documents',

        // HR
        'id_card',
        'insurance_card',
        'helmet',
        'exit_interview',
        'vehicle',
        'petrol_card',
        'hr_others',

        // IT
        'laptop',
        'mouse',
        'email',
        'mobile',
        'storage',
        'access',
        'whatsapp',
        'github',
        'sheet',
        'credentials',
        'it_others',

        // Finance
        'signatory',
        'lease',
        'loan',
        'salary_adv',
        'travel',
        'deduction',
        'bank_loan',
        'pf',
        'notice',
        'buyback',
        'accessories',
        'finance_others',
    ];

    // 🔹 Cast boolean columns (assuming most of these are true/false)
    protected $casts = [
        'handover_documents' => 'boolean',
        'id_card'            => 'boolean',
        'insurance_card'     => 'boolean',
        'helmet'             => 'boolean',
        'exit_interview'     => 'boolean',
        'vehicle'            => 'boolean',
        'petrol_card'        => 'boolean',
        'laptop'             => 'boolean',
        'mouse'              => 'boolean',
        'email'              => 'boolean',
        'mobile'             => 'boolean',
        'storage'            => 'boolean',
        'access'             => 'boolean',
        'whatsapp'           => 'boolean',
        'github'             => 'boolean',
        'sheet'              => 'boolean',
        'credentials'        => 'boolean',
        'signatory'          => 'boolean',
        'lease'              => 'boolean',
        'loan'               => 'boolean',
        'salary_adv'         => 'boolean',
        'travel'             => 'boolean',
        'deduction'          => 'boolean',
        'bank_loan'          => 'boolean',
        'pf'                 => 'boolean',
        'notice'             => 'boolean',
        'buyback'            => 'boolean',
        'accessories'        => 'boolean',
        // Optional: hr_others, it_others, finance_others can be string
        'hr_others'          => 'string',
        'it_others'          => 'string',
        'finance_others'     => 'string',
    ];
}