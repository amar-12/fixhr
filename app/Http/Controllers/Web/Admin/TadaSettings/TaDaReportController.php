<?php
namespace App\Http\Controllers\Web\Admin\TadaSettings;
use App\Exports\TaDa\ClaimExpenseReport;
use App\Exports\TaDa\ClaimBasicReport;
use App\Exports\TaDa\ClaimDetailReport;
use App\Exports\TaDa\TravelDetailedReport;
use App\Exports\TaDa\TravelAdvanceReport;
use App\Exports\TaDa\ClaimSummaryReport;
use App\Exports\TaDa\TravelReport;
use App\Exports\TaDa\AttendanceGeofenceReport;
use App\Models\AttendanceRecord;
use App\Models\TadaRequestDetail;
use App\Models\TadaRequestPlan;
use App\Helpers\CentralLogics;
use App\Exports\DailyAllowanceExport;
use App\Exports\ClaimReportExport;
use App\Http\Controllers\Api\TaDa\PolicyTravelType;
use App\Http\Controllers\Controller;
use App\Imports\VehicleImport;
use App\Models\ApprovalModule;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelAllowance;
use App\Models\PolicyTadaTravelMode;
use App\Models\TadaClaim;
use App\Models\PolicyTadaTravelType;
use App\Models\PolicyTadaTravelVehicle;
use App\Models\PolicyTadaDailyAllowanceLodging;
use App\Models\PolicyTadaDailyAllowance;
use App\Models\PolicyTadaLodging;
use App\Models\RuleCriterion;
use Carbon\Carbon;
use App\Models\TadaMetroCity;
use App\Models\TravelPurpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use Sabberworm\CSS\RuleSet\RuleSet;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmptySheetExport;
use App\Exports\LodgingExport;
use App\Exports\PolicyCategoryExport;
use App\Exports\VehicleExport;
use App\Imports\DailyAllowanceImport;
use App\Imports\LodgingImport;
use App\Imports\PolicyCategoryImport;
use App\Models\TadaExpenseSetting;
use Illuminate\Database\QueryException;
class TaDaReportController extends Controller
{
	public function taDaReport(Request $request)
	{  
		$user = Auth::user();
		$filters = $request->only(['travel_type', 'status', 'report_type', 'from_date', 'to_date', 'employee', 'purpose_type', 'claim_id', 'travel_id', 'designation', 'employee_status', 'branch', 'report_output', 'tap_location']);
		
		// Normalize empty-like values so missing status does not apply any filter
		if (array_key_exists('status', $filters)) {
			$rawStatus = is_null($filters['status']) ? null : trim((string)$filters['status']);
			$filters['status'] = ($rawStatus === '' || strtolower($rawStatus) === 'null' || strtolower($rawStatus) === 'undefined')
				? null
				: $filters['status'];
		}
		
		
		$query = TadaRequestPlan::with([
			'fh_tada_request_details',
			'fh_tada_expenses',
			'fh_tada_claim',
			'fh_approval_status',
			'fh_employee',
			'fh_employee.fh_designation',
			'fh_employee.fh_department',
			'fh_employee.fh_grade',
			'fh_policy_tada_travel_type.fh_travel_type',
			'fh_tada_claim.fh_claim_status',
			'fh_business.fh_currency',
			'fh_employee.fh_branch',
			'fh_employee.fh_employee_status',	
			'fh_travel_purpose',
			'fh_employee.fh_dealership',
			'fh_tada_request_details.fh_policy_tada_travel_vehicle',
			'fh_tada_expenses.fh_expense_type',
			'fh_tada_claim.fh_approval_log_employee_wise',
			'fh_tada_advance_approval_log',
			'fh_tada_advance_approval_log.fh_adl_request_status',
			'fh_tada_advance_approval_log.fh_process_approvers',
			'fh_approval_status',
			'fh_travel_vehicle',
			'fh_tada_request_details.fh_policy_tada_travel_mode.fh_travel_mode',
			'fh_plan_approval_log',
			'fh_plan_approval_log.fh_employee',
			'fh_plan_approval_log.fh_role',
			'fh_plan_approval_log.fh_status',
			
			// 'fh_deduction_log'
		])->where('trp_b_id', $user->emp_b_id);
		
		   
		  // dd($query['fh_tada_request_details']); 
			// Travel Type filter
			$query->when(!empty($filters['travel_type']), function ($q) use ($filters) {
				$q->where('trp_pttt_id', $filters['travel_type']);
			});
			
			// Status filter - For claim reports, filter by claim status, not plan status
			$query->when(!empty($filters['status']), function ($q) use ($filters) {
				$q->whereHas('fh_tada_claim', function ($sub) use ($filters) {
					$sub->where('tc_status', $filters['status']);
				});
			});
			
			
			// Employee filter - Fixed to properly handle employee selection
			if (!empty($filters['employee']) && $filters['employee'] !== '' && $filters['employee'] !== null) {
				\Log::info('Applying employee filter:', ['employee_id' => $filters['employee']]);
				$query->where('trp_emp_id', $filters['employee']);
			} else {
				\Log::info('No employee filter applied - showing all employees');
			}
			
			// Purpose type filter
			$query->when(!empty($filters['purpose_type']), function ($q) use ($filters) {
				$q->where('trp_purpose', $filters['purpose_type']);
			});
			
			// Claim ID filter
			$query->when(!empty($filters['claim_id']), function ($q) use ($filters) {
				$q->whereHas('fh_tada_claim', function ($sub) use ($filters) {
					$sub->where('tc_unique_id', $filters['claim_id']);
				});
			});
		
			// Travel ID filter
			$query->when(!empty($filters['travel_id']), function ($q) use ($filters) {
				$q->where('trp_unique_id', $filters['travel_id']);
			});
		
			// Designation filter
			$query->when(!empty($filters['designation']), function ($q) use ($filters) {
				$q->whereHas('fh_employee', function ($sub) use ($filters) {
					$sub->where('emp_dg_id', $filters['designation']);
				});
			});
			
			// Employee Status filter
			$query->when(!empty($filters['employee_status']), function ($q) use ($filters) {
				$q->whereHas('fh_employee', function ($sub) use ($filters) {
					$sub->where('emp_status', $filters['employee_status']);
				});
			});
		
			// Branch filter
			$query->when(!empty($filters['branch']), function ($q) use ($filters) {
				$q->whereHas('fh_employee', function ($sub) use ($filters) {
					$sub->where('emp_br_id', $filters['branch']);
				});
			});
			// Date range filters - Different logic for travel vs claim reports
			$reportType = $filters['report_type'] ?? null;
			$reportConfig = config('tada_reports');
			$groupIds = $reportConfig['group_ids'] ?? [ 'travel' => [], 'claim' => [] ];
			$isTravelReport = in_array($reportType, $groupIds['travel']);
			$isClaimReport = in_array($reportType, $groupIds['claim']);

			if ($isClaimReport) {
				$query->whereHas('fh_tada_claim', function ($claim) {
					$claim->whereColumn('tc_trp_id', 'trp_id');
				});
			}

			if ($isTravelReport) {
				// For travel reports, filter by travel start date from tada_request_plan table
				$query->when(!empty($filters['from_date']), function ($q) use ($filters) {
					$q->whereDate('trp_start_date', '>=', $filters['from_date']);
				});
				$query->when(!empty($filters['to_date']), function ($q) use ($filters) {
					$q->whereDate('trp_start_date', '<=', $filters['to_date']);
				});	
			} elseif ($isClaimReport) {
				// For claim reports, filter by created_at from tada_claim table
				$query->when(!empty($filters['from_date']), function ($q) use ($filters) {
					$q->whereHas('fh_tada_claim', function ($sub) use ($filters) {
						$sub->whereDate('created_at', '>=', $filters['from_date']);
					});
				});
				$query->when(!empty($filters['to_date']), function ($q) use ($filters) {
					$q->whereHas('fh_tada_claim', function ($sub) use ($filters) {
						$sub->whereDate('created_at', '<=', $filters['to_date']);
					});
				});
			} else {
				// Fallback to original behavior for unknown report types
				$query->when(!empty($filters['from_date']), function ($q) use ($filters) {
					$q->whereDate('created_at', '>=', $filters['from_date']);
				});
				$query->when(!empty($filters['to_date']), function ($q) use ($filters) {
					$q->whereDate('created_at', '<=', $filters['to_date']);
				});
			}
			
			
			try {
				// The attendance-travel report does NOT depend on $filter_data (the TadaRequestPlan
				// query above) — it builds its own dataset from AttendanceRecord. Resolve report_type
				// and reportDef FIRST, then branch before running/checking $filter_data, so this
				// report type isn't blocked by an unrelated empty-plan-query.
				if (empty($filters['report_type'])) {
					if ($request->ajax()) {
						return response()->json([
							'status' => 'error',
							'message' => 'Please select a report type',
						], 422);
					}
					Alert::error('Error', 'Please select a report type');
					return redirect()->back()->with('error', 'Please select a report type');
				}

				$reports = $reportConfig['reports'] ?? [];
				$reportDef = $reports[$filters['report_type']] ?? null;
				if (!$reportDef) {
					if ($request->ajax()) {
						return response()->json([
							'status' => 'error',
							'message' => 'Invalid report type',
						], 422);
					}
					Alert::error('Error', 'Invalid report type');
					return redirect()->back()->with('error', 'Invalid report type');
				}

				// Attendance Travel Report: built entirely from attendance + matching travel plans.
				// Branches out before $filter_data is even evaluated.
				if ($reportDef['slug'] === 'travel-attendance-report') {
					return $this->buildAttendanceTravelReport($request, $filters, $reportDef);
				}

				$filter_data = $query->get()->toArray();
				
				// Log the query and results for debugging
				\Log::info('TADA Report Query Results', [
					'filters' => $filters,
					'count' => count($filter_data),
					'has_claims' => !empty($filter_data) ? array_key_exists('fh_tada_claim', $filter_data[0] ?? []) : false
				]);
				
				// Check if data is empty
				if (empty($filter_data)) {
					$statusMessage = !empty($filters['status']) ? ' for the selected status' : '';
					if ($request->ajax()) {
						return response()->json([
							'status' => 'no_data',
							'message' => 'No records found for the selected criteria' . $statusMessage,
						], 200);
					}
					Alert::warning('No Data', 'No records found for the selected criteria' . $statusMessage);
					return redirect()->back()->withInput();
				}

				$exportClass = $reportDef['export'] ?? null;
				if (!$exportClass) {
					if ($request->ajax()) {
						return response()->json([
							'status' => 'error',
							'message' => 'Export not configured for this report',
						], 422);
					}
					Alert::error('Error', 'Export not configured for this report');
					return redirect()->back()->with('error', 'Export not configured for this report');
				}

				$includeTapLocation = (bool)($reportDef['with_tap_location'] ?? false);
				$tapLocationArg = $includeTapLocation ? ($filters['tap_location'] ?? '0') : null;

				$reportName = preg_replace('/\s+/', '_', $reportDef['name'] ?? 'Report');
				$fileName = $reportName . '_' . now()->format('Y-m-d') . '.xlsx';

				$exportInstance = $includeTapLocation
					? new $exportClass($filter_data, $tapLocationArg)
					: new $exportClass($filter_data);

				return Excel::download($exportInstance, $fileName);
			} catch (QueryException $e) {
				\Log::error('Database error in TADA report: ' . $e->getMessage());
				if ($request->ajax()) {
					return response()->json([
						'status' => 'error',
						'message' => 'An error occurred while fetching data. Please try again.'
					], 500);
				}
				Alert::error('Database Error', 'An error occurred while fetching data. Please try again.');
				return redirect()->back()->withInput();
			} catch (\Exception $e) {
				\Log::error('Error in TADA report: ' . $e->getMessage());
				if ($request->ajax()) {
					return response()->json([
						'status' => 'error',
						'message' => 'An unexpected error occurred. Please try again.'
					], 500);
				}
				Alert::error('Error', 'An unexpected error occurred. Please try again.');
				return redirect()->back()->withInput();
			}
	}

	/**
	 * Builds the "Attendance Travel Report":
	 * every attendance record in the date range, joined with any matching
	 * travel plan for that employee/date. Travel columns are blank ('---')
	 * when there is no matching travel plan for that employee on that date.
	 */
	private function buildAttendanceTravelReport(Request $request, array $filters, array $reportDef)
	{
		$user = Auth::user();

		$from = $filters['from_date'] ?? null;
		$to   = $filters['to_date'] ?? null;
		$from = $from ?: Carbon::now()->startOfMonth()->format('Y-m-d');
		$to   = $to   ?: Carbon::parse($from)->endOfMonth()->format('Y-m-d');

		$attendanceQuery = AttendanceRecord::with([
				'fh_employee.fh_department',
				'fh_employee.fh_branch',
				'fh_employee.fh_designation',
			])
			->where('atd_b_id', $user->emp_b_id)
			->whereDate('atd_date', '>=', $from)
			->whereDate('atd_date', '<=', $to);

		// Optional filters reused from the main filter set
		if (!empty($filters['branch'])) {
			$attendanceQuery->whereHas('fh_employee', function ($q) use ($filters) {
				$q->where('emp_br_id', $filters['branch']);
			});
		}
		if (!empty($filters['designation'])) {
			$attendanceQuery->whereHas('fh_employee', function ($q) use ($filters) {
				$q->where('emp_dg_id', $filters['designation']);
			});
		}
		if (!empty($filters['employee_status'])) {
			$attendanceQuery->whereHas('fh_employee', function ($q) use ($filters) {
				$q->where('emp_status', $filters['employee_status']);
			});
		}
		if (!empty($filters['employee'])) {
			$attendanceQuery->where('atd_emp_id', $filters['employee']);
		}

		$attendances = $attendanceQuery->get();

		// Pull all AttendanceLog rows for the same business/date range once,
		// keyed by "emp_id|date", so each attendance row can do an O(1) lookup
		// instead of running a query per row. AttendanceLog takes priority over
		// AttendanceRecord for late/early values when a row exists for that
		// employee + date.
		$attendanceLogs = \App\Models\AttendanceLog::whereDate('al_date', '>=', $from)
			->whereDate('al_date', '<=', $to)
			->get()
			->keyBy(function ($log) {
				return $log->al_emp_id . '|' . date('Y-m-d', strtotime($log->al_date));
			});

		// Pull all travel plans for the business/date range once, grouped by
		// employee, to avoid running a query per attendance row (N+1).
		$travelPlans = TadaRequestPlan::with([
				'fh_policy_tada_travel_type.fh_travel_type',
				'fh_travel_purpose',
				'fh_plan_approval_log.fh_status',
				'fh_tada_request_details',
			])
			->where('trp_b_id', $user->emp_b_id)
			->whereDate('trp_start_date', '>=', $from)
			->whereDate('trp_start_date', '<=', $to)
			->when(!empty($filters['travel_type']), function ($q) use ($filters) {
				$q->where('trp_pttt_id', $filters['travel_type']);
			})
			->get()
			->groupBy('trp_emp_id');

		$rows = [];

		foreach ($attendances as $atd) {
			$emp = $atd->fh_employee;
			if (!$emp) {
				continue;
			}

			$empId = $emp->emp_id;
			$atdDate = $atd->atd_date;

			// Find a travel plan for this employee whose start date matches the
			// attendance date. If multiple plans exist on the same date, take the first.
			$matchingPlan = null;
			if (isset($travelPlans[$empId])) {
				$matchingPlan = $travelPlans[$empId]->first(function ($plan) use ($atdDate) {
					return date('Y-m-d', strtotime($plan->trp_start_date)) === date('Y-m-d', strtotime($atdDate));
				});
			}

			// Resolve the attendance source for this employee + date once:
			// AttendanceLog takes priority when a row exists for that date;
			// otherwise fall back to the AttendanceRecord ($atd) already loaded.
			$logKey = $empId . '|' . date('Y-m-d', strtotime($atdDate));
			$matchingLog = $attendanceLogs->get($logKey);

			if ($matchingLog) {
				$checkInRaw  = $matchingLog->al_check_in_time;
				$checkOutRaw = $matchingLog->al_check_out_time;
				$lateMinutes = $matchingLog->al_late_duration ?? '---';
				$earlyLeavingMinutes = $matchingLog->al_early_exit_duration ?? '---';
				$remark = $matchingLog->al_reason ?? '---';
			} else {
				$checkInRaw  = $atd->atd_check_in_time;
				$checkOutRaw = $atd->atd_check_out_time;
				$lateMinutes = $atd->atd_late_duration ?? '---';
				$earlyLeavingMinutes = $atd->atd_early_exit_duration ?? '---';
				$remark = $atd->atd_remark ?? '---';
			}

			$checkIn  = $checkInRaw ? date('H:i:s', strtotime($checkInRaw)) : null;
			$checkOut = $checkOutRaw ? date('H:i:s', strtotime($checkOutRaw)) : null;

			$workedHours = '---';
			if ($checkInRaw && $checkOutRaw) {
				$in  = strtotime($checkInRaw);
				$out = strtotime($checkOutRaw);
				if ($out > $in) {
					$diff = $out - $in;
					$workedHours = sprintf('%02d:%02d', floor($diff / 3600), floor(($diff % 3600) / 60));
				}
			}

			$travelType      = '---';
			$travelId        = '---';
			$tripName        = '---';
			$purpose         = '---';
			$travelStartDate = '---';
			$travelStartTime = '---';
			$travelEndDate   = '---';
			$travelEndTime   = '---';
			$appliedDate     = '---';
			$appliedStatus   = '---';

			if ($matchingPlan) {
				$travelType = $matchingPlan->fh_policy_tada_travel_type->fh_travel_type->m_name ?? '---';
				$travelId   = $matchingPlan->trp_unique_id ?? '---';
				$tripName   = $matchingPlan->fh_travel_purpose->tp_name ?? ('Trip ' . ($matchingPlan->trp_unique_id ?? $matchingPlan->trp_id));
				$purpose    = $matchingPlan->fh_travel_purpose->tp_name ?? '---';

				$travelStartDate = $matchingPlan->trp_start_date ? date('d-m-Y', strtotime($matchingPlan->trp_start_date)) : '---';
				$travelStartTime = $matchingPlan->trp_start_time ?? '---';
				$travelEndDate   = $matchingPlan->trp_end_date ? date('d-m-Y', strtotime($matchingPlan->trp_end_date)) : '---';
				$travelEndTime   = $matchingPlan->trp_end_time ?? '---';

				$latestApproval = $matchingPlan->fh_plan_approval_log
					? $matchingPlan->fh_plan_approval_log->sortByDesc('created_at')->first()
					: null;

				if ($latestApproval) {
					$appliedDate   = $latestApproval->created_at ? date('d-m-Y', strtotime($latestApproval->created_at)) : '---';
					$appliedStatus = $latestApproval->fh_status->m_name ?? '---';
				}
			}

			$rows[] = [
				'Employee Code'     => $emp->emp_code ?? $empId,
				'Employee Name'     => $emp->emp_full_name ?? '---',
				'Branch'            => $emp->fh_branch->br_name ?? '---',
				'Department'        => $emp->fh_department->d_name ?? '---',
				'Designation'       => $emp->fh_designation->dg_name ?? '---',
				'Attendance'        => $atd->atd_date ? date('d-m-Y', strtotime($atd->atd_date)) : '---',
				'Check In'          => $checkIn ?: '---',
				'Check Out'         => $checkOut ?: '---',
				'Late (mins)'          => $lateMinutes,
				'Early Leaving (mins)' => $earlyLeavingMinutes,
				'Worked Hours'      => $workedHours,
				'Travel Type'       => $travelType,
				'Travel Id'         => $travelId,
				'Trip Name'         => $tripName,
				'Purpose'           => $purpose,
				'Remark'            => $remark,
				'Travel Start Date' => $travelStartDate,
				'Travel Start Time' => $travelStartTime,
				'Travel End Date'   => $travelEndDate,
				'Travel End Time'   => $travelEndTime,
				'Applied Date'      => $appliedDate,
				'Travel Status'    => $appliedStatus,
			];
		}

		if (empty($rows)) {
			if ($request->ajax()) {
				return response()->json([
					'status'  => 'no_data',
					'message' => 'No attendance records found for the selected criteria',
				], 200);
			}
			Alert::warning('No Data', 'No attendance records found for the selected criteria');
			return redirect()->back()->withInput();
		}

		$reportName = preg_replace('/\s+/', '_', $reportDef['name'] ?? 'Attendance_Travel_Report');
		$fileName   = $reportName . '_' . now()->format('Y-m-d') . '.xlsx';

		return Excel::download(
			new \App\Exports\TaDa\AttendanceGeofenceReport($rows, $from, $to),
			$fileName
		);
	}

	/**
	 * Attendance + Geofence / No-travel report
	 * params: date (Y-m-d)
	 */
	public function attendanceGeofenceReport(Request $request)
	{
		$user = Auth::user();
		$date = $request->input('date', date('Y-m-d'));

		$attendances = AttendanceRecord::with('fh_employee')
			->where('atd_b_id', $user->emp_b_id)
			->whereDate('atd_date', $date)
			->get();

		$rows = [];
		foreach ($attendances as $atd) {
			$emp = $atd->fh_employee;
			if (!$emp) continue;
			$empId = $emp->emp_id;

			// travel count via plans starting that date
			$trpCount = TadaRequestPlan::where('trp_emp_id', $empId)
				->whereDate('trp_start_date', $date)->count();

			// fallback: travel details / locations
			$locCount = TadaRequestDetail::whereHas('fh_policy_tada_request_plan', function ($q) use ($empId, $date) {
				$q->where('trp_emp_id', $empId)->whereDate('trp_start_date', $date);
			})->count();

			$travelCount = max($trpCount, $locCount);

			$totalDistance = TadaRequestDetail::whereHas('fh_policy_tada_request_plan', function ($q) use ($empId, $date) {
				$q->where('trp_emp_id', $empId)->whereDate('trp_start_date', $date);
			})->sum('trd_total_distance');

			$geofenceActive = (int)($emp->emp_is_geofencing_active ?? 0);

			// include when geofencing OFF or when travel_count == 0
			if ($geofenceActive === 0 || $travelCount === 0) {
				$rows[] = [
					'Employee ID' => $emp->emp_code ?? $empId,
					'Employee Name' => $emp->emp_full_name ?? '---',
					'Department' => $emp->fh_department['d_name'] ?? '---',
					'Attendance Date' => date('d-m-Y', strtotime($atd->atd_date)),
					'Check In' => optional($atd->atd_check_in_time)->format('H:i:s') ?? '---',
					'Check Out' => optional($atd->atd_check_out_time)->format('H:i:s') ?? '---',
					'Geofencing Active' => $geofenceActive === 1 ? 'Yes' : 'No',
					'Travel Count' => $travelCount,
					'Total Travel Distance' => $totalDistance ?: 0,
					'Remarks' => $atd->atd_remark ?? '---'
				];
			}
		}

		if (empty($rows)) {
			if ($request->ajax()) {
				return response()->json(['status' => 'no_data', 'message' => 'No records found for the selected date'], 200);
			}
			Alert::warning('No Data', 'No records found for the selected date');
			return redirect()->back()->withInput();
		}

		$fileName = 'attendance_geofence_report_' . now()->format('Y-m-d') . '.xlsx';
		return Excel::download(new AttendanceGeofenceReport($rows), $fileName);
	}
}
