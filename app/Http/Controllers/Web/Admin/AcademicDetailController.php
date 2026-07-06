<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\UniformDetailsExport;
use App\Http\Controllers\Controller;
use App\Imports\UniformDetailsImport;
use App\Models\AcademicDetail;
use App\Models\Board;
use App\Models\CourseDegree;
use App\Models\Employee;
use App\Models\EmpQualification;
use App\Models\MasterTable;
use App\Models\Qualification;
use App\Models\QualificationMaster;
use App\Models\Specialization;
use App\Models\Stream;
use App\Models\UniformDetail;
use App\Models\UniformIssue;
use App\Models\UnivercityList;
use App\Models\FamilyDetail;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class AcademicDetailController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $emp_qulification = EmpQualification::get();
        $emp_course = CourseDegree::get();
        // dd($emp_course);

        $emp_qualifactionFilter = $request->emp_qualifactionFilter;
        $emp_courseFilter = $request->emp_courseFilter;

        // dd($emp_qualifactionFilter, $emp_courseFilter);


        $employee_list = Employee::where('emp_b_id', $business_id)
            ->where('emp_role_id', '!=', 1)
            ->where('emp_status', '!=', 72)
            ->select('emp_full_name', 'emp_id', 'emp_code')
            ->get();

        $employees = Employee::where('emp_b_id', $business_id)
            ->where('emp_status', '!=', 72)
            ->select('emp_full_name', 'emp_id', 'emp_code')
            ->get();

        if (!$this->user) {
            abort(404);
        }

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ad_b_id', $business_id],
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'ad_id',
                        'ad_emp_id',
                        'ad_b_id',
                        'ad_qua_id',
                        'ad_course_degree',
                        'ad_specialization',
                        'ad_university_board',
                        'ad_institute_name',
                        'ad_year_of_passing',
                        'ad_marks_type',
                        'ad_marks_obtained',
                        'ad_document_upload'
                    ],
                    'relation' => []
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['ad_year_of_passing', 'desc'],
                    'relation' => []
                ],

                [
                    'method' => 'whereRaw',
                    'args' => [
                        'ad_qua_id = (
                                SELECT MAX(fad2.ad_qua_id)
                                FROM fh_academic_details as fad2
                                WHERE fad2.ad_emp_id = fh_academic_details.ad_emp_id
                            )'
                    ],
                    'relation' => []
                ]



            ];

            // Filter conditions
            if ($emp_qualifactionFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['ad_qua_id', $emp_qualifactionFilter]];
            }

            if ($emp_courseFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['ad_course_degree', $emp_courseFilter]];
            }


            $searchColumns = [
                'ad_course_degree',
                'ad_specialization',
                'ad_university_board',
                'ad_institute_name',
                'ad_year_of_passing',
                'ad_marks_obtained'
            ];

            $searchRelationships = [
                'employee' => ['emp_full_name', 'emp_code'],
                'board' => ['name'],
                'qualification' => ['name'],
                'courseDegree' => ['name'],
                'specialization' => ['name']
            ];

            $datatableHelper = new DynamicModelDataTableHelper(
                new AcademicDetail(),
                $dynamicConditions,
                $searchColumns,
                $searchRelationships
            );

            $list = $datatableHelper->getServerSideDataTable();

            $rowData = [];
            foreach ($list as $index => $val) {

                $row = [
                    $index + 1,

                    '<a href="javascript:void(0);" class="view-academic-btn" data-id="' . $val->ad_id . '">
                        ' . ($val->employee->emp_full_name ?? '-') . '
                    </a>',

                    $val->employee->emp_code ?? '-',
                    $val->qualification->name ?? '-',
                    $val->courseDegree->name ?? '-',
                    $val->specialization->name ?? $val->ad_specialization,
                    $val->ad_year_of_passing ?? '-',
                    $val->ad_document_upload
                        ? '<a href="' . asset($val->ad_document_upload) .  '" target="_blank">View</a>'
                        : '-',
                    '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">

                               <li>
                                <a href="' . route('academic.edit', $val->ad_id)  . '" class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                     <i class="feather feather-edit"></i> Edit
                                </a>
                                </li>

                                <li>
                                    <form method="POST" action="' . route('academic.destroy', $val->ad_id) . '">
                                        ' . csrf_field() . method_field('DELETE') . '
                                        <button type="submit" class="dropdown-item text-danger fw-semibold delete-academic">
                                            <i class="feather feather-trash-2"></i> Delete
                                        </button>
                                    </form>
                                </li>

                                      <li>
                                        <a href="javascript:void(0);" class="view-academic-btn" data-id="' . $val->ad_id . '">
                                           <i class="feather feather-eye"></i> View
                                        </a>
                                </li>

                            </ul>
                        </div>
                    </div>'
                ];

                $rowData[] = $row;
            }

            return response()->json([
                "draw" => $request->input('draw'),
                "recordsTotal" => count($list),
                "recordsFiltered" => $datatableHelper->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ]);
        }

        // Table Headers
        $columns = [
            'S. No.',
            'Emp. Code',
            'Emp. Name',
            'Qualification',
            'Course/Degree',
            'Specialization',
            'Year of Passing',
            'Document',
            'Action',
        ];

        $academicDetails = AcademicDetail::select(
            'ad_id',
            'ad_emp_id',
            'ad_b_id',
            'ad_qua_id',
            'ad_course_degree',
            'ad_specialization',
            'ad_university_board',
            'ad_institute_name',
            'ad_year_of_passing',
            'ad_marks_type',
            'ad_marks_obtained',
            'ad_document_upload'
        )->orderBy('ad_year_of_passing', 'desc')->first();

        return view('admin.employees.academic_details.index', compact(
            'academicDetails',
            'columns',
            'employee_list',
            'employees',
            'emp_course',
            'emp_qulification',

        ));
    }

    public function create(Request $request)
    {
        $qua_master  =  EmpQualification::get();
        $UnivercityList  =  UnivercityList::get();

        // dd($UnivercityList);
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $query = Employee::where('emp_b_id', $business_id)
            ->where('emp_status', '!=', 72)
            ->where('emp_role_id', '!=', 1)
            ->select('emp_id', 'emp_full_name', 'emp_code');
        if ($request->filled('emp_id')) {
            try {
                $emp_id = Crypt::decrypt($request->emp_id);
                $query->where('emp_id', $emp_id);
            } catch (DecryptException $e) {
                abort(404, 'Unauthorized or invalid access.');
            }
        }


        $employees = $query->get();

        return view('admin.employees.academic_details.create', compact('employees', 'qua_master', 'UnivercityList'));
    }

    public function store(Request $request)
    {
        // dd($request);
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $request->validate([
            'ud_issued_by' => 'required|exists:employees,emp_id',
            'qualification.*'     => 'required|string|max:255',
            'course_name.*'       => 'required|string|max:255',
            'specialization.*'    => 'nullable|string|max:255',
            'university_board.*'  => 'required|string|max:255',
            'institute_name.*'    => 'required|string|max:255',
            'year_of_passing.*'   => 'required|integer|min:1900|max:2099',
            'marks_type.*'        => 'required|string|in:percentage,cgpa,grade,marks',
            'marks_obtained.*'    => 'required|string|max:255',
            'document_upload.*'   => 'nullable|file|mimes:pdf|max:2048',
        ]);

        foreach ($request->qualification as $index => $qualification) {
            $academic = new AcademicDetail();
            $academic->ad_emp_id      = $request->ud_issued_by;
            $academic->ad_b_id      = $business_id;
            $academic->ad_qua_id      = $request->qualification[$index] ?? null;
            $academic->ad_course_degree      = $request->course_name[$index] ?? null;
            $academic->ad_specialization   = $request->specialization[$index] ?? null;
            $academic->ad_university_board = $request->university_board[$index] ?? null;
            $academic->ad_institute_name   = $request->institute_name[$index] ?? null;
            $academic->ad_year_of_passing  = $request->year_of_passing[$index] ?? null;
            $academic->ad_marks_type       = $request->marks_type[$index] ?? null;
            $academic->ad_marks_obtained   = $request->marks_obtained[$index] ?? null;
            $academic->ad_status   = $request->ad_status[$index] ?? null;

            if (isset($request->image[$index])) {
                $file = $request->image[$index];
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('assets/academic');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $academic->ad_document_upload = 'assets/academic/' . $filename;
            }


            $academic->save();
        }

        return redirect()->route('academic.index')->with('success', 'Academic details added successfully!');
    }

    public function destroy($id)
    {
        $academicDetail = AcademicDetail::findOrFail($id);
        $ad_emp_id = $academicDetail->ad_emp_id;
        $academicDetails = AcademicDetail::where('ad_emp_id', $ad_emp_id)->get();
        foreach ($academicDetails as $detail) {
            if ($detail->ad_uplode_photo && file_exists(public_path($detail->ad_uplode_photo))) {
                unlink(public_path($detail->ad_uplode_photo));
            }
            $detail->delete();
        }

        return redirect()->back()->with('success', 'All academic details for the employee deleted successfully.');
    }

    public function edit($id)
    {

        $qua_master  =  EmpQualification::get();

        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $academicDetail = AcademicDetail::findOrFail($id);
        $ad_emp_id = $academicDetail->ad_emp_id;

        $academicDetails = AcademicDetail::where('ad_emp_id', $ad_emp_id)->get();
        $emp_details = AcademicDetail::where('ad_emp_id', $ad_emp_id)->first();

        // dd($academicDetails);

        return view('admin.employees.academic_details.update', compact(
            'academicDetails',
            'qua_master',
            'ad_emp_id',
            'emp_details'

        ));
    }


    public function update(Request $request, $emp_id)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $request->validate([
            'ud_issued_by' => 'required|exists:employees,emp_id',
            'qualification.*'     => 'required|string|max:255',
            'course_name.*'       => 'required|string|max:255',
            'specialization.*'    => 'nullable|string|max:255',
            'university_board.*'  => 'required|string|max:255',
            'institute_name.*'    => 'required|string|max:255',
            'year_of_passing.*'   => 'required|integer|min:1900|max:2099',
            'marks_type.*'        => 'required|string|in:percentage,cgpa,grade,marks',
            'marks_obtained.*'    => 'required|string|max:255',
            'document_upload.*'   => 'nullable|file|mimes:pdf|max:2048',
        ]);

        // Delete old academic details for this employee
        AcademicDetail::where('ad_emp_id', $emp_id)->delete();

        // Add updated records
        foreach ($request->qualification as $index => $qualification) {
            $academic = new AcademicDetail();
            $academic->ad_emp_id      = $request->ud_issued_by;
            $academic->ad_b_id        = $business_id;
            $academic->ad_qua_id      = $request->qualification[$index] ?? null;
            $academic->ad_course_degree = $request->course_name[$index] ?? null;
            $academic->ad_specialization = $request->specialization[$index] ?? null;
            $academic->ad_university_board = $request->university_board[$index] ?? null;
            $academic->ad_institute_name = $request->institute_name[$index] ?? null;
            $academic->ad_year_of_passing = $request->year_of_passing[$index] ?? null;
            $academic->ad_marks_type = $request->marks_type[$index] ?? null;
            $academic->ad_marks_obtained = $request->marks_obtained[$index] ?? null;

            if (isset($request->image[$index])) {
                $file = $request->image[$index];

                // Delete old file if path exists
                $oldPath = $request->existing_image[$index] ?? null;
                if (!empty($oldPath) && file_exists(public_path($oldPath))) {
                    unlink(public_path($oldPath));
                }

                // Save new file
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('assets/academic');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $academic->ad_document_upload = 'assets/academic/' . $filename;
            } else {
                // Keep existing image path if no new file uploaded
                $academic->ad_document_upload = $request->existing_image[$index] ?? null;
            }



            $academic->save();
        }

        return redirect()->route('academic.index')->with('success', 'Academic details updated successfully!');
    }


    public function getCourses(Request $request)
    {
        $qualification = $request->qualification;
        $data = CourseDegree::where('qualification_id', $qualification)
            ->pluck('name', 'id');
        return response()->json($data);
    }

    public function getspecialization(Request $request)
    {
        $specialization = $request->qualification;

        // Return only m_id => m_name
        $data = Specialization::where('course_degree_id', $specialization)
            ->pluck('name', 'id');
        return response()->json($data);
    }

    public function getbords(Request $request)
    {
        $specialization = $request->qualification;

        // Return only m_id => m_name
        $data = Board::where('course_degree_id', $specialization)
            ->pluck('name', 'id');
        return response()->json($data);
    }

    public function getAcademic($id)
    {
        $academicDetail = AcademicDetail::findOrFail($id);

        if ($academicDetail && $academicDetail->employee) {
            $employeeName = $academicDetail->employee->emp_full_name . ' ' . '(' . $academicDetail->employee->emp_code . ')';
        } else {
            dd('Employee or UniformIssue not found');
        }

        // dd($employeeName);

        $academicRecords = AcademicDetail::with(['qualification', 'courseDegree', 'specialization'])
            ->where('ad_emp_id', $academicDetail->ad_emp_id)
            ->get()
            ->map(function ($item) {
                return [
                    'qualification_name' => $item->qualification->name,
                    'course_name' => $item->courseDegree->name,
                    'specialization' => is_numeric($item->ad_specialization)
                        ? optional($item->specialization)->name
                        : $item->ad_specialization,

                    'university_board' => is_numeric($item->ad_university_board)
                        ? optional($item->board)->name // if relation exists
                        : $item->ad_university_board,

                    'institute_name' => $item->ad_institute_name,
                    'year_of_passing' => $item->ad_year_of_passing,
                    'marks_type' => ucfirst($item->ad_marks_type),
                    'marks_obtained' => $item->ad_marks_obtained,
                    'document_path' => $item->ad_document_upload,
                ];
            });

        return response()->json([
            'academicRecords' => $academicRecords,
            'employee' => $employeeName,
        ]);

    }

    public function export()
    {
        return Excel::download(new UniformDetailsExport, 'uniform_details.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $uniformImport = new UniformDetailsImport(Auth::user());

        try {
            Excel::import($uniformImport, $file);
            $errorMessages = $uniformImport->getErrorMessages();
            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return back()->with('error', 'Some rows failed to import. Please check the error file.');
            }

            return back()->with('success', 'Uniform details imported successfully.');
        } catch (\Exception $e) {
            // dd("Hello2");

            \Log::error('Uniform Import Failed: ' . $e->getMessage());
            return back()->with('error', 'Import failed. Please check your file and try again.');
        }
    }

    public function emp_details_index()
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $uniformcount = UniformIssue::where('ui_b_id', $business_id)->count();
        // dd($uniformcount);

        $academiccount = AcademicDetail::where('ad_b_id', $business_id)
            ->distinct('ad_emp_id')
            ->count('ad_emp_id');

        $familyDetails = FamilyDetail::where('fd_b_id', $business_id)
            ->distinct('fd_emp_id')
            ->count('fd_emp_id');


        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $emp_qulification = EmpQualification::get();
        $emp_course = CourseDegree::get();

        $employee_list = Employee::where('emp_b_id', $business_id)
            ->where('emp_role_id', '!=', 1)
            ->where('emp_status', '!=', 72)
            ->select('emp_full_name', 'emp_id', 'emp_code')
            ->get();


        return view('admin.employees.employee_details_folder', compact('uniformcount', 'academiccount', 'employee_list', 'familyDetails'));
    }
}
