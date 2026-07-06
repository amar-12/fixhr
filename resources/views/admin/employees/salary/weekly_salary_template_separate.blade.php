<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Salary Slip</title>
    <style>
        @page {
            size: A4;
            margin: 8mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .salary-box {
            padding: 8px;
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
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th,
        td {
            border: 1px solid #6c757d;
            padding: 4px 5px;
            text-align: left;
        }

        th {
            background-color: #d6d6d6;
        }

        .no-border th,
        .no-border td {
            border: none;
            padding: 1px 3px;
            line-height: 1.2;
        }

        .compact-table td {
            padding: 1px 3px;
            line-height: 1.2;
        }

        .footer-info {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-top: 6px;
        }

        h3 {
            margin: 3px 0;
            font-size: 13px;
        }

        .table-heading {
            background-color: #d6d6d6;
            border: 1px solid #6c757d;
            padding: 5px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 0 auto 8px auto;
            width: 40%;
            border-radius: 10px !important;
            border: 1px solid #6c757d !important;
        }

        .footer p {
            font-size: 12.5px;
            margin-top: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .table-row {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            gap: 20px;
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

        th,
        td {
            padding: 6px 8px;
            border: 1px solid #000;
            text-align: left;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 12px;
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

        /* Additional Earnings Styles */
        .additional-box {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #6c757d;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .additional-title {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            color: #333;
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

        .badge-ot {
            background-color: #28a745;
        }

        .badge-adhoc {
            background-color: #dc3545;
        }

        .badge-bonus {
            background-color: #ffc107;
            color: black;
        }

        .badge-extra {
            background-color: #17a2b8;
        }

        .badge-recurring {
            background-color: #6f42c1;
        }

        .badge-allowance {
            background-color: #fd7e14;
        }

        .badge-other {
            background-color: #6c757d;
        }

        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .net-pay-row {
            background-color: #d6d6d6;
            font-weight: bold;
        }

        .head {
            margin-bottom: 10px;
        }

        .matrix-table {
            font-size: 7px;
        }

        .matrix-table th,
        .matrix-table td {
            padding: 2px 3px;
        }
    </style>

</head>

<body>
    <div class="salary-box">
        <!-- Header -->
        <table>
            <thead>
                <tr>
                    <td style="width: 30%; border: none;">
                        @if(!empty($logoPath))
                            <img src="{{ $logoPath }}" alt="Company Logo" style="width: 150px; height: auto;">
                        @else
                            <span class="header-logo">{{ $employee->fh_business->b_name ?? 'Company' }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; line-height: 1.4; width: 70%; border: none;">
                        <strong>{{ $employee->fh_business->b_name }}</strong><br>
                        {{ optional($employee->fh_business->fh_admin)->emp_phone ?? '-' }}<br>
                        {{ $employee->fh_business->b_address ?? '-' }}
                    </td>
                </tr>
            </thead>
        </table>

        <!-- Line below header -->
        <div class="custom-line" style="border-top: 1px solid #ccc; margin: 0 30px 10px 30px;"></div>

        <div class="text-center">
            <div class="table-heading">Salary Slip (Weekly)</div>
            @if(!empty($week))
                <div style="font-size: 9px; margin: -4px auto 8px; color: #333;">
                    Week {{ $week->ppw_week_number }} &mdash;
                    {{ \Carbon\Carbon::parse($week->ppw_start_date)->format('d M') }}
                    &ndash; {{ \Carbon\Carbon::parse($week->ppw_end_date)->format('d M Y') }}
                    &middot; {{ $payrollPeriod->pp_name ?? '' }}
                </div>
            @endif
            @php
                $matrixHeaders = $weekly_matrix_week_headers ?? [];
                $earningMatrix = $weekly_earning_rows ?? [];
                $deductionMatrix = $weekly_deduction_rows ?? [];
            @endphp
            @if(count($matrixHeaders) > 0)
                <div style="font-size: 8px; margin: -2px auto 10px; color: #444;">
                    Combined view for <strong>{{ $payrollPeriod->pp_name ?? 'this pay period' }}</strong> &mdash; all weeks with earnings and employee deductions by week.
                </div>
            @endif
        </div>

        <!-- Employee Details Table -->
        <table class="no-border" style="margin-bottom: 0; padding-bottom: 0;">
            <tr>
                <td>
                    <table class="no-border compact-table" style="padding-bottom: 0; margin-bottom: 0;">
                        @if($payslipOptions['show_employee_code'])
                        <tr>
                            <td><strong>Emp. Code</strong></td>
                            <td>: {{ $employee->emp_code }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_employee_name'])
                        <tr>
                            <td><strong>Emp. Name</strong></td>
                            <td>: {{ $employee->emp_full_name }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_department'])
                        <tr>
                            <td><strong>Department</strong></td>
                            <td>: {{ $employee->fh_department->d_name ?? '-' }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_designation'])
                        <tr>
                            <td><strong>Designation</strong></td>
                            <td>: {{ $employee->fh_designation->dg_name ?? '-' }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_branch'])
                        <tr>
                            <td><strong>Branch</strong></td>
                            <td>: {{ $employee->fh_branch->br_name ?? '-' }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_bank_details'])
                        <tr>
                            <td><strong>Bank Name</strong></td>
                            <td>: {{ $employee->emp_bank_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>IFSC</strong></td>
                            <td>: {{ $employee->emp_bank_ifsc_code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Account No.</strong></td>
                            <td>: {{ $employee->emp_bank_account_no ?? '-' }}</td>
                        </tr>
                        @endif
                    </table>
                </td>

                <td>
                    <table class="no-border">
                        @if($payslipOptions['show_month'])
                        <tr>
                            <td><strong>Payroll period</strong></td>
                            <td>: {{ $payrollPeriod->pp_name ?? '-' }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_doj'])
                        <tr>
                            <td><strong>Date of Joining</strong></td>
                            <td>: {{ \Carbon\Carbon::parse($employee->emp_date_of_joining)->format('d-m-Y') }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_month_days'])
                        <tr>
                            <td><strong>Month Days</strong></td>
                            <td>: {{ $processedSalary->ps_total_days_in_month }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_salary_days'])
                        <tr>
                            <td><strong>Salary Days</strong></td>
                            <td>: {{ $processedSalary->ps_total_days_worked }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_present_days'])
                        <tr>
                            <td><strong>Present Days</strong></td>
                            <td>: {{ $processedSalary->ps_present_days }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_lwp_days'])
                        <tr>
                            <td><strong>LWP</strong></td>
                            <td>: {{ $processedSalary->ps_upl_count }}</td>
                        </tr>
                        @endif

                        @if($payslipOptions['show_ip_uan'])
                        <tr>
                            <td><strong>IP Number</strong></td>
                            <td>: {{ $employee->emp_ip_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>UAN</strong></td>
                            <td>: {{ $employee->emp_uan_no ?? '-' }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        @include('admin.payroll.weekly_payrun.partials.payslip-weekly-detail-blocks', [
            'processedSalary' => $processedSalary,
            'salaryMasterPerDayWage' => $salaryMasterPerDayWage ?? null,
            'payslipRenderPdf' => true,
        ])

        @php
            $weeklySummaryRows = collect($weeklySummary ?? []);
            $periodTotalsRow = $periodTotals ?? ['ctc' => 0, 'gross' => 0, 'emp_ded' => 0, 'empr_ded' => 0, 'net' => 0];
            $highlightWeekId = $highlightWeekId ?? null;
        @endphp

        <div class="section-title text-center" style="font-size: 11px; margin-top: 8px;"><strong>Weekly payout summary (processed)</strong></div>
        <p class="text-center" style="font-size: 8px; margin: 0 0 6px; color: #555;">Salary Days = frozen attendance total per week; net = gross − employee deductions (employer column is informational).</p>
        @if($weeklySummaryRows->isEmpty())
            <p class="text-center" style="font-size: 10px;">No week configuration found for this payroll period.</p>
        @else
            <table class="rounded-table" style="width: 100%; font-size: 9px; margin-bottom: 10px;">
                <thead style="background-color: #e4e4e4;">
                    <tr>
                        <th>Pay period</th>
                        <th class="text-end">Salary Days</th>
                        <th class="text-end">Gross (₹)</th>
                        <th class="text-end">Emp. ded. (₹)</th>
                        <th class="text-end">Empr. (₹)</th>
                        <th class="text-end">Net (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeklySummaryRows as $wr)
                        @php $isThisWeek = $highlightWeekId && (int) ($wr['ppw_id'] ?? 0) === (int) $highlightWeekId; @endphp
                        <tr style="{{ $isThisWeek ? 'background-color: #f0f9ff;' : '' }}">
                            <td style="{{ $isThisWeek ? 'font-weight: bold;' : '' }}">{{ $wr['week_label'] }}</td>
                            <td class="text-end">{{ number_format((float) ($wr['salaried_days'] ?? 0), 2) }}</td>
                            <td class="text-end">{{ number_format((float) ($wr['gross'] ?? 0), 2) }}</td>
                            <td class="text-end">{{ number_format((float) ($wr['employee_ded'] ?? 0), 2) }}</td>
                            <td class="text-end">{{ number_format((float) ($wr['employer_ded'] ?? 0), 2) }}</td>
                            <td class="text-end"><strong>{{ number_format((float) ($wr['net'] ?? 0), 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(($payslipOptions['include_earnings'] ?? true) && count($matrixHeaders) > 0 && count($earningMatrix) > 0)
            <div class="section-title text-center" style="font-size: 11px; margin-top: 8px;"><strong>Earnings by week (detail)</strong></div>
            <p class="text-center" style="font-size: 7px; margin: 0 0 4px; color: #555;">Processed earning lines per week; period total in last column.</p>
            <table class="rounded-table matrix-table" style="width: 100%; margin-bottom: 10px;">
                <thead style="background-color: #e4e4e4;">
                    <tr>
                        <th>Description</th>
                        @foreach($matrixHeaders as $h)
                            <th class="text-end">{{ $h }}</th>
                        @endforeach
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($earningMatrix as $row)
                        <tr>
                            <td>{{ $row['description'] ?? '' }}</td>
                            @foreach($row['amounts'] ?? [] as $amt)
                                <td class="text-end">{{ number_format((float) $amt, 2) }}</td>
                            @endforeach
                            <td class="text-end"><strong>{{ number_format((float) ($row['total'] ?? 0), 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(($payslipOptions['include_employee_deduction'] ?? true) && count($matrixHeaders) > 0 && count($deductionMatrix) > 0)
            <div class="section-title text-center" style="font-size: 11px; margin-top: 6px;"><strong>Employee deductions by week (detail)</strong></div>
            <p class="text-center" style="font-size: 7px; margin: 0 0 4px; color: #555;">Employee deduction lines per processed week; period total in last column.</p>
            <table class="rounded-table matrix-table" style="width: 100%; margin-bottom: 10px;">
                <thead style="background-color: #e4e4e4;">
                    <tr>
                        <th>Description</th>
                        @foreach($matrixHeaders as $h)
                            <th class="text-end">{{ $h }}</th>
                        @endforeach
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deductionMatrix as $row)
                        <tr>
                            <td>{{ $row['description'] ?? '' }}</td>
                            @foreach($row['amounts'] ?? [] as $amt)
                                <td class="text-end">{{ number_format((float) $amt, 2) }}</td>
                            @endforeach
                            <td class="text-end"><strong>{{ number_format((float) ($row['total'] ?? 0), 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="section-title text-center" style="font-size: 11px; margin-top: 6px;"><strong>Pay period totals (all weeks)</strong></div>
        <p class="text-center" style="font-size: 8px; margin: 0 0 6px; color: #555;">Sum of every processed week in this payroll period.</p>
        <table class="rounded-table" style="width: 100%; font-size: 9px; margin-bottom: 10px;">
            <thead style="background-color: #e4e4e4;">
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount (₹)</th>
                    <th>Description</th>
                    <th class="text-end">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @if($payslipOptions['include_ctc'] ?? true)
                    <td><strong>Total CTC (all weeks)</strong></td>
                    <td class="text-end">₹ {{ number_format((float) ($periodTotalsRow['ctc'] ?? 0), 2) }}</td>
                    @else
                    <td></td>
                    <td class="text-end"></td>
                    @endif
                    <td><strong>Employer contributions (all weeks)</strong></td>
                    <td class="text-end">₹ {{ number_format((float) ($periodTotalsRow['empr_ded'] ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total gross (all weeks)</strong></td>
                    <td class="text-end">₹ {{ number_format((float) ($periodTotalsRow['gross'] ?? 0), 2) }}</td>
                    <td><strong>Employee deductions (all weeks)</strong></td>
                    <td class="text-end">₹ {{ number_format((float) ($periodTotalsRow['emp_ded'] ?? 0), 2) }}</td>
                </tr>
                <tr class="net-pay-row">
                    <td colspan="2"><strong>Net Pay (period):</strong> ₹ {{ number_format((float) ($periodTotalsRow['net'] ?? 0), 2) }}</td>
                    <td colspan="2"><strong>In Words:</strong>
                        @if($payslipOptions['show_net_salary_words'] ?? true)
                            {{ $period_net_salary_words ?? ($net_salary_words ?? '') }}
                        @else
                            &mdash;
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="section-title text-center" style="font-size: 11px; margin-top: 6px;"><strong>Net pay (selected week)</strong></div>
        <table class="rounded-table" style="width: 100%; font-size: 9px; margin-bottom: 12px;">
            <tbody>
                <tr class="net-pay-row">
                    <td colspan="2"><strong>Net pay credited (after employee deductions):</strong> ₹ {{ number_format((float) ($processedSalary->ps_monthly_net_salary ?? 0), 2) }}</td>
                    <td colspan="2"><strong>In Words:</strong>
                        @if($payslipOptions['show_net_salary_words'] ?? true)
                            {{ $net_salary_words ?? '' }}
                        @else
                            &mdash;
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="custom-line"></div>

        <div class="footer">
            @if($payslipOptions['show_disclaimer'] ?? true)
            <p>* This is a computer-generated slip and does not require a signature.</p>
            @endif

            @if(($payslipOptions['show_signature'] ?? true) && isset($authUser))
            <p style="margin-top: 5px;">
                Generated by: <strong>{{ explode(' ', $authUser)[0] ?? 'System' }}</strong>
                <span style="float: right;"> Print Date: {{
                    \Carbon\Carbon::parse($processedSalary->created_at)->format('d-M-Y') }}</span>
            </p>
            @endif
        </div>
    </div>
</body>

</html>
