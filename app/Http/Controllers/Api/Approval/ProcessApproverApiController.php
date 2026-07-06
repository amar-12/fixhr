<?php

namespace App\Http\Controllers\Api\Approval;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Approval\ProcessApproverApiRequest;
use App\Http\Resources\Approval\ProcessApproverApiResource;
use App\Models\ApprovalModule;
use App\Models\MasterTable;
use App\Models\ProcessApprover;
use App\Models\RuleCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LDAP\Result;

class ProcessApproverApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $empId = $user->emp_id;

        $approval = ProcessApprover::where('pa_b_id', $businessId)->where('pa_emp_id', $empId)->first();
        $data = ApprovalModule::where('am_b_id', $businessId)->first();

        if (isset($approval->pa_am_id) && $approval->pa_am_id == $data->am_module_id) {
            $appmod = ApprovalModule::where('am_b_id', $businessId)->pluck('am_id');
            if (isset($appmod)) {
                $rule = RuleCriterion::where('rc_b_id', $businessId)->where('rc_am_id', $appmod)->pluck('rc_condition_option_id');

                if (isset($rule)) {
                    return response()->json(['result' => true, 'status' => true]);
                }
                return response()->json(['result' => false, 'status' => false]);
            }
            return response()->json(['result' => false, 'status' => false]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessApproverApiRequest $request)
    {
        $user = Auth::user();
        $data = new ProcessApprover();

        $data->pa_b_id = $user->emp_b_id;
        $data->pa_am_id = $request->module_id;
        $data->pa_type = $request->type;
        $data->pa_role_id = $request->role_id;
        $data->pa_emp_id = $request->emp_id;
        $data->pa_status_id = $request->message_id;
        $data->pa_sequence = $request->sequence;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(ProcessApproverApiResource::collection([$data])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = ProcessApprover::find($id);
        if ($data) {
            return ReturnHelper::jsonApiReturn(ProcessApproverApiResource::collection([$data])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessApproverApiRequest $request, string $id)
    {

        $data = ProcessApprover::find($id);


        $data->pa_am_id = $request->module_id ?? $data->pa_am_id;
        $data->pa_type = $request->type ?? $data->pa_type;
        $data->pa_role_id = $request->role_id ?? $data->pa_role_id;
        $data->pa_emp_id = $request->emp_id ?? $data->pa_emp_id;
        $data->pa_status_id = $request->message_id ?? $data->pa_status_id;
        $data->pa_sequence = $request->sequence ?? $data->pa_sequence;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(ProcessApproverApiResource::collection([$data])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = ProcessApprover::find($id);
        if ($data->delete()) {
            return response()->json(['result' => true, 'status' => true]);
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    public function approval_button()
    {
        $user = Auth::user();
        $data = ProcessApprover::where('pa_b_id', $user->emp_b_id)->get();

        if ($data) {
            return ReturnHelper::jsonApiReturn(ProcessApproverApiResource::collection($data)->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }
}
