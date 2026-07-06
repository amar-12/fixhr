<?php

namespace App\Exports\TaDa;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
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

class ClaimDetailReport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithMapping, WithCustomStartCell
{
    protected $data;
    protected $travelTypes;
    protected $travelPurpose;
    protected $includeTapLocation;

    public function __construct($data, $includeTapLocation = '0')
    {
        $this->data = $data;
        $this->includeTapLocation = $includeTapLocation === '1';
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
        return 'Claim Detail Report';
    }

    public function collection()
    {
        $reportData = [];
        
        $index = 1;
        foreach ($this->data as $item) {
            $user = Auth::user();
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

                    // Normalize approval log source (can be Collection, array, or JSON string)
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

            // Get DA (Daily Allowance) details
            // $daEligibility is already calculated above from tc_da_calculation_message
            $daClaimed = $item_claim['tc_da_amount'] ?? 0;
            $daDiff = number_format($daEligibility - $daClaimed, 2, '.', '');

            // Get TA (Travel Allowance) details from request details
            $taDetails = collect($item['fh_tada_request_details'] ?? []);
            $taAmount = $taDetails->sum('trd_net_amount') ?? 0;
            $totalDistance = $taDetails->sum('trd_total_distance') ?? 0;
            
            // Get total distance from tapped locations
            $totalTapDistance = $this->getTotalTapDistance($taDetails);
            $tapLocationCount = $this->getTapLocationCount($taDetails);

            // Get expense details
            $expenses = collect($item['fh_tada_expenses'] ?? []);
            $expenseAmount = $expenses->sum('te_amount') ?? 0;


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
                (isset($item['fh_employee']['fh_grade']['g_name']) && $item['fh_employee']['fh_grade']['g_name'] !== '') ? ('' . $item['fh_employee']['fh_grade']['g_name']) : '',
                $item['created_at'] ? date('d-m-Y', strtotime($item['created_at'])) : '',
                $item['trp_unique_id'] ?? '',
                $item['trp_start_date'] ? date('d-m-Y', strtotime($item['trp_start_date'])) : '',
                $item['trp_start_time'] ? date('g:i:s A', strtotime($item['trp_start_time'])) : '',
                $item['trp_end_date'] ? date('d-m-Y', strtotime($item['trp_end_date'])) : '',
                $item['trp_end_time'] ? date('g:i:s A', strtotime($item['trp_end_time'])) : '',
                $item['trp_destination'] ?? 'N/A',
                $item['fh_policy_tada_travel_type']['fh_travel_type']['m_name'] ?? '',
                $this->getTravelPurpose($item['trp_purpose'] ?? null),
                $item['trp_name'] ?? '',
                $item_claim['tc_unique_id'] ?? '',
                $item_claim['created_at'] ? date('d-M-y', strtotime($item_claim['created_at'])) : '',
                $item_claim['created_at'] ? date('H:i', strtotime($item_claim['created_at'])) : '',
                  // Claim Summary Section
                $rawTravelDetailSumAmt,
                $item_claim['tc_da_amount'] ?? 0,
                $item_claim['tc_claimed_amount'] ?? 0, // claimed amount
                $item_claim['tc_amount'] ?? 0, // Total Expense Amount (Inc. travel Allowance, and DA)
                $item['trp_advance_allowance'] ?? 0, // Advance
                $item_claim['tc_deduction_amount'] ?? 0, // Deduction
                $item_claim['tc_amount'] - (($item['trp_advance_allowance'] ?? 0) + ($item_claim['tc_deduction_amount'] ?? 0)), // Net Payable Amount
                // DA Details Section
                $daEligibility, // DA Eligibility
                $daClaimed, // DA Claimedf
                $daDiff, // Diff
                $this->includeTapLocation ? $this->getTapLocationString($taDetails) : 'N/A', // Tap Location Longitude Latitude
                $this->getTravelSource($taDetails),
                $this->getTravelDestination($taDetails),
                // TA Details Section
                $totalTapDistance > 0 ? number_format($totalTapDistance, 0) . 'km' : number_format($totalDistance, 0) . 'km',  // Total Distance (from tap locations)
              
                $this->getTravelMode($taDetails), // Mode
                $item['fh_travel_vehicle']['trd_name'] ?? '',
                $this->getTapDistanceList($taDetails), // Distance from tapped locations
                $this->getClaimType($taDetails), // Claim Type
                $item['created_at'] ? date('d-M-y', strtotime($item['created_at'])) : '', // Applied
                $taAmount, // Amount - ✅ Fixed: Use $taAmount instead of trying to access array directly
                $this->getDocumentRemark($taDetails), // Documents - ✅ Fixed: Use helper method
                $this->getTravelRemarks($taDetails), // Remarks - ✅ Fixed: Use helper method
                // Travel Expense Meal Expense Miscellaneous Expenses Section
                $item['created_at'] ? date('d-M-y', strtotime($item['created_at'])) : '', // Applied On
                $this->getExpenseNames($expenses), // Expense Name
                $this->getDocumentStatus($expenses), // Documents
                $expenseAmount, // Amount (₹)
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
            'Grade',
            'Applied Date',
            'Travel ID',
            'Travel Start date',
            'Travel Start time',
            'Travel End Date',
            'Travel End Time',
            'Travel Destination',
            'Travel Type',
            'Purpose',
            'Trip Name',
            'Claim ID',
            'Claim Date',
            'Claim Time',
             // Claim Summary Section
            'TA',
            'DA',
            'Claimed Amount',
            'Total Expense  Amount (Inc. travel Allowance, and DA)',
            'Advance',
            'Deduction',
            'Net Payable Amount',
            // DA Details Section
            'DA Eligibility',
            'DA Claimed',
            'Difference',
            $this->includeTapLocation ? 'Tap Location Longitude Latitude' : 'Tap Location (Disabled)',
            'Travel Source',
            'Travel Destination',
            // TA Details Section
            'Total Distance',
            'Mode',
            'Vehicle',
            'Distance',
            'Claim Type',
            'Applied',
            'Amount',
            'Documents',    
            'Remarks',
            // Travel Expense Meal Expense Miscellaneous Expenses Section
            'Applied On',
            'Expense Name',
            'Documents',
            'Amount (₹)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set header rows in rows 1-3
        $sheet->setCellValue('A1', 'TADA Report');
        $sheet->setCellValue('A2', date('d-m-Y'));  
        $sheet->setCellValue('A3', 'Report Type: Claim Detail Report');
        
        
        // Add TA Details heading in row 4
        $sheet->setCellValue('AF4', 'TA Details');
        
        // Add Claim Summary heading in row 4
        $sheet->setCellValue('V4', 'Claim Summary');
        
        // Add DA Details heading in row 4
        $sheet->setCellValue('AC4', 'DA Details');
        
        // Add Travel Expense heading in row 4
        $sheet->setCellValue('AR4', 'Travel Expenses');
        
        // Merge header cells
        $sheet->mergeCells('A1:AU1');
        $sheet->setCellValue('A2', date('d-m-Y'));  
        $sheet->mergeCells('A2:AU2'); 
        $sheet->mergeCells('A3:AU3');
        
        
        // Merge Claim Summary heading (TA to Net Payable Amount)
        $sheet->mergeCells('V4:AB4');
        
        // Merge DA Details heading (DA Eligibility to Tap Location or Difference)
        $daDetailsEndColumn = $this->includeTapLocation ? 'AF' : 'AE';
        $sheet->mergeCells('AC4:' . $daDetailsEndColumn . '4');
        
        // Merge TA Details heading (Travel Source to Remarks)
        $taDetailsStartColumn = $this->includeTapLocation ? 'AG' : 'AF';
        $sheet->mergeCells($taDetailsStartColumn . '4:AQ4');
        
        // Merge Travel Expenses heading (Applied On to Amount)
        $sheet->mergeCells('AR4:AU4');
        
        // Style the main title (row 1)
        $sheet->getStyle('A1:AU1')->getFont()->setBold(true);
        $sheet->getStyle('A1:AU1')->getFont()->setSize(16);
        $sheet->getStyle('A1:AU1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:AU1')->getAlignment()->setVertical('center');
        
        // Style the date and report type rows (rows 2-3)
        $sheet->getStyle('A2:AU3')->getFont()->setBold(true);
        $sheet->getStyle('A2:AU3')->getFont()->setSize(12);
        $sheet->getStyle('A2:AU3')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A2:AU3')->getAlignment()->setVertical('center');
        
        
        // Style the Claim Summary heading (row 4)
        $sheet->getStyle('V4:AB4')->getFont()->setBold(true);
        $sheet->getStyle('V4:AB4')->getFont()->setSize(12);
        $sheet->getStyle('V4:AB4')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('V4:AB4')->getAlignment()->setVertical('center');
        $sheet->getStyle('V4:AB4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('V4:AB4')->getFill()->getStartColor()->setRGB('E6F3FF');
        $sheet->getStyle('V4:AB4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Style the DA Details heading (row 4)
        $daDetailsStyleRange = 'AC4:' . $daDetailsEndColumn . '4';
        $sheet->getStyle($daDetailsStyleRange)->getFont()->setBold(true);
        $sheet->getStyle($daDetailsStyleRange)->getFont()->setSize(12);
        $sheet->getStyle($daDetailsStyleRange)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($daDetailsStyleRange)->getAlignment()->setVertical('center');
        $sheet->getStyle($daDetailsStyleRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle($daDetailsStyleRange)->getFill()->getStartColor()->setRGB('E6F3FF');
        $sheet->getStyle($daDetailsStyleRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Style the TA Details heading (row 4)
        $taDetailsStyleRange = $taDetailsStartColumn . '4:AQ4';
        $sheet->getStyle($taDetailsStyleRange)->getFont()->setBold(true);
        $sheet->getStyle($taDetailsStyleRange)->getFont()->setSize(12);
        $sheet->getStyle($taDetailsStyleRange)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($taDetailsStyleRange)->getAlignment()->setVertical('center');
        $sheet->getStyle($taDetailsStyleRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle($taDetailsStyleRange)->getFill()->getStartColor()->setRGB('E6F3FF');
        $sheet->getStyle($taDetailsStyleRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Style the Travel Expenses heading (row 4)
        $sheet->getStyle('AR4:AU4')->getFont()->setBold(true);
        $sheet->getStyle('AR4:AU4')->getFont()->setSize(12);
        $sheet->getStyle('AR4:AU4')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('AR4:AU4')->getAlignment()->setVertical('center');
        $sheet->getStyle('AR4:AU4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('AR4:AU4')->getFill()->getStartColor()->setRGB('E6F3FF');
        $sheet->getStyle('AR4:AU4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Style the column headers (row 5)
        $sheet->getStyle('A5:AU5')->getFont()->setBold(true);    
        $sheet->getStyle('A5:AU5')->getFont()->setSize(11);
        $sheet->getStyle('A5:AU5')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A5:AU5')->getAlignment()->setVertical('center');
        $sheet->getStyle('A5:AU5')->getAlignment()->setWrapText(true);
        
        // Set text wrapping for all data cells
        $sheet->getStyle('A5:AU1000')->getAlignment()->setWrapText(true);
        
        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(25); // Main title row
        $sheet->getRowDimension(2)->setRowHeight(20); // Date row
        $sheet->getRowDimension(3)->setRowHeight(20); // Report type row
        $sheet->getRowDimension(4)->setRowHeight(30); // Section headings row
        $sheet->getRowDimension(5)->setRowHeight(30); // Column headers row

        //
        return [];
    }

    public function startCell(): string
    {
        return 'A5'; // Table starts at row 5
    }
        public function columnWidths(): array
    {
        $columnWidths = [
            'A' => 6,   // S.No.
            'B' => 8,  // Emp. Code
            'C' => 20,  // Emp. Name
            'D' => 20,  // Email ID
            'E' => 15,  // Contact No
            'F' => 14,  // Branch
            'G' => 10,  // Status
            'H' => 15,  // Designation
            'I' => 12,  // Applied Date
            'J' => 12,  // Travel ID
            'K' => 12,  // Travel Start date
            'L' => 12,  // Travel Start time
            'M' => 12,  // Travel End Date
            'N' => 12,  // Travel End Time
            'O' => 15,  // Travel Destination
            'P' => 12,  // Travel Type
            'Q' => 10,  // Purpose
            'R' => 12,  // Trip Name
            'S' => 12,  // Claim ID
            'T' => 12,  // Claim Date
            'U' => 12,  // Claim Time
            // Claim Summary Section - optimized widths
            'V' => 5,  // TA
            'W' => 5,  // DA
            'X' => 8,  // Claimed Amount
            'Y' => 8,  // Total Expense Amount (Inc. travel Allowance, and DA)
            'Z' => 8,  // Advance
            'AA' => 8, // Deduction
            'AB' => 8, // Net Payable Amount
            // DA Details Section - optimized widths
            'AC' => 8, // DA Eligibility
            'AD' => 8, // DA Claimed
            'AE' => 8, // Difference
        ];
        
        // Conditionally add tap location column width
        if ($this->includeTapLocation) {
            $columnWidths['AF'] = 30; // Tap Location Longitude Latitude
        }
        
        // Add remaining columns
        $columnWidths = array_merge($columnWidths, [
            'AG' => 12, // Travel Source
            'AH' => 12, // Travel Destination
            // TA Details Section - optimized widths
            'AI' => 12, // Total Distance
            'AJ' => 10, // Mode
            'AK' => 12, // Vehicle
            'AL' => 12, // Distance
            'AM' => 12, // Claim Type   
            'AN' => 10, // Applied
            'AO' => 8, // Amount
            'AP' => 15, // Documents
            'AQ' => 20, // Remarks
            // Travel Expense Section - optimized widths
            'AR' => 12, // Applied On
            'AS' => 15, // Expense Name
            'AT' => 10, // Documents
            'AU' => 12, // Amount (₹)
        ]);
        
        return $columnWidths;
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
                    $distance = $segment['distance'] ?? '';
                    
                    // Format each segment as: "11:04 am 21.8974 83.3950 (Location Name) - 5.2 KM"
                    if ($time && $latitude && $longitude && $location && $distance) {
                        $formattedSegments[] = sprintf('%s %.4f %.3f (%s) - %s KM', $time, $latitude, $longitude, $location, $distance);
                    } elseif ($latitude && $longitude && $location && $distance) {
                        $formattedSegments[] = sprintf('%.4f %.3f (%s) - %s KM', $latitude, $longitude, $location, $distance);
                    } elseif ($time && $location && $distance) {
                        $formattedSegments[] = sprintf('%s (%s) - %s KM', $time, $location, $distance);
                    } elseif ($location && $distance) {
                        $formattedSegments[] = sprintf('%s - %s KM', $location, $distance);
                    } elseif ($time && $location) {
                        $formattedSegments[] = sprintf('%s (%s)', $time, $location);
                    } elseif ($location) {
                        $formattedSegments[] = $location;
                    }
                }
                
                // Join all segments with Excel line break separator for better readability
                if (!empty($formattedSegments)) {
                    return implode("\n", $formattedSegments);  // Use \n for Excel line breaks
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

    /**
     * Get all distances from tapped locations
     */
    private function getTapDistanceList($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }

        $detail = $taDetails->first();
        
        // Check if we have segments data with distance information
        $segments = $detail['trd_segments'] ?? '';
        if ($segments) {
            $segmentsData = json_decode($segments, true);
            
            if (is_array($segmentsData) && !empty($segmentsData)) {
                $distances = [];
                
                foreach ($segmentsData as $segment) {
                    $distance = $segment['distance'] ?? null;
                    if ($distance && is_numeric($distance)) {
                        $distances[] = number_format($distance, 0) . 'km'; // Remove decimal places and add 'km'
                    }
                }
                
                if (!empty($distances)) {
                    return implode(', ', $distances); // Format: "10km, 10km, 5km"
                }
            }
        }
        
        // Fallback to total distance if segments don't have individual distances
        $totalDistance = $detail['trd_total_distance'] ?? 0;
        if ($totalDistance > 0) {
            return number_format($totalDistance, 0) . 'km'; // Remove decimal places and add 'km'
        }
        
        return 'N/A';
    }

    /**
     * Get detailed distance information with location names
     */
    private function getDetailedDistanceInfo($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }

        $detail = $taDetails->first();
        
        // Check if we have segments data with distance information
        $segments = $detail['trd_segments'] ?? '';
        if ($segments) {
            $segmentsData = json_decode($segments, true);
            
            if (is_array($segmentsData) && !empty($segmentsData)) {
                $distanceDetails = [];
                
                foreach ($segmentsData as $index => $segment) {
                    $location = $segment['location'] ?? 'Location ' . ($index + 1);
                    $distance = $segment['distance'] ?? null;
                    
                    if ($distance && is_numeric($distance)) {
                        $distanceDetails[] = sprintf('%s: %s KM', $location, number_format($distance, 2));
                    }
                }
                
                if (!empty($distanceDetails)) {
                    return implode("\n", $distanceDetails);  // Use \n for Excel line breaks
                }
            }
        }
        
        // Fallback to total distance if segments don't have individual distances
        $totalDistance = $detail['trd_total_distance'] ?? 0;
        if ($totalDistance > 0) {
            return 'Total: ' . number_format($totalDistance, 2) . ' KM';
        }
        
        return 'N/A';
    }

    /**
     * Get total distance from all tapped locations
     */
    private function getTotalTapDistance($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 0;
        }

        $detail = $taDetails->first();
        
        // Check if we have segments data with distance information
        $segments = $detail['trd_segments'] ?? '';
        if ($segments) {
            $segmentsData = json_decode($segments, true);
            
            if (is_array($segmentsData) && !empty($segmentsData)) {
                $totalDistance = 0;
                
                foreach ($segmentsData as $segment) {
                    $distance = $segment['distance'] ?? 0;
                    if (is_numeric($distance)) {
                        $totalDistance += $distance;
                    }
                }
                
                return $totalDistance;
            }
        }
        
        // Fallback to stored total distance
        return $detail['trd_total_distance'] ?? 0;
    }

    /**
     * Get count of tapped locations
     */
    private function getTapLocationCount($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 0;
        }

        $detail = $taDetails->first();
        
        // Check if we have segments data
        $segments = $detail['trd_segments'] ?? '';
        if ($segments) {
            $segmentsData = json_decode($segments, true);
            
            if (is_array($segmentsData)) {
                return count($segmentsData);
            }
        }
        
        return 0;
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
        return $detail['fh_policy_tada_travel_mode']['fh_travel_mode']['m_name'] ?? 'N/A';  
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

    private function getTravelSource($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }
        
        $detail = $taDetails->first();
        return !empty($detail['trd_source']) ? $detail['trd_source'] : 'N/A';
    }


    private function getTravelDestination($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }
        
        $detail = $taDetails->first();
        return !empty($detail['trd_destination']) ? $detail['trd_destination'] : 'N/A';
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
