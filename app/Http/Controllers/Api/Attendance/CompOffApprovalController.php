<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Attendance\CompOffRequestsResource;
use App\Models\CompOff;
use Carbon\Carbon;
use ChandraHemant\HtkcUtils\PaginatedResource;
use ChandraHemant\HtkcUtils\ReturnHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompOffApprovalController extends Controller
{
	public function index(Request $request)
	{

		$user = Auth::user();
		$page = $request->input('page', 1);
		$limit = $request->input('limit', 10);

		$query = CompOff::where('co_b_id', $user->emp_b_id)->whereNotIn('co_status', [139, 192, 156]);

		// $approversList = ProcessApprover::where(['pa_b_id' => $user->emp_b_id, 'pa_am_id' => $query->first()?->lvr_am_id])->pluck('pa_emp_id');

		if (!is_null($request->input('status'))) {
			$query->where('co_status', $request->input('status'));
		}

		if (!is_null($request->input('from_date'))) {
			$query->whereDate('co_request_date', '>=', Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay());
		}

		if (!is_null($request->input('to_date'))) {
			$query->whereDate('co_request_date', '<=', Carbon::createFromFormat('d M, Y', $request->input('to_date'))->startOfDay());
		}

		if ($request->input('last_15_days')) {
			$query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
		}

		if ($request->input('search_filter')) {
			$searchFilter = trim($request->input('search_filter'));

			// Check if the input is a date format
			if (preg_match('/^\d{1,2}(?:\/\d{1,2})?(?:\/\d{4})?$/', $searchFilter)) {
				$dateParts = explode('/', $searchFilter);

				if (count($dateParts) == 1) {
					// Case: Searching by day (e.g., "18" -> all months' 18th day)
					$query->whereDay('co_request_date', $dateParts[0])
						->orWhereDay('created_at', $dateParts[0]);
				} elseif (count($dateParts) == 2) {
					// Case: Searching by day and month (e.g., "18/02" -> 18th Feb of any year)
					$query->whereMonth('co_request_date', $dateParts[1])
						->whereDay('co_request_date', $dateParts[0])
						->orWhereMonth('created_at', $dateParts[1])
						->whereDay('created_at', $dateParts[0]);
				} elseif (count($dateParts) == 3) {
					// Case: Searching by full date (e.g., "18/02/2025")
					$formattedDate = Carbon::createFromFormat('d/m/Y', $searchFilter)->format('Y-m-d');
					$query->whereDate('co_request_date', $formattedDate)
						->orWhereDate('created_at', $formattedDate);
				}
			} else {
				// Case: Searching by employee name
				$query->whereHas('fh_employee', function ($q) use ($searchFilter) {
					$q->whereRaw("REPLACE(emp_full_name, '  ', ' ') LIKE ?", ["%$searchFilter%"])
						->orWhereRaw("REPLACE(emp_fname, '  ', ' ') LIKE ?", ["%$searchFilter%"])
						->orWhereRaw("REPLACE(emp_lname, '  ', ' ') LIKE ?", ["%$searchFilter%"]);
				});
			}
		}

		$data = $query->orderBy('co_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

		if ($query->exists())
			return ReturnHelper::jsonApiReturn(new PaginatedResource($data, CompOffRequestsResource::class));
		else
			return response()->json(['result' => [], 'message' => 'No Comp Off Approval Data found.', 'status' => false]);
	}
}
