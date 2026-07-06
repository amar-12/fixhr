<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Attendance\AutomationRuleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AutomationRule;
use ChandraHemant\HtkcUtils\ReturnHelper;

class CheckPolicyController extends Controller
{
    public function checkPolicy(){
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $policy= AutomationRule::where('ar_b_id', $businessId)->where('ar_is_enabled',1)->get();

        // Check if the policy collection is not empty
        if ($policy->isNotEmpty()) {
            // Convert the collection to an array and print the actual values
            return ReturnHelper::jsonApiReturn(AutomationRuleResource::collection($policy));
        } else {
            return response()->json([
                'status' => false,
                'result' => [],
                'message' => 'No policies found'
            ]);
        }

    }
}
