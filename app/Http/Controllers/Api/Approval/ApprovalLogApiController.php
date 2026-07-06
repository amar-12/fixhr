<?php

namespace App\Http\Controllers\Api\Approval;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Approval\ApprovalLogApiResource;
use App\Models\ActionUponRejection;
use App\Models\ApprovalLog;
use App\Models\ProcessApprover;
use App\Models\TadaRequestPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalLogApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $action = ApprovalLog::where('log_user_id', $user->emp_id)->first();
        if ($action) {
            $data = ProcessApprover::where('pa_am_id',$action->log_am_id)->where('pa_role_id',$action->log_user_role_id)->first();
            return response()->json(['result' => [$data]]);
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function approval(Request $request)
    {
        $user = Auth::user();
        $action = new ApprovalLog();
        $processApprover = ProcessApprover::where('pa_emp_id', $user->emp_id)->first();
        // $plan = TadaRequestPlan::where('trp_emp_id', $user->emp_id)->first();

		$action->log_am_id = $processApprover->pa_am_id;
		$action->log_request_id = $request->request_id; 
		$action->log_user_id = $user->emp_id;
		$action->log_user_role_id = $user->emp_role_id;
		$action->log_status = $processApprover->pa_status_id;
		$action->log_description = $request->description;

        if($action->save()){
            TadaRequestPlan::where('trp_id', $request->request_id)->update(['trp_request_status'=>$action->log_status]);
            return ReturnHelper::jsonApiReturn(ApprovalLogApiResource::collection([$action])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }
    public function actionRejection(Request $request)
    {
        $user = Auth::user();
        $action = new ApprovalLog();
        $reject = ActionUponRejection::where('aur_b_id', $user->b_id)->where('aur_am_id', $request->module_id)->first();

		$action->log_am_id = $reject->aur_am_id;
		$action->log_request_id = $request->request_id;
		$action->log_user_id = $user->emp_id;
		$action->log_user_role_id = $user->emp_role_id;
		$action->log_status = $reject->aur_status_id;
		$action->log_description = $request->description;


        if($action->save()){
            TadaRequestPlan::where('trp_id', $request->request_id)->update(['trp_request_status'=>$action->log_status]);
            return ReturnHelper::jsonApiReturn(ApprovalLogApiResource::collection([$action])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $action = ApprovalLog::find($id);
        if ($action) {
            return ReturnHelper::jsonApiReturn(ApprovalLogApiResource::collection([$action])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $action = ApprovalLog::find($id);
        $action->log_am_id = $request->module_id ?? $action->log_am_id ;
		$action->log_request_id = $request->request_id ?? $action->log_request_id ;
		$action->log_user_id = $request->user_id ?? $action->log_user_id ;
		$action->log_user_role_id = $request->user_role_id ?? $action->log_user_role_id ;
		$action->log_status = $request->status ?? $action->log_status ;
        $action->log_description = $request->description ??  $action->log_description;

        if($action->save()){
            return ReturnHelper::jsonApiReturn(ApprovalLogApiResource::collection([$action])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $action = ApprovalLog::find($id);
        if ($action->delete()) {
            return response()->json(['result' => true, 'status' => true]);
        }
        return response()->json(['result' => [], 'status' => false]);
    }
}
