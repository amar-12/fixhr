<?php

namespace App\Http\Controllers\Web\Admin\Recruitment;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\Recruitment;
use App\Models\RecruitmentCandidate;
use Carbon\Carbon;
use ChandraHemant\HtkcUtils\CommonUtils;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Aws\AwsHelper;

class RecruitmentCandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $user, $awsHelper;

    public function __construct(AwsHelper $awsHelper)
    {
        if(env('STORE_ON_S3')){
            $this->awsHelper = $awsHelper;
        }
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        if ($this->user) {
            // $breadcrumbs = CentralLogics::getBreadcrumbs(); // Generate breadcrumbs
            if ($request->ajax()) {
                $dynamicConditions = [
                    ['method' => 'where', 'args' => ['rc_b_id', $this->user->emp_b_id]],
                    ['method' => 'select', 'args' => ['rc_id', 'rc_b_id', 'rc_is_active', 'rc_name', 'rc_profile', 'rc_portfolio', 'rc_schedule_date', 'rc_email', 'rc_mobile', 'rc_resume', 'rc_address', 'rc_country', 'rc_dob', 'rc_state', 'rc_city', 'rc_zip', 'rc_gender', 'rc_source', 'rc_start_onboard', 'rc_hired', 'rc_canceled', 'rc_joining_date', 'rc_sequence', 'rc_probation_end', 'rc_offer_letter_status', 'rc_last_updated', 'rc_converted_employee_id', 'rc_created_by_id', 'rc_modified_by_id', 'rc_referral_id', 'rc_dg_id', 'rc_recruitment_id', 'rc_stage_id'], 'relation' => ['fh_designation:dg_id,dg_b_id,dg_name']],
                    ['method' => 'sortBy', 'args' => ['rc_is_active', 'rc_name', 'rc_profile', 'rc_portfolio', 'rc_schedule_date', 'rc_email', 'rc_mobile', 'rc_resume', 'rc_address', 'rc_country', 'rc_dob', 'rc_state', 'rc_city', 'rc_zip', 'rc_gender', 'rc_source', 'rc_start_onboard', 'rc_hired', 'rc_canceled', 'rc_joining_date', 'rc_sequence', 'rc_probation_end', 'rc_offer_letter_status', 'rc_last_updated', 'rc_converted_employee_id', 'rc_created_by_id', 'rc_modified_by_id', 'rc_referral_id', 'rc_dg_id', 'rc_recruitment_id', 'rc_stage_id'],]
                ];

                $searchColumns = ['rc_id', 'rc_is_active', 'rc_name', 'rc_profile', 'rc_portfolio', 'rc_schedule_date', 'rc_email', 'rc_mobile', 'rc_resume', 'rc_address', 'rc_country', 'rc_dob', 'rc_state', 'rc_city', 'rc_zip', 'rc_gender', 'rc_source', 'rc_start_onboard', 'rc_hired', 'rc_canceled', 'rc_joining_date', 'rc_sequence', 'rc_probation_end', 'rc_offer_letter_status', 'rc_last_updated', 'rc_converted_employee_id', 'rc_created_by_id', 'rc_modified_by_id', 'rc_referral_id', 'rc_dg_id', 'rc_recruitment_id', 'rc_stage_id'];
                $list = (new DynamicModelDataTableHelper(eloquentModel: new RecruitmentCandidate(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $profile_url = $val->rc_profile ?  $val->rc_profile : $val->rc_profile;
                    $row[] = '<a href="' . route('candidates.show', ['candidate' => md5($val->rc_id)]) . '">
                        <div class="d-flex">
                            <span class="avatar avatar-md brround me-3"
                                style="background-image: url(' . $profile_url . ')"></span>
                            <div class="me-3 mt-0 mt-sm-1 d-block">
                                <h6 class="mb-1 fs-14">' . htmlspecialchars($val->rc_name, ENT_QUOTES, 'UTF-8') . '</h6>
                                <p class="text-muted mb-0 fs-12">' . htmlspecialchars(optional($val->fh_designation)->dg_name, ENT_QUOTES, 'UTF-8') . '</p>
                            </div>
                        </div>
                    </a>';

                    $row[] = $val->rc_email;
                    $row[] = $val->rc_mobile;
                    $row[] = optional($val->fh_recruitment)->r_title;
                    $row[] = optional($val->fh_designation)->dg_name;
                    $row[] = ' <img src="assets/images/files/file.png" alt="img" class="w-20 mx-auto">';

                    $editUrl = route('skills.destroy', $val->rc_id);
                    $editData = json_encode([
                        'id' => md5($val->rc_id),
                        'profile-image-preview' => e($val->rc_profile), // Escape to prevent XSS
                        'rc_name' => e($val->rc_name), // Escape to prevent XSS
                        'rc_portfolio' => e($val->rc_portfolio),
                        'rc_email' => e($val->rc_email),
                        'rc_mobile' => e($val->rc_mobile),
                        'rc_state_edit_value' => e($val->rc_state),
                        'rc_gender' => e($val->rc_gender),
                        'rc_dob' => e($val->rc_dob),
                        'rc_recruitment_id' => e($val->rc_recruitment_id),
                        'rc_dg_id' => e($val->rc_dg_id),
                        'rc_address' => e($val->rc_address),
                        'rc_state' => e($val->rc_state),
                        'rc_country' => e($val->rc_country),
                        'rc_city' => e($val->rc_city),
                        'rc_zip' => e($val->rc_zip),
                        'rc_resume' => e($val->rc_resume), // Ensure resume path is escaped if it's a URL
                        'rc_resume_edit' => e($val->rc_resume),
                    ]);

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
                        data-title="Edit Candidates"
                        data-bs-target="#createCandidateModal"
                        data-edit-data="' . htmlspecialchars($editData, ENT_QUOTES, 'UTF-8') . '">
                        <i class="feather feather-edit"></i> Edit
                    </button>
                </li>
                <li>
                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-button"
                        data-id="' . e($val->rs_id) . '"
                        title="Delete"
                        data-url="' . e($editUrl) . '">
                        <i class="feather feather-trash"></i> Delete
                    </button>
                </li>
            </ul>
        </div>
    </div>
';

$rowData[] = $row;

                }


                $output = ["draw" => intval($request->input('draw')), "recordsTotal" => sizeof($list), "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new RecruitmentCandidate(), dynamicConditions: $dynamicConditions))->countFilteredServerSideDataTable(), "data" => $rowData,];

                return response()->json($output);
            }

            $columns = ['S. No.', 'Candidates', 'Email', 'Phone', 'Recruitment', 'Job Position', 'Resume', 'Actions'];

            $masterData = MasterTable::whereIn('m_group', ['RECURRENCE_DAY', 'WEEK_DAY'])
                ->get()
                ->groupBy('m_group');

            $recurrenceDay = $masterData->get('RECURRENCE_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $weekDay = $masterData->get('WEEK_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $candidatesCount = RecruitmentCandidate::count();

            $business = $this->user->fh_business()
                ->with(
                    'fh_branches:br_id,br_b_id,br_name',
                    'fh_departments:d_id,d_b_id,d_name',
                    'fh_designations:dg_id,dg_name,dg_b_id',
                    'fh_employees:emp_id,emp_b_id,emp_full_name',
                    'fh_recruitment_skills:rs_id,rs_b_id,rs_title',
                    'fh_recruitment:r_id,r_b_id,r_title'
                )
                ->where('b_id', $this->user->emp_b_id)
                ->get();
            $recruitment =  $business->first()->fh_recruitment->pluck('r_title', 'r_id')->toArray();
            $employees =  $business->first()->fh_employees->pluck('emp_full_name', 'emp_id')->toArray();
            $gender = MasterTable::where("m_group", 'GENDER')->pluck('m_name', 'm_id')->toArray();
            $country = Country::pluck('c_name', 'c_id')->toArray();
            $designations = $business->first()->fh_designations->pluck('dg_name', 'dg_id')->toArray();
            return view('recruitment.candidates', compact('columns', 'gender', 'designations', 'country', 'candidatesCount', 'employees', 'recruitment'));
        } else {
            abort(404);
        }

        return view('recruitment.candidates', compact('columns'));
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
    //     $validatedData = $request->validate([
    //         // 'profile_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // optional, must be an image
    //         'rc_name' => 'required|string|max:30',
    //         'rc_dg_id' => 'required|exists:designations,dg_id',  // at least one job position is selected
    //         'rc_email' => 'required|email|max:255|unique:users,email',  // email is unique
    //         'rc_mobile' => 'required|string|max:15|regex:/^[0-9]+$/', // only numbers
    //         'rc_gender' => 'required|exists:master_table,m_id',  // at least one gender is selected
    //         'rc_address' => 'required|string|max:255',
    //         'rc_country' => 'required|exists:countries,c_id',  // at least one country selected
    //         'rc_state' => 'required|exists:states,s_id',    // at least one state selected
    //         'rc_city' => 'required|string|max:50',
    //         // 'rc_resume' => 'required',
    //         'rc_address' => 'required',
    //         'rc_zip' => 'required',
    //         // 'rc_zip' => 'required|string|max:10|regex:/^\d{5}(-\d{4})?$/', // valid zip code
    //     ], [
    //         'rc_name.required' => 'Please enter your name',
    //         'rc_name.max' => 'Name should not be more than 30 characters',
    //         'rc_dg_id.required' => 'Please select job position',
    //         'rc_dg_id.exists' => 'Please select job position',
    //         'rc_email.required' => 'Please enter your email',
    //         'rc_email.email' => 'Please enter a valid email',
    //         'rc_email.unique' => 'Email already exists',
    //         'rc_mobile.required' => 'Please enter your mobile number',
    //         'rc_mobile.max' => 'Mobile number should not be more than 15 characters',
    //         'rc_mobile.regex' => 'Please enter a valid mobile number',
    //         'rc_gender.required' => 'Please select gender',
    //         'rc_gender.exists' => 'Please select gender',
    //         'rc_address.required' => 'Please enter your address',
    //         'rc_address.max' => 'Address should not be more than 255 characters',
    //         'rc_country.required' => 'Please select country',
    //         'rc_country.exists' => 'Please select country',
    //         'rc_state.required' => 'Please select state',
    //         'rc_state.exists' => 'Please select state',
    //         'rc_city.required' => 'Please enter your city',
    //         'rc_city.max' => 'City should not be more than 50 characters',
    //         'rc_resume.required' => 'Please upload your resume',
    //         'rc_zip.required' => 'Please enter your zip code',
    //         'rc_zip.max' => 'Zip code should not be more than 10 characters',
    //         'rc_zip.regex' => 'Please enter a valid zip code',

    //     ]);

    //     $hashedRsId = $request->id; // The MD5-hashed rs_id sent from the client
    //     $candidate = RecruitmentCandidate::whereRaw('MD5(rc_id) = ?', [$hashedRsId])->first();
    //     if ($candidate) {
    //         $candidate->update([
    //             'rc_name' => $request->rc_name,
    //             'rc_portfolio' => $request->rc_portfolio,
    //             'rc_email' => $request->rc_email,
    //             'rc_mobile' => $request->rc_mobile,
    //             'rc_dob' => $request->rc_dob,
    //             'rc_gender' => $request->rc_gender,
    //             'rc_recruitment_id' => $request->rc_recruitment_id,
    //             'rc_dg_id' => $request->rc_dg_id,
    //             'rc_address' => $request->rc_address,
    //             'rc_source' => $request->rc_source,
    //             'rc_country' => $request->rc_country,
    //             'rc_state' => $request->rc_state,
    //             'rc_city' => $request->rc_city,
    //             'rc_zip' => $request->rc_zip,
    //             'rc_referral' => $request->rc_referral,
    //         ]);
    //     } else {
    //         RecruitmentCandidate::create([
    //             'rc_name' => $request->rc_name,
    //             'rc_portfolio' => $request->rc_portfolio,
    //             'rc_email' => $request->rc_email,
    //             'rc_mobile' => $request->rc_mobile,
    //             'rc_dob' => $request->rc_dob,
    //             'rc_gender' => $request->rc_gender,
    //             'rc_recruitment_id' => $request->rc_recruitment_id,
    //             'rc_dg_id' => $request->rc_dg_id,
    //             'rc_address' => $request->rc_address,
    //             'rc_source' => $request->rc_source,
    //             'rc_country' => $request->rc_country,
    //             'rc_state' => $request->rc_state,
    //             'rc_city' => $request->rc_city,
    //             'rc_zip' => $request->rc_zip,
    //             'rc_referral' => $request->rc_referral,
    //         ]);
    //     }
    //     $message = $request->id ? 'Updated' : 'Created';
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Skill ' . $message . ' successfully!',
    //         'data' => $skill
    //     ], 201);
    // }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'rc_name' => 'required|string|max:30',
                'rc_dg_id' => 'required|exists:designations,dg_id',
                'rc_email' => 'required|email|max:255|unique:users,email',
                'rc_mobile' => 'required|string|max:15|regex:/^[0-9]+$/',
                'rc_gender' => 'required|exists:master_table,m_id',
                'rc_address' => 'required|string|max:255',
                'rc_country' => 'required|exists:countries,c_id',
                'rc_state' => 'required|exists:states,s_id',
                'rc_city' => 'required|string|max:50',
                'rc_zip' => 'required',
            ], [
                'rc_name.required' => 'Please enter your name',
                'rc_name.max' => 'Name should not be more than 30 characters',
                'rc_dg_id.required' => 'Please select job position',
                'rc_dg_id.exists' => 'Please select job position',
                'rc_email.required' => 'Please enter your email',
                'rc_email.email' => 'Please enter a valid email',
                'rc_email.unique' => 'Email already exists',
                'rc_mobile.required' => 'Please enter your mobile number',
                'rc_mobile.max' => 'Mobile number should not be more than 15 characters',
                'rc_mobile.regex' => 'Please enter a valid mobile number',
                'rc_gender.required' => 'Please select gender',
                'rc_gender.exists' => 'Please select gender',
                'rc_address.required' => 'Please enter your address',
                'rc_address.max' => 'Address should not be more than 255 characters',
                'rc_country.required' => 'Please select country',
                'rc_country.exists' => 'Please select country',
                'rc_state.required' => 'Please select state',
                'rc_state.exists' => 'Please select state',
                'rc_city.required' => 'Please enter your city',
                'rc_city.max' => 'City should not be more than 50 characters',
                'rc_zip.required' => 'Please enter your zip code',
                'rc_zip.max' => 'Zip code should not be more than 10 characters',
            ]);

            $hashedRsId = $request->id; // The MD5-hashed rs_id sent from the client
            $candidate = RecruitmentCandidate::whereRaw('MD5(rc_id) = ?', [$hashedRsId])->first();

            $profileUrl = NULL;
            if ($request->hasFile('profile_image')) {
                if(env('STORE_ON_S3')){//upload file to AWS S3 storage.
                    $bucket = 'fixhr-uploads';
                    $file = $request->profile_image;
                    $imageUniqueName = $file->getClientOriginalName();
                    $imagePath = 'RecruitmentCandidate/ProfileImage/'.time().$imageUniqueName;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                    if ($uploadResult['status']) {
                        $profileUrl = $uploadResult['ObjectURL'];
                    }

                }else{//upload file to the server directory
                    $profileUrl = CommonUtils::uploadFiles($request, 'profile_image', 'RecruitmentCandidate/ProfileImage', ['prefix' => 'Profile']);
                    $profileUrl = isset($profileUrl[0]) ? url($profileUrl[0]) : NULL;
                }
            }

            $resumeUrl = NULL;
            if ($request->hasFile('rc_resume')) {
                if(env('STORE_ON_S3')){//upload file to AWS S3 storage.
                    $bucket = 'fixhr-uploads';
                    $file = $request->rc_resume;
                    $imageUniqueName = $file->getClientOriginalName();
                    $imagePath = 'RecruitmentCandidate/Resume/'.time().$imageUniqueName;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                    if ($uploadResult['status']) {
                        $resumeUrl = $uploadResult['ObjectURL'];
                    }

                }else{//upload file to the server directory
                    $resumeUrl = CommonUtils::uploadFiles($request, 'rc_resume', 'RecruitmentCandidate/Resume/', ['prefix' =>'resume']);
                $resumeUrl = isset($resumeUrl[0]) ? url($resumeUrl[0]) : NULL;
                }
            }


            if ($candidate) {
                // Update existing candidate
                $candidate->update([
                    'rc_name' => $request->rc_name,
                    'rc_profile' => !empty($uploadedProfile) ? json_encode($uploadedProfile) :  $candidate->rc_profile,
                    'rc_portfolio' => $request->rc_portfolio,
                    'rc_email' => $request->rc_email,
                    'rc_mobile' => $request->rc_mobile,
                    'rc_dob' => $request->rc_dob,
                    'rc_gender' => $request->rc_gender,
                    'rc_recruitment_id' => $request->rc_recruitment_id,
                    'rc_dg_id' => $request->rc_dg_id,
                    'rc_address' => $request->rc_address,
                    'rc_source' => $request->rc_source,
                    'rc_country' => $request->rc_country,
                    'rc_state' => $request->rc_state,
                    'rc_city' => $request->rc_city,
                    'rc_zip' => $request->rc_zip,
                    'rc_referral' => $request->rc_referral,
                    'rc_resume' =>  !empty($uploadedResume) ? json_encode($uploadedResume) : $request->rc_resume_edit,
                ]);
            } else {
                // Create a new candidate
                RecruitmentCandidate::create([
                    'rc_name' => $request->rc_name,
                    'rc_profile' => $profileUrl,
                    'rc_portfolio' => $request->rc_portfolio,
                    'rc_email' => $request->rc_email,
                    'rc_mobile' => $request->rc_mobile,
                    'rc_dob' => $request->rc_dob,
                    'rc_gender' => $request->rc_gender,
                    'rc_recruitment_id' => $request->rc_recruitment_id,
                    'rc_dg_id' => $request->rc_dg_id,
                    'rc_address' => $request->rc_address,
                    'rc_source' => $request->rc_source,
                    'rc_country' => $request->rc_country,
                    'rc_state' => $request->rc_state,
                    'rc_city' => $request->rc_city,
                    'rc_zip' => $request->rc_zip,
                    'rc_referral' => $request->rc_referral,
                    'rc_resume' => $resumeUrl,
                ]);
            }

            $message = $request->id ? 'Updated' : 'Created';
            return response()->json([
                'status' => 'success',
                'message' => 'Candidate ' . $message . ' successfully!',
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-related errors
            return response()->json([
                'status' => 'error',
                'message' => 'Database error occurred: ' . $e->getMessage()
            ], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error occurred: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $encryptedId)
    {
        $breadcrumbs = CentralLogics::getBreadcrumbs(); // Generate breadcrumbs
        array_pop($breadcrumbs); // Remove the last element

        // Fetch the candidate along with its recruitment data in a single query
        $recruitmentCandidate = RecruitmentCandidate::with([
            'fh_recruitment',
            'fh_recruitment_stage',
            'fh_designation',
            'fh_gender',
            'fh_country',
            'fh_state',
            'fh_recruitment_interviewschedules'
        ])
            ->whereRaw('md5(rc_id) = ?', [$encryptedId])
            ->first(); // Directly fetch the record
        $candidate = $recruitmentCandidate->pluck('rc_name', 'rc_id')->toArray();
        // Check if candidate exists
        if (!$recruitmentCandidate) {
            abort(404, 'Candidate not found'); // Handle missing candidate
        }

        // Extract all unique interviewer IDs (handling null values)
        $interviewerIds = $recruitmentCandidate->fh_recruitment_interviewschedules
            ->whereNotNull('ris_interviewer') // Ignore null values
            ->pluck('ris_interviewer') // Get JSON string values
            ->flatMap(fn($ids) => json_decode($ids, true) ?? []) // Convert JSON to array
            ->unique()
            ->values()
            ->all();

        // Fetch employee names in one query
        $interviewers = Employee::whereIn('emp_id', $interviewerIds)
            ->pluck('emp_full_name', 'emp_id'); // ['3' => 'John Doe', '5' => 'Jane Smith']

        // Map employee names to each interview schedule
        $recruitmentCandidate->fh_recruitment_interviewschedules->each(function ($interview) use ($interviewers) {
            $interview->ris_interview_name = collect(json_decode($interview->ris_interviewer, true) ?? [])
                ->map(fn($id) => $interviewers[$id] ?? 'Unknown') // Assign names
                ->implode(', ');
        });


        if ($recruitmentCandidate) {
            // Get the associated recruitment data (assuming there is a relationship)
            $recruitment = $recruitmentCandidate->fh_recruitment;

            // Check if the recruitment data exists
            if ($recruitment) {
                // Decode the 'r_managers' JSON field and fetch the associated interviewers
                $interviewers = Employee::whereIn('emp_id', json_decode($recruitment->r_managers))
                    ->pluck('emp_full_name', 'emp_id')->toArray();

                // Debug output of recruitmentCandidate, key-value pair of rc_name and rc_id, recruitment, and interviewers
                return view('admin.setting.setting', compact('candidate', 'recruitment', 'interviewers', 'recruitmentCandidate','breadcrumbs'));
            } else {
                // Handle the case where the recruitment data is missing
                dd('Recruitment data not found for candidate.');
            }
        } else {
            // Handle the case where no candidate is found
            dd('Candidate not found.');
        }
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

    public function applicationFormShow($encryptedId)
    {
        $decryptedId = Crypt::decrypt($encryptedId);
        $gender = MasterTable::where("m_group", 'GENDER')->pluck('m_name', 'm_id')->toArray();
        $recruitment = Recruitment::find($decryptedId);
        // Check if recruitment exists
        if (!$recruitment) {
            return back()->with('error', 'Recruitment record not found.');
        }

        $currentDate = now()->toDateString(); // Get the current date (YYYY-MM-DD)

        // Check if the current date is within the start and end date range
        if ($currentDate < $recruitment->r_start_date) {
            return '<h1 style="text-align: center; display: flex; justify-content: center; align-items: center; height: 100vh;">Recruitment has not started yet.</h1>';
            return redirect()->back()->with('error', 'Recruitment has not started yet.');
        }

        if ($currentDate > $recruitment->r_end_date) {
            return '<h1 style="text-align: center; display: flex; justify-content: center; align-items: center; height: 100vh;">Recruitment has ended.</h1>';
            return redirect()->back()->with('error', 'Recruitment has ended.');
        }


        // Fetch designations
        $designations = !empty($recruitment->r_dg_id)
            ? Designation::whereIn("dg_id", json_decode($recruitment->r_dg_id))->pluck('dg_name', 'dg_id')->toArray()
            : Designation::pluck('dg_name', 'dg_id')->toArray();

        $country = Country::pluck('c_name', 'c_id')->toArray();

        return view('recruitment.application-form', compact('designations', 'gender', 'country', 'recruitment', 'decryptedId'));
    }


    public function applicationFormSubmit(Request $request)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,jpg,png|max:2048', // optional, must be an image
            'rc_name' => 'required|string|max:30',
            'rc_dg_id' => 'required|exists:designations,dg_id',  // at least one job position is selected
            'rc_email' => 'required|email|max:255|unique:recruitment_candidate,rc_email',  // email is unique
            'rc_mobile' => 'required|string|max:15|regex:/^[0-9]+$/', // only numbers
            'rc_gender' => 'required|exists:master_table,m_id',  // at least one gender is selected
            'rc_address' => 'required|string|max:255',
            'rc_country' => 'required|exists:countries,c_id',  // at least one country selected
            'rc_state' => 'required|exists:states,s_id',    // at least one state selected
            'rc_city' => 'required|string|max:50',
            'rc_resume' => 'required|mimes:pdf|max:10240',
            'rc_address' => 'required',
            'rc_zip' => 'required',
            // 'rc_zip' => 'required|string|max:10|regex:/^\d{5}(-\d{4})?$/', // valid zip code
        ], [
            'rc_name.required' => 'Please enter your name',
            'rc_name.max' => 'Name should not be more than 30 characters',
            'rc_dg_id.required' => 'Please select job position',
            'rc_dg_id.exists' => 'Please select job position',
            'rc_email.required' => 'Please enter your email',
            'rc_email.email' => 'Please enter a valid email',
            'rc_email.unique' => 'Email already exists',
            'rc_mobile.required' => 'Please enter your mobile number',
            'rc_mobile.max' => 'Mobile number should not be more than 15 characters',
            'rc_mobile.regex' => 'Please enter a valid mobile number',
            'rc_gender.required' => 'Please select gender',
            'rc_gender.exists' => 'Please select gender',
            'rc_address.required' => 'Please enter your address',
            'rc_address.max' => 'Address should not be more than 255 characters',
            'rc_country.required' => 'Please select country',
            'rc_country.exists' => 'Please select country',
            'rc_state.required' => 'Please select state',
            'rc_state.exists' => 'Please select state',
            'rc_city.required' => 'Please enter your city',
            'rc_city.max' => 'City should not be more than 50 characters',
            'rc_resume.required' => 'Please upload your resume',
            'rc_resume.mimes' => 'Resume must be in PDF format',
            'rc_resume.max' => 'Resume should not be more than 10MB',
            'rc_zip.required' => 'Please enter your zip code',
            'rc_zip.max' => 'Zip code should not be more than 10 characters',
            'rc_zip.regex' => 'Please enter a valid zip code',

        ]);

        $profileUrl = NULL;
        if ($request->hasFile('profile_image')) {
            if(env('STORE_ON_S3')){//upload file to AWS S3 storage.
                $bucket = 'fixhr-uploads';
                $file = $request->profile_image;
                $imageUniqueName = $file->getClientOriginalName();
                $imagePath = 'RecruitmentCandidate/ProfileImage/'.time().$imageUniqueName;
                $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                if ($uploadResult['status']) {
                    $profileUrl = $uploadResult['ObjectURL'];
                }
            }else{//upload file to the server directory
                $profileUrl = CommonUtils::uploadFiles($request, 'profile_image', 'RecruitmentCandidate/ProfileImage/', ['prefix' =>'Profile']);
                $profileUrl = isset($profileUrl[0]) ? url($profileUrl[0]) : NULL;
            }
        }

        $resumeUrl = NULL;
        if ($request->hasFile('rc_resume')) {
            if(env('STORE_ON_S3')){//upload file to AWS S3 storage.
                $bucket = 'fixhr-uploads';
                $file = $request->rc_resume;
                $imageUniqueName = $file->getClientOriginalName();
                $imagePath = 'RecruitmentCandidate/Resume/'.time().$imageUniqueName;
                $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                if ($uploadResult['status']) {
                    $resumeUrl = $uploadResult['ObjectURL'];
                }

            }else{//upload file to the server directory
                $resumeUrl = CommonUtils::uploadFiles($request, 'rc_resume', 'RecruitmentCandidate/Resume/', ['prefix' =>'resume']);
                $resumeUrl = isset($resumeUrl[0]) ? url($resumeUrl[0]) : NULL;
            }
        }

        $recruitment  = Recruitment::with('fh_recruitment_stages')->find($request->rc_recruitment);
        $stageId = $recruitment?->fh_recruitment_stages()?->value('rsg_id');
        // Prepare data to insert into the database
        $application = RecruitmentCandidate::create([
            'rc_b_id' => $recruitment->r_b_id,
            'rc_name' => $request->rc_name,
            'rc_dg_id' => $request->rc_dg_id ?? null, // Ensure valid data is passed
            'rc_email' => $request->rc_email,
            'rc_mobile' => $request->rc_mobile,
            'rc_gender' => $request->rc_gender ?? null,
            'rc_address' => $request->rc_address,
            'rc_country' => $request->rc_country ?? null,
            'rc_state' => $request->rc_state ?? null,
            'rc_city' => $request->rc_city,
            'rc_zip' => $request->rc_zip,
            'rc_resume' => $resumeUrl,
            'rc_offer_letter_status' => 0,
            'rc_recruitment_id' => $request->rc_recruitment,
            'rc_stage_id' => $stageId,
            'rc_profile' => $profileUrl,

            'rc_address_local' => $request->rc_address_local,
            'rc_terms' => $request->rc_terms,

        ]);

        return response()->json(['status' => true, 'message' => 'Application submitted successfully']);
    }
}
