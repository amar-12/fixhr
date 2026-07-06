<?php

namespace App\Http\Controllers\Api\Policy;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policy\TravelOwnerAndClassRequest;
use App\Http\Requests\Policy\TravelVehicleFilterRequest;
use App\Http\Requests\Policy\TravelVehicleRequest;
use App\Http\Requests\Policy\TravelVehicleUpdateRequest;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\Policy\TravelVehicleResource;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelAllowance;
use App\Models\PolicyTadaTravelMode;
use App\Models\PolicyTadaTravelVehicle;
use App\Models\TadaRequestDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelVehicleApiController extends Controller
{

    public function filterVehicle(TravelVehicleFilterRequest $request)
    {
        // Filter for choose mode and get vehicle from master table
        $user = Auth::user();
        $data = $request->validated();
        $mode_id = $data['travel_mode'];

        $category = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)
            ->where('ptc_d_id', $user->emp_d_id)
            ->whereJsonContains('ptc_dg_id', $user->emp_dg_id)
            ->where('ptc_grade_id', $user->emp_grade_id)
            ->first();

        if (!$category) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No category information found for the user']);
        }

        $vehicleAllowance = PolicyTadaTravelVehicle::where(['pttv_b_id'=>$user->emp_b_id, 'pttv_ptc_id'=>$category->ptc_id,'pttv_pttm_id'=>$mode_id])->get();

        // $filteredData = $vehicle->filter(function ($details) use ($user, $category) {
        //     $vehicleCheck = PolicyTadaTravelAllowance::where('ptta_b_id', $user->emp_b_id)
        //         ->where('ptta_pttv_id', $details->pttv_id)
        //         ->where('ptta_ptc_id', $category->ptc_id)
        //         ->exists();

        //     return $vehicleCheck;
        // });

        if ($vehicleAllowance->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(TravelVehicleResource::collection($vehicleAllowance)->all());
        }

        return response()->json(['result' => [], 'status' => false, 'message' => 'No vehicle information found on your selected mode']);
    }


    public function filter(TravelOwnerAndClassRequest $request)
    {
        // Filter for chosen vehicle and get master owner & class
        $data = $request->validated();
        $vehicle = $data['vehicle_id'];

        $master = MasterTable::whereIn('m_group', ['VEHICLE_OWNER', 'TRAVEL_CLASS'])
            ->whereJsonContains('m_description', json_decode($vehicle))
            ->get();

        if ($master->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(MasterTableResource::collection($master)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $vehicle = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)->get();
        if ($vehicle) {
            return ReturnHelper::jsonApiReturn(TravelVehicleResource::collection($vehicle)->all());
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
    public function store(TravelVehicleRequest $request)
    {
        $user = Auth::user();
        $mode = $request->validated();

        $data = new PolicyTadaTravelVehicle();

        $data->pttv_b_id = $user->emp_b_id;
        $tavelMode = PolicyTadaTravelMode::where('pttm_id',$mode['mode_id'])->first();
        if($tavelMode){
            $data->pttv_pttm_id = $mode['mode_id'];

            $mTableVehicle = MasterTable::where('m_group','VEHICLE')->where('m_description',$tavelMode->pttm_by_mode_id)->pluck('m_id');
            if($mTableVehicle->contains($mode['vehicle_id'])){
                $data->pttv_vehicle_id = $mode['vehicle_id'];
                $mTable = MasterTable::whereIn('m_group', ['TRAVEL_CLASS','VEHICLE_OWNER'])->whereJsonContains('m_description', (int) $mode['vehicle_id'])->get(['m_id','m_group']);
                foreach($mTable as $table){
                    if($table->m_id == $mode['owner_id'] && $table->m_group == 'VEHICLE_OWNER'){
                        $data->pttv_owner_id = $mode['owner_id'];
                    }elseif($table->m_id == $mode['class_id'] && $table->m_group == 'TRAVEL_CLASS'){
                        $data->pttv_class_id = $mode['class_id'];
                    }
                }
                if($data->pttv_owner_id==null && $data->pttv_class_id==null){
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Owner Id or Class Id not available']);
                }
            }else{
                return response()->json(['result' => [], 'status' => false, 'message' => 'Vehicle Id not available']);
            }
        }else{
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Mode Id not available']);
        }
        if($data->save()){
            return ReturnHelper::jsonApiReturn(TravelVehicleResource::collection([$data])->all());
        }
        return response()->json(['result' => [], 'status' => false]);

     }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $vehicle = PolicyTadaTravelVehicle::find($id);
        if ($vehicle) {
            return ReturnHelper::jsonApiReturn(TravelVehicleResource::collection([$vehicle])->all());
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
    public function update(TravelVehicleUpdateRequest $request, string $id)
    {
        $user = Auth::user();
        $mode = $request->validated();

        $tRequestDetailsTable = TadaRequestDetail::where('trd_pttv_id', $id)->first();
        if(!$tRequestDetailsTable){

        $data = PolicyTadaTravelVehicle::where('pttv_id',$id)->where('pttv_b_id',$user->emp_b_id)->first();
        if(!$data){
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Type not found']);
        }

        $mTableVehicle= MasterTable::where('m_group','VEHICLE')->pluck('m_id');
        if($mTableVehicle->contains($mode['vehicle_id'])){
            $data->pttv_vehicle_id = $mode['vehicle_id'] ?? $data->pttv_vehicle_id ;
            $mTable = MasterTable::whereIn('m_group', ['TRAVEL_CLASS','VEHICLE_OWNER'])->get(['m_id','m_group']);
                foreach($mTable as $table){
                    if($table->m_id == $mode['owner_id'] && $table->m_group == 'VEHICLE_OWNER'){
                        $data->pttv_owner_id = $mode['owner_id'] ??  $data->pttv_owner_id  ;
                    }elseif($table->m_id == $mode['class_id'] && $table->m_group == 'TRAVEL_CLASS'){
                        $data->pttv_class_id = $mode['class_id']??  $data->pttv_class_id ;
                    }
                }

            }else{
                return response()->json(['result' => [], 'status' => false, 'message' => 'Vehicle Id not available']);
            }
        }else{
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Mode Id not available']);

        }
        if($data->save()){
            return ReturnHelper::jsonApiReturn(TravelVehicleResource::collection([$data])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $vehicle = PolicyTadaTravelVehicle::where('pttv_id', $id)
        ->where('pttv_b_id', $user->emp_b_id)
        ->first();

        if ($vehicle) {
            $vehicle->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }


}
