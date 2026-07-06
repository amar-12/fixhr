<?php

namespace App\Http\Controllers\Api\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Location;
use App\Models\TadaRequestDetail;


class LocationTrackerController extends Controller
{
    
    
    public function updateLocation(Request $request, $id)
    {
        $startTime = microtime(true);

        try {
            $data = $request->all();

            Log::info('Received location data', $data);

            if (!isset($data['userId'])) {
                // Log::warning('Missing userId in request');
                return response()->json([
                    'status' => 'error',
                    'message' => 'userId is required'
                ], 400);
            }

            if ($data && $data['distance'] > 50.0) {
                $distanceKm = isset($data['distance'])
                    ? (float) number_format($data['distance'] / 1000, 3, '.', '')
                    : 0;

                $newSegment = [
                    'latitude'           => isset($data['latitude']) ? round($data['latitude'], 6) : null,
                    'longitude'          => isset($data['longitude']) ? round($data['longitude'], 6) : null,
                    'location'           => $data['location'] ?? null,
                    'date'               => isset($data['timestamp']) ? date('Y-m-d', strtotime($data['timestamp'])) : null,
                    'time'               => isset($data['timestamp']) ? date('h:i A', strtotime($data['timestamp'])) : null,
                    'distance'           => $distanceKm,
                    'google_distance_km' => null,
                ];

                $detail = TadaRequestDetail::where('trd_id', $data['trdId'])->first();

                $segments = [];

                if ($detail && $detail->trd_segments) {
                    $existing = json_decode($detail->trd_segments, true);
                    if (is_array($existing)) {
                        $segments = $existing;
                    }
                }

                // add new segment
                $segments[] = $newSegment;

                $totalDistance = 0;
                foreach ($segments as $seg) {
                    $totalDistance += isset($seg['distance']) ? (float) $seg['distance'] : 0;
                }


                // Amount calculation

                // Get the Travel Request Detail ID from the input detail
                    $trdId = $data['trdId'];

                     Log::info('trdId: ' . $trdId);

                    // Safely retrieve the related travel allowance object for this request detail
                    $allowance = $detail->fh_policy_tada_travel_allowance;
                    Log::info('allowance', [$allowance]);
                    Log::info('detail', [$detail]);

                    // Extract the travel allowance type ID (e.g., 155 = Policy and 156 = Actual)
                    $travel_allowance = $allowance->pttv_claim_type_id ?? '';
                    Log::info('travel_allowance: ' . $travel_allowance);

                    // Get the eligibility rate (e.g., amount per km) for this allowance
                    $eligibility = optional($detail->fh_policy_tada_travel_allowance ?? null)->pttv_eligibility ?? 0;
                    Log::info('eligibility: ' . $eligibility);

                    // Calculate total amount:
                    // - If claim type is 155 (CLAIM_TYPE -> Policy) and claim type is 156 (CLAIM_TYPE => Actual) (distance-based),
                    //multiply total distance + tolerance by eligibility
                    // - Otherwise, use the net amount directly
                    if ($travel_allowance == 155) {
                        $total_amount = $totalDistance * $eligibility;
                        Log::info('total_amount (policy)', [$total_amount]);

                    } else {
                        $total_amount = $detail->trd_net_amount ?? 0;
                        Log::info('total_amount (actual)', [$total_amount]);
                    }
                //End  Amount calculation

                TadaRequestDetail::updateOrCreate(
                    ['trd_id' => $data['trdId']], // condition
                    [
                        'trd_trp_id'          => $data['trpId'] ?? null,
                        'trd_segments'        => json_encode($segments, JSON_UNESCAPED_UNICODE),
                        'trd_total_distance'  => (float) number_format($totalDistance, 3, '.', ''), // save total
                        'trd_net_amount'  => (float) $total_amount, // save total
                    ]
                );
           }

            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;

            return response()->json([
                'status'            => 'success',
                'message'           => 'Location updated successfully',
                'user_id'           => $id,
                'latitude'          => $data['latitude'] ?? null,
                'longitude'         => $data['longitude'] ?? null,
                'trpId'             => $data['trpId'] ?? null,
                'trdId'             => $data['trdId'] ?? null,
                'distance_meters'   => $data['distance'] ?? null,
                'distance_km'       => $distanceKm ?? null,
                'timestamp'         => $data['timestamp'] ?? null,
                'execution_time_ms' => $executionTime
            ]);

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal server error: ' . $e->getMessage(),
                'execution_time_ms' => $executionTime
            ], 500);
        }
    }
    
    public function syncLocation(Request $request)
    {
        $startTime = microtime(true);
    
        try {
            $data = $request->all();
            Log::info('Received location data', ['payload' => $data]);
    
            // Detect input format
            if (isset($data[0]) && is_array($data[0])) {
                // Case 1: Input is an array of segments
                $incomingSegments = $data;
                $firstSegment     = $data[0];
            } else {
                // Case 2: Input is a single object with optional "segments" key
                $incomingSegments = isset($data['segments']) && is_array($data['segments'])
                    ? $data['segments']
                    : [$data];
                $firstSegment     = $incomingSegments[0];
            }
    
            // Always resolve userId, trpId, trdId from first segment
            $userId = $firstSegment['userId'] ?? null;
            $trpId  = $firstSegment['trpId'] ?? null;
            $trdId  = $firstSegment['trdId'] ?? null;
    
            if (!$userId) {
                return response()->json([
                    'status' => flase,
                    'message' => 'userId is required'
                ]);
            }
    
            if (!$trpId || !$trdId) {
                return response()->json([
                    'status' => flase,
                    'message' => 'Both trpId and trdId are required'
                ]);
            }
    
            $detail   = TadaRequestDetail::where('trd_id', $trdId)->first();
            $segments = [];
    
            // Load existing segments
            if ($detail && $detail->trd_segments) {
                $existing = json_decode($detail->trd_segments, true);
                if (is_array($existing)) {
                    $segments = $existing;
                }
            }
    
            // Merge new segments
            foreach ($incomingSegments as $seg) {
                if (!isset($seg['distance']) || $seg['distance'] <= 0) continue;
    
                // Normalize distance (if > 10 assume meters else km)
                $distanceKm = $seg['distance'] > 10
                    ? (float) number_format($seg['distance'] / 1000, 3, '.', '') // meters → km
                    : (float) number_format($seg['distance'], 3, '.', '');       // already km
    
                $segments[] = [
                    'latitude'           => isset($seg['latitude']) ? round($seg['latitude'], 6) : null,
                    'longitude'          => isset($seg['longitude']) ? round($seg['longitude'], 6) : null,
                    'location'           => $seg['location'] ?? null,
                    'date'               => $seg['date'] ?? (isset($seg['timestamp']) ? date('Y-m-d', strtotime($seg['timestamp'])) : null),
                    'time'               => $seg['time'] ?? (isset($seg['timestamp']) ? date('H:i:s', strtotime($seg['timestamp'])) : null),
                    'distance'           => $distanceKm,
                    'google_distance_km' => $seg['google_distance_km'] ?? null,
                ];
            }
    
            // Sort segments by datetime
            usort($segments, function ($a, $b) {
                return strtotime($a['date'] . ' ' . $a['time']) <=> strtotime($b['date'] . ' ' . $b['time']);
            });
    
            // Recalculate total distance
            $totalDistance = array_sum(array_column($segments, 'distance'));
    
            // Amount calculation
            $allowance         = $detail->fh_policy_tada_travel_allowance ?? null;
            $travel_allowance  = $allowance->pttv_claim_type_id ?? '';
            $eligibility       = $allowance->pttv_eligibility ?? 0;
    
            $total_amount = $travel_allowance == 155
                ? $totalDistance * $eligibility
                : ($detail->trd_net_amount ?? 0);
    
            // Save to DB
            TadaRequestDetail::updateOrCreate(
                ['trd_id' => $trdId],
                [
                    'trd_trp_id'          => $trpId,
                    'trd_segments'        => json_encode($segments, JSON_UNESCAPED_UNICODE),
                    'trd_total_distance'  => (float) number_format($totalDistance, 3, '.', ''),
                    'trd_net_amount'      => (float) $total_amount,
                ]
            );
    
            $endTime       = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
    
            return response()->json([
                'status'             => true,
                'message'            => 'Location updated successfully',
              /* 'user_id'            => $userId,
                'segments_count'     => count($segments),
                'trd_total_distance' => $totalDistance,
                'trd_net_amount'     => $total_amount,
                'execution_time_ms'  => $executionTime*/
            ]);
        } catch (\Exception $e) {
            $endTime       = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
    
            return response()->json([
                'status'  => flase,
                'message' => 'Location is not updated !',
            ]);
        }
    }

    // Old Code
   /* public function updateLocation(Request $request, $id)
    {
        $startTime = microtime(true);

        try {
            $data = $request->all();

            Log::info('Received location data', $data);

            if (!isset($data['userId'])) {
                // Log::warning('Missing userId in request');
                return response()->json([
                    'status' => 'error',
                    'message' => 'userId is required'
                ], 400);
            }

            if ($data && $data['distance'] > 0.0) {
                $distanceKm = isset($data['distance'])
                    ? (float) number_format($data['distance'] / 1000, 3, '.', '')
                    : 0;

                $newSegment = [
                    'latitude'           => isset($data['latitude']) ? round($data['latitude'], 6) : null,
                    'longitude'          => isset($data['longitude']) ? round($data['longitude'], 6) : null,
                    'location'           => $data['location'] ?? null,
                    'date'               => isset($data['timestamp']) ? date('Y-m-d', strtotime($data['timestamp'])) : null,
                    'time'               => isset($data['timestamp']) ? date('h:i A', strtotime($data['timestamp'])) : null,
                    'distance'           => $distanceKm,
                    'google_distance_km' => null,
                ];

                $detail = TadaRequestDetail::where('trd_id', $data['trdId'])->first();

                $segments = [];

                if ($detail && $detail->trd_segments) {
                    $existing = json_decode($detail->trd_segments, true);
                    if (is_array($existing)) {
                        $segments = $existing;
                    }
                }

                // add new segment
                $segments[] = $newSegment;

                $totalDistance = 0;
                foreach ($segments as $seg) {
                    $totalDistance += isset($seg['distance']) ? (float) $seg['distance'] : 0;
                }


                // Amount calculation

                // Get the Travel Request Detail ID from the input detail
                    $trdId = $data['trdId'];

                     Log::info('trdId: ' . $trdId);

                    // Safely retrieve the related travel allowance object for this request detail
                    $allowance = $detail->fh_policy_tada_travel_allowance;
                    Log::info('allowance', [$allowance]);
                    Log::info('detail', [$detail]);

                    // Extract the travel allowance type ID (e.g., 155 = Policy and 156 = Actual)
                    $travel_allowance = $allowance->pttv_claim_type_id ?? '';
                    Log::info('travel_allowance: ' . $travel_allowance);

                    // Get the eligibility rate (e.g., amount per km) for this allowance
                    $eligibility = optional($detail->fh_policy_tada_travel_allowance ?? null)->pttv_eligibility ?? 0;
                    Log::info('eligibility: ' . $eligibility);

                    // Calculate total amount:
                    // - If claim type is 155 (CLAIM_TYPE -> Policy) and claim type is 156 (CLAIM_TYPE => Actual) (distance-based),
                    //multiply total distance + tolerance by eligibility
                    // - Otherwise, use the net amount directly
                    if ($travel_allowance == 155) {
                        $total_amount = $totalDistance * $eligibility;
                        Log::info('total_amount (policy)', [$total_amount]);

                    } else {
                        $total_amount = $detail->trd_net_amount ?? 0;
                        Log::info('total_amount (actual)', [$total_amount]);
                    }
                //End  Amount calculation

                TadaRequestDetail::updateOrCreate(
                    ['trd_id' => $data['trdId']], // condition
                    [
                        'trd_trp_id'          => $data['trpId'] ?? null,
                        'trd_segments'        => json_encode($segments, JSON_UNESCAPED_UNICODE),
                        'trd_total_distance'  => (float) number_format($totalDistance, 3, '.', ''), // save total
                        'trd_net_amount'  => (float) $total_amount, // save total
                    ]
                );
            }

            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;

            return response()->json([
                'status'            => 'success',
                'message'           => 'Location updated successfully',
                'user_id'           => $id,
                'latitude'          => $data['latitude'] ?? null,
                'longitude'         => $data['longitude'] ?? null,
                'trpId'             => $data['trpId'] ?? null,
                'trdId'             => $data['trdId'] ?? null,
                'distance_meters'   => $data['distance'] ?? null,
                'distance_km'       => $distanceKm ?? null,
                'timestamp'         => $data['timestamp'] ?? null,
                'execution_time_ms' => $executionTime
            ]);

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;

            return response()->json([
                'status'  => 'error',
                'message' => 'Internal server error: ' . $e->getMessage(),
                'execution_time_ms' => $executionTime
            ], 500);
        }
    }*/

    // Fetch live locations from Python server and save to DB
    public function getLiveLocations()
    {
        $startTime = microtime(true);
        Log::info('LocationController::getLiveLocations started');

        try {
            // Fetch live locations from Python server
            $response = Http::timeout(5)->get('http://13.204.3.63:5000/api/live_locations');

            if ($response->successful()) {
                $data = $response->json();

                $endTime = microtime(true);
                $executionTime = ($endTime - $startTime) * 1000;

                Log::info('live locations fetched successfully', [
                    'execution_time_ms' => $executionTime,
                    'locations_count' => count($data['locations'] ?? [])
                ]);

                return response()->json($data);
            } else {
                Log::warning('Python server returned error', [
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Python server returned error: ' . $response->status()
                ], $response->status());
            }

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;

            Log::error('LocationController::getLiveLocations failed', [
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Could not connect to Python server: ' . $e->getMessage()
            ], 500);
        }
    }
}