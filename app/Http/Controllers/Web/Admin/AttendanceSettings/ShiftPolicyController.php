<?php

namespace App\Http\Controllers\Web\Admin\AttendanceSettings;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\AttendanceShiftPolicy;
use App\Models\AttendanceShiftPolicyItem;
use App\Models\MasterTable;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShiftPolicyController extends Controller
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

        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['asp_b_id', $this->user->emp_b_id]],
                ['method' => 'select', 'args' => ['asp_id', 'asp_b_id', 'asp_shift_type', 'asp_shift_type_name', 'created_at', 'updated_at'], 'relation' => ['fh_business:b_id', 'fh_attendance_shift_policy_items:*']],
                ['method' => 'sortBy', 'args' => ['asp_b_id', 'asp_shift_type', 'asp_shift_type_name', 'created_at', 'updated_at'],]
            ];

            $searchColumns = ['asp_id', 'asp_b_id', 'asp_shift_type', 'asp_shift_type_name', 'created_at', 'updated_at'];
            $list = (new DynamicModelDataTableHelper(eloquentModel: new AttendanceShiftPolicy(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = optional($val->fh_master_table)->m_name;
                $row[] = $val->asp_shift_type_name;
                $row[] = '<span class="text-primary">' . $val->updated_at->format('Y-m-d H:i:s') . '</span>'; // Example format: 2025-01-14 10:00:00

                // Initialize the data array

                $editData = [];
                $rotationalEditData = null;
                if ($val->asp_shift_type == 244) {
                    $editData = [
                        'id' => md5($val->asp_id),
                        'shiftTypeSelect' => $val->asp_shift_type,
                        'fixedShiftName' => $val->asp_shift_type_name,
                        'fixedShiftId' => isset($val->fh_attendance_shift_policy_items[0]->aspi_id) ?  ($val->fh_attendance_shift_policy_items[0]->aspi_id) : '',
                        'fixedStartTime' => isset($val->fh_attendance_shift_policy_items[0]->aspi_shift_start) ?  $val->fh_attendance_shift_policy_items[0]->aspi_shift_start : '',
                        'fixedEndTime' => isset($val->fh_attendance_shift_policy_items[0]->aspi_shift_end) ?  $val->fh_attendance_shift_policy_items[0]->aspi_shift_end : '',
                        'fixedBreakMin' => isset($val->fh_attendance_shift_policy_items[0]->aspi_break_minute) ?  $val->fh_attendance_shift_policy_items[0]->aspi_break_minute : '',
                        'fixedBreakIs' => isset($val->fh_attendance_shift_policy_items[0]->aspi_break_type) ?  $val->fh_attendance_shift_policy_items[0]->aspi_break_type : '',
                        'fixedPunchBegin' => isset($val->fh_attendance_shift_policy_items[0]->aspi_punch_begin_before) ?  $val->fh_attendance_shift_policy_items[0]->aspi_punch_begin_before : '',
                        'fixedPunchEnd' => isset($val->fh_attendance_shift_policy_items[0]->aspi_punch_end_after) ?  $val->fh_attendance_shift_policy_items[0]->aspi_punch_end_after : '',
                        'fixedGraceTime' => isset($val->fh_attendance_shift_policy_items[0]->aspi_grace_time) ?  $val->fh_attendance_shift_policy_items[0]->aspi_grace_time : '',
                        'fixedPartialDayOn' => isset($val->fh_attendance_shift_policy_items[0]->aspi_partial_day_on) ?  $val->fh_attendance_shift_policy_items[0]->aspi_partial_day_on : '',
                        'fixedBeginsAt' => isset($val->fh_attendance_shift_policy_items[0]->aspi_begins_at) ?  $val->fh_attendance_shift_policy_items[0]->aspi_begins_at : '',
                        'fixedEndsAt' => isset($val->fh_attendance_shift_policy_items[0]->aspi_end_at) ?  $val->fh_attendance_shift_policy_items[0]->aspi_end_at : '',
                    ];
                } else if ($val->asp_shift_type == 245) {
                    $editData = [
                        'id' => md5($val->asp_id),
                        'shiftTypeSelect' => $val->asp_shift_type,
                        'rotationalShiftName' => $val->asp_shift_type_name,
                    ];
                    $rotationalEditData = $val->fh_attendance_shift_policy_items;
                } else if ($val->asp_shift_type == 246) {
                    $editData = [
                        'id' => md5($val->asp_id),
                        'shiftTypeSelect' => $val->asp_shift_type,
                        'openShiftName' => $val->asp_shift_type_name,
                        'openShiftId' => (optional($val->fh_attendance_shift_policy_items[0])->aspi_id),
                        'openHours' => optional($val->fh_attendance_shift_policy_items[0])->aspi_shift_hour,
                        'openMinutes' => optional($val->fh_attendance_shift_policy_items[0])->aspi_shift_minutes,
                        'openBreakMin' => optional($val->fh_attendance_shift_policy_items[0])->aspi_break_minute,
                        'openBreakIs' => optional($val->fh_attendance_shift_policy_items[0])->aspi_break_type,
                        'openPunchBeginBefore' => optional($val->fh_attendance_shift_policy_items[0])->aspi_punch_begin_before,
                        'openPunchBeginAfter' => optional($val->fh_attendance_shift_policy_items[0])->aspi_punch_end_after,
                        'openGraceTime' => optional($val->fh_attendance_shift_policy_items[0])->aspi_grace_time,
                        'openPartialDayOn' => optional($val->fh_attendance_shift_policy_items[0])->aspi_partial_day_on,
                        'openBeginsAt' => optional($val->fh_attendance_shift_policy_items[0])->aspi_begins_at,
                        'openEndsAt' => optional($val->fh_attendance_shift_policy_items[0])->aspi_end_at,
                    ];
                }

                $deleteUrl = route('shift-policy.destroy', Crypt::encryptString($val->asp_id));

                $row[] = '
                <button
                    class="btn btn-sm btn-primary  ' .
                    ($val->asp_shift_type == 245 ? 'rotational-edit-btn' : '') .
                    ' edit-button action-btns"
                    data-title="Edit Shift Policy"
                    data-bs-target="#createShiftModal"
                    data-bs-toggle="modal"
                    data-rotational-data="' . htmlspecialchars($rotationalEditData, ENT_QUOTES, 'UTF-8') . '"
                    data-edit-data=\'' . json_encode($editData, JSON_HEX_APOS | JSON_HEX_QUOT) . '\'>
                    <i class="feather feather-edit"></i>
                </button>
                <button
                    class="btn btn-sm btn-danger delete-button action-btns"
                    data-id="' . htmlspecialchars(md5($val->asp_id), ENT_QUOTES, 'UTF-8') . '"
                    title="Delete"
                    data-url="' . htmlspecialchars($deleteUrl, ENT_QUOTES, 'UTF-8') . '">
                    <i class="feather feather-trash"></i>
                </button>';
                $rowData[] = $row;
            }


            $output = ["draw" => intval($request->input('draw')), "recordsTotal" => sizeof($list), "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new AttendanceShiftPolicy(), dynamicConditions: $dynamicConditions))->countFilteredServerSideDataTable(), "data" => $rowData,];

            return response()->json($output);
        }
        $columns = ['S. No.', 'Shift Type', 'Shift Name', 'W.E.F.', 'Action'];

        $masterData = MasterTable::whereIn('m_group', ['ATTENDANCE_SHIFT_TYPE', 'WEEK_DAY', 'BREAK_TYPE'])
            ->get()
            ->groupBy('m_group');

        $shiftType = $masterData->get('ATTENDANCE_SHIFT_TYPE', collect())
            ->pluck('m_name', 'm_id')
            ->toArray();

        $partialDayOn = $masterData->get('WEEK_DAY', collect())
            ->pluck('m_name', 'm_id')
            ->toArray();
        $breakType = $masterData->get('BREAK_TYPE', collect())
            ->pluck('m_name', 'm_id')
            ->toArray();
        $shiftPolicyCount = AttendanceShiftPolicy::where('asp_b_id', $this->user->emp_b_id)->count();
        return view('admin.setting.attendance.shift-policy', compact('shiftType', 'partialDayOn', 'breakType', 'columns', 'shiftPolicyCount'));
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
    //     use Illuminate\Support\Facades\DB;
    // use Illuminate\Support\Facades\Validator;

    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'shiftTypeSelect' => 'required|integer|in:244,245,246', // Ensure shift type is valid

            // Fixed Shift
            'fixedShiftName' => 'nullable|required_if:shiftTypeSelect,244|string',
            'fixedStartTime' => 'nullable|required_if:shiftTypeSelect,244', // Start time in HH:mm format
            'fixedEndTime' => 'nullable|required_if:shiftTypeSelect,244', // End time in HH:mm format
            'fixedBreakMin' => 'nullable|required_if:shiftTypeSelect,244|integer|min:0', // Break minutes
            'fixedBreakIs' => 'nullable|required_if:shiftTypeSelect,244|exists:master_table,m_id', // Boolean validation for break type

            // Rotational Shift
            'rotationalShiftName' => 'nullable|required_if:shiftTypeSelect,245|string',
            'rotationalItemShiftName.*' => 'nullable|required_if:shiftTypeSelect,245|string|min:3|max:50',
            'rotationalItemStartTime.*' => 'nullable|required_if:shiftTypeSelect,245',
            'rotationalItemEndTime.*' => 'nullable|required_if:shiftTypeSelect,245',
            'rotationalItemBreakMin.*' => 'nullable|required_if:shiftTypeSelect,245|integer|min:0',
            'rotationalItemBreakIs.*' => 'nullable|required_if:shiftTypeSelect,245|exists:master_table,m_id',

            // Open Shift
            'openShiftName' => 'nullable|required_if:shiftTypeSelect,246|string',
            'openHours' => 'nullable|required_if:shiftTypeSelect,246|numeric|min:1', // Shift hours validation
            'openMinutes' => 'nullable|required_if:shiftTypeSelect,246|numeric|min:0', // Shift minutes validation
            'openBreakMin' => 'nullable|required_if:shiftTypeSelect,246|integer|min:0', // Break minutes validation
            'openBreakIs' => 'nullable|required_if:shiftTypeSelect,246|exists:master_table,m_id', // Boolean validation for break type
        ], [
            'shiftTypeSelect.required' => 'Please select a shift type.',
            'shiftTypeSelect.in' => 'Invalid shift type selected.',

            // Fixed Shift Messages
            'fixedShiftName.required_if' => 'Please enter the fixed shift name.',
            'fixedShiftName.string' => 'The fixed shift name must be a string.',
            'fixedStartTime.required_if' => 'Please enter the fixed start time.',
            'fixedEndTime.required_if' => 'Please enter the fixed end time.',
            'fixedBreakMin.required_if' => 'Please enter the fixed break minutes.',
            'fixedBreakMin.integer' => 'The fixed break minutes must be an integer.',
            'fixedBreakMin.min' => 'The fixed break minutes cannot be negative.',
            'fixedBreakIs.required_if' => 'Please specify the fixed break type.',
            'fixedBreakIs.exists' => 'The fixed break type must be a valid option.',

            // Rotational Shift Messages
            'rotationalShiftName.required_if' => 'Please enter the rotational shift names.',
            'rotationalShiftName.string' => 'The rotational shift name must be a string.',
            'rotationalItemShiftName.*.required_if' => 'The shift name is required',
            'rotationalShiftName.array' => 'The rotational shift names must be an array.',
            'rotationalItemStartTime.*.required_if' => 'Please enter the start time.',
            'rotationalItemEndTime.*.required_if' => 'Please enter the end time.',
            'rotationalItemStartTime.*.date_format' => 'Each rotational shift start time must be in HH:mm format.',
            'rotationalItemEndTime.*.date_format' => 'Each rotational shift end time must be in HH:mm format.',
            'rotationalItemBreakMin.*.integer' => 'Each rotational shift break minutes must be an integer.',
            'rotationalItemBreakMin.*.min' => 'Each rotational shift break minutes cannot be negative.',
            'rotationalItemBreakIs.*.required_if' => 'Please specify the break type.',
            'rotationalItemBreakIs.*.exists' => 'Each rotational shift break type must be a valid option.',
            'rotationalItemBreakMin.*.required_if' => 'Please enter the break minutes.',
            'rotationalItemBreakMin.*.integer' => 'The break minutes must be an integer.',
            'rotationalItemBreakMin.*.min' => 'The break minutes cannot be negative.',

            // Open Shift Messages
            'openShiftName.required_if' => 'Please enter the open shift name.',
            'openShiftName.string' => 'The open shift name must be a string.',
            'openHours.required_if' => 'Please enter the open shift hours.',
            'openHours.numeric' => 'The open shift hours must be a number.',
            'openHours.min' => 'The open shift hours must be at least 1.',
            'openMinutes.required_if' => 'Please enter the open shift minutes.',
            'openMinutes.numeric' => 'The open shift minutes must be a number.',
            'openMinutes.min' => 'The open shift minutes cannot be negative.',
            'openBreakMin.required_if' => 'Please enter the open break minutes.',
            'openBreakMin.integer' => 'The open break minutes must be an integer.',
            'openBreakMin.min' => 'The open break minutes cannot be negative.',
            'openBreakIs.required_if' => 'Please specify the open break type.',
            'openBreakIs.exists' => 'The open break type must be a valid option.',
        ])->after(function ($validator) use ($request) {
            if ($request->input('shiftTypeSelect') == 244) {
                $fixedStartTime = $request->input('fixedStartTime');
                $fixedEndTime = $request->input('fixedEndTime');

                if ($fixedStartTime && $fixedEndTime && $fixedStartTime >= $fixedEndTime) {
                    $validator->errors()->add('fixedEndTime', 'The fixed end time must be later than the fixed start time.');
                }
            }

            if ($request->input('shiftTypeSelect') == 245) {
                // Validate rotational shift start and end times
                $rotationalStartTimes = $request->input('rotationalItemStartTime', []);
                $rotationalEndTimes = $request->input('rotationalItemEndTime', []);

                foreach ($rotationalStartTimes as $index => $startTime) {
                    $endTime = isset($rotationalEndTimes[$index]) ? $rotationalEndTimes[$index] : null;

                    if ($startTime && $endTime && $startTime >= $endTime) {
                        $validator->errors()->add("rotationalItemEndTime.$index", 'The rotational shift end time must be later than the start time.');
                    }
                }
            }
        });



        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed!',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction(); // Start the transaction

        try {
            $shiftType = $request->shiftTypeSelect;

            // Fetch existing shift data
            $shiftData = AttendanceShiftPolicy::where('asp_b_id', $this->user->emp_b_id)
                ->whereRaw('md5(asp_id) = ?', [$request->id])
                ->first();
            $shift = AttendanceShiftPolicy::updateOrCreate(
                [
                    'asp_id' => $shiftData?->asp_id,
                    'asp_b_id' => $this->user->emp_b_id,
                ],
                [
                    'asp_b_id' => $this->user->emp_b_id,
                    'asp_shift_type' => $shiftType,
                    'asp_shift_type_name' => $this->getShiftTypeName($shiftType, $request),
                ]
            );

            $shiftItem = null; // Initialize $shiftItem
            // Handle shift types
            if ($shiftType == 244) {
                $shiftItem = $this->handleFixedShift($shift, $request);
            } elseif ($shiftType == 245) {
                $shiftItem = $this->handleRotationalShift($shift, $request);
            } elseif ($shiftType == 246) {
                $shiftItem = $this->handleOpenShift($shift, $request);
            }

            $masterData = MasterTable::where('m_id', $shiftType)->first();


            DB::commit(); // Commit the transaction
            return response()->json([
                'status' => 'success',
                'message' => $masterData->m_name . ' ' . ($shiftData ? 'Updated' : 'Created') . ' successfully!',
                'data' => $shiftItem,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of error

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Helper function to get shift type name
    private function getShiftTypeName($shiftType, $request)
    {
        if ($shiftType == 244) {
            return $request->fixedShiftName;
        } elseif ($shiftType == 245) {
            return $request->rotationalShiftName;
        } elseif ($shiftType == 246) {
            return $request->openShiftName;
        }
        return '';
    }

    // Handle fixed shift
    private function handleFixedShift($shift, $request)
    {
        return $shift->fh_attendance_shift_policy_items()->updateOrCreate(
            [
                'aspi_asp_id' => $shift->asp_id,
                'aspi_id' => $request->fixedShiftId ?? null,
                'aspi_b_id' => $this->user->emp_b_id,
            ],
            [
                'aspi_shift_name' => $request->fixedShiftName,
                'aspi_shift_start' => $request->fixedStartTime,
                'aspi_shift_end' => $request->fixedEndTime,
                'aspi_break_minute' => $request->fixedBreakMin,
                'aspi_break_type' => $request->fixedBreakIs,
                'aspi_punch_begin_before' => $request->fixedPunchBegin ?? null,
                'aspi_punch_end_after' => $request->fixedPunchEnd ?? null,
                'aspi_grace_time' => $request->fixedGraceTime ?? null,
                'aspi_partial_day_on' => $request->fixedPartialDayOn ?? null,
                'aspi_begins_at' => $request->fixedBeginsAt,
                'aspi_end_at' => $request->fixedEndsAt,
                'aspi_working_duration' => $this->calculateWorkingDuration($request->fixedStartTime, $request->fixedEndTime, 0),
                'aspi_is_active' => true,
            ]
        );
    }

    // Handle rotational shift
    private function handleRotationalShift($shift, $request)
    {
        if ($request->has('deletedItems')) {
            $deletedIds = $request->deletedItems;
            foreach ($deletedIds as $id) {
                if ($id  != null) {
                    $shift->fh_attendance_shift_policy_items()->where('aspi_id', $id)->delete();
                }
            }
        }
        $shiftItems = [];
        foreach ($request->rotationalItemShiftName as $key => $shiftName) {
            $shiftItems[] = $shift->fh_attendance_shift_policy_items()->updateOrCreate(
                [
                    'aspi_id' => $request->rotationalItemShiftId[$key] ?? null,
                    'aspi_asp_id' => $shift->asp_id,
                    'aspi_b_id' => $this->user->emp_b_id,
                ],
                [
                    'aspi_shift_name' => $shiftName,
                    'aspi_shift_start' => $request->rotationalItemStartTime[$key],
                    'aspi_shift_end' => $request->rotationalItemEndTime[$key],
                    'aspi_break_minute' => $request->rotationalItemBreakMin[$key],
                    'aspi_break_type' => $request->rotationalItemBreakIs[$key],
                    'aspi_punch_begin_before' => $request->rotationalItemPunchBeginBefore[$key] ?? null,
                    'aspi_punch_end_after' => $request->rotationalItemPunchBeginAfter[$key] ?? null,
                    'aspi_grace_time' => $request->rotationalItemGraceTime[$key] ?? null,
                    'aspi_partial_day_on' => $request->rotationalItemPartialDayOn[$key] ?? null,
                    'aspi_begins_at' => $request->rotationalItemBeginsAt[$key],
                    'aspi_end_at' => $request->rotationalItemEndsAt[$key],
                    'aspi_working_duration' => $this->calculateWorkingDuration($request->rotationalItemStartTime[$key], $request->rotationalItemEndTime[$key], 0),
                    'aspi_is_active' => true,

                ]
            );
        }

        return $shiftItems;
    }

    // Handle open shift
    private function handleOpenShift($shift, $request)
    {
        $openHours = $request->openHours;  // Example: 5 hours
        $openMinutes = $request->openMinutes;  // Example: 30 minutes
        $totalMinutes = ($openHours * 60) + $openMinutes;  // Convert hours to minutes and add the extra minutes
        // Step 2: Calculate hours, minutes, and seconds
        $calculatedHours = floor($totalMinutes / 60); // Get the whole hours
        $remainingMinutes = $totalMinutes % 60; // Get the remaining minutes

        // Step 3: Format the duration as hh:mm:ss
        $formattedDuration = sprintf('%02d:%02d:%02d', $calculatedHours, $remainingMinutes, 0); // 0 seconds for now
        return $shift->fh_attendance_shift_policy_items()->updateOrCreate(
            [
                'aspi_id' => $request->openShiftId ?? null,
                'aspi_asp_id' => $shift->asp_id,
                'aspi_b_id' => $this->user->emp_b_id,
            ],
            [
                'aspi_shift_hour' => $request->openHours,
                'aspi_shift_minutes' => $request->openMinutes,
                'aspi_shift_name' => $request->openShiftName,
                'aspi_shift_end' => $request->openEndsAt,
                'aspi_break_minute' => $request->openBreakMin ?? null,
                'aspi_break_type' => $request->openBreakIs ?? null,
                'aspi_punch_begin_before' => $request->openPunchBeginBefore ?? null,
                'aspi_punch_end_after' => $request->openPunchBeginAfter ?? null,
                'aspi_grace_time' => $request->openGraceTime ?? null,
                'aspi_partial_day_on' => $request->openPartialDayOn ?? null,
                'aspi_begins_at' => $request->openBeginsAt,
                'aspi_end_at' => $request->openEndsAt,
                'aspi_working_duration' => $formattedDuration,
                'aspi_is_active' => true,
            ]
        );
    }

    private function calculateShiftHours($startTime, $endTime)
    {
        $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
        return $end->diffInHours($start);
    }

    private function calculateShiftMinutes($startTime, $endTime)
    {
        $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
        return $end->diffInMinutes($start) % 60;
    }

    private function calculateWorkingDuration($startTime, $endTime, $breakMinutes)
    {
        if (strlen($startTime) == 5) {
            // Format is 'H:i'
            $start = Carbon::createFromFormat('H:i', $startTime);
        } else {
            // Format is 'H:i:s'
            $start = Carbon::createFromFormat('H:i:s', $startTime);
        }

        if (strlen($endTime) == 5) {
            // Format is 'H:i'
            $end = Carbon::createFromFormat('H:i', $endTime);
        } else {
            // Format is 'H:i:s'
            $end = Carbon::createFromFormat('H:i:s', $endTime);
        }
        // Step 2: Calculate the total working minutes
        $totalMinutes = $start->diffInMinutes($end);
        // Step 3: Subtract break time from the total minutes (ensure it's not negative)
        $workingMinutes = max(0, $totalMinutes - $breakMinutes);
        // Step 4: Convert minutes to hours, minutes, and seconds
        $hours = floor($workingMinutes / 60);  // Get the whole hours
        $minutes = $workingMinutes % 60;      // Get the remaining minutes
        $seconds = 0;  // Assuming no additional seconds; you can modify if needed

        // Step 5: Format the result as hh:mms
        $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        // Step 6: Store the result in the database (you can save to a column like `work_duration`)
        // Example for storing:
        // $model->work_duration = $formattedDuration;
        // $model->save();
        return $formattedDuration;
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
        $decryptedAspId = Crypt::decryptString($id);
        if ($this->user) {
            $result = CentralLogics::dynamicDelete(AttendanceShiftPolicy::class, $decryptedAspId);

            if (isset($result['success'])) {
                return response()->json(['success' => $result['success'], 'message' => 'Shift Policy has been deleted successfully']);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }
}
