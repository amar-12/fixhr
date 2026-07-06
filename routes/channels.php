<?php

use App\Helpers\ApprovalHelper;
use App\Models\TadaClaim;
use App\Models\TadaRequestPlan;
use Illuminate\Support\Facades\Broadcast;
use App\Models\Employee;

Broadcast::channel('App.Models.Employee.{emp_id}', function (Employee $user, $emp_id) {
    return (int) $user->emp_id === (int) $emp_id;
});


Broadcast::channel('travel.{trpId}', function (Employee $user, int $trpId) {
    return $user->emp_id === TadaRequestPlan::find($trpId)->trp_emp_id;
});

Broadcast::channel('travel-approval.{request_id}', function () {
    return true;
});

Broadcast::channel('claim-approval.{request_id}', function (Employee $user, int $request_id) {
    return true;
});

Broadcast::channel('travel-approval.{travel_request_id}', function ($user, $travel_request_id) {
    // You can add logic here to check if the user can listen to this channel
    return true; // Allow all authenticated users (customize as needed)
});
