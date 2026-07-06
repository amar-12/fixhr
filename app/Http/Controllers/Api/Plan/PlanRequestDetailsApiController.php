<?php

namespace App\Http\Controllers\Api\Plan;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use ChandraHemant\HtkcUtils\CommonUtils;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\TravelPlanDetailsRequest;
use App\Http\Requests\Plan\TravelPlanDetailsUpdate;
use App\Http\Resources\Request\FilterPlanRequestApiResource;
use App\Http\Resources\Request\PlanRequestDetailResource;
use App\Models\PolicyTadaCategory;
use App\Models\TadaRequestDetail;
use App\Models\TadaRequestPlan;
use App\Models\TripDistancesModel;
use App\Models\TadaExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\RuleCriterion;
use ChandraHemant\HtkcUtils\PaginatedResource;
use Illuminate\Http\Request;
use App\Http\Requests\Plan\TravelDetailsListRequest;
use App\Http\Resources\TravelPurposeResource;
use App\Models\Employee;
use App\Models\PolicyTadaTravelType;
use App\Models\TadaClaim;
use App\Models\TravelPurpose;
use App\Models\PolicyTadaTravelVehicle;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Http;
use App\Helpers\Aws\AwsHelper;
use App\Http\Resources\Approval\Travel\AdvanceLogApiResource;
use App\Http\Resources\Approval\Travel\TravelPlanApiResource;
use App\Http\Resources\Travel\TravelLocationResource;
use App\Models\AdvanceLog;
use App\Models\ProcessApprover;
use App\Models\TravelLocation;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Cache;

class PlanRequestDetailsApiController extends Controller
{
    protected $awsHelper;

    public function __construct(AwsHelper $awsHelper)
    {
        if (env('STORE_ON_S3')) {
            $this->awsHelper = $awsHelper;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function travelPurpose()
    {
        $user = Auth::user();
        $travel_purpose = TravelPurpose::where(['tp_b_id' => $user->emp_b_id, 'tp_d_id' => $user->emp_d_id])->get();

        if ($travel_purpose) {
            return ReturnHelper::jsonApiReturn(TravelPurposeResource::collection($travel_purpose)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = TadaRequestDetail::whereHas('fh_policy_tada_request_plan', function ($query) use ($user) {
            $query->where('trp_b_id', $user->emp_b_id);
        })->with('fh_policy_tada_request_plan');

        // Apply orderBy and paginate on the query builder
        $data = $query->orderBy('trd_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        if ($data->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, PlanRequestDetailResource::class));
        }

        return response()->json(['result' => [], 'status' => false]);
    }

    /*public function storeLocations(Request $request)
    {
        $user = Auth::user();

        $trp_id = $request->trp_id;

        $newLocation = [
            'latitude' => (double) $request->latitude,
            'longitude' => (double) $request->longitude,
            'date' =>  date('Y-m-d'),
            'time' => $request->time,
            'location' => $request->location,
            'distance' => $request->distance,
        ];

        $locationData = [
            $newLocation
        ];

        $vehicle = PolicyTadaTravelVehicle::where('pttv_id', $request->travel_vehicle_id)->first();
        if ($vehicle) {
            $details =  TadaRequestDetail::where(['trd_trp_id' => $trp_id, 'trd_pttm_id' => $vehicle->pttv_pttm_id, 'trd_pttv_id' => $vehicle->pttv_id])->first();
            if ($details) {
                $old_locations = json_decode($details->trd_segments, true) ?? [];
                $old_locations[] = $newLocation;
                $details->update(['trd_segments' => $old_locations]);
            }
        }

        $locations = [
            'travel' => [
                'vehicle_id' => $request->travel_vehicle_id,
                'location_list' => $locationData
            ]
        ];

        $travelLocation = TravelLocation::create([
            'lc_trp_id' => $request->trp_id,
            'lc_trd_id' => $request->trd_id,
            'lc_emp_id' => $user->emp_id,
            'locations' => json_encode($locations),
        ]);

        return ReturnHelper::jsonApiReturn(new TravelLocationResource($travelLocation));
    }*/

    public function storeLocations(Request $request)
    {
        $user = Auth::user();
        $trp_id = $request->trp_id;

        $currentLat = (double) $request->latitude;
        $currentLng = (double) $request->longitude;
        $currentLocation = "{$currentLat},{$currentLng}";

        $vehicle = PolicyTadaTravelVehicle::where('pttv_id', $request->travel_vehicle_id)->first();
        if ($vehicle) {
            $details = TadaRequestDetail::where([
                'trd_trp_id' => $trp_id,
                'trd_pttm_id' => $vehicle->pttv_pttm_id,
                'trd_pttv_id' => $vehicle->pttv_id
            ])->first();

            if ($details) {
                $old_locations = json_decode($details->trd_segments, true) ?? [];

                $distanceKm = 0;

               /* if (!empty($old_locations)) {

                    $last = end($old_locations);

                    $origin = "{$last['latitude']},{$last['longitude']}";

                    $result = ApprovalHelper::getDistanceAndDuration($origin, $currentLocation);

                    if ($result) {
                        $distanceData = $result;
                    }
                }

                $distanceKm = $distanceData['distance_value'] / 1000;
                */

                if (!empty($old_locations)) {
                    $last = end($old_locations);
                    if (!empty($last['latitude']) && !empty($last['longitude'])) {
                        $origin = "{$last['latitude']},{$last['longitude']}";
                        $result = ApprovalHelper::getDistanceAndDuration($origin, $currentLocation);
                        if (!empty($result['distance_value'])) {
                            $distanceKm = $result['distance_value'] / 1000;
                        }
                    }
                }

                // Calculate total distance
                $total_distance = 0;
                if (!empty($old_locations)) {
                    $total_distance = (double) $details->trd_total_distance;
                }
                $total_distance += $distanceKm;

                $newLocation = [
                    'latitude' => $currentLat,
                    'longitude' => $currentLng,
                    'date' => date('Y-m-d'),
                    'time' => $request->time,
                    'location' => $request->location,
                    'distance' => round($distanceKm, 3), // km
                ];

                $old_locations[] = $newLocation;
                // $details->update(['trd_segments' => $old_locations]);
                $details->update(['trd_segments' => $old_locations, 'trd_total_distance' => $total_distance]);

                $locations = [
                    'travel' => [
                        'vehicle_id' => $request->travel_vehicle_id,
                        'location_list' => [$newLocation],
                    ]
                ];

                $travelLocation = TravelLocation::create([
                    'lc_trp_id' => $request->trp_id,
                    'lc_trd_id' => $request->trd_id,
                    'lc_emp_id' => $user->emp_id,
                    'locations' => json_encode($locations),
                    'lc_total_distance' => $newLocation['distance'],
                ]);

                if ($request->trd_id) {
                    $getData = TadaRequestDetail::where('trd_id', $request->trd_id)->first();
                    $existingSegments = json_decode($getData->trd_segments, true);
                    $finalLocation = [$newLocation];
                    if (empty($existingSegments)) {
                        $getData->update([
                            'trd_segments' => json_encode($finalLocation)
                        ]);
                    } else {
                        $mergedSegments = array_merge($existingSegments, $finalLocation);
                        $getData->update([
                            'trd_segments' => json_encode($mergedSegments)
                        ]);
                    }
                }

                return response()->json([
                    'result' => new TravelLocationResource($travelLocation),
                    'status' => true
                ]);
            }
        }

        return response()->json(['result' => [], 'status' => false]);
    }

    public function updateLocations(Request $request, $trd_id)
    {
        $user = Auth::user();

        // $travelLocation = TravelLocation::find($lc_id);
        $travelLocation = TravelLocation::where('lc_trd_id', $trd_id)->first();
        if (!$travelLocation) {
            return response()->json(['error' => 'Location entry not found.'], 404);
        }

        $existingLocations = json_decode($travelLocation->locations, true) ?? [];

        // Get previous location from 'travel' if exists
        $previousLocation = null;
        if (isset($existingLocations['travel']['location_list'])) {
            $locationList = $existingLocations['travel']['location_list'];
            $previousLocation = end($locationList); // last recorded location
        }

        // Build origin and current location for distance calculation
        $origin = $previousLocation ? "{$previousLocation['latitude']},{$previousLocation['longitude']}" : null;
        $current = "{$request->latitude},{$request->longitude}";

        // Calculate distance only if origin exists
        $distanceResult = $origin ? ApprovalHelper::getDistanceAndDuration($origin, $current) : null;

        $distanceKm = isset($distanceResult['distance_value']) ? $distanceResult['distance_value'] / 1000 : null;
        $newLocation = [
            'latitude' => (double) $request->latitude,
            'longitude' => (double) $request->longitude,
            'date' =>  date('Y-m-d'),
            'time' => $request->time,
            'location' => $request->location,
            'distance' => round($distanceKm, 3)
        ];

        $total_distance = $newLocation['distance'];

        // Update TadaRequestDetail
        $trp_id = $request->trp_id;
        $vehicle = PolicyTadaTravelVehicle::where('pttv_id', $request->travel_vehicle_id)->first();
        if ($vehicle) {
            $details = TadaRequestDetail::where([
                'trd_trp_id' => $trp_id,
                'trd_pttm_id' => $vehicle->pttv_pttm_id,
                'trd_pttv_id' => $vehicle->pttv_id
            ])->first();

            if ($details) {
                $old_locations = json_decode($details->trd_segments, true) ?? [];
                $old_locations[] = $newLocation;
                if ($travelLocation->lc_total_distance) {
                    $total_distance += $travelLocation->lc_total_distance;
                }
                $details->update(['trd_segments' => $old_locations]);
            }
        }

        $vehicleId = $request->travel_vehicle_id;
        if (isset($existingLocations['travel'])) {
            if ($existingLocations['travel']['vehicle_id'] == $vehicleId) {
                $existingLocations['travel']['location_list'][] = $newLocation;
            } else {
                $existingLocations['travel'] = [
                    'vehicle_id' => $vehicleId,
                    'location_list' => [$newLocation],
                ];
            }
        } else {
            $existingLocations['travel'] = [
                'vehicle_id' => $vehicleId,
                'location_list' => [$newLocation],
            ];
        }

        $travelLocation->update(['lc_total_distance' => $total_distance, 'locations' => json_encode($existingLocations)]);
          /*if ($travelLocation->lc_trd_id) {
            $getData = TadaRequestDetail::where('trd_id', $travelLocation->lc_trd_id)->first();
            $existingSegments = json_decode($getData->trd_segments, true);
            $finalLocation = [$newLocation];
            if (empty($existingSegments)) {
                $getData->update([
                    'trd_segments' => json_encode($finalLocation)
                ]);
            } else {
                $mergedSegments = array_merge($existingSegments, $finalLocation);
                $getData->update([
                    'trd_segments' => json_encode($mergedSegments)
                ]);
            }
        }*/

        /*if ($travelLocation->lc_trd_id) {
            $getData = TadaRequestDetail::where('trd_id', $travelLocation->lc_trd_id)->first();
            if ($getData) {
                $existingSegments = json_decode($getData->trd_segments, true) ?? [];
                // Avoid duplication
                $segmentExists = false;
                foreach ($existingSegments as $seg) {
                    if (
                        round($seg['latitude'], 5) === round($newLocation['latitude'], 5) &&
                        round($seg['longitude'], 5) === round($newLocation['longitude'], 5) &&
                        $seg['date'] === $newLocation['date'] &&
                        $seg['time'] === $newLocation['time']
                    ) {
                        $segmentExists = true;
                        break;
                    }
                }
                if (!$segmentExists) {
                    $existingSegments[] = $newLocation;
                }

                // Recalculate total distance

                $totalTrdDistance = 0;
                foreach ($existingSegments as $seg) {
                    $segDistance = isset($seg['distance']) ? (float)$seg['distance'] : 0;
                    $totalTrdDistance += $segDistance;
                }
                $getData->update([
                    'trd_segments' => json_encode($existingSegments),
                    'trd_total_distance' => $totalTrdDistance,
                ]);
            }
        }*/

        if ($travelLocation->lc_trd_id) {
            $details = TadaRequestDetail::where('trd_id', $travelLocation->lc_trd_id)->first();
            if ($details) {
                $existingSegments = json_decode($details->trd_segments, true) ?? [];
                // Avoid duplication
                $segmentExists = false;
                foreach ($existingSegments as $seg) {
                    if (
                        round($seg['latitude'], 5) === round($newLocation['latitude'], 5) &&
                        round($seg['longitude'], 5) === round($newLocation['longitude'], 5) &&
                        $seg['date'] === $newLocation['date'] &&
                        $seg['time'] === $newLocation['time']
                    ) {
                        $segmentExists = true;
                        break;
                    }
                }

                if (!$segmentExists) {
                    $existingSegments[] = $newLocation;
                }

                $totalTrdDistance = 0;
                foreach ($existingSegments as $seg) {
                    $segDistance = isset($seg['distance']) ? (float)$seg['distance'] : 0;
                    $totalTrdDistance += $segDistance;
                }

                $eligibilityRate = $details->fh_policy_tada_travel_allowance->pttv_eligibility ?? 0;

                $netAmount = $eligibilityRate * $totalTrdDistance;

                $details->update([
                    'trd_segments' => json_encode($existingSegments),
                    'trd_total_distance' => $totalTrdDistance,
                    'trd_net_amount' => $netAmount,
                ]);
            }
        }

        return response()->json([
            'result' => new TravelLocationResource($travelLocation),
            'status' => true,
            'message' => 'Location updated successfully',

        ]);
    }

    /*public function updateLocations(Request $request, $lc_id)
    {

        $travelLocation = TravelLocation::find($lc_id);
        if (!$travelLocation) {
            return response()->json(['error' => 'Location entry not found.'], 404);
        }
        $existingLocations = json_decode($travelLocation->locations, true) ?? [];

        $newLocation = [
            'latitude' => (double) $request->latitude,
            'longitude' => (double) $request->longitude,
            'date' =>  date('Y-m-d'),
            'time' => $request->time,
            'location' => $request->location,
            'distance' => $request->distance,
        ];


        $trp_id = $request->trp_id;
        $vehicle = PolicyTadaTravelVehicle::where('pttv_id', $request->travel_vehicle_id)->first();
        if ($vehicle) {
            $details =  TadaRequestDetail::where(['trd_trp_id' => $trp_id, 'trd_pttm_id' => $vehicle->pttv_pttm_id, 'trd_pttv_id' => $vehicle->pttv_id])->first();
            if ($details) {
                $old_locations = json_decode($details->trd_segments, true) ?? [];
                $old_locations[] = $newLocation;
                $details->update(['trd_segments' => $old_locations]);
            }
        }

        $vehicleId = $request->travel_vehicle_id;

        // Check if 'travel' entry exists
        if (isset($existingLocations['travel'])) {
            // Check if travelModeId matches
            if ($existingLocations['travel']['vehicle_id'] == $vehicleId) {
                $existingLocations['travel']['location_list'][] = $newLocation;
            } else {
                // If travelModeId doesn't match, replace with new mode and location
                $existingLocations['travel'] = [
                    'vehicle_id' => $vehicleId,
                    'location_list' => [$newLocation],
                ];
            }
        } else {
            // Create a new 'travel' entry if it doesn't exist
            $existingLocations['travel'] = [
                'vehicle_id' => $vehicleId,
                'location_list' => [$newLocation],
            ];
        }

        $travelLocation->update(['locations' => json_encode($existingLocations)]);

        return ReturnHelper::jsonApiReturn(new TravelLocationResource($travelLocation));
    }*/

    // public function startJourney(Request $request)
    // {
    //     $user = Auth::user();
    //     $request->validate([
    //         'start_lat' => 'required|numeric',
    //         'start_lng' => 'required|numeric',
    //     ]);

    //     $origin = "{$request->start_lat},{$request->start_lng}";

    //     Cache::put("journey_origin_{$user->emp_id}", $origin, now()->addHours(5));

    //     return response()->json(['message' => 'Journey started', 'origin' => $origin]);
    // }

    // // Update distance from origin to current location
    // public function updateDistance(Request $request)
    // {
    //     $user = Auth::user();

    //     $request->validate([
    //         'current_lat' => 'required|numeric',
    //         'current_lng' => 'required|numeric',
    //     ]);

    //     $origin = Cache::get("journey_origin_{$user->emp_id}");

    //     if (!$origin) {
    //         return response()->json(['error' => 'Journey not started'], 400);
    //     }

    //     $currentLocation = "{$request->current_lat},{$request->current_lng}";

    //     $result = ApprovalHelper::getDistanceAndDuration($origin, $currentLocation);

    //     return response()->json($result ?? ['error' => 'Unable to calculate distance']);
    // }

      public function travelUpdate(Request $request, $travel_type_id)
    {
        $user = Auth::user();
        $emp_d_id = $user->emp_d_id;

        $tableName = (new TadaRequestPlan)->getTable();

        $validated = $request->validate([
            'travel_id'      => "required|integer|exists:$tableName,trp_id",
            'trp_call_id'    => 'nullable|string',
            'trp_name'       => 'nullable|string|max:255',
            'trp_destination'=> 'nullable|string|max:255',
            'trp_purpose'    => 'nullable|integer|max:500',
            'trp_remarks'    => 'nullable|string|max:500',
            'trp_document.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $uploadedPhotos = [];
        if ($request->hasFile('trp_document')) {
            if (env('STORE_ON_S3')) {
                $bucket = 'fixhr-uploads';
                foreach ($request->file('trp_document') as $file) {
                    $imageUniqueName = time() . '_' . $file->getClientOriginalName();
                    $imagePath = 'PlanDocument/' . $user->fh_business->b_unique_id . '/' . $imageUniqueName;

                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);

                    if (!empty($uploadResult['status']) && $uploadResult['status']) {
                        $uploadedPhotos[] = $uploadResult['ObjectURL'];
                    }
                }
            } else {
                $uploadedPath = CommonUtils::uploadFiles(
                    $request,
                    'trp_document',
                    'PlanDocument',
                    ['prefix' => 'Plan', 'isApi' => true]
                );

                if (!empty($uploadedPath)) {
                    foreach ($uploadedPath as $path) {
                        $uploadedPhotos[] = url($path);
                    }
                }
            }
        }

        $existingPlan = TadaRequestPlan::where('trp_id', $validated['travel_id'])->first();

        if (!$existingPlan) {
            return response()->json([
                'result' => ['message' => 'Travel plan not found or you do not have permission to update it'],
                'status' => false
            ], 404);
        }

        // Build update data dynamically (skip nulls)
        $updateData = [];

        if (!is_null($validated['trp_call_id'] ?? null)) {
            $updateData['trp_call_id'] = $validated['trp_call_id'];
        }
        if (!is_null($validated['trp_name'] ?? null)) {
            $updateData['trp_name'] = $validated['trp_name'];
        }
        if (!is_null($validated['trp_destination'] ?? null)) {
            $updateData['trp_destination'] = $validated['trp_destination'];
        }
        if (!is_null($validated['trp_purpose'] ?? null)) {
            $updateData['trp_purpose'] = $validated['trp_purpose'];
        }
        if (!is_null($validated['trp_remarks'] ?? null)) {
            $updateData['trp_remarks'] = $validated['trp_remarks'];
        }

        // Handle documents
        if (!empty($uploadedPhotos)) {
            $existingDocuments = $existingPlan->trp_document ? json_decode($existingPlan->trp_document, true) : [];
            $allDocuments = array_merge($existingDocuments, $uploadedPhotos);
            $updateData['trp_document'] = json_encode($allDocuments);
        }

        if (empty($updateData)) {
            return response()->json([
                'message' => 'No changes provided',
                'status' => false
            ], 400);
        }

        $updated = TadaRequestPlan::where('trp_id', $validated['travel_id'])->update($updateData);

        if ($updated) {
            return response()->json([
                'message' => 'Travel plan updated successfully',
                'status' => true
            ]);
        }

        return response()->json([
            'message' => 'No record updated',
            'status' => false
        ]);
    }


    //Travel Request, Travel Expense, Travel Details Delete according to travel request id
    public function travelDelete(string $id)
    {
        $user = Auth::user();
        $user_b_id = $user->emp_b_id;

        $travelPlan = TadaRequestPlan::where('trp_id', $id)
            ->where('trp_b_id', $user_b_id)
            ->first();

        if (!$travelPlan) {
            return response()->json([
                'result' => ['message' => 'Travel plan not found or you do not have permission to delete it'],
                'status' => false
            ], 404);
        }

        try {
            //Travel All Expense Data delete
            TadaExpense::where('te_trp_id', $travelPlan->trp_id)->delete();

            //Travel Details Delete
            TadaRequestDetail::where('trd_trp_id', $travelPlan->trp_id)->delete();

            //Travel Request Delete
            $travelPlan->delete();

            return response()->json([
                'message' => 'Travel plan and all associated expenses moved to trash successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete travel plan: ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    public function localToOutstationConversion(Request $request, int $id)
    {
        $user = Auth::user();
        $user_b_id = $user->emp_b_id;

        $travel_trp_id        = $request->input('trp_id');
        $travel_end_date      = $request->input('end_date');
        $travel_end_time      = $request->input('end_time');
        $travel_trp_status    = $request->input('trp_status');
        $travel_trp_destination = $request->input('trp_destination');

        // Get Local Travel Plan
        $travelPlan = TadaRequestPlan::where('trp_id', $travel_trp_id)
            ->where('trp_b_id', $user_b_id)
            ->where('trp_pttt_id', $id) // local type
            ->first();

        if (!$travelPlan) {
            return response()->json(['error' => 'Local travel plan not found'], 404);
        }

        // Get Local Policy Travel Type (with approval type)
        $localPolicyTravelType = $travelPlan->fh_policy_tada_travel_type;

        if (!$localPolicyTravelType) {
            return response()->json(['error' => 'Local policy not linked with travel plan'], 404);
        }

        // Get Outstation Travel Policy
        $outstationPolicyTravelType = PolicyTadaTravelType::where('pttt_b_id', $user_b_id)
            ->where('pttt_type_id', 125) // 125 = Outstation
            ->where('pttt_status', 1)
            ->first();

        if (!$outstationPolicyTravelType) {
            return response()->json(['error' => 'Outstation policy not found'], 404);
        }

        // Default values
        $travel_pttt_id          = $outstationPolicyTravelType->pttt_id;
        $outstationApprovalType  = $outstationPolicyTravelType->pttt_approval_type_id;
        $localApprovalType       = $localPolicyTravelType->pttt_approval_type_id;

        $travel_request_status = null; // initialize
        $trp_next_approver = null; // initialize
        $trp_stage_completed = null; // initialize

        // Save old data for rollback
        $previousData = [
            'trp_pttt_id'        => $travelPlan->trp_pttt_id,
            'trp_end_date'       => $travelPlan->trp_end_date,
            'trp_end_time'       => $travelPlan->trp_end_time,
            'trp_request_status' => $travelPlan->trp_request_status,
            'trp_destination'    => $travelPlan->trp_destination,
            'trp_next_approver'  => $travelPlan->trp_next_approver,
            'trp_stage_completed'=> $travelPlan->trp_stage_completed,
            'trp_is_converted'   => $travelPlan->trp_is_converted,
        ];

         // Store rollback snapshot in DB (JSON column or new table)
        $travelPlan->update([
            'trp_previous_snapshot' => json_encode($previousData)
        ]);

        // Debugging start
        \Log::info('Local to Outstation Conversion Start', [
            'travel_trp_id' => $travel_trp_id,
            'local_approval_type' => $localApprovalType,
            'outstation_approval_type' => $outstationApprovalType,
        ]);

        // Conversion Logic with logs
        if ($localApprovalType == 197 && $outstationApprovalType == 197) {
            \Log::info('Condition: Local = Auto, Outstation = Auto');
            $travel_request_status = 171; // Auto Approved
            $trp_next_approver = 1;
            $trp_stage_completed = 1;
        } elseif ($localApprovalType == 197 && $outstationApprovalType == 198) {
            \Log::info('Condition: Local = Auto, Outstation = Manual');
            $travel_request_status = 140; // Requested
            $trp_next_approver = 1;
            $trp_stage_completed = 0;
        } elseif ($localApprovalType == 198 && $outstationApprovalType == 197) {
            \Log::info('Condition: Local = Manual, Outstation = Auto');
            if ($travelPlan->fh_approval_log()->exists()) {
                \Log::info('Deleting old approval logs (Manual → Auto conversion)');
                $travelPlan->fh_approval_log()->delete();
            }
            $travel_request_status = 171; // Auto Approved
            $trp_next_approver = 1;
            $trp_stage_completed = 1;
        } elseif ($localApprovalType == 198 && $outstationApprovalType == 198) {
            \Log::info('Condition: Local = Manual, Outstation = Manual');
            if ($travelPlan->fh_approval_log()->exists()) {
                \Log::info('Deleting old approval logs (Manual → Manual conversion)');
                $travelPlan->fh_approval_log()->delete();
            }
            $travel_request_status = 140; // Requested
            $trp_next_approver = 1;
            $trp_stage_completed = 0;
        } else {
            \Log::error('Approval type mismatch', [
                'local_approval_type' => $localApprovalType,
                'outstation_approval_type' => $outstationApprovalType,
            ]);
            return response()->json([
                'error' => 'Approval type mismatch',
                'local_approval_type' => $localApprovalType,
                'outstation_approval_type' => $outstationApprovalType,
            ], 422);
        }
        // dd($travel_pttt_id, $travel_end_date, $travel_end_time, $travel_request_status);

        // Update travel plan safely
        TadaRequestPlan::where('trp_id', $travel_trp_id)->update([
            'trp_pttt_id'     => $travel_pttt_id,
            'trp_end_date'    => $travel_end_date,
            'trp_end_time'    => $travel_end_time,
            'trp_request_status' => $travel_request_status,
            'trp_destination' => $travel_trp_destination,
            'trp_next_approver' => $trp_next_approver,
            'trp_stage_completed' => $trp_stage_completed,
            'trp_is_converted' => 1
        ]);

        // Final log with all updated data
        \Log::info('Local to Outstation Conversion Completed', [
            'trp_id'          => $travelPlan->trp_id,
            'updated_pttt_id' => $travel_pttt_id,
            'end_date'        => $travel_end_date,
            'end_time'        => $travel_end_time,
            'status'          => $travel_request_status,
            'destination'     => $travel_trp_destination,
        ]);

        return response()->json([
            'message' => 'Travel converted from Local to Outstation successfully',
            'outstation_approval_type' => $outstationApprovalType,
            'applied_status' => $travel_request_status,
            'updated_travel_plan' => $travelPlan
        ]);
    }

     /**
     * Rollback function on rejection
     */
    public function rollbackToLocal($trp_id)
    {
        $travelPlan = TadaRequestPlan::where('trp_id', $trp_id)->first();
        if (!$travelPlan || !$travelPlan->trp_previous_snapshot) {
            return response()->json(['error' => 'No rollback data found'], 404);
        }

        $previousData = json_decode($travelPlan->trp_previous_snapshot, true);

        // Restore old values
        $travelPlan->update($previousData + ['trp_previous_snapshot' => null]);

        // Restore approval logs if needed
        // (You may need to restore from backup table)

        // Restore approval logs if they were soft-deleted
        if (method_exists($travelPlan, 'fh_approval_log')) {
            $travelPlan->fh_approval_log()
                ->withTrashed()   // include soft-deleted logs
                ->restore();      // restore them
        }

        return response()->json([
            'message' => 'Travel plan reverted back to Local successfully',
            'restored_data' => $previousData
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TravelPlanDetailsRequest $request)
    {
        $user = Auth::user();
        // Log::info('right place.');
        $emp_d_id = $user->emp_d_id;
        $category = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->where('ptc_d_id', $user->emp_d_id)->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->where('ptc_grade_id', $user->emp_grade_id)->whereJsonContains('ptc_pttt_id', (int) $request->input('trp_travel_type_id'))->first();

        if (!isset($category)) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! you are not eligible for any travel category']);
        }

        // $amId = null;
        // $ruleCriteria = RuleCriterion::with('fh_approval_module')
        //     ->where('rc_b_id', $user->emp_b_id)
        //     ->where('rc_condition_option_id', 140)
        //     ->whereHas('fh_approval_module', function ($query) {
        //         $query->where('am_module_id', 145)
        //             ->where('am_status', 1);
        //     })->first();

        // $processApprovers = [];

        // // Ensure $ruleCriteria exists before accessing the relationship
        // if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
        //     // Fetch filtered process approvers using emp_d_id
        //     $processApprovers = $ruleCriteria->fh_approval_module
        //         ->filteredProcessApprovers($emp_d_id)
        //         ->get(); // Fetch the filtered data
        // }

        // if (count($processApprovers)) {
        //     $amId = $ruleCriteria->rc_am_id;
        // } else {
        //     $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 145);
        //     if (!$approvalMapping) {
        //         return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for travel module, contact administration.']);
        //     }
        // }


         // NEW: Try to get employee-wise approval mapping first
         $approvalMapping = \App\Helpers\ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 145);
        //  dd($approvalMapping);
         $approvalEmpIds = [];
         $amId = null;
 
         if ($approvalMapping) {
             // Employee-wise mapping exists, get approver emp_ids
             $approvalEmpIds = \App\Helpers\ApprovalHelper::getApprovalArray($approvalMapping);
            //  dd($approvalEmpIds);
             // $amId = null; // Store mapping's am_id if necessary (adjust as per actual column)
         } else {
             // Fallback to hierarchy: get RuleCriteria and processApprovers
             $ruleCriteria = RuleCriterion::with('fh_approval_module')
                 ->where('rc_b_id', $user->emp_b_id)
                 ->where('rc_condition_option_id', 140)
                 ->whereHas('fh_approval_module', function ($query) {
                     $query->where('am_module_id', 145)
                         ->where('am_status', 1);
                 })->first();
             $processApprovers = [];
             $emp_d_id = $user->emp_d_id;
             if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                 $processApprovers = $ruleCriteria->fh_approval_module
                     ->filteredProcessApprovers($emp_d_id)
                     ->get();
             }
 
             if (!empty($processApprovers)) {
                 $amId = $ruleCriteria->rc_am_id;
                 foreach ($processApprovers as $pa) {
                     if ($pa->pa_emp_id) {
                         $approvalEmpIds[] = $pa->pa_emp_id;
                     }
                 }
             } else {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for travel module, contact administration.']);
            }
        }

        $trpDetails = $request->input('trp_details');


        // Decode the JSON data to an associative array
        $data = is_array($trpDetails) ? $trpDetails : (json_decode($trpDetails, true) ?? []);

        $trp_unique_id = '';
        $lastUnique = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)->get()->last();
        if ($lastUnique) {
            $trp_unique_id = $lastUnique->trp_unique_id ?? '';
        }
        $unique_id = CentralLogics::alpha_numeric_generator(4, 'TRP', '', $trp_unique_id ?? '');

        $start_date = $end_date = $fromTime = $toTime = null;

        if ($request->input('trp_start_date')) {
            try {
                $start_date = Carbon::createFromFormat('d M, Y', $request->input('trp_start_date'))->format('Y-m-d');
            } catch (\Exception $e) {
                $start_date = null;
            }
        }

        if ($request->input('trp_end_date')) {
            try {
                $end_date = Carbon::createFromFormat('d M, Y', $request->input('trp_end_date'))->format('Y-m-d');
            } catch (\Exception $e) {
                $end_date = null;
            }
        }

        if ($request->input('trp_start_time')) {
            $fromTime = null;
            try {
                $fromTime = Carbon::parse($request->input('trp_start_time'))->format('H:i:s');
            } catch (\Exception $e) {
                $fromTime = null;
            }
        }

        if ($request->input('trp_end_time')) {
            $toTime = null;
            try {
                $toTime = Carbon::parse($request->input('trp_end_time'))->format('H:i:s');
            } catch (\Exception $e) {
                $toTime = null;
            }
        }

        /*if ($start_date && $end_date) {
            $overlappingPlans = TadaRequestPlan::where('trp_emp_id', $user->emp_id)->whereNull('deleted_at')
                ->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                    $query->where(function ($query) use ($start_date, $end_date) {
                        $query->whereBetween('trp_start_date', [$start_date, $end_date])
                            ->orWhereBetween('trp_end_date', [$start_date, $end_date])
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('trp_start_date', '<=', $end_date)
                                    ->where('trp_end_date', '>=', $start_date);
                            });
                    })
                        ->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                            $query->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                                $query->whereDate('trp_start_date', $start_date)
                                    ->whereDate('trp_end_date', $end_date)
                                    ->whereTime('trp_start_time', '<=', $toTime)
                                    ->whereTime('trp_end_time', '>=', $fromTime);
                            })
                                ->orWhere(function ($query) use ($start_date, $fromTime, $toTime) {
                                    $query->whereDate('trp_start_date', $start_date)
                                        ->whereNull('trp_end_date')
                                        ->whereTime('trp_start_time', '<=', $toTime)
                                        ->whereTime('trp_end_time', '>=', $fromTime);
                                })
                                ->orWhere(function ($query) use ($end_date, $fromTime, $toTime) {
                                    $query->whereNull('trp_start_date')
                                        ->whereDate('trp_end_date', $end_date)
                                        ->whereTime('trp_start_time', '<=', $toTime)
                                        ->whereTime('trp_end_time', '>=', $fromTime);
                                });
                        });
                })
                ->whereYear('trp_start_date', date('Y', strtotime($start_date)))
                ->whereYear('trp_end_date', date('Y', strtotime($end_date)))
                ->exists();
            if ($overlappingPlans) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'The specified date range overlaps with an existing plan for the same employee.'], 422);
            }
        }*/

        if ($start_date && $end_date) {
            $overlappingPlans = TadaRequestPlan::where('trp_emp_id', $user->emp_id)->whereNull('deleted_at')->where('trp_request_status', '!=', 170)
                ->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                    $query->where(function ($query) use ($start_date, $end_date) {
                        $query->whereBetween('trp_start_date', [$start_date, $end_date])
                            ->orWhereBetween('trp_end_date', [$start_date, $end_date])
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('trp_start_date', '<=', $end_date)
                                    ->where('trp_end_date', '>=', $start_date);
                            });
                    })
                        ->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                            $query->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                                $query->whereDate('trp_start_date', $start_date)
                                    ->whereDate('trp_end_date', $end_date)
                                    ->whereTime('trp_start_time', '<=', $toTime)
                                    ->whereTime('trp_end_time', '>=', $fromTime);
                            })
                                ->orWhere(function ($query) use ($start_date, $fromTime, $toTime) {
                                    $query->whereDate('trp_start_date', $start_date)
                                        ->whereNull('trp_end_date')
                                        ->whereTime('trp_start_time', '<=', $toTime)
                                        ->whereTime('trp_end_time', '>=', $fromTime);
                                })
                                ->orWhere(function ($query) use ($end_date, $fromTime, $toTime) {
                                    $query->whereNull('trp_start_date')
                                        ->whereDate('trp_end_date', $end_date)
                                        ->whereTime('trp_start_time', '<=', $toTime)
                                        ->whereTime('trp_end_time', '>=', $fromTime);
                                });
                        });
                })
                ->whereYear('trp_start_date', date('Y', strtotime($start_date)))
                ->whereYear('trp_end_date', date('Y', strtotime($end_date)))
                ->exists();
            if ($overlappingPlans) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'The selected date and time range overlaps with an existing active plan for this employee.'], 422);
            }
        }

        $uploadedPhotos = [];
        if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
            $bucket = 'fixhr-uploads';
            if ($request->trp_document != '' && $request->trp_document != NULL && $request->trp_document != []) {
                foreach ($request->trp_document as $file) {
                    $imageUniqueName = $file->getClientOriginalName();
                    $imagePath = 'PlanDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                    if ($uploadResult['status']) {
                        $uploadedPhotos[] = $uploadResult['ObjectURL'];
                    }
                }
            }
        } else {
            $uploadedPath = CommonUtils::uploadFiles($request, 'trp_document', 'PlanDocument', ['prefix' => 'Plan', 'isApi' => true]);
            if (!empty($uploadedPath)) {
                foreach ($uploadedPath as $path) {
                    $uploadedPhotos[] = url($path);
                }
            }
        }

        $plan = TadaRequestPlan::create([
            'trp_emp_id' => $user->emp_id,
            'trp_unique_id' => $unique_id,
            'trp_name' => $request->input('trp_name'),
            'trp_destination' => $request->input('trp_destination') ? $request->input('trp_destination') : null,
            'trp_start_date' => $start_date,
            'trp_end_date' => $end_date,
            'trp_start_time' => $fromTime,
            'trp_end_time' => $toTime,
            'trp_purpose' => $request->input('trp_purpose'),
            'trp_pttt_id' => $request->input('trp_travel_type_id'),
            'trp_advance_allowance' => $request->input('trp_advance'),
            'trp_request_status' => $request->input('trp_request_status'),
            'trp_call_id' => $request->input('trp_call_id'),
            'trp_ptc_id' => $category->ptc_id,
            'trp_am_id' => $amId,
            'trp_b_id' => $user->emp_b_id,
            'trp_br_id' => $user->emp_br_id,
            'trp_remarks' => $request->input('trp_remarks'),
            'trp_document' => json_encode($uploadedPhotos),
        ]);

        // Check for process approvers and proceed to email logic
        // if (count($processApprovers) && $plan) {
        //     // Determine if any approver type is 'anyone'
        //     $approvers = ($processApprovers[0]->pa_type == 'anyone') ? $processApprovers : [$processApprovers[0]];

        //     // Loop through approvers (could be one or many)
        //     foreach ($approvers as $approver) {
        //         $placeholders = [
        //             '{approver_name}' => optional($approver->fh_employee)->emp_full_name ?? '',
        //             '{receiver_name}' => optional($approver->fh_employee)->emp_full_name ?? '',
        //             '{employee_name}' => optional($plan->fh_employee)->emp_full_name ?? 'N/A',
        //             '{employee_code}' => optional($plan->fh_employee)->emp_code ?? 'N/A',
        //             '{emp_code}' => optional($plan->fh_employee)->emp_code ?? 'N/A',
        //             '{travel_id}' => $plan->trp_unique_id ?? 'N/A',
        //             '{trip_name}' => $plan->trp_name ?? 'N/A',
        //             '{travel_purpose}' => $plan->trp_purpose ?? 'N/A',
        //             '{travel_type}' => optional($plan->fh_policy_tada_travel_type->fh_travel_type)->m_name ?? 'N/A',
        //             '{departure_date}' => $plan->trp_start_date ?? 'N/A',
        //             '{return_date}' => $plan->trp_end_date ?? 'N/A',
        //             '{advance_amount}' => $plan->trp_advance_allowance ?? 'N/A',
        //             '{portal_url}' => route('login'),
        //         ];
        //         $recipientEmail = optional($approver->fh_employee)->emp_email;
        //         $templateType = 385; // Replace with your mail template type
        //         $businessId = $user->emp_b_id; // Replace with your business ID if applicable
        //         $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
        //     }
        // }

        if ($plan->fh_policy_tada_travel_type->pttt_approval_type_id == 197) {
            $plan->trp_stage_completed = 1;
            $plan->save();
        }

        if ($plan && !empty($data)) {
            foreach ($data as $index => $detail) {
                $processedSegments = [];
                if (isset($detail['segments']) && $detail['segments']) {
                    foreach ($detail['segments'] as $segment) {
                        $latitude = $segment['latitude'];
                        $longitude = $segment['longitude'];
                        $time = $segment['time'];
                        $distance = $segment['distance'];

                        $getlocationResponse = $this->getLocationName(new Request(['latitude' => $latitude, 'longitude' => $longitude]));
                        $getlocation = json_decode($getlocationResponse->getContent(), true); // Decode the JSON response

                        // Now you can access the formatted_address safely
                        $processedSegments[] = [
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'location' => $getlocation['formatted_address'] ?? '',
                            'date' =>  date('Y-m-d'),
                            'time' => $time,
                            'distance' => $distance,
                        ];
                    }
                }

                $uploadedDetailsPhotos = [];
                if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
                    $bucket = 'fixhr-uploads';
                    if ($request->document != '' && $request->document != NULL && $request->document != []) {
                        foreach ($request->document as $file) {
                            $imageUniqueName = $file->getClientOriginalName();
                            $imagePath = 'DetailDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
                            $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                            if ($uploadResult['status']) {
                                $uploadedDetailsPhotos[] = $uploadResult['ObjectURL'];
                            }
                        }
                    }
                } else {
                    $uploadedPath = CommonUtils::uploadFiles($request, 'document' . $index, 'DetailDocument/' . $plan->trp_id, ['prefix' => 'PlanDetail', 'isApi' => true]);
                    if (!empty($uploadedPath)) {
                        foreach ($uploadedPath as $path) {
                            $uploadedDetailsPhotos[] = url($path);
                        }
                    }
                }

                $trd_type_id = isset($detail['type']) ? $detail['type'] : 181; // by default 181 travel
                $trd_p_set_amount = 0;
                $trd_p_set_amount = 0;
                if ($trd_type_id == 181) { // Travel
                    $trd_p_set_amount = isset($category->fh_vehicle_allowance) ? $category->fh_vehicle_allowance->pttv_eligibility : 0;
                }
                 log::info("data saved successfully");
                // Create travel plan details
                $detail = TadaRequestDetail::create([
                    'trd_trp_id' => $plan->trp_id,
                    'trd_name' => $detail['trd_name'],
                    'trd_pttm_id' => $detail['mode_id'],
                    'trd_pttv_id' => $detail['vehicle_id'],
                    'trd_source' => $detail['source'],
                    'trd_hotel_location' => isset($detail['trd_hotel_location']) ? $detail['trd_hotel_location'] : null,
                    'trd_type_id' => $trd_type_id,
                    'trd_destination' => $detail['destination'],
                    'trd_start_date' => $detail['start_date'] ? Carbon::createFromFormat('d M, Y', $detail['start_date'])->format('Y-m-d') : null,
                    'trd_end_date' => $detail['end_date'] ? Carbon::createFromFormat('d M, Y', $detail['end_date'])->format('Y-m-d') : null,
                    'trd_start_time' => $detail['start_time'] ? Carbon::createFromFormat('g:i A', $detail['start_time'])->format('H:i:s') : null,
                    'trd_end_time' => $detail['end_time'] ? Carbon::createFromFormat('g:i A', $detail['end_time'])->format('H:i:s') : null,
                    'trd_documents' => json_encode($uploadedDetailsPhotos),
                    'trd_segments' => !empty($processedSegments) ? json_encode($processedSegments) : null,
                    'trd_total_distance' => $detail['total_distance'],
                    'trd_total_tolerance' => $detail['total_tolerance'],
                    'trd_call_id' => $detail['call_id'],
                    'trd_status' => $detail['status'],
                    'trd_remarks' => $detail['remark'],
                    'trd_purpose' => $detail['purpose'],
                    'trd_ticket_type' => $detail['ticket_type'],
                    'trd_net_amount' => $detail['amount'],
                    'trd_p_set_amount' => $trd_p_set_amount
                ]);
            }

            // Update the plan's details added status
            if (TadaRequestDetail::where('trd_trp_id', $plan->trp_id)->count()) {
                $plan->trp_is_details_added = 1;
                $plan->save();
            }
            return ReturnHelper::jsonApiReturn(PlanRequestDetailResource::collection(TadaRequestDetail::where('trd_trp_id', $plan->trp_id)->get())->all());
        }
        
        if ($plan) {
            $title = 'Travel Request';
            $body = 'A travel request has been submitted by ' . $user->emp_full_name;
            $additionalData = [
                'user_id' => $user->emp_id,
                'notification_type' => 'alert',
                'route' => '/TadaApprovalList',
            ];
            $serviceAccountPath = public_path('fixhr-app-firebase.json');
      
           // Notify only first approver (both employee-wise and hierarchy-wise)
          $notifyEmpIds = !empty($approvalEmpIds) ? [reset($approvalEmpIds)] : [];


          foreach ($notifyEmpIds as $approverEmpId) {
              $emp = Employee::find($approverEmpId);
              $approver = ApprovalHelper::getApprovalOrRejectionData($plan->trp_id, $plan->trp_request_status, $amId, $approverEmpId, 145);            
                if ($emp && $emp->emp_is_notification_enabled=='1') {
                   log::info("notification sent");
                      if($emp->emp_fcm_token) {
                          FirebaseNotification::sendPushNotification(
                              $title,
                              $body,
                              $emp->emp_fcm_token,
                              $serviceAccountPath,
                              config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                              $additionalData
                          );
                      }
                      NotificationHelper::saveNotification(
                          $user->emp_id,
                          $approverEmpId,
                          $title,
                          $body,
                          $additionalData
                      );
                }

            }


            return ReturnHelper::jsonApiReturn(FilterPlanRequestApiResource::collection([$plan])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $details = TadaRequestDetail::where('trd_trp_id', $id)->get();

        if ($details->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(PlanRequestDetailResource::collection($details));
        }

        return response()->json(['result' => [], 'status' => false]);
    }


    /**
     * Update the specified resource in storage.
     */
    // public function update(TravelPlanDetailsRequest $request, string $id)
    // {
    //     $user = Auth::user();

    //     // Retrieve the category based on user details
    //     $category = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)
    //         ->where('ptc_d_id', $user->emp_d_id)
    //         ->whereJsonContains('ptc_dg_id', $user->emp_dg_id)
    //         ->where('ptc_grade_id', $user->emp_grade_id)
    //         ->whereJsonContains('ptc_pttt_id', (int) $request->input('trp_travel_type_id'))
    //         ->first();

    //     if (!isset($category)) {
    //         return response()->json([
    //             'result' => [],
    //             'status' => false,
    //             'message' => 'Sorry! You are not eligible for any travel category'
    //         ]);
    //     }

    //     $trpDetails = $request->input('trp_details');


    //     $amId = null;
    //     $ruleCriteria = RuleCriterion::with('fh_approval_module')
    //         ->where('rc_b_id', $user->emp_b_id)
    //         ->where('rc_condition_option_id', 140)
    //         ->whereHas('fh_approval_module', function ($query) {
    //             $query->where('am_module_id', 145)
    //                 ->where('am_status', 1);
    //         })->first();

    //     $processApprovers = [];

    //     // Ensure $ruleCriteria exists before accessing the relationship
    //     if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
    //         // Fetch filtered process approvers using emp_d_id
    //         $processApprovers = $ruleCriteria->fh_approval_module
    //             ->filteredProcessApprovers($user->emp_d_id)
    //             ->get(); // Fetch the filtered data
    //     }

    //     if (count($processApprovers)) {
    //         $amId = $ruleCriteria->rc_am_id;
    //     } else {
    //         $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 145);
    //         if (!$approvalMapping) {
    //             return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for travel module, contact administration.']);
    //         }
    //     }
    //     // Parse date and time
    //     $start_date = $this->parseDate($request->input('trp_start_date'));
    //     $end_date = $this->parseDate($request->input('trp_end_date'));
    //     $fromTime = $this->parseTime($request->input('trp_start_time'));
    //     $toTime = $this->parseTime($request->input('trp_end_time'));

    //     // Check for overlapping plans
    //     if ($start_date && $end_date) {
    //         $overlappingPlans = TadaRequestPlan::where('trp_emp_id', $user->emp_id)
    //             ->whereNull('deleted_at')
    //             ->where('trp_id', '!=', $id)
    //             ->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
    //                 $query->where(function ($query) use ($start_date, $end_date) {
    //                     $query->whereBetween('trp_start_date', [$start_date, $end_date])
    //                         ->orWhereBetween('trp_end_date', [$start_date, $end_date])
    //                         ->orWhere(function ($query) use ($start_date, $end_date) {
    //                             $query->where('trp_start_date', '<=', $end_date)
    //                                 ->where('trp_end_date', '>=', $start_date);
    //                         });
    //                 })
    //                     ->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
    //                         $query->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
    //                             $query->whereDate('trp_start_date', $start_date)
    //                                 ->whereDate('trp_end_date', $end_date)
    //                                 ->whereTime('trp_start_time', '<=', $toTime)
    //                                 ->whereTime('trp_end_time', '>=', $fromTime);
    //                         })
    //                             ->orWhere(function ($query) use ($start_date, $fromTime, $toTime) {
    //                                 $query->whereDate('trp_start_date', $start_date)
    //                                     ->whereNull('trp_end_date')
    //                                     ->whereTime('trp_start_time', '<=', $toTime)
    //                                     ->whereTime('trp_end_time', '>=', $fromTime);
    //                             })
    //                             ->orWhere(function ($query) use ($end_date, $fromTime, $toTime) {
    //                                 $query->whereNull('trp_start_date')
    //                                     ->whereDate('trp_end_date', $end_date)
    //                                     ->whereTime('trp_start_time', '<=', $toTime)
    //                                     ->whereTime('trp_end_time', '>=', $fromTime);
    //                             });
    //                     });
    //             })
    //             ->whereYear('trp_start_date', date('Y', strtotime($start_date)))
    //             ->whereYear('trp_end_date', date('Y', strtotime($end_date)))
    //             ->exists();

    //         if ($overlappingPlans) {
    //             return response()->json([
    //                 'result' => [],
    //                 'status' => false,
    //                 'message' => 'The specified date range overlaps with an existing plan for the same employee.'
    //             ], 422);
    //         }
    //     }

    //     // Decode JSON data to array
    //     $data = is_array($trpDetails) ? $trpDetails : (json_decode($trpDetails, true) ?? []);

    //     $uploadedPhotos = [];
    //     if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
    //         $bucket = 'fixhr-uploads';
    //         if ($request->trp_document != '' && $request->trp_document != NULL && $request->trp_document != []) {
    //             foreach ($request->trp_document as $file) {
    //                 $imageUniqueName = $file->getClientOriginalName();
    //                 $imagePath = 'PlanDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
    //                 $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
    //                 if ($uploadResult['status']) {
    //                     $uploadedPhotos[] = $uploadResult['ObjectURL'];
    //                 }
    //             }
    //         }
    //     } else {
    //         $uploadedPath = CommonUtils::uploadFiles($request, 'trp_document', 'PlanDocument', ['prefix' => 'Plan', 'isApi' => true]);
    //         if (!empty($uploadedPath)) {
    //             foreach ($uploadedPath as $path) {
    //                 $uploadedPhotos[] = url($path);
    //             }
    //         }
    //     }


    //     // Update the plan
    //     TadaRequestPlan::where('trp_id', $id)->update([
    //         'trp_call_id' => $request->trp_call_id,
    //         'trp_request_status' => $request->trp_request_status,
    //         'trp_am_id' => $amId,
    //         'trp_destination' => $request->input('trp_destination'),
    //         'trp_start_date' => $start_date,
    //         'trp_end_date' => $end_date,
    //         'trp_start_time' => $fromTime,
    //         'trp_end_time' => $toTime,
    //         'trp_advance_allowance' => $request->input('trp_advance'),
    //         'trp_purpose' => $request->input('trp_purpose'),
    //         'trp_remarks' => $request->input('trp_remarks'),
    //         'trp_document' => json_encode($uploadedPhotos)
    //     ]);

    //     // Fetch the updated plan and existing details
    //     $updatePlan = TadaRequestPlan::where('trp_id', $id)->get();
    //     $tadaRequestDetails = TadaRequestDetail::where('trd_trp_id', $id)->get();

    //     // Delete existing documents and details
    //     if (!empty($data)) {
    //         foreach ($tadaRequestDetails as $detail) {

    //             if ($detail->trd_documents != '' && $detail->trd_documents != NULL && $detail->trd_documents != []) {
    //                 $documents = json_decode($detail->trd_documents, true);
    //                 if (env('STORE_ON_S3')) {
    //                     foreach ($documents as $pf) {
    //                         // Delete from AWS S3
    //                         $bucket = 'fixhr-uploads';
    //                         $ObjectURL = ltrim(parse_url($pf, PHP_URL_PATH), '/');
    //                         $response = $this->awsHelper->deleteFileFromS3($bucket, $ObjectURL);
    //                         if (!$response['status']) {
    //                             \Log::error("Failed to delete travel details file from S3: " . $response['message']);
    //                         }
    //                     }
    //                 } else {
    //                     foreach ($documents as $pf) {
    //                         if (file_exists($pf) && $pf != 'uploads/') {
    //                             unlink($pf);
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         // Delete existing details
    //         if (count($data)) {
    //             TadaRequestDetail::where('trd_trp_id', $id)->delete(); //delete details and create with old and new details
    //             // Insert new details
    //             foreach ($data as $index => $detail) {
    //                 $processedSegments = [];
    //                 if (isset($detail['segments']) && $detail['segments']) {
    //                     foreach ($detail['segments'] as $segment) {
    //                         $latitude = $segment['latitude'];
    //                         $longitude = $segment['longitude'];
    //                         $time = $segment['time'];
    //                         $distance = $segment['distance'];

    //                         $getlocationResponse = $this->getLocationName(new Request(['latitude' => $latitude, 'longitude' => $longitude]));
    //                         $getlocation = json_decode($getlocationResponse->getContent(), true); // Decode the JSON response

    //                         // Now you can access the formatted_address safely
    //                         $processedSegments[] = [
    //                             'latitude' => $latitude,
    //                             'longitude' => $longitude,
    //                             'location' => $getlocation['formatted_address'] ?? '',
    //                             'date' =>  date('Y-m-d'),
    //                             'time' => $time,
    //                             'distance' => $distance,
    //                         ];
    //                     }
    //                 }

    //                 $uploadedDetailsPhotos = [];
    //                 if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
    //                     $bucket = 'fixhr-uploads';
    //                     if ($request->document != '' && $request->document != NULL && $request->document != []) {
    //                         foreach ($request->document as $file) {
    //                             $imageUniqueName = $file->getClientOriginalName();
    //                             $imagePath = 'DetailDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
    //                             $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
    //                             if ($uploadResult['status']) {
    //                                 $uploadedDetailsPhotos[] = $uploadResult['ObjectURL'];
    //                             }
    //                         }
    //                     }
    //                 } else {
    //                     $uploadedPath = CommonUtils::uploadFiles($request, 'document' . $index, 'DetailDocument/' . $id, ['prefix' => 'PlanDetail', 'isApi' => true]);
    //                     if (!empty($uploadedPath)) {
    //                         foreach ($uploadedPath as $path) {
    //                             $uploadedDetailsPhotos[] = url($path);
    //                         }
    //                     }
    //                 }
    //                 TadaRequestDetail::create([
    //                     'trd_trp_id' => $id,
    //                     'trd_name' => $detail['trd_name'],
    //                     'trd_pttm_id' => $detail['mode_id'],
    //                     'trd_pttv_id' => $detail['vehicle_id'],
    //                     'trd_source' => $detail['source'],
    //                     'trd_destination' => $detail['destination'],
    //                     'trd_hotel_location' => $detail['trd_hotel_location'] ?? null,
    //                     'trd_start_date' => $detail['start_date'] ? Carbon::createFromFormat('d M, Y', $detail['start_date'])->format('Y-m-d') : null,
    //                     'trd_end_date' => $detail['end_date'] ? Carbon::createFromFormat('d M, Y', $detail['end_date'])->format('Y-m-d') : null,
    //                     'trd_start_time' => $detail['start_time'] ? Carbon::createFromFormat('g:i A', $detail['start_time'])->format('H:i:s') : null,
    //                     'trd_end_time' => $detail['end_time'] ? Carbon::createFromFormat('g:i A', $detail['end_time'])->format('H:i:s') : null,
    //                     'trd_type_id' => $detail['type'] ?? null,
    //                     'trd_documents' => json_encode($uploadedDetailsPhotos),
    //                     'trd_segments' => !empty($processedSegments) ? json_encode($processedSegments) : null,
    //                     'trd_total_distance' => $detail['total_distance'],
    //                     'trd_total_tolerance' => $detail['total_tolerance'],
    //                     'trd_call_id' => $detail['call_id'],
    //                     'trd_status' => $detail['status'],
    //                     'trd_remarks' => $detail['remark'],
    //                     'trd_ticket_type' => $detail['ticket_type'],
    //                     'trd_net_amount' => $detail['amount']
    //                 ]);
    //             }
    //             if (TadaRequestDetail::where('trd_trp_id', $id)->count()) {
    //                 TadaRequestPlan::where('trp_id', $id)->update(['trp_is_details_added' => 1]);
    //             }



    //             return ReturnHelper::jsonApiReturn(PlanRequestDetailResource::collection(TadaRequestDetail::where('trd_trp_id', $id)->get())->all());
    //         }
    //     }

    //     // Return updated plan details if available
    //     if ($updatePlan) {
    //         return ReturnHelper::jsonApiReturn(FilterPlanRequestApiResource::collection($updatePlan)->all());
    //     }

    //     return response()->json(['result' => [], 'status' => false]);
    // }

    public function update(TravelPlanDetailsRequest $request, string $id)
    {
        $user = Auth::user();

        // Retrieve the category based on user details
        $category = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)
            ->where('ptc_d_id', $user->emp_d_id)
            ->whereJsonContains('ptc_dg_id', $user->emp_dg_id)
            ->where('ptc_grade_id', $user->emp_grade_id)
            ->whereJsonContains('ptc_pttt_id', (int) $request->input('trp_travel_type_id'))
            ->first();

        if (!isset($category)) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'Sorry! You are not eligible for any travel category'
            ]);
        }

        $trpDetails = $request->input('trp_details');


        $amId = null;
        $ruleCriteria = RuleCriterion::with('fh_approval_module')
            ->where('rc_b_id', $user->emp_b_id)
            ->where('rc_condition_option_id', 140)
            ->whereHas('fh_approval_module', function ($query) {
                $query->where('am_module_id', 145)
                    ->where('am_status', 1);
            })->first();

        $processApprovers = [];

        // Ensure $ruleCriteria exists before accessing the relationship
        if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
            // Fetch filtered process approvers using emp_d_id
            $processApprovers = $ruleCriteria->fh_approval_module
                ->filteredProcessApprovers($user->emp_d_id)
                ->get(); // Fetch the filtered data
        }

        if (count($processApprovers)) {
            $amId = $ruleCriteria->rc_am_id;
        } else {
            $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 145);
            if (!$approvalMapping) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for travel module, contact administration.']);
            }
        }
        // Parse date and time
        $start_date = $this->parseDate($request->input('trp_start_date'));
        $end_date = $this->parseDate($request->input('trp_end_date'));
        $fromTime = $this->parseTime($request->input('trp_start_time'));
        $toTime = $this->parseTime($request->input('trp_end_time'));

        // Check for overlapping plans
        if ($start_date && $end_date) {
            $overlappingPlans = TadaRequestPlan::where('trp_emp_id', $user->emp_id)
                ->whereNull('deleted_at')
                ->where('trp_id', '!=', $id)
                ->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                    $query->where(function ($query) use ($start_date, $end_date) {
                        $query->whereBetween('trp_start_date', [$start_date, $end_date])
                            ->orWhereBetween('trp_end_date', [$start_date, $end_date])
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('trp_start_date', '<=', $end_date)
                                    ->where('trp_end_date', '>=', $start_date);
                            });
                    })
                        ->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                            $query->where(function ($query) use ($start_date, $end_date, $fromTime, $toTime) {
                                $query->whereDate('trp_start_date', $start_date)
                                    ->whereDate('trp_end_date', $end_date)
                                    ->whereTime('trp_start_time', '<=', $toTime)
                                    ->whereTime('trp_end_time', '>=', $fromTime);
                            })
                                ->orWhere(function ($query) use ($start_date, $fromTime, $toTime) {
                                    $query->whereDate('trp_start_date', $start_date)
                                        ->whereNull('trp_end_date')
                                        ->whereTime('trp_start_time', '<=', $toTime)
                                        ->whereTime('trp_end_time', '>=', $fromTime);
                                })
                                ->orWhere(function ($query) use ($end_date, $fromTime, $toTime) {
                                    $query->whereNull('trp_start_date')
                                        ->whereDate('trp_end_date', $end_date)
                                        ->whereTime('trp_start_time', '<=', $toTime)
                                        ->whereTime('trp_end_time', '>=', $fromTime);
                                });
                        });
                })
                ->whereYear('trp_start_date', date('Y', strtotime($start_date)))
                ->whereYear('trp_end_date', date('Y', strtotime($end_date)))
                ->exists();

            if ($overlappingPlans) {
                return response()->json([
                    'result' => [],
                    'status' => false,
                    'message' => 'The specified date range overlaps with an existing plan for the same employee.'
                ], 422);
            }
        }

        // Decode JSON data to array
        $data = is_array($trpDetails) ? $trpDetails : (json_decode($trpDetails, true) ?? []);

        $uploadedPhotos = [];
        if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
            $bucket = 'fixhr-uploads';
            if ($request->trp_document != '' && $request->trp_document != NULL && $request->trp_document != []) {
                foreach ($request->trp_document as $file) {
                    $imageUniqueName = $file->getClientOriginalName();
                    $imagePath = 'PlanDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                    if ($uploadResult['status']) {
                        $uploadedPhotos[] = $uploadResult['ObjectURL'];
                    }
                }
            }
        } else {
            $uploadedPath = CommonUtils::uploadFiles($request, 'trp_document', 'PlanDocument', ['prefix' => 'Plan', 'isApi' => true]);
            if (!empty($uploadedPath)) {
                foreach ($uploadedPath as $path) {
                    $uploadedPhotos[] = url($path);
                }
            }
        }


        // Update the plan
        TadaRequestPlan::where('trp_id', $id)->update([
            'trp_call_id' => $request->trp_call_id,
            'trp_request_status' => $request->trp_request_status,
            'trp_am_id' => $amId,
            'trp_destination' => $request->input('trp_destination'),
            'trp_start_date' => $start_date,
            'trp_end_date' => $end_date,
            'trp_start_time' => $fromTime,
            'trp_end_time' => $toTime,
            'trp_advance_allowance' => $request->input('trp_advance'),
            'trp_purpose' => $request->input('trp_purpose'),
            'trp_remarks' => $request->input('trp_remarks'),
            'trp_document' => json_encode($uploadedPhotos)
        ]);

        // Fetch the updated plan and existing details
        $updatePlan = TadaRequestPlan::where('trp_id', $id)->get();
        $existingDetails = TadaRequestDetail::where('trd_trp_id', $id)->get()->keyBy('trd_id');

        foreach ($data as $index => $detail) {
            // Handle segments
            $processedSegments = [];
            if (!empty($detail['segments'])) {
                foreach ($detail['segments'] as $segment) {
                    $latitude = $segment['latitude'];
                    $longitude = $segment['longitude'];
                    $time = $segment['time'];
                    $distance = $segment['distance'];

                    $getlocationResponse = $this->getLocationName(new Request(['latitude' => $latitude, 'longitude' => $longitude]));
                    $getlocation = json_decode($getlocationResponse->getContent(), true);

                    $processedSegments[] = [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'location' => $getlocation['formatted_address'] ?? '',
                        'date' =>  date('Y-m-d'),
                        'time' => $time,
                        'distance' => $distance,
                    ];
                }
            }

            // Handle documents
            $uploadedDetailsPhotos = [];
            if (env('STORE_ON_S3')) {
                $bucket = 'fixhr-uploads';
                if (!empty($request->document)) {
                    foreach ($request->document as $file) {
                        $imageUniqueName = $file->getClientOriginalName();
                        $imagePath = 'DetailDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                        if ($uploadResult['status']) {
                            $uploadedDetailsPhotos[] = $uploadResult['ObjectURL'];
                        }
                    }
                }
            } else {
                $uploadedPath = CommonUtils::uploadFiles($request, 'document' . $index, 'DetailDocument/' . $id, ['prefix' => 'PlanDetail', 'isApi' => true]);
                if (!empty($uploadedPath)) {
                    foreach ($uploadedPath as $path) {
                        $uploadedDetailsPhotos[] = url($path);
                    }
                }
            }

            $trdId = $detail['trd_id'];
            $eligibility = optional($existingDetails[$trdId]->fh_policy_tada_travel_allowance ?? null)->pttv_eligibility ?? 0;
            $total_distance = $detail['total_distance'] ?? 0;
            $total_amount = $total_distance * $eligibility;

            // Common detail data
            $detailData = [
                'trd_trp_id' => $id,
                'trd_name' => $detail['trd_name'],
                'trd_pttm_id' => $detail['mode_id'],
                'trd_pttv_id' => $detail['vehicle_id'],
                'trd_source' => $detail['source'],
                'trd_destination' => $detail['destination'],
                'trd_hotel_location' => $detail['trd_hotel_location'] ?? null,
                'trd_start_date' => !empty($detail['start_date']) ? Carbon::createFromFormat('d M, Y', $detail['start_date'])->format('Y-m-d') : null,
                'trd_end_date' => !empty($detail['end_date']) ? Carbon::createFromFormat('d M, Y', $detail['end_date'])->format('Y-m-d') : null,
                'trd_start_time' => !empty($detail['start_time']) ? Carbon::createFromFormat('g:i A', $detail['start_time'])->format('H:i:s') : null,
                'trd_end_time' => !empty($detail['end_time']) ? Carbon::createFromFormat('g:i A', $detail['end_time'])->format('H:i:s') : null,
                'trd_type_id' => $detail['type'] ?? null,
                'trd_documents' => json_encode($uploadedDetailsPhotos),
                'trd_segments' => !empty($processedSegments) ? json_encode($processedSegments) : null,

                // 'trd_total_distance' => $detail['total_distance'],
                'trd_total_distance' => $total_distance,
                'trd_total_tolerance' => $detail['total_tolerance'],
                'trd_call_id' => $detail['call_id'],
                'trd_status' => $detail['status'],
                'trd_remarks' => $detail['remark'],
                'trd_ticket_type' => $detail['ticket_type'],
                // 'trd_net_amount' => $detail['amount'],
                'trd_net_amount' => $total_amount,
                'trd_geo_work_active' => $detail['trd_geo_work_active'],
            ];

            // If trd_id exists and matches a record, update it
            if (!empty($detail['trd_id']) && $existingDetails->has($detail['trd_id'])) {
                $existingDetails[$detail['trd_id']]->update($detailData);
            } else {
                // Otherwise create new

                $vehicle_id = $detail['vehicle_id'] ?? '';
                $policy_vehicle = PolicyTadaTravelVehicle::where('pttv_id', $vehicle_id)->first();
                $eligibility = $policy_vehicle->pttv_eligibility ?? 0;
                $total_distance = $detail['total_distance'] ?? 0;
                $total_amount = $total_distance * $eligibility;
                $detailData['trd_total_distance'] = $total_distance;
                $detailData['trd_net_amount'] = $total_amount;
                TadaRequestDetail::create($detailData);
            }
        }

        if (TadaRequestDetail::where('trd_trp_id', $id)->count()) {
            TadaRequestPlan::where('trp_id', $id)->update(['trp_is_details_added' => 1]);
        }

        return ReturnHelper::jsonApiReturn(PlanRequestDetailResource::collection(
            TadaRequestDetail::where('trd_trp_id', $id)->get()
        )->all());

        // Return updated plan details if available
        if ($updatePlan) {
            return ReturnHelper::jsonApiReturn(FilterPlanRequestApiResource::collection($updatePlan)->all());
        }

        return response()->json(['result' => [], 'status' => false]);
    }

    public function travelDetailsUpdate(TravelPlanDetailsUpdate $request, string $id)
    {
        $user = Auth::user();

        $trpDetails = $request->input('trp_details');

        // Decode JSON data to array
        $data = is_array($trpDetails) ? $trpDetails : (json_decode($trpDetails, true) ?? []);

        // Fetch the updated plan and existing details
        // $updatePlan = TadaRequestPlan::where('trp_id', $id)->get();
        $existingDetails = TadaRequestDetail::where('trd_trp_id', $id)->get()->keyBy('trd_id');

        foreach ($data as $index => $detail) {

            $processedSegments = [];
            if (!empty($detail['segments'])) {
                foreach ($detail['segments'] as $segment) {
                    $latitude = $segment['latitude'];
                    $longitude = $segment['longitude'];
                    $time = $segment['time'];
                    $distance = $segment['distance'];

                    $getlocationResponse = $this->getLocationName(new Request(['latitude' => $latitude, 'longitude' => $longitude]));
                    $getlocation = json_decode($getlocationResponse->getContent(), true);

                    $processedSegments[] = [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'location' => $getlocation['formatted_address'] ?? '',
                        'date' =>  date('Y-m-d'),
                        'time' => $time,
                        'distance' => $distance,
                        'google_distance_km' => $googleDistances[$index] ?? null, // match with leg index
                    ];
                }
            }

            // Handle documents
            $uploadedDetailsPhotos = [];
            if (env('STORE_ON_S3')) {
                $bucket = 'fixhr-uploads';
                if (!empty($request->document)) {
                    foreach ($request->document as $file) {
                        $imageUniqueName = $file->getClientOriginalName();
                        $imagePath = 'DetailDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                        if ($uploadResult['status']) {
                            $uploadedDetailsPhotos[] = $uploadResult['ObjectURL'];
                        }
                    }
                }
            } else {
                $uploadedPath = CommonUtils::uploadFiles($request, 'document' . $index, 'DetailDocument/' . $id, ['prefix' => 'PlanDetail', 'isApi' => true]);
                if (!empty($uploadedPath)) {
                    foreach ($uploadedPath as $path) {
                        $uploadedDetailsPhotos[] = url($path);
                    }
                }
            }

            // Get the Travel Request Detail ID from the input detail
            $trdId = $detail['trd_id'];

            // Safely retrieve the related travel allowance object for this request detail
            $allowance = optional($existingDetails[$trdId] ?? null)->fh_policy_tada_travel_allowance;

            // Extract the travel allowance type ID (e.g., 155 = Policy and 156 = Actual)
            $travel_allowance = $allowance->pttv_claim_type_id ?? '';

            // Get the eligibility rate (e.g., amount per km) for this allowance
            $eligibility = optional($existingDetails[$trdId]->fh_policy_tada_travel_allowance ?? null)->pttv_eligibility ?? 0;

            // Retrieve total distance and tolerance, defaulting to 0 if not set
            $total_distance = $detail['total_distance'] ?? 0;
            $total_tolerance = $detail['total_tolerance'] ?? 0;

            // Convert the provided amount to a float for accurate calculations
            $net_amount = floatval($detail['amount']);

            // Calculate total amount:
            // - If claim type is 155 (CLAIM_TYPE -> Policy) and claim type is 156 (CLAIM_TYPE => Actual) (distance-based),
            //multiply total distance + tolerance by eligibility
            // - Otherwise, use the net amount directly
            if ($travel_allowance == 155) {
                $total_amount = ($total_distance + $total_tolerance) * $eligibility;
            } else {
                $total_amount = $net_amount;
            }

            // Common detail data
            $detailData = [
                'trd_trp_id' => $id,
                'trd_name' => $detail['trd_name'],
                'trd_pttm_id' => $detail['mode_id'],
                'trd_pttv_id' => $detail['vehicle_id'],
                'trd_source' => $detail['source'],
                'trd_destination' => $detail['destination'],
                'trd_hotel_location' => $detail['trd_hotel_location'] ?? null,
                'trd_start_date' => !empty($detail['start_date']) ? Carbon::createFromFormat('d M, Y', $detail['start_date'])->format('Y-m-d') : null,
                'trd_end_date' => !empty($detail['end_date']) ? Carbon::createFromFormat('d M, Y', $detail['end_date'])->format('Y-m-d') : null,
                'trd_start_time' => !empty($detail['start_time']) ? Carbon::createFromFormat('g:i A', $detail['start_time'])->format('H:i:s') : null,
                'trd_end_time' => !empty($detail['end_time']) ? Carbon::createFromFormat('g:i A', $detail['end_time'])->format('H:i:s') : null,
                'trd_type_id' => $detail['type'] ?? null,
                'trd_documents' => json_encode($uploadedDetailsPhotos),
                'trd_segments' => !empty($processedSegments) ? json_encode($processedSegments) : null,
                // 'trd_total_distance' => $detail['total_distance'],
                'trd_total_distance' => $total_distance,
                'trd_total_tolerance' => $detail['total_tolerance'],
                'trd_call_id' => $detail['call_id'],
                'trd_status' => $detail['status'],
                'trd_remarks' => $detail['remark'],
                'trd_ticket_type' => $detail['ticket_type'],
                // 'trd_net_amount' => $detail['amount'],
                'trd_net_amount' => $total_amount,
                'trd_geo_work_active' => $detail['trd_geo_work_active'],
            ];

            // If trd_id exists and matches a record, update it
            if (!empty($detail['trd_id']) && $existingDetails->has($detail['trd_id'])) {
                $existingDetails[$detail['trd_id']]->update($detailData);

                $trdIdTravel = $detail['trd_id']; // Use existing trd_id
            } else {
                // Otherwise create new
                $vehicle_id = $detail['vehicle_id'] ?? '';
                $policy_vehicle = PolicyTadaTravelVehicle::where('pttv_id', $vehicle_id)->first();
                $policy_id = $policy_vehicle->pttv_claim_type_id ?? '';
                $eligibility = $policy_vehicle->pttv_eligibility ?? 0;
                $total_distance = $detail['total_distance'] ?? 0;

                // Calculate total amount:
                // - If claim type is 155 (CLAIM_TYPE -> Policy) and claim type is 156 (CLAIM_TYPE => Actual) (distance-based),
                //multiply total distance + tolerance by eligibility
                // - Otherwise, use the net amount directly
                if($policy_id == 155){
                    $total_amount = $total_distance * $eligibility;
                }else{
                    $total_amount = $net_amount;
                }

                // $total_amount = $total_distance * $eligibility;
                $detailData['trd_total_distance'] = $total_distance;
                $detailData['trd_net_amount'] = $total_amount;
                // TadaRequestDetail::create($detailData);

                $newDetail = TadaRequestDetail::create($detailData);
                $trdIdTravel = $newDetail->trd_id;
            }

            /* ---------------------  Distance Calculation  ------------------------------- */
            // If type is manual
            /*if (!empty($detail['source']) && !empty($detail['destination'])) {

                $apiKey = config('services.google_maps.key');
                $directionsResponse = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
                    'origin'      => $detail['source'],
                    'destination' => $detail['destination'],
                    'alternatives'=> 'true',
                    'key'         => $apiKey,
                ]);

                if ($directionsResponse->successful()) {
                    $routes          = $directionsResponse->json('routes') ?? [];
                    $routeDistances  = [];

                    foreach ($routes as $route) {
                        $routeTotalKm = 0;
                        foreach ($route['legs'] ?? [] as $leg) {
                            if (!empty($leg['distance']['value'])) {
                                $routeTotalKm += round($leg['distance']['value'] / 1000, 2);
                            }
                        }
                        if ($routeTotalKm > 0) {
                            $routeDistances[] = $routeTotalKm;
                        }
                    }

                    sort($routeDistances);

                    if (count($routeDistances) === 2) {
                        $weights = [0.6, 0.4];
                    } elseif (count($routeDistances) === 3) {
                        $weights = [0.5, 0.3, 0.2];
                    } elseif (count($routeDistances) >= 4) {
                        $weights = [0.4, 0.3, 0.2, 0.1];
                        $routeDistances = array_slice($routeDistances, 0, 4);
                    } else {
                        $weights = [1];
                    }

                    $weightedAvgKm = 0;
                    foreach ($routeDistances as $i => $km) {
                        $weightedAvgKm += $km * ($weights[$i] ?? 0);
                    }
                    $weightedAvgKm = round($weightedAvgKm, 2);

                    $distanceDiffKm = (count($routeDistances) > 1)
                        ? round(max($routeDistances) - min($routeDistances), 2)
                        : 0;

                    $routeDiffs = [];
                    for ($i = 0; $i < count($routeDistances) - 1; $i++) {
                        $routeDiffs[$i] = round($routeDistances[$i + 1] - $routeDistances[$i], 2);
                    }

                    $segmentData = [[
                        'route_index' => 0,
                        'leg_index'   => 0,
                        'distance_km' => $routeDistances[0] ?? 0,
                        'origin'      => $detail['source'],
                        'destination' => $detail['destination']
                    ]];

                    TripDistancesModel::updateOrCreate(
                        [
                            'tdc_emp_id' => $user->emp_id,
                            'tdc_trp_id' => $id,
                            'tdc_trd_id' => $trdIdTravel,
                        ],
                        [
                            'tdc_trd_id'                  => $trdIdTravel,
                            'tdc_trp_id'                  => $id,
                            'tdc_emp_id'                  => $user->emp_id,
                            'tdc_b_id'                    => $user->emp_b_id ?? '',
                            'tdc_emp_name'                => trim(($user->emp_code ?? '') . '-' . ($user->emp_full_name ?? '')),
                            'tdc_vehicle_type'            => $detail['trd_name'] ?? '',
                            'tdc_segment'                 => json_encode($segmentData),
                            'tdc_directions_response'     => json_encode($directionsResponse->json()),
                            'tdc_routes'                  => json_encode($routeDistances),
                            'tdc_avg_distance'            => round(array_sum($routeDistances) / count($routeDistances), 2),
                            'tdc_weighted_avg_cal'        => $weightedAvgKm,

                            'tdc_route_1_km'              => $routeDistances[0] ?? null,
                            'tdc_route_2_km'              => $routeDistances[1] ?? null,
                            'tdc_route_3_km'              => $routeDistances[2] ?? null,
                            'tdc_route_4_km'              => $routeDistances[3] ?? null,
                            'tdc_distance_difference_km'  => $distanceDiffKm,
                        ]
                    );
                }
            }

            // If segments
            $googleDistances = $this->calculateGoogleDistancesForSegments($detail['segments']);
            if (!empty($googleDistances['segments'])) {

                $existing = TripDistancesModel::where('tdc_emp_id', $user->emp_id)
                    ->where('tdc_trp_id', $id)
                    ->first();

                $newAvgDistance     = $googleDistances['direction_avg_distance'] ?? 0;
                $finalWeightedAvgKm = $googleDistances['final_weighted_avg_Km'] ?? 0;

                if ($existing) {
                    $newAvgDistance     += ($existing->tdc_avg_distance ?? 0);
                    $finalWeightedAvgKm += ($existing->tdc_weighted_avg_cal ?? 0);
                }

                $routes            = [];
                $routeDistances    = [];
                $routeLegDistances = [];

                foreach ($googleDistances['segments'] as $segment) {
                    $routeIndex = $segment['route_index'];
                    $legIndex   = $segment['leg_index'];
                    $distanceKm = $segment['distance_km'];

                    if (!isset($routes[$routeIndex])) {
                        $routes[$routeIndex] = [
                            'route_index' => $routeIndex,
                            'legs'        => []
                        ];
                        $routeDistances[$routeIndex]    = 0;
                        $routeLegDistances[$routeIndex] = [];
                    }

                    $routes[$routeIndex]['legs'][] = [
                        'leg_index'   => $legIndex,
                        'distance_km' => $distanceKm,
                        'origin'      => $segment['origin'],
                        'destination' => $segment['destination']
                    ];

                    $routeDistances[$routeIndex] += $distanceKm;
                    $routeLegDistances[$routeIndex][$legIndex] = $distanceKm;
                }

                $routes            = array_values($routes);
                $routeDistances    = array_values($routeDistances);
                $routeLegDistances = array_values($routeLegDistances);

                asort($routeDistances);
                $sortedDistances = array_values($routeDistances);

                $distanceDiffKm = (count($sortedDistances) > 1)
                    ? round(max($sortedDistances) - min($sortedDistances), 2)
                    : 0;

                $routeDiffs = [];
                for ($i = 0; $i < count($sortedDistances) - 1; $i++) {
                    $routeDiffs[$i] = round($sortedDistances[$i + 1] - $sortedDistances[$i], 2);
                }

                TripDistancesModel::updateOrCreate(
                    [
                        'tdc_emp_id' => $user->emp_id,
                        'tdc_trp_id' => $id,
                        'tdc_trd_id' => $trdIdTravel,
                    ],
                    [
                        'tdc_trd_id'                  => $trdIdTravel,
                        'tdc_trp_id'                  => $id,
                        'tdc_emp_id'                  => $user->emp_id,
                        'tdc_b_id'                    => $user->emp_b_id ?? '',
                        'tdc_emp_name'                => trim(($user->emp_code ?? '') . '-' . ($user->emp_full_name ?? '')),
                        'tdc_vehicle_type'            => $detail['trd_name'] ?? '',
                        'tdc_segment'                 => json_encode($detail['segments']),
                        'tdc_directions_response'     => json_encode($googleDistances['directions_response'] ?? []),
                        'tdc_routes'                  => json_encode($routes),
                        'tdc_avg_distance'            => $newAvgDistance,
                        'tdc_weighted_avg_cal'        => $finalWeightedAvgKm,

                        'tdc_route_1_km'              => $sortedDistances[0] ?? null,
                        'tdc_route_2_km'              => $sortedDistances[1] ?? null,
                        'tdc_route_3_km'              => $sortedDistances[2] ?? null,
                        'tdc_route_4_km'              => $sortedDistances[3] ?? null,
                        'tdc_distance_difference_km'  => $distanceDiffKm,
                    ]
                );
            }*/




            /* ---------------------------------------- Old Code Comment - 16-08-2025 ----------------------------------- */
            // ------------------ Safe JSON Helper ------------------
           /* function safe_json($value)
            {
                if (empty($value)) {
                    return null;
                }

                $roundNumbers = function ($item) use (&$roundNumbers) {
                    if (is_array($item)) {
                        return array_map($roundNumbers, $item);
                    }
                    if (is_numeric($item)) {
                        return number_format((float)$item, 2, '.', '');
                    }
                    return $item;
                };

                $value = $roundNumbers($value);
                return json_encode($value);
            }
            */
            // ===================== MANUAL DISTANCE CALCULATION =====================
            /*if (!empty($detail['source']) && !empty($detail['destination'])) {
                if (!is_string($detail['source']) || !is_string($detail['destination'])) {
                    throw new Exception('Source and destination must be strings');
                }

                $apiKey = config('services.google_maps.key');
                $directionsResponse = Http::get(
                    'https://maps.googleapis.com/maps/api/directions/json',
                    [
                        'origin'      => $detail['source'],
                        'destination' => $detail['destination'],
                        'alternatives'=> 'true',
                        'key'         => $apiKey,
                    ]
                );

                if ($directionsResponse->successful()) {
                    $routes = $directionsResponse->json('routes') ?? [];
                    $routeDistances = [];
                    $routeLegDetails = [];

                    foreach ($routes as $route) {
                        $routeTotalKm = 0;
                        $legsKm = [];
                        foreach ($route['legs'] ?? [] as $leg) {
                            if (!empty($leg['distance']['value'])) {
                                $km = round($leg['distance']['value'] / 1000, 2);
                                $routeTotalKm += $km;
                                $legsKm[] = $km;
                            }
                        }
                        if ($routeTotalKm > 0) {
                            $routeDistances[] = $routeTotalKm;
                            $routeLegDetails[] = $legsKm;
                        }
                    }

                    sort($routeDistances);
                    $countRoutes = count($routeDistances);
                    $weights = match ($countRoutes) {
                        2 => [0.6, 0.4],
                        3 => [0.5, 0.3, 0.2],
                        default => ($countRoutes >= 4 ? [0.4, 0.3, 0.2, 0.1] : [1]),
                    };

                    if ($countRoutes >= 4) {
                        $routeDistances = array_slice($routeDistances, 0, 4);
                        $routeLegDetails = array_slice($routeLegDetails, 0, 4);
                    }

                    $weightedAvgKm = round(
                        array_sum(array_map(fn($km, $w) => $km * $w, $routeDistances, $weights)),
                        2
                    );
                    $distanceDiffKm = $countRoutes > 1 ? round(max($routeDistances) - min($routeDistances), 2) : 0;

                    $segmentData = [];
                    foreach ($routeLegDetails as $routeIndex => $legs) {
                        foreach ($legs as $legIndex => $distance) {
                            $segmentData[] = [
                                'route_index' => $routeIndex,
                                'leg_index'   => $legIndex,
                                'distance_km' => $distance,
                                'origin'      => $detail['source'],
                                'destination' => $detail['destination']
                            ];
                        }
                    }

                    $existing = TripDistancesModel::where('tdc_emp_id', $user->emp_id)
                        ->where('tdc_trp_id', $id)
                        ->where('tdc_trd_id', $trdIdTravel)
                        ->first();

                    $combinedSegments     = $segmentData;
                    $combinedRouteLegs    = $routeLegDetails;
                    $combinedRoutes       = $routeDistances;
                    $combinedWeightedAvg  = $weightedAvgKm;
                    $combinedAvgDistance  = round(array_sum($routeDistances) / $countRoutes, 2);

                    if ($existing) {
                        $combinedSegments   = array_merge(json_decode($existing->tdc_segment, true) ?? [], $segmentData);
                        $combinedRouteLegs  = array_merge(json_decode($existing->tdc_route_legs, true) ?? [], $routeLegDetails);
                        $combinedRoutes     = array_values(array_unique(array_merge(json_decode($existing->tdc_routes, true) ?? [], $routeDistances)));
                        sort($combinedRoutes);

                        $combinedWeightedAvg = ($existing->tdc_weighted_avg_cal ?? 0) + $weightedAvgKm;
                        $combinedAvgDistance = ($existing->tdc_avg_distance ?? 0) + $combinedAvgDistance;
                    }

                    $updateData = [
                        'tdc_trd_id'                 => $trdIdTravel,
                        'tdc_trp_id'                 => $id,
                        'tdc_emp_id'                 => $user->emp_id,
                        'tdc_b_id'                   => $user->emp_b_id ?? '',
                        'tdc_emp_name'               => trim(($user->emp_code ?? '') . '-' . ($user->emp_full_name ?? '')),
                        'tdc_vehicle_type'           => $detail['trd_name'] ?? '',
                        'tdc_segment'                => safe_json($combinedSegments),
                        'tdc_directions_response'    => safe_json($directionsResponse->json()),
                        'tdc_routes'                 => safe_json($combinedRoutes),
                        'tdc_route_legs'             => safe_json($combinedRouteLegs),
                        'tdc_avg_distance'           => $combinedAvgDistance,
                        'tdc_weighted_avg_cal'       => $combinedWeightedAvg,
                        'tdc_distance_difference_km' => $distanceDiffKm,
                    ];

                    for ($i = 0; $i < 4; $i++) {
                        $prev = [];
                        $new  = isset($combinedRouteLegs[$i]) ? $combinedRouteLegs[$i] : [];

                        if ($existing) {
                            $prevData = json_decode($existing->{'tdc_route_' . ($i+1) . '_km'}, true);
                            $prev = is_array($prevData) && isset($prevData[1]) ? $prevData[1] : [];
                        }

                        $maxLen = max(count($prev), count($new));
                        $prev = array_pad($prev, $maxLen, 0.00);
                        $new  = array_pad($new,  $maxLen, 0.00);

                        $prev = array_map(fn($v) => round((float)$v, 2), $prev);
                        $new  = array_map(fn($v) => round((float)$v, 2), $new);

                        $updateData['tdc_route_' . ($i+1) . '_km'] = safe_json([$prev, $new]);
                    }


                    TripDistancesModel::updateOrCreate(
                        ['tdc_emp_id' => $user->emp_id, 'tdc_trp_id' => $id, 'tdc_trd_id' => $trdIdTravel],
                        $updateData
                    );
                } else {
                    throw new Exception('Google Maps API request failed: ' . $directionsResponse->body());
                }
            }*/

            // ===================== SEGMENT DISTANCE CALCULATION =====================
            /*$googleDistances = $this->calculateGoogleDistancesForSegments($detail['segments']);
            if (!empty($googleDistances['segments']) && is_array($googleDistances['segments'])) {
                $existing = TripDistancesModel::where('tdc_emp_id', $user->emp_id)
                    ->where('tdc_trp_id', $id)
                    ->where('tdc_trd_id', $trdIdTravel)
                    ->first();

                $newAvgDistance     = $googleDistances['direction_avg_distance'] ?? 0;
                $finalWeightedAvgKm = $googleDistances['final_weighted_avg_Km'] ?? 0;

                $routeDistances = [];
                $routeLegDistances = [];

                foreach ($googleDistances['segments'] as $segment) {
                    $ri = $segment['route_index'];
                    $li = $segment['leg_index'];
                    $km = $segment['distance_km'];

                    $routeDistances[$ri] = ($routeDistances[$ri] ?? 0) + $km;
                    $routeLegDistances[$ri][$li] = $km;
                }

                $sortedDistances = array_values($routeDistances);
                asort($sortedDistances);
                $sortedDistances = array_values($sortedDistances);

                $distanceDiffKm = count($sortedDistances) > 1 ? round(max($sortedDistances) - min($sortedDistances), 2) : 0;

                $combinedSegments     = $detail['segments'];
                $combinedRouteLegs    = array_values($routeLegDistances);
                $combinedRoutes       = $sortedDistances;
                $combinedWeightedAvg  = $finalWeightedAvgKm;
                $combinedAvgDistance  = $newAvgDistance;

                if ($existing) {
                    $combinedSegments   = array_merge(json_decode($existing->tdc_segment, true) ?? [], $detail['segments']);
                    $combinedRouteLegs  = array_merge(json_decode($existing->tdc_route_legs, true) ?? [], $combinedRouteLegs);
                    $combinedRoutes     = array_values(array_unique(array_merge(json_decode($existing->tdc_routes, true) ?? [], $sortedDistances)));
                    sort($combinedRoutes);

                    $combinedWeightedAvg = ($existing->tdc_weighted_avg_cal ?? 0) + $finalWeightedAvgKm;
                    $combinedAvgDistance = ($existing->tdc_avg_distance ?? 0) + $newAvgDistance;
                }

                $updateData = [
                    'tdc_trd_id'                 => $trdIdTravel,
                    'tdc_trp_id'                 => $id,
                    'tdc_emp_id'                 => $user->emp_id,
                    'tdc_b_id'                   => $user->emp_b_id ?? '',
                    'tdc_emp_name'               => trim(($user->emp_code ?? '') . '-' . ($user->emp_full_name ?? '')),
                    'tdc_vehicle_type'           => $detail['trd_name'] ?? '',
                    'tdc_segment'                => safe_json($combinedSegments),
                    'tdc_directions_response'    => safe_json($googleDistances['directions_response'] ?? []),
                    'tdc_routes'                 => safe_json($combinedRoutes),
                    'tdc_route_legs'             => safe_json($combinedRouteLegs),
                    'tdc_avg_distance'           => $combinedAvgDistance,
                    'tdc_weighted_avg_cal'       => $combinedWeightedAvg,
                    'tdc_distance_difference_km' => $distanceDiffKm,
                ];

                for ($i = 0; $i < 4; $i++) {
                    $prev = [];
                    $new  = isset($combinedRouteLegs[$i]) ? $combinedRouteLegs[$i] : [];

                    if ($existing) {
                        $prevData = json_decode($existing->{'tdc_route_' . ($i+1) . '_km'}, true);
                        $prev = is_array($prevData) && isset($prevData[1]) ? $prevData[1] : [];
                    }

                    $maxLen = max(count($prev), count($new));
                    $prev = array_pad($prev, $maxLen, 0.00);
                    $new  = array_pad($new,  $maxLen, 0.00);

                    $prev = array_map(fn($v) => round((float)$v, 2), $prev);
                    $new  = array_map(fn($v) => round((float)$v, 2), $new);

                    $updateData['tdc_route_' . ($i+1) . '_km'] = safe_json([$prev, $new]);
                }


                TripDistancesModel::updateOrCreate(
                    ['tdc_emp_id' => $user->emp_id, 'tdc_trp_id' => $id, 'tdc_trd_id' => $trdIdTravel],
                    $updateData
                );
            }*/

            /* ---------------------------------------- Old Code End ----------------------------------- */

         /*   // ------------------ Safe JSON Helper ------------------
            function safe_json($value)
            {
                if (empty($value)) {
                    return null;
                }

                $roundNumbers = function ($item) use (&$roundNumbers) {
                    if (is_array($item)) {
                        return array_map($roundNumbers, $item);
                    }
                    if (is_numeric($item)) {
                        return number_format((float)$item, 2, '.', '');
                    }
                    return $item;
                };

                $value = $roundNumbers($value);
                return json_encode($value);
            }

            // ===================== MANUAL DISTANCE CALCULATION =====================
            if (!empty($detail['source']) && !empty($detail['destination'])) {
                if (!is_string($detail['source']) || !is_string($detail['destination'])) {
                    throw new Exception('Source and destination must be strings');
                }

                $apiKey = config('services.google_maps.key');
                $directionsResponse = Http::get(
                    'https://maps.googleapis.com/maps/api/directions/json',
                    [
                        'origin'      => $detail['source'],
                        'destination' => $detail['destination'],
                        'alternatives'=> 'true',
                        'key'         => $apiKey,
                    ]
                );

                if ($directionsResponse->successful()) {
                    $routes = $directionsResponse->json('routes') ?? [];
                    $routeDistances = [];
                    $routeLegDetails = [];
                    $segmentData = [];

                    foreach ($routes as $routeIndex => $route) {
                        $routeTotalKm = 0;
                        $legsKm = [];
                        foreach ($route['legs'] ?? [] as $legIndex => $leg) {
                            if (!empty($leg['distance']['value'])) {
                                $km = round($leg['distance']['value'] / 1000, 2);
                                $routeTotalKm += $km;
                                $legsKm[] = number_format($km, 2, '.', '');
                                $segmentData[] = [
                                    'route_index' => $routeIndex,
                                    'leg_index'   => $legIndex,
                                    'distance_km' => $km,
                                    'origin'      => $detail['source'],
                                    'destination' => $detail['destination']
                                ];
                            }
                        }
                        if ($routeTotalKm > 0) {
                            $routeDistances[] = $routeTotalKm;
                            $routeLegDetails[] = $legsKm; // Store all legs in a single array per route
                        }
                    }

                    sort($routeDistances);
                    $countRoutes = count($routeDistances);
                    $weights = match ($countRoutes) {
                        2 => [0.6, 0.4],
                        3 => [0.5, 0.3, 0.2],
                        default => ($countRoutes >= 4 ? [0.4, 0.3, 0.2, 0.1] : [1]),
                    };

                    if ($countRoutes >= 4) {
                        $routeDistances = array_slice($routeDistances, 0, 4);
                        $routeLegDetails = array_slice($routeLegDetails, 0, 4);
                    }

                    $weightedAvgKm = round(
                        array_sum(array_map(fn($km, $w) => $km * $w, $routeDistances, $weights)),
                        2
                    );
                    $distanceDiffKm = $countRoutes > 1 ? round(max($routeDistances) - min($routeDistances), 2) : 0;

                    $existing = TripDistancesModel::where('tdc_emp_id', $user->emp_id)
                        ->where('tdc_trp_id', $id)
                        ->where('tdc_trd_id', $trdIdTravel)
                        ->first();

                    $combinedSegments     = $segmentData;
                    $combinedRouteLegs    = $routeLegDetails;
                    $combinedRoutes       = $routeDistances;
                    $combinedWeightedAvg  = $weightedAvgKm;
                    $combinedAvgDistance  = round(array_sum($routeDistances) / $countRoutes, 2);

                    if ($existing) {
                        $combinedSegments   = array_merge(json_decode($existing->tdc_segment, true) ?? [], $segmentData);
                        $combinedRouteLegs  = array_merge(json_decode($existing->tdc_route_legs, true) ?? [], $routeLegDetails);
                        $combinedRoutes     = array_values(array_unique(array_merge(json_decode($existing->tdc_routes, true) ?? [], $routeDistances)));
                        sort($combinedRoutes);

                        $combinedWeightedAvg = ($existing->tdc_weighted_avg_cal ?? 0) + $weightedAvgKm;
                        $combinedAvgDistance = ($existing->tdc_avg_distance ?? 0) + $combinedAvgDistance;
                    }

                    $updateData = [
                        'tdc_trd_id'                 => $trdIdTravel,
                        'tdc_trp_id'                 => $id,
                        'tdc_emp_id'                 => $user->emp_id,
                        'tdc_b_id'                   => $user->emp_b_id ?? '',
                        'tdc_emp_name'               => trim(($user->emp_code ?? '') . '-' . ($user->emp_full_name ?? '')),
                        'tdc_vehicle_type'           => $detail['trd_name'] ?? '',
                        'tdc_segment'                => safe_json($combinedSegments),
                        'tdc_directions_response'    => safe_json($directionsResponse->json()),
                        'tdc_routes'                 => safe_json($combinedRoutes),
                        'tdc_route_legs'             => safe_json($combinedRouteLegs),
                        'tdc_avg_distance'           => $combinedAvgDistance,
                        'tdc_weighted_avg_cal'       => $combinedWeightedAvg,
                        'tdc_distance_difference_km' => $distanceDiffKm,
                    ];

                    for ($i = 0; $i < 4; $i++) {
                        $prev = [];
                        $new  = isset($combinedRouteLegs[$i]) ? $combinedRouteLegs[$i] : [];

                        if ($existing) {
                            $prevData = json_decode($existing->{'tdc_route_' . ($i+1) . '_km'}, true);
                            $prev = is_array($prevData) && isset($prevData[1]) ? $prevData[1] : [];
                        }

                        $maxLen = max(count($prev), count($new));
                        $prev = array_pad($prev, $maxLen, 0.00);
                        $new  = array_pad($new, $maxLen, 0.00);

                        $prev = array_map(fn($v) => round((float)$v, 2), $prev);
                        $new  = array_map(fn($v) => round((float)$v, 2), $new);

                        $updateData['tdc_route_' . ($i+1) . '_km'] = safe_json([$prev, $new]);
                    }

                    TripDistancesModel::updateOrCreate(
                        ['tdc_emp_id' => $user->emp_id, 'tdc_trp_id' => $id, 'tdc_trd_id' => $trdIdTravel],
                        $updateData
                    );
                } else {
                    throw new Exception('Google Maps API request failed: ' . $directionsResponse->body());
                }
            }

            // ===================== SEGMENT DISTANCE CALCULATION =====================
            $googleDistances = $this->calculateGoogleDistancesForSegments($detail['segments']);
            if (!empty($googleDistances['segments']) && is_array($googleDistances['segments'])) {
                $existing = TripDistancesModel::where('tdc_emp_id', $user->emp_id)
                    ->where('tdc_trp_id', $id)
                    ->where('tdc_trd_id', $trdIdTravel)
                    ->first();

                $newAvgDistance     = $googleDistances['direction_avg_distance'] ?? 0;
                $finalWeightedAvgKm = $googleDistances['final_weighted_avg_Km'] ?? 0;

                $routeDistances = [];
                $routeLegDistances = [];

                foreach ($googleDistances['segments'] as $segment) {
                    $ri = $segment['route_index'];
                    $li = $segment['leg_index'];
                    $km = $segment['distance_km'];

                    $routeDistances[$ri] = ($routeDistances[$ri] ?? 0) + $km;
                    $routeLegDistances[$ri][] = number_format($km, 2, '.', '');
                }

                $sortedDistances = array_values($routeDistances);
                asort($sortedDistances);
                $sortedDistances = array_values($sortedDistances);

                $distanceDiffKm = count($sortedDistances) > 1 ? round(max($sortedDistances) - min($sortedDistances), 2) : 0;

                $combinedSegments     = $detail['segments'];
                $combinedRouteLegs    = array_values($routeLegDistances);
                $combinedRoutes       = $sortedDistances;
                $combinedWeightedAvg  = $finalWeightedAvgKm;
                $combinedAvgDistance  = $newAvgDistance;

                if ($existing) {
                    $combinedSegments   = array_merge(json_decode($existing->tdc_segment, true) ?? [], $detail['segments']);
                    $combinedRouteLegs  = array_merge(json_decode($existing->tdc_route_legs, true) ?? [], $combinedRouteLegs);
                    $combinedRoutes     = array_values(array_unique(array_merge(json_decode($existing->tdc_routes, true) ?? [], $sortedDistances)));
                    sort($combinedRoutes);

                    $combinedWeightedAvg = ($existing->tdc_weighted_avg_cal ?? 0) + $finalWeightedAvgKm;
                    $combinedAvgDistance = ($existing->tdc_avg_distance ?? 0) + $newAvgDistance;
                }

                $updateData = [
                    'tdc_trd_id'                 => $trdIdTravel,
                    'tdc_trp_id'                 => $id,
                    'tdc_emp_id'                 => $user->emp_id,
                    'tdc_b_id'                   => $user->emp_b_id ?? '',
                    'tdc_emp_name'               => trim(($user->emp_code ?? '') . '-' . ($user->emp_full_name ?? '')),
                    'tdc_vehicle_type'           => $detail['trd_name'] ?? '',
                    'tdc_segment'                => safe_json($combinedSegments),
                    'tdc_directions_response'    => safe_json($googleDistances['directions_response'] ?? []),
                    'tdc_routes'                 => safe_json($combinedRoutes),
                    'tdc_route_legs'             => safe_json($combinedRouteLegs),
                    'tdc_avg_distance'           => $combinedAvgDistance,
                    'tdc_weighted_avg_cal'       => $combinedWeightedAvg,
                    'tdc_distance_difference_km' => $distanceDiffKm,
                ];

                for ($i = 0; $i < 4; $i++) {
                    $prev = [];
                    $new  = isset($combinedRouteLegs[$i]) ? $combinedRouteLegs[$i] : [];

                    if ($existing) {
                        $prevData = json_decode($existing->{'tdc_route_' . ($i+1) . '_km'}, true);
                        $prev = is_array($prevData) && isset($prevData[1]) ? $prevData[1] : [];
                    }

                    $maxLen = max(count($prev), count($new));
                    $prev = array_pad($prev, $maxLen, 0.00);
                    $new  = array_pad($new, $maxLen, 0.00);

                    $prev = array_map(fn($v) => round((float)$v, 2), $prev);
                    $new  = array_map(fn($v) => round((float)$v, 2), $new);

                    $updateData['tdc_route_' . ($i+1) . '_km'] = safe_json([$prev, $new]);
                }

                TripDistancesModel::updateOrCreate(
                    ['tdc_emp_id' => $user->emp_id, 'tdc_trp_id' => $id, 'tdc_trd_id' => $trdIdTravel],
                    $updateData
                );
            }*/

            // ------------------ Safe JSON Helper ------------------
            function safe_json($value)
            {
                if (empty($value)) {
                    return null;
                }

                $roundNumbers = function ($item) use (&$roundNumbers) {
                    if (is_array($item)) {
                        return array_map($roundNumbers, $item);
                    }
                    if (is_numeric($item)) {
                        return number_format((float)$item, 2, '.', '');
                    }
                    return $item;
                };

                $value = $roundNumbers($value);
                return json_encode($value);
            }

         // ===================== MANUAL DISTANCE CALCULATION =====================
            if (!empty($detail['source']) && !empty($detail['destination'])) {
                if (!is_string($detail['source']) || !is_string($detail['destination'])) {
                    throw new Exception('Source and destination must be strings');
                }

                $apiKey = config('services.google_maps.key');
                $directionsResponse = Http::get(
                    'https://maps.googleapis.com/maps/api/directions/json',
                    [
                        'origin'      => $detail['source'],
                        'destination' => $detail['destination'],
                        'alternatives'=> 'true',
                        'key'         => $apiKey,
                    ]
                );

                if ($directionsResponse->successful()) {
                    $routes = $directionsResponse->json('routes') ?? [];
                    $routeDistances = [];
                    $routeLegDetails = [];
                    $segmentData = [];

                    foreach ($routes as $routeIndex => $route) {
                        $routeTotalKm = 0;
                        $legsKm = [];
                        foreach ($route['legs'] ?? [] as $legIndex => $leg) {
                            if (!empty($leg['distance']['value'])) {
                                $km = round($leg['distance']['value'] / 1000, 2);
                                $routeTotalKm += $km;
                                // $legsKm[] = number_format($km, 2, '.', '');
                                $legsKm[] = (float) number_format($km, 2, '.', '');  // store as float, not ["value"]

                                $segmentData[] = [
                                    'route_index' => $routeIndex,
                                    'leg_index'   => $legIndex,
                                    'distance_km' => $km,
                                    'origin'      => $detail['source'],
                                    'destination' => $detail['destination']
                                ];
                            }
                        }
                        if ($routeTotalKm > 0) {
                            $routeDistances[] = $routeTotalKm;
                            $routeLegDetails[] = $legsKm;
                        }
                    }

                    sort($routeDistances);
                    $countRoutes = count($routeDistances);
                    $weights = match ($countRoutes) {
                        2 => [0.6, 0.4],
                        3 => [0.5, 0.3, 0.2],
                        default => ($countRoutes >= 4 ? [0.4, 0.3, 0.2, 0.1] : [1]),
                    };

                    if ($countRoutes >= 4) {
                        $routeDistances = array_slice($routeDistances, 0, 4);
                        $routeLegDetails = array_slice($routeLegDetails, 0, 4);
                    }

                    $weightedAvgKm = round(
                        array_sum(array_map(fn($km, $w) => $km * $w, $routeDistances, $weights)),
                        2
                    );
                    $distanceDiffKm = $countRoutes > 1 ? round(max($routeDistances) - min($routeDistances), 2) : 0;

                    $googleDistances = [
                                            'final_weighted_avg_Km'   => $weightedAvgKm,
                                            'direction_avg_distance'  => $countRoutes > 0
                                            ? round(array_sum($routeDistances) / $countRoutes, 2)
                                            : 0,
                                        ];

                    $existing = TripDistancesModel::where('tdc_emp_id', $user->emp_id)
                        ->where('tdc_trp_id', $id)
                        ->where('tdc_trd_id', $trdIdTravel)
                        ->first();


                    $newAvgDistance     = $googleDistances['direction_avg_distance'] ?? 0;
                    $finalWeightedAvgKm = $googleDistances['final_weighted_avg_Km'] ?? 0;


                    $combinedSegments     = $segmentData;
                    $combinedRouteLegs    = $routeLegDetails;
                    $combinedRoutes       = $routeLegDetails;
                    // $combinedWeightedAvg  = $weightedAvgKm;

                    $combinedWeightedAvg = ($existing->tdc_weighted_avg_cal ?? 0) + $finalWeightedAvgKm;
                    $combinedAvgDistance = ($existing->tdc_avg_distance ?? 0) + $newAvgDistance;

                    // $combinedAvgDistance  = round(array_sum($routeDistances) / $countRoutes, 2);

                    if ($existing) {
                        $combinedSegments   = array_merge(json_decode($existing->tdc_segment, true) ?? [], $segmentData);
                        $combinedRouteLegs  = array_merge(json_decode($existing->tdc_route_legs, true) ?? [], $routeLegDetails);
                        $combinedRoutes     = array_merge(json_decode($existing->tdc_routes, true) ?? [], $routeLegDetails);
                    }

                    // progressive routes with timestamp
                    $progressiveWithTime = [
                        'timestamp' => now()->toDateTimeString(),
                        'routes'    => $combinedRouteLegs
                    ];

                    $updateData = [
                        'tdc_trd_id'                 => $trdIdTravel,
                        'tdc_trp_id'                 => $id,
                        'tdc_emp_id'                 => $user->emp_id,
                        'tdc_b_id'                   => $user->emp_b_id ?? '',
                        'tdc_emp_name'               => trim(($user->emp_code ?? '') . '-' . ($user->emp_full_name ?? '')),
                        'tdc_vehicle_type'           => $detail['trd_name'] ?? '',
                        'tdc_segment'                => safe_json($combinedSegments),
                        'tdc_directions_response'    => safe_json($directionsResponse->json()),
                        'tdc_routes'                 => safe_json($combinedRoutes),
                        'tdc_route_legs'             => safe_json($combinedRouteLegs),
                        'tdc_routes_progress'        => safe_json($progressiveWithTime),
                        'tdc_avg_distance'           => $combinedAvgDistance,
                        'tdc_weighted_avg_cal'       => $combinedWeightedAvg,
                        'tdc_distance_difference_km' => $distanceDiffKm,
                    ];

                    for ($i = 0; $i < 4; $i++) {
                        $prev = [];
                        $new  = isset($combinedRouteLegs[$i]) ? $combinedRouteLegs[$i] : [];

                        if ($existing) {
                            $prevData = json_decode($existing->{'tdc_route_' . ($i+1) . '_km'}, true);
                            $prev = is_array($prevData) && isset($prevData[1]) ? $prevData[1] : [];
                        }

                        $maxLen = max(count($prev), count($new));
                        $prev = array_pad($prev, $maxLen, 0.00);
                        $new  = array_pad($new, $maxLen, 0.00);

                        $prev = array_map(fn($v) => round((float)$v, 2), $prev);
                        $new  = array_map(fn($v) => round((float)$v, 2), $new);

                        $updateData['tdc_route_' . ($i+1) . '_km'] = safe_json([$prev, $new]);
                    }

                    TripDistancesModel::updateOrCreate(
                        ['tdc_emp_id' => $user->emp_id, 'tdc_trp_id' => $id, 'tdc_trd_id' => $trdIdTravel],
                        $updateData
                    );
                } else {
                    throw new Exception('Google Maps API request failed: ' . $directionsResponse->body());
                }
            }

            // ===================== SEGMENT DISTANCE CALCULATION =====================
            $googleDistances = $this->calculateGoogleDistancesForSegments($detail['segments']);
            if (!empty($googleDistances['segments']) && is_array($googleDistances['segments'])) {
                $existing = TripDistancesModel::where('tdc_emp_id', $user->emp_id)
                    ->where('tdc_trp_id', $id)
                    ->where('tdc_trd_id', $trdIdTravel)
                    ->first();

                $newAvgDistance     = $googleDistances['direction_avg_distance'] ?? 0;
                $finalWeightedAvgKm = $googleDistances['final_weighted_avg_Km'] ?? 0;

                $routeDistances = [];
                $routeLegDistances = [];

                foreach ($googleDistances['segments'] as $segment) {
                    $ri = $segment['route_index'];
                    $li = $segment['leg_index'];
                    $km = $segment['distance_km'];

                    $routeDistances[$ri] = ($routeDistances[$ri] ?? 0) + $km;
                    // $routeLegDistances[$ri][] = number_format($km, 2, '.', '');
                    $routeLegDistances[$ri][] = (float) number_format($km, 2, '.', '');
                }

                $sortedDistances = array_values($routeDistances);
                asort($sortedDistances);
                $sortedDistances = array_values($sortedDistances);

                $distanceDiffKm = count($sortedDistances) > 1 ? round(max($sortedDistances) - min($sortedDistances), 2) : 0;

                $combinedSegments     = $detail['segments'];
                $combinedRouteLegs    = array_values($routeLegDistances);
                $combinedRoutes       = array_values($routeLegDistances);
               /* $combinedWeightedAvg  = $finalWeightedAvgKm;
                $combinedAvgDistance  = $newAvgDistance;*/


                $combinedWeightedAvg = ($existing->tdc_weighted_avg_cal ?? 0) + $finalWeightedAvgKm;
                    $combinedAvgDistance = ($existing->tdc_avg_distance ?? 0) + $newAvgDistance;

                if ($existing) {
                    $combinedSegments   = array_merge(json_decode($existing->tdc_segment, true) ?? [], $detail['segments']);
                    $combinedRouteLegs  = array_merge(json_decode($existing->tdc_route_legs, true) ?? [], $combinedRouteLegs);
                    $combinedRoutes     = array_merge(json_decode($existing->tdc_routes, true) ?? [], $combinedRouteLegs);
                }

                $progressiveWithTime = [
                    'timestamp' => now()->toDateTimeString(),
                    'routes'    => $combinedRouteLegs
                ];

                $updateData = [
                    'tdc_trd_id'                 => $trdIdTravel,
                    'tdc_trp_id'                 => $id,
                    'tdc_emp_id'                 => $user->emp_id,
                    'tdc_b_id'                   => $user->emp_b_id ?? '',
                    'tdc_emp_name'               => trim(($user->emp_code ?? '') . '-' . ($user->emp_full_name ?? '')),
                    'tdc_vehicle_type'           => $detail['trd_name'] ?? '',
                    'tdc_segment'                => safe_json($combinedSegments),
                    'tdc_directions_response'    => safe_json($googleDistances['directions_response'] ?? []),
                    'tdc_routes'                 => safe_json($combinedRoutes),
                    'tdc_route_legs'             => safe_json($combinedRouteLegs),
                    'tdc_routes_progress'        => safe_json($progressiveWithTime),
                    'tdc_avg_distance'           => $combinedAvgDistance,
                    'tdc_weighted_avg_cal'       => $combinedWeightedAvg,
                    'tdc_distance_difference_km' => $distanceDiffKm,
                ];

                for ($i = 0; $i < 4; $i++) {
                    $prev = [];
                    $new  = isset($combinedRouteLegs[$i]) ? $combinedRouteLegs[$i] : [];

                    if ($existing) {
                        $prevData = json_decode($existing->{'tdc_route_' . ($i+1) . '_km'}, true);
                        $prev = is_array($prevData) && isset($prevData[1]) ? $prevData[1] : [];
                    }

                    $maxLen = max(count($prev), count($new));
                    $prev = array_pad($prev, $maxLen, 0.00);
                    $new  = array_pad($new, $maxLen, 0.00);

                    $prev = array_map(fn($v) => round((float)$v, 2), $prev);
                    $new  = array_map(fn($v) => round((float)$v, 2), $new);

                    $updateData['tdc_route_' . ($i+1) . '_km'] = safe_json([$prev, $new]);
                }

                TripDistancesModel::updateOrCreate(
                    ['tdc_emp_id' => $user->emp_id, 'tdc_trp_id' => $id, 'tdc_trd_id' => $trdIdTravel],
                    $updateData
                );
            }

        }

        if (TadaRequestDetail::where('trd_trp_id', $id)->count()) {
            TadaRequestPlan::where('trp_id', $id)->update(['trp_is_details_added' => 1]);
        }

        return ReturnHelper::jsonApiReturn(PlanRequestDetailResource::collection(
            TadaRequestDetail::where('trd_trp_id', $id)->get()
        )->all());

        // Return updated plan details if available
        if ($updatePlan) {
            return ReturnHelper::jsonApiReturn(FilterPlanRequestApiResource::collection($updatePlan)->all());
        }

        return response()->json(['result' => [], 'status' => false]);
    }

   /* private function calculateAverageGoogleDistanceKm(string $origin, string $destination): ?float
    {
        if (empty($origin) || empty($destination)) {
            Log::warning('Google Maps distance calculation skipped: Missing origin or destination.', [
                'origin' => $origin,
                'destination' => $destination
            ]);
            return null;
        }

        try {
            $apiKey = config('services.google_maps.key');

            Log::info('Google Maps Distance Calculation Started', [
                'origin' => $origin,
                'destination' => $destination
            ]);

            // Directions API call
            $directionsResponse = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
                'origin'      => $origin,
                'destination' => $destination,
                'alternatives'=> 'true',
                // 'mode'        => 'driving',
                'key'         => $apiKey,
            ]);

            Log::info('Directions API raw response', [
                'status_code' => $directionsResponse->status(),
                'body'        => $directionsResponse->json()
            ]);

            if ($directionsResponse->successful()) {
                $routes = $directionsResponse->json('routes') ?? [];
                $totalDistance = 0;
                $count = 0;

                foreach ($routes as $routeIndex => $route) {
                    foreach ($route['legs'] ?? [] as $legIndex => $leg) {
                        if (!empty($leg['distance']['value'])) {
                            $km = round($leg['distance']['value'] / 1000, 2);
                            Log::info("Directions API Route {$routeIndex} Leg {$legIndex} distance (km)", [
                                'distance_km' => $km
                            ]);
                            $totalDistance += $leg['distance']['value'];
                            $count++;
                        }
                    }
                }

                $directionsKm = $count ? round($totalDistance / $count / 1000, 2) : null;

                Log::info('Directions API calculated average distance (km)', [
                    'average_km' => $directionsKm,
                    'routes_count' => $count
                ]);

                return $directionsKm;
            }

            Log::warning('Directions API request failed', [
                'origin' => $origin,
                'destination' => $destination
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Google Maps Distance Calculation Error', [
                'origin' => $origin,
                'destination' => $destination,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }*/


    /*private function calculateGoogleDistancesForSegments(array $segments): array
    {
        $distances = [];
        $totalKm = 0;
        $count = 0;
        $lastDirectionsResponse = null;

        if (count($segments) < 2) {
            return [
                'directions_response'    => [],
                'direction_avg_distance' => null,
                'segments'               => [],
                'total_distance'         => 0
            ];
        }

        $apiKey = config('services.google_maps.key');


        $segmentCount = count($segments);

        // Only take the last 2 segments
        $startIndex = max(0, $segmentCount - 2);

        for ($i = $startIndex; $i < $segmentCount - 1; $i++) {
            $origin      = "{$segments[$i]['latitude']},{$segments[$i]['longitude']}";
            $destination = "{$segments[$i + 1]['latitude']},{$segments[$i + 1]['longitude']}";

            $directionsResponse = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
                'origin'      => $origin,
                'destination' => $destination,
                'alternatives'=> 'true',
                // 'mode'        => 'driving',
                'key'         => $apiKey,
            ]);

            if ($directionsResponse->successful())
            {
                $lastDirectionsResponse = $directionsResponse->json();

                $routes = $lastDirectionsResponse['routes'] ?? [];

                $routeDistances = [];
                $distances = [];
                $totalKm = 0;
                $count = 0;

                foreach ($routes as $routeIndex => $route) {
                    $routeTotalKm = 0;
                    \Log::info("Processing Route #{$routeIndex}");

                    foreach ($route['legs'] ?? [] as $legIndex => $leg) {
                        if (!empty($leg['distance']['value'])) {
                            $legKm = round($leg['distance']['value'] / 1000, 2);
                            $routeTotalKm += $legKm;
                            $totalKm += $legKm;
                            $count++;

                            $origin      = $leg['start_address'] ?? '';
                            $destination = $leg['end_address'] ?? '';

                            $distances[] = [
                                'route_index'  => $routeIndex,
                                'leg_index'    => $legIndex,
                                'distance_km'  => $legKm,
                                'origin'       => $origin,
                                'destination'  => $destination,
                            ];

                            \Log::info("Leg #{$legIndex} Distance: {$legKm} km | Origin: {$origin} | Destination: {$destination}");
                        }
                    }

                    if ($routeTotalKm > 0) {
                        $routeDistances[] = $routeTotalKm;
                        \Log::info("Total Distance for Route #{$routeIndex}: {$routeTotalKm} km");
                    }
                }

                \Log::info("RouteDistances Array:", $routeDistances);

                // Apply weighted average according to DOCX rules
                sort($routeDistances); // shortest first
                $countRoutes = count($routeDistances);
                \Log::info("Sorted Route Distances:", $routeDistances);
                \Log::info("Count of Routes: {$countRoutes}");

                $weightedAvgKm = 0;
                if ($countRoutes === 2) {
                    $weights = [0.6, 0.4];
                } elseif ($countRoutes === 3) {
                    $weights = [0.5, 0.3, 0.2];
                } elseif ($countRoutes >= 4) {
                    $weights = [0.4, 0.3, 0.2, 0.1];
                    $routeDistances = array_slice($routeDistances, 0, 4); // only 4 routes considered
                } else {
                    $weights = [1]; // single route
                }

                \Log::info("Weights Used:", $weights);

                foreach ($routeDistances as $i => $km) {
                    $weightedPart = $km * ($weights[$i] ?? 0);
                    $weightedAvgKm += $weightedPart;
                    \Log::info("Weighted Calculation for Route #{$i}: {$km} km * {$weights[$i]} = {$weightedPart}");
                }
                \Log::info("Final Weighted Average Distance: {$weightedAvgKm} km");
            } else {
                \Log::warning("Directions API call was not successful.");
            }

            // Calculate segment average distance
            $segmentDistance = $count ? round(($totalKm / $count), 2) : null;


            $weightedAvgKmCal = round(($weightedAvgKm), 2);

            \Log::info("Leg Count: {$count}");
            \Log::info("Segment Average Distance: {$segmentDistance} km");

            // Return data
            return [
                'directions_response'    => $lastDirectionsResponse ?? [],
                'direction_avg_distance' => $segmentDistance,
                'segments'               => $distances,
                'final_weighted_avg_Km' => $weightedAvgKmCal,
            ];
        }
    }*/

    private function calculateGoogleDistancesForSegments(array $segments): array
    {
        $distances = [];
        $totalKm = 0;
        $count = 0;
        $lastDirectionsResponse = null;

        if (count($segments) < 2) {
            return [
                'directions_response' => [],
                'direction_avg_distance' => null,
                'segments' => [],
                'total_distance' => 0,
                'final_weighted_avg_Km' => 0
            ];
        }

        $apiKey = config('services.google_maps.key');
        $segmentCount = count($segments);
        $startIndex = max(0, $segmentCount - 2);

        for ($i = $startIndex; $i < $segmentCount - 1; $i++) {
            $origin = "{$segments[$i]['latitude']},{$segments[$i]['longitude']}";
            $destination = "{$segments[$i + 1]['latitude']},{$segments[$i + 1]['longitude']}";

            $directionsResponse = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
                'origin' => $origin,
                'destination' => $destination,
                'alternatives' => 'true',
                'key' => $apiKey,
            ]);

            if ($directionsResponse->successful()) {
                $lastDirectionsResponse = $directionsResponse->json();
                $routes = $lastDirectionsResponse['routes'] ?? [];
                $routeDistances = [];
                $distances = [];
                $totalKm = 0;
                $count = 0;

                foreach ($routes as $routeIndex => $route) {
                    $routeTotalKm = 0;
                    Log::info("Processing Route #{$routeIndex}");

                    foreach ($route['legs'] ?? [] as $legIndex => $leg) {
                        if (!empty($leg['distance']['value'])) {
                            $legKm = round($leg['distance']['value'] / 1000, 2);
                            $routeTotalKm += $legKm;
                            $totalKm += $legKm;
                            $count++;

                            $origin = $leg['start_address'] ?? '';
                            $destination = $leg['end_address'] ?? '';

                            $distances[] = [
                                'route_index' => $routeIndex,
                                'leg_index' => $legIndex,
                                'distance_km' => $legKm,
                                'origin' => $origin,
                                'destination' => $destination,
                            ];

                            Log::info("Leg #{$legIndex} Distance: {$legKm} km | Origin: {$origin} | Destination: {$destination}");
                        }
                    }

                    if ($routeTotalKm > 0) {
                        $routeDistances[] = $routeTotalKm;
                        Log::info("Total Distance for Route #{$routeIndex}: {$routeTotalKm} km");
                    }
                }

                Log::info("RouteDistances Array:", $routeDistances);

                // Apply weighted average according to DOCX rules
                sort($routeDistances); // shortest first
                $countRoutes = count($routeDistances);
                Log::info("Sorted Route Distances:", $routeDistances);
                Log::info("Count of Routes: {$countRoutes}");

                $weightedAvgKm = 0;
                if ($countRoutes === 2) {
                    $weights = [0.6, 0.4];
                } elseif ($countRoutes === 3) {
                    $weights = [0.5, 0.3, 0.2];
                } elseif ($countRoutes >= 4) {
                    $weights = [0.4, 0.3, 0.2, 0.1];
                    $routeDistances = array_slice($routeDistances, 0, 4);
                } else {
                    $weights = [1];
                }

                Log::info("Weights Used:", $weights);

                foreach ($routeDistances as $i => $km) {
                    $weightedPart = $km * ($weights[$i] ?? 0);
                    $weightedAvgKm += $weightedPart;
                    Log::info("Weighted Calculation for Route #{$i}: {$km} km * {$weights[$i]} = {$weightedPart}");
                }
                Log::info("Final Weighted Average Distance: {$weightedAvgKm} km");

                $segmentDistance = $count ? round(($totalKm / $count), 2) : null;
                $weightedAvgKmCal = round($weightedAvgKm, 2);

                Log::info("Leg Count: {$count}");
                Log::info("Segment Average Distance: {$segmentDistance} km");

                return [
                    'directions_response' => $lastDirectionsResponse ?? [],
                    'direction_avg_distance' => $segmentDistance,
                    'segments' => $distances,
                    'total_distance' => $totalKm,
                    'final_weighted_avg_Km' => $weightedAvgKmCal,
                ];
            } else {
                Log::warning("Directions API call was not successful.");
                return [
                    'directions_response' => [],
                    'direction_avg_distance' => null,
                    'segments' => [],
                    'total_distance' => 0,
                    'final_weighted_avg_Km' => 0
                ];
            }
        }

        // Fallback return in case the loop doesn't execute
        return [
            'directions_response' => [],
            'direction_avg_distance' => null,
            'segments' => [],
            'total_distance' => 0,
            'final_weighted_avg_Km' => 0
        ];
    }


    // Helper methods for date and time parsing
    private function parseDate($date)
    {
        try {
            return $date ? Carbon::parse($date)->format('Y-m-d') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseTime($time)
    {
        try {
            return $time ? Carbon::parse($time)->format('H:i:s') : null;
        } catch (\Exception $e) {
            return null;
        }
    }


   /**
     * Remove the specified resource from storage.
     */

    /*public function destroy(string $id)
    {
        $user = Auth::user();
        $user_b_id = $user->emp_b_id;
        $deleteDetail = TadaRequestDetail::with('fh_policy_tada_request_plan')
            ->where('trd_id', $id)
            ->whereHas('fh_policy_tada_request_plan', function ($query) use ($user_b_id) {
                $query->where('trp_b_id', $user_b_id);
            })
            ->first();
        if ($deleteDetail) {
            $deleteDetail->delete();
            return response()->json(['result' => [], 'status' => true]);
        }
        return response()->json(['result' => [], 'status' => false]);

    }*/

    public function destroy(string $id)
    {
        $user = Auth::user();
        $user_b_id = $user->emp_b_id;

        // Fetch the TadaRequestDetail with business unit check
        $tadaDetail = TadaRequestDetail::with('fh_policy_tada_request_plan')
            ->where('trd_id', $id)
            ->whereHas('fh_policy_tada_request_plan', function ($query) use ($user_b_id) {
                $query->where('trp_b_id', $user_b_id);
            })
            ->first();

        if ($tadaDetail) {
            // Delete associated travel locations
            TravelLocation::where('lc_trd_id', $tadaDetail->trd_id)->delete();

            // Delete main TadaRequestDetail
            $tadaDetail->delete();
            return response()->json(['result' => [], 'status' => true, 'message' => 'TADA detail and associated travel locations deleted successfully.']);
        }
        return response()->json(['result' => [], 'status' => false,  'message' => 'Delete operation failed.']);
    }


    public function detailsList(TravelDetailsListRequest $request)
    {
        $data = $request->validated();
        $plan_id = $data['plan_id'];
        $details_type = $data['details_type'];
        $details = collect();

        if ($details_type == '158') {
            $details = TadaRequestDetail::where('trd_trp_id', $plan_id)->where('trd_type_id', 182)->get();
        } elseif ($details_type == '159') {

            $details = TadaRequestDetail::with('fh_policy_tada_travel_vehicle')->where('trd_trp_id', $plan_id)
                ->where('trd_type_id', 181)
                ->whereHas('fh_policy_tada_travel_vehicle', function ($q) {
                    $q->where('pttv_claim_type_id', 154);
                })
                ->get();
        }

        if ($details) {
            return ReturnHelper::jsonApiReturn(PlanRequestDetailResource::collection($details)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    public function getNextApprovalDetails($type, $id)
    {
        $user = Auth::user();

        if ($type == 146) { // Claim
            $request = TadaClaim::with(['fh_process_approvers.fh_employee', 'fh_deduction_log'])
                ->where('tc_b_id', $user->emp_b_id)
                ->where('tc_id', $id)
                ->first();


            if ($request) {
                if ($request->tc_stage_completed == 1) {
                    return ReturnHelper::jsonApiReturn(['message' => 'Completed', 'data' => null]);
                }
                if ($request->tc_status == 170) {
                    return ReturnHelper::jsonApiReturn(['message' => 'Request is rejected.', 'data' => null]);
                }
                $approverIds = $request->fh_process_approvers ? $request->fh_process_approvers->pluck('pa_emp_id') : collect();
                $deductionLog = $request->fh_deduction_log->sortByDesc('dlog_id')->first();
                $logUserIds = $request->fh_claim_approval_log ? $request->fh_claim_approval_log->pluck('log_user_id') : collect();
                if ($deductionLog) {
                    if (is_null($deductionLog->dlog_requester_action)) {
                        return ReturnHelper::jsonApiReturn(['message' => 'Request has been sent to the user.', 'data' => null]);
                    } elseif ($deductionLog->dlog_requester_action === 0) {
                        return ReturnHelper::jsonApiReturn(['message' => 'Claim was declined.', 'data' => null]);
                    }
                }

                // Get the difference in user IDs
                $difference = $approverIds->diff($logUserIds);
                $firstDifference = $difference->first();

                if ($firstDifference) {
                    $nextApprover = $request->fh_process_approvers->firstWhere('pa_emp_id', $firstDifference);
                    $nextApprovalData = $nextApprover ? $nextApprover->fh_employee : null;

                    if ($nextApprovalData) {
                        return ReturnHelper::jsonApiReturn([
                            'message' => 'Next approval data retrieved successfully.',
                            'data' => $nextApprovalData->emp_full_name // Only return emp_full_name
                        ]);
                    } else {
                        return ReturnHelper::jsonApiReturn(['message' => 'No next approval data available.', 'data' => null]);
                    }
                } else {
                    return ReturnHelper::jsonApiReturn(['message' => 'No pending approval.', 'data' => null]);
                }
            } else {
                return ReturnHelper::jsonApiReturn(['message' => 'No TadaClaim found.', 'data' => null]);
            }
        } elseif ($type == 145) { // Travel
            $request = TadaRequestPlan::with(['fh_process_approvers.fh_employee'])
                ->where('trp_b_id', $user->emp_b_id)
                ->where('trp_id', $id)
                ->first();

            if ($request) {
                if ($request->trp_stage_completed == 1) {
                    return ReturnHelper::jsonApiReturn(['message' => 'Completed', 'data' => null]);
                }
                if ($request->trp_request_status == 170) {
                    return ReturnHelper::jsonApiReturn(['message' => 'Request is rejected.', 'data' => null]);
                }
                $approverIds = $request->fh_process_approvers ? $request->fh_process_approvers->pluck('pa_emp_id') : collect();
                $logUserIds = $request->fh_plan_approval_log ? $request->fh_plan_approval_log->pluck('log_user_id') : collect();

                // Get the difference in user IDs
                $difference = $approverIds->diff($logUserIds);
                $firstDifference = $difference->first();

                if ($firstDifference) {
                    $nextApprover = $request->fh_process_approvers->firstWhere('pa_emp_id', $firstDifference);
                    $nextApprovalData = $nextApprover ? $nextApprover->fh_employee : null;

                    if ($nextApprovalData) {
                        return ReturnHelper::jsonApiReturn([
                            'message' => 'Next approval data retrieved successfully.',
                            'data' => $nextApprovalData->emp_full_name // Only return emp_full_name
                        ]);
                    } else {
                        return ReturnHelper::jsonApiReturn(['message' => 'No next approval data available.', 'data' => null]);
                    }
                } else {
                    return ReturnHelper::jsonApiReturn(['message' => 'No pending approval.', 'data' => null]);
                }
            } else {
                return ReturnHelper::jsonApiReturn(['message' => 'No TadaRequestPlan found.', 'data' => null]);
            }
        }

        // Default case if type does not match
        return ReturnHelper::jsonApiReturn(['message' => 'Invalid request type.', 'data' => null]);
    }



    //********************************************************************** add localtion function start
    public function getLocationName(Request $request)
    {
        // Retrieve latitude and longitude from query parameters
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');

        // Call your function to get detailed location information
        $locationDetails = $this->getLocationFromCoordinates($latitude, $longitude);
        return response()->json($locationDetails);  // Return detailed location info
    }

    public function getCurrentPlanIds()
    {
        $user = Auth::user();
        $currentDateTime = Carbon::now();

        $plan = TadaRequestPlan::whereHas('fh_policy_tada_travel_type', function ($query) {
            $query->where('pttt_type_id', 124);
        })
            ->where('trp_emp_id', $user->emp_id)
            ->whereRaw("CONCAT(trp_start_date, ' ', trp_start_time) <= ?", [$currentDateTime])
            ->whereRaw("CONCAT(trp_end_date, ' ', trp_end_time) >= ?", [$currentDateTime])
            ->where('trp_is_claimed', 0)
            ->select('trp_id', 'trp_pttt_id', 'trp_name', 'trp_unique_id', 'trp_start_time', 'trp_end_time')
            ->first();

        if ($plan) {
            return [
                'result' => [
                    'trp_id' => $plan->trp_id,
                    'trp_pttt_id' => $plan->trp_pttt_id,
                    'trp_name' => $plan->trp_name,
                    'trp_unique_id' => $plan->trp_unique_id,
                    'trp_start_time' => $plan->trp_start_time,
                    'trp_end_time' => $plan->trp_end_time,
                    'is_manual' => $plan->fh_policy_tada_travel_type->pttt_approval_type_id == 198
                ]
            ];
        } else {
            return [
                'status' => false,
                'message' => 'No active plan found.'
            ];
        }
    }

    private function getLocationFromCoordinates($latitude, $longitude)
    {
        // Your Google Maps API key from .env
        $apiKey = config('credentials')['MAP_API_KEY'];

        // Make the API request
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => $latitude . ',' . $longitude,
            'key' => $apiKey,
        ]);

        // Convert the JSON response into an array
        $data = $response->json();

        // Check if the response contains results
        if (!empty($data['results'])) {
            // Extract detailed address components
            $addressComponents = $data['results'][0]['address_components'];

            // Prepare a detailed response~
            $locationDetails = [
                'formatted_address' => $data['results'][0]['formatted_address'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'country' => $this->getAddressComponent($addressComponents, 'country'),
                'state' => $this->getAddressComponent($addressComponents, 'administrative_area_level_1'),
                'city' => $this->getAddressComponent($addressComponents, 'locality'),
                'sublocality' => $this->getAddressComponent($addressComponents, 'sublocality'),
                'postal_code' => $this->getAddressComponent($addressComponents, 'postal_code'),
                'neighborhood' => $this->getAddressComponent($addressComponents, 'neighborhood'),
                'area' => $this->getAddressComponent($addressComponents, 'administrative_area_level_2'),  // Can represent district/area
            ];

            return $locationDetails;
        }

        return null;
    }

    private function getAddressComponent($components, $type)
    {
        // Helper function to extract the specific type from address components
        foreach ($components as $component) {
            if (in_array($type, $component['types'])) {
                return $component['long_name'];  // Return the full name of the component
            }
        }

        return null;  // Return null if the component is not found
    }
    //*************************************************************************************** add localtion function end


    public function getTravelDetails()
    {
        $user = Auth::user();
        $currentDate = Carbon::today(); // Gets today's date (Y-m-d format)

        // Fetch records where the date part of 'created_at' matches today's date
        $details = TravelLocation::where('lc_emp_id', $user->emp_id)
            ->whereDate('created_at', $currentDate)
            ->get();

        if ($details->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(TravelLocationResource::collection($details));
        }

        return response()->json(['result' => [], 'status' => false]);
    }
}
