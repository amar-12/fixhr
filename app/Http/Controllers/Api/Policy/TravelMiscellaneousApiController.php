<?php

namespace App\Http\Controllers\Api\Policy;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policy\TravelMiscellaneousRequest;
use App\Http\Resources\Policy\TravelMiscellaneousResource;
use App\Models\PolicyTadaMiscellaneous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelMiscellaneousApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $Miscellaneous = PolicyTadaMiscellaneous::where('pm_b_id', $user->emp_b_id)->get();

        if ($Miscellaneous) {
            return ReturnHelper::jsonApiReturn(TravelMiscellaneousResource::collection($Miscellaneous)->all());
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
    public function store(TravelMiscellaneousRequest $request)
    {
        $user = Auth::user();
        $MiscellaneousRequest = $request->validated();

        $data = new PolicyTadaMiscellaneous();
        $data->pm_b_id = $user->emp_b_id;
        $data->pm_ptc_id = $MiscellaneousRequest['category_id'];
        $data->pm_miscellaneous_id = $MiscellaneousRequest['miscellaneous_id'];
        $data->pm_ct_type_id = $MiscellaneousRequest['city_type_id'];
        $data->pm_eligibility = $MiscellaneousRequest['eligibility'];
        $data->pm_remarks = $MiscellaneousRequest['remarks'];

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TravelMiscellaneousResource::collection([PolicyTadaMiscellaneous::find($data->pm_id)])->all());
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
        $Miscellaneous = PolicyTadaMiscellaneous::find($id);
        if ($Miscellaneous) {
            return ReturnHelper::jsonApiReturn(TravelMiscellaneousResource::collection($Miscellaneous)->all());
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
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $Miscellaneous = PolicyTadaMiscellaneous::find($id);
        $Miscellaneous->pm_b_id = $user->emp_b_id ?? $Miscellaneous->pm_b_id;
        $Miscellaneous->pm_ptc_id = $request->category_id ?? $Miscellaneous->pm_ptc_id;
        $Miscellaneous->pm_miscellaneous_id = $request->miscellaneous_id ?? $Miscellaneous->pm_miscellaneous_id;
        $Miscellaneous->pm_ct_type_id = $request->city_type_id ?? $Miscellaneous->pm_ct_type_id;
        $Miscellaneous->pm_eligibility = $request->eligibility ?? $Miscellaneous->pm_eligibility;
        $Miscellaneous->pm_remarks = $request->remarks ?? $Miscellaneous->pm_remarks;

        if ($Miscellaneous->save()) {
            return ReturnHelper::jsonApiReturn(TravelMiscellaneousResource::collection([$Miscellaneous])->all());
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
        $Miscellaneous = PolicyTadaMiscellaneous::where('pm_id', $id)
        ->where('pm_b_id', $user->emp_b_id)
        ->first();

        if ($Miscellaneous) {
            $Miscellaneous->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }
}
