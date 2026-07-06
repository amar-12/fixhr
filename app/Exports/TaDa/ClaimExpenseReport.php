<?php
namespace App\Exports\TaDa;
use App\Models\MasterTable;
use App\Models\TravelPurpose;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
class ClaimExpenseReport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
        // dd($this->data);
         $this->travelTypes = MasterTable::where('m_group', 'TRAVEL_TYPE')
        ->pluck('m_name', 'm_id') // [id => name]
        ->toArray();
        $tpIds = collect($this->data)->pluck('trp_purpose')->unique()->filter()->toArray();
    // Fetch their names from TravelPurpose
    $this->travelPurpose = TravelPurpose::whereIn('tp_id', $tpIds)
        ->pluck('tp_name', 'tp_id')
        ->toArray();
    // dd($this->travelPurpose);
    }
    
    public function collection()
    {
        return collect($this->data)->map(function ($item, $index) {
            return [
                'S No' => $index + 1,
                'Employee Code'     => $item['fh_employee']['emp_code'] ?? '',
                'Employee Name'     => $item['fh_employee']['emp_full_name'] ?? '',
                'Travel Type' => $this->travelTypes[$item['fh_policy_tada_travel_type']['pttt_type_id']] ?? '',
                'Status'            => $item['fh_approval_status']['m_name'] ?? '',
                'Claim Amount'      => $item['fh_tada_claim']['tc_total_amount'] ?? ($item['fh_tada_claim']['tc_claimed_amount'] ?? ''),
                'Claim Status'      => $item['fh_tada_claim']['tc_status'] ?? '',
                'Expense Count'     => isset($item['fh_tada_expenses']) ? count($item['fh_tada_expenses']) : 0,
                'Expense Total'     => collect($item['fh_tada_expenses'])->sum('te_amount') ?? 0,
                'Request ID'        => $item['trp_unique_id'] ?? $item['trp_id'] ?? '',
                'Purpose' => $this->travelPurpose[$item['trp_purpose']] ?? '',
                 'Created Date' => isset($item['created_at']) ? \Carbon\Carbon::parse($item['created_at'])->toDateString() : '',
                'Start Date'        => $item['trp_start_date'] ?? '',
                'End Date'          => $item['trp_end_date'] ?? '',
                'Total Days'        => $item['trp_total_days'] ??
                    (isset($item['trp_start_date'], $item['trp_end_date'])
                        ? \Carbon\Carbon::parse($item['trp_start_date'])
                            ->diffInDays(\Carbon\Carbon::parse($item['trp_end_date'])) + 1
                        : ''),
            ];
        });
    }
    public function headings(): array
    {
        return [
            'S No',
            'Employee Code',
            'Employee Name',
            'Travel Type',
            'Status',
            'Claim Amount',
            'Claim Status',
            'Expense Count',
            'Expense Total',
            'Request ID',
            'Purpose',
            'Created Date',
            'Start Date',
            'End Date',
            'Total Days',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // Apply bold style to heading row
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        return [];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 5, // S No
            'B' => 20, // Employee Name
            'C' => 15, // Travel Type
            'D' => 15, // Status
            'E' => 20, // Created At
            'F' => 15, // Claim Amount
            'G' => 15, // Claim Status
            'H' => 15, // Expense Count
            'I' => 20, // Expense Total
            'J' => 20, // Request ID
            'K' => 25, // Purpose
            'L' => 15, // Start Date
            'M' => 15, // End Date
            'N' => 12, // Total Days
        ];
    }
}
