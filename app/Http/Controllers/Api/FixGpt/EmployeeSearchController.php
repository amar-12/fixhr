<?php

namespace App\Http\Controllers\Api\FixGpt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\MasterTable;

class EmployeeSearchController extends Controller
{
    public function search(Request $request)
    {
        $filters = $request->input('filters', []);
        $include = $request->input('include', []);

        $query = Employee::with([
            'fh_department',
            'fh_designation',
            'fh_branch',
            'fh_role',
            'fh_grade',
            'fh_shift_type',
            'fh_employee_type',
            'fh_work_mode',
            'fh_employee_salary',
            'fh_gender',
            'fh_reporting_manager_id' // ✅ fixed relation
        ]);

        /*
        |--------------------------------------------------
        | APPLY FILTERS
        |--------------------------------------------------
        */

        $this->applyFilter($query, 'emp_b_id', $filters['business_ids'] ?? null);
        $this->applyFilter($query, 'emp_id', $filters['employee_ids'] ?? null);
        $this->applyFilter($query, 'emp_d_id', $filters['department_ids'] ?? null);
        $this->applyFilter($query, 'emp_dg_id', $filters['designation_ids'] ?? null);
        $this->applyFilter($query, 'emp_br_id', $filters['branch_ids'] ?? null);
        $this->applyFilter($query, 'emp_grade_id', $filters['grade_ids'] ?? null);
        $this->applyFilter($query, 'emp_role_id', $filters['role_ids'] ?? null);
        $this->applyFilter($query, 'emp_supervisor_id', $filters['reporting_manager_ids'] ?? null);


        /*
        |--------------------------------------------------
        | SEARCH (EMPLOYEE + REPORTING MANAGER)
        |--------------------------------------------------
        */

        $status = $filters['status'] ?? null;


        if (!empty($status)) {


            if ($status == 'active') {

                $query->where('emp_status', 71);
            } elseif ($status == 'inactive') {
                $query->where('emp_status', 72);
            } elseif ($status == 'resigned') {
                $query->whereNotNull('emp_last_working_date');
            } else {
                // if user sends direct ID (70 / 71)
                $query->where('emp_status', $status);
            }
        }

        $search = $filters['emp_full_name'] ?? null;
        if (!empty($search)) {

            $keywords = explode(' ', $search);

            $query->where(function ($q) use ($keywords) {

                foreach ($keywords as $word) {

                    $q->where(function ($qq) use ($word) {

                        // Employee search
                        $qq->where('emp_full_name', 'LIKE', "%{$word}%")
                            ->orWhere('emp_fname', 'LIKE', "%{$word}%")
                            ->orWhere('emp_lname', 'LIKE', "%{$word}%");

                        // Reporting Manager search
                        $qq->orWhereHas('fh_reporting_manager_id', function ($q2) use ($word) {
                            $q2->where('emp_full_name', 'LIKE', "%{$word}%");
                        });
                    });
                }
            });
        }

        /*
        |--------------------------------------------------
        | GET DATA
        |--------------------------------------------------
        */

        $employees = $query->get();

        if ($employees->isEmpty()) {
            return response()->json([
                "status" => false,
                "message" => "No employees found"
            ]);
        }

        /*
        |--------------------------------------------------
        | FORMAT RESPONSE
        |--------------------------------------------------
        */

        $data = $employees->map(function ($emp) {

            $checkInMethods = $emp->emp_checkin_method_id ? MasterTable::whereIn('m_id', $emp->emp_checkin_method_id)->pluck('m_name', 'm_id') : collect();

            return [

                "emp_id" => $emp->emp_id,
                "emp_code" => $emp->emp_code,
                "emp_full_name" => $emp->emp_full_name,
                "emp_gender" => $emp->fh_gender?->m_name,
                "emp_checkin_method_id" => $checkInMethods,
                "business_id" => $emp->emp_b_id,
                "status" => match (true) {
                    !empty($emp->emp_last_working_date) => 'resigned',
                    $emp->emp_status == 71 => 'active',
                    $emp->emp_status == 70 => 'inactive',
                    default => 'unknown'
                },
                // ✅ Reporting Manager
                "reporting_manager" => $emp->fh_reporting_manager_id?->emp_full_name,

                /*
                | ABOUT DETAILS
                */
                "about_details" => [
                    "dob" => $emp->emp_dob,
                    "nationality" => $emp->emp_nationality,
                    "religion" => $emp->emp_religion
                ],

                /*
                | CONTACT DETAILS
                */
                "contact_details" => [
                    "phone" => $emp->emp_phone,
                    "email" => $emp->emp_email,
                    "company_email" => $emp->emp_company_email
                ],

                /*
                | PERSONAL DETAILS
                */
                "personal_details" => [
                    "permanent_address" => $emp->emp_permanent_address,
                    "temporary_address" => $emp->emp_temporary_address
                ],

                /*
                | ORGANIZATION DETAILS
                */
                "organization_details" => [
                    "department" => $emp->fh_department?->d_name,
                    "designation" => $emp->fh_designation?->dg_name,
                    "branch" => $emp->fh_branch?->br_name,
                    "role" => $emp->fh_role?->role_name,
                    "grade" => $emp->fh_grade?->g_name,
                    "reporting_manager" => $emp->fh_reporting_manager_id?->emp_full_name
                ],

                /*
                | JOINING DETAILS
                */
                "joining_details" => [
                    "date_of_joining" => $emp->emp_date_of_joining,
                    "confirmation_date" => $emp->emp_date_of_confirmation,
                    "probation_period" => $emp->emp_probation_period,
                    "year_of_service" => $emp->emp_year_of_service
                ],

                /*
                | BANK DETAILS
                */
                "bank_details" => [
                    "bank" => $emp->emp_bank_name,
                    "account" => $emp->emp_bank_account_no,
                    "ifsc" => $emp->emp_bank_ifsc_code
                ],

                /*
                | SALARY DETAILS
                */
                "salary_details" => [
                    "monthly_ctc" => $emp->fh_employee_salary?->es_monthly_ctc,
                    "annual_ctc" => $emp->fh_employee_salary?->es_annual_ctc
                ],

                /*
                | SYSTEM DETAILS
                */
                "system" => [
                    "created_at" => $emp->created_at,
                    "updated_at" => $emp->updated_at
                ]

            ];
        });

        return response()->json([
            "status" => true,
            "total_records" => $data->count(),
            "data" => $data
        ]);
    }

    /*
    |--------------------------------------------------
    | COMMON FILTER FUNCTION
    |--------------------------------------------------
    */

    private function applyFilter($query, $column, $values)
    {
        if (empty($values)) return;

        if (is_array($values)) {
            $query->whereIn($column, $values);
        } else {
            $query->where($column, $values);
        }
    }
}
