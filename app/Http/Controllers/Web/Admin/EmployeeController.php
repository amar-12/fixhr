<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\EmployeeDataExport;
use App\Exports\EmployeeErrorReportExport;
use App\Exports\EmployeeExport;
use App\Exports\EmployeesExport;
use App\Exports\ErrorExport;
use App\Helpers\CentralLogics;
use App\Helpers\RolePermissionLogics;
use App\Models\PolicyWeekOff;
use ChandraHemant\HtkcUtils\CommonUtils;
use App\Http\Controllers\Controller;
use App\Imports\EmployeeImport;
use App\Mail\NewEmployeeMail;
use App\Models\Branch;
use App\Models\Business;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeApprovalMapping;
use App\Models\Grade;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\SalaryEmployeeSalary;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use App\Models\MasterTable;
use App\Models\PolicyAttendance;
use App\Models\PolicyLeave;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyTadaCategory;
use App\Models\PreviousOrganization;
use App\Models\Role;
use App\Models\State;
use App\Models\Qualification;
use App\Models\Stream;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\Aws\AwsHelper;
use App\Imports\EmployeesImport;
use DateTime;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\File;
use App\Exports\EmployeeDetailsHeaderExport;
use App\Imports\EmployeeDetailImport;
use App\Models\ApprovalFlow;
use App\Models\ApprovalModule;
use App\Models\Asset;
use App\Models\AssetHistory;
use App\Models\AutomationRule;
use App\Models\EarlyGoingAutomation;
use App\Models\EmployeeExitContact;
use App\Models\EmployeeExitRequest;
use App\Models\EmpStatusHistory;
use App\Models\LateComingAutomation;
use App\Models\MailTemplate;
use App\Models\PlanPriceSlab;
use App\Models\Project;
use App\Models\RuleCriterion;
use App\Models\Subscription;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Pest\Support\Str;

// use App\Http\Controllers\Web\Auth\Employee2FAController;

class EmployeeController extends Controller
{
    protected $user, $awsHelper;

    public function __construct()
    {
        $this->user = Auth::user();
        if (env('STORE_ON_S3')) {
            $this->awsHelper = new AwsHelper();
        }
    }

    // public function downloadSampleExcel()
    // {
    //     // $filename = "employee-sheet.xlsx";
    //     // return Excel::download(new EmployeeExport(), $filename);
    //     $filePath = public_path('upload_sample/employee-bulk-upload.csv');
    //     // Check if the file exists
    //     if (file_exists($filePath)) {
    //         // Return the file as a download response
    //         return response()->download($filePath);
    //     } else {
    //         // If the file doesn't exist, return a 404 error
    //         return abort(404, 'File not found');
    //     }
    // }



    // public function export()
    // {
    //     $user = Auth::user();

    //     $branches             = Branch::where('br_b_id', $user->emp_b_id)->pluck('br_name', 'br_id')->toArray();
    //     $departments          = Department::where('d_b_id', $user->emp_b_id)->pluck('d_name', 'd_id')->toArray();
    //     $designations         = Designation::where('dg_b_id', $user->emp_b_id)->pluck('dg_name', 'dg_id')->toArray();
    //     $grades               = Grade::where('g_b_id', $user->emp_b_id)->pluck('g_name', 'g_id')->toArray();
    //     $roles                = Role::where('role_b_id', $user->emp_b_id)->pluck('role_name', 'role_id')->toArray();
    //     $shiftType            = PolicyShiftTiming::where('pst_b_id', $user->emp_b_id)->pluck('pst_name', 'pst_id')->toArray();
    //     $leavePolicy          = PolicyLeave::where('pl_b_id', $user->emp_b_id)->pluck('pl_name', 'pl_ap_id')->toArray();
    //     $leaveCalcBy          = MasterTable::where('m_group', 'LEAVE_CALCULATION_BY')->pluck('m_name', 'm_id')->toArray();
    //     $weekOffs             = optional($user->fh_business->fh_weekOff_policies)->pluck('name', 'id')->toArray() ?? [];
    //     $attendancePreference = MasterTable::where('m_group', 'ATTENDANCE_PREFERENCE')->pluck('m_name', 'm_id')->toArray();
    //     $approvalModules      = MasterTable::where('m_group', 'MODULE')->pluck('m_name', 'm_id')->toArray();
    //     $employees            = Employee::where('emp_b_id', $user->emp_b_id)->pluck('emp_full_name', 'emp_id')->toArray();

    //     // 🔍 Required data map
    //     $requiredData = [
    //         'Branches'               => $branches,
    //         'Departments'            => $departments,
    //         'Designations'           => $designations,
    //         'Grades'                 => $grades,
    //         'Roles'                  => $roles,
    //         'Shift Types'            => $shiftType,
    //         'Leave Policies'         => $leavePolicy,
    //         'Leave Calculation'      => $leaveCalcBy,
    //         'Week Off Policies'      => $weekOffs,
    //         'Attendance Preference'  => $attendancePreference,
    //         'Approval Modules'       => $approvalModules,
    //         'Employees'              => $employees,
    //     ];

    //     $emptyFields = [];

    //     foreach ($requiredData as $label => $data) {
    //         if (empty($data)) {
    //             $emptyFields[] = $label;
    //         }
    //     }

    //     // ❌ Validation failed
    //     if (! empty($emptyFields)) {

    //         // 👉 AJAX request (SweetAlert)
    //         if (request()->ajax()) {
    //             return response()->json([
    //                 'status'         => false,
    //                 'message'        => 'Please configure following before export:',
    //                 'missing_fields' => $emptyFields,
    //             ], 422);
    //         }

    //         // 👉 Normal redirect (fallback)
    //         return redirect()->back()->with([
    //             'export_error'   => 'Please configure following before export:',
    //             'missing_fields' => $emptyFields,
    //         ]);
    //     }

    //     // ✅ Success → Excel download
    //     return Excel::download(
    //         new EmployeesExport(
    //             $branches,
    //             $departments,
    //             $designations,
    //             $grades,
    //             $roles,
    //             $shiftType,
    //             $leavePolicy,
    //             $leaveCalcBy,
    //             $weekOffs,
    //             $attendancePreference,
    //             $approvalModules,
    //             $employees
    //         ),
    //         'employees.xlsx'
    //     );
    // }


    public function export()
    {
        $user       = Auth::user();
        $businessId = $user->emp_b_id;

        // ================= MASTER DATA =================
        $branches        = Branch::where('br_b_id', $businessId)->pluck('br_name', 'br_id')->toArray();
        $departments     = Department::where('d_b_id', $businessId)->pluck('d_name', 'd_id')->toArray();
        $designations    = Designation::where('dg_b_id', $businessId)->pluck('dg_name', 'dg_id')->toArray();
        $grades          = Grade::where('g_b_id', $businessId)->pluck('g_name', 'g_id')->toArray();
        $roles           = Role::where('role_b_id', $businessId)->pluck('role_name', 'role_id')->toArray();
        $shiftTypes      = PolicyShiftTiming::where('pst_b_id', $businessId)->pluck('pst_name', 'pst_id')->toArray();
        $leavePolicies   = PolicyLeave::where('pl_b_id', $businessId)->pluck('pl_name', 'pl_id')->toArray();
        $leaveCalcBy     = MasterTable::where('m_group', 'LEAVE_CALCULATION_BY')->pluck('m_name', 'm_id')->toArray();
        $weekOffs        = optional($user->fh_business->fh_weekOff_policies)->pluck('pwo_name', 'pwo_id')->toArray() ?? [];
        $attendancePref  = MasterTable::where('m_group', 'ATTENDANCE_PREFERENCE')->pluck('m_name', 'm_id')->toArray();
        $attendancePol   = PolicyAttendance::where('ap_b_id', $businessId)
            ->where('ap_status', 1)
            ->orderByDesc('ap_id')
            ->pluck('ap_name', 'ap_id')
            ->toArray();
        $employees       = Employee::where('emp_b_id', $businessId)->pluck('emp_code', 'emp_id')->toArray();

        // ================= EMPLOYEE MASTER =================
        $prefix          = MasterTable::where('m_group', 'PREFIX')->pluck('m_name', 'm_id')->toArray();
        $gender          = MasterTable::where('m_group', 'GENDER')->pluck('m_name', 'm_id')->toArray();
        $maritalStatus   = MasterTable::where('m_group', 'MARITAL_STATUS')->pluck('m_name', 'm_id')->toArray();
        $bloodGroup      = MasterTable::where('m_group', 'BLOOD_GROUP')->pluck('m_name', 'm_id')->toArray();
        $checkInMethod   = MasterTable::where('m_group', 'CHECKIN_METHOD')->pluck('m_name', 'm_id')->toArray();
        $workMode        = MasterTable::where('m_group', 'WORK_MODE')->pluck('m_name', 'm_id')->toArray();
        $status          = MasterTable::where('m_group', 'STATUS')->pluck('m_name', 'm_id')->toArray();
        $employeeType    = MasterTable::where('m_group', 'EMPLOYEE_TYPE')->pluck('m_name', 'm_id')->toArray();
        $jobStatus       = MasterTable::where('m_group', 'JOB_STATUS')->pluck('m_name', 'm_id')->toArray();

        // ================= REQUIRED VALIDATION =================
        $requiredData = [
            'Branches'                  => $branches,
            'Departments'               => $departments,
            'Designations'              => $designations,
            'Grades'                    => $grades,
            'Roles'                     => $roles,
            'Shift Types'               => $shiftTypes,
            'Leave Policies'            => $leavePolicies,
            'Leave Calculation By'      => $leaveCalcBy,
            'Week Off Policies'         => $weekOffs,
            'Attendance Preference'     => $attendancePref,
            'Attendance Policies'       => $attendancePol,
            'Employees'                 => $employees,

            'Prefix *'                  => $prefix,
            'Gender *'                  => $gender,
            'Marital Status *'          => $maritalStatus,
            'Blood Group *'             => $bloodGroup,
            'Check In Method *'         => $checkInMethod,
            'Work Mode *'               => $workMode,
            'Status *'                  => $status,
            'Employee Type *'           => $employeeType,
            'Job Status *'              => $jobStatus,
        ];

        $emptyFields = [];

        foreach ($requiredData as $label => $data) {
            if (empty($data)) {
                $emptyFields[] = $label;
            }
        }

        // ❌ Stop export if anything missing
        if (! empty($emptyFields)) {
            return redirect()->back()->with([
                'export_error'   => 'Please configure following before export:',
                'missing_fields' => $emptyFields,
            ]);
        }

        // ✅ All good → Export
        return Excel::download(
            new EmployeesExport(
                $branches,
                $departments,
                $designations,
                $grades,
                $roles,
                $shiftTypes,
                $leavePolicies,
                $leaveCalcBy,
                $weekOffs,
                $attendancePref,
                $attendancePol,
                $employees,
                $prefix,
                $gender,
                $maritalStatus,
                $bloodGroup,
                $checkInMethod,
                $workMode,
                $status,
                $employeeType,
                $jobStatus
            ),
            'employees.xlsx'
        );
    }



    public function employeeExportData()
    {
        $user = Auth::user();

        $business_id = $user->emp_b_id;
        $employees = Employee::where('emp_b_id', $business_id)->get();


        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', 'No data found.');
        }

        $fileName = 'EmployeeData_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new EmployeeDataExport($user, $employees), $fileName);
    }


    public function employeeImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('import_file');

            Excel::import(new EmployeeImport(Auth::user()), $file);

            return redirect()
                ->back()
                ->with('success', 'Employees imported successfully!');
        } catch (\Exception $e) {

            // 👇 Employee limit reached case
            if ($e->getMessage() === 'EMPLOYEE_LIMIT_REACHED') {
                return redirect()
                    ->back()
                    ->with('error', 'Please upgrade your plan to add more employees.');
            }

            // 👇 General error
            return redirect()
                ->back()
                ->with('error', 'Employee import failed. Please check the file and try again.');
        }
    }




    public function downloadErrorFile()
    {
        // Check if the session contains errors
        if (!session()->has('import_errors')) {
            return redirect()->back();
            return redirect()->route('employee.import')->with('error', 'No error file found!');
        }

        // Get the error messages from the session
        $errorMessages = session()->get('import_errors');
        session()->forget('import_errors_blade');
        session()->forget('import_errors');
        // Return the Excel download with the error messages
        return Excel::download(new ErrorExport($errorMessages), 'import_errors.xlsx');
    }


    public function employeeExport($errors)
    {
        $fileName = 'EmployeeImportErrors_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new EmployeeErrorReportExport($errors), $fileName);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $dealershipFilter = request()->input('emp_dealershipFilter');
        $branchFilter = request()->input('emp_branchFilter');
        $activeFilter = request()->input('emp_activeFilter');
        $designationFilter = request()->input('emp_designationFilter');
        $departmentFilter = request()->input('emp_departmentFilter');
        $gradeFilter = request()->input('emp_gradeFilter');
        $monthFilter = request()->input('mt_monthFilter');
        $emp_projects = Project::where('ps_b_id', $user->emp_b_id)->get();
        // dd($monthFilter);
        $employee_status = MasterTable::where('m_group', 'STATUs')->get();
        // dd($employee_status)


        // Employee Limt Validtion
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $canAddEmployee = false;
        $limitMessage  = 'Please upgrade your plan to add more employees.';

        $subscription = Subscription::where('business_id', $b_id)->first();

        if ($subscription) {
            $slabData = PlanPriceSlab::find($subscription->price_slab_id);

            if ($slabData && $slabData->max_employees) {
                $maxEmployees   = (int) $slabData->max_employees;
                $totalEmployees = Employee::where('emp_b_id', $b_id)->where('emp_status', 71)->where('emp_role_id', '!=', 1)->count();

                if ($totalEmployees < $maxEmployees) {
                    $canAddEmployee = true;
                }
            }
        }


        $dealerships = Dealership::where('dlr_b_id', $this->user->emp_b_id)->pluck('dlr_name', 'dlr_id')->toArray();

        if ($request->ajax()) {
            // First update logic
            $currentDate = now()->toDateString();

            Employee::where('emp_b_id', $this->user->emp_b_id)
                ->where('emp_status', 71)
                ->whereDate('emp_last_working_date', '<', $currentDate)
                ->update(['emp_status' => 72]);
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['emp_b_id', $this->user->emp_b_id]
                ],
                [
                    'method' => 'whereNot',
                    'args' => ['emp_role_id', 1]
                ],
                [
                    'method' => 'select',
                    'args' => ['emp_id', 'emp_b_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_code', 'emp_role_id', 'emp_email', 'emp_type_id', 'emp_br_id', 'emp_d_id', 'emp_dg_id', 'emp_dob', 'emp_date_of_joining', 'emp_phone', 'emp_work_mode_id', 'emp_shift_type_id', 'emp_status', 'emp_grade_id', 'emp_gender_id', 'emp_date_of_joining', 'emp_marital_status_id', 'emp_profile_photo', 'created_at'],
                    'relation' => ['fh_employee_type:m_id,m_name', 'fh_branch:br_id,br_name', 'fh_gender:m_id,m_name', 'fh_designation:dg_id,dg_name', 'fh_department:d_id,d_name', 'fh_work_mode:m_id,m_name', 'fh_shift_type:pst_id,pst_name', 'fh_employee_status:m_id,m_name', 'fh_marital_status:m_id,m_name', 'fh_grade:g_id,g_name,g_b_id', 'fh_role:role_id,role_b_id,role_name,role_description', 'assetTypes:name'],
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['emp_id', 'emp_full_name', 'emp_code', 'emp_type_id', 'emp_br_id', 'emp_d_id', 'emp_date_of_joining', 'emp_phone', 'emp_work_mode_id', 'emp_shift_type_id', 'emp_status']
                ]
            ];

            // Filter conditions
            if ($branchFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
            }

            if ($dealershipFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dlr_id', $dealershipFilter]];
            }

            if ($activeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
            }

            if ($designationFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
            }

            if ($departmentFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
            }

            if ($departmentFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
            }
            if ($gradeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_grade_id', $gradeFilter]];
            }
            if ($monthFilter != '') {
                // Start date of month
                $startDate = $monthFilter . '-01';

                // End date of month (last day of the month)
                $endDate = date('Y-m-t', strtotime($startDate));

                // Add condition
                // $dynamicConditions[] = [
                //     'method' => 'whereBetween',
                //     'args' => ['emp_date_of_joining', [$startDate, $endDate]],
                // ];
                if ($activeFilter == 72) {
                    // Add condition
                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['emp_leave_date', [$startDate, $endDate]],
                    ];
                } else {
                    // Add condition
                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['emp_date_of_joining', [$startDate, $endDate]],
                    ];
                }
            }

            // Define search value, columns, and relationships
            $searchColumns = ['emp_id', 'emp_date_of_joining', 'updated_at', 'emp_code', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_email', 'emp_date_of_joining', 'emp_phone', 'emp_google2fa_secret', 'emp_google2fa_enabled_at'];
            $searchRelationships = [
                'fh_employee_type' => ['m_name'],
                'fh_branch' => ['br_name'],
                'fh_department' => ['d_name'],
                'fh_work_mode' => ['m_name'],
                'fh_shift_type' => ['pst_name'],
                'fh_employee_status' => ['m_name'],
            ];

            // Force select all columns to ensure 2FA fields are included
            $dynamicConditions[] = [
                'method' => 'select',
                'args' => ['*'],
                'relation' => []
            ];

            $list = (new DynamicModelDataTableHelper(eloquentModel: new Employee(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->getServerSideDataTable();


            $rowData = array();
            $i = 1;
            foreach ($list as $key => $val) {
                $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $val->emp_b_id, 'ptc_d_id' => $val->emp_d_id, 'ptc_grade_id' => $val->emp_grade_id])->whereJsonContains('ptc_dg_id', $val->emp_dg_id)->first();
                $dataPolicy = $policyCategory->ptc_name ?? 'N/A';
                $row = array();
                $row[] = $i++;
                $empFullName = $val->emp_fname . ' ';
                $empFullName .= $val->emp_mname != "" ? $val->emp_mname . ' ' . $val->emp_lname : $val->emp_lname;
                $row[] = $empFullName;
                $row[] = $val->emp_code;
                $row[] = $val->fh_employee_type?->m_name ?? 'N/A';
                $row[] = $val->fh_branch?->br_name ?? 'N/A';
                $row[] = $val->fh_department?->d_name ?? 'N/A';
                $row[] = date('d M Y', strtotime($val->emp_date_of_joining));

                $row[] = $val->emp_phone;
                $row[] = optional($val->fh_work_mode)->m_name;
                $row[] = optional($val->fh_shift_type)->pst_name;

                if ($val->emp_status === 71) {
                    // $row[] = '<span class="badge badge-success">' . $val->fh_employee_status->m_name . '</span>';
                    $row[] = '<span class="text-success">' . $val->fh_employee_status->m_name . '</span>';
                } else if ($val->emp_status === 72) {
                    // $row[] = '<span class="badge badge-danger">' . $val->fh_employee_status->m_name . '</span>';
                    $row[] = '<span class="text-danger">' . $val->fh_employee_status->m_name . '</span>';
                } else {
                    $row[] = '--';
                    // $row[] = '<span class="text-info">' . $val->fh_employee_status->m_name . '</span>';
                }

                // $assetTypes = $val->fh_assets->map(function ($asset) {
                //     return optional($asset->assetType)->name;
                // })->filter()->toArray();

                // $row[] = collect($assetTypes)->map(function ($type) {
                //     return '<span class="badge rounded-pill text-primary d-block" style="margin-bottom:5px!important;">'
                //         . e($type) .
                //         '</span>';
                // })->implode('');



                $editUrl = route('update.employee', md5($val->emp_id));
                $row[] = '
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" type="button" id="dropdownMenu' . $val->emp_id . '" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu' . $val->emp_id . '">
                        <li>
                            <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 openBtn"
                                data-id="' . $val->emp_id . '"
                                data-encid="' . md5($val->emp_id) . '"
                                data-name="' . $empFullName . '"
                                data-code="' . $val->emp_code . '"
                                data-type="' . optional($val->fh_employee_type)->m_name . '"
                                data-branch="' . optional($val->fh_branch)->br_name . '"
                                data-department="' . optional($val->fh_department)->d_name . '"
                                data-phone="' . $val->emp_phone . '"
                                data-email="' . $val->emp_email . '"
                                data-designation="' . optional($val->fh_designation)->dg_name . '"
                                data-grade="' . optional($val->fh_grade)->g_name . '"
                                data-gender="' . optional($val->fh_gender)->m_name . '"
                                data-role="' . optional($val->fh_role)->role_name . '"
                                data-birth="' . $val->emp_dob . '"
                                data-marital="' . optional($val->fh_marital_status)->m_name . '"
                                data-joining="' . $val->emp_date_of_joining . '"
                                data-status="' . optional($val->fh_employee_status)->m_name . '"
                                data-mode="' . optional($val->fh_work_mode)->m_name . '"
                                data-shift="' . optional($val->fh_shift_type)->pst_name . '"
                                data-profile="' . $val->emp_profile_photo . '"
                                data-policy="' . $dataPolicy . '">
                                <i class="feather feather-eye"></i> View
                            </button>
                        </li>' .

                    ($val->emp_id != $this->user->emp_id ? '
                        <li>
                            <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                            href="' . (RolePermissionLogics::check_route_permission('admin/employee/form/{id}', 117) ? $editUrl : '#') . '">
                                <i class="feather feather-edit"></i> Edit
                            </a>
                        </li>' : '') . '



                        <li>
                            <button class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2 disable2faBtn"
                                data-employee-id="' . $val->emp_id . '"
                                data-employee-name="' . $val->emp_fname . ' ' . $val->emp_lname . '"
                                data-2fa-enabled="' . ((!empty($val->emp_google2fa_secret) && !empty($val->emp_google2fa_enabled_at)) ? 'true' : 'false') . '"
                                ' . ((!empty($val->emp_google2fa_secret) && !empty($val->emp_google2fa_enabled_at)) ? '' : 'disabled') . '>
                                <i class="feather feather-shield-off"></i> Disable 2FA ' . ((!empty($val->emp_google2fa_secret) && !empty($val->emp_google2fa_enabled_at)) ? '(ENABLED)' : '(DISABLED)') . '
                            </button>
                        </li>
                    </ul>
                </div>';


                $row[] = '<div class="d-flex">
                        <label class="custom-control custom-checkbox-md p-0 ms-2">
                           <input type="checkbox" class="custom-control-input-success select-checkbox testClass"
                                    name="bulk_employee_ids[]" value="' . Crypt::encryptString($val->emp_id) . '"
                                    onclick="selectCheckboxUpdate(this)">
                            <span class="custom-control-label-md success"></span>
                        </label>
                    </div>';


                $rowData[] = $row;
            }

            $output = array(
                "draw" => request()->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new Employee(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            );

            return json_encode($output);
        } else {
            $business = $this->user->fh_business()
                ->with(
                    'fh_branches:br_id,br_b_id,br_name',
                    'fh_departments:d_id,d_b_id,d_name',
                    'fh_designations:dg_id,dg_name,dg_b_id',
                    'fh_employees:*'
                )
                ->where('b_id', $this->user->emp_b_id)
                ->get();

            $businessId = $this->user->emp_b_id;
            $currentMonth = now()->month;

            $allEmployeeCount =  $business->first()->fh_employees()->where('emp_b_id', $businessId)->where('emp_role_id', '<>', 1)->where('emp_status', 71)->count();
            $allInactiveEmployeeCount =  $business->first()->fh_employees()->where('emp_b_id', $businessId)->where('emp_role_id', '<>', 1)->where('emp_status', 72)->count();
            $totalEmployeeCount =  $business->first()->fh_employees()->where('emp_b_id', $businessId)->where('emp_role_id', '<>', 1)->count();

            // dd($allEmployeeCount, $allInactiveEmployeeCount, $totalEmployeeCount);

            $allInactiveEmployeeCount = $totalEmployeeCount - $allEmployeeCount;

            // dd($allInactiveEmployeeCount);

            $genderCounts = $business->first()->fh_employees()->selectRaw('count(*) as count, emp_gender_id')
                ->where('emp_b_id', $businessId)
                ->where('emp_status', 71)
                ->groupBy('emp_gender_id')
                ->pluck('count', 'emp_gender_id');
            $maleEmployeesCount = $genderCounts[33] ?? 0;
            $femaleEmployeesCount = $genderCounts[34] ?? 0;
            $newEmployeesCount = $business->first()->fh_employees()->where('emp_b_id', $businessId)
                ->whereMonth('emp_date_of_joining', $currentMonth)
                ->count();

            $exitEmployeesCount = $business->first()->fh_employees()->where('emp_b_id', $businessId)
                ->whereMonth('emp_last_working_date', $currentMonth)
                ->count();

            $branches = $business->first()->fh_branches->pluck('br_name', 'br_id')->toArray();
            $departments = $business->first()->fh_departments->pluck('d_name', 'd_id')->toArray();
            $designations =  $business->first()->fh_designations->pluck('dg_name', 'dg_id')->toArray();

            // $branches = Branch::where('br_b_id', $user->emp_b_id)->pluck('br_name', 'br_id')->toArray();
            // $departments = Department::where('d_b_id', $user->emp_b_id)->pluck('d_name', 'd_id')->toArray();
            // $designations = Designation::where('dg_b_id', $user->emp_b_id)->pluck('dg_name', 'dg_id')->toArray();
            $countries  = Country::pluck('c_name', 'c_id')->toArray();
            $staticGender =  MasterTable::where('m_group', 'GENDER')->get();
            $maritalStatus  =  MasterTable::where('m_group', 'MARITAL_STATUS')->get();
            $BranchList =  Branch::where('br_b_id', $user->emp_b_id)->get();
            $DepartmentList = Department::where('d_b_id', $user->emp_b_id)->get();
            $DesignationList = Designation::where('dg_b_id', $user->emp_b_id)->get();
            $Grade = Grade::where('g_b_id', $user->emp_b_id)->get();
            $Role = Role::where('role_b_id', $user->emp_b_id)->get();
            $attendancePolicy = PolicyAttendance::where('ap_b_id', $user->emp_b_id)->where('ap_status', 1)->orderBy('ap_id', 'desc')->get();
            $checkInMethod = collect();
            if ($attendancePolicy->first()?->ap_checkin_method_ids) {
                $methodIds = $attendancePolicy->first()?->fh_check_in_method()->pluck('m_id')->toArray();
                $checkInMethod = MasterTable::where('m_group', 'CHECKIN_METHOD')
                    ->whereIn('m_id', $methodIds)
                    ->get();
            }
            $ShiftType = PolicyShiftTiming::where('pst_b_id', $user->emp_b_id)->get();
            $prefix = MasterTable::where('m_group', 'PREFIX')->get();
            $weekOffs = $user->fh_business->fh_weekOff_policies?->pluck('pwo_name', 'pwo_id')->toArray();
            $leavePolicy = PolicyLeave::where('pl_b_id', $user->emp_b_id)->distinct('pl_ap_id')->get();
            $leaveCalcBy = MasterTable::where('m_group', 'LEAVE_CALCULATION_BY')->get();
            $employeeType = MasterTable::where('m_group', 'EMPLOYEE_TYPE')->get();
            $emp_status = MasterTable::where('m_group', 'STATUS')->get();
            $employeeJobStatus = MasterTable::where('m_group', 'JOB_STATUS')->get();
            $supervisor = Employee::where('emp_b_id', $user->emp_b_id)->where('emp_status', 71)->get();
            $businessEmpCode = Business::where([
                'b_id' => $user->emp_b_id,
                'b_emp_code_type' => 190,
            ])->pluck('b_emp_code')->first();

            // Ensure $businessEmpCode is not empty
            if ($businessEmpCode) {
                // Query to get the new employee code

                $newEmpCodeNumber = Employee::select(DB::raw("
                        COALESCE(
                            MAX(CAST(SUBSTRING(emp_code, LENGTH('$businessEmpCode') + 1) AS UNSIGNED)),
                            0
                        ) + 1 AS code_number
                    "))->where('emp_b_id', $user->emp_b_id)
                    ->where('emp_code', 'LIKE', "$businessEmpCode%")
                    ->first();


                if ($newEmpCodeNumber && $newEmpCodeNumber->code_number) {
                    $number = $newEmpCodeNumber->code_number;

                    // Pad with leading zeros if less than 1000
                    if ($number < 1000) {
                        $formattedNumber = str_pad($number, 3, '0', STR_PAD_LEFT);  // e.g., 5 => '005'
                    } else {
                        $formattedNumber = (string)$number;  // No padding for 1000+
                    }

                    $newEmpCode = $businessEmpCode . $formattedNumber;
                } else {
                    // First employee code
                    $newEmpCode = $businessEmpCode . '001';
                }
            } else {
                // Fallback if no businessEmpCode is found
                $newEmpCode = null; // or a default like 'TEMP001'
            }

            $groups = [
                'EMPLOYEE_TYPE',
                'CONTRACTUAL_TYPE',
                'GENDER',
                'MARITAL_STATUS',
                'RELIGION',
                'CAST',
                'BLOOD_GROUP',
                'GOVT_DOC_TYPE',
                'WORK_MODE'
            ];

            $data = MasterTable::whereIn('m_group', $groups)
                ->get(['m_group', 'm_name', 'm_id'])
                ->groupBy('m_group')
                ->map(function ($items) {
                    return $items->pluck('m_name', 'm_id')->toArray();
                });

            $getEmpType = $data['EMPLOYEE_TYPE'] ?? [];
            $getContractualType = $data['CONTRACTUAL_TYPE'] ?? [];
            $genders = $data['GENDER'] ?? [];
            $maritalStatuses = $data['MARITAL_STATUS'] ?? [];
            $religions = $data['RELIGION'] ?? [];
            $casts = $data['CAST'] ?? [];
            $bloodGroups = $data['BLOOD_GROUP'] ?? [];
            $govtIds = $data['GOVT_DOC_TYPE'] ?? [];
            $attendanceMethod = $data['WORK_MODE'] ?? [];
        }

        $columns = [
            'S.No.',
            'Emp. Name',
            'Emp. Code',
            'Emp. Type',
            'Branch',
            'Department',
            'DOJ',
            'Contact',
            'Method',
            'Shift Type',
            'Status',
            // 'Assets',
            'Action',
            'Select All'
        ];

        $bulkUpdateFields = [
            'emp_pl_id' => [
                'label' => 'Leave Policy',
                'input_type' => 'select',
            ],
            'emp_pwo_id' => [
                'label' => 'Weekoff Policy',
                'input_type' => 'select',
            ],
            'emp_shift_type_id' => [
                'label' => 'Shift Policy',
                'input_type' => 'select',
            ],
            'emp_status' => [
                'label' => 'Status',
                'input_type' => 'select', // assuming a status dropdown like Active/Inactive
            ],
            'emp_checkin_method_id' => [
                'label' => 'Checkin Method',
                'input_type' => 'select',
            ],
            'emp_is_geofencing_active' => [
                'label' => 'Geofencing',
                'input_type' => 'select',
            ],
            'emp_is_wifi_restricted' => [
                'label' => 'Wi-Fi Restriction',
                'input_type' => 'select',
            ],
        ];


        return view('admin.employees.employee', compact('allEmployeeCount', 'allInactiveEmployeeCount', 'emp_projects', 'totalEmployeeCount', 'maleEmployeesCount', 'femaleEmployeesCount', 'newEmployeesCount', 'exitEmployeesCount', 'getEmpType', 'getContractualType', 'genders', 'maritalStatuses', 'religions', 'casts', 'bloodGroups', 'govtIds', 'branches', 'departments', 'designations', 'attendanceMethod', 'countries', 'columns', 'bulkUpdateFields', 'employee_status', 'dealerships', 'prefix', "staticGender", "maritalStatus", "BranchList", "DesignationList", "DepartmentList", "Role", "Grade", "supervisor", "attendancePolicy", "checkInMethod", "ShiftType", "attendanceMethod", "weekOffs", "leavePolicy", "leaveCalcBy", "emp_status", "employeeType", "employeeJobStatus", "newEmpCode", "canAddEmployee", "limitMessage"));
    }

    public function getFieldOptions(Request $request)
    {
        $user = Auth::user();
        $field = $request->query('field');

        // Dummy response (you can fetch from DB based on $field)
        $options = [];

        switch ($field) {
            case 'emp_pl_id':
                $options = PolicyLeave::where('pl_b_id', $user->emp_b_id)->pluck('pl_name', 'pl_id')->toArray();
                break;

            case 'emp_pwo_id':
                $options = PolicyWeekOff::where('pwo_b_id', $user->emp_b_id)->pluck('pwo_name', 'pwo_id')->toArray();
                break;

            case 'emp_shift_type_id':
                $options = PolicyShiftTiming::where('pst_b_id', $user->emp_b_id)->pluck('pst_name', 'pst_id')->toArray();
                break;
            case 'emp_status':
                $options = MasterTable::where('m_group', 'STATUS')->pluck('m_name', 'm_id')->toArray();
                break;
            case 'emp_checkin_method_id':
                $options = MasterTable::where('m_group', 'CHECKIN_METHOD')->pluck('m_name', 'm_id')->toArray();;
                break;
            case 'emp_is_wifi_restricted':
                $options = [1 => 'Active', 0 => 'InActive'];
                break;
            case 'emp_is_geofencing_active':
                $options = [1 => 'Active', 0 => 'InActive'];
                break;
            default:
                $options = [];
        }

        return response()->json($options);
    }


    // public function bulkUpdate(Request $request)
    // {
    //     // dd($request->all());
    //     try {
    //         // Get the input values
    //         $empIDs = $request->input('empIDs', []);
    //         $field = $request->input('field');
    //         $value = $request->input('value');

    //         if ($field == 'emp_checkin_method_id') {
    //             $value = array_map('intval', $value);
    //         }

    //         // Check if empIDs array is empty
    //         if (empty($empIDs)) {
    //             return response()->json(['error' => 'No employees selected.'], 400);
    //         }

    //         // Decrypt each employee ID
    //         $decryptedIDs = [];
    //         foreach ($empIDs as $encryptedID) {
    //             try {
    //                 $decryptedIDs[] = Crypt::decryptString($encryptedID);
    //             } catch (\Exception $e) {
    //                 return response()->json(['error' => 'Invalid employee ID detected.'], 400);
    //             }
    //         }

    //         // Ensure employees exist in the database
    //         $existingIDs = Employee::whereIn('emp_id', $decryptedIDs)->pluck('emp_id')->toArray();
    //         if (empty($existingIDs)) {
    //             return response()->json(['error' => 'No valid employees found.'], 404);
    //         }

    //         // Perform the update
    //         Employee::whereIn('emp_id', $existingIDs)->update([
    //             $field => $value,
    //             'updated_at' => now()
    //         ]);

    //         return response()->json(['success' => 'Employees updated successfully!'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Something went wrong! ' . $e->getMessage()], 500);
    //     }
    // }


    public function bulkUpdate(Request $request)
    {
        // dd($request->all());
        try {
            // Get the input values
            $empIDs = $request->input('empIDs', []);
            $field = $request->input('field');
            $value = $request->input('value');

            if ($field == 'emp_checkin_method_id') {
                $value = array_map('intval', $value);
            }

            // Check if empIDs array is empty
            if (empty($empIDs)) {
                return response()->json(['error' => 'No employees selected.'], 400);
            }

            // Decrypt each employee ID
            $decryptedIDs = [];
            foreach ($empIDs as $encryptedID) {
                try {
                    $decryptedIDs[] = Crypt::decryptString($encryptedID);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid employee ID detected.'], 400);
                }
            }

            // Ensure employees exist in the database
            $existingIDs = Employee::whereIn('emp_id', $decryptedIDs)->pluck('emp_id')->toArray();
            if (empty($existingIDs)) {
                return response()->json(['error' => 'No valid employees found.'], 404);
            }

            // ✅ Add status history if updating emp_status
            if ($field == 'emp_status') {
                foreach ($existingIDs as $empId) {
                    $lastStatusId = EmpStatusHistory::where('hs_emp_id', $empId)
                        ->orderByDesc('hs_id')
                        ->value('hs_status_id');

                    if ($lastStatusId != $value) {
                        EmpStatusHistory::create([
                            'hs_emp_id'    => $empId,
                            'hs_b_id'      => auth()->user()->emp_b_id ?? null,
                            'hs_status_id' => $value,
                        ]);
                    }
                    // else skip silently
                }
            }

            // Perform the update
            Employee::whereIn('emp_id', $existingIDs)->update([
                $field => $value,
                'updated_at' => now()
            ]);

            return response()->json(['success' => 'Employees updated successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong! ' . $e->getMessage()], 500);
        }
    }


    // public function form($id = null)
    // {

    //     // $employee = Employee::whereRaw('MD5(emp_id) = ?', [$id])->firstOrFail();
    //     $user = Auth::user();
    //     $maritalStatus  =  MasterTable::where('m_group', 'MARITAL_STATUS')->get();
    //     $bloodGroupList = MasterTable::where('m_group', 'BLOOD_GROUP')->get();
    //     $staticGender =  MasterTable::where('m_group', 'GENDER')->get();
    //     $employeeType = MasterTable::where('m_group', 'EMPLOYEE_TYPE')->get();
    //     $BranchList =  Branch::where('br_b_id', $user->emp_b_id)->get();
    //     $DepartmentList = Department::where('d_b_id', $user->emp_b_id)->get();
    //     $DesignationList = Designation::where('dg_b_id', $user->emp_b_id)->get();
    //     $attendanceMethod = MasterTable::where('m_group', 'WORK_MODE')->get();
    //     $emp_status = MasterTable::where('m_group', 'STATUS')->get();
    //     $emp_leaving = MasterTable::where('m_group', 'Leaving')->get();
    //     $emp_esic_reason = MasterTable::where('m_group', 'esic')->get();
    //     $emp_region = MasterTable::where('m_group', 'Region')->get();
    //     $emp_cast = MasterTable::where('m_group', 'Caste')->get();
    //     $emp_projects = Project::where('ps_b_id', $user->emp_b_id)->get();
    //     $emp_assets = Asset::with('assetType')->where('assets_b_id', $user->emp_b_id)->whereIn('status', ['stock', 'assigned'])->get();
    //     $attendancePolicy = PolicyAttendance::where('ap_b_id', $user->emp_b_id)->where('ap_status', 1)->orderBy('ap_id', 'desc')->get();
    //     $weekOffs = $user->fh_business->fh_weekOff_policies?->pluck('pwo_name', 'pwo_id')->toArray();
    //     // $assets = Asset::with('assetType')->where('assets_b_id', $user->emp_b_id)->where('employee_id', $employee->emp_id)->get();


    //     // approval flow
    //     // $approvalFlowtype = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)->distinct()->pluck('eam_module_id');
    //     // $approvalFlowConfiguration = ApprovalFlow::where('afc_b_id', $user->emp_b_id)->get();
    //     // $approvalFlowConfigurationIds = $approvalFlowConfiguration->pluck('afc_approval_id');
    //     // $approvalFlowmodule = MasterTable::where('m_group', 'MODULE')->select('m_id', 'm_group', 'm_name')->whereIn('m_id', $approvalFlowtype)->whereIn('m_id', $approvalFlowConfigurationIds)->get();
    //     // $approvalFlowstatus = MasterTable::where('m_group', 'APPROVAL_STATUS')->select('m_id', 'm_group', 'm_name')->whereNotIn('m_id', [139, 140, 156, 170, 171, 175, 192])->get();
    //     // $approvalFlowwmpList = Employee::where('emp_b_id', $user->emp_b_id)->select('emp_id', 'emp_full_name')->get();



    //     $approvalFlowwmpList = Employee::where('emp_b_id', $user->emp_b_id)->select('emp_id', 'emp_full_name')->get();


    //     $approvalFlowtype = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)->distinct()->pluck('eam_module_id');


    //     $approvalFlowConfiguration = ApprovalFlow::where('afc_b_id', $user->emp_b_id)->get();

    //     $approvalFlowWithStatuses = $approvalFlowConfiguration->map(function ($approval) {
    //         $statusIds = $approval->afc_approval_status_id;
    //         $approval->status_ids = is_array($statusIds) ? $statusIds : [];
    //         return $approval;
    //     });
    //     // dd($approvalFlowWithStatuses);

    //     $approvalFlowConfigurationIds = $approvalFlowConfiguration->pluck('afc_approval_id');

    //     $approvalWiseStatus = $approvalFlowWithStatuses->mapWithKeys(function ($approval) {
    //         return [$approval->afc_approval_id => $approval->status_ids];
    //     });

    //     $approvalFlowmodule = MasterTable::where('m_group', 'MODULE')
    //         ->select('m_id', 'm_group', 'm_name')
    //         ->whereIn('m_id', $approvalFlowtype)
    //         ->whereIn('m_id', $approvalFlowConfigurationIds)
    //         ->get();

    //     // step 3: dono ko merge karke ek new array banao
    //     $finalResult = $approvalFlowmodule->map(function ($module) use ($approvalWiseStatus) {
    //         return [
    //             'm_id'       => $module->m_id,
    //             'm_group'    => $module->m_group,
    //             'm_name'     => $module->m_name,
    //             'status_ids' => $approvalWiseStatus[$module->m_id] ?? [],
    //         ];
    //     });

    //     $approvalFlowstatus = MasterTable::where('m_group', 'APPROVAL_STATUS')->select('m_id', 'm_group', 'm_name')->whereNotIn('m_id', [139, 140, 156, 170, 171, 175, 192])->get()->keyBy('m_id');

    //     $finalResult = $finalResult->map(function ($module) use ($approvalFlowstatus) {
    //         $statuses = collect($module['status_ids'])->map(function ($statusId) use ($approvalFlowstatus) {
    //             $status = $approvalFlowstatus->get($statusId);
    //             return $status ? [
    //                 'm_id'   => $status->m_id,
    //                 'm_name' => $status->m_name,
    //             ] : null;
    //         })->filter()->values()->toArray();

    //         return [
    //             'm_id'        => $module['m_id'],
    //             'm_group'     => $module['m_group'],
    //             'm_name'      => $module['m_name'],
    //             'statuses'    => $statuses,
    //         ];
    //     });



    //     $checkInMethod = collect();
    //     if ($attendancePolicy->first()?->ap_checkin_method_ids) {
    //         $methodIds = $attendancePolicy->first()?->fh_check_in_method()->pluck('m_id')->toArray();
    //         $checkInMethod = MasterTable::where('m_group', 'CHECKIN_METHOD')
    //             ->whereIn('m_id', $methodIds)
    //             ->get();
    //     }
    //     //$checkInMethod = MasterTable::where('m_group', 'CHECKIN_METHOD')->whereIn('m_id',$attendancePolicy->first()?->fh_check_in_method()->pluck('m_id')->toArray())->get(); //


    //     // ->pluck('id')->toArray();
    //     $FinalPolicyMethod = [];
    //     $AttendanceShiftTemplate = [];
    //     $countryList = Country::all();
    //     $prefix = MasterTable::where('m_group', 'PREFIX')->get();
    //     $Grade = Grade::where('g_b_id', $user->emp_b_id)->get();
    //     $Role = Role::where('role_b_id', $user->emp_b_id)->get();
    //     // $ShiftType = MasterTable::where('m_group', 'SHIFT_TYPE')->get();
    //     $ShiftType = PolicyShiftTiming::where('pst_b_id', $user->emp_b_id)->get();
    //     // $AttendanceMethod

    //     $leavePolicy = PolicyLeave::where('pl_b_id', $user->emp_b_id)->distinct('pl_ap_id')->get();
    //     $leaveCalcBy = MasterTable::where('m_group', 'LEAVE_CALCULATION_BY')->get();
    //     $getEsicLimit = MasterTable::where('m_group', 'YESNO')->get();
    //     // Fetch the employee data if ID is provided
    //     $employee = $id ? Employee::with(
    //         'fh_employee_qualifications',
    //         'latest_salary_master_history',
    //         'fh_gender',
    //         'fh_marital_status',
    //         'fh_blood_group',
    //         'fh_employee_status',
    //         'fh_employee_type',
    //         'fh_esic_limit',
    //         'fh_pf_master',
    //         'fh_branch',
    //         'fh_department',
    //         'fh_designation',
    //         'fh_work_mode',
    //         'fh_grade',
    //         'fh_job_status',
    //         'fh_attendance_preference:m_id,m_name',
    //         'fh_geofencing:m_id,m_name,m_description',
    //         'fh_assets'
    //     )
    //         ->where('emp_b_id', $user->emp_b_id)
    //         ->whereRaw('md5(emp_id) = ?', [$id])
    //         ->first() : null;

    //     // dd($employee );

    //     // $service_date = $employee->emp_date_of_joining; // e.g., "2024-05-18"
    //     $service_date = $employee ? $employee->emp_date_of_joining : null; // e.g., "2024-05-18"
    //     $join_date = new DateTime($service_date);
    //     $today = new DateTime();

    //     $interval = $join_date->diff($today);

    //     $formatted = $interval->format('%y y %m m %d d');

    //     $existingRows = [];
    //     if ($id) {
    //         $existingRows = EmployeeApprovalMapping::with('fh_employee_approver_manager_1', 'fh_employee_approver_manager_2')->whereRaw('md5(eam_emp_id) = ?', [$id])->get();
    //     }
    //     $approvalModules = MasterTable::where('m_group', 'MODULE')->pluck('m_name', 'm_id')->toArray();
    //     $employeeJobStatus = MasterTable::where('m_group', 'JOB_STATUS')->get();
    //     $qualificationCourseType = MasterTable::where('m_group', 'QUALIFICATION_COURSE_TYPE')->get();
    //     $qualification = Qualification::whereNull('qua_b_id')->orWhere('qua_b_id', $user->emp_b_id)->get();
    //     $stream = Stream::whereNull('stm_b_id')->orWhere('stm_b_id', $user->emp_b_id)->orderBy('stm_name')->get();
    //     $natureCourse = MasterTable::where('m_group', 'COURSE_TYPE')->get();
    //     $qualificationStatus = MasterTable::where('m_group', 'QUALIFICATION_STATUS')->get();
    //     $attendancePreference = MasterTable::where('m_group', 'ATTENDANCE_PREFERENCE')->pluck('m_name', 'm_id')->toArray();
    //     $previousOrganizations = $employee ? PreviousOrganization::where('po_emp_id', $employee->emp_id)->get() : null;
    //     $status = MasterTable::where('m_group', 'STATUS')->get();
    //     $supervisor = Employee::where('emp_b_id', $user->emp_b_id)->where('emp_status', 71);
    //     $supervisor = $supervisor->get();
    //     $businessEmpCode = Business::where([
    //         'b_id' => $user->emp_b_id,
    //         'b_emp_code_type' => 190,
    //     ])->pluck('b_emp_code')->first();

    //     // Ensure $businessEmpCode is not empty
    //     if ($businessEmpCode) {
    //         // Query to get the new employee code

    //         $newEmpCodeNumber = Employee::select(DB::raw("
    //                 COALESCE(
    //                     MAX(CAST(SUBSTRING(emp_code, LENGTH('$businessEmpCode') + 1) AS UNSIGNED)),
    //                     0
    //                 ) + 1 AS code_number
    //             "))->where('emp_b_id', $user->emp_b_id)
    //             ->where('emp_code', 'LIKE', "$businessEmpCode%")
    //             ->first();


    //         if ($newEmpCodeNumber && $newEmpCodeNumber->code_number) {
    //             $number = $newEmpCodeNumber->code_number;

    //             // Pad with leading zeros if less than 1000
    //             if ($number < 1000) {
    //                 $formattedNumber = str_pad($number, 3, '0', STR_PAD_LEFT);  // e.g., 5 => '005'
    //             } else {
    //                 $formattedNumber = (string)$number;  // No padding for 1000+
    //             }

    //             $newEmpCode = $businessEmpCode . $formattedNumber;
    //         } else {
    //             // First employee code
    //             $newEmpCode = $businessEmpCode . '001';
    //         }
    //     } else {
    //         // Fallback if no businessEmpCode is found
    //         $newEmpCode = null; // or a default like 'TEMP001'
    //     }
    //     return view(
    //         'admin.employees.add-employee',
    //         compact(
    //             'maritalStatus',
    //             'bloodGroupList',
    //             'staticGender',
    //             'employeeType',
    //             'BranchList',
    //             'DepartmentList',
    //             'DesignationList',
    //             'attendanceMethod',
    //             'FinalPolicyMethod',
    //             'AttendanceShiftTemplate',
    //             'countryList',
    //             'prefix',
    //             'Grade',
    //             'Role',
    //             'ShiftType',
    //             'employee', // Pass the employee data to the view
    //             'getEsicLimit',
    //             'employeeJobStatus',
    //             'qualification',
    //             'stream',
    //             'qualificationCourseType',
    //             'natureCourse',
    //             'qualificationStatus',
    //             'newEmpCode',
    //             'supervisor',
    //             'attendancePolicy',
    //             'checkInMethod',
    //             'leavePolicy',
    //             'leaveCalcBy',
    //             'previousOrganizations',
    //             'attendancePreference',
    //             'status',
    //             'existingRows',
    //             'approvalModules',
    //             'weekOffs',
    //             'emp_status',
    //             'emp_leaving',
    //             'emp_esic_reason',
    //             'emp_region',
    //             'emp_cast',
    //             'emp_projects',
    //             // 'assets',
    //             'emp_assets',
    //             'formatted',
    //             'approvalFlowmodule',
    //             'approvalFlowwmpList',
    //             'finalResult',
    //             'approvalFlowstatus'


    //         )
    //     );
    // }

    public function form($id = null)
    {

        // $employee = Employee::whereRaw('MD5(emp_id) = ?', [$id])->firstOrFail();
        $user = Auth::user();
        $maritalStatus  =  MasterTable::where('m_group', 'MARITAL_STATUS')->get();
        $bloodGroupList = MasterTable::where('m_group', 'BLOOD_GROUP')->get();
        $staticGender =  MasterTable::where('m_group', 'GENDER')->get();
        $employeeType = MasterTable::where('m_group', 'EMPLOYEE_TYPE')->get();
        $BranchList =  Branch::where('br_b_id', $user->emp_b_id)->get();
        $DepartmentList = Department::where('d_b_id', $user->emp_b_id)->get();
        $DesignationList = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $attendanceMethod = MasterTable::where('m_group', 'WORK_MODE')->get();
        $emp_status = MasterTable::where('m_group', 'STATUS')->get();
        $emp_leaving = MasterTable::where('m_group', 'Leaving')->get();
        $emp_esic_reason = MasterTable::where('m_group', 'esic')->get();
        $emp_region = MasterTable::where('m_group', 'Region')->get();
        $emp_cast = MasterTable::where('m_group', 'Caste')->get();
        $emp_projects = Project::where('ps_b_id', $user->emp_b_id)->get();
        $emp_assets = Asset::with('assetType')->where('assets_b_id', $user->emp_b_id)->whereIn('status', ['stock', 'assigned'])->get();
        $attendancePolicy = PolicyAttendance::where('ap_b_id', $user->emp_b_id)->where('ap_status', 1)->orderBy('ap_id', 'desc')->get();
        $weekOffs = $user->fh_business->fh_weekOff_policies?->pluck('pwo_name', 'pwo_id')->toArray();
        // $assets = Asset::with('assetType')->where('assets_b_id', $user->emp_b_id)->where('employee_id', $employee->emp_id)->get();


        // approval flow
        // $approvalFlowtype = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)->distinct()->pluck('eam_module_id');
        // $approvalFlowConfiguration = ApprovalFlow::where('afc_b_id', $user->emp_b_id)->get();
        // $approvalFlowConfigurationIds = $approvalFlowConfiguration->pluck('afc_approval_id');
        // $approvalFlowmodule = MasterTable::where('m_group', 'MODULE')->select('m_id', 'm_group', 'm_name')->whereIn('m_id', $approvalFlowtype)->whereIn('m_id', $approvalFlowConfigurationIds)->get();
        // $approvalFlowstatus = MasterTable::where('m_group', 'APPROVAL_STATUS')->select('m_id', 'm_group', 'm_name')->whereNotIn('m_id', [139, 140, 156, 170, 171, 175, 192])->get();
        // $approvalFlowwmpList = Employee::where('emp_b_id', $user->emp_b_id)->select('emp_id', 'emp_full_name')->get();


        $checkInMethod = collect();
        if ($attendancePolicy->first()?->ap_checkin_method_ids) {
            $methodIds = $attendancePolicy->first()?->fh_check_in_method()->pluck('m_id')->toArray();
            $checkInMethod = MasterTable::where('m_group', 'CHECKIN_METHOD')
                ->whereIn('m_id', $methodIds)
                ->get();
        }
        //$checkInMethod = MasterTable::where('m_group', 'CHECKIN_METHOD')->whereIn('m_id',$attendancePolicy->first()?->fh_check_in_method()->pluck('m_id')->toArray())->get(); //


        // ->pluck('id')->toArray();
        $FinalPolicyMethod = [];
        $AttendanceShiftTemplate = [];
        $countryList = Country::all();
        $prefix = MasterTable::where('m_group', 'PREFIX')->get();
        $Grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        $Role = Role::where('role_b_id', $user->emp_b_id)->get();
        // $ShiftType = MasterTable::where('m_group', 'SHIFT_TYPE')->get();
        $ShiftType = PolicyShiftTiming::where('pst_b_id', $user->emp_b_id)->get();
        // $AttendanceMethod

        $leavePolicy = PolicyLeave::where('pl_b_id', $user->emp_b_id)->distinct('pl_ap_id')->get();
        $leaveCalcBy = MasterTable::where('m_group', 'LEAVE_CALCULATION_BY')->get();
        $getEsicLimit = MasterTable::where('m_group', 'YESNO')->get();
        // Fetch the employee data if ID is provided
        $employee = $id ? Employee::with(
            'fh_employee_qualifications',
            'latest_salary_master_history',
            'fh_gender',
            'fh_marital_status',
            'fh_blood_group',
            'fh_employee_status',
            'fh_employee_type',
            'fh_esic_limit',
            'fh_pf_master',
            'fh_branch',
            'fh_department',
            'fh_designation',
            'fh_work_mode',
            'fh_grade',
            'fh_job_status',
            'fh_attendance_preference:m_id,m_name',
            'fh_geofencing:m_id,m_name,m_description',
            'fh_assets'
        )
            ->where('emp_b_id', $user->emp_b_id)
            ->whereRaw('md5(emp_id) = ?', [$id])
            ->first() : null;


        $empId = $employee?->emp_id;

        // ===============================
        // APPROVAL FLOW (EMPLOYEE WISE FIX)
        // ===============================
        $approvalFlowwmpList = Employee::where('emp_b_id', $user->emp_b_id)
            ->where('emp_role_id', '<>', 1)
            ->where('emp_status', 71)
            ->select('emp_id', 'emp_full_name')
            ->get();

        $approvalFlowtype = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
            ->when($empId, fn($q) => $q->where('eam_emp_id', $empId))
            ->distinct()
            ->pluck('eam_module_id');

        $approvalFlowConfiguration = ApprovalFlow::where('afc_b_id', $user->emp_b_id)->get();

        $approvalFlowWithStatuses = $approvalFlowConfiguration->map(function ($approval) {
            return [
                'afc_approval_id' => $approval->afc_approval_id,
                'status_ids' => is_array($approval->afc_approval_status_id)
                    ? $approval->afc_approval_status_id
                    : [],
            ];
        });

        $approvalFlowtypes = EmployeeApprovalMapping::with(['approvalStatuses' => function ($q) {
            // $q->orderByDesc('eas_id');
            $q->orderBy('eas_id');
        }])
            ->where('eam_b_id', $user->emp_b_id)
            ->when($empId, fn($q) => $q->where('eam_emp_id', $empId))
            ->get();

        $approvalFlowtypesandemp = $approvalFlowtypes
            ->flatMap(fn($m) => $m->approvalStatuses->map(fn($s) => [
                'eam_module_id'       => $m->eam_module_id,
                'eas_approvel_id'     => $s->eas_approvel_id,
                'eas_approvel_status' => $s->eas_approvel_status,
                'eas_id'              => $s->eas_id,
            ]))
            // ->sortByDesc('eas_id')
            ->sortBy('eas_id')
            ->groupBy('eam_module_id')
            ->map(fn($i) => $i->values());

        $approvalWiseStatus = collect($approvalFlowWithStatuses)
            ->mapWithKeys(fn($a) => [$a['afc_approval_id'] => $a['status_ids']]);

        $approvalFlowmodule = MasterTable::where('m_group', 'MODULE')
            ->whereIn('m_id', $approvalFlowtype)
            ->whereIn('m_id', $approvalFlowConfiguration->pluck('afc_approval_id'))
            ->select('m_id', 'm_group', 'm_name')
            ->get();

        $finalResult = $approvalFlowmodule->map(fn($m) => [
            'm_id'      => $m->m_id,
            'm_group'   => $m->m_group,
            'm_name'    => $m->m_name,
            'approvals' => $approvalFlowtypesandemp[$m->m_id] ?? [],
            'status_ids' => $approvalWiseStatus[$m->m_id] ?? [],
        ]);

        $approvalFlowstatus = MasterTable::where('m_group', 'APPROVAL_STATUS')
            ->whereNotIn('m_id', [139, 140, 156, 170, 171, 175, 192])
            ->select('m_id', 'm_name')
            ->get()
            ->keyBy('m_id');

        $finalResult = $finalResult->map(function ($m) use ($approvalFlowstatus) {
            $statuses = collect($m['status_ids'])
                ->map(
                    fn($id) => $approvalFlowstatus->get($id)
                        ? ['m_id' => $id, 'm_name' => $approvalFlowstatus[$id]->m_name]
                        : null
                )->filter()->values()->toArray();

            return [
                'm_id' => $m['m_id'],
                'm_group' => $m['m_group'],
                'm_name' => $m['m_name'],
                'approvals' => $m['approvals'],
                'statuses' => $statuses,
            ];
        });

        // $service_date = $employee->emp_date_of_joining; // e.g., "2024-05-18"
        $service_date = $employee ? $employee->emp_date_of_joining : null; // e.g., "2024-05-18"
        $join_date = new DateTime($service_date);
        $today = new DateTime();

        $interval = $join_date->diff($today);

        $formatted = $interval->format('%y y %m m %d d');

        $existingRows = [];
        if ($id) {
            $existingRows = EmployeeApprovalMapping::with('fh_employee_approver_manager_1', 'fh_employee_approver_manager_2')->whereRaw('md5(eam_emp_id) = ?', [$id])->get();
        }
        $approvalModules = MasterTable::where('m_group', 'MODULE')->pluck('m_name', 'm_id')->toArray();
        $employeeJobStatus = MasterTable::where('m_group', 'JOB_STATUS')->get();
        $qualificationCourseType = MasterTable::where('m_group', 'QUALIFICATION_COURSE_TYPE')->get();
        $qualification = Qualification::whereNull('qua_b_id')->orWhere('qua_b_id', $user->emp_b_id)->get();
        $stream = Stream::whereNull('stm_b_id')->orWhere('stm_b_id', $user->emp_b_id)->orderBy('stm_name')->get();
        $natureCourse = MasterTable::where('m_group', 'COURSE_TYPE')->get();
        $qualificationStatus = MasterTable::where('m_group', 'QUALIFICATION_STATUS')->get();
        $attendancePreference = MasterTable::where('m_group', 'ATTENDANCE_PREFERENCE')->pluck('m_name', 'm_id')->toArray();
        $previousOrganizations = $employee ? PreviousOrganization::where('po_emp_id', $employee->emp_id)->get() : null;
        $status = MasterTable::where('m_group', 'STATUS')->get();
        $supervisor = Employee::where('emp_b_id', $user->emp_b_id)->where('emp_status', 71);
        $supervisor = $supervisor->get();
        $businessEmpCode = Business::where([
            'b_id' => $user->emp_b_id,
            'b_emp_code_type' => 190,
        ])->pluck('b_emp_code')->first();

        // Ensure $businessEmpCode is not empty
        if ($businessEmpCode) {
            // Query to get the new employee code

            $newEmpCodeNumber = Employee::select(DB::raw("
                    COALESCE(
                        MAX(CAST(SUBSTRING(emp_code, LENGTH('$businessEmpCode') + 1) AS UNSIGNED)),
                        0
                    ) + 1 AS code_number
                "))->where('emp_b_id', $user->emp_b_id)
                ->where('emp_code', 'LIKE', "$businessEmpCode%")
                ->first();


            if ($newEmpCodeNumber && $newEmpCodeNumber->code_number) {
                $number = $newEmpCodeNumber->code_number;

                // Pad with leading zeros if less than 1000
                if ($number < 1000) {
                    $formattedNumber = str_pad($number, 3, '0', STR_PAD_LEFT);  // e.g., 5 => '005'
                } else {
                    $formattedNumber = (string)$number;  // No padding for 1000+
                }

                $newEmpCode = $businessEmpCode . $formattedNumber;
            } else {
                // First employee code
                $newEmpCode = $businessEmpCode . '001';
            }
        } else {
            // Fallback if no businessEmpCode is found
            $newEmpCode = null; // or a default like 'TEMP001'
        }
        return view(
            'admin.employees.add-employee',
            compact(
                'maritalStatus',
                'bloodGroupList',
                'staticGender',
                'employeeType',
                'BranchList',
                'DepartmentList',
                'DesignationList',
                'attendanceMethod',
                'FinalPolicyMethod',
                'AttendanceShiftTemplate',
                'countryList',
                'prefix',
                'Grade',
                'Role',
                'ShiftType',
                'employee', // Pass the employee data to the view
                'getEsicLimit',
                'employeeJobStatus',
                'qualification',
                'stream',
                'qualificationCourseType',
                'natureCourse',
                'qualificationStatus',
                'newEmpCode',
                'supervisor',
                'attendancePolicy',
                'checkInMethod',
                'leavePolicy',
                'leaveCalcBy',
                'previousOrganizations',
                'attendancePreference',
                'status',
                'existingRows',
                'approvalModules',
                'weekOffs',
                'emp_status',
                'emp_leaving',
                'emp_esic_reason',
                'emp_region',
                'emp_cast',
                'emp_projects',
                // 'assets',
                'emp_assets',
                'formatted',
                'approvalFlowmodule',
                'approvalFlowwmpList',
                'finalResult',
                'approvalFlowtypesandemp',
                'approvalFlowstatus'
            )
        );
    }

    public function checkEmployeeId(Request $request)
    {
        if ($request->has('emp_id')) {
            $user = Auth::user();
            $empCheck = Employee::where('emp_b_id', $user->emp_b_id)->where('emp_code', $request->emp_id)->first();
            if ($empCheck) {
                $EmpIdError = 'This employee id is already assigned in this business.';
                return response()->json([
                    'status' => false,
                    'message' => $EmpIdError
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => '', //'Available'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found in request'
            ]);
        }
    }

    // public function checkPhoneEmail(Request $request)
    // {
    //     if ($request->for == 0) {
    //         $user = Auth::user();
    //         $empCheck = Employee::where('emp_id', '<>', $request->primary_emp_id)->where('emp_b_id', $user->emp_b_id)->where('emp_phone', $request->id)->first(); // first check the same business then the not created the same mo.no.
    //         $empCheck2 = Employee::where('emp_id', '<>', $request->primary_emp_id)->where('emp_b_id', '<>', $user->emp_b_id)->where('emp_status', 71)->where('emp_phone', $request->id)->first(); // if the employee mo.no. assigned with the diff business and emp_status active and mo.no register then the show the error
    //         if ($empCheck || $empCheck2) {
    //             $EmpIdError = $empCheck ? 'This number is already assigned in this business.' : 'This number is already assigned in other business.';
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => $EmpIdError
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => ''
    //             ]);
    //         }
    //     } else if ($request->for == 1) {
    //         $user = Auth::user();
    //         $empCheck = Employee::where('emp_id', '<>', $request->primary_emp_id)->where('emp_b_id', $user->emp_b_id)->where('emp_email', $request->id)->first();
    //         $empCheck2 = Employee::where('emp_id', '<>', $request->primary_emp_id)->where('emp_b_id', '<>', $user->emp_b_id)->where('emp_status', 71)->where('emp_email', $request->id)->first();
    //         if ($empCheck || $empCheck2) {
    //             $EmpIdError = $empCheck ? 'This email id is already assigned in this business.' : 'This email id is already assigned in other business.';
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => $EmpIdError
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => ''
    //             ]);
    //         }
    //     } else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Employee not found in request'
    //         ]);
    //     }
    // }

    public function checkPhoneEmail(Request $request)
    {
        $input = trim($request->id);
        $type = $request->for; // 0 = phone, 1 = email

        // Check for empty input
        if (empty($input)) {
            return response()->json([
                'status' => false,
                'message' => "This field can't be empty."
            ]);
        }

        if ($type == 0) {
            // Validate phone number existence
            $exists = Employee::where('emp_phone', $input)->exists();
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'This phone number already exists.'
                ]);
            }
        } elseif ($type == 1) {
            // Validate email existence
            $exists = Employee::where('emp_email', $input)->exists();
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'This email ID already exists.'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid request type.'
            ]);
        }

        // Success — input is available
        return response()->json([
            'status' => true,
            'message' => ''
        ]);
    }



    public function saveData(Request $request)
    {

        // dd($request->all());
        $emp_primary_id = $request->emp_primary_id;
        // if (empty($emp_primary_id)) {
        //     $user = Auth::user();
        //     $b_id = $user->emp_b_id;

        //     // Get subscription
        //     $subscription = Subscription::where('business_id', $b_id)->first();
        //     if (! $subscription) {
        //         return response()->json([
        //             'status'  => false,
        //             'type'    => 'NO_SUBSCRIPTION',
        //             'message' => 'No active subscription found.',
        //         ], 403);
        //     }

        //     $slabData = PlanPriceSlab::find($subscription->price_slab_id);
        //     if (! $slabData || ! $slabData->max_employees) {
        //         return response()->json([
        //             'status'  => false,
        //             'type'    => 'INVALID_PLAN',
        //             'message' => 'Invalid subscription plan configuration.',
        //         ], 403);
        //     }

        //     $maxEmployees   = (int) $slabData->max_employees;
        //     $totalEmployees = Employee::where('emp_b_id', $b_id)->where('emp_status', 71)->where('emp_role_id', '!=', 1)->count();

        //     if ($totalEmployees >= $maxEmployees) {
        //         return response()->json([
        //             'status'  => false,
        //             'type'    => 'LIMIT_EXCEEDED',
        //             'message' => 'Please upgrade your plan to add more employees.',
        //         ], 403);
        //     }
        // }

        $user = Auth::user();
        $b_id = $user->emp_b_id;

        // Get subscription
        // $subscription = Subscription::where('business_id', $b_id)->first();
        // if (! $subscription) {
        //     return response()->json([
        //         'status'  => false,
        //         'type'    => 'NO_SUBSCRIPTION',
        //         'message' => 'No active subscription found.',
        //     ], 403);
        // }

        // $slabData = PlanPriceSlab::find($subscription->price_slab_id);
        // if (! $slabData || ! $slabData->max_employees) {
        //     return response()->json([
        //         'status'  => false,
        //         'type'    => 'INVALID_PLAN',
        //         'message' => 'Invalid subscription plan configuration.',
        //     ], 403);
        // }

        // $maxEmployees   = (int) $slabData->max_employees;
        // $totalEmployees = Employee::where('emp_b_id', $b_id)->where('emp_status', 71)->where('emp_role_id', '!=', 1)->count();

        // if ($totalEmployees >= $maxEmployees) {
        //     return response()->json([
        //         'status'  => false,
        //         'type'    => 'LIMIT_EXCEEDED',
        //         'message' => 'Please upgrade your plan to add more employees.',
        //     ], 403);
        // }

        $empCheck = Employee::where('emp_id', $request->emp_id)->first();
        $profile_url =  null;
        if ($empCheck && $request->action == 'ajaxcapsave') {
            if ($request->hasFile('emp_profile_photo')) {

                $image = $request->file('emp_profile_photo');
                $imageName = time() . '_' . md5($image->getClientOriginalName()) . '.' . $image->extension();
                $bucket = 'fixhr-employee-profiles';
                $imagePath = $empCheck->fh_business->b_unique_id . '/' . $imageName;
                $isFaceDetectionActive = $this->user->fh_business->is_face_detection_active;
                $shouldStoreOnS3 = env('STORE_ON_S3');

                // Delete old image before uploading new one
                $oldProfilePhoto = $empCheck->emp_profile_photo;

                if ($oldProfilePhoto) {
                    if ($shouldStoreOnS3) {
                        $parsedUrl = parse_url($oldProfilePhoto, PHP_URL_PATH);
                        $key = ltrim($parsedUrl, '/');

                        // Delete from S3 bucket
                        $this->awsHelper->deleteFileFromS3($bucket, $key);
                    } else {
                        $parsedUrl = parse_url($oldProfilePhoto, PHP_URL_PATH);
                        $relativePath = ltrim($parsedUrl, '/');
                        $fullPath = public_path($relativePath);

                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                    }
                }

                // Upload and index face on AWS Rekognition (if required)
                if (is_null($empCheck->emp_rekognition_id) && $image && in_array(316, $empCheck->emp_checkin_method_id)) {
                    $awsResponse = app('App\Http\Controllers\FaceController')->uploadAndIndexFace($empCheck, $image, $imageName);
                }

                // Upload image to AWS S3 if enabled
                if ($shouldStoreOnS3) {
                    if (isset($awsResponse['result']['profile_s3_url']) && $awsResponse['result']['profile_s3_url']) {
                        $profile_url = $awsResponse['result']['profile_s3_url'];
                    } else {
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $image);
                        if (!empty($uploadResult['status'])) {
                            $profile_url = $uploadResult['ObjectURL'];
                        }
                    }
                } else {
                    // Store image in local server directory
                    $profile_url = CommonUtils::uploadFiles($request, 'emp_profile_photo', 'employee_profile/' . $empCheck->fh_business->b_unique_id, ['prefix' => 'emp_profile']);
                    $profile_url = isset($profile_url[0]) ? url($profile_url[0]) : NULL;
                }
            }

            $empCheck->update([
                'emp_full_name' => DB::raw("CONCAT(emp_fname, ' ', COALESCE(emp_mname, ''), ' ', emp_lname)"),
                'emp_profile_photo' => $profile_url,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully.'
            ]);
        }

        DB::beginTransaction(); // Begin transaction
        try {
            $empDateOfJoining = $request->input('emp_date_of_joining');
            $empProbationPeriod = (int) $request->input('emp_probation_period');
            $lastProbationDate = Carbon::parse($empDateOfJoining)->addDays($empProbationPeriod);
            $checkInMethods = $request->input('emp_checkin_method_id');
            $checkInMethods = array_map('intval', explode(',', $checkInMethods));


            $user = Auth::user();
            // Define validation rules
            $rules = [
                'emp_code' => [
                    'required',
                    Rule::unique('employees')->where(function ($query) use ($user, $request) {
                        return $query->where('emp_b_id', $user->emp_b_id)
                            ->where('emp_id', '!=', $request->emp_primary_id);
                    }),
                ],
                'prefix' => 'required',
                'emp_fname' => 'required',
                'emp_phone' => ['required', 'numeric', 'digits_between:10,15'],
                'emp_email' => ['required', 'email'],
                'emp_dob' => 'required|date',
                'emp_gender_id' => 'required',
                'emp_status' => 'required',
                'emp_type_id' => 'required',
                'emp_date_of_joining' => 'required',
                'emp_job_status' => 'required',
                'emp_br_id' => 'required',
                'emp_d_id' => 'required',
                'emp_dg_id' => 'required',
                'emp_grade_id' => 'required',
                'emp_role_id' => 'required',
                // 'emp_sap_budget_code' => 'required',
                'emp_shift_type_id' => 'required',
                'emp_work_mode_id' => 'required',
                'emp_checkin_method_id' => 'required',
                // 'emp_account_code' => 'required',
                'aadharUpload' => 'nullable',
                'drivingUpload' => 'nullable',
                'passbookUpload' => 'nullable',
                'passportUpload' => 'nullable',
                'voterUpload' => 'nullable',
                'emp_permanent_address' => 'required',
                // 'emp_permanent_longitude' => 'required',
                // 'emp_permanent_latitude' => 'required',
                'emp_temporary_pin_code' => 'required',
                'regex:/^\d{6}$/',
                'emp_permanent_pin_code' => 'required',
                'regex:/^\d{6}$/',
                'emp_temporary_address' => 'required',
                // 'emp_temporary_longitude' => 'required',
                // 'emp_temporary_latitude' => 'required',
                'emp_ap_id' => 'required',
                'emp_pl_id' => 'required',
                'emp_pwo_id' => 'required',
                'emp_allow_joining_leave' => 'required',
                'emp_profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            ]; //

            if ($request->emp_profile_photo == 'undefined' || $request->emp_profile_photo == 'null') {
                unset($rules['emp_profile_photo']);
            }
            $messages = [
                'emp_profile_photo.required' => 'The profile photo is required.',
                'emp_profile_photo.image' => 'The file must be an image.',
                'emp_profile_photo.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
                'emp_profile_photo.max' => 'The image size must not exceed 1 MB.' // This will match with proper max rule
            ];

            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422); // Return validation errors
            }



            // Define custom validation messages

            // Case 1: Check if phone number is already assigned within the same business
            $sameBusinessPhone = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_phone', $request->emp_phone)
                ->where('emp_id', '<>', $request->emp_primary_id)
                ->exists();

            if ($sameBusinessPhone) {
                return response()->json([
                    'status' => false,
                    'message' => 'This phone number is already assigned within the same business.'
                ]); // HTT Unprocessable Entity for validation errors
            }

            // Case 2: Check if phone number is assigned to another business with active status
            $otherBusinessPhone = Employee::where('emp_b_id', '<>', $user->emp_b_id)
                ->where('emp_phone', $request->emp_phone)
                ->where('emp_status', 71) // Active status
                ->exists();

            if ($otherBusinessPhone) {
                return response()->json([
                    'status' => false,
                    'message' => 'This phone number is assigned to another business and the employee is active.'
                ]);
            }

            // Case 1: Check if phone number is already assigned within the same business
            $sameBusinessEmail = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_email', $request->emp_email)
                ->where('emp_id', '<>', $request->emp_primary_id)
                ->exists();

            if ($sameBusinessEmail) {
                return response()->json([
                    'status' => false,
                    'message' => 'This email is already assigned within the same business.'
                ]); // HTT Unprocessable Entity for validation errors
            }

            // Case 2: Check if phone number is assigned to another business with active status
            $otherBusinessEmail = Employee::where('emp_b_id', '<>', $user->emp_b_id)
                ->where('emp_email', $request->emp_email)
                ->where('emp_status', 71) // Active status
                ->exists();

            if ($otherBusinessEmail) {
                return response()->json([
                    'status' => false,
                    'message' => 'This email is assigned to another business and the employee is active.'
                ]);
            }


            $sectionID = $request->id;


            // $imageName = CommonUtils::uploadFiles($request, 'image', 'BusinessLogo/' . $business->b_unique_id, ['prefix' => 'Logo', 'ref_file' => $business->b_logo, 'isArray' => false]);

            $employeeMaxId = Employee::max('emp_id');
            $employeeNextId = $employeeMaxId + 1;
            // Define the document uploads array
            $existingFiles = json_decode(Employee::where('emp_id', $request->emp_primary_id)->value('emp_documents_ref_file'));
            $existingAadhaarFiles = isset($existingFiles->aadharUpload) ? $existingFiles->aadharUpload : null;
            $existingDrivingLicenseFiles = isset($existingFiles->drivingUpload) ? $existingFiles->drivingUpload : null;
            $existingPassbookFiles = isset($existingFiles->passbookUpload) ? $existingFiles->passbookUpload : null;
            $existingPassportFiles = isset($existingFiles->passportUpload) ? $existingFiles->passportUpload : null;
            $existingVoterIdFiles = isset($existingFiles->voterUpload) ? $existingFiles->voterUpload : null;
            $existingPanFiles = isset($existingFiles->panUpload) ? $existingFiles->panUpload : null;

            $docs = [
                'aadharUpload' => $request->file('aadharUpload')
                    ? CommonUtils::uploadFiles($request, 'aadharUpload', 'EmployeeDocs/' . ($request->emp_primary_id ?? $employeeNextId), ['prefix' => 'Aadhaar', 'ref_file' => $existingAadhaarFiles, 'isArray' => false])
                    : $existingAadhaarFiles,

                'drivingUpload' => $request->file('drivingUpload')
                    ? CommonUtils::uploadFiles($request, 'drivingUpload', 'EmployeeDocs/' . ($request->emp_primary_id ?? $employeeNextId), ['prefix' => 'License', 'ref_file' => $existingDrivingLicenseFiles, 'isArray' => false])
                    : $existingDrivingLicenseFiles,

                'passbookUpload' => $request->file('passbookUpload')
                    ? CommonUtils::uploadFiles($request, 'passbookUpload', 'EmployeeDocs/' . ($request->emp_primary_id ?? $employeeNextId), ['prefix' => 'PassBook', 'ref_file' => $existingPassbookFiles, 'isArray' => false])
                    : $existingPassbookFiles,

                'passportUpload' => $request->file('passportUpload')
                    ? CommonUtils::uploadFiles($request, 'passportUpload', 'EmployeeDocs/' . ($request->emp_primary_id ?? $employeeNextId), ['prefix' => 'Passport', 'ref_file' => $existingPassportFiles, 'isArray' => false])
                    : $existingPassportFiles,

                'voterUpload' => $request->file('voterUpload')
                    ? CommonUtils::uploadFiles($request, 'voterUpload', 'EmployeeDocs/' . ($request->emp_primary_id ?? $employeeNextId), ['prefix' => 'VoterId', 'ref_file' => $existingVoterIdFiles, 'isArray' => false])
                    : $existingVoterIdFiles,

                'panUpload' => $request->file('panUpload')
                    ? CommonUtils::uploadFiles($request, 'panUpload', 'EmployeeDocs/' . ($request->emp_primary_id ?? $employeeNextId), ['prefix' => 'Pan', 'ref_file' => $existingPanFiles, 'isArray' => false])
                    : $existingPanFiles,
            ];
            // Strip the directory path from the stored file paths
            // $docs = array_map(function ($path) {
            //     return $path ? basename($path) : null;
            // }, $docs);
            // Add the document details
            $docs['aadhar_number'] = $request->aadhar_number ?? 'N/A';
            $docs['account_number'] = $request->account_number ?? 'N/A';
            $docs['driving_license_number'] = $request->driving_license_number ?? 'N/A';
            $docs['voter_id_number'] = $request->voter_id_number ?? 'N/A';
            $docs['passport_number'] = $request->passport_number ?? 'N/A';
            $docs['pan_number'] = $request->pan_number ?? 'N/A';


            $employee = Employee::find($request->emp_primary_id);

            if ($employee == null) {

                $aadharPath = $this->handleUpload($request, 'upload_aadhar') ?? 'N/A';
                $drivingPath = $this->handleUpload($request, 'upload_drivng_license') ?? 'N/A';
                $voterPath = $this->handleUpload($request, 'upload_voter_id') ?? 'N/A';
                $passbookPath = $this->handleUpload($request, 'upload_passbook') ?? 'N/A';
                $passportPath = $this->handleUpload($request, 'upload_passport') ?? 'N/A';
                $panPath = $this->handleUpload($request, 'upload_pan') ?? 'N/A';
            } else {

                $aadharPath = $this->handleUpload($request, 'upload_aadhar') ?? $employee->emp_aadhar_file;
                $drivingPath = $this->handleUpload($request, 'upload_drivng_license') ?? $employee->emp_driving_license_file;
                $voterPath = $this->handleUpload($request, 'upload_voter_id') ?? $employee->emp_voter_id_file;
                $passbookPath = $this->handleUpload($request, 'upload_passbook') ?? $employee->emp_passbook_file;
                $passportPath = $this->handleUpload($request, 'upload_passport') ?? $employee->emp_passport_file;
                $panPath = $this->handleUpload($request, 'upload_pan') ?? $employee->emp_pan_file;
            }

            // Handle file uploads before saving the employee
            // Update or create the employee record
            $employee = Employee::updateOrCreate(
                ['emp_id' => $request->emp_primary_id, 'emp_b_id' => $user->emp_b_id],
                [
                    'emp_status' => $request->input('emp_status'),
                    'emp_b_id' => $user->emp_b_id,
                    'emp_br_id' => $request->input('emp_br_id'),
                    'emp_sap_budget_code' => $request->input('emp_sap_budget_code'),
                    'emp_code' => $request->input('emp_code'),
                    'emp_prefix' => $request->input('prefix'),
                    'emp_fname' => $request->input('emp_fname'),
                    'emp_mname' => $request->input('emp_mname'),
                    'emp_lname' => $request->input('emp_lname'),
                    'emp_d_id' => $request->input('emp_d_id'),
                    'emp_dg_id' => $request->input('emp_dg_id'),
                    'emp_role_id' => $request->input('emp_role_id'),
                    'emp_supervisor_id' => $request->input('emp_supervisor_id'),
                    'emp_type_id' => $request->input('emp_type_id'),
                    'emp_contractual_type_id' => null, // $request->input('emp_contractual_type_id'),
                    'emp_phone' => $request->input('emp_phone'),
                    'emp_email' => $request->input('emp_email'),
                    'emp_dob' => $request->input('emp_dob'),
                    'emp_date_of_joining' => $request->input('emp_date_of_joining'),
                    'emp_job_status' => $request->input('emp_job_status'),
                    'emp_group_date_of_joining' => $request->input('emp_group_date_of_joining'),
                    'emp_date_of_gratuity' => $request->input('emp_date_of_gratuity'),
                    'emp_date_of_transfer' => $request->input('emp_date_of_transfer'),
                    'emp_date_of_expected_confirmation' => $request->input('emp_date_of_expected_confirmation'),
                    'emp_date_of_confirmation' => $request->input('emp_date_of_confirmation'),
                    'emp_date_of_pay_structure' => $request->input('emp_date_of_pay_structure'),
                    'emp_probation_period' => $request->input('emp_probation_period'),
                    'emp_probation_last_date' => $lastProbationDate->format('Y-m-d'),
                    'emp_gender_id' => $request->input('emp_gender_id'),
                    'emp_marital_status_id' => $request->input('emp_marital_status_id'),
                    'emp_ap_id' => $request->input('emp_ap_id'),
                    'emp_pl_id' => $request->input('emp_pl_id'),
                    // 'emp_cast_id' => $request->input('emp_cast_id'),
                    'emp_blood_group_id' => $request->input('emp_blood_group_id'),
                    // 'emp_gov_doc_type_id' => $request->input('emp_gov_doc_type_id'),
                    // 'emp_gov_doc_type_number' => $request->input('emp_gov_doc_type_number'),
                    // 'emp_nationality' => $request->input('emp_nationality'),
                    // 'emp_religion_id' => $request->input('emp_religion_id'),
                    // 'emp_address' => $request->input('emp_address'),
                    'emp_temporary_pin_code' => $request->input('emp_temporary_pin_code'),
                    // 'emp_permanent_address' => $request->input('emp_permanent_address')
                    'emp_permanent_pin_code' => $request->input('emp_permanent_pin_code'),
                    'emp_shift_type_id' => $request->input('emp_shift_type_id'),
                    // 'emp_assign_shift_start_time' => $request->input('emp_assign_shift_start_time'),
                    // 'emp_assign_shift_end_time' => $request->input('emp_assign_shift_end_time'),
                    // 'emp_reporting_manager_id' => $request->input('emp_reporting_manager_id'),
                    // 'emp_imei_no' => $request->input('emp_imei_no'),
                    'emp_work_mode_id' => $request->input('emp_work_mode_id'),
                    'emp_checkin_method_id' => $checkInMethods,
                    'emp_grade_id' => $request->input('emp_grade_id'),
                    'emp_bank_ifsc_code' => $request->input('emp_bank_ifsc_code'),
                    'emp_account_code' => $request->input('emp_account_code'),
                    'emp_bank_name' => $request->input('emp_bank_name'),
                    'emp_bank_branch_name' => $request->input('emp_bank_branch_name'),
                    'emp_bank_account_no' => $request->input('emp_bank_account_no'),
                    'emp_bank_branch_code' => $request->input('emp_bank_branch_code'),
                    'emp_bank_micr_code' => $request->input('emp_bank_micr_code'),
                    // 'emp_bank_address_line1' => $request->input('emp_bank_address_line1'),
                    // 'emp_bank_address_line2' => $request->input('emp_bank_address_line2'),
                    'emp_pf_no' => $request->input('emp_pf_no'),
                    'emp_pf_trust_code' => $request->input('emp_pf_trust_code'),
                    'emp_pf_found_member' => $request->input('emp_pf_found_member'),
                    'emp_pf_universal_ac_no' => $request->input('emp_pf_universal_ac_no'),
                    'emp_vpf_percentage' => $request->input('vpfPercentage'),
                    'emp_pf_joining_date' => $request->input('emp_pf_joining_date'),
                    'emp_pf_leaving_date' => $request->input('emp_pf_leaving_date'),
                    'emp_pr_leaving_reason' => $request->input('emp_pr_leaving_reason'),
                    // 'emp_pf_joining_no' => $request->input('emp_pf_joining_no'),
                    'emp_lwf_no' => $request->input('emp_lwf_no'),
                    // 'emp_eps_no' => $request->input('emp_eps_no'),
                    'emp_esic_no' => $request->input('emp_esic_no'),
                    'emp_esic_joining_date' => $request->input('emp_esic_joining_date'),
                    'emp_esic_leaving_date' => $request->input('emp_esic_leaving_date'),
                    'emp_esic_leaving_reason' => $request->input('emp_esic_leaving_reason'),
                    'emp_esic_dispensary' => $request->input('emp_esic_dispensary'),
                    'emp_esic_limit' => $request->input('emp_esic_limit'),
                    'emp_is_pf_enabled' => $request->input('emp_is_pf_enabled'),
                    'emp_year_of_service' => $request->input('emp_year_of_service'),
                    'emp_retirement_date' => $request->input('emp_retirement_date'),
                    'emp_separation_submit_date' => $request->input('emp_separation_submit_date'),
                    'emp_expected_leaving_date' => $request->input('emp_expected_leaving_date'),
                    'emp_leaving_date_as_per_notice_period' => $request->input('emp_leaving_date_as_per_notice_period'),
                    'emp_notice_period_req_days' => $request->input('emp_notice_period_req_days'),
                    'emp_leaving_reason' => $request->input('emp_leaving_reason'),
                    'emp_leave_date' => $request->input('emp_leave_date'),
                    'emp_notice_period_serve_days' => $request->input('emp_notice_period_serve_days'),
                    'emp_settlement_from_date' => $request->input('emp_settlement_from_date'),
                    'emp_final_settlement_date' => $request->input('emp_final_settlement_date'),
                    'emp_notice_period_shortfall_days' => $request->input('noticePeriodShortfallDays'),
                    'emp_exit_interview_date' => $request->input('emp_exit_interview_date'),
                    'emp_last_working_date' => $request->input('emp_last_working_date'),
                    'emp_remark' => $request->input('emp_remark'),
                    'emp_notice_period_day_for_employer' => $request->input('employerNoticePeriod'),
                    'emp_notice_period_day_for_employee' => $request->input('employeeNoticePeriod'),
                    'emp_is_geofencing_active' => $request->input('emp_is_geofencing_active'),
                    'emp_assign_geo_branch' => $request->input('assign_geo_branch'),
                    'emp_offline_status' => (int)$request->input('emp_offline_status', 0),
                    'is_approval_manager' => (int)$request->input('is_approval_manager'),

                    // Map
                    'emp_permanent_address' => $request->input('emp_permanent_address'),
                    'emp_permanent_longitude' => $request->input('emp_permanent_longitude'),
                    'emp_permanent_latitude' => $request->input('emp_permanent_latitude'),
                    'emp_temporary_address' => $request->input('emp_temporary_address'),
                    'emp_temporary_longitude' => $request->input('emp_temporary_longitude'),
                    'emp_temporary_latitude' => $request->input('emp_temporary_latitude'),

                    'emp_documents_ref_file' => json_encode($docs),

                    'emp_allow_joining_leave' => $request->input('emp_allow_joining_leave'),
                    'emp_joining_leave_calc_type' => $request->input('emp_joining_leave_calc_type'),
                    'emp_joining_leave_before_date' => $request->input('emp_joining_leave_before_date'),
                    'emp_allow_probation_leave' => $request->input('emp_allow_probation_leave'),

                    'emp_attendance_preference' => $request->input('emp_attendance_preference'),
                    'emp_is_temporary_add_same' => $request->emp_is_temporary_add_same == 'on' ?  1 : 0,
                    'emp_pwo_id' => $request->input('emp_pwo_id'),


                    'emp_group_insured_by' => $request->input('emp_group_insured_by'),
                    'emp_group_insurance_no' => $request->input('emp_group_insurance_no'),
                    'emp_group_insurance_start_date' => $request->input('emp_group_insurance_start_date'),
                    'emp_group_insurance_till_date' => $request->input('emp_group_insurance_till_date'),

                    'emp_nationality' => $request->input('emp_nationality'),
                    'emp_category_id' => $request->input('emp_category'),
                    'emp_religion' => $request->input('emp_religion'),
                    'emp_body_mark' => $request->input('emp_body_mark'),



                    'emp_official_email' => $request->input('emp_official_email'),
                    'emp_official_contact' => $request->input('emp_official_contact'),
                    'emp_emergency_contact' => $request->input('emp_emergency_contact'),
                    'emp_emergency_relation' => $request->input('emp_emergency_relation'),

                    'emp_salary_account_code' => $request->input('emp_salary_accountCode'),
                    'emp_salary_bank_ifsc_code' => $request->input('emp_salary_ifsc'),
                    'emp_salary_bank_name' => $request->input('emp_salary_bankName'),
                    'emp_salary_bank_branch_name' => $request->input('emp_salary_branchName'),
                    'emp_salary_bank_micr_code' => $request->input('emp_salary_micr'),
                    'emp_salary_bank_branch_code' => $request->input('emp_salary_branch_code'),
                    'emp_salary_bank_account_no' => $request->input('emp_salary_bank_account_number'),

                    'emp_profit_center' => $request->input('profitCenter'),
                    'emp_cost_center' => $request->input('costCenter'),
                    'emp_region_id' => $request->input('assignedRegion'),
                    'emp_project_id' => $request->input('emp_project_id'),
                    'emp_assets_id' => $request->input('emp_assets_id'),

                    'emp_is_geowork_active' => $request->input('emp_is_geowork_active'),


                    'emp_aadhar_number' => $request->input('aadhar_number'),
                    'emp_driving_license_number' => $request->input('driving_license_number'),
                    'emp_voter_id_number' => $request->input('voter_id_number'),
                    'emp_passport_number' => $request->input('passport_number'),
                    'emp_account_number' => $request->input('account_number'),
                    'emp_pan_number' => $request->input('pan_number'),

                    'emp_is_eps_enabled' => $request->input('emp_is_eps_enabled'),
                    'emp_paymentmode' => $request->input('payment'),
                    'emp_accountpurpose' => $request->input('account_type'),

                    'emp_drivng_license_valid' => $request->input('emp_drivng_license_valid'),
                    'emp_passport_valid' => $request->input('emp_passport_valid'),

                    // Document Uplode
                    'emp_aadhar_file'              => $aadharPath ?? null,
                    'emp_driving_license_file'     => $drivingPath ?? null,
                    'emp_voter_id_file'            => $voterPath ?? null,
                    'emp_passbook_file'            => $passbookPath ?? null,
                    'emp_passport_file'            => $passportPath ?? null,
                    'emp_pan_file'                 => $panPath ?? null,

                ]
            );

            // Employee is submit Sepration

            $subscription = Subscription::where('business_id', $user->emp_b_id)->first();
            $plan_id = $subscription->plan_id;

            // Get menu_ids from fh_plan_web_menu
            $menuIds = DB::table('plan_web_menu')
                ->where('plan_id', $plan_id)
                ->pluck('menu_id');

            $menuIdsArray = $menuIds->toArray();

            $fnf_id = 548;

            // dd($menuIdsArray,$fnf_id);

            // Check condition
            if (in_array($fnf_id, $menuIdsArray)) {

                if ($request->filled('emp_last_working_date') || $request->filled('emp_separation_submit_date')) {

                    $user = Auth::user();
                    $emp_b_id = $user->emp_b_id;
                    $business_name = Business::where('b_id', $emp_b_id)->first();
                    $business_name = $business_name->b_name;
                    $words = explode(' ', $business_name);
                    $initials = '';
                    foreach ($words as $word) {
                        $initials .= strtoupper(substr($word, 0, 1));
                    }

                    // Get current month and year
                    $month = Carbon::now()->format('m');
                    $year = Carbon::now()->format('Y');
                    $count = EmployeeExitRequest::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->count() + 1;
                    $serial = str_pad($count, 4, '0', STR_PAD_LEFT);
                    $fnfReference = "{$initials}/{$month}/{$year}/{$serial}";




                    $employeeId = $request->emp_primary_id;
                    $businessId = $user->emp_b_id;
                    $names = [
                        'Admin Review',
                        'Finance Review',
                        'HR Review',
                        'Manager Review'
                    ];

                    $masterData = MasterTable::where('m_group', 'MODULE')
                        ->where('m_type', 'FNF')
                        ->whereIn('m_name', $names)
                        ->pluck('m_id', 'm_name')
                        ->toArray();

                    // Module IDs
                    $managerReviewId = $masterData['Manager Review'] ?? null;

                    // NEW: Try to get employee-wise approval mapping first
                    $approvalMapping = \App\Helpers\ApprovalHelper::getApprovalMapping($employee->emp_b_id, $employee->emp_id, $managerReviewId);

                    // dd($user->emp_b_id, $user->emp_id, $approvalMapping);
                    $approvalEmpIds = [];
                    $amId = null;

                    if ($approvalMapping) {
                        // Employee-wise mapping exists, get approver emp_ids
                        $approvalEmpIds = \App\Helpers\ApprovalHelper::getApprovalArray($approvalMapping);
                        // $amId = null; // Store mapping's am_id if necessary (adjust as per actual column)

                        // dd($approvalEmpIds);
                    } else {
                        // Fallback to hierarchy: get RuleCriteria and processApprovers
                        $ruleCriteria = RuleCriterion::with('fh_approval_module')
                            ->where('rc_b_id', $user->emp_b_id)
                            ->where('rc_condition_option_id', 140)
                            ->whereHas('fh_approval_module', function ($query) {
                                $query->where('am_module_id', 5888)
                                    ->where('am_status', 1);
                            })->first();

                        // dd($ruleCriteria);




                        $processApprovers = [];
                        $emp_d_id = $user->emp_d_id;
                        if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                            $processApprovers = $ruleCriteria->fh_approval_module
                                ->filteredProcessApprovers($emp_d_id)
                                ->get();
                        }

                        if (!empty($processApprovers)) {
                            $amId = $ruleCriteria->rc_am_id;
                            foreach ($processApprovers as $pa) {
                                if ($pa->pa_emp_id) {
                                    $approvalEmpIds[] = $pa->pa_emp_id;
                                }
                            }
                        } else {
                            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for FNF module, contact administration.']);
                        }
                    }

                    $data = [
                        'er_exit_type_id'       => $request->emp_leaving_reason,
                        'er_reason'             => $request->emp_remark,
                        'er_resignation_date'   => $request->emp_separation_submit_date,
                        'er_notice_period_days' => $request->emp_notice_period_req_days,
                        'er_last_working_day'   => $request->emp_last_working_date,
                        'er_manager_status'     => 'PENDING',
                        'er_hr_status'          => 'PENDING',
                        'er_overall_status'     => 'RESIGNATION_SUBMITTED',
                        'er_remark'             => $request->emp_remark,
                        'er_updated_by'         => $user->emp_id,
                        'er_ref_no'         => $fnfReference,

                        'er_am_id'           => $amId,
                        'er_module_id'       => $managerReviewId,
                        'er_status'          => 140,
                        'er_stage_completed' => 0,
                        'er_module_stage'    => 1,
                        'er_next_approver'   => 1,

                    ];

                    try {
                        $existingExit = EmployeeExitRequest::where('er_emp_id', $employeeId)
                            ->where('er_b_id', $businessId)
                            ->whereNotIn('er_overall_status', ['RELIEVED', 'REJECTED'])
                            ->first();

                        if ($existingExit) {
                            $existingExit->update($data);

                            Log::info('Employee exit request updated', [
                                'employee_id' => $employeeId,
                                'business_id' => $businessId,
                                'updated_by'  => $user->emp_id,
                                'timestamp'   => now()
                            ]);

                            // return back()->with('success', 'Exit request updated successfully');
                        } else {
                            EmployeeExitRequest::create(array_merge($data, [
                                'er_emp_id'     => $employeeId,
                                'er_b_id'       => $businessId,
                                'er_created_by' => $user->emp_id
                            ]));

                            Log::info('Employee exit request created', [
                                'employee_id' => $employeeId,
                                'business_id' => $businessId,
                                'created_by'  => $user->emp_id,
                                'timestamp'   => now()
                            ]);
                        }

                        // Get Employee Details
                        $employee = Employee::with('fh_department', 'fh_designation')
                            ->where('emp_b_id', $b_id)
                            ->where('emp_id', $employeeId)
                            ->firstOrFail();

                        // Get Manager Details
                        $manager = Employee::with('fh_department', 'fh_designation')
                            ->where('emp_b_id', $b_id)
                            ->where('emp_id', $employee->emp_supervisor_id)
                            ->first();

                        if (!$manager) {
                            return response()->json([
                                'status'  => false,
                                'message' => 'Manager not found'
                            ]);
                        }

                        // Get all required mail templates at once
                        $templates = MailTemplate::where('mt_b_id', $b_id)
                            ->where('mt_is_enabled', 1)
                            ->whereIn('mt_id', [32, 33, 39])
                            ->get()
                            ->keyBy('mt_id');

                        $sendEmail = function ($email, $subject, $body) {
                            try {
                                Mail::html($body, function ($message) use ($email, $subject) {
                                    $message->to($email)->subject($subject);
                                });
                            } catch (\Exception $e) {
                                Log::error("Email failed for {$email}: " . $e->getMessage());
                            }
                        };

                        $replace = function ($body, $placeholders) {
                            return str_replace(
                                array_keys($placeholders),
                                array_values($placeholders),
                                $body
                            );
                        };

                        $formatDate = function ($date) {
                            return !empty($date)
                                ? \Carbon\Carbon::parse($date)->format('d-M-Y')
                                : '';
                        };

                        // Employee Email
                        if ($templates->has(32) && !empty($employee->emp_email)) {

                            $placeholders = [
                                '[Employee Name]'   => $employee->emp_full_name ?? '',
                                '[Emp Code]'        => $employee->emp_code ?? '',
                                '[Submission Date]' => $formatDate($employee->emp_separation_submit_date),
                                '[Designation]'     => $employee->fh_designation->dg_name ?? '',
                                '[Department]'      => $employee->fh_department->d_name ?? '',
                                '[Effective LWD]'   => !empty($employee->emp_last_working_date)
                                    ? $formatDate($employee->emp_last_working_date)
                                    : 'Pending Approval',
                            ];

                            $body = $replace($templates[32]->mt_body, $placeholders);

                            $subject = $replace(
                                $templates[32]->mt_title ?? 'Resignation Submitted',
                                $placeholders
                            );

                            $sendEmail(
                                $employee->emp_email,
                                $subject,
                                $body
                            );
                        }

                        // Manager Email
                        if ($templates->has(33) && !empty($manager->emp_email)) {

                            $placeholders = [
                                '[Manager Name]'    => $manager->emp_full_name ?? '',
                                '[Employee Name]'   => $employee->emp_full_name ?? '',
                                '[Emp Code]'        => $employee->emp_code ?? '',
                                '[Submission Date]' => $formatDate($employee->emp_separation_submit_date),
                                '[Designation]'     => $employee->fh_designation->dg_name ?? '',
                                '[Department]'      => $employee->fh_department->d_name ?? '',
                                '[Effective LWD]'   => !empty($employee->emp_last_working_date)
                                    ? $formatDate($employee->emp_last_working_date)
                                    : 'Not Specified',
                            ];

                            $body = $replace($templates[33]->mt_body, $placeholders);

                            $subject = $replace(
                                $templates[33]->mt_title ?? 'Approval Request',
                                $placeholders
                            );

                            $sendEmail(
                                $manager->emp_email,
                                $subject,
                                $body
                            );
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing employee exit request', [
                            'employee_id' => $employeeId,
                            'business_id' => $businessId,
                            'error'       => $e->getMessage(),
                            'timestamp'   => now()
                        ]);
                    }
                }
            }



            $user = Auth::user();
            $emp_id = $user->emp_id;
            $businessId = $user->emp_b_id;

            // $assetIds = $request->input('emp_assets_id');

            // if (!empty($assetIds) && is_array($assetIds)) {
            //     $assets = Asset::whereIn('id', $assetIds)->get();

            //     foreach ($assets as $asset) {
            //         // 1. Asset update
            //         $asset->update([
            //             'status'      => 'assigned',
            //             'employee_id' => $employee->emp_id,
            //             'assigned_at' => now(),
            //         ]);

            //         // 2. AssetHistory entry
            //         AssetHistory::create([
            //             'asset_id'     => $asset->id,
            //             'employee_id'  => $employee->emp_id,
            //             'assets_b_id'  => $businessId,
            //             'user_id'      => $emp_id,
            //             'action'       => 'assigned',
            //             'from_status'  => 'stock',
            //             'to_status'    => 'assigned',
            //             'notes'        => $request->remark ?: "Asset assigned to {$employee->emp_full_name}",
            //         ]);
            //     }
            // } else {
            //     $assets = Asset::where('employee_id', $employee->emp_id)->get();

            //     foreach ($assets as $asset) {
            //         // 1. Asset update => nullify
            //         $asset->update([
            //             'status'      => 'stock',
            //             'employee_id' => null,
            //             'assigned_at' => null,
            //         ]);

            //         // 2. AssetHistory entry => to_status = Stock
            //         AssetHistory::create([
            //             'asset_id'     => $asset->id,
            //             'employee_id'  => $employee->emp_id,
            //             'assets_b_id'  => $businessId,
            //             'user_id'      => $emp_id,
            //             'action'       => 'unassigned',
            //             'from_status'  => 'assigned',
            //             'to_status'    => 'stock',
            //             'notes'        => $request->remark ?: "Asset unassigned from {$employee->emp_full_name}",
            //         ]);
            //     }
            // }


            // $profile_url =  null;
            if ($request->hasFile('emp_profile_photo')) {

                $image = $request->file('emp_profile_photo');
                $imageName = time() . '_' . md5($image->getClientOriginalName()) . '.' . $image->extension();
                $bucket = 'fixhr-employee-profiles';
                $imagePath = $employee->fh_business->b_unique_id . '/' . $imageName;
                $isFaceDetectionActive = $this->user->fh_business->is_face_detection_active;
                $shouldStoreOnS3 = env('STORE_ON_S3');


                // Upload and index face on AWS Rekognition (if required)
                // if (is_null($employee->emp_rekognition_id) && $image && in_array(316, $checkInMethods) && $isFaceDetectionActive) {
                if (is_null($employee->emp_rekognition_id) && $image && in_array(316, $checkInMethods)) {
                    $awsResponse = app('App\Http\Controllers\FaceController')->uploadAndIndexFace($employee, $image, $imageName);
                }

                // Upload image to AWS S3 if enabled
                if ($shouldStoreOnS3) {
                    if (isset($awsResponse['result']['profile_s3_url']) && $awsResponse['result']['profile_s3_url']) {
                        $profile_url = $awsResponse['result']['profile_s3_url'];
                    } else {
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $image);
                        if (!empty($uploadResult['status'])) {
                            $profile_url = $uploadResult['ObjectURL'];
                        }
                    }
                } else {
                    // Store image in local server directory
                    $profile_url = CommonUtils::uploadFiles($request, 'emp_profile_photo', 'employee_profile/' . $employee->fh_business->b_unique_id, ['prefix' => 'emp_profile']);
                    $profile_url = isset($profile_url[0]) ? url($profile_url[0]) : NULL;
                }
            }

            $updateEmployeeFields = [
                'emp_full_name' => DB::raw("CONCAT(emp_fname, ' ', COALESCE(emp_mname, ''), ' ', COALESCE(emp_lname, ''))"),

            ];
            if ($profile_url) {
                $updateEmployeeFields['emp_profile_photo'] = $profile_url;
            }
            $employee->update($updateEmployeeFields);

            // Create employee qualification record
            $qualificationData = [
                'eq_qualification_id' => $request->input('eq_qualification_id'),
                'eq_emp_id' => $employee->emp_id,
                'eq_stream_id' => $request->input('eq_stream_id'),
                'eq_course_type_id' => $request->input('eq_course_type_id'),
                'eq_specialization' => $request->input('eq_specialization'),
                'eq_course_nature' => $request->input('eq_course_nature'),
                'eq_qualification_status' => $request->input('eq_qualification_status'),
                'eq_institution_name' => $request->input('eq_institution_name'),
                'eq_university_name' => $request->input('eq_university_name'),
                'eq_edu_from_date' => $request->input('eq_edu_from_date'),
                'eq_edu_to_date' => $request->input('eq_edu_to_date'),
                'eq_passing_date' => $request->input('eq_passing_date'),
                'eq_percentage' => $request->input('eq_percentage'),
                'eq_edu_grade' => $request->input('eq_edu_grade'),
                'eq_duration' => $request->input('eq_duration'),
                'eq_year' => $request->input('year'),
                'eq_temp_country' => $request->input('eq_temp_country'),
            ];

            // Use updateOrCreate to handle qualification creation or update
            $employee->fh_employee_qualifications()->updateOrCreate(
                ['eq_emp_id' => $employee->emp_id, 'eq_id' => $request->input('eq_primary_id')],
                $qualificationData
            );

            // Loop through the data and insert into the table
            foreach ($request->po_company_name as $index => $companyName) {
                $designationName   = $request->po_dg_id[$index] ?? null;
                $fromDate          = $request->po_from_date[$index] ?? null;
                $toDate            = $request->po_to_date[$index] ?? null;
                $serviceDuration   = $request->po_serviceduration[$index] ?? null;
                $poID              = $request->po_id[$index] ?? null;

                if (!empty($companyName)) {
                    PreviousOrganization::updateOrCreate(
                        [
                            'po_id' => $poID
                        ],
                        [
                            'po_company_name'      => $companyName,
                            'po_emp_id'            => $employee->emp_id,
                            'po_designation_name'  => $designationName,
                            'po_from_date'         => $fromDate,
                            'po_to_date'           => $toDate,
                            'po_serviceduration'   => $serviceDuration,
                        ]
                    );
                }
            }

            // Creating default password for new employee
            if (!$request->filled('emp_primary_id')) {
                $joining_year = date('Y', strtotime($request->input('emp_date_of_joining')));
                // $password = $request->input('emp_code') . $joining_year;
                $password = Str::random(8);
                $hashedPassword = Hash::make($password);

                $employee->update([
                    'emp_password' => $hashedPassword
                ]);

                $businessName = isset($user->fh_business) ? $user->fh_business->b_name : Business::where('b_id', $employee->emp_b_id)->pluck('b_name')->first();

                $mailData = [
                    'url' => env('APP_URL'),
                    'business_name' => $businessName,
                    'name' => $employee->emp_fname,
                    'email' => $employee->emp_email,
                    'password' => $password,
                ];
                CentralLogics::send_mail($employee->emp_email, new NewEmployeeMail($mailData));
            }

            /* LEAVE BALANCE INITIAL DATA INSERTION */

            $checkLeaveBal = LeaveBalance::where('lb_emp_id', $employee->emp_id)->where('lb_b_id', $user->fh_business->b_id)
                ->where('lb_month', date('m'))->where('lb_year', date('Y'))
                ->get(); //this check current month leave balance


            /* CHECKING JOINING LEAVE */
            if ($employee->emp_allow_joining_leave == 0) { // Not Allowed
                foreach ($checkLeaveBal as $leaveBal) {
                    $joiningMonth = date('m', strtotime($request->emp_date_of_joining));
                    if ($joiningMonth == $leaveBal->lb_month) {
                        $leaveBal->lb_alloted_leave = "0.00";
                        $leaveBal->lb_taken_leave = "0.00";
                        $leaveBal->lb_balance_remaining_leave = "0.00";
                        $leaveBal->save();
                    }
                }
            } elseif ($employee->emp_allow_joining_leave == 1) { // Allowed
                foreach ($checkLeaveBal as $leaveBal) { //366
                    if ($employee->emp_joining_leave_calc_type == 366 && $employee->emp_joining_leave_before_date <= $employee->emp_date_of_joining) { // Applicable date is less then date of joining
                        $leaveBal->lb_alloted_leave = "0.00";
                        $leaveBal->lb_taken_leave = "0.00";
                        $leaveBal->lb_balance_remaining_leave = "0.00";
                        $leaveBal->save();
                    }
                }
            }


            $gender = null;
            if ($employee->emp_gender_id == 33) { // Male
                $gender = 224; //Male
            } elseif ($employee->emp_gender_id == 34) { // Female
                $gender = 225; //Female
            } else { // Other
                $gender = 0;
            }

            //assign employee leave balance by leave policy  & leave category wise
            $leaveType = LeaveType::where('lvt_pl_id', $employee->emp_pl_id)->get();
            foreach ($leaveType as $lvType) {
                $isValidGender = false;

                // Find existing leave balance entry for the specific leave type
                $existingLeaveBal = $checkLeaveBal->firstWhere('lb_cat_type_id', $lvType->lvt_cat_type_id);

                $updateData = [
                    'lb_b_id' => $employee->emp_b_id,
                    'lb_emp_id' => $employee->emp_id,
                    'lb_month' => date('m'),
                    'lb_year' => date('Y'),
                    'lb_cat_type_id' => $lvType->lvt_cat_type_id,
                    'lb_alloted_leave' => $lvType->lvt_days_per_year,
                    'lb_taken_leave' => 0,
                    'lb_balance_remaining_leave' => $lvType->lvt_days_per_year - ($existingLeaveBal->lb_taken_leave ?? 0),
                ];

                // Check if the gender matches or is applicable for all
                if ($lvType->lvt_applicable_to_id == 223 || $lvType->lvt_applicable_to_id == $gender) {
                    $isValidGender = true;
                }

                // insert if leave is not assigned to the employee
                if (!$existingLeaveBal && ($lvType->lvt_applicable_to_id == 0 || $isValidGender)) {
                    LeaveBalance::create($updateData);
                }
            }

            $newStatusId = $request->input('emp_status');
            $empId       = $employee->emp_id;

            // Get the latest status ID for this employee
            $lastStatusId = EmpStatusHistory::where('hs_emp_id', $empId)
                ->orderByDesc('hs_id')
                ->value('hs_status_id');

            // Only save if status is different
            if ($lastStatusId != $newStatusId) {
                EmpStatusHistory::create([
                    'hs_emp_id'    => $empId,
                    'hs_b_id'      => $user->emp_b_id,
                    'hs_status_id' => $newStatusId,
                ]);
            }


            $updates = true;

            // dd($employee->emp_id);

            DB::commit(); // Commit transaction if all is successful

            $empSalaryExists = SalaryEmployeeSalary::where('es_emp_id', $employee->emp_id)->exists();

            if ($empSalaryExists == false) {
                return response()->json([
                    'status'   => true,
                    'redirect' => url('admin/employee/payroll-add-edit/' . Crypt::encrypt($employee->emp_id)),
                ], 200);
            }

            if ($request->emp_primary_id) {
                return response()->json([
                    'status' => true,
                    'message' => 'Updated successfully.',
                    'emp_id' => null,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Saved successfully.',
                'emp_id' => Crypt::encrypt($employee->emp_id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if an error occurs
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function quickAddEmp(Request $request)
    {
        $profile_url =  null;

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $rules = [
                'emp_code' => [
                    'required',
                    Rule::unique('employees')->where(function ($query) use ($user, $request) {
                        return $query->where('emp_b_id', $user->emp_b_id)
                            ->where('emp_id', '!=', $request->emp_primary_id);
                    })
                ],
                'prefix' => 'required',
                'emp_fname' => 'required',
                'emp_gender_id' => 'required',
                'emp_dob' => 'required|date',
                'emp_phone' => ['required', 'numeric', 'digits_between:10,12'],
                'emp_permanent_address' => 'required',
                'emp_permanent_pin_code' => 'required',
                'regex:/^\d{6}$/',
                'emp_br_id' => 'required',
                'emp_d_id' => 'required',
                'emp_dg_id' => 'required',
                'emp_grade_id' => 'required',
                'emp_role_id' => 'required',
                'emp_reporting_manager_id' => 'required',
                'emp_ap_id' => 'required',
                'checkInMethod' => 'required',
                'emp_shift_type_id' => 'required',
                'emp_work_mode_id' => 'required',
                'emp_is_geofencing_active' => 'required',
                'emp_pwo_id' => 'required',
                'emp_is_geowork_active' => 'required',
                'emp_pl_id' => 'required',
                'emp_allow_joining_leave' => 'required',
                'emp_allow_probation_leave' => 'required',
                'emp_status' => 'required',
                'emp_type_id' => 'required',
                'emp_date_of_joining' => 'required|date',
                'emp_job_status' => 'required',
                // 'emp_profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            ];

            // if ($request->emp_profile_photo == 'undefined' || $request->emp_profile_photo == 'null') {
            //     unset($rules['emp_profile_photo']);
            // }
            // $messages = [
            //     'emp_profile_photo.required' => 'The profile photo is required.',
            //     'emp_profile_photo.image' => 'The file must be an image.',
            //     'emp_profile_photo.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
            //     'emp_profile_photo.max' => 'The image size must not exceed 1 MB.' // This will match with proper max rule
            // ];

            // Data Validation
            // $validator = Validator::make($request->all(), $rules, $messages);
            // if ($validator->fails()) {
            //     return response()->json([
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            // Custom Validation messages
            // Case 1: Check if phone number is already assigned within the same business
            $sameBusinessPhone = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_phone', $request->emp_phone)
                ->where('emp_id', '<>', $request->emp_primary_id)
                ->exists();

            if ($sameBusinessPhone) {
                return response()->json([
                    'status' => false,
                    'message' => 'This phone number is already assigned within the same business.'
                ]); // HTT Unprocessable Entity for validation errors
            }

            // Case 2: Check if phone number is assigned to another business with active status
            $otherBusinessPhone = Employee::where('emp_b_id', '<>', $user->emp_b_id)
                ->where('emp_phone', $request->emp_phone)
                ->where('emp_status', 71) // Active status
                ->exists();

            if ($otherBusinessPhone) {
                return response()->json([
                    'status' => false,
                    'message' => 'This phone number is assigned to another business and the employee is active.'
                ]);
            }

            if ($request->input('emp_email')) {
                // Case 3: Check if email is already assigned within the same business
                $sameBusinessEmail = Employee::where('emp_b_id', $user->emp_b_id)
                    ->where('emp_email', $request->emp_email)
                    ->where('emp_id', '<>', $request->emp_primary_id)
                    ->exists();

                if ($sameBusinessEmail) {
                    return response()->json([
                        'status' => false,
                        'message' => 'This email is already assigned within the same business.'
                    ]); // HTT Unprocessable Entity for validation errors
                }

                // Case 4: Check if email is assigned to another business with active status
                $otherBusinessEmail = Employee::where('emp_b_id', '<>', $user->emp_b_id)
                    ->where('emp_email', $request->emp_email)
                    ->where('emp_status', 71) // Active status
                    ->exists();

                if ($otherBusinessEmail) {
                    return response()->json([
                        'status' => false,
                        'message' => 'This email is assigned to another business and the employee is active.'
                    ]);
                }
            }

            $checkInMethods = $request->input('emp_checkin_method_id');
            $checkInMethods = array_map('intval', explode(',', $checkInMethods));

            $employee = Employee::create(
                [
                    'emp_b_id' => $user->emp_b_id,
                    'emp_code' => $request->input('emp_code'),
                    'emp_prefix' => $request->input('prefix'),
                    'emp_fname' => $request->input('emp_fname'),
                    'emp_mname' => $request->input('emp_mname'),
                    'emp_lname' => $request->input('emp_lname'),
                    'emp_gender_id' => $request->input('emp_gender_id'),
                    'emp_marital_status_id' => $request->input('emp_marital_status_id'),
                    'emp_dob' => $request->input('emp_dob'),
                    'emp_phone' => $request->input('emp_phone'),
                    'emp_email' => $request->input('emp_email'),
                    'emp_permanent_address' => $request->input('emp_permanent_address'),
                    'emp_permanent_pin_code' => $request->input('emp_permanent_pin_code'),
                    'emp_br_id' => $request->input('emp_br_id'),
                    'emp_d_id' => $request->input('emp_d_id'),
                    'emp_dg_id' => $request->input('emp_dg_id'),
                    'emp_grade_id' => $request->input('emp_grade_id'),
                    'emp_role_id' => $request->input('emp_role_id'),
                    'emp_reporting_manager_id' => $request->input('emp_reporting_manager_id'),
                    'emp_ap_id' => $request->input('emp_ap_id'),
                    'emp_shift_type_id' => $request->input('emp_shift_type_id'),
                    'emp_work_mode_id' => $request->input('emp_work_mode_id'),
                    'emp_is_geofencing_active' => $request->input('emp_is_geofencing_active'),
                    'emp_pwo_id' => $request->input('emp_pwo_id'),
                    'emp_is_geowork_active' => $request->input('emp_is_geowork_active'),
                    'emp_offline_status' => (int)$request->input('emp_offline_status', 0),
                    'emp_pl_id' => $request->input('emp_pl_id'),
                    'emp_allow_joining_leave' => $request->input('emp_allow_joining_leave'),
                    'emp_joining_leave_calc_type' => $request->input('emp_joining_leave_calc_type'),
                    'emp_joining_leave_before_date' => $request->input('emp_joining_leave_before_date'),
                    'emp_allow_probation_leave' => $request->input('emp_allow_probation_leave'),
                    'emp_status' => $request->input('emp_status'),
                    'emp_type_id' => $request->input('emp_type_id'),
                    'emp_date_of_joining' => $request->input('emp_date_of_joining'),
                    'emp_job_status' => $request->input('emp_job_status'),
                    'emp_checkin_method_id' => $checkInMethods,
                ]
            );

            // $profile_url =  null;
            if ($request->hasFile('emp_profile_photo')) {

                $image = $request->file('emp_profile_photo');
                $imageName = time() . '_' . md5($image->getClientOriginalName()) . '.' . $image->extension();
                $bucket = 'fixhr-employee-profiles';
                $imagePath = $employee->fh_business->b_unique_id . '/' . $imageName;
                $isFaceDetectionActive = $this->user->fh_business->is_face_detection_active;
                $shouldStoreOnS3 = env('STORE_ON_S3');


                // Upload and index face on AWS Rekognition (if required)
                if (is_null($employee->emp_rekognition_id) && $image && in_array(316, $checkInMethods) && $isFaceDetectionActive) {
                    $awsResponse = app('App\Http\Controllers\FaceController')->uploadAndIndexFace($employee, $image, $imageName);
                }

                // Upload image to AWS S3 if enabled
                if ($shouldStoreOnS3) {
                    if (isset($awsResponse['result']['profile_s3_url']) && $awsResponse['result']['profile_s3_url']) {
                        $profile_url = $awsResponse['result']['profile_s3_url'];
                    } else {
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $image);
                        if (!empty($uploadResult['status'])) {
                            $profile_url = $uploadResult['ObjectURL'];
                        }
                    }
                } else {
                    // Store image in local server directory
                    $profile_url = CommonUtils::uploadFiles($request, 'emp_profile_photo', 'employee_profile/' . $employee->fh_business->b_unique_id, ['prefix' => 'emp_profile']);
                    $profile_url = isset($profile_url[0]) ? url($profile_url[0]) : NULL;
                }
            }

            $updateEmployeeFields = [
                'emp_full_name' => DB::raw("CONCAT(emp_fname, ' ', COALESCE(emp_mname, ''), ' ', COALESCE(emp_lname, ''))"),
            ];
            if ($profile_url) {
                $updateEmployeeFields['emp_profile_photo'] = $profile_url;
            }
            $employee->update($updateEmployeeFields);

            // Creating default password for new employee
            $joining_year = date('Y', strtotime($request->input('emp_date_of_joining')));
            // $password = $request->input('emp_code') . $joining_year;
            $password = Str::random(8);
            $hashedPassword = Hash::make($password);

            $employee->update([
                'emp_password' => $hashedPassword
            ]);

            if ($request->input('emp_email')) {
                $businessName = isset($user->fh_business) ? $user->fh_business->b_name : Business::where('b_id', $employee->emp_b_id)->pluck('b_name')->first();

                $mailData = [
                    'url' => env('APP_URL'),
                    'business_name' => $businessName,
                    'name' => $employee->emp_fname,
                    'email' => $employee->emp_email,
                    'password' => $password,
                ];
                CentralLogics::send_mail($employee->emp_email, new NewEmployeeMail($mailData));
            }

            /* LEAVE BALANCE INITIAL DATA INSERTION */

            $checkLeaveBal = LeaveBalance::where('lb_emp_id', $employee->emp_id)->where('lb_b_id', $user->fh_business->b_id)
                ->where('lb_month', date('m'))->where('lb_year', date('Y'))
                ->get(); //this check current month leave balance


            /* CHECKING JOINING LEAVE */
            if ($employee->emp_allow_joining_leave == 0) { // Not Allowed
                foreach ($checkLeaveBal as $leaveBal) {
                    $joiningMonth = date('m', strtotime($request->emp_date_of_joining));
                    if ($joiningMonth == $leaveBal->lb_month) {
                        $leaveBal->lb_alloted_leave = "0.00";
                        $leaveBal->lb_taken_leave = "0.00";
                        $leaveBal->lb_balance_remaining_leave = "0.00";
                        $leaveBal->save();
                    }
                }
            } elseif ($employee->emp_allow_joining_leave == 1) { // Allowed
                foreach ($checkLeaveBal as $leaveBal) { //366
                    if ($employee->emp_joining_leave_calc_type == 366 && $employee->emp_joining_leave_before_date <= $employee->emp_date_of_joining) { // Applicable date is less then date of joining
                        $leaveBal->lb_alloted_leave = "0.00";
                        $leaveBal->lb_taken_leave = "0.00";
                        $leaveBal->lb_balance_remaining_leave = "0.00";
                        $leaveBal->save();
                    }
                }
            }


            $gender = null;
            if ($employee->emp_gender_id == 33) { // Male
                $gender = 224; //Male
            } elseif ($employee->emp_gender_id == 34) { // Female
                $gender = 225; //Female
            } else { // Other
                $gender = 0;
            }

            //assign employee leave balance by leave policy  & leave category wise
            $leaveType = LeaveType::where('lvt_pl_id', $employee->emp_pl_id)->get();
            foreach ($leaveType as $lvType) {
                $isValidGender = false;

                // Find existing leave balance entry for the specific leave type
                $existingLeaveBal = $checkLeaveBal->firstWhere('lb_cat_type_id', $lvType->lvt_cat_type_id);

                $updateData = [
                    'lb_b_id' => $employee->emp_b_id,
                    'lb_emp_id' => $employee->emp_id,
                    'lb_month' => date('m'),
                    'lb_year' => date('Y'),
                    'lb_cat_type_id' => $lvType->lvt_cat_type_id,
                    'lb_alloted_leave' => $lvType->lvt_days_per_year,
                    'lb_taken_leave' => 0,
                    'lb_balance_remaining_leave' => $lvType->lvt_days_per_year - ($existingLeaveBal->lb_taken_leave ?? 0),
                ];

                // Check if the gender matches or is applicable for all
                if ($lvType->lvt_applicable_to_id == 223 || $lvType->lvt_applicable_to_id == $gender) {
                    $isValidGender = true;
                }

                // insert if leave is not assigned to the employee
                if (!$existingLeaveBal && ($lvType->lvt_applicable_to_id == 0 || $isValidGender)) {
                    LeaveBalance::create($updateData);
                }
            }

            DB::commit(); // Commit transaction if all is successful
            return response()->json([
                'status' => true,
                'message' => 'Saved successfully.',
                'emp_id' => Crypt::encrypt($employee->emp_id), // encrypt here
            ]);
        } catch (Exception $e) {
            DB::rollBack(); // Rollback transaction if an error occurs
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    private function handleUpload($request, $field)
    {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('assets/employee_documents');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);

            return 'assets/employee_documents/' . $filename;
        }

        return null;
    }



    public function getIFSCDetails(Request $request)
    {
        $ifsc = $request->ifsc;
        $pattern = '/^[A-Z]{4}0[A-Z0-9]{6}$/';

        if (preg_match($pattern, $ifsc)) {
            $json_result = @file_get_contents('https://ifsc.razorpay.com/' . $ifsc);

            if ($json_result !== false) {
                $output = json_decode($json_result);
                return response()->json(['status' => true, 'data' => $output, 'message' => '']);
            } else {
                $IFSCError = 'Failed to fetch data from IFSC API';
                return response()->json(['status' => false, 'data' => '', 'message' => $IFSCError]);
            }
        } else {
            $IFSCError = 'Invalid IFSC code format';
            return response()->json(['status' => false, 'data' => '', 'message' => $IFSCError]);
        }
    }
    public function getsalaryIFSCDetails(Request $request)
    {
        $ifsc = $request->emp_salary_ifsc;
        $pattern = '/^[A-Z]{4}0[A-Z0-9]{6}$/';

        if (preg_match($pattern, $ifsc)) {
            $json_result = @file_get_contents('https://ifsc.razorpay.com/' . $ifsc);

            if ($json_result !== false) {
                $output = json_decode($json_result);
                return response()->json(['status' => true, 'data' => $output, 'message' => '']);
            } else {
                $IFSCError = 'Failed to fetch data from IFSC API';
                return response()->json(['status' => false, 'data' => '', 'message' => $IFSCError]);
            }
        } else {
            $IFSCError = 'Invalid IFSC code format';
            return response()->json(['status' => false, 'data' => '', 'message' => $IFSCError]);
        }
    }

    // public function getEmployeeData(Request $request)
    // {
    //     $user = Auth::user();
    //     if ($request->REQUEST_TYPE == 'get-reporting-managers') {
    //         $departmentId = $request->department_id;
    //         // Fetch the managers based on the department ID
    //         $managers = Employee::where('emp_d_id', $departmentId)->where('emp_b_id', $user->emp_b_id)->get();
    //         return response()->json([
    //             'managers' => $managers
    //         ]);
    //     } else {
    //         $DATA = Employee::where('emp_id', $request->emp_id)
    //             ->orWhere(function ($query) use ($request, $user) {
    //                 $query->where('emp_code', $request->emp_id)
    //                     ->where('emp_b_id', $user->emp_b_id);
    //             })
    //             ->with([
    //                 'fh_department:d_id,d_name',
    //                 'fh_designation:dg_id,dg_name',
    //                 'fh_employee_status:m_id,m_name',
    //                 'fh_gender:m_id,m_name',
    //                 'fh_employee_type:m_id,m_name',
    //                 'fh_grade:g_id,g_name',
    //                 'fh_branch:br_id,br_name',
    //                 'fh_work_mode:m_id,m_name',
    //                 'fh_marital_status:m_id,m_name',
    //                 'fh_cast_category:m_id,m_name',
    //                 'fh_blood_group:m_id,m_name',
    //                 'fh_role:role_id,role_name',
    //                 'fh_reporting_manager_id:emp_id,emp_full_name',
    //                 'fh_emp_region:m_id,m_name',
    //                 'fh_attendance_policy:ap_id,ap_name',
    //                 'fh_shift_type:pst_id,pst_name',
    //                 'fh_work_mode:m_id,m_name',
    //                 'fh_geofencing:m_id,m_name',
    //                 'fh_week_off_policy2:pwo_id,pwo_name',
    //                 'fh_policy_leave:pl_id,pl_name',
    //                 'fh_leave_calculation_type:m_id,m_name',
    //                 'fh_pf_master:m_id,m_name',
    //             ])
    //             ->first();

    //         if ($DATA && !empty($DATA->emp_project_id)) {
    //             $projectIds = $DATA->emp_project_id;
    //             $DATA->project_names = Project::whereIn('ps_id', $projectIds)
    //                 ->pluck('ps_name');
    //         }

    //         if ($DATA && !empty($DATA->emp_checkin_method_id)) {
    //             $checkInMethodIds = $DATA->emp_checkin_method_id;
    //             $DATA->checkin_method_names = MasterTable::whereIn('m_id', $checkInMethodIds)
    //                 ->pluck('m_name');
    //         }

    //         $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $DATA->emp_b_id, 'ptc_d_id' => $DATA->emp_d_id, 'ptc_grade_id' => $DATA->emp_grade_id])->whereJsonContains('ptc_dg_id', $DATA->emp_dg_id)->first();
    //         $dataPolicy = $policyCategory->ptc_name ?? 'N/A';
    //         $DATA->tadaPolicy = $dataPolicy;

    //         $lateRule = LateComingAutomation::where('lca_b_id', $DATA->emp_b_id)->where('lca_is_penalty_enabled', 0)->orderBy('lca_no_late')->first();
    //         $DATA->allowedLateComings = $lateRule ? $lateRule->lca_no_late - 1 : 0;

    //         $earlyRule = EarlyGoingAutomation::where('ega_b_id', $DATA->emp_b_id)->where('ega_is_penalty_enabled', 0)->orderBy('ega_no_early')->first();
    //         $DATA->allowedEarlyGoings = $earlyRule ? $earlyRule->ega_no_early - 1 : 0;

    //         $gatePassRule = AutomationRule::where('ar_b_id', $DATA->emp_b_id)->where('ar_rule_type', '418')->where('ar_is_enabled', 1)->first();
    //         $DATA->allowedGatePasses = $gatePassRule ? $gatePassRule->ar_occurrences : 0;

    //         $mspRule = AutomationRule::where('ar_b_id', $DATA->emp_b_id)->where('ar_rule_type', '417')->where('ar_is_enabled', 1)->first();
    //         $DATA->allowedMSP = $mspRule ? $mspRule->ar_occurrences : 0;

    //         return response()->json(['data' => $DATA]);
    //     }
    // }

    public function getEmployeeData(Request $request)
    {
        $user = Auth::user();
        if ($request->REQUEST_TYPE == 'get-reporting-managers') {
            $departmentId = $request->department_id;
            // Fetch the managers based on the department ID
            $managers = Employee::where('emp_d_id', $departmentId)->where('emp_b_id', $user->emp_b_id)->get();
            return response()->json([
                'managers' => $managers
            ]);
        } else {
            $DATA = Employee::where('emp_id', $request->emp_id)
                ->orWhere(function ($query) use ($request, $user) {
                    $query->where('emp_code', $request->emp_id)
                        ->where('emp_b_id', $user->emp_b_id);
                })
                ->with([
                    'fh_department:d_id,d_name',
                    'fh_designation:dg_id,dg_name',
                    'fh_employee_status:m_id,m_name',
                    'fh_gender:m_id,m_name',
                    'fh_employee_type:m_id,m_name',
                    'fh_grade:g_id,g_name',
                    'fh_branch:br_id,br_name',
                    'fh_work_mode:m_id,m_name',
                    'fh_marital_status:m_id,m_name',
                    'fh_cast_category:m_id,m_name',
                    'fh_blood_group:m_id,m_name',
                    'fh_role:role_id,role_name',
                    'fh_reporting_manager_id:emp_id,emp_full_name',
                    'fh_emp_region:m_id,m_name',
                    'fh_attendance_policy:ap_id,ap_name',
                    'fh_shift_type:pst_id,pst_name',
                    'fh_work_mode:m_id,m_name',
                    'fh_geofencing:m_id,m_name',
                    'fh_week_off_policy2:pwo_id,pwo_name',
                    'fh_policy_leave:pl_id,pl_name',
                    'fh_leave_calculation_type:m_id,m_name',
                ])
                ->first();

            if ($DATA && !empty($DATA->emp_project_id)) {
                $projectIds = $DATA->emp_project_id;
                $DATA->project_names = Project::whereIn('ps_id', $projectIds)
                    ->pluck('ps_name');
            }

            if ($DATA && !empty($DATA->emp_checkin_method_id)) {
                $checkInMethodIds = $DATA->emp_checkin_method_id;
                $DATA->checkin_method_names = MasterTable::whereIn('m_id', $checkInMethodIds)
                    ->pluck('m_name');
            }

            return response()->json(['data' => $DATA]);
        }
    }


    public function uploadEmployeeAvatar(Request $request)
    {
        // profile_photo
        $user = Auth::user();
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $file = $request->file('avatar');
        $path = $file->store($request->emp_id . '/employee_profile', 'emp_files_uploads');
        $img_path = Storage::url($path);
        $updates = Employee::updateOrCreate(
            ['emp_b_id' => $user->emp_b_id, 'emp_code' => $request->emp_id],
            [
                'emp_profile_photo' => $img_path,
            ]
        );

        return response()->json(['success' => true, 'path' => $img_path]);
    }


    public function getStateCity(Request $request)
    {
        if ($request->for == 1) {
            $StateList = State::where('s_c_id', $request->value)->get();
            return response()->json(['request' => $request->all(), 'data' => $StateList]);
        } else {
            $CityList = City::where('ct_s_id', $request->value)->get();
            return response()->json(['request' => $request->all(), 'data' => $CityList]);
        }
    }

    public function getQualifications(Request $request)
    {
        $user = Auth::user();
        $qualification = Qualification::where('qua_stm_id', $request->stream)
            // ->orWhere('qua_b_id', $user->emp_b_id)
            ->orderBy('qua_name')
            ->get();

        if ($qualification) {
            return response()->json(['data' => $qualification], 200);
        } else {
            return response()->json(['data' => $qualification], 400);
        }
    }

    // public function bulkImageUpload(Request $request)
    // {
    //     $request->validate([
    //         'images.*' => 'required|mimes:jpg,jpeg,png|max:2048'
    //     ]);

    //     $uploadedImages = $request->file('images');
    //     $storeOnS3 = env('STORE_ON_S3', false);
    //     $bucket = 'fixhr-employee-profiles';

    //     $errors = [];

    //     foreach ($uploadedImages as $image) {
    //         $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME); // e.g., "EMP001"
    //         $extension = $image->getClientOriginalExtension();
    //         $parts = explode('_', $originalName);

    //         if (count($parts) < 2) {
    //             $errors[] = "Invalid file name format: $originalName";
    //             Log::warning("Invalid file name format: $originalName");
    //             continue;
    //         }

    //         $business_unique_id = $parts[0];
    //         $emp_code = $parts[1];
    //         $b_id = Business::where('b_unique_id', $business_unique_id)->value('b_id');
    //         if (!$b_id) {
    //             $errors[] = "Invalid business ID: $business_unique_id in $originalName";
    //             Log::warning("Invalid business ID in: $originalName");
    //             continue;
    //         }

    //         // Fetch employee using employee code from file name
    //         $employee = Employee::where(['emp_b_id' => $b_id, 'emp_code' => $emp_code])->first();
    //         if (!$employee) {
    //             $errors[] = "No employee found for: $originalName";
    //             Log::warning("No employee found for code: $originalName");
    //             continue;
    //         }

    //         // Delete old profile photo if exists
    //         $oldProfilePhoto = $employee->emp_profile_photo;
    //         if ($oldProfilePhoto) {
    //             $parsedUrl = parse_url($oldProfilePhoto, PHP_URL_PATH);
    //             $keyOrPath = ltrim($parsedUrl, '/');

    //             if ($storeOnS3) {
    //                 $this->awsHelper->deleteFileFromS3($bucket, $keyOrPath);
    //             } else {
    //                 $fullPath = public_path($keyOrPath);
    //                 if (file_exists($fullPath)) {
    //                     @unlink($fullPath);
    //                 }
    //             }
    //         }

    //         $imageName = time() . '_' . md5($originalName) . '.' . $extension;
    //         $imagePath = $employee->fh_business->b_unique_id . '/' . $imageName;
    //         $profileUrl = null;

    //         // Upload to S3 or local
    //         if ($storeOnS3) {
    //             $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $image);
    //             if (!empty($uploadResult['status'])) {
    //                 $profileUrl = $uploadResult['ObjectURL'];
    //             }
    //         } else {
    //             // $localPath = CommonUtils::uploadFiles($image, null, 'uploads/employee_profile', [
    //             //     'prefix' => 'emp_profile',
    //             //     'filename' => $imageName,
    //             //     'store_multiple' => false // each file separately
    //             // ]);
    //             // $profileUrl = isset($localPath[0]) ? url($localPath[0]) : null;
    //             $storagePath = $image->storeAs('employee_profile/'.$employee->fh_business->b_unique_id, $imageName, 'uploads');
    //             $profileUrl = url('uploads/' . $storagePath);
    //         }

    //         if ($profileUrl) {
    //             $employee->update([
    //                 'emp_profile_photo' => $profileUrl,
    //             ]);
    //         }
    //     }
    //     return response()->json([
    //         'success' => 'Employee images uploaded.',
    //         'errors' => $errors
    //     ], 200);
    //     // return response()->json(['success' => 'Employees images uploaded successfully!'], 200);
    // }

    public function bulkImageUpload(Request $request)
    {
        $request->validate([
            'images.*' => 'required|mimes:jpg,jpeg,png|max:2048'
        ]);

        $uploadedImages = $request->file('images');
        $storeOnS3 = env('STORE_ON_S3', false);
        $bucket = 'fixhr-employee-profiles';

        $errors = [];
        $successCount = 0;

        foreach ($uploadedImages as $image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $parts = explode('_', $originalName);

            if (count($parts) < 2) {
                $errors[] = "Invalid file name format: $originalName";
                continue;
            }

            $business_unique_id = $parts[0];
            $emp_code = $parts[1];
            $b_id = Business::where('b_unique_id', $business_unique_id)->value('b_id');
            if (!$b_id) {
                $errors[] = "Invalid business ID: $business_unique_id in $originalName";
                continue;
            }

            // Fetch employee using employee code from file name
            $employee = Employee::where(['emp_b_id' => $b_id, 'emp_code' => $emp_code])->first();
            if (!$employee) {
                $errors[] = "No employee found for: $originalName";
                continue;
            }

            // Delete old profile photo if exists
            $oldProfilePhoto = $employee->emp_profile_photo;
            if ($oldProfilePhoto) {
                $parsedUrl = parse_url($oldProfilePhoto, PHP_URL_PATH);
                $keyOrPath = ltrim($parsedUrl, '/');

                if ($storeOnS3) {
                    $this->awsHelper->deleteFileFromS3($bucket, $keyOrPath);
                } else {
                    $fullPath = public_path($keyOrPath);
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }

            $imageName = time() . '_' . md5($originalName) . '.' . $extension;
            $imagePath = $employee->fh_business->b_unique_id . '/' . $imageName;
            $profileUrl = null;

            // Upload and index face on AWS Rekognition (if required)
            if (is_null($employee->emp_rekognition_id) && $image && in_array(316, $employee->emp_checkin_method_id)) {
                $awsResponse = app('App\Http\Controllers\FaceController')->uploadAndIndexFace($employee, $image, $imageName);
            }

            // Upload to S3 or local
            if ($storeOnS3) {
                $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $image);
                if (!empty($uploadResult['status'])) {
                    $profileUrl = $uploadResult['ObjectURL'];
                }
            } else {
                // $localPath = CommonUtils::uploadFiles($image, null, 'uploads/employee_profile', [
                //     'prefix' => 'emp_profile',
                //     'filename' => $imageName,
                //     'store_multiple' => false // each file separately
                // ]);
                // $profileUrl = isset($localPath[0]) ? url($localPath[0]) : null;
                $storagePath = $image->storeAs('employee_profile/' . $employee->fh_business->b_unique_id, $imageName, 'uploads');
                $profileUrl = url('uploads/' . $storagePath);
            }

            if ($profileUrl) {
                $employee->update([
                    'emp_profile_photo' => $profileUrl,
                ]);
                $successCount++;
            }
        }
        return response()->json([
            'success' => 'Employee images uploaded.',
            'successCount' => $successCount,
            'errors' => $errors,
            'failed_count' => count($errors)
        ], 200);
        // return response()->json(['success' => 'Employees images uploaded successfully!'], 200);
    }


    public function getBankDetails(Request $request)
    {

        $request->validate([
            'ifsc' => 'required|string|size:11',
        ]);

        $ifsc = $request->input('ifsc');

        $response = Http::get("https://ifsc.razorpay.com/{$ifsc}");

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid IFSC code or API error.',
            ], 404);
        }
    }

    public function getsalaryBankDetails(Request $request)
    {

        $request->validate([
            'emp_salary_ifsc' => 'required|string|size:11',
        ]);

        $emp_salary_ifsc = $request->input('ifsc');

        $response = Http::get("https://ifsc.razorpay.com/{$emp_salary_ifsc}");

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid IFSC code or API error.',
            ], 404);
        }
    }

    public function downloadEmployeeDetailsSample($type)
    {
        $headers = [];

        if ($type === 'epf_esic') {
            $headers = [
                'Emp Code',
                'PF Enable',
                'PF Trust Code',
                'Pension Fund Member',
                'PF Number',
                'UAN',
                'VPF(%)',
                'PF DOJ(DD/MM/YYYY)',
                'PF DOL(DD/MM/YYYY)',
                'Reason of Leaving PF',
                'ESIC Enable',
                'ESIC Number',
                'ESIC Dispensary',
                'ESIC DOJ(DD/MM/YYYY)',
                'ESIC DOL(DD/MM/YYYY)',
                'Reason of Leaving ESIC',
                'Insured By',
                'Insurance Number',
                'Valid From',
                'Valid Thru'
            ];
        } elseif ($type === 'identity') {
            $headers = [
                'Emp Code',
                'Aadhar Number',
                'Driving License Number',
                'Election Card Number',
                'Passport Number',
                'PAN Number',
                'Bank AC Number'
            ];
        } elseif ($type === 'bank_details') {
            $headers = [
                'Emp Name',
                'Emp Code',
                'Payment Method',
                'Account Code',
                'IFSC Code',
                'Bank Name',
                'Branch Name',
                'MICR',
                'Branch Code',
                'AC Number',
                'Account Type',
            ];
        } else {
            return abort(404);
        }

        $filename = ucfirst(str_replace('_', ' ', $type)) . ' Template.xlsx';
        return Excel::download(new EmployeeDetailsHeaderExport($headers, $type), $filename);
    }


    public function importEmployeeDetails(Request $request)
    {
        $request->validate([
            'field_type' => 'required|in:epf_esic,identity,bank_details',
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);
        try {
            $empDetailsImport = new EmployeeDetailImport($request->field_type, Auth::user());
            Excel::import($empDetailsImport, $request->file('import_file'));
            $errorMessages = $empDetailsImport->getErrorMessages();
            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }
            return back()->with('success', 'Employee details imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function uploadEmpImageByAdminCheck(Request $request)
    {
        // Validate input
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'emp_code' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        $employee = Employee::where('emp_code', $request->emp_code)->first();
        if (!$employee || !$employee->fh_business) {
            return response()->json([
                'status' => false,
                'message' => 'Employee or business not found.'
            ], 404);
        }

        $file = $request->file('image');

        // Generate unique filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . md5($originalName) . '.' . $extension;
        $imagePath = $employee->fh_business->b_unique_id . '/' . $filename;

        $shouldStoreOnS3 = env('STORE_ON_S3', false);
        $bucket = 'fixhr-employee-profiles';
        $profile_url = null;

        // ✅ Delete old image if exists
        if (!empty($employee->emp_profile_photo)) {
            try {
                if ($shouldStoreOnS3) {
                    $parsedUrl = parse_url($employee->emp_profile_photo, PHP_URL_PATH);
                    $key = ltrim($parsedUrl, '/');
                    $this->awsHelper->deleteFileFromS3($bucket, $key);
                } else {
                    $oldPath = str_replace(url('storage/'), '', $employee->emp_profile_photo);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Old profile delete failed: ' . $e->getMessage());
            }
        }

        // Upload and index face on AWS Rekognition (if required)
        if (is_null($employee->emp_rekognition_id) && $file && in_array(316, $employee->emp_checkin_method_id)) {
            // dd('jjjd');
            $awsResponse = app('App\Http\Controllers\FaceController')->uploadAndIndexFace($employee, $file, $filename);
        }

        // ✅ Upload file
        try {
            if ($shouldStoreOnS3) {
                $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                if (!empty($uploadResult['status'])) {
                    $profile_url = $uploadResult['ObjectURL'];
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to upload to S3.'
                    ], 500);
                }
            } else {
                $storagePath = 'employee_profile/' . $employee->fh_business->b_unique_id;
                $path = $file->storeAs($storagePath, $filename, 'public');
                $profile_url = asset('storage/' . $path);
            }
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'File upload failed: ' . $e->getMessage()
            ], 500);
        }

        // ✅ Update employee record
        $employee->update([
            'emp_profile_photo' => $profile_url,
        ]);

        return response()->json([
            'result' => [
                ['file_url' => $profile_url]
            ],
            'status' => true,
            'message' => 'Image uploaded successfully.',
        ]);
    }

    public function getEmployeesForAssignment()
    {
        $user = Auth::user();
        $bussiness_id = $user->emp_b_id;
        $employees = Employee::where('emp_status', 71)->where('emp_b_id', $bussiness_id)->select('emp_id', 'emp_code', 'emp_full_name')->get();
        return response()->json($employees);
    }


    public function updateProjects(Request $request, $id)
    {
        $request->validate([
            'emp_project_id' => 'required|array',
        ]);

        $employee = Employee::findOrFail($id);

        $existing = $employee->emp_project_id ?? [];
        $merged   = array_unique(array_merge($existing, $request->emp_project_id));

        $employee->emp_project_id = $merged;
        $employee->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Projects created/updated successfully!'
        ]);
    }

    public function getProjects($id)
    {
        $employee = Employee::findOrFail($id);

        return response()->json([
            'projects' => $employee->emp_project_id ?? []
        ]);
    }

    public function downloadProfile($id)
    {
        $employee = Employee::with([
            'fh_designation',
            'fh_department',
            'fh_branch',
            'fh_role',
            'fh_gender',
            'fh_employee_type',
            'fh_grade',
            'fh_work_mode',
            'fh_reporting_manager_id',
            'fh_religion',
            'fh_shift_type'
        ])->findOrFail($id);


        // dd($employee);

        $isPdf = true;
        $pdf = Pdf::loadView('admin.employees.employee-profile', compact('employee', 'isPdf'))
            ->setPaper('a4', 'portrait');

        // return $pdf->stream($employee->emp_code . '_profile.pdf');
        // return $pdf->download($employee->emp_code . '_profile.pdf');
        return $pdf->download($employee->emp_full_name . '_' . $employee->emp_code  . '_profile.pdf');
    }
}
