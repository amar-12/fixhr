<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payslip extends Model
{
    use HasFactory;    
    protected $table = 'payslips';
    protected $primaryKey = 'p_id';

    protected $fillable = [
        'p_emp_id',
        'p_month',
        'p_year',
        'p_gross_salary',
        'p_net_salary',
        'p_total_deductions',
        'p_total_employer_deductions',
        'p_payslip_pdf',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'p_emp_id', 'emp_id');
    }
}
