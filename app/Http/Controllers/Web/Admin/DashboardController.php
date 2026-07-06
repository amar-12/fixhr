<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\ApprovalHelper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelType;
use App\Models\TadaClaim;
use App\Models\TadaMetroCity;
use App\Models\TadaReimburse;
use App\Models\TadaRequestPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use NumberToWords\NumberToWords;

class DashboardController extends Controller
{
    public function index(Request $request)
    {

        // dd($request->all());
        $user = Auth::user();
        $isSuper = $user->emp_role_id;
        $employeeFilter = request()->input('travel_employeeFilter');
        $department = request()->input('travel_department');
        $designation = request()->input('travel_designation');
        $statusFilter = request()->input('travel_statusFilter');
        $fromToDateFilter = request()->input('fromDate');

        if ($request->ajax()) {

            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['tc_b_id', $user->emp_b_id],
                ],
                [
                    'method' => 'where',
                    'args' => ['tc_status', '!=', 192],
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'tc_id',
                        'tc_unique_id',
                        'tc_b_id',
                        'tc_emp_id',
                        'tc_trp_id',
                        'tc_am_id',
                        'tc_module_id',
                        'tc_amount',
                        'tc_claimed_amount',
                        'tc_deduction_amount',
                        'tc_status',
                        'tc_stage_completed',
                        'tc_next_approver',
                        'tc_payment_date',
                        'created_at',
                    ],
                    'relation' => [
                        'fh_employee:emp_id,emp_code,emp_full_name,emp_d_id,emp_dg_id',
                        'fh_employee.fh_department:d_id,d_name',
                        'fh_employee.fh_designation:dg_id,dg_name',
                        'fh_claim_status:m_id,m_name,m_other',
                        'fh_tada_request_plan:trp_id,trp_unique_id,trp_name',
                    ],
                ],
            ];

            // Filters
            if (! empty($employeeFilter)) {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['tc_emp_id', $employeeFilter],
                ];
            }

            // Filter by designation
            if (! empty($designation)) {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'relation' => 'fh_employee.fh_designation',
                    'args' => ['dg_id', $designation], // dg_id corresponds to designation
                ];
            }

            // Filter by department
            if (! empty($department)) {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'relation' => 'fh_employee.fh_department',
                    'args' => ['d_id', $department], // d_id corresponds to department
                ];
            }

            if (! empty($statusFilter)) {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['tc_status', $statusFilter],
                ];
            }

            if (! empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);
                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
                    $endDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();

                    $dynamicConditions[] = ['method' => 'where', 'args' => ['created_at', '>=', $startDate]];
                    $dynamicConditions[] = ['method' => 'where', 'args' => ['created_at', '<=', $endDate]];
                }
            }

            $searchColumns = [
                'tc_unique_id',
                'tc_id',
                'tc_amount',
                'tc_claimed_amount',
                'tc_status',
                'created_at',
            ];

            $searchRelationships = [
                'fh_employee' => ['emp_full_name', 'emp_code'],
                'fh_employee.fh_designation' => ['dg_name'],
                'fh_claim_status' => ['m_name'],
                'fh_tada_request_plan' => ['trp_unique_id', 'trp_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaClaim,
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            // dd($list);

            $rowData = [];
            $i = 0;
            foreach ($list as $claim) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $claim->fh_employee->emp_code;
                $row[] = $claim->fh_employee->emp_full_name;
                $row[] = optional($claim->fh_employee->fh_designation)->dg_name;
                $row[] = optional($claim->fh_employee->fh_department)->d_name;
                $row[] = $claim->tc_unique_id;
                // $row[] = optional($claim->fh_tada_request_plan)->trp_unique_id;
                $row[] = $claim->tc_amount;
                $row[] = $claim->tc_claimed_amount;
                $row[] = $claim->tc_deduction_amount ?? 0;
                $row[] = \Carbon\Carbon::parse($claim->created_at)->format('d-M-Y H:i');

                // Status Badge
                $json = optional($claim->fh_claim_status)->m_other;
                $decoded = json_decode($json, true);
                $color = $decoded['color'] ?? '#999';
                $icon = $decoded['web_icon'] ?? 'feather-info';

                $row[] = '<span class="badge" style="background-color:' . $color . '">
                    <i class="' . $icon . '"></i> ' . optional($claim->fh_claim_status)->m_name . '
                  </span>';
                $rowData[] = $row;
            }

            $output = [
                'draw' => $request->input('draw'),
                'recordsTotal' => count($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaClaim,
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            ];

            return response()->json($output);
        }

        // 1. Average Net payable By travel Type And Vehicle 
        $dateRange = request()->input('dateRange');
        $startDate = $endDate = null;

        if ($dateRange) {
            [$start, $end] = explode(' - ', $dateRange);
            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->endOfDay();
        }

        $currentStart = $startDate ?? Carbon::now()->startOfMonth();
        $currentEnd = $endDate ?? Carbon::now()->endOfMonth();

        $business_id = auth()->user()->emp_b_id;

        // Fetch claims with related data
        $claims = TadaClaim::with([
            'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type',
            'fh_tada_request_plan.fh_tada_request_details.fh_policy_tada_travel_vehicle.fh_vehicle'
        ])
            ->where('tc_b_id', $business_id)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->get();

        // Vehicle name mapping for consistency
        $vehicleMapping = [
            'Car' => 'Car',
            'Bike' => 'Bike',
            'Auto Rickshaw' => 'Auto',
            'Cab/Taxi' => 'Cab',
            'Bus' => 'Bus',
            'Train' => 'Train',
            'Flight' => 'Flight',
            'Metro' => 'Metro',
            'Bike-Taxi Service' => 'Bike-Taxi'
        ];

        // Map all claims and flatten multiple request details
        $mapped = $claims->flatMap(function ($claim) use ($vehicleMapping) {
            $travelType = $claim->fh_tada_request_plan?->fh_policy_tada_travel_type?->fh_travel_type?->m_name ?? 'N/A';

            return $claim->fh_tada_request_plan?->fh_tada_request_details->map(function ($detail) use ($claim, $travelType, $vehicleMapping) {
                $vehicleName = $detail?->fh_policy_tada_travel_vehicle?->fh_vehicle?->m_name ?? 'N/A';
                return [
                    'travelType' => $travelType,
                    'vehicle' => $vehicleMapping[$vehicleName] ?? 'N/A',
                    'amount' => $claim->tc_payed_amount ?? 0,
                    'paid_amount' => $claim->tc_amount ?? 0
                ];
            }) ?? collect();
        });


        $travelTypes = $mapped->pluck('travelType')->unique()->values()->toArray();
        $allVehicles = $mapped->pluck('vehicle')->unique()->values()->toArray();

        // Ensure 'N/A' is included in vehicle list and vehicle colors
        if (!in_array('N/A', $allVehicles)) {
            $allVehicles[] = 'N/A';
        }

        $vehicleColors['N/A'] = 'rgba(200,200,200,0.6)'; // default color


        // Group amounts by travel type and vehicle (both claimed and paid)
        $grouped = [];
        foreach ($travelTypes as $travel) {
            foreach ($allVehicles as $vehicle) {
                $totalClaimed = $mapped->where('travelType', $travel)
                    ->where('vehicle', $vehicle)
                    ->sum('amount');

                $totalPaid = $mapped->where('travelType', $travel)
                    ->where('vehicle', $vehicle)
                    ->sum('paid_amount');

                $grouped[$travel][$vehicle] = [
                    'claimed' => $totalPaid,
                    'paid' => $totalClaimed
                ];
            }
        }

        // Vehicle colors
        $vehicleColors = [
            'Bike'  => 'rgba(255, 159, 64, 0.8)',
            'Car'   => 'rgba(153, 102, 255, 0.8)',
            'Bus'   => 'rgba(144, 238, 144, 0.8)',
            'Train' => 'rgba(255, 206, 86, 0.8)',
            'Cab'   => 'rgba(75, 192, 192, 0.8)',
            'Auto'  => 'rgba(173, 216, 230, 0.8)',
            'Flight' => 'rgba(255,99,132,0.8)',
            'Metro' => 'rgba(54,162,235,0.8)',
            'Bike-Taxi' => 'rgba(255, 206, 86,0.8)',
            'N/A' => 'rgba(150,150,150,0.4)'
        ];

        $datasets = collect($allVehicles)->flatMap(function ($vehicle) use ($grouped, $travelTypes, $vehicleColors) {
            return [
                [
                    'label' => $vehicle . ' (Claimed)',
                    'data' => collect($travelTypes)->map(
                        fn($type) => $grouped[$type][$vehicle]['claimed'] ?? 0
                    )->toArray(),
                    'backgroundColor' => $vehicleColors[$vehicle] ?? 'rgba(200,200,200,0.6)'
                ],
                [
                    'label' => $vehicle . ' (Paid)',
                    'data' => collect($travelTypes)->map(
                        fn($type) => $grouped[$type][$vehicle]['paid'] ?? 0
                    )->toArray(),
                    'backgroundColor' => $vehicleColors[$vehicle] ? str_replace('0.8', '0.4', $vehicleColors[$vehicle]) : 'rgba(150,150,150,0.4)'
                ]
            ];
        })->toArray();



        // 2. Claimed vs Net Payable By Grade
        $gradeWiseData = TadaClaim::with('fh_employee.fh_grade')
            ->where('tc_b_id', $business_id)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->get()
            ->groupBy(function ($claim) {
                return $claim->fh_employee->fh_grade->g_name ?? 'No Grade';
            })
            ->map(function ($claims, $gradeName) {
                return [
                    'claimed' => $claims->sum('tc_claimed_amount'),
                    'payable' => $claims->sum('tc_payed_amount')
                ];
            })
            ->sortKeys(); // <--- This will sort by grade name ascending

        // Prepare data for Chart.js
        $grades = $gradeWiseData->keys(); // Sorted grade names
        $claimedAmounts = $gradeWiseData->pluck('claimed');
        $payableAmounts = $gradeWiseData->pluck('payable');


        // 3. Top 10 Employees By Net Payable 
        $topClaims = TadaClaim::select('tc_emp_id', DB::raw('SUM(tc_payed_amount) as total_payed'))
            ->with('fh_employee:emp_id,emp_full_name')
            ->where('tc_b_id', $business_id)
            ->where('tc_status', '!=', 192)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->groupBy('tc_emp_id')
            ->havingRaw('SUM(tc_payed_amount) > 0')
            ->orderByDesc('total_payed')
            ->limit(10)
            ->get();

        // Prepare labels and data
        $topemployee = $topClaims->map(fn($c) => $c->fh_employee->emp_full_name ?? 'Unknown')->toArray();
        $emp_amounts = $topClaims->pluck('total_payed')->toArray();



        // 4. Monthly Claims Summary 
        // $monthlyClaims = TadaClaim::where('tc_b_id', $business_id)
        //     ->where('tc_status', 200)
        //     ->whereBetween('created_at', [$currentStart, $currentEnd])
        //     // ->where('tc_next_approver', 1)
        //     // ->where('tc_stage_completed', 1)
        //     ->selectRaw("
        //             DATE_FORMAT(created_at, '%Y-%m') as month,
        //             COUNT(tc_id) as claim_count,
        //             SUM(tc_claimed_amount) as total_claimed,
        //             SUM(IFNULL(tc_payed_amount,0)) as total_paid,
        //             IFNULL(
        //                 (
        //                     (SUM(tc_claimed_amount) - SUM(IFNULL(tc_payed_amount,0))) 
        //                     / NULLIF(SUM(tc_claimed_amount), 0)
        //                 ) * 100
        //             ,0) as percent_difference
        //         ")
        //     ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
        //     ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
        //     ->get();


        $monthlyClaims = TadaClaim::where('tc_b_id', $business_id)
            // ->where('tc_status', 200)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            // ->where('tc_next_approver', 1)
            // ->where('tc_stage_completed', 1)
            ->selectRaw("
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(tc_id) as claim_count,
                    SUM(tc_claimed_amount) as total_claimed,
                    SUM(IFNULL(tc_claimed_amount,0) - IFNULL(tc_deduction_amount,0)) as total_paid,
                    ROUND((SUM(IFNULL(tc_deduction_amount,0)) / SUM(tc_claimed_amount)) * 100,2) as percent_difference
                ")
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get();




        // 5. Top Expense Categories By Amount 
        $claims = TadaClaim::with('fh_tada_request_plan.fh_tada_expenses.fh_expense_type')
            ->where('tc_b_id', $business_id)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->get();

        $expenseSummary = [];
        foreach ($claims as $claim) {
            $requestPlan = $claim->fh_tada_request_plan;
            if (!$requestPlan) continue;

            foreach ($requestPlan->fh_tada_expenses as $expense) {
                $typeName = $expense->fh_expense_type?->m_name ?? 'Unknown';

                if (!isset($expenseSummary[$typeName])) {
                    $expenseSummary[$typeName] = [
                        'total_amount' => 0,
                        'total_claims' => 0,
                    ];
                }

                $expenseSummary[$typeName]['total_amount'] += $expense->te_amount; // assuming te_amount is your field
                $expenseSummary[$typeName]['total_claims'] += 1;
            }
        }


        // 6. High Gap And Large Claims
        // $highclaimsdata = TadaClaim::with([
        //     'fh_employee:emp_id,emp_full_name',
        //     'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
        //     'fh_tada_request_plan.fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name'
        // ])
        //     ->where('tc_b_id', $business_id)
        //     ->where('tc_status', '!=', 192)
        //     // ->where('tc_next_approver', 1)
        //     // ->where('tc_stage_completed', 1)
        //     ->whereBetween('created_at', [$currentStart, $currentEnd])
        //     ->where('tc_deduction_amount', '>', 0)
        //     ->orderByDesc('tc_deduction_amount')
        //     // ->limit(10)
        //     ->get()
        //     ->map(function ($claim) {
        //         return [
        //             'claim_id' => $claim->tc_unique_id,
        //             'employee_name' => $claim->fh_employee->emp_full_name ?? '-',
        //             'travel_type' => $claim->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name ?? '-',
        //             'vehicle_type' => $claim->fh_tada_request_plan->fh_policy_tada_travel_mode->fh_travel_mode->m_name ?? '-',
        //             'claimed_amount' => $claim->tc_claimed_amount ?? 0,
        //             'net_payable_amount' => $claim->tc_payed_amount ?? 0,
        //             'absolute_gap' => $claim->tc_deduction_amount ?? 0,
        //         ];
        //     });


        $highclaimsdata = TadaClaim::with([
            'fh_employee:emp_id,emp_full_name',
            'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
            'fh_tada_request_plan.fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name'
        ])
            ->where('tc_b_id', $business_id)
            ->where('tc_status', '!=', 192)
            // ->where('tc_next_approver', 1)
            // ->where('tc_stage_completed', 1)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->where('tc_deduction_amount', '>', 0)
            ->orderByDesc('tc_deduction_amount')
            // ->limit(10)
            ->get()
            ->map(function ($claim) {
                return [
                    'claim_id' => $claim->tc_unique_id,
                    'employee_name' => $claim->fh_employee->emp_full_name ?? '-',
                    'travel_type' => $claim->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name ?? '-',
                    'vehicle_type' => $claim->fh_tada_request_plan->fh_policy_tada_travel_mode->fh_travel_mode->m_name ?? '-',
                    'claimed_amount' => $claim->tc_claimed_amount ?? 0,
                    // 'net_payable_amount' => $claim->tc_claimed_amount - $claim->tc_deduction_amount ?? 0,
                    'net_payable_amount' => (($claim->tc_claimed_amount ?? 0) - ($claim->tc_deduction_amount ?? 0)),
                    'absolute_gap' => $claim->tc_deduction_amount ?? 0,
                ];
            });



        //  Local vs Outstation vs International
        $travel_types = PolicyTadaTravelType::with('fh_travel_type')
            ->where('pttt_b_id', $business_id)
            ->where('pttt_status', 1)
            ->get();


        $ptttIds = $travel_types->pluck('pttt_id')->filter()->all();

        $baseQuery = TadaClaim::with('fh_tada_request_plan.fh_policy_tada_travel_type')
            ->where('tc_b_id', $business_id)

            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })

            ->whereHas('fh_tada_request_plan', function ($q) use ($business_id, $ptttIds) {
                $q->where('trp_b_id', $business_id)
                    ->when($ptttIds, function ($q) use ($ptttIds) {
                        $q->whereIn('trp_pttt_id', $ptttIds);
                    });
            });

        $filter = $request->get('filter', '1year');
        $filterStartDate = match ($filter) {
            '1week' => Carbon::now()->subWeek(),
            '1month' => Carbon::now()->subMonth(),
            '1year' => Carbon::now()->subYear(),
            'all' => Carbon::create(2000, 1, 1),
            default => Carbon::now()->subYear(),
        };

        $tripsQuery = (clone $baseQuery)->where('created_at', '>=', $filterStartDate);

        $labels = match ($filter) {
            '1week' => collect(range(0, 6))->map(fn($d) => Carbon::now()->subDays(6 - $d)->format('d M')),
            '1month' => collect(range(0, 29))->map(fn($d) => Carbon::now()->subDays(29 - $d)->format('d M')),
            '1year' => collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->format('M')),
            default => $tripsQuery->selectRaw('DISTINCT DATE(created_at) as date')->orderBy('date')->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d M')),
        };


        $getCounts = function ($typeId) use ($tripsQuery, $filter) {
            $query = (clone $tripsQuery)
                ->when($typeId, fn($q) => $q->whereHas('fh_tada_request_plan.fh_policy_tada_travel_type', fn($q2) => $q2->where('pttt_type_id', $typeId)));

            if ($filter == '1year') {
                return $query->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
                    ->groupBy('month')
                    ->get()
                    ->mapWithKeys(fn($item) => [Carbon::create()->month($item->month)->format('M') => $item->total]);
            }

            return $query->selectRaw('COUNT(*) as total, DATE(created_at) as date')
                ->groupBy('date')
                ->get()
                ->pluck('total', 'date');
        };



        $localData = $getCounts(124);
        $outData = $getCounts(125);
        $interData = $getCounts(126);

        $local = $labels->map(fn($l) => $localData[$l] ?? 0);
        $outstation = $labels->map(fn($l) => $outData[$l] ?? 0);
        $international = $labels->map(fn($l) => $interData[$l] ?? 0);

        $lineChartData = [
            'labels' => $labels,
            'local' => $local,
            'outstation' => $outstation,
            'international' => $international,
        ];

        $counts = [
            'local' => $local->sum(),
            'outstation' => $outstation->sum(),
            'international' => $international->sum(),
        ];

        $totalTrips = array_sum($counts);
        $donutChartData = [
            ['label' => 'Local', 'count' => $counts['local'], 'percent' => $totalTrips ? round(($counts['local'] / $totalTrips) * 100) : 0],
            ['label' => 'Outstation', 'count' => $counts['outstation'], 'percent' => $totalTrips ? round(($counts['outstation'] / $totalTrips) * 100) : 0],
            ['label' => 'International', 'count' => $counts['international'], 'percent' => $totalTrips ? round(($counts['international'] / $totalTrips) * 100) : 0],
        ];


        $tripByDepartment = TadaClaim::with('fh_employee.fh_department')
            ->where('tc_b_id', $business_id)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->get()
            ->groupBy(fn($item) => optional($item->fh_employee->fh_department)->d_name ?? 'No Department')
            ->map(fn($group) => $group->count());

        $deptLabels = $tripByDepartment->keys()->values();
        $deptTrips = $tripByDepartment->values();

        // cards Details
        $total_trip = TadaClaim::where('tc_b_id', $business_id)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->count('tc_id') ?? 0;

        $currentTrips = TadaClaim::where('tc_b_id', $business_id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count('tc_id');

        $pending_approvals = TadaClaim::where('tc_b_id', $business_id)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->whereNotNull('tc_next_approver')
            ->where('tc_stage_completed', '!=', 1)
            ->count() ?? 0;

        $approved_approvals = TadaClaim::where('tc_b_id', $business_id)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->where('tc_status', 157)
            ->count() ?? 0;

        $approved_rem = TadaClaim::where('tc_b_id', $business_id)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->where('tc_status', 200)
            ->count() ?? 0;


        $currenyt_pending_approvals = TadaClaim::where('tc_b_id', $business_id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereNotNull('tc_next_approver')
            ->where('tc_stage_completed', '!=', 1)
            ->count();

        $totaltSpend = TadaClaim::where('tc_b_id', $business_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('created_at', [$currentStart, $currentEnd])
            )
            ->sum('tc_claimed_amount');

        $currentSpend = TadaClaim::where('tc_b_id', $business_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('tc_claimed_amount');

        $total_saving = TadaClaim::where('tc_b_id', $business_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->sum('tc_deduction_amount') ?? 0;

        $current_saving = TadaClaim::where('tc_b_id', $business_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('tc_deduction_amount') ?? 0;

        $total_employee = TadaClaim::where('tc_b_id', $business_id)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->distinct('tc_emp_id')
            ->count('tc_emp_id') ?? 0;

        $total_reimburse = TadaClaim::where('tc_b_id', $business_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->sum('tc_payed_amount') ?? 0;

        $current_reimburse = TadaClaim::where('tc_b_id', $business_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('tc_payed_amount') ?? 0;


        $total_reimburse_amount_claim_wise = TadaClaim::where('tc_b_id', $business_id)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$currentStart, $currentEnd]))
            ->sum('tc_payed_amount') ?? 0;


        $monthWiseSpend = TadaClaim::where('tc_b_id', $business_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->where('tc_claimed_amount', '>', 0)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(tc_claimed_amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $monthWiseReimburse = TadaClaim::where('tc_b_id', $business_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->where('tc_payed_amount', '>', 0)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(tc_payed_amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');


        $columns = [
            'S.No',
            'Emp Code',
            'Emp Name',
            'Designation',
            'Department',
            'Claim ID',
            'Amount',
            'Claimed',
            'Deduction',
            'Date',
            'Status',
        ];

        $title = 'Dashboard';

        return view('admin.dashboard.dashboard', compact(
            'columns',
            'title',
            'counts',
            'lineChartData',
            'donutChartData',
            'deptLabels',
            'deptTrips',
            'totaltSpend',
            'currentSpend',
            'total_reimburse',
            'pending_approvals',
            'total_trip',
            'total_employee',
            'currentTrips',
            'currenyt_pending_approvals',
            'current_reimburse',
            'total_reimburse_amount_claim_wise',
            'current_saving',
            'total_saving',
            'monthWiseSpend',
            'monthWiseReimburse',
            'travelTypes',
            'datasets',
            'grades',
            'claimedAmounts',
            'payableAmounts',
            'topemployee',
            'monthlyClaims',
            'emp_amounts',
            'expenseSummary',
            'approved_approvals',
            'approved_rem',
            'highclaimsdata'
        ));
    }

    public function show(string $id)
    {
        $user = Auth::user();
        $actionRoute = request()->route()->getName();
        $claimData = TadaClaim::with(
            'fh_employee:emp_id,emp_full_name,emp_fname,emp_mname,emp_lname,emp_b_id,emp_dg_id,emp_d_id,emp_grade_id,emp_phone,emp_code',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_tada_request_plan',
            'fh_tada_request_plan.fh_policy_tada_category:ptc_id,ptc_name',
            'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
            'fh_tada_request_plan.fh_branch:br_id,br_name',
            'fh_tada_request_plan.fh_tada_expenses',
            'fh_tada_request_plan.fh_tada_expenses.fh_expense_type',
            'fh_claim_status:m_id,m_name'
        )->where('tc_b_id', $user->emp_b_id)
            ->where((DB::raw('md5(tc_id)')), $id)
            ->first();

        $approvalData = ApprovalHelper::getApprovalOrRejectionData($claimData->tc_trp_id, $claimData->tc_status, $claimData->tc_am_id, null, 146);

        $expenseTypes = $claimData->fh_tada_request_plan->fh_tada_expenses->map(function ($expense) {
            return $expense->fh_expense_type;
        });

        // $claimUserDetails = Employee::where('',);
        // DA Calcualation
        $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $claimData->fh_employee->emp_b_id, 'ptc_d_id' => $claimData->fh_employee->emp_d_id, 'ptc_grade_id' => $claimData->fh_employee->emp_grade_id])->whereJsonContains('ptc_dg_id', $claimData->fh_employee->emp_dg_id)->first();
        if (! $policyCategory) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! policy not found, contact administration.']);
        }

        $cities = TadaMetroCity::where('ctm_b_id', $user->emp_b_id)->pluck('ctm_ct_address');
        $isMetro = $cities->filter(function ($city) use ($claimData) {
            return strpos($city, $claimData->fh_tada_request_plan->trp_destination) === 0;
        });
        if (! empty($isMetro)) {
            $daEligibility = $policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23)->pluck('ptdal_da_eligibility')->first() ?? 0;              // 23 == 'Metro'
            $daEligibilitySameDay = $policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23)->pluck('ptdal_da_same_day_eligibility')->first() ?? 0;     // 23 == 'Metro'
            $lodgingWithBill = $policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23)->pluck('ptdal_lodging_eligibility_bill')->first() ?? 0;    // 23 == 'Metro'
            $lodgingWithOutBill = $policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23)->pluck('ptdal_lodging_eligibility_no_bill')->first() ?? 0; // 23 == 'Metro'
        } else {
            $daEligibility = $policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24)->pluck('ptdal_da_eligibility')->first() ?? 0;              // 24 == 'Non Metro'
            $daEligibilitySameDay = $policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24)->pluck('ptdal_da_same_day_eligibility')->first() ?? 0;     // 24 == 'Non Metro'
            $lodgingWithBill = $policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24)->pluck('ptdal_lodging_eligibility_bill')->first() ?? 0;    // 24 == 'Non Metro'
            $lodgingWithOutBill = $policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24)->pluck('ptdal_lodging_eligibility_no_bill')->first() ?? 0; // 24 == 'Non Metro'
        }

        $displayDeductionHandler = false;
        if ($user->emp_id == $claimData->tc_emp_id && $claimData->tc_deduction_amount > 0 && is_null($claimData->tc_deduction_status)) {
            $displayDeductionHandler = true;
        }
        $workingHour = 8;

        $nextApprovalData = DB::table('next_approval_details')->where('nxt_tc_id', $claimData->tc_id)->first();

        return view('admin.ta-da-request.claimdetails', compact('claimData', 'approvalData', 'displayDeductionHandler', 'daEligibility', 'lodgingWithBill', 'lodgingWithOutBill', 'workingHour', 'daEligibilitySameDay', 'nextApprovalData', 'actionRoute'));
    }

    public function travelClaim($claimId)
    {

        $user = Auth::user();
        $claimData = TadaClaim::where('tc_b_id', $user->emp_b_id)->where((DB::raw('md5(tc_id)')), $claimId)->first();

        if (! $claimData) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Claim data not found.']);
        }

        $policyCategory = PolicyTadaCategory::where([
            'ptc_b_id' => optional($claimData->fh_employee)->emp_b_id,
            'ptc_d_id' => optional($claimData->fh_employee)->emp_d_id,
            'ptc_grade_id' => optional($claimData->fh_employee)->emp_grade_id,
        ])->whereJsonContains('ptc_dg_id', optional($claimData->fh_employee)->emp_dg_id)->first();

        if (! $policyCategory) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! policy not found, contact administration.']);
        }

        $cities = TadaMetroCity::where('ctm_b_id', optional($user)->emp_b_id)->pluck('ctm_ct_address');
        $isMetro = $cities->filter(function ($city) use ($claimData) {
            return strpos($city, optional($claimData->fh_tada_request_plan)->trp_destination) === 0;
        });

        if (! empty($isMetro)) {
            $daEligibility = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23))->pluck('ptdal_da_eligibility')->first() ?? 0;              // 23 == 'Metro'
            $lodgingWithBill = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23))->pluck('ptdal_lodging_eligibility_bill')->first() ?? 0;    // 23 == 'Metro'
            $lodgingWithOutBill = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23))->pluck('ptdal_lodging_eligibility_no_bill')->first() ?? 0; // 23 == 'Metro'
        } else {
            $daEligibility = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24))->pluck('ptdal_da_eligibility')->first() ?? 0;              // 24 == 'Non Metro'
            $lodgingWithBill = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24))->pluck('ptdal_lodging_eligibility_bill')->first() ?? 0;    // 24 == 'Non Metro'
            $lodgingWithOutBill = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24))->pluck('ptdal_lodging_eligibility_no_bill')->first() ?? 0; // 24 == 'Non Metro'
        }

        $expenseData = optional($claimData->fh_tada_request_plan->fh_tada_expenses)->groupBy('fh_expense_type.m_name') ?? [];

        $numberToWords = new NumberToWords;

        // Get the converter for English
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        // Convert the number to words
        $payableAmount = ($claimData->tc_amount ?? 0) - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0));
        $words = $numberTransformer->toWords($payableAmount);
        $capitalizedWords = ucfirst($words);

        $data = [
            'title' => 'Welcome to Laravel PDF Generation',
            'claimData' => $claimData,
            'capitalizedWords' => $capitalizedWords,
            'daEligibility' => $daEligibility,
            'expenseData' => $expenseData,
            'logoPath' => optional($user->fh_business)->b_logo,
        ];

        $pdf = Pdf::loadView('admin.tada-reports.document', $data);

        return $pdf->stream('document.pdf');
    }
}
