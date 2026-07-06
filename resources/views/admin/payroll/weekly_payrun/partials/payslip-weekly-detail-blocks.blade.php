@php
    $ps = $processedSalary ?? null;
    $isPdf = $payslipRenderPdf ?? false;
    $tableClass = $isPdf ? 'rounded-table' : 'table table-bordered table-sm mb-0';
    $perDayRateDisplay = $salaryMasterPerDayWage !== null && $salaryMasterPerDayWage !== ''
        ? (float) $salaryMasterPerDayWage
        : (float) ($ps->ps_per_day_salary ?? 0);
@endphp

@if($ps)
    <div class="{{ $isPdf ? '' : 'mb-3' }}" style="{{ $isPdf ? 'margin-bottom: 10px;' : '' }}">
        <table class="{{ $tableClass }}" style="{{ $isPdf ? 'width: 100%; font-size: 9px;' : 'font-size: 0.8rem;' }}">
            <thead class="{{ $isPdf ? '' : 'table-light' }}" style="{{ $isPdf ? 'background-color: #e4e4e4;' : '' }}">
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount (₹)</th>
                    <th>Description</th>
                    <th class="text-end">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Per day salary (rate)</strong></td>
                    <td class="text-end">{{ number_format($perDayRateDisplay, 2) }}</td>
                    <td><strong>Basic / prorated pay</strong></td>
                    <td class="text-end">{{ number_format((float) ($ps->ps_basic_salary ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Worked days pay</strong></td>
                    <td class="text-end">{{ number_format((float) ($ps->ps_worked_days_salary ?? 0), 2) }}</td>
                    <td><strong>Gross (earnings)</strong></td>
                    <td class="text-end">{{ number_format((float) ($ps->ps_earnings ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Employee deductions</strong></td>
                    <td class="text-end">{{ number_format((float) ($ps->ps_employee_deductions ?? 0), 2) }}</td>
                    <td><strong>Employer contributions</strong></td>
                    <td class="text-end">{{ number_format((float) ($ps->ps_employer_deductions ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>CTC (this week)</strong></td>
                    <td class="text-end">{{ number_format((float) ($ps->ps_monthly_ctc ?? 0), 2) }}</td>
                    <td><strong>Other / rem. allowance</strong></td>
                    <td class="text-end">{{ number_format((float) ($ps->ps_rem_allowance ?? 0), 2) }}</td>
                </tr>
                @if((float) ($ps->ps_tada_payed_amount ?? 0) != 0.0)
                    <tr>
                        <td colspan="2"><strong>T&amp;A / adhoc paid</strong></td>
                        <td colspan="2" class="text-end">{{ number_format((float) $ps->ps_tada_payed_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="{{ $isPdf ? 'net-pay-row' : 'table-secondary' }}">
                    <td colspan="2"><strong>Net pay (this week)</strong></td>
                    <td colspan="2" class="text-end"><strong>₹ {{ number_format((float) ($ps->ps_monthly_net_salary ?? 0), 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
@endif
