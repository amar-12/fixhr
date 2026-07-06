@extends('admin.layout.master')

@section('title', $title)

@section('css')
<style>
    .salary-calc-menu .card { border-radius: 14px; border: 1px solid #e2e8f0; }
    .salary-calc-menu .card-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .salary-calc-menu .table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; }
    .salary-calc-menu .filter-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        padding: 12px;
        margin-bottom: 14px;
    }
    .salary-calc-menu .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 10px;
        background: #f8fafc;
    }
    .salary-calc-menu .filter-chip label {
        margin: 0;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        white-space: nowrap;
    }
    .salary-calc-menu .filter-chip .form-control,
    .salary-calc-menu .filter-chip .form-select {
        border: 0;
        background: transparent;
        box-shadow: none !important;
        min-width: 160px;
        padding: 0;
        font-weight: 600;
    }
    .salary-calc-menu .react-like-card {
        background: rgba(30, 32, 61, 0.04);
        border: 1px solid #dbe3f4;
        border-radius: 12px;
        padding: 14px;
    }
    .salary-calc-menu .react-like-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .15em;
        color: #2563eb;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .salary-calc-menu .quick-step {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #fff;
    }
    .salary-calc-menu .quick-step:last-child { margin-bottom: 0; }
    .salary-calc-menu .empty-note { color: #64748b; font-size: 12px; }
    .salary-calc-menu .selected-row { background: #eff6ff; }
    .salary-calc-menu .preview-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #dbeafe;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .dark-mode .salary-calc-menu .card,
    [data-theme="dark"] .salary-calc-menu .card { background: #1f2937; border-color: #374151; }
    .dark-mode .salary-calc-menu .filter-card,
    [data-theme="dark"] .salary-calc-menu .filter-card { background: #111827; border-color: #374151; }
    .dark-mode .salary-calc-menu .filter-chip,
    [data-theme="dark"] .salary-calc-menu .filter-chip { background: #0f172a; border-color: #334155; }
    .dark-mode .salary-calc-menu .filter-chip label,
    [data-theme="dark"] .salary-calc-menu .filter-chip label { color: #cbd5e1; }
    .dark-mode .salary-calc-menu .card-header,
    [data-theme="dark"] .salary-calc-menu .card-header { background: #111827; border-color: #374151; }
    .dark-mode .salary-calc-menu .table,
    .dark-mode .salary-calc-menu .table td,
    .dark-mode .salary-calc-menu .table th,
    .dark-mode .salary-calc-menu .text-muted,
    [data-theme="dark"] .salary-calc-menu .table,
    [data-theme="dark"] .salary-calc-menu .table td,
    [data-theme="dark"] .salary-calc-menu .table th,
    [data-theme="dark"] .salary-calc-menu .text-muted { color: #e5e7eb !important; border-color: #374151 !important; }
    .dark-mode .salary-calc-menu .react-like-card,
    [data-theme="dark"] .salary-calc-menu .react-like-card { background: rgba(30, 32, 61, 0.45); border-color: #374151; }
    .dark-mode .salary-calc-menu .quick-step,
    [data-theme="dark"] .salary-calc-menu .quick-step { background: #111827; border-color: #374151; }
    .dark-mode .salary-calc-menu .form-control,
    .dark-mode .salary-calc-menu .form-select,
    [data-theme="dark"] .salary-calc-menu .form-control,
    [data-theme="dark"] .salary-calc-menu .form-select { background: #0f172a; border-color: #374151; color: #e5e7eb; }
    .dark-mode .salary-calc-menu .selected-row,
    [data-theme="dark"] .salary-calc-menu .selected-row { background: rgba(30, 58, 138, 0.3); }
    .dark-mode .salary-calc-menu .preview-badge,
    [data-theme="dark"] .salary-calc-menu .preview-badge { background: rgba(37, 99, 235, 0.25); color: #bfdbfe; }
</style>
@endsection

@section('content')
<div class="salary-calc-menu">
    @php
        $previewEmpId = (int) request('preview_emp', 0);
        $previewEmployee = $employees->firstWhere('emp_id', $previewEmpId);
    @endphp
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background:none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="#">Payroll</a></li>
                <li class="active"><span><b>{{ $title }}</b></span></li>
            </ol>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-12">
            <div class="filter-card">
                <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
                    <div class="filter-chip">
                        <label><i class="fe fe-search"></i></label>
                        <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search name / code">
                    </div>
                    <div class="filter-chip">
                        <label><i class="fe fe-sliders"></i> Status</label>
                        <select class="form-select" name="status">
                            <option value="">All</option>
                            <option value="71" {{ (string)$status === '71' ? 'selected' : '' }}>Active</option>
                            <option value="72" {{ (string)$status === '72' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="fe fe-filter me-1"></i> Filter
                    </button>
                    <a href="{{ url()->current() }}" class="btn btn-light btn-sm">
                        <i class="fe fe-x me-1"></i> Reset
                    </a>
                </form>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">Employees List</h4>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Total: {{ $employees->total() }}</span>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#salaryCalculatorPanelModal">
                            <i class="fe fe-grid me-1"></i> Salary Calculator Panel
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Emp Code</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($employees as $emp)
                                <tr>
                                    <td>{{ $emp->emp_code ?? '-' }}</td>
                                    <td>{{ $emp->emp_full_name }}</td>
                                    <td>{{ optional($emp->fh_department)->d_name ?? '-' }}</td>
                                    <td>{{ optional($emp->fh_designation)->dg_name ?? '-' }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-light"
                                           href="{{ request()->fullUrlWithQuery(['preview_emp' => $emp->emp_id]) }}">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('employee.salaries.addEdit', \Illuminate\Support\Facades\Crypt::encrypt($emp->emp_id)) }}">
                                            <i class="fe fe-calculator me-1"></i> Create
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No employees found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="salaryCalculatorPanelModal" tabindex="-1" aria-labelledby="salaryCalculatorPanelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salaryCalculatorPanelModalLabel">Salary Calculator Panel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="react-like-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="react-like-label mb-0">
                            <i class="fe fe-calculator"></i> React-style Salary Calculator
                        </label>
                        <span class="preview-badge">
                            <i class="fe fe-zap"></i> Quick Start
                        </span>
                    </div>

                    @if($previewEmployee)
                        <div class="quick-step">
                            <div class="fw-semibold">{{ $previewEmployee->emp_full_name }}</div>
                            <div class="small text-muted">
                                {{ $previewEmployee->emp_code ?? 'No Code' }} |
                                {{ optional($previewEmployee->fh_department)->d_name ?? 'No Dept' }} |
                                {{ optional($previewEmployee->fh_designation)->dg_name ?? 'No Designation' }}
                            </div>
                            <a class="btn btn-primary btn-sm mt-2"
                               href="{{ route('employee.salaries.addEdit', \Illuminate\Support\Facades\Crypt::encrypt($previewEmployee->emp_id)) }}">
                                Open Salary Calculator
                            </a>
                        </div>
                    @else
                        <div class="quick-step">
                            <div class="fw-semibold">Select an employee</div>
                            <div class="small text-muted">Click Preview in the list to load employee details here.</div>
                        </div>
                    @endif

                    <div class="quick-step">
                        <div class="fw-semibold">Step 1: Input basis</div>
                        <div class="small text-muted">Choose CTC/Gross and Monthly/Annual mode in calculator.</div>
                    </div>
                    <div class="quick-step">
                        <div class="fw-semibold">Step 2: Components</div>
                        <div class="small text-muted">Adjust earnings/deductions and review computed totals.</div>
                    </div>
                    <div class="quick-step">
                        <div class="fw-semibold">Step 3: Save structure</div>
                        <div class="small text-muted">Finalize and save payroll structure for employee.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const previewParam = new URLSearchParams(window.location.search).get('preview_emp');
        const previewModalEl = document.getElementById('salaryCalculatorPanelModal');
        const openPreviewModal = function () {
            if (!previewModalEl || typeof bootstrap === 'undefined') return;
            const previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
            previewModal.show();
        };

        if (!previewParam) return;

        document.querySelectorAll('tbody tr').forEach(function (row) {
            const previewLink = row.querySelector('a[href*="preview_emp="]');
            if (!previewLink) return;

            try {
                const url = new URL(previewLink.href);
                if (url.searchParams.get('preview_emp') === previewParam) {
                    row.classList.add('selected-row');
                    openPreviewModal();
                }
            } catch (e) {
                // Ignore malformed URLs; row highlight is non-critical.
            }
        });
    });
</script>
@endsection
