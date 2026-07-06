<?php

namespace App\Http\Controllers\Api\Approval;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Approval\ApprovalModuleApiResource;
use App\Models\ApprovalModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalModuleApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $user = Auth::user();
       $data = ApprovalModule::where('am_b_id',$user->emp_b_id)->get();

       if($data){
         return ReturnHelper::jsonApiReturn(ApprovalModuleApiResource::collection($data)->all());
       }else{
         return response()->json(['result' => [],'status' => false]);
       }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = new ApprovalModule();

        $data->am_b_id = $user->emp_b_id;
		$data->am_module_id = $request->module_id;
		$data->am_name = $request->name;
		$data->am_description = $request->description;
		$data->am_exe_on = $request->exe_on;
		$data->am_status = $request->status;

        if($data-> save() ) {
            return ReturnHelper::jsonApiReturn(ApprovalModuleApiResource::collection([$data])->all());
          }else{
            return response()->json(['result' => [],'status' => false]);
          }
      }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = ApprovalModule::find($id);
        if($data){
            return ReturnHelper::jsonApiReturn(ApprovalModuleApiResource::collection([$data])->all());
          }else{
            return response()->json(['result' => [],'status' => false]);
          }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = ApprovalModule::find($id);
;
		$data->am_module_id=$request->module_id ?? $data->am_module_id;
		$data->am_name=$request->name ?? $data->am_name;
		$data->am_description=$request->description ?? $data->am_description;
		$data->am_exe_on=$request->exe_on ?? $data->am_exe_on;
		$data->am_status=$request->status ?? $data->am_status;

        if($data->save()){
            return ReturnHelper::jsonApiReturn(ApprovalModuleApiResource::collection([$data])->all());
          }else{
            return response()->json(['result' => [],'status' => false]);
          }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = ApprovalModule::find($id);
        if($data-> delete()){
            return response()->json(['result' => true,'status' => true]);
          }else{
            return response()->json(['result' => [],'status' => false]);
          }
    }
}
