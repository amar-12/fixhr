<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TravelDashboardApi extends Controller
{
    public function index(Request $request, $id)
    {
        $business = Business::with('fh_admin')->find($id);

        if (!$business) {
            return response()->json(['error' => 'Business not found'], 404);
        }

        if (!$business->fh_admin) {
            return response()->json(['error' => 'Business has no admin'], 422);
        }

        // Login business admin (same auth context as web)
        Auth::login($business->fh_admin);

        try {
            // Call the EXISTING dashboard controller
            $controller = app()->make(
                \App\Http\Controllers\Web\Admin\DashboardController::class
            );

            $response = $controller->index($request);

            /**
             * If dashboard returned a View,
             * extract EXACT SAME data and return as JSON
             */
            if ($response instanceof View) {

                // dd($response->getData());
                return response()->json([
                    'success' => true,
                    'data' => $response->getData(), // 🔥 SAME DATA AS VIEW
                ]);
            }

            /**
             * If dashboard returned JSON (datatable ajax),
             * return it as-is
             */
            return $response;

        } catch (\Throwable $e) {
            Log::error('TravelDashboardApi failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Travel dashboard API failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
