<?php

namespace App\Exports\TaDa;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Helpers\ApprovalHelper;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use App\Models\Employee;
use App\Models\TadaClaim;


class ClaimBasicReport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell
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
            $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item_claim['tc_id'], $item_claim['tc_b_id']);
            $approval = ApprovalHelper::checkApproval($user->emp_b_id, $item_claim['tc_module_id'], $item_claim['tc_trp_id'], optional($item['fh_employee'])->emp_d_id, $item_claim['tc_emp_id']);

           $approverName = '---';
           $allApproverNames = '';

           if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($item_claim['tc_b_id'], $item_claim['tc_emp_id'], $item_claim['tc_module_id']);
                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);

                        // Normalize approval log source (Collection, array, or JSON string)
                        $approvalLogSource = $item_claim['fh_approval_log_employee_wise'] ?? null;
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

                        $approvalArray = (array) $approvalArray;
                        $approvalLog = (array) $approvalLog;

                        $diff = array_values(array_diff($approvalArray, $approvalLog));
                        if ($diff) {

                            $approverName = Employee::where('emp_id',$diff[0])->pluck('emp_full_name')->first() ??  '---';
                            // if($item->tc_id == 95){
                            //   dd($diff[0],$approverName);
                            // }
                        } else {
                            $approverName = '---';
                        }
                    } else {
                        $approverName = '---';
                    }
                }

            // Build full approver list in sequence
            try {
                // 1) Employee-wise approval flow
                $approvalMapping = ApprovalHelper::getApprovalMapping($item_claim['tc_b_id'], $item_claim['tc_emp_id'], $item_claim['tc_module_id']);
                $approverIdsList = [];
                if ($approvalMapping) {
                    $approverIdsList = ApprovalHelper::getApprovalArray($approvalMapping) ?? [];
                } else {
                    // 2) Hierarchy-wise approval flow
                    $claimModel = TadaClaim::with(['fh_process_approvers.fh_employee', 'fh_employee'])
                        ->where('tc_id', $item_claim['tc_id'])
                        ->first();
                    if ($claimModel) {
                        $approverIdsList = $claimModel->filteredProcessApprovers(optional($claimModel->fh_employee)->emp_d_id)
                            ? $claimModel->filteredProcessApprovers(optional($claimModel->fh_employee)->emp_d_id)->pluck('pa_emp_id')->toArray()
                            : [];
                    }
                }

                if (!empty($approverIdsList)) {
                    $names = [];
                    foreach (array_values($approverIdsList) as $idx => $empId) {
                        $name = Employee::where('emp_id', $empId)->pluck('emp_full_name')->first();
                        $names[] = 'Approver-' . ($idx + 1) . ' ' . ($name ?: '---');
                    }
                    $allApproverNames = implode(', ', $names);
                }
            } catch (\Throwable $e) {
                $allApproverNames = '';
            }

            // dd($item['fh_deduction_log']);
            return [
                'S No' => $index + 1,
                'Emp. Code' => $item['fh_employee']['emp_code'] ?? '',
                'Emp. Name' => $item['fh_employee']['emp_full_name'] ?? '',
                'Designation' => $item['fh_employee']['fh_designation']['dg_name'] ?? '',
                'Travel Type' => $item['fh_policy_tada_travel_type']['fh_travel_type']['m_name'] ?? '',
                'Trip Id' => '#' . $item['trp_unique_id'] ?? '',
                 'Claim Id' => '#' . $item['fh_tada_claim']['tc_unique_id'] ?? '',
                 'Currency' => $item['fh_business']['fh_currency']['c_currency_code'] . ' - ' . $item['fh_business']['fh_currency']['c_currency_symbol'] ?? 'INR',
                 'Claim Amount' => !empty($item['fh_tada_claim']['tc_claimed_amount']) ? $item['fh_tada_claim']['tc_claimed_amount'] : '0',
                'Deduction Amount' => !empty($item['fh_tada_claim']['tc_deduction_amount']) ? $item['fh_tada_claim']['tc_deduction_amount'] : '0',
                // 'Payment Amount' => !empty($item['fh_tada_claim']['tc_payed_amount']) ? $item['fh_tada_claim']['tc_payed_amount'] : '0',
                
                'Payment Amount' => !empty($item['fh_tada_claim']['tc_amount'])
                    ? (
                        $item['fh_tada_claim']['tc_amount']
                        + ($item['fh_tada_claim']['fh_tada_request_plan']['trp_advance_allowance'] ?? 0)
                        - ($item['fh_tada_claim']['tc_deduction_amount'] ?? 0)
                    )
                    : 0,

                'Claim Date' => !empty($item['fh_tada_claim']['created_at']) ? (date('d-m-Y', strtotime($item['fh_tada_claim']['created_at']))) : '',
                'Claim Time' => !empty($item['fh_tada_claim']['created_at']) ? (date('H:i', strtotime($item['fh_tada_claim']['created_at']))) : '',
                'Claim Status' => $item['fh_tada_claim']['fh_claim_status']['m_name'] ?? '',
                'Approval Level' => 'Approval ' . ($approval['approvallog_count'] ?? '0') . ' (' . ($approval['approvalcount'] ?? '0') . ')',
                'Submitted To' => $allApproverNames ?: (isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : ($approverName ?? '---')),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'S No',
            'Emp. Code',
            'Emp. Name',
            'Designation',
            'Travel Type',
            'Trip Id',
            'Claim Id',
            'Currency',
            'Claim Amount',
            'Deduction Amount',
            'Payment Amount',
            'Claim Date',
            'Claim Time',
            'Claim Status',
            'Approval Level',
            'Submitted To',
        ];
    }

    public function styles(Worksheet $sheet)
    {
      // Set header rows in rows 1-3
      $sheet->setCellValue('A1', 'TADA Report');
      $sheet->setCellValue('A2', date('d-m-Y'));  
      $sheet->setCellValue('A3', 'Report Type: Claim Basic Report');
      
      // Merge header cells
      $sheet->mergeCells('A1:P1');
      $sheet->setCellValue('A2', date('d-m-Y'));  
      $sheet->mergeCells('A2:P2'); 
      $sheet->mergeCells('A3:P3');
      
      // Style the header rows
      $sheet->getStyle('A1:P3')->getFont()->setBold(true);
      $sheet->getStyle('A1:P3')->getFont()->setSize(12);
      $sheet->getStyle('A1:P3')->getAlignment()->setHorizontal('left');
      $sheet->getStyle('A1:P3')->getAlignment()->setVertical('center');
      
      // Style the column headers (row 4)
      $sheet->getStyle('A4:P4')->getFont()->setBold(true);    
      $sheet->getStyle('A4:P4')->getFont()->setSize(11);
      $sheet->getStyle('A4:P4')->getAlignment()->setHorizontal('left');
      $sheet->getStyle('A4:P4')->getAlignment()->setVertical('center');
    }

    public function startCell(): string
    {
        return 'A4'; // Table starts at row 4
    }


    
}
