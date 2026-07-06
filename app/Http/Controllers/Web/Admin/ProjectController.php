<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class ProjectController extends Controller
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

        if ($this->user) {
            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['ps_b_id', $businessId]
                    ],
                    [
                        'method' => 'select',
                        'args' => ['ps_id', 'ps_name', 'ps_description', 'created_at', 'updated_at'],
                        'relation' => []
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['created_at', 'desc'],
                        'relation' => []
                    ]
                ];

                $searchColumns = ['ps_name', 'ps_description', 'created_at', 'updated_at', 'ps_b_id'];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new Project(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->ps_name;
                    $row[] = $val->ps_description;
                    $row[] = Carbon::parse($val->created_at)->format('d-M-Y');
                    $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-project"
                                        type="button"
                                        data-id="' . $val->ps_id . '"
                                        data-name="' . e($val->ps_name) . '"
                                        data-description="' . e($val->ps_description) . '">
                                        <i class="feather feather-edit"></i> Edit
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-project"
                                        type="button"
                                        data-id="' . $val->ps_id . '">
                                        <i class="feather feather-trash-2"></i> Delete
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
                        eloquentModel: new Project(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            $columns = [
                'S. No.',
                'Project Name',
                'Description',
                'Created Date',
                'Action',
            ];

            $projects = Project::where('ps_b_id', $businessId)
                ->select('ps_id', 'ps_name', 'ps_description', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.setting.business.business_index', compact('projects', 'columns'));
        }

        abort(404);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $emp_b_id = $user->emp_b_id;

        $isUpdate = $request->filled('ps_id');

        $validated = $request->validate([
            'ps_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects_setup', 'ps_name')
                    ->where(fn($query) => $query->where('ps_b_id', $emp_b_id))
                    ->ignore($request->ps_id, 'ps_id'),
            ],
            'ps_description' => 'required|string',
        ], [
            'ps_name.required' => 'Project name is required.',
            'ps_name.max' => 'Project name should not exceed 255 characters.',
            'ps_name.unique' => 'This project name already exists for your business.',
            'ps_description.required' => 'Project description is required.',
        ]);

        $validated['ps_b_id'] = $emp_b_id;

        if ($isUpdate) {
            $project = Project::findOrFail($request->ps_id);
            $project->update($validated);
            $message = 'Project updated successfully.';
        } else {
            Project::create($validated);
            $message = 'Project created successfully.';
        }

        return response()->json(['message' => $message]);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json(['success' => 'Project deleted successfully.']);
    }
}
