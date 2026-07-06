@php
    $weeklySummaryRows = collect($weeklySummary ?? []);
    $periodTotalsRow = $periodTotals ?? ['ctc' => 0, 'gross' => 0, 'emp_ded' => 0, 'empr_ded' => 0, 'net' => 0];
    $highlightWeekId = $highlightWeekId ?? null;
@endphp
<div class="payslip-preview-weekly">
    <div class="border rounded p-3 weekly-payslip-preview-panel">
        <div class="d-flex justify-content-between flex-wrap gap-2 border-bottom pb-2 mb-3">
            <div>
                <h5 class="mb-1 weekly-preview-amount">Weekly payslip preview</h5>
                <div class="text-muted small">
                    @if($week)
                        Week {{ $week->ppw_week_number }} ({{ \Carbon\Carbon::parse($week->ppw_start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($week->ppw_end_date)->format('d M Y') }})
                    @else
                        Week -
                    @endif
                </div>
            </div>
            <div class="text-end">
                <div class="fw-semibold">{{ $employee->emp_full_name ?? 'N/A' }}</div>
                <div class="small text-muted">{{ $employee->emp_code ?? 'N/A' }}</div>
            </div>
        </div>

        @if($processedSalary)
            <div class="row small mb-2 gx-3">
                <div class="col-6 col-md-4">
                    <span class="text-muted">Month Days</span>
                    <div class="fw-semibold">{{ $processedSalary->ps_total_days_in_month }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted">Salary Days</span>
                    <div class="fw-semibold">{{ $processedSalary->ps_total_days_worked }}</div>
                </div>
            </div>
        @endif

        <div class="small mb-3">
            @include('admin.payroll.weekly_payrun.partials.payslip-weekly-detail-blocks', [
                'processedSalary' => $processedSalary,
                'salaryMasterPerDayWage' => $salaryMasterPerDayWage ?? null,
                'payslipRenderPdf' => false,
            ])
        </div>

        <div class="table-responsive mb-3 small">
            <div class="fw-bold text-center mb-2">Weekly payout summary (processed)</div>
            <p class="text-center text-muted mb-2" style="font-size: 0.72rem;">Each row is one week. <strong>Salary Days</strong> = frozen attendance total for that week. Net = gross − employee deductions only.</p>
            @if($weeklySummaryRows->isEmpty())
                <p class="text-center text-muted mb-0">No week data for this payroll period.</p>
            @else
                <table class="table table-bordered table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="table-light">
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
                            <tr class="{{ $highlightWeekId && (int) ($wr['ppw_id'] ?? 0) === (int) $highlightWeekId ? 'table-info' : '' }}">
                                <td class="fw-semibold">{{ $wr['week_label'] }}</td>
                                <td class="text-end">{{ number_format((float) ($wr['salaried_days'] ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float) ($wr['gross'] ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float) ($wr['employee_ded'] ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float) ($wr['employer_ded'] ?? 0), 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format((float) ($wr['net'] ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="table-responsive mb-3 small">
            <div class="fw-bold text-center mb-2">Pay period totals (all weeks)</div>
            <p class="text-center text-muted mb-2" style="font-size: 0.72rem;">Sum of every processed week in {{ $payrollPeriod->pp_name ?? 'this payroll period' }}.</p>
            <table class="table table-bordered table-sm mb-0" style="font-size: 0.8rem;">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Amount (₹)</th>
                        <th>Description</th>
                        <th class="text-end">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold">Total CTC (all weeks)</td>
                        <td class="text-end">₹{{ number_format((float) ($periodTotalsRow['ctc'] ?? 0), 2) }}</td>
                        <td class="fw-semibold">Employer contributions (all weeks)</td>
                        <td class="text-end">₹{{ number_format((float) ($periodTotalsRow['empr_ded'] ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Total gross (all weeks)</td>
                        <td class="text-end">₹{{ number_format((float) ($periodTotalsRow['gross'] ?? 0), 2) }}</td>
                        <td class="fw-semibold">Employee deductions (all weeks)</td>
                        <td class="text-end">₹{{ number_format((float) ($periodTotalsRow['emp_ded'] ?? 0), 2) }}</td>
                    </tr>
                    <tr class="table-secondary">
                        <td colspan="2" class="fw-bold">Net Pay (period): ₹{{ number_format((float) ($periodTotalsRow['net'] ?? 0), 2) }}</td>
                        <td colspan="2" class="fw-bold small">In Words: {{ $period_net_salary_words ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="rounded p-3 mb-3 weekly-payslip-preview-net">
            <div class="small text-muted mb-1">Net pay credited (this week &mdash; after employee deductions)</div>
            <div class="h5 mb-0 weekly-preview-amount">₹{{ number_format((float) ($processedSalary->ps_monthly_net_salary ?? 0), 2) }}</div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('payroll.weekly.downloadPayslip', ['id' => $processedSalary->ps_id]) }}"
               class="btn btn-primary">
                <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                Download PDF
            </a>
        </div>
    </div>
</div>
