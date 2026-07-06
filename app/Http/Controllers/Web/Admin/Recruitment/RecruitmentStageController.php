<?php

namespace App\Http\Controllers\Web\Admin\Recruitment;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\MasterTable;
use App\Models\Recruitment;
use App\Models\RecruitmentSkill;
use App\Models\RecruitmentStage;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecruitmentStageController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        if ($this->user) {
            // $breadcrumbs = CentralLogics::getBreadcrumbs(); // Generate breadcrumbs
            if ($request->ajax()) {
                $dynamicConditions = [
                    ['method' => 'where', 'args' => ['rsg_b_id', $this->user->emp_b_id]],
                    ['method' => 'select', 'args' => ['rsg_id', 'rsg_b_id', 'rsg_is_active', 'rsg_stage', 'rsg_sequence', 'rsg_stage_type', 'rsg_created_by_id', 'rsg_modified_by_id', 'rsg_recruitment_id', 'rsg_managers', 'created_at', 'updated_at'], 'relation' => ['fh_business:b_id', 'fh_employee_modified:emp_id,emp_b_id,emp_full_name', 'fh_master_table:m_id,m_name', 'fh_employee_created:emp_id,emp_b_id,emp_full_name']],
                    ['method' => 'sortBy', 'args' => ['rsg_recruitment_id', 'rsg_b_id', 'rsg_is_active', 'rsg_stage', 'rsg_sequence', 'rsg_stage_type', 'rsg_created_by_id', 'rsg_modified_by_id', 'rsg_recruitment_id', 'rsg_managers', 'created_at', 'updated_at'],]
                ];

                $searchColumns = ['rsg_id', 'rsg_b_id', 'rsg_is_active', 'rsg_stage', 'rsg_sequence', 'rsg_stage_type', 'rsg_created_by_id', 'rsg_modified_by_id', 'rsg_recruitment_id', 'rsg_managers', 'created_at', 'updated_at'];
                $list = (new DynamicModelDataTableHelper(eloquentModel: new RecruitmentStage(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->fh_recruitment()->pluck('r_title');  // To get an array of 'm_name' values
                    $row[] = $val->rsg_stage;
                    $row[] = $val->fh_managers($val->rsg_managers ?? "[]"); // $val->fh_managers(json_decode($val->rsg_managers));
                    $row[] = $val->fh_master_table()->pluck('m_name');  // To get an array of 'm_name' values
                    $row[] = $val->fh_employee_created()->pluck('emp_full_name');  // To get an array of 'm_name' values
                    $row[] = Carbon::parse($val->created_at)->format('d-M-Y h:i A');
                    $editUrl = route('stages.destroy', $val->rsg_id);
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
                        data-title="Edit Stages"
                        data-bs-target="#createStageModal"
                        data-edit-data=\'' . json_encode([
                            'id' => md5($val->rsg_id),
                            'rsg_b_id' => $val->rsg_b_id,
                            'rsg_is_active' => $val->rsg_is_active,
                            'rsg_stage' => $val->rsg_stage,
                            'rsg_sequence' => $val->rsg_sequence,
                            'rsg_stage_type' => $val->rsg_stage_type,
                            'rsg_created_by_id' => $val->rsg_created_by_id,
                            'rsg_modified_by_id' => $val->rsg_modified_by_id,
                            'rsg_recruitment_id' => $val->rsg_recruitment_id,
                            'rsg_managers' => $val->rsg_managers,
                        ]) . '\'>
                        <i class="feather feather-edit"></i> Edit
                    </button>
                </li>
                <li>
                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-button"
                        data-id="' . $val->rs_id . '"
                        title="Delete"
                        data-url="' . $editUrl . '">
                        <i class="feather feather-trash"></i> Delete
                    </button>
                </li>
            </ul>
        </div>
    </div>
';

$rowData[] = $row;

                }


                $output = ["draw" => intval($request->input('draw')), "recordsTotal" => sizeof($list), "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new RecruitmentStage(), dynamicConditions: $dynamicConditions))->countFilteredServerSideDataTable(), "data" => $rowData,];

                return response()->json($output);
            }
            $columns = ['S. No.', 'Recruitment', 'Title', 'Manager', 'Type', 'Created By', 'W.E.F.', 'Action'];

            $business = $this->user->fh_business()
                ->with(
                    'fh_branches:br_id,br_b_id,br_name',
                    'fh_departments:d_id,d_b_id,d_name',
                    'fh_designations:dg_id,dg_name,dg_b_id',
                    'fh_employees:emp_id,emp_b_id,emp_full_name',
                    'fh_recruitment:r_id,r_b_id,r_title'
                )
                ->where('b_id', $this->user->emp_b_id)
                ->get();
            $stageType = MasterTable::where('m_group', 'STAGE_TYPE')->pluck('m_name', 'm_id')->toArray();
            $employees = $business->first()->fh_employees->pluck('emp_full_name', 'emp_id')->toArray();
            $recruitment = $business->first()->fh_recruitment->pluck('r_title', 'r_id')->toArray();
            return view('recruitment.stages', compact('columns', 'recruitment', 'stageType', 'employees'));
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     // Validation
    //     $request->validate([
    //         'rsg_stage' => 'required|string|max:200',
    //         'rsg_recruitment_id' => 'required|exists:recruitment,r_id',
    //         'rsg_managers' => 'required|array',
    //         'rsg_managers.*' => 'exists:employees,emp_id',
    //         'rsg_stage_type' => 'required|exists:master_table,m_id',
    //     ], [
    //         'rsg_stage.required' => 'The stage name is required.',
    //         'rsg_recruitment_id.required' => 'Please select a recruitment.',
    //         'rsg_recruitment_id.exists' => 'The selected recruitment does not exist.',
    //         'rsg_managers.required' => 'Please select at least one stage manager.',
    //         'rsg_managers.*.exists' => 'One or more selected stage managers are invalid.',
    //         'rsg_stage_type.required' => 'Please select a stage type.',
    //         'rsg_stage_type.exists' => 'The selected stage type is invalid.',
    //     ]);

    //     // Retrieve the maximum sequence for the given recruitment ID
    //     $currentMaxSequence = RecruitmentStage::where('rsg_recruitment_id', $request->input('rsg_recruitment_id'))
    //         ->max('rsg_sequence');

    //     // Set the new sequence value (starts at 0 if no records exist)
    //     $newSequence = $currentMaxSequence !== null ? $currentMaxSequence + 1 : 0;
    //     $hashedRsId = $request->id; // The MD5-hashed rs_id sent from the client

    //     // Search for the skill by matching the hashed rs_id in the database
    //     $stage = RecruitmentStage::whereRaw('MD5(rsg_id) = ?', [$hashedRsId])->first();

    //     if ($stage) {
    //         // Update the existing stage
    //         $stage->update([
    //             'rsg_stage' => $request->input('rsg_stage'),
    //             'rsg_recruitment_id' => $request->input('rsg_recruitment_id'),
    //             'rsg_managers' => json_encode($request->input('rsg_managers')),
    //             'rsg_stage_type' => $request->input('rsg_stage_type'),
    //             'rsg_modified_by_id' => $this->user->emp_id,
    //         ]);
    //     } else {
    //         // Create the new recruitment stage
    //         $recruitmentStage = RecruitmentStage::create([
    //             'rsg_b_id' => $this->user->emp_b_id,
    //             'rsg_is_active' => true,
    //             'rsg_sequence' => $newSequence,
    //             'rsg_stage' => $request->input('rsg_stage'),
    //             'rsg_recruitment_id' => $request->input('rsg_recruitment_id'),
    //             'rsg_managers' => json_encode($request->input('rsg_managers')),
    //             'rsg_stage_type' => $request->input('rsg_stage_type'),
    //             'rsg_created_by_id' => $this->user->emp_id,
    //             'rsg_modified_by_id' => $this->user->emp_id,
    //         ]);
    //     }

    //     // Return success response
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Recruitment stage created successfully!',
    //     ], 201);
    // }
    public function store(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'rsg_stage' => 'required|string|max:200',
                'rsg_recruitment_id' => 'required|exists:recruitment,r_id',
                'rsg_managers' => 'required|array',
                'rsg_managers.*' => 'exists:employees,emp_id',
                'rsg_stage_type' => 'required|exists:master_table,m_id',
            ], [
                'rsg_stage.required' => 'The stage name is required.',
                'rsg_recruitment_id.required' => 'Please select a recruitment.',
                'rsg_recruitment_id.exists' => 'The selected recruitment does not exist.',
                'rsg_managers.required' => 'Please select at least one stage manager.',
                'rsg_managers.*.exists' => 'One or more selected stage managers are invalid.',
                'rsg_stage_type.required' => 'Please select a stage type.',
                'rsg_stage_type.exists' => 'The selected stage type is invalid.',
            ]);

            // Retrieve the maximum sequence for the given recruitment ID
            $currentMaxSequence = RecruitmentStage::where('rsg_recruitment_id', $request->input('rsg_recruitment_id'))
                ->max('rsg_sequence');

            // Set the new sequence value (starts at 0 if no records exist)
            $newSequence = $currentMaxSequence !== null ? $currentMaxSequence + 1 : 0;
            $hashedRsId = $request->id; // The MD5-hashed rs_id sent from the client

            // Search for the skill by matching the hashed rs_id in the database
            $stage = RecruitmentStage::whereRaw('MD5(rsg_id) = ?', [$hashedRsId])->first();

            if ($stage) {
                // Update the existing stage
                $stage->update([
                    'rsg_stage' => $request->input('rsg_stage'),
                    'rsg_recruitment_id' => $request->input('rsg_recruitment_id'),
                    'rsg_managers' => json_encode($request->input('rsg_managers')),
                    'rsg_stage_type' => $request->input('rsg_stage_type'),
                    'rsg_modified_by_id' => $this->user->emp_id,
                ]);
            } else {
                // Create the new recruitment stage
                RecruitmentStage::create([
                    'rsg_b_id' => $this->user->emp_b_id,
                    'rsg_is_active' => true,
                    'rsg_sequence' => $newSequence,
                    'rsg_stage' => $request->input('rsg_stage'),
                    'rsg_recruitment_id' => $request->input('rsg_recruitment_id'),
                    'rsg_managers' => json_encode($request->input('rsg_managers')),
                    'rsg_stage_type' => $request->input('rsg_stage_type'),
                    'rsg_created_by_id' => $this->user->emp_id,
                    'rsg_modified_by_id' => $this->user->emp_id,
                ]);
            }

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Recruitment stage created successfully!',
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Check for Duplicate Entry error (SQLSTATE[23000])
            if ($e->getCode() == "23000") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A recruitment stage with the same name already exists for this recruitment. Enter a different stage name.',
                ], 409); // HTTP 409 Conflict
            }

            // General database error handling
            return response()->json([
                'status' => 'error',
                'message' => 'A database error occurred.',
                'error' => $e->getMessage(), // Remove this in production
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(), // Debugging purpose (remove in production)
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($this->user) {
            $result = CentralLogics::dynamicDelete(RecruitmentStage::class, $id);

            if (isset($result['success'])) {
                return response()->json(['success' => $result['success'], 'message' => 'Stage has been deleted successfully']);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }
}
