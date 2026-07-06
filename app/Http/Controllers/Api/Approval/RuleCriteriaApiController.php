<?php

namespace App\Http\Controllers\Api\Approval;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Approval\RuleCriteriaApiResource;
use App\Models\RuleCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RuleCriteriaApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $data = RuleCriterion::where('rc_b_id', $user->emp_b_id)->get();

        if ($data) {
            return ReturnHelper::jsonApiReturn(RuleCriteriaApiResource::collection($data)->all());
        }
        return response()->json(['result' => [],'status' => false]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $user = Auth::user();
        $data = new RuleCriterion();

        $data->rc_b_id = $user->emp_b_id;
	    $data->rc_am_id = $request->am_id;
	    $data->rc_approval_rule_id = $request->approval_rule_id;
	    $data->rc_rule_condition_id = $request->rule_condition_id;
	    $data->rc_condition_option_id = $request->condition_option_id;
	    $data->rc_custom_value = $request->custom_value;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(RuleCriteriaApiResource::collection([$data])->all());
        }
        return response()->json(['result' => [],'status' => false]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = RuleCriterion::find($id);
        if ($data) {
            return ReturnHelper::jsonApiReturn(RuleCriteriaApiResource::collection([$data])->all());
        }
        return response()->json(['result' => [],'status' => false]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = RuleCriterion::find($id);

	    $data->rc_am_id = $request->am_id ?? $data->am_id;
	    $data->rc_approval_rule_id = $request->approval_rule_id ?? $data->approval_rule_id;
	    $data->rc_rule_condition_id = $request->rule_condition_id ?? $data->rule_condition_id;
	    $data->rc_condition_option_id = $request->condition_option_id ?? $data->condition_option_id;
	    $data->rc_custom_value = $request->custom_value ?? $data->custom_value;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(RuleCriteriaApiResource::collection([$data])->all());
        }
        return response()->json(['result' => [],'status' => false]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = RuleCriterion::find($id);
        if ($data->delete()) {
            return response()->json(['result' => true,'status' => true]);
        }
        return response()->json(['result' => [],'status' => false]);
    }
}
