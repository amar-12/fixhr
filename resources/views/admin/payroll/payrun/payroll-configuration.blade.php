@extends('admin.layout.master')

@section('title', $title)

@section('css')
<style>
    .payroll-config-page {
        color: #1e293b;
    }
    .payroll-config-page .hero-card {
        background: #fff;
        border: 1px solid #e9edf4;
        border-radius: 7px;
        padding: 18px 20px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .payroll-config-page .hero-icon {
        width: 40px;
        height: 40px;
        border-radius: 7px;
        background: var(--primary-bg-color);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .payroll-config-page .tab-panel {
        display: inline-flex;
        gap: 6px;
        background: #f4f6fb;
        border: 1px solid #e9edf4;
        border-radius: 7px;
        padding: 5px;
    }
    .payroll-config-page .config-tab {
        border: 0;
        background: transparent;
        color: #6b7280;
        border-radius: 5px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        transition: all .18s ease;
    }
    .payroll-config-page .config-tab:hover,
    .payroll-config-page .config-tab.active {
        background: var(--primary-bg-color);
        color: #fff;
        box-shadow: 0 2px 6px rgba(24, 119, 242, .18);
    }
    .payroll-config-page .content-card {
        background: #fff;
        border: 1px solid #e9edf4;
        border-radius: 7px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        overflow: hidden;
    }
    .payroll-config-page .content-header {
        background: #fff;
        border-bottom: 1px solid #e9edf4;
        padding: 16px 20px;
    }
    .payroll-config-page .metric-card {
        background: #fff;
        border: 1px solid #e9edf4;
        border-radius: 7px;
        padding: 16px;
        height: 100%;
    }
    .payroll-config-page .soft-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
        border: 1px solid transparent;
    }
    .payroll-config-page .badge-purple {
        background: var(--primary-transparentcolor);
        color: var(--primary-bg-color);
        border-color: rgba(24, 119, 242, .12);
    }
    .payroll-config-page .badge-blue {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #dbeafe;
    }
    .payroll-config-page .badge-green {
        background: #ecfdf5;
        color: #047857;
        border-color: #d1fae5;
    }
    .payroll-config-page .badge-red {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fee2e2;
    }
    .payroll-config-page .config-table th {
        color: #6b7280;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        background: #f4f6fb;
    }
    .payroll-config-page .config-table td {
        vertical-align: middle;
        font-size: 13px;
    }
    .payroll-config-page .grade-card {
        border: 1px solid #e9edf4;
        border-radius: 7px;
        padding: 18px;
        background: #fff;
        height: 100%;
        transition: all .18s ease;
    }
    .payroll-config-page .grade-card:hover {
        border-color: var(--primary-bg-color);
        box-shadow: 0 6px 18px rgba(24, 119, 242, .08);
    }
    .payroll-config-page .empty-state {
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 28px;
        text-align: center;
        color: #64748b;
        background: #f8fafc;
    }
    .payroll-config-section {
        display: none;
    }
    .payroll-config-section.active {
        display: block;
    }
    .statutory-sub-tab {
        border: 1px solid #e9edf4;
        background: #fff;
        color: #64748b;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 800;
    }
    .statutory-sub-tab.active {
        background: var(--primary-bg-color);
        color: #fff;
        border-color: var(--primary-bg-color);
    }
    .statutory-sub-section {
        display: none;
    }
    .statutory-sub-section.active {
        display: block;
    }
    .payroll-config-page .component-edit-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    .component-edit-modal .modal-dialog {
        max-width: 980px;
    }
    .component-edit-modal .modal-content {
        border-radius: 7px;
        border: 1px solid #e9edf4;
        box-shadow: 0 10px 35px rgba(15, 23, 42, .14);
    }
    .component-edit-modal .modal-header {
        background: #fff;
        border-bottom: 1px solid #e9edf4;
        padding: 16px 22px;
    }
    .component-edit-modal .modal-body {
        padding: 22px 24px;
    }
    .component-edit-modal .form-label {
        font-size: 12px;
        font-weight: 700;
        color: #4b5563;
    }
    .component-edit-modal .zoho-tip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        color: var(--primary-bg-color);
        border-radius: 6px;
        padding: 8px 11px;
        font-size: 12px;
        font-weight: 600;
    }
    .component-edit-modal .edit-form-divider {
        border-top: 1px solid #e9edf4;
        margin: 18px 0;
    }
    .component-edit-modal .other-config-panel {
        border-left: 1px solid #e9edf4;
        padding-left: 22px;
        height: 100%;
    }
    .component-edit-modal .other-config-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 16px;
    }
    .component-edit-modal .config-check {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 15px;
    }
    .component-edit-modal .config-check .form-check-input {
        margin-top: 2px;
    }
    .component-edit-modal .config-check label {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1px;
    }
    .component-edit-modal .config-check .small {
        line-height: 1.35;
    }
    .component-edit-modal .calc-radio-row {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .component-edit-modal .calc-radio-row .form-check-label {
        font-size: 13px;
        font-weight: 600;
    }
    .component-edit-modal .amount-input-group {
        max-width: 190px;
    }
    .component-edit-modal .edit-note {
        background: #fff8e6;
        border: 1px solid #ffecb5;
        color: #7a5a00;
        border-radius: 7px;
        padding: 12px 14px;
        font-size: 12px;
        line-height: 1.45;
    }
    .component-edit-modal .switch-row {
        border: 1px solid #e9edf4;
        border-radius: 7px;
        padding: 12px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        height: 100%;
    }
    .component-edit-modal .switch-row .small {
        line-height: 1.35;
    }

    .dark-mode .payroll-config-page,
    [data-theme="dark"] .payroll-config-page {
        color: #e5e7eb;
    }
    .dark-mode .payroll-config-page .hero-card,
    .dark-mode .payroll-config-page .content-card,
    .dark-mode .payroll-config-page .metric-card,
    .dark-mode .payroll-config-page .grade-card,
    [data-theme="dark"] .payroll-config-page .hero-card,
    [data-theme="dark"] .payroll-config-page .content-card,
    [data-theme="dark"] .payroll-config-page .metric-card,
    [data-theme="dark"] .payroll-config-page .grade-card {
        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;
        box-shadow: none;
    }
    .dark-mode .payroll-config-page .content-header,
    [data-theme="dark"] .payroll-config-page .content-header {
        background: #111827;
        border-color: #374151;
    }
    .dark-mode .payroll-config-page .tab-panel,
    [data-theme="dark"] .payroll-config-page .tab-panel {
        background: #111827;
        border-color: #374151;
    }
    .dark-mode .payroll-config-page .config-tab,
    [data-theme="dark"] .payroll-config-page .config-tab {
        color: #cbd5e1;
    }
    .dark-mode .payroll-config-page .config-tab:hover,
    .dark-mode .payroll-config-page .config-tab.active,
    [data-theme="dark"] .payroll-config-page .config-tab:hover,
    [data-theme="dark"] .payroll-config-page .config-tab.active {
        color: #fff;
    }
    .dark-mode .payroll-config-page .text-muted,
    [data-theme="dark"] .payroll-config-page .text-muted {
        color: #cbd5e1 !important;
    }
    .dark-mode .payroll-config-page .config-table,
    .dark-mode .payroll-config-page .config-table td,
    .dark-mode .payroll-config-page .config-table th,
    [data-theme="dark"] .payroll-config-page .config-table,
    [data-theme="dark"] .payroll-config-page .config-table td,
    [data-theme="dark"] .payroll-config-page .config-table th {
        color: #e5e7eb;
        border-color: #374151;
    }
    .dark-mode .payroll-config-page .config-table th,
    [data-theme="dark"] .payroll-config-page .config-table th {
        background: #111827;
        color: #cbd5e1;
    }
    .dark-mode .payroll-config-page .config-table tbody tr:hover,
    [data-theme="dark"] .payroll-config-page .config-table tbody tr:hover {
        background: rgba(255, 255, 255, .03);
    }
    .dark-mode .payroll-config-page .empty-state,
    [data-theme="dark"] .payroll-config-page .empty-state {
        background: #111827;
        border-color: #475569;
        color: #cbd5e1;
    }
    .dark-mode .payroll-config-page .badge-purple,
    [data-theme="dark"] .payroll-config-page .badge-purple {
        background: rgba(24, 119, 242, .18);
        color: #bfdbfe;
        border-color: rgba(96, 165, 250, .24);
    }
    .dark-mode .payroll-config-page .badge-blue,
    [data-theme="dark"] .payroll-config-page .badge-blue {
        background: rgba(37, 99, 235, .18);
        color: #bfdbfe;
        border-color: rgba(96, 165, 250, .24);
    }
    .dark-mode .payroll-config-page .badge-green,
    [data-theme="dark"] .payroll-config-page .badge-green {
        background: rgba(5, 150, 105, .18);
        color: #a7f3d0;
        border-color: rgba(52, 211, 153, .24);
    }
    .dark-mode .payroll-config-page .badge-red,
    [data-theme="dark"] .payroll-config-page .badge-red {
        background: rgba(220, 38, 38, .18);
        color: #fecaca;
        border-color: rgba(248, 113, 113, .24);
    }
    .dark-mode .payroll-config-page .grade-card:hover,
    [data-theme="dark"] .payroll-config-page .grade-card:hover {
        border-color: var(--primary-bg-color);
        box-shadow: 0 6px 18px rgba(24, 119, 242, .12);
    }
    .dark-mode .component-edit-modal .modal-content,
    [data-theme="dark"] .component-edit-modal .modal-content {
        background: #1f2937;
        color: #e5e7eb;
    }
    .dark-mode .component-edit-modal .modal-header,
    [data-theme="dark"] .component-edit-modal .modal-header {
        background: #111827;
        border-color: #374151;
    }
    .dark-mode .component-edit-modal .modal-content,
    [data-theme="dark"] .component-edit-modal .modal-content {
        border-color: #374151;
    }
    .dark-mode .component-edit-modal .form-label,
    .dark-mode .component-edit-modal .config-check label,
    [data-theme="dark"] .component-edit-modal .form-label {
        color: #cbd5e1;
    }
    [data-theme="dark"] .component-edit-modal .config-check label {
        color: #cbd5e1;
    }
    .dark-mode .component-edit-modal .form-control,
    .dark-mode .component-edit-modal .form-select,
    [data-theme="dark"] .component-edit-modal .form-control,
    [data-theme="dark"] .component-edit-modal .form-select {
        background: #111827;
        border-color: #374151;
        color: #e5e7eb;
    }
    .dark-mode .component-edit-modal .switch-row,
    [data-theme="dark"] .component-edit-modal .switch-row {
        background: #111827;
        border-color: #374151;
    }
    .dark-mode .component-edit-modal .zoho-tip,
    [data-theme="dark"] .component-edit-modal .zoho-tip {
        background: rgba(24, 119, 242, .18);
        color: #bfdbfe;
    }
    .dark-mode .component-edit-modal .edit-form-divider,
    .dark-mode .component-edit-modal .other-config-panel,
    [data-theme="dark"] .component-edit-modal .edit-form-divider,
    [data-theme="dark"] .component-edit-modal .other-config-panel {
        border-color: #374151;
    }
    .dark-mode .component-edit-modal .edit-note,
    [data-theme="dark"] .component-edit-modal .edit-note {
        background: rgba(245, 158, 11, .14);
        border-color: rgba(245, 158, 11, .28);
        color: #fde68a;
    }
    @media (max-width: 767.98px) {
        .component-edit-modal .other-config-panel {
            border-left: 0;
            border-top: 1px solid #e9edf4;
            padding-left: 0;
            padding-top: 18px;
            margin-top: 8px;
        }
    }
</style>
@endsection

@section('content')
@php
    $activeEarnings = $earnings->where('sa_is_active', true)->count();
    $activeDeductions = $statutoryDeductions->where('std_status', true)->count();
    $componentPayload = $earnings->mapWithKeys(function ($earning) {
        return [
            $earning->sa_id => [
                'id' => $earning->sa_id,
                'title' => $earning->sa_title,
                'description' => $earning->sa_description,
                'name_in_payslip' => $earning->sa_name_in_payslip,
                'earning_type_id' => $earning->sa_earning_type_id,
                'payroll_heading_id' => $earning->sa_payroll_heading_id,
                'calculation_type' => $earning->sa_calculation_type,
                'threshold_value' => $earning->sa_threshold_value,
                'sequence_valu' => $earning->sa_sequence_valu,
                'pf_check' => (bool) $earning->sa_consider_for_pf,
                'pf_condition' => $earning->sa_consider_for_pf_condition,
                'esic_check' => (bool) $earning->sa_consider_for_esic,
                'calculate_basis' => (bool) $earning->sa_calculate_on_prorata_basis,
                'tax_check' => (bool) $earning->sa_is_taxable,
                'payslip_check' => (bool) $earning->sa_show_in_payslip,
                'status_check' => (bool) $earning->sa_is_active,
            ],
        ];
    });
    $statutoryPayload = $statutoryDeductions->mapWithKeys(function ($deduction) {
        return [
            $deduction->std_id => [
                'id' => $deduction->std_id,
                'title' => optional($deduction->fh_deduction_type)->m_name ?? 'Statutory Deduction',
                'deduction_type_id' => $deduction->std_deduction_type_id,
                'deduction_cycle_id' => $deduction->std_deduction_cycle_id,
                'employee_rate' => $deduction->std_employee_contri_rate_amount,
                'employer_rate' => $deduction->std_employer_contri_rate_amount,
                'threshold' => $deduction->std_threshold,
                'status_check' => (bool) $deduction->std_status,
            ],
        ];
    });
@endphp

<div class="payroll-config-page">
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background:none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="#">Payroll</a></li>
                <li class="active"><span><b>{{ $title }}</b></span></li>
            </ol>
        </div>
    </div>

    <div class="hero-card mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fe fe-settings"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold">Payroll Configuration</h3>
                <div class="small text-muted">Salary components, statutory setup, and pay grade overview</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <div class="tab-panel">
                <button type="button" class="config-tab active" data-config-tab="statutory">
                    <i class="fe fe-shield"></i> Statutory
                </button>
                <button type="button" class="config-tab" data-config-tab="components">
                    <i class="fe fe-grid"></i> Components
                </button>
                <button type="button" class="config-tab" data-config-tab="grades">
                    <i class="fe fe-briefcase"></i> Pay Grades
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="metric-card">
                <div class="text-muted small fw-bold text-uppercase">Salary Components</div>
                <div class="h3 fw-bold mb-0 mt-2">{{ $earnings->count() }}</div>
                <span class="soft-badge badge-green mt-3">{{ $activeEarnings }} Active</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <div class="text-muted small fw-bold text-uppercase">Statutory Deductions</div>
                <div class="h3 fw-bold mb-0 mt-2">{{ $statutoryDeductions->count() }}</div>
                <span class="soft-badge badge-blue mt-3">{{ $activeDeductions }} Active</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <div class="text-muted small fw-bold text-uppercase">Pay Grades</div>
                <div class="h3 fw-bold mb-0 mt-2">{{ $payGrades->count() }}</div>
                <span class="soft-badge badge-purple mt-3">Configured Bands</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="payroll-config-section active" id="config-statutory">
                <div class="content-card">
                    <div class="content-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="mb-1 fw-bold">Statutory Components</h4>
                            <div class="small text-muted">EPF, ESI, PT, LWF and other statutory rules configured for this business</div>
                        </div>
                        <span class="soft-badge badge-purple"><i class="fe fe-shield"></i> Compliance</span>
                    </div>
                    <div class="p-3">
                        @if($statutoryDeductions->isEmpty() && $professionalTaxSlabs->isEmpty() && $labourWelfareMasters->isEmpty())
                            <div class="empty-state">No statutory deduction configured.</div>
                        @else
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                                <button type="button" class="statutory-sub-tab active" data-statutory-tab="statutory-master">
                                    Statutory
                                </button>
                                <button type="button" class="statutory-sub-tab" data-statutory-tab="professional-tax">
                                    Professional Tax
                                </button>
                                <button type="button" class="statutory-sub-tab" data-statutory-tab="labour-welfare">
                                    LWF
                                </button>
                            </div>

                            <div class="statutory-sub-section active" id="statutory-master">
                                @if($statutoryDeductions->isEmpty())
                                    <div class="empty-state">No PF/ESI statutory deduction configured.</div>
                                @else
                                    <div class="row g-3">
                                        @foreach($statutoryDeductions as $deduction)
                                            <div class="col-md-6">
                                                <div class="metric-card">
                                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                                        <div>
                                                            <div class="fw-bold">{{ optional($deduction->fh_deduction_type)->m_name ?? 'Statutory Deduction' }}</div>
                                                            <div class="small text-muted">{{ optional($deduction->fh_deduction_cycle)->m_name ?? 'Monthly' }}</div>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="soft-badge {{ $deduction->std_status ? 'badge-green' : 'badge-red' }}">
                                                                {{ $deduction->std_status ? 'Active' : 'Inactive' }}
                                                            </span>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-primary statutory-edit-btn"
                                                                    data-deduction-id="{{ $deduction->std_id }}">
                                                                <i class="fe fe-edit"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 small">
                                                        <div class="col-6">
                                                            <div class="text-muted fw-bold">Employee Rate</div>
                                                            <div class="fw-bold">{{ number_format((float) $deduction->std_employee_contri_rate_amount, 2) }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fw-bold">Employer Rate</div>
                                                            <div class="fw-bold">{{ number_format((float) $deduction->std_employer_contri_rate_amount, 2) }}</div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="text-muted fw-bold">Threshold</div>
                                                            <div class="fw-bold">₹{{ number_format((float) $deduction->std_threshold, 2) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="statutory-sub-section" id="professional-tax">
                                @if($professionalTaxMaster->isEmpty())
                                    <div class="empty-state">No professional tax master configured.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table config-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>State</th>
                                                    <th>Cycle</th>
                                                    <th>Gender</th>
                                                    <th>Income From</th>
                                                    <th>Income To</th>
                                                    <th>Tax Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($professionalTaxMaster as $taxMaster)
                                                    <tr>
                                                        <td>{{ optional($taxMaster->state)->s_name ?? '-' }}</td>
                                                        <td>{{ optional($taxMaster->cycle)->m_name ?? '-' }}</td>
                                                        <td>{{ optional($taxMaster->gender)->m_name ?? ($taxMaster->ptm_gender ?? 'All') }}</td>
                                                        <td>₹{{ number_format((float) $taxMaster->ptm_income_from, 2) }}</td>
                                                        <td>{{ $taxMaster->ptm_income_to !== null ? '₹'.number_format((float) $taxMaster->ptm_income_to, 2) : 'Above' }}</td>
                                                        <td class="fw-bold">₹{{ number_format((float) $taxMaster->ptm_tax_amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            <div class="statutory-sub-section" id="labour-welfare">
                                @if($labourWelfareMasters->isEmpty())
                                    <div class="empty-state">No labour welfare fund configured.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table config-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>State</th>
                                                    <th>Cycle</th>
                                                    <th>Employee Contribution</th>
                                                    <th>Employer Contribution</th>
                                                    <th>Total Contribution</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($labourWelfareMasters as $lwf)
                                                    <tr>
                                                        <td>{{ optional($lwf->state)->s_name ?? '-' }}</td>
                                                        <td>{{ optional($lwf->cycle)->m_name ?? '-' }}</td>
                                                        <td>₹{{ number_format((float) $lwf->lwf_employee_contri, 2) }}</td>
                                                        <td>₹{{ number_format((float) $lwf->lwf_employer_contri, 2) }}</td>
                                                        <td class="fw-bold">₹{{ number_format((float) $lwf->total_contribution, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="payroll-config-section" id="config-components">
                <div class="content-card">
                    <div class="content-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="mb-1 fw-bold">Salary Components</h4>
                            <div class="small text-muted">Earning heads and calculation rules used by salary calculator</div>
                        </div>
                        <span class="soft-badge badge-blue"><i class="fe fe-list"></i> Component Master</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table config-table mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Calculation</th>
                                    <th>Value</th>
                                    <th>PF</th>
                                    <th>ESI</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($earnings as $earning)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $earning->sa_title }}</div>
                                            <div class="small text-muted">{{ $earning->sa_name_in_payslip ?: 'Payslip name not set' }}</div>
                                        </td>
                                        <td>{{ optional($earning->fh_allowance_calculation_type)->m_name ?? '-' }}</td>
                                        <td class="fw-bold">₹{{ number_format((float) $earning->sa_threshold_value, 2) }}</td>
                                        <td>
                                            <span class="soft-badge {{ $earning->sa_consider_for_pf ? 'badge-green' : 'badge-red' }}">
                                                {{ $earning->sa_consider_for_pf ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="soft-badge {{ $earning->sa_consider_for_esic ? 'badge-green' : 'badge-red' }}">
                                                {{ $earning->sa_consider_for_esic ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="soft-badge {{ $earning->sa_is_active ? 'badge-green' : 'badge-red' }}">
                                                {{ $earning->sa_is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary component-edit-btn"
                                                data-component-id="{{ $earning->sa_id }}"
                                                title="Edit component">
                                                <i class="fe fe-edit-2"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No salary components configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="payroll-config-section" id="config-grades">
                <div class="content-card">
                    <div class="content-header">
                        <h4 class="mb-1 fw-bold">Pay Grades</h4>
                        <div class="small text-muted">Configured salary ranges and grade bands</div>
                    </div>
                    <div class="p-3">
                        @if($payGrades->isEmpty())
                            <div class="empty-state">No pay grades configured.</div>
                        @else
                            <div class="row g-3">
                                @foreach($payGrades as $grade)
                                    <div class="col-md-6">
                                        <div class="grade-card">
                                            <div class="d-flex justify-content-between align-items-start mb-4">
                                                <div>
                                                    <h5 class="fw-bold mb-1">{{ optional($grade->fh_grade)->g_name ?? 'Grade' }}</h5>
                                                    <div class="small text-muted">{{ $grade->pg_description ?: 'No description' }}</div>
                                                </div>
                                                <span class="soft-badge badge-purple">Band</span>
                                            </div>
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span class="text-muted fw-bold">Min Salary</span>
                                                <span class="fw-bold">₹{{ number_format((float) $grade->pg_min_salary, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span class="text-muted fw-bold">Max Salary</span>
                                                <span class="fw-bold">₹{{ number_format((float) $grade->pg_max_salary, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between small">
                                                <span class="text-muted fw-bold">Bonus</span>
                                                <span class="fw-bold">{{ number_format((float) $grade->pg_bonus_percentage, 2) }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade component-edit-modal" id="componentEditModal" tabindex="-1" aria-labelledby="componentEditModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="componentEditModalLabel">Edit Earning</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="componentEditForm">
                @csrf
                <input type="hidden" name="sa_id" id="edit_sa_id">
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="mb-3">
                                <label class="form-label" for="edit_earning_type_id">Earning Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="earning_type_id" id="edit_earning_type_id" required>
                                    <option value="">Select Earning Type</option>
                                    @foreach($earningTypes as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-7 d-flex align-items-end">
                            <div class="zoho-tip mb-3">
                                <i class="fe fe-info"></i>
                                Fixed amount paid at the end of every month.
                            </div>
                        </div>
                    </div>

                    <div class="edit-form-divider"></div>

                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="mb-3">
                                <label class="form-label" for="edit_name">Earning Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="edit_name" placeholder="Earning Name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="edit_payslip">Name in Payslip <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="payslip" id="edit_payslip" placeholder="Name In Payslip" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="edit_payroll_heading_id">Payroll Heading <span class="text-danger">*</span></label>
                                <select class="form-select" name="payroll_heading_id" id="edit_payroll_heading_id" required>
                                    <option value="">Select Payroll Heading</option>
                                    @foreach($payrollHeadings as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Calculation Type <span class="text-danger">*</span></label>
                                <div class="calc-radio-row">
                                    @foreach($calculationTypes as $id => $name)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="calculation_type" id="edit_calculation_type_{{ $id }}" value="{{ $id }}" required>
                                            <label class="form-check-label" for="edit_calculation_type_{{ $id }}">{{ $name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="edit_calculation_value">Enter Amount / Percentage <span class="text-danger">*</span></label>
                                <div class="input-group amount-input-group">
                                    <input type="number" step="0.01" class="form-control" name="calculation_value" id="edit_calculation_value" placeholder="0.00" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="edit_sequence_valu">Sequence No. <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="sequence_valu" id="edit_sequence_valu" placeholder="Sequence No." required>
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="edit_status_check" name="status_check" value="1">
                                <label class="form-check-label fw-semibold" for="edit_status_check">Mark this as Active</label>
                            </div>
                            <textarea class="d-none" name="des" id="edit_des"></textarea>
                        </div>

                        <div class="col-lg-7">
                            <div class="other-config-panel">
                                <div class="other-config-title">Other Configurations</div>

                                <div class="config-check">
                                    <input class="form-check-input component-switch" type="checkbox" id="edit_calculate_basis" name="calculate_basis" value="1">
                                    <div>
                                        <label for="edit_calculate_basis">Calculate on pro-rata basis</label>
                                        <div class="small text-muted">Pay will be adjusted based on employee working days.</div>
                                    </div>
                                </div>

                                <div class="config-check">
                                    <input class="form-check-input component-switch" type="checkbox" id="edit_tax_check" name="tax_check" value="1">
                                    <div>
                                        <label for="edit_tax_check">This is a taxable earning</label>
                                        <div class="small text-muted">The income tax amount will be divided equally and deducted every month across the financial year.</div>
                                    </div>
                                </div>

                                <div class="config-check">
                                    <input class="form-check-input component-switch" type="checkbox" id="edit_pf_check" name="pf_check" value="1">
                                    <div>
                                        <label for="edit_pf_check">Consider for EPF Contribution</label>
                                        <div class="mt-2">
                                            @foreach($pfConditions as $id => $name)
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input" type="radio" name="pf_condition" id="edit_pf_condition_{{ $id }}" value="{{ $id }}">
                                                    <label class="form-check-label small" for="edit_pf_condition_{{ $id }}">{{ $name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="config-check">
                                    <input class="form-check-input component-switch" type="checkbox" id="edit_esic_check" name="esic_check" value="1">
                                    <div>
                                        <label for="edit_esic_check">Consider for ESI Contribution</label>
                                    </div>
                                </div>

                                <div class="config-check">
                                    <input class="form-check-input component-switch" type="checkbox" id="edit_payslip_check" name="payslip_check" value="1">
                                    <div>
                                        <label for="edit_payslip_check">Show this component in payslip</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="edit-note">
                                <strong>Note:</strong> Once you associate this component with an employee, changes to amount/percentage may apply based on salary processing rules.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="componentEditSaveBtn">
                        <i class="fe fe-save me-1"></i> Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade component-edit-modal" id="statutoryEditModal" tabindex="-1" aria-labelledby="statutoryEditModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="statutoryEditModalLabel">Edit Statutory Deduction</h5>
                    <div class="small text-muted">Update PF/ESIC rates, threshold and status.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="statutoryEditForm">
                @csrf
                <input type="hidden" name="std_id" id="edit_std_id">
                <input type="hidden" name="title" id="edit_std_title">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_std_deduction_type_id">Deduction Type</label>
                            <select class="form-select" name="std_deduction_type_id" id="edit_std_deduction_type_id" required>
                                <option value="">Select Deduction Type</option>
                                @foreach($statutoryDeductionTypes as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_std_deduction_cycle_id">Deduction Cycle</label>
                            <select class="form-select" name="std_deduction_cycle_id" id="edit_std_deduction_cycle_id" required>
                                <option value="">Select Cycle</option>
                                @foreach($deductionCycles as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_std_employee_rate">Employee Rate / Amount</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="std_employee_contri_rate_amount" id="edit_std_employee_rate" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_std_employer_rate">Employer Rate / Amount</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="std_employer_contri_rate_amount" id="edit_std_employer_rate" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_std_threshold">Threshold</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="std_threshold" id="edit_std_threshold">
                        </div>
                        <div class="col-12">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="edit_std_status" name="std_status">
                                <label class="form-check-label fw-semibold" for="edit_std_status">Mark this deduction as Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="statutoryEditSaveBtn">
                        <i class="fe fe-save me-1"></i> Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const payrollComponents = @json($componentPayload);
        const statutoryDeductions = @json($statutoryPayload);
        const componentModalEl = document.getElementById('componentEditModal');
        const componentModal = componentModalEl && typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(componentModalEl)
            : null;
        const componentForm = document.getElementById('componentEditForm');
        const saveButton = document.getElementById('componentEditSaveBtn');
        const statutoryModalEl = document.getElementById('statutoryEditModal');
        const statutoryModal = statutoryModalEl && typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(statutoryModalEl)
            : null;
        const statutoryForm = document.getElementById('statutoryEditForm');
        const statutorySaveButton = document.getElementById('statutoryEditSaveBtn');

        const storageKey = 'payroll_configuration_active_tab';
        const availableTabs = Array.from(document.querySelectorAll('[data-config-tab]'))
            .map(function (button) {
                return button.getAttribute('data-config-tab');
            });

        function activateConfigTab(tab, shouldPersist = true) {
            if (!availableTabs.includes(tab)) {
                tab = 'statutory';
            }

            document.querySelectorAll('[data-config-tab]').forEach(function (item) {
                item.classList.toggle('active', item.getAttribute('data-config-tab') === tab);
            });

            document.querySelectorAll('.payroll-config-section').forEach(function (section) {
                section.classList.toggle('active', section.id === 'config-' + tab);
            });

            if (shouldPersist) {
                localStorage.setItem(storageKey, tab);
                history.replaceState(null, '', '#' + tab);
            }
        }

        const initialTab = window.location.hash
            ? window.location.hash.replace('#', '')
            : localStorage.getItem(storageKey);

        activateConfigTab(initialTab || 'statutory', false);

        document.querySelectorAll('[data-config-tab]').forEach(function (button) {
            button.addEventListener('click', function () {
                activateConfigTab(button.getAttribute('data-config-tab'));
            });
        });

        document.querySelectorAll('[data-statutory-tab]').forEach(function (button) {
            button.addEventListener('click', function () {
                const tab = button.getAttribute('data-statutory-tab');

                document.querySelectorAll('[data-statutory-tab]').forEach(function (item) {
                    item.classList.toggle('active', item === button);
                });

                document.querySelectorAll('.statutory-sub-section').forEach(function (section) {
                    section.classList.toggle('active', section.id === tab);
                });
            });
        });

        document.querySelectorAll('.statutory-edit-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const deduction = statutoryDeductions[button.getAttribute('data-deduction-id')];
                if (!deduction || !statutoryForm) {
                    return;
                }

                statutoryForm.reset();
                document.getElementById('edit_std_id').value = deduction.id || '';
                document.getElementById('edit_std_title').value = deduction.title || '';
                document.getElementById('edit_std_deduction_type_id').value = deduction.deduction_type_id || '';
                document.getElementById('edit_std_deduction_cycle_id').value = deduction.deduction_cycle_id || '';
                document.getElementById('edit_std_employee_rate').value = deduction.employee_rate ?? '';
                document.getElementById('edit_std_employer_rate').value = deduction.employer_rate ?? '';
                document.getElementById('edit_std_threshold').value = deduction.threshold ?? '';
                document.getElementById('edit_std_status').checked = !!deduction.status_check;
                document.getElementById('statutoryEditModalLabel').textContent = 'Edit ' + (deduction.title || 'Statutory Deduction');

                if (statutoryModal) {
                    statutoryModal.show();
                }
            });
        });

        statutoryForm?.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(statutoryForm);
            const statusInput = statutoryForm.querySelector('[name="std_status"]');
            if (!statusInput.checked) {
                formData.delete('std_status');
            } else {
                formData.set('std_status', 'on');
            }

            statutorySaveButton.disabled = true;
            statutorySaveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            fetch(@json(route('payroll.deduction.create.update')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                },
                body: formData,
            })
                .then(async function (response) {
                    const data = await response.json().catch(function () {
                        return {};
                    });
                    if (!response.ok || data.status === 'failed') {
                        throw new Error(data.message || 'Unable to update deduction.');
                    }
                    return data;
                })
                .then(function (data) {
                    if (statutoryModal) {
                        statutoryModal.hide();
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: data.message || 'Deduction updated successfully.',
                            timer: 1200,
                            showConfirmButton: false,
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }
                })
                .catch(function (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', error.message, 'error');
                    } else {
                        alert(error.message);
                    }
                })
                .finally(function () {
                    statutorySaveButton.disabled = false;
                    statutorySaveButton.innerHTML = '<i class="fe fe-save me-1"></i> Save changes';
                });
        });

        document.querySelectorAll('.component-edit-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const component = payrollComponents[button.getAttribute('data-component-id')];
                if (!component || !componentForm) {
                    return;
                }

                componentForm.reset();
                document.getElementById('edit_sa_id').value = component.id || '';
                document.getElementById('edit_earning_type_id').value = component.earning_type_id || '';
                document.getElementById('edit_payroll_heading_id').value = component.payroll_heading_id || '';
                document.getElementById('edit_name').value = component.title || '';
                document.getElementById('edit_payslip').value = component.name_in_payslip || component.title || '';
                document.getElementById('edit_des').value = component.description || '';
                document.getElementById('edit_calculation_value').value = component.threshold_value ?? '';
                document.getElementById('edit_sequence_valu').value = component.sequence_valu ?? '';
                document.getElementById('edit_status_check').checked = !!component.status_check;
                document.getElementById('edit_pf_check').checked = !!component.pf_check;
                document.getElementById('edit_esic_check').checked = !!component.esic_check;
                document.getElementById('edit_calculate_basis').checked = !!component.calculate_basis;
                document.getElementById('edit_tax_check').checked = !!component.tax_check;
                document.getElementById('edit_payslip_check').checked = !!component.payslip_check;

                componentForm.querySelectorAll('[name="calculation_type"]').forEach(function (radio) {
                    radio.checked = String(radio.value) === String(component.calculation_type || '');
                });
                componentForm.querySelectorAll('[name="pf_condition"]').forEach(function (radio) {
                    radio.checked = String(radio.value) === String(component.pf_condition || '');
                });

                if (componentModal) {
                    componentModal.show();
                }
            });
        });

        componentForm?.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(componentForm);

            ['status_check', 'pf_check', 'esic_check', 'calculate_basis', 'tax_check', 'payslip_check'].forEach(function (name) {
                const input = componentForm.querySelector('[name="' + name + '"]');
                formData.set(name, input && input.checked ? '1' : '0');
            });
            const selectedPfCondition = componentForm.querySelector('[name="pf_condition"]:checked');
            formData.set('pf_condition', selectedPfCondition ? selectedPfCondition.value : '');

            saveButton.disabled = true;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            fetch(@json(route('payroll.component.create')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                },
                body: formData,
            })
                .then(async function (response) {
                    const data = await response.json().catch(function () {
                        return {};
                    });
                    if (!response.ok || data.status === false) {
                        throw new Error(data.message || 'Unable to update component.');
                    }
                    return data;
                })
                .then(function (data) {
                    if (componentModal) {
                        componentModal.hide();
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: data.message || 'Component updated successfully.',
                            timer: 1200,
                            showConfirmButton: false,
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }
                })
                .catch(function (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', error.message, 'error');
                    } else {
                        alert(error.message);
                    }
                })
                .finally(function () {
                    saveButton.disabled = false;
                    saveButton.innerHTML = '<i class="fe fe-save me-1"></i> Save changes';
                });
        });
    });
</script>
@endsection
