@extends('admin.layout.master')
@section('title')
{{ $pageTitle }}
@endsection
@section('header')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('css')
<style>
    /* ===== PERIOD CARDS GRID ===== */
    #periodsList {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 10px;
        margin: 0;
    }

    .period-col {
        width: 100%;
        padding: 0;
        margin: 0;
    }
    .text-wrap {
        font-size: 12px;
    }

    /* ===== PERIOD CARD STYLES ===== */
    .period-card {
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 8px;
        padding: 10px 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        height: auto;
        min-height: 75px;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .period-card:hover {
        border-color: #80bdff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
        transform: translateY(-1px);
    }

    .period-card.selected {
        border-color: #007bff;
        background-color: #f0f7ff;
        border-width: 2px;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
    }

    .period-card.selected::before {
        content: '\f00c';
        font-family: 'FontAwesome';
        position: absolute;
        top: 4px;
        right: 4px;
        color: #007bff;
        font-size: 10px;
        background: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #007bff;
    }

    /* ===== PERIOD CARD CONTENT ===== */
    .period-name {
        font-weight: 600;
        color: #495057;
        margin-bottom: 4px;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 20px;
        line-height: 1.3;
    }

    .period-dates {
        font-size: 10px;
        color: #6c757d;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }

    .period-dates i {
        font-size: 9px;
        color: #6c757d;
    }

    .period-status {
        font-size: 9px;
        padding: 2px 5px;
        border-radius: 10px;
        background: #e9ecef;
        color: #495057;
        display: inline-block;
        width: fit-content;
        font-weight: 500;
        line-height: 1.2;
    }

    .period-status.generated {
        background: #d4edda;
        color: #155724;
    }

    .period-status.pending {
        background: #fff3cd;
        color: #856404;
    }

    .period-month {
        font-size: 9px;
        color: #6c757d;
        font-weight: normal;
    }

    /* ===== DARK MODE ===== */
    [data-theme="dark"] .period-card,
    .dark-mode .period-card {
        --card-bg: #2d2d2d;
        --border-color: #404040;
        --text-primary: #e0e0e0;
        --text-secondary: #a0a0a0;
        background: #2d2d2d;
        border-color: #404040;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    [data-theme="dark"] .period-name,
    .dark-mode .period-name {
        color: #e0e0e0;
    }

    [data-theme="dark"] .period-dates,
    .dark-mode .period-dates {
        color: #a0a0a0;
    }

    [data-theme="dark"] .period-dates i,
    .dark-mode .period-dates i {
        color: #808080;
    }

    [data-theme="dark"] .period-status,
    .dark-mode .period-status {
        background: #404040;
        color: #e0e0e0;
    }

    [data-theme="dark"] .period-status.generated,
    .dark-mode .period-status.generated {
        background: rgba(40, 167, 69, 0.2);
        color: #75b798;
    }

    [data-theme="dark"] .period-status.pending,
    .dark-mode .period-status.pending {
        background: rgba(255, 193, 7, 0.2);
        color: #ffda6a;
    }

    [data-theme="dark"] .period-card.selected,
    .dark-mode .period-card.selected {
        background-color: rgba(0, 123, 255, 0.15);
    }

    [data-theme="dark"] .period-card.selected::before,
    .dark-mode .period-card.selected::before {
        background: #2d2d2d;
        color: #007bff;
    }

    /* ===== LOADING SPINNER ===== */
    .loading-spinner {
        display: none;
        text-align: center;
        padding: 30px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .loading-spinner.active {
        display: block;
    }

    [data-theme="dark"] .loading-spinner,
    .dark-mode .loading-spinner {
        background: #2d2d2d;
    }

    /* ===== NO PERIODS MESSAGE ===== */
    .no-periods-message {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        background: #f8f9fa;
        border-radius: 8px;
    }

    [data-theme="dark"] .no-periods-message,
    .dark-mode .no-periods-message {
        background: #2d2d2d;
        color: #a0a0a0;
    }

    /* ===== QUICK STATS ROW ===== */
    #periodStats .bg-light {
        background: #f8f9fa !important;
        color: #495057;
        padding: 8px 15px;
        border-radius: 6px;
    }

    #periodStats small.text-muted {
        color: #6c757d !important;
        font-size: 12px;
    }

    #periodStats .badge {
        font-size: 10px;
        padding: 3px 6px;
        margin-left: 5px;
    }

    [data-theme="dark"] #periodStats .bg-light,
    .dark-mode #periodStats .bg-light {
        background: #2d2d2d !important;
        color: #e0e0e0;
    }

    [data-theme="dark"] #periodStats small.text-muted,
    .dark-mode #periodStats small.text-muted {
        color: #a0a0a0 !important;
    }

    /* ===== BUTTONS ===== */
    .export-button {
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
        transition: all 0.2s ease;
    }

    .export-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .custom-button {
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
        transition: all 0.2s ease;
    }

    .custom-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .custom-button svg {
        width: 16px;
        height: 16px;
    }

    /* ===== DROPDOWNS ===== */
    .dropdown-menu-export {
        font-size: 14px;
        min-width: 140px;
    }

    .dropdown-menu-export .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .form-select {
        font-size: 10px !important;
        width: 100%;
    }

    /* ===== TABLE STYLES ===== */
    #payroll-policy-table-dynamic tbody tr:hover {
        background-color: #f0f0f0;
        transition: background-color 0.2s ease-in-out;
        cursor: pointer;
    }

    [data-theme="dark"] #payroll-policy-table-dynamic tbody tr:hover,
    .dark-mode #payroll-policy-table-dynamic tbody tr:hover {
        background-color: #3a3a3a;
    }

    .table-container {
        transition: opacity 0.3s ease;
    }

    .table-container.loading {
        opacity: 0.5;
        pointer-events: none;
    }

    .td {
        font-size: 12px;
    }

    /* ===== RESPONSIVE ===== */
    @media (min-width: 1200px) {
        #periodsList {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    @media (min-width: 992px) and (max-width: 1199px) {
        #periodsList {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (min-width: 768px) and (max-width: 991px) {
        #periodsList {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 767px) {
        #periodsList {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .period-card {
            padding: 8px 6px;
            min-height: 70px;
        }

        .period-name {
            font-size: 12px;
        }

        .period-dates {
            font-size: 9px;
        }

        .period-status {
            font-size: 8px;
            padding: 1px 4px;
        }

        .period-month {
            font-size: 8px;
        }

        .export-button,
        .custom-button {
            padding: 6px 10px;
            font-size: 12px;
        }
    }

    /* ===== FINANCIAL YEAR SELECTION ===== */
    #financial_year_main {
        height: 45px;
        font-size: 15px;
    }

    #loadPeriodsBtn {
        height: 45px;
        min-width: 120px;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        #financial_year_main {
            height: 40px;
            font-size: 14px;
        }

        #loadPeriodsBtn {
            height: 40px;
            min-width: 100px;
            font-size: 13px;
        }

        .d-flex.gap-2 {
            flex-direction: column;
        }

        #loadPeriodsBtn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
@if (session('error'))
<div class="alert alert-danger fade-message">
    {{ session('error') }}
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
                const msg = document.querySelector('.fade-message');
                if (msg) {
                    setTimeout(() => {
                        msg.style.opacity = '0';
                        setTimeout(() => msg.remove(), 500);
                    }, 2000);
                }
            });
</script>
@endif

@if (session('success'))
<div class="alert alert-success fade-message">
    {{ session('success') }}
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
                const msg = document.querySelector('.fade-message');
                if (msg) {
                    setTimeout(() => {
                        msg.style.opacity = '0';
                        setTimeout(() => msg.remove(), 500);
                    }, 2000);
                }
            });
</script>
@endif

{{-- Breadcrumbs --}}
<div class="mt-3">
    <div class="row">
        <div class="col-md-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="#">Payroll</a></li>
                <li class="active"><span><b>Payslips</b></span></li>
            </ol>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <!-- Financial Year Selection -->
        <div class="card mb-3">
           <!-- Financial Year Selection - Updated with auto-select -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <label for="financial_year_main" class="form-label fw-bold">Financial Year</label>
                            <div class="d-flex gap-2">
                                <select id="financial_year_main" class="form-select form-select-lg">
                                    <option value="">-- Choose Financial Year --</option>
                                    @foreach ($financial_year as $data)
                                        @php
                                            // Check if this is current financial year
                                            $currentYear = date('Y');
                                            $isCurrentYear = (strpos($data->fy_year, (string)$currentYear) !== false);
                                        @endphp
                                        <option value="{{ $data->fy_id }}" {{ $isCurrentYear ? 'selected' : '' }}>
                                            {{ $data->fy_year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll Periods Cards -->
            <div class="card mb-3" id="periodsCard" style="display: none;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-calendar me-2 text-primary"></i>
                        Payroll Period
                    </h5>
                    <span class="badge bg-info" id="periodCount">0 periods</span>
                </div>
                <div class="card-body">
                    <div class="loading-spinner" id="periodsSpinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading payroll periods...</p>
                    </div>

                    <div id="noPeriodsMessage" class="no-periods-message" style="display: none;">
                        <i class="fa fa-info-circle fa-2x mb-2 text-muted"></i>
                        <p>No payroll periods found for this financial year.</p>
                    </div>

                    <!-- Period Cards Grid - 12 entries ke liye optimized -->
                    <div id="periodsList" class="row g-2">
                        <!-- Period cards will be loaded here -->
                    </div>

                    <!-- Quick Stats Row -->
                    {{-- <div class="row mt-3" id="periodStats" style="display: none;">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                                <div>
                                    <small class="text-muted">Generated: <span id="generatedCount"
                                            class="badge bg-success">0</span></small>
                                    <small class="text-muted ms-3">Pending: <span id="pendingCount"
                                            class="badge bg-warning">0</span></small>
                                </div>
                                <div>
                                    <small class="text-muted">Click on any period to view payslips</small>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>

        </div>

        <!-- Payroll Periods Cards -->
        <div class="card mb-3" id="periodsCard" style="display: none;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-calendar me-2 text-primary"></i>
                    Payroll Period
                </h5>
                <span class="badge bg-info" id="periodCount">0 periods</span>
            </div>
            <div class="card-body">
                <div class="loading-spinner" id="periodsSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading payroll periods...</p>
                </div>

                <div id="noPeriodsMessage" class="no-periods-message" style="display: none;">
                    <i class="fa fa-info-circle fa-2x mb-2 text-muted"></i>
                    <p>No payroll periods found for this financial year.</p>
                </div>

                <!-- Period Cards Grid - 12 entries ke liye optimized -->
                <div id="periodsList" class="row g-2">
                    <!-- Period cards will be loaded here -->
                </div>

                <!-- Quick Stats Row -->
                <div class="row mt-3" id="periodStats" style="display: none;">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                            <div>
                                <small class="text-muted">Generated: <span id="generatedCount"
                                        class="badge bg-success">0</span></small>
                                <small class="text-muted ms-3">Pending: <span id="pendingCount"
                                        class="badge bg-warning">0</span></small>
                            </div>
                            <div>
                                <small class="text-muted">Click on any period to view payslips</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payslips Table -->
        <div class="card" id="payslipsCard" style="display: none;">
            <div class="card-header border-0 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-file-text-o me-2 text-success"></i>
                        Payslips
                        <span id="selectedPeriodInfo" class="text-muted" style="font-size: 14px;"></span>
                    </h4>
                    <div>
                        {{-- <button class="btn btn-outline-primary btn-sm me-2" onclick="refreshTable()" title="Refresh">
                            <i class="fa fa-refresh"></i>
                        </button> --}}
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3 align-items-end">
                    <!-- Show entries -->
                    <div class="col-lg-1 col-md-2">
                        <div class="form-group">
                            <label class="form-label">Show</label>
                            <select id="customLengthMenu" class="form-select form-select-sm">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="col-lg-2 col-md-3">
                        <div class="form-group">
                            <label class="form-label">Search</label>
                            <input type="text" id="searchFilter" placeholder="Name, email..."
                                class="form-control form-control-sm" />
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="col-lg-2 col-md-2">
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <select id="payslips_department" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($departmentList as $departmentF)
                                <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Designation -->
                    <div class="col-lg-2 col-md-2">
                        <div class="form-group">
                            <label class="form-label">Designation</label>
                            <select id="payslips_designation" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($designationList as $designationF)
                                <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-lg-1 col-md-2">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select id="payslips_activeFilter" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($emp_status as $data)
                                <option value="{{ $data->m_id }}">{{ $data->m_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Select All Checkbox -->
                    <div class="col-lg-1 col-md-2">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check">
                                <input type="checkbox" id="selectAll" class="form-check-input"
                                    onclick="selectAllCheckboxes(this)">
                                <label class="form-check-label" for="selectAll">Select All</label>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Email Button -->
                    <div class="col-lg-1 col-md-2">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-success btn-sm w-100" onclick="sendSelectedEmployees()">
                                <i class="fa fa-envelope me-1"></i> Email
                            </button>
                        </div>
                    </div>

                    <!-- Export Dropdown -->
                    <div class="col-lg-1 col-md-2">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm w-100 dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="fa fa-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportData('csv')">CSV</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportData('excel')">Excel</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">PDF</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-container" id="tableContainer">
                    <table class="table table-hover table-vcenter text-wrap" id="payroll-policy-table-dynamic">
                        <thead>
                            <tr>
                                <th style="font-size: 13px; width: 100px">Emp. Code</th>
                                <th style="font-size: 13px; width: 150px">Emp. Name</th>
                                <th style="font-size: 13px; width: 120px">Department</th>
                                <th style="font-size: 13px; width: 120px">Designation</th>
                                <th style="font-size: 13px; width: 120px">Payroll Period</th>
                                <th style="font-size: 13px; width: 100px">Net Salary</th>
                                <th style="font-size: 13px; width: 100px">Created At</th>
                                <th style="font-size: 13px; width: 100px">Action</th>
                                <th style="font-size: 13px; width: 60px">
                                    <div class="form-check">
                                        <input type="checkbox" id="selectAll" class="form-check-input"
                                            onclick="selectAllCheckboxes(this)">
                                        <label class="form-check-label" for="selectAll">All</label>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Table body will be populated by DataTable -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Make sure SweetAlert is available
if (typeof Swal === 'undefined') {
    console.error('SweetAlert is not loaded');
}

let currentPeriodId = null;
let dataTableInstance = null;
let empIDs = [];

// ===== PAGE LOAD AUTOMATION =====
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Hide periods card initially
    $('#periodsCard').hide();
    $('#payslipsCard').hide();

    // Load periods when financial year changes
    $('#financial_year_main').on('change', function() {
        if ($(this).val()) {
            loadPayrollPeriodsFromMain();
        } else {
            $('#periodsCard').hide();
            $('#payslipsCard').hide();
        }
    });

    // Auto-load current financial year periods on page load
    setTimeout(function() {
        autoLoadCurrentFinancialYear();
    }, 500); // Small delay to ensure DOM is fully ready
});

// ===== AUTO-LOAD CURRENT FINANCIAL YEAR =====
function autoLoadCurrentFinancialYear() {
    const fySelect = $('#financial_year_main');

    if (fySelect.length === 0) {
        console.log('Financial year select not found');
        return;
    }

    // Get current selected value (from HTML)
    let selectedFyId = fySelect.val();

    if (selectedFyId && selectedFyId !== '') {
        console.log('Auto-loading periods for financial year ID:', selectedFyId);

        // Show periods card and spinner
        $('#periodsCard').show();
        $('#periodsSpinner').addClass('active');
        $('#periodsList').empty();
        $('#noPeriodsMessage').hide();

        // Load payroll periods
        $.ajax({
            url: '/payroll/get-payroll-periods',
            type: 'GET',
            data: { fy_id: selectedFyId },
            dataType: 'json',
            success: function(response) {
                console.log('Auto-load Success:', response);
                $('#periodsSpinner').removeClass('active');

                if (response && response.length > 0) {
                    displayPeriods(response);
                } else {
                    $('#noPeriodsMessage').show();
                    $('#periodStats').hide();
                    console.log('No periods found for FY ID:', selectedFyId);
                }
            },
            error: function(xhr, status, error) {
                $('#periodsSpinner').removeClass('active');
                console.error('Auto-load Error:', error);
                $('#noPeriodsMessage').show();
                $('#periodStats').hide();
            }
        });
    }
}

// Load payroll periods when financial year is selected (manual)
function loadPayrollPeriodsFromMain() {
    const fyId = $('#financial_year_main').val();

    console.log('Selected FY ID:', fyId);

    if (!fyId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Financial Year',
                text: 'Please select a financial year first!',
                timer: 2000
            });
        } else {
            alert('Please select a financial year first!');
        }
        return;
    }

    // Show periods card and spinner
    $('#periodsCard').show();
    $('#periodsSpinner').addClass('active');
    $('#periodsList').empty();
    $('#noPeriodsMessage').hide();

    $.ajax({
        url: '/payroll/get-payroll-periods',
        type: 'GET',
        data: { fy_id: fyId },
        dataType: 'json',
        success: function(response) {
            console.log('AJAX Success - Response:', response);
            $('#periodsSpinner').removeClass('active');

            if (response && response.length > 0) {
                displayPeriods(response);
            } else {
                $('#noPeriodsMessage').show();
                $('#periodStats').hide();
                console.log('No periods found for FY ID:', fyId);
            }
        },
        error: function(xhr, status, error) {
            $('#periodsSpinner').removeClass('active');
            console.error('AJAX Error:', error);
            $('#noPeriodsMessage').show();
            $('#periodStats').hide();

            let errorMsg = 'Error loading payroll periods. ';
            if (xhr.status === 404) {
                errorMsg += 'Route not found.';
            } else if (xhr.status === 500) {
                errorMsg += 'Server error.';
            }
            $('#noPeriodsMessage p').text(errorMsg);
        }
    });
}

// Display periods as cards
function displayPeriods(periods) {
    console.log('Displaying periods:', periods);
    let html = '';
    let generated = 0;
    let pending = 0;

    // Sort periods by date
    periods.sort((a, b) => {
        return new Date(a.pp_start_date) - new Date(b.pp_start_date);
    });

    periods.forEach(function(period) {
        const isGenerated = period.payslip_generated || false;
        const statusClass = isGenerated ? 'generated' : 'pending';
        const statusText = isGenerated ? 'Generated' : 'Pending';

        if (isGenerated) generated++; else pending++;

        const startDate = period.pp_start_date || '';
        let monthName = '';
        if (startDate) {
            const date = new Date(startDate);
            monthName = date.toLocaleString('default', { month: 'short' });
        }

        const startDateFormatted = formatDate(startDate);
        const endDateFormatted = formatDate(period.pp_end_date || '');

        html += `
            <div class="period-col">
                <div class="period-card" onclick="selectPeriod(${period.pp_id}, '${escapeString(period.pp_name)}', '${startDate}', '${period.pp_end_date || ''}')">
                    <div class="period-name" title="${escapeString(period.pp_name)}">
                        <i class="fa fa-calendar-check-o me-1 text-primary"></i>
                        ${escapeString(period.pp_name)}
                    </div>
                    <div class="period-dates" title="${startDateFormatted} to ${endDateFormatted}">
                        <i class="fa fa-clock-o"></i>
                        ${startDateFormatted} - ${endDateFormatted}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="period-status ${statusClass}">
                            ${statusText}
                        </span>
                        <small class="text-muted">${monthName}</small>
                    </div>
                </div>
            </div>
        `;
    });

    $('#periodsList').html(html);

    // Update stats
    $('#periodCount').text(periods.length + ' periods');
    $('#generatedCount').text(generated);
    $('#pendingCount').text(pending);
    $('#periodStats').show();
}

// Escape string to prevent XSS
function escapeString(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"]/g, function(match) {
        if (match === '&') return '&amp;';
        if (match === '<') return '&lt;';
        if (match === '>') return '&gt;';
        if (match === '"') return '&quot;';
        return match;
    });
}

// Format date
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return 'Invalid Date';
        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    } catch (e) {
        return dateStr;
    }
}

// Select a period and load payslips
function selectPeriod(periodId, periodName, startDate, endDate) {
    // Remove selection from all cards
    $('.period-card').removeClass('selected');

    // Add selection to clicked card
    $(event.currentTarget).addClass('selected');

    // Update current period
    currentPeriodId = periodId;

    // Update period info
    const formattedStart = formatDate(startDate);
    const formattedEnd = formatDate(endDate);
    $('#selectedPeriodInfo').html(`- ${escapeString(periodName)} (${formattedStart} to ${formattedEnd})`);

    // Show payslips card
    $('#payslipsCard').show();

    // Load payslips for this period
    loadPayslipsForPeriod(periodId);
}

// Load payslips for selected period
function loadPayslipsForPeriod(periodId) {
    if (!periodId) return;

    // Show loading state
    $('#tableContainer').addClass('loading');

    // If DataTable is already initialized, destroy it
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#payroll-policy-table-dynamic')) {
        $('#payroll-policy-table-dynamic').DataTable().destroy();
    }

    // Clear the table
    $('#payroll-policy-table-dynamic tbody').empty();

    // Initialize DataTable with period filter
    try {
        dataTableInstance = $('#payroll-policy-table-dynamic').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/payroll/payslips',
                type: 'GET',
                data: function(d) {
                    d.period_id = periodId;
                    d.financial_year = $('#financial_year_main').val();
                    d.department = $('#payslips_department').val();
                    d.designation = $('#payslips_designation').val();
                    d.employee_status = $('#payslips_activeFilter').val();
                    
                    // DON'T set d.search manually - DataTables handles this automatically
                    // The search value is already in d.search from DataTables
                    
                    // Don't override start and length unless necessary
                    // d.start = d.start || 0;
                    // d.length = d.length || 10;
                },
                dataSrc: function(json) {
                    console.log('Response from server:', json);
                    $('#tableContainer').removeClass('loading');
                    setTimeout(updateSelectAllState, 500);
                    return json.data || [];
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX error:', error);
                    $('#tableContainer').removeClass('loading');
                }
            },
            columns: [
                { data: 0, name: 'emp_code' },
                { data: 1, name: 'emp_name' },
                { data: 2, name: 'department' },
                { data: 3, name: 'designation' },
                { data: 4, name: 'payroll_period' },
                { data: 5, name: 'net_salary' },
                { data: 6, name: 'created_at' },
                { data: 7, name: 'action', orderable: false, searchable: false },
                { data: 8, name: 'checkbox', orderable: false, searchable: false }
            ],
            pageLength: parseInt($('#customLengthMenu').val()),
            lengthMenu: [5, 10, 25, 50, 100],
            searching: true, // Enable searching
            ordering: true,
            order: [[0, 'asc']],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"></div>',
                emptyTable: 'No payslips found for this period',
                zeroRecords: 'No matching payslips found'
            },
            initComplete: function() {
                $('#tableContainer').removeClass('loading');
                console.log('DataTable initialized');
                $('#selectAll').off('click').on('click', function() {
                    selectAllCheckboxes(this);
                });
            },
            drawCallback: function() {
                updateSelectAllState();
            }
        });

        // Update length menu
        $('#customLengthMenu').off('change').on('change', function() {
            if (dataTableInstance) {
                dataTableInstance.page.len($(this).val()).draw();
            }
        });

        // Update search with debounce - use DataTables search API
        let searchTimer;
        $('#searchFilter').off('keyup').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                if (dataTableInstance) {
                    // Use DataTables search API instead of manual ajax reload
                    dataTableInstance.search($(this).val()).draw();
                }
            }, 500);
        });

        // Update filters
        $('#payslips_department, #payslips_designation, #payslips_activeFilter').off('change').on('change', function() {
            if (dataTableInstance) {
                dataTableInstance.ajax.reload();
            }
        });

    } catch (e) {
        console.error('Error initializing DataTable:', e);
        $('#tableContainer').removeClass('loading');
        $('#payroll-policy-table-dynamic tbody').html('<tr><td colspan="9" class="text-center text-danger">Error loading data table</td></tr>');
    }
}

// Bulk email function
function sendSelectedEmployees() {
    if (empIDs.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one employee!'
        });
        return;
    }

    Swal.fire({
        title: 'Sending Emails...',
        html: 'Please wait while we process your request',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '/salary/bulk-email',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            emp_ids: empIDs,
            period_id: currentPeriodId
        },
        success: function(response) {
            Swal.close();

            let successCount = 0;
            let failedCount = 0;

            if (response.details) {
                successCount = response.details.filter(item => item.status === 'Success').length;
                failedCount = response.details.filter(item => item.status === 'Failed').length;
            }

            let html = `<strong>${response.summary || 'Bulk email processed'}</strong><br><br>`;

            if (successCount > 0) {
                html += `<span class="text-success">✓ ${successCount} emails sent successfully</span><br>`;
            }
            if (failedCount > 0) {
                html += `<span class="text-danger">✗ ${failedCount} failed</span>`;
            }

            Swal.fire({
                title: 'Bulk Email Report',
                html: html,
                icon: failedCount > 0 ? 'warning' : 'success',
                width: 500
            });
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error',
                text: 'Something went wrong while sending emails!',
                icon: 'error'
            });
        }
    });
}

// Export function
function exportData(type) {
    if (!currentPeriodId) {
        Swal.fire({
            icon: 'warning',
            title: 'No Period Selected',
            text: 'Please select a payroll period first!'
        });
        return;
    }

    let url = '/payroll/payslips/export?type=' + type +
              '&period_id=' + currentPeriodId +
              '&financial_year=' + $('#financial_year_main').val() +
              '&department=' + $('#payslips_department').val() +
              '&designation=' + $('#payslips_designation').val() +
              '&employee_status=' + $('#payslips_activeFilter').val() +
              '&search=' + $('#searchFilter').val();

    window.location.href = url;
}

// Refresh current table
function refreshTable() {
    if (currentPeriodId && dataTableInstance) {
        dataTableInstance.ajax.reload();
    } else if (currentPeriodId) {
        loadPayslipsForPeriod(currentPeriodId);
    }
}

// View payslip function
function viewPayslip(id) {
    if (id) {
        window.location.href = '/admin/monthly-salary/view-payslip/' + id;
    }
}

// Select all checkboxes function
function selectAllCheckboxes(source) {
    var isChecked = $(source).is(':checked');
    $('#payroll-policy-table-dynamic tbody .testClass').each(function() {
        $(this).prop('checked', isChecked);
    });

    empIDs = [];
    if (isChecked) {
        $('#payroll-policy-table-dynamic tbody .testClass').each(function() {
            empIDs.push($(this).val());
        });
    }
    console.log('Selected IDs:', empIDs);
}

// Update select all checkbox state
function updateSelectAllState() {
    var totalCheckboxes = $('#payroll-policy-table-dynamic tbody .testClass').length;
    var checkedCheckboxes = $('#payroll-policy-table-dynamic tbody .testClass:checked').length;

    if (totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes) {
        $('#selectAll').prop('checked', true);
        $('#selectAll').prop('indeterminate', false);
    } else if (checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes) {
        $('#selectAll').prop('checked', false);
        $('#selectAll').prop('indeterminate', true);
    } else {
        $('#selectAll').prop('checked', false);
        $('#selectAll').prop('indeterminate', false);
    }
}

// Individual checkbox update
function selectCheckboxUpdate(element) {
    var empId = $(element).val();
    var isChecked = $(element).is(':checked');

    if (isChecked) {
        if (!empIDs.includes(empId)) {
            empIDs.push(empId);
        }
    } else {
        empIDs = empIDs.filter(id => id != empId);
    }
    updateSelectAllState();
    console.log('Selected IDs:', empIDs);
}
</script>
@endsection
