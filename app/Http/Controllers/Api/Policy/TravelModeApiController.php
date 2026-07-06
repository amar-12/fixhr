<?php

namespace App\Http\Controllers\Api\Policy;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policy\FilterModeRequest;
use App\Http\Requests\Policy\TravelModeRequest;
use App\Http\Resources\Policy\TravelModeResource;
use App\Models\MasterTable;
use App\Models\PolicyTadaTravelMode;
use App\Models\PolicyTadaTravelType;
use App\Models\PolicyTadaTravelVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelModeApiController extends Controller
{
    public function filterMode(FilterModeRequest $request)
    {
        $user = Auth::user();
        $type = $request->validated();
        $travel_type = $type['travel_type'];
        $data = PolicyTadaTravelMode::where('pttm_pttt_id', $travel_type)->where('pttm_b_id', $user->emp_b_id)->where('pttm_status', 1)->get();

        if ($data->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(TravelModeResource::collection($data)->all());
        }
        return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Mode not found']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $model = PolicyTadaTravelMode::where('pttm_b_id', $user->emp_b_id)->get();
        if ($model) {
            return ReturnHelper::jsonApiReturn(TravelModeResource::collection($model)->all());
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
    public function store(TravelModeRequest $request)
    {
        $user = Auth::user();
        $mode = $request->validated();
        if(PolicyTadaTravelMode::where('pttm_pttt_id',$mode['travel_type_id'])->where('pttm_by_mode_id',$mode['travel_mode_id'])->where('pttm_b_id',$user->emp_b_id)->first()){
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Mode and Travel Type both already exist in current business']);
        }
        $data = new PolicyTadaTravelMode();

        $data->pttm_b_id = $user->emp_b_id;

        $tavelMode = PolicyTadaTravelType::where('pttt_id',$mode['travel_type_id'])->first();
        // return response()->json(['message' => [$tavelMode]]);

        if($tavelMode){
            $data->pttm_pttt_id = $mode['travel_type_id'];

            $mTableMode =MasterTable::where('m_group','TRAVEL_MODE')->pluck('m_id');
            if($mTableMode->contains($mode['travel_mode_id'])){
                $data->pttm_by_mode_id = $mode['travel_mode_id'];
            }else{
                return response()->json(['result' => [], 'status' => false, 'message' => 'Vehicle Id not available']);
            }
        }else{
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Mode Id not available']);
        }
        if($data->save()){
            return ReturnHelper::jsonApiReturn(TravelModeResource::collection([$data])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $show = PolicyTadaTravelMode::find($id);
        if ($show) {
            return ReturnHelper::jsonApiReturn(TravelModeResource::collection($show)->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
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
    public function update(TravelModeRequest $request, string $id)
    {

    $user = Auth::user();
    $mode = $request->validated();

    $tVehicleTable = PolicyTadaTravelVehicle::find($id);
    if(!$tVehicleTable){

    $data = PolicyTadaTravelMode::where('pttm_id',$id)->where('pttm_b_id',$user->emp_b_id)->first();
    if(!$data){
        return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Type not found']);
    }

    $data->pttm_by_mode_id = $mode['travel_mode_id'];
    $data->pttm_pttt_id = $mode['travel_type_id'];
    if($data->save()){
        return ReturnHelper::jsonApiReturn(TravelModeResource::collection([$data])->all());
    }
    }else{
        return response()->json(['result' => [], 'status' => false, 'message' => 'This ID is already associated elsewhere you cannot be updated.']);
    }
    return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $mode = PolicyTadaTravelMode::where('pttm_id', $id)
        ->where('pttm_b_id', $user->emp_b_id)
        ->first();

        if ($mode) {
            $mode->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }






}
