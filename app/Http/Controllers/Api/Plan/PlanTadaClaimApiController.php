<?php

namespace App\Http\Controllers\Api\Plan;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use ChandraHemant\HtkcUtils\CommonUtils;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\TravelPlanClaimRequest;
use App\Http\Resources\Approval\Travel\ClaimRequestApiResource;
use App\Http\Resources\Approval\Travel\ClaimRequestDetailsApiResource;
use App\Http\Resources\Request\PlanRequestApiResource;
use App\Http\Resources\Request\PlanRequestClaimResource;
use App\Models\ApprovalModule;
use App\Models\Employee;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelType;
use App\Models\RuleCriterion;
use App\Models\TadaClaim;
use App\Models\TadaExpense;
use App\Models\TadaMetroCity;
use App\Models\TadaRequestDetail;
use App\Models\TadaRequestPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Helpers\NotificationHelper;
use ChandraHemant\HtkcUtils\PaginatedResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use NumberToWords\NumberToWords;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PlanTadaClaimApiController extends Controller
{

    public function businessFilterClaim(Request $request)
    {
        $user = Auth::user();
        $filter = $request->all();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        // Step 1: Fetch all the relevant employee IDs related to the business
        $emp_ids = TadaClaim::where('tc_b_id', $user->emp_b_id)->pluck('tc_trp_id');
        if ($emp_ids->isEmpty()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No claim found for this business']);
        }

        $query = TadaClaim::where('tc_b_id', $user->emp_b_id)
            ->whereIn('tc_trp_id', $emp_ids)
            ->with('fh_tada_request_plan'); // Load related plans

        if (isset($filter['travel_type'])) {
            $travel_type = $filter['travel_type'];
            $pttt_type_id = PolicyTadaTravelType::where('pttt_id', $travel_type)->pluck('pttt_type_id')->first();
            if (!$pttt_type_id) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Invalid travel type']);
            }

            $query->whereHas('fh_tada_request_plan.fh_policy_tada_travel_type', function ($query) use ($pttt_type_id, $user) {
                $query->where('pttt_type_id', $pttt_type_id)
                    ->where('pttt_b_id', $user->emp_b_id);
            });
        }

        if (isset($filter['15_day']) && $filter['15_day'] == 1) {
            $previous_date = Carbon::now()->subDays(15)->toDateString();
            $query->whereDate('created_at', '>=', $previous_date);
        }

        if (isset($filter['from_date']) || isset($filter['to_date'])) {
            $from_date = isset($filter['from_date']) ? Carbon::parse($filter['from_date'])->toDateString() : null;
            $to_date = isset($filter['to_date']) ? Carbon::parse($filter['to_date'])->toDateString() : Carbon::now()->toDateString();
            $query->whereBetween('created_at', [$from_date, $to_date]);
        }

        if (isset($filter['status'])) {
            $query->where('tc_status', $filter['status']);
        }

        $plans = $query->get();

        $data = $query->orderBy('tc_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        if ($data->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, ClaimRequestApiResource::class));
        }

        return response()->json(['result' => [], 'status' => false, 'message' => 'No claim found with the given criteria']);
    }


    public function filterClaim(Request $request)
    {
        $user = Auth::user();
        $filter = $request->all();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $emp_ids = TadaClaim::where('tc_emp_id', $user->emp_id)->pluck('tc_trp_id');
        if ($emp_ids->isEmpty()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No claim found for this employee']);
        }

        $query = TadaClaim::where('tc_emp_id', $user->emp_id)
            ->whereIn('tc_trp_id', $emp_ids)
            ->with(['fh_tada_request_plan']); // Load related plans and travel type

        if (isset($filter['travel_type'])) {
            $travel_type = $filter['travel_type'];
            $pttt_type_id = PolicyTadaTravelType::where('pttt_id', $travel_type)->pluck('pttt_type_id')->first();
            if (!$pttt_type_id) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Invalid travel type']);
            }

            $query->whereHas('fh_tada_request_plan.fh_policy_tada_travel_type', function ($query) use ($pttt_type_id, $user) {
                $query->where('pttt_type_id', $pttt_type_id)
                    ->where('pttt_b_id', $user->emp_b_id);
            });
        }

        if (isset($filter['15_day']) && $filter['15_day'] == 1) {
            $previous_date = Carbon::now()->subDays(15)->toDateString();
            $query->whereDate('created_at', '>=', $previous_date);
        }

        if (isset($filter['from_date']) || isset($filter['to_date'])) {
            $from_date = isset($filter['from_date']) ? Carbon::parse($filter['from_date'])->toDateString() : null;
            $to_date = isset($filter['to_date']) ? Carbon::parse($filter['to_date'])->toDateString() : Carbon::now()->toDateString();
            if ($from_date) {
                $query->whereBetween('created_at', [$from_date, $to_date]);
            } else {
                $query->whereDate('created_at', '<=', $to_date);
            }
        }

        if (isset($filter['status'])) {
            $query->where('tc_status', $filter['status']);
        }

        $plans = $query->get();
        $data = $query->orderBy('tc_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        if ($data->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, ClaimRequestApiResource::class));
        }

        return response()->json(['result' => [], 'status' => false, 'message' => 'No claim found with the given criteria']);
    }

    public function generateTravelClaimPdf($claimId)
    {
        $claimData = TadaClaim::where(DB::raw('md5(tc_id)'), $claimId)->first();

        if (!$claimData) {
            return response()->json(['status' => false, 'message' => 'Claim not found.'], 404);
        }

        $expenseData = optional($claimData->fh_tada_request_plan->fh_tada_expenses)->groupBy('fh_expense_type.m_name') ?? [];

        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        $payableAmount = ($claimData->tc_amount ?? 0) - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0));
        $capitalizedWords = ucfirst($numberTransformer->toWords($payableAmount));

        $data = [
            'title' => 'Welcome to Laravel PDF Generation',
            'claimData' => $claimData,
            'capitalizedWords' => $capitalizedWords,
            'expenseData' => $expenseData,
            'logoPath' => optional(optional($claimData->fh_employee)->fh_business)->b_logo,
        ];

        $pdf = Pdf::loadView('admin.tada-reports.document', $data);
        return $pdf->stream('travel-claim.pdf');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $query = TadaClaim::where('tc_b_id', $user->emp_b_id);

        $data = $query->orderBy('tc_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        if ($data)
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, ClaimRequestApiResource::class));
        else
            return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function claimList(Request $request, $id)
    {
        $user = Auth::user();
        $travel_type = $id;
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        
        $plans = TadaRequestPlan::where('trp_pttt_id', $travel_type)
            ->where('trp_emp_id', $user->emp_id)
            ->get();

        if ($plans->isEmpty()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No travel plan found for the user']);
        }
        
        $planIds = $plans->pluck('trp_id')->toArray();
        $policyTravelTypeId = PolicyTadaTravelType::where('pttt_b_id',$user->emp_b_id)->where('pttt_id',$travel_type)->first();
       
        if ($policyTravelTypeId->pttt_type_id == '124') { //here 124 is local travel type id  this condation set only one id
            $uniqueIds = TadaClaim::select('tc_unique_id')
                ->where('tc_emp_id', $user->emp_id)
                ->whereIn('tc_trp_id', $planIds)
                ->where('tc_status', '!=', 156)
                ->groupBy('tc_unique_id')
                ->havingRaw('COUNT(tc_unique_id) = 1')
                ->pluck('tc_unique_id')
                ->toArray();
        
            $query = TadaClaim::where('tc_emp_id', $user->emp_id)
                ->whereIn('tc_trp_id', $planIds)
                ->whereIn('tc_unique_id', $uniqueIds)
                ->where('tc_status', '!=', 156)
                ->orderBy('tc_id', 'DESC');
        } else {
            $query = TadaClaim::where('tc_emp_id', $user->emp_id)
                ->whereIn('tc_trp_id', $planIds)
                ->where('tc_status', '!=', 156)
                ->orderBy('tc_id', 'DESC');
        }
        
        /*  $query = TadaClaim::where('tc_emp_id', $user->emp_id)
                ->whereIn('tc_trp_id', $planIds)
                ->where('tc_status', '!=', 156)
                ->orderBy('tc_id', 'DESC');
        */
        $data = $query->paginate($limit, ['*'], 'page', $page);

        if ($data->isEmpty()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No claim on your travel type']);
        }
        return ReturnHelper::jsonApiReturn(new PaginatedResource($data, ClaimRequestApiResource::class));
    }
    
    public function acceptanceList(Request $request, $id)
    {
        $user = Auth::user();
        $travelType = $id;
    
        // Get all travel plans for employee & type
        $planIds = TadaRequestPlan::where('trp_pttt_id', $travelType)
            ->where('trp_emp_id', $user->emp_id)
            ->pluck('trp_id');
    
        if ($planIds->isEmpty()) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'No travel plan found for the user',
            ]);
        }
    
        // Get claims
        $claims = TadaClaim::where('tc_emp_id', $user->emp_id)
            ->whereIn('tc_trp_id', $planIds)
            ->where('tc_status', '!=', 156)
            ->orderBy('tc_id', 'DESC')
            ->get();
    
        if ($claims->isEmpty()) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'No claim on your travel type',
            ]);
        }
    
        // Filter claims that have deduction logs without requester_action
        $acceptanceList = $claims->filter(function ($claim) {
            return $claim->fh_deduction_log()
                ->whereNull('dlog_requester_action')
                ->exists();
        });
        
        if ($acceptanceList->isEmpty()) {
            return response()->json([
                'result'  => [],
                'status'  => true,
                'message' => 'No data found',
            ]);
        }
        
        return ReturnHelper::jsonApiReturn(
            ClaimRequestApiResource::collection($acceptanceList)
        );
    }
    

    public function groupClaimList(Request $request, $id)
    {
        $user = Auth::user();
        $travel_type = $id;
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
    
        $plans = TadaRequestPlan::where('trp_pttt_id', $travel_type)
            ->where('trp_emp_id', $user->emp_id)
            ->get();
    
        if ($plans->isEmpty()) {
            return response()->json([
                'status' => false,
                'result' => [],
                'message' => 'No travel plan found for the user'
            ]);
        }
    
        $planIds = $plans->pluck('trp_id');
    
        $claims = TadaClaim::where('tc_emp_id', $user->emp_id)
            ->whereIn('tc_trp_id', $planIds)
            ->where('tc_status', '!=', 156)
            ->orderBy('tc_id', 'DESC')
            ->get();
    
        if ($claims->isEmpty()) {
            return response()->json([
                'status' => false,
                'result' => [],
                'message' => 'No claim on your travel type'
            ]);
        }
        
        // Group claims by unique_id and filter out single entries
        $grouped = $claims->groupBy('tc_unique_id')
            ->filter(function ($claims) {
                return $claims->count() > 1; 
            })
            ->map(function ($claims, $uniqueId) {
                return [
                    'claim_id' => $uniqueId,
                    // 'items' => ClaimRequestApiResource::collection($claims),
                ];
            })
            ->values(); 
    
        // Paginate grouped result
        $sliced = $grouped->forPage($page, $limit)->values(); 
        $total = $grouped->count();
    
        $paginated = new LengthAwarePaginator(
            $sliced,
            $total,
            $limit,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    
        return response()->json([
            'result' => [
                'data' => $paginated->items(),
                'pagination' => [
                    'total'         => $paginated->total(),
                    'count'         => $paginated->count(),
                    'per_page'      => $paginated->perPage(),
                    'current_page'  => $paginated->currentPage(),
                    'last_pages'    => $paginated->lastPage(),
                ]
            ],
            'status' => true
        ]);
    }

    public function claimGroupTravelList(Request $request, $id)
    {
        $user = Auth::user();
        $travel_type = $id;
    
        $tc_unique_id = $request->tc_unique_id; 
    
        $claims = TadaClaim::where('tc_emp_id', $user->emp_id)
            ->where('tc_unique_id', $tc_unique_id)
            ->orderBy('tc_trp_id', 'ASC')
            ->get();
    
        if ($claims->isEmpty()) {
            return response()->json([
                'result'  => [],
                'status'  => true,
                'message' => 'No data found',
            ]);
        }
    
        return ReturnHelper::jsonApiReturn(
            ClaimRequestApiResource::collection($claims)
        );
    }

   /* public function fetchExpenseByPlanId($planId)
    {
        $user = Auth::user();

        $expenses = TadaExpense::whereHas('fh_tada_request_plan', function ($query) use ($user, $planId) {
            $query->where([
                'trp_emp_id' => $user->emp_id,
                'te_trp_id' => $planId
            ]);
        })
        ->with([
            'fh_tada_request_plan',
            'fh_expense_type',
            'fh_policy_tada_travel_mode',
            'fh_policy_tada_travel_vehicle',
            'fh_sub_expense'
        ])
        ->get();

        if ($expenses->isNotEmpty()) {
            $grouped = $expenses->groupBy(function($item) {
                $type = $item->fh_expense_type;
                return $type ? $type->m_name : '';
            });
            $transformedGroups = [];

            foreach ($grouped as $type => $items) {
                $transformedGroups[] = [
                    'type' => $type,
                    'items' => TadaExpenseWiseResource::collection($items),
                ];
            }

            return response()->json([
                'status' => true,
                'result' => $transformedGroups,
            ]);
        }

        return response()->json([
            'status' => false,
            'result' => [],
            'message' => 'No Data'
        ]);
    }*/

    public function planList(Request $request)
    {
        $user = Auth::user();

        $query = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)
            ->whereHas('fh_claim', function ($q) use ($user, $request) {
                $q->where('tc_b_id', $user->emp_b_id)
                    ->where('tc_id', $request->claim_id);
            });

        $plans = $query->get();

        if ($plans->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(PlanRequestApiResource::collection($plans));
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(TravelPlanClaimRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $emp_d_id = $user->emp_d_id;

            $value = $request->validated();
            $planIds = json_decode($value['plan_id'], true);

            $tc_unique_id = '';
            $lastUnique = TadaClaim::where('tc_b_id', $user->emp_b_id)->get()->last();
            if ($lastUnique) {
                $tc_unique_id = $lastUnique->tc_unique_id ?? '';
            }

            $unique_id = CentralLogics::alpha_numeric_generator(4, 'TC', '', $tc_unique_id ?? '');
            $claims = [];
            $amId = null;

            // NEW: Try to get employee-wise approval mapping first
    		$approvalMapping = \App\Helpers\ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 146);
    		$approvalEmpIds = [];
    		$amId = null;

    		if ($approvalMapping) {
    			$approvalEmpIds = \App\Helpers\ApprovalHelper::getApprovalArray($approvalMapping);
    		} else {
                $ruleCriteria = RuleCriterion::with('fh_approval_module')
                    ->where('rc_b_id', $user->emp_b_id)
                    ->where('rc_condition_option_id', 140)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 146)
                            ->where('am_status', 1);
                    })->first();

                $processApprovers = [];

                // Ensure $ruleCriteria exists before accessing the relationship
                if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                    $processApprovers = $ruleCriteria->fh_approval_module
                        ->filteredProcessApprovers($emp_d_id)
                        ->get();
                }

                if (!empty($processApprovers)) {
    				$amId = $ruleCriteria->rc_am_id;
    				foreach ($processApprovers as $pa) {
    					if ($pa->pa_emp_id) {
    						$approvalEmpIds[] = $pa->pa_emp_id;
    					}
    				}
    			} else {

    				return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for claim module, contact administration.']);
    			}
    		}

			/*
            if (empty($processApprovers)) {
                $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 146);
                if (!$approvalMapping) {
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for claim module, contact administration.']);
                }
            } else {
                $amId = $ruleCriteria->rc_am_id;
            }*/

            $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $user->emp_b_id, 'ptc_d_id' => $user->emp_d_id, 'ptc_grade_id' => $user->emp_grade_id])->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();
            if (!$policyCategory) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! policy not found, contact administration.']);
            }

            foreach ($planIds as $ele) {
                $trpId = $ele['trp_id'];
                $plan = TadaRequestPlan::where('trp_id', $trpId)->first();

                $isLocal = $plan->fh_policy_tada_travel_type->fh_travel_type->m_id == 124 ? true : false;

                  // Generate unique ID for EACH claim (only for outstation travel)
                $tc_unique_id = '';

                if(!$isLocal){
                    $lastUnique = TadaClaim::where('tc_b_id', $user->emp_b_id)->get()->last();
                    if ($lastUnique) {
                        $tc_unique_id = $lastUnique->tc_unique_id ?? '';
                    }

                    $unique_id = CentralLogics::alpha_numeric_generator(4, 'TC', '', $tc_unique_id ?? '');
                }

                // Eligibility check for DA based on policy travel type
                $response = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')->getEligibilityByPolicyTravelType($policyCategory, $plan->fh_policy_tada_travel_type->pttt_id);

                $totalDAAll = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')->calculateDailyAllowance($plan, $response['da_eligibility']);
                $totalDA = $totalDAAll['totalDA'];
                $calculationMessage = $totalDAAll['calculationMessage'] ?? '';
                // DA calculation logic


                // Other expense calculations
                $totalExpenseAmount = $plan->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_amount');
                $totalExpenseTaxes = $plan->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_taxes');
                $totalExpenseDeviation = $plan->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_deviation');

                $totalTravelAmount = $plan->fh_tada_request_details->sum('trd_net_amount');   // New Add Line
                /*
                Reason: TA amount did not recalculate when actual outstation travel was added. Code commented pending fix.

                if ($isLocal) {
                    $totalTravelAmount = $plan->fh_tada_request_details->sum('trd_net_amount');
                } else {
                    $totalTravelAmount = TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function ($query) {
                        $query->where('pttv_claim_type_id', 155);
                    })->where('trd_trp_id', $trpId)  // Assuming you are filtering based on the related detail's ID.
                        ->sum('trd_net_amount');
                }*/

                $customAmount = $value['custom_amount'] ?? null;

                if ($customAmount !== null && $customAmount > $totalDA) {
                    return response()->json([
                        'result' => [],
                        'status' => false,
                        'message' => 'Custom amount cannot exceed the total claimable amount.'
                    ]);
                }


                // Create a new claim
                $claim = new TadaClaim();
                $claim->tc_claimed_amount = round(($totalExpenseAmount ?? 0) + ($totalExpenseTaxes ?? 0) + ($totalTravelAmount ?? 0) + $totalDA);
                $claim->tc_amount = round(($totalExpenseAmount + $totalExpenseTaxes + $totalTravelAmount + $totalDA) - $totalExpenseDeviation);
                $claim->tc_da_amount = round($totalDA);
                $claim->tc_da_calculation_message = $calculationMessage;
                $claim->tc_emp_id = $user->emp_id;
                $claim->tc_unique_id = $unique_id;
                $claim->tc_b_id = $user->emp_b_id;
                $claim->tc_trp_id = $trpId;
                $claim->tc_am_id = $amId;
                $claim->tc_status = 140;

                if ($claim->save()) {
                    $trp = TadaRequestPlan::find($trpId);

                    if ($trp) {

                        $trp->update([
                            'trp_request_status' => 156,
                            'trp_is_claimed'     => 1,
                            'trp_next_approver'  => 0,
                        ]);

                        // CASE 1: Trip has its own advance
                        if ($trp->trp_advance_allowance > 0) {

                            // Get UNUSED balance for THIS trip only
                            $tripBalance = TadaAdvanceWallet::where('aw_emp_id', $trp->trp_emp_id)
                                ->where('aw_b_id', $trp->trp_b_id)
                                ->where('aw_trp_id', $trp->trp_id)
                                ->selectRaw("
                                    SUM(CASE WHEN aw_type='credit' THEN aw_amount ELSE 0 END) -
                                    SUM(CASE WHEN aw_type='debit' THEN aw_amount ELSE 0 END)
                                    AS balance
                                ")
                                ->value('balance') ?? 0;

                            if ($tripBalance >= $trp->trp_advance_allowance) {
                                $aw_amount = $claim->tc_claimed_amount <= $trp->trp_advance_allowance ? $claim->tc_claimed_amount : $trp->trp_advance_allowance;

                                TadaAdvanceWallet::create([
                                    'aw_b_id' => $trp->trp_b_id,
                                    'aw_emp_id' => $trp->trp_emp_id,
                                    'aw_trp_id' => $trp->trp_id,
                                    'aw_type' => 'debit',
                                    'aw_amount' => $aw_amount,
                                    'aw_date' => now(),
                                    'aw_remark' => 'Applied advance on ' . $trp->trp_unique_id,
                                    'aw_source_type' => 'trip',
                                ]);

                                $trp->trp_advance_allowance = $aw_amount;
                            }
                        }

                        // CASE 2: No trip advance → use wallet
                        else if ($ele['isApplyAdvance']) {

                            $walletBalance = TadaAdvanceWallet::getBalanceWithoutTrp($trp->trp_emp_id, $trp->trp_b_id);

                            // if ($walletBalance >= $claim->tc_claimed_amount) {
                            if ($claim->tc_claimed_amount > 0 && $walletBalance > 0) {
                                $aw_amount = $claim->tc_claimed_amount <= $walletBalance ? $claim->tc_claimed_amount : $walletBalance;

                                // Update trip allowance dynamically
                                $trp->trp_advance_allowance = $aw_amount;

                                TadaAdvanceWallet::create([
                                    'aw_b_id' => $trp->trp_b_id,
                                    'aw_emp_id' => $trp->trp_emp_id,
                                    'aw_trp_id' => $trp->trp_id,
                                    'aw_type' => 'debit',
                                    'aw_amount' => $aw_amount,
                                    'aw_date' => now(),
                                    'aw_remark' => 'Applied advance on ' . $trp->trp_unique_id,
                                    'aw_source_type' => 'trip',
                                ]);
                            }
                        }

                        $trp->save();
                    }

                    $claims[] = $claim;
                }
            }

            if ($claims) {
                $title = 'New Claim Request';
                $body = 'A new claim request has been submitted by ' . $user->emp_full_name;
                if ($customAmount) {
                    $body .= ' with a custom claim amount of ' . $customAmount . '.';
                }
                $additionalData = [
                    'user_id' => $user->emp_id,
                    'notification_type' => 'alert',
                    'route' => '/TadaApprovalList',
                ];

                $serviceAccountPath = public_path('fixhr-app-firebase.json');

                if (!file_exists($serviceAccountPath)) {
                    Log::error('Firebase JSON file not found', ['path' => $serviceAccountPath]);
                }

                 Log::warning('Employee not found', [
                        'serviceAccountPath' => $serviceAccountPath
                    ]);

                // Notify only first approver (both employee-wise and hierarchy-wise)
			    $notifyEmpIds = !empty($approvalEmpIds) ? [reset($approvalEmpIds)] : [];

    			foreach ($notifyEmpIds as $approverEmpId)
                {

                    $emp = Employee::find($approverEmpId);

                    if (!$emp) {
                        Log::warning('Employee not found', [
                            'approver_emp_id' => $approverEmpId
                        ]);
                        continue;
                    }

                    $approver = ApprovalHelper::getApprovalOrRejectionData(
                        $claim->tc_id,
                        $claim->tc_status,
                        $amId,
                        $emp->emp_id,
                        146
                    );

                    if ($emp->emp_is_notification_enabled == '1') {

                        if ($emp->emp_fcm_token) {

                            FirebaseNotification::sendPushNotification(
                                $title,
                                $body,
                                $emp->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );

                        }

                      Log::info('Push notification sent', [
                            'emp_fcm_token' => $emp->emp_fcm_token ?? null,
                            'emp_id' => $emp->emp_id ?? null,
                            'serviceAccountPath' => $serviceAccountPath,
                            'credentials' => config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                            'additionalData' => $additionalData
                        ]);
                        NotificationHelper::saveNotification(
                            $user->emp_id,
                            $approverEmpId,
                            $title,
                            $body,
                            $additionalData
                        );
                    }
                }

                DB::commit();
                return ReturnHelper::jsonApiReturn(PlanRequestClaimResource::collection($claims)->all());
            }

            return response()->json(['result' => [], 'status' => false]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'An error occurred while processing the claim: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateCustomAmount(TravelPlanClaimRequest $request)
    {
        $user = Auth::user();
        $emp_d_id = $user->emp_d_id;

        $validated = $request->validate([
            'plan_id' => 'required',
            'custom_amount' => 'required|numeric|min:0',
        ]);

        $planIds = json_decode($validated['plan_id']);
        $customAmount = isset($validated['custom_amount']) ? $validated['custom_amount'] : null;

        if ($customAmount === null) {
            return response()->json(['status' => false, 'message' => 'Custom amount is required and cannot be null.']);
        }

        $tc_unique_id = '';
        $lastUnique = TadaClaim::where('tc_b_id', $user->emp_b_id)->latest()->first();
        $tc_unique_id = $lastUnique->tc_unique_id ?? '';

        $unique_id = CentralLogics::alpha_numeric_generator(4, 'TC', '', $tc_unique_id);
        $claims = [];
        $amId = null;

        // Fetch approval settings
        $ruleCriteria = RuleCriterion::with('fh_approval_module')
            ->where('rc_b_id', $user->emp_b_id)
            ->where('rc_condition_option_id', 140)
            ->whereHas('fh_approval_module', function ($query) {
                $query->where('am_module_id', 146)->where('am_status', 1);
            })->first();

        $processApprovers = $ruleCriteria && $ruleCriteria->fh_approval_module
            ? $ruleCriteria->fh_approval_module->filteredProcessApprovers($emp_d_id)->get()
            : [];

        if (empty($processApprovers)) {
            return response()->json(['status' => false, 'message' => 'No approval settings found for the claim module. Contact administration.']);
        }

        $amId = $ruleCriteria->rc_am_id;

        $policyCategory = PolicyTadaCategory::where([
            'ptc_b_id' => $user->emp_b_id,
            'ptc_d_id' => $user->emp_d_id,
            'ptc_grade_id' => $user->emp_grade_id,
        ])->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();

        if (!$policyCategory) {
            return response()->json(['status' => false, 'message' => 'Policy not found. Contact administration.']);
        }

        foreach ($planIds as $trpId) {
            $plan = TadaRequestPlan::where('trp_id', $trpId)->first();

            if (!$plan) {
                return response()->json(['status' => false, 'message' => 'Invalid plan ID.']);
            }

            $isLocal = $plan->fh_policy_tada_travel_type->fh_travel_type->m_id == 124;

            $response = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')
                ->getEligibilityByPolicyTravelType($policyCategory, $plan->fh_policy_tada_travel_type->pttt_id);

            $totalDAAll = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')
                ->calculateDailyAllowance($plan, $response['da_eligibility']);
            $totalDA = $totalDAAll['totalDA'];
            $calculationMessage = $totalDAAll['calculationMessage'];

            if ($customAmount !== null && $customAmount > $totalDA) {
                dd(1);
                return response()->json(['status' => false, 'message' => 'Custom amount cannot exceed the total DA amount.']);
            }

            $totalExpenseAmount = $plan->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_amount');
            $totalExpenseTaxes = $plan->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_taxes');
            $totalExpenseDeviation = $plan->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_deviation');
            $totalTravelAmount = $isLocal
                ? $plan->fh_tada_request_details->sum('trd_net_amount')
                : TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function ($query) {
                    $query->where('pttv_claim_type_id', 155);
                })->where('trd_trp_id', $trpId)->sum('trd_net_amount');

            $existingClaim = TadaClaim::where('tc_trp_id', $trpId)->where('tc_emp_id', $user->emp_id)->latest('created_at')->first();

            if ($existingClaim) {
                $remainingDA = $existingClaim->tc_da_amount - $customAmount;
                $newCustomAmount = $existingClaim->tc_custom_amount + $customAmount;

                if ($newCustomAmount > $remainingDA) {
                    return response()->json(['status' => false, 'message' => 'Custom amount exceeds remaining DA amount.']);
                }

                $existingClaim->tc_custom_amount = $newCustomAmount;
                $existingClaim->tc_da_amount -= $customAmount;

                $existingClaim->remaining_da_amount = $remainingDA - $customAmount;
                $existingClaim->save();
            } else {
                $claim = new TadaClaim();
                $claim->tc_claimed_amount = round($totalExpenseAmount + $totalExpenseTaxes + $totalTravelAmount + $totalDA);
                $claim->tc_amount = round(($totalExpenseAmount + $totalExpenseTaxes + $totalTravelAmount + $totalDA) - $totalExpenseDeviation);
                $claim->tc_da_amount = round($totalDA) - $customAmount;
                $claim->remaining_da_amount = $totalDA - $customAmount;
                $claim->tc_da_calculation_message = $calculationMessage;
                $claim->tc_emp_id = $user->emp_id;
                $claim->tc_unique_id = $unique_id;
                $claim->tc_b_id = $user->emp_b_id;
                $claim->tc_trp_id = $trpId;
                $claim->tc_am_id = $amId;
                $claim->tc_status = 140;
                $claim->tc_custom_amount = $customAmount;

                if ($claim->save()) {
                    TadaRequestPlan::where('trp_id', $trpId)->update(['trp_request_status' => 156, 'trp_is_claimed' => 1, 'trp_next_approver' => 0]);
                    $claims[] = $claim;
                }
            }
        }

        if ($claims) {
            $title = 'New Claim Request';
            $body = 'A new claim request has been submitted by ' . $user->emp_full_name;
            if ($customAmount) {
                $body .= ' with a custom claim amount of ' . $customAmount . '.';
            }
            $additionalData = [
                'user_id' => $user->emp_id,
                'notification_type' => 'alert',
                'route' => '/TadaApprovalList',
            ];
            $serviceAccountPath = public_path('fixhr-app-firebase.json');

            foreach ($processApprovers as $pa) {
                $emp = Employee::find($pa->pa_emp_id);
                $approver = ApprovalHelper::getApprovalOrRejectionData($claim->tc_id, $claim->tc_status, $amId, $pa->pa_emp_id,146);

                /*if ($approver) {
                    FirebaseNotification::sendPushNotification(
                        $title,
                        $body,
                        $emp->emp_fcm_token,
                        $serviceAccountPath,
                        config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                        $additionalData
                    );
                }*/
            }

            return ReturnHelper::jsonApiReturn(PlanRequestClaimResource::collection($claims)->all());
        }

        return response()->json(['status' => false, 'message' => 'Failed to create claims.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $claim = TadaClaim::where('tc_emp_id', $user->emp_id)->where('tc_id', $id)->first();
        if ($claim) {
            return ReturnHelper::jsonApiReturn(new PlanRequestClaimResource($claim));
        }
        return response()->json(['result' => [], 'status' => false]);
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
        $user = Auth::user();
        $claim = TadaClaim::find($id);
        $trpexpense = json_decode($request->getContent());

        $claim->tc_trp_id = $request->plan_id ?? $claim->tc_trp_id;
        $claim->tc_emp_id = $user->emp_id ?? $claim->tc_emp_id;
        $claim->tc_amount = $request->amount ?? $claim->tc_amount;
        $claim->tc_deduction_amount = $request->correction ?? $claim->tc_deduction_amount;
        $claim->tc_approved_date = $request->approved_date ?? $claim->tc_approved_date;
        $claim->tc_payment_date = $request->payment_date ?? $claim->tc_payment_date;
        $claim->tc_claimed_amount = $request->claimed_amount ?? $claim->tc_claimed_amount;
        $claim->tc_status = $request->status ?? $claim->tc_status;
        $claim->tc_remarks = $request->remarks ?? $claim->tc_remarks;

        if ($claim->save()) {
            return ReturnHelper::jsonApiReturn(PlanRequestClaimResource::collection([$claim])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $plan = TadaClaim::where('tc_id', $id)
            ->where('tc_emp_id', $user->emp_id)
            ->first();

        if ($plan) {
            $plan->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }

     /**
     * Show the form for creating a new resource.
    */
    public function claimDetailList(Request $request, $id)
    {
        $user = Auth::user();
        $travel_type = $id;
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $plans = TadaRequestPlan::where('trp_pttt_id', $travel_type)
            ->where('trp_emp_id', $user->emp_id)
            ->get();

        if ($plans->isEmpty()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No travel plan found for the user']);
        }

        $planIds = $plans->pluck('trp_id')->toArray();
        $policyTravelTypeId = PolicyTadaTravelType::where('pttt_b_id', $user->emp_b_id)
            ->where('pttt_id', $travel_type)
            ->first();

        // Base query
        $query = TadaClaim::with(['fh_employee', 'fh_tada_request_plan.fh_policy_tada_travel_type', 'fh_approval_log'])
            ->where('tc_emp_id', $user->emp_id)
            ->whereIn('tc_trp_id', $planIds)
            ->whereNotIn('tc_status', [139, 192, 156]);

        // Special logic for local travel type (ID: 124)
        if ($policyTravelTypeId && $policyTravelTypeId->pttt_type_id == '124') {
            $uniqueIds = TadaClaim::select('tc_unique_id')
                ->where('tc_emp_id', $user->emp_id)
                ->whereIn('tc_trp_id', $planIds)
                ->where('tc_status', '!=', 156)
                ->groupBy('tc_unique_id')
                ->havingRaw('COUNT(tc_unique_id) = 1')
                ->pluck('tc_unique_id')
                ->toArray();

            $query->whereIn('tc_unique_id', $uniqueIds);
        }

        // Merge query parameters and JSON data for filtering
        $filter = array_merge($request->query(), $request->json()->all());

        // Search Filter
            if (!empty($filter['value']) && $filter['value'] !== 'null') {
            $value = trim($filter['value']);
            $query->where(function ($q) use ($value) {
                // Travel purpose
                $q->orWhereHas('fh_tada_request_plan.fh_travel_purpose', fn($p) =>
                    $p->whereRaw('LOWER(tp_name) LIKE ?', ['%'.strtolower($value).'%'])
                );


                // Approval status
                $q->orWhereHas('fh_tada_request_plan.fh_approval_status', fn($s) =>
                    $s->whereRaw('LOWER(m_name) LIKE ?', ['%'.strtolower($value).'%'])
                );

                // Unique IDs
                $q->orWhereHas('fh_tada_request_plan', fn($trp) =>
                    $trp->whereRaw('LOWER(trp_unique_id) LIKE ?', ['%'.strtolower($value).'%'])
                );
                $q->orWhereRaw('LOWER(tc_unique_id) LIKE ?', ['%'.strtolower($value).'%']);
            });
        }

        // Status Filter
        if (!empty($filter['status']) && $filter['status'] !== 'null') {
            $query->where('tc_status', $filter['status']);
        }

        // Execute query with pagination
        $data = $query->orderBy('tc_id', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);

        if ($data->isEmpty()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No claim found for your travel type']);
        }

        return ReturnHelper::jsonApiReturn(
            new PaginatedResource($data, ClaimRequestDetailsApiResource::class)
        );
    }
}
