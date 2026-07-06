<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeApprovalMapping;
use App\Models\EmployeeApprovalStatus;
use App\Models\MasterTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class EmployeeApprovalMappingImport implements ToCollection
{
    protected $user;
    protected $moduleId;
    public $errorMessages = [];
    public $errorDetails = [];
    public $summary = ['success' => 0, 'failed' => 0];

    /**
     * @param  mixed  $user  Auth user
     * @param  int  $moduleId  master_table.m_id for MODULE
     */
    public function __construct($user, $moduleId)
    {
        $this->user = $user;
        $this->moduleId = (int) $moduleId;
    }

    public function collection(Collection $rows)
    {
        if ($rows->count() === 0) {
            $this->addError(null, null, 'Empty sheet.');
            return;
        }
    
        $headers = $rows->first()->toArray();
    
        // Normalize headers for consistent comparison
        $normalize = function ($h) {
            if ($h === null) return '';
            $h = str_replace("\xC2\xA0", ' ', (string) $h);
            $h = preg_replace('/\s+/u', ' ', $h);
            return strtolower(trim($h));
        };
    
        $expectedFirstHeader = 'employee code';
        $normalizedUploaded = array_map($normalize, $headers);
        $firstIsHeader = !empty($normalizedUploaded) && $normalizedUploaded[0] === $normalize($expectedFirstHeader);
    
        if ($firstIsHeader) {
            $rows->shift(); // Drop header row
        }
    
        // Build dynamic approver column map when headers exist
        $approverColumnPairs = [];
        if ($firstIsHeader) {
            $numberToCols = [];
            foreach ($normalizedUploaded as $idx => $name) {
                if ($idx === 0) continue; // Skip Employee Code
                if (preg_match('/^approver\s+(\d+)\s+(code|status)$/', $name, $m)) {
                    $num = (int) $m[1];
                    $type = $m[2];
                    if (!isset($numberToCols[$num])) {
                        $numberToCols[$num] = ['code' => null, 'status' => null];
                    }
                    $numberToCols[$num][$type] = $idx;
                }
            }
            ksort($numberToCols);
            foreach ($numberToCols as $num => $pair) {
                // Only include pairs with both code and status columns
                if ($pair['code'] !== null && $pair['status'] !== null) {
                    $approverColumnPairs[] = $pair;
                }
            }
    
            if (empty($approverColumnPairs)) {
                $this->addError(null, null, 'No valid approver code-status pairs found in headers.');
                return;
            }
        }
    
        $businessId = $this->user->emp_b_id;
        $processedRows = [];
    
        // Process each row
        foreach ($rows as $index => $row) {
            $empRaw = trim((string) ($row[0] ?? ''));
            $empCode = $this->extractCode($empRaw);
    
            // Skip empty rows or rows with no employee code
            if ($empCode === '') {
                continue;
            }
    
            // Extract approver pairs (code, status) from the row
            $approvers = [];
            if ($firstIsHeader && !empty($approverColumnPairs)) {
                foreach ($approverColumnPairs as $pair) {
                    $codeIdx = $pair['code'];
                    $statusIdx = $pair['status'];
                    $approverRaw = trim((string) ($row[$codeIdx] ?? ''));
                    $approverCode = $this->extractCode($approverRaw);
                    if ($approverCode === '') {
                        $approverCode = $approverRaw;
                    }
                    $statusCode = isset($row[$statusIdx]) ? trim((string) ($row[$statusIdx] ?? '')) : '';
    
                    // Skip if both approver code and status are empty
                    if ($approverCode === '' || $statusCode === '') {
                        continue;
                    }
    
                    $approvers[] = [
                        'approver' => $approverCode,
                        'status' => $statusCode,
                    ];
                }
            } else {
                // Fallback: assume pairs start at column 1 (code, status)
                for ($i = 1; $i < count($row); $i += 2) {
                    $approverRaw = trim((string) ($row[$i] ?? ''));
                    $approverCode = $this->extractCode($approverRaw);
                    $statusCode = isset($row[$i + 1]) ? trim((string) ($row[$i + 1] ?? '')) : '';
    
                    // Skip if both approver code and status are empty
                    if ($approverCode === '' || $statusCode === '') {
                        continue;
                    }
    
                    $approvers[] = [
                        'approver' => $approverCode,
                        'status' => $statusCode,
                    ];
                }
            }
    
            // Skip rows with no valid approver data
            if (empty($approvers)) {
            //     $this->addError($index + 2, $empCode, 'No valid approver code-status pair found.');
            //     $this->summary['failed']++;
                continue;
            }
    
            $processedRows[] = [
                'empCode' => $empCode,
                'approvers' => $approvers,
                'row' => $index + 2,
            ];
        }
    
        // If no valid rows to process, exit early
        if (empty($processedRows)) {
            // $this->addError(null, null, 'No valid rows with employee and approver data found.');
            // return;
            
        }
    
        DB::beginTransaction();
        try {
            foreach ($processedRows as $rowData) {
                $empCode = $rowData['empCode'];
                $approvers = $rowData['approvers'];
                $rowNumber = $rowData['row'];
    
                // Validate employee
                $employee = Employee::where('emp_b_id', $businessId)
                    ->where('emp_code', $empCode)
                    ->first();
                if (!$employee) {
                    $this->addError($rowNumber, $empCode, 'Employee code not found (' . $empCode . ').');
                    $this->summary['failed']++;
                    continue;
                }
    
                // Validate module
                $module = MasterTable::where('m_group', 'MODULE')
                    ->where('m_id', $this->moduleId)
                    ->first();
                if (!$module) {
                    $this->addError($rowNumber, $empCode, 'Invalid module selected.');
                    $this->summary['failed']++;
                    continue;
                }
    
                // Check for pending approvals
                if ($this->hasPendingApprovals($businessId, $employee->emp_id, $this->moduleId)) {
                    $this->addError($rowNumber, $empCode, 'Cannot update approval mapping - pending approvals exist.');
                    $this->summary['failed']++;
                    continue;
                }
    
                // Check for existing mapping
                $existing = EmployeeApprovalMapping::where('eam_b_id', $businessId)
                    ->where('eam_emp_id', $employee->emp_id)
                    ->where('eam_module_id', $this->moduleId)
                    ->first();
    
                if ($existing) {
                    Log::info('Overwriting existing approval mapping for employee: ' . $empCode . ' in module: ' . $this->moduleId);
                    EmployeeApprovalStatus::where('eas_eam_id', $existing->eam_id)->delete();
                    $existing->delete();
                }
    
                // Create new mapping
                $eam = EmployeeApprovalMapping::create([
                    'eam_b_id' => $businessId,
                    'eam_emp_id' => $employee->emp_id,
                    'eam_module_id' => $this->moduleId,
                ]);
    
                // Process each approver
                foreach ($approvers as $approver) {
                    $manager = Employee::where('emp_b_id', $businessId)
                        ->where('emp_code', $approver['approver'])
                        ->first();
                    if (!$manager) {
                        $manager = Employee::where('emp_b_id', $businessId)
                        ->where('emp_role_id', 1)
                        ->first();
                    }
                    if (!$manager) {
                        $this->addError($rowNumber, $empCode, 'Approver code not found (' . $approver['approver'] . ').');
                        $this->summary['failed']++;
                        continue;
                    }
    
                    $statusId = $this->getStatusId($approver['status']);
                    if ($statusId === null) {
                        $this->addError($rowNumber, $empCode, 'Invalid status code (' . $approver['status'] . ').');
                        $this->summary['failed']++;
                        continue;
                    }
    
                    EmployeeApprovalStatus::create([
                        'eas_eam_id' => $eam->eam_id,
                        'eas_approvel_id' => $manager->emp_id,
                        'eas_approvel_status' => $statusId,
                    ]);
                    $this->summary['success']++;
                }
            }
    
            DB::commit();
        } catch (\Throwable $t) {
            DB::rollBack();
            Log::error('EmployeeApprovalMappingImport failed: ' . $t->getMessage(), [
                'trace' => $t->getTraceAsString(),
            ]);
            $this->addError(null, null, 'Unexpected error: ' . $t->getMessage());
        }
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function getErrorDetails()
    {
        return $this->errorDetails;
    }

    /**
     * Check if there are pending approvals for an employee in a specific module
     */
    private function hasPendingApprovals($businessId, $employeeId, $moduleId)
    {
        // Define module configurations for checking pending approvals
        $modules = [
            // 145 Travel
            145 => [
                'model' => \App\Models\TadaRequestPlan::class,
                'business_column' => 'trp_b_id',
                'stage_column' => 'trp_stage_completed',
                'employee_column' => 'trp_emp_id',
            ],
            // 146 Claim
            146 => [
                'model' => \App\Models\TadaClaim::class,
                'business_column' => 'tc_b_id',
                'stage_column' => 'tc_stage_completed',
                'employee_column' => 'tc_emp_id',
            ],
            // 199 Advance (special: business and employee come from related TadaRequestPlan)
            199 => [
                'model' => \App\Models\AdvanceLog::class,
                'business_column' => null,
                'stage_column' => 'adl_stage_completed',
                'employee_column' => null,
                'special' => 'advance_via_trp',
            ],
            // 229 Mispunch
            229 => [
                'model' => \App\Models\AttendanceException::class,
                'business_column' => 'ae_b_id',
                'stage_column' => 'ae_stage_completed',
                'employee_column' => 'ae_emp_id',
            ],
            // 249 Attendance
            249 => [
                'model' => \App\Models\AttendanceRecord::class,
                'business_column' => 'atd_b_id',
                'stage_column' => 'atd_stage_completed',
                'employee_column' => 'atd_emp_id',
            ],
            // 250 Leave
            250 => [
                'model' => \App\Models\LeaveRequest::class,
                'business_column' => 'lvr_b_id',
                'stage_column' => 'lvr_stage_completed',
                'employee_column' => 'lvr_emp_id',
            ],
            // 339 Gatepass
            339 => [
                'model' => \App\Models\GatePass::class,
                'business_column' => 'gtp_b_id',
                'stage_column' => 'gtp_stage_completed',
                'employee_column' => 'gtp_emp_id',
            ],
            // 442 Loan
            442 => [
                'model' => \App\Models\LoanRequest::class,
                'business_column' => 'lnr_b_id',
                'stage_column' => 'lnr_stage_completed',
                'employee_column' => 'lnr_emp_id',
            ],
            // 562 Ot Approval Status
            562 => [
                'model' => \App\Models\OtApprovalStatus::class,
                'business_column' => 'ot_b_id',
                'stage_column' => 'ot_stage_completed',
                'employee_column' => 'ot_emp_id',
            ],
        ];
        

        if (!isset($modules[$moduleId])) {
            return false; // Unknown module, allow update
        }

        $moduleConfig = $modules[$moduleId];

        // Special handling for Advance: filter via related TadaRequestPlan for business and employee
        if (($moduleConfig['special'] ?? null) === 'advance_via_trp') {
            $query = $moduleConfig['model']::where($moduleConfig['stage_column'], 0)
                ->whereHas('fh_tada_request_plan', function ($q) use ($businessId, $employeeId) {
                    $q->where('trp_b_id', $businessId)
                      ->where('trp_emp_id', $employeeId);
                });
            return $query->exists();
        }

        $query = $moduleConfig['model']::where($moduleConfig['business_column'], $businessId)
            ->where($moduleConfig['employee_column'], $employeeId)
            ->where($moduleConfig['stage_column'], 0); // 0 means incomplete/pending

        return $query->exists();
    }

    /**
     * Get status ID from status code (supports both numeric ID and text)
     */
    private function getStatusId($statusCode)
    {
        $statusId = null;
        
        if (ctype_digit($statusCode)) {
            $statusId = (int) $statusCode;
            $status = MasterTable::where('m_group', 'APPROVAL_STATUS')->where('m_id', $statusId)->first();
            if (!$status) {
                $statusId = null;
            }
        }

        if ($statusId === null) {
            $status = MasterTable::where('m_group', 'APPROVAL_STATUS')
                ->where(function ($q) use ($statusCode) {
                    $q->where('m_name', $statusCode)
                      ->orWhere('m_description', $statusCode);
                })
                ->first();
            if ($status) {
                $statusId = $status->m_id;
            }
        }

        return $statusId;
    }

    /**
     * Extract code from a display string like "CODE - NAME" or return the raw string if no hyphen
     */
    private function extractCode($value)
    {
        $value = trim((string) $value);
        if ($value === '') return '';
        $parts = explode('-', $value, 2);
        return trim($parts[0]);
    }

    private function addError($rowNumber, $employeeCode, $message)
    {
        $prefix = '';
        if ($rowNumber !== null) {
            $prefix .= 'Row ' . $rowNumber . ': ';
        }
        $this->errorMessages[] = $prefix . $message;

        $key = $rowNumber ?? 'general';
        if (!isset($this->errorDetails[$key])) {
            $this->errorDetails[$key] = [
                'row' => $rowNumber,
                'employee_code' => $employeeCode,
                'messages' => [],
            ];
        }
        $this->errorDetails[$key]['messages'][] = $message;
    }
}



