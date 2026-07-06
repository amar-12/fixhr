<?php

namespace App\Http\Controllers\Payroll\Taxation;

use App\Helpers\PayrollLogics;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\FormChallan;
use App\Models\ProcessedEmployeeSalary;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LedgerController extends Controller
{
    public function form16AAvailability(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'financial_year_id' => 'required|integer|exists:financial_years,fy_id',
            'quarter' => 'required|in:Q1,Q2,Q3,Q4,ALL',
        ]);

        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;
        $employeeId = (int) $request->employee_id;
        $financialYearId = (int) $request->financial_year_id;
        $quarter = (string) $request->quarter;

        // Ensure employee belongs to business
        Employee::where('emp_id', $employeeId)
            ->where('emp_b_id', $businessId)
            ->firstOrFail();

        $financialYear = FinancialYear::where('fy_id', $financialYearId)
            ->where('fy_b_id', $businessId)
            ->first();

        if (! $financialYear || ! $financialYear->fy_start_date) {
            return response()->json([
                'exists' => false,
                'count' => 0,
                'message' => 'Financial year dates are not configured.',
            ]);
        }

        $fyStart = Carbon::parse($financialYear->fy_start_date)->startOfDay();
        $quarterStart = $fyStart->copy();
        $quarterEnd = $financialYear->fy_end_date
            ? Carbon::parse($financialYear->fy_end_date)->endOfDay()
            : $fyStart->copy()->addYear()->subDay()->endOfDay();

        if ($quarter !== 'ALL') {
            $qIndex = (int) str_replace('Q', '', $quarter); // 1..4
            $quarterStart = $fyStart->copy()->addMonths(($qIndex - 1) * 3)->startOfDay();
            $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay()->endOfDay();
        }

        $rows = ProcessedEmployeeSalary::query()
            ->with(['payrollPeriod'])
            ->where('ps_b_id', $businessId)
            ->where('ps_emp_id', $employeeId)
            ->whereHas('payrollPeriod', function ($query) use ($financialYearId, $quarterStart, $quarterEnd) {
                $query->where('pp_fy_id', $financialYearId)
                    ->whereNotNull('pp_start_date')
                    ->whereBetween('pp_start_date', [$quarterStart, $quarterEnd]);
            })
            ->count();

        if ($rows <= 0) {
            return response()->json([
                'exists' => false,
                'count' => 0,
                'message' => $quarter === 'ALL'
                    ? 'Salary of this financial year is not generated yet.'
                    : 'Salary of this quarter is not generated yet.',
            ]);
        }

        // Challan validation: quarter-wise requires challan; FY requires challan for all quarters having salary data
        if ($quarter !== 'ALL') {
            $challan = FormChallan::where('f16ac_b_id', $businessId)
                ->where('f16ac_emp_id', $employeeId)
                ->where('f16ac_fy_id', $financialYearId)
                ->where('f16ac_quarter', $quarter)
                ->where('f16ac_form_key', 'FORM16A')
                ->first();

            if (! $challan) {
                return response()->json([
                    'exists' => false,
                    'count' => $rows,
                    'message' => 'Challan details for this quarter are not updated yet.',
                ]);
            }
        } else {
            // Determine which quarters have salary in this FY
            $quarterHasSalary = ['Q1' => false, 'Q2' => false, 'Q3' => false, 'Q4' => false];
            $salaryRows = ProcessedEmployeeSalary::query()
                ->with(['payrollPeriod'])
                ->where('ps_b_id', $businessId)
                ->where('ps_emp_id', $employeeId)
                ->whereHas('payrollPeriod', function ($query) use ($financialYearId) {
                    $query->where('pp_fy_id', $financialYearId)->whereNotNull('pp_start_date');
                })
                ->get();

            foreach ($salaryRows as $r) {
                $startDate = optional($r->payrollPeriod)->pp_start_date;
                if (! $startDate) continue;
                $q = $this->quarterFromMonthNumber((int) Carbon::parse($startDate)->format('n'));
                $quarterHasSalary[$q] = true;
            }

            $existing = FormChallan::where('f16ac_b_id', $businessId)
                ->where('f16ac_emp_id', $employeeId)
                ->where('f16ac_fy_id', $financialYearId)
                ->where('f16ac_form_key', 'FORM16A')
                ->get()
                ->keyBy('f16ac_quarter');

            foreach ($quarterHasSalary as $q => $hasSalary) {
                if (! $hasSalary) continue;
                if (! $existing->has($q)) {
                    return response()->json([
                        'exists' => false,
                        'count' => $rows,
                        'message' => "Challan details for {$q} are not updated yet.",
                    ]);
                }
            }
        }

        return response()->json([
            'exists' => $rows > 0,
            'count' => $rows,
            'message' => 'OK',
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $financialYears = FinancialYear::where('fy_b_id', $businessId)
            ->orderBy('fy_year', 'desc')
            ->get(['fy_id', 'fy_year', 'fy_is_current']);

        $selectedFYId = (int) $request->input('financial_year_id');
        if (! $selectedFYId) {
            $selectedFYId = (int) optional($financialYears->firstWhere('fy_is_current', 1))->fy_id;
        }
        if (! $selectedFYId) {
            $selectedFYId = (int) optional($financialYears->first())->fy_id;
        }

        $processedEmployeeIds = ProcessedEmployeeSalary::query()
            ->where('ps_b_id', $businessId)
            ->whereHas('payrollPeriod', function ($query) use ($selectedFYId) {
                $query->where('pp_fy_id', $selectedFYId);
            })
            ->pluck('ps_emp_id')
            ->unique()
            ->values();

        $employees = Employee::query()
            ->with(['fh_designation:dg_id,dg_name', 'fh_department:d_id,d_name', 'fh_branch:br_id,br_name'])
            ->where('emp_b_id', $businessId)
            ->whereIn('emp_status', [71, 1])
            ->where('emp_role_id', '!=', 1)
            ->whereIn('emp_id', $processedEmployeeIds)
            ->orderBy('emp_full_name')
            ->get([
                'emp_id',
                'emp_code',
                'emp_full_name',
                'emp_pan_number',
                'emp_dg_id',
                'emp_d_id',
                'emp_br_id',
                'emp_email',
                'emp_phone',
                'emp_role_id',
            ])
            ->map(function ($employee) {
                return [
                    'id' => $employee->emp_id,
                    'code' => $employee->emp_code ?: ('EMP' . $employee->emp_id),
                    'name' => $employee->emp_full_name ?: ('Employee #' . $employee->emp_id),
                    'designation' => optional($employee->fh_designation)->dg_name ?: 'N/A',
                    'department' => optional($employee->fh_department)->d_name ?: 'N/A',
                    'branch' => optional($employee->fh_branch)->br_name ?: 'N/A',
                    'pan' => $employee->emp_pan_number ?: 'N/A',
                    'email' => $employee->emp_email ?: 'N/A',
                    'phone' => $employee->emp_phone ?: 'N/A',
                    'regime' => 'New',
                ];
            });

        return view('admin.payroll.taxation.monthly-ledger', [
            'employees' => $employees,
            'totalEmployees' => $employees->count(),
            'financialYears' => $financialYears,
            'selectedFYId' => $selectedFYId,
        ]);
    }


    public function generateForm16A(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'financial_year_id' => 'required|integer|exists:financial_years,fy_id',
            'quarter' => 'nullable|in:Q1,Q2,Q3,Q4',
        ]);

        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $selectedFYId = (int) $request->financial_year_id;
        $selectedQuarter = $request->input('quarter');

        $employeeModel = Employee::where('emp_id', $request->employee_id)
            ->where('emp_b_id', $businessId)
            ->firstOrFail();

        $financialYear = FinancialYear::find($selectedFYId);
        if (! $financialYear) {
            return redirect()->back()->with('denied', 'Financial year not found.');
        }

        $processedRows = ProcessedEmployeeSalary::query()
            ->with(['payrollPeriod.month', 'deductions'])
            ->where('ps_b_id', $businessId)
            ->where('ps_emp_id', $employeeModel->emp_id)
            ->whereHas('payrollPeriod', function ($query) use ($selectedFYId) {
                $query->where('pp_fy_id', $selectedFYId);
            })
            ->get();

        if ($processedRows->isEmpty()) {
            return redirect()->back()->with('denied', 'No processed salary data found for selected financial year.');
        }

        $allProcessedRows = $processedRows;
        $quarterStart = null;
        $quarterEnd = null;
        if ($selectedQuarter) {
            $fyStart = $financialYear->fy_start_date ? Carbon::parse($financialYear->fy_start_date)->startOfDay() : null;
            if ($fyStart) {
                $qIndex = (int) str_replace('Q', '', $selectedQuarter); // 1..4
                $quarterStart = $fyStart->copy()->addMonths(($qIndex - 1) * 3)->startOfDay();
                $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay()->endOfDay();

                $processedRows = $processedRows->filter(function ($row) use ($quarterStart, $quarterEnd) {
                    $startDate = optional($row->payrollPeriod)->pp_start_date;
                    if (! $startDate) {
                        return false;
                    }
                    $d = Carbon::parse($startDate);
                    return $d->betweenIncluded($quarterStart, $quarterEnd);
                })->values();
            }
        }

        if ($selectedQuarter && $processedRows->isEmpty()) {
            return response('Salary of this quarter is not generated yet.', 422);
        }

        // Challan validation
        $challanByQuarter = FormChallan::where('f16ac_b_id', $businessId)
            ->where('f16ac_emp_id', (int) $employeeModel->emp_id)
            ->where('f16ac_fy_id', $selectedFYId)
            ->where('f16ac_form_key', 'FORM16A')
            ->get()
            ->keyBy('f16ac_quarter');

        if ($selectedQuarter) {
            if (! $challanByQuarter->has($selectedQuarter)) {
                return response('Challan details for this quarter are not updated yet.', 422);
            }
        } else {
            // For full FY, require challan for quarters that have salary
            $quarterHasSalary = ['Q1' => false, 'Q2' => false, 'Q3' => false, 'Q4' => false];
            foreach ($allProcessedRows as $r) {
                $startDate = optional($r->payrollPeriod)->pp_start_date;
                if (! $startDate) continue;
                $q = $this->quarterFromMonthNumber((int) Carbon::parse($startDate)->format('n'));
                $quarterHasSalary[$q] = true;
            }
            foreach ($quarterHasSalary as $q => $hasSalary) {
                if (! $hasSalary) continue;
                if (! $challanByQuarter->has($q)) {
                    return response("Challan details for {$q} are not updated yet.", 422);
                }
            }
        }

        $payrollHistory = $processedRows->map(function ($row) {
            $period = $row->payrollPeriod;
            $monthName = optional(optional($period)->month)->m_name;

            $tdsFromDeductions = $row->deductions
                ->filter(function ($deduction) {
                    $type = strtolower((string) ($deduction->ps_deduction_type ?? ''));
                    $category = strtolower((string) ($deduction->ps_d_category ?? ''));
                    return str_contains($type, 'tds') || str_contains($category, 'tds');
                })
                ->sum('ps_d_amount');

            return [
                'month' => $monthName ?: optional($period?->pp_start_date)->format('M Y'),
                'gross' => (float) ($row->ps_monthly_gross ?? 0),
                'tds' => (float) $tdsFromDeductions,
                'payment_date' => optional($period?->pp_payment_date)->format('d/m/Y') ?: '',
                'challan' => [
                    'bsr' => '-',
                    'date' => optional($period?->pp_payment_date)->format('d/m/Y') ?: '-',
                    'serial' => '-',
                ],
            ];
        })->values();

        // Enrich challan info per month (quarter-level challan reused for all months in quarter)
        $payrollHistory = $payrollHistory->map(function ($item) use ($challanByQuarter) {
            $monthText = (string) ($item['month'] ?? '');
            $monthNum = null;
            try {
                $monthNum = (int) Carbon::parse('01 ' . $monthText)->format('n');
            } catch (\Throwable $e) {
                $monthNum = null;
            }
            if (! $monthNum) {
                return $item;
            }
            $q = $this->quarterFromMonthNumber($monthNum);
            $c = $challanByQuarter->get($q);
            if (! $c) {
                return $item;
            }
            $item['challan'] = [
                'bsr' => $c->f16ac_bsr_code ?? '-',
                'date' => optional($c->f16ac_challan_date)->format('d/m/Y') ?: '-',
                'serial' => $c->f16ac_challan_serial_no ?? '-',
            ];
            return $item;
        })->values()->all();

        $employee = (object) [
            'id' => $employeeModel->emp_id,
            'name' => $employeeModel->emp_full_name,
            'pan' => $employeeModel->emp_pan_number,
            'address' => $employeeModel->emp_address,
            'designation' => optional($employeeModel->fh_designation)->dg_name,
        ];

        $business = Business::with('fh_city')->find($businessId);
        $businessAddress = trim((string) ($business->b_address ?? ''));
        $businessCity = optional($business->fh_city)->ct_name
            ?? ($business->b_city ?? '')
            ?? '';
        $businessPin = $business->b_pin_code
            ?? ($business->b_pincode ?? '')
            ?? '';

        $company = (object) [
            'name' => optional($business)->b_name,
            'address' => $businessAddress,
            'pan' => optional($business)->b_pan_no,
            'tan' => optional($business)->b_tan_no
                ?? optional($business)->b_tan
                ?? optional($business)->b_tax_deduction_account_no
                ?? '',
            'cit_address' => optional($business)->b_cit_tds_address
                ?? optional($business)->b_tds_address
                ?? $businessAddress,
            'city' => $businessCity,
            'pin' => $businessPin,
        ];

        // Calculate totals
        $totalGross = collect($payrollHistory)->sum('gross');
        $totalTds   = collect($payrollHistory)->sum('tds');
        $regime = $this->resolveRegime($businessId, $selectedFYId);
        $expectedTaxBySlab = $this->calculateTaxFromSlabs($businessId, $selectedFYId, $regime, (float) $totalGross);
        $taxVariance = (float) $totalTds - (float) $expectedTaxBySlab;

        $quarterBuckets = ['Q1' => 0.0, 'Q2' => 0.0, 'Q3' => 0.0, 'Q4' => 0.0];
        foreach ($allProcessedRows as $row) {
            $periodStart = optional($row->payrollPeriod)->pp_start_date;
            if (! $periodStart) {
                continue;
            }
            $monthNum = (int) Carbon::parse($periodStart)->format('n'); // 1..12
            $deduction = $row->deductions
                ->filter(function ($item) {
                    $type = strtolower((string) ($item->ps_deduction_type ?? ''));
                    $category = strtolower((string) ($item->ps_d_category ?? ''));
                    return str_contains($type, 'tds') || str_contains($category, 'tds');
                })
                ->sum('ps_d_amount');

            // FY quarters: Q1 Apr-Jun, Q2 Jul-Sep, Q3 Oct-Dec, Q4 Jan-Mar
            if (in_array($monthNum, [4, 5, 6], true)) {
                $quarterBuckets['Q1'] += $deduction;
            } elseif (in_array($monthNum, [7, 8, 9], true)) {
                $quarterBuckets['Q2'] += $deduction;
            } elseif (in_array($monthNum, [10, 11, 12], true)) {
                $quarterBuckets['Q3'] += $deduction;
            } elseif (in_array($monthNum, [1, 2, 3], true)) {
                $quarterBuckets['Q4'] += $deduction;
            }
        }

        $quarters = collect($quarterBuckets)->mapWithKeys(function ($amount, $quarter) use ($challanByQuarter) {
            return [
                $quarter => [
                    'receipt_no' => optional($challanByQuarter->get($quarter))->f16ac_receipt_no ?? '-',
                    'tds_deducted' => (float) $amount,
                    'tds_deposited' => (float) $amount,
                ],
            ];
        })->all();

        if ($selectedQuarter && isset($quarters[$selectedQuarter])) {
            $quarters = [
                $selectedQuarter => $quarters[$selectedQuarter],
            ];
        }

        $fyYear = (string) ($financialYear->fy_year ?? '');
        $assessmentYear = $fyYear;
        if (preg_match('/(\d{4})\D+(\d{4})/', $fyYear, $matches)) {
            $assessmentYear = ($matches[1] + 1) . '-' . substr((string) ($matches[2] + 1), -2);
        }

        // Data array for Blade view
        $data = [
            'employee'         => $employee,
            'company'          => $company,
            'payrollHistory'   => $payrollHistory,
            'quarters'         => $quarters,
            'challanByQuarter' => $challanByQuarter,

            'certificateNo'    => 'TDS/' . now()->format('Y') . '/' . $employeeModel->emp_id,
            'currentDate'      => now()->format('d/m/Y'),
            'assessmentYear'   => $assessmentYear,
            'fromDate'         => $quarterStart ? $quarterStart->format('d/m/Y') : optional($financialYear->fy_start_date)->format('d/m/Y'),
            'toDate'           => $quarterEnd ? $quarterEnd->format('d/m/Y') : optional($financialYear->fy_end_date)->format('d/m/Y'),

            'totalGross'       => $totalGross,
            'totalTds'         => $totalTds,
            'totalTdsWords'    => $this->numberToWords($totalTds),
            'expectedTaxBySlab' => $expectedTaxBySlab,
            'taxVariance' => $taxVariance,
            'regime' => ucfirst($regime),

            'natureOfPayment'  => 'Salary',

            'signatoryName'        => $user->emp_full_name,
            'signatoryFather'      => '',
            'signatoryDesignation' => optional($user->fh_designation)->dg_name ?: 'Authorized Signatory',
        ];

        // Load Blade view and generate PDF
        $pdf = Pdf::loadView('admin.payroll.forms.pdf.form16a', $data);
        $pdf->setPaper('A4', 'portrait');

        $empName = Str::slug((string) ($employee->name ?? 'employee'));
        $empCode = Str::slug((string) ($employeeModel->emp_code ?? $employeeModel->emp_id));
        $filename = 'form16a_' . $empName . '_' . $empCode . '.pdf';

        return $pdf->stream($filename);
    }

    public function getEmployeeLedgerData(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'financial_year_id' => 'required|integer|exists:financial_years,fy_id',
        ]);

        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $employeeId = (int) $request->employee_id;
        $financialYearId = (int) $request->financial_year_id;

        $employee = Employee::where('emp_id', $employeeId)
            ->where('emp_b_id', $businessId)
            ->firstOrFail();

        $processedRows = ProcessedEmployeeSalary::query()
            ->with(['payrollPeriod.month', 'deductions', 'earnings'])
            ->where('ps_b_id', $businessId)
            ->where('ps_emp_id', $employeeId)
            ->whereHas('payrollPeriod', function ($query) use ($financialYearId) {
                $query->where('pp_fy_id', $financialYearId);
            })
            ->get();

        $rows = $processedRows->map(function ($row) {
            $earnings = $row->earnings instanceof Collection ? $row->earnings : collect($row->earnings);
            $deductions = $row->deductions instanceof Collection ? $row->deductions : collect($row->deductions);

            $basic = $this->sumEarningsByKeywords($earnings, ['basic']);
            $hra = $this->sumEarningsByKeywords($earnings, ['hra', 'house rent']);
            $special = $this->sumEarningsByKeywords($earnings, ['special']);
            $bonusLta = $this->sumEarningsByKeywords($earnings, ['bonus', 'lta', 'leave travel']);

            $pf = $this->sumDeductionsByKeywords($deductions, ['pf', 'provident']);
            $pt = $this->sumDeductionsByKeywords($deductions, ['pt', 'professional tax']);
            $tds = $this->sumDeductionsByKeywords($deductions, ['tds', 'tax deducted']);

            $gross = (float) ($row->ps_monthly_gross ?? 0);
            $net = (float) ($row->ps_monthly_net_salary ?? 0);

            $period = $row->payrollPeriod;
            $monthName = optional(optional($period)->month)->m_name ?: optional($period?->pp_start_date)->format('M Y');

            return [
                'month' => $monthName ?: '-',
                'basic' => $basic,
                'hra' => $hra,
                'special' => $special,
                'bonus_lta' => $bonusLta,
                'gross' => $gross,
                'pf' => $pf,
                'pt' => $pt,
                'tds' => $tds,
                'net' => $net,
            ];
        })->values();

        $summary = [
            'annual_gross' => (float) $rows->sum('gross'),
            'total_tds' => (float) $rows->sum('tds'),
            'total_pf' => (float) $rows->sum('pf'),
            'net_payable' => (float) $rows->sum('net'),
            'taxable_income' => (float) max($rows->sum('gross') - $rows->sum('pf') - $rows->sum('pt'), 0),
        ];
        $regime = $this->resolveRegime($businessId, $financialYearId);
        $expectedTaxBySlab = $this->calculateTaxFromSlabs($businessId, $financialYearId, $regime, $summary['taxable_income']);
        $taxVariance = (float) $summary['total_tds'] - (float) $expectedTaxBySlab;

        $totals = [
            'basic' => (float) $rows->sum('basic'),
            'hra' => (float) $rows->sum('hra'),
            'special' => (float) $rows->sum('special'),
            'bonus_lta' => (float) $rows->sum('bonus_lta'),
            'gross' => (float) $rows->sum('gross'),
            'pf' => (float) $rows->sum('pf'),
            'pt' => (float) $rows->sum('pt'),
            'tds' => (float) $rows->sum('tds'),
            'net' => (float) $rows->sum('net'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->emp_id,
                    'name' => $employee->emp_full_name,
                    'code' => $employee->emp_code,
                    'regime' => ucfirst($regime),
                ],
                'rows' => $rows,
                'summary' => $summary,
                'totals' => $totals,
                'tax_validation' => [
                    'expected_tax_by_slab' => (float) $expectedTaxBySlab,
                    'actual_tds_deducted' => (float) $summary['total_tds'],
                    'variance' => (float) $taxVariance,
                    'is_aligned' => abs($taxVariance) < 1,
                ],
            ],
        ]);
    }

    private function resolveRegime(int $businessId, int $financialYearId): string
    {
        return PayrollLogics::resolveIncomeTaxRegime($businessId, $financialYearId);
    }

    private function quarterFromMonthNumber(int $monthNum): string
    {
        // FY quarters: Q1 Apr-Jun, Q2 Jul-Sep, Q3 Oct-Dec, Q4 Jan-Mar
        if (in_array($monthNum, [4, 5, 6], true)) {
            return 'Q1';
        }
        if (in_array($monthNum, [7, 8, 9], true)) {
            return 'Q2';
        }
        if (in_array($monthNum, [10, 11, 12], true)) {
            return 'Q3';
        }
        return 'Q4';
    }

    private function calculateTaxFromSlabs(int $businessId, int $financialYearId, string $regime, float $annualTaxableIncome): float
    {
        return PayrollLogics::calculateIncomeTaxFromSlabs($businessId, $financialYearId, $regime, $annualTaxableIncome);
    }

    private function sumEarningsByKeywords(Collection $earnings, array $keywords): float
    {
        return (float) $earnings
            ->filter(function ($item) use ($keywords) {
                $type = strtolower((string) ($item->ps_earning_type ?? ''));
                foreach ($keywords as $keyword) {
                    if (str_contains($type, strtolower($keyword))) {
                        return true;
                    }
                }
                return false;
            })
            ->sum('ps_e_amount');
    }

    private function sumDeductionsByKeywords(Collection $deductions, array $keywords): float
    {
        return (float) $deductions
            ->filter(function ($item) use ($keywords) {
                $type = strtolower((string) ($item->ps_deduction_type ?? ''));
                $category = strtolower((string) ($item->ps_d_category ?? ''));
                foreach ($keywords as $keyword) {
                    $needle = strtolower($keyword);
                    if (str_contains($type, $needle) || str_contains($category, $needle)) {
                        return true;
                    }
                }
                return false;
            })
            ->sum('ps_d_amount');
    }

    private function numberToWords($number)
    {
        $words = [
            "",
            "One",
            "Two",
            "Three",
            "Four",
            "Five",
            "Six",
            "Seven",
            "Eight",
            "Nine",
            "Ten",
            "Eleven",
            "Twelve",
            "Thirteen",
            "Fourteen",
            "Fifteen",
            "Sixteen",
            "Seventeen",
            "Eighteen",
            "Nineteen"
        ];
        $tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

        if ($number == 0) return "Zero Rupees Only";

        $num = (int) $number;
        $str = "";

        if ($num >= 10000000) {
            $str .= $this->numberToWords(floor($num / 10000000)) . " Crore ";
            $num %= 10000000;
        }
        if ($num >= 100000) {
            $str .= $this->numberToWords(floor($num / 100000)) . " Lakh ";
            $num %= 100000;
        }
        if ($num >= 1000) {
            $str .= $this->numberToWords(floor($num / 1000)) . " Thousand ";
            $num %= 1000;
        }
        if ($num >= 100) {
            $str .= $words[floor($num / 100)] . " Hundred ";
            $num %= 100;
        }
        if ($num > 0) {
            if ($num < 20) {
                $str .= $words[$num] . " ";
            } else {
                $str .= $tens[floor($num / 10)] . " ";
                if ($num % 10) $str .= $words[$num % 10] . " ";
            }
        }

        return trim($str) . " Rupees Only";
    }

    public function generateForm16(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'financial_year_id' => 'required|integer|exists:financial_years,fy_id',
        ]);

        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;
        $employeeId = (int) $request->employee_id;
        $financialYearId = (int) $request->financial_year_id;

        $financialYear = FinancialYear::where('fy_id', $financialYearId)
            ->where('fy_b_id', $businessId)
            ->firstOrFail();

        // Form 16 should be generated after FY end date.
        $fyEndDate = $financialYear->fy_end_date
            ? \Carbon\Carbon::parse($financialYear->fy_end_date)->endOfDay()
            : null;
        if ($fyEndDate && now()->lt($fyEndDate)) {
            return redirect()->back()->with('denied', 'Form 16 can be generated only after end of selected financial year.');
        }

        $employeeModel = Employee::with('fh_designation')
            ->where('emp_id', $employeeId)
            ->where('emp_b_id', $businessId)
            ->firstOrFail();

        $processedRows = ProcessedEmployeeSalary::query()
            ->with(['payrollPeriod.month', 'deductions', 'earnings'])
            ->where('ps_b_id', $businessId)
            ->where('ps_emp_id', $employeeId)
            ->whereHas('payrollPeriod', function ($query) use ($financialYearId) {
                $query->where('pp_fy_id', $financialYearId);
            })
            ->get();

        if ($processedRows->isEmpty()) {
            return redirect()->back()->with('denied', 'No processed payroll data found for selected employee in this financial year.');
        }

        $regime = $this->resolveRegime($businessId, $financialYearId);

        $payrollHistory = $processedRows->map(function ($row) {
            $period = $row->payrollPeriod;
            $monthName = optional(optional($period)->month)->m_name ?: optional($period?->pp_start_date)->format('M Y');

            $earnings = $row->earnings instanceof Collection ? $row->earnings : collect($row->earnings);
            $deductions = $row->deductions instanceof Collection ? $row->deductions : collect($row->deductions);

            $basic = $this->sumEarningsByKeywords($earnings, ['basic']);
            $hra = $this->sumEarningsByKeywords($earnings, ['hra', 'house rent']);
            $special = $this->sumEarningsByKeywords($earnings, ['special']);
            $bonusLta = $this->sumEarningsByKeywords($earnings, ['bonus', 'lta', 'leave travel']);

            $pf = $this->sumDeductionsByKeywords($deductions, ['pf', 'provident']);
            $pt = $this->sumDeductionsByKeywords($deductions, ['pt', 'professional tax']);
            $tds = $this->sumDeductionsByKeywords($deductions, ['tds', 'tax deducted']);

            return [
                'month' => $monthName ?: '-',
                'gross' => (float) ($row->ps_monthly_gross ?? 0),
                'tds' => (float) $tds,
                'pf' => (float) $pf,
                'pt' => (float) $pt,
                'components' => [
                    'basic' => (float) $basic,
                    'hra' => (float) $hra,
                    'special' => (float) $special,
                    'bonus' => (float) $bonusLta,
                    'lta' => 0.0,
                ],
                'challan' => [
                    'bsr' => '-',
                    'date' => optional($period?->pp_payment_date)->format('d/m/Y') ?: '-',
                    'serial' => '-',
                    'amount' => (float) $tds,
                ],
                'quarter_id' => $this->quarterFromMonthId((int) optional($period)->pp_month_id),
            ];
        })->values()->all();

        $totals = [
            'gross' => (float) collect($payrollHistory)->sum('gross'),
            'tds' => (float) collect($payrollHistory)->sum('tds'),
            'basic' => (float) collect($payrollHistory)->sum('components.basic'),
            'hra' => (float) collect($payrollHistory)->sum('components.hra'),
            'special' => (float) collect($payrollHistory)->sum('components.special'),
            'bonus_lta' => (float) collect($payrollHistory)->sum('components.bonus'),
            'pf' => (float) collect($payrollHistory)->sum('pf'),
            'pt' => (float) collect($payrollHistory)->sum('pt'),
        ];

        $quarterMap = ['Q1' => ['paid' => 0.0, 'tds_deducted' => 0.0], 'Q2' => ['paid' => 0.0, 'tds_deducted' => 0.0], 'Q3' => ['paid' => 0.0, 'tds_deducted' => 0.0], 'Q4' => ['paid' => 0.0, 'tds_deducted' => 0.0]];
        foreach ($payrollHistory as $row) {
            $q = $row['quarter_id'] ?: 'Q1';
            if (!isset($quarterMap[$q])) {
                $quarterMap[$q] = ['paid' => 0.0, 'tds_deducted' => 0.0];
            }
            $quarterMap[$q]['paid'] += (float) ($row['gross'] ?? 0);
            $quarterMap[$q]['tds_deducted'] += (float) ($row['tds'] ?? 0);
        }
        $quarters = collect($quarterMap)->mapWithKeys(function ($val, $q) {
            return [$q => [
                'receipt_no' => '-',
                'paid' => round((float) $val['paid'], 2),
                'tds_deducted' => round((float) $val['tds_deducted'], 2),
                'tds_deposited' => round((float) $val['tds_deducted'], 2),
            ]];
        })->all();

        $stdDeduction = 50000.0;
        $ltaExempt = 0.0;
        $hraExempt = 0.0;
        $salaryAfterExemptions = max($totals['gross'] - $hraExempt - $ltaExempt, 0);
        $totalSection16 = $stdDeduction + $totals['pt'];
        $incomeFromSalary = max($salaryAfterExemptions - $totalSection16, 0);
        $totalVIA = min($totals['pf'], 150000.0);
        $taxableIncome = max($incomeFromSalary - $totalVIA, 0);
        $tax = $this->calculateTaxFromSlabs($businessId, $financialYearId, $regime, $taxableIncome);
        $cess = round($tax * 0.04, 2);
        $totalTax = round($tax + $cess, 2);

        $taxComputation = [
            'hraExempt' => $hraExempt,
            'stdDeduction' => $stdDeduction,
            'totalVIA' => $totalVIA,
            'taxable' => $incomeFromSalary,
            'tax' => $tax,
            'cess' => $cess,
            'totalTax' => $totalTax,
            'lta' => $ltaExempt,
        ];

        $business = Business::with('fh_city')->find($businessId);
        $businessAddress = trim((string) ($business->b_address ?? ''));
        $businessCity = optional($business->fh_city)->ct_name
            ?? ($business->b_city ?? '')
            ?? '';
        $businessPin = $business->b_pin_code
            ?? ($business->b_pincode ?? '')
            ?? '';

        $company = (object) [
            'name' => optional($business)->b_name,
            'address' => $businessAddress,
            'pan' => optional($business)->b_pan_no,
            'tan' => optional($business)->b_tan_no
                ?? optional($business)->b_tan
                ?? optional($business)->b_tax_deduction_account_no
                ?? '',
            'cit_address' => optional($business)->b_cit_tds_address
                ?? optional($business)->b_tds_address
                ?? $businessAddress,
            'city' => $businessCity,
            'pin' => $businessPin,
        ];

        $fyYear = (string) ($financialYear->fy_year ?? '');
        $assessmentYear = $fyYear;
        if (preg_match('/(\d{4})\D+(\d{4})/', $fyYear, $matches)) {
            $assessmentYear = ($matches[1] + 1) . '-' . substr((string) ($matches[2] + 1), -2);
        }

        $employee = (object) [
            'id' => $employeeModel->emp_id,
            'name' => $employeeModel->emp_full_name,
            'pan' => $employeeModel->emp_pan_number,
            'address' => $employeeModel->emp_address,
            'designation' => optional($employeeModel->fh_designation)->dg_name,
            'regime' => ucfirst($regime),
            'ref_no' => $employeeModel->emp_code,
        ];

        $data = [
            'employee' => $employee,
            'company' => $company,
            'payrollHistory' => $payrollHistory,
            'totals' => $totals,
            'taxComputation' => $taxComputation,
            'quarters' => $quarters,
            'totalTds' => $totals['tds'],
            'totalTdsWords' => $this->numberToWords($totals['tds']),
            'currentDate' => now()->format('d/m/Y'),
            'certificateNo' => 'TDS/' . now()->format('Y') . '/' . $employeeModel->emp_id,
            'assessmentYear' => $assessmentYear,
            'fromDate' => optional($financialYear->fy_start_date)->format('d/m/Y'),
            'toDate' => optional($financialYear->fy_end_date)->format('d/m/Y'),
            'signatoryName' => $user->emp_full_name,
            'signatoryFather' => '',
            'signatoryDesignation' => optional($user->fh_designation)->dg_name ?: 'Authorized Signatory',
        ];

        $pdf = Pdf::loadView('admin.payroll.forms.pdf.form16', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Form_16_' . str_replace(' ', '_', $employee->name) . '.pdf');
    }

    private function quarterFromMonthId(int $monthId): string
    {
        if (in_array($monthId, [3, 4, 5], true)) {
            return 'Q1';
        }
        if (in_array($monthId, [6, 7, 8], true)) {
            return 'Q2';
        }
        if (in_array($monthId, [9, 10, 11], true)) {
            return 'Q3';
        }
        if (in_array($monthId, [12, 1, 2], true)) {
            return 'Q4';
        }
        return 'Q1';
    }

    public function generateForm15G(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'financial_year_id' => 'required|integer|exists:financial_years,fy_id',
        ]);

        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;
        $employeeId = (int) $request->employee_id;
        $financialYearId = (int) $request->financial_year_id;

        $financialYear = FinancialYear::where('fy_id', $financialYearId)
            ->where('fy_b_id', $businessId)
            ->firstOrFail();

        $employeeModel = Employee::where('emp_id', $employeeId)
            ->where('emp_b_id', $businessId)
            ->firstOrFail();

        $business = Business::with('fh_city')->find($businessId);

        $processedRows = ProcessedEmployeeSalary::query()
            ->with(['payrollPeriod', 'deductions'])
            ->where('ps_b_id', $businessId)
            ->where('ps_emp_id', $employeeId)
            ->whereHas('payrollPeriod', function ($query) use ($financialYearId) {
                $query->where('pp_fy_id', $financialYearId);
            })
            ->get();

        $estimatedIncome = (float) $processedRows->sum(function ($row) {
            return (float) ($row->ps_monthly_gross ?? 0);
        });
        $estimatedTax = (float) $processedRows->sum(function ($row) {
            $deductions = $row->deductions instanceof Collection ? $row->deductions : collect($row->deductions);
            return $this->sumDeductionsByKeywords($deductions, ['tds', 'tax deducted']);
        });

        $fyYear = (string) ($financialYear->fy_year ?? '');
        $assessmentYear = $fyYear;
        if (preg_match('/(\d{4})\D+(\d{4})/', $fyYear, $matches)) {
            $assessmentYear = ($matches[1] + 1) . '-' . substr((string) ($matches[2] + 1), -2);
        }

        $previousYear = $fyYear;
        $declarationYear = now()->format('Y');

        $employee = (object) [
            'id' => $employeeModel->emp_id,
            'name' => $employeeModel->emp_full_name,
            'pan' => $employeeModel->emp_pan_number,
            'address' => $employeeModel->emp_address,
            'mobile' => $employeeModel->emp_mobile_no ?? '',
            'email' => $employeeModel->emp_email ?? '',
            'dob' => optional($employeeModel->emp_dob)->format('d/m/Y'),
            'status' => 'Individual',
            'residential_status' => 'Resident',
        ];

        $businessAddress = trim((string) ($business->b_address ?? ''));
        $businessCity = optional($business->fh_city)->ct_name
            ?? ($business->b_city ?? '')
            ?? '';
        $company = (object) [
            'name' => optional($business)->b_name,
            'address' => trim($businessAddress . ($businessCity ? (', ' . $businessCity) : '')),
            'pan' => optional($business)->b_pan_no,
            'tan' => optional($business)->b_tan_no
                ?? optional($business)->b_tan
                ?? optional($business)->b_tax_deduction_account_no
                ?? '',
            'email' => optional($business)->b_email,
            'phone' => optional($business)->b_phone_no,
        ];

        $data = [
            'employee' => $employee,
            'company' => $company,
            'currentDate' => now()->format('d/m/Y'),
            'assessmentYear' => $assessmentYear,
            'previousYear' => $previousYear,
            'declarationYear' => $declarationYear,
            'financialYearFrom' => optional($financialYear->fy_start_date)->format('d/m/Y'),
            'financialYearTo' => optional($financialYear->fy_end_date)->format('d/m/Y'),
            'estimatedIncome' => $estimatedIncome,
            'estimatedTax' => $estimatedTax,
        ];

        $pdf = Pdf::loadView('admin.payroll.forms.pdf.form15g', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Form_15G_' . str_replace(' ', '_', $employee->name) . '.pdf');
    }
}
