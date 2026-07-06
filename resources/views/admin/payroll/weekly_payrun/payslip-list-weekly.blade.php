@extends('admin.layout.master')

@section('title', 'Payslip List - ' . ($payrollPeriod->pp_name ?? 'Weekly'))

@section('css')
<style>
    @media (max-width: 1400px) {
        .payroll-system>div { grid-template-columns: repeat(3, 1fr) !important; }
    }
    @media (max-width: 1024px) {
        .payroll-system>div { grid-template-columns: repeat(2, 1fr) !important; }
    }
    @media (max-width: 768px) {
        .payroll-system>div { grid-template-columns: 1fr !important; }
    }

    .scrollable-row {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 12px;
        padding: 12px 8px;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
    }
    .scrollable-row>div { flex: 0 0 auto; min-width: 200px; }
    .scrollable-row::-webkit-scrollbar { height: 6px; }
    .scrollable-row::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
    .scrollable-row::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
    .scrollable-row::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }
    .dark-mode .scrollable-row::-webkit-scrollbar-track { background: #374151; }
    .dark-mode .scrollable-row::-webkit-scrollbar-thumb { background: #4b5563; }
    .dark-mode .scrollable-row::-webkit-scrollbar-thumb:hover { background: #6b7280; }

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

    .head-text { font-size: 1.125rem; font-weight: 700; }
    .dark-mode .head-text { color: #fff !important; }

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

    .btn-preview {
        background-color: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.3);
        color: var(--payslip-primary);
        transition: all 0.2s ease;
    }
    .btn-preview:hover {
        background-color: var(--payslip-primary);
        border-color: var(--payslip-primary);
        color: #fff;
    }
    .btn-download {
        background-color: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.3);
        color: var(--payslip-success);
        transition: all 0.2s ease;
    }
    .btn-download:hover {
        background-color: var(--payslip-success);
        border-color: var(--payslip-success);
        color: #fff;
    }

    .payslip-card {
        background: var(--payslip-card-bg);
        border: 1px solid var(--payslip-border);
        border-radius: 12px;
        box-shadow: var(--payslip-shadow);
        overflow: hidden;
    }
    .payslip-card .card-body { color: var(--payslip-text); }

    .payslip-list-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid var(--payslip-border);
        border-radius: 8px;
        overflow: hidden;
        background: var(--payslip-card-bg);
    }
    .payslip-list-table thead { background: var(--payslip-header-bg); }
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
    .payslip-list-table tbody tr:last-child td { border-bottom: none; }
    .payslip-list-table tbody tr:hover { background-color: var(--payslip-hover-bg); }

    .employee-cell-mini { display: flex; align-items: center; gap: 12px; }
    .employee-avatar-mini {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--payslip-primary), #6366f1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
        color: #fff;
        flex-shrink: 0;
    }
    .employee-info-mini { min-width: 0; }
    .employee-name {
        font-weight: 600;
        color: var(--payslip-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }
    .employee-code { font-size: 0.75rem; color: var(--payslip-text-muted); }

    .text-muted { color: var(--payslip-text-muted) !important; }

    .modal-content {
        background-color: var(--payslip-card-bg);
        border: 1px solid var(--payslip-border);
        color: var(--payslip-text);
    }
    .modal-header {
        border-bottom: 1px solid var(--payslip-border);
        background: var(--payslip-header-bg);
    }
    .btn-close { filter: var(--bs-btn-close-filter, none); }
    .dark-mode .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

    .weekly-payslip-list-page .btn-outline-secondary {
        color: var(--payslip-text);
        border-color: var(--payslip-border);
    }
    .dark-mode .weekly-payslip-list-page .btn-outline-secondary { color: var(--payslip-light-text); }

    #weeklyPayslipPreviewModal .weekly-payslip-preview-panel {
        background: var(--payslip-card-bg);
        border-color: var(--payslip-border) !important;
        color: var(--payslip-text);
    }
    #weeklyPayslipPreviewModal .weekly-payslip-preview-panel .border { border-color: var(--payslip-border) !important; }
    #weeklyPayslipPreviewModal .weekly-payslip-preview-net {
        background: var(--payslip-header-bg) !important;
        border: 1px solid var(--payslip-border) !important;
        color: var(--payslip-text);
    }
    .weekly-preview-amount { color: var(--payslip-primary); }

    .weekly-payslip-list-page .form-control {
        background-color: var(--payslip-card-bg);
        border: 1px solid var(--payslip-border);
        color: var(--payslip-text);
        border-radius: 8px;
        padding: 8px 12px 8px 36px;
    }
    .weekly-payslip-list-page .form-control:focus {
        background-color: var(--payslip-card-bg);
        border-color: var(--payslip-primary);
        color: var(--payslip-text);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .weekly-payslip-list-page .form-control::placeholder {
        color: var(--payslip-text-muted);
    }

    .dark-mode ::-webkit-scrollbar { width: 8px; height: 8px; }
    .dark-mode ::-webkit-scrollbar-track { background: var(--payslip-header-bg); border-radius: 4px; }
    .dark-mode ::-webkit-scrollbar-thumb { background: var(--payslip-border); border-radius: 4px; }
    .dark-mode ::-webkit-scrollbar-thumb:hover { background: var(--payslip-text-muted); }

    @media (max-width: 768px) {
        .payslip-list-table th, .payslip-list-table td { padding: 12px 8px; }
        .employee-name { max-width: 120px; }
    }

    .payslip-action-btn {
        min-width: 44px;
        min-height: 44px;
        padding: 0.5rem 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
    .payslip-action-btn i, .payslip-action-btn [data-lucide] {
        width: 18px !important;
        height: 18px !important;
    }

    .text-dark { color: var(--payslip-text) !important; }
    .search-container { position: relative; width: 300px; max-width: 100%; }
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--payslip-text-muted);
        pointer-events: none;
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
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid var(--payslip-primary);
        border-right-color: transparent;
        border-radius: 50%;
        animation: weekly-payslip-spinner 0.75s linear infinite;
    }
    @keyframes weekly-payslip-spinner { to { transform: rotate(360deg); } }

    .weekly-period-summary-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid var(--payslip-border);
        border-radius: 8px;
        overflow: hidden;
        font-size: 0.8125rem;
    }
    .weekly-period-summary-table thead {
        background: var(--payslip-header-bg);
    }
    .weekly-period-summary-table th,
    .weekly-period-summary-table td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--payslip-border);
        color: var(--payslip-text);
    }
    .weekly-period-summary-table tbody tr:last-child td { border-bottom: none; }
</style>
@endsection

@section('content')
<div class="container-fluid py-2 weekly-payslip-list-page">
    <div class="p-0 mt-2">
        <div class="row">
            <div class="col-md-12">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
                    <li><a href="{{ route('payroll.cycles.weekly') }}" class="text-decoration-none text-dark">Weekly Payroll Cycles</a></li>
                    <li class="active"><span><b>Payslips</b></span></li>
                </ol>
            </div>
        </div>
    </div>
    <br>

    <div class="d-flex align-items-start gap-3 mb-4">
        <div class="flex-shrink-0">
            <button type="button" onclick="goBackWeeklyPayslip()"
                class="btn btn-light btn-icon rounded-circle d-flex align-items-center justify-content-center"
                style="width: 40px; height: 40px;" title="Go back" aria-label="Go back">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
            </button>
        </div>
        <div class="flex-grow-1">
            <h class="mb-1 text-dark">Payslips &amp; Reports</h>
            <p class="text-muted mb-0">
                {{ $payrollPeriod->pp_name }} •
                {{ \Carbon\Carbon::parse($payrollPeriod->pp_start_date)->format('d M Y') }} –
                {{ \Carbon\Carbon::parse($payrollPeriod->pp_end_date)->format('d M Y') }}
                <span class="d-block small mt-1">
                    Week {{ $week->ppw_week_number ?? '-' }}:
                    {{ \Carbon\Carbon::parse($week->ppw_start_date)->format('d M Y') }} – {{ \Carbon\Carbon::parse($week->ppw_end_date)->format('d M Y') }}
                </span>
            </p>
        </div>
    </div>

    <div class="payslip-card mb-6">
        <div class="card-body">
            @include('admin.payroll.partials.payroll-reports-by-master-mode', [
                'payrollPeriod' => $payrollPeriod,
                'week' => $week,
                'isWeeklyPayrollMode' => $isWeeklyPayrollMode ?? false,
                'layout' => 'payslip_grid',
            ])
        </div>
    </div>

    <div class="payslip-card">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom"
                style="border-color: var(--payslip-border)">
                <div class="search-container">
                    <div class="search-icon">
                        <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                    </div>
                    <input type="text" class="form-control" placeholder="Search employee..." id="weeklyPayslipSearch"
                        aria-label="Search employees">
                </div>
                <div class="text-muted small d-none d-md-block">
                    <span class="fw-semibold text-dark">{{ $processedSalaries->count() }}</span> Employees
                    @if ($processedSalaries->count() > 0)
                        • Net Pay: <span class="fw-semibold text-dark">
                            ₹{{ number_format((float) $processedSalaries->sum('ps_monthly_net_salary'), 2) }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="d-block d-md-none p-3 border-bottom" style="border-color: var(--payslip-border)">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-center p-2 rounded" style="background: var(--payslip-light-bg)">
                            <div class="small text-muted">Employees</div>
                            <div class="fw-bold text-dark">{{ $processedSalaries->count() }}</div>
                        </div>
                    </div>
                    @if ($processedSalaries->count() > 0)
                        <div class="col-6">
                            <div class="text-center p-2 rounded" style="background: var(--payslip-light-bg)">
                                <div class="small text-muted">Net Pay</div>
                                <div class="fw-bold text-dark">
                                    ₹{{ number_format((float) $processedSalaries->sum('ps_monthly_net_salary'), 2) }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table mb-0 payslip-list-table" id="weeklyPayslipTable">
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
                        @forelse($processedSalaries as $salary)
                            @php $emp = $salary->employee; @endphp
                            <tr class="employee-row"
                                data-name="{{ strtolower($emp->emp_full_name ?? '') }}"
                                data-designation="{{ strtolower($emp->fh_designation->dg_name ?? '') }}"
                                data-department="{{ strtolower($emp->fh_department->d_name ?? '') }}"
                                data-code="{{ strtolower($emp->emp_code ?? '') }}">
                                <td class="ps-4">
                                    <div class="employee-cell-mini">
                                        <div class="employee-avatar-mini">{{ strtoupper(substr($emp->emp_full_name ?? 'E', 0, 1)) }}</div>
                                        <div class="employee-info-mini">
                                            <div class="employee-name text-dark">{{ $emp->emp_full_name ?? 'N/A' }}</div>
                                            <div class="employee-code">{{ $emp->emp_code ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $emp->fh_designation->dg_name ?? 'N/A' }}</td>
                                <td class="text-muted">{{ $emp->fh_department->d_name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light">{{ $salary->ps_total_days_worked ?? 0 }} Days</span>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    ₹{{ number_format((float) ($salary->ps_monthly_net_salary ?? 0), 2) }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group-actions">
                                        <button type="button" class="btn btn-sm btn-preview btn-icon-only weekly-preview-btn"
                                            data-employee-id="{{ $emp->emp_id }}" title="Preview payslip"
                                            aria-label="Preview payslip for {{ $emp->emp_full_name ?? 'Employee' }}">
                                            <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                                        </button>
                                        <a href="{{ route('payroll.weekly.downloadPayslip', ['id' => $salary->ps_id]) }}"
                                            class="btn btn-sm btn-download btn-icon-only" title="Download PDF">
                                            <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    No weekly processed payslips found.
                                    <div class="mt-3">
                                        <a href="{{ route('payroll.weekly.process', ['periodId' => $payrollPeriod->pp_id, 'weekId' => $week->ppw_id]) }}"
                                            class="btn btn-primary btn-sm">Back to Weekly Process</a>
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

<div class="modal fade" id="weeklyPayslipPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">Payslip Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="weeklyPayslipPreviewContent">
                <div class="text-center py-5 text-muted">Select an employee to preview payslip.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function goBackWeeklyPayslip() {
        if (document.referrer && document.referrer.includes(window.location.hostname)) {
            window.history.back();
        } else {
            window.location.href = "{{ route('payroll.cycles.weekly') }}";
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        const searchInput = document.getElementById('weeklyPayslipSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#weeklyPayslipTable tbody tr.employee-row');
                if (searchTerm === '') {
                    rows.forEach(row => { row.style.display = ''; });
                    return;
                }
                rows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const designation = row.getAttribute('data-designation') || '';
                    const department = row.getAttribute('data-department') || '';
                    const code = row.getAttribute('data-code') || '';
                    if (name.includes(searchTerm) || designation.includes(searchTerm)
                        || code.includes(searchTerm) || department.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        const modalEl = document.getElementById('weeklyPayslipPreviewModal');
        const modalInstance = new bootstrap.Modal(modalEl);
        const contentEl = document.getElementById('weeklyPayslipPreviewContent');
        const previewUrlTemplate = `{{ route('payroll.weekly.employee.payslip', ['employeeId' => '__EMP__']) }}`;
        const payrollId = @json($payrollPeriod->pp_id);
        const weekId = @json($week->ppw_id);

        $(document).on('click', '.weekly-preview-btn', function() {
            const employeeId = $(this).data('employee-id');
            const previewUrl = previewUrlTemplate.replace('__EMP__', employeeId);

            contentEl.innerHTML = '<div class="text-center py-5"><div class="loading-spinner mx-auto mb-3"></div><p class="text-muted">Loading payslip preview...</p></div>';
            modalInstance.show();

            $.ajax({
                url: previewUrl,
                type: 'GET',
                data: {
                    payroll_id: payrollId,
                    week_id: weekId,
                    _token: '{{ csrf_token() }}'
                },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    if (response.success && response.html) {
                        contentEl.innerHTML = response.html;
                    } else {
                        contentEl.innerHTML = '<div class="alert alert-danger mb-0">Failed to load payslip preview.</div>';
                    }
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                },
                error: function(xhr) {
                    const message = xhr?.responseJSON?.message || 'Failed to load payslip preview.';
                    contentEl.innerHTML = `<div class="alert alert-danger mb-0">${message}</div>`;
                }
            });
        });
    });
</script>
@endsection
