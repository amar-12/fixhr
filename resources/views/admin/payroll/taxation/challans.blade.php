@extends('admin.layout.master')

@section('title', 'Challan Details - FixHR')

@section('css')
<style>
    .challan-page {
        color: #0f172a;
    }

    .page-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
        min-width: 190px;
    }

    .filter-chip label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin: 0;
        white-space: nowrap;
    }

    .filter-chip select {
        border: 0;
        padding: 0;
        height: auto;
        font-weight: 600;
        color: #0f172a;
        background: transparent;
        box-shadow: none !important;
    }

    .stat-card {
        border: 0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .stat-icon.primary { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .stat-icon.success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .stat-icon.warning { background: rgba(245, 158, 11, 0.14); color: #d97706; }

    .panel-card {
        border: 0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .panel-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .panel-title .dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #3b82f6;
    }

    .emp-picker {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        overflow: hidden;
    }

    .emp-picker-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding: 10px 12px;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
    }

    .emp-picker-search-wrap {
        position: relative;
        flex: 1;
        min-width: 180px;
    }

    .emp-picker-search-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 13px;
        pointer-events: none;
    }

    .emp-picker-search {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px 8px 34px;
        font-size: 13px;
        background: #fff;
        color: #0f172a;
    }

    .emp-picker-search:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .emp-picker-count {
        font-size: 12px;
        font-weight: 700;
        color: #2563eb;
        background: rgba(59, 130, 246, 0.1);
        border-radius: 999px;
        padding: 4px 10px;
        white-space: nowrap;
    }

    .emp-picker-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .emp-picker-actions .btn {
        font-size: 12px;
        padding: 5px 10px;
        border-radius: 8px;
    }

    .emp-picker-list {
        max-height: 280px;
        overflow-y: auto;
        padding: 8px;
    }

    .emp-picker-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 12px;
        margin-bottom: 6px;
        border: 1px solid transparent;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    }

    .emp-picker-item:last-child {
        margin-bottom: 0;
    }

    .emp-picker-item:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
    }

    .emp-picker-item.selected {
        border-color: #93c5fd;
        background: #eff6ff;
        box-shadow: inset 3px 0 0 #3b82f6;
    }

    .emp-picker-item input[type="checkbox"] {
        margin-top: 3px;
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        cursor: pointer;
        accent-color: #2563eb;
    }

    .emp-picker-item-body {
        min-width: 0;
        flex: 1;
    }

    .emp-picker-name {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }

    .emp-picker-meta {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
        line-height: 1.35;
    }

    .emp-picker-empty {
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
        padding: 28px 12px;
    }

    .table thead th {
        background: #f8fafc;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .dark-mode .challan-page,
    [data-theme="dark"] .challan-page {
        color: #e5e7eb;
    }

    .dark-mode .challan-page .text-muted,
    [data-theme="dark"] .challan-page .text-muted {
        color: #94a3b8 !important;
    }

    .dark-mode .challan-page .filter-chip,
    [data-theme="dark"] .challan-page .filter-chip {
        border-color: #334155;
        background: #111827;
    }

    .dark-mode .challan-page .filter-chip label,
    [data-theme="dark"] .challan-page .filter-chip label {
        color: #cbd5e1;
    }

    .dark-mode .challan-page .filter-chip select,
    [data-theme="dark"] .challan-page .filter-chip select {
        color: #e2e8f0;
    }

    .dark-mode .challan-page .stat-card,
    .dark-mode .challan-page .panel-card,
    [data-theme="dark"] .challan-page .stat-card,
    [data-theme="dark"] .challan-page .panel-card {
        background: #0f172a;
        border: 1px solid #1f2937;
        box-shadow: none;
    }

    .dark-mode .challan-page .panel-card .card-header,
    [data-theme="dark"] .challan-page .panel-card .card-header {
        background: #111827;
        border-bottom-color: #1f2937;
    }

    .dark-mode .challan-page .form-control,
    .dark-mode .challan-page .form-control:focus,
    [data-theme="dark"] .challan-page .form-control,
    [data-theme="dark"] .challan-page .form-control:focus {
        background: #111827;
        color: #e5e7eb;
        border-color: #334155;
    }

    .dark-mode .challan-page .emp-picker,
    [data-theme="dark"] .challan-page .emp-picker {
        background: #0b1220;
        border-color: #334155;
    }

    .dark-mode .challan-page .emp-picker-toolbar,
    [data-theme="dark"] .challan-page .emp-picker-toolbar {
        background: #111827;
        border-bottom-color: #334155;
    }

    .dark-mode .challan-page .emp-picker-search,
    [data-theme="dark"] .challan-page .emp-picker-search {
        background: #0f172a;
        color: #e5e7eb;
        border-color: #334155;
    }

    .dark-mode .challan-page .emp-picker-item,
    [data-theme="dark"] .challan-page .emp-picker-item {
        background: #111827;
    }

    .dark-mode .challan-page .emp-picker-item:hover,
    .dark-mode .challan-page .emp-picker-item.selected,
    [data-theme="dark"] .challan-page .emp-picker-item:hover,
    [data-theme="dark"] .challan-page .emp-picker-item.selected {
        background: #172554;
        border-color: #3b82f6;
    }

    .dark-mode .challan-page .emp-picker-name,
    [data-theme="dark"] .challan-page .emp-picker-name {
        color: #f1f5f9;
    }

    .dark-mode .challan-page .emp-picker-meta,
    [data-theme="dark"] .challan-page .emp-picker-meta {
        color: #94a3b8;
    }

    .dark-mode .challan-page .form-control::placeholder,
    [data-theme="dark"] .challan-page .form-control::placeholder {
        color: #94a3b8;
    }

    .dark-mode .challan-page .btn.btn-light,
    [data-theme="dark"] .challan-page .btn.btn-light {
        background: #1f2937;
        border-color: #334155;
        color: #e2e8f0;
    }

    .dark-mode .challan-page .table,
    .dark-mode .challan-page .table td,
    .dark-mode .challan-page .table th,
    [data-theme="dark"] .challan-page .table,
    [data-theme="dark"] .challan-page .table td,
    [data-theme="dark"] .challan-page .table th {
        color: #e5e7eb;
        border-color: #334155 !important;
    }

    .dark-mode .challan-page .table thead th,
    [data-theme="dark"] .challan-page .table thead th {
        background: #111827;
        color: #f1f5f9;
        border-bottom-color: #334155 !important;
    }
</style>
@endsection

@section('content')
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="#">Payroll</a></li>
                        <li><a href="#">Taxation</a></li>
                        <li class="active"><span><b>Challan Details</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @php
        $savedCount = is_countable($challans ?? null) ? count($challans) : (is_object($challans ?? null) && method_exists($challans, 'count') ? $challans->count() : 0);
        $employeeCount = is_countable($employees ?? null) ? count($employees) : (is_object($employees ?? null) && method_exists($employees, 'count') ? $employees->count() : 0);
        $formLabel = ($forms ?? [])[$selectedFormKey ?? ''] ?? ($selectedFormKey ?? 'Form');
    @endphp

    <div class="challan-page">
    <div class="mb-3">
        <div class="page-title-row">
            <div>
                <h3 class="mb-1">Challan Details</h3>
                <div class="text-muted">Maintain challan info used while generating PDFs (quarter validations apply).</div>
            </div>

            <form method="GET" action="{{ route('challans.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                <div class="filter-chip">
                    <label>Form</label>
                    <select name="form_key" onchange="this.form.submit()">
                        @foreach(($forms ?? []) as $key => $label)
                            <option value="{{ $key }}" {{ ($selectedFormKey ?? '') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-chip">
                    <label>FY</label>
                    <select name="financial_year_id" onchange="this.form.submit()">
                        @foreach(($financialYears ?? []) as $year)
                            <option value="{{ $year->fy_id }}" {{ (int) ($selectedFYId ?? 0) === (int) $year->fy_id ? 'selected' : '' }}>
                                {{ $year->fy_year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-chip">
                    <label>Department</label>
                    <select name="department_id" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach(($departments ?? []) as $dept)
                            <option value="{{ $dept->d_id }}" {{ (int) ($selectedDepartmentId ?? 0) === (int) $dept->d_id ? 'selected' : '' }}>
                                {{ $dept->d_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-chip">
                    <label>Designation</label>
                    <select name="designation_id" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach(($designations ?? []) as $desig)
                            <option value="{{ $desig->dg_id }}" {{ (int) ($selectedDesignationId ?? 0) === (int) $desig->dg_id ? 'selected' : '' }}>
                                {{ $desig->dg_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Fallback alerts (kept hidden; Swal will show message) --}}
    @if(session('success'))
        <div class="alert alert-success d-none" id="serverSuccessMsg">{{ session('success') }}</div>
    @endif
    @if(session('denied'))
        <div class="alert alert-danger d-none" id="serverErrorMsg">{{ session('denied') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon primary">F</div>
                    <div>
                        <div class="text-muted small">Selected Form</div>
                        <div class="fw-bold">{{ $formLabel }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon warning">FY</div>
                    <div>
                        <div class="text-muted small">Financial Year</div>
                        <div class="fw-bold">
                            {{ optional(($financialYears ?? collect())->firstWhere('fy_id', (int) ($selectedFYId ?? 0)))->fy_year ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon success">{{ $savedCount }}</div>
                    <div>
                        <div class="text-muted small">Saved challans</div>
                        <div class="fw-bold">{{ $savedCount }} record(s)</div>
                        <div class="text-muted small">{{ $employeeCount }} employee(s) in current filter</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card panel-card h-100">
                <div class="card-header">
                    <div class="panel-title">
                        <span class="dot"></span>
                        <div>
                            <div class="fw-bold">Add / Update Challan</div>
                            <div class="text-muted small">Save once for multiple employees.</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('challans.upsert') }}">
                        @csrf
                        <input type="hidden" name="financial_year_id" value="{{ $selectedFYId }}">
                        <input type="hidden" name="form_key" value="{{ $selectedFormKey }}">

                        <div class="form-group">
                            <label class="mb-2 fw-semibold d-block">Employees</label>
                            <div class="emp-picker" id="employeePicker">
                                <div class="emp-picker-toolbar">
                                    <div class="emp-picker-search-wrap">
                                        <i class="fa fa-search"></i>
                                        <input type="text"
                                               id="employeePickerSearch"
                                               class="emp-picker-search"
                                               placeholder="Search by name, code, department..."
                                               autocomplete="off">
                                    </div>
                                    <span class="emp-picker-count" id="employeePickerCount">0 selected</span>
                                    <div class="emp-picker-actions">
                                        <button type="button" class="btn btn-sm btn-light" onclick="selectVisibleEmployees(true)">Select visible</button>
                                        <button type="button" class="btn btn-sm btn-light" onclick="selectAllEmployees(true)">Select all</button>
                                        <button type="button" class="btn btn-sm btn-light" onclick="selectAllEmployees(false)">Clear</button>
                                    </div>
                                </div>
                                <div class="emp-picker-list" id="employeePickerList">
                                    @forelse(($employees ?? []) as $emp)
                                        @php
                                            $deptName = optional($emp->fh_department)->d_name;
                                            $desigName = optional($emp->fh_designation)->dg_name;
                                            $searchText = strtolower(trim(implode(' ', array_filter([
                                                $emp->emp_full_name,
                                                $emp->emp_code,
                                                $deptName,
                                                $desigName,
                                            ]))));
                                        @endphp
                                        <label class="emp-picker-item"
                                               data-search="{{ $searchText }}"
                                               for="emp_pick_{{ $emp->emp_id }}">
                                            <input type="checkbox"
                                                   class="emp-picker-checkbox"
                                                   id="emp_pick_{{ $emp->emp_id }}"
                                                   name="employee_ids[]"
                                                   value="{{ $emp->emp_id }}">
                                            <span class="emp-picker-item-body">
                                                <span class="emp-picker-name">
                                                    {{ $emp->emp_full_name }}{{ $emp->emp_code ? ' (' . $emp->emp_code . ')' : '' }}
                                                </span>
                                                @if($deptName || $desigName)
                                                    <span class="emp-picker-meta">
                                                        {{ $deptName ?: '—' }}{{ $deptName && $desigName ? ' · ' : '' }}{{ $desigName ?: '' }}
                                                    </span>
                                                @endif
                                            </span>
                                        </label>
                                    @empty
                                        <div class="emp-picker-empty">No employees found for selected financial year.</div>
                                    @endforelse
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">Click rows or checkboxes to select multiple employees easily.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold">Quarter</label>
                                    <select name="quarter" id="challanQuarterSelect" class="form-control" required>
                                        <option value="Q1">Q1 (Apr–Jun)</option>
                                        <option value="Q2">Q2 (Jul–Sep)</option>
                                        <option value="Q3">Q3 (Oct–Dec)</option>
                                        <option value="Q4">Q4 (Jan–Mar)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold">Challan Date</label>
                                    <input type="date"
                                           name="challan_date"
                                           id="challanDateInput"
                                           class="form-control"
                                           required>
                                    <small class="text-muted d-block mt-1" id="challanDateHint">
                                        Select quarter to see allowed challan month.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="fw-semibold">Receipt No.</label>
                            <input type="text" name="receipt_no" class="form-control" required maxlength="100">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold">BSR Code</label>
                                    <input type="text" name="bsr_code" class="form-control" required maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold">Serial No. (optional)</label>
                                    <input type="text" name="challan_serial_no" class="form-control" maxlength="30">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Challan Details</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card panel-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="panel-title">
                        <span class="dot" style="background:#22c55e;"></span>
                        <div>
                            <div class="fw-bold">Saved Challans</div>
                            <div class="text-muted small">Showing saved records for selected Form + FY.</div>
                        </div>
                    </div>
                    <div class="text-muted small">Total: <strong>{{ $savedCount }}</strong></div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Quarter</th>
                                    <th>Receipt No.</th>
                                    <th>BSR</th>
                                    <th>Challan Date</th>
                                    <th>Serial</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($challans ?? []) as $row)
                                    <tr>
                                        <td>{{ $employees->firstWhere('emp_id', $row->f16ac_emp_id)?->emp_full_name ?? ('Emp #' . $row->f16ac_emp_id) }}</td>
                                        <td>{{ $row->f16ac_quarter }}</td>
                                        <td>{{ $row->f16ac_receipt_no }}</td>
                                        <td>{{ $row->f16ac_bsr_code }}</td>
                                        <td>{{ optional($row->f16ac_challan_date)->format('d/m/Y') }}</td>
                                        <td>{{ $row->f16ac_challan_serial_no ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No challan details saved for this selection yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-muted small mt-2">
                        Note: Generation will be blocked until challan details are added as per the form’s validations.
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('script')
<script>
    const CHALLAN_FY_ID = @json((int) ($selectedFYId ?? 0));
    const CHALLAN_FY_START = @json($selectedFyStartDate ?? null);
    const CHALLAN_FY_END = @json($selectedFyEndDate ?? null);
    const CHALLAN_SERVER_TODAY = @json($todayDate ?? now()->toDateString());
    const CHALLAN_QUARTER_AVAILABILITY_URL = @json(route('challans.quarter-salary-availability'));
    const CHALLAN_QUARTER_OPTIONS = [
        { value: 'Q1', label: 'Q1 (Apr–Jun)', index: 1 },
        { value: 'Q2', label: 'Q2 (Jul–Sep)', index: 2 },
        { value: 'Q3', label: 'Q3 (Oct–Dec)', index: 3 },
        { value: 'Q4', label: 'Q4 (Jan–Mar)', index: 4 },
    ];

    document.addEventListener('DOMContentLoaded', function () {
        const successEl = document.getElementById('serverSuccessMsg');
        const errorEl = document.getElementById('serverErrorMsg');

        if (typeof Swal !== 'undefined' && successEl && successEl.textContent.trim()) {
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: successEl.textContent.trim(),
                timer: 1800,
                showConfirmButton: false
            });
        }

        if (typeof Swal !== 'undefined' && errorEl && errorEl.textContent.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Unable to save',
                text: errorEl.textContent.trim()
            });
        }

        initEmployeePicker();
        initChallanDatePicker();
    });

    function parseChallanDateOnly(dateStr) {
        if (!dateStr) return null;
        const parts = String(dateStr).substring(0, 10).split('-').map(Number);
        if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) return null;
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function formatChallanDateInput(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function addMonthsToChallanDate(date, months) {
        const d = new Date(date.getTime());
        d.setMonth(d.getMonth() + months);
        return d;
    }

    function getChallanMonthAfterQuarter(quarter) {
        const fyStart = parseChallanDateOnly(CHALLAN_FY_START);
        if (!fyStart || !quarter) return null;

        const qIndex = parseInt(String(quarter).replace('Q', ''), 10);
        if (!qIndex || qIndex < 1 || qIndex > 4) return null;

        const quarterStart = addMonthsToChallanDate(fyStart, (qIndex - 1) * 3);
        const quarterEnd = addMonthsToChallanDate(quarterStart, 3);
        quarterEnd.setDate(quarterEnd.getDate() - 1);

        const nextMonthStart = new Date(quarterEnd.getFullYear(), quarterEnd.getMonth() + 1, 1);
        const nextMonthEnd = new Date(nextMonthStart.getFullYear(), nextMonthStart.getMonth() + 1, 0);

        return {
            min: nextMonthStart,
            max: nextMonthEnd,
            label: nextMonthStart.toLocaleDateString('en-IN', { month: 'long', year: 'numeric' }),
        };
    }

    function isChallanQuarterSelectable(quarter) {
        const today = parseChallanDateOnly(CHALLAN_SERVER_TODAY);
        const fyEnd = parseChallanDateOnly(CHALLAN_FY_END);
        const range = getChallanMonthAfterQuarter(quarter);

        if (!today || !range) {
            return false;
        }

        if (fyEnd && today.getTime() > fyEnd.getTime()) {
            return true;
        }

        return today.getTime() >= range.min.getTime();
    }

    function updateChallanQuarterOptions() {
        const quarterSelect = document.getElementById('challanQuarterSelect');
        if (!quarterSelect) return;

        const previousValue = quarterSelect.value;
        let firstEnabledValue = null;

        quarterSelect.innerHTML = CHALLAN_QUARTER_OPTIONS.map((option) => {
            const enabled = isChallanQuarterSelectable(option.value);
            if (enabled && firstEnabledValue === null) {
                firstEnabledValue = option.value;
            }

            const suffix = enabled ? '' : ' — not available yet';
            return `<option value="${option.value}" ${enabled ? '' : 'disabled'}>${option.label}${suffix}</option>`;
        }).join('');

        if (previousValue && isChallanQuarterSelectable(previousValue)) {
            quarterSelect.value = previousValue;
        } else if (firstEnabledValue) {
            quarterSelect.value = firstEnabledValue;
        }
    }

    function getSelectedEmployeeIds() {
        return Array.from(document.querySelectorAll('#employeePickerList .emp-picker-checkbox:checked'))
            .map((checkbox) => parseInt(checkbox.value, 10))
            .filter((id) => !Number.isNaN(id) && id > 0);
    }

    function revertChallanQuarter(previousQuarter) {
        const quarterSelect = document.getElementById('challanQuarterSelect');
        if (!quarterSelect) return;

        if (previousQuarter && isChallanQuarterSelectable(previousQuarter)) {
            quarterSelect.value = previousQuarter;
        } else {
            updateChallanQuarterOptions();
        }
    }

    async function validateChallanQuarterSelection(options = {}) {
        const {
            revertOnFail = false,
            previousQuarter = '',
            showAlert = true,
        } = options;

        const quarterSelect = document.getElementById('challanQuarterSelect');
        if (!quarterSelect || !CHALLAN_FY_ID || !CHALLAN_QUARTER_AVAILABILITY_URL) {
            return true;
        }

        const quarter = quarterSelect.value;
        const params = new URLSearchParams({
            financial_year_id: String(CHALLAN_FY_ID),
            quarter: quarter,
        });

        getSelectedEmployeeIds().forEach((employeeId) => {
            params.append('employee_ids[]', String(employeeId));
        });

        try {
            const response = await fetch(`${CHALLAN_QUARTER_AVAILABILITY_URL}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json().catch(() => ({}));
            if (response.ok && data.exists) {
                quarterSelect.dataset.lastValid = quarter;
                return true;
            }

            if (showAlert && typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Salary not available',
                    text: data.message || 'Salary is not available for this quarter. You cannot select this quarter.',
                });
            }

            if (revertOnFail) {
                revertChallanQuarter(previousQuarter || quarterSelect.dataset.lastValid || '');
            }

            return false;
        } catch (error) {
            return true;
        }
    }

    async function onChallanQuarterChange() {
        const quarterSelect = document.getElementById('challanQuarterSelect');
        if (!quarterSelect) return;

        const previousQuarter = quarterSelect.dataset.lastValid || '';
        const allowed = await validateChallanQuarterSelection({
            revertOnFail: true,
            previousQuarter,
            showAlert: true,
        });

        if (allowed || quarterSelect.dataset.lastValid) {
            updateChallanDateRange();
        }
    }

    function initChallanDatePicker() {
        const quarterSelect = document.getElementById('challanQuarterSelect');
        quarterSelect?.addEventListener('change', onChallanQuarterChange);
        updateChallanQuarterOptions();

        if (quarterSelect?.value) {
            quarterSelect.dataset.lastValid = quarterSelect.value;
        }

        updateChallanDateRange();
        validateChallanQuarterSelection({ showAlert: false });
    }

    function updateChallanDateRange() {
        const quarterSelect = document.getElementById('challanQuarterSelect');
        const dateInput = document.getElementById('challanDateInput');
        const hint = document.getElementById('challanDateHint');
        if (!quarterSelect || !dateInput) return;

        const range = getChallanMonthAfterQuarter(quarterSelect.value);
        if (!range) {
            dateInput.removeAttribute('min');
            dateInput.removeAttribute('max');
            dateInput.value = '';
            if (hint) hint.textContent = 'Financial year start date is required to limit challan month.';
            return;
        }

        const today = parseChallanDateOnly(CHALLAN_SERVER_TODAY);
        let maxDate = range.max;
        if (today && today.getTime() < maxDate.getTime()) {
            maxDate = today;
        }

        const minStr = formatChallanDateInput(range.min);
        const maxStr = formatChallanDateInput(maxDate);

        dateInput.min = minStr;
        dateInput.max = maxStr;

        if (dateInput.value && (dateInput.value < minStr || dateInput.value > maxStr)) {
            dateInput.value = '';
        }

        if (minStr > maxStr) {
            dateInput.value = '';
            dateInput.disabled = true;
            if (hint) {
                hint.textContent = `${range.label} is not available yet. Challan date opens from ${range.min.toLocaleDateString('en-IN')}.`;
            }
            return;
        }

        dateInput.disabled = false;

        if (!dateInput.value) {
            dateInput.value = maxStr;
        }

        if (hint) {
            hint.textContent = `Allowed challan dates: ${range.min.toLocaleDateString('en-IN')} to ${maxDate.toLocaleDateString('en-IN')} (${range.label})`;
        }
    }

    function initEmployeePicker() {
        const searchInput = document.getElementById('employeePickerSearch');
        const list = document.getElementById('employeePickerList');
        if (!list) return;

        searchInput?.addEventListener('input', filterEmployeePicker);

        list.querySelectorAll('.emp-picker-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', async () => {
                const wasChecked = checkbox.checked;

                if (wasChecked) {
                    const allowed = await validateChallanQuarterSelection({ showAlert: true });
                    if (!allowed) {
                        checkbox.checked = false;
                    }
                }

                syncEmployeePickerItemState(checkbox);
                updateEmployeePickerCount();
            });
        });

        list.querySelectorAll('.emp-picker-item').forEach((item) => {
            item.addEventListener('click', (event) => {
                if (event.target.matches('input[type="checkbox"]')) return;
                const checkbox = item.querySelector('.emp-picker-checkbox');
                if (!checkbox) return;
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        const form = list.closest('form');
        form?.addEventListener('submit', (event) => {
            const selected = list.querySelectorAll('.emp-picker-checkbox:checked').length;
            if (selected === 0) {
                event.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select employees',
                        text: 'Please select at least one employee.',
                    });
                }
            }
        });

        updateEmployeePickerCount();
    }

    function syncEmployeePickerItemState(checkbox) {
        const item = checkbox?.closest('.emp-picker-item');
        if (!item) return;
        item.classList.toggle('selected', checkbox.checked);
    }

    function updateEmployeePickerCount() {
        const countEl = document.getElementById('employeePickerCount');
        const list = document.getElementById('employeePickerList');
        if (!countEl || !list) return;

        const selected = list.querySelectorAll('.emp-picker-checkbox:checked').length;
        countEl.textContent = `${selected} selected`;
    }

    function filterEmployeePicker() {
        const query = (document.getElementById('employeePickerSearch')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('#employeePickerList .emp-picker-item');

        items.forEach((item) => {
            const haystack = item.dataset.search || '';
            item.style.display = !query || haystack.includes(query) ? '' : 'none';
        });
    }

    function selectAllEmployees(select) {
        document.querySelectorAll('#employeePickerList .emp-picker-checkbox').forEach((checkbox) => {
            checkbox.checked = !!select;
            syncEmployeePickerItemState(checkbox);
        });
        updateEmployeePickerCount();
    }

    function selectVisibleEmployees(select) {
        document.querySelectorAll('#employeePickerList .emp-picker-item').forEach((item) => {
            if (item.style.display === 'none') return;
            const checkbox = item.querySelector('.emp-picker-checkbox');
            if (!checkbox) return;
            checkbox.checked = select !== false;
            syncEmployeePickerItemState(checkbox);
        });
        updateEmployeePickerCount();
    }
</script>
@endsection

