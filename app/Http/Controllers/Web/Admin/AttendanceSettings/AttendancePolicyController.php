<?php
namespace App\Http\Controllers\Web\Admin\AttendanceSettings;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\MasterTable;
use App\Models\PolicyAttendance;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendancePolicyController extends Controller
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
                        'method' => 'where',
                        'args' => ['ap_b_id', $this->user->emp_b_id]
                    ],
                    [
                        'method' => 'select',
                        'args' => ['ap_id', 'ap_b_id', 'ap_name', 'ap_description', 'ap_mark_absent_check', 'ap_is_selfie_restricted', 'ap_punch_duration', 'ap_status', 'ap_checkin_method_ids', 'updated_at', 'ap_id'],
                        'relation' => ['fh_business:b_id']
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['ap_id', 'ap_b_id', 'ap_name', 'ap_punch_duration', 'ap_description', 'ap_mark_absent_check', 'ap_status', 'updated_at'],
                    ]
                ];

                $searchColumns = ['ap_id', 'ap_b_id', 'ap_name', 'ap_description', 'ap_punch_duration', 'ap_status', 'updated_at', 'ap_id'];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyAttendance(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {

                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->ap_name;
                    $row[] = $val->ap_description;
                    $row[] =  '<span class="fs-11 fw-bold"></span>
                    <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                    $editUrl = route('attendance-policies.destroy', $val->ap_id);
                    $editData = json_encode([
                        'id' => md5($val->ap_id),
                        'ap_b_id' => $val->ap_b_id,
                        'ap_name' => $val->ap_name,
                        'ap_description' => (string)$val->ap_description,
                        'ap_punch_duration' => (int)$val->ap_punch_duration,
                        'ap_mark_absent_check' => (int)$val->ap_mark_absent_check,
                        'ap_is_selfie_restricted' => (int)$val->ap_is_selfie_restricted,
                    ]);

                    $row[] = '
                        <div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
                                </button>
                                <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                    <li>
                                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-button"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-title="Edit Attendance Policy"
                                            data-bs-target="#attendancePolicyForm"
                                            data-ap_checkin_method_ids="' . $val->ap_checkin_method_ids . '"
                                            data-edit-data="' . htmlspecialchars($editData, ENT_QUOTES, 'UTF-8') . '">
                                            <i class="feather feather-edit"></i> Edit
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-button"
                                            type="button"
                                            data-id="' . $val->ap_id . '"
                                            data-url="' . $editUrl . '"
                                            title="Delete">
                                            <i class="feather feather-trash"></i> Delete
                                        </button>
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
                        eloquentModel: new PolicyAttendance(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return json_encode($output);
            }

            $columns = [
                'S. No.',
                'Name',
                'Description',
                'W.e.f',
                'Action',
            ];

            $policies = PolicyAttendance::where('ap_b_id', $this->user->emp_b_id)->get();
            // Query to get CHECKIN METHOD
            $masterData = MasterTable::whereIn('m_group', ['CHECKIN_METHOD'])
                ->get();
            $checkInMethod = $masterData->where('m_group', 'CHECKIN_METHOD')->pluck('m_name', 'm_id');
            return view('admin.setting.attendance.attendance-policies', compact('policies', 'columns', 'checkInMethod'));
        } else {
            abort('404');
        }
    }

    public function store(Request $request)
    {
        if ($this->user) {
            try {

                $request->merge([
                    'ap_b_id' => $this->user->emp_b_id
                ]);
                $validated = $request->validate([
                    'ap_b_id' => 'required|integer',
                    'ap_name' => 'required|string|max:255',
                    'ap_description' => 'nullable|string',
                    'ap_mark_absent_check' => 'nullable|int',
                ], [
                    'ap_name.required' => 'The name field is required',
                    'ap_checkin_method_ids.*' => 'The check in method is required',
                ]);

                $checkinMethodIds = array_filter($request->input('ap_checkin_method_ids'), function ($value) {
                    return !is_null($value); // Remove null values
                });
                $request->merge(['ap_checkin_method_ids' => $checkinMethodIds]);

                $selfieMethodId = 314;
                $isRestricted = $request->ap_is_selfie_restricted ?? 0;
                $request->merge([
                    'ap_is_selfie_restricted' => in_array($selfieMethodId, $checkinMethodIds)
                        ? $isRestricted
                        : 0
                ]);

                $validated = $request->validate([
                    'ap_checkin_method_ids' => 'required|array|min:1|exists:master_table,m_id',  // Validate the check-in method
                ]);
                $checkinMethodIds = array_map('intval', $request->input('ap_checkin_method_ids'));
                $request->merge(['ap_checkin_method_ids' => json_encode($checkinMethodIds)]);
                $id =  $request->input('id');
                $attendancePolicy = PolicyAttendance::where('ap_b_id', $this->user->emp_b_id)->whereRaw('md5(ap_id) = ?', [$id])->first();
                if ($attendancePolicy) {
                    $attendancePolicy->update($request->all());
                } else {
                    $attendancePolicy = new PolicyAttendance();
                    $attendancePolicy->fill($request->all());
                    $attendancePolicy->save();
                }
                $message = $request->id ? 'Updated' : 'Created';
                return response()->json([
                    'status' => 'success',
                    'message' => 'Attendance Policy ' . $message . ' successfully!',
                ], 201);
            } catch (\Illuminate\Database\QueryException $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Database error occurred: ' . $e->getMessage()
                ], 500);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error occurred: ' . $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An unexpected error occurred: ' . $e->getMessage()
                ], 500);
            }
        } else {
            abort('404');
        }
    }



    public function destroy($id)
    {
        if ($this->user) {
            $result = CentralLogics::dynamicDelete(PolicyAttendance::class, $id);

            if (isset($result['success'])) {
                return response()->json(['success' => $result['success'], 'message' => 'Attendance Policy has been deleted successfully']);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }
}
