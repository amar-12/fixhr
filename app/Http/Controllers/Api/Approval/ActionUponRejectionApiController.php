<?php

namespace App\Http\Controllers\Api\Approval;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Approval\ActionUponRejectionApiResource;
use App\Models\ActionUponRejection;
use App\Models\ProcessApprover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionUponRejectionApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $user = Auth::user();
        $action = ActionUponRejection::where('aur_b_id', $user->emp_b_id)->get();
        if ($action) {
            return ReturnHelper::jsonApiReturn(ActionUponRejectionApiResource::collection($action)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $action = new ActionUponRejection();

        $plan = ProcessApprover::where('pa_emp_id', $user->emp_id)->where('pa_am_id', $request->module_id)->first();
        // $plan = TadaRequestPlan::where('trp_emp_id', $user->emp_id)->first();


        $action->aur_b_id = $user->emp_b_id;
		$action->aur_am_id = $request->module_id;
		$action->aur_group_ids = $request->user_ids;
		$action->aur_status_id = $request->status_id;
		$action->aur_description = $request->description;
        $action->aur_role_id = $request->role_id;

        if($action->save()){
            return ReturnHelper::jsonApiReturn(ActionUponRejectionApiResource::collection([$action])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $action = ActionUponRejection::find($id);
        if ($action) {
            return ReturnHelper::jsonApiReturn(ActionUponRejectionApiResource::collection([$action])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $action = ActionUponRejection::find($id);
		$action->aur_am_id = $request->module_id ?? $action->aur_am_id;
		$action->aur_group_ids = $request->user_ids ?? $action->aur_group_ids;
		$action->aur_status_id = $request->status_id ?? $action->aur_status_id;
		$action->aur_description = $request->description ?? $action->aur_description;
        $action->aur_role_id = $request->role_id ?? $action->aur_role_id;

        if($action->save()){
            return ReturnHelper::jsonApiReturn(ActionUponRejectionApiResource::collection([$action])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $action = ActionUponRejection::find($id);
        if ($action->delete()) {
            return response()->json(['result' => true, 'status' => true]);
        }
        return response()->json(['result' => [], 'status' => false]);
    }
}
