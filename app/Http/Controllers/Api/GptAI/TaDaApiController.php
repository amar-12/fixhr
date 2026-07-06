<?php

namespace App\Http\Controllers\Api\GptAI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TadaClaim;
use App\Http\Resources\Approval\Travel\ClaimRequestApiResource;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\TadaRequestPlan;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Resources\Request\FilterPlanRequestApiResource;
use App\Models\PolicyTadaTravelType;
use App\Http\Resources\MasterTableResource;
use App\Models\PolicyTadaDailyAllowance;
use App\Models\PolicyTadaTravelAllowance;
use Illuminate\Support\Facades\Log;

class TaDaApiController extends Controller
{
	// =========================================================================
	// SHARED HELPERS
	// =========================================================================

	/**
	 * Build a base Employee query with all supported filters.
	 *
	 * Supported filter keys (all optional):
	 *   emp_id, department_id, designation_id, branch_id,
	 *   role_id, grade_id, job_status_id, work_mode_id, search
	 */
	private function buildEmployeeQuery(int $businessId, array $filters)
	{
		$q = Employee::with('fh_role', 'fh_designation', 'fh_department')
			->where('emp_b_id', $businessId)
			->where('emp_role_id', '!=', 1)
			->select(
				'emp_id',
				'emp_full_name',
				'emp_code',
				'emp_role_id',
				'emp_dg_id',
				'emp_d_id',
				'emp_br_id',
				'emp_grade_id',
				'emp_job_status',
				'emp_work_mode_id'
			);

		if (!empty($filters['emp_id']))          $q->where('emp_id',           $filters['emp_id']);
		if (!empty($filters['department_id']))   $q->where('emp_d_id',         $filters['department_id']);
		if (!empty($filters['designation_id']))  $q->where('emp_dg_id',        $filters['designation_id']);
		if (!empty($filters['branch_id']))       $q->where('emp_br_id',        $filters['branch_id']);
		if (!empty($filters['role_id']))         $q->where('emp_role_id',      $filters['role_id']);
		if (!empty($filters['grade_id']))        $q->where('emp_grade_id',     $filters['grade_id']);
		if (!empty($filters['job_status_id']))   $q->where('emp_job_status',   $filters['job_status_id']);
		if (!empty($filters['work_mode_id']))    $q->where('emp_work_mode_id', $filters['work_mode_id']);

		// Free-text search on name or code
		if (!empty($filters['search'])) {
			$s = $filters['search'];
			$q->where(function ($sub) use ($s) {
				$sub->where('emp_full_name', 'like', "%{$s}%")
					->orWhere('emp_code',     'like', "%{$s}%");
			});
		}

		return $q;
	}

	/**
	 * Resolve a status name → m_id from APPROVAL_STATUS master table.
	 */
	private function resolveStatusId(?string $statusName): ?int
	{
		if (!$statusName) return null;
		$rec = MasterTable::where('m_group', 'APPROVAL_STATUS')
			->where('m_name', $statusName)
			->first();
		return $rec?->m_id;
	}

	/**
	 * Resolve a travel-type name → PolicyTadaTravelType ids for a given business.
	 */
	private function resolvePolicyTravelTypeIds(?string $travelTypeName, int $businessId): array
	{
		// dd($travelTypeName, $businessId);
		if (!$travelTypeName) return [];

		$master = MasterTable::where('m_group', 'TRAVEL_TYPE')
			->whereRaw('LOWER(m_name) = ?', [strtolower(trim($travelTypeName))])
			->select('m_id', 'm_name')
			->first();

		if (!$master) return [];

		return PolicyTadaTravelType::where('pttt_type_id', $master->m_id)
			->where('pttt_b_id', $businessId)
			->pluck('pttt_id')
			->toArray();
	}

	/**
	 * Apply a time / date-range filter to an Eloquent query builder.
	 *
	 * Priority: custom from_date+to_date  >  time_filter keyword  >  default (current month)
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $q
	 * @param string   $column     DB column to filter on
	 * @param ?string  $fromDate
	 * @param ?string  $toDate
	 * @param ?string  $timeFilter weekly|monthly|previous_month|quarterly|yearly|all_time
	 * @param bool     $applyDefaultMonth  When no filter supplied, fall back to current month
	 */
	private function applyDateFilter(
		$q,
		string $column,
		?string $fromDate,
		?string $toDate,
		?string $timeFilter,
		bool $applyDefaultMonth = true
	) {
		// Swap if caller passes dates in wrong order
		if ($fromDate && $toDate && $fromDate > $toDate) {
			[$fromDate, $toDate] = [$toDate, $fromDate];
		}

		if ($fromDate && $toDate) {
			return $q->whereBetween($column, [$fromDate, $toDate . ' 23:59:59']);
		}

		switch (strtolower((string) $timeFilter)) {
			case 'weekly':
				return $q->whereBetween($column, [
					now()->startOfWeek()->toDateString(),
					now()->endOfWeek()->toDateString() . ' 23:59:59',
				]);
			case 'monthly':
				return $q->whereMonth($column, now()->month)
					->whereYear($column, now()->year);
			case 'previous_month':
				$pm = now()->subMonth();
				return $q->whereMonth($column, $pm->month)
					->whereYear($column, $pm->year);
			case 'quarterly':
				$quarterStart = now()->firstOfQuarter()->toDateString();
				$quarterEnd   = now()->lastOfQuarter()->toDateString() . ' 23:59:59';
				return $q->whereBetween($column, [$quarterStart, $quarterEnd]);
			case 'yearly':
				return $q->whereYear($column, now()->year);
			case 'all_time':
				return $q; // no restriction
			default:
				if ($applyDefaultMonth) {
					return $q->whereMonth($column, now()->month)
						->whereYear($column, now()->year);
				}
				return $q;
		}
	}

	/**
	 * Apply pagination or return a full collection.
	 * Returns ['data', 'meta'] array.
	 */
	private function paginate($query, ?int $perPage, ?int $page): array
	{
		if ($perPage && $perPage > 0) {
			$paginated = $query->paginate($perPage, ['*'], 'page', $page ?? 1);
			return [
				'data' => $paginated->items(),
				'meta' => [
					'total'        => $paginated->total(),
					'per_page'     => $paginated->perPage(),
					'current_page' => $paginated->currentPage(),
					'last_page'    => $paginated->lastPage(),
				],
			];
		}

		$all = $query->get();
		return [
			'data' => $all->all(),
			'meta' => ['total' => $all->count()],
		];
	}

	// =========================================================================
	// 1. INDIVIDUAL TA/DA QUERIES  (unified stats endpoint)
	// =========================================================================
	/**
	 * GET /api/gpt/tada/individual-queries
	 *
	 * Filters
	 * -------
	 * Employee scope  : emp_id, department_id, designation_id, branch_id,
	 *                   role_id, grade_id, job_status_id, work_mode_id, search
	 * Date            : from_date, to_date  OR  time_filter
	 *                   (weekly|monthly|previous_month|quarterly|yearly|all_time)
	 * Status          : status  (approval status name or id)
	 * Travel type     : travel_type  (local|outstation|international)
	 * Amount          : amount_min, amount_max
	 * Sort            : sort_by (created_at|tc_amount|tc_deduction_amount),
	 *                   sort_order (asc|desc)
	 * Pagination      : per_page, page
	 */
	public function individualTadaQueries(Request $request)
	{
		$user       = Auth::user();
		$businessId = $user->emp_b_id;

		// ---- collect all filter inputs ----
		$empFilters  = $this->extractEmployeeFilters($request);
		$fromDate    = $request->input('from_date');
		$toDate      = $request->input('to_date');
		$timeFilter  = $request->input('time_filter');
		$statusInput = $request->input('status');
		$travelType  = $request->input('travel_type');
		$amountMin   = $request->input('amount_min');
		$amountMax   = $request->input('amount_max');
		$sortBy      = $request->input('sort_by', 'created_at');
		$sortOrder   = $request->input('sort_order', 'desc');
		$perPage     = (int) $request->input('per_page', 0);
		$page        = (int) $request->input('page', 1);

		// ---- employees ----
		$employees = $this->buildEmployeeQuery($businessId, $empFilters)->get()->keyBy('emp_id');

		if ($employees->isEmpty()) {
			return $this->emptyResponse('No employees found for the given filters.');
		}

		$empIds = $employees->keys();

		// ---- resolve status ----
		$statusId = is_numeric($statusInput)
			? (int) $statusInput
			: $this->resolveStatusId($statusInput);

		// ---- resolve policy travel type ids ----
		$policyTravelTypeIds = $this->resolvePolicyTravelTypeIds($travelType, $businessId);

		// ---- base claims query ----
		$claimQuery = TadaClaim::with([
			'fh_employee:emp_id,emp_full_name,emp_code,emp_role_id,emp_dg_id,emp_d_id',
			'fh_employee.fh_role:role_id,role_name',
			'fh_employee.fh_designation:dg_id,dg_name',
			'fh_employee.fh_department:d_id,d_name',
			'fh_tada_request_plan:trp_id,trp_unique_id,trp_start_date,trp_end_date,trp_start_time,trp_end_time,trp_request_status,trp_pttt_id,created_at',
			'fh_tada_request_plan.fh_tada_expenses.fh_expense_type:m_id,m_name',
			'fh_tada_request_plan.fh_policy_tada_travel_type:pttt_id,pttt_type_id,pttt_b_id',
			'fh_claim_status:m_id,m_name',
		])
			->select(
				'tc_id',
				'tc_unique_id',
				'tc_b_id',
				'tc_emp_id',
				'tc_status',
				'tc_deduction_amount',
				'tc_amount',
				'tc_claimed_amount',
				'tc_is_payed',
				'tc_paid_status',
				'created_at',
				'tc_remarks',
				'tc_trp_id'
			)
			->where('tc_b_id', $businessId)
			->whereIn('tc_emp_id', $empIds);

		// Date filter
		$claimQuery = $this->applyDateFilter($claimQuery, 'created_at', $fromDate, $toDate, $timeFilter, false);

		// Status filter
		if ($statusId) {
			$claimQuery->where('tc_status', $statusId);
		}

		// Travel type filter via plan ids
		if (!empty($policyTravelTypeIds)) {
			$travelPlanIds = TadaRequestPlan::where('trp_b_id', $businessId)
				->whereIn('trp_pttt_id', $policyTravelTypeIds)
				->pluck('trp_id');
			$claimQuery->whereIn('tc_trp_id', $travelPlanIds);
		}

		// Amount range
		if ($amountMin !== null) $claimQuery->where('tc_amount', '>=', $amountMin);
		if ($amountMax !== null) $claimQuery->where('tc_amount', '<=', $amountMax);

		// Sort
		$allowedSortCols = ['created_at', 'tc_amount', 'tc_deduction_amount', 'tc_claimed_amount'];
		$sortBy = in_array($sortBy, $allowedSortCols) ? $sortBy : 'created_at';
		$claimQuery->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

		// Paginate or get all
		$paged = $this->paginate($claimQuery, $perPage, $page);
		$claims = collect($paged['data']);

		if ($claims->isEmpty()) {
			return $this->emptyResponse('No travel expense reports found for the given filters.');
		}

		// --- aggregate stats (scoped to filtered empIds, no date restriction) ---
		$baseStats = TadaClaim::where('tc_b_id', $businessId)->whereIn('tc_emp_id', $empIds);
		$totalAllTime = (clone $baseStats)->count();
		$totalThisMonth = (clone $baseStats)->whereMonth('created_at', now()->month)
			->whereYear('created_at', now()->year)->count();
		$totalThisYear  = (clone $baseStats)->whereYear('created_at', now()->year)->count();

		$claimsByEmployee = $claims->groupBy(fn($c) => $c->fh_employee->emp_full_name ?? 'Unknown');
		$expenses = $this->getExpenseReportForAllEmployees($request, $employees, $businessId);

		return response()->json([
			'status'  => true,
			'message' => 'Travel expense report retrieved successfully.',
			'filters_applied' => $this->appliedFilters($request, $empFilters),
			'meta'    => $paged['meta'],
			'result'  => [
				'claims_by_employee'    => $claimsByEmployee,
				'expenses'              => $expenses,
				'claims_count'          => $claims->count(),
				'total_all_time'        => $totalAllTime,
				'total_this_month'      => $totalThisMonth,
				'total_this_year'       => $totalThisYear,
				'grand_total_amount'    => round($claims->sum('tc_amount'), 2),
				'grand_total_deduction' => round($claims->sum('tc_deduction_amount'), 2),
			],
		]);
	}

	// =========================================================================
	// 2. TRAVEL DETAILS  (rich list with all relations)
	// =========================================================================
	/**
	 * GET /api/gpt/tada/travel-details
	 *
	 * Filters
	 * -------
	 * Employee scope  : emp_id, department_id, designation_id, branch_id,
	 *                   role_id, grade_id, job_status_id, work_mode_id, search
	 * Date            : from_date, to_date  OR  time_filter
	 * Status          : status  (approval status name or id)
	 * Travel type     : travel_type (local|outstation|international)
	 * Travel purpose  : travel_purpose_id
	 * Sort            : sort_by (created_at|trp_start_date|trp_end_date),
	 *                   sort_order (asc|desc)
	 * Pagination      : per_page, page
	 */
	public function travelDetails(Request $request)
	{
		$user       = Auth::user();
		$businessId = $user->emp_b_id;

		$empFilters       = $this->extractEmployeeFilters($request);
		$fromDate         = $request->input('from_date');
		$toDate           = $request->input('to_date');
		$timeFilter       = $request->input('time_filter');
		$statusInput      = $request->input('status');
		$travelType       = $request->input('travel_type');
		$travelPurposeId  = $request->input('travel_purpose_id');
		$sortBy           = $request->input('sort_by', 'trp_id');
		$sortOrder        = $request->input('sort_order', 'desc');
		$perPage          = (int) $request->input('per_page', 0);
		$page             = (int) $request->input('page', 1);

		// ---- employees ----
		$employees = $this->buildEmployeeQuery($businessId, $empFilters)->get()->keyBy('emp_id');

		if ($employees->isEmpty()) {
			return $this->emptyResponse('No employees found for the given filters.');
		}

		$empIds = $employees->keys();

		// ---- resolve filters ----
		$statusId = is_numeric($statusInput)
			? (int) $statusInput
			: $this->resolveStatusId($statusInput);
		$policyTravelTypeIds = $this->resolvePolicyTravelTypeIds($travelType, $businessId);

		// ---- travel plan query ----
		$travelQuery = TadaRequestPlan::with([
			'fh_employee:emp_id,emp_full_name,emp_code,emp_role_id,emp_dg_id,emp_d_id',
			'fh_employee.fh_role:role_id,role_name',
			'fh_employee.fh_designation:dg_id,dg_name',
			'fh_employee.fh_department:d_id,d_name',
			'fh_approval_status:m_id,m_name',
			'fh_policy_tada_travel_type:pttt_id,pttt_approval_type_id,pttt_type_id',
			'fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
			'fh_travel_purpose:tp_id,tp_name',
			'fh_tada_advance_approval_log:adl_id,adl_trp_id,adl_requested_amount,adl_reimburse_amount,adl_approver_id,adl_remark,adl_module_id,adl_am_id,adl_request_status,adl_next_approver,adl_stage_completed',
			'fh_tada_request_details:trd_id,trd_trp_id,trd_name,trd_pttm_id,trd_pttv_id,trd_source,trd_destination,trd_start_date,trd_end_date,trd_documents,trd_segments,trd_start_time,trd_end_time,trd_total_distance,trd_total_tolerance,trd_call_id,trd_status,trd_remarks,trd_purpose,trd_ticket_type,trd_net_amount,trd_hotel_location,trd_type_id,trd_p_set_amount,trd_geo_work_active',
			'fh_tada_claim:tc_id,tc_trp_id,tc_amount,tc_deduction_amount,tc_deduction_status,tc_approved_date,tc_payment_date,tc_claimed_amount,tc_status,tc_stage_completed,tc_next_approver,tc_deduction_remarks,tc_remarks,tc_da_amount,tc_is_payed,transaction_date,reference_no,tc_payed_amount,tc_da_calculation_message,deleted_by,tc_paid_status,tc_group_claim',
		])
			->select(
				'trp_id',
				'trp_unique_id',
				'trp_b_id',
				'trp_emp_id',
				'trp_request_status',
				'trp_start_date',
				'trp_end_date',
				'trp_start_time',
				'trp_end_time',
				'created_at',
				'trp_pttt_id',
				'trp_tp_id'
			)
			->where('trp_b_id', $businessId)
			->whereIn('trp_emp_id', $empIds);

		// Date filter on start date
		$travelQuery = $this->applyDateFilter($travelQuery, 'trp_start_date', $fromDate, $toDate, $timeFilter, false);

		// Status filter
		if ($statusId) {
			$travelQuery->where('trp_request_status', $statusId);
		}

		// Travel type filter
		if (!empty($policyTravelTypeIds)) {
			$travelQuery->whereIn('trp_pttt_id', $policyTravelTypeIds);
		}

		// Travel purpose filter
		if ($travelPurposeId) {
			$travelQuery->where('trp_tp_id', $travelPurposeId);
		}

		// Sort
		$allowedSort = ['trp_id', 'created_at', 'trp_start_date', 'trp_end_date'];
		$sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'trp_id';
		$travelQuery->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

		// Paginate
		$paged  = $this->paginate($travelQuery, $perPage, $page);
		$claims = collect($paged['data']);

		if ($claims->isEmpty()) {
			return $this->emptyResponse('No travel records found for the given filters.');
		}

		// --- aggregate stats ---
		$base          = TadaRequestPlan::where('trp_b_id', $businessId)->whereIn('trp_emp_id', $empIds);
		$totalAllTime  = (clone $base)->count();
		$totalThisMonth = (clone $base)->whereMonth('created_at', now()->month)
			->whereYear('created_at', now()->year)->count();
		$totalThisYear  = (clone $base)->whereYear('created_at', now()->year)->count();

		// --- format records ---
		$formattedList = $claims->map(fn($travel) => $this->formatTravelRecord($travel))->values();

		return response()->json([
			'status'          => true,
			'message'         => 'Travel requests retrieved successfully.',
			'filters_applied' => $this->appliedFilters($request, $empFilters),
			'meta'            => $paged['meta'],
			'result'          => [
				'travels_list'           => $formattedList,
				'total_all_time'         => $totalAllTime,
				'total_this_month'       => $totalThisMonth,
				'total_this_year'        => $totalThisYear,
				'travels_count'          => $claims->count(),
			],
		]);
	}

	// =========================================================================
	// 3. TRAVEL DETAILS SHOW  (individual / self-view)
	// =========================================================================
	/**
	 * GET /api/gpt/tada/travel-details-show
	 *
	 * All previous filters PLUS:
	 *   travel_purpose_id, vehicle_id (te_pttv_id), mode_id (pttm_id),
	 *   time_filter (weekly|monthly|previous_month|quarterly|yearly|all_time)
	 */
	public function travelDetailsShow(Request $request)
	{
		$user       = Auth::user();
		$businessId = $user->emp_b_id;

		$travelType      = $request->input('travel_type');
		$toDate          = $request->input('to_date');
		$fromDate        = $request->input('from_date');
		$timeFilter      = $request->input('time_filter');
		$statusInput     = $request->input('status');
		$empId           = $request->input('emp_id');
		$travelPurposeId = $request->input('travel_purpose_id');
		$modeId          = $request->input('mode_id');
		$sortBy          = $request->input('sort_by', 'trp_id');
		$sortOrder       = $request->input('sort_order', 'desc');
		$perPage         = (int) $request->input('per_page', 0);
		$page            = (int) $request->input('page', 1);

		$query = TadaRequestPlan::with(
			'fh_policy_tada_travel_type.fh_travel_type',
			'fh_approval_status',
			'fh_travel_purpose',
			'fh_tada_claim'
		)
			->where('trp_b_id', $businessId)
			->where('trp_request_status', '!=', 156);

		// Access control
		if ($user->emp_role_id != 1) {
			$query->where('trp_emp_id', $user->emp_id);
		} elseif ($empId) {
			$query->where('trp_emp_id', $empId);
		}

		// Travel type
		if ($travelType) {
			$policyIds = $this->resolvePolicyTravelTypeIds($travelType, $businessId);
			if (!empty($policyIds)) {
				$query->whereIn('trp_pttt_id', $policyIds);
			}
		}

		// Travel mode
		if ($modeId) {
			$query->where('trp_pttm_id', $modeId);
		}

		// Travel purpose
		if ($travelPurposeId) {
			$query->where('trp_tp_id', $travelPurposeId);
		}

		// Date filter
		$query = $this->applyDateFilter($query, 'trp_start_date', $fromDate, $toDate, $timeFilter, false);

		// Status
		if ($statusInput) {
			$statusId = is_numeric($statusInput)
				? (int) $statusInput
				: $this->resolveStatusId($statusInput);
			if ($statusId) {
				$query->where('trp_request_status', $statusId);
			}
		}

		// Sort
		$allowedSort = ['trp_id', 'created_at', 'trp_start_date', 'trp_end_date'];
		$sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'trp_id';
		$query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

		$totalCount = $query->count();

		$paged = $this->paginate($query, $perPage, $page);
		$data  = collect($paged['data']);

		$data->each(function ($item) use ($totalCount) {
			$item->trp_type_id        = $item->fh_policy_tada_travel_type->fh_travel_type->m_id ?? null;
			$item->total_travel       = $totalCount;
			$item->travel_purpose_name = $item->fh_travel_purpose->tp_name ?? '';
		});

		return ReturnHelper::jsonApiReturn($data, FilterPlanRequestApiResource::class);
	}

	// =========================================================================
	// 4. CLAIM LIST
	// =========================================================================
	/**
	 * GET /api/gpt/tada/claims
	 *
	 * Filters
	 * -------
	 * Employee scope  : emp_id, department_id, designation_id, branch_id,
	 *                   role_id, grade_id, job_status_id, work_mode_id, search
	 * Date            : from_date, to_date  OR  filter / time_filter
	 *                   (weekly|monthly|previous_month|quarterly|yearly|all_time)
	 * Status          : status  (approval status name or id)
	 * Travel type     : travel_type
	 * Payment status  : payment_status (paid|unpaid|partially_paid)
	 * Deduction status: deduction_status (0|1)
	 * Amount range    : amount_min, amount_max
	 * Unique ID search: unique_id
	 * Sort            : sort_by, sort_order
	 * Pagination      : per_page, page
	 */
	public function claimList(Request $request)
	{
		$user       = Auth::user();
		$businessId = $user->emp_b_id;

		// Support both query params and JSON body
		$input = array_merge($request->json()->all(), $request->query());

		$empFilters     = $this->extractEmployeeFilters($request);
		$travelType     = $input['travel_type']      ?? null;
		$toDate         = $input['to_date']           ?? null;
		$fromDate       = $input['from_date']         ?? null;
		$timeFilter     = $input['time_filter']       ?? ($input['filter'] ?? null);
		$statusInput    = $input['status']            ?? null;
		$empId          = $input['emp_id']            ?? null;
		$paymentStatus  = $input['payment_status']    ?? null; // paid|unpaid|partially_paid
		$deductionStatus = $input['deduction_status']  ?? null; // 0|1
		$amountMin      = $input['amount_min']        ?? null;
		$amountMax      = $input['amount_max']        ?? null;
		$uniqueId       = $input['unique_id']         ?? null;
		$sortBy         = $input['sort_by']           ?? 'tc_id';
		$sortOrder      = $input['sort_order']        ?? 'desc';
		$perPage        = (int) ($input['per_page']   ?? 0);
		$page           = (int) ($input['page']       ?? 1);

		$query = TadaClaim::with('fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type')
			->where('tc_b_id', $businessId);

		// Access control
		if ($user->emp_role_id != 1) {
			$query->where('tc_emp_id', $user->emp_id);
		} else {
			// Employee scope filters (super-admin only)
			if ($empId || !empty(array_filter($empFilters))) {
				$empFilters['emp_id'] = $empId ?? $empFilters['emp_id'] ?? null;
				$empIds = $this->buildEmployeeQuery($businessId, $empFilters)
					->pluck('emp_id');
				$query->whereIn('tc_emp_id', $empIds);
			}
		}

		// Travel type
		if ($travelType) {
			$policyIds = $this->resolvePolicyTravelTypeIds($travelType, $businessId);
			if (!empty($policyIds)) {
				$travelPlanIds = TadaRequestPlan::where('trp_b_id', $businessId)
					->whereIn('trp_pttt_id', $policyIds)
					->pluck('trp_id');
				$query->whereIn('tc_trp_id', $travelPlanIds);
			}
		}

		// Date filter
		$hasDateFilter   = ($fromDate && $toDate) || $timeFilter;
		$hasOtherFilters = $travelType || $statusInput || $empId || !empty(array_filter($empFilters));
		$defaultMonth    = !$hasDateFilter && !$hasOtherFilters;
		$query = $this->applyDateFilter($query, 'created_at', $fromDate, $toDate, $timeFilter, $defaultMonth);

		// Approval status
		if ($statusInput) {
			$statusId = is_numeric($statusInput)
				? (int) $statusInput
				: $this->resolveStatusId($statusInput);
			if ($statusId) {
				$query->where('tc_status', $statusId);
			}
		}

		// Payment status
		if ($paymentStatus) {
			match (strtolower($paymentStatus)) {
				'paid'           => $query->where('tc_is_payed', 1),
				'unpaid'         => $query->where('tc_is_payed', 0),
				'partially_paid' => $query->where('tc_paid_status', 'partially_paid'),
				default          => null,
			};
		}

		// Deduction status
		if ($deductionStatus !== null) {
			$query->where('tc_deduction_status', (int) $deductionStatus);
		}

		// Amount range
		if ($amountMin !== null) $query->where('tc_amount', '>=', $amountMin);
		if ($amountMax !== null) $query->where('tc_amount', '<=', $amountMax);

		// Unique ID search
		if ($uniqueId) {
			$query->where('tc_unique_id', 'like', "%{$uniqueId}%");
		}

		// Sort
		$allowedSort = ['tc_id', 'created_at', 'tc_amount', 'tc_deduction_amount', 'tc_claimed_amount'];
		$sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'tc_id';
		$query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

		$totalCount = $query->count();
		$paged      = $this->paginate($query, $perPage, $page);
		$data       = collect($paged['data']);

		return ReturnHelper::jsonApiReturn(
			ClaimRequestApiResource::collection($data),
			$totalCount
		);
	}

	// =========================================================================
	// 5. EXPENSE CATEGORY LIST
	// =========================================================================
	/**
	 * GET /api/gpt/tada/expense-categories
	 *
	 * Filters
	 * -------
	 * Employee scope  : emp_id, department_id, designation_id, branch_id, ...
	 * Date            : from_date, to_date  OR  time_filter
	 * Travel type     : travel_type  (drives which expense types are shown)
	 * Expense type    : expense_type_id  (filter totals to a single type)
	 * Vehicle type    : vehicle_id  (te_pttv_id)
	 */
	public function expenseCategoryList(Request $request)
	{
		$user       = Auth::user();
		$businessId = $user->emp_b_id;

		$empFilters    = $this->extractEmployeeFilters($request);
		$travelTypeName = $request->input('travel_type');
		$empId         = $request->input('emp_id');
		$toDate        = $request->input('to_date');
		$fromDate      = $request->input('from_date');
		$timeFilter    = $request->input('time_filter');
		$expenseTypeId = $request->input('expense_type_id');
		$vehicleId     = $request->input('vehicle_id');

		// Build expense query base
		$expenseQuery = \App\Models\TadaExpense::with('fh_tada_request_plan', 'fh_expense_type');

		// Date filter
		$expenseQuery = $this->applyDateFilter($expenseQuery, 'te_from_date', $fromDate, $toDate, $timeFilter, false);

		// Expense type filter
		if ($expenseTypeId) {
			$expenseQuery->where('te_type_id', $expenseTypeId);
		}

		// Vehicle filter
		if ($vehicleId) {
			$expenseQuery->where('te_pttv_id', $vehicleId);
		}

		// Access / employee filters
		if ($user->emp_role_id != 1) {
			$expenseQuery->whereHas('fh_tada_request_plan', function ($q) use ($user) {
				$q->where('trp_emp_id', $user->emp_id);
			});
		} else {
			// For super-admin: apply employee scope filters
			$empFilters['emp_id'] = $empId ?? $empFilters['emp_id'] ?? null;
			if (!empty(array_filter($empFilters))) {
				$filteredEmpIds = $this->buildEmployeeQuery($businessId, $empFilters)->pluck('emp_id');
				$expenseQuery->whereHas('fh_tada_request_plan', function ($q) use ($filteredEmpIds) {
					$q->whereIn('trp_emp_id', $filteredEmpIds);
				});
			}
		}

		$currentMonthExpenses = $expenseQuery->get();

		// ---- DA / TA calculation (unchanged logic) ----
		$daExpenses = 0;
		$taExpenses = 0;

		$travelPlans = $currentMonthExpenses->pluck('fh_tada_request_plan')->unique('trp_id')->filter();
		foreach ($travelPlans as $plan) {
			if ($plan) {
				$daExpenses += $this->calculateDAAmount(
					$plan->trp_emp_id,
					$plan->trp_b_id ?? $businessId,
					$plan->trp_pttt_id,
					$plan->trp_ptc_id,
					$plan->trp_start_date,
					$plan->trp_end_date
				);

				$vId = $currentMonthExpenses
					->where('te_trp_id', $plan->trp_id)
					->whereNotNull('te_pttv_id')
					->groupBy('te_pttv_id')
					->map->count()->sortDesc()->keys()->first();

				$taExpenses += $this->calculateTAAmount(
					$plan->trp_emp_id,
					$plan->trp_b_id ?? $businessId,
					$plan->trp_pttt_id,
					$plan->trp_ptc_id,
					$plan->trp_pttm_id,
					$vId
				);
			}
		}

		// ---- Expense category type list (driven by travel_type) ----
		$expense_type = $this->resolveExpenseTypeList($travelTypeName);

		// ---- Per-type totals helper ----
		$sum = fn(int $typeId) => $currentMonthExpenses->where('te_type_id', $typeId)
			->sum(fn($e) => ($e->te_amount ?? 0) + ($e->te_taxes ?? 0));

		$sumByVehicle = fn(int $vId) => $currentMonthExpenses->where('te_pttv_id', $vId)
			->sum(fn($e) => ($e->te_amount ?? 0) + ($e->te_taxes ?? 0));

		$hotelExpenses        = $sum(158);
		$foodExpenses         = $sum(160);
		$travelExpenses       = $sum(159);
		$tollTaxExpenses      = $sum(608);
		$claimedDAExpenses    = $sum(586);
		$claimedTAExpenses    = $sum(585);
		$miscExpenseExpenses  = $sum(456);
		$miscellaneous        = $sum(161);

		$knownTypes = [158, 160, 159, 608, 586, 585, 456, 161];
		$otherExpenses = $currentMonthExpenses->whereNotIn('te_type_id', $knownTypes)
			->sum(fn($e) => ($e->te_amount ?? 0) + ($e->te_taxes ?? 0));

		$currency = $user->fh_business->b_currency ?? 'INR';

		$response = [
			'expense_categories' => MasterTableResource::collection($expense_type),
			'filters_applied' => [
				'travel_type'    => $travelTypeName,
				'from_date'      => $fromDate,
				'to_date'        => $toDate,
				'time_filter'    => $timeFilter,
				'expense_type_id' => $expenseTypeId,
				'vehicle_id'     => $vehicleId,
				'emp_id'         => $empId,
			],
			'currency' => $currency,
			'monthly_totals' => [
				'loading_expenses'                   => round($hotelExpenses, 2),
				'meals_expenses'                     => round($foodExpenses, 2),
				'travel_expenses'                    => round($travelExpenses, 2),
				'toll_tax_expenses'                  => round($tollTaxExpenses, 2),
				'da_expenses'                        => round($daExpenses, 2),
				'da_claimed_expenses'                => round($claimedDAExpenses, 2),
				'ta_expenses'                        => round($taExpenses, 2),
				'ta_claimed_expenses'                => round($claimedTAExpenses, 2),
				'misc_expense'                       => round($miscExpenseExpenses, 2),
				'miscellaneous_expenses'             => round($miscellaneous, 2),
				'other_expenses'                     => round($otherExpenses, 2),
				// Vehicle breakdown
				'train_travel_expenses'              => round($sumByVehicle(122), 2),
				'car_travel_expenses'                => round($sumByVehicle(8),   2),
				'bike_travel_expenses'               => round($sumByVehicle(109), 2),
				'bus_travel_expenses'                => round($sumByVehicle(112), 2),
				'cab_taxi_travel_expenses'           => round($sumByVehicle(111), 2),
				'auto_travel_expenses'               => round($sumByVehicle(110), 2),
				'flight_travel_expenses'             => round($sumByVehicle(123), 2),
				'metro_travel_expenses'              => round($sumByVehicle(599), 2),
				'bike_taxi_service_travel_expenses'  => round($sumByVehicle(600), 2),
				'total_expenses'                     => round(
					$hotelExpenses + $foodExpenses + $travelExpenses + $tollTaxExpenses +
						$daExpenses + $taExpenses + $miscExpenseExpenses + $miscellaneous + $otherExpenses,
					2
				),
				'date_range' => [
					'from_date' => $fromDate,
					'to_date'   => $toDate,
				],
			],
		];

		return ReturnHelper::jsonApiReturn($response);
	}

	// =========================================================================
	// 6. DISTANCE / KM BASED TRAVEL
	// =========================================================================
	/**
	 * GET /api/gpt/tada/distance-km
	 *
	 * Filters
	 * -------
	 * Employee scope  : emp_id, department_id, designation_id, branch_id, ...
	 * Date            : from_date, to_date  OR  time_filter
	 * Travel type     : travel_type
	 * Vehicle type    : vehicle_id  (te_pttv_id)
	 * Distance range  : distance_min, distance_max (km)
	 * Sort            : sort_by (te_date|trd_start_date), sort_order
	 * Pagination      : per_page, page
	 */
	public function distanceKmBasedTravel(Request $request)
	{
		$user       = Auth::user();
		$businessId = $user->emp_b_id;

		$empFilters  = $this->extractEmployeeFilters($request);
		$empId       = $request->input('emp_id');
		$toDate      = $request->input('to_date');
		$fromDate    = $request->input('from_date');
		$timeFilter  = $request->input('time_filter');
		$travelType  = $request->input('travel_type');
		$vehicleId   = $request->input('vehicle_id');
		$distanceMin = $request->input('distance_min');
		$distanceMax = $request->input('distance_max');
		$sortOrder   = $request->input('sort_order', 'desc');
		$perPage     = (int) $request->input('per_page', 0);
		$page        = (int) $request->input('page', 1);

		// --- resolve employee ids (super admin with scope filters) ---
		$filteredEmpIds = null;
		if ($user->emp_role_id == 1) {
			$empFilters['emp_id'] = $empId ?? $empFilters['emp_id'] ?? null;
			if (!empty(array_filter($empFilters))) {
				$filteredEmpIds = $this->buildEmployeeQuery($businessId, $empFilters)->pluck('emp_id');
			}
		}

		// --- resolve policy travel type ids ---
		$policyTravelTypeIds = $this->resolvePolicyTravelTypeIds($travelType, $businessId);

		// --- Expense query (te_type_id = 159, has km distance) ---
		$expenseQuery = \App\Models\TadaExpense::with('fh_tada_request_plan.fh_employee')
			->where('te_type_id', 159)
			->where('te_total_km_driven', '>', 0);

		// Date filter
		$expenseQuery = $this->applyDateFilter($expenseQuery, 'te_date', $fromDate, $toDate, $timeFilter, true);

		// Vehicle filter
		if ($vehicleId) {
			$expenseQuery->where('te_pttv_id', $vehicleId);
		}

		// Distance range filter (on expenses)
		if ($distanceMin !== null) $expenseQuery->where('te_total_km_driven', '>=', $distanceMin);
		if ($distanceMax !== null) $expenseQuery->where('te_total_km_driven', '<=', $distanceMax);

		// Employee scope
		if ($filteredEmpIds) {
			$expenseQuery->whereHas('fh_tada_request_plan', fn($q) => $q->whereIn('trp_emp_id', $filteredEmpIds));
		} elseif ($user->emp_role_id != 1) {
			$expenseQuery->whereHas('fh_tada_request_plan', fn($q) => $q->where('trp_emp_id', $user->emp_id));
		}

		// Travel type scope
		if (!empty($policyTravelTypeIds)) {
			$expenseQuery->whereHas('fh_tada_request_plan', fn($q) => $q->whereIn('trp_pttt_id', $policyTravelTypeIds));
		}

		// --- Request Detail query ---
		$requestDetailQuery = \App\Models\TadaRequestDetail::with('fh_policy_tada_request_plan.fh_employee')
			->whereNotNull('trd_total_distance')
			->where('trd_total_distance', '>', 0);

		$requestDetailQuery = $this->applyDateFilter($requestDetailQuery, 'trd_start_date', $fromDate, $toDate, $timeFilter, true);

		if ($distanceMin !== null) $requestDetailQuery->where('trd_total_distance', '>=', $distanceMin);
		if ($distanceMax !== null) $requestDetailQuery->where('trd_total_distance', '<=', $distanceMax);

		if ($filteredEmpIds) {
			$requestDetailQuery->whereHas('fh_policy_tada_request_plan', fn($q) => $q->whereIn('trp_emp_id', $filteredEmpIds));
		} elseif ($user->emp_role_id != 1) {
			$requestDetailQuery->whereHas('fh_policy_tada_request_plan', fn($q) => $q->where('trp_emp_id', $user->emp_id));
		}

		// Sort
		$expenseQuery->orderBy('te_date', $sortOrder === 'asc' ? 'asc' : 'desc');
		$requestDetailQuery->orderBy('trd_start_date', $sortOrder === 'asc' ? 'asc' : 'desc');

		// Paginate expenses
		$pagedExpenses = $this->paginate($expenseQuery, $perPage, $page);
		$distanceExpenses = collect($pagedExpenses['data']);
		$requestDetails   = $requestDetailQuery->get();

		$totalDistance = $distanceExpenses->sum('te_total_km_driven')
			+ $requestDetails->sum('trd_total_distance')
			+ $requestDetails->sum('trd_total_tolerance');

		return ReturnHelper::jsonApiReturn([
			'filters_applied' => [
				'from_date'    => $fromDate,
				'to_date'      => $toDate,
				'time_filter'  => $timeFilter,
				'travel_type'  => $travelType,
				'vehicle_id'   => $vehicleId,
				'distance_min' => $distanceMin,
				'distance_max' => $distanceMax,
				'emp_id'       => $empId,
			],
			'meta'             => $pagedExpenses['meta'],
			'total_distance'   => $totalDistance,
			'distance_expenses' => $distanceExpenses,
			'request_details'  => $requestDetails,
		], true);
	}

	// =========================================================================
	// PRIVATE HELPERS
	// =========================================================================

	/** Pull all employee-scope filter inputs from the request. */
	private function extractEmployeeFilters(Request $request): array
	{
		return [
			'emp_id'         => $request->input('emp_id'),
			'department_id'  => $request->input('department_id'),
			'designation_id' => $request->input('designation_id'),
			'branch_id'      => $request->input('branch_id'),
			'role_id'        => $request->input('role_id'),
			'grade_id'       => $request->input('grade_id'),
			'job_status_id'  => $request->input('job_status_id'),
			'work_mode_id'   => $request->input('work_mode_id'),
			'search'         => $request->input('search'),
		];
	}

	/** Build a "filters_applied" summary for response transparency. */
	private function appliedFilters(Request $request, array $empFilters): array
	{
		return array_filter(array_merge($empFilters, [
			'from_date'       => $request->input('from_date'),
			'to_date'         => $request->input('to_date'),
			'time_filter'     => $request->input('time_filter'),
			'status'          => $request->input('status'),
			'travel_type'     => $request->input('travel_type'),
			'amount_min'      => $request->input('amount_min'),
			'amount_max'      => $request->input('amount_max'),
		]), fn($v) => $v !== null && $v !== '');
	}

	/** Standard empty/not-found response. */
	private function emptyResponse(string $message): \Illuminate\Http\JsonResponse
	{
		return response()->json([
			'result'  => [],
			'status'  => false,
			'message' => $message,
		]);
	}

	/** Return expense type list scoped to travel type. */
	private function resolveExpenseTypeList(?string $travelTypeName)
	{
		$q = MasterTable::where('m_group', 'EXPENSE_TYPE');

		if ($travelTypeName) {
			$lower = strtolower($travelTypeName);
			if ($lower === 'local') {
				$q->whereNotIn('m_id', [158, 159, 456]);
			} elseif ($lower === 'outstation') {
				$q->whereNotIn('m_id', [160, 161]);
			}
			// international → all types
		}

		return $q->get();
	}

	/** Format a single TadaRequestPlan model into the API response shape. */
	private function formatTravelRecord($travel): array
	{
		return [
			'trp_id'                     => $travel->trp_id,
			'trp_unique_id'              => $travel->trp_unique_id,
			'trp_b_id'                   => $travel->trp_b_id,
			'trp_emp_id'                 => $travel->trp_emp_id,
			'trp_travel_purpose'         => $travel->fh_travel_purpose->tp_name ?? null,
			'trp_tada_request_details'   => $travel->fh_tada_request_details ?? [],
			'travel_type'                => $travel->fh_policy_tada_travel_type->fh_travel_type->m_name ?? null,
			'trp_request_status'         => $travel->trp_request_status,
			'approval_status'            => $travel->fh_approval_status->m_name ?? null,
			'trp_start_date'             => $travel->trp_start_date,
			'trp_end_date'               => $travel->trp_end_date,
			'trp_start_time'             => $travel->trp_start_time,
			'trp_end_time'               => $travel->trp_end_time,
			'created_at'                 => $travel->created_at,
			'employee'                   => [
				'emp_id'      => $travel->fh_employee->emp_id       ?? null,
				'emp_full_name' => $travel->fh_employee->emp_full_name ?? null,
				'emp_code'    => $travel->fh_employee->emp_code     ?? null,
				'role'        => $travel->fh_employee->fh_role->role_name        ?? null,
				'designation' => $travel->fh_employee->fh_designation->dg_name  ?? null,
				'department'  => $travel->fh_employee->fh_department->d_name    ?? null,
			],
			'travel_advance_approval_log' => $travel->fh_tada_advance_approval_log ?? [],
			'claims'                     => $travel->fh_tada_claim ?? [],
			'total_claims'               => $travel->fh_tada_claim?->count() ?? 0,
			'total_claims_month'         => $travel->fh_tada_claim?->where('created_at', '>=', now()->startOfMonth())->count() ?? 0,
			'total_claims_year'          => $travel->fh_tada_claim?->where('created_at', '>=', now()->startOfYear())->count() ?? 0,
			'total_claims_week'          => $travel->fh_tada_claim?->where('created_at', '>=', now()->startOfWeek())->count() ?? 0,
		];
	}

	private function getExpenseReportForAllEmployees($request, $employees, $businessId): array
	{
		$empIds = $employees->keys();

		$claims = TadaClaim::with([
			'fh_employee',
			'fh_tada_request_plan.fh_tada_expenses.fh_expense_type',
			'fh_tada_request_plan.fh_policy_tada_travel_type',
			'fh_claim_status',
		])
			->where('tc_b_id', $businessId)
			->whereIn('tc_emp_id', $empIds)
			->orderBy('tc_id', 'DESC')
			->get();

		if ($claims->isEmpty()) {
			return [
				'employee'     => ['emp_id' => 'all', 'emp_full_name' => 'All Employees', 'emp_code' => 'N/A'],
				'expense_report' => [],
				'summary'      => [
					'total_expense_types' => 0,
					'total_claims' => 0,
					'grand_total_amount' => 0,
					'grand_total_claimed' => 0
				],
			];
		}

		$expenseReport = [];
		foreach ($claims as $claim) {
			$expenses = $claim->fh_tada_request_plan->fh_tada_expenses ?? collect();
			foreach ($expenses as $expense) {
				$type = $expense->fh_expense_type->m_name ?? 'Unknown';
				if (!isset($expenseReport[$type])) {
					$expenseReport[$type] = ['type' => $type, 'total_amount' => 0, 'total_taxes' => 0, 'claims' => []];
				}
				$expenseReport[$type]['total_amount'] += $expense->te_amount ?? 0;
				$expenseReport[$type]['total_taxes']  += $expense->te_taxes  ?? 0;
				$expenseReport[$type]['claims'][] = [
					'claim_id'        => $claim->tc_id,
					'claim_unique_id' => $claim->tc_unique_id,
					'amount'          => $expense->te_amount ?? 0,
					'taxes'           => $expense->te_taxes  ?? 0,
					'date'            => $claim->created_at->format('Y-m-d'),
					'status'          => $claim->fh_claim_status->m_name ?? 'Unknown',
				];
			}
		}

		return [
			'employee'       => ['emp_id' => 'all', 'emp_full_name' => 'All Employees', 'emp_code' => 'N/A'],
			'expense_report' => array_values($expenseReport),
			'summary'        => [
				'total_expense_types'  => count($expenseReport),
				'total_claims'         => $claims->count(),
				'grand_total_amount'   => $claims->sum('tc_amount'),
				'grand_total_claimed'  => $claims->sum('tc_claimed_amount'),
			],
		];
	}

	// ---- DA / TA calculation (unchanged) ------------------------------------

	private function calculateDAAmount($employeeId, $businessId, $travelTypeId, $categoryId, $startDate, $endDate): float
	{
		try {
			$daPolicy = PolicyTadaDailyAllowance::where('ptda_b_id', $businessId)
				->where('ptda_ptc_id', $categoryId)
				->where('ptda_pttt_id', $travelTypeId)
				->first();

			if (!$daPolicy) return 0.0;

			$travelDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
			$daPerDay   = $daPolicy->ptda_da_amount ?? 0;
			$totalDA    = $daPerDay * $travelDays;

			if (($daPolicy->ptda_half_da ?? false) && $travelDays > 1) {
				$totalDA = $daPerDay * ($travelDays - 1) + ($daPerDay / 2);
			}

			return round($totalDA, 2);
		} catch (\Exception $e) {
			Log::error('DA Calculation Error: ' . $e->getMessage());
			return 0.0;
		}
	}

	private function calculateTAAmount($employeeId, $businessId, $travelTypeId, $categoryId, $travelModeId, $vehicleId): float
	{
		try {
			$taPolicy = PolicyTadaTravelAllowance::where('ptta_b_id', $businessId)
				->where('ptta_ptc_id', $categoryId)
				->where('ptta_pttt_id', $travelTypeId)
				->where('ptta_pttm_id', $travelModeId)
				->when($vehicleId, fn($q) => $q->where('ptta_pttv_id', $vehicleId))
				->first();

			if (!$taPolicy) {
				$taPolicy = PolicyTadaTravelAllowance::where('ptta_b_id', $businessId)
					->where('ptta_ptc_id', $categoryId)
					->where('ptta_pttt_id', $travelTypeId)
					->where('ptta_pttm_id', $travelModeId)
					->first();
			}

			return $taPolicy ? round($taPolicy->ptta_eligibility ?? 0, 2) : 0.0;
		} catch (\Exception $e) {
			Log::error('TA Calculation Error: ' . $e->getMessage());
			return 0.0;
		}
	}
}
