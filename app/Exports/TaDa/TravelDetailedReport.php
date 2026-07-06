<?php

namespace App\Exports\TaDa;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Helpers\ApprovalHelper;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TravelDetailedReport implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell, WithEvents
{
    protected $data;
    protected $travelTypes;
    protected $travelPurpose;
    protected $documentUrls = [];
    protected $includeTapLocation = false;

    public function __construct($data, $includeTapLocation = '0')
    {
        $this->data = $data;
        $this->includeTapLocation = ($includeTapLocation === '1' || $includeTapLocation === 1 || $includeTapLocation === true);
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

            // Compute approver details only when claim data is present and complete; otherwise keep defaults
            $nextApproval = null;
            $approverName = '---';
            if (is_array($item_claim) && isset($item_claim['tc_id'], $item_claim['tc_b_id'], $item_claim['tc_module_id'], $item_claim['tc_emp_id'])) {
                $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item_claim['tc_id'], $item_claim['tc_b_id']);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $item_claim['tc_module_id'], $item_claim['tc_trp_id'] ?? null, optional($item['fh_employee'])->emp_d_id, $item_claim['tc_emp_id']);

                if (!(is_array($nextApproval) && isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($item_claim['tc_b_id'], $item_claim['tc_emp_id'], $item_claim['tc_module_id']);
                    if ($approvalMapping) {
                        $approvalArray = (array) ApprovalHelper::getApprovalArray($approvalMapping);

                        if (isset($item_claim['fh_approval_log_employee_wise']) && $item_claim['fh_approval_log_employee_wise']) {
                            $approvalLogSource = $item_claim['fh_approval_log_employee_wise'];
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

                            $diff = array_values(array_diff($approvalArray, (array) $approvalLog));
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

            return [
                'S No' => $index + 1,
                'Emp. Code' => $item['fh_employee']['emp_code'] ?? '---',
                'Emp. Name' => $item['fh_employee']['emp_full_name'] ?? '---',
                'Branch' => $item['fh_employee']['fh_branch']['br_name'] ?? '---',
                'Department' => $item['fh_employee']['fh_department']['d_name'] ?? '---',
                'Designation' => $item['fh_employee']['fh_designation']['dg_name'] ?? '---',
                'Dealership' => $item['fh_employee']['fh_dealership']['dlr_name'] ?? '---',
                'Geo Work Location' => isset($item['fh_employee']['emp_is_geowork_active']) ? ($item['fh_employee']['emp_is_geowork_active'] == 1 ? 'Active' : 'Inactive') : '---',
                'Trip Id' => '#' . ($item['trp_unique_id'] ?? '---'),
                'Application Date' => !empty($item_claim['created_at']) ? (date('d-m-Y', strtotime($item_claim['created_at']))) : '---',
                'Application Time' => !empty($item_claim['created_at']) ? (date('H:i', strtotime($item_claim['created_at']))) : '---',
                'Travel Type' => $item['fh_policy_tada_travel_type']['fh_travel_type']['m_name'] ?? '---',
                'Trip Name' => $item['trp_name'] ?? '---',
                'Trip Purpose' => $item['fh_travel_purpose']['tp_name'] ?? '---',
                'Advance Amount' => $item['trp_advance_allowance'] ?? '---',
                'Status' => $item['fh_approval_status']['m_name'] ?? '---',
                'Submitted To' => (is_array($nextApproval) && isset($nextApproval['approver_name']) && $nextApproval['approver_name']) ? $nextApproval['approver_name'] : $approverName,
                'Call Id' => $item['trp_call_id'] ?? '---',
                'Remarks' => $item['trp_remarks'] ?? '---',
                'Document' => $documentUrl ? 'Attached' : 'Not Attached',
                'Travel Start Date' => !empty($item['trp_start_date']) ? (date('d-m-Y', strtotime($item['trp_start_date']))) : '---',
                'Travel End Date' => !empty($item['trp_end_date']) ? (date('d-m-Y', strtotime($item['trp_end_date']))) : '---',
                'Travel Start Time' => !empty($item['trp_start_time']) ? (date('H:i', strtotime($item['trp_start_time']))) : '---',
                'Travel End Time' => !empty($item['trp_end_time']) ? (date('H:i', strtotime($item['trp_end_time']))) : '---',
                'Travel Source' => $this->getTravelSource($item),
                'Travel Destination' => $this->getTravelDestination($item),
                'Travel Taped Location' => $this->includeTapLocation ? $this->getTapLocationString($item['fh_tada_request_details'] ?? []) : 'N/A',
                'Total Distance' => $this->getTotalDistance($item),
                'Claim Status' => isset($item_claim['fh_claim_status']['m_name']) && !empty($item_claim['fh_claim_status']['m_name']) ? 'Yes' : 'No',
                'Destination' => $item['trp_destination'] ?? '---',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'S No',
            'Emp. Code',
            'Emp. Name',
            'Branch',
            'Department',
            'Designation',
            'Dealership',
            'Geo Work Location',
            'Trip Id',
            'Application Date',
            'Application Time',
            'Travel Type',
            'Trip Name',
            'Trip Purpose',
            'Advance Amount',
            'Status',
            'Submitted To',
            'Call Id',
            'Remarks',
            'Document',
            'Travel Start Date',
            'Travel End Date',
            'Travel Start Time',
            'Travel End Time',
            'Travel Source',
            'Travel Destination',
            'Travel Taped Location',
            'Total Distance',
            'Claim Status',
            'Destination',
        ];
    }

    public function styles(Worksheet $sheet)
    {
         // Set header rows in rows 1-3
         $sheet->setCellValue('A1', 'TADA Report');
         $sheet->setCellValue('A2', date('d-m-Y'));  
         $sheet->setCellValue('A3', 'Report Type: Travel Detailed Report');
         
        // Merge header cells (extend to AD to cover all columns)
        $sheet->mergeCells('A1:AD1');
        $sheet->setCellValue('A2', date('d-m-Y'));  
        $sheet->mergeCells('A2:AD2'); 
        $sheet->mergeCells('A3:AD3');
         
        // Style the header rows
        $sheet->getStyle('A1:AD3')->getFont()->setBold(true);
        $sheet->getStyle('A1:AD3')->getFont()->setSize(12);
        $sheet->getStyle('A1:AD3')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A1:AD3')->getAlignment()->setVertical('center');
         
        // Style the column headers (row 4)
        $sheet->getStyle('A4:AD4')->getFont()->setBold(true);    
        $sheet->getStyle('A4:AD4')->getFont()->setSize(11);
        $sheet->getStyle('A4:AD4')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A4:AD4')->getAlignment()->setVertical('center');
        $sheet->getStyle('A4:AD4')->getAlignment()->setWrapText(true);
         return [];
    }   

    public function startCell(): string
    {
        return 'A4'; // Table starts at row 4
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Set explicit widths to prevent oversized columns due to long text
                $columnWidths = [
                    'A' => 6,   // S No
                    'B' => 10,  // Emp. Code
                    'C' => 20,  // Emp. Name
                    'D' => 18,  // Branch
                    'E' => 18,  // Department
                    'F' => 18,  // Designation
                    'G' => 18,  // Dealership
                    'H' => 14,  // Geo Work Location
                    'I' => 12,  // Trip Id
                    'J' => 12,  // Application Date
                    'K' => 10,  // Application Time
                    'L' => 12,  // Travel Type
                    'M' => 22,  // Trip Name
                    'N' => 22,  // Trip Purpose
                    'O' => 12,  // Advance Amount
                    'P' => 12,  // Status
                    'Q' => 22,  // Submitted To
                    'R' => 12,  // Call Id
                    'S' => 30,  // Remarks
                    'T' => 12,  // Document
                    'U' => 12,  // Travel Start Date
                    'V' => 12,  // Travel End Date
                    'W' => 10,  // Travel Start Time
                    'X' => 10,  // Travel End Time
                    'Y' => 12,  // Travel Source
                    'Z' => 15,  // Travel Destination
                    'AA' => 40, // Travel Taped Location
                    'AB' => 12, // Total Distance
                    'AC' => 12, // Claim Status
                    'AD' => 18, // Destination
                ];
                foreach ($columnWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                // Enable wrap text for all data cells to avoid overflow
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A5:AD' . $highestRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A5:AD' . $highestRow)->getAlignment()->setVertical('top');

                // Data starts at row 5 (since headers at row 4 and startCell A4)
                $startRow = 5;
                foreach ($this->documentUrls as $index => $url) {
                    $row = $startRow + $index;
                    $cell = 'T' . $row; // Document column (column T)
                    if ($url) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($url);
                        // Optional styling for hyperlinks
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                        $sheet->getStyle($cell)->getFont()->setUnderline(true);
                    }
                }
            },
        ];
    }

    private function getTravelSource($item)
    {
        $taDetails = collect($item['fh_tada_request_details'] ?? []);
        if ($taDetails->isEmpty()) {
            return '---';
        }
        
        $detail = $taDetails->first();
        return $detail['trd_source'] ?? '---';
    }

    private function getTravelDestination($item)
    {
        $taDetails = collect($item['fh_tada_request_details'] ?? []);
        if ($taDetails->isEmpty()) {
            return '---';
        }
        
        $detail = $taDetails->first();
        return $detail['trd_destination'] ?? '---';
    }

    private function getTotalDistance($item)
    {
        $taDetails = collect($item['fh_tada_request_details'] ?? []);
        if ($taDetails->isEmpty()) {
            return '---';
        }

        // First preference: sum distances from segments across all details (if available)
        $totalFromSegments = 0;
        $hasSegmentDistances = false;

        foreach ($taDetails as $detail) {
            $segmentsRaw = $detail['trd_segments'] ?? '';
            if (!$segmentsRaw) {
                continue;
            }

            // trd_segments might be stored as JSON string or as an array
            $segmentsData = is_string($segmentsRaw) ? json_decode($segmentsRaw, true) : $segmentsRaw;

            if (json_last_error() !== JSON_ERROR_NONE && is_string($segmentsRaw)) {
                $segmentsData = null;
            }

            if (is_array($segmentsData) && !empty($segmentsData)) {
                foreach ($segmentsData as $segment) {
                    $distance = $segment['distance'] ?? null;
                    if ($distance !== null && is_numeric($distance)) {
                        $totalFromSegments += (float) $distance;
                        $hasSegmentDistances = true;
                    }
                }
            }
        }

        if ($hasSegmentDistances) {
            // Return as integer if whole number, else with 2 decimals
            return (fmod($totalFromSegments, 1.0) === 0.0)
                ? (string) number_format($totalFromSegments, 0)
                : (string) number_format($totalFromSegments, 2, '.', '');
        }

        // Fallback: sum stored total distance across details
        $sumStored = 0;
        foreach ($taDetails as $detail) {
            $sumStored += (float) ($detail['trd_total_distance'] ?? 0);
        }

        if ($sumStored > 0) {
            return (fmod($sumStored, 1.0) === 0.0)
                ? (string) number_format($sumStored, 0)
                : (string) number_format($sumStored, 2, '.', '');
        }

        return '---';
    }

    private function getTapLocationString($taDetails)
    {
        $taDetails = collect($taDetails);
        if ($taDetails->isEmpty()) {
            return 'N/A';
        }

        $detail = $taDetails->first();
        
        // Check if we have segments data (formatted location data)
        $segmentsRaw = $detail['trd_segments'] ?? '';
        if ($segmentsRaw) {
            // Parse segments data that may be JSON string or array
            $segmentsData = is_string($segmentsRaw) ? json_decode($segmentsRaw, true) : $segmentsRaw;
            if (json_last_error() !== JSON_ERROR_NONE && is_string($segmentsRaw)) {
                $segmentsData = null;
            }

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
            
            // If parsing fails, return the raw segments data (cast to string when array)
            return is_array($segmentsRaw) ? json_encode($segmentsRaw) : $segmentsRaw;
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

}
