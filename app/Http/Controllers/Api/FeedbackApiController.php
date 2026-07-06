<?php

namespace App\Http\Controllers\Api;

use App\Models\Feedback;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FeedbackApiController extends Controller
{
    public function submitFeedback(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $filePath = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // Save to public/uploads/feedbacks/
            $file->move(public_path('uploads/feedbacks'), $filename);

            $filePath = 'feedbacks/' . $filename;
        }

        $feedback = Feedback::create([
            'f_emp_id'      => $user->emp_id,
            'f_b_id'        => $user->emp_b_id,
            'f_title'       => $validated['title'],
            'f_description' => $validated['description'],
            'f_attachment'  => $filePath, // this will be null if no file uploaded
        ]);

        $feedbackArray = $feedback->toArray();
        $feedbackArray['file_url'] = $filePath ? [asset($filePath)] : [];

        return response()->json([
            'status'   => true,
            'message'  => 'Feedback submitted successfully.',
            'result'   => [$feedbackArray]
        ], 201);
    }

    public function saveRating(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'rat_type'   => 'required|string',
            'rat_count'  => 'required|integer',
            'rat_remark' => 'nullable|string',
        ]);

        // Create or Update based on rat_b_id + rat_emp_id
        $rating = Rating::updateOrCreate(
            [
                'rat_b_id'   => $user->emp_b_id,
                'rat_emp_id' => $user->emp_id,
            ],
            [
                'rat_type'   => $request->rat_type,
                'rat_count'  => $request->rat_count,
                'rat_remark' => $request->rat_remark,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => $rating->wasRecentlyCreated 
                         ? 'Rating created successfully'
                         : 'Rating updated successfully',
            'result'  => true
        ]);
    }


}
