<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\CentralLogics;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Resources\Attendance\LeaveResource;
use App\Http\Resources\LeaveTypeResource;
use App\Http\Resources\MasterTableResource;
use App\Exports\ErrorExport;
use App\Http\Controllers\Controller;
use App\Helpers\ApprovalHelper;
use App\Imports\LeaveBalanceImport;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\MasterTable;
use App\Models\PolicyLeave;
use App\Models\RuleCriterion;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class LeaveManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $branch = Branch::whereNull('br_b_id')->orWhere('br_b_id', $businessId)->get();
        $departments = Department::whereNull('d_b_id')->orWhere('d_b_id', $businessId)->get();
        $designations = Designation::whereNull('dg_b_id')->orWhere('dg_b_id', $businessId)->get();

        $branchFilter = $request->input('branchFilter');
        $departmentFilter = $request->input('departmentFilter');
        $designationFilter = $request->input('designationFilter');
        $toDateFilter = $request->input('toDate');
        $activeFilter = request()->input('activeFilter');

        if (!empty($toDateFilter) && str_contains($toDateFilter, '-')) {
            $dateParts = explode('-', $toDateFilter);
            $year = count($dateParts) === 2 ? $dateParts[0] : null;
            $month = count($dateParts) === 2 ? $dateParts[1] : null;
        } else {
            $year = null;
            $month = null;
        }

        $activeLeaveTypeIds = LeaveBalance::where('lb_b_id', $businessId)
            ->distinct()
            ->pluck('lb_cat_type_id'); // Directly get the unique IDs

            $masterLeaveCategory = MasterTable::whereIn('m_id', $activeLeaveTypeIds)
                ->pluck('m_name', 'm_id'); // Fetch only needed columns

            $columns = [
                'S. No.',
                'Emp. Name',
                'Emp. Code',
            ];

            foreach ($masterLeaveCategory as $key => $name) {
                $columns[$key] = $name;
            }

            $columns[] = 'Action';   
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['emp_b_id', $businessId]
                ],
                [
                    'method' => 'where',
                    'args' => ['emp_role_id', '!=', '1']
                ],
                [
                    'method' => 'where',
                    'args' => ['emp_date_of_joining', '<=', Carbon::parse($toDateFilter)->startOfMonth()->toDateString()]
                ],
                [
                    'method' => 'select',
                    'args' => ['emp_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_code', 'emp_br_id', 'emp_d_id', 'emp_dg_id', 'emp_date_of_joining'],
                    'relation' => ['leaveBalances:lb_emp_id,lb_cat_type_id,lb_year,lb_month,lb_balance_remaining_leave']
                ],
                [
                    'method' => 'sortBy',
                    'args' => array_merge(
                        ['emp_id', 'emp_fname', 'emp_code'],
                        array_fill(0, count($masterLeaveCategory->toArray()), 'emp_id') // Add 'emp_b_id' count times
                    )
                ]
            ];

            if (!empty($branchFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
            }

            if (!empty($departmentFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
            }

            if (!empty($designationFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
            }


            if ($activeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
            }


            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Employee(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['emp_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_code'],
                searchRelationships: [
                    'leaveBalances' => ['lb_cat_type_id', 'lb_year', 'lb_month', 'lb_balance_remaining_leave']
                ]
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;

            foreach ($list as $employee) {
                $row = [];
                $row[] = $i++;
                $row[] = trim("{$employee->emp_fname} {$employee->emp_mname} {$employee->emp_lname}");
                $row[] = $employee->emp_code;
                

                // Initialize leave data with default value '-'
                $leaveData = [];
                foreach ($masterLeaveCategory as $key => $name) {
                    $leaveData[$key] = '-';
                }
                foreach ($employee->leaveBalances as $leave) {
                    if (!is_null($month) && !is_null($year)) {
                        if ($leave->lb_year == $year && $leave->lb_month == $month) {
                            if (isset($leaveData[$leave->lb_cat_type_id])) {
                                $leaveData[$leave->lb_cat_type_id] = $leave->lb_balance_remaining_leave;
                            }
                        }
                    } else {
                        if (isset($leaveData[$leave->lb_cat_type_id])) {
                            $leaveData[$leave->lb_cat_type_id] = $leave->lb_balance_remaining_leave;
                        }
                    }
                }

                // Append leave balances dynamically based on masterLeaveCategory
                foreach ($masterLeaveCategory as $key => $name) {
                    $row[] = $leaveData[$key];
                }

                 // Add action dropdown
                 $row[] = '<div class="card-options">
                        <a href="javascript:void(0);" class="me-0 option-dots text-default" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="feather feather-more-vertical"></span>
                        </a>
                        

                        <ul class="dropdown-menu dropdown-menu-end" role="menu">
                            <li><a href="'.url('admin/attendance/leave-details/' . md5($employee->emp_id)).'" class="view-employee"><i class="feather feather-eye me-2"></i>View</a></li>
                            <li><a href="javascript:void(0);" class="apply-leave-btn" data-emp-id="' . $employee->emp_id . '" data-emp-name="' . trim("{$employee->emp_fname} {$employee->emp_lname}") . '"><i class="feather feather-plus-circle me-2"></i>Apply Leave</a></li>
                        </ul>
                    </div>';

                $rowData[] = $row;
            }


            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $list->count(),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return response()->json($output);
        }

        return view('admin.setting.attendance-details.leave-management', compact(
            'departments',
            'designations',
            'branch',
            'columns'
        ));  

    }

    public function getEmployeeLeaveOptions(Request $request)
    {
        $user = Auth::user();
        $excludeLeaveCatId = null;

        $employee = Employee::where('emp_id', $request->employee_id)->first();
        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.',
                'status' => false,
            ]);
        }

        $dayTypes = MasterTableResource::collection(
            MasterTable::where('m_group', 'LEAVE_TYPE')->get()
        );
        $daySegment = MasterTableResource::collection(
            MasterTable::where('m_group', 'LEAVE_DAY_SEGMENT')->get()
        );

        // Exclude leave category based on gender
        if ($user->emp_gender_id == 34) { // Female
            $excludeLeaveCatId = 211; // Paternity Leave
        } elseif ($user->emp_gender_id == 33) { // Male
            $excludeLeaveCatId = 210; // Maternity Leave
        }

        // Get leave types for employee's pay level, excluding one category if needed
        $leaveTypes = LeaveType::where('lvt_pl_id', $employee->emp_pl_id)
            ->when($excludeLeaveCatId, function ($query) use ($excludeLeaveCatId) {
                return $query->where('lvt_cat_type_id', '!=', $excludeLeaveCatId);
            })
            ->get();

        $master = MasterTable::where('m_id', 215)->first();

        // Get leave balances for employee this month/year excluding the category
        $leaveBalances = LeaveBalance::where('lb_emp_id', $employee->emp_id)
            ->selectRaw('
                lb_cat_type_id, 
                lb_alloted_leave as total_alloted_leave, 
                lb_taken_leave as total_taken_leave, 
                lb_balance_remaining_leave as total_balance_remaining_leave, 
                lb_carried_forward as total_carried_forward
            ')
            ->when($excludeLeaveCatId, function ($query) use ($excludeLeaveCatId) {
                return $query->where('lb_cat_type_id', '!=', $excludeLeaveCatId);
            })
            ->where('lb_year', now()->year)
            ->where('lb_month', now()->month)
            ->get()
            ->keyBy('lb_cat_type_id');

        // Attach leave_balance_details to each LeaveType
        foreach ($leaveTypes as $leaveType) {
            $balance = $leaveBalances->get($leaveType->lvt_cat_type_id);
            if ($balance) {
                $leaveType->leave_balance_details = [
                    'total_alloted_leave' => $balance->total_alloted_leave,
                    'total_taken_leave' => $balance->total_taken_leave,
                    'total_balance_remaining_leave' => $balance->total_balance_remaining_leave,
                    'total_carried_forward' => $balance->total_carried_forward,
                ];
            } else {
                $leaveType->leave_balance_details = null;
            }
        }

        // Create a custom leave type (e.g., Leave Without Pay)
        $customLeaveType = new LeaveType();

        $customLeaveType->lvt_id = 0;
        $customLeaveType->lvt_cat_type_id = $master->m_id ?? 215;
        $customLeaveType->cat_type_id = $master;
        $customLeaveType->leave_balance_details = null; 

        $leaveTypes->push($customLeaveType);

        $leaveTypeResource = LeaveTypeResource::collection($leaveTypes);

        if ($dayTypes->isEmpty() && $leaveTypeResource->isEmpty() && $daySegment->isEmpty()) {
            return [
                'leave_day_type' => [],
                'leave_type' => [],
                'leave_day_segment' => [],
                'message' => 'Day type, leave type or day segment not found.',
                'status' => false
            ];
        }

        return [
            'result' => [
                [
                    'leave_day_type' => $dayTypes,
                    'leave_day_segment' => $daySegment,
                    'leave_type' => $leaveTypeResource,
                ]
            ],
            'status' => true,
        ];
    }

    public function store(Request $request)
    {
        DB::beginTransaction(); 
        try {
            $employee = Employee::where('emp_id', $request->employee_id)->first();
            $leaveReqCat = $request->input('leave_category_id');
            $dayType = $request->input('leave_day_type_id');
            $extraDays = $request->input('extra_days') ? collect($request->input('extra_days')) : collect([]);

            if ($employee->emp_pl_id) {
                $leave_policy = PolicyLeave::with(['fh_leave_type'])->where('pl_id', $employee->emp_pl_id)->first();
                if (!$leave_policy) {
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! It seems the leave policy could not be found.']);
                }
            } else {
                return response()->json(['result' => [], 'status' => false, 'message' => 'We apologize for the inconvenience, but it seems that the leave policy has not been assigned.']);
            }

            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $employee->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 250)
                        ->where('am_status', 1);
                })->first();
            
            $processApprovers = [];
            $emp_d_id = $employee->emp_d_id;
            $amId = null;

            // Ensure $ruleCriteria exists before accessing the relationship
            if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                // Fetch filtered process approvers using emp_d_id
                $processApprovers = $ruleCriteria->fh_approval_module
                    ->filteredProcessApprovers($emp_d_id)
                    ->get(); // Fetch the filtered data
            }
            // dd($ruleCriteria->fh_approval_module);
            if (empty($processApprovers)) {
                $approvalMapping = ApprovalHelper::getApprovalMapping($employee->emp_b_id, $employee->emp_id, 250);
                if (!$approvalMapping) {
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for leave module, contact administration.']);
                }
            } else {
                $amId = $ruleCriteria->rc_am_id;
            }

            // Check Employee probation for leave
            $probActive = Employee::where('emp_id', $employee->emp_id)->where('emp_allow_probation_leave', 1)->first();
            if ($probActive === null) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! you can not take leave in probation period.']);
            }

            
           
            $leaveRequest = new LeaveRequest();
            $start_date = null;
            // if ($request->input('leave_start_date')) {
            //     try {
            //         $start_date = Carbon::createFromFormat('d M, Y', $request->input('leave_start_date'))->format('Y-m-d');
            //     } catch (\Exception $e) {
            //         $start_date = null;
            //     }
            // }

            

            // $end_date = null;
            // if ($request->input('leave_end_date')) {
            //     try {
            //         $end_date = Carbon::createFromFormat('d M, Y', $request->input('leave_end_date'))->format('Y-m-d');
            //     } catch (\Exception $e) {
            //         $end_date = null;
            //     }
            // }

            $start_date = null;
            if ($request->input('leave_start_date') ?: $request->input('leave_start_date1')) {
                try {
                    $start_date = Carbon::parse($request->input('leave_start_date'))->format('d M Y');
                } catch (\Exception $e) {
                    $start_date = null;
                }
            }

            $end_date = null;
            if ($request->input('leave_end_date')) {
                try {
                    $end_date = Carbon::parse($request->input('leave_end_date'))->format('d M Y');
                } catch (\Exception $e) {
                    $end_date = null;
                }
            }

            $leave_day_segment = null;
            $checkSegment = false;

            if ($request->input('leave_day_type_id') == 202) {
                $leave_day_segment = $request->input('leave_day_segment_id');

                // Check if any leave exists already on that start_date for the user
                $startDateExists = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                    ->where('lvr_start_date', $start_date)->where('lvr_leave_day_type_id', 201)
                    ->exists();

                // Only check segment if the start date is NOT already taken
                $checkSegment = !$startDateExists;
            }

            if (is_null($end_date)) {
                $end_date = $start_date;
            }

            if ($this->checkForOverlap($start_date, $end_date, $employee->emp_id, $checkSegment ? $leave_day_segment : null)) {
                return [
                    'result' => [],
                    'message' => 'There is already a leave request for the specified dates.',
                    'status' => false
                ];
            }

            $uploadedPhotos = [];
            if (env('STORE_ON_S3')) { 
                $bucket = 'fixhr-uploads';
                if ($request->lvr_documents != '' && $request->lvr_documents != NULL && $request->lvr_documents != []) {
                    foreach ($request->lvr_documents as $file) {
                        $imageUniqueName = $file->getClientOriginalName();
                        $imagePath = 'LeaveDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                        if ($uploadResult['status']) {
                            $uploadedPhotos[] = $uploadResult['ObjectURL'];
                        }
                    }
                }
            } else { //upload file to the server directory
                $uploadedPath = CommonUtils::uploadFiles($request, 'leave_document', 'LeaveDocument', ['prefix' => 'leave', 'isApi' => true]);
                if (!empty($uploadedPath)) {
                    foreach ($uploadedPath as $path) {
                        $uploadedPhotos[] = url($path);
                    }
                }
            }


            if ($dayType == 202) {
                $leaveRequest->lvr_total_leave_days = 0.5;
            } else {
                $leaveRequest->lvr_total_leave_days = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;
            }


            $leaveRequest->lvr_b_id = $employee->fh_business->b_id;
            $leaveRequest->lvr_emp_id = $employee->emp_id;
            $leaveRequest->lvr_pl_id = $leave_policy->pl_id;
            $leaveRequest->lvr_leave_day_type_id = $dayType;
            $leaveRequest->lvr_day_segment_id = $request->input('leave_day_segment_id');
            $leaveRequest->lvr_reason = $request->input('reason');
            $leaveRequest->lvr_documents = json_encode($uploadedPhotos);
            $leaveRequest->lvr_am_id = $amId;

            $leaveWithoutPayData = new LeaveRequest();

            // Checking Leave Balance before applying leave
            if ($leaveReqCat != 215) {
                $leaveBalance = LeaveBalance::where(['lb_emp_id'=>$employee->emp_id, 'lb_b_id'=>$employee->fh_business->b_id,'lb_cat_type_id'=>$leaveReqCat])
                    ->orderBy('lb_id', 'desc')
                    ->first();

                //this condition is for when leave balance is not available autometically leave without pay will be applied
                if ($leaveBalance->lb_balance_remaining_leave < $leaveRequest->lvr_total_leave_days) {
                    $availableLeave = $leaveBalance->lb_balance_remaining_leave;
                    $leaveWithoutPay = $leaveRequest->lvr_total_leave_days - $availableLeave;

                    if ($availableLeave == 0.5) {
                        $firstEndDate = $start_date;
                        $secondStartDate = $start_date;
                    } else {
                        $firstEndDate = Carbon::parse($start_date)->addDays($availableLeave - 1)->format('Y-m-d');
                        $secondStartDate = Carbon::parse($firstEndDate)->addDays(1)->format('Y-m-d');
                    }

                    $leaveWithoutPayData = $leaveRequest->replicate();

                    $leaveRequest->lvr_start_date = $start_date;
                    $leaveRequest->lvr_end_date = $firstEndDate;
                    $leaveRequest->lvr_cat_type_id = $leaveReqCat;
                    $leaveRequest->lvr_total_leave_days = $availableLeave;

                    $leaveWithoutPayData->lvr_start_date = $secondStartDate;
                    $leaveWithoutPayData->lvr_end_date = $end_date;
                    $leaveWithoutPayData->lvr_cat_type_id = 215;
                    $leaveWithoutPayData->lvr_total_leave_days = $leaveWithoutPay;
                } else { // this section is for when leave balance is available
                    $leaveRequest->lvr_start_date = $start_date;
                    $leaveRequest->lvr_end_date = $end_date;
                    $leaveRequest->lvr_cat_type_id = $leaveReqCat;
                }
            } else {
                $leaveRequest->lvr_start_date = $start_date;
                $leaveRequest->lvr_end_date = $end_date ?? $start_date;
                $leaveRequest->lvr_cat_type_id = $leaveReqCat;
            }

            if ($leaveRequest->save()) {
                $currLvrId = $leaveRequest->lvr_id;
                if ($leaveWithoutPayData->lvr_total_leave_days > 0) {
                    $leaveWithoutPayData->lvr_p_id = $currLvrId;
                    if (!($leaveWithoutPayData->save())) {
                        $leaveRequest->delete();
                        return [
                            'result' => [],
                            'message' => 'Failed to apply leave.',
                            'status' => false
                        ];
                    }
                }

                if ($leaveReqCat != 215) {
                    $leaveReqCount = $leaveRequest->lvr_total_leave_days ?? 0;

                    if ($leave_policy && $leave_policy->fh_leave_type->isNotEmpty()) { //lvt_cat_type_id
                        $leave_type = $leave_policy->fh_leave_type->where('lvt_cat_type_id', $leaveReqCat)->first();
                    }

                    $leaveBalance = LeaveBalance::where('lb_emp_id', $employee->emp_id)
                        ->where('lb_b_id', $employee->fh_business->b_id)
                        ->where('lb_cat_type_id', $leaveReqCat)->orderBy('lb_id', 'desc')
                        ->first();

                    $lastTakenCount = $leaveBalance->lb_taken_leave;

                    $update = [
                        'lb_taken_leave' => $lastTakenCount + $leaveReqCount,
                        'lb_balance_remaining_leave' => ($leaveBalance->lb_balance_remaining_leave - $leaveReqCount),
                    ];

                    // Update Leave Balance
                    LeaveBalance::where('lb_emp_id', $employee->emp_id)
                        ->where('lb_b_id', $employee->fh_business->b_id)
                        ->where('lb_month', date('m'))
                        ->where('lb_year', date('Y'))
                        ->where('lb_cat_type_id', $leaveReqCat)
                        ->update($update);
                }

                //**************** SANDWICH LEAVE CALCULATION *******************************

                $currentLeaveBalance = LeaveBalance::where('lb_emp_id', $employee->emp_id)
                    ->where('lb_cat_type_id', $leaveReqCat)
                    ->first();

                // Get the leave types that match the user's plan and leave type
                $leaveTypes = LeaveType::where('lvt_pl_id', $employee->emp_pl_id)
                    ->where('lvt_cat_type_id', $leaveReqCat)
                    ->first();

                // Check if the leave type is a sandwich type
                $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;
                // dd($user->emp_pl_id, $user->emp_id, $leaveTypes, $currentLeaveBalance);

                if ($currentLeaveBalance && $extraDays->isNotEmpty() && $currentLeaveBalance->lb_balance_remaining_leave >= $extraDays->count() && $isSandwich == 1) {
                    // Deduct from current leave category
                    $currentLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                    $currentLeaveBalance->lb_taken_leave += $extraDays->count();
                    if (!($currentLeaveBalance->save())) {
                        $leaveRequest->delete();
                        return [
                            'result' => [],
                            'message' => 'Failed to apply leave.',
                            'status' => false
                        ];
                    } else {
                        $this->handleSandwichLeave($leaveRequest, $leaveReqCat, $extraDays, $employee, $currLvrId, $currentLeaveBalance);
                    }
                } else {
                    // If current leave category balance is insufficient, check the last leave taken
                    $lastLeave = LeaveRequest::where('lvr_emp_id', $employee->emp_id)->orderBy('lvr_end_date', 'desc')->skip(1)->first();

                    if ($lastLeave) {
                        $lastLeaveCategoryId = $lastLeave->lvr_cat_type_id;
                        $lastLeaveBalance = LeaveBalance::where('lb_emp_id', $employee->emp_id)->where('lb_cat_type_id', $lastLeaveCategoryId)->first();
                        $leaveTypes = LeaveType::where('lvt_pl_id', $employee->emp_pl_id)->where('lvt_cat_type_id', $lastLeaveCategoryId)->first();

                        $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;

                        if ($lastLeaveBalance && $extraDays->isNotEmpty() && $lastLeaveBalance->lb_balance_remaining_leave >= $extraDays->count() && $isSandwich == 1) {
                            // Deduct from last leave category
                            $lastLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                            $lastLeaveBalance->lb_taken_leave += $extraDays->count();
                            if (!($lastLeaveBalance->save())) {
                                $leaveRequest->delete();
                                return [
                                    'result' => [],
                                    'message' => 'Failed to apply leave.',
                                    'status' => false
                                ];
                            } else {
                                $this->handleSandwichLeave($leaveRequest, $lastLeaveCategoryId, $extraDays, $employee, $currLvrId, $lastLeaveBalance);
                            }
                            // dd('else - if :',$currentLeaveBalance->lb_balance_remaining_leave);
                        } else if ($extraDays->isNotEmpty()) {
                            // Mark extra days as UPL
                            $this->handleSandwichLeave($leaveRequest, 215, $extraDays, $employee, $currLvrId, null);
                        }
                    } else if ($extraDays->isNotEmpty()) {
                        // Mark extra days as UPL
                        $this->handleSandwichLeave($leaveRequest, 215, $extraDays, $employee, $currLvrId, null);
                    }
                }

                $title = 'New Leave Request';
                $body = 'A new leave request has been submitted by ' . $employee->emp_full_name;
                $additionalData = [
                    'user_id' => $employee->emp_id,
                    'notification_type' => 'alert',
                    'route' => '/LeaveApprovalList',
                ];

                $serviceAccountPath = public_path('fixhr-app-firebase.json');
                foreach ($processApprovers as $pa) {
                    $emp = Employee::where('emp_id', $pa->pa_emp_id)->first();
                    $placeholders = [
                        '{start_date}' => ($leaveRequest->lvr_start_date)->format('d-m-Y'),
                        '{end_date}' => $leaveWithoutPayData->lvr_end_date
                            ? ($leaveWithoutPayData->lvr_end_date)->format('d-m-Y')
                            : ($leaveRequest->lvr_end_date)->format('d-m-Y'),
                        '{reason}' => $leaveRequest->lvr_reason,
                        '{receiver_name}' => optional($pa->fh_employee)->emp_full_name ?? 'N/A',
                        '{approver_name}' => optional($pa->fh_employee)->emp_full_name ?? 'N/A',
                        '{emp_full_name}' => optional($leaveRequest->fh_employee)->emp_full_name,
                        '{employee_name}' => optional($leaveRequest->fh_employee)->emp_full_name,
                        '{emp_code}' => optional($leaveRequest->fh_employee)->emp_code,
                        '{employee_code}' => optional($leaveRequest->fh_employee)->emp_code,
                        '{emp_position}' => optional($leaveRequest->fh_employee)->fh_designation->dg_name,
                        '{employee_position}' => optional($leaveRequest->fh_employee)->fh_designation->dg_name,
                        '{emp_phone}' => optional($leaveRequest->fh_employee)->emp_phone,
                        '{employee_phone}' => optional($leaveRequest->fh_employee)->emp_phone,
                        '{leave_category}' => ($leaveWithoutPayData && $leaveWithoutPayData->fh_leave_cat_type)
                            ? $leaveRequest->fh_leave_cat_type->m_name . ' & ' . $leaveWithoutPayData->fh_leave_cat_type->m_name
                            : $leaveRequest->fh_leave_cat_type->m_name ?? 'N/A',
                        '{leave_type}' =>  optional($leaveRequest->fh_leave_day_type)->m_name ?? 'N/A',
                        '{applied_date}' => $leaveRequest->created_at ? $leaveRequest->created_at->format('d-m-Y H:i:s') : 'N/A',
                        '{day_segment}' => optional($leaveRequest->fh_leave_day_segment)->m_name ?  '(' . optional($leaveRequest->fh_leave_day_segment)->m_name . ')' :  '',
                        '{days}' => $leaveWithoutPayData->lvr_total_leave_days
                            ? ($leaveRequest->lvr_total_leave_days + $leaveWithoutPayData->lvr_total_leave_days)
                            : $leaveRequest->lvr_total_leave_days ?? 'N/A',
                    ];

       
                    $recipientEmail = $pa->fh_employee->emp_email; 
          
                    $templateType = 407; // Replace with your mail template type
                    $businessId = $employee->emp_b_id; // Replace with your business ID if applicable

                    // Sending the email using the CentralLogics class
                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                    $approver = ApprovalHelper::getApprovalOrRejectionData($leaveRequest->lvr_id, $leaveRequest->lvr_status, $amId, $pa->pa_emp_id, 250);
                }

                DB::commit(); // Commit transaction if all is successful
                return ReturnHelper::jsonApiReturn(LeaveResource::collection([LeaveRequest::find($leaveRequest->lvr_id)]));
            } else {
                DB::commit(); // Commit transaction if all is successful
                // return response()->json(['result' => [], 'status' => false]);
                return response()->json([
                    'success' => true,
                    'message' => 'Leave applied successfully.',
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while applying leave.',
            ], 500);
        }
    }

    private function handleSandwichLeave($leaveRequest, $leaveCategoryId, $extraDays, $employee, $currLvrId, $leaveBalance)
    {
        $extraStartDay = $extraDays->first();
        $extraEndDay = $extraDays->last();

        $sandwichLeaveData = new LeaveRequest();
        $sandwichLeaveData->lvr_start_date = $extraStartDay;
        $sandwichLeaveData->lvr_end_date = $extraEndDay;
        $sandwichLeaveData->lvr_cat_type_id = $leaveCategoryId;
        $sandwichLeaveData->lvr_total_leave_days = $extraDays->count();
        $sandwichLeaveData->lvr_p_id = $currLvrId;
        $sandwichLeaveData->lvr_emp_id = $employee->emp_id;
        $sandwichLeaveData->lvr_pl_id = $employee->emp_pl_id;
        $sandwichLeaveData->lvr_is_sandwich = 1;
        $sandwichLeaveData->lvr_leave_day_type_id = 201;

        if (!($sandwichLeaveData->save())) {
            $leaveRequest->delete();

            if ($leaveCategoryId != 215) {
                $leaveBalance->lb_balance_remaining_leave += $extraDays->count();
                $leaveBalance->lb_taken_leave -= $extraDays->count();
                $leaveBalance->save();
            }

            return [
                'result' => [],
                'message' => 'Failed to apply leave.',
                'status' => false
            ];
        }
    }

    public function checkForOverlap($start_date, $end_date, $user_id, $leave_day_segment = null)
    {
        $overlappingLeave = LeaveRequest::where('lvr_emp_id', $user_id)
            ->when($leave_day_segment, function ($query, $leave_day_segment) {
                return $query->where('lvr_day_segment_id', $leave_day_segment);
            })
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('lvr_start_date', [$start_date, $end_date])
                    ->orWhereBetween('lvr_end_date', [$start_date, $end_date])
                    ->orWhere(function ($query) use ($start_date, $end_date) {
                        $query->where('lvr_start_date', '<=', $start_date)
                            ->where('lvr_end_date', '>=', $end_date);
                    });
            })->exists();

        return $overlappingLeave;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048', 
        ]);

        $file = $request->file('file'); 
        $LeaveBalanceImport = new LeaveBalanceImport(Auth::user());

        try {

            Excel::import($LeaveBalanceImport, $file);

            $errorMessages = $LeaveBalanceImport->getErrorMessages();

            if (!empty($errorMessages)) {
                session()->put('leave_import_errors', $errorMessages);
                session()->flash('leave_import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to import. Please check the file format and try again.');
        }
    }

    public function show(Request $request, $id)
    {

        $employee = Employee::where(DB::raw('md5(emp_id)'), $id)->firstOrFail();

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $filterMonth = $request->input('month');
        $filterYear = $request->input('year');
        $otherSearch = $request->input('other_search');

        $year = $filterYear ?? now()->year;
        $month = $filterMonth ?? now()->month;

        $leaveRequestsQuery = LeaveRequest::with('fh_leave_cat_type')
            ->where('lvr_emp_id', $employee->emp_id);

        if ($request->ajax()) {
            if ($fromDate) { 
                $leaveRequestsQuery->whereDate('lvr_start_date', '>=', Carbon::parse($fromDate));
            }
            if ($toDate) {
                $leaveRequestsQuery->whereDate('lvr_end_date', '<=', Carbon::parse($toDate));
            }
            if ($filterMonth) {
                $leaveRequestsQuery->whereMonth('lvr_start_date', $filterMonth);
            }
            if ($filterYear) {
                $leaveRequestsQuery->whereYear('lvr_start_date', $filterYear);
            }

            if ($otherSearch) {
                $leaveRequestsQuery->where(function ($query) use ($otherSearch) {
                    $query->whereHas('fh_leave_cat_type', function ($q) use ($otherSearch) {
                        $q->where('m_name', 'like', "%{$otherSearch}%");
                    })->orWhere('lvr_reason', 'like', "%{$otherSearch}%")
                    ->orWhere('lvr_total_leave_days', 'like', "%{$otherSearch}%")
                    ->orWhere('lvr_status', 'like', "%{$otherSearch}%")
                    ->orWhere('lvr_reason', 'like', "%{$otherSearch}%");
                });
            }

            $draw = intval($request->input('draw'));
            $start = intval($request->input('start'));
            $length = intval($request->input('length'));

            $totalRecords = $leaveRequestsQuery->count();

            $leaveRequests = $leaveRequestsQuery
                ->orderBy('created_at', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

            $data = [];
            $i = $start + 1;

            foreach ($leaveRequests as $leaveData) {
                $statusLabel = match ($leaveData->lvr_status) {
                    140 => '<span class="badge badge-warning">Requested</span>',
                    157 => '<span class="badge badge-success">Approved</span>',
                    170 => '<span class="badge badge-danger">Rejected</span>',
                    default => '<span class="badge badge-secondary">Unknown</span>',
                };

                $action = '<a href="javascript:void(0);" class="action-btns1 view-leave-btn" '
                    . 'data-bs-toggle="modal" data-bs-target="#leaveapplictionmodal" '
                    . 'data-id="' . $leaveData->lvr_id . '" '
                    . 'data-empid="' . $leaveData->lvr_emp_id . '" '
                    . 'data-start="' . $leaveData->lvr_start_date->format('Y-m-d') . '" '
                    . 'data-end="' . $leaveData->lvr_end_date->format('Y-m-d') . '" '
                    . 'data-days="' . $leaveData->lvr_total_leave_days . '" '
                    . 'data-reason="' . e($leaveData->lvr_reason) . '" '
                    . 'data-status="' . $leaveData->lvr_status . '" '
                    . 'data-created-at="' . $leaveData->created_at->format('Y-m-d H:i:s') . '" '
                    . 'data-leave-type="' . e($leaveData->fh_leave_cat_type->m_name ?? '') . '" '
                    . 'data-type="' . $leaveData->lvr_leave_day_type_id . '">'
                    . '<i class="feather feather-eye text-primary" data-bs-toggle="tooltip" title="View"></i>'
                    . '</a>';

                $data[] = [
                    's_no' => $i++,
                    'leave_type' => $leaveData->fh_leave_cat_type->m_name ?? 'N/A',
                    'from' => $leaveData->lvr_start_date->format('d F, Y'),
                    'to' => $leaveData->lvr_end_date->format('d F, Y'),
                    'days' => $leaveData->lvr_total_leave_days . ' ' . ($leaveData->lvr_total_leave_days == 1 ? 'day' : 'days'),
                    'reason' => $leaveData->lvr_reason,
                    'applied_on' => $leaveData->created_at->format('d F, Y'),
                    'status' => $statusLabel,
                    'action' => $action,
                ];
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ]);
        }

        // Non-AJAX: Prepare chart and summary
        $excludeLeaveCatId = null;
        if ($employee->emp_gender_id == 34) {
            $excludeLeaveCatId = 211; // Paternity
        } elseif ($employee->emp_gender_id == 33) {
            $excludeLeaveCatId = 210; // Maternity
        }

        $leaveBalancesQuery = LeaveBalance::where('lb_emp_id', $employee->emp_id)
            ->where('lb_year', $year)
            ->where('lb_month', $month);

        if ($excludeLeaveCatId) {
            $leaveBalancesQuery->where('lb_cat_type_id', '!=', $excludeLeaveCatId);
        }

        $leaveBalances = $leaveBalancesQuery->get();

        $chartData = [];
        $leaveSummary = [];

        foreach ($leaveBalances as $balance) {
            $leaveType = MasterTable::find($balance->lb_cat_type_id);
            if ($leaveType) {
                $chartData[$leaveType->m_name] = $balance->lb_taken_leave;
                $leaveSummary[$leaveType->m_name] = [
                    'allocated' => $balance->lb_alloted_leave,
                    'used' => $balance->lb_taken_leave,
                    'remaining' => $balance->lb_balance_remaining_leave,
                    'carried_forward' => $balance->lb_carried_forward,
                ];
            }
        }
        

        $columns = ['S.No.', 'Leave Type', 'From', 'To', 'Days', 'Reason', 'Applied On', 'Status', 'Action'];

        // dd($leaveSummary);


        return view('admin.setting.attendance-details.leave-details-show', [
            'employee' => $employee,
            'chartData' => $chartData,
            'leaveSummary' => $leaveSummary,
            'filterYear' => $year,
            'filterMonth' => $month,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'columns' => $columns
        ]);
    }


    public function leaveBalance()
    {
        $users = leaveBalance::get();

        return view('leave-balance', compact('leave-balance'));
    }

    public function downloadSampleExcel()
    {
        // $filename = "employee-sheet.xlsx";
        // return Excel::download(new EmployeeExport(), $filename);
        $filePath = public_path('upload_sample/leave-balance-request-bulk-upload.csv');
        // Check if the file exists
        if (file_exists($filePath)) {
            // Return the file as a download response
            return response()->download($filePath);
        } else {
            // If the file doesn't exist, return a 404 error
            return abort(404, 'File not found');
        }
    }

    public function downloadErrorFile()
    {
        if (!session()->has('leave_import_errors')) {
            return redirect()->back();
            return redirect()->route('employee.import')->with('error', 'No error file found!');
        }

        $errorMessages = session()->get('leave_import_errors');
        session()->forget('import_errors_blade');
        session()->forget('leave_import_errors');
        // Return the Excel download with the error messages
        return Excel::download(new ErrorExport($errorMessages), 'import_errors.xlsx');

        // Get the error messages from the session
        $errorMessages = session()->get('import_errors');
        session()->forget('import_errors_blade');
        session()->forget('import_errors');
        // Return the Excel download with the error messages
        return Excel::download(new ErrorExport($errorMessages), 'import_errors.xlsx');
    }
}
