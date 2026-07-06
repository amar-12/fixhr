<?php

namespace App\Http\Controllers\Api\FixGpt;

use App\Http\Controllers\Controller;

class EmployeeMetaController extends Controller
{
    public function meta()
    {
        return response()->json([

            "status" => true,

            "entity" => "employee",

            /*
            |----------------------------------------
            | SECTIONS
            |----------------------------------------
            */
            "sections" => [
                "about_details",
                "contact_details",
                "personal_details",
                "identity_details",
                "organization_details",
                "joining_details",
                "separation_details",
                "attendance_details",
                "bank_details",
                "pf_esic_details",
                "salary_details"
            ],

            /*
            |----------------------------------------
            | FILTERS
            |----------------------------------------
            */
            "filters" => [
                "business_ids",
                "employee_ids",
                "department_ids",
                "designation_ids",
                "branch_ids",
                "role_ids",
                "grade_ids",
                "shift_ids",
                "work_mode_ids",
                "job_status_ids",
                "employee_type_ids",
                "reporting_manager_ids",
                "payment_methods",
                "emp_full_name",
                "status"
            ]

        ]);
    }
}