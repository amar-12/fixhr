<?php

namespace App\Http\Controllers\Web\Admin\AttendanceSettings;

use App\Http\Controllers\Controller;
use App\Models\MasterTable;
use App\Models\PolicyAttendance;
use App\Models\PolicyHolidayList;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class PolicyHolidayController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $data = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->get();
        $attendance = PolicyAttendance::where('ap_b_id', $user->emp_b_id)->get();
        $type = MasterTable::where('m_group', 'HOLIDAY_TYPE')->get();
        $day_type_id = MasterTable::where('m_group', 'LEAVE_TYPE')->get();
        $day_segment_type_id = MasterTable::where('m_group', 'LEAVE_DAY_SEGMENT')->get();
        $financialYears = FinancialYear::where('fy_b_id', $user->emp_b_id)->get();

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['phl_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['phl_id', 'phl_b_id', 'phl_name', 'phl_type_id', 'phl_day_type_id', 'phl_day_segment_id', 'phl_start_date', 'phl_end_date', 'updated_at'],
                    'relation' => ['fh_business:b_id', 'fh_master_table:m_id,m_name']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['phl_id', 'phl_name', 'phl_type_id', 'updated_at', 'phl_b_id', 'phl_start_date', 'phl_end_date'],
                ]
            ];

            if (!empty($request->financialYearFilter)) {
                $fy = FinancialYear::find($request->financialYearFilter);

                if ($fy) {
                    $dynamicConditions[] = [
                        'method' => 'whereRaw',
                        'args' => ["(
                            (phl_start_date BETWEEN ? AND ?)
                            OR
                            (phl_end_date BETWEEN ? AND ?)
                            OR
                            (phl_start_date <= ? AND phl_end_date >= ?)
                        )", [
                            $fy->fy_start_date,
                            $fy->fy_end_date,
                            $fy->fy_start_date,
                            $fy->fy_end_date,
                            $fy->fy_start_date,
                            $fy->fy_end_date
                        ]]
                    ];
                }
            }

            $searchColumns = ['phl_name', 'phl_type_id', 'phl_start_date', 'phl_end_date', 'updated_at'];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new PolicyHolidayList(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns
            ))->getServerSideDataTable();

            $rowData = [];
            foreach ($list as $index => $val) {
                $startDate = Carbon::parse($val->phl_start_date);
                $endDate = Carbon::parse($val->phl_end_date);
                $holidayDays = $startDate->diffInDays($endDate) + 1;
                $holidayDays = $val->phl_day_type_id == 202 ? $holidayDays / 2 : $holidayDays;

                $row = [];
                $row[] = $index + 1;
                $row[] = $val->phl_name;
                $row[] = optional($val->fh_master_table)->m_name ?? 'N/A';
                $row[] = optional(MasterTable::find($val->phl_day_type_id))->m_name ?? 'N/A';
                $row[] = optional(MasterTable::find($val->phl_day_segment_id))->m_name ?? 'N/A';
                $row[] = Carbon::parse($val->phl_start_date)->format('d-M-Y');
                $row[] = Carbon::parse($val->phl_end_date)->format('d-M-Y');
                $row[] = $holidayDays . ' Days';
                $row[] =  '<span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                $action = '
                        <div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu p-2" style="min-width: 200px;">';

                // if (Carbon::parse($val->phl_end_date)->gte(Carbon::today())) {
                $action .=           '<li>
                                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            onclick="openEditModel(this)"
                                            data-id="' . $val->phl_id . '"
                                            data-holiday_name="' . $val->phl_name . '"
                                            data-holiday_from="' . $val->phl_start_date . '"
                                            data-holiday_to="' . $val->phl_end_date . '"
                                            data-day-type="' . $val->phl_day_type_id . '"
                                            data-day-segment="' . $val->phl_day_segment_id . '"
                                            data-type_name="' . $val->fh_master_table->m_id . '">
                                            <i class="feather feather-edit"></i> Edit
                                        </button>
                                    </li>';
                // }

                $action .=           '
                                    <li>
                                        <button class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2"
                                            onclick="openViewModel(this)" ref="javascript:void(0);"
                                            data-id="' . $val->phl_id . '"
                                            data-holiday_name="' . $val->phl_name . '"
                                            data-holiday_from="' . $val->phl_start_date . '"
                                            data-holiday_to="' . $val->phl_end_date . '"
                                            data-type_name="' . $val->fh_master_table->m_name . '">
                                            <i class="feather feather-eye"></i> View
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                            onclick="ItemDeleteModel(this)" ref="javascript:void(0);"
                                            data-id="' . $val->phl_id . '"
                                            data-holiday_name="' . $val->phl_name . '"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editDeleteModel">
                                            <i class="feather feather-trash"></i> Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>';
                $row[] = $action;
                $rowData[] = $row;
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => count($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new PolicyHolidayList(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData
            ];

            return response()->json($output);
        }

        $columns = [
            'S. No.',
            'Holiday Name',
            'Holiday Policy Type',
            'Day Type',
            'Day Segment',
            'Start Date',
            'End Date',
            'Numbers of Holiday',
            'WEF',
            'Action',
        ];

        return view('admin.setting.attendance.holiday-policy', compact('data', 'columns', 'type', 'day_type_id', 'day_segment_type_id', 'financialYears'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $dateAlreadyExist = [];
        $nameAlreadyExist = [];
        $emptyDates = [];

        foreach ($request->holiday_name as $key => $holidayName) {
            $startDate = $request->holiday_from[$key] ?? $request->holiday_date[$key];
            $endDate = $request->holiday_to[$key] ?? $request->holiday_date[$key];
            if (($startDate == "" || $startDate == null) && ($endDate == "" || $endDate == null)) {
                $emptyDates[] = $holidayName;
                continue; // Skip saving this record
            }

            $nameExists = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
                ->where('phl_name', $holidayName)
                ->exists();

            if ($nameExists) {
                $nameAlreadyExist[] = $holidayName;
                continue;
            }

            // Check if the start date or end date already exists
            $startDateExists = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
                ->where(function ($query) use ($startDate) {
                    $query->where('phl_start_date', '=', $startDate)
                        ->orWhere('phl_end_date', '=', $startDate);
                })->exists();

            $endDateExists = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
                ->where(function ($query) use ($endDate) {
                    $query->where('phl_start_date', '=', $endDate)
                        ->orWhere('phl_end_date', '=', $endDate);
                })->exists();

            // If either date exists, add it to the array and skip storing the data
            if ($startDateExists) {
                $dateAlreadyExist[] = $startDate;
                continue; // Skip saving this record
            }

            $overlapExists = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('phl_start_date', [$startDate, $endDate])
                        ->orWhereBetween('phl_end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('phl_start_date', '<=', $startDate)
                                ->where('phl_end_date', '>=', $endDate);
                        });
                })->exists();

            if ($overlapExists) {
                $dateAlreadyExist[] = "$startDate to $endDate";
                continue;
            }

            // Save the record only if both dates do not exist
            $data = new PolicyHolidayList();
            $data->phl_b_id = $user->emp_b_id;
            $data->phl_name = $holidayName;
            $data->phl_type_id = $request->policy_type[$key];
            $data->phl_day_type_id = $request->day_type[$key];
            $data->phl_day_segment_id = $request->day_segment_type[$key];
            $data->phl_start_date = $startDate;
            $data->phl_end_date = $endDate;
            $data->save();
        }

        $messages = [];

        if (!empty($nameAlreadyExist)) {
            $messages[] = 'These holiday names already exist: <strong>' . implode(', ', $nameAlreadyExist) . '</strong>';
        }

        if (!empty($dateAlreadyExist)) {
            $messages[] = 'These date ranges overlap with existing holidays: <strong>' . implode(', ', $dateAlreadyExist) . '</strong>';
            return redirect()->route('get.policy-holiday')->with('error', 'The holiday has already been created for the date: ' . implode(', ', $dateAlreadyExist));
        }

        if (!empty($emptyDates)) {
            return redirect()->route('get.policy-holiday')->with('error', 'The following holidays have empty dates and were not created: ' . implode(', ', $emptyDates));
        }

        if (!empty($messages)) {
            return redirect()
                ->route('get.policy-holiday')
                ->with('validation_errors', $messages);
        }

        return redirect()
            ->route('get.policy-holiday')
            ->with('success', 'Holiday policies created successfully.');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $holiday = PolicyHolidayList::findOrFail($request->id);

        // Allow edit only if holiday is in the future
        try {
            $holidayStart = Carbon::parse($holiday->phl_start_date)->startOfDay();
            $holidayEnd = Carbon::parse($holiday->phl_end_date)->startOfDay();
        } catch (\Exception $e) {
            return redirect()->route('get.policy-holiday')->with('error', 'Invalid holiday date.');
        }

        // if ($holidayStart->lt(Carbon::today()) || $holidayEnd->lt(Carbon::today())) {
        //     return redirect()->route('get.policy-holiday')->with('error', 'Cannot edit a holiday that has already started or occurred.');
        // }

        // Use the same request field names as the store() method (support fallback names)
        $holidayName = $request->holiday_name ?? $request->update_holiday_name;
        $startDate   = $request->holiday_from ?? $request->holiday_date ?? null;
        $endDate     = $request->holiday_to ?? $request->holiday_date ?? null;
        $policyType  = $request->update_policy_type ?? null;
        $dayType     = $request->day_type ?? $request->update_day_type ?? 201;
        $daySegmentType = $request->day_segment_type ?? $request->update_day_segment_type ?? null;

        // Check overlapping dates
        $overlapExists = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
            ->where('phl_id', '!=', $holiday->phl_id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('phl_start_date', [$startDate, $endDate])
                    ->orWhereBetween('phl_end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('phl_start_date', '<=', $startDate)
                            ->where('phl_end_date', '>=', $endDate);
                    });
            })->exists();

        if ($overlapExists) {
            return redirect()->route('get.policy-holiday')->with('success', 'Holiday updated successfully.');
        }

        // Update holiday (also persist day type and segment to match store())
        $holiday->update([
            'phl_name'         => $holidayName,
            'phl_type_id'      => $policyType,
            'phl_day_type_id'  => $dayType,
            'phl_day_segment_id' => $daySegmentType,
            'phl_start_date'   => $startDate,
            'phl_end_date'     => $endDate,
        ]);


        return redirect()->route('get.policy-holiday')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Request $request)
    {
        $holidayId = $request->input('holiday_id');
        $holiday = PolicyHolidayList::find($holidayId);

        if (!$holiday) {
            return redirect()->back()->with('error', 'Holiday not found.');
        }

        // Prevent deletion of holidays that are already in the past
        try {
            $holidayEnd = Carbon::parse($holiday->phl_end_date)->startOfDay();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid holiday date.');
        }

        if ($holidayEnd->lt(Carbon::today())) {
            return redirect()->back()->with('error', 'Cannot delete a holiday that has already occurred.');
        }

        $holiday->delete();

        return redirect()->back()->with('success', 'Holiday deleted successfully.');
    }
}
