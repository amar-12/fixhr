<?php

namespace App\Http\Controllers\Api\FixGpt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MonitoringMetaController extends Controller
{
    public function reportTypes()
    {
        return response()->json([
            "available_report_types" => [
                ["key" => "present"],
                ["key" => "absent"],
                ["key" => "late-coming"],
                ["key" => "early-going"],
                ["key" => "half-day"],
                ["key" => "holiday-present"]
            ]
        ]);
    }

    public function reportSchema()
    {
        return response()->json([
            "required_fields" => [
                "business_id",
                "reports[]"
            ],
            "reports_structure" => [
                "type" => "string (required)",
                "from_date" => "Y-m-d (required)",
                "to_date" => "Y-m-d (required)"
            ],
            "global_filters" => [
                "employee_id",
                "department_id",
                "designation_id",
                "branch_id",
                "dealer_id",
                "shift_id",
                "grade_id",
                "job_status_id",
                "work_mode_id"
            ]
        ]);
    }
}
