<?php

namespace App\Http\Controllers\Web\Admin;

use Hash;
use Carbon\Carbon;
use App\Models\City;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Admin;
use App\Models\Grade;
use App\Models\State;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Project;
use App\Models\Business;
use App\Models\Employee;
use App\Models\Timezone;
use App\Models\Dealership;
use App\Models\Department;
use App\Imports\RoleImport;
use App\Models\Designation;
use App\Models\MasterTable;
use App\Models\NewUserRole;
use App\Models\PolicyLeave;
use App\Imports\GradeImport;
use App\Models\BusinessBank;
use Illuminate\Http\Request;
use App\Imports\BranchImport;
use App\Models\PolicyWeekOff;
use App\Models\TravelPurpose;
use App\Helpers\CentralLogics;
use App\Models\TadaRequestPlan;
use Illuminate\Validation\Rule;
use App\Models\PolicyAttendance;
use App\Exports\RoleSampleExport;
use App\Imports\DealershipImport;
use App\Imports\DepartmentImport;
use App\Models\PolicyHolidayList;
use App\Models\PolicyShiftTiming;
use Illuminate\Http\JsonResponse;
use App\Exports\GradeSampleExport;
use App\Imports\DesignationImport;
use App\Models\PolicyTadaCategory;
use App\Models\RolesHasPermission;
use App\Exports\BranchSampleExport;
use App\Http\Controllers\Controller;
use App\Models\BusinessPolicyFolder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\RolePermissionLogics;
use App\Models\AttendanceShiftPolicy;
use App\Models\BusinessPolicyDocument;
use App\Exports\DealershipSampleExport;
use App\Exports\DepartmentSampleExport;
use Illuminate\Support\Facades\Session;
use App\Exports\DesignationSampleExport;
use App\Models\ApprovalFlow;
use App\Models\CompOffPolicy;
use App\Models\EmployeeApprovalMapping;
use App\Models\KitStock;
use ChandraHemant\HtkcUtils\CommonUtils;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Http\Response;
use League\Uri\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\SwitchBusinessMailer;
use App\Models\EmailConfiguration;

class SettingController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function account()
    {
        if (Session()->get('firstEmail')) {
            Session::forget('firstEmail');
        }
        $user = Auth::user();
        $branch =  Branch::where('br_b_id', $user->emp_b_id)->count();
        $department =  Department::where('d_b_id', $user->emp_b_id)->count();
        $designation =  Designation::where('dg_b_id', $user->emp_b_id)->count();
        $grade =  Grade::where('g_b_id', $user->emp_b_id)->count();
        $role =  Role::where('role_b_id', $user->emp_b_id)->count();
        $travelpurpose = TravelPurpose::where('tp_b_id', $user->emp_b_id)->count();
        $menuCount = Menu::where('menu_p_id', 19)->count();

        $menu_list = Menu::where('menu_p_id', 19)->get();

        $business = Business::where('b_id', $user->emp_b_id)->first();
        $b_dashboard_id = $business ? $business->b_dashboard_id : null;
        $dealership = Dealership::where('dlr_b_id', $user->emp_b_id)->count();
        $projects = Project::where('ps_b_id', $user->emp_b_id)->count();
        $approvalflowCount = ApprovalFlow::where('afc_b_id', $user->emp_b_id)->count();
        // $kit_details = KitStock::where('b_id', $user->emp_b_id)->count();
        $kit_details = 20;

        return view('admin.setting.business.business', compact('projects', 'designation', 'grade', 'department', 'branch', 'role', 'travelpurpose', 'menu_list', 'menuCount', 'b_dashboard_id', 'dealership', 'approvalflowCount','kit_details'));
    }

    public function business()
    {
        $user = Auth::user();
        $accDetail = Business::with('fh_business_category:m_id,m_name', 'fh_business_type:m_id,m_name', 'fh_currency')->where('b_id', $user->emp_b_id)->with('fh_admin', 'fh_emp_code_type')->first();
        $businessCategory = MasterTable::where('m_group', 'BUSINESS_CATEGORY')->pluck('m_id', 'm_name')->toArray();
        $businessType = MasterTable::where('m_group', 'BUSINESS_TYPE')->pluck('m_id', 'm_name')->toArray();
        $country = Country::orderBy('c_name')->get();
        $emailConfig = EmailConfiguration::where('b_id', Auth::user()->emp_b_id)->first();

        // $time_zones = Timezone::orderBy('tz_id')->where('tz_country_id',$accDetail->b_country_id)->get();
        $time_zones = Timezone::orderBy('tz_id')->get();

        $state = State::where('s_c_id', $accDetail->b_country_id)->orderBy('s_name')->get();
        $city = City::where('ct_s_id', $accDetail->b_state_id)->orderBy('ct_name')->get();
        $empCodeType = MasterTable::where('m_group', 'EMP_CODE')->pluck('m_id', 'm_name')->toArray();
        $owenerName = Employee::where('emp_b_id', $accDetail->b_id)->where('emp_role_id', 1)->first();

        $business_id = $user->emp_b_id;
        $businessfolders = BusinessPolicyFolder::where('pbf_b_id', $business_id)->get();
        $folderIds = $businessfolders->pluck('bpf_id')->count();

        $switchEmails = [];
        if ($accDetail && $accDetail->switch_business_email) {
            $switchEmails = explode(',', $accDetail->switch_business_email);
        }

        return view('admin.setting.account.account', compact('accDetail', 'folderIds', 'businessCategory', 'businessType', 'country', 'emailConfig', 'state', 'city', 'empCodeType', 'owenerName', 'time_zones'));
    }

    //**** account setting page start ****
    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        $data = ['status_code' => 0, 'status_text' => '', 'result' => null];
        if ($request->POST_TYPE == 'LOGO') {
            if ($request->hasFile('image')) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'image.0' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                    ]
                );

                if ($validator->fails()) {
                    // Redirect back with input and validation errors
                    Alert::error('', 'Uploaded image file must be jpeg, png, jpg, gif')->autoClose(3000);
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                // Get the uploaded image file
                $business = Business::where('b_id', $user->emp_b_id)->first();

                $imageUrl =  NULL;
                if (env('STORE_ON_S3')) {
                    $bucket = 'fixhr-uploads';
                    $originalName = $request->image->getClientOriginalName();
                    $imageUniqueName = str_replace(' ', '-', $originalName) . '-' . time(); // Replace spaces with hyphens
                    $imagePath = 'BusinessLogo/' . $business->b_unique_id . '/' . $imageUniqueName;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $request->image);
                    if ($uploadResult['status']) {
                        $imageUrl = $uploadResult['ObjectURL'];
                    }
                } else {
                    $imageUrl = CommonUtils::uploadFiles($request, 'image', 'BusinessLogo/' . $business->b_unique_id, ['prefix' => 'Logo', 'ref_file' => $business->b_logo, 'isArray' => false]);
                    $imageUrl = $imageUrl ? url($imageUrl) : NULL;
                }

                if ($imageUrl) {
                    $data['result'] = $business->update(['b_logo' => $imageUrl]);
                } else {
                    $data['result'] = false;
                }

                if ($data['result']) {
                    $data['status_code'] = 1;
                    $data['status_text'] = 'Business logo updated successfully';
                    Alert::success('', 'Your Business logo has been updated successfully')->autoClose(3000);
                } else {
                    $data['status_text'] = 'Failed to update Business logo';
                    Alert::error('', 'Your Business logo has not been updated')->autoClose(3000);
                }
            } else {
                $data['status_text'] = 'No image file uploaded';
                Alert::error('', 'No image file uploaded')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'CATEGORY') {
            $data['result'] = Business::where('b_id', $user->emp_b_id)->update(['b_category_id' => $request->business_category]);

            if ($data['result']) {
                $data['status_code'] = 1;
                $data['status_text'] = 'Business category updated successfully';
                Alert::success('', 'Your Business category has been updated successfully')->autoClose(3000);
            } else {
                $data['status_text'] = 'Failed to update Business category';
                Alert::error('', 'Your Business category has not been updated')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'NAME') {
            $data['result'] = Business::where('b_id', $user->emp_b_id)->update(['b_name' => $request->business_name]);

            if ($data['result']) {
                $data['status_code'] = 1;
                $data['status_text'] = 'Business name updated successfully';
                Alert::success('', 'Your Business name has been updated successfully')->autoClose(3000);
            } else {
                $data['status_text'] = 'Failed to update Business name';
                Alert::error('', 'Your Business name has not been updated')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'PHONE') {
            $data['result'] = Employee::where('emp_id', $request->adminID)->update(['emp_phone' => $request->phone]);

            if ($data['result']) {
                $data['status_code'] = 1;
                $data['status_text'] = 'Phone number updated successfully';
                Alert::success('', 'Your Phone number has been updated successfully')->autoClose(3000);
            } else {
                $data['status_text'] = 'Failed to update Phone number';
                Alert::error('', 'Your Phone number has not been updated')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'OWNER_NMAE') {
            // Update the Administrator in the Employee model using the emp_id
            $data['result'] = Employee::where('emp_id', $request->adminID)
                ->update(['emp_full_name' => $request->ownername]);

            if ($data['result']) {
                $data['status_code'] = 1;
                $data['status_text'] = 'Administrator updated successfully';
                Alert::success('', 'Your Administrator has been updated successfully')->autoClose(3000);
            } else {
                $data['status_text'] = 'Failed to update Administrator';
                Alert::error('', 'Your Administrator has not been updated')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'EMAIL') {

            if (Employee::where('emp_email', $request->email)->where('emp_status', 71)->first()) {
                $data['status_text'] = 'Email all ready exist';
                Alert::info('', 'Your Business Email has not been updated')->autoClose(3000);
            }

            $update['result'] = Employee::where('emp_id', $request->adminID)->update(['emp_email' => $request->email]);

            if ($update) {
                $data['status_code'] = 1;
                $data['status_text'] = 'Email updated successfully';
                Alert::success('', 'Your Email has been updated successfully')->autoClose(3000);
            } else {
                $data['status_text'] = 'Email update failed';
                Alert::info('', 'Your Email has not been updated')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'BUSINESS_TYPE') {
            $update = Business::where('b_id', $user->emp_b_id)->update(['b_type_id' => $request->business_type]);

            if ($update) {
                $data['status_code'] = 1;
                $data['status_text'] = 'Business type updated successfully';
                Alert::success('', 'Your Business type has been updated successfully')->autoClose(3000);
            } else {
                $data['status_text'] = 'Business type update failed';
                Alert::info('', 'Your Business type has not been updated')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'ADDRESS') {
            $validatedData = Validator::make(
                $request->all(),
                [
                    'country' => ['required'],
                    'state' => ['required'],
                    'city' => ['required'],
                    'pincode' => ['required', 'max:6', 'min:6', 'digits'],
                    'address' => ['required', 'max:255'],
                    'latitude' => ['required', 'numeric'],
                    'longitude' => ['required', 'numeric'],
                ],
                [
                    'country.exists' => 'Invalid country',
                    'state.exists' => 'Invalid state',
                    'city.exists' => 'Invalid city',
                    'pincode.digits' => 'Invalid pincode',
                    'address.max' => 'Address too long',
                    'latitude.numeric' => 'Invalid latitude',
                    'longitude.numeric' => 'Invalid longitude',
                ]
            );

            $address = Business::where('b_id', $user->emp_b_id)
                ->update([
                    'b_country_id' => $request->country,
                    'b_state_id' => $request->state,
                    'b_city_id' => $request->city,
                    'b_pin_code' => $request->pincode,
                    'b_address' => $request->address,
                    'b_latitude' => $request->latitude,
                    'b_longitude' => $request->longitude,
                ]);

            if ($address) {
                $data['status_code'] = 1;
                $data['status_text'] = 'Address updated successfully';
                Alert::success('', 'Your Business address has been updated successfully')->autoClose(3000);
            } else {
                $data['status_text'] = 'Address update failed';
                Alert::info('', 'Your Business address has not been updated')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'EMP_CODE') {
            $validators = Validator(
                $request->all(),
                [
                    'empCodeType' => ['required'],
                ],
                [
                    'empCodeType.required' => 'Employee Code Type is required',
                ]
            );
            if ($validators->fails()) {
                return back();
            }
            if ($request->empCodeType == '190') {
                $validator = Validator(
                    $request->all(),
                    [
                        'empCodeValue' => ['required', 'max:3', 'alpha_num'],
                    ],
                    [
                        'empCodeValue.required' => 'Employee Code is required',
                        'empCodeValue.max' => 'Employee Code should not exceed 3 characters',
                        'empCodeValue.alpha_num' => 'Employee Code should only contain alphabets and numbers',
                    ]
                );
                if ($validator->fails()) {
                    return back();
                }
            }
            $employeeCode = Business::where('b_id', $user->emp_b_id)
                ->update([
                    'b_emp_code_type' => $request->empCodeType,
                    'b_emp_code' => ($request->empCodeType == '190') ? $request->empCodeValue : null,
                ]);
            if ($employeeCode) {
                $data['status_code'] = 1;
                $data['status_text'] = 'Employee Code updated successfully';
                Alert::success('', 'Your Employee Code has been updated successfully')->autoClose(3000);
            } else {
                $data['status_text'] = 'Employee Code update failed';
                Alert::info('', 'Your Employee Code has not been updated')->autoClose(3000);
            }
        } elseif ($request->POST_TYPE == 'CURRENCY') {
            // Validate the incoming request
            $validators = Validator::make($request->all(), [
                'currency' => ['required', 'exists:countries,c_id'], // Ensure currency is required and exists in countries table
            ], [
                'currency.required' => 'Currency is required',
                'currency.exists' => 'Selected currency does not exist',
            ]);

            // Check if validation fails
            if ($validators->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validators->errors()
                ], 422);
            }

            // Update the currency in the business table
            $currencyUpdate = Business::where('b_id', $user->emp_b_id)
                ->update([
                    'b_currency' => $request->currency, // Assuming b_currency is the column for currency
                ]);

            // Check if the update was successful
            if ($currencyUpdate) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Currency updated successfully!',
                ], 200); // 200 OK
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Currency update failed.',
                ], 500); // 500 Internal Server Error
            }
        } elseif ($request->POST_TYPE == 'BANK_DETAILS') {
            $validators = Validator::make($request->all(), [
                'bank_name'      => ['nullable', 'string', 'max:255'],
                'account_number' => ['nullable', 'string', 'max:50'],
                'ifsc_code'      => ['nullable', 'string', 'max:20'],
                'bank_address'   => ['nullable', 'string', 'max:255'],
                'cheque_number'  => ['nullable', 'string', 'max:50'],
            ]);

            if ($validators->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validators->errors()
                ], 422);
            }

            $bankUpdate = Business::where('b_id', $user->emp_b_id)->update([
                'b_bank_name'      => $request->bank_name,
                'b_bank_acc_no' => $request->account_number,
                'b_bank_ifsc'      => $request->ifsc_code,
                'b_bank_address'   => $request->bank_address,
                'b_cheque_no'  => $request->cheque_number,
            ]);

            if ($bankUpdate) {
                Alert::success('', 'Bank details updated successfully!')->autoClose(3000);
            } else {
                Alert::error('', 'Bank details update failed.')->autoClose(3000);
            }
        }

        return redirect()->back();
    }

    public function bankIndex()
    {
        // dd("hello");
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $banks = BusinessBank::where('bb_b_id', $business_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.setting.account.business_banks', compact('banks'));
    }

    public function saveBank(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $bankId = $request->bank_id;

        // Messages
        $messages = [
            'bank_name.required'    => 'Bank name is required.',
            'bank_acc_no.required'  => 'Account number is required.',
            'ifsc_code.required'    => 'IFSC code is required.',
            'branch_name.required'  => 'Branch name is required.',
            'micr.required'         => 'MICR is required.',
            'micr.digits'           => 'MICR must be exactly 9 digits.',
            'bank_acc_no.unique'    => 'This account number already exists for your business.',
        ];

        // Validation
        $request->validate([
            'account_code' => 'required|string|max:20',
            'ifsc_code'    => 'required|string|max:11',
            'bank_name'    => 'required|string|max:255',
            'branch_name'  => 'required|string|max:255',
            'micr'         => 'required|digits:9',
            'branch_code'  => 'required|string|max:50',

            // Unique per business (ignore on update)
            'bank_acc_no' => [
                'required',
                'string',
                'max:18',
                Rule::unique('business_banks', 'bb_bank_acc_no')
                    ->where(fn($q) => $q->where('bb_b_id', $business_id))
                    ->ignore($bankId, 'bb_id'),
            ],

            'account_type' => 'required|string|max:50',
            'bank_address' => 'nullable|string|max:255',
            'cheque_no'    => 'nullable|string|max:50',
            'status'  => 'nullable|boolean',
        ], $messages);

        // Map Input → DB Columns
        $data = [
            'bb_b_id'          => $business_id,
            'bb_account_code'  => $request->account_code,
            'bb_ifsc_code'     => $request->ifsc_code,
            'bb_bank_name'     => $request->bank_name,
            'bb_branch_name'   => $request->branch_name,
            'bb_micr'          => $request->micr,
            'bb_branch_code'   => $request->branch_code,
            'bb_bank_acc_no'   => $request->bank_acc_no,
            'bb_account_type'  => $request->account_type,
            'bb_bank_address'  => $request->branch_name,
            'bb_cheque_no'     => $request->cheque_no,
            'bb_bank_status'   => $request->boolean('status') ? 1 : 0,
        ];

        // UPDATE
        if ($bankId) {
            $bank = BusinessBank::find($bankId);

            if (!$bank) {
                return response()->json(['message' => 'Bank not found.'], 404);
            }

            $bank->update($data);

            return response()->json(['message' => 'Bank updated successfully.']);
        }

        // CREATE
        BusinessBank::create($data);

        return response()->json(['message' => 'Bank added successfully.']);
    }

    public function fetchIFSCDetails(Request $request)
    {
        // dd("hello");
        $ifsc = strtoupper(trim($request->ifsc));
        $pattern = '/^[A-Z]{4}0[A-Z0-9]{6}$/';

        // Validate IFSC format
        if (!preg_match($pattern, $ifsc)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid IFSC code format'
            ]);
        }

        // Fetch API response
        $json_result = @file_get_contents("https://ifsc.razorpay.com/" . $ifsc);

        if ($json_result === false) {
            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch data from IFSC API'
            ]);
        }

        $output = json_decode($json_result, true);

        if (!is_array($output)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid API response'
            ]);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'bank_name' => $output['BANK'] ?? '',
                'branch'    => $output['BRANCH'] ?? '',
                'address'   => $output['ADDRESS'] ?? '',
                'micr'      => $output['MICR'] ?? '',
                'bank_code' => $output['BANKCODE'] ?? '',
                'city'      => $output['CITY'] ?? '',
                'state'     => $output['STATE'] ?? '',
            ]
        ]);
    }

    public function deleteBank($id)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $bank = BusinessBank::where('bb_b_id', $business_id)->where('bb_id', $id)->first();

        if (!$bank) {
            return response()->json(['error' => 'Bank not found or unauthorized'], 404);
        }

        $bank->delete();

        return response()->json(['success' => 'Bank deleted successfully']);
    }

    public function getCountryStateCityAjax(Request $request)
    {
        $state = [];
        $city = [];
        if (isset($request->country)) {
            $state = State::where('s_c_id', $request->country)->orderBy('s_name')->get();
        } elseif (isset($request->state)) {
            $city = City::where('ct_s_id', $request->state)->orderBy('ct_name')->get();
        }
        return response()->json(['states' => $state, 'city' => $city]);
    }

    // business setting
    public function branches(Request $request)
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['br_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['br_id', 'br_code', 'br_name', 'br_email', 'br_address', 'br_longitude', 'br_latitude', 'updated_at', 'br_range_limit', 'br_is_active', 'br_c_id', 'br_s_id', 'br_is_wifi_restricted', 'br_wifi_address'],
                    'relation' => ['fh_business:b_id']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['br_id', 'br_code', 'br_name', 'br_email', 'updated_at', 'br_id']
                ]
            ];

            $searchColumns = ['br_b_id', 'br_code', 'br_name', 'br_email', 'updated_at'];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Branch(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->br_code;
                $row[] = $val->br_name;
                $row[] = $val->br_email;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                 <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';

                if (($val->br_id  !=  $user->emp_br_id) || ($user->emp_role_id == 1)) {
                    $row[] = '
                        <div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                    <li>
                                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-target="#editBranchName"
                                            onclick="openEditDesignation(this)"
                                            data-id="' . $val->br_id . '"
                                            data-c_id="' . $val->br_c_id . '"
                                            data-s_id="' . $val->br_s_id . '"
                                            data-branch_name="' . $val->br_name . '"
                                            data-code="' . $val->br_code . '"
                                            data-branch_email="' . $val->br_email . '"
                                            data-address="' . $val->br_address . '"
                                            data-longitude="' . $val->br_longitude . '"
                                            data-latitude="' . $val->br_latitude . '"
                                            data-isactive="' . $val->br_is_active . '"
                                            data-rangelimitSet="' . $val->br_range_limit . '"
                                            data-bs-toggle="modal"
                                            data-iswifirestricted="' . $val->br_is_wifi_restricted . '"
                                            data-wifiaddress="' . $val->br_wifi_address . '"
                                            type="button">
                                            <i class="feather feather-edit"></i> Edit
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal"
                                            onclick="ItemDeleteModel(this)"
                                            data-branch_id="' . $val->br_id . '"
                                            data-branch_name="' . $val->br_name . '"
                                            data-bs-target="#branchDeletebtn"
                                            title="Edit"
                                            type="button">
                                            <i class="feather feather-trash"></i> Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>';
                } else {
                    $row[] = '';
                }

                $rowData[] = $row;
                // Corrected this line to add the row to rowData array
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Branch(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Branch Code',
            'Branch Name',
            'Email',
            'Updated At',
            'Action',
        ];

        $countries = Country::pluck('c_name', 'c_id')->toArray();
        $states = State::all();
        return view('admin.setting.business.branches', compact('branch', 'columns', 'countries', 'states'));
    }

    public function addBranch(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'branch' => 'required|string|max:255|unique:branches,br_name,NULL,br_id,br_b_id,' . $user->emp_b_id,
            'code' => 'required|string|max:255|unique:branches,br_code,NULL,br_id,br_b_id,' . $user->emp_b_id,
            'location' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'country' => 'required',
            'state' => 'required',
        ]);

        $is_active = $request->is_active != null ? ($request->is_active == 'on' ? '1' : '0') : '0';
        $is_wifi_restricted = $request->is_wifi_restricted != null ? ($request->is_wifi_restricted == 'on' ? '1' : '0') : '0';
        $data = [
            'br_b_id' => $user->emp_b_id,
            'br_name' => $request->branch,
            'br_code' => $request->code,
            'br_email' => $request->email,
            'br_is_active' => $is_active,
            'br_range_limit' => $request->range_limit,
            'br_address' => $request->location,
            'br_longitude' => $request->longitude,
            'br_latitude' => $request->latitude,
            'updated_at' => CentralLogics::timezone_configure(),
            'br_c_id' => $request->country,
            'br_s_id' => $request->state,
            'br_is_wifi_restricted' => $is_wifi_restricted,
            'br_wifi_address' => $request->wifi_address,
        ];
        $addBranch = Branch::insert($data);

        if ($addBranch) {
            return response()->json(['success' => 'Your Branch has been created successfully.']);
        } else {
            return response()->json(['error' => 'Your Branch has not been created.']);
        }
    }

    public function updateBranch(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'branch' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'br_name')
                    ->where(function ($query) use ($user) {
                        return $query->where('br_b_id', $user->emp_b_id);
                    })
                    ->ignore($request->editBranchId, 'br_id'), // Ignore the current record's ID
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'br_code')
                    ->where(function ($query) use ($user) {
                        return $query->where('br_b_id', $user->emp_b_id);
                    })
                    ->ignore($request->editBranchId, 'br_id'), // Ignore the current record's ID
            ],
            'address' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'edit_country' => 'required',
            'edit_state' => 'required',
        ], [

            'edit_country.required' => 'The country name is required.',
            'edit_state.required' => 'The state name is required.',
            'branch.required' => 'The branch name is required.',
            'branch.string' => 'The branch name must be a string.',
            'branch.max' => 'The branch name must not exceed 255 characters.',
            'branch.unique' => 'The branch name has already been taken.',
            'code.required' => 'The code is required.',
            'code.string' => 'The code must be a string.',
            'code.max' => 'The code must not exceed 255 characters.',
            'code.unique' => 'The code has already been taken.',
            'address.required' => 'The location is required.',
            'longitude.required' => 'The longitude is required.',
            'latitude.required' => 'The latitude is required.',
        ]);

        $is_active = $request->is_active != null ? ($request->is_active == 'on' ? '1' : '0') : '0';
        $is_wifi_restricted = $request->is_wifi_restricted != null ? ($request->is_wifi_restricted == 'on' ? '1' : '0') : '0';
        $branch = Branch::where('br_id', $request->editBranchId)
            ->where('br_b_id', $user->emp_b_id)
            ->update([
                'br_name' => $request->branch,
                'br_code' => $request->code,
                'br_email' => $request->email,
                'br_is_active' => $is_active,
                'br_range_limit' => $request->range_limit,
                'br_address' => $request->address,
                'br_longitude' => $request->longitude,
                'br_latitude' => $request->latitude,
                'updated_at' => CentralLogics::timezone_configure(),
                'br_c_id' => $request->edit_country,
                'br_s_id' => $request->edit_state,
                'br_is_wifi_restricted' => $is_wifi_restricted,
                'br_wifi_address' => $request->wifi_address,
            ]);

        if (isset($branch)) {
            return response()->json(['success' => 'Your Branch has been updated successfully.']);
        } else {
            return response()->json(['success' => 'Your Branch has been not updated.']);
        }
    }

    public function branchesSampleExport()
    {
        return Excel::download(new BranchSampleExport, 'branch_upload_format.xlsx');
    }

    public function importBranches(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $branchImport = new BranchImport(Auth::user());

        try {
            // Attempt the import
            Excel::import($branchImport, $file);
            $errorMessages = $branchImport->getErrorMessages();
            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            // If no errors, redirect with success message
            return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deleteBranch(Request $request)
    {
        $user = Auth::user();
        $branch_id = $request->branch_id;
        $checkMatch = Employee::where('emp_b_id', $user->emp_b_id)
            ->where('emp_br_id', $branch_id)
            ->first();

        if (isset($checkMatch)) {
            Alert::error('', 'You cannot delete the Branch if you have an employee associated with it.')->autoClose(3000);
        } else {
            Branch::where('br_b_id', $user->emp_b_id)
                ->where('br_id', $branch_id)
                ->delete();
            Alert::success('', 'Your Branch has been deleted successfully')->autoClose(3000);
        }
        return redirect()->back();
    }

    public function department(Request $request)
    {
        // $user = Auth::user();
        $business = $this->user->fh_business()
            ->with([
                'fh_departments:d_id,d_b_id,d_name',                           // Include department data
                'fh_attendance_policies:ap_id,ap_b_id,ap_name',                // Include attendance policies
                'fh_shift_timings:pst_id,pst_b_id,pst_name,pst_auto_assign_shift', // Include shift timings
                'fh_holiday_policies:phl_id,phl_b_id,phl_name',              // Include holiday policies
                'fh_leave_policies:pl_id,pl_b_id,pl_name',                   // Include leave policies
                'fh_weekOff_policies:pwo_id,pwo_b_id,pwo_name',              // Include week-off policies
            ])
            ->where('b_id', $this->user->emp_b_id)
            ->get();

        // Extract data for departments
        $department = $business->first()->fh_departments->count();

        // Extract data for attendance policies
        $attendancePolicies = $business->first()->fh_attendance_policies->pluck('ap_name', 'ap_id')->toArray();

        // Extract data for shift timings
        // $shiftTimings = $business->first()->fh_shift_timings->pluck('pst_name', 'pst_id')->toArray();

        $businessModel = $business->first();

        $shiftTimings = $businessModel
            ? $businessModel->fh_shift_timings
                ->where('pst_auto_assign_shift', 1)
                ->pluck('pst_name', 'pst_id')
                ->toArray()
            : [];

        // Extract data for holiday policies
        $holidayPolicies = $business->first()->fh_holiday_policies->pluck('phl_name', 'phl_id')->toArray();

        // Extract data for leave policies
        $leavePolicies = $business->first()->fh_leave_policies->pluck('pl_name', 'pl_id')->toArray();

        // Extract data for week-off policies
        $weekOffPolicies = $business->first()->fh_weekOff_policies->pluck('pwo_name', 'pwo_id')->toArray();

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['d_b_id',  $this->user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['d_id', 'd_b_id', 'd_name', 'd_ap_id', 'd_pst_id', 'd_phl_id', 'd_pl_id', 'd_pwo_id', 'd_status', 'updated_at'],
                    'relation' => ['fh_business:b_id']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['d_id', 'd_name', 'd_ap_id', 'd_pst_id', 'd_phl_id', 'd_pl_id', 'd_pwo_id', 'updated_at', 'd_id'],
                ]
            ];

            $searchColumns = ['d_name', 'updated_at'];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Department(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->d_name;
                $row[] = optional($val->fh_policy_attendance)->ap_name ?? '---';
                $row[] = optional($val->fh_policy_shift_timing)->pst_name ?? '---';
                $row[] = optional($val->fh_policy_holiday_list)->phl_name ?? '---';
                $row[] = optional($val->fh_policy_leave)->pl_name ?? '---';
                $row[] = optional($val->fh_policy_week_off)->pwo_name ?? '---';
                $row[] = '<span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                $editUrl = route('delete.department', $val->d_id);

                if (($val->d_id !=  $this->user->emp_d_id) || ($this->user->emp_role_id == 1)) {
                    $row[] = '
                            <div class="btn-list ms-3">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                        <li>
                                            <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-button"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-title="Edit Department"
                                                data-bs-target="#createDepartmentModal"
                                                data-edit-data=\'' . json_encode([
                        'id' => md5($val->d_id),
                        'd_name' => e($val->d_name),
                        'd_ap_id' => e($val->d_ap_id),
                        // 'd_pst_id' => e($val->d_pst_id),
                        'd_pst_id' => $val->d_pst_id ? explode(',', $val->d_pst_id) : [],
                        'd_phl_id' => e($val->d_phl_id),
                        'd_pl_id' => e($val->d_pl_id),
                        'd_pwo_id' => e($val->d_pwo_id),
                    ]) . '\'>
                                                <i class="feather feather-edit"></i> Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-button"
                                                type="button"
                                                data-id="' . $val->d_id . '"
                                                title="Delete"
                                                data-url="' . $editUrl . '">
                                                <i class="feather feather-trash"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>';
                } else {
                    $row[] = '';
                }

                $rowData[] = $row;

            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Department(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Department Name',
            'Attendance Policy',
            'Shift Policy',
            'Holiday Policy',
            'Leave Policy',
            'Weekly Policy',
            'W.E.F.',
            'Action',
        ];

        return view('admin.setting.business.department', compact('department', 'columns', 'attendancePolicies', 'shiftTimings', 'holidayPolicies', 'leavePolicies', 'weekOffPolicies'));
    }

    public function addDepartment(Request $request)
    {
        // If the ID is provided, get the actual numeric department ID

        try {
            $hashedRsId = $request->id; // The MD5-hashed rs_id sent from the client
            $department = Department::whereRaw('MD5(d_id) = ?', [$hashedRsId])->first();
            $departmentId = $request->id ? $department->d_id : null;
            $validatedData = $request->validate([
                'd_name' => 'required|unique:departments,d_name,' . ($departmentId ? $departmentId : 'NULL') . ',d_id,d_b_id,' . $this->user->emp_b_id,
            ], [
                'd_name.required' => 'Department name is required',
                'd_name.unique' => 'Department name already exists',

            ]);

            if ($department) {
                // Update the existing record
                $department->update([
                    'd_b_id' => $this->user->emp_b_id,
                    'd_name' => $request->d_name,
                    'd_ap_id' => $request->d_ap_id,
                    'd_pst_id' => !empty($request->d_pst_id) && is_array($request->d_pst_id)
                                    ? implode(',', $request->d_pst_id)
                                    : '',
                    // 'd_pst_id' => $request->d_pst_id,
                    'd_phl_id' => $request->d_phl_id,
                    'd_pl_id' => $request->d_pl_id,
                    'd_pwo_id' => $request->d_pwo_id,
                    'd_status' => $request->d_status ?? 1,
                ]);
            } else {
                // Create a new record if no match is found
                $department = Department::create([
                    'd_b_id' => $this->user->emp_b_id,
                    'd_name' => $request->d_name,
                    'd_ap_id' => $request->d_ap_id,
                    'd_pst_id' => !empty($request->d_pst_id) && is_array($request->d_pst_id)
                                    ? implode(',', $request->d_pst_id)
                                    : '',
                    'd_phl_id' => $request->d_phl_id,
                    'd_pl_id' => $request->d_pl_id,
                    'd_pwo_id' => $request->d_pwo_id,
                    'd_status' => $request->d_status ?? 1,
                ]);
            }

            $message = $request->id ? 'Updated' : 'Created';
            return response()->json([
                'status' => 'success',
                'message' => 'Department ' . $message . ' successfully!',
                // 'data' => $department // Include the department data
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-related errors
            return response()->json([
                'status' => 'error',
                'message' => 'Database error occurred: ' . $e->getMessage()
            ], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error occurred: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDepartment(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'edit_department_name' => [
                'required',
                Rule::unique('departments', 'd_name')
                    ->where(function ($query) use ($user) {
                        return $query->where('d_b_id', $user->emp_b_id);
                    })
                    ->ignore($request->editid, 'd_id'), // Ignore the current record's ID
            ],
        ], [
            'edit_department_name.unique' => 'The department name has already been taken.'
        ]);

        // Perform the update
        $department = Department::where('d_id', $request->editid)
            ->where('d_b_id', $user->emp_b_id)
            ->update([
                'd_b_id' => $user->emp_b_id,
                'd_name' => $request->edit_department_name,
            ]);

        if ($department) {
            return response()->json(['success' => 'Your Department has been updated successfully.']);
        } else {
            return response()->json(['error' => 'Your Department has not been updated.']);
        }
    }

    public function DeleteDepartment($id)
    {
        if ($this->user) {
            $result = CentralLogics::dynamicDelete(Department::class, $id);
            if (isset($result['success'])) {
                return response()->json(['success' => $result['success'], 'message' => 'Department has been deleted successfully']);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }

    public function departmentSampleExport()
    {
        return Excel::download(new DepartmentSampleExport, 'department_upload_format.xlsx');
    }

    public function importDepartment(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $departmentImport = new DepartmentImport(Auth::user());

        try {
            // Attempt the import
            Excel::import($departmentImport, $file);
            $errorMessages = $departmentImport->getErrorMessages();
            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                // return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            // If no errors, redirect with success message
            // return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            // return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function designationSampleExport()
    {
        return Excel::download(new DesignationSampleExport, 'designation_upload_format.xlsx');
    }

    public function importDesignation(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $designationImport = new DesignationImport(Auth::user());

        try {
            // Attempt the import
            Excel::import($designationImport, $file);
            $errorMessages = $designationImport->getErrorMessages();
            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            // If no errors, redirect with success message
            return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function dealership(Request $request)
    {
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['dlr_b_id',  $this->user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['dlr_id', 'dlr_code', 'dlr_name', 'updated_at'],
                    'relation' => ['fh_business:b_id']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['dlr_id', 'dlr_code', 'dlr_name', 'updated_at'],
                ]
            ];

            $searchColumns = ['d_name', 'updated_at'];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Dealership(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            // dd('kya list h',$list);
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->dlr_code;
                $row[] = $val->dlr_name;
                $row[] =  '<span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                $editUrl = route('delete.dealership', $val->dlr_id);
                if (($val->dlr_id !=  $this->user->emp_dlr_id) || ($this->user->emp_role_id == 1)) {
                    $row[] = '
                            <div class="btn-list ms-3">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                        <li>
                                            <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#createDealershipModal"
                                                data-title="Edit Dealership"
                                                data-edit-data=\'' . json_encode([
                        'id' => md5($val->dlr_id),
                        'dlr_code' => $val->dlr_code,
                        'dlr_name' => e($val->dlr_name),
                    ]) . '\'
                                                type="button">
                                                <i class="feather feather-edit"></i> Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-button"
                                                data-id="' . $val->dlr_id . '"
                                                title="Delete"
                                                data-url="' . $editUrl . '"
                                                type="button">
                                                <i class="feather feather-trash"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>';
                } else {
                    $row[] = '';
                }

                $rowData[] = $row;
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Dealership(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Dealership Name',
            'Dealership Code',
            'W.E.F.',
            'Action',
        ];

        return view('admin.setting.business.dealership', compact('columns'));
    }

    public function addDealership(Request $request)
    {
        // If the ID is provided, get the actual numeric department ID

        try {
            $hashedRsId = $request->id; // The MD5-hashed rs_id sent from the client
            $dealership = Dealership::whereRaw('MD5(dlr_id) = ?', [$hashedRsId])->first();
            $dealershipId = $request->id ? $dealership->dlr_id : null;
            $validatedData = $request->validate([
                'dlr_code' => 'required|unique:dealerships,dlr_code,' . ($dealershipId ? $dealershipId : 'NULL') . ',dlr_id,dlr_b_id,' . $this->user->emp_b_id,
            ], [
                'dlr_code.required' => 'Dealership code is required',
                'dlr_code.unique' => 'Dealership code already exists',

            ]);

            if ($dealership) {
                // Update the existing record
                $dealership->update([
                    'dlr_b_id' => $this->user->emp_b_id,
                    'dlr_code' => $request->dlr_code,
                    'dlr_name' => $request->dlr_name,
                ]);
            } else {
                // Create a new record if no match is found
                $dealership = Dealership::create([
                    'dlr_b_id' => $this->user->emp_b_id,
                    'dlr_code' => $request->dlr_code,
                    'dlr_name' => $request->dlr_name,
                ]);
            }

            $message = $request->id ? 'Updated' : 'Created';
            return response()->json([
                'status' => 'success',
                'message' => 'Dealership ' . $message . ' successfully!',
                // 'data' => $department // Include the department data
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-related errors
            return response()->json([
                'status' => 'error',
                'message' => 'Database error occurred: ' . $e->getMessage()
            ], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error occurred: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function DeleteDealership($id)
    {
        if ($this->user) {
            $result = CentralLogics::dynamicDelete(Dealership::class, $id);
            if (isset($result['success'])) {
                return response()->json(['success' => $result['success'], 'message' => 'Dealership has been deleted successfully']);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }

    public function dealershipSampleExport()
    {
        return Excel::download(new DealershipSampleExport, 'dealership_upload_format.xlsx');
    }

    public function importDealership(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $dealershipImport = new DealershipImport(Auth::user());

        try {
            Excel::import($dealershipImport, $file);
            $errorMessages = $dealershipImport->getErrorMessages();

            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                // return redirect()->back()->with('error', 'Import completed with issues. Check errors.');
            }
            // return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            // return redirect()->back()->with('error', 'Something went wrong during import. Please check the logs.');
        }
    }

    public function designation(Request $request)
    {
        $user = Auth::user();
        $Designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['dg_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['dg_id', 'dg_b_id', 'dg_name', 'updated_at'],
                    'relation' => ['fh_business:b_id']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['dg_id', 'dg_name', 'updated_at', 'dg_id']
                ]
            ];
            $searchColumns = ['dg_name', 'updated_at'];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Designation(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->dg_name;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                    <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';

                if (($val->dg_id != $user->emp_dg_id) || ($user->emp_role_id == 1)) {
                    $row[] = '
                        <div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                    <li>
                                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            type="button"
                                            onclick="openEditDesignation(this)"
                                            data-id="' . $val->dg_id . '"
                                            data-dg_name="' . $val->dg_name . '">
                                            <i class="feather feather-edit"></i> Edit
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                            type="button"
                                            onclick="openDeleteDesignation(this)"
                                            data-id="' . $val->dg_id . '"
                                            data-dg_name="' . $val->dg_name . '">
                                            <i class="feather feather-trash"></i> Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>';
                } else {
                    $row[] = '';
                }

                $rowData[] = $row;
            }
            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Designation(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];
            return json_encode($output);
        }
        $columns = [
            'S. No.',
            'Designation Name',
            '',
            'Action',
        ];
        return view('admin.setting.business.designation', compact('Designation', 'columns'));
    }

    public function addDesignation(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'designation' => 'required|unique:designations,dg_name,NULL,dg_id,dg_b_id,' . $user->emp_b_id,
        ]);
        $designation  = Designation::insert([
            'dg_b_id' => $user->emp_b_id,
            'dg_name' => $request->designation,
        ]);
        if ($designation) {
            return response()->json(['success' => 'Your Designation  has been created successfully.']);
        } else {
            return response()->json(['error' => 'Your Designation  has not been created.']);
        }
    }

    public function updateDesignation(Request $request)
    {
        $user = Auth::user();
        $request->validate(
            [
                'edit_desig_name' => [
                    'required',
                    Rule::unique('designations', 'dg_name')
                        ->where(function ($query) use ($user) {
                            $query->where('dg_b_id', $user->emp_b_id);
                        })
                        ->ignore($request->editid, 'dg_id'), // Ignore the current record's ID
                ],
            ],
            [
                'edit_desig_name.unique' => 'The Designation name has already been taken.'
            ]
        );

        $designation = Designation::where(['dg_id' => $request->editid, 'dg_b_id' => $user->emp_b_id])
            ->update([
                'dg_name' => $request->edit_desig_name,
            ]);
        if (isset($designation)) {
            Alert::success('', 'Your Designation has been Updated Successfully')->autoClose(3000);
        } else {
            Alert::error('', 'Your Designation has not been Updated')->autoClose(3000);
        }
        if ($designation) {
            return response()->json(['success' => 'Your Designation has been updated successfully.']);
        } else {
            return response()->json(['error' => 'Your Designation has not been updated.']);
        }
    }

    public function deleteDesignation(Request $request)
    {
        $user = Auth::user();
        $employeeExists  = Employee::where([
            'emp_b_id' => $user->emp_b_id,
            'emp_dg_id' => $request->deleteId,
        ])->first();
        if ($employeeExists) {
            Alert::error('', 'You cannot delete the Designation if you have an employee associated with it.')->autoClose(3000);
        } else {
            $deleted  = Designation::where(['dg_id' => $request->deleteId, 'dg_b_id' => $user->emp_b_id])->delete();
            if (isset($deleted)) {
                Alert::success('', 'Your Designation has been deleted successfully')->autoClose(3000);
            } else {
                Alert::error('', 'Your Designation has not been Deleted')->autoClose(3000);
            }
        }
        return redirect()->back();
    }

    public function grade(Request $request)
    {
        $user = Auth::user();
        $Grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['g_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['g_id', 'g_b_id', 'g_name', 'updated_at'],
                    'relation' => ['fh_business:b_id']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['g_id', 'g_name', 'updated_at', 'g_id']
                ]
            ];
            $searchColumns = ['g_name', 'updated_at'];
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Grade(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();
            $rowData = [];
            $i = 0;

            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->g_name;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
            <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';

                if (($val->g_id != $user->emp_grade_id) || ($user->emp_role_id == 1)) {
                    $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                    type="button"
                                    onclick="openEditGrade(this)"
                                    data-id="' . $val->g_id . '"
                                    data-g_name="' . $val->g_name . '">
                                    <i class="feather feather-edit"></i> Edit
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                    type="button"
                                    onclick="openDeleteGrade(this)"
                                    data-id="' . $val->g_id . '"
                                    data-g_name="' . $val->g_name . '">
                                    <i class="feather feather-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>';
                } else {
                    $row[] = '';
                }

                $rowData[] = $row;

            }
            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Grade(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];
            return json_encode($output);
        }
        $columns = [
            'S. No.',
            'Grade Name',
            '',
            'Action',
        ];
        return view('admin.setting.business.grade', compact('Grade', 'columns'));
    }

    public function addGrade(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'grade' => 'required|unique:grades,g_name,NULL,g_id,g_b_id,' . $user->emp_b_id,
        ]);
        $grade = Grade::create([
            'g_b_id' => $user->emp_b_id,
            'g_name' => $request->grade,
        ]);
        if ($grade) {
            return response()->json(['success' => 'Your Grade has been created successfully.']);
        } else {
            return response()->json(['error' => 'Your Grade has not been created.']);
        }
    }

    public function updateGrade(Request $request)
    {
        $user = Auth::user();
        $request->validate(
            [
                'edit_grade_name' => [
                    'required',
                    Rule::unique('grades', 'g_name')
                        ->where(function ($query) use ($user) {
                            return $query->where('g_b_id', $user->emp_b_id);
                        })
                        ->ignore($request->editid, 'g_id'), // Ignore the current record's ID
                ],
            ],
            [
                'edit_grade_name.unique' => 'The grade name has already been taken.'
            ]
        );
        $grade = Grade::where(['g_id' => $request->editid, 'g_b_id' => $user->emp_b_id])
            ->update([
                'g_name' => $request->edit_grade_name,
            ]);

        if (isset($grade)) {
            return response()->json(['success' => 'Your Grade has been updated successfully.']);
        } else {
            return response()->json(['success' => 'Your Grade has been not updated.']);
        }
    }

    public function deleteGrade(Request $request)
    {
        $user = Auth::user();
        $deletedId = $request->deleteId;
        $businessId = $user->emp_b_id;
        $employeeExists  = Employee::where([
            'emp_b_id' => $businessId,
            'emp_grade_id' => $deletedId,
        ])->first();
        $errorMessage = '';

        $policyCategoryExists = PolicyTadaCategory::where([
            'ptc_b_id' => $businessId,
            'ptc_grade_id' => $deletedId,
        ])->first();
        $message = 'You cannot delete the Grade if you have an ';
        if ($employeeExists && $policyCategoryExists) {
            $message .= 'employee and policy category';
        } elseif ($employeeExists) {
            $message .= 'employee';
        } elseif ($policyCategoryExists) {
            $message .= 'policy category';
        }
        $message .= ' associated with it.';
        if ($employeeExists || $policyCategoryExists) {
            Alert::error('', $message)->autoClose(3000);
        } else {
            $deleted  = Grade::where(['g_id' => $deletedId, 'g_b_id' => $businessId])->delete();
            if (isset($deleted)) {
                Alert::success('', 'Your Grade has been deleted successfully')->autoClose(3000);
            } else {
                Alert::error('', 'Your Grade has not been Deleted')->autoClose(3000);
            }
        }
        return redirect()->back();
    }

    public function gradeSampleExport()
    {
        return Excel::download(new GradeSampleExport, 'grade_upload_format.xlsx');
    }

    public function importGrade(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $gradeImport = new GradeImport(Auth::user());

        try {
            // Attempt the import
            Excel::import($gradeImport, $file);
            $errorMessages = $gradeImport->getErrorMessages();
            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            // If no errors, redirect with success message
            return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function holidayPolicy()
    {
        return view('admin.setting.business.holiday_policy');
    }

    public function role(Request $request)
    {
        $user = Auth::user();
        $Role = Role::where('role_b_id', $user->emp_b_id)->get();
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['role_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['role_id', 'role_b_id', 'role_name', 'role_description', 'updated_at'],
                    'relation' => ['fh_business:b_id']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['role_id', 'role_name', 'role_description', 'updated_at', 'role_id']
                ],
            ];
            $searchColumns =
                [
                    'role_name',
                    'role_description',
                    'updated_at',
                ];
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Role(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();
            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->role_name;
                $row[] = $val->role_description;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                    <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';

                if (($val->role_id != $user->emp_role_id) || ($user->emp_role_id == 1)) {
                    $row[] = '
                        <div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                    <li>
                                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            type="button"
                                            onclick="openEditRole(this)"
                                            data-id="' . $val->role_id . '"
                                            data-role_name="' . $val->role_name . '"
                                            data-role_description="' . $val->role_description . '">
                                            <i class="feather feather-edit"></i> Edit
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                            type="button"
                                            onclick="openDeleteRole(this)"
                                            data-id="' . $val->role_id . '"
                                            data-role_name="' . $val->role_name . '"
                                            data-role_description="' . $val->role_description . '">
                                            <i class="feather feather-trash"></i> Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>';
                } else {
                    $row[] = '';
                }

                $rowData[] = $row;

            }
            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Role(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];
            return json_encode($output);
        }
        $columns = [
            'S. No.',
            'Role Name',
            'Description',
            '',
            'Action',
        ];
        return view('admin.setting.business.role', compact('Role', 'columns'));
    }

    public function addRole(Request $request): JsonResponse
    {
        $user = Auth::user();
        $request->validate([
            'role' => 'required|unique:roles,role_name,NULL,role_id,role_b_id,' . $user->emp_b_id,
            'description' => 'required',
        ]);
        $role = Role::create([
            'role_b_id' => $user->emp_b_id,
            'role_name' => $request->role,
            'role_description' => $request->description,
        ]);
        if ($role) {
            return response()->json(['success' => 'Your Role has been created successfully.']);
        } else {
            return response()->json(['error' => 'Your Role has not been created.']);
        }
    }

    public function updateRole(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'edit_role_name' => [
                'required',
                Rule::unique('roles', 'role_name')
                    ->where(function ($query) use ($user) {
                        return $query->where('role_b_id', $user->emp_b_id);
                    })
                    ->ignore($request->editid, 'role_id'), // Ignore the current record's ID
            ],
            'edit_role_description' => 'required',
        ]);
        $role = Role::where(['role_id' => $request->editid, 'role_b_id' => $user->emp_b_id])
            ->update([
                'role_name' => $request->edit_role_name,
                'role_description' => $request->edit_role_description,
            ]);
        if ($role) {
            return response()->json(['success' => 'Your Role has been updated successfully.']);
        } else {
            return response()->json(['error' => 'Your Role has not been updated.']);
        }
    }

    public function newUserRole(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'role' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:new_user_roles,nur_email,' . $request->nur_id . ',nur_id',
            'password' => [
                $request->nur_id ? 'nullable' : 'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,20}$/'
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $checkEmployee = Employee::where('emp_email', $request->email)->first();
        if ($checkEmployee) {
            return response()->json(['error' => 'This user already exists in employee.']);
        }

        $role = new Role;
        $role->role_b_id = $user->emp_b_id;
        $role->role_name = $request->role;
        $role->role_description = $request->description;

        if ($role->save()) {
            $newUserRole = NewUserRole::updateOrCreate(
                ['nur_id' => $request->nur_id],
                [
                    'nur_role_id' => $role->role_id,
                    'nur_b_id' => $role->role_b_id,
                    'nur_email' => $request->email,
                    'nur_password' => $request->filled('password') ? Hash::make($request->password) : null,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => $request->nur_id ? 'User role updated successfully.' : 'User role created successfully.',
                'data' => $newUserRole
            ]);
        }

        return response()->json(['error' => 'Your Role has not been created.']);
    }

    public function deleteRole(Request $request)
    {
        $user = Auth::user();
        $employeeExists  = Employee::where(['emp_b_id' => $user->emp_b_id, 'emp_role_id' => $request->deleteId,])->first();
        $roleExists = RolesHasPermission::where('rhp_role_id', $request->deleteId)->first();

        if ($employeeExists && $roleExists) {
            return response()->json(['error' => 'You cannot delete the Role if you have an employee and assigned permissions.'], 400);
        } else if ($employeeExists) {
            return response()->json(['error' => 'You cannot delete the Employee if you have an employee associated with it.'], 400);
        } else if ($roleExists) {
            return response()->json(['error' => 'You cannot delete the Role if you have assigned permissions.'], 400);
        } else {
            $deleted  = Role::where(['role_id' => $request->deleteId, 'role_b_id' => $user->emp_b_id])->delete();
            if ($deleted) {
                Alert::success('', 'Your Role has been deleted successfully')->autoClose(3000);
                return response()->json(['success' => 'Your Role has been deleted successfully.'], 200);
            } else {
                return response()->json(['error' => 'Your Role has not been deleted.'], 500);
            }
        }
    }

    public function roleSampleExport()
    {
        return Excel::download(new RoleSampleExport, 'role_upload_format.xlsx');
    }

    public function importRole(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $roleImport = new RoleImport(Auth::user());

        try {
            // Attempt the import
            Excel::import($roleImport, $file);
            $errorMessages = $roleImport->getErrorMessages();
            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            // If no errors, redirect with success message
            return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function attendance()
    {
        $user = Auth::user();
        $attendancePolicy = PolicyAttendance::where('ap_b_id', $user->emp_b_id)->count();
        $attendanceShiftType = PolicyShiftTiming::where('pst_b_id', $user->emp_b_id)->count();
        $holidayPolicy = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->count();
        $attendanceLeavePolicy = PolicyLeave::where('pl_b_id', $user->emp_b_id)->count();
        $weeklyPolicy = PolicyWeekOff::where('pwo_b_id', $user->emp_b_id)->count();
        $shiftPolicy = AttendanceShiftPolicy::where('asp_b_id', $user->emp_b_id)->count();
        return view('admin.setting.attendance.attendance', compact('attendancePolicy', 'attendanceShiftType', 'attendanceLeavePolicy', 'holidayPolicy', 'weeklyPolicy', 'shiftPolicy'));
    }

    public function updateTimezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|exists:time_zone,tz_id',
        ]);

        $user = Auth::user();

        $business = Business::where('b_id', $user->emp_b_id)->first();
        if ($business) {
            $business->b_timezone = $request->timezone;
            $business->save();
        }

        $account = $user->accountDetail ?? null;
        if ($account) {
            $account->b_timezone = $request->timezone;
            $account->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Time Zone updated successfully.',
        ]);
    }

    public function addDashboard(Request $request): JsonResponse
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,menu_id',
        ]);

        $user = Auth::user();
        $menuId = $request->menu_id;

        $businessUpdated = false;
        $business = Business::where('b_id', $user->emp_b_id)->first();

        if ($business) {
            $business->b_dashboard_id = $menuId;
            $businessUpdated = $business->save();
        }

        if ($businessUpdated) {
            return response()->json(['success' => true, 'message' => 'Default Dashboard set successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to set dashboard.']);
        }
    }

    public function toggleNotificationOld(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'enabled' => ['required', 'integer', 'in:0,1'],
        ]);

        try {
            $enabled = (int) $request->input('enabled');
            $user->emp_is_notification_enabled = $enabled;
            $saved = $user->save();

            if (! $saved) {
                return response()->json(['error' => 'Failed to update'], 500);
            }

            // 👇 only return enabled status
            return response()->json([
                'status'  => 'success',
                'enabled' => $user->emp_is_notification_enabled,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleNotification(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'enabled' => ['required', 'integer', 'in:0,1'],
            'field'   => ['required', 'string']
        ]);

        try {

            $enabled = (int) $request->enabled;
            $field   = $request->field;

            if(in_array($field, [
                'emp_is_notification_enabled',
                'emp_is_whatsapp_enabled'
            ])){
                $user->$field = $enabled;
                $user->save();
            }
            else{
                $user->fh_business()->update([
                    $field => $enabled
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'enabled' => $enabled,
                'field'   => $field
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function adminSettingNotification(Request $request)
    {
        $user = Auth::user();

        try {
            return view('admin.setting.notification-settings', compact('user'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function saveSwitchEmail(Request $request)
    {
        // Validation
        $request->validate([
            'emails' => 'required|array|min:1|max:5',
            'emails.*' => 'required|email|distinct'
        ]);

        $user = Auth::user();

        // Prevent same email as logged-in user
        if (in_array($user->emp_email, $request->emails)) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot add your own login email'
            ], 422);
        }

        //Check Email is exist or not
        $validEmails = Employee::whereIn('emp_email', $request->emails)
            ->where('emp_status', 71)
            ->where('emp_role_id', 1)
            ->pluck('emp_email')
            ->toArray();

        $invalidEmails = array_diff($request->emails, $validEmails);

        if (!empty($invalidEmails)) {
            return response()->json([
                'status' => false,
                'message' => 'These emails are invalid or not allowed: ' . implode(', ', $invalidEmails),
                'invalid_emails' => array_values($invalidEmails)
            ], 403);
        }

        // Security Check
        $business = Business::where('b_id', $user->emp_b_id)->first();
        if (!$business) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized business access'
            ], 403);
        }

        $emailString = implode(',', $request->emails);
        $business->update([
            'switch_business_email' => $emailString
        ]);

        // Success Response
        return response()->json([
            'status' => true,
            'message' => 'Business email saved successfully'
        ]);
    }

    public function switchUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = Auth::user();

        $business = Business::find($user->emp_b_id);
        if (!$business || !$business->switch_business_email) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        // Security check
        $allowedEmails = array_map('trim', explode(',', $business->switch_business_email));
        if (!in_array($request->email, $allowedEmails)) {
            return response()->json([
                'status' => false,
                'message' => 'Email not allowed'
            ]);
        }

        // Find employee
        $employee = Employee::where('emp_email', $request->email)->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }

        // SWITCH LOGIN
        Auth::login($employee);
        $request->session()->regenerate();

        return response()->json([
            'status' => true,
            'message' => 'Switched successfully',
            'redirect' => url()->previous()
        ]);
    }

    public function sendOtpEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $otp = rand(100000, 999999);
        
        $details = [
            'name' => 'User',  // You can fetch name from database if needed
            'otp' => $otp,
        ];

        //Check Email is exist or not
        // $exists = Employee::where('emp_email', $request->email)
        //     ->where('emp_status', 71)
        //     ->where('emp_role_id', 1)
        //     ->exists();

        // if (!$exists) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'This email is invalid or not allowed: ' . $request->email
        //     ], 403);
        // }

        // Send mail using CentralLogics::send_mail
        $sendMail = CentralLogics::send_mail($request->email, new SwitchBusinessMailer($details));
        
        if ($sendMail) {
            // Store OTP in session
            session(['switch_business_otp_' . $request->email => [
                'code' => $otp,
                'expires_at' => now()->addMinutes(10)
            ]]);

            return response()->json([
                'status' => true,
                'message' => 'OTP has been sent successfully to ' . $request->email
            ]);
        }
        
        return response()->json([
            'status' => false,
            'message' => 'Failed to send OTP. Please try again.'
        ]);
    }

    public function verifyOtpEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6'
        ]);

        $sessionData = session('switch_business_otp_' . $request->email);

        if (!$sessionData) {
            return response()->json([
                'status' => false,
                'message' => 'No OTP request found. Please request OTP first.'
            ]);
        }

        // Check if OTP is expired
        if (Carbon::now()->gt($sessionData['expires_at'])) {
            session()->forget('switch_business_otp_' . $request->email);
            return response()->json([
                'status' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ]);
        }

        // Verify OTP
        if ($sessionData['code'] == $request->otp) {
            session()->forget('switch_business_otp_' . $request->email);
            return response()->json([
                'status' => true,
                'message' => 'Email verified successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP. Please try again.'
        ]);
    }

    public function updateEmailConfiguration(Request $request)
    {
        $user = Auth::user();
        $emailConfiguration = EmailConfiguration::where('b_id', $user->emp_b_id)->first();
        $rules = [
            'mailer' => 'required',
        ];

        switch ($request->mailer) {
            case 'smtp':
                $rules += [
                    'host'         => 'required|string|max:255',
                    'port'         => 'required|integer',
                    'encryption'   => 'required|in:tls,ssl',
                    'username'     => 'required|string|max:255',
                    'password'     => $emailConfiguration ? 'nullable|string' : 'required|string',
                    'from_address' => 'required|email|max:255',
                    'from_name'    => 'required|string|max:255',
                ];
                break;

            case 'sendmail':
                $rules += [
                    'sendmail_path' => 'required|string|max:255',
                    'from_address'  => 'required|email|max:255',
                    'from_name'     => 'required|string|max:255',
                ];
                break;

            case 'mailgun':
                $rules += [
                    'mailgun_domain' => 'required|string|max:255',
                    'mailgun_secret' => $emailConfiguration ? 'nullable|string' : 'required|string',
                    'from_address'   => 'required|email|max:255',
                    'from_name'      => 'required|string|max:255',
                ];
                break;

            case 'ses':
                $rules += [
                    'ses_key'       => 'required|string|max:255',
                    'ses_secret'    => $emailConfiguration ? 'nullable|string' : 'required|string',
                    'ses_region'    => 'required|string|max:255',
                    'from_address'  => 'required|email|max:255',
                    'from_name'     => 'required|string|max:255',
                ];
                break;

            case 'postmark':
                $rules += [
                    'postmark_token' => $emailConfiguration ? 'nullable|string' : 'required|string',
                    'from_address'   => 'required|email|max:255',
                    'from_name'      => 'required|string|max:255',
                ];
                break;

            case 'log':
            case 'array':
                $rules += [
                    'from_address' => 'required|email|max:255',
                    'from_name'    => 'required|string|max:255',
                ];
                break;

            default:
                return redirect()->back()
                    ->withInput()
                    ->with([
                        'swal_icon'    => 'error',
                        'swal_title'   => 'Error',
                        'swal_message' => 'Invalid mail driver selected.',
                    ]);
        }

        $request->validate($rules);

        $data = [
            'b_id'           => $user->emp_b_id,
            'mailer'         => $request->mailer,

            'from_address'   => $request->from_address,
            'from_name'      => $request->from_name,

            'host'           => $request->host,
            'port'           => $request->port,
            'encryption'     => $request->encryption,
            'username'       => $request->username,

            'sendmail_path'  => $request->sendmail_path,

            'mailgun_domain' => $request->mailgun_domain,

            'ses_key'        => $request->ses_key,
            'ses_region'     => $request->ses_region,

            'is_active'      => 1,
            'updated_by'     => $user->emp_id,
        ];


        if (!empty($request->password)) {
            $data['password'] = CentralLogics::encrypt_or_decrypt($request->password);
        } elseif ($emailConfiguration) {
            $data['password'] = $emailConfiguration->password;
        }

        if (!empty($request->mailgun_secret)) {
            $data['mailgun_secret'] = CentralLogics::encrypt_or_decrypt($request->mailgun_secret);
        } elseif ($emailConfiguration) {
            $data['mailgun_secret'] = $emailConfiguration->mailgun_secret;
        }

        if (!empty($request->ses_secret)) {
            $data['ses_secret'] = CentralLogics::encrypt_or_decrypt($request->ses_secret);
        } elseif ($emailConfiguration) {
            $data['ses_secret'] = $emailConfiguration->ses_secret;
        }

        if (!empty($request->postmark_token)) {
            $data['postmark_token'] = CentralLogics::encrypt_or_decrypt($request->postmark_token);
        } elseif ($emailConfiguration) {
            $data['postmark_token'] = $emailConfiguration->postmark_token;
        }

        if ($emailConfiguration) {
            $emailConfiguration->update($data);
            return redirect()->back()->with([
                'swal_icon'    => 'success',
                'swal_title'   => 'Updated',
                'swal_message' => 'Email configuration updated successfully.',
            ]);

        } else {
            $data['created_by'] = $user->emp_id;
            EmailConfiguration::create($data);
            return redirect()->back()->with([
                'swal_icon'    => 'success',
                'swal_title'   => 'Saved',
                'swal_message' => 'Email configuration saved successfully.',
            ]);
        }
    }
}
