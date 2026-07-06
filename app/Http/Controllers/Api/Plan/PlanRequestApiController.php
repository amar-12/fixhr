<?php

namespace App\Http\Controllers\Api\Plan;

use App\Helpers\CentralLogics;
use ChandraHemant\HtkcUtils\CommonUtils;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\TravelPlanFilterRequest;
use App\Http\Requests\Plan\TravelPlanRequest;
use App\Http\Resources\Approval\Travel\TravelRequestApiResource;
use App\Http\Resources\Approval\Travel\TravelRequestApprovalSearchApiController;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\Request\FilterPlanRequestApiResource;
use App\Http\Resources\Request\PlanRequestApiResource;
use App\Http\Resources\Request\PlanRequestDetailResource;
use App\Http\Resources\Request\GetSegementsDetailResource;
use App\Models\MasterTable;
use App\Models\TadaRequestPlan;
use App\Models\TadaRequestDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\PolicyTadaTravelType;
use ChandraHemant\HtkcUtils\PaginatedResource;

class PlanRequestApiController extends Controller
{

    public function filterPlanByClaim(Request $request)
    {
        $user = Auth::user();
        $filter = $request->all();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        // Check if 'travel_type' exists in the filter array
        if (!isset($filter['travel_type'])) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel type is required']);
        }

        $travel_type = $filter['travel_type'];

        $emp_id = TadaRequestPlan::where('trp_emp_id', $user->emp_id)->get();
        if ($emp_id->isEmpty()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No plan found for this business']);
        }

        $pttt_type_id = PolicyTadaTravelType::where('pttt_id', $travel_type)->pluck('pttt_type_id')->first();
        $query = TadaRequestPlan::where('trp_emp_id', $user->emp_id)
            ->whereNot('trp_is_claimed', 1)
            ->whereHas('fh_policy_tada_travel_type', function ($query) use ($pttt_type_id) {
                $query->where('pttt_type_id', $pttt_type_id);
            })->with('fh_policy_tada_travel_type');

        if (isset($filter['15_day']) && $filter['15_day'] == 1) {
            $previous_date = Carbon::now()->subDays(15)->toDateString();
            $query->whereDate('created_at', '>=', $previous_date);
        }

        if (isset($filter['from_date'])) {
            $from_date = Carbon::parse($filter['from_date'])->toDateString();
            $to_date = isset($filter['to_date']) ? Carbon::parse($filter['to_date'])->toDateString() : Carbon::now()->toDateString();
            $query->whereBetween('created_at', [$from_date, $to_date]);
        }

        if (isset($filter['from_date']) && isset($filter['to_date'])) {
            $from_date = Carbon::parse($filter['from_date'])->toDateString();
            $to_date = Carbon::parse($filter['to_date'])->toDateString();
            $query->whereBetween('created_at', [$from_date, $to_date]);
        }

        if (isset($filter['from_date']) && isset($filter['status'])) {
            $from_date = Carbon::parse($filter['from_date'])->toDateString();
            $status = $filter['status'];
            $to_date = isset($filter['to_date']) ? Carbon::parse($filter['to_date'])->toDateString() : Carbon::now()->toDateString();
            $query->whereBetween('created_at', [$from_date, $to_date])->where('trp_request_status', $status);
        }

        if (isset($filter['status'])) {
            $query->where('trp_request_status', $filter['status']);
        }

        if (isset($filter['from_date']) && isset($filter['to_date']) && isset($filter['status'])) {
            $from_date = Carbon::parse($filter['from_date'])->toDateString();
            $to_date = Carbon::parse($filter['to_date'])->toDateString();
            $status = $filter['status'];
            $query->whereBetween('created_at', [$from_date, $to_date])->where('trp_request_status', $status);
        }

        $plans = $query->get();
        $data = $query->orderBy('trp_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        if ($data->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, FilterPlanRequestApiResource::class));
        }

        return response()->json(['result' => [], 'status' => false, 'message' => 'No plans found with the given criteria']);
    }

    public function businessFilterPlanByClaim(Request $request)
    {
        $user = Auth::user();
        $filter = $request->all();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $emp_id = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)->get();
        if ($emp_id->isEmpty()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No plan found for this business']);
        }

        // Initialize the query
        $query = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)
            ->whereNot('trp_is_claimed', 1);

        // Apply travel_type filter if it is present
        if (isset($filter['travel_type'])) {
            $travel_type = $filter['travel_type'];
            $pttt_type_id = PolicyTadaTravelType::where('pttt_id', $travel_type)->pluck('pttt_type_id')->first();
            $query->whereHas('fh_policy_tada_travel_type', function ($query) use ($pttt_type_id) {
                $query->where('pttt_type_id', $pttt_type_id);
            })->with('fh_policy_tada_travel_type');
        }

        // Apply 15-day filter if it's set
        if (isset($filter['15_day']) && $filter['15_day'] == 1) {
            $previous_date = Carbon::now()->subDays(15)->toDateString();
            $query->whereDate('created_at', '>=', $previous_date);
        }

        // Apply from_date and to_date filters
        if (isset($filter['from_date'])) {
            $from_date = Carbon::parse($filter['from_date'])->toDateString();
            $to_date = isset($filter['to_date']) ? Carbon::parse($filter['to_date'])->toDateString() : Carbon::now()->toDateString();
            $query->whereBetween('created_at', [$from_date, $to_date]);
        }

        // Apply status filter
        if (isset($filter['status'])) {
            $query->where('trp_request_status', $filter['status']);
        }

        // Get the plans and paginate results
        $plans = $query->get();
        $data = $query->orderBy('trp_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        // Return response
        if ($plans->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, TravelRequestApiResource::class));
        }

        return response()->json(['result' => [], 'status' => false, 'message' => 'No plans found with the given criteria']);
    }



    public function status()
    {
        // get all status like(approved , rejected) from master table

        $user = Auth::user();
        $master = MasterTable::where('m_group', 'APPROVAL_STATUS')->get();
        if ($master) {
            return ReturnHelper::jsonApiReturn(MasterTableResource::collection($master));
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    public function updateStatus(Request $request)
    {
        // update plan status into 192
        $validated = $request->validate([
            'plan_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $plan_id = $validated['plan_id'];

        $plan = TadaRequestPlan::where('trp_id', $plan_id)
            ->where('trp_emp_id', $user->emp_id)
            ->first();

        if (!$plan) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Plan not found'], 404);
        }

        $plan->trp_request_status = 192;

        if ($plan->save()) {
            return ReturnHelper::jsonApiReturn(PlanRequestApiResource::collection([$plan])->all());
        } else {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Failed to update plan status'], 500);
        }
    }


    public function filterPlan(TravelPlanFilterRequest $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $validated = $request->validated();
        $travel_type = $validated['travel_type'];

        $query = TadaRequestPlan::where('trp_pttt_id', $travel_type)
            ->where('trp_emp_id', $user->emp_id)
            ->where('trp_request_status', '!=', 156)
            ->orderBy('trp_id', 'DESC');

        $data = $query->paginate($limit, ['*'], 'page', $page);

        if ($data->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, FilterPlanRequestApiResource::class));
        }

        return response()->json(['result' => [], 'status' => false, 'message' => 'no record found']);
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $query = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)->whereNotIn('trp_request_status', [139, 192, 156]); // also want to show Local->124
            // ->whereDoesntHave('fh_policy_tada_travel_type', function ($query) use ($user) {
            //     $query->where('pttt_type_id', 124)
            //         ->where('pttt_b_id', $user->emp_b_id);
            // });

        $data = $query->orderBy('trp_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        if ($data)
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, TravelRequestApiResource::class));
        else
            return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TravelPlanRequest $request)
    {
        $user = Auth::user();

        $plan = $request->validated();
        $trp_unique_id = '';
        $data = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)->get()->last();
        if ($data) {
            $trp_unique_id = $data->trp_unique_id;
        }

        $unique_id = CentralLogics::alpha_numeric_generator(4, 'TRP', '', $trp_unique_id);
        $data = new TadaRequestPlan();
        $data->trp_b_id = $user->emp_b_id;
        $data->trp_unique_id = $unique_id;
        $data->trp_br_id = $user->emp_br_id;
        $data->trp_emp_id = $user->emp_id;
        $data->trp_pttt_id = $plan['trp_pttt_id'];
        $data->trp_ptc_id = $plan['trp_ptc_id'];
        $data->trp_advance_allowance = $plan['trp_advance_allowance'];
        $data->trp_name = $plan['trp_name'];
        $data->trp_purpose = $plan['trp_purpose'];
        $data->trp_request_status = $plan['trp_request_status'];
        $data->trp_call_id = $plan['trp_call_id'];

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(PlanRequestApiResource::collection([$data])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
        // Create a new PolicyTadaTravelAllowance instance and set its attributes
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $plan = TadaRequestPlan::find($id);

        if ($plan) {
            return ReturnHelper::jsonApiReturn(PlanRequestApiResource::collection([$plan])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Display the specified resource.
     */
    public function getSingleTravelDetails(string $id)
    {
        $user = Auth::user();
        $details = TadaRequestDetail::where('trd_id', $id)->get();

        if ($details->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(PlanRequestDetailResource::collection($details));
        }

        return response()->json(['result' => [], 'status' => false]);
    }

     /**
     * Display the specified resource.
     */
    public function getSegementsDetails(Request $request, $id)
    {
        $user = Auth::user();
    
        $details = TadaRequestDetail::where('trd_id', $id)
            ->where('trd_trp_id', $request->trp_id)
            ->first();
    
        if ($details) {
            return ReturnHelper::jsonApiReturn(new GetSegementsDetailResource($details));
        }
    
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();

        $plan = TadaRequestPlan::where('trp_id', $id)->first();
        $plan->trp_b_id = $user->emp_b_id ?? $plan->business_id;
        $plan->trp_ptc_id = $request->trp_ptc_id ?? $plan->trp_ptc_id;
        $plan->trp_pttt_id = $request->trp_pttt_id ?? $plan->trp_pttt_id;
        $plan->trp_advance_allowance = $request->trp_advance_allowance ?? $plan->trp_advance_allowance;
        $plan->trp_name = $request->trp_name ?? $plan->trp_name;
        $plan->trp_purpose = $request->trp_purpose ?? $plan->trp_purpose;
        $plan->trp_call_id = $request->trp_call_id ?? $plan->trp_call_id;


        if ($plan->save()) {
            return ReturnHelper::jsonApiReturn(PlanRequestApiResource::collection([TadaRequestPlan::find($plan->trp_id)])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /*public function destroy(string $id)
    {
        $user = Auth::user();
        $plan = TadaRequestPlan::find($id);

        if ($plan) {
            $plan->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }*/

    public function travelRequestApprovalSearch(Request $request)
    {
        $user = Auth::user();
        $filter = $request->all();

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        // $mode = $request->input('mode', 'TadaRequestPlan');

        // === 1. Base Query ===
        /*$query = TadaRequestPlan::with(['fh_employee', 'fh_policy_tada_travel_type','fh_approval_log'])
            ->where('trp_b_id', $user->emp_b_id)
            ->whereNotIn('trp_request_status', [139, 192, 156])
            ->where('trp_is_claimed', '!=', 1);*/

        $query = TadaRequestPlan::with(['fh_employee', 'fh_policy_tada_travel_type', 'fh_approval_log'])
            ->where('trp_b_id', $user->emp_b_id)
            ->whereNotIn('trp_request_status', [139, 192, 156])
            ->where('trp_is_claimed', '!=', 1)
            ->whereHas('fh_approval_log', function ($q) use ($user) {
                $q->where('log_user_id', $user->emp_id); // Only include if current user is in the approval log
        });

        // === 2. Search Filter ===
        if (!empty($filter['value'])) {
            $value = $filter['value'];

            $query->where(function ($q) use ($value) {

                // Match employee name or code (via relationship 'fh_employee')
                $q->where(function ($subQ) use ($value) {
                    $subQ->whereHas('fh_employee', function ($q2) use ($value) {
                        $q2->where(function ($q3) use ($value) {
                            $q3->where('emp_fname', 'LIKE', "%{$value}%")
                                ->orWhere('emp_mname', 'LIKE', "%{$value}%")
                                ->orWhere('emp_lname', 'LIKE', "%{$value}%")
                                ->orWhere('emp_full_name', 'LIKE', "%{$value}%");
                        });
                    })
                    ->orWhereHas('fh_employee', function ($q2) use ($value) {
                        $q2->where('emp_code', 'LIKE', "%{$value}%");
                    });
                });

                // Match travel purpose (via relationship 'fh_travel_purpose')
                $q->orWhereHas('fh_travel_purpose', function ($q2) use ($value) {
                    $q2->where('tp_name', 'LIKE', "%{$value}%");
                });

                $q->orWhereHas('fh_approval_status', function ($q2) use ($value) {
                    $q2->where('m_name', 'LIKE', "%{$value}%");
                });

                // Search via nested relationship: trp_pttt_name (m_name from MasterTable)
                $q->orWhereHas('fh_policy_tada_travel_type.fh_travel_type', function ($q2) use ($value) {
                    $q2->where('m_name', 'LIKE', "%{$value}%");
                });

                // Match direct fields in TadaRequestPlan
                $q->orWhere('trp_purpose', 'LIKE', "%{$value}%")
                  ->orWhere('trp_destination', 'LIKE', "%{$value}%")
                  ->orWhere('trp_unique_id', 'LIKE', "%{$value}%");
            });
        }

        // === 3. Travel Type Filter ===
        if (!empty($filter['travel_type'])) {
            $travel_type = $filter['travel_type'];
            $query->whereHas('fh_policy_tada_travel_type', function ($q) use ($travel_type) {
                $q->where('pttt_id', $travel_type);
            });
        }

        // === 4. 15-day Filter ===
        if (!empty($filter['15_day']) && $filter['15_day'] == 1) {
            $previous_date = Carbon::now()->subDays(15)->toDateString();
            $query->whereDate('created_at', '>=', $previous_date);
        }

        // === 5. Date Range Filter ===
        if (!empty($filter['from_date'])) {
            $from_date = Carbon::parse($filter['from_date'])->startOfDay();
            $to_date = !empty($filter['to_date'])
                ? Carbon::parse($filter['to_date'])->endOfDay()
                : Carbon::now()->endOfDay();

            $query->whereBetween('created_at', [$from_date, $to_date]);
        }

        // === 6. Status Filter ===
        if (!empty($filter['status'])) {
            $query->where('trp_request_status', $filter['status']);
        }

        // === 7. Execute and Return Results ===
        $data = $query->orderBy('trp_id', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);

        return ReturnHelper::jsonApiReturn(
            new PaginatedResource($data, TravelRequestApprovalSearchApiController::class)
        );
    }

}
