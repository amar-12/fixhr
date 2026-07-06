<?php
use Illuminate\Support\Carbon;
use App\Models\AttendanceRecord;
use App\Helpers\CentralLogics;
use App\Helpers\RolePermissionLogics;
$permission = new RolePermissionLogics();
?>

@extends('admin.layout.master')
@section('title')
    Monthly Attendance
@endsection
@section('css')
<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    .fs-14 {
        font-size: 12;
    }

    #global-loader{
        display: none !important;
    }

    h6 {
        font-size: 12 !important;
    }

    table td {
        padding: 0;
    }

    table.dataTable tbody th, table.dataTable tbody td, table.dataTable thead th, table.dataTable thead td {
        padding: 10px 6px !important;
    }

    .big-checkbox {
        width: 25px;
        height: 25px;
        cursor: pointer;
    }

    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        display: none;
    }

    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Table loading state */
    .table-loading {
        position: relative;
        opacity: 0.6;
        pointer-events: none;
    }

    .table-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 30px;
        height: 30px;
        margin: -15px 0 0 -15px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        z-index: 10;
    }

    /* Layout Wrappers */
    .employee-list-card {
        height: calc(100vh - 100px);
        border-right: 1px solid #dee2e6;
    }
    .employee-list-body {
        overflow-y: auto;
        height: calc(100% - 60px);
    }
    .attendance-table-wrapper {
        overflow-x: auto;
    }
    .attendance-table {
        width: max-content;
    }

    /* Sticky Headers */
    .employee-name-header {
        min-width: 200px;
        position: sticky;
        left: 0;
        background: white;
        z-index: 2;
    }
    .day-header {
        min-width: 50px;
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 1;
    }

    /* Status Cell Styling */
    .status-cell {
        font-size: 14px;
        cursor: pointer;
        text-align: center;
        font-weight: bold;
        padding: 2px 8px;
        border-radius: 4px;
        min-width: 24px;
        transition: all 0.2s ease;
    }
    .status-cell:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

  
    /* Tooltip Styling */
    .tooltip-inner {
        background: #ffffff !important;
        color: #333 !important;
        border: 1px solid #dee2e6 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        text-align: left !important;
        padding: 1rem !important;
        opacity: 1 !important;         
    }
    .tooltip {
        opacity: 1 !important;        
    }
    .tooltip.bs-tooltip-top .tooltip-arrow::before,
    .tooltip.bs-tooltip-bottom .tooltip-arrow::before,
    .tooltip.bs-tooltip-left .tooltip-arrow::before,
    .tooltip.bs-tooltip-right .tooltip-arrow::before {
        background-color: #ffffff !important;  
        border: 1px solid #dee2e6 !important;  
    }


    /* Tooltip Content */
    .attendance-tooltip div {
        margin-bottom: 5px;
    }
    .attendance-tooltip strong {
        display: inline-block;
        width: 80px;
        color: #666;
    }

    .attendance-tooltip {
        min-width: 30px;
        font-size: 13px;
    }


    /* Status badge inside Tooltip */
    .tooltip-inner .status-P,
    .tooltip-inner .status-A,
    .tooltip-inner .status-L,
    .tooltip-inner .status-MSP,
    .tooltip-inner .status-H {
        padding: 2px 6px;
        background-color: #ffc107; color: white;
        border-radius: 4px;
    }

    /* Filter Section */
    .filter-section {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    /* Month input styling */
    .month-input-wrapper {
        position: relative;
    }

    .month-input-wrapper input[type="month"] {
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .month-input-wrapper input[type="month"]:focus {
        border-color: #007bff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

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
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .export-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .dropdown-menu-export {
        font-size: 14px;
        min-width: 140px;
    }

    .dropdown-menu-export .dropdown-item:hover {
        background-color: #f8f9fa;
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
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .custom-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .custom-button svg {
        width: 16px;
        height: 16px;
    }

    .shift-row {
        display: flex;
        gap: 6px;
        font-size: 14px;
        font-weight: 600;
    }
    .shift-label {
        min-width: 95px;   /* label width fix */
    }
</style>
@endsection
@section('content')
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/attendance/monthly-attendance') }}">Attendance</a></li>
                <li class="active"><span><b>Monthly Attendance</b></span></li>
            </ol>
        </div>
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Monthly Attendance </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-1">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test" style="width: 100%"
                                        data-length>
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                            <div class="col-sm-6">
                            </div>
                            <div class="col-sm-1">
                                <button class="custom-button w-100" type="button" onclick="toggleFilters()"
                                    style="margin-top: 28px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <line x1="3" y1="8" x2="21" y2="8"
                                            stroke-linecap="round" />
                                        <circle cx="10" cy="8" r="1.5" fill="currentColor" />
                                        <line x1="3" y1="16" x2="21" y2="16"
                                            stroke-linecap="round" />
                                        <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                    </svg>
                                    Filters
                                </button>
                            </div>
                            <div class="col-sm-1" style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
                                <div class="form-group dropdown">
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
                            </div>
                            @if(
                                $permission->check_route_permission('admin/attendance/byattendance-update', 115) 
                                || 
                                $permission->check_route_permission('admin/attendance/byattendance-update', 117)
                            )
                            <div class="col-sm-1" style="margin-top: 30px;">
                                <div class="form-group filter_dots">
                                    <div id="statusLoader" style="display:none; position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center;">
                                            <div style="background:#fff;padding:20px;border-radius:8px;font-weight:bold;">
                                                Updating Status...
                                            </div>
                                        </div>
                                    <button class="btn btn-info" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown"
                                        style="min-width: 220px;">
                                        {{-- <li>
                                            <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                                data-bs-toggle="modal" data-bs-target="#monthlyAttendanceBulkUpload">
                                                <i class="las la-file-upload"></i> Upload File
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                                href="{{ route('monthly.attendance.downloadExcel') }}">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                                href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#exportEmpAttendanceData">
                                                <i class="las la-file-download"></i> Export Status Data
                                            </a>
                                            <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2" href="javascript:void(0)" 
                                               onclick="updateStatus()">
                                                <i class="las la-sync"></i> Update Status
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            @endif
                            <div class="row">
                                <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <label for="branchFilter" class="form-label">Branch</label>
                                            <select id="mt_branchFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($branchList as $branchF)
                                                    <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="departmentFilter" class="form-label">Department</label>
                                            <select id="mt_departmentFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($departmentList as $departmentF)
                                                    <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="designationFilter" class="form-label">Designation</label>
                                            <select id="mt_designationFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($designationList as $designationF)
                                                    <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="activeFilter" class="form-label">Status</label>
                                            <select id="mt_activeFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                <option value="71">Active</option>
                                                <option value="72">Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="mt_monthFilter" class="form-label">Month</label>
                                            <div class="month-input-wrapper">
                                                <input type="month" data-filter id="mt_monthFilter" name="monthFilter" 
                                                    value="{{ date('Y-m') }}" class="form-control filter_border" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (session('import_errors_blade'))
                            <div class="alert d-flex align-items-center mt-3">
                                <p>There were errors in the import. You can download the error file from the link below:</p>
                                <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()"
                                    class="ms-2 mb-4 btn btn-danger">Download Error File</a>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table display table-hover table-vcenter text-wrap border-bottom table-bordered"
                                id="montly-attendance-table-dynamic">
                                <thead>
                                    <tr>
                                        @foreach ($columns as $column)
                                            <th class="text-center" style="font-size: 12px">{!! $column !!}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div class="row mt-5">
                            <div class="col-sm-6">
                                <div id="custom-show-entries" data-show-entries></div>
                            </div>
                            <div class="col-sm-6 d-flex justify-content-end">
                                <ul data-pagination class="custom-pagination">
                                    {{-- {{ $data->links() }}<!-- Pagination links --> --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals remain the same -->
    <div class="modal fade" id="monthlyAttendanceBulkUpload" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Monthly Attendance</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('monthly.attendance.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Upload File:</label>
                                    <input type="file" name="import_file" id="import_file" class="form-control"
                                        required accept=".xlsx, .csv">
                                    <br>
                                    <div style="display: flex; align-items: center;">
                                        <p class="fw-bold" style="margin: 0;">Note -</p>
                                        <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance Modal -->
    <div id="attendanceModal" class="modal fade" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- <h5 class="modal-title" id="attendanceModalLabel">Update Attendance</h5> -->
                    <h5 class="modal-title" style="font-size:17px;"><span id="emp_code"></span> - <span
                            id="modalEmployeeName"></span> (<span id="punch_date_show"></span>)</h5>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="attendanceUpdateForm">
                        @csrf
                        <input type="hidden" id="punch_date" name="punch_date" class="form-control">
                        <input type="hidden" id="fallback_date">
                        <div class="row">
                            <div class="mb-5 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="shift-row">
                                        <span class="shift-label fw-bold fs-14">Shift Name</span>:
                                        <span id="shift_name" class="fw-bold fs-14"></span>
                                    </div>
                                    <div class="shift-row">
                                        <span class="shift-label fw-bold fs-14">Shift Start</span>:
                                        <span id="modalShiftStartTime" class="fw-bold fs-14"></span>
                                    </div>
                                    <div class="shift-row">
                                        <span class="shift-label fw-bold fs-14">Shift End</span>:
                                        <span id="modalShiftEndTime" class="fw-bold fs-14"></span>
                                    </div>
                                </div>

                                <div class="form-check ms-3 d-flex align-items-center">
                                    <input type="checkbox" name="mark_as_absent" class="form-check-input big-checkbox" id="mark_as_absent">
                                    <label for="mark_as_absent" class="form-check-label fw-bold fs-14 ms-2">Mark as Absent</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <input type="text" class="d-none form-control" id="modalEmployeeId" name="id">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Check In Date</label>
                                    <div class="input-group">
                                        <input type="date"
                                               class="form-control"
                                               name="in_date"
                                               id="modalStatusDate">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Check In</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control time_format_24hrs" name="in_time" placeholder="HH:MM" id="modalCheckInTime">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Check Out Date</label>
                                    <div class="input-group">
                                        <input type="date"
                                               class="form-control"
                                               name="out_date"
                                               id="modalStatusEndDate">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Check Out</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control time_format_24hrs" name="out_time" placeholder="HH:MM" id="modalCheckOutTime">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <textarea rows="3" id="reason" class="form-control" name="reason" placeholder="Enter Reason" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updateAttendanceBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="applyLeaveModalLabel">Apply Leave</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="leaveApplyForm">
              <input type="hidden" name="employee" id="leaveEmployee">
              <input type="hidden" name="date" id="leaveDate">
              
              <div class="mb-3">
                <label for="leaveType" class="form-label">Leave Type</label>
                <select class="form-select" id="leaveType" name="leaveType" required>
                  <option value="">Select Leave Type</option>
                  <option value="CL">Casual Leave</option>
                  <option value="SL">Sick Leave</option>
                  <option value="PL">Paid Leave</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="leaveReason" class="form-label">Reason</label>
                <textarea class="form-control" id="leaveReason" name="leaveReason" required></textarea>
              </div>

              <div class="mb-3">
                <label for="fromDate" class="form-label">From Date</label>
                <input type="date" class="form-control" id="fromDate" name="fromDate" required>
              </div>

              <div class="mb-3">
                <label for="toDate" class="form-label">To Date</label>
                <input type="date" class="form-control" id="toDate" name="toDate" required>
              </div>

              <button type="submit" class="btn btn-success">Submit Leave</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="exportEmpAttendanceData" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <h5 class="modal-title">Export Employee Attendance Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="attendance_status" class="form-label">Select Status</label>
                        <select class="form-select" id="attendance_status">
                            <option value="">Select</option>
                            @foreach ($attendanceStatus as $key => $status)
                                @if ($status['m_id'] != 323)
                                    <option value="{{ $status['m_id'] }}">{{ $status['m_name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-outline-primary" id="exportBtn">Export</button>
                </div>
            </div>
        </div>
    </div>

@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    let monthlyAttendanceTable;
    let isTableInitialized = false;

    function showLoader() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoader() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    function showTableLoader() {
        $('#montly-attendance-table-dynamic').addClass('table-loading');
    }

    function hideTableLoader() {
        $('#montly-attendance-table-dynamic').removeClass('table-loading');
    }

    function toggleFilters() {
        const container = document.getElementById('filterContainer');
        container.style.display = container.style.display === 'none' ? '' : 'none';
    }

    function updateStatus() {
        console.log("Updating Status...");
        var mt_monthFilter = $('#mt_monthFilter').val();

        // Show loader
        $('#statusLoader').fadeIn();

        $.ajax({
            url: '{{ route("monthly.attendance.status.update") }}',
            type: 'POST',
            data: {
                mt_monthFilter: mt_monthFilter
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#statusLoader').fadeOut();

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });

            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops',
                    text: 'Something went wrong! Please try again.',
                    confirmButtonText: 'OK'
                });

                // Hide loader
                $('#statusLoader').fadeOut();
            }
        });
    }

</script>

<script>
    function toggleFilters() {
        const container = document.getElementById('filterContainer');
        if (container) {
            container.style.display = container.style.display === 'none' ? '' : 'none';
        }
    }
</script>

<script type="text/javascript">
    // $(document).ready(function() {
    //     // Initialize datatable
    //     datatable({
    //         tableId: "montly-attendance-table-dynamic",
    //         url: "{{ route('attendance.month-attendance') }}",
    //         dataLength: '[data-length]',
    //         dataSearch: '[data-search]',
    //         dataFilter: '[data-filter]',
    //         dataExport: '[data-export]',
    //         dataDateFilter: '[data-date-filter]',
    //         dataShowEntries: '[data-show-entries]',
    //         dataPagination: '[data-pagination]',
    //         dataStateSave: false
    //     });
    // });

    $(document).ready(function() {
        // Check if mt_monthFilter URL parameter is missing and set it to current month
        var params = new URLSearchParams(window.location.search);
        if (!params.has('mt_monthFilter')) {
            localStorage.removeItem('filterValues');
        }

        //Show loading overlay when ajax starts
        $(document).ajaxStart(function() {
            $('#loadingOverlay').show();
        });

        // Hide loading overlay when ajax stops
        $(document).ajaxStop(function() {
            $('#loadingOverlay').hide();
        });

        // Initialize datatable with custom ajax.data to include filters
        try {
            datatable({
                tableId: "montly-attendance-table-dynamic",
                url: "{{ route('attendance.month-attendance') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false,
                ajax: {
                    data: function(d) {
                        // Include current filter values in every AJAX request
                        d.mt_monthFilter = $('#mt_monthFilter').val();
                        d.mt_branchFilter = $('#mt_branchFilter').val();
                        d.mt_departmentFilter = $('#mt_departmentFilter').val();
                        d.mt_designationFilter = $('#mt_designationFilter').val();
                        d.mt_activeFilter = $('#mt_activeFilter').val();
                    }
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable Error:', error, thrown);
                    $('#loadingOverlay').hide();
                    alert('Failed to load attendance data. Please try again or contact support.');
                }
            });
        } catch (e) {
            console.error('DataTable initialization error:', e);
            $('#loadingOverlay').hide();
        }
    
        // Override filter changes: Reload page with GET params instead of AJAX
        // $('[data-filter]').change(function() {
        $('#mt_monthFilter').change(function() {
            // Get current query params
            var params = new URLSearchParams(window.location.search);
            
            // Update the changed filter (use the input's id as param name)
            params.set(this.id, $(this).val());
            
            // Reload page with new params
            window.location.search = params.toString();
        });
    });
    
    // Keep your existing toggleFilters() if it's defined elsewhere
    function toggleFilters() {
        $('#filterContainer').toggle();
    }
    
</script>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Success message
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                customClass: {
                    toast: 'swal2-toast-green-glow'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        // Error message
        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        // Error messages (if multiple validation errors or custom messages are passed)
        @if ($errors->any())
            let errorMessages = '';
            @foreach ($errors->all() as $error)
                errorMessages += '{{ $error }}' + '<br>';
            @endforeach
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: 'Validation Errors',
                html: errorMessages,
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif
    });

    $(document).ready(function() {
        // Utility function to convert time to minutes
        function timeToMinutes(timeStr) {
            if (!timeStr || timeStr === '--') return 0;
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }

        // Format time to 12-hour format
        function formatTo12Hour(timeStr) {
            if (!timeStr || timeStr === '--') return '--';

            const [hoursStr, minutes] = timeStr.split(':');
            let hours = parseInt(hoursStr, 10);
            const ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12;

            return `${hours}:${minutes} ${ampm}`;
        }

        // Format time to 24-hour format
        function formatTo24Hour(timeStr) {
            if (!timeStr || timeStr === '--') return '';

            const time = timeStr.match(/(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
            if (!time) return '';

            let hours = parseInt(time[1], 10);
            const minutes = time[2];
            const meridian = time[3] ? time[3].toUpperCase() : null;

            if (meridian === 'PM' && hours < 12) {
                hours += 12;
            } else if (meridian === 'AM' && hours === 12) {
                hours = 0;
            }

            return `${hours.toString().padStart(2, '0')}:${minutes}`;
        }

        function convertUTCToLocal(timeStr) {
            if (!timeStr || timeStr === '--') return '--';
            const date = new Date(timeStr);
            if (isNaN(date)) return '--';
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        }

        // Tooltip functionality for status cells
        $(document).on('mouseenter', '.status-cell', function() {
            const element = $(this);

            const employee   = element.attr('data-employee') || '';
            const date       = element.attr('data-date') || '';
            const inTime     = element.attr('data-intime') || '--';
            const outTime    = element.attr('data-outtime') || '--';
            const shiftEnd   = element.attr('data-shift-end') || '';
            const status     = element.attr('data-status') || 'No Data';
            const statusColor= element.attr('data-status-color') || '#000';
            const workingHour= element.attr('data-working-hour');
            const statusId   = element.attr('data-status-id') || '';

            // <div><strong>In Time:</strong> ${formatTo24Hour(inTime)}</div>
            // <div><strong>Out Time:</strong> ${formatTo24Hour(outTime)}</div>

            let tooltipContent = `
                <div class="attendance-tooltip">
                    <div><strong>Employee:</strong> ${employee}</div>
                    <div><strong>Date:</strong> ${date}</div>
                    <div><strong>In Time:</strong> ${inTime}</div>
                    <div><strong>Out Time:</strong> ${outTime}</div>
                    <div><strong>Status:</strong> <span style="color: ${statusColor}; font-weight:600;">${status}</span></div>
            `;

            if (workingHour && parseFloat(workingHour) > 0) {
                tooltipContent += `<div><strong>Work Hr:</strong> ${workingHour}</div>`;
            } else {
                tooltipContent += `<div><strong>Work Hr:</strong> --</div>`;
            }

            if (shiftEnd && outTime && outTime !== '--') {
                const shiftEndMinutes = timeToMinutes(shiftEnd);
                const outTimeMinutes = timeToMinutes(outTime);

                if (outTimeMinutes < shiftEndMinutes) {
                    const earlyExitMinutes = shiftEndMinutes - outTimeMinutes;
                    tooltipContent += `<div><strong>Early Exit:</strong> ${earlyExitMinutes} min</div>`;
                }
            }

            tooltipContent += `</div>`;

            // Dispose existing tooltip and create new one
            element.tooltip('dispose');
            element.tooltip({
                html: true,
                sanitize: false,
                trigger: 'manual',
                placement: 'right',
                fallbackPlacements: ['left', 'bottom'],
                title: tooltipContent,
                container: 'body'
            });

            element.tooltip('show');
        });

        // Hide tooltip on mouseleave
        $(document).on('mouseleave', '.status-cell', function() {
            $(this).tooltip('hide');
        });

        // Apply leave button click handler
        $(document).on('click', '.apply-leave-btn', function(e) {
            e.stopPropagation();
            e.preventDefault();

            const employee = $(this).data('employee');
            const date = $(this).data('date');

            // Fill Modal Data
            $('#leaveEmployee').val(employee);
            $('#leaveDate').val(date);

            // Show Modal
            $('#applyLeaveModal').modal('show');
        });

        function formatDateDDMonYYYY(dateStr) {
            if (!dateStr) return '';

            const [year, month, day] = dateStr.split('-');
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

            return `${day}-${months[parseInt(month, 10) - 1]}-${year}`;
        }

        // Status cell click handler
        $(document).on('click', '.status-cell', function(e) {
            // Don't trigger if apply-leave-btn was clicked
            if ($(e.target).hasClass('apply-leave-btn')) {
                return;
            }

            const employeeId = $(this).attr('data-employee-id');
            const employeeCode = $(this).attr('data-employee-code');
            const statusId = $(this).attr('data-status-id');
            const employee = $(this).attr('data-employee');
            const date = $(this).attr('data-date');
            const inTime = $(this).attr('data-intime');
            const outTime = $(this).attr('data-outtime');
            const status = $(this).attr('data-status');
            const shiftName = $(this).attr('data-shift-name');
            const shiftStartTime = $(this).attr('data-shift-start-time');
            const shiftEndTime = $(this).attr('data-shift-end-time');

            const formattedInTime = formatTo24Hour(inTime);
            const formattedOutTime = formatTo24Hour(outTime);
            const formattedShiftStartTime = formatTo24Hour(shiftStartTime);
            const formattedShiftEndTime = formatTo24Hour(shiftEndTime);

            // Multi shift data
            const resolverData = $(this).data('dept-shifts');
            if (resolverData && Array.isArray(resolverData.shifts) && resolverData.shifts.length > 0) {
                let shiftNames  = [];
                let shiftStarts = [];
                let shiftEnds   = [];

                resolverData.shifts.forEach(shift => {
                    shiftNames.push(shift.shift_name);
                    shiftStarts.push(shift.shift_start);
                    shiftEnds.push(shift.shift_end);
                });

                $('#shift_name').text(shiftNames.join(' | '));
                $('#modalShiftStartTime').text(shiftStarts.join(' | '));
                $('#modalShiftEndTime').text(shiftEnds.join(' | '));

            } else {
                // Single shift fallback
                $('#shift_name').text(shiftName);
                $('#modalShiftStartTime').text(formattedShiftStartTime);
                $('#modalShiftEndTime').text(formattedShiftEndTime);
            }

            // Populate modal fields
            $('#modalCheckInTime').val(formattedInTime);
            $('#modalCheckOutTime').val(formattedOutTime);
            $('#modalEmployeeId').val(employeeId);
            $('#modalStatusId').val(statusId);
            // $('#modalEmployeeName').val(employee);
            $('#modalEmployeeName').text(employee);
            $('#modalStatusDate').val(date);
            $('#modalStatusEndDate').val(date);
            $('#punch_date').val(date);
            $('#punch_date_show').text(formatDateDDMonYYYY(date));
            $('#emp_code').text(employeeCode);
            $('#modalStatus').val(status);
            // $('#shift_name').text(shiftName);
            // $('#modalShiftStartTime').text(formattedShiftStartTime);
            // $('#modalShiftEndTime').text(formattedShiftEndTime);
            $("#reason").val("");
            // Show attendance modal
            $('#attendanceModal').modal('show');
        });

        function calculateWorkHours(checkIn, checkOut) {
            if (!checkIn || !checkOut) return "--";

            // Convert to 24-hour Date objects
            const parseTime = (timeStr) => {
                const [time, modifier] = timeStr.split(" ");
                let [hours, minutes] = time.split(":").map(Number);

                if (modifier === "PM" && hours < 12) hours += 12;
                if (modifier === "AM" && hours === 12) hours = 0;

                return { hours, minutes };
            };

            const inTime = parseTime(checkIn);
            const outTime = parseTime(checkOut);

            const inDate = new Date(0, 0, 0, inTime.hours, inTime.minutes);
            const outDate = new Date(0, 0, 0, outTime.hours, outTime.minutes);

            let diff = (outDate - inDate) / 1000 / 60; // difference in minutes
            if (diff < 0) diff += 24 * 60; // handle overnight shift

            const hrs = String(Math.floor(diff / 60)).padStart(2, "0");
            const mins = String(diff % 60).padStart(2, "0");

            return `${hrs} hr ${mins} min`;
        }

        function calculateWorkHours(checkIn, checkOut) {
            if (!checkIn || !checkOut) return "--";

            // Convert to 24-hour Date objects
            const parseTime = (timeStr) => {
                const [time, modifier] = timeStr.split(" ");
                let [hours, minutes] = time.split(":").map(Number);

                if (modifier === "PM" && hours < 12) hours += 12;
                if (modifier === "AM" && hours === 12) hours = 0;

                return { hours, minutes };
            };

            const inTime = parseTime(checkIn);
            const outTime = parseTime(checkOut);

            const inDate = new Date(0, 0, 0, inTime.hours, inTime.minutes);
            const outDate = new Date(0, 0, 0, outTime.hours, outTime.minutes);

            let diff = (outDate - inDate) / 1000 / 60; // difference in minutes
            if (diff < 0) diff += 24 * 60; // handle overnight shift

            const hrs = String(Math.floor(diff / 60)).padStart(2, "0");
            const mins = String(diff % 60).padStart(2, "0");

            return `${hrs} hr ${mins} min`;
        }

        function formatDate(input) {
            if (!input) {
                console.error("Invalid date input:", input);
                return "";
            }

            // Log input to ensure it's in ISO 8601 format
            console.log("Input Date for formatting:", input);  // Example: "2025-09-02T18:30:00.000000Z"

            // Parse the ISO 8601 date string to a Date object
            const date = new Date(input);
            
            // Check if the date is valid
            if (isNaN(date.getTime())) {
                console.error("Invalid date:", input);
                return "";
            }

            // Get the year, month, and day from the Date object
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');  // Ensure two-digit month
            const day = date.getDate().toString().padStart(2, '0');  // Ensure two-digit day

            // Return the date in 'YYYY-MM-DD' format
            return `${year}-${month}-${day}`;
        }

        // Update attendance button click handler
        $('#updateAttendanceBtn').on('click', function(e) {
            e.preventDefault();

            const form = $('#attendanceUpdateForm')[0];

            // Custom validation: Ensure at least one of "Punch In" or "Punch Out" is filled
            const inTime = $('#modalCheckInTime').val();
            const outTime = $('#modalCheckOutTime').val();
            const mark_as_absent = $('#mark_as_absent').is(':checked') ? 1 : 0;
            console.log(mark_as_absent);
            const permissions = {{ 
                $permission->check_route_permission('admin/attendance/byattendance-update', 115) 
                || 
                $permission->check_route_permission('admin/attendance/byattendance-update', 117) 
                ? 'true' : 'false' 
            }};
            if (!permissions) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Permission denied for update attendance.',
                    showConfirmButton: false,
                    timer: 3000
                });
                $('#attendanceModal').modal('hide');
                return; // Prevent form submission

            }

            if (!mark_as_absent) {
                if (!inTime && !outTime) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'At least one of the fields (Punch In or Punch Out) is required.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }
            }

            if (form.checkValidity() === false) {
                form.reportValidity();
                return;
            }

            const formData = $('#attendanceUpdateForm').serialize();

            $.ajax({
                url: "{{ route('attendance.month-attendance-update') }}",
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status) {
                        console.log(response);

                        let atd_data = response.logData;
                        let masterStatusData = response.statusData;

                        // Parse the 'm_other' field for color and other data
                        let statusOther = JSON.parse(masterStatusData.m_other);

                        // Debugging: Log the original date string
                        console.log("Original al_date:", atd_data.al_date); // Log the original date string

                        // Format the date to match the format of 'data-date' in the HTML
                        const atdDateFormatted = formatDate(atd_data.al_date);

                        // Log the formatted date for debugging
                        console.log("Formatted Date:", atdDateFormatted); // Should log '2025-09-02'

                        // Find the specific span element that matches the employee ID and formatted date
                        const atd_span = $(`#montly-attendance-table-dynamic tbody tr td span[data-employee-id='${atd_data.al_emp_id}'][data-date='${atdDateFormatted}']`);

                        // Check if the element exists
                        if (atd_span.length > 0) {
                            // Update the text/status
                            $(atd_span).text(masterStatusData.m_type);

                            // Reset classes
                            $(atd_span).removeClass();
                            $(atd_span).addClass(`status-cell status-${masterStatusData.m_type}`);

                            // Apply the color from 'm_other'
                            $(atd_span).css('color', statusOther.color);

                            const localInTime  = convertUTCToLocal(atd_data.al_check_in_time);
                            const localOutTime = convertUTCToLocal(atd_data.al_check_out_time);

                            // Set or update attributes
                            // $(atd_span).attr('data-intime', atd_data.al_check_in_time);
                            // $(atd_span).attr('data-outtime', atd_data.al_check_out_time);
                            $(atd_span).attr('data-intime', localInTime);
                            $(atd_span).attr('data-outtime', localOutTime);
                            $(atd_span).attr('data-status', masterStatusData.m_name);
                            $(atd_span).attr('data-status-id', atd_data.al_attendance_status);
                            $(atd_span).attr('data-status-color', statusOther.color);
                            $(atd_span).attr('data-working-hour', decimalToHHMM(atd_data.al_total_worked_hours));

                            // Optionally, show success message
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            console.log("The target span element was not found.");
                        }

                        // Hide the modal
                        $('#attendanceModal').modal('hide');
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error updating attendance';
                    // $('#attendanceModal').modal('hide');
                    
                    // Try to get more specific error message
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            // Keep default error message
                        }
                    }
            
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: errorMessage,
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
            $('#mark_as_absent').prop('checked', false);
        });

        function decimalToHHMM(decimalHours) {
            const hours = Math.floor(decimalHours);
            const minutes = Math.round((decimalHours - hours) * 60);
            
            // Format with leading zeros
            const formattedHours = String(hours).padStart(2, '0');
            const formattedMinutes = String(minutes).padStart(2, '0');
            
            return `${formattedHours}:${formattedMinutes}`;
        }

        // Clean up tooltips when datatable is redrawn
        $('#montly-attendance-table-dynamic').on('draw.dt', function() {
            $('.status-cell').tooltip('dispose');
        });

        $("#exportBtn").on("click", function (e) {
            e.preventDefault();
            let attendanceStatus = $("#attendance_status").val();
            if (attendanceStatus != "") {
                // Collect other filters if needed
                let branch = $("#mt_branchFilter").val();
                let department = $("#mt_departmentFilter").val();
                let designation = $("#mt_designationFilter").val();
                let month = $("#mt_monthFilter").val();

                // Build query string
                let params = $.param({
                    attendanceStatus: attendanceStatus,
                    branch: branch,
                    department: department,
                    designation: designation,
                    month: month,
                    _token: '{{ csrf_token() }}'
                });

                // Use window.location to trigger file download
                window.location.href = "{{ route('monthly.attendance.status.download') }}?" + params;

                // Optionally, close the modal
                $('#exportEmpAttendanceData').modal('hide');
            } else {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Please select a status to export.',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    });
</script>