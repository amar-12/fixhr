<?php

namespace App\Http\Controllers\Api\GptAI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TadaClaim;
use App\Http\Resources\Approval\Travel\ClaimRequestApiResource;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Helpers\CentralLogics;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\AttendanceLog;
use App\Models\AttendanceException;
use App\Models\LeaveRequest;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use App\Models\MasterTable;
use App\Models\TadaRequestPlan;
use ChandraHemant\HtkcUtils\ReturnHelper;
use ChandraHemant\HtkcUtils\PaginatedResource;
use App\Http\Resources\Request\FilterPlanRequestApiResource;
use App\Models\PolicyTadaTravelType;
use App\Http\Resources\MasterTableResource;
use App\Models\TadaRequestDetail;

class TaDaApiController extends Controller
{
    /**
     * Unified TA/DA Queries API - Single endpoint for all individual TA/DA queries
     */
    public function individualTadaQueries(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        // $employeeName = $request->input('employee_name');
        $queryType = $request->input('query_type', 'all');
        $month = $request->input('month');
        $year = $request->input('year');
        $status = $request->input('status');

        // ================= EMPLOYEES =================
        $employee = Employee::with('fh_role', 'fh_designation', 'fh_department')->where('emp_b_id', $businessId)
            ->where('emp_role_id', '!=', 1)
            ->select('emp_id','emp_full_name','emp_code')
            ->get()
            ->keyBy('emp_id');    
    

        if ($employee->isEmpty()) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'No employees found.'
            ]);
        }

        $empIds = $employee->keys();

          // ================= MASTER CACHE =================
        $masters = MasterTable::where('m_group','APPROVAL_STATUS')->where('m_id',$status)
            ->get()->keyBy('m_id');

        $statusName = $masters->get($status)?->m_name;

        // TA/DA claim list
        $claims = TadaClaim::with([
                'fh_employee:emp_id,emp_full_name,emp_code,emp_role_id,emp_dg_id,emp_d_id',
                'fh_employee.fh_role:role_id,role_name',
                'fh_employee.fh_designation:dg_id,dg_name',
                'fh_employee.fh_department:d_id,d_name',
                'fh_tada_request_plan:trp_id,trp_unique_id,trp_start_date,trp_end_date,trp_start_time,trp_end_time,trp_request_status,created_at',
                'fh_tada_request_plan.fh_tada_expenses.fh_expense_type:m_id,m_name',
                'fh_tada_request_plan.fh_policy_tada_travel_type:m_id,m_name',
                'fh_claim_status:m_id,m_name'
            ])
            ->select('tc_id','tc_unique_id','tc_b_id','tc_emp_id','tc_status','tc_deduction_amount','tc_amount','created_at','tc_remarks','tc_trp_id')
            ->where('tc_b_id', $businessId)
            ->whereIn('tc_emp_id', $empIds)
            ->get()
            ->keyBy('tc_id');
          
        if ($status) {
            $claims = $claims->where('tc_status', $status);
        }

        if ($claims->isEmpty()) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'No travel expense reports found.'
            ]);
        }

        // travel claims this month.
        $thisMonthClaims = TadaClaim::where('tc_b_id', $businessId)
            ->whereIn('tc_emp_id', $empIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get()
            ->keyBy('tc_id');

            
        // travel claims this year.
        $thisYearClaims = TadaClaim::where('tc_b_id', $businessId)
            ->whereIn('tc_emp_id', $empIds)
            ->whereYear('created_at', now()->year)
            ->get()
            ->keyBy('tc_id');
            

        // travel claims count
        $claimsCount = $claims->count();

        // travel claims total.    
        $totalClaims = TadaClaim::where('tc_b_id', $businessId)
            ->whereIn('tc_emp_id', $empIds)
            ->get()
            ->keyBy('tc_id');

        //according to employee name , emp code , emp full name with show claim list   
        $claimsByEmployee = $claims->groupBy('fh_employee.emp_full_name');
       
        // travel expense list call this getExpenseReport()
        $expenses = $this->getExpenseReportForAllEmployees($request, $employee, $businessId);

        // last travel claim
        $lastClaim = $claims->first();

        return response()->json([

            'result' => [
                'claims_by_employee' => $claimsByEmployee,
                'expenses' => $expenses,
                'this_month_claims' => $thisMonthClaims,
                'this_year_claims' => $thisYearClaims,
                'total_claims' => $totalClaims,
                'claims_count' => $claimsCount,
            ],
            'status' => true,
            'message' => 'Travel expense report retrieved successfully'
        ]);
        
    }

    private function getExpenseReportForAllEmployees($request, $employees, $businessId)
    {
        $empIds = $employees->keys();
        
        $claims = TadaClaim::with([
                'fh_employee',
                'fh_tada_request_plan.fh_tada_expenses.fh_expense_type',
                'fh_tada_request_plan.fh_policy_tada_travel_type',
                'fh_claim_status'
            ])
            ->where('tc_b_id', $businessId)
            ->whereIn('tc_emp_id', $empIds)
            ->orderBy('tc_id', 'DESC')
            ->get();

        if ($claims->isEmpty()) {
            return [
                'employee' => [
                    'emp_id' => 'all',
                    'emp_full_name' => 'All Employees',
                    'emp_code' => 'N/A',
                ],
                'expense_report' => [],
                'summary' => [
                    'total_expense_types' => 0,
                    'total_claims' => 0,
                    'grand_total_amount' => 0,
                    'grand_total_claimed' => 0,
                ]
            ];
        }

        // Group expenses by type
        $expenseReport = [];
        foreach ($claims as $claim) {
            $expenses = $claim->fh_tada_request_plan->fh_tada_expenses ?? collect();
            
            foreach ($expenses as $expense) {
                $expenseType = $expense->fh_expense_type->m_name ?? 'Unknown';
                if (!isset($expenseReport[$expenseType])) {
                    $expenseReport[$expenseType] = [
                        'type' => $expenseType,
                        'total_amount' => 0,
                        'total_taxes' => 0,
                        'claims' => []
                    ];
                }
                
                $expenseReport[$expenseType]['total_amount'] += $expense->te_amount ?? 0;
                $expenseReport[$expenseType]['total_taxes'] += $expense->te_taxes ?? 0;
                $expenseReport[$expenseType]['claims'][] = [
                    'claim_id' => $claim->tc_id,
                    'claim_unique_id' => $claim->tc_unique_id,
                    'amount' => $expense->te_amount ?? 0,
                    'taxes' => $expense->te_taxes ?? 0,
                    'date' => $claim->created_at->format('Y-m-d'),
                    'status' => $claim->fh_claim_status->m_name ?? 'Unknown'
                ];
            }
        }

        return [
            'employee' => [
                'emp_id' => 'all',
                'emp_full_name' => 'All Employees',
                'emp_code' => 'N/A',
            ],
            'expense_report' => array_values($expenseReport),
            'summary' => [
                'total_expense_types' => count($expenseReport),
                'total_claims' => $claims->count(),
                'grand_total_amount' => $claims->sum('tc_amount'),
                'grand_total_claimed' => $claims->sum('tc_claimed_amount'),
            ]
        ];
    }


    //Travel Request Queries create api
    public function travelDetails(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        
        $queryType = $request->input('query_type', 'all');
        $month = $request->input('month');
        $year = $request->input('year');
        $status = $request->input('status');
        $employeeId = $request->input('employee_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $travelType = $request->input('travel_type'); // local, outstation, international

        // ================= EMPLOYEES =================
        $employee = Employee::with('fh_role', 'fh_designation', 'fh_department')->where('emp_b_id', $businessId)
            ->where('emp_role_id', '!=', 1)
            ->select('emp_id','emp_full_name','emp_code')
            ->get()
            ->keyBy('emp_id');    
    
        if ($employee->isEmpty()) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'No employees found.'
            ]);
        }

        $empIds = $employee->keys();

          // ================= MASTER CACHE =================
        $masters = MasterTable::where('m_group','APPROVAL_STATUS')->where('m_id',$status)
            ->get()->keyBy('m_id');

        $statusName = $masters->get($status)?->m_name;

        // TA/DA travel list with date filtering
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
                'fh_tada_claim:tc_id,tc_trp_id,tc_amount,tc_deduction_amount,tc_deduction_status,tc_approved_date,tc_payment_date,tc_claimed_amount,tc_status,tc_stage_completed,tc_next_approver,tc_deduction_remarks,tc_remarks,tc_da_amount,tc_is_payed,transaction_date,reference_no,tc_payed_amount,tc_da_calculation_message,deleted_by,tc_paid_status,tc_group_claim'
            ])
            ->select('trp_id','trp_unique_id','trp_b_id','trp_emp_id','trp_request_status','trp_start_date','trp_end_date','trp_start_time','trp_end_time','created_at','trp_pttt_id')
            ->where('trp_b_id', $businessId)
            ->whereIn('trp_emp_id', $empIds);
        
        // Add date filtering if provided
        if ($fromDate && $toDate) {
            $travelQuery = $travelQuery->whereBetween('trp_start_date', [$fromDate, $toDate])
                                      ->whereBetween('trp_end_date', [$fromDate, $toDate]);
        }

        // Add travel type filtering if provided
        if ($travelType && in_array($travelType, ['local', 'outstation', 'international'])) {
            $travelTypeRecord = MasterTable::where('m_group', 'TRAVEL_TYPE')
                                        ->where('m_name', $travelType)
                                        ->first();
            
            if ($travelTypeRecord) {
                $travelQuery = $travelQuery->where('trp_pttt_id', $travelTypeRecord->m_id);
            }
        }
        
        $claims = $travelQuery->get()->keyBy('trp_id');
          
        if ($status) {
            $claims = $claims->where('trp_request_status', $status);
        }

        if ($claims->isEmpty()) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'No travel expense reports found.'
            ]);
        }

        // travel claims this month count.
        $thisMonthTravelsCount = TadaRequestPlan::where('trp_b_id', $businessId)
            ->whereIn('trp_emp_id', $empIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

            
        // travel claims this year count.
        $thisYearTravelsCount = TadaRequestPlan::where('trp_b_id', $businessId)
            ->whereIn('trp_emp_id', $empIds)
            ->whereYear('created_at', now()->year)
            ->count();
            

        // travel claims count
        $travelsCount = $claims->count();

        // travel claims total count.    
        $totalTravelsCount = TadaRequestPlan::where('trp_b_id', $businessId)
            ->whereIn('trp_emp_id', $empIds)
            ->count();

        //according to employee name , emp code , emp full name with show claim list   
        $employeeTravels = $claims->groupBy('fh_employee.emp_full_name');
        
        // Format the employee travels data
        $formattedEmployeeTravels = [];
        foreach ($employeeTravels as $employeeName => $travels) {
            $formattedEmployeeTravels = array_merge($formattedEmployeeTravels, $travels->map(function ($travel) {
                return [
                    'trp_id' => $travel->trp_id,
                    'trp_unique_id' => $travel->trp_unique_id,
                    'trp_b_id' => $travel->trp_b_id,
                    'trp_emp_id' => $travel->trp_emp_id,
                    'trp_travel_purpose' => $travel->fh_travel_purpose->tp_name ?? null,
                    'trp_tada_request_details' => $travel->fh_tada_request_details ?? [],
                    'travel_type' => $travel->fh_policy_tada_travel_type->fh_travel_type->m_name ?? null,
                    'trp_request_status' => $travel->trp_request_status,
                    'trp_start_date' => $travel->trp_start_date,
                    'trp_end_date' => $travel->trp_end_date,
                    'trp_start_time' => $travel->trp_start_time,
                    'trp_end_time' => $travel->trp_end_time,
                    'created_at' => $travel->created_at,
                    'employee' => [
                        'emp_id' => $travel->fh_employee->emp_id ?? null,
                        'emp_full_name' => $travel->fh_employee->emp_full_name ?? null,
                        'emp_code' => $travel->fh_employee->emp_code ?? null,
                        'role' => $travel->fh_employee->fh_role->role_name ?? null,
                        'designation' => $travel->fh_employee->fh_designation->dg_name ?? null,
                        'department' => $travel->fh_employee->fh_department->d_name ?? null,
                    ],
                    'travel_advance_approval_log' => $travel->fh_tada_advance_approval_log ?? [],
                    'travel_details' => $travel->trp_tada_request_details ?? [],
                    'approval_status' => $travel->fh_approval_status->m_name ?? null,
                    'claims' => $travel->fh_tada_claim ?? [],
                    'total_claims' => $travel->fh_tada_claim?->count() ?? 0,
                    'total_claims_month' => $travel->fh_tada_claim?->where('created_at', '>=', now()->startOfMonth())->count() ?? 0,
                    'total_claims_year' => $travel->fh_tada_claim?->where('created_at', '>=', now()->startOfYear())->count() ?? 0,
                    'total_claims_week' => $travel->fh_tada_claim?->where('created_at', '>=', now()->startOfWeek())->count() ?? 0,
                ];
            })->toArray());
        }
       
        if($employeeTravels->isEmpty()){
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'No travel records found.'
            ]);
        }

        return response()->json([
            'result' => [
                'travels_list' => $formattedEmployeeTravels ?: [],
                'this_month_travels_count' => $thisMonthTravelsCount,
                'this_year_travels_count' => $thisYearTravelsCount,
                'total_travels_count' => $totalTravelsCount,
                'travels_count' => $travelsCount,
            ],
            'status' => true,
            'message' => 'Travel requests retrieved successfully'
        ]);
    }

    //Travel Request Queries show api only 
    public function travelDetailsShow(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $travelType = $request->input('travel_type');
        $to_date   = $request->input('to_date');
        $from_date = $request->input('from_date');
        $status = $request->input('status');
        $empId = $request->input('emp_id');
        //set montly, weekly, yearly, all time filters
        $timeFilter = $request->input('time_filter');
        

        $query = TadaRequestPlan::with('fh_policy_tada_travel_type.fh_travel_type', 'fh_approval_status')->where('trp_b_id', $businessId)
            ->where('trp_request_status', '!=', 156)
            ->orderBy('trp_id', 'DESC');

        
        if ($travelType) {
            $travelTypeRecord = MasterTable::where('m_group', 'TRAVEL_TYPE')
                                        ->where('m_name', $travelType)
                                        ->first();
            if ($travelTypeRecord) {
                // Get PolicyTadaTravelType records that match this travel type pttt_type_id and pttt_b_id
                $policyTravelTypes = PolicyTadaTravelType::where('pttt_type_id', $travelTypeRecord->m_id)->where('pttt_b_id', $businessId)
                                                        ->pluck('pttt_id');
                
                if ($policyTravelTypes->isNotEmpty()) {
                    $query = $query->whereIn('trp_pttt_id', $policyTravelTypes);
                }
            }
        }
        
        // Super admin can see all travel requests, regular users see only their own
        if ($user->emp_role_id != 1) {
            $query = $query->where('trp_emp_id', $user->emp_id);
        }

        // Add date range filter if provided trp_start_date and trp_end_date
        if ($to_date && $from_date) {
            $query = $query->where(function($q) use ($from_date, $to_date) {
                $q->whereBetween('trp_start_date', [$from_date, $to_date])
                  ->orWhereBetween('trp_end_date', [$from_date, $to_date])
                  ->orWhere(function($subQ) use ($from_date, $to_date) {
                      $subQ->where('trp_start_date', '<=', $from_date)
                           ->where('trp_end_date', '>=', $to_date);
                  });
            });
        }

        if ($status) {
            $statusRecord = MasterTable::where('m_group', 'APPROVAL_STATUS')
                                      ->where('m_name', $status)
                                      ->first();
            
            if ($statusRecord) {
                $query = $query->where('trp_request_status', $statusRecord->m_id);
            }
        }

        if($empId){
            $query = $query->where('trp_emp_id', $empId);
        }

        if($timeFilter){
            // monthly, weekly, yearly, all time
            switch($timeFilter){
                case 'monthly':
                    $query = $query->whereMonth('trp_start_date', now()->month);
                    break;
                case 'weekly':
                    $query = $query->whereBetween('trp_start_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'yearly':
                    $query = $query->whereYear('trp_start_date', now()->year);
                    break;
                case 'all_time':
                    // No filter needed
                    break;
            }
        }

        // add response total count accoding to serach and default and set serach by emp_id
        $totalCount = $query->count();
       
        //add total travel in response
        $data = $query->get();
        // $data->each(function ($item) use ($totalCount) {
        //     $item->trp_pttt_id = $item->fh_policy_tada_travel_type->fh_travel_type->m_id;
        //     $item->total_travel = $totalCount;
        // });

         $data->each(function ($item) use ($totalCount) {
            $item->trp_pttt_id = $item->fh_policy_tada_travel_type->fh_travel_type->m_id;
            $item->total_travel = $totalCount;
            // Set travel purpose name
            if ($item->fh_travel_purpose) {
                $item->travel_purpose_name = $item->fh_travel_purpose->tp_name ?? '';
            } else {
                $item->travel_purpose_name = '';
            }
        });

        return ReturnHelper::jsonApiReturn($data, FilterPlanRequestApiResource::class);
    }
    

    public function claimList(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        
        // Support both query parameters and JSON input
        $jsonData = $request->json()->all();
        $travelType = $request->input('travel_type') ?? ($jsonData['travel_type'] ?? null);
        $to_date   = $request->input('to_date') ?? ($jsonData['to_date'] ?? null);
        $from_date = $request->input('from_date') ?? ($jsonData['from_date'] ?? null);
        $status = $request->input('status') ?? ($jsonData['status'] ?? null);
        $empId = $request->input('emp_id') ?? ($jsonData['emp_id'] ?? null);
        $filter = $request->input('filter') ?? ($jsonData['filter'] ?? null); //set default as current month
        
        $query = TadaClaim::with('fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type')->where('tc_b_id', $businessId)
            ->orderBy('tc_id', 'DESC');

        // Add travel type filter only if provided
        if ($travelType) {
            $travelTypeRecord = MasterTable::where('m_group', 'TRAVEL_TYPE')
                                        ->where('m_name', $travelType)
                                        ->first();
                                        
            if ($travelTypeRecord) {
                // Get PolicyTadaTravelType records that match this travel type pttt_type_id and pttt_b_id
                $policyTravelTypes = PolicyTadaTravelType::where('pttt_type_id', $travelTypeRecord->m_id)->where('pttt_b_id', $businessId)
                                                        ->pluck('pttt_id');
                if ($policyTravelTypes->isNotEmpty()) {
                    // Get travel plans with these policy travel types
                    $travelPlanIds = TadaRequestPlan::where('trp_b_id', $businessId)
                                                    ->whereIn('trp_pttt_id', $policyTravelTypes)
                                                    ->pluck('trp_pttt_id')
                                                    ->toArray();
                    
                    if (!empty($travelPlanIds)) {
                        $query = $query->whereIn('tc_trp_id', $travelPlanIds);
                    }
                }
            }
        }
        
        // Super admin can see all claims, regular users see only their own
        if ($user->emp_role_id != 1) {
            $query = $query->where('tc_emp_id', $user->emp_id);
        }

        // Add date range filter if provided, otherwise use filter parameter
        if ($to_date && $from_date && $to_date !== '' && $from_date !== '') {
            // Validate and swap dates if needed (from_date should be earlier than to_date)
            if ($from_date > $to_date) {
                $temp = $from_date;
                $from_date = $to_date;
                $to_date = $temp;
            }
            
            // Use custom date range (highest priority)
            $query = $query->whereBetween('created_at', [$from_date, $to_date]);
        } elseif ($filter && $filter !== '') {
            // Use filter parameter if no custom dates provided
            switch (strtolower($filter)) {
                case 'weekly':
                    $query = $query->whereBetween('created_at', [
                        now()->startOfWeek()->format('Y-m-d'),
                        now()->endOfWeek()->format('Y-m-d')
                    ]);
                    break;
                case 'monthly':
                    $query = $query->whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year);
                    break;
                case 'previous_month':
                    $query = $query->whereMonth('created_at', now()->subMonth()->month)
                                   ->whereYear('created_at', now()->subMonth()->year);
                    break;
                case 'yearly':
                    $query = $query->whereYear('created_at', now()->year);
                    break;
                default:
                    // Default to show all data if filter is invalid
                    break;
            }
        } elseif ($travelType || $status || $empId) {
            // If other filters are applied but no date filter, show all data
            // No date restriction applied
        } else {
            // Default to current month if no filters at all
            $query = $query->whereMonth('created_at', now()->month)
                           ->whereYear('created_at', now()->year);
        }

        // Add status filter if provided
        if ($status) {
            $statusRecord = MasterTable::where('m_group', 'APPROVAL_STATUS')
                                      ->where('m_name', $status)
                                      ->first();
            
            if ($statusRecord) {
                $query = $query->where('tc_status', $statusRecord->m_id);
            }
        }

        // Add employee filter if provided
        if($empId){
            $query = $query->where('tc_emp_id', $empId);
        }
        

        // Add total count
        $totalCount = $query->count();
       
        $data = $query->get();
        
        return ReturnHelper::jsonApiReturn(ClaimRequestApiResource::collection($data), $totalCount);
    }

    // Expense Category Queries 
    public function expenseCategoryList(Request $request)
    {
        $user = Auth::user();
        $travelTypeName = $request->input('travel_type');
        $empId = $request->input('emp_id'); // Get emp_id from request

        //to date and form date
        $toDate = $request->input('to_date');
        $fromDate = $request->input('from_date');
        //set serach by to super admin or user
        $searchBy = $user->emp_role_id == 1 ? null : $user->emp_id;

        // Get current month's expenses for the user or super admin than show all expenses
        $expenseQuery = \App\Models\TadaExpense::with('fh_tada_request_plan', 'fh_expense_type')
        ->when($fromDate && $toDate, function($query) use ($fromDate, $toDate) {
            $query->whereBetween('te_from_date', [$fromDate, $toDate]);
        });

        // Apply user filter - super admin sees all, regular users see their own
        if ($user->emp_role_id != 1) {
            $expenseQuery->whereHas('fh_tada_request_plan', function($query) use ($user) {
                $query->where('trp_emp_id', $user->emp_id);
            });
        }

        // Add employee filter if provided (for super admin)
        if($empId && $user->emp_role_id == 1){
            $expenseQuery->whereHas('fh_tada_request_plan', function($query) use ($empId) {
                $query->where('trp_emp_id', $empId);
            });
        }

        $currentMonthExpenses = $expenseQuery->get();

        // Calculate DA and TA amounts based on employee travel eligibility
        $daExpenses = 0;
        $taExpenses = 0;
        
        // Get unique travel plans from expenses to calculate eligibility
        $travelPlans = $currentMonthExpenses->pluck('fh_tada_request_plan')->unique('trp_id')->filter();
        
        foreach ($travelPlans as $plan) {
            if ($plan) {
                $employeeId = $plan->trp_emp_id;
                $businessId = $plan->trp_b_id ?? $user->fh_business->b_id;
                $travelTypeId = $plan->trp_pttt_id;
                $categoryId = $plan->trp_ptc_id;
                $travelModeId = $plan->trp_pttm_id;
                $startDate = $plan->trp_start_date;
                $endDate = $plan->trp_end_date;
                
                // Calculate DA for this travel plan
                $daExpenses += $this->calculateDAAmount($employeeId, $businessId, $travelTypeId, $categoryId, $startDate, $endDate);
                
                // Calculate TA for this travel plan (get most common vehicle from expenses)
                $vehicleId = $currentMonthExpenses
                    ->where('te_trp_id', $plan->trp_id)
                    ->where('te_pttv_id', '!=', null)
                    ->groupBy('te_pttv_id')
                    ->map->count()
                    ->sortDesc()
                    ->keys()
                    ->first();
                    
                $taExpenses += $this->calculateTAAmount($employeeId, $businessId, $travelTypeId, $categoryId, $travelModeId, $vehicleId);
            }
        }

        // Calculate totals by expense type
        $hotelExpenses = $currentMonthExpenses->where('te_type_id', 158)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Loading expenses
        });

        $foodExpenses = $currentMonthExpenses->where('te_type_id', 160)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Meals expenses
        });

        $travelExpenses = $currentMonthExpenses->where('te_type_id', 159)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Travel expenses
        });

        $tollTaxExpenses = $currentMonthExpenses->where('te_type_id', 608)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Toll and tax expenses
        });

        // Keep existing DA/TA expense calculations as fallback (actual claimed amounts)
        $claimedDAExpenses = $currentMonthExpenses->where('te_type_id', 586)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Claimed DA expenses
        });

        $claimedTAExpenses = $currentMonthExpenses->where('te_type_id', 585)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Claimed TA expenses
        });

        $miscExpenseExpenses = $currentMonthExpenses->where('te_type_id', 456)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Misc expense
        });

        $miscellaneousExpenses = $currentMonthExpenses->where('te_type_id', 161)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Miscellaneous expenses
        });

        $otherExpenses = $currentMonthExpenses->whereNotIn('te_type_id', [158, 160, 159, 608, 586, 585, 456, 161])->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Other expenses
        });

        // Get expense categories based on travel type
        if ($travelTypeName) {
            if ($travelTypeName == 'local' || $travelTypeName == 'Local') {
                $expense_type = MasterTable::where('m_group', 'EXPENSE_TYPE')
                    ->whereNotIn('m_id', [158, 159, 456]) 
                    ->get();
            } elseif ($travelTypeName == 'outstation' || $travelTypeName == 'Outstation') {
                $expense_type = MasterTable::where('m_group', 'EXPENSE_TYPE')
                    ->whereNotIn('m_id', [160, 161]) 
                    ->get();
            } elseif ($travelTypeName == 'international' || $travelTypeName == 'International') {
                $expense_type = MasterTable::where('m_group', 'EXPENSE_TYPE')->get();
            } else {
                $expense_type = MasterTable::where('m_group', 'EXPENSE_TYPE')->get();
            }
        } else {
            $expense_type = MasterTable::where('m_group', 'EXPENSE_TYPE')->get();
        }
        
        $currency = $user->fh_business->b_currency ?? 'INR'; // or get from business settings
        // Prepare response with expense categories and monthly totals

        // add total expense in bike , car and other travel types
        $totalTravelExpenses = $travelExpenses + $tollTaxExpenses;
        
        //  Show train travel expenses - filter by te_pttv_id (vehicle)
        $trainTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 122)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Train travel expenses
        });

        // Show car travel expenses - filter by te_pttv_id (vehicle)
        $carTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 8)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Car travel expenses
        });

        // show bike travel expenses - filter by te_pttv_id (vehicle)
        $bikeTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 109)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Bike travel expenses
        });

        // show bus travel expenses - filter by te_pttv_id (vehicle)
        $busTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 112)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Bus travel expenses
        });

        // show cab/taxi travel expenses - filter by te_pttv_id (vehicle)
        $cabTaxiTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 111)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Cab/Taxi travel expenses
        });

        // show auto travel expenses - filter by te_pttv_id (vehicle)
        $autoTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 110)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Auto travel expenses
        });

        // show flight travel expenses - filter by te_pttv_id (vehicle)
        $flightTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 123)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Flight travel expenses
        });

        // show metro travel expenses - filter by te_pttv_id (vehicle)
        $metroTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 599)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Metro travel expenses
        });

        // show bike-taxi service travel expenses - filter by te_pttv_id (vehicle)
        $bikeTaxiServiceTravelExpenses = $currentMonthExpenses->where('te_pttv_id', 600)->sum(function($expense) {
            return $expense->te_amount + $expense->te_taxes; // Bike-Taxi Service travel expenses
        });
        
        $response = [
            'expense_categories' => MasterTableResource::collection($expense_type),
            'monthly_totals' => [
                'loading_expenses' => round($hotelExpenses, 2),
                'meals_expenses' => round($foodExpenses, 2),
                'travel_expenses' => round($travelExpenses, 2),
                'toll_tax_expenses' => round($tollTaxExpenses, 2),
                'da_expenses' => round($daExpenses, 2), // Calculated based on eligibility
                'da_claimed_expenses' => round($claimedDAExpenses, 2), // Actual claimed amounts
                'ta_expenses' => round($taExpenses, 2), // Calculated based on eligibility
                'ta_claimed_expenses' => round($claimedTAExpenses, 2), // Actual claimed amounts
                'misc_expense' => round($miscExpenseExpenses, 2),
                'miscellaneous_expenses' => round($miscellaneousExpenses, 2),
                'other_expenses' => round($otherExpenses, 2),
                'train_travel_expenses' => round($trainTravelExpenses, 2),
                'car_travel_expenses' => round($carTravelExpenses, 2),
                'bike_travel_expenses' => round($bikeTravelExpenses, 2),
                'bus_travel_expenses' => round($busTravelExpenses, 2),
                'cab_taxi_travel_expenses' => round($cabTaxiTravelExpenses, 2),
                'auto_travel_expenses' => round($autoTravelExpenses, 2),
                'flight_travel_expenses' => round($flightTravelExpenses, 2),
                'metro_travel_expenses' => round($metroTravelExpenses, 2),
                'bike_taxi_service_travel_expenses' => round($bikeTaxiServiceTravelExpenses, 2),
                'total_expenses' => round($hotelExpenses + $foodExpenses + $travelExpenses + $trainTravelExpenses + $carTravelExpenses + $bikeTravelExpenses + $busTravelExpenses + $cabTaxiTravelExpenses + $autoTravelExpenses + $flightTravelExpenses + $metroTravelExpenses + $bikeTaxiServiceTravelExpenses + $tollTaxExpenses + $daExpenses + $taExpenses + $miscExpenseExpenses + $miscellaneousExpenses + $otherExpenses, 2),
                'month' => $fromDate && $toDate ? $fromDate . ' to ' . $toDate : now()->format('F Y'),
                'date_range' => [
                    'from_date' => $fromDate,
                    'to_date' => $toDate
                ]
            ]
        ];

        return ReturnHelper::jsonApiReturn($response);
    }  
    
    //Distance / KM Based Travel
    public function distanceKmBasedTravel(Request $request)
    {
        $user = Auth::user();
        $travelTypeName = $request->input('travel_type');
        $empId = $request->input('emp_id');
        $toDate = $request->input('to_date');
        $fromDate = $request->input('from_date');

        $expenseQuery = \App\Models\TadaExpense::with('fh_tada_request_plan.fh_employee')
            ->where('te_type_id', 159)
            ->where('te_total_km_driven', '>', 0);
         
        $requestDetailQuery = \App\Models\TadaRequestDetail::with('fh_policy_tada_request_plan.fh_employee')
            ->whereNotNull('trd_total_distance')
            ->where('trd_total_distance', '>', 0);

        $totalExpenseRecords = \App\Models\TadaExpense::where('te_type_id', 159)
            ->where('te_total_km_driven', '>', 0)
            ->count();
            
        $totalDetailRecords = \App\Models\TadaRequestDetail::whereNotNull('trd_total_distance')
            ->where('trd_total_distance', '>', 0)
            ->count();    

        // Apply date filters to both queries
        if ($fromDate && $toDate) {
            // Validate date range
            $fromDateObj = \Carbon\Carbon::parse($fromDate);
            $toDateObj = \Carbon\Carbon::parse($toDate);
            
            if ($fromDateObj->gt($toDateObj)) {
                return ReturnHelper::jsonApiReturn([
                    'error' => 'Invalid date range',
                    'message' => 'from_date cannot be later than to_date',
                    'from_date' => $fromDate,
                    'to_date' => $toDate
                ], false);
            }
            
            $expenseQuery->whereBetween('te_date', [$fromDate, $toDate]);
            $requestDetailQuery->whereBetween('trd_start_date', [$fromDate, $toDate]);
        } else {
            // Default to current month
            $expenseQuery->whereMonth('te_date', now()->month)
                         ->whereYear('te_date', now()->year);
            $requestDetailQuery->whereMonth('trd_start_date', now()->month)
                              ->whereYear('trd_start_date', now()->year);
        }

        // Add employee filter if provided (for super admin)
        if($empId && $user->emp_role_id == 1){
            $expenseQuery->whereHas('fh_tada_request_plan', function($query) use ($empId) {
                $query->where('trp_emp_id', $empId);
            });
            $requestDetailQuery->whereHas('fh_policy_tada_request_plan', function($query) use ($empId) {
                $query->where('trp_emp_id', $empId);
            });
        }

        $distanceExpenses = $expenseQuery->get();
        $requestDetails = $requestDetailQuery->get();

        //    dd($requestDetails->pluck('trd_total_tolerance')->sum(), $distanceExpenses->sum('te_total_km_driven'), $requestDetails->sum('trd_total_distance'));

        $totalDistance = $distanceExpenses->sum('te_total_km_driven') + $requestDetails->sum('trd_total_distance')+ $requestDetails->sum('trd_total_tolerance'); //with varation and other 
        
        // set response with all related data

        return ReturnHelper::jsonApiReturn([
            'total_distance' => $totalDistance,
            'distance_expenses' => $distanceExpenses,
            'request_details' => $requestDetails
        ], true);
        
       
    }

     /**
     * Calculate DA amount based on travel dates and employee eligibility
     */
    private function calculateDAAmount($employeeId, $businessId, $travelTypeId, $categoryId, $startDate, $endDate)
    {
        try {
            // Get DA policy for the employee's category and travel type
            $daPolicy = \App\Models\PolicyTadaDailyAllowance::where('ptda_b_id', $businessId)
                ->where('ptda_ptc_id', $categoryId)
                ->where('ptda_pttt_id', $travelTypeId)
                ->first();

            if (!$daPolicy) {
                return 0;
            }

            // Calculate number of travel days
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            $travelDays = $start->diffInDays($end) + 1; // Including both start and end dates

            // Get DA amount per day from policy
            $daPerDay = $daPolicy->ptda_da_amount ?? 0;
            
            // Check if half DA is applicable for the last day
            $halfDa = $daPolicy->ptda_half_da ?? false;
            $totalDA = $daPerDay * $travelDays;
            
            if ($halfDa && $travelDays > 1) {
                $totalDA = $daPerDay * ($travelDays - 1) + ($daPerDay / 2);
            }

            return round($totalDA, 2);
        } catch (\Exception $e) {
            \Log::error('DA Calculation Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate TA amount based on travel dates and employee eligibility
     */
    private function calculateTAAmount($employeeId, $businessId, $travelTypeId, $categoryId, $travelModeId, $vehicleId)
    {
        try {
            // Get TA policy for the employee's category, travel type, and mode
            $taPolicy = \App\Models\PolicyTadaTravelAllowance::where('ptta_b_id', $businessId)
                ->where('ptta_ptc_id', $categoryId)
                ->where('ptta_pttt_id', $travelTypeId)
                ->where('ptta_pttm_id', $travelModeId)
                ->when($vehicleId, function($query) use ($vehicleId) {
                    return $query->where('ptta_pttv_id', $vehicleId);
                })
                ->first();

            if (!$taPolicy) {
                // Try without vehicle filter
                $taPolicy = \App\Models\PolicyTadaTravelAllowance::where('ptta_b_id', $businessId)
                    ->where('ptta_ptc_id', $categoryId)
                    ->where('ptta_pttt_id', $travelTypeId)
                    ->where('ptta_pttm_id', $travelModeId)
                    ->first();
            }

            if (!$taPolicy) {
                return 0;
            }

            // Get eligibility amount from policy
            $taEligibility = $taPolicy->ptta_eligibility ?? 0;
            
            return round($taEligibility, 2);
        } catch (\Exception $e) {
            \Log::error('TA Calculation Error: ' . $e->getMessage());
            return 0;
        }
    }
}
