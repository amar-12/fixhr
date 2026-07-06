<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalFlow;
use App\Models\EmployeeApprovalMapping;
use App\Models\MasterTable;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class ApprovalFlowController extends Controller
{
    protected $user;
    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        if (!$this->user) {
            abort(404);
        }

        // Fetch all modules and statuses once for mapping
        $approvalFlowtype = EmployeeApprovalMapping::where('eam_b_id', $businessId)
            ->distinct()->pluck('eam_module_id');

        $approvalFlowmodule = MasterTable::where('m_group', 'MODULE')
            ->select('m_id', 'm_name')
            ->whereIn('m_id', $approvalFlowtype)
            ->get()
            ->keyBy('m_id'); // map by id

        $approvalFlowstatus = MasterTable::where('m_group', 'APPROVAL_STATUS')
            ->select('m_id', 'm_name')
            ->whereNotIn('m_id', [139, 140, 156, 170, 171, 175, 192])
            ->get()
            ->keyBy('m_id'); // map by id
            

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['afc_b_id', $businessId]
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'afc_id',
                        'afc_b_id',
                        'afc_approval_id',
                        'afc_approval_status_id',
                        'afc_status',
                        'created_at',
                        'updated_at'
                    ],
                    'relation' => []
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['created_at', 'desc'],
                    'relation' => []
                ]
            ];

            $searchColumns = [
                'afc_id',
                'afc_b_id',
                'afc_approval_id',
                'afc_approval_status_id',
                'afc_status',
                'created_at',
                'updated_at'
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new ApprovalFlow(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            // dd($list);

            $rowData = [];
            $i = 0;
            foreach ($list as $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $approvalFlowmodule[$val->afc_approval_id]->m_name ?? $val->afc_approval_id;
                if (is_array($val->afc_approval_status_id)) {
                    $statusNames = [];
                    foreach ($val->afc_approval_status_id as $statusId) {
                        if (isset($approvalFlowstatus[$statusId])) {
                            $statusNames[] = $approvalFlowstatus[$statusId]->m_name;
                        }
                    }
                    $row[] = implode(', ', $statusNames);
                } else {
                    $row[] = $approvalFlowstatus[$val->afc_approval_status_id]->m_name ?? $val->afc_approval_status_id;
                }

                $row[] = \Carbon\Carbon::parse($val->created_at)->format('d-M-Y');
                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-approval"
                                    type="button"
                                    data-id="' . $val->afc_id . '"
                                    data-bid="' . $val->afc_b_id . '"
                                    data-approval="' . $val->afc_approval_id . '"
                                    data-status=\'' . json_encode($val->afc_approval_status_id) . '\'
                                    data-afcstatus="' . $val->afc_status . '">
                                    <i class="feather feather-edit"></i> Edit
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
                    eloquentModel: new ApprovalFlow(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return response()->json($output);
        }

        $columns = [
            'S. No.',
            'Approval Module',
            'Approval Stage',
            'Created Date',
            'Action',
        ];

        $approvalFlows = ApprovalFlow::where('afc_b_id', $businessId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.setting.business.businessapproval', compact(
            'approvalFlows',
            'columns',
            'approvalFlowmodule',
            'approvalFlowstatus'
        ));
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $data = $request->validate([
            'afc_approval_id' => [
                'required',
                'integer',
                Rule::unique('approval_flows', 'afc_approval_id')
                    ->where('afc_b_id', $b_id)
            ],
            'afc_approval_status_id' => 'required|array',
            'afc_approval_status_id.*' => 'integer',
            'afc_status' => 'nullable|integer',
        ]);

        $approval = ApprovalFlow::create([
            'afc_b_id' => $b_id,
            'afc_approval_id' => $data['afc_approval_id'],
            'afc_approval_status_id' => $data['afc_approval_status_id'],
            'afc_status' => $data['afc_status'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Approval flow created successfully',
            'data' => $approval
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $data = $request->validate([
            'afc_approval_id' => [
                'required',
                'integer',
                Rule::unique('approval_flows', 'afc_approval_id')
                    ->where('afc_b_id', $b_id)
                    ->ignore($id, 'afc_id')
            ],
            'afc_approval_status_id' => 'required|array',
            'afc_approval_status_id.*' => 'integer',
            'afc_status' => 'nullable|integer',
        ]);

        $approval = ApprovalFlow::findOrFail($id);
        $approval->update([
            'afc_approval_id' => $data['afc_approval_id'],
            'afc_approval_status_id' => $data['afc_approval_status_id'],
            'afc_status' => $data['afc_status'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Approval flow updated successfully',
            'data' => $approval
        ]);
    }



    public function edit($id)
    {
        $approval = ApprovalFlow::findOrFail($id);
        return response()->json(['success' => true, 'data' => $approval]);
    }


    public function destroy($id)
    {
        $approval = ApprovalFlow::findOrFail($id);
        $approval->delete();

        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}
