<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\MasterTable;
use App\Models\PolicyLeave;
use App\Models\RuleCriterion;
use App\Models\EmployeeApprovalMapping;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Exports\ErrorExport;

class LeaveRequestImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    public $successfulImports = 0;
    public $failedImports = 0;
    public $errorMessages = [];
    protected $user;
    protected $fileType;
    protected $headersValidated = false;

    protected $expectedHeaders = [
        's. no. *',
        'employee code *',
        'leave category *',
        'leave type *',
        'segment day',
        'from date *',
        'to date *',
    ];

    public function __construct($user, $file)
    {
        $this->user = $user;
        $this->fileType = $file->getClientOriginalExtension();
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $rowData = $row->toArray();

        // Validate headers on the second row
        if ($rowIndex === 2 && !$this->headersValidated) {
            if (!$this->validateHeaders(array_keys($rowData))) {
                throw new \Exception("Header validation failed:\n" . implode("\n", $this->errorMessages));
            }
        }

        try {
            $empCode            = $rowData['employee_code'] ?? null;
            $leaveCategoryName  = $rowData['leave_category'] ?? null;
            $leaveTypeName      = $rowData['leave_type'] ?? null;
            $segmentDayName     = $rowData['segment_day'] ?? null;
            $fromDateRaw        = $rowData['from_date'] ?? null;
            $toDateRaw          = $rowData['to_date'] ?? null;

            $fromDate = $this->parseDate($fromDateRaw);
            $toDate   = $this->parseDate($toDateRaw);

            $errors = [];

            if (!$empCode) {
                $errors[] = "Employee code is missing.";
            }
            if (!$leaveCategoryName) {
                $errors[] = "Leave category name is missing.";
            }
            if (!$leaveTypeName) {
                $errors[] = "Leave type name is missing.";
            }
            if (!$fromDateRaw) {
                $errors[] = "From date is missing.";
            }
            if (!$toDateRaw) {
                $errors[] = "To date is missing.";
            }
            if ($fromDate->gt($toDate)) {
                $errors[] = "Start Date cannot be after End Date.";
            }

            $employee = Employee::where('emp_b_id', $this->user->emp_b_id)
                ->where('emp_code', $empCode)
                ->where('emp_status', 71)
                ->first();

            $leaveCategory = MasterTable::where('m_group', 'LEAVE_CATEGORY')
                ->where('m_name', $leaveCategoryName)
                ->first();

            $leaveType = MasterTable::where('m_group', 'LEAVE_TYPE')
                ->where('m_name', $leaveTypeName)
                ->first();

            if (!$employee || !$leaveCategory || !$leaveType) {
                $errors[] = "Invalid employee or leave category/type.";
            }

            $leaveDaySegment = null;
            if ($leaveType && $leaveType->m_id == 202) {
                if (empty($segmentDayName)) {
                    $errors[] = "Segment day is required for half-day leave.";
                } else {
                    $leaveDaySegment = MasterTable::where('m_group', 'LEAVE_DAY_SEGMENT')
                        ->where('m_name', $segmentDayName)
                        ->first();

                    if (!$leaveDaySegment) {
                        $errors[] = "Invalid segment day.";
                    }
                }
            }

            $totalLeaveDays = $leaveType && $leaveType->m_id == 202 ? 0.5 : $fromDate->diffInDays($toDate) + 1;

            $leavePolicy = PolicyLeave::with('fh_leave_type')
                ->where('pl_id', $employee?->emp_pl_id)
                ->first();

            if (!$leavePolicy) {
                $errors[] = "Leave policy not found.";
            }

            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $this->user->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 250)->where('am_status', 1);
                })
                ->first();

                $processApprovers = [];
                $approvalModuleId = null;

                if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                    $processApprovers = $ruleCriteria->fh_approval_module
                        ->filteredProcessApprovers($employee->emp_d_id)
                        ->get();
                    $approvalModuleId = $ruleCriteria->rc_am_id;
                }

                if (empty($processApprovers)) {
                    $empWiseApprove = EmployeeApprovalMapping::where('eam_emp_id', $employee->emp_id)->where('eam_module_id', 250)->first();
                    if (empty($empWiseApprove)) {
                        $errors[] = "No approval found for employee.";
                    }
                }

            $existingRequest = LeaveRequest::where('lvr_emp_id', $employee?->emp_id)
                ->where('lvr_cat_type_id', $leaveCategory?->m_id)
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('lvr_start_date', [$fromDate, $toDate])
                          ->orWhereBetween('lvr_end_date', [$fromDate, $toDate]);
                })
                ->first();

            if ($leaveType && $leaveType->m_id == 202 && $leaveDaySegment) {
                $duplicateHalfDay = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                    ->where('lvr_cat_type_id', $leaveCategory->m_id)
                    ->where('lvr_start_date', $fromDate)
                    ->where('lvr_day_segment_id', $leaveDaySegment->m_id)
                    ->first();

                if ($duplicateHalfDay) {
                    $errors[] = "Duplicate half-day leave for same date and segment.";
                }
            }

            if ($existingRequest && $leaveType->m_id != 202) {
                $errors[] = "Overlapping leave request exists.";
            }

            if (!empty($errors)) {
                $this->errorMessages[] = "Row {$rowIndex}: " . implode(' ', $errors);
                return;
            }

            if ($existingRequest) {
                $existingRequest->lvr_start_date = min($existingRequest->lvr_start_date, $fromDate);
                $existingRequest->lvr_end_date = max($existingRequest->lvr_end_date, $toDate);
                $existingRequest->lvr_total_leave_days = $fromDate->diffInDays($toDate) + 1;
                $existingRequest->save();
            } else {
                $this->createLeaveRequest(
                    $employee,
                    $leavePolicy,
                    $approvalModuleId,
                    $leaveCategory,
                    $leaveType,
                    $leaveDaySegment,
                    $fromDate,
                    $toDate,
                    $totalLeaveDays
                );
            }

            $this->successfulImports++;
        } catch (Exception $e) {
            $this->errorMessages[] = "Row {$rowIndex}: " . $e->getMessage();
            Log::error("Leave import error on row {$rowIndex}: " . $e->getMessage());
        }
    }

    protected function createLeaveRequest($employee, $leavePolicy, $approvalModuleId, $leaveCategory, $leaveType, $leaveDaySegment, $fromDate, $toDate, $totalLeaveDays)
    {
        $leaveRequest = new LeaveRequest();
        $leaveRequest->lvr_b_id = $employee->emp_b_id;
        $leaveRequest->lvr_emp_id = $employee->emp_id;
        $leaveRequest->lvr_pl_id = $leavePolicy->pl_id;
        $leaveRequest->lvr_reason = "Imported from Excel";
        $leaveRequest->lvr_am_id = $approvalModuleId;
        $leaveRequest->lvr_cat_type_id = $leaveCategory->m_id;
        $leaveRequest->lvr_leave_day_type_id = $leaveType->m_id;

        if ($leaveType->m_id == 202 && $leaveDaySegment) {
            $leaveRequest->lvr_day_segment_id = $leaveDaySegment->m_id;
        }

        $leaveRequest->lvr_start_date = $fromDate;
        $leaveRequest->lvr_end_date = $toDate;

        $leaveWithoutPayData = null;

        if ($leaveCategory->m_id != 215) {
            $leaveBalance = LeaveBalance::where('lb_emp_id', $employee->emp_id)
                ->where('lb_b_id', $employee->emp_b_id)
                ->where('lb_cat_type_id', $leaveCategory->m_id)
                ->orderBy('lb_id', 'desc')
                ->first();

            if ($leaveBalance && $leaveBalance->lb_balance_remaining_leave < $totalLeaveDays) {
                $available = $leaveBalance->lb_balance_remaining_leave;
                $leaveWithoutPay = $totalLeaveDays - $available;

                $firstEndDate = $fromDate->copy()->addDays($available - 1);
                $secondStartDate = $firstEndDate->copy()->addDay();

                $leaveRequest->lvr_end_date = $firstEndDate;
                $leaveRequest->lvr_total_leave_days = $available;

                $leaveWithoutPayData = $leaveRequest->replicate();
                $leaveWithoutPayData->lvr_cat_type_id = 215;
                $leaveWithoutPayData->lvr_start_date = $secondStartDate;
                $leaveWithoutPayData->lvr_end_date = $toDate;
                $leaveWithoutPayData->lvr_total_leave_days = $leaveWithoutPay;
            } else {
                $leaveRequest->lvr_total_leave_days = $totalLeaveDays;
            }
        } else {
            $leaveRequest->lvr_total_leave_days = $totalLeaveDays;
        }

        if ($leaveRequest->save()) {
            if ($leaveWithoutPayData && $leaveWithoutPayData->lvr_total_leave_days > 0) {
                $leaveWithoutPayData->lvr_p_id = $leaveRequest->lvr_id;
                if (!$leaveWithoutPayData->save()) {
                    $leaveRequest->delete();
                    throw new Exception('Leave without pay save failed');
                }
            }

            if ($leaveCategory->m_id != 215) {
                $this->updateLeaveBalance($employee, $leaveCategory->m_id, $totalLeaveDays);
            }
        } else {
            throw new Exception('Failed to save leave request');
        }
    }

    protected function updateLeaveBalance($employee, $catTypeId, $days)
    {
        $leaveBalance = LeaveBalance::where('lb_emp_id', $employee->emp_id)
            ->where('lb_b_id', $employee->emp_b_id)
            ->where('lb_cat_type_id', $catTypeId)
            ->orderBy('lb_id', 'desc')
            ->first();

        if ($leaveBalance) {
            $used = $leaveBalance->lb_taken_leave + $days;
            $balance = $leaveBalance->lb_alloted_leave - $used;

            LeaveBalance::where('lb_id', $leaveBalance->lb_id)->update([
                'lb_taken_leave' => $used,
                'lb_balance_remaining_leave' => $balance,
            ]);
        }
    }

    protected function parseDate($value): Carbon
    {
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value);
        } catch (\Exception $e) {
            return Carbon::parse($value);
        }
    }

    protected function validateHeaders($headers): bool
    {
        $normalizedExpected = $this->normalizeHeaders($this->expectedHeaders);
        $normalizedActual   = $this->normalizeHeaders($headers);

        $this->errorMessages = [];

        foreach ($normalizedExpected as $expected) {
            if (!in_array($expected, $normalizedActual)) {
                throw new \Exception("Invalid column headers. Please match the required format exactly.");
            }
        }

        return empty($this->errorMessages);
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $normalized = strtolower(trim(str_replace(['*', '.', ' '], ['', '', '_'], $header)));
            return rtrim($normalized, '_');
        }, $headers);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }
}
