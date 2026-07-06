<?php

namespace App\Http\Controllers\Web\Admin\AttendanceSettings;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\MasterTable;
use App\Models\AttendanceRecord;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyShiftTimingLog;
use App\Models\LeaveRequest;
use App\Models\PolicyAttendance;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\PolicyHolidayList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\CarbonPeriod;

class AttendanceShiftTypeController extends Controller
{
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
                        'args' => ['pst_b_id', $this->user->emp_b_id]
                    ],
                    [
                        'method' => 'select',
                        'args' => [
                            'pst_id', // Primary key
                            'pst_b_id', // Business ID
                            'pst_ap_id', // Attendance policy ID
                            'pst_type_id', // Shift type ID
                            'pst_name', // Shift name
                            'pst_code', // Shift Code
                            'pst_start_time', // Start time
                            'pst_end_time', // End time
                            'pst_shift_duration', // Shift Duration
                            'pst_break_duration_minutes', // Shift Break Duration
                            'pst_end_next_day', // Check Shift End Day
                            'pst_end_by', // Shift End Day Time
                            'pst_allow_break1', // Allow Break1
                            'pst_break_begin_time1', // Break1 Start Time
                            'pst_break_end_time1', // Break1 End Time
                            'pst_is_break_paid', // Break1 Paid/Unpaid
                            // 'pst_punch_begin_before', // Break1 Start Time Begin
                            // 'pst_punch_end_after', // Break1 End Time
                            'pst_break1_duration', // Break1 Duration
                            'pst_allow_break2', // Allow Break2
                            'pst_break_begin_time2', // Break2 Start Time
                            'pst_break_end_time2', // Break2 End Time
                            'pst_allow_punch_begin_before', // Allow punching in before start time
                            'pst_mins_punch_begin_before', // Minutes allowed for early punch-in
                            'pst_allow_punch_end_after', // Allow punching out after end time
                            'pst_mins_punch_end_after', // Minutes allowed for late punch-out
                            'pst_allow_grace_time', // Whether grace time is allowed
                            'pst_grace_time', // Grace time in minutes
                            'pst_allow_partial_day', // Whether partial days are allowed
                            'pst_partial_day_type_id', // Partial day type ID
                            'pst_partial_day_begin_time', // Partial day begin time
                            'pst_partial_day_end_time', // Partial day end time
                            'week_off', // Partial day Week
                            'pst_allow_partial_day2', // Whether partial days are allowed
                            'pst_partial_day_type_id2', // Partial day type ID
                            'pst_partial_day_begin_time2', // Partial day begin time
                            'pst_partial_day_end_time2', // Partial day end time
                            'week_off2', // Partial day Week
                            'pst_hd_office_report_after', // Check Half Day Report After
                            'pst_hd_office_report_after_time', // Half Day Report After Time
                            'pst_session1_end_by', // Session End
                            'pst_session2_grace_time', // Session End Time
                            'pst_hd_office_report_before', // Check Half Day Report Before Leave
                            'pst_hd_office_report_before_time', // Check Half Day Report Before Leave Time
                            'pst_is_high', // Face config High/Low
                            'pst_is_face_track_yes', // Face Track Yes/No
                            'pst_min_work_hour',
                            'pst_work_hour_penalty_status_id',
                            'pst_break_duration_minutes', // Break duration in minutes
                            'pst_is_break_paid', // Whether the break is paid
                            'pst_allow_break', // Whether breaks are allowed
                            'pst_break_end_time', // Break end time
                            'pst_auto_assign_shift', // Auto assign shift
                            'created_at', // Record creation timestamp
                            'updated_at', // Record update timestamp
                        ],
                        'relation' => ['fh_business:b_id']
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['pst_id', 'pst_b_id', 'pst_ap_id', 'pst_type_id', 'pst_name', 'pst_start_time', 'pst_end_time', 'pst_break_duration_minutes', 'pst_is_break_paid', 'updated_at'],
                    ]
                ];

                $searchColumns = [
                    'pst_id',
                    'pst_b_id',
                    'pst_ap_id',
                    'pst_type_id',
                    'pst_name',
                    'pst_start_time',
                    'pst_end_time',
                    'pst_break_duration_minutes',
                    'pst_is_break_paid',
                    'updated_at'
                ];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyShiftTiming(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->pst_name;
                    $row[] = $val->fh_attendance_policy->ap_name;
                    $row[] = $val->fh_master_table->m_name;
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
                                            <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-shift-type action-btns"
                                                data-shift=\'' . json_encode([
                                                    'id' => $val->pst_id,
                                                    'b_id' => $val->pst_b_id,
                                                    'ap_id' => $val->pst_ap_id,
                                                    'type_id' => $val->pst_type_id,
                                                    'name' => $val->pst_name,
                                                    'code' => $val->pst_code,
                                                    'start_time' => Carbon::parse($val->pst_start_time)->format('H:i'),
                                                    'end_time' => Carbon::parse($val->pst_end_time)->format('H:i'),
                                                    'allow_grace_time' => $val->pst_allow_grace_time,
                                                    'grace_time' => $val->pst_grace_time,
                                                    'shift_duration' => $val->pst_shift_duration,
                                                    'break_duration' => $val->pst_break_duration_minutes,
                                                    'min_work_hour' => Carbon::parse($val->pst_min_work_hour)->format('H:i'),
                                                    'work_hour_penalty_status_id' => $val->pst_work_hour_penalty_status_id,
                                                    'end_next_day' => $val->pst_end_next_day,
                                                    'end_by' => Carbon::parse($val->pst_end_by)->format('H:i'),
                                                    'allow_break1' => $val->pst_allow_break1,
                                                    'break_begin_time1' => Carbon::parse($val->pst_break_begin_time1)->format('H:i'),
                                                    'break_end_time1' => Carbon::parse($val->pst_break_end_time1)->format('H:i'),
                                                    'break1_duration' => $val->pst_break1_duration,
                                                    'is_paid' => $val->pst_is_break_paid,
                                                    'allow_break2' => $val->pst_allow_break2,
                                                    'break_begin_time2' => Carbon::parse($val->pst_break_begin_time2)->format('H:i'),
                                                    'break_end_time2' => Carbon::parse($val->pst_break_end_time2)->format('H:i'),
                                                    'allow_punch_begin_before' => $val->pst_allow_punch_begin_before,
                                                    'mins_punch_begin_before' => $val->pst_mins_punch_begin_before,
                                                    'allow_punch_end_after' => $val->pst_allow_punch_end_after,
                                                    'mins_punch_end_after' => $val->pst_mins_punch_end_after,
                                                    'allow_partial_day' => $val->pst_allow_partial_day,
                                                    'partial_day_type_id' => $val->pst_partial_day_type_id,
                                                    'partial_day_begin_time' => Carbon::parse($val->pst_partial_day_begin_time)->format('H:i'),
                                                    'partial_day_end_time' => Carbon::parse($val->pst_partial_day_end_time)->format('H:i'),
                                                    'week_off' => $val->week_off,
                                                    'allow_partial_day2' => $val->pst_allow_partial_day2,
                                                    'partial_day_type_id2' => $val->pst_partial_day_type_id2,
                                                    'partial_day_begin_time2' => Carbon::parse($val->pst_partial_day_begin_time2)->format('H:i'),
                                                    'partial_day_end_time2' => Carbon::parse($val->pst_partial_day_end_time2)->format('H:i'),
                                                    'week_off2' => $val->week_off2,
                                                    'hd_office_report_after' => $val->pst_hd_office_report_after,
                                                    'hd_office_report_after_time' => Carbon::parse($val->pst_hd_office_report_after_time)->format('H:i'),
                                                    'session1_end_by' => $val->pst_session1_end_by,
                                                    'session2_grace_time' => $val->pst_session2_grace_time,
                                                    'hd_office_report_before' => $val->pst_hd_office_report_before,
                                                    'hd_office_report_before_time' => Carbon::parse($val->pst_hd_office_report_before_time)->format('H:i'),
                                                    'is_high' => $val->pst_is_high,
                                                    'is_face_track_yes' => $val->pst_is_face_track_yes,
                                                    'auto_assign_shift' => $val->pst_auto_assign_shift,
                                                ]) . '\'>
                                                <i class="feather feather-edit"></i> Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-shift-type action-btns"
                                                data-id="' . $val->pst_id . '">
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
                        eloquentModel: new PolicyShiftTiming(),
                        dynamicConditions: $dynamicConditions
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            $columns = ['S. No.',  'Shift Name', 'Attendance Policy', 'Shift Type', '', 'Action'];
            $masterData = MasterTable::whereIn('m_group', ['ATTENDANCE_SHIFT_TYPE', 'WEEK_DAY', 'RECURRENCE_DAY'])
                ->get()
                ->groupBy('m_group');

            $shiftType = $masterData->get('ATTENDANCE_SHIFT_TYPE', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $week = $masterData->get('WEEK_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $shiftTiming = PolicyShiftTiming::where('pst_b_id', $this->user->emp_b_id)->get();
            $attendancePolicyType = PolicyAttendance::where('ap_b_id', $this->user->emp_b_id)->pluck('ap_name', 'ap_id')->toArray();


            $recurrenceDay = $masterData->get('RECURRENCE_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            return view('admin.setting.attendance.attendance-shift-type', compact('shiftTiming', 'shiftType', 'attendancePolicyType', 'columns', 'week', 'recurrenceDay'));
        } else {
            abort(404);
        }
    }


    /*public function store(Request $request)
    {
        // dd($request->all());
        if ($this->user) {
            // Create the Validator instance
            $validator = Validator::make($request->all(), [
                'pst_ap_id' => 'required|string|max:255',
                'pst_type_id' => 'required|integer|exists:master_table,m_id',
                'pst_name' => 'required|string|max:255',
                'pst_start_time' => 'required|date_format:H:i',
                'pst_end_time' => 'required|date_format:H:i|after:pst_start_time',
                'pst_break_duration_minutes' => 'required|numeric|min:0',
                'pst_is_break_paid' => 'nullable|boolean',
                'pst_allow_break' => 'nullable|boolean',
                'pst_allow_grace_time' => 'nullable|boolean',
                'pst_allow_partial_day' => 'nullable|boolean',
                'pst_allow_punch_begin_before' => 'nullable|boolean',
                'pst_allow_punch_end_after' => 'nullable|boolean',
                'pst_min_work_hour' => 'nullable|date_format:H:i|min:0',
                'pst_work_hour_penalty_status_id' => 'nullable|numeric|min:0'
            ], [
                // Custom messages (you can keep your existing messages)
                'pst_type_id.required' => 'Shift type field is mandatory.',
                'pst_type_id.exists' => 'The selected shift type is invalid.',
                'pst_name.required' => 'Please provide a name for the shift.',
                'pst_start_time.required' => 'Start time is required.',
                'pst_start_time.date_format' => 'Start time must be in the format HH:MM.',
                'pst_end_time.required' => 'End time is required.',
                'pst_end_time.after' => 'End time must be after the start time.',
                'pst_break_duration_minutes.required' => 'Break duration is required.',
                'pst_break_duration_minutes.numeric' => 'Break duration must be a number.',
                'pst_grace_time.required' => 'Minutes race time field is required.',
                'pst_mins_punch_end_after.required' => 'Minute punch end after is required.',
                'pst_ap_id.required' => 'Attendance policy type is required.',
                'pst_break_begin_time.required' => 'Break begin time is required.',
                'pst_break_end_time.required' => 'Break end time is required.',
                // The pst mins punch end after field is required. pst_ap_id
                'pst_grace_time.numeric' => 'Grace time must be a valid number.',
                'pst_partial_day_begin_time.before' => 'Partial day begin time must be before the partial day end time.',
                'pst_partial_day_end_time.after' => 'Partial day end time must be after the partial day begin time.',
                'pst_mins_punch_begin_before.min' => 'Minutes to punch begin before must be zero or more.',
                'pst_mins_punch_begin_before.required' => 'Minutes punch begin before field is required.',
                'pst_mins_punch_end_after.min' => 'Minutes to punch end after must be zero or more.',
                'pst_partial_day_type_id.required' => 'Partial day field is required.',
                'pst_break_begin_time.before' => 'The break begin time field must be a time before break end time.',
                'pst_break_end_time.after' => 'The break end time field must be a time after break begin time..',
            ]);

            // Add conditional rules
            $validator->sometimes('pst_break_begin_time', 'required|date_format:H:i|before:pst_break_end_time', function ($input) {
                return $input->pst_allow_break == 1;
            });

            $validator->sometimes('pst_break_end_time', 'required|date_format:H:i|after:pst_break_begin_time', function ($input) {
                return $input->pst_allow_break == 1;
            });

            $validator->sometimes('pst_partial_day_begin_time', 'required|date_format:H:i|before:pst_partial_day_end_time', function ($input) {
                return $input->pst_allow_partial_day == 1;
            });

            $validator->sometimes('pst_partial_day_end_time', 'required|date_format:H:i|after:pst_partial_day_begin_time', function ($input) {
                return $input->pst_allow_partial_day == 1;
            });

            // Add other conditional rules similarly
            $validator->sometimes('pst_mins_punch_begin_before', 'required|numeric|min:0', fn($input) => $input->pst_allow_punch_begin_before == 1);
            $validator->sometimes('pst_mins_punch_end_after', 'required|numeric|min:0', fn($input) => $input->pst_allow_punch_end_after == 1);
            $validator->sometimes('pst_mins_punch_end_after', 'required|numeric|min:0', fn($input) => $input->pst_allow_punch_end_after == 1);
            $validator->sometimes('pst_grace_time', 'required|numeric|min:0', fn($input) => $input->pst_allow_grace_time == 1);
            $validator->sometimes('pst_partial_day_type_id', 'required|integer|exists:master_table,m_id', fn($input) => $input->pst_allow_partial_day == 1);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // If validation passes, proceed with saving
            $validatedData = $validator->validated();
            $validatedData['pst_b_id'] = $this->user->emp_b_id;


            // Save or update the policy shift timing
            PolicyShiftTiming::updateOrCreate(
                ['pst_id' => $request->input('pst_id')],
                $validatedData
            );

            // Return appropriate success response
            if ($request->input('pst_id')) {

                // Check if dates are in frozen payroll periods
                $startDate = $request->pst_effective_from;
                $endDate   = $request->pst_effective_to;

                if(!empty($startDate) && !empty($endDate)) {
                    $isFrozen = PayrollPeriod::where('pp_b_id', $this->user->emp_b_id)
                    ->where('pp_is_freezed', 120)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('pp_start_date', [$startDate, $endDate])
                          ->orWhereBetween('pp_end_date', [$startDate, $endDate])
                          ->orWhere(function ($q2) use ($startDate, $endDate) {
                              $q2->where('pp_start_date', '<=', $startDate)
                                  ->where('pp_end_date', '>=', $endDate);
                          });
                    })
                    ->exists();

                    if ($isFrozen) {
                        return response()->json(['success' => 'Shift saved, but recalculation skipped due to frozen payroll.']);
                    }

                    $this->recalculateShiftAttendanceInline($request->input('pst_id'), $startDate, $endDate);
                }

                // PolicyShiftTimingLog::create(array_combine(
                //     array_map(fn($k) => str_replace('pst_', 'pstl_', $k), array_keys($validatedData)),
                //     array_values($validatedData)
                // ));
                return response()->json(['success' => 'Policy updated successfully']);
            } else {
                return response()->json(['success' => 'Policy saved successfully']);
            }
        } else {
            abort(404);
        }
    }

    private function recalculateShiftAttendanceInline($pstId, $start, $end)
    {
        $shift = PolicyShiftTiming::find($pstId);
        if (!$shift) return;

        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        Employee::where('emp_shift_type_id', $pstId)->chunk(100, function ($employees) use ($shift, $startDate, $endDate) {
            foreach ($employees as $employee) {
                $weekOffDates = CentralLogics::getWeekOffDates($employee, null, null, $startDate->copy(), $endDate->copy());

                $holidayDates = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('phl_start_date', [$startDate, $endDate])
                          ->orWhereBetween('phl_end_date', [$startDate, $endDate])
                          ->orWhere(function ($q2) use ($startDate, $endDate) {
                              $q2->where('phl_start_date', '<=', $startDate)
                                 ->where('phl_end_date', '>=', $endDate);
                          });
                    })
                    ->get()
                    ->flatMap(fn($holiday) =>
                        Carbon::parse($holiday->phl_start_date)
                              ->daysUntil(Carbon::parse($holiday->phl_end_date))
                              ->map(fn($date) => $date->format('Y-m-d'))
                    )
                    ->unique()
                    ->values()
                    ->toArray();

                $attendances = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
                    ->whereBetween('atd_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->get();

                foreach ($attendances as $attendance) {
                    $attendanceDate = Carbon::parse($attendance->atd_date)->toDateString();

                    // ðŸ”’ Check if the payroll period for this date is frozen
                    $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                        ->where('pp_start_date', '<=', $attendanceDate)
                        ->where('pp_end_date', '>=', $attendanceDate)
                        ->where('pp_is_freezed', 120)  // 120 = frozen
                        ->exists();

                    if ($frozen) {
                        continue; // Skip if payroll is frozen for that date
                    }
                    
                    $attdExcept = AttendanceException::where('ae_emp_id', $attendance->atd_emp_id)->where('ae_date', $attendance->atd_date)->where('ae_stage_completed', 1)->first();

                    $attendanceDate = Carbon::parse($attendance->atd_date)->toDateString();
                    $aeDate = Carbon::parse($attdExcept?->ae_date)->format('Y-m-d');
                    $checkInTime = $attdExcept?->ae_in_time ? Carbon::parse("{$aeDate} {$attdExcept->ae_in_time}") : Carbon::parse($attendance->atd_check_in_time);
                    $checkOutTime = $attdExcept?->ae_out_time ? Carbon::parse("{$aeDate} {$attdExcept->ae_out_time}") : Carbon::parse($attendance->atd_check_out_time);
                    $shiftStart = Carbon::parse($attendanceDate . ' ' . $shift->pst_start_time);
                    $shiftEnd = Carbon::parse($attendanceDate . ' ' . $shift->pst_end_time);
                    $punchDate = $checkInTime->format('Y-m-d');
                    $attendanceStatus = 251; // Present

                    // Handle Partial Day
                    $dayName = Carbon::parse($attendanceDate)->format('l');
                    if ($shift->pst_allow_partial_day && $shift->fh_master_table_week->m_name === $dayName) {
                        $shiftStart = Carbon::parse($attendanceDate . ' ' . $shift->pst_partial_day_begin_time);
                        $shiftEnd = Carbon::parse($attendanceDate . ' ' . $shift->pst_partial_day_end_time);
                    }

                    // Grace Time
                    $graceMinutes = $shift->pst_allow_grace_time ? ($shift->pst_grace_time ?? 0) : 0;
                    $shiftStartWithGrace = $shiftStart->copy()->addMinutes($graceMinutes);
                    //Late Time
                    $isLate = $checkInTime->gt($shiftStartWithGrace);
                    $lateMinutes = $isLate ? max(0, $shiftStartWithGrace->diffInMinutes($checkInTime)) : 0;
                    //Early Time
                    $isEarlyExit = $checkOutTime->lt($shiftEnd);
                    $earlyExitMinutes = $isEarlyExit ? max(0, $checkOutTime->diffInMinutes($shiftEnd)) : 0;

                    //OT Time
                    $checkInTime = Carbon::parse($checkInTime)->startOfMinute();
                    $checkOutTime = Carbon::parse($checkOutTime)->startOfMinute();
                    $shiftStart = Carbon::parse($shiftStart)->startOfMinute();
                    $shiftEnd = Carbon::parse($shiftEnd)->startOfMinute();
                    $totalWorkedMinutes = $checkInTime->diffInMinutes($checkOutTime);
                    $shiftDurationMinutes = $shiftStart->diffInMinutes($shiftEnd);

                    $isOvertime = $checkOutTime->gt($shiftEnd);
                    $overtimeMinutes = max(0, $totalWorkedMinutes - $shiftDurationMinutes);

                    // Calculate min work hour in minutes
                    if(isset($shift->pst_min_work_hour) && !empty($shift->pst_min_work_hour)) {
                        $minWorkHour = $shift->pst_min_work_hour ? Carbon::parse("$punchDate {$shift->pst_start_time}")->diffInMinutes(Carbon::parse("$punchDate {$shift->pst_min_work_hour}")) : 0;
                        $minHDWorkHour = $minWorkHour/2;
                        // Attendance Status Logic
                        if ($totalWorkedMinutes >= $minWorkHour) {
                            $attendanceStatus = 251; // Present
                        } elseif ($totalWorkedMinutes >= $minHDWorkHour && $minHDWorkHour <= $minWorkHour) {
                            $attendanceStatus = 252; // Half Day
                            $isLate = 0;
                            $lateMinutes = 0;
                        } elseif ($totalWorkedMinutes < $minHDWorkHour) {
                            $attendanceStatus = 203; // Absent
                        }
                    }
                    
                    $leaveReqSeg = LeaveRequest::where('lvr_emp_id', $attendance->atd_emp_id)->where('lvr_start_date', $attendance->atd_date)->where('lvr_leave_day_type_id', 202)->first();
                    if($leaveReqSeg) {
                        $attendanceStatus = 252; // Half Day
                        $isLate = 0;
                        $lateMinutes = 0;
                        $isEarlyExit = 0;
                        $earlyExitMinutes = 0;
                    }

                    if (!$checkInTime || !$checkOutTime) {
                        $attendanceStatus = 228;
                        $isOvertime = 0;
                        $overtimeMinutes = 0;
                    }

                    $attendance->update([
                        'atd_is_late' => $isLate ? 1 : 0,
                        'atd_late_duration' => $lateMinutes,
                        'atd_is_early_exit' => $isEarlyExit ? 1 : 0,
                        'atd_early_exit_duration' => $earlyExitMinutes,
                        'atd_is_overtime' => $isOvertime ? 1 : 0,
                        'atd_overtime_hours' => $overtimeMinutes,
                        'atd_attendance_status' => $attendanceStatus, // Default to Present
                    ]);

                    // app('App\Http\Controllers\Api\Attendance\PunchInApiController')->calculateAttendanceStatus($attendance);

                    // $attendance->update([
                    //     'atd_is_late' => $isLate,
                    //     'atd_late_duration' => $lateMinutes,
                    //     'atd_is_early_exit' => $isEarlyExit,
                    //     'atd_early_exit_duration' => $earlyExitMinutes,
                    //     'atd_is_overtime' => $isOvertime,
                    //     'atd_overtime_hours' => number_format($overtimeMinutes / 60, 2),
                    //     'atd_attendance_status' => 251,
                    //     // 'atd_attendance_status' => app('App\Http\Controllers\Api\Attendance\PunchInApiController')->calculateAttendanceStatus($attendance),
                    // ]);
                }
            }
        });
    }*/

    public function store(Request $request)
    {
        if (!$this->user) {
            abort(404);
        }

        // dd($request->toArray());
        if ($this->user) {
            // Create the Validator instance
            $validator = Validator::make($request->all(), [
                'pst_ap_id' => 'required|string|max:255',
                'pst_type_id' => 'required|integer|exists:master_table,m_id',
                'pst_name' => 'required|string|max:255',
                'pst_code' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('policy_shift_timings', 'pst_code')
                    ->ignore($request->input('pst_id'), 'pst_id')
                    ->where(function ($query) use ($request) {
                        return $query->where('pst_b_id', $request->input('pst_b_id'));
                    }),
                ],
                'pst_start_time' => 'nullable|date_format:H:i',
                'pst_end_time' => 'nullable|date_format:H:i',

                'pst_shift_duration' => 'nullable|numeric|min:0',
                'pst_break_duration_minutes' => 'nullable|numeric|min:0',
                'pst_grace_time' => 'nullable|numeric|min:0',
                'pst_min_work_hour' => 'nullable|date_format:H:i',
                'pst_work_hour_penalty_status_id' => 'nullable|numeric|min:0',

                'pst_end_by' => 'nullable|date_format:H:i',
                'pst_break1_duration' => 'nullable|numeric|min:0',
                'pst_break_begin_time1' => 'nullable|date_format:H:i',
                'pst_break_end_time1' => 'nullable|date_format:H:i',
                // 'pst_punch_begin_before' => 'nullable|numeric|min:0',
                // 'pst_punch_end_after' => 'nullable|numeric|min:0',
                'pst_break_begin_time2' => 'nullable|date_format:H:i',
                'pst_break_end_time2' => 'nullable|date_format:H:i',
                'pst_mins_punch_begin_before' => 'nullable',
                'pst_mins_punch_end_after' => 'nullable',

                'pst_partial_day_type_id' => 'nullable',
                'pst_partial_day_type_id2' => 'nullable',
                'pst_partial_day_begin_time' => 'nullable|date_format:H:i',
                'pst_partial_day_end_time' => 'nullable|date_format:H:i',
                'pst_partial_day_begin_time2' => 'nullable|date_format:H:i',
                'pst_partial_day_end_time2' => 'nullable|date_format:H:i',

                'pst_session1_end_by' => 'nullable|date_format:H:i',
                'pst_session2_grace_time' => 'nullable|integer|min:0',
                'pst_hd_office_report_after_time' => 'nullable|date_format:H:i',
                'pst_hd_office_report_before_time' => 'nullable|date_format:H:i',

                'pst_is_break_paid' => 'nullable|boolean',
                'pst_is_high' => 'nullable|boolean',
                'pst_is_face_track_yes' => 'nullable|boolean',
            ], [
                // Custom messages (you can keep your existing messages)
                'pst_ap_id.required' => 'Attendance policy type is required.',
                'pst_type_id.required' => 'Shift type field is mandatory.',
                'pst_type_id.exists' => 'The selected shift type is invalid.',
                'pst_name.required' => 'Please provide a name for the shift.',
                'pst_code.unique' => 'This shift code is already in use.',

                'pst_start_time.required' => 'Start time is required.',
                'pst_start_time.date_format' => 'Start time must be in the format HH:MM.',
                
                'pst_end_time.required' => 'End time is required.',

                'pst_grace_time.required' => 'Minutes grace time field is required.',
                'pst_grace_time.numeric' => 'Grace time must be a valid number.',

                'pst_shift_duration.required' => 'Please provide a duration(in minutes) for the shift.',
                'pst_shift_duration.numeric' => 'Shift duration must be a valid number.',

                'pst_break_duration_minutes.required' => 'Break duration is required.',
                'pst_break_duration_minutes.numeric' => 'Break duration must be a number.',

                'pst_end_by.date_format' => 'End by must be in the format HH:MM.',

                'pst_break_begin_time1.required' => 'Break begin time is required.',
                'pst_break_begin_time1.date_format' => 'Break begin time 1 must be in the format HH:MM.',
                'pst_break_begin_time1.before' => 'The break begin time field must be a time before break end time.',

                'pst_break_end_time1.required' => 'Break end time is required.',
                'pst_break_end_time1.date_format' => 'Break end time 1 must be in the format HH:MM.',
                'pst_break_end_time1.after' => 'The break end time field must be a time after break begin time.',

                'pst_break_begin_time2.required' => 'Break begin time is required.',
                'pst_break_begin_time2.date_format' => 'Break begin time 2 must be in the format HH:MM.',
                'pst_break_begin_time2.before' => 'The break begin time field must be a time before break end time.',

                'pst_break_end_time2.required' => 'Break end time is required.',
                'pst_break_end_time2.date_format' => 'Break end time 2 must be in the format HH:MM.',
                'pst_break_end_time2.after' => 'The break end time field must be a time after break begin time.',

                'pst_break1_duration.numeric' => 'Break duration 1 must be a number.',

                'pst_mins_punch_begin_before.min' => 'Minutes to punch begin before must be zero or more.',
                'pst_mins_punch_begin_before.required' => 'Minutes punch begin before field is required.',

                'pst_mins_punch_end_after.required' => 'Minute punch end after is required.',
                'pst_mins_punch_end_after.min' => 'Minutes to punch end after must be zero or more.',
                
                // The pst mins punch end after field is required. pst_ap_id

                'pst_partial_day_begin_time.before' => 'Partial day begin time must be before the partial day end time.',
                'pst_partial_day_end_time.after' => 'Partial day end time must be after the partial day begin time.',

                'pst_partial_day_begin_time2.before' => 'Partial day begin time must be before the partial day end time.',
                'pst_partial_day_end_time2.after' => 'Partial day end time must be after the partial day begin time.',

                'week_off.array' => 'Week off must be an array.',
                'week_off2.array' => 'Week off must be an array.',

                'pst_hd_office_report_after_time.date_format' => 'HD office report after must be in the format HH:MM.',
                'pst_session1_end_by.date_format' => 'Session 1 end by must be in the format HH:MM.',
                'pst_session2_grace_time.numeric' => 'Session 2 grace time must be a number.',
                'pst_hd_office_report_before_time.date_format' => 'HD office report before must be in the format HH:MM.',
            ]);

            // Add conditional rules
            $validator->sometimes('pst_end_time', 'required|date_format:H:i|after:pst_start_time', function ($input) {
                return $input->pst_type_id != 245;
            });

            $validator->sometimes('pst_break_begin_time1', 'required|date_format:H:i|before:pst_break_end_time1', function ($input) {
                return $input->pst_allow_break1 == 1;
            });

            $validator->sometimes('pst_break_end_time1', 'required|date_format:H:i|after:pst_break_begin_time1', function ($input) {
                return $input->pst_allow_break1 == 1;
            });

            $validator->sometimes('pst_break_begin_time2', 'required|date_format:H:i|before:pst_break_end_time2', function ($input) {
                return $input->pst_allow_break2 == 1;
            });

            $validator->sometimes('pst_break_end_time2', 'required|date_format:H:i|after:pst_break_begin_time2', function ($input) {
                return $input->pst_allow_break2 == 1;
            });

            // Add other conditional rules similarly
            $validator->sometimes('pst_mins_punch_begin_before', 'required|numeric|min:0', fn($input) => $input->pst_allow_punch_begin_before == 1);
            $validator->sometimes('pst_mins_punch_end_after', 'required|numeric|min:0', fn($input) => $input->pst_allow_punch_end_after == 1);
            $validator->sometimes('pst_grace_time', 'required|numeric|min:0', fn($input) => $input->pst_allow_grace_time == 1);
            $validator->sometimes('pst_partial_day_type_id', 'integer|exists:master_table,m_id', fn($input) => $input->pst_allow_partial_day == 1);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // If validation passes, proceed with saving
            $validatedData = $validator->validated();
            $validatedData['pst_b_id'] = $this->user->emp_b_id;

            // Checkbox/radio fields (normalize to 1/0)
            $checkboxes = [
                'pst_allow_grace_time',
                'pst_end_next_day',
                'pst_allow_partial_day',
                'pst_allow_partial_day2',
                'pst_allow_punch_begin_before',
                'pst_allow_punch_end_after',
                'pst_allow_break1',
                'pst_allow_break2',
                'pst_hd_office_report_after',
                'pst_hd_office_report_before',
                'pst_auto_assign_shift',
            ];
            foreach ($checkboxes as $field) {
                $validatedData[$field] = $request->has($field) && $request->input($field) != 0 ? 1 : 0;
            }

            // Save week_off & week_off2 as JSON
            if ($request->has('week_off')) {
                $weekOffValues = $request->input('week_off');
                $formatted = '{' . implode(',', $weekOffValues) . '}';
                $validatedData['week_off'] = $formatted;
            } else {
                $validatedData['week_off'] = null;
            }

            if ($request->has('week_off2')) {
                $weekOffValues = $request->input('week_off2');
                $formatted = '{' . implode(',', $weekOffValues) . '}';
                $validatedData['week_off2'] = $formatted;
            } else {
                $validatedData['week_off2'] = null;
            }

            // dd($validatedData);

            try {
                // Save or update the policy shift timing
                $policy = PolicyShiftTiming::updateOrCreate(
                    ['pst_id' => $request->input('pst_id')],
                    $validatedData
                );
            } catch (\Exception $e) {
                // Handle exception
                Log::error('Error saving policy shift timing of policy - ' . $policy . ' : ' . $e->getMessage());
            }

            // Handle recalculation if needed
            if ($request->input('pst_id') && !empty($request->pst_effective_from) && !empty($request->pst_effective_to)) {
                $isFrozen = PayrollPeriod::where('pp_b_id', $this->user->emp_b_id)
                    ->where('pp_is_freezed', 120)
                    ->where(function ($q) use ($request) {
                        $q->whereBetween('pp_start_date', [$request->pst_effective_from, $request->pst_effective_to])
                          ->orWhereBetween('pp_end_date', [$request->pst_effective_from, $request->pst_effective_to])
                          ->orWhere(function ($q2) use ($request) {
                              $q2->where('pp_start_date', '<=', $request->pst_effective_from)
                                 ->where('pp_end_date', '>=', $request->pst_effective_to);
                          });
                    })
                    ->exists();

                if ($isFrozen) {
                    return response()->json(['success' => 'Shift saved, but recalculation skipped due to frozen payroll.']);
                }

                $request->merge([
                    'start_date' => $request->pst_effective_from,
                    'end_date' => $request->pst_effective_to
                ]);
                $this->fastRecalculate($request);

                // Return immediate response and process in background
                return response()->json([
                    'success' => 'Policy updated successfully. Recalculation started.',
                    'recalculating' => true,
                    'pst_id' => $request->input('pst_id'),
                    'start_date' => $request->pst_effective_from,
                    'end_date' => $request->pst_effective_to
                ]);
            }
        }
        return response()->json(['success' => 'Policy saved successfully']);
    }

    // New API endpoint for fast recalculation
    public function fastRecalculate(Request $request)
    {
        set_time_limit(300); // 5 minutes max execution time
        
        $pstId = $request->input('pst_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $this->fastRecalculation($pstId, $startDate, $endDate);
        
        return response()->json(['status' => 'completed', 'message' => 'Recalculation finished']);
    }

    // Fast recalculation method
    private function fastRecalculation($pstId, $start, $end)
    {
        $shift = PolicyShiftTiming::find($pstId);
        if (!$shift) return;
        
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        
        // Get all employee IDs for this shift
        $employeeIds = Employee::where('emp_shift_type_id', $pstId)
            ->pluck('emp_id')
            ->toArray();
        
        if (empty($employeeIds)) return;
        
        // Get all attendances for these employees in date range
        $attendances = AttendanceRecord::whereIn('atd_emp_id', $employeeIds)
            ->whereBetween('atd_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();
        
        // Get all exceptions in one query
        $exceptions = AttendanceException::whereIn('ae_emp_id', $employeeIds)
            ->whereBetween('ae_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('ae_stage_completed', 1)
            ->get()
            ->keyBy(function($item) {
                return $item->ae_emp_id . '_' . $item->ae_date;
            });
        
        // Process in batches
        $batchSize = 1000;
        $updates = [];
        
        foreach ($attendances as $attendance) {
            $exceptionKey = $attendance->atd_emp_id . '_' . $attendance->atd_date;
            $exception = $exceptions[$exceptionKey] ?? null;
            
            // Calculate new values
            $calculated = $this->calculateSimpleAttendance($attendance, $exception, $shift);
            
            // Prepare update
            $updates[] = [
                'id' => $attendance->atd_id,
                'data' => $calculated
            ];
            
            // Execute batch update when reached batch size
            if (count($updates) >= $batchSize) {
                $this->batchUpdateSimple($updates);
                $updates = [];
            }
        }
        
        // Update any remaining records
        if (!empty($updates)) {
            $this->batchUpdateSimple($updates);
        }
    }

    // Simple calculation method
    private function calculateSimpleAttendance($attendance, $exception, $shift)
    {
        $date = $attendance->atd_date;
        
        // Get check in/out times (use exception if available)
        $checkIn = $exception && $exception->ae_in_time 
            ? Carbon::parse($exception->ae_in_time)
            : Carbon::parse($attendance->atd_check_in_time);
            
        $checkOut = $exception && $exception->ae_out_time 
            ? Carbon::parse($exception->ae_out_time)
            : Carbon::parse($attendance->atd_check_out_time);
        
        // Skip if missing check in/out
        if (!$checkIn || !$checkOut) {
            return [
                'atd_is_late' => 0,
                'atd_late_duration' => 0,
                'atd_is_early_exit' => 0,
                'atd_early_exit_duration' => 0,
                'atd_is_overtime' => 0,
                'atd_overtime_hours' => 0,
                'atd_attendance_status' => 228 // Missing punch
            ];
        }
        
        // Calculate shift times
        $shiftStart = Carbon::parse($shift->pst_start_time);
        $shiftEnd = Carbon::parse($shift->pst_end_time);
        
        // Apply grace time if enabled
        $graceTime = $shift->pst_allow_grace_time ? $shift->pst_grace_time : 0;
        $graceStart = $shiftStart->copy()->addMinutes($graceTime);
        
        // Calculate metrics
        $isLate = $checkIn->gt($graceStart);
        $lateMinutes = $isLate ? $graceStart->diffInMinutes($checkIn) : 0;
        
        $isEarlyExit = $checkOut->lt($shiftEnd);
        $earlyMinutes = $isEarlyExit ? $checkOut->diffInMinutes($shiftEnd) : 0;
        
        $isOvertime = $checkOut->gt($shiftEnd);
        $overtimeMinutes = $isOvertime ? $checkOut->diffInMinutes($shiftEnd) : 0;
        
        // Calculate attendance status
        $workedMinutes = $checkIn->diffInMinutes($checkOut);
        $status = 251; // Present
        
        if ($shift->pst_min_work_hour) {
            $minWorkMinutes = Carbon::parse($shift->pst_start_time)
                ->diffInMinutes(Carbon::parse($shift->pst_min_work_hour));
            
            if ($workedMinutes >= $minWorkMinutes) {
                $status = 251; // Present
            } elseif ($workedMinutes >= ($minWorkMinutes / 2)) {
                $status = 252; // Half day
            } else {
                $status = 203; // Absent
            }
        }
        
        return [
            'atd_is_late' => $isLate ? 1 : 0,
            'atd_late_duration' => $lateMinutes,
            'atd_is_early_exit' => $isEarlyExit ? 1 : 0,
            'atd_early_exit_duration' => $earlyMinutes,
            'atd_is_overtime' => $isOvertime ? 1 : 0,
            'atd_overtime_hours' => $overtimeMinutes,
            'atd_attendance_status' => $status
        ];
    }

    public function destroy($id)
    {
        if ($this->user) {
            $result = CentralLogics::dynamicDelete(PolicyShiftTiming::class, $id);
            if (isset($result['success'])) {
                return response()->json(['success' => $result['success']]);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }

    // Simple calculation for log entries
    private function calculateLogMetrics($log, $shift)
    {
        $checkIn = $log->al_check_in_time ? Carbon::parse($log->al_check_in_time) : null;
        $checkOut = $log->al_check_out_time ? Carbon::parse($log->al_check_out_time) : null;

        if (!$checkIn || !$checkOut) {
            return [
                'log_is_late' => 0,
                'log_late_duration' => 0,
                'log_is_early_exit' => 0,
                'log_early_exit_duration' => 0,
                'log_is_overtime' => 0,
                'log_overtime_hours' => 0,
                'log_attendance_status' => 228 // Missing punch
            ];
        }

        $shiftStart = Carbon::parse($shift->pst_start_time);
        $shiftEnd = Carbon::parse($shift->pst_end_time);

        // Apply grace time if enabled
        $graceTime = $shift->pst_allow_grace_time ? $shift->pst_grace_time : 0;
        $graceStart = $shiftStart->copy()->addMinutes($graceTime);

        // Metrics calculation
        $isLate = $checkIn->gt($graceStart);
        $lateMinutes = $isLate ? $graceStart->diffInMinutes($checkIn) : 0;

        $isEarlyExit = $checkOut->lt($shiftEnd);
        $earlyMinutes = $isEarlyExit ? $checkOut->diffInMinutes($shiftEnd) : 0;

        $isOvertime = $checkOut->gt($shiftEnd);
        $overtimeMinutes = $isOvertime ? $checkOut->diffInMinutes($shiftEnd) : 0;

        // Work duration
        $workedMinutes = $checkIn->diffInMinutes($checkOut);

        // Determine status
        $status = 251; // Present by default
        if ($shift->pst_min_work_hour) {
            $minWorkMinutes = Carbon::parse($shift->pst_start_time)
                ->diffInMinutes(Carbon::parse($shift->pst_min_work_hour));

            if ($workedMinutes >= $minWorkMinutes) {
                $status = 251; // Present
            } elseif ($workedMinutes >= ($minWorkMinutes / 2)) {
                $status = 252; // Half day
            } else {
                $status = 203; // Absent
            }
        }

        return [
            'log_is_late' => $isLate ? 1 : 0,
            'log_late_duration' => $lateMinutes,
            'log_is_early_exit' => $isEarlyExit ? 1 : 0,
            'log_early_exit_duration' => $earlyMinutes,
            'log_is_overtime' => $isOvertime ? 1 : 0,
            'log_overtime_hours' => $overtimeMinutes,
            'log_attendance_status' => $status
        ];
    }


    // Simple batch update
    private function batchUpdateSimple($table, $updates)
    {
        if (empty($updates)) return;

        // Collect all fields from the first update
        $fields = array_keys(reset($updates)['data']);
        $cases = [];
        $params = [];
        $ids = array_column($updates, 'id');

        foreach ($fields as $field) {
            $case = "{$field} = CASE id "; // assume primary key column is 'id' for log table
            foreach ($updates as $update) {
                $id = $update['id'];
                $value = $update['data'][$field] ?? null;
                $case .= "WHEN {$id} THEN ? ";
                $params[] = $value;
            }
            $case .= "END";
            $cases[] = $case;
        }

        $sql = "UPDATE {$table} SET " . implode(", ", $cases);
        $sql .= " WHERE id IN (" . implode(",", $ids) . ")";
        DB::update($sql, $params);
    }
}
