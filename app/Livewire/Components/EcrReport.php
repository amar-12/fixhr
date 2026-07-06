<?php

namespace App\Livewire\Components;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ProcessedSalaryEarning;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;

class EcrReport extends Component
{
    public $payrollId;
    public $payrollPeriod;
    public $isGenerating = false;
    public $message = '';
    public $totalEmployees = 0;
    public $businessId;

    // Debug properties
    public $debugInfo = '';
    public $showDebug = false;

    public function mount($payrollId)
    {
        $this->payrollId = $payrollId;
        $this->businessId = Auth::user()->emp_b_id ?? 1;

        // Get payroll period
        $this->payrollPeriod = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_id', $payrollId)
            ->first();

        $this->loadStatistics();
    }

    /**
     * Load PF statistics
     */
    public function loadStatistics()
    {
        try {
            $this->totalEmployees = Employee::where('emp_b_id', $this->businessId)
                ->where('emp_is_pf_enabled', 120)
                ->whereNotNull('emp_pf_no')
                ->where('emp_pf_no', '!=', '')
                ->whereHas('processedSalaries', function($query) {
                    $query->where('ps_payroll_id', $this->payrollId);
                })
                ->count();

            Log::info('ECR Report Statistics:', [
                'payroll_id' => $this->payrollId,
                'business_id' => $this->businessId,
                'total_pf_employees' => $this->totalEmployees
            ]);

        } catch (\Exception $e) {
            $this->message = 'Error loading statistics: ' . $e->getMessage();
            Log::error('Error in loadStatistics:', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate and download PF report in ECR format
     */
    public function generateReport()
    {
        try {
            $this->isGenerating = true;
            $this->message = 'Generating ECR report...';

            if (!$this->payrollPeriod) {
                throw new \Exception('Payroll period not found.');
            }

            if ($this->totalEmployees === 0) {
                throw new \Exception('No employees with PF data found for this payroll period.');
            }

            // Method 1: Try main method
            $ecrData = $this->fetchEcrDataMethod1();

            // Method 2: If no data, try alternative
            if ($ecrData->isEmpty()) {
                $ecrData = $this->fetchEcrDataMethod2();
            }

            // Method 3: If still no data, try simplified method
            if ($ecrData->isEmpty()) {
                $ecrData = $this->fetchEcrDataMethod3();
            }

            if ($ecrData->isEmpty()) {
                throw new \Exception('No ECR data available for download.');
            }

            // Create Excel file
            $fileName = $this->generateFileName();
            $filePath = $this->generateExcelFile($ecrData, $fileName);

            $this->message = 'ECR report generated successfully! Downloaded: ' . $fileName;
            $this->isGenerating = false;

            // Return download response
            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->isGenerating = false;
            Log::error('Error generating ECR report:', [
                'error' => $e->getMessage(),
                'payroll_id' => $this->payrollId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Method 1: Using ProcessedEmployeeSalary model with relations
     */
    private function fetchEcrDataMethod1()
    {
        try {
            Log::info('Fetching ECR data using Method 1...');

            $processedSalaries = ProcessedEmployeeSalary::with([
                    'employee',
                    'deductions' => function($q) {
                        $q->where('ps_deduction_type_id', 351); // PF deductions
                    },
                    'earnings' => function($q) {
                        $q->whereIn('ps_earning_type_id', [360, 362]); // Basic and DA
                    }
                ])
                ->where('ps_payroll_id', $this->payrollId)
                ->where('ps_b_id', $this->businessId)
                ->whereHas('employee', function($q) {
                    $q->where('emp_is_pf_enabled', 120)
                      ->whereNotNull('emp_pf_no')
                      ->where('emp_pf_no', '!=', '');
                })
                ->get();

            $result = collect([]);

            foreach ($processedSalaries as $ps) {
                if (!$ps->employee) continue;

                $basic = $ps->earnings->where('ps_earning_type_id', 360)->sum('ps_e_amount') ?? 0;
                $da    = $ps->earnings->where('ps_earning_type_id', 362)->sum('ps_e_amount') ?? 0;

                $pfWages = $basic + $da;
                $pfWagesCapped = min($pfWages, 15000);

                // =============================
                // Employee Contribution (12%)
                // =============================
                $employeePf = round($pfWagesCapped * 0.12, 2);

                // =============================
                // Employer Contribution Split
                // =============================
                $eps = round(min($pfWagesCapped * 0.0833, 1250), 2);
                $employerPf = round($employeePf - $eps, 2);

                // =============================
                // EDLI + Admin
                // =============================
                $edli = round($pfWagesCapped * 0.005, 2);
                $admin = round($pfWagesCapped * 0.015, 2);

                // =============================
                // NCP Days
                // =============================
                $ncpDays = 0;
                if ($ps->ps_total_days_in_month && $ps->ps_total_days_worked) {
                    $ncpDays = max(0, $ps->ps_total_days_in_month - $ps->ps_total_days_worked);
                }


                // $ecrRecord = (object)[
                //     'UAN' => $ps->employee->emp_pf_no,
                //     'NAME' => $ps->employee->emp_full_name,
                //     'GROSS_WAGES' => round($ps->ps_monthly_gross ?? 0, 2),
                //     'EPF_WAGES' => round($ps->ps_monthly_gross ?? 0, 2),
                //     'EPS_WAGES' => round($epsWages, 2),
                //     'EDLI_WAGES' => round($edliWages, 2),
                //     'EPF_CONTRIBUTION_REMITTED' => round($employeePf + $employerPf, 2),
                //     'EPS_CONTRIBUTION_REMITTED' => round($epsContribution, 2),
                //     'DIFF_EPF_EPS_CONTRIBUTION_REMITTED' => round(($employeePf + $employerPf) - $epsContribution, 2),
                //     'NCP_DAYS' => max(0, $ncpDays),
                //     'REFUND_OF_ADVANCES' => 0.00
                // ];

                $ecrRecord = (object)[
                    'UAN' => $ps->employee->emp_pf_no,
                    'NAME' => $ps->employee->emp_full_name,
                    'GROSS_WAGES' => round($ps->ps_monthly_gross ?? 0, 2),
                    'EPF_WAGES' => round($pfWages, 2),
                    'EPS_WAGES' => round($pfWagesCapped, 2),
                    'EDLI_WAGES' => round($pfWagesCapped, 2),
                    'EPF_CONTRIBUTION_REMITTED' => round($employeePf + $employerPf, 2),
                    'EPS_CONTRIBUTION_REMITTED' => $eps,
                    'DIFF_EPF_EPS_CONTRIBUTION_REMITTED' => round(($employeePf + $employerPf) - $eps, 2),
                    'NCP_DAYS' => $ncpDays,
                    'REFUND_OF_ADVANCES' => 0.00
                ];

                $result->push($ecrRecord);
            }

            Log::info('Method 1 fetched data count:', ['ecrRecord' => $ecrRecord, 'count' => $result->count()]);
            return $result;

        } catch (\Exception $e) {
            Log::error('Error in Method 1:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    /**
     * Method 2: Using direct DB queries (more efficient)
     */
    private function fetchEcrDataMethod2()
    {
        try {
            Log::info('Fetching ECR data using Method 2...');

            return DB::table('processed_salaries as ps')
                ->join('employees as e', 'e.emp_id', '=', 'ps.ps_emp_id')
                ->leftJoin('processed_salary_earnings as basic', function($join) {
                    $join->on('basic.ps_id', '=', 'ps.ps_id')
                         ->where('basic.ps_earning_type_id', 360); // Basic
                })
                ->leftJoin('processed_salary_earnings as da', function($join) {
                    $join->on('da.ps_id', '=', 'ps.ps_id')
                         ->where('da.ps_earning_type_id', 362); // DA
                })
                ->leftJoin('processed_salary_deductions as emp_pf', function($join) {
                    $join->on('emp_pf.ps_id', '=', 'ps.ps_id')
                         ->where('emp_pf.ps_deduction_type_id', 351)
                         ->where('emp_pf.ps_d_category', 'employee');
                })
                ->leftJoin('processed_salary_deductions as emp_eps', function($join) {
                    $join->on('emp_eps.ps_id', '=', 'ps.ps_id')
                         ->where('emp_eps.ps_deduction_type_id', 351)
                         ->where('emp_eps.ps_d_category', 'employer')
                         ->where('emp_eps.ps_deduction_type', 'like', '%EPS%');
                })
                ->leftJoin('processed_salary_deductions as emp_epf', function($join) {
                    $join->on('emp_epf.ps_id', '=', 'ps.ps_id')
                         ->where('emp_epf.ps_deduction_type_id', 351)
                         ->where('emp_epf.ps_d_category', 'employer')
                         ->where('emp_epf.ps_deduction_type', 'not like', '%EPS%');
                })
                ->where('ps.ps_payroll_id', $this->payrollId)
                ->where('ps.ps_b_id', $this->businessId)
                ->where('e.emp_is_pf_enabled', 120)
                ->whereNotNull('e.emp_pf_no')
                ->where('e.emp_pf_no', '!=', '')
                ->select(
                    'e.emp_pf_no as UAN',
                    'e.emp_full_name as NAME',
                    DB::raw('COALESCE(ROUND(ps.ps_monthly_gross, 2), 0) as GROSS_WAGES'),
                    DB::raw('ROUND(
                        COALESCE(basic.ps_e_amount, 0) +
                        COALESCE(da.ps_e_amount, 0), 2
                    ) as EPF_WAGES'),
                    DB::raw('ROUND(
                        CASE
                            WHEN COALESCE(basic.ps_e_amount, 0) > 15000 THEN 15000
                            ELSE COALESCE(basic.ps_e_amount, 0)
                        END, 2
                    ) as EPS_WAGES'),
                    DB::raw('ROUND(
                        CASE
                            WHEN (COALESCE(basic.ps_e_amount, 0) + COALESCE(da.ps_e_amount, 0)) > 15000 THEN 15000
                            ELSE (COALESCE(basic.ps_e_amount, 0) + COALESCE(da.ps_e_amount, 0))
                        END, 2
                    ) as EDLI_WAGES'),
                    DB::raw('ROUND(
                        COALESCE(emp_pf.ps_d_amount, 0) +
                        COALESCE(emp_epf.ps_d_amount, 0), 2
                    ) as EPF_CONTRIBUTION_REMITTED'),
                    DB::raw('COALESCE(ROUND(emp_eps.ps_d_amount, 2), 0) as EPS_CONTRIBUTION_REMITTED'),
                    DB::raw('ROUND(
                        (COALESCE(emp_pf.ps_d_amount, 0) + COALESCE(emp_epf.ps_d_amount, 0)) -
                        COALESCE(emp_eps.ps_d_amount, 0), 2
                    ) as DIFF_EPF_EPS_CONTRIBUTION_REMITTED'),
                    DB::raw('COALESCE(
                        ps.ps_total_days_in_month - ps.ps_total_days_worked, 0
                    ) as NCP_DAYS'),
                    DB::raw('0.00 as REFUND_OF_ADVANCES')
                )
                ->orderBy('e.emp_full_name')
                ->get();

        } catch (\Exception $e) {
            Log::error('Error in Method 2:', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    /**
     * Method 3: Simplified method using processed_salaries table only
     */
    private function fetchEcrDataMethod3()
    {
        try {
            Log::info('Fetching ECR data using Method 3...');

            return DB::table('processed_salaries as ps')
                ->join('employees as e', 'e.emp_id', '=', 'ps.ps_emp_id')
                ->where('ps.ps_payroll_id', $this->payrollId)
                ->where('ps.ps_b_id', $this->businessId)
                ->where('e.emp_is_pf_enabled', 120)
                ->whereNotNull('e.emp_pf_no')
                ->where('e.emp_pf_no', '!=', '')
                ->select(
                    'e.emp_pf_no as UAN',
                    'e.emp_full_name as NAME',
                    DB::raw('COALESCE(ROUND(ps.ps_monthly_gross, 2), 0) as GROSS_WAGES'),
                    DB::raw('COALESCE(ROUND(ps.ps_basic_salary, 2), 0) as EPF_WAGES'),
                    DB::raw('CASE
                        WHEN COALESCE(ps.ps_basic_salary, 0) > 15000 THEN 15000
                        ELSE COALESCE(ROUND(ps.ps_basic_salary, 2), 0)
                    END as EPS_WAGES'),
                    DB::raw('CASE
                        WHEN COALESCE(ps.ps_basic_salary, 0) > 15000 THEN 15000
                        ELSE COALESCE(ROUND(ps.ps_basic_salary, 2), 0)
                    END as EDLI_WAGES'),
                    DB::raw('COALESCE(ROUND(ps.ps_employee_deductions, 2), 0) as EPF_CONTRIBUTION_REMITTED'),
                    DB::raw('CASE
                        WHEN COALESCE(ps.ps_basic_salary, 0) > 15000 THEN 1250
                        ELSE COALESCE(ROUND(ps.ps_basic_salary * 0.0833, 2), 0)
                    END as EPS_CONTRIBUTION_REMITTED'),
                    DB::raw('COALESCE(ROUND(
                        ps.ps_employee_deductions -
                        CASE
                            WHEN COALESCE(ps.ps_basic_salary, 0) > 15000 THEN 1250
                            ELSE COALESCE(ps.ps_basic_salary * 0.0833, 0)
                        END, 2
                    ), 0) as DIFF_EPF_EPS_CONTRIBUTION_REMITTED'),
                    DB::raw('COALESCE(
                        ps.ps_total_days_in_month - ps.ps_total_days_worked, 0
                    ) as NCP_DAYS'),
                    DB::raw('0.00 as REFUND_OF_ADVANCES')
                )
                ->orderBy('e.emp_full_name')
                ->get();

        } catch (\Exception $e) {
            Log::error('Error in Method 3:', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    /**
     * Generate Excel file using PhpSpreadsheet
     */
    private function generateExcelFile($data, $fileName)
    {
        // Create temporary file path
        $tempPath = storage_path('app/temp/' . $fileName);

        // Ensure temp directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Create new Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setTitle('ECR Report');

        // Set headers with proper ECR format
        $headers = [
            'UAN',
            'NAME',
            'GROSS_WAGES',
            'EPF_WAGES',
            'EPS_WAGES',
            'EDLI_WAGES',
            'EPF_CONTRIBUTION_REMITTED',
            'EPS_CONTRIBUTION_REMITTED',
            'DIFF_EPF_EPS_CONTRIBUTION_REMITTED',
            'NCP_DAYS',
            'REFUND_OF_ADVANCES'
        ];

        // Write headers
        $sheet->fromArray([$headers], null, 'A1');

        // Apply header styles
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'E8F4FD']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ];

        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

        // Write data
        $row = 2;
        foreach ($data as $record) {
            $sheet->fromArray([
                [
                    $record->UAN ?? '',
                    $record->NAME ?? '',
                    $record->GROSS_WAGES ?? 0,
                    $record->EPF_WAGES ?? 0,
                    $record->EPS_WAGES ?? 0,
                    $record->EDLI_WAGES ?? 0,
                    $record->EPF_CONTRIBUTION_REMITTED ?? 0,
                    $record->EPS_CONTRIBUTION_REMITTED ?? 0,
                    $record->DIFF_EPF_EPS_CONTRIBUTION_REMITTED ?? 0,
                    $record->NCP_DAYS ?? 0,
                    $record->REFUND_OF_ADVANCES ?? 0
                ]
            ], null, 'A' . $row);

            // Format number columns
            foreach (['C', 'D', 'E', 'F', 'G', 'H', 'I', 'K'] as $col) {
                $sheet->getStyle($col . $row)->getNumberFormat()
                      ->setFormatCode('#,##0.00');
            }

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Apply data borders
        $lastRow = max(1, count($data) + 1);
        if ($lastRow > 1) {
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'EEEEEE']
                    ]
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ];
            $sheet->getStyle('A2:K' . $lastRow)->applyFromArray($dataStyle);
        }

        // Add summary info
        $summaryRow = $lastRow + 2;
        $sheet->setCellValue('A' . $summaryRow, 'ECR Report Summary');
        $sheet->mergeCells('A' . $summaryRow . ':K' . $summaryRow);
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);

        $sheet->setCellValue('A' . ($summaryRow + 1), 'Payroll Period:');
        $sheet->setCellValue('B' . ($summaryRow + 1), $this->payrollPeriod->pp_name ?? 'N/A');

        $sheet->setCellValue('A' . ($summaryRow + 2), 'Total Employees:');
        $sheet->setCellValue('B' . ($summaryRow + 2), $this->totalEmployees);

        $sheet->setCellValue('A' . ($summaryRow + 3), 'Generated On:');
        $sheet->setCellValue('B' . ($summaryRow + 3), now()->format('d-M-Y H:i:s'));

        $sheet->setCellValue('A' . ($summaryRow + 4), 'Generated By:');
        $sheet->setCellValue('B' . ($summaryRow + 4), Auth::user()->emp_full_name ?? 'System');

        // Save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Generate filename for ECR report
     */
    private function generateFileName()
    {
        $periodName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->payrollPeriod->pp_name ?? 'Unknown_Period');
        $businessName = preg_replace('/[^a-zA-Z0-9_-]/', '_', Auth::user()->fh_business->b_name ?? 'Business');
        return "ECR_REPORT_{$businessName}_{$periodName}_" . now()->format('Ymd_His') . '.xlsx';
    }

    /**
     * Debug function to test data fetching
     */
    public function debugDataFetch()
    {
        try {
            $methods = [
                'Method 1 (Model)' => $this->fetchEcrDataMethod1(),
                'Method 2 (DB Query)' => $this->fetchEcrDataMethod2(),
                'Method 3 (Simplified)' => $this->fetchEcrDataMethod3()
            ];

            $debugInfo = "=== ECR Data Fetch Debug ===\n\n";
            $debugInfo .= "Payroll ID: {$this->payrollId}\n";
            $debugInfo .= "Business ID: {$this->businessId}\n";
            $debugInfo .= "Total PF Employees: {$this->totalEmployees}\n\n";

            foreach ($methods as $name => $data) {
                $debugInfo .= "{$name}:\n";
                $debugInfo .= "  Count: " . $data->count() . "\n";
                if ($data->count() > 0) {
                    $firstRecord = $data->first();
                    $debugInfo .= "  Sample Record:\n";
                    foreach ($firstRecord as $key => $value) {
                        $debugInfo .= "    {$key}: {$value}\n";
                    }
                } else {
                    $debugInfo .= "  No data found\n";
                }
                $debugInfo .= "\n";
            }

            $this->debugInfo = $debugInfo;
            $this->showDebug = true;

        } catch (\Exception $e) {
            $this->debugInfo = "Debug Error: " . $e->getMessage();
            $this->showDebug = true;
        }
    }

    /**
     * Close debug panel
     */
    public function closeDebug()
    {
        $this->showDebug = false;
        $this->debugInfo = '';
    }

    /**
     * Refresh component data
     */
    public function refreshData()
    {
        $this->loadStatistics();
        $this->message = '';
        $this->showDebug = false;
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.components.ecr-report', [
            'totalEmployees' => $this->totalEmployees,
            'isGenerating' => $this->isGenerating,
            'message' => $this->message,
            'payrollPeriod' => $this->payrollPeriod,
            'debugInfo' => $this->debugInfo,
            'showDebug' => $this->showDebug
        ]);
    }
}
