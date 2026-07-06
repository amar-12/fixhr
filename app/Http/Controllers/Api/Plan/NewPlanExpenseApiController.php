<?php

namespace App\Http\Controllers\Api\Plan;

use App\Helpers\CentralLogics;
use ChandraHemant\HtkcUtils\CommonUtils;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\TadaExpenseResource;
use App\Http\Resources\TadaExpenseWiseResource;
use App\Http\Resources\TadaExpenseSettingResource;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelType;
use App\Models\TadaExpense;
use App\Models\TadaExpenseSetting;
use App\Models\TadaRequestPlan;
use Carbon\Carbon;
use ChandraHemant\HtkcUtils\PaginatedResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Aws\AwsHelper;
use Illuminate\Support\Facades\Log;

class NewPlanExpenseApiController extends Controller
{
    protected $awsHelper;

    public function __construct(AwsHelper $awsHelper)
    {
        if(env('STORE_ON_S3')){
            $this->awsHelper = $awsHelper;
        }
    }

    public function expense_total(string $id)
    {
        $plan = TadaRequestPlan::find($id);

        if (!$plan) {
            return response()->json(['message' => 'Plan not found.'], 404);
        }

        $totalExpenses = TadaExpense::where('te_trp_id', $plan->trp_id)
            ->where('te_paid_by', 'self')
            ->groupBy('te_type_id')
            ->selectRaw('te_type_id, SUM(te_amount) as total_amount')
            ->selectRaw('te_type_id, SUM(te_taxes) as total_tax')
            ->get();

        $travelTypes = [];
        $overallTotalAmount = 0;
        $overallTotalTax = 0;

        foreach ($totalExpenses as $expense) {
            $type_name = MasterTable::where('m_id', $expense->te_type_id)->first();
            $travelTypes[] = [
                'expense_id' => $expense->te_type_id,
                'expense_name' => $type_name ? $type_name->m_name : 'Unknown',
                'total_amount_inc_tax' => $expense->total_amount + $expense->total_tax
            ];

            $overallTotalAmount += $expense->total_amount;
            $overallTotalTax += $expense->total_tax;
        }

        $overallTotal = $overallTotalAmount + $overallTotalTax;

        return ReturnHelper::jsonApiReturn([[
            'plan_id' => $id,
            'travel_expense' => $travelTypes,
            'total_travel_expense' => $overallTotal,
        ]]);
    }

    public function expenseType(string $pttt_id)
    {
        $pt = PolicyTadaTravelType::where('pttt_id', $pttt_id)->first();
        if ($pt->fh_travel_type->m_id == 124) {
            $expense_type = MasterTable::where('m_group', 'EXPENSE_TYPE')
                ->whereNotIn('m_id', [158, 159])
                ->get();
        } else {
            $expense_type = MasterTable::where('m_group', 'EXPENSE_TYPE')->get();
        }

        if ($expense_type) {
            return ReturnHelper::jsonApiReturn(MasterTableResource::collection($expense_type)->all());
        }

        return response()->json(['result' => [], 'status' => false]);
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        // Querying with orderBy before fetching the collection
        $expense = TadaExpense::whereHas('fh_tada_request_plan', function ($query) use ($user) {
            $query->where('trp_emp_id', $user->emp_id);
        })
            ->orderBy('te_id', 'DESC') // Add orderBy here
            ->with('fh_tada_request_plan')
            ->paginate($limit, ['*'], 'page', $page);

        if ($expense) {
            return ReturnHelper::jsonApiReturn(new PaginatedResource($expense, TadaExpenseResource::class));
        }

        return response()->json(['result' => [], 'status' => false]);
    }


    /*public function store(Request $request)
    {
        $user = Auth::user();
        $expenses = $request->input('expenses');
        $planId = $request->input('planId');
        $data = is_array($expenses) ? $expenses : json_decode($expenses, true);
        $plan = TadaRequestPlan::where('trp_id', $planId)->where('trp_emp_id', $user->emp_id)->first();
        if (!$plan) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No plan found for the given ID or you do not have permission to add expense.']);
        }
        $details = [];

        $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $user->emp_b_id, 'ptc_d_id' => $user->emp_d_id, 'ptc_grade_id' => $user->emp_grade_id])->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();
        if (!$policyCategory) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! policy not found, contact administration.']);
        }

        $tadaExpense = TadaExpense::where('te_trp_id', $planId)->get();

        // Delete the files associated with these records
        if(env('STORE_ON_S3')){
            foreach ($tadaExpense as $detail) {
                    if ($detail->te_document != '' && $detail->te_document != NULL && $detail->te_document != []) {
                            $documents = json_decode($detail->te_document, true);
                            foreach ($documents as $pf) {
                                // Delete from AWS S3
                                $bucket = 'fixhr-uploads';
                                $ObjectURL = ltrim(parse_url($pf, PHP_URL_PATH), '/');
                                $response = $this->awsHelper->deleteFileFromS3($bucket, $ObjectURL);
                                if (!$response['status']) {
                                    \Log::error("Failed to delete from S3: " . $response['message']);
                                }
                            }
                    }
            }
        }else{
            foreach ($tadaExpense as $detail) {
                if ($detail->te_document) {
                    if ($detail->te_document != '' && $detail->te_document != NULL && $detail->te_document != []) {
                        if (is_array($detail->te_document)) {
                            foreach ($detail->te_document  as $pf) {
                                if ((file_exists($pf))) {
                                    unlink($pf);
                                }
                            }
                        } elseif (json_decode($detail->te_document)) {
                            foreach (json_decode($detail->te_document) as $pf) {
                                if ((file_exists($pf))) {
                                    unlink($pf);
                                }
                            }
                        } else {
                            if (file_exists($detail->te_document)) {
                                unlink($detail->te_document);
                            }
                        }
                    }
                }
            }
        }

        TadaExpense::where('te_trp_id', $planId)->delete();

        $lodgingDates = [];
        foreach ($data as $index => $detail) {
            $expense_deviation = 0;
            $te_p_set_amount = 0;
            $te_additional_info = '';
            $response = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')->getEligibilityByPolicyTravelType($policyCategory, $plan->fh_policy_tada_travel_type->pttt_id);

            //Lodging calculation note=>currently lodiging only on outstation travel
            if ($detail['expenseTypeId'] == 158) { //158=='Lodging Expense'
                $date = date('Y-m-d', strtotime($detail['fromDate']));
                $totalLodgingAll = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')->calculateNightStayLodging($detail,  $response['lodging_eligibility']);
                $totalLodging = $totalLodgingAll['lodging']['totalPayableLodgingAmount'];
                $calculationMessage = $totalLodgingAll['lodging']['calculationMessage'];
                if (in_array($date, $lodgingDates)) { //this condition will add expense amount on deviation if multiple lodgings found for the same date
                    $expense_deviation = round($detail['amount']);
                } else {
                    $expense_deviation = (round($detail['amount']) > $totalLodging) ? round($detail['amount']) - $totalLodging : 0;
                }
                $lodgingDates[] = $date;
                $te_additional_info = json_encode($totalLodgingAll);
            }
            //Lodging calculation end


            $uploadedPhotos = [];
            if(env('STORE_ON_S3')){//upload file to AWS S3 storage.
                $bucket = 'fixhr-uploads';
                // if ($detail->te_document != '' && $detail->te_document != NULL && $detail->te_document != []) {
                if (isset($detail->te_document) && !empty($detail->te_document)) {
                    foreach($request->document as $file) {
                        $imageUniqueName = $file->getClientOriginalName();
                        $imagePath = 'ExpenseDocs/'.$user->fh_business->b_unique_id.'/'.time().$imageUniqueName;
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                        if ($uploadResult['status']) {
                            $uploadedPhotos[] = $uploadResult['ObjectURL'];
                        }
                    }
              }
            }else{
                $uploadedPath = CommonUtils::uploadFiles($request, 'document' . $index, 'ExpenseDocs/' . $planId, ['prefix' => 'Expense', 'isApi' => true]);
                if(!empty($uploadedPath)){
                    foreach($uploadedPath as $path) {
                        $uploadedPhotos[] = url($path);
                    }
                }
            }

            //handle date & time conversion
            $fromDate = $toDate = $fromTime = $toTime =  NULL;
            if (isset($detail['fromDate']) && $detail['fromDate']) {
                $fromDate = Carbon::createFromFormat('d M, Y', $detail['fromDate'])->format('Y-m-d');
            }
            if (isset($detail['toDate']) && $detail['toDate']) {
                $toDate = Carbon::createFromFormat('d M, Y', $detail['toDate'])->format('Y-m-d');
            }

            if (isset($detail['fromTime']) && $detail['fromTime']) {
                $fromTime = Carbon::createFromFormat('g:i A', $detail['fromTime'])->format('H:i:s');
            }
            if (isset($detail['toTime']) && $detail['toTime']) {
                $toTime = Carbon::createFromFormat('g:i A', $detail['toTime'])->format('H:i:s');
            }

            if (isset($detail['expenseDate']) && $detail['expenseDate']) {
                try {
                    $te_date = Carbon::parse($detail['expenseDate'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $te_date = NULL;
                }
            }
            if ($detail['expenseTypeId'] == 159) {
                $vehicle = collect($response['vehicle_eligibility'])
                    ->firstWhere(function ($ve) use ($detail) {
                        return $ve['claim_type_id'] == 155 &&  $ve['vehicle_id'] == $detail['te_vehicle_id'] && $ve['vehicle_mode_id'] == $detail['te_mode_id'];
                    });
                $te_p_set_amount = (isset($vehicle['eligibility']) && $vehicle['eligibility']) ?? NULL;
            } else if ($detail['expenseTypeId'] == 158) {
                $te_p_set_amount = $totalLodging;
            }

            $standardCheckOutTime = NULL;
            if (isset($detail['checkoutTime']) && $detail['checkoutTime']) {
                $standardCheckOutTime = Carbon::createFromFormat('g:i A', $detail['checkoutTime'])->format('H:i:s');
            }

            $expense =  TadaExpense::create([
                'te_trp_id' => $planId,
                'te_type_id' => $detail['expenseTypeId'],
                'te_date' => $te_date,
                'te_pttm_id' => $detail['te_mode_id'],
                'te_pttv_id' => $detail['te_vehicle_id'],
                'te_amount' => $detail['amount'],
                'te_sub_expense_id' => (isset($detail['te_sub_expense_id']) && $detail['te_sub_expense_id']) ? $detail['te_sub_expense_id'] : NULL,
                'te_round_trip' => isset($detail['round_trip']) && $detail['round_trip'] ? $detail['round_trip'] : NULL,
                'te_standard_checkout_time' => $standardCheckOutTime,
                'te_deviation' => $expense_deviation,
                'te_name' => isset($detail['expenseName']) && $detail['expenseName'] ? $detail['expenseName'] : NULL,
                'te_from_location' => isset($detail['source']) && $detail['source'] ? $detail['source'] : NULL,
                'te_to_location' => isset($detail['destination']) && $detail['destination'] ? $detail['destination'] : NULL,
                'te_conversion_rate' => isset($detail['conversion_rate']) && $detail['conversion_rate'] ? $detail['conversion_rate'] : NULL,
                'te_foreign_amount' => isset($detail['foreign_amount']) && $detail['foreign_amount'] ? $detail['foreign_amount'] : NULL,
                'te_country_code' => isset($detail['country_code']) && $detail['country_code'] ? $detail['country_code'] : NULL,
                'te_from_date' => $fromDate,
                'te_to_date' => $toDate,
                'te_total_km_driven' => isset($detail['totalDistance']) && $detail['totalDistance'] ? $detail['totalDistance'] : NULL,
                'te_hotel_name' => isset($detail['hotelName']) && $detail['hotelName'] ? $detail['hotelName'] : NULL,
                'te_from_time' => $fromTime,
                'te_to_time' =>  $toTime,
                'te_document' => json_encode($uploadedPhotos),
                'te_taxes' => isset($detail['tax']) && $detail['tax'] ? $detail['tax'] : NULL,
                'te_occupancy' => isset($detail['occupancy']) && $detail['occupancy'] ? $detail['occupancy'] : '',
                'te_paid_by' => isset($detail['paidBy']) && $detail['paidBy'] ? $detail['paidBy'] : '',
                'te_remarks' => isset($detail['remarks']) && $detail['remarks'] ? $detail['remarks'] : NULL,
                'te_p_set_amount' => $te_p_set_amount,
                'te_additional_info' => $te_additional_info,
                'calculation_message' => isset($calculationMessage) ? $calculationMessage : '',
            ]);

            $details[] = $expense;
        }
        if (TadaExpense::where('te_trp_id', $planId)->count()) {
            $plan->trp_is_expense_added = 1;
            $plan->save();
        }
        if ($details) {
            return ReturnHelper::jsonApiReturn(TadaExpenseResource::collection($details)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }*/


    public function store(Request $request)
    {
        $user = Auth::user();

        $expenses = $request->input('expenses');
        $planId = $request->input('planId');
        $data = is_array($expenses) ? $expenses : json_decode($expenses, true);
        $plan = TadaRequestPlan::where('trp_id', $planId)->where('trp_emp_id', $user->emp_id)->first();
        if (!$plan) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No plan found for the given ID or you do not have permission to add expense.']);
        }
        $details = [];

        $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $user->emp_b_id, 'ptc_d_id' => $user->emp_d_id, 'ptc_grade_id' => $user->emp_grade_id])->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();
        if (!$policyCategory) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! policy not found, contact administration.']);
        }

        $tadaExpense = TadaExpense::where('te_trp_id', $planId)->get();
        
        // Delete the files associated with these records
        if(env('STORE_ON_S3')){
            foreach ($tadaExpense as $detail) {
                    if ($detail->te_document != '' && $detail->te_document != NULL && $detail->te_document != []) {
                            $documents = json_decode($detail->te_document, true);
                            foreach ($documents as $pf) {
                                // Delete from AWS S3
                                $bucket = 'fixhr-uploads';
                                $ObjectURL = ltrim(parse_url($pf, PHP_URL_PATH), '/');
                                $response = $this->awsHelper->deleteFileFromS3($bucket, $ObjectURL);
                                if (!$response['status']) {
                                    \Log::error("Failed to delete from S3: " . $response['message']);
                                }
                            }
                    }
            }
        }else{
            foreach ($tadaExpense as $detail) {
                if ($detail->te_document) {
                    if ($detail->te_document != '' && $detail->te_document != NULL && $detail->te_document != []) {
                        if (is_array($detail->te_document)) {
                            foreach ($detail->te_document  as $pf) {
                                if ((file_exists($pf))) {
                                    unlink($pf);
                                }
                            }
                        } elseif (json_decode($detail->te_document)) {
                            foreach (json_decode($detail->te_document) as $pf) {
                                if ((file_exists($pf))) {
                                    unlink($pf);
                                }
                            }
                        } else {
                            if (file_exists($detail->te_document)) {
                                unlink($detail->te_document);
                            }
                        }
                    }
                }
            }
        }

        // TadaExpense::where('te_trp_id', $planId)->delete();

        $lodgingDates = [];
        foreach ($data as $index => $detail) {
            $expense_deviation = 0;
            $te_p_set_amount = 0;
            $te_additional_info = '';
            $response = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')->getEligibilityByPolicyTravelType($policyCategory, $plan->fh_policy_tada_travel_type->pttt_id);

            //Lodging calculation note=>currently lodiging only on outstation travel
            if ($detail['expenseTypeId'] == 158) { //158=='Lodging Expense'
                $date = date('Y-m-d', strtotime($detail['fromDate']));
                $totalLodgingAll = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')->calculateNightStayLodging($detail,  $response['lodging_eligibility']);
                $totalLodging = $totalLodgingAll['lodging']['totalPayableLodgingAmount'];
                $calculationMessage = $totalLodgingAll['lodging']['calculationMessage'];
                if (in_array($date, $lodgingDates)) { //this condition will add expense amount on deviation if multiple lodgings found for the same date
                    $expense_deviation = round($detail['amount']);
                } else {
                    $expense_deviation = (round($detail['amount']) > $totalLodging) ? round($detail['amount']) - $totalLodging : 0;
                }
                $lodgingDates[] = $date;
                $te_additional_info = json_encode($totalLodgingAll);
            }
            //Lodging calculation end


            $uploadedPhotos = [];
            if(env('STORE_ON_S3')){//upload file to AWS S3 storage.
                $bucket = 'fixhr-uploads';
                // if ($detail->te_document != '' && $detail->te_document != NULL && $detail->te_document != []) {
                if (isset($detail->te_document) && !empty($detail->te_document)) {
                    foreach($request->document as $file) {
                        $imageUniqueName = $file->getClientOriginalName();
                        $imagePath = 'ExpenseDocs/'.$user->fh_business->b_unique_id.'/'.time().$imageUniqueName;
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                        if ($uploadResult['status']) {
                            $uploadedPhotos[] = $uploadResult['ObjectURL'];
                        }
                    }
              }
            }else{
                $uploadedPath = CommonUtils::uploadFiles($request, 'document' . $index, 'ExpenseDocs/' . $planId, ['prefix' => 'Expense', 'isApi' => true]);
                if(!empty($uploadedPath)){
                    foreach($uploadedPath as $path) {
                        $uploadedPhotos[] = url($path);
                    }
                }
            }

            //handle date & time conversion
            $fromDate = $toDate = $fromTime = $toTime =  NULL;
            if (isset($detail['fromDate']) && $detail['fromDate']) {
                $fromDate = Carbon::createFromFormat('d M, Y', $detail['fromDate'])->format('Y-m-d');
            }
            if (isset($detail['toDate']) && $detail['toDate']) {
                $toDate = Carbon::createFromFormat('d M, Y', $detail['toDate'])->format('Y-m-d');
            }

            if (isset($detail['fromTime']) && $detail['fromTime']) {
                $fromTime = Carbon::createFromFormat('g:i A', $detail['fromTime'])->format('H:i:s');
            }
            if (isset($detail['toTime']) && $detail['toTime']) {
                $toTime = Carbon::createFromFormat('g:i A', $detail['toTime'])->format('H:i:s');
            }

            $te_date = NUll;
            if (isset($detail['expenseDate']) && $detail['expenseDate']) {
                try {
                    $te_date = Carbon::parse($detail['expenseDate'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $te_date = NULL;
                }
            }
            if ($detail['expenseTypeId'] == 159) {
                $vehicle = collect($response['vehicle_eligibility'])
                    ->firstWhere(function ($ve) use ($detail) {
                        return $ve['claim_type_id'] == 155 &&  $ve['vehicle_id'] == $detail['te_vehicle_id'] && $ve['vehicle_mode_id'] == $detail['te_mode_id'];
                    });
                $te_p_set_amount = (isset($vehicle['eligibility']) && $vehicle['eligibility']) ?? NULL;
            } else if ($detail['expenseTypeId'] == 158) {
                $te_p_set_amount = $totalLodging;
            }

            $standardCheckOutTime = NULL;
            if (isset($detail['checkoutTime']) && $detail['checkoutTime']) {
                $standardCheckOutTime = Carbon::createFromFormat('g:i A', $detail['checkoutTime'])->format('H:i:s');
            }

            $rawExpenses = $request->input('expenses');

            $expenses_id = json_decode($rawExpenses, true);

            if (is_array($expenses_id) && count($expenses_id) > 0)
            {
                $expenseId = $expenses_id[0]['expenseId'] ?? '';
                $expenseTypeId = $expenses_id[0]['expenseTypeId'] ?? '';
            }
            
            // Only check for Lodging (158) or Travel (159)
            if (in_array($expenseTypeId, [158, 159]) && $fromDate && $toDate) {
                $overlappingPlans = TadaExpense::where('te_trp_id', $planId)
                    ->whereIn('te_type_id', [158, 159]) // only Lodging & Travel
                    ->where(function ($query) use ($fromDate, $toDate, $fromTime, $toTime) {
                        // DATE overlap
                        $query->where(function ($q) use ($fromDate, $toDate) {
                            $q->whereBetween('te_from_date', [$fromDate, $toDate])
                              ->orWhereBetween('te_to_date', [$fromDate, $toDate])
                              ->orWhere(function ($q2) use ($fromDate, $toDate) {
                                  $q2->where('te_from_date', '<=', $toDate)
                                     ->where('te_to_date', '>=', $fromDate);
                              });
                        })
                        // TIME overlap (only when same date)
                        ->where(function ($q) use ($fromDate, $toDate, $fromTime, $toTime) {
                            $q->where(function ($q2) use ($fromDate, $toDate, $fromTime, $toTime) {
                                $q2->whereDate('te_from_date', $fromDate)
                                   ->whereDate('te_to_date', $toDate)
                                   ->whereTime('te_from_time', '<', $toTime)
                                   ->whereTime('te_to_time', '>', $fromTime);
                            });
                        });
                    })
                    // If updating, ignore the same expense ID
                    ->when(!empty($expenseId), function ($q) use ($expenseId) {
                        $q->where('te_id', '!=', $expenseId);
                    })
                    ->exists();
            
                if ($overlappingPlans) {
                    return response()->json([
                        'result' => [],
                        'status' => false,
                        'message' => 'The selected date and time range overlaps with an existing active plan for this employee.'
                    ], 422);
                }
            }

            // $expense =  TadaExpense::create([

            $expense = TadaExpense::updateOrCreate(
                ['te_id' => $expenseId ?? null], [
                'te_trp_id' => $planId,
                'te_type_id' => $detail['expenseTypeId'],
                'te_date' => $te_date,
                'te_pttm_id' => $detail['te_mode_id'],
                'te_pttv_id' => $detail['te_vehicle_id'],
                'te_amount' => $detail['amount'],
                'te_sub_expense_id' => (isset($detail['te_sub_expense_id']) && $detail['te_sub_expense_id']) ? $detail['te_sub_expense_id'] : NULL,
                'te_round_trip' => isset($detail['round_trip']) && $detail['round_trip'] ? $detail['round_trip'] : NULL,
                'te_standard_checkout_time' => $standardCheckOutTime,
                'te_deviation' => $expense_deviation,
                'te_name' => isset($detail['expenseName']) && $detail['expenseName'] ? $detail['expenseName'] : NULL,
                'te_from_location' => isset($detail['source']) && $detail['source'] ? $detail['source'] : NULL,
                'te_to_location' => isset($detail['destination']) && $detail['destination'] ? $detail['destination'] : NULL,
                'te_conversion_rate' => isset($detail['conversion_rate']) && $detail['conversion_rate'] ? $detail['conversion_rate'] : NULL,
                'te_foreign_amount' => isset($detail['foreign_amount']) && $detail['foreign_amount'] ? $detail['foreign_amount'] : NULL,
                'te_country_code' => isset($detail['country_code']) && $detail['country_code'] ? $detail['country_code'] : NULL,
                'te_from_date' => $fromDate,
                'te_to_date' => $toDate,
                'te_total_km_driven' => isset($detail['totalDistance']) && $detail['totalDistance'] ? $detail['totalDistance'] : NULL,
                'te_hotel_name' => isset($detail['hotelName']) && $detail['hotelName'] ? $detail['hotelName'] : NULL,
                'te_from_time' => $fromTime,
                'te_to_time' =>  $toTime,
                'te_document' => json_encode($uploadedPhotos),
                'te_taxes' => isset($detail['tax']) && $detail['tax'] ? $detail['tax'] : NULL,
                'te_occupancy' => isset($detail['occupancy']) && $detail['occupancy'] ? $detail['occupancy'] : '',
                'te_paid_by' => isset($detail['paidBy']) && $detail['paidBy'] ? $detail['paidBy'] : '',
                'te_remarks' => isset($detail['remarks']) && $detail['remarks'] ? $detail['remarks'] : NULL,
                'te_p_set_amount' => $te_p_set_amount,
                'te_additional_info' => $te_additional_info,
                'calculation_message' => isset($calculationMessage) ? $calculationMessage : '',
                'te_tolerance_km' => $detail['toleranceKm'],
            ]);

            $details[] = $expense;
        }
       /* if (TadaExpense::where('te_trp_id', $planId)->count()) {
            $plan->trp_is_expense_added = 1;
            $plan->save();
        }
        */
        if ($details) {
            return ReturnHelper::jsonApiReturn(TadaExpenseResource::collection($details)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $expense = TadaExpense::find($id);
        if ($expense->save()) {
            return ReturnHelper::jsonApiReturn(TadaExpenseResource::collection([TadaExpense::find($expense->te_id)])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $data = TadaExpense::find($id);
        $trpexpense = json_decode($request->getContent());
        
        $data->te_trp_id = $request->plan_id ?? $data->te_trp_id;
        $data->te_type_id = $request->type_id ?? $data->te_type_id;
        $data->te_from_location = $request->from_location ?? $data->te_from_location;
        $data->te_to_location = $request->to_location ?? $data->te_to_location;
        $data->te_from_date = $request->from_date ?? $data->te_from_date;
        $data->te_to_date = $request->to_date ?? $data->te_to_date;
        $data->te_date = $request->date ?? $data->te_date;
        $data->te_total_km_driven = $request->docutotal_kmments ?? $data->te_total_km_driven;
        $data->te_hotel_name = $request->hotel_name ?? $data->te_hotel_name;
        $data->te_from_time = $request->from_time ?? $data->trd_te_from_timestart_time;
        $data->te_to_time = $request->to_time ?? $data->te_to_time;
        $data->te_document = $request->document ?? $data->te_document;
        $data->te_taxes = $request->taxes ?? $data->te_taxes;
        $data->te_amount = $request->amount ?? $data->te_amount;
        $data->te_occupancy = $request->occupancy ?? $data->te_occupancy;
        $data->te_paid_by = $request->paid_by ?? $data->te_paid_by;
        $data->te_remarks = $request->remarks ?? $data->te_remarks;
        $data->te_pttm_id = $request->te_mode_id ?? $data->te_pttm_id;
        $data->te_pttv_id = $request->te_vehicle_id ?? $data->te_pttv_id;
        $data->te_tolerance_km = $request->toleranceKm ?? $data->te_tolerance_km;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TadaExpenseResource::collection([$data])->all());
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
        $user_id = $user->fh_employees->emp_id;

        $expense = TadaExpense::where('te_id', $id)
            ->join('tada_request_plan', 'tada_expenses.te_trp_id', '=', 'tada_request_plan.trp_id')
            ->where('tada_request_plan.trp_emp_id', $user_id)
            ->select('tada_expenses.*')
            ->with('fh_tada_request_plan')
            ->first();

        if ($expense) {
            $expense->delete();
            return ReturnHelper::jsonApiReturn(true);
        }

        return response()->json(['result' => [], 'status' => false]);
    }*/

    public function destroy($id)
    {
        $user = Auth::user();
        $tadaExpense = TadaExpense::where('te_id', $id)->first();

        if (!$tadaExpense) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Record not found']);
        }

        // Delete the files associated with the record
        if (env('STORE_ON_S3')) {
            if (!empty($tadaExpense->te_document)) {
                $documents = json_decode($tadaExpense->te_document, true);
                if (is_array($documents)) {
                    foreach ($documents as $pf) {
                        $bucket = 'fixhr-uploads';
                        $ObjectURL = ltrim(parse_url($pf, PHP_URL_PATH), '/');
                        $response = $this->awsHelper->deleteFileFromS3($bucket, $ObjectURL);
                        if (!$response['status']) {
                            \Log::error("Failed to delete from S3: " . $response['message']);
                        }
                    }
                }
            }
        } else {
            if (!empty($tadaExpense->te_document)) {
                $documents = is_array($tadaExpense->te_document)
                    ? $tadaExpense->te_document
                    : json_decode($tadaExpense->te_document, true);

                if (is_array($documents)) {
                    foreach ($documents as $pf) {
                        if (file_exists($pf)) {
                            unlink($pf);
                        }
                    }
                } else {
                    if (file_exists($tadaExpense->te_document)) {
                        unlink($tadaExpense->te_document);
                    }
                }
            }
        }

        if ($tadaExpense) {
            $tadaExpense->delete();
            return response()->json([
                'result' => [],
                'status' => true,
                'message' => 'Expense deleted successfully.'
            ]);
        }

        return response()->json([
            'result' => [],
            'status' => false,
            'message' => 'Expense could not be deleted or does not exist.'
        ]);
    }

    public function getExpenseByPlanId($planId)
    {
        $user = Auth::user();

        $expense = TadaExpense::whereHas('fh_tada_request_plan', function ($query) use ($user, $planId) {
            $query->where(['trp_emp_id' => $user->emp_id, 'te_trp_id' => $planId]);
        })->with('fh_tada_request_plan')->get();

        if ($expense) {
            return ReturnHelper::jsonApiReturn(TadaExpenseResource::collection($expense)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    public function fetchExpenseByPlanId($planId)
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
        ->orderBy('te_id', 'DESC')
        ->get();

        if ($expenses->isNotEmpty()) {
            $grouped = $expenses->groupBy(function($item) {
                $type = $item->fh_expense_type;
                return $type ? $type->m_name : '';
            });
            $transformedGroups = [];

            foreach ($grouped as $type => $items) {
                $total_amount = number_format($items->sum('te_amount'), 2);
                $transformedGroups[] = [
                    'type' => $type . ' - ' . $total_amount . '/-',
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
    }

    public function getSubExpense($expenseId)
    {
        $user = Auth::user();
        $userBId = $user->emp_b_id;

        $expenseSettings = TadaExpenseSetting::where('tes_expense_type_id', $expenseId)
            ->where('tes_b_id', $userBId)
            ->get();

        if ($expenseSettings->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(TadaExpenseSettingResource::collection($expenseSettings)->all());
        }

        return response()->json(['result' => [], 'status' => false]);
    }
}
