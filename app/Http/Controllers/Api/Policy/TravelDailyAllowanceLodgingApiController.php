<?php

namespace App\Http\Controllers\Api\Policy;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policy\TravelDailyAllowanceLodgingRequest;
use App\Http\Resources\Policy\TravelDailyAllowanceLodgingResource;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaDailyAllowanceLodging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelDailyAllowanceLodgingApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $data = PolicyTadaDailyAllowanceLodging::where('ptdal_b_id', $user->emp_b_id)->get();

        if ($data) {
            return ReturnHelper::jsonApiReturn(TravelDailyAllowanceLodgingResource::collection($data)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TravelDailyAllowanceLodgingRequest $request)
    {
        $user = Auth::user();

        $value = $request->validated();
        $data = new PolicyTadaDailyAllowanceLodging();
        $data->ptdal_b_id = $user->emp_b_id;
        $data->ptdal_ptc_id = $value['category_id'];
        $data->ptdal_ct_type_id = $value['city_type_id'];
        $data->ptdal_da_per_day_elig = $value['da_eligibility'];
        $data->ptdal_da_same_day_ret_elig = $value['same_day_eligibility'];
        $data->ptdal_same_day_remark = $value['remark'];


        $data->ptdal_lodg_sngl_w_bill_elig = $value['eligibility_bill'];
        $data->ptdal_lodg_sngl_wo_bill_elig = $value['eligibility_no_bill'];

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TravelDailyAllowanceLodgingResource::collection([PolicyTadaDailyAllowanceLodging::find($data->ptdal_id)])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $data = PolicyTadaDailyAllowanceLodging::where('ptdal_ptc_id', $id)->where('ptdal_b_id', $user->emp_b_id)->first();
        if ($data) {
            $cat = PolicyTadaCategory::where('ptc_id', $data->ptdal_ptc_id)->where('ptc_d_id', $user->emp_d_id)->where('ptc_grade_id', $user->emp_grade_id)->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->get();

            if ($cat) {
                return ReturnHelper::jsonApiReturn(TravelDailyAllowanceLodgingResource::collection([$data])->all());
            }
            return response()->json(['result' => [], 'status' => false]);
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

        $data = PolicyTadaDailyAllowanceLodging::find($id);
        $data->ptdal_b_id = $user->emp_b_id ?? $data->ptdal_b_id;
        $data->ptdal_ptc_id = $request->category_id ?? $data->ptdal_ptc_id;
        $data->ptdal_ct_type_id = $request->city_type_id ?? $data->ptdal_ct_type_id;
        $data->ptdal_da_per_day_elig = $request->da_eligibility ?? $data->ptdal_da_per_day_elig;
        $data->ptdal_da_same_day_ret_elig = $request->same_day_eligibility ?? $data->ptdal_da_same_day_ret_elig;
        $data->ptdal_same_day_remark = $request->remark ?? $data->ptdal_same_day_remark;
        $data->ptdal_lodg_sngl_w_bill_elig = $request->eligibility_bill ?? $data->ptdal_lodg_sngl_w_bill_elig;
        $data->ptdal_lodg_sngl_wo_bill_elig = $request->eligibility_no_bill ?? $data->ptdal_lodg_sngl_wo_bill_elig;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TravelDailyAllowanceLodgingResource::collection([$data])->all());
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
        $data = PolicyTadaDailyAllowanceLodging::where('ptdal_id', $id)
            ->where('ptdal_b_id', $user->emp_b_id)
            ->first();

        if ($data) {
            $data->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }
}
