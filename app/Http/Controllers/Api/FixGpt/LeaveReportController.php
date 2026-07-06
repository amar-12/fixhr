<?php

namespace App\Http\Controllers\Api\FixGpt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveReportController extends Controller
{

    public function report(Request $request)
    {

        $request->validate([
            'business_ids' => 'required|array',
            'from_date' => 'required|date',
            'to_date' => 'required|date'
        ]);

        $fromDate = Carbon::parse($request->from_date);
        $toDate   = Carbon::parse($request->to_date)->endOfDay();

        $query = LeaveRequest::with([
            'fh_employees_details',
            'fh_leave_cat_type',
            'fh_leave_day_type',
            'fh_leave_day_segment',
            'fh_approval_status'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Business Filter
        |--------------------------------------------------------------------------
        */

        $query->whereIn('lvr_b_id', $request->business_ids);

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */

        $query->where(function ($query) use ($fromDate, $toDate) {

            $query
                ->whereBetween('lvr_start_date', [$fromDate, $toDate])
                ->orWhereBetween('lvr_end_date', [$fromDate, $toDate])
                ->orWhere(function ($q) use ($fromDate, $toDate) {

                    $q->where('lvr_start_date', '<=', $fromDate)
                        ->where('lvr_end_date', '>=', $toDate);

                });

        });

        /*
        |--------------------------------------------------------------------------
        | Employee Filter
        |--------------------------------------------------------------------------
        */

        if ($request->employee_ids) {
            $query->whereIn('lvr_emp_id', $request->employee_ids);
        }

        /*
        |--------------------------------------------------------------------------
        | Department Filter
        |--------------------------------------------------------------------------
        */

        if ($request->department_ids) {

            $query->whereHas('fh_employees_details', function ($q) use ($request) {

                $q->whereIn('emp_d_id', $request->department_ids);

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Designation Filter
        |--------------------------------------------------------------------------
        */

        if ($request->designation_ids) {

            $query->whereHas('fh_employees_details', function ($q) use ($request) {

                $q->whereIn('emp_dg_id', $request->designation_ids);

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Dealer Filter
        |--------------------------------------------------------------------------
        */

        if ($request->dealer_ids) {

            $query->whereHas('fh_employees_details', function ($q) use ($request) {

                $q->whereIn('emp_dlr_id', $request->dealer_ids);

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Leave Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->leave_type_ids) {

            $query->whereIn('lvr_leave_day_type_id', $request->leave_type_ids);

        }

        /*
        |--------------------------------------------------------------------------
        | Leave Segment Filter
        |--------------------------------------------------------------------------
        */

        if ($request->leave_segment_ids) {

            $query->whereIn('lvr_day_segment_id', $request->leave_segment_ids);

        }

        /*
        |--------------------------------------------------------------------------
        | Leave Category Filter
        |--------------------------------------------------------------------------
        */

        if ($request->leave_category_ids) {

            $query->whereIn('lvr_cat_type_id', $request->leave_category_ids);

        }

        /*
        |--------------------------------------------------------------------------
        | Approval Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->approval_status_ids) {

            $query->whereIn('lvr_status', $request->approval_status_ids);

        }

        $records = $query->get();

        return response()->json([
            "status" => true,
            "total_records" => $records->count(),
            "data" => $records
        ]);

    }

}