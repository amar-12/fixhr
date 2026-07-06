<?php

namespace App\Http\Controllers\Api\Policy;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policy\TravelTypeRequest;
use App\Http\Requests\Policy\TravelTypeUpdateRequest;
use App\Http\Resources\TadaTravelTypeResource;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelTypeApiController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $category = PolicyTadaCategory::where('ptc_b_id',$user->emp_b_id)->where('ptc_d_id',$user->emp_d_id)->where('ptc_grade_id',$user->emp_grade_id)->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();

        if(!isset($category)){
            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! you are not eligible for any travel category']);
        }
        $travel_type = PolicyTadaTravelType::whereIn('pttt_id', json_decode($category->ptc_pttt_id))->where('pttt_status', 1)->get();

        if ($travel_type) {
            return ReturnHelper::jsonApiReturn(TadaTravelTypeResource::collection($travel_type)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    public function travelType(){
        $user = auth::user();

        $travel_type = PolicyTadaTravelType::where('pttt_b_id', $user->emp_b_id)->where('pttt_status', 1)->get();

        if ($travel_type) {
            return ReturnHelper::jsonApiReturn(TadaTravelTypeResource::collection($travel_type)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Store a newly created resource in storage.
     */


     
    public function store(TravelTypeRequest $request)
    {
        $user = Auth::user();
        $mode = $request->validated();
        if (PolicyTadaTravelType::where('pttt_type_id', $mode['travel_id'])->where('pttt_b_id', $user->emp_b_id)->first()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Type already exist in current business']);
        }
        $data = new PolicyTadaTravelType();

        $data->pttt_b_id = $user->emp_b_id;

        $mTableType = MasterTable::where('m_group', 'TRAVEL_TYPE')->pluck('m_id');
        if ($mTableType->contains($mode['travel_id'])) {
            $data->pttt_type_id = $mode['travel_id'];
            $data->pttt_status = $mode['travel_status'];
        } else {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Type not available']);
        }
        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TadaTravelTypeResource::collection([$data])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $travel_type = PolicyTadaTravelType::find($id);
        if ($travel_type) {
            return ReturnHelper::jsonApiReturn(TadaTravelTypeResource::collection([$travel_type])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TravelTypeUpdateRequest $request, string $id)
    {
        $user = Auth::user();
        $mode = $request->validated();

        $data = PolicyTadaTravelType::where('pttt_id', $id)->where('pttt_b_id', $user->emp_b_id)->first();
        if (!$data) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Type not found']);
        }

        $data->pttt_status = $mode['travel_status'] ?? $data->pttt_status;
        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TadaTravelTypeResource::collection([$data])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $type = PolicyTadaTravelType::where('pttt_id', $id)
            ->where('pttt_b_id', $user->emp_b_id)
            ->first();

        if ($type) {
            $type->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }
}
