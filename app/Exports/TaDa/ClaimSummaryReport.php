<?php

namespace App\Exports\TaDa;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Helpers\ApprovalHelper;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\TravelPurpose;
use App\Models\TadaRequestDetail;
use App\Models\PolicyTadaCategory;
use App\Models\TadaMetroCity;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ClaimSummaryReport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithMapping, WithCustomStartCell
{
    protected $data;
    protected $travelTypes;
    protected $travelPurpose;

    public function __construct($data)
    {
        $this->data = $data;
        $this->travelTypes = MasterTable::where('m_group', 'TRAVEL_TYPE')
            ->pluck('m_name', 'm_id')
            ->toArray();
        
        $tpIds = collect($this->data)->pluck('trp_purpose')->unique()->filter()->toArray();
        $this->travelPurpose = TravelPurpose::whereIn('tp_id', $tpIds)
            ->pluck('tp_name', 'tp_id')
            ->toArray();
    }

    public function title(): string
    {
        return 'Claim Summary Report';
    }

    public function collection()
    {
        $reportData = [];
        
        $index = 1;
        foreach ($this->data as $item) {
            $user = Auth::user();
    // dd($item['fh_tada_request_details'][0]['trd_remarks']);   
            
            // Debug first item to see data structure
            if ($index === 1) {
                \Log::info('First item employee data:', $this->debugEmployeeData($item['fh_employee'] ?? []));
                \Log::info('First item claim data:', ['claim_data' => $item['fh_tada_claim'] ?? 'No claim data']);
            }
            
            // Check if item_claim exists and is not null
            if (!isset($item['fh_tada_claim']) || !$item['fh_tada_claim']) {
                continue; // Skip items without claims
            }

            $item_claim = $item['fh_tada_claim'];


            // Calculate DA Eligibility from policy-based calculation (same logic as DashboardController)
            $daEligibility = 0;
            
            // First try to get from calculation message if available
            $daCalculationMessage = json_decode($item_claim['tc_da_calculation_message'] ?? '[]', true);
            if (is_array($daCalculationMessage) && !empty($daCalculationMessage)) {
                foreach ($daCalculationMessage as $key => $value) {
                    if (isset($value['amount']) && is_numeric($value['amount'])) {
                        $daEligibility += $value['amount'];
                    }
                }
            }
            
            // If no calculation message, calculate from policy
            if ($daEligibility == 0) {
                $policyCategory = PolicyTadaCategory::where([
                    'ptc_b_id' => $item['fh_employee']['emp_b_id'] ?? null,
                    'ptc_d_id' => $item['fh_employee']['emp_d_id'] ?? null,
                    'ptc_grade_id' => $item['fh_employee']['emp_grade_id'] ?? null
                ])->whereJsonContains('ptc_dg_id', $item['fh_employee']['emp_dg_id'] ?? null)->first();
                
                if ($policyCategory) {
                    $cities = TadaMetroCity::where('ctm_b_id', $item['fh_employee']['emp_b_id'] ?? null)->pluck('ctm_ct_address');
                    $isMetro = $cities->filter(function ($city) use ($item) {
                        return strpos($city, $item['trp_destination'] ?? '') === 0;
                    });
                    
                    if (!empty($isMetro)) {
                        $daEligibility = $policyCategory->fh_policy_tada_daily_allowance_lodgings
                            ->where('ptdal_ct_type_id', 23) // 23 == 'Metro'
                            ->pluck('ptdal_da_eligibility')
                            ->first() ?? 0;
                    } else {
                        $daEligibility = $policyCategory->fh_policy_tada_daily_allowance_lodgings
                            ->where('ptdal_ct_type_id', 24) // 24 == 'Non Metro'
                            ->pluck('ptdal_da_eligibility')
                            ->first() ?? 0;
                    }
                }
                
            
            }
            
          
            // Ensure item_claim is an object/array before proceeding
            if (!is_object($item_claim) && !is_array($item_claim)) {
                continue;
            }

            // Get approval information
            $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item_claim['tc_id'] ?? null, $item_claim['tc_b_id'] ?? null);
            $approval = ApprovalHelper::checkApproval($user->emp_b_id, $item_claim['tc_module_id'] ?? null, $item_claim['tc_trp_id'] ?? null, optional($item['fh_employee'])->emp_d_id ?? null, $item_claim['tc_emp_id'] ?? null);

            $approverName = '---';

            if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                $approvalMapping = ApprovalHelper::getApprovalMapping($item_claim['tc_b_id'] ?? null, $item_claim['tc_emp_id'] ?? null, $item_claim['tc_module_id'] ?? null);
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
                        $approverName = Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ?? '---';
                    }
                }
            }

            // Get expense details by category
            $expenses = collect($item['fh_tada_expenses'] ?? []);
            $expenseAmount = $expenses->sum('te_amount') ?? 0;

            // Calculate expenses by category
            $lodgingAmount = $this->getLodgingAmount($item);
            $mealAmount = $this->getMealAmount($item);
            $miscellaneousAmount = $this->getMiscellaneousAmount($item);
            $travelAmount = $this->getTravelAmount($item);

            // Calculate deviations
            $lodgingDeviation = $this->getDeviationLodging($item);
            $mealDeviation = $this->getDeviationMeal($item);
            $miscellaneousDeviation = $this->getDeviationMiscellaneous($item);
            $travelDeviation = $this->getDeviationTravel($item);
            $totalDeviation = $this->getDeviationTotal($item);

            // Format amounts for display
            $formattedTravelAmount = number_format($travelAmount, 2, '.', '');
            $formattedLodgingAmount = number_format($lodgingAmount, 2, '.', '');
            $formattedMealAmount = number_format($mealAmount, 2, '.', '');
            $formattedMiscellaneousAmount = number_format($miscellaneousAmount, 2, '.', '');
            $formattedLodgingDeviation = number_format($lodgingDeviation, 2, '.', '');
            $formattedMealDeviation = number_format($mealDeviation, 2, '.', '');
            $formattedMiscellaneousDeviation = number_format($miscellaneousDeviation, 2, '.', '');
            $formattedTravelDeviation = number_format($travelDeviation, 2, '.', '');
            $formattedTotalDeviation = number_format($totalDeviation, 2, '.', '');

            $rawTravelTypeLocal = data_get($item, 'fh_policy_tada_travel_type.fh_travel_type.m_id') == 124;

            $rawTravelDetails = data_get($item, 'fh_tada_request_details', []);

            $rawTravelDetailSumAmt = $rawTravelTypeLocal
                ? number_format(collect($rawTravelDetails)->sum('trd_net_amount'), 2, '.', '')
                : number_format(
                    TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function($query) {
                        $query->where('pttv_claim_type_id', 155);
                    })->where('trd_trp_id', $item['trp_id'])
                    ->sum('trd_net_amount'),
                    2,
                    '.',
                    ''
                );
                
               
            // Create main row
            $mainRow = [
                $index,
                $item['fh_employee']['emp_code'] ?? '',
                $item['fh_employee']['emp_full_name'] ?? '',
                $item['fh_employee']['emp_email'] ?? '',
                $item['fh_employee']['emp_contact'] ?? $item['fh_employee']['emp_mobile'] ?? $item['fh_employee']['emp_phone'] ?? '',
                $item['fh_employee']['fh_branch']['br_name'] ?? '', // Branch   
                $item['fh_employee']['fh_employee_status']['m_name'] ?? '', // Status
                $item['fh_employee']['fh_designation']['dg_name'] ?? $item['fh_employee']['designation_name'] ?? $item['fh_employee']['dg_name'] ?? '',
                $item['created_at'] ? date('d-M-Y', strtotime($item['created_at'])) : '',
                $item['trp_unique_id'] ?? '',
                $item['fh_policy_tada_travel_type']['fh_travel_type']['m_name'] ?? '',
                $this->getTravelPurpose($item['trp_purpose'] ?? null),
                $item_claim['tc_unique_id'] ?? '',
                $item_claim['created_at'] ? date('d-M-y', strtotime($item_claim['created_at'])) : '',
                $formattedTravelAmount, // Travel
                $formattedTravelDeviation, // Deviation Travel
                $formattedLodgingAmount, // Lodging
                $formattedLodgingDeviation, // Deviation Lodging
                $formattedMealAmount, // Meal
                $formattedMealDeviation, // Deviation Meal
                $formattedMiscellaneousAmount, // Miscellaneous Expenses
                $formattedMiscellaneousDeviation, // Deviation Miscellaneous Expenses
                $item_claim['tc_amount'] ?? 0, // sub-total
                $item['trp_advance_allowance'] ?? 0, // Advance
                $item_claim['tc_deduction_amount'] ?? 0, // Deduction
                $item_claim['tc_amount'] - (($item['trp_advance_allowance'] ?? 0) + ($item_claim['tc_deduction_amount'] ?? 0)) ?? 0, // Net Payable Amount
                $this->getClaimStatus($item_claim) ?? '---', // Claim Status
                // $this->getApproverName($item_claim) ?? '---', // Approver Name
                isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : ($approverName ?? '---'),
                $this->getDeductionAcceptance($item_claim) ?? '---', // Deduction Acceptance
                $this->getAcceptanceDate($item_claim) ?? '---', // Acceptance Date
                $this->getAcceptanceTime($item_claim) ?? '---', // Acceptance Time
                $this->getAcceptanceStatus($item_claim) ?? '---', // Acceptance
                $this->getFinanceStatus($item_claim) ?? '---', // Finance Status
                $this->getFinanceApproverName($item_claim) ?? '---', // Finance Approver Name
                $this->getFinanceRemark($item_claim) ?? '---', // Finance Remark
                $this->getFinanceApprovalDateTime($item_claim) ?? '---', // Finance Approval Date & Time
            ];

            $reportData[] = $mainRow;
            $index++;

          
        }

        return collect($reportData);
    }

    public function map($row): array
    {
        return $row;
    }

    public function headings(): array
    {
        return [
            'S.No.',
            'Emp. Code',
            'Emp. Name',
            'Email ID',
            'Contact No',
            'Branch',
            'Status',
            'Designation',
            'Applied Date',
            'Travel ID',
            'Travel Type',
            'Purpose',
            'Claim ID',
            'Claim Date',
            'Travel',
            'Deviation',
            'Lodging',
            'Deviation',
            'Meal',
            'Deviation',
            "Miscellaneous Expenses",
            'Deviation',
            "Subtotal Total \nExpense Amount \n(Inc. Taxes), Travel Allowance, and DA)\nafter Deduction of Deviation.",
            'Advance',
            'Deduction',
            'Net Payable Amount',
            'Claim Status',
            'Approver Name',
            'Deduction Acceptance',
            'Acceptance Date',
            'Acceptance Time',
            'Acceptance',
            'Finance Status',
            'Finance Approver Name',
            'Finance Remark',
            'Finance Approval Date & Time',
        ];
    }

    public function styles(Worksheet $sheet)
    {
  // Set header rows in rows 1-3
  $sheet->setCellValue('A1', 'TADA Report');
  $sheet->setCellValue('A2', date('d-m-Y'));  
  $sheet->setCellValue('A3', 'Report Type: Claim Summary Report');
  
  // Merge header cells
  $sheet->mergeCells('A1:AG1');
  $sheet->setCellValue('A2', date('d-m-Y'));  
  $sheet->mergeCells('A2:AG2'); 
  $sheet->mergeCells('A3:AG3');
  
  // Style the header rows
  $sheet->getStyle('A1:AG3')->getFont()->setBold(true);
  $sheet->getStyle('A1:AG3')->getFont()->setSize(12);
  $sheet->getStyle('A1:AG3')->getAlignment()->setHorizontal('left');
  $sheet->getStyle('A1:AG3')->getAlignment()->setVertical('center');
  
  // Style the column headers (row 4)
  $sheet->getStyle('A4:AJ4')->getFont()->setBold(true);    
  $sheet->getStyle('A4:AJ4')->getFont()->setSize(11);
  $sheet->getStyle('A4:AJ4')->getAlignment()->setHorizontal('left');
  $sheet->getStyle('A4:AJ4')->getAlignment()->setVertical('center');
  $sheet->getStyle('A4:AJ4')->getAlignment()->setWrapText(true);
        return [];
    }

    public function startCell(): string
    {
        return 'A4'; // Table starts at row 4
    }

        public function columnWidths(): array
    {
        return [
            'A' => 8,   // S.No.
            'B' => 12,  // Emp. Code
            'C' => 20,  // Emp. Name
            'D' => 25,  // Email ID
            'E' => 15,  // Contact No
            'F' => 15,  // Branch
            'G' => 12,  // Status
            'H' => 20,  // Designation
            'I' => 15,  // Applied Date
            'J' => 15,  // Travel ID
            'K' => 15,  // Travel Type
            'L' => 20,  // Purpose
            'M' => 15,  // Claim ID
            'N' => 15,  // Claim Date
            'O' => 15,  // Travel
            'P' => 20,  // Deviation Lodging
            'Q' => 20,  // Deviation Meal
            'R' => 30,  // Deviation Miscellaneous Expenses
            'S' => 15,  // Deviation
            'T' => 8,   // Empty column
            'U' => 15,  // Advance
            'V' => 15,  // Deduction
            'W' => 15,  // Amount
            'X' => 18,  // Claim Status
            'Y' => 25,  // Approver Name
            'Z' => 20,  // Deduction Acceptance
            'AA' => 18, // Acceptance Date
            'AB' => 18, // Acceptance Time
            'AC' => 15, // Acceptance
            'AD' => 18, // Finance Status
            'AE' => 25, // Finance Approver Name
            'AF' => 20, // Finance Remark
            'AG' => 30, // Finance Approval Date & Time
        ];
    }

    private function getTapLocationString($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }

        $detail = $taDetails->first();
        
        // Check if we have segments data (formatted location data)
        $segments = $detail['trd_segments'] ?? '';
        if ($segments) {
            // Parse the JSON segments data
            $segmentsData = json_decode($segments, true);
            
            if (is_array($segmentsData) && !empty($segmentsData)) {
                $formattedSegments = [];
                
                // Process all segments and format them
                foreach ($segmentsData as $segment) {
                    $time = $segment['time'] ?? '';
                    $latitude = $segment['latitude'] ?? '';
                    $longitude = $segment['longitude'] ?? '';
                    $location = $segment['location'] ?? '';
                    
                    // Format each segment as: "11:04 am 21.8974 83.3950 (Location Name)"
                    if ($time && $latitude && $longitude && $location) {
                        $formattedSegments[] = sprintf('%s %.4f %.3f (%s)', $time, $latitude, $longitude, $location);
                    } elseif ($latitude && $longitude && $location) {
                        $formattedSegments[] = sprintf('%.4f %.3f (%s)', $latitude, $longitude, $location);
                    } elseif ($time && $location) {
                        $formattedSegments[] = sprintf('%s (%s)', $time, $location);
                    } elseif ($location) {
                        $formattedSegments[] = $location;
                    }
                }
                
                // Join all segments with Excel line break separator for better readability
                if (!empty($formattedSegments)) {
                    return implode("\r\n", $formattedSegments);
                }
            }
            
            // If JSON parsing fails, return the raw segments data
            return $segments;
        }
        
        // Fallback to old format if available
        $time = isset($detail['trd_start_time']) ? date('g:i A', strtotime($detail['trd_start_time'])) : '';
        $location = $detail['trd_destination'] ?? '';
        
        if ($time && $location) {
            return $time . ' ' . $location;
        } elseif ($location) {
            return $location;
        }
        
        return 'N/A';
    }

    private function getTravelRemarks($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }
        
        $remarks = $taDetails->pluck('trd_remarks')->filter()->unique()->implode(', ');
        return $remarks ?: 'N/A';
    }

    private function getTravelMode($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }
        
        $detail = $taDetails->first();
        return $detail['fh_policy_tada_travel_mode']['pttm_name'] ?? 'By Road';
    }

    private function getTravelVehicle($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }
        
        $detail = $taDetails->first();
        return $detail['fh_policy_tada_travel_vehicle']['fh_vehicle']['m_name'] ?? 'Bike';
    }

    private function getClaimType($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }
        
        $detail = $taDetails->first();
        return $detail['fh_policy_tada_travel_vehicle']['fh_claim_type']['m_name'] ?? 'Policy';
    }

    private function getDocumentRemark($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }
        
        $detail = $taDetails->first();
        return !empty($detail['trd_documents']) ? 'Attached' : 'Not Attached';
    }

    private function getExpenseNames($expenses)
    {
        $expenses = collect($expenses);
        if ($expenses->isEmpty()) {
            return 'N/A';
        }
        
        $names = $expenses->map(function($expense) {
            return '(' . ($expense['fh_expense_type']['m_name'] ?? 'Unknown') . ')';
        })->implode(', ');
        
        return $names;
    }

    private function getDocumentStatus($expenses)
    {
        $expenses = collect($expenses);
        if ($expenses->isEmpty()) {
            return 'N/A';
        }
        
        $hasDocuments = $expenses->some(function($expense) {
            return !empty($expense['te_document']);
        });
        
        return $hasDocuments ? 'Attached' : 'Not Attached';
    }

    private function getTravelPurpose($purposeId)
    {
        return $this->travelPurpose[$purposeId] ?? 'N/A';
    }

    // New helper methods for the updated columns
    private function getDeviationLodging($item)
    {
        // Get deviation lodging amount from expenses
        $expenses = collect($item['fh_tada_expenses'] ?? []);
        return $expenses->where('te_type_id', 158)->sum('te_deviation') ?? 0; // 158 = Lodging Expense
    }

    private function getDeviationMeal($item)
    {
        // Get deviation meal amount from expenses
        $expenses = collect($item['fh_tada_expenses'] ?? []);
        return $expenses->where('te_type_id', 160)->sum('te_deviation') ?? 0; // 160 = Meal Expense
    }

    private function getDeviationMiscellaneous($item)
    {
        // Get deviation miscellaneous expenses amount
        $expenses = collect($item['fh_tada_expenses'] ?? []);
        return $expenses->where('te_type_id', 161)->sum('te_deviation') ?? 0; // 161 = Other/Miscellaneous Expense
    }

    private function getDeviationTravel($item)
    {
        // Get deviation travel expenses amount
        $expenses = collect($item['fh_tada_expenses'] ?? []);
        return $expenses->where('te_type_id', 159)->sum('te_deviation') ?? 0; // 159 = Travel Expense
    }

    private function getDeviationTotal($item)
    {
        // Calculate total deviation
        return $this->getDeviationLodging($item) + $this->getDeviationMeal($item) + $this->getDeviationMiscellaneous($item) + $this->getDeviationTravel($item);
    }

    /**
     * Get lodging expense amount
     */
    private function getLodgingAmount($item)
    {
        $expenses = collect($item['fh_tada_expenses'] ?? []);
        return $expenses->where('te_type_id', 158)->sum('te_amount') ?? 0; // 158 = Lodging Expense
    }

    /**
     * Get meal expense amount
     */
    private function getMealAmount($item)
    {
        $expenses = collect($item['fh_tada_expenses'] ?? []);
        return $expenses->where('te_type_id', 160)->sum('te_amount') ?? 0; // 160 = Meal Expense
    }

    

    /**
     * Get miscellaneous expense amount
     */
    private function getMiscellaneousAmount($item)
    {
        $expenses = collect($item['fh_tada_expenses'] ?? []);
        return $expenses->where('te_type_id', 161)->sum('te_amount') ?? 0; // 161 = Other/Miscellaneous Expense
    }

    /**
     * Get travel allowance amount
     */
    private function getTravelAmount($item)
    {

        $expenses = collect($item['fh_tada_expenses'] ?? []);
        return $expenses->where('te_type_id', 159)->sum('te_amount') + $expenses->sum('te_taxes') ?? 0; // 160 = Meal Expense



        // $rawTravelTypeLocal = data_get($item, 'fh_policy_tada_travel_type.fh_travel_type.m_id') == 124;
        // $rawTravelDetails = data_get($item, 'fh_tada_request_details', []);

        // if ($rawTravelTypeLocal) {
        //     return collect($rawTravelDetails)->sum('trd_net_amount') ?? 0;
        // } else {
        //     return TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function($query) {
        //         $query->where('pttv_claim_type_id', 155);
        //     })->where('trd_trp_id', $item['trp_id'])
        //     ->sum('trd_net_amount') ?? 0;
        // }
    }

    /**
     * Get total expense amount including all categories
     */
    private function getTotalExpenseAmount($item)
    {
        $travelAmount = $this->getTravelAmount($item);
        $lodgingAmount = $this->getLodgingAmount($item);
        $mealAmount = $this->getMealAmount($item);
        $miscellaneousAmount = $this->getMiscellaneousAmount($item);
        
        return $travelAmount + $lodgingAmount + $mealAmount + $miscellaneousAmount;
    }

    /**
     * Get subtotal after deviation deduction
     */
    private function getSubtotalAfterDeviation($item)
    {
        $totalExpense = $this->getTotalExpenseAmount($item);
        $totalDeviation = $this->getDeviationTotal($item);
        
        return $totalExpense - $totalDeviation;
    }

    private function getClaimStatus($item_claim)
    {
        // Get claim status from approval status
        if (isset($item_claim['fh_claim_status']['m_name'])) {
            return $item_claim['fh_claim_status']['m_name'];
        }
        return '---';
    }

    private function getApproverName($item_claim)
    {
        // Get approver name from approval log
        if (isset($item_claim['fh_approval_log_employee_wise']) && $item_claim['fh_approval_log_employee_wise']) {
            $source = $item_claim['fh_approval_log_employee_wise'];
            if (is_string($source)) {
                $decoded = json_decode($source, true);
                $source = is_array($decoded) ? $decoded : [];
            }

            if ($source instanceof \Illuminate\Support\Collection) {
                $latestApproval = $source->where('log_status', 'approved')->sortByDesc('created_at')->first();
            } else {
                $collection = collect(is_array($source) ? $source : []);
                $latestApproval = $collection->filter(function ($row) {
                    return (is_array($row) && ($row['log_status'] ?? null) === 'approved') || (is_object($row) && ($row->log_status ?? null) === 'approved');
                })->sortByDesc(function ($row) {
                    return is_array($row) ? ($row['created_at'] ?? null) : ($row->created_at ?? null);
                })->first();
            }

            if ($latestApproval) {
                $logUserId = is_array($latestApproval) ? ($latestApproval['log_user_id'] ?? null) : ($latestApproval->log_user_id ?? null);
                if ($logUserId) {
                    $employee = Employee::where('emp_id', $logUserId)->first();
                    if ($employee) {
                        return $employee->emp_full_name . ' (' . $employee->emp_code . ')';
                    }
                }
            }
        }
        return '---';
    }

    private function getDeductionAcceptance($item_claim)
    {
        // Check if admin entered any deduction amount
        if (!isset($item_claim['tc_deduction_amount']) || $item_claim['tc_deduction_amount'] <= 0) {
            return 'Not Applicable';
        }

        // Check if there's a deduction log to determine user's response
        if (isset($item_claim['fh_deductionLog']) && $item_claim['fh_deductionLog']) {
            $deductionLog = $item_claim['fh_deductionLog'];
            
            // Check if user has responded to the deduction
            if (isset($deductionLog['dlog_requester_action'])) {
                if ($deductionLog['dlog_requester_action'] == 1) {
                    return 'Accepted';
                } else {
                    return 'Declined';
                }
            }
            
            // If no response yet, show pending
            return 'Pending';
        }

        // If deduction amount exists but no log, show pending
        return 'Pending';
    }

    private function getAcceptanceDate($item_claim)
    {
        // Check if there's a deduction log to get acceptance date
        if (isset($item_claim['fh_deductionLog']) && $item_claim['fh_deductionLog']) {
            $deductionLog = $item_claim['fh_deductionLog'];
            
            // Check if user has responded to the deduction
            if (isset($deductionLog['dlog_requester_action'])) {
                return date('d-M-y', strtotime($deductionLog['updated_at'] ?? $deductionLog['created_at']));
            }
        }

        // If no deduction log or no response, check approval log
        if (isset($item_claim['fh_approval_log_employee_wise']) && $item_claim['fh_approval_log_employee_wise']) {
            $source = $item_claim['fh_approval_log_employee_wise'];
            if (is_string($source)) {
                $decoded = json_decode($source, true);
                $source = is_array($decoded) ? $decoded : [];
            }

            if ($source instanceof \Illuminate\Support\Collection) {
                $latestApproval = $source->where('log_status', 'approved')->sortByDesc('created_at')->first();
                if ($latestApproval) {
                    return date('d-M-y', strtotime($latestApproval->created_at));
                }
            } else {
                $collection = collect(is_array($source) ? $source : []);
                $latestApproval = $collection->filter(function ($row) {
                    return (is_array($row) && ($row['log_status'] ?? null) === 'approved') || (is_object($row) && ($row->log_status ?? null) === 'approved');
                })->sortByDesc(function ($row) {
                    return is_array($row) ? ($row['created_at'] ?? null) : ($row->created_at ?? null);
                })->first();
                if ($latestApproval) {
                    $createdAt = is_array($latestApproval) ? ($latestApproval['created_at'] ?? null) : ($latestApproval->created_at ?? null);
                    if ($createdAt) {
                        return date('d-M-y', strtotime($createdAt));
                    }
                }
            }
        }
        
        return '---';
    }

    private function getAcceptanceTime($item_claim)
    {
        // Check if there's a deduction log to get acceptance time
        if (isset($item_claim['fh_deductionLog']) && $item_claim['fh_deductionLog']) {
            $deductionLog = $item_claim['fh_deductionLog'];
            
            // Check if user has responded to the deduction
            if (isset($deductionLog['dlog_requester_action'])) {
                return date('H:i', strtotime($deductionLog['updated_at'] ?? $deductionLog['created_at']));
            }
        }

        // If no deduction log or no response, check approval log
        if (isset($item_claim['fh_approval_log_employee_wise']) && $item_claim['fh_approval_log_employee_wise']) {
            $source = $item_claim['fh_approval_log_employee_wise'];
            if (is_string($source)) {
                $decoded = json_decode($source, true);
                $source = is_array($decoded) ? $decoded : [];
            }

            if ($source instanceof \Illuminate\Support\Collection) {
                $latestApproval = $source->where('log_status', 'approved')->sortByDesc('created_at')->first();
                if ($latestApproval) {
                    return date('H:i', strtotime($latestApproval->created_at));
                }
            } else {
                $collection = collect(is_array($source) ? $source : []);
                $latestApproval = $collection->filter(function ($row) {
                    return (is_array($row) && ($row['log_status'] ?? null) === 'approved') || (is_object($row) && ($row->log_status ?? null) === 'approved');
                })->sortByDesc(function ($row) {
                    return is_array($row) ? ($row['created_at'] ?? null) : ($row->created_at ?? null);
                })->first();
                if ($latestApproval) {
                    $createdAt = is_array($latestApproval) ? ($latestApproval['created_at'] ?? null) : ($latestApproval->created_at ?? null);
                    if ($createdAt) {
                        return date('H:i', strtotime($createdAt));
                    }
                }
            }
        }
        
        return '---';
    }

    private function getAcceptanceStatus($item_claim)
    {
        // Check if there's a deduction log to get acceptance status
        if (isset($item_claim['fh_deductionLog']) && $item_claim['fh_deductionLog']) {
            $deductionLog = $item_claim['fh_deductionLog'];
            
            // Check if user has responded to the deduction
            if (isset($deductionLog['dlog_requester_action'])) {
                if ($deductionLog['dlog_requester_action'] == 1) {
                    return 'Accepted';
                } else {
                    return 'Declined';
                }
            }
            
            // If no response yet, show pending
            return 'Pending';
        }

        // If no deduction log, check approval log
        if (isset($item_claim['fh_approval_log_employee_wise']) && $item_claim['fh_approval_log_employee_wise']) {
            $source = $item_claim['fh_approval_log_employee_wise'];
            if (is_string($source)) {
                $decoded = json_decode($source, true);
                $source = is_array($decoded) ? $decoded : [];
            }

            if ($source instanceof \Illuminate\Support\Collection) {
                $latestApproval = $source->where('log_status', 'approved')->sortByDesc('created_at')->first();
                if ($latestApproval) {
                    return 'Approved';
                }
            } else {
                $collection = collect(is_array($source) ? $source : []);
                $latestApproval = $collection->first(function ($row) {
                    return (is_array($row) && ($row['log_status'] ?? null) === 'approved') || (is_object($row) && ($row->log_status ?? null) === 'approved');
                });
                if ($latestApproval) {
                    return 'Approved';
                }
            }
        }
        
        return '---';
    }

    private function getFinanceStatus($item_claim)
    { 
        // Check if there's a claim approval log (matches Blade: fh_claim_approval_log)
        if (isset($item_claim['fh_claim_approval_log']) && $item_claim['fh_claim_approval_log']) {
            $financeLogs = collect($item_claim['fh_claim_approval_log']);
            
            // Get the latest log entry (matches Blade: sortBy('log_id'))
            $financeLog = $financeLogs->sortBy('log_id')->last();
            
            if ($financeLog && isset($financeLog['fh_status']['m_name'])) {
                return $financeLog['fh_status']['m_name'] . ' By';
            }
        }
        
        return '---';
    }

    private function getFinanceApproverName($item_claim)
    {
        // Check if there's a claim approval log (matches Blade: fh_claim_approval_log)
        if (isset($item_claim['fh_claim_approval_log']) && $item_claim['fh_claim_approval_log']) {
            $financeLogs = collect($item_claim['fh_claim_approval_log']);
            
            // Get the latest log entry (matches Blade: sortBy('log_id'))
            $financeLog = $financeLogs->sortBy('log_id')->last();
            
            if ($financeLog && isset($financeLog['fh_employee']['emp_full_name'])) {
                return $financeLog['fh_employee']['emp_full_name'];
            }
        }
        
        return '---';
    }

    private function getFinanceRemark($item_claim)
    {
        // Check if there's a claim approval log (matches Blade: fh_claim_approval_log)
        if (isset($item_claim['fh_claim_approval_log']) && $item_claim['fh_claim_approval_log']) {
            $financeLogs = collect($item_claim['fh_claim_approval_log']);
            
            // Get the latest log entry (matches Blade: sortBy('log_id'))
            $financeLog = $financeLogs->sortBy('log_id')->last();
            
            if ($financeLog && isset($financeLog['log_description'])) {
                return $financeLog['log_description'];
            }
        }
        
        return '---';
    }

    private function getFinanceApprovalDateTime($item_claim)
    {
        // Check if there's a claim approval log (matches Blade: fh_claim_approval_log)
        if (isset($item_claim['fh_claim_approval_log']) && $item_claim['fh_claim_approval_log']) {
            $financeLogs = collect($item_claim['fh_claim_approval_log']);
            
            // Get the latest log entry (matches Blade: sortBy('log_id'))
            $financeLog = $financeLogs->sortBy('log_id')->last();
            
            if ($financeLog && isset($financeLog['created_at'])) {
                // Match Blade format exactly: 'd-M-Y h:i A'
                return date('d-M-Y h:i A', strtotime($financeLog['created_at']));
            }
        }
        
        return '---';
    }

    private function debugEmployeeData($employee)
    {
        if (empty($employee)) {
            return 'No employee data';
        }
        
        $keys = array_keys($employee);
        $branchKeys = [];
        $contactKeys = [];
        
        foreach ($keys as $key) {
            if (strpos($key, 'branch') !== false || strpos($key, 'br_') !== false) {
                $branchKeys[] = $key;
            }
            if (strpos($key, 'contact') !== false || strpos($key, 'mobile') !== false || strpos($key, 'phone') !== false) {
                $contactKeys[] = $key;
            }
        }
        
        return [
            'all_keys' => $keys,
            'branch_keys' => $branchKeys,
            'contact_keys' => $contactKeys,
            'branch_data' => isset($employee['fh_branch']) ? $employee['fh_branch'] : 'No fh_branch',
            'contact_data' => [
                'emp_contact' => $employee['emp_contact'] ?? 'Not found',
                'emp_mobile' => $employee['emp_mobile'] ?? 'Not found',
                'emp_phone' => $employee['emp_phone'] ?? 'Not found'
            ]
        ];
    }
}
