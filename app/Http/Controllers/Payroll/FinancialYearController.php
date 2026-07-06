<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\FinancialYear;
use App\Models\SalaryPolicySalary;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialYearController extends Controller
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
                        'args' => ['fy_b_id', $businessId]
                    ],
                    [
                        'method' => 'select',
                        'args' => ['fy_id', 'fy_year', 'fy_start_date', 'fy_end_date', 'fy_is_current'],
                        'relation' => []
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['fy_start_date', 'desc'],
                        'relation' => []
                    ]
                ];

                $searchColumns = ['fy_year', 'fy_start_date', 'fy_end_date', 'fy_is_current','fy_b_id'];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new FinancialYear(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->fy_year;
                    $row[] = Carbon::parse($val->fy_start_date)->format('d-M-Y');
                    $row[] = Carbon::parse($val->fy_end_date)->format('d-M-Y');
                    $row[] = $val->fy_is_current ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                    $row[] = '
                            <div class="btn-list ms-3">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
                                    </button>
                                    <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                        <li>
                                            <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-financial-year"
                                                type="button"
                                                data-id="' . $val->fy_id . '"
                                                data-year="' . $val->fy_year . '"
                                                data-start_date="' . $val->fy_start_date . '"
                                                data-end_date="' . $val->fy_end_date . '"
                                                data-is_current="' . $val->fy_is_current . '">
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
                        eloquentModel: new FinancialYear(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return json_encode($output);
            }

            $columns = [
                'S. No.',
                'Financial Year',
                'Start Date',
                'End Date',
                'Current Status',
                'Action',
            ];

            $financialYears = FinancialYear::where('fy_b_id', $businessId)->select('fy_id', 'fy_year', 'fy_start_date', 'fy_end_date', 'fy_is_current')
                ->orderBy('fy_start_date', 'desc')
                ->get();

            return view('admin.payroll.financial-year', compact('financialYears', 'columns'));
        } else {
            abort(404);
        }
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $fyId = $request->fy_id;

        // Get current FY year based on current date
        $currentDate = now();
        $currentStartYear = $currentDate->month >= 4 ? $currentDate->year : $currentDate->year - 1;
        $currentEndYear = $currentStartYear + 1;
        $currentFyYear = $currentStartYear . '-' . $currentEndYear;

        // Validation: Only allow marking current FY as current
        if ($request->fy_is_current == 1 && $request->fy_year !== $currentFyYear) {
            return response()->json([
                'errors' => ['fy_year' => ['Only the current financial year can be marked as current.']]
            ], 422);
        }

        // Check for duplicate fy_year + business
        $duplicateQuery = FinancialYear::where('fy_year', $request->fy_year)
            ->where('fy_b_id', $businessId);

        if ($fyId) {
            $duplicateQuery->where('fy_id', '!=', $fyId);
        }

        if ($duplicateQuery->exists()) {
            return response()->json([
                'errors' => ['fy_year' => ['This financial year already exists.']]
            ], 422);
        }

        // Ensure only one can be current
        if ($request->fy_is_current == 1) {
            FinancialYear::where('fy_b_id', $businessId)
                ->where('fy_is_current', 1)
                ->update(['fy_is_current' => 0]);
        }

        // Validate dates and structure
        $request->validate([
            'fy_year' => ['required', 'regex:/^\d{4}-\d{4}$/', function ($attribute, $value, $fail) {
                $years = explode('-', $value);
                if ((int)$years[1] - (int)$years[0] !== 1) {
                    $fail('The financial year range must be exactly one year.');
                }
            }],
            'fy_start_date' => ['required', 'date', function ($attribute, $value, $fail) use ($request) {
                $expectedStart = $request->fy_year ? explode('-', $request->fy_year)[0] . '-04-01' : null;
                if ($value !== $expectedStart) {
                    $fail("Start date must be {$expectedStart}.");
                }
            }],
            'fy_end_date' => ['required', 'date', function ($attribute, $value, $fail) use ($request) {
                $expectedEnd = $request->fy_year ? explode('-', $request->fy_year)[1] . '-03-31' : null;
                if ($value !== $expectedEnd) {
                    $fail("End date must be {$expectedEnd}.");
                }
            }],
        ]);

        $data = [
            'fy_year' => $request->fy_year,
            'fy_b_id' => $businessId,
            'fy_start_date' => $request->fy_start_date,
            'fy_end_date' => $request->fy_end_date,
            'fy_is_current' => $request->fy_is_current ? 1 : 0,
        ];

        if ($fyId) {
            $financialYear = FinancialYear::find($fyId);
            if (!$financialYear) {
                return response()->json(['error' => 'Financial Year not found.'], 404);
            }

            $isCurrentlyCurrent = $financialYear->fy_is_current == 1;
            $isTryingToUnset = $isCurrentlyCurrent && $request->fy_is_current != 1;

            $otherCurrentExists = FinancialYear::where('fy_is_current', 1)
                ->where('fy_b_id', $businessId)
                ->where('fy_id', '!=', $fyId)
                ->exists();

            if ($isTryingToUnset && !$otherCurrentExists) {
                return response()->json([
                    'errors' => ['fy_is_current' => ['There must always be one current financial year.']]
                ], 422);
            }

            $financialYear->update($data);
            $message = 'Financial Year updated successfully.';
        } else {
            $financialYear = FinancialYear::create($data);
            $message = 'Financial Year created successfully.';
        }

        return response()->json([
            'success' => $message,
        ]);
    }


    public function destroy($id)
    {

        $financialYear = FinancialYear::find($id);

        if (!$financialYear) {
            return response()->json(['error' => 'Financial Year not found.'], 404);
        }

        $financialYear->delete();

        return response()->json(['success' => 'Financial Year deleted successfully.']);
    }
}
