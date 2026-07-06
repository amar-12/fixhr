<?php

namespace App\Http\Controllers\Api\GptAI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterTable;
use App\Models\PolicyTadaTravelType;
use App\Models\TravelPurpose;

/**
 * TaDaMetaController
 *
 * Exposes every valid filter option so the AI / frontend always knows
 * what values it can pass to the TA/DA query endpoints.
 *
 * Routes (suggested):
 *   GET /api/gpt/tada/meta/filter-options   → filterOptions()
 *   GET /api/gpt/tada/meta/schema           → schema()
 */
class TaDaMetaController extends Controller
{
    /**
     * Return all valid filter values for every TA/DA endpoint.
     *
     * This is the single source of truth the AI assistant should call
     * before building any TA/DA query, so it never guesses IDs or names.
     */
    public function filterOptions(Request $request)
    {
        $user       = Auth::user();
        $businessId = $user->emp_b_id;

        // ---- Travel types ----
        $travelTypes = MasterTable::where('m_group', 'TRAVEL_TYPE')
            ->select('m_id', 'm_name')
            ->get()
            ->map(fn($r) => ['id' => $r->m_id, 'name' => strtolower($r->m_name), 'label' => $r->m_name]);

        // ---- Approval / claim statuses ----
        $approvalStatuses = MasterTable::where('m_group', 'APPROVAL_STATUS')
            ->select('m_id', 'm_name')
            ->get()
            ->map(fn($r) => ['id' => $r->m_id, 'name' => strtolower($r->m_name), 'label' => $r->m_name]);

        // ---- Expense types ----
        $expenseTypes = MasterTable::where('m_group', 'EXPENSE_TYPE')
            ->select('m_id', 'm_name')
            ->get()
            ->map(fn($r) => ['id' => $r->m_id, 'name' => $r->m_name]);

        // ---- Vehicle types ----
        $vehicleTypes = MasterTable::where('m_group', 'TRAVEL_VEHICLE')
            ->select('m_id', 'm_name')
            ->get()
            ->map(fn($r) => ['id' => $r->m_id, 'name' => $r->m_name]);

        // ---- Travel modes ----
        $travelModes = MasterTable::where('m_group', 'TRAVEL_MODE')
            ->select('m_id', 'm_name')
            ->get()
            ->map(fn($r) => ['id' => $r->m_id, 'name' => $r->m_name]);

        return response()->json([
            'status'  => true,
            'message' => 'Filter options retrieved successfully.',
            'filters' => [

                // ---- DATE FILTERS ----
                'time_filter' => [
                    'description' => 'Pre-built date range shortcuts. Ignored when from_date+to_date are provided.',
                    'options' => [
                        ['key' => 'weekly',         'label' => 'This Week'],
                        ['key' => 'monthly',        'label' => 'This Month (default)'],
                        ['key' => 'previous_month', 'label' => 'Previous Month'],
                        ['key' => 'quarterly',      'label' => 'This Quarter'],
                        ['key' => 'yearly',         'label' => 'This Year'],
                        ['key' => 'all_time',       'label' => 'All Time (no date restriction)'],
                    ],
                ],
                'custom_date_range' => [
                    'description' => 'Custom range (highest priority over time_filter).',
                    'fields' => [
                        'from_date' => 'Y-m-d',
                        'to_date'   => 'Y-m-d',
                    ],
                ],

                // ---- EMPLOYEE SCOPE ----
                'employee_scope' => [
                    'description' => 'Narrow results to specific employees or groups.',
                    'fields' => [
                        'emp_id'         => 'integer — exact employee',
                        'department_id'  => 'integer — emp_d_id',
                        'designation_id' => 'integer — emp_dg_id',
                        'branch_id'      => 'integer — emp_br_id',
                        'role_id'        => 'integer — emp_role_id',
                        'grade_id'       => 'integer — emp_grade_id',
                        'job_status_id'  => 'integer — emp_job_status',
                        'work_mode_id'   => 'integer — emp_work_mode_id',
                        'search'         => 'string — free-text search on emp_full_name or emp_code',
                    ],
                ],

                // ---- TRAVEL TYPE ----
                'travel_type' => [
                    'description' => 'Pass the "name" value (lowercase). Filters by policy travel type.',
                    'options'     => $travelTypes,
                ],

                // ---- STATUS ----
                'status' => [
                    'description' => 'Approval / claim status. Pass "name" (case-insensitive) or numeric "id".',
                    'options'     => $approvalStatuses,
                ],

                // ---- PAYMENT STATUS (claimList only) ----
                'payment_status' => [
                    'description' => 'Applies to claimList endpoint only.',
                    'options' => [
                        ['key' => 'paid',            'label' => 'Paid'],
                        ['key' => 'unpaid',          'label' => 'Unpaid'],
                        ['key' => 'partially_paid',  'label' => 'Partially Paid'],
                    ],
                ],

                // ---- DEDUCTION STATUS (claimList only) ----
                'deduction_status' => [
                    'description' => 'Applies to claimList endpoint only. 0 = no deduction, 1 = deducted.',
                    'options' => [
                        ['key' => 0, 'label' => 'No Deduction'],
                        ['key' => 1, 'label' => 'Deducted'],
                    ],
                ],

                // ---- AMOUNT RANGE ----
                'amount_range' => [
                    'description' => 'Filter by claim/expense amount.',
                    'fields' => [
                        'amount_min' => 'numeric',
                        'amount_max' => 'numeric',
                    ],
                ],

                // ---- EXPENSE TYPE (expenseCategoryList only) ----
                'expense_type_id' => [
                    'description' => 'Filter expense totals to a single expense type (expenseCategoryList only).',
                    'options'     => $expenseTypes,
                ],

                // ---- VEHICLE TYPE (expenseCategoryList & distanceKmBasedTravel) ----
                'vehicle_id' => [
                    'description' => 'Filter by vehicle / transport type.',
                    'options'     => $vehicleTypes,
                ],

                // ---- TRAVEL MODE (travelDetailsShow only) ----
                'mode_id' => [
                    'description' => 'Filter by travel mode (travelDetailsShow only).',
                    'options'     => $travelModes,
                ],

                // ---- DISTANCE RANGE (distanceKmBasedTravel only) ----
                'distance_range' => [
                    'description' => 'Filter by total km distance (distanceKmBasedTravel only).',
                    'fields' => [
                        'distance_min' => 'numeric (km)',
                        'distance_max' => 'numeric (km)',
                    ],
                ],

                // ---- SORT ----
                'sort' => [
                    'description' => 'Sorting options (per endpoint).',
                    'sort_order'  => ['asc', 'desc'],
                    'sort_by_per_endpoint' => [
                        'individualTadaQueries' => ['created_at', 'tc_amount', 'tc_deduction_amount', 'tc_claimed_amount'],
                        'travelDetails'         => ['trp_id', 'created_at', 'trp_start_date', 'trp_end_date'],
                        'travelDetailsShow'     => ['trp_id', 'created_at', 'trp_start_date', 'trp_end_date'],
                        'claimList'             => ['tc_id', 'created_at', 'tc_amount', 'tc_deduction_amount', 'tc_claimed_amount'],
                        'distanceKmBasedTravel' => ['te_date', 'trd_start_date'],
                    ],
                ],

                // ---- PAGINATION ----
                'pagination' => [
                    'description' => 'Omit or set per_page=0 to return all records.',
                    'fields' => [
                        'per_page' => 'integer (default: 0 = no pagination)',
                        'page'     => 'integer (default: 1)',
                    ],
                ],

                // ---- UNIQUE ID SEARCH (claimList only) ----
                'unique_id' => [
                    'description' => 'Partial match on tc_unique_id (claimList only).',
                    'type'        => 'string',
                ],
            ],
        ]);
    }

    /**
     * Return the full schema of every TA/DA endpoint —
     * URL, method, accepted params, and their types.
     */
    public function schema()
    {
        $endpoints = [
            [
                'name'        => 'Individual TA/DA Queries',
                'url'         => '/api/gpt/tada/individual-queries',
                'method'      => 'GET',
                'description' => 'Aggregate claim stats grouped by employee with expense breakdown.',
                'params'      => $this->commonParams(['amount_min','amount_max','sort_by','sort_order','per_page','page']),
            ],
            [
                'name'        => 'Travel Details',
                'url'         => '/api/gpt/tada/travel-details',
                'method'      => 'GET',
                'description' => 'Rich list of travel request plans with all relations and aggregate counts.',
                'params'      => $this->commonParams(['travel_purpose_id','sort_by','sort_order','per_page','page']),
            ],
            [
                'name'        => 'Travel Details Show (self/user view)',
                'url'         => '/api/gpt/tada/travel-details-show',
                'method'      => 'GET',
                'description' => 'Individual/self travel request list with mode and purpose filters.',
                'params'      => $this->commonParams(['travel_purpose_id','mode_id','sort_by','sort_order','per_page','page']),
            ],
            [
                'name'        => 'Claim List',
                'url'         => '/api/gpt/tada/claims',
                'method'      => 'GET | POST (JSON body also accepted)',
                'description' => 'Paginated claim list with payment, deduction, amount-range and unique-id filters.',
                'params'      => $this->commonParams([
                    'payment_status','deduction_status','amount_min','amount_max',
                    'unique_id','sort_by','sort_order','per_page','page',
                ]),
            ],
            [
                'name'        => 'Expense Category List',
                'url'         => '/api/gpt/tada/expense-categories',
                'method'      => 'GET',
                'description' => 'Expense totals broken down by category and vehicle, plus DA/TA eligibility.',
                'params'      => $this->commonParams(['expense_type_id','vehicle_id']),
            ],
            [
                'name'        => 'Distance / KM Based Travel',
                'url'         => '/api/gpt/tada/distance-km',
                'method'      => 'GET',
                'description' => 'Total KM driven with vehicle and distance-range filters.',
                'params'      => $this->commonParams(['vehicle_id','distance_min','distance_max','sort_order','per_page','page']),
            ],
        ];

        return response()->json([
            'status'      => true,
            'message'     => 'TA/DA endpoint schema retrieved successfully.',
            'meta_url'    => url('/api/gpt/tada/meta/filter-options'),
            'endpoints'   => $endpoints,
        ]);
    }

    /** Base params shared across all endpoints, merged with extras. */
    private function commonParams(array $extras = []): array
    {
        $base = [
            'from_date'      => 'date (Y-m-d) — custom range start',
            'to_date'        => 'date (Y-m-d) — custom range end',
            'time_filter'    => 'string — weekly|monthly|previous_month|quarterly|yearly|all_time',
            'status'         => 'string|integer — approval/claim status name or id',
            'travel_type'    => 'string — local|outstation|international',
            'emp_id'         => 'integer — filter to one employee',
            'department_id'  => 'integer',
            'designation_id' => 'integer',
            'branch_id'      => 'integer',
            'role_id'        => 'integer',
            'grade_id'       => 'integer',
            'job_status_id'  => 'integer',
            'work_mode_id'   => 'integer',
            'search'         => 'string — name / code free-text search',
        ];

        foreach ($extras as $key) {
            $base[$key] = match($key) {
                'amount_min', 'amount_max'       => 'numeric',
                'distance_min', 'distance_max'   => 'numeric (km)',
                'sort_by'                        => 'string (see filter-options for valid columns per endpoint)',
                'sort_order'                     => 'string — asc|desc',
                'per_page'                       => 'integer (0 = no pagination)',
                'page'                           => 'integer',
                'payment_status'                 => 'string — paid|unpaid|partially_paid',
                'deduction_status'               => 'integer — 0|1',
                'unique_id'                      => 'string — partial match on tc_unique_id',
                'expense_type_id'                => 'integer — m_id from EXPENSE_TYPE master',
                'vehicle_id'                     => 'integer — m_id from TRAVEL_VEHICLE master',
                'mode_id'                        => 'integer — m_id from TRAVEL_MODE master',
                'travel_purpose_id'              => 'integer — tp_id from travel_purposes table',
                default                          => 'mixed',
            };
        }

        return $base;
    }
}