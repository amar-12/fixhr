<?php

namespace App\Http\Controllers\Web\Admin\Recruitment;

use App\Helpers\CentralLogics;
use App\Models\RecruitmentSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\MasterTable;
use App\Models\PolicyAttendance;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyWeekOff;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RecruitmentSkillController extends Controller
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
                    ['method' => 'where', 'args' => ['rs_b_id', $this->user->emp_b_id]],
                    ['method' => 'select', 'args' => ['rs_id', 'rs_title', 'rs_b_id', 'rs_created_by_id', 'created_at', 'updated_at'], 'relation' => ['fh_business:b_id', 'fh_employee:emp_id,emp_full_name']],
                    ['method' => 'sortBy', 'args' => ['rs_id', 'rs_title', 'rs_b_id', 'rs_created_by_id', 'created_at', 'updated_at'],]
                ];

                $searchColumns = ['rs_id', 'rs_title', 'rs_b_id', 'rs_created_by_id', 'created_at', 'updated_at'];
                $list = (new DynamicModelDataTableHelper(eloquentModel: new RecruitmentSkill(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->rs_title;
                    $row[] = optional($val->fh_employee)->emp_full_name;

                    $row[] = '<span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                    $editUrl = route('skills.destroy', $val->rs_id);

                    // Edit Button



      $row[] = '
    <div class="btn-list ms-3">
        <div class="dropdown">
            <button class="btn btn-light  btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
            </button>
            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                <li>
                    <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-button"
                        href="#"
                        data-bs-toggle="modal"
                        data-title="Edit Skill"
                        data-bs-target="#createSkillModal"
                        data-edit-data=\'' . json_encode([ 'id'=> md5($val->rs_id),
                        'rs_title' => $val->rs_title,
                        ]) . '\'>
                        <i class="feather feather-edit"></i> Edit
                    </a>
                </li>
                <li>
                    <a class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-shift-type delete-button"
                        href="#"
                        data-id="' . $val->rs_id . '"
                        data-url="' . $editUrl . '"
                        title="Delete">
                        <i class="feather feather-trash me-2 text-danger"></i> Delete
                    </a>
                </li>

            </ul>
        </div>
    </div>';

    $rowData[] = $row;



                }


                $output = ["draw" => intval($request->input('draw')), "recordsTotal" => sizeof($list), "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new RecruitmentSkill(), dynamicConditions: $dynamicConditions))->countFilteredServerSideDataTable(), "data" => $rowData,];

                return response()->json($output);
            }

            $columns = ['S. No.',  'Title', 'Created By', 'W.E.F.', 'Action'];
            $masterData = MasterTable::whereIn('m_group', ['RECURRENCE_DAY', 'WEEK_DAY'])
                ->get()
                ->groupBy('m_group');

            $recurrenceDay = $masterData->get('RECURRENCE_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $weekDay = $masterData->get('WEEK_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $skillsCount = RecruitmentSkill::where('rs_b_id', $this->user->emp_b_id)->count();
            return view('recruitment.skills', compact('columns', 'weekDay', 'recurrenceDay', 'skillsCount'));
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
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'rs_title' => 'required|string|max:255|unique:recruitment_skills,rs_title',
        ], [
            'rs_title.unique' => 'The skill field already exists. Please choose a different one.',
            'rs_title.required' => 'The skill field is required.',
            'rs_title.string' => 'The skill field must be a string.',
            'rs_title.max' => 'The skill field must not exceed 255 characters.'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create the skill
            // Assuming the 'id' in the request is an MD5-hashed 'rs_id'
            $hashedRsId = $request->id; // The MD5-hashed rs_id sent from the client

            // Search for the skill by matching the hashed rs_id in the database
            $skill = RecruitmentSkill::whereRaw('MD5(rs_id) = ?', [$hashedRsId])->first();

            // If skill is found, update it; otherwise, create a new record
            if ($skill) {
                // Update the existing record
                $skill->update([
                    'rs_title' => $request->input('rs_title'),
                    'rs_b_id' => $this->user->emp_b_id,
                    'rs_created_by_id' => $this->user->emp_id,
                ]);
            } else {
                // Create a new record if no match is found
                $skill = RecruitmentSkill::create([
                    'rs_title' => $request->input('rs_title'),
                    'rs_b_id' => $this->user->emp_b_id,
                    'rs_created_by_id' => $this->user->emp_id,
                ]);
            }

            $message = $request->id ? 'Updated' : 'Created';
            return response()->json([
                'status' => 'success',
                'message' => 'Skill ' . $message . ' successfully!',
                'data' => $skill
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => $e->getMessage()
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
            $result = CentralLogics::dynamicDelete(RecruitmentSkill::class, $id);

            if (isset($result['success'])) {
                return response()->json(['success' => $result['success'], 'message' => 'Skill has been deleted successfully']);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }
}
