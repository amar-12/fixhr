<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class AttendanceDashboardApi extends Controller
{
    public function index(Request $request, $id)
    {
        $business = Business::with('fh_admin')->find($id);

        if (! $business) {
            return response()->json(['error' => 'Business not found'], 404);
        }

        if (! $business->fh_admin) {
            return response()->json(['error' => 'Business admin not found'], 422);
        }

        // Login as business admin
        Auth::loginUsingId($business->fh_admin->emp_id);

        // Make this an AJAX call internally
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        // Use your main dashboard controller
        $controllerClass = \App\Http\Controllers\Web\Admin\AttendanceDashboardController::class;

        try {
            $controller = app($controllerClass);

            if (! method_exists($controller, 'index')) {
                return response()->json(['error' => 'index() method missing'], 500);
            }

            $response = $controller->index($request);

            if ($response instanceof JsonResponse) {
                return $response;
            }

            return response()->json([
                'error' => 'Expected JSON, but got a view.',
            ], 500);

        } catch (\Throwable $e) {
            Log::error('Attendance API error: '.$e->getMessage());

            return response()->json([
                'error' => 'Server error while generating attendance dashboard',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
