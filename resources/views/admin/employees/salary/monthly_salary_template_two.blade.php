<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay Slip</title>
    <style>
        @page {
            size: A4;
            margin: 8mm;
        }

        /* DomPDF requires explicit font configuration */
        body {
            font-family: 'DejaVu Sans', 'DejaVu Sans Condensed', 'Noto Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        /* Force all elements to use the same font family */
        * {
            font-family: 'DejaVu Sans', 'DejaVu Sans Condensed', 'Noto Sans', sans-serif;
        }

        .salary-box {
            padding: 5px 8px;

            background: #fff;
            width: 100%;
        }

        .header-logo {
            font-size: 10px;
            font-weight: bold;
            color: #333;
        }

        .header-logo span {
            color: #f39c12;
        }

        .custom-line {
            height: 1px;
            background-color: #ccc;
            margin: 5px 0;
        }

        /* Base table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        th, td {
            border: 1px solid #6c757d;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #d6d6d6;
            font-weight: 600;
        }

        /* No border tables for employee details */
        .no-border th,
        .no-border td {
            border: none;
            padding: 2px 4px;
            line-height: 1.3;
        }

        .compact-table td {
            padding: 2px 4px;
            line-height: 1.3;
        }

        .footer-info {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-top: 5px;
        }

        h3 {
            margin: 5px 0;
            font-size: 13px;
        }

        /* Section heading style */
        .table-heading {
            background-color: #d6d6d6;
            border: 1px solid #6c757d;
            padding: 5px 8px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 0 auto 8px auto;
            width: 40%;
            border-radius: 10px !important;
        }

        .footer p {
            font-size: 10px;
            margin-top: 5px;
            margin-bottom: 3px;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }

        /* Two-column layout */
        .table-row {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            gap: 15px;
        }

        .table-column {
            width: 48%;
        }

        table {
            table-layout: fixed;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            width: 100%;
        }

        .rounded-table {
            border-radius: 10px !important;
            border: 1px solid #6c757d !important;
            overflow: hidden;
            border-collapse: separate !important;
            border-spacing: 0;
        }

        .rounded-table th,
        .rounded-table td {
            border: 1px solid #6c757d !important;
        }

        /* Additional Earnings Box */
        .additional-box {
            margin: 5px 0 8px 0;
            padding: 8px 10px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border-radius: 10px !important;
        }

        .additional-title {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 6px;
            color: #2c3e50;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            color: white;
            margin-left: 5px;
            display: inline-block;
        }

        .badge-ot { background-color: #28a745; }
        .badge-adhoc { background-color: #dc3545; }
        .badge-bonus { background-color: #ffc107; color: black; }
        .badge-extra { background-color: #17a2b8; }
        .badge-recurring { background-color: #6f42c1; }
        .badge-allowance { background-color: #fd7e14; }
        .badge-other { background-color: #6c757d; }

        .total-row {
            font-weight: bold;
        }

        .net-pay-row {
            background-color: #d6d6d6;
            font-weight: bold;
        }

        .head {
            margin-bottom: 8px;
        }

        /* Employee details row spacing */
        .employee-details-row td {
            padding: 0 8px 0 0;
            vertical-align: top;
        }
        
        /* Additional spacing for inner tables */
        .no-border {
            margin-bottom: 0;
        }
        
        /* Payout summary table spacing */
        .payout-table th, .payout-table td {
            padding: 5px 6px;
        }
        
        /* Employer deduction section spacing */
        .employer-section {
            margin-top: 5px;
        }
        
        /* Consistent spacing for all sections */
        .earnings-deductions-wrapper {
            margin: 5px 0;
        }
        
        /* Logo and header spacing */
        .header-table {
            margin-bottom: 5px;
        }
        
        /* Rupee symbol specific styling */
        .rupee-symbol {
            font-family: 'DejaVu Sans', 'DejaVu Sans Condensed', sans-serif;
        }
    </style>
</head>
<body>
    <div class="salary-box">
        <!-- Header with logo - consistent spacing -->
        <table class="header-table" style="margin-bottom: 5px;">
            <thead>
                <tr>
                    <td style="width: 30%; border: none; padding: 3px 0;">
                        @if($logoPath)
                            <img src="{{ $logoPath }}" alt="Company Logo" style="width: 150px; height: auto;">
                        @endif
                    </td>
                    <td style="text-align: right; line-height: 1.4; width: 70%; border: none; padding: 3px 0;">
                        <strong>{{ $employee->fh_business->b_name }}</strong><br>
                        {{ optional($employee->fh_business->fh_admin)->emp_phone ?? '-' }}<br>
                        {{ $employee->fh_business->b_address ?? '-' }}
                    </td>
                </tr>
            </thead>
        </table>

        <!-- Divider line -->
        <div class="custom-line"></div>

        <div class="text-center">
            <div class="table-heading">Salary Slip</div>
        </div>

       @php
            // Get all earnings from ProcessedSalaryEarning
            $earnings = App\Models\ProcessedSalaryEarning::where('ps_id', $processedSalary->ps_id)->get();

            // Get all active allowance titles from SalaryAllowance table
            $allowanceTitles = App\Models\SalaryAllowance::where('sa_is_active', true)
                ->pluck('sa_title')
                ->map(function($title) {
                    return strtolower(trim($title));
                })
                ->toArray();

            $regularTitles = array_merge($allowanceTitles, ['other allowance', 'remaining']);

            $regularEarnings = $earnings->filter(function($item) use ($regularTitles) {
                $earningType = strtolower(trim($item->ps_earning_type ?? ''));
                return in_array($earningType, $regularTitles);
            });
              $displayAdditionalEarnings = collect();

            $additionalEarnings = $earnings->filter(function($item) use ($regularTitles) {
                $earningType = strtolower(trim($item->ps_earning_type ?? ''));
                return !in_array($earningType, $regularTitles);
            });

            $totalRegularEarnings = $regularEarnings->sum('ps_e_amount');
            $totalAdditionalEarnings = $additionalEarnings->sum('ps_e_amount');

            $remainingEarning = $regularEarnings->first(function($item) {
                $earningType = strtolower(trim($item->ps_earning_type ?? ''));
                return $earningType === 'remaining';
            });

            $allAdditionalEarnings = collect();
            foreach($additionalEarnings as $earning) {
                $type = 'Other';
                $description = $earning->ps_earning_type ?? 'Additional Component';
                if(str_contains(strtolower($description), 'overtime') || str_contains(strtolower($description), 'ot')) {
                    $type = 'Overtime';
                } elseif(str_contains(strtolower($description), 'adhoc')) {
                    $type = 'Adhoc';
                } elseif(str_contains(strtolower($description), 'bonus')) {
                    $type = 'Bonus';
                } elseif(str_contains(strtolower($description), 'recurring')) {
                    $type = 'Recurring';
                } elseif(str_contains(strtolower($description), 'extra')) {
                    $type = 'Extra';
                }
                $displayAdditionalEarnings->push([
                    'type' => $type,
                    'description' => $description,
                    'amount' => (float) $earning->ps_e_amount
                ]);
            }

             // Add TADA amount if it exists and is greater than 0
            if (isset($tadaPayedAmount) && $tadaPayedAmount > 0) {
                $displayAdditionalEarnings->push([
                    'type' => 'reimbursement',
                    'description' => 'TADA Reimbursement',
                    'amount' => round($tadaPayedAmount, 2)
                ]);
            }

            // Calculate total after adding TADA
            $totalAdditionalEarningsDisplay = $displayAdditionalEarnings->sum('amount');

            // Split into two equal columns
            $chunks = $displayAdditionalEarnings->chunk(ceil($displayAdditionalEarnings->count() / 2));
            $leftChunk = $chunks->get(0, collect());
            $rightChunk = $chunks->get(1, collect());
            $maxRows = max($leftChunk->count(), $rightChunk->count());

            $employeeDeductions = App\Models\ProcessedSalaryDeduction::where('ps_id', $processedSalary->ps_id)
                ->where('ps_d_category', 'employee')
                ->get();

            $employerDeductions = App\Models\ProcessedSalaryDeduction::where('ps_id', $processedSalary->ps_id)
                ->where('ps_d_category', 'employer')
                ->get();

            $totalEPFEmployer = App\Models\ProcessedSalaryDeduction::where('ps_id', $processedSalary->ps_id)
                ->where('ps_d_category', 'employer')
                ->where('ps_deduction_type_id', 351)
                ->sum('ps_d_amount');
        @endphp


        <!-- Employee Details Table - Two column layout with spacing -->
        <table class="no-border" style="margin-bottom: 5px; width: 100%;">
            <tr>
                <td style="width: 50%; border: none; padding-right: 10px; vertical-align: top;">
                    <table class="no-border compact-table" style="width: 100%;">
                        @if($payslipOptions['show_employee_code'])
                        <tr><td style="padding: 2px 4px;"><strong>Emp. Code</strong></td><td style="padding: 2px 4px;">: {{ $employee->emp_code }}</td></tr>
                        @endif
                        @if($payslipOptions['show_employee_name'])
                        <tr><td style="padding: 2px 4px;"><strong>Emp. Name</strong></td><td style="padding: 2px 4px;">: {{ $employee->emp_full_name }}</td></tr>
                        @endif
                        @if($payslipOptions['show_department'])
                        <tr><td style="padding: 2px 4px;"><strong>Department</strong></td><td style="padding: 2px 4px;">: {{ $employee->fh_department->d_name ?? '-' }}</td></tr>
                        @endif
                        @if($payslipOptions['show_designation'])
                        <tr><td style="padding: 2px 4px;"><strong>Designation</strong></td><td style="padding: 2px 4px;">: {{ $employee->fh_designation->dg_name ?? '-' }}</td></tr>
                        @endif
                        @if($payslipOptions['show_branch'])
                        <tr><td style="padding: 2px 4px;"><strong>Branch</strong></td><td style="padding: 2px 4px;">: {{ $employee->fh_branch->br_name ?? '-' }}</td></tr>
                        @endif
                        @if($payslipOptions['show_bank_details'])
                        <tr><td style="padding: 2px 4px;"><strong>Bank Name</strong></td><td style="padding: 2px 4px;">: {{ $employee->emp_bank_name ?? '-' }}</td></tr>
                        <tr><td style="padding: 2px 4px;"><strong>IFSC</strong></td><td style="padding: 2px 4px;">: {{ $employee->emp_bank_ifsc_code ?? '-' }}</td></tr>
                        <tr><td style="padding: 2px 4px;"><strong>Account No.</strong></td><td style="padding: 2px 4px;">: {{ $employee->emp_bank_account_no ?? '-' }}</td></tr>
                        @endif
                    </table>
                </td>
                <td style="width: 50%; border: none; padding-left: 10px; vertical-align: top;">
                    <table class="no-border compact-table" style="width: 100%;">
                        @if($payslipOptions['show_month'])
                        <tr><td style="padding: 2px 4px;"><strong>Month</strong></td><td style="padding: 2px 4px;">: {{ $payroll_period->pp_name ?? '-' }}</td></tr>
                        @endif
                        @if($payslipOptions['show_doj'])
                        <tr><td style="padding: 2px 4px;"><strong>Date of Joining</strong></td><td style="padding: 2px 4px;">: {{ \Carbon\Carbon::parse($employee->emp_date_of_joining)->format('d-m-Y') }}</td></tr>
                        @endif
                        @if($payslipOptions['show_month_days'])
                        <tr><td style="padding: 2px 4px;"><strong>Month Days</strong></td><td style="padding: 2px 4px;">: {{ $processedSalary->ps_total_days_in_month }}</td></tr>
                        @endif
                        @if($payslipOptions['show_salary_days'])
                        <tr><td style="padding: 2px 4px;"><strong>Salary Days</strong></td><td style="padding: 2px 4px;">: {{ $processedSalary->ps_total_days_worked }}</td></tr>
                        @endif
                        @if($payslipOptions['show_present_days'])
                        <tr><td style="padding: 2px 4px;"><strong>Present Days</strong></td><td style="padding: 2px 4px;">: {{ $processedSalary->ps_present_days }}</td></tr>
                        @endif
                        @if($payslipOptions['show_lwp_days'])
                        <tr><td style="padding: 2px 4px;"><strong>LWP</strong></td><td style="padding: 2px 4px;">: {{ $processedSalary->ps_upl_count }}</td></tr>
                        @endif
                        @if($payslipOptions['show_ip_uan'])
                        <tr><td style="padding: 2px 4px;"><strong>IP Number</strong></td><td style="padding: 2px 4px;">: {{ $employee->emp_ip_no ?? '-' }}</td></tr>
                        <tr><td style="padding: 2px 4px;"><strong>UAN</strong></td><td style="padding: 2px 4px;">: {{ $employee->emp_uan_no ?? '-' }}</td></tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <!-- Earnings & Deductions Layout - with 5px spacing around -->
        <div style="margin: 5px 0;">
            <table style="width: 100%; border: none; margin-bottom: 0;">
                <tr>
                    @if($payslipOptions['include_earnings'])
                    <td style="width: 50%; padding: 0 8px 0 0; vertical-align: top; border: none;">
                        <table class="rounded-table" style="width: 100%;">
                            <thead>
                                <tr><th style="font-size:10px; padding:5px 6px;">Earnings</th><th style="font-size:10px; padding:5px 6px;">Amount (₹)</th></tr>
                            </thead>
                            <tbody>
                                @foreach($regularEarnings as $earning)
                                <tr>
                                    <td style="padding:5px 6px; font-size:10px;">{{ $earning->ps_earning_type }}</td>
                                    <td style="padding:5px 6px; font-size:10px;">Rs. {{ number_format($earning->ps_e_amount, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td style="padding:5px 6px; font-size:10px;"><strong>Gross Earnings</strong></td>
                                    <td style="padding:5px 6px; font-size:10px;"><strong>Rs. {{ number_format($processedSalary->ps_earnings, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    @endif

                    <td style="width: 50%; padding: 0 0 0 8px; vertical-align: top; border: none;">
                        @if($payslipOptions['include_employee_deduction'])
                        <table class="rounded-table" style="width: 100%; margin-bottom: 8px;">
                            <thead>
                                <tr><th style="font-size:10px; padding:5px 6px;">Employee Deductions</th><th style="font-size:10px; padding:5px 6px;">Amount (₹)</th></tr>
                            </thead>
                            <tbody>
                                @foreach($employeeDeductions as $deduction)
                                <tr>
                                    <td style="padding:5px 6px; font-size:10px;">{{ $deduction->ps_deduction_type }}</td>
                                    <td style="padding:5px 6px; font-size:10px;">Rs. {{ number_format($deduction->ps_d_amount, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td style="padding:5px 6px;"><strong>Total</strong></td>
                                    <td style="padding:5px 6px;"><strong>Rs. {{ number_format($processedSalary->ps_employee_deductions, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                        @endif

                        @if($payslipOptions['include_employer_deduction'])
                        <table class="rounded-table" style="width: 100%;">
                            <thead>
                                <tr><th style="font-size:10px; padding:5px 6px;">Employer Deductions</th><th style="font-size:10px; padding:5px 6px;">Amount (₹)</th></tr>
                            </thead>
                            <tbody>
                                @if($totalEPFEmployer > 0)
                                <tr>
                                    <td style="padding:5px 6px; font-size:10px;">EPF - Employer Contribution</td>
                                    <td style="padding:5px 6px; font-size:10px;">Rs. {{ number_format($totalEPFEmployer, 2) }}</td>
                                </tr>
                                @endif
                                @foreach($employerDeductions as $deduction)
                                    @if($deduction->ps_deduction_type_id != 351)
                                    <tr>
                                        <td style="padding:5px 6px; font-size:10px;">{{ $deduction->ps_deduction_type }}</td>
                                        <td style="padding:5px 6px; font-size:10px;">Rs. {{ number_format($deduction->ps_d_amount, 2) }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                @if(!$employerDeductions->isEmpty())
                                <tr>
                                    <td style="padding:5px 6px;"><strong>Total Employer Deductions</strong></td>
                                    <td style="padding:5px 6px;"><strong>Rs. {{ number_format($processedSalary->ps_employer_deductions, 2) }}</strong></td>
                                </tr>
                                @else
                                <tr><td colspan="2" style="text-align:center; padding:5px;">No employer deductions</td></tr>
                                @endif
                            </tbody>
                        </table>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Additional Earnings Breakup - with ~5px spacing above/below -->
        @if($displayAdditionalEarnings->count() > 0)
        <div class="additional-box" style="margin: 8px 0 8px 0;">
            <div class="additional-title" style="margin-bottom: 8px; border-radius: 10px !important;">Additional Earnings</div>
            <table style="width: 100%; border-collapse: collapse; border-radius: 10px !important;">
                <thead>
                    <tr style="background-color: #e9ecef; border-radius: 10px !important;">
                        <th style="border: 1px solid #6c757d; padding: 5px 6px; font-size: 10px;">Description</th>
                        <th style="border: 1px solid #6c757d; padding: 5px 6px; font-size: 10px; text-align: right;">Amount (₹)</th>
                        <th style="border: 1px solid #6c757d; padding: 5px 6px; font-size: 10px;">Description</th>
                        <th style="border: 1px solid #6c757d; padding: 5px 6px; font-size: 10px; text-align: right;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalAdditionalEarnings = $displayAdditionalEarnings->sum('amount');

                        // Split into two equal columns
                        $chunks = $displayAdditionalEarnings->chunk(ceil($displayAdditionalEarnings->count() / 2));

                        $leftChunk = $chunks->get(0, collect());
                        $rightChunk = $chunks->get(1, collect());

                        $maxRows = max($leftChunk->count(), $rightChunk->count());
                    @endphp

                    @for($i = 0; $i < $maxRows; $i++)
                    <tr>
                        @if($i < $leftChunk->count())
                        <td style="border: 1px solid #6c757d; padding: 5px 6px; font-size: 10px;">{{ $leftChunk[$i]['description'] }}</td>
                        <td style="border: 1px solid #6c757d; padding: 5px 6px; font-size: 10px; text-align: right;">Rs. {{ number_format($leftChunk[$i]['amount'], 2) }}</td>
                        @else
                        <td style="border: 1px solid #6c757d; padding: 5px 6px;">&nbsp;</td>
                        <td style="border: 1px solid #6c757d; padding: 5px 6px;">&nbsp;</td>
                        @endif
                        
                        @if($i < $rightChunk->count())
                        <td style="border: 1px solid #6c757d; padding: 5px 6px; font-size: 10px;">{{ $rightChunk[$i]['description'] }}</td>
                        <td style="border: 1px solid #6c757d; padding: 5px 6px; font-size: 10px; text-align: right;">Rs. {{ number_format($rightChunk[$i]['amount'], 2) }}</td>
                        @else
                        <td style="border: 1px solid #6c757d; padding: 5px 6px;">&nbsp;</td>
                        <td style="border: 1px solid #6c757d; padding: 5px 6px;">&nbsp;</td>
                        @endif
                    </tr>
                    @endfor
                    <tr class="total-row">
                        <td colspan="2" style="border: 1px solid #6c757d; padding: 5px 6px; text-align: left;"><strong>Total</strong></td>
                        <td colspan="2" style="border: 1px solid #6c757d; padding: 5px 6px; text-align: right;"><strong>Rs. {{ number_format($totalAdditionalEarnings, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        <!-- Payout Summary - with ~5px spacing -->
        <div class="section-title text-center" style="font-size: 11px; margin: 8px 0 5px 0;"><strong>Payout Summary</strong></div>
        <table class="table table-bordered rounded-table" style="width: 100%; margin-bottom: 8px;">
            <thead style="background-color: #e4e4e4;">
                <tr>
                    <th style="padding: 5px 6px;">Description</th>
                    <th style="padding: 5px 6px;">Amount (₹)</th>
                    <th style="padding: 5px 6px;">Description</th>
                    <th style="padding: 5px 6px;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @if($payslipOptions['include_ctc'])
                    <td style="padding: 5px 6px;"><strong>Monthly CTC</strong></td>
                    <td style="padding: 5px 6px;">Rs. {{ number_format($processedSalary->ps_monthly_ctc ?? 0, 2) }}</td>
                    @else
                    <td style="padding: 5px 6px;"></td>
                    <td style="padding: 5px 6px;"></td>
                    @endif
                    <td style="padding: 5px 6px;"><strong>Employer Deductions</strong></td>
                    <td style="padding: 5px 6px;">Rs. {{ number_format($processedSalary->ps_employer_deductions, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 6px;"><strong>Monthly Gross</strong></td>
                    <td style="padding: 5px 6px;">Rs. {{ number_format($processedSalary->ps_monthly_gross, 2) }}</td>
                    <td style="padding: 5px 6px;"><strong>Employee Deductions</strong></td>
                    <td style="padding: 5px 6px;">Rs. {{ number_format($processedSalary->ps_employee_deductions, 2) }}</td>
                </tr>
                <tr class="net-pay-row">
                    <td colspan="2" style="padding: 5px 6px;"><strong>Net Pay:</strong> (₹) {{ number_format($processedSalary->ps_monthly_net_salary, 2) }}</td>
                    <td colspan="2" style="padding: 5px 6px;"><strong>In Words:</strong> {{ $net_salary_words }}</td>
                </tr>
            </tbody>
        </table>

        <div class="custom-line" style="margin: 5px 0;"></div>

        <div class="footer">
            @if($payslipOptions['show_disclaimer'] ?? true)
            <p style="font-size: 9px; margin: 5px 0 2px 0;">* This is a computer-generated slip and does not require a signature.</p>
            @endif

            @if(($payslipOptions['show_signature'] ?? true) && isset($authUser))
            <p style="margin-top: 5px; font-size: 9px;">
                Generated by: <strong>{{ explode(' ', $authUser)[0] ?? 'System' }}</strong>
                <span style="float: right;"> Print Date: {{ \Carbon\Carbon::parse($processedSalary->created_at)->format('d-M-Y') }}</span>
            </p>
            @endif
        </div>
    </div>
</body>
</html>