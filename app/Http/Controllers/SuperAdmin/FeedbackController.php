<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with(['employee', 'business'])->orderBy('created_at', 'desc')->get();
        return view('superadmin.feedback.index', compact('feedbacks'));
    }
} 