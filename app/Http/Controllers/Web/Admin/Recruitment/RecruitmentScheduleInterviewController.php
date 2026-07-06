<?php

namespace App\Http\Controllers\Web\Admin\Recruitment;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecruitmentScheduleInterviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'ris_candidate_id' => 'required|exists:recruitment_candidate,rc_id', // Ensure the candidate exists in the database
            'ris_interviewer' => 'required|exists:employees,emp_id',             // Ensure the interviewer exists in the database
            'ris_interview_date' => 'required|date|after_or_equal:today',        // Date should be today or in the future
            'ris_interview_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    $interviewDate = $request->input('ris_interview_date');
                    $interviewTime = Carbon::createFromFormat('H:i', $value);
                    if ($interviewDate == Carbon::today()->toDateString() && $interviewTime->lt(Carbon::now())) {
                        $fail('The interview time must be greater than the current time.');
                    }
                },
            ],
            'ris_description' => 'nullable|string|max:1000', // Optional, max 1000 characters
            'ris_completed' => 'boolean',                   // Should be true or false
        ]);
        dd($request->all());

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
