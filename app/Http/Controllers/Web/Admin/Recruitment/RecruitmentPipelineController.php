<?php

namespace App\Http\Controllers\Web\Admin\Recruitment;

use App\Helpers\CentralLogics;
use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Employee;
use App\Models\MailTemplate;
use App\Models\MasterTable;
use App\Models\Recruitment;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentEmailLog;
use App\Models\RecruitmentInterviewschedule;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RecruitmentPipelineController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($this->user) {
            $breadcrumbs = CentralLogics::getBreadcrumbs(); // Generate breadcrumbs
            $masterData = MasterTable::whereIn('m_group', ['RECURRENCE_DAY', 'WEEK_DAY'])
                ->get()
                ->groupBy('m_group');

            $recurrenceDay = $masterData->get('RECURRENCE_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $weekDay = $masterData->get('WEEK_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            // Load the business and its relationships
            $business = $this->user->fh_business()
                ->with([
                    'fh_branches:br_id,br_b_id,br_name',
                    'fh_departments:d_id,d_b_id,d_name',
                    'fh_designations:dg_id,dg_name,dg_b_id',
                    'fh_employees:emp_id,emp_b_id,emp_full_name,emp_code',
                    'fh_recruitment_skills:rs_id,rs_b_id,rs_title',
                    'fh_recruitment_candidates',
                    'fh_recruitments',
                    'fh_recruitments.fh_recruitment_stages',
                    'fh_recruitments.fh_recruitment_candidates',
                    'fh_recruitments.fh_recruitment_candidates.fh_designation',
                    'fh_mail_templates'
                ])
                ->where('b_id', $this->user->emp_b_id)
                ->first(); // Using first here as you're dealing with a single business

            // Access the relationships directly from the $business object:
            $employees = $business->fh_employees()
                ->selectRaw("CASE WHEN emp_code IS NOT NULL THEN CONCAT(emp_code, ' - ', emp_full_name) ELSE emp_full_name END as name, emp_id")
                ->get()
                ->pluck('name', 'emp_id')
                ->toArray();

            $designations = $business->fh_designations->pluck('dg_name', 'dg_id')->toArray();
            $branch = $business->fh_branches->pluck('br_name', 'br_id')->toArray();
            $skills = $business->fh_recruitment_skills->pluck('rs_title', 'rs_id')->toArray();
            $candidates = $business->fh_recruitment_candidates; // No need to call get() if it's already a collection
            $mailTemplate = $business->fh_mail_templates->whereIn('mt_mail_type', [374, 375, 376]);
            // Apply pagination on the fh_recruitments relation (use query builder instead of calling paginate on collection)
            $recruitment = $business->fh_recruitments()
                ->where('r_b_id', $this->user->emp_b_id)
                ->paginate(5);


            $sheduled = RecruitmentInterviewschedule::get();
            $emal_logs = RecruitmentEmailLog::get();

            $mailTemplateIds = $emal_logs->pluck('rel_mail_template_id')->unique();
            $mailTemplates = MailTemplate::whereIn('mt_id', $mailTemplateIds)->get()->keyBy('mt_id');




            return view('recruitment.pipeline', compact('employees', 'designations', 'branch', 'skills',  'candidates', 'recruitment', 'breadcrumbs', 'mailTemplate', 'sheduled', 'mailTemplates','emal_logs'));
        } else {
            abort(404);
        }
    }


    public function getCandidateData(Request $request)
    {
        $candidateId = Crypt::decrypt($request->id);
        $candidate = RecruitmentCandidate::with(['fh_recruitment_email_logs'])->where('rc_id', $candidateId)->first();
        $mailTemplateIds = $candidate?->fh_recruitment_email_logs?->pluck('rel_mail_template_id')->toArray() ?? [];
        $templates = MailTemplate::whereIn('mt_mail_type', [374, 375,  443, 376])->whereNotIn('mt_id', $mailTemplateIds)->get();
        $options = '';
        foreach ($templates as $template) {
            $options .= "<option value='{$template->mt_id}'>{$template->mt_title}</option>";
        }

        return response()->json([
            'candidate_id' => $candidateId,
            'options' => $options,
        ]);
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
            'r_dg_id' => 'required|array|min:1', // Ensure it's an array with at least one value
            'r_dg_id.*' => 'integer|exists:designations,dg_id', // Validate each item in the array
            'r_title' => 'required|string|max:30',
            'r_description' => 'required|string',
            'respective_dg_id' => 'required|array|min:1',
            'r_dg_id.*' => 'integer|exists:designations,dg_id',
            // 'r_is_event_based' => 'required|boolean',
            // 'r_is_published' => 'required|boolean',
            // 'r_is_active' => 'required|boolean',
            'r_vacancy' => 'nullable|integer',
            'r_start_date' => 'required|date',
            'r_end_date' => 'nullable|date|after_or_equal:r_start_date',
            // 'r_optional_profile_image' => 'required|boolean',
            // 'r_optional_resume' => 'required|boolean',
            // 'r_created_by_id' => 'required|integer|exists:employees,emp_id',
            'r_modified_by_id' => 'nullable|integer|exists:employees,emp_id',
            'r_managers' => 'nullable|array',
            'r_skills' => 'nullable|array',
        ], [
            'r_br_id.required' => 'Branch is required',
            'r_br_id.exists' => 'Branch does not exist',
            'r_dg_id.required' => 'Designation is required',
            'r_dg_id.exists' => 'Designation does not exist',
            'r_title.required' => 'Title is required',
            'r_title.string' => 'Title must be a string',
            'r_title.max' => 'Title must not exceed 30 characters',
            'r_description.required' => 'Description is required',
            'r_description.string' => 'Description must be a string',
            'r_is_event_based.required' => 'Event-based status is required',
            'r_is_event_based.boolean' => 'Event-based status must be a boolean',
            'r_is_published.required' => 'Published status is required',
            'r_is_published.boolean' => 'Published status must be a boolean',
            'r_is_active.required' => 'Active status is required',
            'r_is_active.boolean' => 'Active status must be a boolean',
            'r_vacancy.integer' => 'Vacancy must be an integer',
            'r_start_date.required' => 'Start date is required',
            'r_start_date.date' => 'Start date must be a date',
            'r_end_date.date' => 'End date must be a date',
            'r_end_date.after_or_equal' => 'End date must be on or after the start date',
            'r_optional_profile_image.required' => 'Optional profile image status is required',
            'r_optional_profile_image.boolean' => 'Optional profile image status must be a boolean',
        ]);

        // Add additional data not in the request
        $validatedData['r_b_id'] = $this->user->emp_b_id; // Assume $this->user is available

        // Create recruitment record
        $recruitment = Recruitment::create([
            'r_b_id' => $this->user->emp_b_id,
            'r_br_id' => $validatedData['r_br_id'],
            'r_dg_id' => json_encode($validatedData['r_dg_id']),
            'r_title' => $validatedData['r_title'],
            'r_description' => $validatedData['r_description'],
            // 'r_is_event_based' => $validatedData['r_is_event_based'],
            // 'r_is_published' => $validatedData['r_is_published'],
            // 'r_is_active' => $validatedData['r_is_active'],
            'r_vacancy' => $validatedData['r_vacancy'],
            'r_start_date' => $validatedData['r_start_date'],
            'r_end_date' => $validatedData['r_end_date'],
            'r_is_event_based' => 1,
            'r_is_published' => 1,
            'r_is_active' => 1,
            'r_optional_profile_image' => 0,
            'r_optional_resume' => 0,
            'r_created_by_id' => $this->user->emp_id,
            'r_modified_by_id' => $this->user->emp_id,
            'r_managers' => json_encode($validatedData['r_managers']),
            'r_skills' => json_encode($validatedData['r_skills']),
        ]);

        // Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Recruitment created successfully!',
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
        //
    }

    // public function updateCandidateStage(Request $request)
    // {
    //     $request->validate([
    //         'candidate_id' => 'required|integer',
    //         'stage_id' => 'required|integer',
    //     ]);

    //     // Find the candidate and update the stage
    //     $candidate = RecruitmentCandidate::find($request->candidate_id);
    //     if ($candidate) {
    //         $candidate->rc_stage_id = $request->stage_id; // Assuming you have a stage_id field
    //         $candidate->save();
    //         return response()->json(['success' => true, 'recruitmentKey' => $candidate->rc_recruitment_id, 'newStageId' => $candidate->rc_stage_id]);
    //     }

    //     return response()->json(['success' => false, 'message' => 'Candidate not found.']);
    // }

    // public function updateBulkCandidateStage(Request $request)
    // {
    //     // Validate incoming request
    //     $request->validate([
    //         'candidate_id' => 'required|array',  // Array of candidate IDs
    //         'stage_id' => 'required|integer',    // Stage ID
    //     ]);

    //     // Fetch the candidates by candidate IDs
    //     $candidates = RecruitmentCandidate::whereIn(DB::raw('md5(rc_id)'), $request->candidate_id)->get();
    //     // If no candidates are found, return an error response
    //     if ($candidates->isEmpty()) {
    //         return response()->json(['success' => false, 'message' => 'No candidates found.']);
    //     }

    //     // Update the candidates' stage
    //     $updatedCandidates = RecruitmentCandidate::whereIn(DB::raw('md5(rc_id)'), $request->candidate_id)
    //         ->update(['rc_stage_id' => $request->stage_id]);

    //     // Get the primary ids (rc_id) of updated candidates
    //     $updatedCandidateIds = $candidates->pluck('rc_id');

    //     // Return the response with updated candidate IDs
    //     return response()->json([
    //         'success' => true,
    //         'updated_candidates' => $updatedCandidateIds, // Updated candidate IDs
    //         'new_stage_id' => $request->stage_id, // The stage ID that's been updated
    //         'recruitmentKey' => $candidates->first()->rc_recruitment_id,
    //     ]);
    // }

    public function updateCandidateStage(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'candidate_id' => 'required', // candidate_id can be an integer or array
            'stage_id' => 'required|integer', // Stage ID
        ]);

        // Check if it's a single candidate or bulk candidates
        $candidateIds = is_array($request->candidate_id) ? $request->candidate_id : [$request->candidate_id];
        $stageId = $request->stage_id;

        // If multiple candidate IDs are provided, perform bulk update
        if (count($candidateIds) > 1) {
            // Bulk update: Update the stage for all candidates
            $candidates = RecruitmentCandidate::whereIn(DB::raw('md5(rc_id)'), $candidateIds)->get();

            if ($candidates->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No candidates found.']);
            }

            // Perform the bulk update
            RecruitmentCandidate::whereIn(DB::raw('md5(rc_id)'), $candidateIds)
                ->update(['rc_stage_id' => $stageId]);

            // Collect updated candidate IDs
            $updatedCandidateIds = $candidates->pluck('rc_id');

            // Return the response with the updated candidates
            return response()->json([
                'success' => true,
                'updated_candidates' => $updatedCandidateIds,
                'new_stage_id' => $stageId,
                'recruitmentKey' => $candidates->first()->rc_recruitment_id,
            ]);
        } else {
            // Single candidate update
            $candidate = RecruitmentCandidate::find($candidateIds[0]);

            if ($candidate) {
                // Update the stage for the single candidate
                $candidate->rc_stage_id = $stageId;
                $candidate->save();

                return response()->json([
                    'success' => true,
                    'recruitmentKey' => $candidate->rc_recruitment_id,
                    'newStageId' => $candidate->rc_stage_id,
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'Candidate not found.']);
            }
        }
    }

    public function interviewSchedule(Request $request)
    {
        $request->merge([
            'ris_candidate_id' => Crypt::decrypt($request->ris_candidate_id)
        ]);

        $validator = Validator::make($request->all(), [
            'ris_candidate_id' => 'required|exists:recruitment_candidate,rc_id', // Assuming rc_id is the primary key in candidates table
            'ris_interviewer' => 'required|array|min:1', // Validates that at least one interviewer is selected
            'ris_interviewer.*' => 'exists:employees,emp_id', // Ensures all selected interviewers are valid employees
            'ris_interview_date' => 'required|date|after_or_equal:today', // Ensures the date is today or in the future
            'ris_interview_time' => [
                'required',
                'date_format:H:i', // Ensures valid time format
                function ($attribute, $value, $fail) use ($request) {
                    // Check if the interview date is today and time is in the past
                    if ($request->ris_interview_date === date('Y-m-d') && strtotime($value) < time()) {
                        $fail('Interview time cannot be in the past when the interview date is today.');
                    }
                },
            ],
            'ris_description' => 'required|string|max:1000', // Optional description with max length
            // 'ris_completed' => 'boolean', // Ensures the checkbox is either true or false
        ], [
            'ris_candidate_id.required' => 'Candidate is required.',
            'ris_candidate_id.exists' => 'Candidate not found.',
            'ris_interviewer.required' => 'At least one interviewer is required.',
            'ris_interviewer.*.exists' => 'Invalid interviewer.',
            'ris_interview_date.required' => 'Interview date is required.',
            'ris_interview_date.date' => 'Interview date must be a valid date.',
            'ris_interview_date.after_or_equal' => 'Interview date must be today or in the future.',
            'ris_interview_time.required' => 'Interview time is required.',
            'ris_interview_time.date_format' => 'Interview time must be in the format HH:mm.',
            'ris_description.required' => 'Interview description is required.',
            'ris_description.max' => 'Description must not exceed 1000 characters.',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = RecruitmentInterviewschedule::create([
            'ris_is_active' => 1,
            'ris_interviewer' => json_encode(array_map('intval', $request->ris_interviewer)),
            'ris_interview_date' => $request->ris_interview_date,
            'ris_interview_time'  => $request->ris_interview_time,
            'ris_description'  => $request->ris_description,
            'ris_completed'  => $request->ris_completed == 'on' ? 1 : 0,
            'ris_candidate_id'  => $request->ris_candidate_id,
            'ris_created_by_id'  => $this->user->emp_id,
        ]);

        if ($data) {
            $candidate_id = $data->ris_candidate_id;

            $candidate = RecruitmentCandidate::where('rc_id', $candidate_id)
                ->select('rc_name', 'rc_email', 'rc_recruitment_id')
                ->first();

            $business = Business::where('b_id', $this->user->emp_b_id)
                ->select('b_name', 'b_address', 'b_pin_code')
                ->first();

            $positionData = Recruitment::where('r_id', $candidate->rc_recruitment_id)
                ->select('r_title')
                ->first();

            $candidate_name = $candidate->rc_name;
            $position = $positionData->r_title;
            // $date = $data->ris_interview_date;
            // $time = $data->ris_interview_time;

            $date = date('d M Y', strtotime($data->ris_interview_date));
            $time = date('h:i A', strtotime($data->ris_interview_time));


            $address = $business->b_address;
            $pincode = $business->b_pin_code;

            $manager_ids = json_decode($data->ris_interviewer);
            $manager_names = [];

            // Email Templates
            $candidateTemplate = MailTemplate::where('mt_mail_type', 375)->first();
            $interviewerTemplate = MailTemplate::where('mt_mail_type', 443)->first();

            // dd($candidateTemplate, $interviewerTemplate);

            // Send mail to interviewers
            foreach ($manager_ids as $manager_id) {
                $manager = Employee::where('emp_id', $manager_id)
                    ->select('emp_full_name', 'emp_email')
                    ->first();

                if ($manager) {
                    $manager_name = $manager->emp_full_name;
                    $manager_email = $manager->emp_email ?? "khilesh.fixingdot@gmail.com";
                    // $manager_email = "khilesh.fixingdot@gmail.com";
                    $manager_names[] = $manager_name;

                    $interviewerPlaceholders = [
                        '{{interviewer_name}}' => $manager_name,
                        '{{candidate_name}}' => $candidate_name,
                        '{{position}}' => $position,
                        '{{date}}' => $date,
                        '{{time}}' => $time,
                        '{{address}}' => $address,
                        '{{pincode}}' => $pincode,
                    ];

                    CentralLogics::sendemailinteviver(
                        $interviewerTemplate,
                        $interviewerPlaceholders,
                        $manager_email,
                        $this->user->emp_b_id
                    );
                }
            }

            // Send mail to candidate
            $interviewer_list = implode(', ', $manager_names);

            $candidatePlaceholders = [
                '{{candidate_name}}' => $candidate_name,
                '{{position}}' => $position,
                '{{date}}' => $date,
                '{{time}}' => $time,
                '{{address}}' => $address,
                '{{pincode}}' => $pincode,
                '{{interviewer_list}}' => $interviewer_list,
            ];

            CentralLogics::sendemailcandidate(
                $candidateTemplate,
                $candidatePlaceholders,
                $candidate->rc_email ?? "fogame3065@f5url.com",
                // $candidate = "fogame3065@f5url.com",
                $this->user->emp_b_id
            );
        }



        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => 'Interview Schedule successfully!',
                // 'data' => $data
            ], 201);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong. Please try again later.',
            // 'error' => $e->getMessage()
        ], 500);
    }

    public function sendMailStore(Request $request)
    {
        $request->merge([
            'rel_candidate_id' => Crypt::decrypt($request->rel_candidate_id)
        ]);

        $validator = Validator::make($request->all(), [
            'rel_candidate_id' => 'required|exists:recruitment_candidate,rc_id',
            'rel_mail_template_id' => 'required|exists:mail_templates,mt_id',
            'rel_subject' => 'required',
            'rel_body' => 'required',
        ], [
            'rel_candidate_id.required' => 'Candidate ID is required.',
            'rel_candidate_id.exists' => 'Candidate not found.',
            'rel_mail_template_id.required' => 'Mail template ID is required.',
            'rel_mail_template_id.exists' => 'Mail template not found.',
            'rel_subject.required' => 'Subject is required.',
            'rel_body.required' => 'Body is required.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        $recruitmentCandidate = RecruitmentCandidate::find($request->rel_candidate_id);
        // Store the relationship
        $envFromAddress = env('MAIL_FROM_ADDRESS'); // Retrieves the value of APP_ENV

        $data = RecruitmentEmailLog::create([
            'rel_b_id' => $this->user->emp_b_id,
            'rel_to' => $recruitmentCandidate->rc_email,
            'rel_subject' => $request->rel_subject,
            'rel_candidate_id' => $request->rel_candidate_id,
            'rel_mail_template_id' => $request->rel_mail_template_id,
            'rel_subject' => $request->rel_subject,
            'rel_body' => $request->rel_body,
            'rel_attachment' => $request->rel_attachment,
            'rel_from_email' => $envFromAddress,
            'rel_status' => 1,
        ]);
        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => 'Mail store successfully!',
            ], 201);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong. Please try again later.',
        ], 500);
    }
}
