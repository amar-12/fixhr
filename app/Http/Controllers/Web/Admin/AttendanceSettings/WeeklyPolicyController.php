<?php

namespace App\Http\Controllers\Web\Admin\AttendanceSettings;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\MasterTable;
use App\Models\PolicyAttendance;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyWeekOff;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WeeklyPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $weeklyPolicy = PolicyWeekOff::where('pwo_b_id', $user->emp_b_id)->count();
    //     return view('admin.setting.attendance.weekly-policy', compact('weeklyPolicy', 'columns'));
    // }

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
                    [
                        'method' => 'where',
                        'args' => ['pwo_b_id', $this->user->emp_b_id]
                    ],
                    [
                        'method' => 'select',
                        'args' => [
                            'pwo_id',
                            'pwo_b_id',
                            'pwo_name',
                            'pwo_is_unpaid',
                            'pwo_day_ids',
                            'pwo_recurrence_day_ids',
                            'created_at', // Record creation timestamp
                            'updated_at', // Record update timestamp
                        ],
                        'relation' => ['fh_business:b_id']
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['pwo_id', 'pwo_b_id', 'pwo_is_unpaid', 'pwo_name', 'pwo_day_ids', 'pwo_recurrence_day_ids', 'created_at', 'updated_at'],
                    ]
                ];

                $searchColumns = [
                    'pwo_id',
                    'pwo_b_id',
                    'pwo_is_unpaid',
                    'pwo_name',
                    'pwo_day_ids',
                    'pwo_recurrence_day_ids',
                    'updated_at', // Record update timestamp
                ];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyWeekOff(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->pwo_name;
                    // Assuming $val is an instance of PolicyWeekOff or similar
                    /*$row[] = implode(' ', $val->getDays($val->pwo_day_ids));  // Joining m_name values with a space
                    // $row[] = json_encode($val->getWeek(json_decode($val->pwo_recurrence_day_ids)));

                    // Assuming you're calling the getWeek function
                    $weekData = $val->getWeek(json_decode($val->pwo_recurrence_day_ids));*/

                    // ✅ Safe parsing of day IDs
                    $dayIds = is_string($val->pwo_day_ids)
                        ? explode(',', $val->pwo_day_ids)
                        : (is_array($val->pwo_day_ids) ? $val->pwo_day_ids : []);

                    if ($dayIds) {
                        $days = $val->getDays($dayIds);
                        $row[] = is_array($days) ? implode(' ', $days) : '';
                    } else {
                        $row[] = [];
                    }
                    

                    // ✅ Safe parsing of recurrence week data
                    $recurrenceIds = json_decode($val->pwo_recurrence_day_ids, true) ?? [];
                    $weekData = $val->getWeek($recurrenceIds) ?? [];

                    $output = '';
                    foreach ($weekData as $day => $subDays) {
                        $output .= "<h6 class='m-0'>{$day}:</h6>";
                        $output .= "<span>Week: " . implode(', ', $subDays) . "</span><br>";
                    }

                    // Initialize an empty string or array for the output
                    $output = '';

                    foreach ($weekData as $day => $subDays) {
                        $output .= "<h6 class='m-0'>{$day}:</h6>";
                        $output .= "<span>Week :" . implode(', ', $subDays) . "</span><br>";
                    }

                    $row[] = $output; // Assign the generated output to the $row array

                    // $row[] = '';// $val->days;  // This will return the array of m_name values
                    $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                          <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                    $row[] = '
                            <div class="btn-list ms-3">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu p-2" style="min-width: 200px;">
                                        <li>
                                            <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-weekly-policy"
                                                data-weekly=\'' . json_encode([
                        'id' => $val->pwo_id,
                        'b_id' => $val->pwo_b_id,
                        'name' => $val->pwo_name,
                        'pwo_is_unpaid' => $val->pwo_is_unpaid != "NULL" ? $val->pwo_is_unpaid : json_encode([]),
                        'days_id' => $val->pwo_day_ids,
                        'recurrence_day_ids' => $val->pwo_recurrence_day_ids,
                    ]) . '\'>
                                                <i class="feather feather-edit"></i> Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-weekly-policy"
                                                data-id="' . $val->pwo_id . '" title="Edit">
                                                <i class="feather feather-trash"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>';

                    $rowData[] = $row;
                }

                $output = [
                    "draw" => intval($request->input('draw')),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new PolicyWeekOff(),
                        dynamicConditions: $dynamicConditions
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            $columns = ['S. No.',  'Week Policy Name', 'Week off Days', 'Week',  '', 'Action'];
            $masterData = MasterTable::whereIn('m_group', ['RECURRENCE_DAY', 'WEEK_DAY'])
                ->get()
                ->groupBy('m_group');

            $recurrenceDay = $masterData->get('RECURRENCE_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $weekDay = $masterData->get('WEEK_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $weeklyPolicy = PolicyWeekOff::where('pwo_b_id', $this->user->emp_b_id)->count();
            return view('admin.setting.attendance.weekly-policy', compact('columns', 'weekDay', 'recurrenceDay', 'weeklyPolicy'));
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
        // dd($request->toArray());
        // Original 'week_off' data from the request
        $weekOffData = $request->input('week_off');  // Assuming input data is provided

        // Initialize the transformed array
        $formattedData = [];

        // Loop through each main key in 'week_off'
        if (!empty($weekOffData)) {
            foreach ($weekOffData as $key => $values) {
                // Extract only the keys (e.g., 334, 335) and store as an array
                $formattedData[(int)$key] = array_keys($values);  // Corrected line
            }
        }

        try {
            $topLevelKeys = $request->input('week_off') ? array_keys($request->input('week_off')) : '';

            // Save the policy
            $policy = PolicyWeekOff::updateOrCreate(
                ['pwo_id' => $request->input('pwo_id')],
                [
                    'pwo_b_id' => $this->user->emp_b_id,
                    'pwo_is_unpaid' => $request->input('is_unpaid') ? json_encode(array_keys($request->input('is_unpaid'))) : json_encode([]),
                    'pwo_name' => $request->input('pwo_name'),
                    'pwo_day_ids' => json_encode($topLevelKeys),
                    'pwo_recurrence_day_ids' => $formattedData ? json_encode($formattedData) : json_encode([]),
                ]
            );

            return response()->json(['success' => true, 'message' => 'Policy saved successfully!', 'policy' => $policy]);
        } catch (\Exception $e) {
            Log::error('Policy save failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
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
            $result = CentralLogics::dynamicDelete(PolicyWeekOff::class, $id);

            if (isset($result['success'])) {
                return response()->json(['success' => $result['success']]);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }
}
