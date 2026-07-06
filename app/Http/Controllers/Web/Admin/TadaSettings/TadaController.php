<?php

namespace App\Http\Controllers\Web\Admin\TadaSettings;

use App\Helpers\CentralLogics;
use App\Exports\DailyAllowanceExport;
use App\Exports\ClaimReportExport;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use App\Http\Controllers\Api\TaDa\PolicyTravelType;
use App\Http\Controllers\Controller;
use App\Imports\VehicleImport;
use App\Models\ApprovalModule;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelAllowance;
use App\Models\PolicyTadaTravelMode;
use App\Models\PolicyTadaTravelType;
use App\Models\PolicyTadaTravelVehicle;
use App\Models\PolicyTadaDailyAllowanceLodging;
use App\Models\PolicyTadaDailyAllowance;
use App\Models\PolicyTadaLodging;
use App\Models\RuleCriterion;
use App\Models\TadaRequestPlan;
use Carbon\Carbon;
use App\Models\TadaMetroCity;
use App\Models\TravelPurpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use Sabberworm\CSS\RuleSet\RuleSet;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmptySheetExport;
use App\Exports\LodgingExport;
use App\Exports\PolicyCategoryExport;
use App\Exports\VehicleExport;
use App\Imports\DailyAllowanceImport;
use App\Imports\LodgingImport;
use App\Imports\PolicyCategoryImport;
use App\Models\PaymentMode;
use App\Models\TadaExpenseSetting;
use Illuminate\Database\QueryException;

class TadaController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index()
    {

        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $paymentMode = PaymentMode::where('pm_b_id', $businessId)->first();

        // dd($paymentMode);

        return view('admin.setting.tada-settings.tada-settings', compact('paymentMode'));
    }


    // public function travelList()
    // {
    //     $user = Auth::user();
    //     $travelTypes = MasterTable::where('m_group', 'TRAVEL_TYPE')->select('m_id', 'm_name')->get();
    //     $approvalTypes = MasterTable::where('m_group', 'APPROVAL_TYPE')->select('m_id', 'm_name')->get();
    //     $tadaTravelTypeData = PolicyTadaTravelType::with('fh_travel_type:m_id,m_name')->where('pttt_b_id', $user->emp_b_id)->select('pttt_id', 'pttt_type_id', 'pttt_approval_type_id', 'pttt_status', 'updated_at')->get();
    //     return view('admin.setting.tada-settings.travel-type-list', compact('travelTypes', 'approvalTypes', 'tadaTravelTypeData'));
    // }


    public function travelList(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'with',
                        'args' => ['fh_travel_type:m_id,m_name'],
                        'relation' => []
                    ],
                    [
                        'method' => 'where',
                        'args' => ['pttt_b_id', $user->emp_b_id],
                        'relation' => []
                    ],
                    [
                        'method' => 'select',
                        'args' => ['pttt_id', 'pttt_type_id', 'pttt_approval_type_id', 'pttt_status', 'updated_at'],
                        'relation' => []
                    ]
                ];

                $searchColumns = ['pttt_type_id', 'pttt_approval_type_id', 'pttt_status'];
                    $searchRelationships = [
                        'fh_travel_type' => ['m_name'],
                        'fh_approval_type' => ['m_name'],
                    ];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyTadaTravelType(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->fh_travel_type->m_name ?? '-';
                    $row[] = $val->fh_approval_type->m_name;
                    $row[] = $val->pttt_status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                    $row[] = 'W.E.F. <span class="text-info">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                    $row[] = '
                        <div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                    <li>
                                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-travel-type"
                                            type="button"
                                            data-id="' . $val->pttt_id . '"
                                            data-type_id="' . $val->pttt_type_id . '"
                                            data-type_name="' . (isset($val->fh_travel_type) ? $val->fh_travel_type->m_name : '') . '"
                                            data-approval_type_id="' . $val->pttt_approval_type_id . '"
                                            data-approval_type_name="' . (isset($val->fh_approval_type) ? $val->fh_approval_type->m_name : '') . '"
                                            data-status="' . $val->pttt_status . '">
                                            <i class="feather feather-edit"></i> Edit
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>';
                        /*
                         Delete Button
                        <li>
                            <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                type="button"
                                onclick="openDeleteTravelTypeModal(this)"
                                data-pttt_id="' . $val->pttt_id . '"
                                data-pttt_name="' . (isset($val->fh_travel_type) ? $val->fh_travel_type->m_name : '') . '">
                                <i class="feather feather-trash"></i> Delete
                            </button>
                        </li>*/
                    $rowData[] = $row;

                }

                $output = [
                    "draw" => $request->input('draw'),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new PolicyTadaTravelType(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return json_encode($output);
            }

            $columns = [
                'S. No.',
                'Travel Type',
                'Approval Type',
                'Status',
                'Updated At',
                'Action',
            ];

            $travelTypes = MasterTable::where('m_group', 'TRAVEL_TYPE')->select('m_id', 'm_name')->get();
            $approvalTypes = MasterTable::where('m_group', 'APPROVAL_TYPE')->select('m_id', 'm_name')->get();
            $tadaTravelTypeData = PolicyTadaTravelType::with('fh_travel_type:m_id,m_name')
                ->where('pttt_b_id', $user->emp_b_id)
                ->select('pttt_id', 'pttt_type_id', 'pttt_approval_type_id', 'pttt_status', 'updated_at')
                ->get();

            return view('admin.setting.tada-settings.travel-type-list', compact('travelTypes', 'approvalTypes', 'tadaTravelTypeData', 'columns'));
        } else {
            abort(404);
        }
    }


    public function createOrUpdateTravelType(Request $request)
    {
        $user = Auth::user();
        $response = ['status' => false, 'message' => ''];

        if (!$request->travel_type_id) {
            $response['status'] = false;
            $response['message'] = "Travel Type Must be required.";
        }
        $status = (isset($request->travel_type_status) && $request->travel_type_status == "on") ? 1 : 0;
        $pttt = null;

        if (isset($request->travelTypeId) && $request->travelTypeId) {


            $existsInBusiness = PolicyTadaTravelType::where('pttt_type_id', $request->travel_type_id)
                ->where('pttt_b_id', $user->fh_business->b_id)

                ->exists();

            if (!$existsInBusiness) {
                $pttt = PolicyTadaTravelType::where('pttt_id', $request->travelTypeId)->update([
                    'pttt_status' => $status,
                    'pttt_type_id' => $request->travel_type_id,
                    'pttt_approval_type_id' => $request->approval_type_id
                ]);
            } else {
                $pttt = PolicyTadaTravelType::where('pttt_id', $request->travelTypeId)->update([
                    'pttt_status' => $status,
                    'pttt_approval_type_id' => $request->approval_type_id
                ]);
            }

            $msg = 'Updated';
        } else {
            $exist = PolicyTadaTravelType::where(['pttt_b_id' => $user->emp_b_id, 'pttt_type_id' => $request->travel_type_id])->first();
            if ($exist) {
                $response['status'] = false;
                $response['message'] = "Travel Type Already Exist.";
                return response()->json($response);
            } else {
                $pttt = PolicyTadaTravelType::create([
                    'pttt_b_id' => $user->emp_b_id,
                    'pttt_type_id' => $request->travel_type_id,
                    'pttt_approval_type_id' => $request->approval_type_id,
                    'pttt_status' => $status,
                ]);
                $msg = 'Created';
            }
        }
        if ($pttt) {
            $response['status'] = true;
            $response['message'] = "Your Travel Type has been  {$msg} Successfully";
        } else {
            $response['status'] = false;
            $response['message'] = "Your Travel Type has not been {$msg}";
        }
        return response()->json($response);
    }


    public function deleteTravelType(Request $request)
    {
        $idAssignOrNot = PolicyTadaTravelAllowance::where('ptta_pttt_id', $request->travelTypeId)->first();
        $idAssignOrNot2 = PolicyTadaTravelMode::where('pttm_pttt_id', $request->travelTypeId)->first();
        if ($idAssignOrNot && $idAssignOrNot2) {
            Alert::error('', 'Travel Type Cannot be deleted as it is assigned to at least one travel allowance & travel mode.')->autoClose(3000);
        } else if ($idAssignOrNot) {
            Alert::error('', 'Travel Type Cannot be deleted as it is assigned to at least one travel allowance.')->autoClose(3000);
        } else if ($idAssignOrNot2) {
            Alert::error('', 'Travel Type Cannot be deleted as it is assigned to at least one travel mode.')->autoClose(3000);
        } else {
            $pttt = PolicyTadaTravelType::where('pttt_id', $request->travelTypeId)->delete();
            if ($pttt) {
                Alert::success('', 'Travel Type Deleted Successfully')->autoClose(3000);
            } else {
                Alert::success('', 'Travel Type Not Found')->autoClose(3000);
            }
        }
        return redirect()->route('travel.type.list');
    }

    public function test()
    {
        return view('admin.setting.tada-settings.test');
    }

    public function downloadSampleExcelPolicyCategory()
    {
        $filename = "policy-category-sheet.xlsx";
        return Excel::download(new PolicyCategoryExport(), $filename);
    }

    public function policyCategoryImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $user = Auth::user();

        try {

            $fileExt = $file->getClientOriginalExtension();
            if ($fileExt !== 'xlsx' && $fileExt !== 'csv') {
                return redirect()->back()->with('error', 'Invalid File only accept .xlsx or .csv file');
            }

            $policyCategoryImport = new PolicyCategoryImport($user);
            Excel::import($policyCategoryImport, $file);

            $successfulImports = $policyCategoryImport->successfulImports;
            if ($successfulImports > 0) {
                return redirect()->back()->with('success', "Policy category data imported successfully!");
            } else {
                return redirect()->back()->with('error', 'No policy category were imported. Please check the file contents.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'File import failed. ' . $e->getMessage());
        }
    }


    public function travelPolicyCategory(Request $request)
    {
        $user = Auth::user();
        $grades = Grade::whereNull('g_b_id')->orWhere('g_b_id', $user->emp_b_id)->get();
        $departments = Department::whereNull('d_b_id')->orWhere('d_b_id', $user->emp_b_id)->get();
        $designations = Designation::whereNull('dg_b_id')->orWhere('dg_b_id', $user->emp_b_id)->get();
        $travelTypes = PolicyTadaTravelType::where('pttt_b_id', $user->emp_b_id)->with('fh_travel_type')->get();
        $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->get();

        $gradeFilter = request()->input('tada_gradeFilter');
        $departmentFilter = request()->input('tada_departmentFilter');
        $designationFilter = request()->input('tada_designationFilter');
        $travelTypeFilter = request()->input('tada_travelTypeFilter');

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ptc_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['ptc_id', 'ptc_b_id', 'ptc_name', 'ptc_d_id', 'ptc_dg_id', 'ptc_grade_id', 'ptc_pttt_id', 'ptc_status', 'updated_at'],
                    'relation' => [
                        'fh_grade:g_id,g_name',
                        'fh_department:d_id,d_name',
                        'fh_designation:dg_id,dg_name',
                        'fh_travel_type.fh_travel_type:m_id,m_name',
                    ]
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['ptc_id', 'ptc_grade_id', 'ptc_d_id', 'ptc_dg_id', 'ptc_pttt_id', 'ptc_status', 'updated_at']
                ]
            ];

            // Filter conditions
            if ($gradeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['ptc_grade_id', $gradeFilter],
                ];
            }

            if ($departmentFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['ptc_d_id', $departmentFilter],
                ];
            }

            if ($designationFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereJsonContains',
                    'args' => ['ptc_dg_id', (int) $designationFilter],
                ];
            }

            if ($travelTypeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereJsonContains',
                    'args' => ['ptc_pttt_id', (int) $travelTypeFilter],
                ];
            }

            $searchColumns = ['ptc_name', 'updated_at'];
            $searchRelationships = [
                'fh_grade' => ['g_name'],
                'fh_department' => ['d_name'],
                'fh_designation' => ['dg_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new PolicyTadaCategory(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->ptc_name; // Category Name
                $row[] = $val->fh_grade->g_name ?? '-'; // Grade
                $row[] = $val->fh_department->d_name ?? '-'; // Department
                $row[] = $val->getFhDesignationsAttribute ? $val->getFhDesignationsAttribute->pluck('dg_name')->implode(', ') : '';

                // 1st Method
                // $travelTypeNames = [];
                // foreach ($travelTypes as $travelType) {
                //     if (in_array($travelType->pttt_id, json_decode($val->ptc_pttt_id, true))) {
                //         $travelTypeNames[] = $travelType->fh_travel_type->m_name;
                //     }
                // }
                // $travelTypeNames = implode(', ', $travelTypeNames);
                // $row[] = $travelTypeNames;

                // 2nd Method
                $travelTypeNames = $travelTypes->whereIn('pttt_id', json_decode($val->ptc_pttt_id, true))
                    ->pluck('fh_travel_type.m_name')
                    ->implode(', ');
                $row[] = $travelTypeNames; // Travel Type

                $row[] = isset($val->ptc_status) && $val->ptc_status == 1 ? 'Active' : 'Inactive'; // Status
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
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
                                    data-bs-target="#addPolicyCategoryModal"
                                    data-bs-toggle="modal"
                                    onclick="openEditPolicyCategory(this)"
                                    data-id="' . $val->ptc_id . '"
                                    data-category_name="' . $val->ptc_name . '"
                                    data-grade="' . $val->fh_grade->g_id . '"
                                    data-department="' . $val->fh_department->d_id . '"
                                    data-designation="' . $val->ptc_dg_id . '"
                                    data-travel_type="' .  $val->ptc_pttt_id  . '"
                                    data-status="' . $val->ptc_status . '">
                                    <i class="feather feather-edit"></i> Edit
                                </button>
                            </li>
                            <!--
                            <li>
                                <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                    type="button"
                                    data-bs-toggle="modal"
                                    onclick="travelDeleteModel(this)"
                                    data-id="' . $val->ptc_id . '"
                                    data-sno="' . $i . '"
                                    data-bs-target="#travelDeletebtn"
                                    title="Edit">
                                    <i class="feather feather-trash"></i> Delete
                                </button>
                            </li>
                            -->
                        </ul>
                    </div>
                </div>';

            $rowData[] = $row;

            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyTadaCategory(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Category Name',
            'Grade',
            'Department',
            'Designation',
            'Travel Types',
            'Status',
            'Updated At',
            'Action',
        ];

        return view('admin.setting.tada-settings.policy-category', compact('departments', 'designations', 'travelTypes', 'grades', 'policyCategory', 'columns'));
    }

    public function createUpdateTravelPolicyCategory(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $validate = Validator::make($request->all(), [
                'cat_name.*'  => 'required',
                'grade.*'  => 'required',
                'department.*'  => 'required',
                'designation.*'  => 'required',
                'travelTypeId.*'  => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error'  => $validate->errors()->all()
                ]);
            }

            $id = $request->editPolicyCategory;
            $name = $request->cat_name;
            $grade = $request->grade;
            $department = $request->department;
            $designation = is_array($request->designation) ? array_values($request->designation) : [$request->designation];
            $status = $request->status;
            $travelTypeId = isset($request->travelTypeId) ? array_values($request->travelTypeId) : [];
            $existingRecord = PolicyTadaCategory::where([
                'ptc_d_id' => $department,
                'ptc_grade_id' => $grade,
                'ptc_b_id' => $user->emp_b_id
            ])->first();

            if ($existingRecord) {
                if ($existingRecord->ptc_id != $id) {
                    $errors = ["Duplicate combination of Grade and Department found."];
                    return response()->json(['status' => false, 'errors' => $errors], 422);
                }
            }

            $data = [
                'ptc_b_id' => $user->emp_b_id,
                'ptc_name' => $name,
                'ptc_d_id' => $department,
                'ptc_dg_id' => json_encode(array_map('intval', $designation)),
                'ptc_grade_id' => $grade,
                'ptc_status' => $status,
                'ptc_pttt_id' => json_encode(array_map('intval', $travelTypeId))
            ];

            $save = PolicyTadaCategory::updateOrCreate(
                ['ptc_id' => $id ?? null],
                $data
            );

            if (!$save) {
                return response()->json(['status' => false, 'errors' => 'Failed to save policy categories.'], 500);
            }
            return response()->json(['status'  => true, 'message' => 'Policy Category saved successfully.']);
        }
    }

    public function travelMode()
    {
        $user = Auth::user();
        $travelTypes = PolicyTadaTravelType::with('fh_travel_type:m_id,m_name')->where('pttt_b_id', $user->emp_b_id)->select('pttt_id', 'pttt_type_id', 'pttt_status')->get();
        $travelModes = MasterTable::where('m_group', 'TRAVEL_MODE')->pluck('m_name', 'm_id')->toArray();
        $travelModeStoredData = PolicyTadaTravelMode::where('pttm_b_id', $user->emp_b_id)->get();
        $travelModeToggle = $travelModeStoredData->where('pttm_status', 1)->pluck('pttm_pttt_id')->unique()->toArray();
        $travelModeSubOptions = $travelModeStoredData->where('pttm_status', 1)->pluck('pttm_by_mode_id')->unique()->toArray();
        return view('admin.setting.tada-settings.travel-mode', compact('travelTypes', 'travelModes', 'travelModeToggle', 'travelModeStoredData'));
    }

    public function saveTravelModes(Request $request)
    {
        $user = Auth::user();
        $data = $request->travel_data ?? [];
        $pids = [];
        foreach ($data as $item) {
            if (isset($item['pid'])) {
                $pids = array_merge($pids, $item['pid']);
            }
        }
        $businessId = $user->emp_b_id;
        $storedDataPids = PolicyTadaTravelMode::where('pttm_b_id', $businessId)->pluck('pttm_id')->toArray();
        $pidsToDeactivate = array_diff($storedDataPids, $pids);
        if (!empty($pidsToDeactivate)) {
            PolicyTadaTravelMode::where('pttm_b_id', $businessId)->whereIn('pttm_id', $pidsToDeactivate)->update([
                'pttm_status' => 0,
            ]);
        }
        $created  = false;
        $updated = false;
        foreach ($data as $key => $item) {
            if ($item['status'] == 1) {
                foreach ($item['by_mode'] as $key2 => $item2) {
                    if (isset($item['pid'][$key2])) {
                        $updated = true;
                    } else {
                        $created = true;
                    }
                    PolicyTadaTravelMode::updateOrCreate(
                        [
                            'pttm_id' => $item['pid'][$key2] ?? null,
                            'pttm_b_id' => $businessId,
                        ],
                        [
                            'pttm_b_id' => $businessId,
                            'pttm_pttt_id' => $key,
                            'pttm_by_mode_id' => $item2,
                            'pttm_status' => 1,
                        ]
                    );
                }
            } else {
                $updated = true;
                PolicyTadaTravelMode::where([
                    'pttm_b_id' => $businessId,
                    'pttm_pttt_id' => $key,
                ])->update([
                    'pttm_status' => 0,
                ]);
            }
        }
        if ($created && $updated) {
            $successMessages = "Travel Mode has been created & updated successfully";
        } elseif ($created) {
            $successMessages = 'Travel Mode has been created successfully.';
        } elseif ($updated) {
            $successMessages = 'Travel Mode has been updated successfully.';
        }
        return response()->json(['success' => $successMessages]);
    }

    public function downloadSampleExcelAllowance()
    {
        $filename = "daily-allowance-sheet.xlsx";
        return Excel::download(new DailyAllowanceExport(), $filename);
    }

    public function dailyAllowanceImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $user = Auth::user();

        try {

            $fileExt = $file->getClientOriginalExtension();
            if ($fileExt !== 'xlsx' && $fileExt !== 'csv') {
                return redirect()->back()->with('error', 'Invalid File only accept .xlsx or .csv file.');
            }

            $dailyAllowanceImport = new DailyAllowanceImport($user);
            Excel::import($dailyAllowanceImport, $file);

            $successfulImports = $dailyAllowanceImport->successfulImports;

            if ($successfulImports > 0) {
                return redirect()->back()->with('success', "daily-allowance data imported successfully!");
            } else {
                return redirect()->back()->with('error', 'No daily-allowance were imported. Please check the file contents.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'File import failed. ' . $e->getMessage());
        }
    }



    public function travelAllowance()
    {
        $user = Auth::user();
        $data = PolicyTadaTravelAllowance::with('fh_claim_type:m_id,m_name')->where('ptta_b_id', $user->emp_b_id)->get();
        $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->where('ptc_status', 1)->get();
        $travelType = PolicyTadaTravelType::with('fh_travel_type')->where('pttt_b_id', $user->emp_b_id)->where('pttt_status', 1)->get();
        $travelMode = PolicyTadaTravelMode::with('fh_travel_mode')->where('pttm_b_id', $user->emp_b_id)->where('pttm_status', 1)->get();
        $vehicleList = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)->with('fh_vehicle:m_id,m_name', 'fh_travel_class:m_id,m_name', 'fh_vehicle_owner:m_id,m_name', 'fh_claim_type:m_id,m_name')->get();
        return view('admin.setting.tada-settings.travel-allowance', compact('data', 'policyCategory', 'travelType', 'travelMode', 'vehicleList'));
    }

    public function travelAllowance2()
    {
        $user = Auth::user();
        $data = PolicyTadaTravelAllowance::with('fh_claim_type:m_id,m_name')->where('ptta_b_id', $user->emp_b_id)->get();
        $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->where('ptc_status', 1)->get();
        $travelType = PolicyTadaTravelType::with('fh_travel_type')->where('pttt_b_id', $user->emp_b_id)->where('pttt_status', 1)->get();
        $travelMode = PolicyTadaTravelMode::with('fh_travel_mode')->where('pttm_b_id', $user->emp_b_id)->where('pttm_status', 1)->get();
        $vehicleList = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)->with('fh_vehicle:m_id,m_name', 'fh_travel_class:m_id,m_name', 'fh_vehicle_owner:m_id,m_name', 'fh_claim_type:m_id,m_name')->get();
        return view('admin.setting.tada-settings.travel-allowance2', compact('data', 'policyCategory', 'travelType', 'travelMode', 'vehicleList'));
    }



    public function createOrUpdateTravelAllowance(Request $request)
    {
        // Perform validation
        $user = Auth::user();
        foreach ($request->input('dynamic', []) as $key => $value) {
            if ($value['_delete'] == 0) {
                $validated = $request->validate(
                    [
                        "dynamic.$key.ptta_ptc_id" => 'required',
                        "dynamic.$key.ptta_pttt_id" => 'required',
                        "dynamic.$key.ptta_pttm_id" => 'required',
                        "dynamic.$key.ptta_pttv_id" => 'required',
                        //"dynamic.$key.ptta_eligibility" => 'required|numeric',
                        "dynamic.$key.ptta_remarks" => 'nullable|string|max:255',
                    ],
                    [
                        "dynamic.$key.ptta_ptc_id.required" => 'Policy Category is required',
                        "dynamic.$key.ptta_pttt_id.required" => 'Travel Type is required',
                        "dynamic.$key.ptta_pttm_id.required" => 'Travel Mode is required',
                        "dynamic.$key.ptta_pttm_id.required" => 'Travel Vehicle List is required',
                        //"dynamic.$key.ptta_eligibility.required" => 'Eligibility Amount/Km is required',
                        "dynamic.$key.ptta_remarks.required" => 'Remarks is required',
                        "dynamic.$key.ptta_ptc_id.exists" => 'Policy Category does not exist',
                        "dynamic.$key.ptta_pttt_id.exists" => 'Travel Type does not exist',
                    ],
                );
            }
        }

        // Process the data
        $data = $request->all();
        $createCount = 0;
        $updateCount = 0;
        $previousExistingData = PolicyTadaTravelAllowance::where('ptta_b_id', $user->emp_b_id)->pluck('ptta_id');
        $currentData = collect($data['dynamic'])->pluck('ptta_id');

        // Calculate the difference
        $difference = $previousExistingData->diff($currentData);
        foreach ($difference as $key => $value) {
            PolicyTadaTravelAllowance::where('ptta_b_id', $user->emp_b_id)->where('ptta_id', $value)->delete();
        }
        foreach ($data['dynamic'] as $row) {
            unset($row['_delete']); // Remove the _delete field before update or create
            unset($row['_index']); // Remove the _delete field before update or create
            $vehicle = PolicyTadaTravelVehicle::where('pttv_id', $row['ptta_pttv_id'])->first();
            $row['ptta_claim_type_id'] = $vehicle->fh_claim_type->m_id;
            if (isset($row['ptta_id'])) {


                // Update existing record
                PolicyTadaTravelAllowance::where('ptta_b_id', $user->emp_b_id)
                    ->where('ptta_id', $row['ptta_id'])
                    ->update($row);
                $updateCount++;
            } else {
                // Create new record
                $row['ptta_b_id'] = $user->emp_b_id;
                PolicyTadaTravelAllowance::create($row);
                $createCount++;
            }
        }

        if ($createCount > 0 && $updateCount > 0) {
            $message = 'Your Travel Allowance has been Created and Updated Successfully';
        } elseif ($createCount > 0) {
            $message = 'Your Travel Allowance has been Created Successfully';
        } elseif ($updateCount > 0) {
            $message = 'Your Travel Allowance has been Updated Successfully';
        } else {
            $message = 'No changes were made to the Travel Allowance';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }



    public function travelVehicle(Request $request)
    {
        $user = Auth::user();
        $travelTypeFilter = request()->input('travel-vehicletravelTypeFilter');
        $travelModeFilter = request()->input('travel-vehicletravelModeFilter');
        $travelVehiclesFilter = request()->input('travel-vehicletravelVehiclesFilter');
        $policyCategoryFilter = request()->input('travel-vehiclepolicyCategoryFilter');
        $travelTypes = PolicyTadaTravelType::with('fh_travel_type:m_id,m_name')->where('pttt_b_id', $user->emp_b_id)->where('pttt_status', 1)->get();
        // $travelVehicles = PolicyTadaTravelVehicle::where('pttv_b_id',$user->emp_b_id)->with('fh_policy_tada_travel_mode:pttm_id,pttm_pttt_id')->get();

        $travelVehicles = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)->get();
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['pttv_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['pttv_id', 'pttv_b_id', 'pttv_pttm_id', 'pttv_vehicle_id', 'pttv_claim_type_id', 'pttv_owner_id', 'pttv_class_id', 'pttv_is_conveyance', 'pttv_ptc_id', 'pttv_eligibility', 'updated_at'],
                    'relation' => [
                        'fh_policy_tada_travel_mode:pttm_id,pttm_pttt_id,pttm_by_mode_id',
                        'fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name',
                        'fh_policy_tada_travel_mode.fh_travel_type.fh_travel_type:m_id,m_name',
                        'fh_claim_type:m_id,m_name',
                        'fh_vehicle_owner:m_id,m_name',
                        'fh_policy_tada_category:ptc_id,ptc_name',
                    ]
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['pttv_id', 'pttv_pttm_id', 'pttv_pttm_id', 'pttv_vehicle_id', 'pttv_claim_type_id', 'pttv_owner_id', 'pttv_class_id', 'updated_at', 'pttv_id']
                ]
            ];

            // Filter conditions
            // if ($branchFilter != '') {
            //     $dynamicConditions[] = [
            //         'method' => 'whereRelation',
            //         'parentMethod' => 'whereHas',
            //         'childMethod' => 'where',
            //         'args' => ['trp_br_id', $branchFilter],
            //         'relation' => 'fh_tada_request_plan'
            //     ];
            // }

            if ($travelTypeFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'whereHas',
                        'args' => ['m_id', $travelTypeFilter],
                        'relation' => 'fh_policy_tada_travel_mode.fh_travel_type.fh_travel_type'
                    ];
            }

            if ($travelModeFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'whereHas',
                        'args' => ['m_id', $travelModeFilter],
                        'relation' => 'fh_policy_tada_travel_mode.fh_travel_mode'
                    ];
            }

            if ($travelVehiclesFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'whereHas',
                        'args' => ['m_id', $travelVehiclesFilter],
                        'relation' => 'fh_vehicle'
                    ];
            }

            if ($policyCategoryFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'where',
                        'args' => ['pttv_ptc_id', $policyCategoryFilter],
                    ];
            }


            $searchColumns = ['updated_at'];
            $searchRelationships = [
                'fh_policy_tada_travel_mode.fh_travel_mode' => ['m_name'],
                'fh_claim_type' => ['m_name'],
                'fh_vehicle_owner' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new PolicyTadaTravelVehicle(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->fh_policy_tada_travel_mode->fh_travel_type->fh_travel_type->m_name; // travel type
                $row[] = $val->fh_policy_tada_travel_mode->fh_travel_mode->m_name; // travel mode
                $row[] = $val->fh_vehicle->m_name; // travel vehicle
                $row[] = $val->fh_claim_type->m_name; // claim type
                $row[] = $val->fh_vehicle_owner->m_name ?? '-'; // vehicle owner
                $row[] = $val->fh_travel_class->m_name ?? '-'; // vehicle class
                $row[] = $val->fh_policy_tada_category->ptc_name ?? '-'; // vehicle class
                $row[] = $val->pttv_eligibility ?? '-'; // vehicle class
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
               $row[] = '
    <div class="btn-list ms-3">
        <div class="dropdown">
            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu p-2" style="min-width: 200px;">
                <li>
                    <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        type="button"
                        data-bs-target="#addTravelVehicleModal"
                        onclick="openEditTravelVehicle(this)"
                        data-bs-toggle="modal"
                        data-id="' . $val->pttv_id . '"
                        data-travel_type="' . $val->fh_policy_tada_travel_mode->fh_travel_type->pttt_id . '"
                        data-mode="' . $val->fh_policy_tada_travel_mode->pttm_id . '"
                        data-conveyance="' . $val->pttv_is_conveyance . '"
                        data-vehicle="' . $val->fh_vehicle->m_id . '"
                        data-claim="' . $val->fh_claim_type->m_id . '"
                        data-owner="' . (isset($val->fh_vehicle_owner->m_id) ? $val->fh_vehicle_owner->m_id : '-') . '"
                        data-class="' . (isset($val->fh_travel_class->m_id) ? $val->fh_travel_class->m_id : '-') . '"
                        data-policy_category="' . $val->pttv_ptc_id . '"
                        data-el_amount="' .  $val->pttv_eligibility  . '">
                        <i class="feather feather-edit"></i> Edit
                    </button>
                </li>
                <li>
                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 deleteTravelVehicle"
                        type="button"
                        data-id="' . $val->pttv_id . '">
                        <i class="feather feather-trash"></i> Delete
                    </button>
                </li>
                <!--
                <li>
                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 deleteTravelVehicle"
                        type="button"
                        data-bs-toggle="modal"
                        data-id="' . $val->pttv_id . '"
                        data-sno="' . $i . '"
                        data-bs-target="#travelDeletebtn"
                        title="Edit">
                        <i class="feather feather-trash"></i> Delete (Modal)
                    </button>
                </li>
                -->
            </ul>
        </div>
    </div>';

$rowData[] = $row;
// Corrected this line to add the row to rowData array
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyTadaTravelVehicle(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Travel Type',
            'Travel Mode',
            'Travel Vehicle',
            'Claim Type',
            'Vehicle Owner',
            'Vehicle Class',
            'Policy Category',
            'Eligibility',
            'Updated At',
            'Action',
        ];
        // $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->where('ptc_status', 1)->get();


        return view('admin.setting.tada-settings.travel-vehicle', compact('travelTypes', 'travelVehicles', 'columns'));
    }

    public function getTravelVehicleSetting(Request $request)
    {
        $data = [];
        $user = Auth::user();

        if ($request->REQUEST_TYPE == 'TRAVEL_MODE') {

            $data = PolicyTadaTravelMode::with('fh_travel_mode:m_id,m_name')->where(['pttm_b_id' => $user->emp_b_id, 'pttm_pttt_id' => $request->travelType])->where('pttm_status', 1)->get();
        }
        if ($request->REQUEST_TYPE == 'VEHICLE') {
            $by_mode_id =  PolicyTadaTravelMode::with('fh_travel_mode:m_id,m_name')->where(['pttm_id' => $request->mode_type_id])->pluck('pttm_by_mode_id')->first();
            $data = MasterTable::where(['m_group' => 'VEHICLE', 'm_description' => $by_mode_id])->select('m_name', 'm_id', 'm_description')->get();
        }
        if ($request->REQUEST_TYPE == 'TRAVEL_CLASS_TRAVEL_OWNER') {
            $data = MasterTable::whereIn('m_group', ['TRAVEL_CLASS', 'VEHICLE_OWNER'])->whereJsonContains('m_description', (int) $request->vehicle_id)->get();
        }

        if (count($data)) {
            return response()->json(['status' => true, 'message' => 'success', 'result' => $data]);
        } else {
            return response()->json(['status' => false, 'message' => 'Record not found.', 'result' => '']);
        }
    }

    function deleteTravelVehicleSetting(Request $request)
    {
        $user = Auth::user();
        $travel_id = $request->travel_id;

        try {
            // Try to delete the travel vehicle
            $check = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)
                ->where('pttv_id', $travel_id)
                ->delete();

            if ($check) {
                return response()->json(['success' => 'Your Travel Vehicle has been deleted successfully'], 200);
            } else {
                return response()->json(['error' => 'Your Travel Vehicle has not been deleted'], 200);
            }
        } catch (QueryException $e) {
            // Catch the foreign key constraint violation
            if ($e->getCode() == 23000) { // Integrity constraint violation code
                // Extract table name from the error message
                $errorMessage = $e->getMessage();
                $relatedTable = $this->getRelatedTableFromError($errorMessage);
                $relatedTable = $relatedTable ==  'fh_tada_expenses' ? 'travel expense' : $relatedTable;
                return response()->json(['error' => "Cannot delete this Travel Vehicle as it is related to records in the $relatedTable."], 200);
            }

            // For any other exception, return a generic error message
            return response()->json(['error' => 'An error occurred while deleting the Travel Vehicle.'], 500);
        }
    }

    private function getRelatedTableFromError($message)
    {
        // Match the related table name (look for `fails (\`database_name\`.\`table_name\`` pattern)
        preg_match("/fails \(`\w+`\.`(\w+)`/", $message, $matches);
        return $matches[1] ?? 'unknown table'; // If no match, return "unknown table"
    }

    // public function saveTravelVehicleSetting(Request $request)
    // {
    //     $user = Auth::user();

    //     $travelData = $request->input('data');
    //     $businessId = $user->emp_b_id;

    //     // Loop through each item in the travelData array
    //     foreach($travelData as $item) {
    //         $existingRecord = PolicyTadaTravelVehicle::where([
    //             'pttv_pttm_id' => $item['mode'],
    //             'pttv_vehicle_id' => $item['vehicle'],
    //             'pttv_class_id' => isset($item['class']) ? $item['class'] : null,
    //             'pttv_owner_id' => isset($item['owner']) ? $item['owner'] : null,
    //         ])->exists();
    //         if ($existingRecord && (!PolicyTadaTravelVehicle::where('pttv_id', $item['tv_id'])->exists())) {
    //             $errors[] = "Duplicate combination of Mode, Vehicle, Vehicle Class, and Vehicle Owner found.";
    //             Alert::error('', $errors)->autoClose(3000);
    //             return response()->json(['status' => false, 'errors' => $errors], 422);
    //         }

    //         $save = PolicyTadaTravelVehicle::updateOrCreate(
    //             ['pttv_id' => $item['tv_id']],
    //             [
    //                 'pttv_b_id' => $businessId,
    //                 'pttv_pttm_id' => $item['mode'],
    //                 'pttv_vehicle_id' => $item['vehicle'],
    //                 'pttv_class_id' => $item['class'] ?? null,
    //                 'pttv_owner_id' => $item['owner'] ?? null,
    //                 'pttv_claim_type_id' => $item['claim_type']
    //             ]
    //         );

    //         if(!$save) {
    //             Alert::error('', "Failed to save vehicle list.")->autoClose(3000);
    //             return response()->json(['error' => 'Failed to vehicle list.']);
    //         }
    //     }

    //     if (!empty($errors)) {
    //         return response()->json(['status' => false, 'errors' => $errors], 422);
    //     }
    //     return response()->json(['status' => true, 'message' => 'Travel Vehicle settings have been modified.']);
    // }


    public function downloadEmptyTemplate()
    {
        $filename = "vehicles-sheet.xlsx";
        return Excel::download(new VehicleExport(), $filename);
    }


    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $user = Auth::user();

        try {

            $fileExt = $file->getClientOriginalExtension();
            if ($fileExt == 'xlsx' && $fileExt == 'csv') {
                return redirect()->back()->with('error', 'Invalid file. Only .xlsx or .csv are accepted.');
            }

            $vehicleImport = new VehicleImport($user);
            Excel::import($vehicleImport, $file);

            $successfulImports = $vehicleImport->successfulImports;
            $errorCount = $vehicleImport->errorCount;

            $downloadLink = '';
            if ($vehicleImport->errorFileName) {
                $downloadLink = asset('storage/' . $vehicleImport->errorFileName);
            }

            $successMessage = "{$successfulImports} vehicles imported successfully. {$errorCount} errors found.";
            $errorMessage = "No vehicles imported successfully. {$errorCount} errors found.";
            if ($downloadLink) {
                $successMessage .= " <a href='{$downloadLink}' class='text-blue-600 underline'>Download error file</a>";
                $errorMessage .= " <a href='{$downloadLink}' class='text-blue-600 underline'>Download error file</a>";
            }

            if ($successfulImports > 0) {
                return redirect()->back()->with('success_html', $successMessage);
            } elseif ($errorCount > 0) {
                return redirect()->back()->with('error', "Import failed. {$errorCount} errors found.");
            } else {
                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'File import failed. ' . $e->getMessage());
        }
    }



    public function saveTravelVehicleSetting(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $mode = $request->input('travel_mode_id');
        $vehicle = $request->input('vehicle_id');
        $class = $request->input('vehicle_class_id') ?? null;
        $owner = $request->input('vehicle_owner_id') ?? null;
        $claimType = $request->input('claim_type');
        $conveyance = $request->input('conveyance');
        $tvId = $request->input('editTravelVehicle');
        $policyCategory = $request->policy_category;

        $classOrOwner = !empty($class) ? $class : (!empty($owner) ? $owner : null);
        if ($tvId == null && is_array($classOrOwner)) {
            foreach ($classOrOwner as $classOrOwnerItem) {
                $existingRecord = PolicyTadaTravelVehicle::where([
                    'pttv_b_id' => $businessId,
                    'pttv_pttm_id' => $mode,
                    'pttv_vehicle_id' => $vehicle,
                    !empty($class) ? 'pttv_class_id' : (!empty($owner) ? 'pttv_owner_id' : null) => $classOrOwnerItem,
                    // 'pttv_class_id' => $classOrOwnerItem,
                    'pttv_owner_id' => $owner,
                    'pttv_ptc_id' => $policyCategory,
                ])->exists();
                if ($existingRecord) {
                    $errors = ["Duplicate combination of Mode, Vehicle, Vehicle Class or Vehicle Owner found & Policy Category."];
                    return response()->json(['status' => false, 'errors' => $errors], 422);
                }
            }

            // if ($tvId && PolicyTadaTravelVehicle::where('pttv_id', $tvId)->first() ) {
            //     $errors = ["Duplicate combination of Mode, Vehicle, Vehicle Class or Vehicle Owner found."];
            //     return response()->json(['status' => false, 'errors' => $errors], 422);
            // }
            foreach ($classOrOwner as $classOrOwnerItem) {
                $save = PolicyTadaTravelVehicle::updateOrCreate(
                    ['pttv_id' => $tvId],
                    [
                        'pttv_b_id' => $businessId,
                        'pttv_pttm_id' => $mode,
                        'pttv_vehicle_id' => $vehicle,
                        !empty($class) ? 'pttv_class_id' : (!empty($owner) ? 'pttv_owner_id' : null) => $classOrOwnerItem,
                        // 'pttv_class_id' => $classOrOwnerItem,
                        // 'pttv_owner_id' => $owner,
                        'pttv_is_conveyance' => isset($conveyance) ? 1 : 0,
                        'pttv_claim_type_id' => $claimType,
                        'pttv_ptc_id' => $policyCategory,
                        'pttv_eligibility' => $request->taEligibilityAmountKm ?? 0,
                    ]
                );
            }
        } else {
            $existingRecord = PolicyTadaTravelVehicle::where('pttv_id', '!=', $tvId)
                ->where([
                    'pttv_b_id' => $businessId,
                    'pttv_pttm_id' => $mode,
                    'pttv_vehicle_id' => $vehicle,
                    'pttv_class_id' => $class,
                    'pttv_owner_id' => $owner,
                    'pttv_ptc_id' => $policyCategory,
                ])->exists();
            if ($existingRecord) {
                $errors = ["Duplicate combination of Mode, Vehicle, Vehicle Class or Vehicle Owner found."];
                return response()->json(['status' => false, 'errors' => $errors], 422);
            }
            $save = PolicyTadaTravelVehicle::updateOrCreate(
                ['pttv_id' => $tvId],
                [
                    'pttv_b_id' => $businessId,
                    'pttv_pttm_id' => $mode,
                    'pttv_vehicle_id' => $vehicle,
                    'pttv_class_id' => $class,
                    'pttv_owner_id' => $owner,
                    'pttv_is_conveyance' => isset($conveyance) ? 1 : 0,
                    'pttv_claim_type_id' => $claimType,
                    'pttv_ptc_id' => $policyCategory,
                    'pttv_eligibility' => $request->taEligibilityAmountKm ?? 0,
                ]
            );
        }
        if (!$save) {
            return response()->json(['status' => false, 'errors' => "Failed to save vehicle list."], 500);
        }

        return response()->json(['status' => true, 'message' => 'Travel Vehicle settings have been modified.']);
    }

    public function getTravelModes($id)
    {
        $user = Auth::user();
        $travelModes = PolicyTadaTravelMode::with('fh_travel_mode')->where('pttm_b_id', $user->emp_b_id)->where('pttm_pttt_id', base64_decode($id))->where('pttm_status', 1)->get();
        if ($travelModes) {
            return response()->json($travelModes);
        }
        return response()->json(['error' => 'Error fetching travel modes.'], 500);
    }

    public function getTravelLists(Request $request)
    {
        $user = Auth::user();
        // $travelModes = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)->where('pttv_pttm_id', $request->id)->get();
        $vehicleList = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)->where('pttv_pttm_id', base64_decode($request->id))
            ->with('fh_vehicle:m_id,m_name', 'fh_travel_class:m_id,m_name', 'fh_vehicle_owner:m_id,m_name', 'fh_claim_type:m_id,m_name')->get();

        if ($request->REQUEST_TYPE == 'GET_VEHICLE') {
            $vehicleList = $vehicleList->map(function ($item) {
                $item->name = '(' . $item->fh_vehicle->m_name . ' - ' . optional($item->fh_travel_class)->m_name . ' ' . optional($item->fh_vehicle_owner)->m_name . ' - ' . $item->fh_claim_type->m_name . ')';
                return $item;
            });
            if ($vehicleList) {
                return response()->json($vehicleList);
            }
        } elseif ($request->REQUEST_TYPE == 'GET_VEHICLE_TYPE') {
            $vehicle = PolicyTadaTravelVehicle::where('pttv_id', base64_decode($request->id))->first();
            $vehicle->fh_claim_type->m_id;
            return response()->json(['vehicleClaimId' => $vehicle->fh_claim_type->m_id]);
        } elseif ($request->REQUEST_TYPE == 'GET_CLAIM_TYPE') {
            $claimType = $vehicleList->pluck('fh_claim_type.m_id', 'fh_claim_type.m_name')->unique();
            if ($claimType) {
                return response()->json($claimType);
            }
        } elseif ($request->REQUEST_TYPE == 'GET_VEHICLE_LIST') {
            $vehicleList = $vehicleList->filter(function ($item) use ($request) {
                return $item->fh_claim_type->m_id == base64_decode($request->travel_claim_type);
            })->map(function ($item) {
                $item->name = '(' . $item->fh_vehicle->m_name . ' - ' . optional($item->fh_travel_class)->m_name . ' ' . optional($item->fh_vehicle_owner)->m_name . ' - ' . $item->fh_claim_type->m_name . ')';
                return $item;
            });
            if ($vehicleList) {
                return response()->json($vehicleList);
            }
        }

        return response()->json(['error' => 'Error fetching travel lists.'], 500);
    }

    public function downloadEmptyTemplatelodging()
    {
        $filename = "lodging-sheet.xlsx";
        return Excel::download(new LodgingExport(), $filename);
    }


    public function lodgingImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $user = Auth::user();

        try {

            $fileExt = $file->getClientOriginalExtension();
            if ($fileExt !== 'xlsx' && $fileExt !== 'csv') {
                return redirect()->back()->with('error', 'Invalid File only accept .xlsx or .csv file');
            }

            $lodgingImport = new LodgingImport($user);
            Excel::import($lodgingImport, $file);

            $successfulImports = $lodgingImport->successfulImports;

            if ($successfulImports > 0) {
                return redirect()->back()->with('success', "lodging data imported successfully!");
            } else {
                return redirect()->back()->with('error', 'No lodging were imported. Please check the file contents.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'File import failed. ' . $e->getMessage());
        }
    }

    public function Lodging1(Request $request)
    {
        $user = Auth::user();
        $travelTypes = PolicyTadaTravelType::with('fh_travel_type:m_id,m_name')->where('pttt_b_id', $user->emp_b_id)->where('pttt_status', 1)->select('pttt_id', 'pttt_type_id')->get();
        $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->where('ptc_status', 1)->pluck('ptc_name', 'ptc_id')->toArray();
        $cityType = MasterTable::where('m_group', 'CITY_TYPE')->pluck('m_name', 'm_id')->toArray();
        // return view('admin.setting.tada-settings.lodging', compact('lodging', 'travelTypes', 'policyCategory', 'cityType'));

        $policyCategoryFilter = request()->input('policyCategoryFilter');
        $travelTypeFilter = request()->input('travelTypeFilter');
        $cityFilter = request()->input('cityFilter');

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ptdal_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['ptdal_id', 'ptdal_b_id', 'ptdal_ptc_id', 'ptdal_pttt_id', 'ptdal_ct_type_id', 'ptdal_da_per_day_elig', 'ptdal_same_day_remark', 'ptdal_da_per_day_elig', 'ptdal_da_same_day_ret_elig', 'ptdal_lodg_sngl_w_bill_elig', 'ptdal_lodg_sngl_wo_bill_elig', 'ptdal_lodg_dbl_w_bill_elig', 'ptdal_lodg_dbl_wo_bill_elig', 'updated_at'],
                    'relation' => []
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['ptdal_id', 'ptdal_ptc_id', 'ptdal_pttt_id', 'ptdal_ct_type_id', 'ptdal_da_per_day_elig', 'ptdal_da_same_day_ret_elig', 'ptdal_lodg_sngl_w_bill_elig', 'ptdal_lodg_sngl_wo_bill_elig', 'ptdal_lodg_dbl_w_bill_elig', 'ptdal_lodg_dbl_wo_bill_elig', 'updated_at', 'ptdal_id']
                ]
            ];

            // Filter conditions
            if ($policyCategoryFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['ptdal_ptc_id', $policyCategoryFilter]
                ];
            }

            if ($travelTypeFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'whereHas',
                        'args' => ['ptdal_pttt_id', $travelTypeFilter],
                        'relation' => 'fh_policy_tada_travel_type.fh_travel_type'
                    ];
            }

            if ($cityFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'whereHas',
                        'args' => ['m_id', $cityFilter],
                        'relation' => 'fh_city_type'
                    ];
            }

            $searchColumns = ['updated_at'];
            $searchRelationships = [
                'fh_policy_tada_category' => ['ptc_name'],
                'fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
                'fh_city_type' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new PolicyTadaDailyAllowanceLodging(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->fh_policy_tada_category->ptc_name;
                $row[] = $val->fh_policy_tada_travel_type->fh_travel_type->m_name ?? ' ';
                $row[] = $val->fh_city_type->m_name;
                $row[] = $val->ptdal_da_per_day_elig;
                $row[] = $val->ptdal_da_same_day_ret_elig;
                $row[] = $val->ptdal_lodg_sngl_w_bill_elig;
                $row[] = $val->ptdal_lodg_dbl_w_bill_elig;
                $row[] = $val->ptdal_lodg_sngl_wo_bill_elig;
                $row[] = $val->ptdal_lodg_dbl_wo_bill_elig;
                $row[] = $val->updated_at->format('d-m-Y H:i:s');
                $row[] = '<button onclick="editPolicy(this)" class="btn action-btns btn-sm btn-primary edittravelAllowance"
                    data-id="' . $val->ptdal_id . '"
                    data-ptdal_ptc_id="' . $val->ptdal_ptc_id . '"
                    data-ptdal_pttt_id="' . $val->ptdal_pttt_id . '"
                    data-ptdal_ct_type_id="' . $val->ptdal_ct_type_id . '"
                    data-ptdal_da_per_day_elig="' . $val->ptdal_da_per_day_elig . '"
                    data-ptdal_da_same_day_ret_elig="' . $val->ptdal_da_same_day_ret_elig . '"
                    data-ptdal_lodg_sngl_w_bill_elig="' . $val->ptdal_lodg_sngl_w_bill_elig . '"
                    data-ptdal_lodg_sngl_wo_bill_elig="' . $val->ptdal_lodg_sngl_wo_bill_elig . '"
                    data-ptdal_lodg_dbl_w_bill_elig="' . $val->ptdal_lodg_dbl_w_bill_elig . '"
                    data-ptdal_lodg_dbl_wo_bill_elig="' . $val->ptdal_lodg_dbl_wo_bill_elig . '">
                    <i class="feather feather-edit"></i>
                    </button>
                    <button class="btn action-btns btn-sm btn-danger deleteTravelAllowance" data-id="' . $val->ptdal_id . '">
                        <i class="feather feather-trash"></i>
                </button>';
                $rowData[] = $row; // Corrected this line to add the row to rowData array
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyTadaDailyAllowanceLodging(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Policy Category',
            'Travel Type',
            'City Type',
            'Full Day DA',
            'Same day returned DA',
            'Lodging With Bill Single Occupancy',
            'Lodging With Bill Double Occupancy',
            'Lodging Without Bill Single Occupancy',
            'Lodging Without Bill Double Occupancy',
            'Updated At',
            'Action',
        ];

        return view('admin.setting.tada-settings.lodging1', compact('columns', 'travelTypes', 'policyCategory', 'cityType'));
    }

    public function createUpdateLodging1(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $validate = Validator::make($request->all(), [
                'policy_category' => 'required',
                'travel_type' => 'required',
                'city_type' => 'required',
                'da_same_day' => 'required',
                'da_different_days' => 'required',
                'lodging_single_with_bill' => 'required',
                'lodging_double_with_bill' => 'required',
                'lodging_single_without_bill' => 'required',
                'lodging_double_without_bill' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()->all()
                ]);
            }

            $id = $request->policy_id;

            $existingRecord = PolicyTadaDailyAllowanceLodging::where([
                'ptdal_b_id' => $user->emp_b_id,
                'ptdal_ptc_id' => $request->policy_category,
                'ptdal_pttt_id' => $request->travel_type,
                'ptdal_ct_type_id' => $request->city_type,
            ])->where('ptdal_id', '!=', $id)->first();

            if ($existingRecord == true) {
                return response()->json([
                    'error' => 'Duplicate Entry found for Policy Category, Travel Type and City Type.'
                ], 200);
            }
            $policyCreateOrUpdate = PolicyTadaDailyAllowanceLodging::updateOrCreate(
                [
                    'ptdal_id' =>  $id,
                ],
                [
                    'ptdal_b_id' => $user->emp_b_id,
                    'ptdal_ptc_id' => $request->policy_category,
                    'ptdal_pttt_id' => $request->travel_type,
                    'ptdal_ct_type_id' => $request->city_type,
                    'ptdal_da_per_day_elig'  => $request->da_same_day,
                    'ptdal_da_same_day_ret_elig' => $request->da_different_days,
                    'ptdal_lodg_sngl_w_bill_elig' => $request->lodging_single_with_bill,
                    'ptdal_lodg_sngl_wo_bill_elig' => $request->lodging_single_without_bill,
                    'ptdal_lodg_dbl_w_bill_elig' => $request->lodging_double_with_bill,
                    'ptdal_lodg_dbl_wo_bill_elig' => $request->lodging_double_without_bill,
                ]
            );
            $message = $id ? 'Updated' : 'Created';
            if (!$policyCreateOrUpdate) {
                return response()->json(['error' => 'Failed to ' . $message . ' lodging.'], 500);
            } else {
                return response()->json(['success' => true, 'message' => 'Your Lodging Policy has been ' . $message . ' Successfully!']);
            }
        }
    }

    public function deleteDALodging(Request $request)
    {
        $user = Auth::user();
        $delete  =    PolicyTadaDailyAllowanceLodging::where([
            'ptdal_b_id' => $user->emp_b_id,
            'ptdal_id' => base64_decode($request->id),
        ])
            ->delete();
        if ($delete) {
            return response()->json(['success' => true, 'message' => 'Daily allowance & Lodging has been deleted successfully.']);
        } else {
            return response()->json(['error' => true, 'message' => 'Failed to delete Daily allowance & Lodging .']);
        }
    }

    public function tadaDetailsShow(string $id)
    {
        $user = Auth::user();
        $tadaDetails = TadaRequestPlan::where(['trp_b_id' => $user->emp_b_id])
            ->where((DB::raw('md5(trp_id)')), $id)
            ->first();

        $travelAmount = $tadaDetails->fh_tada_expenses->sum('te_amount') + $tadaDetails->fh_tada_expenses->sum('te_taxes');


        if (!$tadaDetails) {
            abort(404, 'Record not found');
        }
        return view('admin.ta-da-request.tadadetails', compact('tadaDetails'));
    }

    public function citiesTravel(Request $request)
    {
        $user = Auth::user();

        $city = TadaMetroCity::where('ctm_b_id', $user->emp_b_id)->get();
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ctm_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['ctm_id', 'ctm_ct_address', 'ctm_longitude', 'ctm_latitude', 'updated_at'],
                    'relation' => ['fh_business:b_id']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['ctm_id', 'ctm_ct_address', 'ctm_e']
                ]
            ];

            $searchColumns = ['ctm_b_id', 'ctm_ct_address', 'updated_at'];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaMetroCity(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->ctm_ct_address;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                        <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
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
                                    data-bs-target="#editCityName"
                                    data-bs-toggle="modal"
                                    onclick="openEditCity(this)"
                                    data-id="' . $val->ctm_id . '"
                                    data-address="' . $val->ctm_ct_address . '"
                                    data-longitude="' . $val->ctm_longitude . '"
                                    data-latitude="' . $val->ctm_latitude . '">
                                    <i class="feather feather-edit"></i> Edit
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                    type="button"
                                    data-bs-toggle="modal"
                                    onclick="ItemDeleteModel(this)"
                                    data-city_id="' . $val->ctm_id . '"
                                    data-city_address="' . $val->ctm_ct_address . '"
                                    data-bs-target="#cityDeletebtn"
                                    title="Edit">
                                    <i class="feather feather-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>';

            $rowData[] = $row;
 // Corrected this line to add the row to rowData array
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaMetroCity(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'City Name',
            'Updated At',
            'Action',
        ];

        return view('admin.setting.tada-settings.travel-city', compact('columns', 'city'));
    }

    public function addCity(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'location' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

           // Check if city already exists for this business (only for new cities, not edits)
        if (!$request->editCityId) {
            $existingCity = TadaMetroCity::where('ctm_b_id', $user->emp_b_id)
                ->where('ctm_ct_address', $request->location)
                ->first();

            if ($existingCity) {
                return response()->json(['error' => 'This city already exists in your metro cities list.']);
            }
        }


        $addCity = TadaMetroCity::updateOrCreate(
            ['ctm_id' => $request->editCityId ?? null],
            [
                'ctm_b_id' => $user->emp_b_id,
                'ctm_ct_address' => $request->location,
                'ctm_longitude' => $request->longitude,
                'ctm_latitude' => $request->latitude,
            ]
        );

        if ($addCity) {
            // return response()->json(['success' => 'Your Metro City has been created successfully.']);
              $message = $request->editCityId ? 'Your Metro City has been updated successfully.' : 'Your Metro City has been created successfully.';
            return response()->json(['success' => $message]);
        } else {
            // return response()->json(['error' => 'Your Metro City has not been created.']);
               $message = $request->editCityId ? 'Your Metro City has not been updated.' : 'Your Metro City has not been created.';
            return response()->json(['error' => $message]);
        }
    }

    public function deleteCity(Request $request)
    {
        $user = Auth::user();
        $city_id = $request->city_id;
        $check = TadaMetroCity::where('ctm_b_id', $user->emp_b_id)
            ->where('ctm_id', $city_id)
            ->delete();

        if (isset($check)) {
            Alert::success('', 'Your Metro City has been deleted successfully')->autoClose(3000);
        } else {
            Alert::error('', 'Your Metro City has not been deleted')->autoClose(3000);
        }
        return redirect()->back();
    }


    public function travelPurpose(Request $request)
    {
        $user = Auth::user();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $travelpurpose = TravelPurpose::where('tp_b_id', $user->emp_b_id)->count();

        if ($request->ajax()) {
            // Dynamic conditions for the query
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['tp_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['tp_id', 'tp_b_id', 'tp_d_id', 'tp_name', 'updated_at'],
                    'relation' => ['fh_business:b_id']
                ]
            ];

            // Columns to be searched
            $searchColumns = [
                'tp_d_id',
                'tp_name',
                'updated_at',
            ];

            // Fetch the list with dynamic conditions
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TravelPurpose(),  // Changed to correct model
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;

            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = optional($val->fh_department)->d_name;  // Fetch department name dynamically
                $row[] = $val->tp_name;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                          <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';

               if ($val->tp_b_id == $user->emp_b_id) {
                $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 200px;">
                                <li>
                                    <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                        onclick="openEditRole(this)"
                                        data-id="' . $val->tp_id . '"
                                        data-department="' . $val->tp_d_id . '"
                                        data-purpose="' . $val->tp_name . '">
                                        <i class="feather feather-edit"></i> Edit
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-purpose"
                                        data-id="' . base64_encode($val->tp_id) . '"
                                        data-department="' . $val->tp_d_id . '"
                                        data-purpose="' . $val->tp_name . '">
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
                    eloquentModel: new TravelPurpose(),  // Changed to correct model
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Department Name',
            'Travel Purpose',
            '',
            'Action',
        ];

        return view('admin.setting.business.travelpurpose', compact('travelpurpose', 'columns', 'department'));
    }


    public function addTravelPurpose(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'department' => 'required',
            'purpose' => 'required|string|max:255',
        ]);

        $travelpurpose = TravelPurpose::create([
            'tp_b_id' => $user->emp_b_id,
            'tp_d_id' => $request->department,
            'tp_name' => $request->purpose,
        ]);

        if ($travelpurpose) {
            return response()->json(['success' => 'Your travel purpose has been created successfully.']);
        } else {
            return response()->json(['error' => 'Your travel purpose has not been created.']);
        }
    }

    public function updateTravelPurpose(Request $request)
    {
        $user = Auth::user();

        $travelPurpose = TravelPurpose::findOrFail($request->editid);

        $travelPurpose->tp_d_id = $request->department;
        $travelPurpose->tp_name = $request->purpose;
        $travelPurpose->save();

        if ($travelPurpose) {
            return response()->json(['success' => 'Your travel purpose been updated successfully.']);
        } else {
            return response()->json(['error' => 'Your travel purpose has not been updated.']);
        }
    }

    public function deleteTravelPurpose($id)
    {
        $user = Auth::user();
        $id = base64_decode($id);
        $planExists = TadaRequestPlan::where([
            'trp_b_id' => $user->emp_b_id,
            'trp_purpose' => $id,
        ])->first();

        if ($planExists) {
            return response()->json(['error' => 'Your cannot delete this travel purpose as it is use elsewhere!']);
        } else {
            $deleted = TravelPurpose::where([
                'tp_id' => $id,
                'tp_b_id' => $user->emp_b_id
            ])->delete();

            if ($deleted) {
                return response()->json(['success' => 'Your travel purpose deleted successfully!']);
            } else {
                return response()->json(['error' => 'Your travel purpose not deleted!']);
            }
        }
    }

    public function dailyAllowance(Request $request)
    {
        $user = Auth::user();
        $travelTypes = PolicyTadaTravelType::with('fh_travel_type:m_id,m_name')->where('pttt_b_id', $user->fh_business->b_id)->where('pttt_status', 1)->select('pttt_id', 'pttt_type_id')->get();
        $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->fh_business->b_id)->where('ptc_status', 1)->pluck('ptc_name', 'ptc_id')->toArray();

        $policyCategoryFilter = request()->input('tada_policyCategoryFilter');
        $travelTypeFilter = request()->input('tada_travelTypeFilter');
        $tada_cityFilter = request()->input('tada_cityFilter');

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ptda_b_id', $user->fh_business->b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['ptda_id', 'ptda_b_id', 'ptda_ptc_id', 'ptda_pttt_id', 'ptda_da_amount','ptda_da_amount2', 'ptda_da_cal_limit', 'ptda_da_cal_type_id', 'ptda_distance', 'ptda_lodging', 'ptda_half_da', 'updated_at'],
                    'relation' => ['fh_policy_tada_daily_allowance_cal_type']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['ptda_id', 'ptda_ptc_id', 'ptda_pttt_id', 'ptda_id', 'ptda_da_cal_limit', 'ptda_da_amount', 'updated_at', 'ptda_id']
                ]
            ];

            // Filter conditions
            if ($policyCategoryFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['ptda_ptc_id', $policyCategoryFilter]
                ];
            }

            if ($travelTypeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['ptda_pttt_id', $travelTypeFilter],
                    'relation' => 'fh_policy_tada_travel_type.fh_travel_type'
                ];
            }

            $searchColumns = ['updated_at'];
            $searchRelationships = [
                'fh_policy_tada_category' => ['ptc_name'],
                'fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
                'fh_city_type' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new PolicyTadaDailyAllowance(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->fh_policy_tada_category->ptc_name;
                $row[] = $val->fh_policy_tada_travel_type->fh_travel_type->m_name ?? '';
                $row[] = (isset($val->fh_policy_tada_daily_allowance_cal_type) ? $val->fh_policy_tada_daily_allowance_cal_type->m_name : '') ?? '';
                $ranges = [];
                if ($val->ptda_da_cal_limit) {
                    $limits = explode('|', $val->ptda_da_cal_limit);
                    if (count($limits) === 1) {
                        // Single value case
                        $ranges[] = $limits[0];
                    } elseif (count($limits) % 2 == 0) {
                        // Ranges case
                        for ($j = 0; $j < count($limits); $j += 2) {
                            $rangeIndex = ($j / 2) + 1; // Range index starts from 1
                            $ranges[] = "range{$rangeIndex} : {$limits[$j]}-{$limits[$j + 1]}";
                        }
                    } else {
                        $ranges[] = "Invalid data in limit";
                    }
                }
                $row[] = implode('<br>', $ranges);
                $row[] = $val->ptda_da_amount;
                $row[] = $val->updated_at->format('d-m-Y H:i:s');
                $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <button onclick="editDA(this)" class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                        data-id="' . $val->ptda_id . '"
                                        data-ptda_ptc_id="' . $val->ptda_ptc_id . '"
                                        data-ptda_pttt_id="' . $val->ptda_pttt_id . '"
                                        data-ptda_da_cal_type_id="' . $val->ptda_da_cal_type_id . '"
                                        data-ptda_da_cal_limit="' . $val->ptda_da_cal_limit . '"
                                        data-ptda_da_amount="' . $val->ptda_da_amount . '"
                                        data-ptda_da_amount2="' . $val->ptda_da_amount2 . '"
                                        data-ptda_distance="' . $val->ptda_distance . '"
                                        data-ptda_lodging="' . $val->ptda_lodging . '"
                                        data-ptda_half_da="' . $val->ptda_half_da . '"
                                        data-valid="' . (isset($val->ptda_distance) && isset($val->ptda_lodging) && isset($val->ptda_half_da) ? 'true' : 'false') . '">
                                        <i class="feather feather-edit"></i> Edit
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 deleteDA"
                                        data-id="' . $val->ptda_id . '">
                                        <i class="feather feather-trash"></i> Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>';

                $rowData[] = $row;
 // Corrected this line to add the row to rowData array
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyTadaDailyAllowance(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Policy Category',
            'Travel Type',
            'DA Type',
            'Limit',
            'Da Amount',
            'Updated At',
            'Action',
        ];
        $dailyAllowanceOption = MasterTable::where('m_group', 'DAILY_ALLOWANCE')->pluck('m_name', 'm_id')->toArray();
        return view('admin.setting.tada-settings.travel-daily-allowance', compact('columns', 'travelTypes', 'policyCategory', 'dailyAllowanceOption'));
    }

    public function createUpdateDailyAllowance(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $validate = Validator::make($request->all(), [
                'policy_category' => 'required',
                'travel_type' => 'required',
                'ptda_da_cal_type_id' => 'required',
                'daAmount' => 'required',
                'hours' => 'required_if:ptda_da_cal_type_id,239',
                'km1' => 'required_if:ptda_da_cal_type_id,240',
                'km2' => 'required_if:ptda_da_cal_type_id,240',
                "distance" => 'required_if:toggleSettings,on',
                "lodging_type" => 'required_if:toggleSettings,on',
                "half_da_type" => 'required_if:toggleSettings,on'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()->all()
                ]);
            }

            $id = $request->da_id;
            $existingRecord = PolicyTadaDailyAllowance::where([
                'ptda_b_id' => $user->fh_business->b_id,
                'ptda_ptc_id' => $request->policy_category,
                'ptda_pttt_id' => $request->travel_type,
            ])->where('ptda_id', '!=', $id)->first();

            if ($existingRecord == true) {
                return response()->json([
                    'error' => 'Duplicate Entry found for Policy Category and Travel Type.'
                ], 200);
            }

            if($request->ptda_da_cal_type_id == 239){
                $daLimit = $request->hours;
            }elseif($request->ptda_da_cal_type_id == 240){
                $daLimit = $request->km1 . '|' . $request->km2;
                $daLimit = ($request->km3 && $request->km4 && $request->daAmount2) ?  $daLimit.'|'.$request->km3 . '|' . $request->km4 : $daLimit;
            }else{
                $daLimit = null;
            }
            $requestData = [
                'ptda_b_id' => $user->fh_business->b_id,
                'ptda_ptc_id' => $request->policy_category,
                'ptda_pttt_id' => $request->travel_type,
                // 'ptda_per_day'  => $request->da_same_day,
                'ptda_da_cal_type_id'  => $request->ptda_da_cal_type_id,
                'ptda_da_cal_limit'  => $daLimit,
                'ptda_da_amount'  => $request->daAmount,
                'ptda_da_amount2'  => $request->daAmount2 ?? NULL,
                'ptda_distance' => $request->distance ?? NULL,
                'ptda_lodging' => $request->lodging_type ?? NULL,
                'ptda_half_da' => $request->half_da_type ?? NULL,
            ];

            $policyCreateOrUpdate = PolicyTadaDailyAllowance::updateOrCreate(
                ['ptda_id' => $id],
                $requestData
            );

            $message = $id ? 'Updated' : 'Created';
            if (!$policyCreateOrUpdate) {
                return response()->json(['error' => 'Failed to ' . $message . ' Daily Allowance.'], 500);
            }

            return response()->json(['success' => true, 'message' => 'Your Daily Allowance Policy has been ' . $message . ' Successfully!']);
        }
    }

    public function deleteDailyAllowance(Request $request)
    {
        $user = Auth::user();
        $delete = PolicyTadaDailyAllowance::where([
            'ptda_b_id' => $user->fh_business->b_id,
            'ptda_id' => base64_decode($request->id),
        ])->delete();

        if ($delete) {
            return response()->json(['success' => true, 'message' => 'Daily Allowance has been deleted successfully.']);
        }

        return response()->json(['error' => true, 'message' => 'Failed to delete Daily Allowance.']);
    }

    public function lodging(Request $request)
    {
        $user = Auth::user();
        $travelTypes = PolicyTadaTravelType::with('fh_travel_type:m_id,m_name')->where('pttt_b_id', $user->fh_business->b_id)->where('pttt_status', 1)->select('pttt_id', 'pttt_type_id')->get();
        $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->fh_business->b_id)->where('ptc_status', 1)->pluck('ptc_name', 'ptc_id')->toArray();
        $cityType = MasterTable::where('m_group', 'CITY_TYPE')->pluck('m_name', 'm_id')->toArray();

        $policyCategoryFilter = request()->input('lodging_policyCategoryFilter');
        $travelTypeFilter = request()->input('lodging_travelTypeFilter');
        $cityFilter = request()->input('lodging_cityFilter');

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ptl_b_id', $user->fh_business->b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['ptl_id', 'ptl_b_id', 'ptl_ptc_id', 'ptl_pttt_id', 'ptl_ct_type_id', 'ptl_sngl_w_bill', 'ptl_sngl_wo_bill', 'ptl_dbl_w_bill', 'ptl_dbl_wo_bill', 'updated_at'],
                    'relation' => []
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['ptl_id', 'ptl_ptc_id', 'ptl_pttt_id', 'ptl_ct_type_id', 'ptl_sngl_w_bill', 'ptl_sngl_wo_bill', 'ptl_dbl_w_bill', 'ptl_dbl_wo_bill', 'updated_at']
                ]
            ];

            // Filter conditions
            if ($policyCategoryFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['ptl_ptc_id', $policyCategoryFilter]
                ];
            }

            if ($travelTypeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['ptl_pttt_id', $travelTypeFilter],
                    'relation' => 'fh_policy_tada_travel_type.fh_travel_type'
                ];
            }

            if ($cityFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['m_id', $cityFilter],
                    'relation' => 'fh_city_type'
                ];
            }

            $searchColumns = ['updated_at'];
            $searchRelationships = [
                'fh_policy_tada_category' => ['ptc_name'],
                'fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
                'fh_city_type' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new PolicyTadaLodging(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->fh_policy_tada_category->ptc_name;
                $row[] = $val->fh_policy_tada_travel_type->fh_travel_type->m_name ?? ' ';
                $row[] = $val->fh_city_type->m_name;
                $row[] = $val->ptl_sngl_w_bill;
                $row[] = $val->ptl_dbl_w_bill;
                $row[] = $val->ptl_sngl_wo_bill;
                $row[] = $val->ptl_dbl_wo_bill;
                $row[] = $val->updated_at->format('d-m-Y H:i:s');
               $row[] = '
    <div class="btn-list ms-3">
        <div class="dropdown">
            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu p-2" style="min-width: 200px;">
                <li>
                    <button onclick="editLodging(this)" class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        data-id="' . $val->ptl_id . '"
                        data-ptl_ptc_id="' . $val->ptl_ptc_id . '"
                        data-ptl_pttt_id="' . $val->ptl_pttt_id . '"
                        data-ptl_ct_type_id="' . $val->ptl_ct_type_id . '"
                        data-ptl_sngl_w_bill="' . $val->ptl_sngl_w_bill . '"
                        data-ptl_sngl_wo_bill="' . $val->ptl_sngl_wo_bill . '"
                        data-ptl_dbl_w_bill="' . $val->ptl_dbl_w_bill . '"
                        data-ptl_dbl_wo_bill="' . $val->ptl_dbl_wo_bill . '">
                        <i class="feather feather-edit"></i> Edit
                    </button>
                </li>
                <li>
                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 deleteLodging"
                        data-id="' . $val->ptl_id . '">
                        <i class="feather feather-trash"></i> Delete
                    </button>
                </li>
            </ul>
        </div>
    </div>';

$rowData[] = $row;
 // Corrected this line to add the row to rowData array
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyTadaLodging(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Policy Category',
            'Travel Type',
            'City Type',
            'Lodging With Bill Single Occupancy',
            'Lodging With Bill Double Occupancy',
            'Lodging Without Bill Single Occupancy',
            'Lodging Without Bill Double Occupancy',
            'Updated At',
            'Action',
        ];

        return view('admin.setting.tada-settings.lodging', compact('columns', 'travelTypes', 'policyCategory', 'cityType'));
    }

    public function createUpdateLodging(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $validate = Validator::make($request->all(), [
                'policy_category' => 'required',
                'travel_type' => 'required',
                'city_type' => 'required',
                'lodging_single_with_bill' => 'required',
                'lodging_double_with_bill' => 'required',
                'lodging_single_without_bill' => 'required',
                'lodging_double_without_bill' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()->all()
                ]);
            }

            $id = $request->lodging_id;
            $existingRecord = PolicyTadaLodging::where([
                'ptl_b_id' => $user->fh_business->b_id,
                'ptl_ptc_id' => $request->policy_category,
                'ptl_pttt_id' => $request->travel_type,
                'ptl_ct_type_id' => $request->city_type,
            ])->where('ptl_id', '!=', $id)->first();

            if ($existingRecord == true) {
                return response()->json([
                    'error' => 'Duplicate Entry found for Policy Category, Travel Type and City Type.'
                ], 200);
            }
            $lodgingCreateOrUpdate = PolicyTadaLodging::updateOrCreate(
                ['ptl_id' =>  $id],
                [
                    'ptl_b_id' => $user->fh_business->b_id,
                    'ptl_ptc_id' => $request->policy_category,
                    'ptl_pttt_id' => $request->travel_type,
                    'ptl_ct_type_id' => $request->city_type,
                    'ptl_sngl_w_bill' => $request->lodging_single_with_bill,
                    'ptl_sngl_wo_bill' => $request->lodging_single_without_bill,
                    'ptl_dbl_w_bill' => $request->lodging_double_with_bill,
                    'ptl_dbl_wo_bill' => $request->lodging_double_without_bill,
                ]
            );
            $message = $id ? 'Updated' : 'Created';
            if (!$lodgingCreateOrUpdate) {
                return response()->json(['error' => 'Failed to ' . $message . ' Lodging.'], 500);
            } else {
                return response()->json(['success' => true, 'message' => 'Your Lodging has been ' . $message . ' Successfully!']);
            }
        }
    }

    public function deleteLodging(Request $request)
    {
        $user = Auth::user();
        $delete = PolicyTadaLodging::where([
            'ptl_b_id' => $user->fh_business->b_id,
            'ptl_id' => base64_decode($request->id),
        ])->delete();

        if ($delete) {
            return response()->json(['success' => true, 'message' => 'Lodging has been deleted successfully.']);
        }

        return response()->json(['error' => true, 'message' => 'Failed to delete Lodging.']);
    }


    public function expenseDetails(Request $request)
    {
        $user = Auth::user();

        $expense = MasterTable::where('m_group', 'EXPENSE_TYPE')->get();
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['tes_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['tes_id', 'tes_expense_type_id', 'tes_b_id', 'tes_code', 'tes_head', 'tes_fixed_amount', 'tes_is_fixed', 'updated_at'],
                    'relation' => ['fh_business:b_id', 'fh_master_table:m_id,m_name']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['tes_expense_type_id', 'tes_code', 'tes_head']
                ]
            ];

            $searchColumns = ['tes_b_id', 'tes_expense_type_id', 'tes_code', 'tes_head', 'tes_fixed_amount', 'updated_at'];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaExpenseSetting(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->fh_master_table->m_name;
                $row[] = $val->tes_code;
                $row[] = $val->tes_head;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                        <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';

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
                                        data-bs-target="#addExpenseDetailsModal"
                                        data-bs-toggle="modal"
                                        onclick="openEditExpenseDetails(this)"
                                        data-id="' . $val->tes_id . '"
                                        data-type_id="' . $val->tes_expense_type_id . '"
                                        data-type_name="' . $val->fh_master_table->m_name . '"
                                        data-code="' . $val->tes_code . '"
                                        data-name="' . $val->tes_head . '"
                                        data-is_fixed="' . $val->tes_is_fixed . '"
                                        data-fixed_amount="' . $val->tes_fixed_amount . '">
                                        <i class="feather feather-edit"></i> Edit
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-expense-setting"
                                        type="button"
                                        data-id="' . $val->tes_id . '">
                                        <i class="feather feather-trash"></i> Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>';

                $rowData[] = $row;

 // Corrected this line to add the row to rowData array
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaExpenseSetting(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Expense Type',
            'Code',
            'Name',
            'Updated At',
            'Action',
        ];

        return view('admin.setting.tada-settings.expense-settings', compact('columns', 'expense'));
    }



    public function storeUpdateExpenseDetails(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $validate = Validator::make($request->all(), [
                'expense_type.*'  => 'required',
                'code.*'  => 'required',
                'name.*'  => 'required',
                'is_fixed' => 'sometimes|boolean',
                'fixed_amount' => 'sometimes|numeric|min:0|max:999999.99',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validate->errors()->all()
                ]);
            }

            $id = $request->editExpenseDetails;
            $existingRecord = TadaExpenseSetting::where([
                'tes_b_id' => $user->emp_b_id,
                'tes_expense_type_id' => $request->expense_type,
                'tes_code' => $request->code,
                'tes_is_fixed' => $request->is_fixed,
                'tes_fixed_amount' => $request->fixed_amount
            ])->where('tes_id', '!=', $id)->first();

            if ($existingRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Duplicate expense settings found for expense type.'
                ]);
            }

            $save = TadaExpenseSetting::updateOrCreate(
                ['tes_id' => $id],
                [
                    'tes_b_id' => $user->emp_b_id,
                    'tes_expense_type_id' => $request->expense_type,
                    'tes_code' => $request->code,
                    'tes_head' => $request->name,
                    'tes_is_fixed' => $request->is_fixed,
                    'tes_fixed_amount' => $request->fixed_amount
                ]
            );

            if ($save) {
                $message = $id ? 'Expense settings updated successfully!' : 'Expense settings created successfully!';
                return response()->json(['status' => true, 'message' => $message]);
            } else {
                return response()->json(['status' => false, 'message' => 'Failed to save expense settings.']);
            }
        }
    }


    public function deleteExpenseDetails($id)
    {
        try {
            $expense = TadaExpenseSetting::findOrFail($id); // Check if the record exists
            $expense->delete(); // Perform the deletion

            return response()->json(['success' => 'Expense setting deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting.'], 500);
        }
    }


}
