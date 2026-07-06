<?php

namespace App\Http\Controllers\Api\WebSocket;

use App\Http\Controllers\Controller;
use App\Events\LocationUpdateEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class LocationWebSocketController extends Controller
{
    /**
     * Update user's current location
     */
    public function updateLocation(Request $request)
    {
        // Log the incoming request
        \Log::info('WebSocket Location Update Request Received', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'timestamp' => 'nullable|date',
            'request_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::warning('WebSocket Location Update Validation Failed', [
                'user_id' => Auth::id(),
                'errors' => $validator->errors()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $locationData = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'timestamp' => $request->timestamp ?? now()->toISOString(),
            'user_id' => $user->emp_id,
            'user_name' => $user->emp_fname,
        ];

        // Cache the location data for quick access
        $cacheKey = "user_location_{$user->emp_id}";
        Cache::put($cacheKey, $locationData, now()->addMinutes(30));

        // Broadcast the location update
        if ($request->request_id) {
            // Broadcast to specific request channel
            broadcast(new LocationUpdateEvent($locationData, $user->emp_id, $request->request_id));
        } else {
            // Broadcast to user's private channel
            broadcast(new LocationUpdateEvent($locationData, $user->emp_id));
        }

        \Log::info('WebSocket Location Update Successful', [
            'user_id' => $user->emp_id,
            'request_id' => $request->request_id,
            'cache_key' => $cacheKey
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated successfully',
            'data' => $locationData
        ]);
    }

    /**
     * Get user's current location
     */
    public function getCurrentLocation(Request $request)
    {
        // Log the request
        \Log::info('WebSocket Get Current Location Request', [
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString()
        ]);

        $user = Auth::user();
        $cacheKey = "user_location_{$user->emp_id}";
        
        $locationData = Cache::get($cacheKey);
        
        if (!$locationData) {
            \Log::warning('WebSocket Get Current Location: Location not found', [
                'user_id' => $user->emp_id,
                'cache_key' => $cacheKey
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Location not found or expired'
            ], 404);
        }

        \Log::info('WebSocket Get Current Location: Location retrieved successfully', [
            'user_id' => $user->emp_id,
            'cache_key' => $cacheKey
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $locationData
        ]);
    }

    /**
     * Get location for a specific request
     */
    public function getRequestLocation($requestId)
    {
        // Log the request
        \Log::info('WebSocket Get Request Location Request', [
            'user_id' => Auth::id(),
            'request_id' => $requestId,
            'timestamp' => now()->toISOString()
        ]);

        $user = Auth::user();
        $cacheKey = "request_location_{$requestId}";
        
        $locationData = Cache::get($cacheKey);
        
        if (!$locationData) {
            \Log::warning('WebSocket Get Request Location: Location not found', [
                'user_id' => $user->emp_id,
                'request_id' => $requestId,
                'cache_key' => $cacheKey
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Request location not found'
            ], 404);
        }

        \Log::info('WebSocket Get Request Location: Location retrieved successfully', [
            'user_id' => $user->emp_id,
            'request_id' => $requestId,
            'cache_key' => $cacheKey
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $locationData
        ]);
    }

    /**
     * Subscribe to location updates for a specific request
     */
    public function subscribeToRequestLocation($requestId)
    {
        // Log the subscription request
        \Log::info('WebSocket Subscribe to Request Location', [
            'user_id' => Auth::id(),
            'request_id' => $requestId,
            'timestamp' => now()->toISOString()
        ]);

        $user = Auth::user();
        
        // Store subscription in cache
        $subscriptionKey = "request_subscription_{$requestId}_{$user->emp_id}";
        Cache::put($subscriptionKey, true, now()->addHours(24));

        \Log::info('WebSocket Subscribe to Request Location: Subscription successful', [
            'user_id' => $user->emp_id,
            'request_id' => $requestId,
            'subscription_key' => $subscriptionKey
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscribed to request location updates',
            'channel' => "request.{$requestId}"
        ]);
    }

    /**
     * Unsubscribe from location updates
     */
    public function unsubscribeFromLocation($requestId = null)
    {
        // Log the unsubscribe request
        \Log::info('WebSocket Unsubscribe from Location', [
            'user_id' => Auth::id(),
            'request_id' => $requestId,
            'timestamp' => now()->toISOString()
        ]);

        $user = Auth::user();
        
        if ($requestId) {
            $subscriptionKey = "request_subscription_{$requestId}_{$user->emp_id}";
            Cache::forget($subscriptionKey);
            \Log::info('WebSocket Unsubscribe: Removed specific request subscription', [
                'user_id' => $user->emp_id,
                'request_id' => $requestId,
                'subscription_key' => $subscriptionKey
            ]);
        } else {
            // Remove all user location data
            $cacheKey = "user_location_{$user->emp_id}";
            Cache::forget($cacheKey);
            \Log::info('WebSocket Unsubscribe: Removed all user location data', [
                'user_id' => $user->emp_id,
                'cache_key' => $cacheKey
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Unsubscribed successfully'
        ]);
    }
}
