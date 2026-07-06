<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GatePassConfirmation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GatePassConfirmationApiController extends Controller
{
    /**
     * Confirm gatepass for an employee
     */
    public function confirmGatepass(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'emp_id' => 'required|exists:employees,emp_id',
                'gcp_gtp_id' => 'required|string',
                'date' => 'required|string',
                'out_time' => 'nullable|string',
                'in_time' => 'nullable|string',
                'gatepass_type' => 'nullable|in:out_gatepass,in_gatepass',
                'out_confirmation' => 'boolean',
                'in_confirmation' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Confirmation failed',
                    // 'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Set confirmation status based on gatepass_type
            $outConfirmation = $request->out_confirmation ?? false;
            $inConfirmation = $request->in_confirmation ?? false;

            if ($request->gatepass_type) {
                if ($request->gatepass_type === 'out_gatepass') {
                    $outConfirmation = true;
                } elseif ($request->gatepass_type === 'in_gatepass') {
                    $inConfirmation = true;
                }
            }
            $user = Auth::user();
            // Check if record exists for this employee and gatepass id
            $gatePass = GatePassConfirmation::where('gcp_emp_id', $request->emp_id)
            ->where('gcp_gtp_id', $request->gcp_gtp_id)
            ->where('gcp_emp_b_id', $user->emp_b_id)
            ->first();

            if (!$gatePass) {
                // Create new record - First entry (usually in_time)
              
                $createData = [
                    'gcp_emp_id' => $request->emp_id,
                    'gcp_emp_b_id' => $user->emp_b_id, // Default business ID if not provided
                    'gcp_gtp_id' => $request->gcp_gtp_id,
                    'gcp_date' => $request->date,
                    'gcp_out_time_confirmation' => $outConfirmation,
                    'gcp_in_time_confirmation' => $inConfirmation
                ];

                // Set times based on what's provided
                if ($request->in_time) {
                    $createData['gcp_in_time'] = $request->in_time;
                }
                if ($request->out_time) {
                    $createData['gcp_out_time'] = $request->out_time;
                }

                $gatePass = GatePassConfirmation::create($createData);
                
                $message = 'Gatepass entry created successfully';
                
            } else {
                // Update existing record - Second entry (usually out_time)
                $updateData = [];
                
                // Update times only if provided AND not already set
                if ($request->in_time && !$gatePass->gcp_in_time) {
                    $updateData['gcp_in_time'] = $request->in_time;
                }
                if ($request->out_time && !$gatePass->gcp_out_time) {
                    $updateData['gcp_out_time'] = $request->out_time;
                }
                
                // Only update confirmation status if gatepass_type is provided
                if ($request->gatepass_type) {
                    if ($request->gatepass_type === 'out_gatepass') {
                        $updateData['gcp_out_time_confirmation'] = true;
                    } elseif ($request->gatepass_type === 'in_gatepass') {
                        $updateData['gcp_in_time_confirmation'] = true;
                    }
                }
                
                if (!empty($updateData)) {
                    $gatePass->update($updateData);
                }
                
                $message = 'Gatepass entry updated successfully';
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $message,
                // 'data' => $gatePass->load('employee')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  
}
