@extends('admin.layout.master')

@section('title', $title)

@section('css')
<style>
    .salary-calc-menu .table thead th,
    .salary-calc-menu .table tbody td {
        font-size: 12px;
    }
    .salary-calc-menu .search_test {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        min-height: 38px;
    }
    .salary-calc-menu .custom-heighlight {
        width: 100%;
        padding: 0.5rem;
    }
    .salary-calc-menu .export-button {
        display: flex;
        align-items: center;
        gap: 6px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .salary-calc-menu .export-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .salary-calc-menu .dropdown-menu-export {
        font-size: 14px;
        min-width: 140px;
    }
    .salary-calc-menu .salary-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 1.25rem;
        flex-wrap: nowrap;
    }
    .salary-calc-menu .salary-filter-row .form-group {
        margin-bottom: 0;
    }
    .salary-calc-menu .filter-show {
        width: 90px;
    }
    .salary-calc-menu .filter-status {
        width: 220px;
    }
    .salary-calc-menu .filter-search {
        width: 300px;
    }
    .salary-calc-menu .filter-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-left: auto;
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
                <li><a href="#">New Payroll</a></li>
                <li class="active"><span><b>{{ $title }}</b></span></li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Employees List</h4>
                </div>

                <div class="card-body">
                    <div class="salary-filter-row">
                        <div class="filter-show">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-status">
                            <div class="form-group">
                                <p class="form-label">Status</p>
                                <select id="statusFilter" class="search_test custom-heighlight">
                                    <option value="">All</option>
                                    <option value="71" {{ (string)$status === '71' ? 'selected' : '' }}>Active</option>
                                    <option value="72" {{ (string)$status === '72' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-search">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <div class="form-group mb-3">
                                    <input type="search" id="searchFilter" placeholder="Search" class="form-control" value="{{ $search }}" autocomplete="off" />
                                </div>
                            </div>
                        </div>

                        <div class="filter-actions">
                            <div class="form-group">
                                <button class="export-button dropdown-toggle" type="button" id="defaultDropdown"
                                    data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                    <i class="fa fa-download me-2"></i> Export As
                                </button>
                                <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                    <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                </ul>
                            </div>
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#salaryCalculatorPanelModal">
                                    <i class="fe fe-grid me-1"></i> Panel
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="salaryCalculatorEmployeesTable">
                            <thead>
                                <tr>
                                    <th>Emp Code</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Monthly Gross</th>
                                    <th>Monthly CTC</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach($employees as $emp)
                                @php
                                    $statusFilterValue = (int) $emp->emp_status === 71 ? '71' : '72';
                                @endphp
                                <tr>
                                    <td>{{ $emp->emp_code ?? '-' }}</td>
                                    <td>{{ $emp->emp_full_name }}</td>
                                    <td>{{ optional($emp->fh_department)->d_name ?? '-' }}</td>
                                    <td>{{ optional($emp->fh_designation)->dg_name ?? '-' }}</td>
                                    <td>{{ optional($emp->fh_employee_salary)->es_monthly_gross !== null ? number_format((float) optional($emp->fh_employee_salary)->es_monthly_gross, 2) : '-' }}</td>
                                    <td>{{ optional($emp->fh_employee_salary)->es_monthly_ctc !== null ? number_format((float) optional($emp->fh_employee_salary)->es_monthly_ctc, 2) : '-' }}</td>
                                    <td>{{ $statusFilterValue }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('payroll.salary-calculator.history', \Illuminate\Support\Facades\Crypt::encrypt($emp->emp_id)) }}">
                                                        <i class="fe fe-eye me-2"></i> View
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('employee.salaries.addEdit', \Illuminate\Support\Facades\Crypt::encrypt($emp->emp_id)) }}">
                                                        <i class="fe fe-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-5">
                        <div class="col-sm-6">
                            <div id="custom-show-entries"></div>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-end">
                            <div id="custom-pagination"></div>
                        </div>
                    </div>
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
                        <div class="fw-semibold">Step 1: Select employee and open calculator</div>
                        <div class="small text-muted">Click Preview to verify employee details, then click Open Salary Calculator or Create.</div>
                    </div>
                    <div class="quick-step">
                        <div class="fw-semibold">Step 2: Enter salary input</div>
                        <div class="small text-muted">Choose Monthly or Annual and enter the amount. The Monthly Gross/CTC cards follow this input basis.</div>
                    </div>
                    <div class="quick-step">
                        <div class="fw-semibold">Step 3: Review earnings</div>
                        <div class="small text-muted">Saved earnings are shown automatically. If you edit earnings, Total Gross shows the remaining or excess amount against the input gross.</div>
                    </div>
                    <div class="quick-step">
                        <div class="fw-semibold">Step 4: Check deductions</div>
                        <div class="small text-muted">PF and ESIC appear in employee and employer deductions only when they are enabled for that employee. PT/LWF appear as configured.</div>
                    </div>
                    <div class="quick-step">
                        <div class="fw-semibold">Step 5: Validate net pay and tax</div>
                        <div class="small text-muted">Net Pay Preview includes employee deductions and TDS estimate when tax slabs are configured for the financial year.</div>
                    </div>
                    <div class="quick-step">
                        <div class="fw-semibold">Step 6: Save salary structure</div>
                        <div class="small text-muted">Save only after gross, earnings, deductions and employer deductions look correct. Saved values update the employee salary master.</div>
                    </div>

                    <div class="quick-step">
                        <div class="fw-semibold">Important conditions</div>
                        <div class="small text-muted">
                            Admin users are not shown in this list. Only employees from the current business are available.
                            Disabled PF/ESIC will not be calculated or saved for that employee. If TDS looks blank, verify FY tax slabs first.
                        </div>
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
        const searchFilter = document.getElementById('searchFilter');
        const statusFilter = document.getElementById('statusFilter');
        const resetFilters = document.getElementById('resetFilters');
        const lengthMenu = document.getElementById('customLengthMenu');
        let employeeTable = null;

        if (window.jQuery && $.fn.DataTable && document.getElementById('salaryCalculatorEmployeesTable')) {
            const tableOptions = {
                pageLength: 20,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true,
                dom: 'rt<"d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4"ip>',
                columnDefs: [
                    { targets: 6, visible: false, searchable: true },
                    { targets: 7, orderable: false, searchable: false }
                ],
                language: {
                    emptyTable: 'No employees found.',
                    zeroRecords: 'No matching employees found.'
                }
            };

            if ($.fn.dataTable && $.fn.dataTable.Buttons) {
                tableOptions.buttons = ['csv', 'excel', 'pdf', 'copy', 'print'];
            }

            employeeTable = $('#salaryCalculatorEmployeesTable').DataTable(tableOptions);
            if (employeeTable.buttons) {
                employeeTable.buttons().container().hide().appendTo(document.body);
            }

            if (searchFilter && searchFilter.value) {
                employeeTable.search(searchFilter.value);
            }

            if (statusFilter && statusFilter.value) {
                employeeTable.column(6).search(statusFilter.value);
            }

            employeeTable.draw();
        }

        if (lengthMenu) {
            lengthMenu.addEventListener('change', function () {
                if (!employeeTable) return;
                employeeTable.page.len(parseInt(this.value, 10) || 20).draw();
            });
        }

        if (searchFilter) {
            searchFilter.addEventListener('input', function () {
                if (!employeeTable) return;
                employeeTable.search(this.value).draw();
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                if (!employeeTable) return;
                employeeTable.column(6).search(this.value).draw();
            });
        }

        if (resetFilters) {
            resetFilters.addEventListener('click', function () {
                if (searchFilter) searchFilter.value = '';
                if (statusFilter) statusFilter.value = '';

                if (!employeeTable) return;
                employeeTable.search('');
                employeeTable.column(6).search('');
                employeeTable.draw();
            });
        }

        document.querySelectorAll('[data-export]').forEach(function (exportLink) {
            exportLink.addEventListener('click', function (event) {
                event.preventDefault();
                if (!employeeTable || !employeeTable.button) return;

                const exportType = this.dataset.export;
                const buttonMap = {
                    csv: '.buttons-csv',
                    excel: '.buttons-excel',
                    pdf: '.buttons-pdf',
                    copy: '.buttons-copy',
                    print: '.buttons-print'
                };
                const buttonSelector = buttonMap[exportType];
                if (buttonSelector) {
                    employeeTable.button(buttonSelector).trigger();
                }
            });
        });

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

