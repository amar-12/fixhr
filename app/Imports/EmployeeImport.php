<?php
namespace App\Imports;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\MasterTable;
use App\Models\PlanPriceSlab;
use App\Models\PolicyAttendance;
use App\Models\PolicyLeave;
use App\Models\PolicyShiftTiming;
use App\Models\Role;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmployeeImport implements ToCollection, WithChunkReading
{
    public array $failures      = [];
    public array $errorMessages = [];

    protected int $businessId;

    // Preloaded caches
    protected array $branches           = [];
    protected array $departments        = [];
    protected array $designations       = [];
    protected array $grades             = [];
    protected array $roles              = [];
    protected array $shifts             = [];
    protected array $leavePolicies      = [];
    protected array $attendancePolicies = [];
    protected array $employeeCodeToId   = [];
    protected array $weekOffNameToId    = [];
    protected array $masterCache        = [];

    public function __construct()
    {
        if (! Auth::check()) {
            throw new \Exception('User not authenticated');
        }
        $this->businessId = Auth::user()->emp_b_id;
    }
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return; 
        }

        $user  = Auth::user();
        $bId   = $user->emp_b_id;

        // Fetch subscription and max employees
        $subscription = Subscription::where('business_id', $bId)->first();
        $limitMessage = 'Employee limit exceeded. Please upgrade your plan to add more employees.';

        if ($subscription) {
            $slabData = PlanPriceSlab::find($subscription->price_slab_id);
            $limit = ($slabData && $slabData->max_employees) ? (int)$slabData->max_employees : 0;
        } else {
            $limit = 0; // No subscription → cannot add employees
        }

        $currentCount = Employee::where('emp_b_id', $bId)->count();
        $remaining    = $limit - $currentCount;

        if ($remaining <= 0) {
            throw new \Exception($limitMessage);
        }

        $this->failures      = [];
        $this->errorMessages = [];
        $addedCount          = 0;

        $this->preloadAllReferenceData();

        // Remove header row
        $rows->shift();

        foreach ($rows as $index => $row) {
            $excelRowNumber = $index + 3; // 2 header + 1-based index
            $rowArray       = $row->toArray();

            // Skip completely empty rows
            if (empty(array_filter($rowArray, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            DB::beginTransaction();

            try {
                if ($addedCount >= $remaining) {
                    throw new \Exception('EMPLOYEE_LIMIT_REACHED');
                }

                $val  = fn($idx) => $rowArray[$idx] ?? null;
                $trim = fn($v)   => is_string($v) ? trim($v) : $v;

                // Extract and trim values
                $employeeCode     = $trim($val(1));
                $prefix           = $trim($val(2));
                $firstName        = $trim($val(3));
                $middleName       = $trim($val(4));
                $lastName         = $trim($val(5));
                $contactNumber    = $trim($val(6));
                $email            = $trim($val(7));
                $dob              = $val(8);
                $gender           = $trim($val(9));
                $maritalStatus    = $trim($val(10));
                $bloodGroup       = $trim($val(11));
                $permanentAddress = $trim($val(12));
                $permanentPin     = $trim($val(13));
                $temporaryAddress = $trim($val(14));
                $temporaryPin     = $trim($val(15));
                $branchName       = $trim($val(16));
                $deptName         = $trim($val(17));
                $designationName  = $trim($val(18));
                $gradeName        = $trim($val(19));
                $roleName         = $trim($val(20));
                $managerCode      = $trim($val(21));
                $budgetCode       = $trim($val(22));
                $assignPolicy     = $trim($val(23));
                $checkInMethod    = $trim($val(24));
                $shiftName        = $trim($val(25));
                $assignMode       = $trim($val(26));
                $geofencing       = $trim($val(27));
                $weekoffName      = $trim($val(28));
                $preference       = $trim($val(29));
                $leavePolicyName  = $trim($val(30));
                $leaveProrata     = $trim($val(31));
                $joinLeaveMethod  = $trim($val(32));
                $probationProrata = $trim($val(33));
                $status           = $trim($val(34));
                $employeeType     = $trim($val(35));
                $doj              = $val(36);
                $jobStatus        = $trim($val(37));
                $paymentMethod    = $trim($val(38));

                // Required fields validation
                $required = [
                    'Employee Code'                    => $employeeCode,
                    'Prefix'                           => $prefix,
                    'First Name'                       => $firstName,
                    'Contact Number'                   => $contactNumber,
                    'Email'                            => $email,
                    'Gender'                           => $gender,
                    'Marital Status'                   => $maritalStatus,
                    'Blood Group'                      => $bloodGroup,
                    'Permanent Address'                => $permanentAddress,
                    'Permanent Pin Code'               => $permanentPin,
                    'Temporary Address'                => $temporaryAddress,
                    'Temporary Pin Code'               => $temporaryPin,
                    'Branch'                           => $branchName,
                    'Department'                       => $deptName,
                    'Designation'                      => $designationName,
                    'Grade'                            => $gradeName,
                    'Role'                             => $roleName,
                    'Assign Policy'                    => $assignPolicy,
                    'Assign Check In Method'           => $checkInMethod,
                    'Assign Shift'                     => $shiftName,
                    'Assign Mode'                      => $assignMode,
                    'Geofencing'                       => $geofencing,
                    'Weekoff'                          => $weekoffName,
                    'Preference'                       => $preference,
                    'Leave Assign Policy'              => $leavePolicyName,
                    'Leave credit on pro-rata'         => $leaveProrata,
                    'Joining Leave Calculation Method' => $joinLeaveMethod,
                    'Probation leave on pro-rata'      => $probationProrata,
                    'Status'                           => $status,
                    'Employee Type'                    => $employeeType,
                    'Job Status'                       => $jobStatus,
                    'Payment Method'                   => $paymentMethod,
                ];

                foreach ($required as $field => $value) {
                    if ($value === null || (is_string($value) && trim($value) === '')) {
                        throw new \Exception("{$field} is required");
                    }
                }

                // Resolve reference IDs
                $branchId           = $this->getBranchId($branchName);
                $deptId             = $this->getDepartmentId($deptName);
                $designationId      = $this->getDesignationId($designationName);
                $gradeId            = $this->getGradeId($gradeName);
                $roleId             = $this->getRoleId($roleName);
                $shiftId            = $this->getShiftId($shiftName);
                $leavePolicyId      = $this->getLeavePolicyId($leavePolicyName);
                $attendancePolicyId = $this->getAttendancePolicyId($assignPolicy);
                $weekoffId          = $this->getWeekOffId($weekoffName);
                $managerId          = $this->getManagerId($managerCode); // nullable

                $prefixId        = $this->getMasterId('PREFIX', $prefix);
                $genderId        = $this->getMasterId('GENDER', $gender);
                $maritalStatusId = $this->getMasterId('MARITAL_STATUS', $maritalStatus);
                $bloodGroupId    = $this->getMasterId('BLOOD_GROUP', $bloodGroup);

                $checkInMethodId = explode(',', $this->getMasterId('CHECKIN_METHOD', $checkInMethod));
                $checkInMethodId = array_map('intval', array_filter($checkInMethodId));

                $assignModeId      = $this->getMasterId('WORK_MODE', $assignMode);
                $statusId          = $this->getMasterId('STATUS', $status);
                $employeeTypeId    = $this->getMasterId('EMPLOYEE_TYPE', $employeeType);
                $jobStatusId       = $this->getMasterId('JOB_STATUS', $jobStatus);
                $preferenceId      = $this->getMasterId('ATTENDANCE_PREFERENCE', $preference);
                $joinLeaveMethodId = $this->getMasterId('LEAVE_CALCULATION_BY', $joinLeaveMethod);

                $dobCarbon = $this->parseDate($dob, 'Date of Birth');
                $dojCarbon = $this->parseDate($doj, 'Date of Joining');

                // Create / Update Employee
                Employee::updateOrCreate(
                    [
                        'emp_b_id' => $this->businessId,
                        'emp_code' => $employeeCode,
                    ],
                    [
                        'emp_b_id'                    => $this->businessId,
                        'emp_code'                    => $employeeCode,
                        'emp_sap_budget_code'         => $budgetCode,

                        'emp_prefix'                  => $prefixId,
                        'emp_gender_id'               => $genderId,
                        'emp_marital_status_id'       => $maritalStatusId,
                        'emp_blood_group_id'          => $bloodGroupId,
                        'emp_checkin_method_id'       => $checkInMethodId, // array
                        'emp_work_mode_id'            => $assignModeId,
                        'emp_status'                  => $statusId,
                        'emp_type_id'                 => $employeeTypeId,
                        'emp_job_status'              => $jobStatusId,

                        'emp_fname'                   => $firstName,
                        'emp_mname'                   => $middleName,
                        'emp_lname'                   => $lastName,
                        'emp_full_name'               => trim(implode(' ', array_filter([$prefix, $firstName, $middleName, $lastName]))),

                        'emp_phone'                   => $contactNumber,
                        'emp_email'                   => $email,
                        'emp_dob'                     => $dobCarbon->format('Y-m-d'),
                        'emp_date_of_joining'         => $dojCarbon->format('Y-m-d'),

                        'emp_permanent_address'       => $permanentAddress,
                        'emp_permanent_pin_code'      => $permanentPin,
                        'emp_temporary_address'       => $temporaryAddress,
                        'emp_temporary_pin_code'      => $temporaryPin,

                        'emp_br_id'                   => $branchId,
                        'emp_d_id'                    => $deptId,
                        'emp_dg_id'                   => $designationId,
                        'emp_grade_id'                => $gradeId,
                        'emp_role_id'                 => $roleId,
                        'emp_supervisor_id'           => $managerId,

                        'emp_attendance_preference'   => $preferenceId,
                        'emp_pwo_id'                  => $weekoffId,
                        'emp_pl_id'                   => $leavePolicyId,
                        'emp_ap_id'                   => $attendancePolicyId,
                        'emp_joining_leave_calc_type' => $joinLeaveMethodId,
                        'emp_shift_type_id'           => $shiftId,

                        'emp_allow_joining_leave'     => $leaveProrata === 'Allowed' ? 1 : 0,
                        'emp_allow_probation_leave'   => $probationProrata === 'Allowed' ? 1 : 0,
                        'emp_is_geofencing_active'    => $geofencing === 'Active' ? 1 : 0,

                        'emp_paymentmode'             => $paymentMethod,
                    ]
                );

                $addedCount++;

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                $message = "Row {$excelRowNumber}: " . $e->getMessage();

                $this->failures[]      = new Failure($excelRowNumber, 'general', [$e->getMessage()], $rowArray);
                $this->errorMessages[] = $message;

                // Most common case: continue with next row instead of stopping whole import
                // If you want to stop on first error → remove continue and/or re-throw
                continue;
            }
        }
    }

    private function preloadAllReferenceData(): void
    {
        if (! empty($this->branches)) {
            return;
        }

        $toLowerKey = fn($collection) => $collection->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])->toArray();

        $this->branches           = $toLowerKey(Branch::where('br_b_id', $this->businessId)->pluck('br_id', 'br_name'));
        $this->departments        = $toLowerKey(Department::where('d_b_id', $this->businessId)->pluck('d_id', 'd_name'));
        $this->designations       = $toLowerKey(Designation::where('dg_b_id', $this->businessId)->pluck('dg_id', 'dg_name'));
        $this->grades             = $toLowerKey(Grade::where('g_b_id', $this->businessId)->pluck('g_id', 'g_name'));
        $this->roles              = $toLowerKey(Role::where('role_b_id', $this->businessId)->pluck('role_id', 'role_name'));
        $this->shifts             = $toLowerKey(PolicyShiftTiming::where('pst_b_id', $this->businessId)->pluck('pst_id', 'pst_name'));
        $this->leavePolicies      = $toLowerKey(PolicyLeave::where('pl_b_id', $this->businessId)->pluck('pl_id', 'pl_name'));
        $this->attendancePolicies = $toLowerKey(
            PolicyAttendance::where('ap_b_id', $this->businessId)->where('ap_status', 1)->pluck('ap_id', 'ap_name')
        );

        $this->employeeCodeToId = Employee::where('emp_b_id', $this->businessId)
            ->pluck('emp_id', 'emp_code')
            ->toArray();

        $weekOffs              = Auth::user()->fh_business->fh_weekOff_policies ?? collect();
        $this->weekOffNameToId = $weekOffs->pluck('pwo_id', 'pwo_name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        // Master tables
        $groups  = ['PREFIX', 'GENDER', 'MARITAL_STATUS', 'BLOOD_GROUP', 'CHECKIN_METHOD', 'WORK_MODE', 'STATUS', 'EMPLOYEE_TYPE', 'JOB_STATUS', 'LEAVE_CALCULATION_BY', 'ATTENDANCE_PREFERENCE'];
        $masters = MasterTable::whereIn('m_group', $groups)->get();

        foreach ($masters as $m) {
            $key                                  = strtolower(trim($m->m_name));
            $this->masterCache[$m->m_group][$key] = $m->m_id;
        }
    }

    // Getters
    private function getBranchId(string $name): int
    {return $this->branches[strtolower(trim($name))] ?? throw new \Exception("Branch '{$name}' not found");}
    private function getDepartmentId(string $name): int
    {return $this->departments[strtolower(trim($name))] ?? throw new \Exception("Department '{$name}' not found");}
    private function getDesignationId(string $name): int
    {return $this->designations[strtolower(trim($name))] ?? throw new \Exception("Designation '{$name}' not found");}
    private function getGradeId(string $name): int
    {return $this->grades[strtolower(trim($name))] ?? throw new \Exception("Grade '{$name}' not found");}
    private function getRoleId(string $name): int
    {return $this->roles[strtolower(trim($name))] ?? throw new \Exception("Role '{$name}' not found");}
    private function getShiftId(string $name): int
    {return $this->shifts[strtolower(trim($name))] ?? throw new \Exception("Shift '{$name}' not found");}
    private function getLeavePolicyId(string $name): int
    {return $this->leavePolicies[strtolower(trim($name))] ?? throw new \Exception("Leave Policy '{$name}' not found");}
    private function getAttendancePolicyId(string $name): int
    {return $this->attendancePolicies[strtolower(trim($name))] ?? throw new \Exception("Attendance Policy '{$name}' not found");}
    private function getWeekOffId(string $name): int
    {return $this->weekOffNameToId[strtolower(trim($name))] ?? throw new \Exception("Week Off '{$name}' not found");}

    // private function getManagerId(?string $code): ?int
    // {
    //     if (empty($code)) {
    //         return null; // Optional during bulk import
    //     }
    //     $code = trim($code);
    //     return $this->employeeCodeToId[$code] ?? null; // Return null if not found yet
    // }

    private function getManagerId(?string $code): ?int
    {
        // If code is empty (bulk import, etc.)
        if (empty($code)) {
            return Auth::user()?->emp_id; // default logged-in user
        }

        $code = trim($code);

        // If code exists in mapping, return it
        if (isset($this->employeeCodeToId[$code])) {
            return $this->employeeCodeToId[$code];
        }

        // Fallback if code not found
        return Auth::user()?->emp_id;
    }

    private function getMasterId(string $group, string $name): int
    {
        $key = strtolower(trim($name));
        return $this->masterCache[$group][$key] ?? throw new \Exception("Invalid {$group}: '{$name}'");
    }

    private function parseDate($date, string $field): Carbon
    {
        if (empty($date)) {
            throw new \Exception("{$field} is required");
        }

        // Excel serial date
        if (is_numeric($date)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $date));
            } catch (\Exception $e) {
                throw new \Exception("Invalid Excel date in {$field}");
            }
        }

        // Try common formats
        $formats = ['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'd/M/Y'];
        foreach ($formats as $format) {
            $parsed = Carbon::createFromFormat($format, trim($date));
            if ($parsed && $parsed->format($format) === trim($date)) {
                return $parsed;
            }
        }

        // Fallback
        try {
            return Carbon::parse(trim($date));
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format for {$field}: '{$date}'. Use DD/MM/YYYY");
        }
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
