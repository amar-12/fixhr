{{--
    Weekly payroll master: pms_payroll_cycle === 441 (see WeeklyPayrunController::isWeeklyPayslipUiEnabled).

    @param \App\Models\PayrollPeriod $payrollPeriod
    @param bool $isWeeklyPayrollMode
    @param string $layout "verification" | "payslip_scroll" | "payslip_grid"
    @param \App\Models\PayrollPeriodWeek|null $week Required for week-scoped weekly reports
--}}
@php
    $pid = $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0);
    $useWeeklyReports = $isWeeklyPayrollMode && isset($week) && $week;
@endphp

@if ($layout === 'verification')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
        @if ($useWeeklyReports)
            @livewire('weekly-payroll.bank-sheet-report', [
                'payrollId' => $pid,
                'weekId' => $week->ppw_id,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer;',
            ])
            @livewire('weekly-payroll.payroll-register-report', [
                'payrollId' => $pid,
                'weekId' => $week->ppw_id,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer;',
            ])
            @livewire('weekly-payroll.p-f-e-p-f-report', [
                'payrollId' => $pid,
                'weekId' => $week->ppw_id,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer;',
            ])
            @livewire('weekly-payroll.e-s-i-c-report', [
                'payrollId' => $pid,
                'weekId' => $week->ppw_id,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer;',
            ])
            @livewire('weekly-payroll.letter-head', [
                'payrollId' => $pid,
                'weekId' => $week->ppw_id,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer;',
            ])
            @livewire('weekly-payroll.consolidated-payroll-report', [
                'payrollId' => $pid,
                'weekId' => $week->ppw_id,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer;',
            ])
        @else
            @livewire('components.bank-sheet-report', [
                'payrollId' => $pid,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
            ])
            @livewire('components.payroll-register-report', [
                'payrollId' => $pid,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
            ])
            @livewire('components.p-f-e-p-f-report', [
                'payrollId' => $pid,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
            ])
            @livewire('components.e-s-i-c-report', [
                'payrollId' => $pid,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
            ])
            @livewire('components.letter-head', [
                'payrollId' => $pid,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
            ])
            @livewire('components.consolidated-payroll-report', [
                'payrollId' => $pid,
                'showButton' => true,
                'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;',
            ])
        @endif
    </div>
@elseif ($layout === 'payslip_scroll')
    <div class="scrollable-row">
        @if ($useWeeklyReports)
            <div>@livewire('weekly-payroll.bank-sheet-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true])</div>
            <div>@livewire('weekly-payroll.payroll-register-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true])</div>
            <div>@livewire('weekly-payroll.p-f-e-p-f-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true])</div>
            <div>@livewire('weekly-payroll.e-s-i-c-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true])</div>
            <div>@livewire('weekly-payroll.letter-head', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true])</div>
            <div>@livewire('weekly-payroll.consolidated-payroll-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true])</div>
        @else
            <div>@livewire('components.bank-sheet-report', ['payrollId' => $pid, 'showButton' => true])</div>
            <div>@livewire('components.payroll-register-report', ['payrollId' => $pid, 'showButton' => true])</div>
            <div>@livewire('components.p-f-e-p-f-report', ['payrollId' => $pid, 'showButton' => true])</div>
            <div>@livewire('components.e-s-i-c-report', ['payrollId' => $pid, 'showButton' => true])</div>
            <div>@livewire('components.letter-head', ['payrollId' => $pid, 'showButton' => true])</div>
            <div>@livewire('components.consolidated-payroll-report', ['payrollId' => $pid, 'showButton' => true])</div>
        @endif
        <div>@livewire('components.ecr-report', ['payrollId' => $pid, 'showButton' => true])</div>
        <div>@livewire('components.ecr-report-generator', ['payrollId' => $pid, 'showButton' => true])</div>
        <div>@livewire('components.employee-e-c-r-file-generator', ['payrollId' => $pid, 'showButton' => true])</div>
        <div>@livewire('components.esic-upload-template', ['payrollId' => $pid, 'showButton' => true])</div>
    </div>
@elseif ($layout === 'payslip_grid')
    @php
        $payslipReportBtn = 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;';
    @endphp
    <div class="payroll-system">
        <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; margin-bottom: 32px;">
            @if ($useWeeklyReports)
                @livewire('weekly-payroll.bank-sheet-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('weekly-payroll.payroll-register-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('weekly-payroll.p-f-e-p-f-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('weekly-payroll.e-s-i-c-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('weekly-payroll.letter-head', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('weekly-payroll.consolidated-payroll-report', ['payrollId' => $pid, 'weekId' => $week->ppw_id, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
            @else
                @livewire('components.bank-sheet-report', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('components.payroll-register-report', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('components.p-f-e-p-f-report', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('components.e-s-i-c-report', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('components.letter-head', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
                @livewire('components.consolidated-payroll-report', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
            @endif
            @livewire('components.ecr-report', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
            @livewire('components.ecr-report-generator', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
            @livewire('components.employee-e-c-r-file-generator', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
            @livewire('components.esic-upload-template', ['payrollId' => $pid, 'showButton' => true, 'buttonStyle' => $payslipReportBtn])
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
@endif
