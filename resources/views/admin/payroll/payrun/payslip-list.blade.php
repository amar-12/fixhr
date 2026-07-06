@extends('admin.layout.master')

@section('title', 'Payslip List - ' . $payrollPeriod->pp_name)

@section('css')
<style>
    /* Responsive styling for reports row */
    @media (max-width: 1400px) {
        .payroll-system>div {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    @media (max-width: 1024px) {
        .payroll-system>div {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 768px) {
        .payroll-system>div {
            grid-template-columns: 1fr !important;
        }
    }

    /* Scrollable row for small screens */
    .scrollable-row {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 12px;
        padding: 12px 8px;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
    }

    .scrollable-row>div {
        flex: 0 0 auto;
        min-width: 200px;
    }

    /* Hide scrollbar for Chrome, Safari and Opera */
    .scrollable-row::-webkit-scrollbar {
        height: 6px;
    }

    .scrollable-row::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .scrollable-row::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .scrollable-row::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }

    .dark-mode .scrollable-row::-webkit-scrollbar-track {
        background: #374151;
    }

    .dark-mode .scrollable-row::-webkit-scrollbar-thumb {
        background: #4b5563;
    }

    .dark-mode .scrollable-row::-webkit-scrollbar-thumb:hover {
        background: #6b7280;
    }


    /* ============================================
                   DARK MODE COMPATIBLE STYLES
                   ============================================ */

    /* CSS Variables for Dark/Light Mode */
    :root {
        --payslip-bg: #ffffff;
        --payslip-border: #e2e8f0;
        --payslip-text: #1e293b;
        --payslip-text-muted: #64748b;
        --payslip-header-bg: #f8fafc;
        --payslip-card-bg: #ffffff;
        --payslip-hover-bg: #f1f5f9;
        --payslip-primary: #3b82f6;
        --payslip-success: #10b981;
        --payslip-secondary: #94a3b8;
        --payslip-light-bg: #f1f5f9;
        --payslip-light-text: #475569;
        --payslip-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        --payslip-border-color: #e2e8f0;
    }

    .dark-mode {
        --payslip-bg: #1f2937;
        --payslip-border: #374151;
        --payslip-text: #f9fafb;
        --payslip-text-muted: #9ca3af;
        --payslip-header-bg: #111827;
        --payslip-card-bg: #1f2937;
        --payslip-hover-bg: #2d3748;
        --payslip-primary: #60a5fa;
        --payslip-success: #34d399;
        --payslip-secondary: #6b7280;
        --payslip-light-bg: #374151;
        --payslip-light-text: #d1d5db;
        --payslip-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.5);
        --payslip-border-color: #4b5563;
    }

    .dark-mode .input-text {
        width: 50px;
        text-align: center;
        background: #25274a;
        border: 1px solid #3c3f76;
        color: #ffffff;
    }

    .head-text {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .dark-mode .head-text {
        color: white !important;
        font-size: 1.125rem;
        font-weight: 700;
    }

    .dark-mode .para-text {
        font-size: 0.875rem;
        font-weight: 500;
        color: white;
    }

    .dark-mode .para-text-table {
        font-size: 10px;
        font-weight: 500;
        color: white;
    }


    /* Back button styles */
    .back-btn-header {
        position: absolute;
        left: 0;
        top: 0;
        z-index: 1;
    }

    .header-with-back {
        position: relative;
        padding-left: 50px;
        min-height: 40px;
    }

    /* Button improvements - Dark Mode Compatible */
    .btn-light {
        background-color: var(--payslip-light-bg);
        border-color: var(--payslip-border-color);
        color: var(--payslip-light-text);
        transition: all 0.2s ease;
    }

    .btn-light:hover {
        background-color: var(--payslip-hover-bg);
        border-color: var(--payslip-border-color);
        color: var(--payslip-text);
    }

    /* Preview Button - Blue with eye icon */
    .btn-preview {
        background-color: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.3);
        color: var(--payslip-primary);
        transition: all 0.2s ease;
    }

    .btn-preview:hover {
        background-color: var(--payslip-primary);
        border-color: var(--payslip-primary);
        color: white;
    }

    /* Download Button - Green with download icon */
    .btn-download {
        background-color: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.3);
        color: var(--payslip-success);
        transition: all 0.2s ease;
    }

    .btn-download:hover {
        background-color: var(--payslip-success);
        border-color: var(--payslip-success);
        color: white;
    }

    /* Card Styling */
    .payslip-card {
        background: var(--payslip-card-bg);
        border: 1px solid var(--payslip-border);
        border-radius: 12px;
        box-shadow: var(--payslip-shadow);
        overflow: hidden;
    }

    .payslip-card .card-header {
        background: var(--payslip-header-bg);
        border-bottom: 1px solid var(--payslip-border);
        color: var(--payslip-text);
    }

    /* Table Styling - Dark Mode Compatible */
    .payslip-list-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid var(--payslip-border);
        border-radius: 8px;
        overflow: hidden;
        background: var(--payslip-card-bg);
    }

    .payslip-list-table thead {
        background: var(--payslip-header-bg);
    }

    .payslip-list-table th {
        padding: 12px 16px;
        border-bottom: 2px solid var(--payslip-border);
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--payslip-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: var(--payslip-header-bg);
    }

    .payslip-list-table td {
        padding: 16px;
        border-bottom: 1px solid var(--payslip-border);
        vertical-align: middle;
        color: var(--payslip-text);
        background: var(--payslip-card-bg);
    }

    .payslip-list-table tbody tr:last-child td {
        border-bottom: none;
    }

    .payslip-list-table tbody tr:hover {
        background-color: var(--payslip-hover-bg);
    }

    /* Employee Cell Styling */
    .employee-cell-mini {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .employee-avatar-mini {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #3b82f6 display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
        color: white;
        flex-shrink: 0;
    }

    .employee-info-mini {
        min-width: 0;
    }

    .employee-name {
        font-weight: 600;
        color: var(--payslip-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }

    .employee-code {
        font-size: 0.75rem;
        color: var(--payslip-text-muted);
    }

    /* Badge Styling - Dark Mode Compatible */
    .badge.bg-light {
        background-color: var(--payslip-light-bg) !important;
        color: var(--payslip-light-text) !important;
        border: 1px solid var(--payslip-border-color) !important;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Text Color Classes */
    .text-dark {
        color: var(--payslip-text) !important;
    }

    .text-muted {
        color: var(--payslip-text-muted) !important;
    }

    /* Form Control Styling */
    .form-control {
        background-color: var(--payslip-card-bg);
        border: 1px solid var(--payslip-border);
        color: var(--payslip-text);
        border-radius: 8px;
        padding: 8px 12px 8px 36px;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        background-color: var(--payslip-card-bg);
        border-color: var(--payslip-primary);
        color: var(--payslip-text);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control::placeholder {
        color: var(--payslip-text-muted);
    }

    /* Search Container */
    .search-container {
        position: relative;
        width: 300px;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--payslip-text-muted);
        pointer-events: none;
    }

    /* Button Group Actions */
    .btn-group-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Modal Styling - Dark Mode Compatible */
    .modal-content {
        background-color: var(--payslip-card-bg);
        border: 1px solid var(--payslip-border);
        color: var(--payslip-text);
    }

    .modal-header {
        border-bottom: 1px solid var(--payslip-border);
        background: var(--payslip-header-bg);
    }

    .modal-footer {
        border-top: 1px solid var(--payslip-border);
        background: var(--payslip-header-bg);
    }

    /* Close Button for Dark Mode */
    .btn-close {
        filter: var(--bs-btn-close-filter, none);
    }

    .dark-mode .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--payslip-text-muted);
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        color: var(--payslip-secondary);
    }

    /* Status Indicators */
    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-processed {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--payslip-success);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .status-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .header-with-back {
            padding-left: 45px;
        }

        .search-container {
            width: 100%;
            margin-bottom: 12px;
        }

        .btn-group-actions {
            flex-direction: column;
            width: 100%;
        }

        .btn-group-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .payslip-list-table th,
        .payslip-list-table td {
            padding: 12px 8px;
        }

        .employee-name {
            max-width: 120px;
        }
    }

    @media (max-width: 576px) {
        .header-with-back {
            padding-left: 40px;
            padding-top: 8px;
        }

        .payslip-list-table {
            font-size: 0.875rem;
        }

        .employee-cell-mini {
            gap: 8px;
        }

        .employee-avatar-mini {
            width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }
    }

    /* Loading Spinner */
    .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid var(--payslip-primary);
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-rotate 0.75s linear infinite;
    }

    @keyframes spinner-rotate {
        to {
            transform: rotate(360deg);
        }
    }

    /* Print Styles */
    @media print {

        .back-btn-header,
        .btn-group-actions,
        .search-container,
        .modal-footer {
            display: none !important;
        }

        .payslip-list-table {
            border: 1px solid #000 !important;
        }

        .payslip-list-table th,
        .payslip-list-table td {
            color: #000 !important;
            background: white !important;
        }
    }

    /* Custom Scrollbar for Dark Mode */
    .dark-mode ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .dark-mode ::-webkit-scrollbar-track {
        background: var(--payslip-header-bg);
        border-radius: 4px;
    }

    .dark-mode ::-webkit-scrollbar-thumb {
        background: var(--payslip-border);
        border-radius: 4px;
    }

    .dark-mode ::-webkit-scrollbar-thumb:hover {
        background: var(--payslip-text-muted);
    }

    /* Focus States for Accessibility */
    .btn:focus,
    .form-control:focus {
        outline: 2px solid var(--payslip-primary);
        outline-offset: 2px;
    }

    /* Transition for theme switching */
    * {
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }

    .btn-icon-only {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .btn-group-actions {
        display: flex;
        gap: 6px;
        justify-content: flex-end;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <div class="p-0 mt-3">
            <div class="row">
                <div class="col-md-12">
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
                        <li><a href="{{ route('payroll.cycles') }}" class="text-decoration-none text-dark">Payroll
                                Cycles</a></li>
                        <li class="active"><span><b>Payslips</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
        <br>

        <!-- Flexbox Solution -->
        <div class="d-flex align-items-start gap-3 mb-4">
            <!-- Back Button -->
            <div class="flex-shrink-0">
                <button onclick="goBackSmart()"
                    class="btn btn-light btn-icon rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;" title="Go back to previous page" aria-label="Go back">
                    <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <!-- Title Content -->
            <div class="flex-grow-1">
                <h class="mb-1 text-dark">Payslips & Reports</h>
                <p class="text-muted mb-0">
                    {{ $payrollPeriod->pp_name }} •
                    {{ \Carbon\Carbon::parse($payrollPeriod->pp_start_date)->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($payrollPeriod->pp_end_date)->format('d M Y') }}
                    {{-- <span class="status-indicator status-processed">
                        <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i>
                        Verified Payroll Batch
                    </span> --}}
                </p>
            </div>
        </div>

        <div class="payslip-card mb-6">
            <div class="payroll-system">
                {{-- Reports Section with Buttons - Grid Layout --}}
                <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; margin-bottom: 32px;">

                    <!-- First Row: 5 Cards -->
                    <!-- Bank Sheet Report Component -->
                    @livewire('components.bank-sheet-report', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])

                    <!-- Payroll Register Livewire Component -->
                    @livewire('components.payroll-register-report', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])

                    <!-- PF/EPF Livewire Component -->
                    @livewire('components.p-f-e-p-f-report', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])

                    <!-- ESIC Livewire Component -->
                    @livewire('components.e-s-i-c-report', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])

                    <!-- Letter Head Livewire Component -->
                    @livewire('components.letter-head', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])

                     @livewire('components.consolidated-payroll-report', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])

                     @livewire('components.ecr-report', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])


                    <!-- Second Row: 2 Cards (ECR first, then ESIC Template) -->
                    <!-- ECR Report Card - Now in Second Row First Position -->
                    @livewire('components.ecr-report-generator', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])


                     @livewire('components.employee-e-c-r-file-generator', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])

                    <!-- ESIC Upload Template -->
                    @livewire('components.esic-upload-template', [
                    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
                    'showButton' => true,
                    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
                    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 8px; width: 100%;',
                    ])


                    <!-- Empty placeholders for remaining grid slots (to maintain 5-column grid) -->
                    <div></div>
                    <div></div>
                    <div></div>

                </div>
            </div>
        </div>


        <!-- Card with Table -->
        <div class="payslip-card">
            <div class="card-body p-0">
                <!-- Search and Info Bar -->
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom"
                    style="border-color: var(--payslip-border)">
                    <div class="search-container">
                        <div class="search-icon">
                            <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                        </div>
                        <input type="text" class="form-control" placeholder="Search employee..." id="payslipSearch"
                            aria-label="Search employees">
                    </div>
                    <div class="text-muted small d-none d-md-block">
                        <span class="fw-semibold text-dark">{{ $processedEmployees->count() }}</span> Employees
                        @if ($processedEmployees->count() > 0)
                        • Net Pay: <span class="fw-semibold text-dark">
                            ₹{{ number_format(
                            $processedEmployees->sum(function ($emp) {
                            return $emp->processedSalary->ps_monthly_net_salary ?? 0;
                            }),
                            2,
                            ) }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Mobile Stats -->
                <div class="d-block d-md-none p-3 border-bottom" style="border-color: var(--payslip-border)">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center p-2 rounded" style="background: var(--payslip-light-bg)">
                                <div class="small text-muted">Employees</div>
                                <div class="fw-bold text-dark">{{ $processedEmployees->count() }}</div>
                            </div>
                        </div>
                        @if ($processedEmployees->count() > 0)
                        <div class="col-6">
                            <div class="text-center p-2 rounded" style="background: var(--payslip-light-bg)">
                                <div class="small text-muted">Net Pay</div>
                                <div class="fw-bold text-dark">
                                    ₹{{ number_format(
                                    $processedEmployees->sum(function ($emp) {
                                    return $emp->processedSalary->ps_monthly_net_salary ?? 0;
                                    }),
                                    2,
                                    ) }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table mb-0 payslip-list-table" id="payslipTable">
                        <thead>
                            <tr>
                                <th class="ps-4">Employee</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th class="text-center">Salaried Days</th>
                                <th class="text-end">Net Pay</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($processedEmployees as $employee)
                            <tr class="employee-row" data-name="{{ strtolower($employee->emp_full_name) }}"
                                data-designation="{{ strtolower($employee->designation_name) }}"
                                data-department="{{ strtolower($employee->department_name) }}"
                                data-code="{{ strtolower($employee->emp_code) }}"
                                data-processed-salary-id="{{ $employee->processedSalary?->ps_id ?? '' }}">
                                <td class="ps-4">
                                    <div class="employee-cell-mini">
                                        <div class="employee-avatar-mini">
                                            {{ strtoupper(substr($employee->emp_full_name, 0, 1)) }}
                                        </div>
                                        <div class="employee-info-mini">
                                            <div class="employee-name text-dark">{{ $employee->emp_full_name }}
                                            </div>
                                            <div class="employee-code">{{ $employee->emp_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    {{ $employee->designation_name }}
                                </td>
                                <td class="text-muted">
                                    {{ $employee->department_name }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light">
                                        {{ $employee->processedSalary->ps_total_days_worked ?? 0 }} Days
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    ₹{{ number_format($employee->processedSalary->ps_monthly_net_salary ?? 0, 2) }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group-actions">
                                        <!-- Preview Button - Eye Icon Only -->
                                        <button class="btn btn-sm btn-preview btn-icon-only"
                                            onclick="viewPayslip({{ $employee->emp_id }}, {{ $payrollPeriod->pp_id }})"
                                            title="Preview Payslip"
                                            aria-label="Preview payslip for {{ $employee->emp_full_name }}"
                                            data-bs-toggle="tooltip">
                                            <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                                        </button>
                                        <!-- Download Button - Download Icon Only -->
                                        <!-- Temporary fix with direct URL -->
                                        @if ($employee->processedSalary)
                                        @php
                                        $processedSalaryId = $employee->processedSalary->ps_id;
                                        // Direct URL use करें
                                        $downloadUrl =
                                        url("/payroll/payroll-new/download-payslip/{$processedSalaryId}");
                                        @endphp

                                        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-download btn-icon-only"
                                            title="Download PDF"
                                            onclick="console.log('Direct Download URL:', '{{ $downloadUrl }}'); return true;"
                                            data-bs-toggle="tooltip">
                                            <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                                        </a>
                                        @endif

                                        <!-- More Options Button -->
                                        {{-- <div class="dropdown">
                                            <button class="btn btn-sm btn-light btn-icon-only dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" title="More options"
                                                aria-label="More options for {{ $employee->emp_full_name }}">
                                                <i data-lucide="more-horizontal" style="width: 16px; height: 16px;"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="viewPayslip({{ $employee->emp_id }}, {{ $payrollPeriod->pp_id }})">
                                                        <i data-lucide="eye" class="me-2"
                                                            style="width: 14px; height: 14px;"></i>
                                                        Preview
                                                    </a>
                                                </li>
                                                @if ($employee->processedSalary &&
                                                $employee->processedSalary->ps_payslip_url)
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ $employee->processedSalary->ps_payslip_url }}"
                                                        target="_blank">
                                                        <i data-lucide="download" class="me-2"
                                                            style="width: 14px; height: 14px;"></i>
                                                        Download
                                                    </a>
                                                </li>
                                                @endif
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="viewEmployeeDetails({{ $employee->emp_id }})">
                                                        <i data-lucide="user" class="me-2"
                                                            style="width: 14px; height: 14px;"></i>
                                                        View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        onclick="resendPayslip({{ $employee->emp_id }})">
                                                        <i data-lucide="mail" class="me-2"
                                                            style="width: 14px; height: 14px;"></i>
                                                        Resend Email
                                                    </a>
                                                </li>
                                            </ul>
                                        </div> --}}
                                    </div>
                                </td>


                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i data-lucide="file-text"></i>
                                        </div>
                                        <h5 class="text-dark mb-2">No payslips available</h5>
                                        <p class="text-muted mb-4">Process salaries first to generate payslips.</p>
                                        <a href="{{ route('payroll.new.process', ['period' => $payrollPeriod->pp_id]) }}"
                                            class="btn btn-primary d-flex align-items-center gap-2 mx-auto"
                                            style="width: fit-content;">
                                            <i data-lucide="play-circle"></i>
                                            Process Salaries
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payslip Preview Modal -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">Payslip Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="payslipContent">
                <!-- Payslip content will be loaded here -->
                <div class="text-center py-5">
                    <div class="loading-spinner"></div>
                    <p class="mt-3 text-muted">Loading payslip preview...</p>
                </div>
            </div>
            <div class="modal-footer">
                <!-- Close Button - Secondary -->
                <button type="button" class="btn btn-light d-flex align-items-center gap-1" data-bs-dismiss="modal">
                    <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                    Close
                </button>

                <!-- Print Button - Outline -->
                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-1"
                    onclick="printPayslip()">
                    <i data-lucide="printer" style="width: 16px; height: 16px;"></i>
                    Print
                </button>

                   <a href="#"
                   id="downloadPayslipLink"
                   class="btn btn-primary d-flex align-items-center gap-1"
                   target="_blank" rel="noopener"
                    onclick="event.preventDefault(); const u = this.getAttribute('href'); if (!u || u === '#' || u === '') { if (typeof showError === 'function') { showError('Payslip is still loading or download is not available.'); } return false; } window.open(u, '_blank'); return false;">
                    <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                    Download PDF
                </a>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
    // Smart Back Navigation
    function goBackSmart() {
        if (document.referrer && document.referrer.includes(window.location.hostname)) {
            window.history.back();
        } else {
            window.location.href = "{{ route('payroll.cycles') }}";
        }
    }

    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add other initialization code here
        initPayslipPage();
    });

    function initPayslipPage() {
        // Enhanced Search functionality
        const searchInput = document.getElementById('payslipSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#payslipTable tbody tr.employee-row');

                if (searchTerm === '') {
                    rows.forEach(row => row.style.display = '');
                    return;
                }

                rows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    const designation = row.getAttribute('data-designation');
                    const department = row.getAttribute('data-department');
                    const code = row.getAttribute('data-code');


                    if (name.includes(searchTerm) ||
                        designation.includes(searchTerm) ||
                        code.includes(searchTerm) ||
                        department.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    }

    // Store current payslip URL for download
    const PAYSLIP_DOWNLOAD_BASE = @json(url('/payroll/payroll-new/download-payslip'));
    let currentPayslipUrl = '';
    let currentPsId = '';
    let currentEmployeeName = '';

    // View payslip function (Preview)
   function viewPayslip(employeeId, payrollId) {
        console.log('Fetching payslip for employee:', employeeId, 'payroll:', payrollId);

        // Get employee name
        const row = document.querySelector(`button[onclick*="viewPayslip(${employeeId}, ${payrollId})"]`)?.closest('tr');
        if (row) {
            currentEmployeeName = row.querySelector('.employee-name').textContent;
            currentPsId = (row.dataset.processedSalaryId || '').trim();
        } else {
            currentPsId = '';
        }

        const downloadPayslipLink = document.getElementById('downloadPayslipLink');
        if (downloadPayslipLink) {
            downloadPayslipLink.setAttribute('href', '#');
        }

        // Show loading
        document.getElementById('payslipContent').innerHTML = `
            <div class="text-center py-5">
                <div class="loading-spinner"></div>
                <p class="mt-3 text-muted">Loading payslip for ${currentEmployeeName || 'employee'}...</p>
            </div>
        `;

        // Reset current payslip URL
        currentPayslipUrl = '';

        // Create URL
        const url = `/payroll/payroll-new/employee-payslip/${employeeId}?payroll_id=${payrollId}`;
        console.log('Fetch URL:', url);

        // Fetch payslip data with timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);

        fetch(url, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            })
            .then(response => {
                console.log('Response status:', response.status, response.statusText);
                clearTimeout(timeoutId);

                if (!response.ok) {
                    console.error('Response not OK:', response);
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success && data.html) {
                    document.getElementById('payslipContent').innerHTML = data.html;

                    // Update modal title
                    document.querySelector('#payslipModal .modal-title').textContent =
                        `Payslip - ${currentEmployeeName}`;

                    // Try to extract payslip URL from the HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    const downloadLink = tempDiv.querySelector('a[href*="payslip"]');
                    if (downloadLink && downloadLink.href) {
                        currentPayslipUrl = downloadLink.href;
                    } else if (currentPsId) {
                        currentPayslipUrl = PAYSLIP_DOWNLOAD_BASE + '/' + currentPsId;
                    }

                    const downloadPayslipLink = document.getElementById('downloadPayslipLink');
                    if (downloadPayslipLink && currentPayslipUrl) {
                        downloadPayslipLink.setAttribute('href', currentPayslipUrl);
                    }

                    // Reinitialize icons in modal
                    setTimeout(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }, 100);
                } else {
                    showError('Failed to load payslip: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Fetch error:', error);
                if (error.name === 'AbortError') {
                    showError('Request timeout. Please try again.');
                } else if (error.message.includes('HTTP error')) {
                    showError(`Server error: ${error.message}`);
                } else {
                    showError('Error loading payslip. Please try again.');
                }
            });

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('payslipModal'));
        modal.show();
    }


    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#3b82f6'
        });
    }


    // Add missing functions
    function viewEmployeeDetails(empId) {
        alert('View employee details for ID: ' + empId);
        // Implement your logic here
        // window.location.href = '/employees/' + empId;
    }

    function resendPayslip(empId) {
        if (confirm('Are you sure you want to resend payslip email to this employee?')) {
            showToast('Resending email...', 'info');

            // Use the correct route for your application
            fetch(`/payroll/resend-payslip/${empId}?payroll_id={{ $payrollPeriod->pp_id }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Payslip email resent successfully!', 'success');
                } else {
                    showToast('Failed to resend: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error resending email. Please try again.', 'error');
            });
        }
    }

    function printPayslip() {
        const printContent = document.getElementById('payslipContent').innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;

        // Reinitialize after print
        window.location.reload();
    }


    function downloadPayslipFromModal() {
        console.log('Download button clicked in modal');
        console.log('currentPayslipUrl:', currentPayslipUrl);

        // First priority: Use stored URL
        if (currentPayslipUrl && currentPayslipUrl !== '') {
            console.log('Using stored URL:', currentPayslipUrl);

            // Open in new tab to trigger download
            const downloadWindow = window.open(currentPayslipUrl, '_blank');

            // If popup blocked, try direct navigation
            if (!downloadWindow || downloadWindow.closed || typeof downloadWindow.closed === 'undefined') {
                console.log('Popup blocked, trying direct navigation');
                window.location.href = currentPayslipUrl;
            }
            return;
        }

        // Second priority: Try to find any download link in the modal content
        const payslipContent = document.getElementById('payslipContent');
        if (payslipContent) {
            // Try to find any link that contains 'payslip', 'download', or 'pdf'
            const downloadLinks = payslipContent.querySelectorAll('a[href*="payslip"], a[href*="download"], a[href$=".pdf"]');

            console.log('Found download links:', downloadLinks.length);

            for (let link of downloadLinks) {
                if (link.href) {
                    console.log('Found download link:', link.href);
                    window.open(link.href, '_blank');
                    return;
                }
            }
        }

        // Third priority: Try to find any button with download functionality
        const downloadBtn = document.querySelector('#payslipContent .btn-download, #payslipContent a[title*="Download"]');
        if (downloadBtn && downloadBtn.href) {
            console.log('Found download button:', downloadBtn.href);
            window.open(downloadBtn.href, '_blank');
            return;
        }

        // If all else fails, show error
        showError('Download link not available. Please try refreshing the page or contact support.');
    }



    function showToast(message, type = 'info') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    }


    // Download payslip handler
    function handlePayslipDownload(event, processedSalaryId, employeeName) {
        // Optional: Show loading spinner
        const button = event.target.closest('a');
        const originalContent = button.innerHTML;

        // Add loading spinner
        button.innerHTML = '<span class="loading-spinner" style="width: 16px; height: 16px; border-width: 2px;"></span>';
        button.disabled = true;

        console.log('Downloading payslip for:', employeeName);
        console.log('Processed Salary ID:', processedSalaryId);

        // Reset button after 3 seconds
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.disabled = false;
        }, 3000);

        // Let the link work normally - PDF download हो जाएगा
        return true;
    }

    // Alternative: अगर automatic download चाहिए
    function handlePayslipDownloadAuto(event, processedSalaryId, employeeName) {
        event.preventDefault();

        // Show loading
        const button = event.target.closest('a');
        const originalContent = button.innerHTML;
        button.innerHTML = '<span class="loading-spinner"></span>';
        button.disabled = true;

        // Create download URL
        const url = `/download-payslip/${processedSalaryId}`;

        // Method 1: Hidden iframe से download
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);

        // Method 2: Fetch और blob download
        fetch(url)
            .then(response => response.blob())
            .then(blob => {
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = `Payslip-${employeeName.replace(/\s+/g, '_')}.pdf`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(downloadUrl);
            })
            .catch(error => {
                console.error('Download error:', error);
                // Fallback: original link open करें
                window.open(url, '_blank');
            });

        // Reset button
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.disabled = false;
            if (iframe.parentNode) {
                document.body.removeChild(iframe);
            }
        }, 3000);

        return false;
    }


</script>
@endsection
