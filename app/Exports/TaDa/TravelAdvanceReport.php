<?php

namespace App\Exports\TaDa;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Helpers\ApprovalHelper;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class TravelAdvanceReport implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell, ShouldAutoSize, WithEvents
{
    protected $data;
    protected $travelTypes;
    protected $travelPurpose;

    public function __construct($data)
    {
        $this->data = $data;
        $this->travelTypes = [
            1 => 'Local',
            2 => 'Outstation',
            3 => 'International'
        ];
        $this->travelPurpose = [
            1 => 'Business Meeting',
            2 => 'Training',
            3 => 'Client Visit',
            4 => 'Conference',
            5 => 'Other'
        ];
    }

    public function collection()
    {
        // dd($this->data);
        return collect($this->data)->map(function ($item, $index) {
            $user = Auth::user();
            $item_claim = $item['fh_tada_claim'];
            
            // Skip this item if there's no claim data
            if (!$item_claim) {
                return null;
            }
            
            // Check if required properties exist before proceeding
            if (!isset($item_claim['tc_id']) || !isset($item_claim['tc_b_id']) || !isset($item_claim['tc_module_id']) || !isset($item_claim['tc_emp_id'])) {
                return null;
            }
            
            $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item_claim['tc_id'], $item_claim['tc_b_id']);
            $approval = ApprovalHelper::checkApproval($user->emp_b_id, $item_claim['tc_module_id'], $item_claim['tc_trp_id'], optional($item['fh_employee'])->emp_d_id, $item_claim['tc_emp_id']);

            // Ensure we have valid approval data
            if (!is_array($nextApproval) || !is_array($approval)) {
                return null;
            }

            // Show only records that have a valid advance amount (> 0)
            $numericAdvance = $this->getAdvanceAmount($item);
            if (!($numericAdvance !== null && $numericAdvance > 0)) {
                return null;
            }

            $approverName = '---';
            if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                $approvalMapping = ApprovalHelper::getApprovalMapping($item_claim['tc_b_id'], $item_claim['tc_emp_id'], $item_claim['tc_module_id']);
                if ($approvalMapping) {
                    $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);

                    // Check if the relationship exists and has data
                    if (isset($item_claim['fh_approval_log_employee_wise']) && $item_claim['fh_approval_log_employee_wise']) {
                        $approvalLogSource = $item_claim['fh_approval_log_employee_wise'];

                        // Normalize source: support Collection, array, or JSON string
                        if (is_string($approvalLogSource)) {
                            $decoded = json_decode($approvalLogSource, true);
                            $approvalLogSource = is_array($decoded) ? $decoded : [];
                        }

                        if ($approvalLogSource instanceof \Illuminate\Support\Collection) {
                            $approvalLog = $approvalLogSource->pluck('log_user_id')->toArray();
                        } elseif (is_array($approvalLogSource)) {
                            $approvalLog = array_values(array_filter(array_map(function ($row) {
                                if (is_array($row) && isset($row['log_user_id'])) {
                                    return $row['log_user_id'];
                                }
                                if (is_object($row) && isset($row->log_user_id)) {
                                    return $row->log_user_id;
                                }
                                return null;
                            }, $approvalLogSource), function ($v) {
                                return $v !== null && $v !== '';
                            }));
                        } else {
                            $approvalLog = [];
                        }

                        $diff = array_values(array_diff((array) $approvalArray, (array) $approvalLog));
                        if ($diff) {
                            $approverName = Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ?? '---';
                        }
                    }
                }
            }

            // dd($item['fh_deduction_log']);
            return [
                'Travel Type' => $item['fh_policy_tada_travel_type']['fh_travel_type']['m_name'] ?? '---',
                'Plan Unique Id' => $item['trp_unique_id'] ?? '---',
                'Employee Code' => $item['fh_employee']['emp_code'] ?? '---',
                'Employee Name' => $item['fh_employee']['emp_full_name'] ?? '---',
                'Plan Name' => $item['trp_name'] ?? '---',
                'Travel Purpose' => $item['fh_travel_purpose']['tp_name'] ?? '---',
                'Travel Remark' => $item['trp_remarks'] ?? '---',
                'Travel Advance' => $numericAdvance ?? '---',
                
                'Advance ID' => isset($item['fh_tada_advance_approval_log'][0]['adl_id']) ? $item['fh_tada_advance_approval_log'][0]['adl_id'] : '---',
                'Submitted To' => isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : $approverName,
                'Status' => $item['fh_tada_advance_approval_log'][0]['fh_adl_request_status']['m_name'] ?? '---',
                'Requested Amount' => $item['fh_tada_advance_approval_log'][0]['adl_requested_amount'] ?? '---',
                'Approved amount' => $item['fh_tada_advance_approval_log'][0]['adl_reimburse_amount'] ?? '---',
                'Emp Remark' => $item['fh_tada_advance_approval_log'][0]['adl_remark'] ?? '---',
                'Approved By' => $item['fh_tada_advance_approval_log'][0]['fh_process_approvers'][0]['pa_message'] ?? '---',
                'Bank Status' => $this->getBankStatus($item) ?? '---',
               
            ];
        })->filter(function ($row) {
            if ($row === null) {
                return false;
            }
            // Remove rows that would appear blank (all values null/empty/'---')
            $hasMeaningfulValue = collect($row)->contains(function ($value) {
                return !in_array($value, [null, '', '---'], true);
            });
            return $hasMeaningfulValue;
        })->values()->map(function ($row, $i) {
            // Prepend continuous serial number
            return array_merge(['S No' => $i + 1], $row);
        });
    }

    public function headings(): array
    {
        return [
            'S No',
            'Travel Type',
            'Plan Unique Id',
            'Employee Code',
            'Employee Name',
            'Plan Name',
            'Travel Purpose',
            'Travel Remark',
            'Travel Advance',
            'Advance ID',
            'Submitted To',
            'Status',
            'Requested Amount',
            'Approved amount',
            'Emp Remark',
            'Approved By',
            'Bank Status',  
        ];
    }

    /**
     * Get the name of the person who approved the claim
     */
    private function getApprovedBy($item_claim)
    {
        if (isset($item_claim['fh_approval_log_employee_wise']) && $item_claim['fh_approval_log_employee_wise']) {
            $source = $item_claim['fh_approval_log_employee_wise'];
            if (is_string($source)) {
                $decoded = json_decode($source, true);
                $source = is_array($decoded) ? $decoded : [];
            }

            if ($source instanceof \Illuminate\Support\Collection) {
                $latestApproval = $source->where('log_status', 'approved')->sortByDesc('created_at')->first();
                if ($latestApproval) {
                    return Employee::where('emp_id', $latestApproval->log_user_id)->pluck('emp_full_name')->first() ?? '---';
                }
            } else {
                $collection = collect(is_array($source) ? $source : []);
                $latestApproval = $collection->filter(function ($row) {
                    return (is_array($row) && ($row['log_status'] ?? null) === 'approved') || (is_object($row) && ($row->log_status ?? null) === 'approved');
                })->sortByDesc(function ($row) {
                    return is_array($row) ? ($row['created_at'] ?? null) : ($row->created_at ?? null);
                })->first();
                if ($latestApproval) {
                    $logUserId = is_array($latestApproval) ? ($latestApproval['log_user_id'] ?? null) : ($latestApproval->log_user_id ?? null);
                    if ($logUserId) {
                        return Employee::where('emp_id', $logUserId)->pluck('emp_full_name')->first() ?? '---';
                    }
                }
            }
        }
        return '---';
    }

    /**
     * Get the bank status based on payment information
     */
    private function getBankStatus($item)
    {
        if (isset($item['fh_tada_advance_approval_log'][0]['fh_adl_request_status']['m_name']) && $item['fh_tada_advance_approval_log'][0]['fh_adl_request_status']['m_name'] == 'Approved') {
            return 'Paid';
        } elseif (isset($item['fh_tada_advance_approval_log'][0]['fh_adl_request_status']['m_name']) && $item['fh_tada_advance_approval_log'][0]['fh_adl_request_status']['m_name'] == 'Processed') {
            return 'Processed';
        } else {
            return 'Pending';
        }
    }

    /**
     * Parse an amount into a float if possible, handling strings with commas/spaces; otherwise null.
     */
    private function parseAmount($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }
            // Remove thousand separators/commas and spaces
            $normalized = str_replace([',', ' '], '', $trimmed);
            if ($normalized === '' || !is_numeric($normalized)) {
                return null;
            }
            return (float) $normalized;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        return null;
    }

    /**
     * Determine the effective advance amount for filtering/display.
     * Priority: trp_advance_allowance -> adl_requested_amount -> adl_reimburse_amount
     */
    private function getAdvanceAmount($item)
    {
        $primary = $this->parseAmount($item['trp_advance_allowance'] ?? null);
        if ($primary !== null && $primary > 0) {
            return $primary;
        }
        $requested = $this->parseAmount($item['fh_tada_advance_approval_log'][0]['adl_requested_amount'] ?? null);
        if ($requested !== null && $requested > 0) {
            return $requested;
        }
        $approved = $this->parseAmount($item['fh_tada_advance_approval_log'][0]['adl_reimburse_amount'] ?? null);
        if ($approved !== null && $approved > 0) {
            return $approved;
        }
        return null;
    }

    public function styles(Worksheet $sheet)
    {
        // Apply bold style to heading row - updated to cover all columns (A1:AJ1)
 // Set header rows in rows 1-3
 $sheet->setCellValue('A1', 'TADA Report');
 $sheet->setCellValue('A2', date('d-m-Y'));  
 $sheet->setCellValue('A3', 'Report Type: Travel Advance Report');
 
 // Merge header cells
 $sheet->mergeCells('A1:Q1');
 $sheet->setCellValue('A2', date('d-m-Y'));  
 $sheet->mergeCells('A2:Q2'); 
 $sheet->mergeCells('A3:Q3');
 
 // Style the header rows
 $sheet->getStyle('A1:Q3')->getFont()->setBold(true);
 $sheet->getStyle('A1:Q3')->getFont()->setSize(12);
 $sheet->getStyle('A1:Q3')->getAlignment()->setHorizontal('left');
 $sheet->getStyle('A1:Q3')->getAlignment()->setVertical('center');
 
 // Style the column headers (row 4)
 $sheet->getStyle('A4:Q4')->getFont()->setBold(true);    
 $sheet->getStyle('A4:Q4')->getFont()->setSize(11);
 $sheet->getStyle('A4:Q4')->getAlignment()->setHorizontal('left');
 $sheet->getStyle('A4:Q4')->getAlignment()->setVertical('center');      
   return [];
    }

    


    public function startCell(): string
    {
        return 'A4'; // Table starts at row 4
    }


    // Using ShouldAutoSize; ensure autosize after sheet is built as well
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Auto-size columns A through Q explicitly (headers + data)
                for ($col = ord('A'); $col <= ord('Q'); $col++) {
                    $letter = chr($col);
                    $sheet->getColumnDimension($letter)->setAutoSize(true);
                }
            },
        ];
    }
}
