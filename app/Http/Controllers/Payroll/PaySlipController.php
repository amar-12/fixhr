<?php

namespace App\Http\Controllers\Payroll;

use NumberFormatter;
use App\Models\Employee;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaySlipController extends Controller
{
    public function generatePayslip($employee_id, $month)
    {
        $employee = Employee::findOrFail($employee_id);
        
        // Fetch salary details from database
        $earnings = [
            ['head' => 'Basic', 'amount' => 20000],
            ['head' => 'Dearness Allowance', 'amount' => 5000],
            ['head' => 'House Rent Allowance', 'amount' => 8000],
            ['head' => 'Conveyance Allowance', 'amount' => 1600],
            ['head' => 'Other Allowance', 'amount' => 5400],
        ];

        $deductions = [
            ['head' => 'Provident Fund', 'amount' => 0],
            ['head' => 'ESIC', 'amount' => 0],
            ['head' => 'Advance', 'amount' => 0],
            ['head' => 'Other', 'amount' => 0],
        ];

        $gross_salary = 40000;
        $total_deductions = 0;
        $net_salary = $gross_salary - $total_deductions;
        $net_salary_words = ucfirst((new NumberFormatter('en', NumberFormatter::SPELLOUT))->format($net_salary));

        $pdf = PDF::loadView('payslip', compact(
            'employee',
            'month',
            'earnings',
            'deductions',
            'gross_salary',
            'total_deductions',
            'net_salary',
            'net_salary_words'
        ));

        return $pdf->download("Payslip-{$employee->name}-{$month}.pdf");
    }
}
