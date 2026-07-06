<?php

namespace App\Exports;

use App\Models\SmHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\Support\Responsable;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SmHistoryExport implements FromCollection, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $exportData = [];
        $wef = $this->data->created_at;
        $formattedWef = \Carbon\Carbon::parse($wef)->format('d-M-Y');
        $rowData = [
            1,
            $this->data->employees->emp_full_name ?? '',
            $this->data->business->b_name ?? '',
            $this->data->sm_monthly_ctc ?? '-',
            $this->data->sm_annual_ctc ?? '-',
            $this->data->sm_basic ?? '-',
            $this->data->sm_hra ?? '-',
            $this->data->sm_dear_allow ?? '-',
            $this->data->sm_conv_allow ?? '-',
            $this->data->sm_other_allow ?? '-',
            $this->data->sm_employee_epf ?? '-',
            $this->data->sm_employee_esic ?? '-',
            $this->data->sm_employee_lwf ?? '-',
            $this->data->sm_employee_total_ded ?? '-',
            $this->data->sm_employer_epf ?? '-',
            $this->data->sm_employer_esic ?? '-',
            $this->data->sm_employer_lwf ?? '-',
            $this->data->sm_employer_total_ded ?? '-',
            $this->data->sm_total_earning ?? '-',
            $this->data->sm_gross_pay ?? '-',
            $this->data->sm_net_pay ?? '-',
            $this->data->financial_years->fy_year ?? '',
            $this->data->sm_remark ?? '',
            $formattedWef,
        ];

        $exportData[] = $rowData;

        return collect($exportData);
    }

    public function headings(): array
    {
        return [
            'S.No.', 'Employee Name', 'Business Name', 'Monthly CTC', 'Annual CTC', 'Basic Salary',
            'HRA', 'Dearness Allowance', 'Conveyance Allowance', 'Other Allowance', 'Employee PF',
            'Employee ESIC', 'Employee LWF', 'Employee Deduction', 'Employer PF', 'Employer ESIC',
            'Employer LWF', 'Employer Deduction', 'Total Earnings', 'Gross Pay', 'Net Pay',
            'Financial Year', 'Remark', 'With Effect From Date'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ADD8E6'], // Light Blue
                ],
            ],
        ];
    }

}
