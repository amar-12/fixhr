<?php

namespace App\Http\Controllers\Api\FixGpt;

use App\Http\Controllers\Controller;

class LeaveMetaController extends Controller
{
    public function meta()
    {
        return response()->json([
            "report_types" => [
                "summary",
                "details",
                "balance"
            ],
            "filters" => [
                "business_ids" => "array",
                "employee_ids" => "array",
                "department_ids" => "array",
                "designation_ids" => "array",
                "dealer_ids" => "array",
                "leave_type_ids" => "array",
                "leave_segment_ids" => "array",
                "leave_category_ids" => "array",
                "approval_status_ids" => "array",
                "employee_status_ids" => "array"
            ],
            "date_filters" => [
                "from_date" => "required",
                "to_date" => "required"
            ]
        ]);
    }
}
