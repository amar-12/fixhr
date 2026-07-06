<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\MasterTable;
use App\Models\PolicyAttendance;
use App\Models\PolicyLeave;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyWeekOff;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Exports\ErrorExport;
use Maatwebsite\Excel\Facades\Excel;

// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\ErrorExport;
// use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Hash;

// class EmployeeImport implements ToCollection, WithMapping
class EmployeeImport implements ToCollection
{
    public $errorMessages = []; // To store error messages
    protected $user;

    // Constructor to accept the user
    public function __construct($user)
    {
        $this->user = $user;
    }

    // Collection method to handle the import process
    public function collection(Collection $rows)
    {
        $successfulImports = [];
        $unsuccessfulImports = [];
        $successfulCount = 0;  // Count for successful rows
        $unsuccessfulCount = 0;  // Count for unsuccessful rows
        // Define the expected columns
        $expectedColumns = [
            'S. No. *',
            'Employee Code *',
            'Prefix *',
            'First Name *',
            'Middle Name',
            'Last Name *',
            'Contact Number *',
            'Email *',
            'Date Of Birth (DD/MM/YYYY) *',
            'Gender *',
            'Marital Status *',
            'Blood Group *',
            'Status *',
            'Employee Type *',
            'Date Of Joining (DD/MM/YYYY) *',
            'Job Status *',
            'Branch *',
            'Department *',
            'Designation *',
            'Grade *',
            'Role *',
            'Attendance Policy Name *',
            'Assign Attendance Mode *',
            'Assign Shift *',
            'Permanent Address *',
            'Permanent Pin Code *',
            'Temporary Address *',
            'Temporary Pin Code *',
            'Budget Code (SAP)*',
            'Account Code *',
            'PAN Number *',
            'Work Mode *',
            'Leave Policy Name *',
            'Weekoff Policy Name *',
        ];

        // Loop through rows
        foreach ($rows as $key => $row) {
            // Skip the header row (first row)
            if ($key == 0) {
                // Get column headers from the first row and trim spaces
                $headers = array_map('trim', $row->toArray()); // Ensure extra spaces are trimmed

                // Check if the headers match the expected columns
                if ($headers !== $expectedColumns) {
                    throw new \Exception('Invalid CSV columns. The columns in the file do not match the required columns.');
                }
                continue;
            }

            // Assign row data to variables (ensure each value is set)
            $emp_code = $row[1] ?? null;
            $prefix_data = $row[2] ?? null;
            $f_name = $row[3] ?? null;
            $m_name = $row[4] ?? null;
            $l_name = $row[5] ?? null;
            $contact = $row[6] ?? null;
            $email = $row[7] ?? null;
            $dob = $row[8] ?? null;

            $gender_data = $row[9] ?? null;
            $marital_status_data = $row[10] ?? null;
            $blood_grp_data = $row[11] ?? null;
            $status_data = $row[12] ?? null;
            $emp_type_data = $row[13] ?? null;
            $doj = $row[14] ?? null;
            $job_status_data = $row[15] ?? null;
            $branch_data = $row[16] ?? null;
            $department_data = $row[17] ?? null;
            $designation_data = $row[18] ?? null;
            $grade_data = $row[19] ?? null;
            $role_data = $row[20] ?? null;
            $attendance_policy_data = $row[21] ?? null;
            $checkInMethods = $row[22];
            $assign_shift_data = $row[23] ?? null;
            $permanent_address = $row[24] ?? null;
            $permanent_pin_code = $row[25] ?? null;
            $temporary_address = $row[26] ?? null;
            $temporary_pin_code = $row[27] ?? null;
            $budget_code = $row[28] ?? null;
            $account_code = $row[29] ?? null;
            $pan_number = $row[30] ?? null;
            $work_mode = $row[31] ?? null;
            $leave_policy_name = $row[32] ?? null;
            $week_off_policy_name = $row[33] ?? null;

            // Prepare to collect row errors
            $rowErrors = [];

            // Initialize an array to hold the missing fields
            $missingFields = [];

            // Validate required fields and check for missing data
            if (!$emp_code) {
                $missingFields[] = 'Employee Code';
            }

            if (!$prefix_data) {
                $missingFields[] = 'Prefix Code';
            }

            if (!$emp_code) {
                $missingFields[] = 'Employee Code';
            }
            if (!$f_name) {
                $missingFields[] = 'First Name';
            }
            if (!$l_name) {
                $missingFields[] = 'Last Name';
            }
            if (!$contact) {
                $missingFields[] = 'Contact';
            }
            if (!$email) {
                $missingFields[] = 'Email';
            }

            if (!$dob) {
                $missingFields[] = 'Date of Birth';
            }

            if (!$gender_data) {
                $missingFields[] = 'Gender';
            }
            if (!$marital_status_data) {
                $missingFields[] = 'Marital Status';
            }
            if (!$blood_grp_data) {
                $missingFields[] = 'Blood Group';
            }
            if (!$status_data) {
                $missingFields[] = 'Status';
            }
            if (!$emp_type_data) {
                $missingFields[] = 'Employee Type';
            }
            if (!$doj) {
                $missingFields[] = 'Date of Joining';
            }
            if (!$job_status_data) {
                $missingFields[] = 'Job Status';
            }
            if (!$branch_data) {
                $missingFields[] = 'Branch';
            }
            if (!$department_data) {
                $missingFields[] = 'Department';
            }
            if (!$designation_data) {
                $missingFields[] = 'Designation';
            }
            if (!$grade_data) {
                $missingFields[] = 'Grade';
            }
            if (!$role_data) {
                $missingFields[] = 'Role';
            }
            if (!$attendance_policy_data) {
                $missingFields[] = 'Attendance Policy Name';
            }
            if (!$checkInMethods) {
                $missingFields[] = 'Assign Attendance Mode';
            }
            if (!$assign_shift_data) {
                $missingFields[] = 'Assign Shift';
            }



            if (!$permanent_address) {
                $missingFields[] = 'Permanent Address';
            }

            if (!$permanent_pin_code) {
                $missingFields[] = 'Permanent Pin Code';
            }

            if (!$temporary_address) {
                $missingFields[] = 'Temporary Address';
            }

            if (!$temporary_pin_code) {
                $missingFields[] = 'Temporary Pin Code';
            }

            if (!$pan_number) {
                $missingFields[] = 'Pan Number';
            }
            if (!$account_code) {
                $missingFields[] = 'Account Code';
            }

            if (!$budget_code) {
                $missingFields[] = 'Budget Code (SAP)';
            }
            if (!$work_mode) {
                $missingFields[] = 'Work Mode';
            }
            if (!$leave_policy_name) {
                $missingFields[] = 'Leave Policy Name';
            }
            if (!$week_off_policy_name) {
                $missingFields[] = 'Weekoff Policy Name';
            }

            // If there are any missing fields, add the error message
            if (!empty($missingFields)) {
                $rowErrors[] = "Required fields missing in row {$key}: " . implode(', ', $missingFields) . '.';
            }


            $existingEmpCode = Employee::where('emp_code', $emp_code)->where('emp_b_id', $this->user->emp_b_id)->exists();
            if ($existingEmpCode) {
                $rowErrors[] = "Employee Code already exists for another employee.";
            }

            // Check for unique email for active employees
            $existingEmailEmployee = Employee::where(function ($query) use ($email) {
                // Check email for the same business
                $query->where('emp_b_id', $this->user->emp_b_id)
                    ->where('emp_email', $email);
            })
                ->orWhere(function ($query) use ($email) {
                    // Check email for a different business with active status
                    $query->where('emp_b_id', '<>', $this->user->emp_b_id)
                        ->where('emp_email', $email)
                        ->where('emp_status', 71);
                })
                ->first();

            if ($existingEmailEmployee) {
                // Determine which error message to show based on the business id
                if ($existingEmailEmployee->emp_b_id == $this->user->emp_b_id) {
                    $rowErrors[] = "Email already exists for another employee.";
                } else {
                    $rowErrors[] = "Email already exists for another active employee in a different business.";
                }
            }

            // Check for unique phone for active employees
            $existingPhoneEmployee = Employee::where(function ($query) use ($contact) {
                // Check phone for the same business
                $query->where('emp_b_id', $this->user->emp_b_id)
                    ->where('emp_phone', $contact);
            })
                ->orWhere(function ($query) use ($contact) {
                    // Check phone for a different business with active status
                    $query->where('emp_b_id', '<>', $this->user->emp_b_id)
                        ->where('emp_phone', $contact)
                        ->where('emp_status', 71);
                })
                ->first();

            if ($existingPhoneEmployee) {
                // Determine which error message to show based on the business id
                if ($existingPhoneEmployee->emp_b_id == $this->user->emp_b_id) {
                    $rowErrors[] = "Phone already exists for another employee.";
                } else {
                    $rowErrors[] = "Phone already exists for another active employee in a different business.";
                }
            }

            // Define an array of the groups and names you're looking for
            $groupsAndNames = [
                'PREFIX' => $prefix_data,
                'GENDER' => $gender_data,
                'STATUS' => $status_data,
                'WORK_MODE' => $work_mode,
                'MARITAL_STATUS' => $marital_status_data,
                'BLOOD_GROUP' => $blood_grp_data,
                'EMPLOYEE_TYPE' => $emp_type_data,
                'JOB_STATUS' => $job_status_data,
            ];
            // Query for all relevant rows from the MasterTable
            $masterTableResults = MasterTable::whereIn('m_group', array_keys($groupsAndNames))
                ->whereIn('m_name', array_values($groupsAndNames))
                ->get()
                ->groupBy('m_group');
            // Now map the results to the appropriate variables and check for missing entries
            $prefix = $masterTableResults->get('PREFIX', collect())->pluck('m_id')->first();
            if (!$prefix) {
                $rowErrors[] = "Prefix '{$prefix_data}' not found.";
            }

            $gender = $masterTableResults->get('GENDER', collect())->pluck('m_id')->first();
            if (!$gender) {
                $rowErrors[] = "Gender '{$gender_data}' not found.";
            }

            $status = $masterTableResults->get('STATUS', collect())->pluck('m_id')->first();
            if (!$status) {
                $rowErrors[] = "Status '{$status_data}' not found.";
            }

            $marital_status = $masterTableResults->get('MARITAL_STATUS', collect())->pluck('m_id')->first();
            if (!$marital_status) {
                $rowErrors[] = "Marital Status '{$marital_status_data}' not found.";
            }

            $blood_grp = $masterTableResults->get('BLOOD_GROUP', collect())->pluck('m_id')->first();
            if (!$blood_grp) {
                $rowErrors[] = "Blood Group '{$blood_grp_data}' not found.";
            }

            $work_mode = $masterTableResults->get('WORK_MODE', collect())->pluck('m_id')->first();
            if (!$work_mode) {
                $rowErrors[] = "Work Mode '{$work_mode}' not found.";
            }

            $emp_type = $masterTableResults->get('EMPLOYEE_TYPE', collect())->pluck('m_id')->first();
            if (!$emp_type) {
                $rowErrors[] = "Employee Type '{$emp_type_data}' not found.";
            }

            $job_status = $masterTableResults->get('JOB_STATUS', collect())->pluck('m_id')->first();
            if (!$job_status) {
                $rowErrors[] = "Job Status '{$job_status_data}' not found.";
            }

            $checkInMethodNames = explode(',', $checkInMethods);
            $checkInMethodIds = [];
            if (!empty($checkInMethodNames)) {
                foreach ($checkInMethodNames as $checkIn_mode) {
                    if ($cMethod = MasterTable::where(['m_group' => 'CHECKIN_METHOD', 'm_name' => $checkIn_mode])->first()) {
                        $checkInMethodIds[] = $cMethod->id;
                    } else {
                        $rowErrors[] = "Check-in Method '{$checkIn_mode}' not found.";
                    }
                }
            }

            $business = $this->user->fh_business()
                ->with(
                    'fh_branches:br_id,br_b_id,br_name',
                    'fh_departments:d_id,d_b_id,d_name',
                    'fh_designations:dg_id,dg_name,dg_b_id',
                    'fh_recruitment_skills:rs_id,rs_b_id,rs_title',
                    'fh_leave_policies:pl_id,pl_b_id,pl_name',
                    'fh_grades:g_id,g_b_id,g_name',
                    'fh_roles:role_id,role_b_id,role_name',
                    'fh_shift_timings:pst_id,pst_b_id,pst_name',
                    'fh_weekOff_policies:pwo_id,pwo_b_id,pwo_name',
                    'fh_attendance_policies:ap_id,ap_b_id,ap_name',
                )
                ->where('b_id', $this->user->emp_b_id)
                ->get();

            // Fetch related data from other models
            $branch = $business->first()->fh_branches
                ->where('br_name', $branch_data)
                ->pluck('br_id')
                ->first();
            if (!$branch) {
                $rowErrors[] = "Branch '{$branch_data}' not found.";
            }

            $department = $business->first()->fh_departments
                ->where('d_name', $department_data)
                ->pluck('d_id')
                ->first();
            if (!$department) {
                $rowErrors[] = "Department '{$department_data}' not found.";
            }

            $designation = $business->first()->fh_designations
                ->where('dg_name', $designation_data)
                ->pluck('dg_id')
                ->first();
            if (!$designation) {
                $rowErrors[] = "Designation '{$designation_data}' not found.";
            }

            $grade = $business->first()->fh_grades
                ->where('g_name', $grade_data)
                ->pluck('g_id')
                ->first();
            if (!$grade) {
                $rowErrors[] = "Grade '{$grade_data}' not found.";
            }

            $role = $business->first()->fh_roles
                ->where('role_name', $role_data)
                ->pluck('role_id')
                ->first();
            if (!$role) {
                $rowErrors[] = "Role '{$role_data}' not found.";
            }

            $attendance_policy = $business->first()->fh_attendance_policies
                ->where('ap_name', $attendance_policy_data)
                ->pluck('ap_id')
                ->first();
            if (!$attendance_policy) {
                $rowErrors[] = "Attendance Policy '{$attendance_policy_data}' not found.";
            }

            $assign_shift = $business->first()->fh_shift_timings
                ->where('pst_name', $assign_shift_data)
                ->pluck('pst_id')
                ->first();
            if (!$assign_shift) {
                $rowErrors[] = "Assign Shift '{$assign_shift_data}' not found.";
            }

            $leave_policy = $business->first()->fh_leave_policies
                ->where('pl_name', $leave_policy_name)
                ->pluck('pl_id')
                ->first();
            if (!$leave_policy) {
                $rowErrors[] = "Leave Policy '{$leave_policy_name}' not found.";
            }

            $week_off_policy = $business->first()->fh_weekOff_policies
                ->where('pwo_name', $week_off_policy_name)
                ->pluck('pwo_id')
                ->first();
            if (!$week_off_policy) {
                $rowErrors[] = "Week-Off Policy '{$week_off_policy_name}' not found.";
            }

            // If row has errors, add to error list
            if (!empty($rowErrors)) {
                $this->errorMessages[] = "Row {$key} failed: " . implode(", ", $rowErrors);
                $unsuccessfulCount++;  // Increment the unsuccessful row count
                continue; // Skip this row if there are errors
            }


            // Insert data if row is valid
            try {

                $password = $emp_code.Carbon::createFromFormat('d/m/Y', $doj)->format('Y');
                $hashedPassword = Hash::make($password);

                $employee = Employee::create([
                    'emp_prefix' => $prefix,
                    'emp_b_id' => Auth::user()->emp_b_id,
                    'emp_code' => $emp_code,
                    'emp_full_name' => $f_name . ' ' . ($m_name ? $m_name . ' ' : '') . $l_name,
                    'emp_fname' => $f_name,
                    'emp_mname' => $m_name,
                    'emp_lname' => $l_name,
                    'emp_phone' => $contact,
                    'emp_email' => $email,
                    'emp_dob' => Carbon::createFromFormat('d/m/Y', $dob)->format('Y-m-d'),
                    'emp_gender_id' => $gender ?? null,
                    'emp_marital_status_id' => $marital_status ?? null,
                    'emp_blood_group_id' => $blood_grp ?? null,
                    'emp_status' => $status,
                    'emp_type_id' => $emp_type ?? null,
                    'emp_date_of_joining' => Carbon::createFromFormat('d/m/Y', $doj)->format('Y-m-d'),
                    'emp_job_status' => $job_status ?? null,
                    'emp_br_id' => $branch,
                    'emp_d_id' => $department,
                    'emp_dg_id' => $designation,
                    'emp_grade_id' => $grade,
                    'emp_work_mode_id' => $work_mode,
                    'emp_role_id' => $role,
                    'emp_ap_id' => $attendance_policy,
                    'emp_shift_type_id' => $assign_shift,
                    'emp_permanent_address' => $permanent_address,
                    'emp_permanent_pin_code' => $permanent_pin_code,
                    'emp_temporary_address' => $temporary_address,
                    'emp_temporary_pin_code' => $temporary_pin_code,
                    'emp_sap_budget_code' => $budget_code,
                    'emp_account_code' => $account_code,
                    'emp_pan_number' => $pan_number,
                    'emp_pl_id' => $leave_policy,
                    'emp_pwo_id' => $week_off_policy,
                    'emp_password'=> $hashedPassword,
                ]);
                // If no errors, process the row (successful import)
                $successfulImports[] = $row;
                $successfulCount++;  // Increment the successful row count
            } catch (\Exception $e) {
                // Now dump the values for debugging
                $this->errorMessages[] = "Error inserting employee with code {$emp_code}: " . $e->getMessage();

                $unsuccessfulCount++;  // Increment the unsuccessful row count
            }
        }

        // Return response with the results
        if (!empty($this->errorMessages)) {
            $this->errorMessages[] = "Successfully records imported count: {$successfulCount} & Unsuccessfully records imported count: {$unsuccessfulCount} ";
            return Excel::download(new ErrorExport($this->errorMessages), 'import_errors.xlsx');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Import completed successfully!',
            'imported_rows' => $successfulImports,
            'successful_count' => $successfulCount,  // Return successful count
            'unsuccessful_count' => $unsuccessfulCount,  // Return unsuccessful count
        ]);
    }


    // Method to get error messages
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function getRows()
    {
        return $this->rows;
    }
}
