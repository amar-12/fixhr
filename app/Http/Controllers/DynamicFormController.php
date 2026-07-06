<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\DynamicForm;
use App\Models\Employee;
use App\Models\EmployeeApprovalMapping;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\Grade;
use App\Models\MasterTable;
use App\Models\PolicyAttendance;
use App\Models\PolicyLeave;
use App\Models\PolicyShiftTiming;
use App\Models\PreviousOrganization;
use App\Models\Qualification;
use App\Models\Role;
use App\Models\Stream;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


use App\Exports\EmployeeDataExport;
use App\Exports\EmployeeErrorReportExport;
use App\Exports\EmployeeExport;
use App\Exports\ErrorExport;
use App\Helpers\CentralLogics;
use App\Helpers\RolePermissionLogics;
use App\Models\PolicyWeekOff;
use ChandraHemant\HtkcUtils\CommonUtils;
use App\Http\Controllers\Controller;
use App\Imports\EmployeeImport;
use App\Mail\NewEmployeeMail;
use App\Models\City;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\SalaryEmployeeSalary;
use App\Models\PolicyTadaCategory;
use App\Models\State;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\Aws\AwsHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;


class DynamicFormController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }


    public function index(Request $request)
    {
        if ($this->user) {
            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'select',
                        'args' => ['id', 'form_name', 'created_at', 'updated_at'],
                        'relation' => []
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['created_at', 'desc'],
                        'relation' => []
                    ]
                ];

                $searchColumns = ['form_name', 'created_at', 'updated_at'];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new DynamicForm(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->form_name;
                    $row[] = Carbon::parse($val->created_at)->format('d-M-Y H:i:s');
                    $row[] = Carbon::parse($val->updated_at)->format('d-M-Y H:i:s');
                    $row[] = '     <button type="submit"  class="btn btn-sm btn-primary view-dynamic-form" data-id="' . $val->id . '">
                                     <i class="feather feather-eye"></i>
                                </button>
                                <button type="submit" class="btn btn-sm btn-danger delete-dynamic-form" data-id="' . $val->id . '">
                                    <i class="feather feather-trash"></i>
                                </button>';

                    $rowData[] = $row;
                }

                $output = [
                    "draw" => $request->input('draw'),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new DynamicForm(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            $columns = [
                'S. No.',
                'Form Name',
                'Created At',
                'Updated At',
                'Action',
            ];

            $dynamicForms = DynamicForm::select('id', 'form_name', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.dynamic_form.index', compact('dynamicForms', 'columns'));
        } else {
            abort(404);
        }
    }



    public function create()
    {
        return view('admin.dynamic_form.create');
    }


    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'form_name' => 'required|string|unique:dynamic_forms,form_name',
            'sections' => 'required|array|min:1',
        ]);

        $form = DynamicForm::create([
            'form_name' => $request->form_name
        ]);

        foreach ($request->sections as $section) {
            $newSection = FormSection::create([
                'form_id' => $form->id,
                'section_name' => $section['section_name']
            ]);

            foreach ($section['fields'] as $field) {
                FormField::create([
                    'section_id' => $newSection->id,
                    'field_name' => $field['field_name'],
                    'field_type' => $field['field_type'],
                    'placeholder' => $field['placeholder'],
                    'colume_name' => $field['colume_name'],
                    'is_required' => isset($field['required']) ? 1 : 0,
                ]);
            }
        }

        return redirect()->route('forms.index')->with('success', 'Form created successfully!');
    }




    public function show($id = null)
    {
        // Fetching the necessary data for the form

        $user = Auth::user();
        $maritalStatus  =  MasterTable::where('m_group', 'MARITAL_STATUS')->get();
        $bloodGroupList = MasterTable::where('m_group', 'BLOOD_GROUP')->get();
        $staticGender =  MasterTable::where('m_group', 'GENDER')->get();
        $employeeType = MasterTable::where('m_group', 'EMPLOYEE_TYPE')->get();
        $BranchList =  Branch::where('br_b_id', $user->emp_b_id)->get();
        $DepartmentList = Department::where('d_b_id', $user->emp_b_id)->get();
        $DesignationList = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $attendanceMethod = MasterTable::where('m_group', 'WORK_MODE')->get();
        $attendancePolicy = PolicyAttendance::where('ap_b_id', $user->emp_b_id)->where('ap_status', 1)->orderBy('ap_id', 'desc')->get();

        $weekOffs = $user->fh_business->fh_weekOff_policies?->pluck('pwo_name', 'pwo_id')->toArray();
        $formSections = FormSection::where('form_id', $id)->get();




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
        $employee = $id ? Employee::with('fh_employee_qualifications',  'fh_gender', 'fh_marital_status', 'fh_blood_group', 'fh_employee_status', 'fh_employee_type', 'fh_esic_limit', 'fh_pf_master', 'fh_branch', 'fh_department', 'fh_designation', 'fh_work_mode', 'fh_grade', 'fh_job_status', 'fh_attendance_preference:m_id,m_name', 'fh_geofencing:m_id,m_name,m_description')
            ->where('emp_b_id', $user->emp_b_id)
            ->whereRaw('md5(emp_id) = ?', [$id])
            ->first() : null;
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
        $status = MasterTable::where('m_group', 'STATUS')->pluck('m_name', 'm_description')->toArray();
        $supervisor = Employee::where('emp_b_id', $user->emp_b_id)->where('emp_status', 71);
        // if ($employee) {
        //     $supervisor = $supervisor->where('emp_br_id', $employee->emp_br_id);
        // }

        $supervisor = $supervisor->get();
        $businessEmpCode = Business::where([
            'b_id' => $user->emp_b_id,
            'b_emp_code_type' => 190,
        ])->pluck('b_emp_code')->first();

        // Ensure $businessEmpCode is not empty
        if ($businessEmpCode) {
            // Query to get the new employee code
            $newEmpCodeResult = Employee::select(DB::raw("
                    CONCAT(
                        '$businessEmpCode',
                        LPAD(MAX(CAST(SUBSTRING(emp_code, LENGTH('$businessEmpCode') + 1) AS UNSIGNED)) + 1, 2, '0')) AS new_emp_code"))
                ->where('emp_b_id', $user->emp_b_id)
                ->where('emp_code', 'LIKE', "$businessEmpCode%")
                ->first();

            // Get the new_emp_code property
            if ($newEmpCodeResult && $newEmpCodeResult->new_emp_code) {
                $newEmpCode = $newEmpCodeResult->new_emp_code;
            } else {
                // Handle the case where there are no matching emp_code records
                $newEmpCode = $businessEmpCode . '01'; // Start with '01' if no records are found
            }
        } else {
            // Handle the case where $businessEmpCode is not found
            $newEmpCode = null; // Or any default value you prefer
        }

        return view(
            'admin.dynamic_form.show',
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
                'employee',
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
                'formSections'

            )
        );
    }


    public function destroy($id)
    {
        try {
            $form = DynamicForm::findOrFail($id);
            $form->delete();

            return response()->json(['success' => 'Form has been deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the form.'], 500);
        }
    }





    public function submit(Request $request)
    {

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
                // 'prefix' => 'required',
                // 'emp_fname' => 'required',
                // 'emp_phone' => ['required', 'numeric', 'digits_between:10,15'],
                // 'emp_email' => ['required', 'email'],
                // 'emp_dob' => 'required|date',
                // 'emp_gender_id' => 'required',
                // 'emp_status' => 'required',
                // 'emp_type_id' => 'required',
                // 'emp_date_of_joining' => 'required',
                // 'emp_job_status' => 'required',
                // 'emp_br_id' => 'required',
                // 'emp_d_id' => 'required',
                // 'emp_dg_id' => 'required',
                // 'emp_grade_id' => 'required',
                // 'emp_role_id' => 'required',
                // // 'emp_sap_budget_code' => 'required',
                // 'emp_shift_type_id' => 'required',
                // 'emp_work_mode_id' => 'required',
                // 'emp_checkin_method_id' => 'required',
                // // 'emp_account_code' => 'required',
                // 'aadharUpload' => 'nullable',
                // 'drivingUpload' => 'nullable',
                // 'passbookUpload' => 'nullable',
                // 'passportUpload' => 'nullable',
                // 'voterUpload' => 'nullable',
                // 'emp_permanent_address' => 'required',
                // // 'emp_permanent_longitude' => 'required',
                // // 'emp_permanent_latitude' => 'required',
                // 'emp_temporary_pin_code' => 'required',
                // 'regex:/^\d{6}$/',
                // 'emp_permanent_pin_code' => 'required',
                // 'regex:/^\d{6}$/',
                // 'emp_temporary_address' => 'required',
                // // 'emp_temporary_longitude' => 'required',
                // // 'emp_temporary_latitude' => 'required',
                // 'emp_ap_id' => 'required',
                // 'emp_pl_id' => 'required',
                // 'emp_pwo_id' => 'required',
                // 'emp_allow_joining_leave' => 'required',
                // 'emp_profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            ]; //

            if ($request->emp_profile_photo == 'undefined') {
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
            $docs['aadhar_number'] = $request->aadhar_number;
            $docs['account_number'] = $request->account_number;
            $docs['driving_license_number'] = $request->driving_license_number;
            $docs['voter_id_number'] = $request->voter_id_number;
            $docs['passport_number'] = $request->passport_number;
            $docs['pan_number'] = $request->pan_number;


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
                    // 'emp_relative_name' => $request->input('emp_relative_name'),
                    // 'emp_relationship' => $request->input('emp_relationship'),
                    // 'emp_relative_phone_no' => $request->input('emp_relative_phone_no'),

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
                ]
            );

            $profile_url =  null;
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
                    $profile_url = CommonUtils::uploadFiles($request, 'emp_profile_photo', 'uploads/employee_profile', ['prefix' => 'emp_profile']);
                    $profile_url = isset($profile_url[0]) ? url($profile_url[0]) : NULL;
                }
            }

            $employee->update([
                'emp_full_name' => DB::raw("CONCAT(emp_fname, ' ', COALESCE(emp_mname, ''), ' ', emp_lname)"),
                'emp_profile_photo' => $profile_url,
            ]);

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
                $designationName = $request->po_dg_id[$index] ?? null;
                $joiningPeriod = $request->po_joining_period[$index] ?? null;
                $poID = $request->po_id[$index] ?? null;

                // Split the joining period into start and end dates
                $fromDate = $toDate = null; // Initialize variables to default values
                if ($joiningPeriod && strpos($joiningPeriod, ' to ') !== false) {
                    [$fromDate, $toDate] = explode(' to ', $joiningPeriod);
                }

                if (isset($companyName)) {
                    // Create a new instance of the model and save
                    PreviousOrganization::updateOrCreate(
                        [
                            // 'po_emp_id' => $employee->emp_id
                            'po_id' => $poID
                        ],
                        [
                            'po_company_name' => $companyName,
                            'po_emp_id' => $employee->emp_id,
                            'po_designation_name' => $designationName,
                            'po_from_date' => $fromDate,
                            'po_to_date' => $toDate,
                        ]
                    );
                }
            }

            // Creating default password for new employee
            if (!$request->filled('emp_primary_id')) {
                $joining_year = date('Y', strtotime($request->input('emp_date_of_joining')));
                $password = $request->input('emp_code') . $joining_year;
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

            $updates = true;

            DB::commit(); // Commit transaction if all is successful

            return response()->json(['data' => $request->all(), 'section' => $sectionID, 'status' => (bool) $updates]);
            // return response()->json(['data' => $request->all(), 'status' => true]);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if an error occurs
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
