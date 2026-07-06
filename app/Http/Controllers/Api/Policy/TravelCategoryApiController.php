<?php

namespace App\Http\Controllers\Api\Policy;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policy\TravelCategoryRequest;
use App\Http\Requests\Policy\TravelCategoryUpdateRequest;
use App\Http\Resources\Policy\TravelCategoryResource;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelCategoryApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $category = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->get();

        if ($category) {
            return ReturnHelper::jsonApiReturn(TravelCategoryResource::collection($category)->all());
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
    public function store(TravelCategoryRequest $request)
    {
        $user = Auth::user();
        $category = $request->validated();

        $data = new PolicyTadaCategory();
        $data->ptc_b_id = $user->emp_b_id;
        $data->ptc_d_id = $category['department_id'];
        $data->ptc_name = $category['category_name'];
        $data->ptc_dg_id = json_encode(array_map('intval', $category['designation_id']));
        $data->ptc_grade_id = $category['grade_id'];
        $data->ptc_pttt_id = json_encode($category['travel_type_id']); // Convert array to JSON string

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TravelCategoryResource::collection([PolicyTadaCategory::find($data->ptc_id)])->all());
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
        $category = PolicyTadaCategory::find($id);
        if ($category) {
            return ReturnHelper::jsonApiReturn(TravelCategoryResource::collection($category)->all());
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
        $mode = $request->validated();

        $travelTypeIds = is_array($mode['travel_type_id']) ? $mode['travel_type_id'] : [$mode['travel_type_id']];

        $data = PolicyTadaCategory::where('ptc_id', $id)->first();

        if (!$data) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Travel Type not found']);
        }

        $validTravelTypeIds = [];
        foreach ($travelTypeIds as $travelTypeId) {
            $tVehicleTable = PolicyTadaTravelType::where('pttt_id', $travelTypeId)->all();
            if ($tVehicleTable) {
                $validTravelTypeIds[] = (string)$travelTypeId; // Ensure IDs are strings
            }
        }

        if (empty($validTravelTypeIds)) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'No valid Travel Type IDs found']);
        }

        $data->ptc_pttt_id = json_encode($validTravelTypeIds) ?? $data->ptc_pttt_id;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TravelCategoryResource::collection([PolicyTadaCategory::find($data->ptc_id)])->all());
        }

        return response()->json(['result' => [], 'status' => false]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $category = PolicyTadaCategory::where('ptc_id', $id)
            ->where('ptc_b_id', $user->emp_b_id)
            ->first();

        if ($category) {
            $category->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }
}
