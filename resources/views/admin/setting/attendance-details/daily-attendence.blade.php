<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$branchFilter = CommonUtils::getCustomModelData(new Branch(), [['method' => 'where', 'args' => ['br_b_id', $user->emp_b_id]]]);
$departmentFilter = CommonUtils::getCustomModelData(new Department(), [['method' => 'where', 'args' => ['d_b_id', $user->emp_b_id]]]);
$designationFilter = CommonUtils::getCustomModelData(new Designation(), [['method' => 'where', 'args' => ['dg_b_id', $user->emp_b_id]]]);
$gradeFilter = CommonUtils::getCustomModelData(new Grade(), [['method' => 'where', 'args' => ['g_b_id', $user->emp_b_id]]]);

$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    Daily Attendance
@endsection
@section('css')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            // First initialize your custom datatable
            datatable({
                tableId: "daily-attendance-table-dynamic",
                url: "{{ route('attendance.daily-attendance') }}",
                pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false,
                drawCallback: function (settings) {
                    const json = settings.json;
                    if (json) {
                        $("#totalEmployees").text(json.totalEmployees);
                        $("#presentCount").text(json.presentCount);
                        $("#absentCount").text(json.absentCount);
                        $("#halfDayCount").text(json.halfDayCount);
                        $("#lateCount").text(json.lateCount);
                        $("#leaveCount").text(json.leaveCount);
                    }
                    const approvalButtons = $(".approval-buttons");

                    if (settings.json?.canApprove ||
                        (settings.json?.apHiBtn && typeof settings.json.apHiBtn === "object" && Object.keys(settings.json.apHiBtn).length > 0)) {
                        approvalButtons.css("display", "block");
                    } else {
                        approvalButtons.css("display", "none");
                    }
                }
            });

            // 🔹 Now get the DataTable instance
            const table = $('#daily-attendance-table-dynamic').DataTable();
table.page.len(10).draw();
            // 🔹 Disable sorting for specific columns
            [5, 6, 7, 8, 9, 10].forEach(idx => {
                table.settings()[0].aoColumns[idx].bSortable = false;
            });

            // 🔹 Redraw table so changes take effect
            table.draw();
        });

    </script>
@endsection
<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    /* #btnXyz:hover {
        color: #fff
    } */

    table td {
        padding: 0;
    }

</style>



@section('content')
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/attendance/daily-attendance') }}">Attendance</a></li>
                <li class="active"><span><b>Daily Attendance</b></span></li>
            </ol>
        </div>

        <!-- START ROW -->
        <div class="row">
            <div class="col-xxl-2 col-xl-2 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="text-start"> <span class="font-weight-semibold">Total</span>
                                    <h3 class="mb-0 mt-1 text-primary" id="totalEmployees"> {{ $totalEmployees }} </h3>
                                </div>
                            </div>
                            <div class="col-5 ">
                                <div class="icon1 bg-primary-transparent my-auto pt-3 float-end"> <i
                                        class="las la-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Present</span>
                                    <h3 class="mb-0 mt-1 text-success" id="presentCount"> {{ $presentCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-success-transparent my-auto pt-3 float-end"> <i
                                        class="las la-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Absent</span>
                                    <h3 class="mb-0 mt-1 text-danger" id="absentCount"> {{ $absentCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-danger-transparent my-auto float-end pt-3"> <i class="las la-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Half-Day</span>
                                    <h3 class="mb-0 mt-1 text-secondary" id="halfDayCount"> {{ $halfDayCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-secondary-transparent my-auto pt-3 float-end"><i
                                        class="las la-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Late</span>
                                    <h3 class="mb-0 mt-1 text-orange" id="lateCount"> {{ $lateCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-orange-transparent my-auto pt-3 float-end"> <i class="las la-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Leave</span>
                                    <h3 class="mb-0 mt-1 text-pink" id="leaveCount"> {{ $leaveCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-pink-transparent my-auto pt-3 float-end"> <i class="las la-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END ROW -->

        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Daily Attendance </h4>
                        <div class="page-rightheader ms-auto">
                            <div class="align-items-end flex-wrap my-auto right-content breadcrumb-right">
                                <div class="d-flex">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-1">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test" style="width: 100%"
                                        data-length>
                                        <option value="5">5</option>
                                        <option value="10" selected>10</option>
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

                            <div class="col-sm-4">
                            </div>

                            {{-- <div class="col-sm-2" style="margin-top: 10px;">
                                <div id="approval-buttons" class="justify-content-end gap-3 m-5"
                                    style="text-align: right;">
                                    <label class="custom-control custom-checkbox-md mx-3">
                                        Select All &nbsp;&nbsp;
                                        <input type="checkbox" id="selectAll" class="custom-control-input-success"
                                            name="example-checkbox1" value="option1" onclick="selectCheckbox(this)">
                                        <span class="custom-control-label-md success"></span>
                                    </label>
                                </div>
                            </div> --}}

                            <div class="col-sm-2" style="margin-top: 10px;">
                                <div id="approval-buttons" class="justify-content-end gap-3 m-5"
                                    style="text-align: right;">
                                    <label class="custom-control custom-checkbox-md mx-3">
                                        Select All &nbsp;&nbsp;
                                        <input type="checkbox" id="selectAll" class="custom-control-input-success"
                                            onclick="selectCheckbox(this)">
                                        <span class="custom-control-label-md success"></span>
                                    </label>
                                </div>
                            </div>



                            <div class="col-sm-1">
                                <button class="custom-button w-100" type="button" onclick="toggleFilters()"
                                    style="margin-top: 28px;">
                                    <!-- Custom SVG: 2 horizontal lines with knobs -->
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <!-- Top slider -->
                                        <line x1="3" y1="8" x2="21" y2="8"
                                            stroke-linecap="round" />
                                        <circle cx="10" cy="8" r="1.5" fill="currentColor" />

                                        <!-- Bottom slider -->
                                        <line x1="3" y1="16" x2="21" y2="16"
                                            stroke-linecap="round" />
                                        <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                    </svg>
                                    Filters
                                </button>
                            </div>

                            <div class="col-sm-1"
                                style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
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
                                    <button class="btn btn-info" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu p-2" style="min-width: fit-content;">
                                        <li>
                                            <a class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                                id="approveBtn" value="1">
                                                <i class="las la-check-circle"></i> Approve
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                                id="rejectBtn" value="0">
                                                <i class="las la-times-circle"></i> Reject
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)"
                                                class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                                data-bs-toggle="modal" data-bs-target="#ExcelModal">
                                                <i class="las la-file-upload"></i> Upload Attendance
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('daily.attendance.downloadExcel') }}"
                                                class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)" onclick="handleBulkAttendance()"
                                                class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2">
                                                <i class="las la-clock"></i> Time Correction
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
                                            <select id="daily_branchFilter" data-filter class="form-select search-txt filter_border ">
                                                <option value="">All</option>
                                                @foreach ($branchFilter as $branchF)
                                                    <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="departmentFilter" class="form-label">Department</label>
                                            <select id="daily_departmentFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($departmentFilter as $departmentF)
                                                    <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="designationFilter" class="form-label">Designation</label>
                                            <select id="daily_designationFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($designationFilter as $designationF)
                                                    <option value="{{ $designationF->dg_id }}">
                                                        {{ $designationF->dg_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="activeFilter" class="form-label">Status</label>
                                            <select id="daily_activeFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                <option value="71">Active</option>
                                                <option value="72">Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="toDate" class="form-label">Date</label>
                                            <input type="date" id="fromDate" value="{{ old('fromDate', date('Y-m-d')) }}" name="fromDate"
                                                class="form-control filter_border" data-date-filter="from-date" />
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <script>
                                function toggleFilters() {
                                    const container = document.getElementById('filterContainer');
                                    container.style.display = container.style.display === 'none' ? 'flex' : 'none';
                                }
                            </script>



                            <style>
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
                            </style>

                        </div>



                        <div class="table-responsive">
                            <table class="table display table-vcenter table-hover text-wrap border-bottom"
                                id="daily-attendance-table-dynamic">
                                <thead>
                                    <tr>
                                        @foreach ($columns as $column)
                                            <th style="font-size: 13px">{{ $column }}</th>
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
                                <ul data-pagination class="custom-pagination"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload excel file -->
    <div class="modal fade" id="ExcelModal" tabindex="-1" role="dialog" aria-labelledby="largemodal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <h5 class="modal-title" id="DaModalLabel">Upload Daily Attendance File</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('daily-attendance.import') }}" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="upload_file">Upload File :</label>
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
                    <div class="modal-footer">
                        <a class="btn btn-outline-danger " data-bs-dismiss="modal">Close</a>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- close Upload excel file -->

    {{-- Edit modal start --}}
    <div class="container">
        <div class="modal fade" id="showmodal_attendance" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" style="font-size:17px;"> Current Date -
                            ({{ \Carbon\Carbon::now()->format('d-m-Y') }})</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" data-bs-dismiss="modal">&times;</span>
                        </button>
                    </div>

                    <form action="{{ route('bulk.attendance') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="mb-5">
                                    <span class="my-5"><span class="fw-bold fs-14">Shift Start : <span
                                                id="genral_shift_start"></span> <br>
                                            <span class="my-5"><span class="fw-bold fs-14">Shift End : <span
                                                        id="genral_shift_end"></span>
                                </div>
                            </div>
                            <div class="row">
                                <input type="hidden" id="emp_id" name="id" class="form-control">
                                <input type="hidden" id="punch_date" name="punch_date" class="form-control">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Punch In</label>
                                        <div class="input-group">
                                            <input type="time" name="in_time" class="form-control timepicker"
                                                pattern="^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(?:[APap][mM])?$" id="punch_in">
                                            {{-- <div class="input-group-text">
                                                <i class="fa fa-clock-o"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Punch Out</label>
                                        <div class="input-group">
                                            <input type="time" class="form-control timepicker" id="punch_out"
                                                name="out_time" required
                                                pattern="^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(?:[APap][mM])?$">
                                            {{-- <div class="input-group-text">
                                                <i class="fa fa-clock-o"></i>
                                            </div> --}}
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

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-danger " data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-outline-primary" type="submit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- Edit modal end --}}

    <!-- ✅ Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="approvalMessage"></p>
                    <label for="approvalRemark" class="form-label">Enter Remark:</label>
                    <textarea class="form-control" id="approvalRemark" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-primary" id="confirmApproval">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
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

        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif
    });
</script>
<script>
    function convertToAmPm(time) {
        if (!time) return ""; // Prevent errors if time is undefined
        let [hours, minutes] = time.split(":").map(Number);
        let period = hours >= 12 ? "PM" : "AM";
        hours = hours % 12 || 12; // Convert 24-hour format to 12-hour format
        return `${hours}:${minutes < 10 ? "0" : ""}${minutes} ${period}`;
    }

    function handleBulkAttendance() {
        let selectedEmployees = [];
        let punchDates = [];
        let shiftStartTimes = [];
        let shiftEndTimes = [];

        // Get all checked checkboxes
        const selectedCheckboxes = document.querySelectorAll(".employee-checkbox:checked");

        selectedCheckboxes.forEach(function(checkbox) {
            let values = checkbox.value.split("|"); // Split the concatenated values
            selectedEmployees.push(values[0]); // Employee ID
            punchDates.push(values[1]); // Attendance Date
            shiftStartTimes.push(values[2]); // Shift Start Time
            shiftEndTimes.push(values[3]); // Shift End Time
        });

        if (selectedEmployees.length > 0) {
            document.getElementById("emp_id").value = selectedEmployees.join(",");
            document.getElementById("punch_date").value = punchDates[0]; // Assigning attendance dates
            console.log(punchDates);
            // Convert and display shift start & end times
            if (shiftStartTimes[0] || shiftEndTimes[0]) {
                document.getElementById("genral_shift_start").innerText = convertToAmPm(shiftStartTimes[0]);
                document.getElementById("genral_shift_end").innerText = convertToAmPm(shiftEndTimes[0]);
            }

            // Open the Bootstrap modal
            var myModal = new bootstrap.Modal(document.getElementById("showmodal_attendance"));
            myModal.show();
        } else {
            alert("Please select at least one employee.");
        }
    }

    var attendIDs = [];

    function selectCheckbox(checkbox) {
        if (checkbox.id === 'selectAll') {
            var isChecked = $(checkbox).is(':checked');
            $('.employee-checkbox').each(function() {
                $(this).prop('checked', isChecked).trigger('change');
            });
        } else {
            var attendID = $(checkbox).data('id');
            var isChecked = $(checkbox).is(':checked');

            if (isChecked) {
                if (!attendIDs.includes(attendID)) {
                    attendIDs.push(attendID);
                }
            } else {
                var index = attendIDs.indexOf(attendID);
                if (index !== -1) {
                    attendIDs.splice(index, 1);
                }
            }

            var allChecked = $('.employee-checkbox').length === $('.employee-checkbox:checked').length;
            $('#selectAll').prop('checked', allChecked);
        }
    }



    $(document).ready(function() {
        let approvalType = ""; // Store action type (Approve/Reject)
        let logStatus;

        // ✅ Open Modal on Approve/Reject Click
        $("#approveBtn, #rejectBtn").click(function() {
            let isApprove = $(this).attr('id') === "approveBtn";
            approvalType = $(this).attr('value');
            actionType = isApprove ? "Approve" : "Reject";
            logStatus = isApprove ? 157 : 170;
            $("#approvalMessage").text(
                `Are you sure you want to ${actionType.toLowerCase()} this request?`);
            $("#approvalModal").modal("show");
        });

        // ✅ Confirm Button Click
        $("#confirmApproval").click(function() {
            let remark = $("#approvalRemark").val().trim();
            console.log("attendIDs:", attendIDs);
            if (remark === "") {
                Swal.fire({
                    icon: "warning",
                    text: "Please enter a remark before proceeding.",
                    timer: 3000,
                });
                $("#confirmApproval").attr("disabled", false);
                return false;
            }

            if (attendIDs.length === 0) {
                Swal.fire({
                    icon: "warning",
                    text: "No attendance record selected for approval.",
                    timer: 3000,
                });
                $("#confirmApproval").attr("disabled", false);
                return false;
            }

            // Get DataTable instance
            let table = $("#daily-attendance-table-dynamic").DataTable();
            let settings = table.settings()[0];
            let apHiBtnData = settings.json?.apHiBtn || {};
            let canApprove = settings.json?.canApprove;
            let moduleId = settings.json?.moduleId;

            if (typeof apHiBtnData !== "undefined" && Object.keys(apHiBtnData).length > 0) {
                // Create an array of objects for each leave ID
                let formattedData = attendIDs.map(attendID => ({
                    approval_status: apHiBtnData.approval_status,
                    approval_type: approvalType,
                    approval_action_type: apHiBtnData.approval_action_type,
                    approval_sequence: apHiBtnData.approval_sequence,
                    atd_id: attendID,
                    module_id: apHiBtnData.module_id,
                    is_last_approval: apHiBtnData.is_last_approval,
                    emp_d_id: apHiBtnData.emp_d_id,
                    message: remark
                }));
                // console.log("Formatted Data:", formattedData);

                // Send AJAX request for approval/rejection
                $.ajax({
                    url: '{{ route('admin.bulk-handler') }}',
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        POST_TYPE: 'Attendance_REQUEST_APPROVAL',
                        data: formattedData
                    },
                    beforeSend: function() {
                        $("#confirmApproval").attr("disabled", true);
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: "success",
                            text: response.message,
                            timer: 3000,
                            didClose: () => location.reload()
                        });
                    },
                    error: function(error) {
                        Swal.fire({
                            icon: "error",
                            text: "Something went wrong!",
                            timer: 3000,
                        });
                    },
                    complete: function() {
                        $("#confirmApproval").attr("disabled", false);
                        $("#approvalModal").modal("hide");
                    }
                });
            } else {
                let formattedData = attendIDs.map(attendID => ({
                    log_module_id: moduleId.module_id,
                    log_request_id: attendID,
                    log_description: remark,
                    log_status: logStatus,
                    deduction_amount: 0,
                    prefix: 'atd_',
                    model: 'AttendanceRecord'
                }));

                // Send AJAX request for approval/rejection
                $.ajax({
                    url: '{{ url('daily-attendance/bulk-approval') }}',
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        data: formattedData
                    },
                    beforeSend: function() {
                        $("#confirmApproval").attr("disabled", true);
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: "success",
                                text: response.message,
                                timer: 3000,
                                didClose: () => location.reload()
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                text: response.message || "Something went wrong!",
                            });
                        }
                    },
                    error: function(error) {
                        Swal.fire({
                            icon: "error",
                            text: "Something went wrong!",
                            timer: 3000,
                        });
                    },
                    complete: function() {
                        $("#confirmApproval").attr("disabled", false);
                        $("#approvalModal").modal("hide");
                    }
                });
            }
        });
    });
</script>
