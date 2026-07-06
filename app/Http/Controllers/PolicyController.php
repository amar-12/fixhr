<?php

namespace App\Http\Controllers;

use App\Exports\Policy\HolidayPolicyReport;
use App\Exports\Policy\WeeklyOffPolicyReport;
use App\Helpers\CentralLogics;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Models\BusinessPolicyDocument;
use App\Models\BusinessPolicyFolder;
use App\Models\FhBusinessPolicyDocument;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Auth;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PolicyController extends Controller
{
    protected $user;
    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request, $id = null)
    {

        $user_id = $request->route('id');

        if (!$this->user) {
            abort(404);
        }

        if ($request->ajax()) {


            $id = $request->get('id') ?? $id;

            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['bpd_b_id', $this->user->emp_b_id],
                ],

                [
                    'method' => 'where',
                    'args' => ['bpd_status', 1],
                ],


                [
                    'method' => 'where',
                    'args' => ['bpd_folder_id', $id],
                ],


                [
                    'method' => 'select',
                    'args' => [
                        'bpd_id',
                        'bpd_folder_name',
                        'bpd_version',
                        'bpd_file_name',
                        'bpd_with_effect_from',
                        'bpd_file_path',
                        'bpd_status',
                        'created_at'
                    ],
                    'relation' => []
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['bpd_with_effect_from', 'desc'],
                    'relation' => []
                ]
            ];

            $searchColumns = ['bpd_file_name', 'bpd_with_effect_from', 'bpd_status'];

            $datatableHelper = new DynamicModelDataTableHelper(
                eloquentModel: new BusinessPolicyDocument(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            );

            $list = $datatableHelper->getServerSideDataTable();
            $rowData = [];
            foreach ($list as $index => $val) {
                $row = [];
                $row[] = $index + 1;
                $row[] = $val->bpd_file_name
                    ? '<a href="' . asset($val->bpd_file_path) . '" target="_blank">' . e($val->bpd_file_name) . '</a>'
                    : '';

                $row[] = e($val->bpd_version);
                $row[] = $val->bpd_with_effect_from
                    ? Carbon::parse($val->bpd_with_effect_from)->format('d-M-Y')
                    : '';

                $row[] = $val->bpd_status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';

                // Actions dropdown

                $downloadUrl = route('document.download', $val->bpd_id);

                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-document"
                                    type="button"
                                    data-id="' . $val->bpd_id . '"
                                    data-folder="' . e($val->bpd_folder_name) . '"
                                    data-version="' . e($val->bpd_version) . '"
                                    data-file_name="' . e($val->bpd_file_name) . '"
                                    data-with_effect_from="' . e($val->bpd_with_effect_from) . '"
                                    data-file_path="' . e($val->bpd_file_path) . '"
                                    data-status="' . e($val->bpd_status) . '">
                                    <i class="feather feather-edit"></i> Edit
                                </button>
                            </li>
                            <li>
                                <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2" href="' . asset($val->bpd_file_path) . '" target="_blank">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>

                         <li>
                          <a href="' . $downloadUrl . '" class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                            <i class="fa fa-download"></i> Download
                           </a>
                         </li>
                                        
                        </ul>
                    </div>
                </div>';

                // Checkbox column
                $row[] = '<div class="d-flex">
                        <label class="custom-control custom-checkbox-md p-0 ms-2">
                          <input type="checkbox" class="custom-control-input-success select-checkbox testClass"
                                name="bulk_employee_ids[]" value="' . $val->bpd_id . '"
                              onclick="selectCheckboxUpdate(this)">
                            <span class="custom-control-label-md success"></span>
                        </label>
                    </div>';


                $rowData[] = $row;
            }

            return response()->json([
                "draw" => $request->input('draw'),
                "recordsTotal" => count($list),
                "recordsFiltered" => $datatableHelper->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ]);
        }

        // Non-AJAX page load
        $columns = [
            'S. No.',
            'Regulatory Document',
            'Document Version',
            'W.E.F.',
            'Status',
            'Action',
            '',
        ];

        $documents = BusinessPolicyDocument::select(
            'bpd_id',
            'bpd_folder_name',
            'bpd_file_name',
            'bpd_with_effect_from',
            'bpd_file_path',
            'bpd_status'
        )
            ->where('bpd_b_id', $this->user->emp_b_id)
            ->orderBy('bpd_with_effect_from', 'desc')
            ->get();

        return view('admin.setting.account.business_policy', compact('documents', 'columns', 'user_id'));
    }

    public function store(Request $request)
    {

        // dd($request->all());

        $request->validate([
            'bpd_file_name' => 'required|string|max:255',
            'bpd_with_effect_from' => 'required|date',
            'bpd_status' => 'required|in:0,1',
            'bpd_file_path' => $request->bpd_id ? 'nullable|file|mimes:pdf,doc,docx' : 'required|file|mimes:pdf,doc,docx',
        ]);

        $user = Auth::user();
        $businessId = $user->emp_b_id;



        $document = $request->bpd_id ? BusinessPolicyDocument::findOrFail($request->bpd_id) : new BusinessPolicyDocument();



        if ($request->hasFile('bpd_file_path')) {
            $file = $request->file('bpd_file_path');
            $extension = $file->getClientOriginalExtension();

            // Generate a unique filename
            $filename = uniqid('bpd_', true) . '.' . $extension;

            $destination = public_path('upload');

            // Ensure directory exists
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            // Move file to destination with unique filename
            $file->move($destination, $filename);

            // Save the relative path in the database
            $document->bpd_file_path = 'upload/' . $filename;
        }





        $document->bpd_file_name = $request->bpd_file_name;
        $document->bpd_folder_id = $request->user_id;
        $document->bpd_folder_name = "N/A";
        $document->bpd_b_id = $businessId;
        $document->bpd_version = $request->bpd_version;
        $document->bpd_with_effect_from = Carbon::parse($request->bpd_with_effect_from)->format('Y-m-d');
        $document->bpd_status = $request->bpd_status;

        $document->save();

        return response()->json([
            'success' => $request->bpd_id ? 'Document updated successfully.' : 'Document created successfully.',
        ]);
    }

    public function destroy($id)
    {
        $document = BusinessPolicyDocument::findOrFail($id);

        if ($document->bpd_file_path && file_exists(public_path($document->bpd_file_path))) {
            unlink(public_path($document->bpd_file_path));
        }

        $document->delete();

        return response()->json([
            'success' => 'Document deleted successfully.',
        ]);
    }

    public function folder_index()
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $businessfolders = BusinessPolicyFolder::where('pbf_b_id', $business_id)->get();
        $folderIds = $businessfolders->pluck('bpf_id')->toArray();
        $businesdocument = BusinessPolicyDocument::whereIn('bpd_folder_id', $folderIds)
            // ->orderBy('bpd_version', 'desc')
            ->orderBy('created_at', 'desc')
            ->where('bpd_status', 1)
            ->get();




        return view('admin.setting.account.business_policy_folder', compact('businessfolders', 'businesdocument'));
    }

    public function folder_store(Request $request)
    {
        try {
            $user = Auth::user();
            $business_id = $user->emp_b_id;

            $validatedData = $request->validate([
                'bpf_name' => 'required|string|max:255|unique:business_policy_folder,bpf_name,' . $request->bpf_id . ',bpf_id',
            ]);

            if ($request->filled('bpf_id')) {
                $folder = BusinessPolicyFolder::findOrFail($request->bpf_id);
                $folder->bpf_name = $validatedData['bpf_name'];
                $folder->pbf_b_id = $business_id;
                $folder->save();
                $message = 'Folder updated successfully.';
            } else {
                BusinessPolicyFolder::create([
                    'bpf_name' => $validatedData['bpf_name'],
                    'pbf_b_id' => $business_id
                ]);
                $message = 'Folder created successfully.';
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unexpected error occurred.'], 500);
        }
    }

    public function folder_destroy($id)
    {
        $folder = BusinessPolicyFolder::findOrFail($id);
        $folder->delete();

        return
            response()->json(['success' => 'Folder deleted successfully.']);
    }


    public function bulkDownload(Request $request)
    {
        $employeeIds = $request->input('emp_ids');

        $documents = BusinessPolicyDocument::whereIn('bpd_id', $employeeIds)->get();

        if ($documents->isEmpty()) {
            return response()->json(['message' => 'No documents found.'], 404);
        }

        // Temporary ZIP file path
        $zipFileName = 'business_policy_documents_' . time() . '.zip';
        $zipFilePath = public_path('upload/temp/' . $zipFileName);

        // Ensure the temp directory exists
        if (!file_exists(public_path('upload/temp'))) {
            mkdir(public_path('upload/temp'), 0777, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($documents as $doc) {
                $filePath = public_path($doc->bpd_file_path); // FIXED

                if (file_exists($filePath)) {
                    // You can name it anything inside the ZIP (optional: original name or ID)
                    $zip->addFile($filePath, basename($filePath));
                }
            }
            $zip->close();
        } else {
            return response()->json(['message' => 'Could not create zip file.'], 500);
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function downloadPDF($id)
    {
        $document = BusinessPolicyDocument::findOrFail($id);
        $filePath = public_path($document->bpd_file_path);

        if (file_exists($filePath)) {
            return response()->download($filePath, 'document_' . $id . '.pdf');
        } else {
            abort(404, 'File not found');
        }
    }


   public function policyReport($slug)
{
    if (!in_array($slug, ['holiday-policy', 'week-off-policy'])) {
        abort(404);
    }

    // ---------------- HOLIDAY REPORT (UNCHANGED) ----------------
    if ($slug === 'holiday-policy') {
        try {
            $businessId = Auth::user()->emp_b_id;

            // Query all holidays for the business ID
            $query = PolicyHolidayList::with([
                'fh_master_table'
            ])
                ->where('phl_b_id', $businessId)
                ->orderBy('phl_start_date', 'asc');

            $records = $query->get();

            // Process records to calculate total days for each holiday
            $records->each(function ($record) {
                $start = Carbon::parse($record->phl_start_date)->startOfDay();
                $end = $record->phl_end_date ? Carbon::parse($record->phl_end_date)->endOfDay() : $start;

                // Calculate total days (inclusive of start and end date)
                if ($start->equalTo($end)) {
                    $record->total_days = 1; // Single-day holiday
                } else {
                    $record->total_days = floor($start->diffInDays($end) + 1); // Multi-day holiday, inclusive, rounded down
                }
            });

            // Check if records are empty
            if ($records->isEmpty()) {
                throw new \Exception('No holidays found for the business.');
            }

            $businessName = Business::find($businessId)->b_name ?? 'N/A';
            $fileName = 'HolidayReport_' . now()->format('Y-m-d') . '.xlsx';
            return Excel::download(
                new HolidayPolicyReport($businessName, $records, $slug),
                $fileName
            );
        } catch (\Exception $e) {
            // Handle errors (e.g., database issues or empty results)
            $this->addError('general', 'Failed to generate report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    // ---------------- WEEKLY-OFF (controller-only logic; Business ID only) ----------------
    if ($slug === 'week-off-policy') {
        // Get business info here (keeps holiday block 100% unchanged)
        $businessId   = Auth::user()->emp_b_id;
        $businessName = Business::find($businessId)->b_name ?? 'N/A';

        $year = now()->year; // generate for the current year

        // 1) Load the business weekly-off policy
        $policy = PolicyWeekOff::where('pwo_b_id', $businessId)->first(); // <-- adjust model/column names if needed
        if (!$policy) {
            $this->addError('general', 'No weekly-off policy configured for this business.');
            return redirect()->back();
        }

        // Expect tokens like ["1st Saturday","3rd Sunday"] OR ["Saturday","Sunday"] (meaning ALL occurrences)
        $recurrence = $policy->pwo_recurrence_day_ids
            ? json_decode($policy->pwo_recurrence_day_ids, true)
            : [];

        // Prefer model's getWeek(); else normalize here
        $weekDays = method_exists($policy, 'getWeek')
            ? $policy->getWeek($recurrence)   // ['Saturday'=>['1st','3rd'], 'Sunday'=>['2nd']]
            : $this->normalizeWeekDaysAllowAll($recurrence);

        // 2) Build the full year's dates month by month
        $dates = [];
        for ($m = 1; $m <= 12; $m++) {
            $occ = $this->occurrencesOfDaysInMonth($year, $m); // weekday => ['Y-m-d', ...]
            $dates = array_merge($dates, $this->pickNthOrAllWeekdays($weekDays, $occ));
        }

        // Unique + sorted
        $dates = array_values(array_unique($dates));
        sort($dates);

        if (empty($dates)) {
            $this->addError('general', "No weekly-off dates found for $year.");
            return redirect()->back();
        }

        // Prepare export rows: date + day
        $records = collect($dates)->map(function ($d) {
            $c = \Carbon\Carbon::parse($d);
            return (object)[
                'date'     => $c->toDateString(),
                'day_name' => $c->format('l'),
            ];
        });

        // dd($records->toArray());

        $fileName = "WeeklyOffReport_{$year}_" . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new WeeklyOffPolicyReport($businessName, $records, $slug), $fileName);
    }

    // Fallback (shouldn't hit)
    $this->addError('general', 'Invalid report type.');
    return redirect()->back();
}

/* ====================== INTERNAL (CONTROLLER-ONLY) METHODS ====================== */

/**
 * Map weekday => ordered dates for a given month.
 * Example:
 * [
 *   'Saturday' => ['2025-08-02','2025-08-09','2025-08-16','2025-08-23','2025-08-30'],
 *   'Sunday'   => ['2025-08-03','2025-08-10','2025-08-17','2025-08-24','2025-08-31'],
 * ]
 */
private function occurrencesOfDaysInMonth(int $year, int $month): array
{
    $first = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
    $last  = $first->copy()->endOfMonth();

    $map = [
        'Monday' => [], 'Tuesday' => [], 'Wednesday' => [],
        'Thursday' => [], 'Friday' => [], 'Saturday' => [], 'Sunday' => [],
    ];

    for ($d = $first->copy(); $d->lte($last); $d->addDay()) {
        $map[$d->format('l')][] = $d->toDateString();
    }
    return $map;
}

/**
 * Accepts tokens like ["1st Saturday","3rd Sunday"] OR ["Saturday","Sunday"] (meaning ALL).
 * Returns ['Saturday'=>['1st','3rd']] or ['Saturday'=>[]] (empty array = ALL occurrences).
 */
private function normalizeWeekDaysAllowAll(array $recurrence): array
{
    $map = [];
    foreach ($recurrence as $token) {
        $token = trim($token);

        // "1st Saturday" style
        if (preg_match('/^(1st|2nd|3rd|4th|5th)\s+(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)$/i', $token, $m)) {
            $nth = $m[1];
            $day = ucfirst(strtolower($m[2]));
            $map[$day] = $map[$day] ?? [];
            $map[$day][] = $nth;
            continue;
        }

        // "Saturday" style => ALL occurrences
        if (preg_match('/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)$/i', $token, $m)) {
            $day = ucfirst(strtolower($m[1]));
            $map[$day] = $map[$day] ?? []; // empty array means ALL
        }
    }
    return $map;
}

/**
 * If nth list is empty => ALL occurrences for that weekday.
 * Else pick the nth entries (1..5) from the monthly occurrences map.
 */
private function pickNthOrAllWeekdays(array $weekDays, array $occurrences): array
{
    $out = [];
    foreach ($occurrences as $weekday => $dates) {
        if (!array_key_exists($weekday, $weekDays)) continue;

        $nths = $weekDays[$weekday];

        // ALL occurrences for that weekday
        if (empty($nths)) {
            $out = array_merge($out, $dates);
            continue;
        }

        // Specific nths
        foreach ($nths as $nthToken) {
            $nth = (int) filter_var($nthToken, FILTER_SANITIZE_NUMBER_INT);
            if ($nth > 0 && isset($dates[$nth - 1])) {
                $out[] = $dates[$nth - 1];
            }
        }
    }
    return $out;
}

}
