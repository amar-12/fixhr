<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Helpers\ApprovalHelper;
use App\Http\Controllers\CommonApprovalController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Models\AttendanceRecord;
use App\Models\AttendanceException;
use App\Models\OtApprovalStatus;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use ChandraHemant\HtkcUtils\PaginatedResource;
use ChandraHemant\HtkcUtils\ReturnHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Attendance\OvertimeResource;

class AttendanceApprovalController extends Controller
{
	public function index(Request $request)
	{
		$user = Auth::user();
		$page = $request->input('page', 1);
		$limit = $request->input('limit', 10);

		$query = AttendanceRecord::where('atd_b_id', $user->emp_b_id)->approvableBy($user);

		if (!is_null($request->input('status'))) {
			$query->where('atd_request_status', $request->input('status'));
		}

		if (!is_null($request->input('from_date'))) {
			$query->whereDate('atd_date', '>=', Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay());
		} else {
			$query->whereDate('atd_date', '>=', Carbon::now()->format('Y-m-d'));
		}

		if (!is_null($request->input('to_date'))) {
			$query->whereDate('atd_date', '<=', Carbon::createFromFormat('d M, Y', $request->input('to_date'))->startOfDay());
		} else {
			$query->whereDate('atd_date', '<=', Carbon::now()->format('Y-m-d'));
		}

		if ($request->input('last_15_days')) {
			$query->whereDate('atd_date', '>=', Carbon::now()->subDays(15));
		}

		if ($request->input('search_filter')) {
			$searchFilter = trim($request->input('search_filter'));

			// Check if the input is a date format
			if (preg_match('/^\d{1,2}(?:\/\d{1,2})?(?:\/\d{4})?$/', $searchFilter)) {
				$dateParts = explode('/', $searchFilter);

				if (count($dateParts) == 1) {
					// Case: Searching by day (e.g., "18" -> all months' 18th day)
					$query->whereDay('atd_date', $dateParts[0])
						->orWhereDay('created_at', $dateParts[0]);
				} elseif (count($dateParts) == 2) {
					// Case: Searching by day and month (e.g., "18/02" -> 18th Feb of any year)
					$query->whereMonth('atd_date', $dateParts[1])
						->whereDay('atd_date', $dateParts[0])
						->orWhereMonth('created_at', $dateParts[1])
						->whereDay('created_at', $dateParts[0]);
				} elseif (count($dateParts) == 3) {
					// Case: Searching by full date (e.g., "18/02/2025")
					$formattedDate = Carbon::createFromFormat('d/m/Y', $searchFilter)->format('Y-m-d');
					$query->whereDate('atd_date', $formattedDate)
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

		$data = $query->orderBy('atd_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

		if ($query->exists())
			return ReturnHelper::jsonApiReturn(new PaginatedResource($data, AttendanceResource::class));
		else
			return response()->json(['result' => [], 'message' => 'No Attendance Approval Data found.', 'status' => false]);
	}

	public function bulkApproveOld(Request $request)
	{
		$user = Auth::user();
		$ids = $request->input('bulk_request_id', []);
		$status = $request->input('status', null);
		$masterModuleId = $request->master_module_id;
		$approvalData = [];

		if (empty($ids)) {
			return response()->json(['message' => 'Invalid request data.', 'status' => false], 400);
		}

		foreach ($ids as $key) {
			if ($request->module_id !== null && $request->module_id !== 0 && filled($request->module_id)) {
				$approvalDetails = ApprovalHelper::getApprovalOrRejectionData($key, $status, $request->module_id, Null, $masterModuleId);
				if ($approvalDetails && $approvalDetails['pa_emp_id'] == $user->emp_id) {
					$approvalData['atd_id'] = md5($key);
					$approvalData['module_id'] = $request->module_id;
					$approvalData['approval_status'] = $approvalDetails->pa_status_id;
					$approvalData['approval_type'] = $request->approval_type;
					$approvalData['approval_action_type'] = $approvalDetails->pa_type;
					$approvalData['approval_sequence'] = $approvalDetails->pa_sequence;
					$approvalData['is_last_approval'] = $approvalDetails->pa_last;
					$approvalData['message'] = $request->remark;

					// Ensure that each data entry is an object
					$formattedRequest = new Request([
						'POST_TYPE' => "Attendance_REQUEST_APPROVAL",
						'data' => (object) $approvalData // Convert array to object
					]);

					$result = app(CommonApprovalController::class)->handlerApproval($formattedRequest);

					if ($result instanceof JsonResponse) {
						$result = $result->getData(true); // Convert JSON response to an array
					}

					if (!isset($result['status']) || !$result['status']) {
						return response()->json($result, 400); // Stop if any approval fails
					}
				} else {
					return response()->json(['status' => false, 'message' => 'Selected requests are already processed or you are not authorized to approve/reject this request.'], 403);
				}
			} else {
				$data = AttendanceRecord::where('atd_b_id', $user->emp_b_id)->where('atd_id', $key)->first();
				$approval = ApprovalHelper::getApprovalData($data, $user, 'atd_');
				if ($approval && $approval["canApprove"]) {
					$approvalData['log_module_id'] = $masterModuleId;
					$approvalData['log_request_id'] = $key;
					$approvalData['log_description'] = $request->remark;
					$approvalData['log_status'] = $request->approval_type ? $approval['masterApproveBtn'][1]->m_id : 170;
					$approvalData['deduction_amount'] = 0;
					$approvalData['prefix'] = 'atd_';

					// Ensure that each data entry is an object
					$formattedRequest = new Request([
						'data' => (object) $approvalData // Convert array to object
					]);

					$response = ApprovalHelper::processApproval($formattedRequest['data'], $data, $user, "atd_");
					
					if ($response instanceof JsonResponse) {
						$response = $response->getData(true); // Convert JSON response to an array
					}

					if (!isset($response['status']) || !$response['status']) {
						return response()->json($response, 400); // Stop if any approval fails
					}
				} else {
					return response()->json(['status' => false, 'message' => 'Selected requests are already processed or you are not authorized to approve/reject this request.'], 403);
				}
			}
		}

		return response()->json(['status' => true, 'message' => 'All approvals processed successfully.', 'result' => true], 200);
	}

	public function bulkApproveMSP(Request $request)
	{
		$user = Auth::user();
		$ids = $request->input('bulk_request_id', []);
		$status = $request->input('status', null);
		$masterModuleId = $request->master_module_id;
		$approvalData = [];

		if (empty($ids)) {
			return response()->json(['message' => 'Invalid request data.', 'status' => false], 400);
		}

		foreach ($ids as $key) {
			if ($request->module_id !== null && $request->module_id !== 0 && filled($request->module_id)) {
				$approvalDetails = ApprovalHelper::getApprovalOrRejectionData($key, $status, $request->module_id, Null, $masterModuleId);
				if ($approvalDetails && $approvalDetails['pa_emp_id'] == $user->emp_id) {
					$approvalData['ae_id'] = md5($key);
					$approvalData['module_id'] = $request->module_id;
					$approvalData['approval_status'] = $approvalDetails->pa_status_id;
					$approvalData['approval_type'] = $request->approval_type;
					$approvalData['approval_action_type'] = $approvalDetails->pa_type;
					$approvalData['approval_sequence'] = $approvalDetails->pa_sequence;
					$approvalData['is_last_approval'] = $approvalDetails->pa_last;
					$approvalData['message'] = $request->remark;

					// Ensure that each data entry is an object
					$formattedRequest = new Request([
						'POST_TYPE' => "MISPUNCH_REQUEST_APPROVAL",
						'data' => (object) $approvalData // Convert array to object
					]);

					$result = app(CommonApprovalController::class)->handlerApproval($formattedRequest);

					if ($result instanceof JsonResponse) {
						$result = $result->getData(true); // Convert JSON response to an array
					}

					if (!isset($result['status']) || !$result['status']) {
						return response()->json($result, 400); // Stop if any approval fails
					}
				} else {
					return response()->json(['status' => false, 'message' => 'Selected requests are already processed or you are not authorized to approve/reject this request.'], 403);
				}
			} else {
				$data = AttendanceException::where('ae_b_id', $user->emp_b_id)->where('ae_id', $key)->first();
				$approval = ApprovalHelper::getApprovalData($data, $user, 'ae_');
				if ($approval && $approval["canApprove"]) {
					$approvalData['log_module_id'] = $masterModuleId;
					$approvalData['log_request_id'] = $key;
					$approvalData['log_description'] = $request->remark;
					$approvalData['log_status'] = $request->approval_type ? $approval['masterApproveBtn'][1]->m_id : 170;
					$approvalData['deduction_amount'] = 0;
					$approvalData['prefix'] = 'ae_';

					// Ensure that each data entry is an object
					$formattedRequest = new Request([
						'data' => (object) $approvalData // Convert array to object
					]);

					$response = ApprovalHelper::processApproval($formattedRequest['data'], $data, $user, "ae_");
					
					if ($response instanceof JsonResponse) {
						$response = $response->getData(true); // Convert JSON response to an array
					}

					if (!isset($response['status']) || !$response['status']) {
						return response()->json($response, 400); // Stop if any approval fails
					}
				} else {
					return response()->json(['status' => false, 'message' => 'Selected requests are already processed or you are not authorized to approve/reject this request.'], 403);
				}
			}
		}

		return response()->json(['status' => true, 'message' => 'All approvals processed successfully.', 'result' => true], 200);
	}

	public function bulkApprove(Request $request)
	{
	    $user = Auth::user();
	    $ids = $request->input('bulk_request_id', []);
	    $status = $request->input('status');
	    $moduleId = $request->module_id;
	    $masterModuleId = $request->master_module_id;
	    $remark = $request->remark;

	    if (empty($ids)) {
	        return response()->json(['status' => false, 'message' => 'No request selected.'], 400);
	    }

	    $moduleType = $request->input('module_type');  

	    switch ($moduleType) {
	        case "ATTENDANCE":
	            $model = AttendanceRecord::class;
	            $prefix = "atd_";
	            $postType = "Attendance_REQUEST_APPROVAL";
	            break;

	        case "MSP":
	            $model = AttendanceException::class;
	            $prefix = "ae_";
	            $postType = "MISPUNCH_REQUEST_APPROVAL";
	            break;

	        case "OT":
	            $model = OtApprovalStatus::class;
	            $prefix = "ot_";
	            $postType = "OVERTIME_REQUEST_APPROVAL";
	            break;

	        case "LEAVE":
	            $model = LeaveRequest::class;
	            $prefix = "lvr_";
	            $postType = "LEAVE_REQUEST_APPROVAL";
	            break;

	        case "OUTDOOR":
	            $model = AttendanceOutDoor::class;
	            $prefix = "atd_od_";
	            $postType = "OUTDOOR_REQUEST_APPROVAL";
	            break;

	        default:
	            return response()->json(['status' => false, 'message' => 'Invalid Module Type'], 400);
	    }

	    foreach ($ids as $reqId) {

	        if (!is_null($moduleId) && $moduleId !== 0 && filled($moduleId)) {
	            $approvalDetails = ApprovalHelper::getApprovalOrRejectionData(
	                $reqId,
	                $status,
	                $moduleId,
	                null,
	                $masterModuleId
	            );

	            if (!$approvalDetails || $approvalDetails['pa_emp_id'] != $user->emp_id) {
	                return response()->json([
	                    'status' => false,
	                    'message' => 'Not authorized or already processed.'
	                ], 403);
	            }

	            $data = [
	                "{$prefix}id"            => md5($reqId),
	                "module_id"              => $moduleId,
	                "approval_status"        => $approvalDetails->pa_status_id,
	                "approval_type"          => $request->approval_type,
	                "approval_action_type"   => $approvalDetails->pa_type,
	                "approval_sequence"      => $approvalDetails->pa_sequence,
	                "is_last_approval"       => $approvalDetails->pa_last,
	                "message"                => $remark,
	            ];

	            $formattedRequest = new Request([
	                'POST_TYPE' => $postType,
	                'data'      => (object) $data,
	            ]);

	            $response = app(CommonApprovalController::class)->handlerApproval($formattedRequest);

	            if ($response instanceof JsonResponse) {
	                $response = $response->getData(true);
	            }

	            if (!($response['status'] ?? false)) {
	                return response()->json($response, 400);
	            }
	        } else {
	            $record = $model::where("{$prefix}b_id", $user->emp_b_id)
	                ->where("{$prefix}id", $reqId)
	                ->first();

	            $approval = ApprovalHelper::getApprovalData($record, $user, $prefix);

	            if (!$approval || !$approval["canApprove"]) {
	                return response()->json([
	                    'status' => false,
	                    'message' => 'Not authorized or already processed.'
	                ], 403);
	            }

	            $logData = [
	                "log_module_id"    => $masterModuleId,
	                "log_request_id"   => $reqId,
	                "log_description"  => $remark,
	                "log_status"       => $request->approval_type ? $approval['masterApproveBtn'][1]->m_id : 170,
	                "deduction_amount" => 0,
	                "prefix"           => $prefix,
	            ];

	            $formattedRequest = new Request([
	                'data' => (object) $logData
	            ]);

	            $result = ApprovalHelper::processApproval(
	                $formattedRequest['data'],
	                $record,
	                $user,
	                $prefix
	            );

	            if ($result instanceof JsonResponse) {
	                $result = $result->getData(true);
	            }

	            if (!($result['status'] ?? false)) {
	                return response()->json($result, 400);
	            }
	        }
	    }

	    return response()->json([
	        'status' => true,
	        'message' => 'All approvals processed successfully.',
	    ]);
	}

	public function otlist(Request $request, $isApproval = null)
	{
	    $user = Auth::user();
	    if ($isApproval) {
	        $page  = $request->input('page', 1);
	        $limit = $request->input('limit', 10);
	        $query = OtApprovalStatus::where('ot_b_id', $user->emp_b_id)
	                ->approvableBy($user);

	        if ($request->filled('status')) {
	            $query->where('ot_requested_status', $request->input('status'));
	        }

	        if ($request->filled('from_date')) {
	            $query->whereDate(
	                'ot_date',
	                '>=',
	                Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay()
	            );
	        }

	        if ($request->filled('to_date')) {
	            $query->whereDate(
	                'ot_date',
	                '<=',
	                Carbon::createFromFormat('d M, Y', $request->input('to_date'))->endOfDay()
	            );
	        }

	        if ($request->boolean('last_15_days')) {
	            $query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
	        }

	        $data = $query->orderBy('ot_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

	        if ($data->count() > 0) {
	            return ReturnHelper::jsonApiReturn(
	                new PaginatedResource($data, OvertimeResource::class)
	            );
	        }

	        return response()->json([
	            'result'  => [],
	            'message' => 'No overtime application found',
	            'status'  => false
	        ]);
	    }
	}
}
