<?php

namespace App\Http\Controllers\Web\Admin\AttendanceSettings;

use App\Helpers\CentralLogics;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyAttendance;
use App\Models\PolicyLeave;
use App\Models\MasterTable;
use Illuminate\Support\Facades\Auth;

class LeavePolicyController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        if ($this->user) {

            $attendancePolicyType = PolicyAttendance::where('ap_b_id', $this->user->emp_b_id)->get();
            $leavePolicy = PolicyLeave::where('pl_b_id', $this->user->emp_b_id)->get();
            $leaveCategory = MasterTable::where('m_group', 'LEAVE_CATEGORY')->where('m_id', '!=', 567)->get();
            $leaveCycle = MasterTable::where('m_group', 'LEAVE_CYCLE')->get();
            $leaveUnused = MasterTable::where('m_group', 'UNUSED_LEAVE_RULE')->get();
            $leaveApplicable = MasterTable::where('m_group', 'LEAVE_APPLICABLE_TO')->get();
            $leaveTypes = LeaveType::all();

            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['pl_b_id', $this->user->emp_b_id]
                    ],
                    [
                        'method' => 'select',
                        'args' => ['pl_id', 'pl_b_id', 'pl_name', 'pl_effective_date', 'pl_expire_date', 'updated_at'],
                        'relation' => []
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['pl_id', 'pl_name', 'updated_at'] // 'pl_effective_date', 'pl_expire_date',
                    ]
                ];

                $searchColumns = ['pl_name', 'pl_effective_date', 'pl_expire_date', 'updated_at'];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyLeave(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->pl_name; // Leave Policy
                    // $row[] = $val->pl_effective_date ?? '-'; // Effective Date
                    // $row[] = $val->pl_expire_date ?? '-'; // Expire Date
                    // $row[] = isset($val->pl_status) && $val->pl_status == 1 ? 'Active' : 'Inactive'; // Status
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
                                        <a href="' . route('leave-policy.edit', md5($val->pl_id)) . '" class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                            <i class="feather feather-edit"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-leave-policy" data-id="' . $val->pl_id . '">
                                            <i class="feather feather-trash"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>';

                    $rowData[] = $row;

                }

                $output = [
                    "draw" => $request->input('draw'),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new PolicyLeave(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return json_encode($output);
            }

            $columns = [
                'S. No.',
                'Leave Policy',
                'Updated At',
                'Action',
            ];

            return view('admin.setting.attendance.leave-policy', compact('columns', 'leavePolicy', 'leaveCategory', 'leaveCycle', 'attendancePolicyType', 'leaveUnused', 'leaveApplicable', 'leaveTypes'));
        } else {
            abort('404');
        }
    }

    public function create() {
        $attendancePolicyType = PolicyAttendance::where('ap_b_id', $this->user->emp_b_id)->get();
        $leavePolicy = null;//PolicyLeave::where('pl_b_id', $this->user->emp_b_id)->get();
        $leaveCategory = MasterTable::where('m_group', 'LEAVE_CATEGORY')->where('m_id', '!=', 567)->get();
        $leaveCycle = MasterTable::where('m_group', 'LEAVE_CYCLE')->get();
        $leaveUnused = MasterTable::where('m_group', 'UNUSED_LEAVE_RULE')->get();
        $leaveApplicable = MasterTable::where('m_group', 'LEAVE_APPLICABLE_TO')->get();
        $leaveTypes = null;
        return view('admin.setting.attendance.add-edit-leave-policy', compact('leavePolicy', 'leaveCategory', 'leaveCycle', 'attendancePolicyType', 'leaveUnused', 'leaveApplicable', 'leaveTypes'));
    }

    public function edit($id) {
        $attendancePolicyType = PolicyAttendance::where('ap_b_id', $this->user->emp_b_id)->get();
        $leavePolicy = PolicyLeave::where('pl_b_id', $this->user->emp_b_id)->whereRaw('MD5(pl_id) = ?', [$id])->first();
        $leaveCategory = MasterTable::where('m_group', 'LEAVE_CATEGORY')->where('m_id', '!=', 567)->get();
        $leaveCycle = MasterTable::where('m_group', 'LEAVE_CYCLE')->get();
        $leaveUnused = MasterTable::where('m_group', 'UNUSED_LEAVE_RULE')->get();
        $leaveApplicable = MasterTable::where('m_group', 'LEAVE_APPLICABLE_TO')->get();
        $leaveTypes = LeaveType::whereRaw('MD5(lvt_pl_id) = ?', [$id])->get();
        return view('admin.setting.attendance.add-edit-leave-policy', compact('leavePolicy', 'leaveCategory', 'leaveCycle', 'attendancePolicyType', 'leaveUnused', 'leaveApplicable', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name.*' => 'required|integer|exists:master_table,m_id', // Foreign key validation
            'leave_cycle.*' => 'required|integer',
            'days.*' => 'required|numeric|min:0',
            'unused_leave_rule.*' => 'required|integer',
            'carry_forward_limit.*' => 'required|numeric|min:0',
            'applicable_to.*' => 'required|string|in:223,224,225',
        ]);

        // Validate priorities: they must form a contiguous sequence 1..N (no duplicates and no gaps)
        // Ignore blank/null priorities
        $priorities = $request->input('priority', []);
        $nums = array_filter(array_map(function($v) { return (int) $v; }, $priorities), function($v) { return $v > 0; });
        
        if (!empty($nums)) {
            $nums = array_values($nums); // re-index after filtering
            $count = count($nums);
            $unique = array_unique($nums);

            if (count($unique) !== $count) {
                return response()->json(['status' => false, 'message' => 'Priority values contain duplicates. Please ensure priorities are sequential and unique.']);
            }

            $min = min($nums);
            $max = max($nums);
            if ($min !== 1 || $max !== $count) {
                return response()->json(['status' => false, 'message' => 'Priority values must be sequential integers from 1 to ' . $count . '.']);
            }

            // ensure there are no missing values between 1 and count
            $expected = range(1, $count);
            sort($nums);
            if ($nums !== $expected) {
                return response()->json(['status' => false, 'message' => 'Priority values must include every integer between 1 and ' . $count . ' with no gaps.']);
            }
        }

        if (!empty($request->pl_name)) {

            $existingPolicy = PolicyLeave::where('pl_b_id', $this->user->emp_b_id)
                ->where('pl_name', $request->pl_name)
                ->first();
            // dump($existingPolicy);

            if ($existingPolicy) {
                if ($existingPolicy->pl_id != $request->pl_id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Existing Leave Policy Name Found'
                    ]);
                }
            }
        }

        $upl_applicable = $request->pl_upl_applicable ? 1 : 0;
        $limit_check = $request->pl_limit_check ? 1 : 0;

        $save = PolicyLeave::updateOrCreate(
            [ 'pl_id' => $request->pl_id ?? null ],
            [
                'pl_b_id' => $this->user->emp_b_id,
                'pl_name' => $request->pl_name,
                'pl_upl_applicable' => $upl_applicable,
                'pl_limit_check' => $limit_check,
                'pl_limit_before' => $request->pl_limit_before,
                'pl_limit_after' => $request->pl_limit_after,
            ]
        );

        $primaryKey = $save->pl_id;

        if ($request->deletedLeaveTypes) {
            $deletedLeaveIds = explode(',', $request->deletedLeaveTypes);
            LeaveType::whereIn('lvt_id', $deletedLeaveIds)->delete();
        }

        foreach ($request->category_name as $index => $category) {
            if (!empty($category) && $category != 0) {
                $saveLT = LeaveType::updateOrCreate(
                    [
                        'lvt_id' => $request->lvt_id[$index] ?? null,
                        'lvt_pl_id' => $request->pl_id ?? null,
                    ],
                    [
                        // 'lvt_b_id' => $this->user->emp_b_id,
                        'lvt_pl_id' => $primaryKey,
                        'lvt_cat_type_id' => $category,
                        'lvt_leave_cycle_id' => $request->leave_cycle[$index],
                        'lvt_days_per_year' => $request->days[$index],
                        'lvt_priority' => $request->priority[$index],
                        'lvt_unused_leave_rule_id' => $request->unused_leave_rule[$index],
                        // 'lvt_leave_accrual_rate' => $request->leave_accrual_rate[$index], // New not implemented yet
                        'lvt_carry_forward' => $request->carry_forward_limit[$index],
                        'lvt_applicable_to_id' => $request->applicable_to[$index],
                        'lvt_is_sandwich' => $request->hidden_sandwich[$index],
                        'lvt_encashable' => $request->hidden_lvt_encashable[$index],
                        'lvt_el_per_period' => array_key_exists($index, $request->leave_per_period) ? $request->leave_per_period[$index] : null
                    ]
                );
                if(!$saveLT)
                    return response()->json(['status' => false, 'message' => 'Policy unable to save.']);
            } else {
                return response()->json(['status' => false, 'message' => 'Policy unable to save.']);
            }
        }
        return response()->json(['status' => true, 'message' => 'Policy saved successfully.']);
    }

    public function destroy($id)
    {
        if ($this->user) {
            $existsInEmployee = Employee::where('emp_pl_id', $id)->exists();

            if ($existsInEmployee) {
                return response()->json(['error' => 'Policy cannot be deleted as it is linked to an employee'], 400);
            }

            PolicyLeave::destroy($id);
            return response()->json(['success' => 'Policy deleted successfully']);
        } else {
            abort(404);
        }
    }
}
