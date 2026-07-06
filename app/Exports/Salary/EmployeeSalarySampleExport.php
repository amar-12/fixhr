<?php

namespace App\Exports\Salary;

use App\Models\SalaryAllowance;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EmployeeSalarySampleExport implements FromCollection,WithMapping, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
   protected $businessId;

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
    }

    public function collection()
    {
        return SalaryAllowance::where('sa_b_id', $this->businessId)
            ->where('sa_is_active', 1)
            ->orderBy('sa_sequence_val', 'asc')
            ->get([
                'sa_title',
                'sa_name_in_payslip',
                'sa_calculation_type',
                'sa_threshold_value',
                'sa_consider_for_pf',
                'sa_consider_for_esic',
                'sa_is_taxable',
                'sa_show_in_payslip',
                'sa_is_active',
            ]);
    }

    public function map($row): array
    {
        return [
            $row->sa_title,
            $row->sa_name_in_payslip,
            $this->calcType($row->sa_calculation_type),
            $row->sa_threshold_value,
            $row->sa_consider_for_pf ? 'Yes' : 'No',
            $row->sa_consider_for_esic ? 'Yes' : 'No',
            $row->sa_is_taxable ? 'Yes' : 'No',
            $row->sa_show_in_payslip ? 'Yes' : 'No',
            $row->sa_is_active ? 'Active' : 'Inactive',
        ];
    }

    public function headings(): array
    {
        return [
            'Allowance Title',
            'Payslip Name',
            'Calculation Type',
            'Threshold Value',
            'Consider for PF',
            'Consider for ESIC',
            'Is Taxable',
            'Show in Payslip',
            'Status',
        ];
    }

    private function calcType($type)
    {
        return match ($type) {
            1 => 'Fixed',
            2 => 'Percentage',
            3 => 'Formula',
            default => 'Unknown',
        };
    }
}
