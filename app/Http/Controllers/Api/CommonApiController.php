<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PolicyHolidayList;
use App\Models\PolicyShiftTiming;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\LeaveTypeResource;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\Attendance\HolidayResource;
use App\Http\Resources\Policy\PolicyShiftTimeResource;

class CommonApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function sendTestMail()
    {
        try {
            Mail::raw('This is a test email from Laravel.', function ($message) {
                $message->to('shubhi@example.com') // 🔁 Replace with your email
                    ->subject('Test Mail');
            });

            return response()->json([
                'status' => true,
                'message' => 'Test mail sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Mail failed to send.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::select('emp_id', 'emp_checkin_method_id','emp_shift_type_id')->where('emp_id', $user->emp_id)->first();
        $result = [];
        if ($request->type == 'check_in_methods') {
            $result = [['check_in_methods'=>$employee->emp_checkin_method_id ? MasterTableResource::collection($employee->fh_checkin_method()) : [],
            'shift_details'=>PolicyShiftTimeResource::collection($employee->fh_shift_type()->get())]
        ];
        } elseif ($request->type == 'holiday_list') {
            $first_date = "$request->year-01-01";
            $last_date = "$request->year-12-31";

            $holiday_record = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
                ->where('phl_start_date', '<=', $last_date)
                ->where('phl_end_date', '>=', $first_date)
                ->get();

            $updatedData = $holiday_record->flatMap(function ($item) use ($result) {
                $startDate = Carbon::parse($item->phl_start_date);
                $endDate = Carbon::parse($item->phl_end_date);

                while ($startDate <= $endDate) {
                    $currentMonthStart = Carbon::createFromFormat('Y-m-d', $startDate->copy()->startOfMonth()->max($startDate)->format('Y-m-d'));
                    $currentMonthEnd = Carbon::createFromFormat('Y-m-d', $startDate->copy()->endOfMonth()->min($endDate)->format('Y-m-d'));

                    $result[] = [
                        'phl_b_id' => $item->phl_b_id,
                        'phl_ap_id' => $item->phl_ap_id,
                        'phl_name' => $item->phl_name,
                        'phl_start_date' => $currentMonthStart,
                        'phl_end_date' => $currentMonthEnd,
                        'phl_month' => $currentMonthStart->format('F'),
                        'phl_month_number' => $currentMonthStart->format('m'),
                        'days_in_month' => $currentMonthStart->diffInDays($currentMonthEnd) + 1,
                    ];

                    $startDate = $currentMonthEnd->copy()->addDay();
                }

                return $result;
            });

            $filteredData = $updatedData->filter(function ($holiday) use ($request) {
                $holidayYear = Carbon::parse($holiday['phl_start_date'])->year;
                return $holidayYear == $request->year;
            });

            $filteredData = $filteredData->map(function ($holiday) {
                return (object) $holiday;
            });

            $result = HolidayResource::collection($filteredData->values());

        }elseif($request->type == 'leave_type_list'){
            $result = MasterTableResource::collection(MasterTable::where('m_group', 'LEAVE_CATEGORY')->get());
        }

        return ReturnHelper::jsonApiReturn($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
}
