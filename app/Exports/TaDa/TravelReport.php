<?php

namespace App\Exports\TaDa;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

use App\Helpers\ApprovalHelper;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class TravelReport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $data;
    protected $travelTypes;
    protected $travelPurpose;
    protected $documentUrls = [];

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
            $item_claim = $item['fh_tada_claim'] ?? null;
            // dd($item['fh_approval_status']);
            
            $nextApproval = null;
            $approverName = '---';

            // Only compute approval details when claim data is present and complete
            if (is_array($item_claim) 
                && isset($item_claim['tc_id'], $item_claim['tc_b_id'], $item_claim['tc_module_id'], $item_claim['tc_emp_id'])) {
                $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item_claim['tc_id'], $item_claim['tc_b_id']);
                // Avoid optional() on array; only used when claim exists
                $approval = ApprovalHelper::checkApproval(
                    $user->emp_b_id,
                    $item_claim['tc_module_id'],
                    $item_claim['tc_trp_id'] ?? null,
                    null,
                    $item_claim['tc_emp_id']
                );

                if (!(is_array($nextApproval) && isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($item_claim['tc_b_id'], $item_claim['tc_emp_id'], $item_claim['tc_module_id']);
                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);

                        if (isset($item_claim['fh_approval_log_employee_wise']) && $item_claim['fh_approval_log_employee_wise']) {
                            $approvalLogSource = $item_claim['fh_approval_log_employee_wise'];
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

                            $diff = array_values(array_diff($approvalArray, $approvalLog));
                            if ($diff) {
                                $approverName = Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ?? '---';
                            }
                        }
                    }
                }
            }

            // Build document URL and store for hyperlinking after sheet is created
            $documentUrl = null;
            $rawDocument = $item['trp_document'] ?? null;
            if ($rawDocument !== null && $rawDocument !== '' && $rawDocument !== '[]') {
                $urlCandidate = null;
                if (is_string($rawDocument)) {
                    $trimmed = trim($rawDocument);
                    if (strlen($trimmed) > 0 && $trimmed[0] === '[') {
                        // Try JSON decode of array-like string
                        $decoded = json_decode($trimmed, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $urlCandidate = $decoded[0] ?? null;
                        } else {
                            // Fallback: strip brackets and quotes
                            $urlCandidate = trim($trimmed, "[]\"' ");
                        }
                    } else {
                        $urlCandidate = $trimmed;
                    }
                } elseif (is_array($rawDocument)) {
                    $urlCandidate = $rawDocument[0] ?? null;
                }

                if (is_string($urlCandidate) && $urlCandidate !== '') {
                    // Absolute URL stays as-is
                    if (preg_match('/^https?:\/\//i', $urlCandidate)) {
                        $documentUrl = $urlCandidate;
                    } else {
                        // Relative path -> default to storage
                        $documentUrl = asset('storage/' . ltrim($urlCandidate, '/'));
                    }
                }
            }
            $this->documentUrls[$index] = $documentUrl;

            // dd($item['fh_deduction_log']);
            return [
                'S No' => $index + 1,
                'Employee Name' => is_array($item['fh_employee']) ? ($item['fh_employee']['emp_full_name'] ?? '---') : '---',
                'Travel Type' => is_array($item['fh_policy_tada_travel_type']) && isset($item['fh_policy_tada_travel_type']['fh_travel_type']) ? ($item['fh_policy_tada_travel_type']['fh_travel_type']['m_name'] ?? '---') : '---',
                'Travel Id' => $item['trp_unique_id'] ?? '---',
                'Trip Name' => $item['trp_name'] ?? '---',
                'Purpose' => is_array($item['fh_travel_purpose']) ? ($item['fh_travel_purpose']['tp_name'] ?? '---') : '---',
                'Remark' =>  $item['trp_remarks'] ?? '---',
                'Document' => $documentUrl ? 'Attached' : 'Not Attached',
                'Advance' => (isset($item['trp_advance_allowance']) && $item['trp_advance_allowance'] !== null && $item['trp_advance_allowance'] !== '') ? $item['trp_advance_allowance'] : '---',
                'Travel Start' => !empty($item['trp_start_date']) ? date('d-m-Y', strtotime($item['trp_start_date'])) : '---',
                'Travel End' => !empty($item['trp_end_date']) ? date('d-m-Y', strtotime($item['trp_end_date'])) : '---',
                'Applied Date' => !empty($item['created_at']) ? date('d-m-Y', strtotime($item['created_at'])) : '---',
                'Applied Status' => is_array($item['fh_approval_status'] ?? null) ? ($item['fh_approval_status']['m_name'] ?? '---') : '---',
                'Submitted To' => (is_array($nextApproval) && isset($nextApproval['approver_name']) && $nextApproval['approver_name']) ? $nextApproval['approver_name'] : $approverName,
               
            ];
        });
    }

    public function headings(): array
    {
        return [
            'S. No.',
            'Employee Name',
            'Travel Type',
            'Travel Id',
            'Trip Name',
            'Purpose',
            'Remark',
            'Document',
            'Advance',
            'Travel Start',
            'Travel End',
            'Applied Date',
            'Applied Status',
            'Submitted To',
        ];
    }

  



    public function styles(Worksheet $sheet)
    {
        // Set header rows in rows 1-3
        $sheet->setCellValue('A1', 'TADA Report');
        $sheet->setCellValue('A2', date('d-m-Y'));  
        $sheet->setCellValue('A3', 'Report Type: Travel Report');
        
        // Merge header cells (cover all columns A-N)
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A2', date('d-m-Y'));  
        $sheet->mergeCells('A2:N2'); 
        $sheet->mergeCells('A3:N3');
        
        // Style the header rows
        $sheet->getStyle('A1:N3')->getFont()->setBold(true);
        $sheet->getStyle('A1:N3')->getFont()->setSize(12);  
        $sheet->getStyle('A1:N3')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A1:N3')->getAlignment()->setVertical('center');
        
        // Style the column headers (row 4) across all columns A-N
        $sheet->getStyle('A4:N4')->getFont()->setBold(true);    
        $sheet->getStyle('A4:N4')->getFont()->setSize(11);
        $sheet->getStyle('A4:N4')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A4:N4')->getAlignment()->setVertical('center');
        
        return [];
    }


    public function startCell(): string
    {
        return 'A4'; // Table starts at row 4
    }

    // Column widths are auto-sized via ShouldAutoSize

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Data starts at row 5 (since headers at row 4 and startCell A4)
                $startRow = 5;
                foreach ($this->documentUrls as $index => $url) {
                    $row = $startRow + $index;
                    $cell = 'H' . $row; // Document column
                    if ($url) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getCell($cell)->getHyperlink()->setUrl($url);
                        // Optional styling for hyperlinks
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                        $sheet->getStyle($cell)->getFont()->setUnderline(true);
                    }
                }
            },
        ];
    }
}

