<?php

namespace App\Http\Controllers;

use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $policies = PrivacyPolicy::all();
        return response()->json([
            'status' => true,
            'message' => 'Privacy policies fetched successfully',
            'result' => $policies
        ]);
    }

    /**
     * Display a listing of the resource in a web view.
     */
    public function webIndex()
    {
        $policies = PrivacyPolicy::all();
        return view('privacy-policy-list', compact('policies'));
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pp_title' => 'required|string|max:255',
            'pp_description' => 'required|string',
        ]);
        $policy = PrivacyPolicy::create($validated);
        return response()->json([
            'status' => true,
            'message' => 'Privacy policy created successfully',
            'result' => $policy
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PrivacyPolicy $privacyPolicy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PrivacyPolicy $privacyPolicy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PrivacyPolicy $privacyPolicy)
    {
        $validated = $request->validate([
            'pp_title' => 'required|string|max:255',
            'pp_description' => 'required|string',
        ]);
        $privacyPolicy->update($validated);
        return response()->json([
            'status' => true,
            'message' => 'Privacy policy updated successfully',
            'result' => $privacyPolicy
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PrivacyPolicy $privacyPolicy)
    {
        $privacyPolicy->delete();
        return response()->json([
            'status' => true,
            'message' => 'Privacy policy deleted successfully',
            'result' => ['message' => 'Deleted successfully']
        ]);
    }
}
