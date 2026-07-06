<?php

namespace App\Http\Controllers\Web\Admin\Recruitment;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\MasterTable;
use App\Models\Recruitment;
use App\Models\RecruitmentSkill;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class RecruitmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
                ['method' => 'where', 'args' => ['r_b_id', $this->user->emp_b_id]],
                    ['method' => 'select', 'args' => ['r_id', 'r_b_id', 'r_br_id', 'r_dg_id', 'r_title', 'r_description', 'r_is_event_based', 'r_closed', 'r_is_published', 'r_is_active', 'r_vacancy', 'r_start_date', 'r_end_date', 'r_optional_profile_image', 'r_optional_resume', 'r_created_by_id', 'r_modified_by_id', 'r_managers', 'r_skills', 'created_at', 'updated_at'], 'relation' => ['fh_business:b_id']],
                    ['method' => 'sortBy', 'args' => ['r_id','r_b_id', 'r_br_id', 'r_dg_id', 'r_title', 'r_description', 'r_is_event_based', 'r_closed', 'r_is_published', 'r_is_active', 'r_vacancy', 'r_start_date', 'r_end_date', 'r_optional_profile_image', 'r_optional_resume', 'r_created_by_id', 'r_modified_by_id', 'r_managers', 'r_skills', 'created_at', 'updated_at'],]
            ];

                $searchColumns = ['r_b_id', 'r_br_id', 'r_dg_id', 'r_title', 'r_description', 'r_is_event_based', 'r_closed', 'r_is_published', 'r_is_active', 'r_vacancy', 'r_start_date', 'r_end_date', 'r_optional_profile_image', 'r_optional_resume', 'r_created_by_id', 'r_modified_by_id', 'r_managers', 'r_skills', 'created_at', 'updated_at'];
                $list = (new DynamicModelDataTableHelper(eloquentModel: new Recruitment(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->r_title;
                    // Check and decode JSON safely
                    $r_managers = json_decode($val->r_managers, true); // Decode as an array
                    $r_dg_id = json_decode($val->r_dg_id, true); // Decode as an array
                    // Safely count the number of items if valid JSON
                    $row[] = is_array($r_managers) ? count($r_managers) . ' Managers' : '0 Managers';
                    $row[] = is_array($r_dg_id) ? count($r_dg_id) . ' Jobs' : '0 Jobs';

                    $row[] = $val->r_vacancy;
                    $row[] = 0;
                    $row[] = Carbon::parse($val->r_start_date)->format('d-m-y');
                    $row[] = Carbon::parse($val->r_end_date)->format('d-m-y');
                    $row[] = 'Open';
                    $editUrl = route('recruitments.destroy', $val->r_id);
                    $editData = json_encode([
                            'id' => Crypt::encrypt($val->r_id),
                            'r_title' => $val->r_title,
                            'r_dg_id' => $val->r_dg_id,
                            'r_br_id' => $val->r_br_id,
                            'r_is_event_based' => $val->r_is_event_based,
                            'r_closed' => $val->r_closed,
                            'r_is_published' => $val->r_is_published,
                            'r_is_active' => $val->r_is_active,
                            'r_vacancy' => $val->r_vacancy,
                            'r_start_date' => $val->r_start_date,
                            'r_end_date' => $val->r_end_date,
                            'r_optional_profile_image' => $val->r_optional_profile_image,
                            'r_optional_resume' => $val->r_optional_resume,
                            'r_created_by_id' => $val->r_created_by_id,
                            'r_modified_by_id' => $val->r_modified_by_id,
                            'r_managers' => $r_managers,
                            'r_skills' => $val->r_skills,
                            'r_description' => $val->r_description,
                            'created_at' => $val->created_at,
                            'updated_at' => $val->updated_at,
                    ]);

$row[] = '
    <div class="btn-list ms-3">
        <div class="dropdown">
            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
            </button>
            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                <li>
                    <button class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2 share-button"
                            onclick="copyLink(\'' . route('re-ap-form.show', Crypt::encrypt($val->r_id)) . '\')"
                            title="Share Link">
                        <i class="fa fa-share-alt"></i> Share Link
                    </button>
                </li>
                <li>
                    <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-button"
                            data-bs-toggle="modal"
                            data-title="Edit Recruitment"
                            data-bs-target="#recruitmentModal"
                            data-r-description="' . json_encode(htmlspecialchars($val->r_description, ENT_QUOTES, 'UTF-8')) . '"
                            data-edit-data="' . htmlspecialchars($editData, ENT_QUOTES, 'UTF-8') . '">
                        <i class="feather feather-edit"></i> Edit
                    </button>
                </li>
                <li>
                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-button"
                            data-id="' . $val->r_id . '"
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


                $output = ["draw" => intval($request->input('draw')), "recordsTotal" => sizeof($list), "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new Recruitment(), dynamicConditions: $dynamicConditions))->countFilteredServerSideDataTable(), "data" => $rowData,];

                return response()->json($output);
        }

        $columns = ['S. No.', 'Recruitment', 'Managers', 'Open Jobs', 'Vacancy', 'Total Hires', 'Start Date', 'End Date', 'Status', 'Actions'];
        $masterData = MasterTable::whereIn('m_group', ['RECURRENCE_DAY', 'WEEK_DAY'])
            ->get()
            ->groupBy('m_group');

            $recurrenceDay = $masterData->get('RECURRENCE_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $weekDay = $masterData->get('WEEK_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $business = $this->user->fh_business()
                ->with(
            'fh_branches:br_id,br_b_id,br_name',
            'fh_departments:d_id,d_b_id,d_name',
            'fh_designations:dg_id,dg_name,dg_b_id',
            'fh_employees:emp_id,emp_b_id,emp_full_name',
            'fh_recruitment_skills:rs_id,rs_b_id,rs_title'
                )
                ->where('b_id', $this->user->emp_b_id)
                ->get();
            $breadcrumbs = CentralLogics::getBreadcrumbs(); // Generate breadcrumbs
            $employees = $business->first()->fh_employees->pluck('emp_full_name', 'emp_id')->toArray();
            $designations = $business->first()->fh_designations->pluck('dg_name', 'dg_id')->toArray();
            $branch = $business->first()->fh_branches->pluck('br_name', 'br_id')->toArray();
            $skills = $business->first()->fh_recruitment_skills->pluck('rs_title', 'rs_id')->toArray();

            return view('recruitment.recruitments', compact('employees', 'designations', 'branch', 'skills', 'columns'));
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
        // Validate request data
        $validatedData = $request->validate([
            'r_br_id' => 'required|integer|exists:branches,br_id',
            'r_dg_id' => 'required|array|min:1',
            'r_dg_id.*' => 'integer|exists:designations,dg_id',
            'r_title' => 'required|string|max:30',
            'r_description' => 'nullable|string',
            'r_job_type' => 'required|string',
            'r_start_date' => 'required|date',
            'r_end_date' => 'required|date|after_or_equal:r_start_date',
            'r_vacancy' => 'required|integer|min:1',
            'r_education' => 'required|string',
            'r_experience' => 'required|integer|min:0',
            'r_salary' => 'nullable|string',
            'r_shift' => 'required|string',
            'r_schedule' => 'required|string',
            'r_location' => 'required|string',
            'r_managers' => 'required|array',
            'r_languages' => 'required|array',
            'r_skills' => 'nullable|array',
            'r_modified_by_id' => 'nullable|integer|exists:employees,emp_id',
        ], [
            'r_br_id.required' => 'Branch is required',
            'r_br_id.exists' => 'Branch does not exist',
            'r_dg_id.required' => 'Designation is required',
            'r_dg_id.*.exists' => 'One or more designations do not exist',
            'r_title.required' => 'Title is required',
            'r_title.max' => 'Title must not exceed 30 characters',
            'r_start_date.required' => 'Start date is required',
            'r_end_date.after_or_equal' => 'End date must be after or equal to the start date',
            'r_vacancy.min' => 'Vacancy must be at least 1',
        ]);

        // Decrypt ID if present (for updates)
        $r_id = $request->id ? Crypt::decrypt($request->id) : null;

        // Set default values
        $validatedData['r_b_id'] = $this->user->emp_b_id;
        $validatedData['r_created_by_id'] = $this->user->emp_id;
        $validatedData['r_modified_by_id'] = $this->user->emp_id;
        $validatedData['r_is_event_based'] = 1;
        $validatedData['r_is_published'] = 1;
        $validatedData['r_is_active'] = 1;
        $validatedData['r_optional_profile_image'] = 0;
        $validatedData['r_optional_resume'] = 0;

        // Create or update recruitment
        $recruitment = Recruitment::updateOrCreate(
            ['r_id' => $r_id],
            [
                'r_b_id' => $validatedData['r_b_id'],
                'r_br_id' => $validatedData['r_br_id'],
                'r_dg_id' => json_encode($validatedData['r_dg_id']),
                'r_title' => $validatedData['r_title'],
                'r_description' => $validatedData['r_description'] ?? '',
                'r_job_type' => $validatedData['r_job_type'],
                'r_start_date' => $validatedData['r_start_date'],
                'r_end_date' => $validatedData['r_end_date'],
                'r_is_event_based' => $validatedData['r_is_event_based'],
                'r_is_published' => $validatedData['r_is_published'],
                'r_is_active' => $validatedData['r_is_active'],
                'r_optional_profile_image' => $validatedData['r_optional_profile_image'],
                'r_optional_resume' => $validatedData['r_optional_resume'],
                'r_created_by_id' => $validatedData['r_created_by_id'],
                'r_modified_by_id' => $validatedData['r_modified_by_id'],
                'r_vacancy' => $validatedData['r_vacancy'],
                'r_education' => $validatedData['r_education'],
                'r_experience' => $validatedData['r_experience'],
                'r_salary' => $validatedData['r_salary'],
                'r_shift' => $validatedData['r_shift'],
                'r_schedule' => $validatedData['r_schedule'],
                'r_location' => $validatedData['r_location'],
                'r_managers' => json_encode($validatedData['r_managers']),
                'r_languages' => json_encode($validatedData['r_languages']),
                'r_skills' => json_encode($validatedData['r_skills'] ?? []),
            ]
        );

        // Insert recruitment stages only for new entries
        if (!$r_id) {
            $stageType = MasterTable::where('m_group', 'STAGE_TYPE')
                ->whereIn('m_id', ['340', '341'])
                ->orderByDesc('m_id')
                ->pluck('m_name', 'm_id')
                ->toArray();

            foreach ($stageType as $key => $stage) {
                $recruitment->fh_recruitment_stages()->create([
                    'rsg_b_id' => $this->user->emp_b_id,
                    'rsg_is_active' => 1,
                    'rsg_stage' => $stage,
                    'rsg_sequence' => 0,
                    'rsg_managers' => json_encode($validatedData['r_managers']),
                    'rsg_stage_type' => $key,
                    'rsg_created_by_id' => $this->user->emp_id,
                    'rsg_modified_by_id' => $this->user->emp_id,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $r_id ? 'Recruitment updated successfully!' : 'Recruitment created successfully!',
            'data' => $recruitment
        ], 201);
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
            $result = CentralLogics::dynamicDelete(Recruitment::class, $id);

            if (isset($result['success'])) {
                return response()->json(['success' => $result['success'], 'message' => 'Recruitment has been deleted successfully']);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }
}
