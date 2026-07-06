<?php
use App\Helpers\CentralLogics;
use App\Models\AttendanceRecord;
use App\Models\OvertimePolicy;
use Illuminate\Support\Carbon;
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;
$permission = new RolePermissionLogics();
$user = Auth::user();

$is_ot_enabled = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->where('ot_is_enabled', 1)->exists();
// $permissions2 = RolePermissionLogics::get_role_wise_menu_permissions();

// dd($permissions2->rhp_permissions);
// dd($permission->check_route_permission('admin/attendance/byattendance-update', 115));
?>
@extends('admin.layout.master')
@section('title')
    Attendance By
@endsection
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

    <style>
        /* Make the table header sticky */
        thead {
            position: sticky;
            top: 0;
            background-color: white; /* Ensure header is visible */
            z-index: 1000; /* Keep it above other elements */
        }

        /* Optional: Add border and shadow for better visibility */
        thead th {
            border-bottom: 2px solid black;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            padding: 10px;
        }

        /* Scrollable table body */
        .table-container {
            max-height: 550px; /* Set table height */
            overflow-y: auto; /* Enable vertical scroll */
        }

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
        
        .table-responsive table {
            font-size: 12px; /* Base font size for desktop */
        }

        .big-checkbox {
            width: 25px;
            height: 25px;
            cursor: pointer;
        }
        
        @media (max-width: 992px) { /* Tablets and smaller */
            .table-responsive table {
                font-size: 11px;
            }
        }
        
        @media (max-width: 768px) { /* Mobile devices */
            .table-responsive table {
                font-size: 10px;
            }
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
    <div>
        <div class="p-0 mt-3">
            <div class="row">
                <div class="col-md-4">
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ url('/admin/attendance/summary-attendance') }}">Attendance Summary</a></li>
                        <li class="active"><span><b>Attendance By</b></span></li>
                    </ol>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <div class="page-rightheader ms-md-auto">
                        <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                            <div class="page-title d-flex align-items-center gap-2">
                                <input type="month" class="form-control w-auto" id="monthFilter"
                                    value="{{ $monthFilter ? $monthFilter : now()->format('Y-m') }}" readonly>
                                <span class="text-primary">
                                    {{ $emp->emp_fname . ' ' . $emp->emp_mname . ' ' . $emp->emp_lname }} ({{ $emp->emp_code }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- START ROW -->

        <div class="row mt-5">
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row" data-bs-toggle="presentCountDetails" data-bs-placement="right"
                            data-bs-content="{{ 'Full Day Present: ' . ($presentCount - ($approvedLeaveCount + $approvedMissedPunchCount + ($halfDayCount/2))) . ' Half Day: ' . $halfDayCount. ' Approved MisPunch: ' . $approvedMissedPunchCount. ' Approved Leave: ' . $approvedLeaveCount  }}">
                            <div class="col-7">
                                <div class="text-start"> <span class="font-weight-semibold">Present Days</span>
                                    <h3 class="mb-0 mt-1 text-success">{{ $presentCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5 ">
                                <div class="icon1 bg-success-transparent my-auto pt-3 float-end"> <i
                                        class="las la-users"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Absent Days</span>
                                    <h3 class="mb-0 mt-1 text-danger ">{{ $absentCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-danger-transparent my-auto pt-3 float-end"> <i class="las la-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Half Days</span>
                                    <h3 class="mb-0 mt-1 text-secondary">{{ $halfDayCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-secondary-transparent my-auto float-end pt-3"> <i
                                        class="las la-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Holidays</span>
                                    <h3 class="mb-0 mt-1 text-purple">{{ $holidayCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-purple-transparent my-auto pt-3 float-end"><i class="las la-male"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Missed-Punch</span>
                                    <h3 class="mb-0 mt-1 text-warning">{{ $missedPunchCount }}</h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-warning-transparent my-auto pt-3 float-end"> <i
                                        class="las la-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Leave</span>
                                    <h3 class="mb-0 mt-1 text-pink">{{ $leaveCount }}</h3>
                                    </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-pink-transparent my-auto pt-3 float-end"> <i
                                        class="las la-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Total Days</span>
                                    <h3 class="mb-0 mt-1 text-danger">{{ $totalDays }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-danger-transparent my-auto pt-3 float-end"> <i
                                        class="las la-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row"  data-bs-toggle="totalWorkedCountDetails" data-bs-placement="left"
                        data-bs-content="{{ 'Present Days: ' .$presentCount . ' Week off Days: ' . $weekOffCount. ' Holidays: ' . $holidayCount }}">
                            <div class="col-7">
                                <div class="text-start"> <span class="font-weight-semibold">Salary Days</span>
                                    <h3 class="mb-0 mt-1 text-success">{{ $totalWorkedDay }} </h3>
                                </div>
                            </div>
                            <div class="col-5 ">
                                <div class="icon1 bg-success-transparent my-auto pt-3 float-end"> <i
                                        class="las la-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Week off Days</span>
                                    <h3 class="mb-0 mt-1 text-danger">{{ $weekOffCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-danger-transparent my-auto pt-3 float-end"> <i
                                        class="las la-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Late Days</span>
                                    <h3 class="mb-0 mt-1 text-danger">{{ $lateCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-danger-transparent my-auto pt-3 float-end"> <i
                                        class="las la-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Early Exit</span>
                                    <h3 class="mb-0 mt-1 text-orange">{{ $earlyExitCount }}</h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-orange-transparent my-auto pt-3 float-end"> <i
                                        class="las la-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Over Time</span>
                                    <h3 class="mb-0 mt-1 text-orange" style="font-size: 14px;">{{ $is_ot_enabled ? $totalOTHrs : 0 }}</h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-orange-transparent my-auto pt-3 float-end"> <i
                                        class="las la-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- END ROW -->

        {{-- Edit modal start --}}
        <div class="container">
            <div class="modal fade" id="showmodal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="showmodalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content" style="width: 115%">
                        <div class="modal-header">
                            <h5 class="modal-title" style="font-size:17px;"><span id="emp_code"></span> - <span
                                    id="emp_name"></span> (<span id="date"></span>)</h5>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true" data-bs-dismiss="modal">&times;</span>
                            </button>
                        </div>

                        <form action="{{ route('edit.attendence') }}" method="post" id="attendanceUpdateForm" onsubmit="customValidation(event)">
                            @csrf
                            <div class="modal-body">
                                <div class="row">
                                    <div class="mb-5 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="shift-row">
                                                <span class="shift-label fw-bold fs-14">Shift Name</span>:
                                                <span id="shift_name" class="fw-bold fs-14"></span>
                                            </div>
                                            <div class="shift-row">
                                                <span class="shift-label fw-bold fs-14">Shift Start</span>:
                                                <span id="genral_shift_start" class="fw-bold fs-14"></span>
                                            </div>
                                            <div class="shift-row">
                                                <span class="shift-label fw-bold fs-14">Shift End</span>:
                                                <span id="genral_shift_end" class="fw-bold fs-14"></span>
                                            </div>
                                        </div>

                                        <div class="form-check ms-3 d-flex align-items-center">
                                            <input type="checkbox" name="mark_as_absent" class="form-check-input big-checkbox" id="mark_as_absent">
                                            <label for="mark_as_absent" class="form-check-label fw-bold fs-14 ms-2">Mark as Absent</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <input type="hidden" id="emp_id" name="id" class="form-control">
                                    <input type="hidden" id="punch_date" name="punch_date" class="form-control">
                                    <input type="hidden" id="fallback_date">

                                    <input type="hidden" name="device_id" id="device_id">
                                    <input type="hidden" name="platform" id="platform">
                                    <input type="hidden" name="browser" id="browser">
                                    <input type="hidden" name="device_info" id="device_fingerprint">
                                    <input type="hidden" name="latitude" id="latitude">
                                    <input type="hidden" name="longitude" id="longitude">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Check In Date</label>
                                            <div class="input-group">
                                                <input type="date"
                                                       class="form-control"
                                                       name="in_date"
                                                       id="check_in_date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Check In</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control time_format_24hrs" name="in_time" placeholder="HH:MM" id="punch_in">
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
                                                       id="check_out_date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Check Out</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control time_format_24hrs" name="out_time" placeholder="HH:MM" id="punch_out">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label">Reason <sapn class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <textarea rows="3" id="reason" class="form-control" name="reason" placeholder="Enter Reason" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4" id="edit_history">
                                    <div class="col-12">
                                        <h6 class="fw-bold">Edit History</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-sm align-middle text-nowrap">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 60px;">S. No</th>
                                                        <th style="width: 120px;">Log ID</th>
                                                        <th style="width: 120px;">Check In</th>
                                                        <th style="width: 120px;">Check Out</th>
                                                        <th style="width: 150px;">Updated By</th>
                                                        <th style="width: 150px;">Created Time</th>
                                                        <th style="min-width: 200px;">Remark</th>
                                                        <th style="width: 80px;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="attendanceHistoryBody">
                                                    <!-- Dynamic rows will be injected here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-danger " data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- Edit modal end --}}

        {{-- view modal start --}}
        <div class="container">
            <div class="modal fade" id="viewshowmodal" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header ">
                            <h5 class="modal-title" id="exampleModalLongTitle" style="font-size:18px;">Attendance Details</h5>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true" data-bs-dismiss="modal">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card-header border-bottom-0 d-block">
                                        <h5 class="">Timesheet : <span class="fs-14 mx-3 text-muted"
                                                id="date1"></span></h5>
                                        <h6 class=""><span class="fs-14 text-dark" id="gs_text"></span>
                                            <span class="fs-14 text-dark mx-3" id="genral_shift"></span>
                                        </h6>
                                    </div>

                                    <div class="col-sm-12 my-auto" style="height: 260px">
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="p-3 text-center border border-muted">
                                                    <h6 class="mb-1 fs-14 font-weight-semibold" id="punch_in1"></h6>
                                                    <small class="text-muted fs-14">Check In</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="chart-circle chart-circle-md" data-value="100"
                                                    data-thickness="8" data-color="#0dcd94"
                                                    style="border:solid 5px #1877f2; border-radius:50px">
                                                    <div class="chart-circle-value text-muted" id="total_work"></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-3 text-center border border-muted">
                                                    <h6 class="mb-1 fs-14 font-weight-semibold" id="punch_out1"></h6>
                                                    <small class="text-muted fs-14">Check Out</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-5">
                                            <div class="row">
                                                <div class="col-5 text-center border border-muted px-5 py-1 mx-3">
                                                    <small class="text-muted fs-13">Break Time</small>
                                                    <p class="mb-1 fs-14 font-weight-semibold" id="break"></p>
                                                </div>
                                                <div class="col-5 text-center border border-muted px-5 py-1 mx-3">
                                                    <small class="text-muted fs-13">Overtime</small>
                                                    <p class="mb-1 fs-14 font-weight-semibold" id="over_time">
                                                        20</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="col-sm-12">
                                        <div class="">
                                            <h4 class="mx-3">Timeline</h4>
                                        </div>
                                        <div class="col-sm-12 mt-5">
                                            <div class="tl-content tl-content-active">
                                            <ul class="timeline">
                                                <li class="primary">
                                                    <div class="tl-header mx-3">
                                                        <span class="tl-marker"></span>
                                                        <div class="row">
                                                            <div class="col-10">
                                                                <p class="tl-title">Check In at <span id="punch_in2"></span> |
                                                                    <span id="shift_name1"></span>
                                                                    <br>
                                                                    <a target="_blank">
                                                                        <span class="text-muted fs-12 "><i
                                                                                class="fa fa-map-marker mx-1"></i>
                                                                            <span id="location_punch_in1"></span>
                                                                        </span></a>
                                                                    <br>
                                                                    <span class="tl-title"></span>
                                                                </p>
                                                                <p>
                                                                </p>
                                                            </div>
                                                            <div class="col-2">
                                                                <button class=" btn my-auto" data-bs-toggle="modal"
                                                                    data-bs-target="#punchIn">
                                                                    <span class="avatar avatar-md brround me-3 rounded-circle"
                                                                        style="background-image: url('');"></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tl-header d-none mx-3" id="tapList">
                                                        <div class="row">
                                                            <p>
                                                            </p>
                                                            <div class="col-112" style="overflow: scroll; height:7rem">
                                                                <p class="tl-title" id="TapListItem">
                                                                </p>
                                                                <p>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="primary">
                                                    <div class="tl-header mx-3">
                                                        <span class="tl-marker"></span>
                                                        <div class="row">
                                                            <div class="col-10">
                                                                <p class="tl-title">Check Out at
                                                                    <span id="punch_out2"></span> | <span
                                                                        id="shift_name2"></span>
                                                                    <br>
                                                                    <a target="_blank">
                                                                        <span class="text-muted fs-12 "><i
                                                                                class="fa fa-map-marker mx-1"></i>
                                                                            <span id="location_punch_out1"></span>
                                                                        </span></a>
                                                                    <br>
                                                                    <span class="tl-title"></span>
                                                                </p>
                                                                <p>
                                                                </p>
                                                            </div>
                                                            <div class="col-2">
                                                                <button class=" btn my-auto" data-bs-toggle="modal"
                                                                    data-bs-target="#punchOut">
                                                                    <span class="avatar avatar-md brround me-3 rounded-circle"
                                                                        style="background-image: url('');"></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tl-header d-none mx-3">
                                                        <span class="tl-marker"></span>
                                                        <div class="row">
                                                            <div class="col-10">
                                                                <p class="tl-title">Second Half
                                                                    <br>
                                                                    <span class="text-dark fs-12 d-none">Remark:
                                                                        <span class="text-muted"></span>
                                                                    </span>
                                                                    <br>
                                                                </p>
                                                                <p>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ui>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-end">
                            <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- view modal end --}}

        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12" style="z-index:3;">
                <div class="card">

                    <div class="card-body">
                        <div class="table-responsive table-container" >
                            <table class="table display table-vcenter text-wrap border-bottom"
                                id="attendance-by-table-dynamic">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Status</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Working Hour</th>
                                        <th>Updated By</th>
                                        <th>Previous Check In</th>
                                        <th>Previous Out</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthlyAttendanceData as $attendance_data)
                                        <tr>
                                            <td>{{ date('d M Y', strtotime($attendance_data['date']) )}}</td>
                                            <td>{{ date('D', strtotime($attendance_data['date'])) }}</td>


                                            <td>
                                                @foreach (explode('/', $attendance_data['status']) as $key => $single_status)
                                                    <span style="color: {{ explode('/', $attendance_data['statusColor'])[$key] }}">{{ $key > 0 ? '/ ' . $single_status : $single_status }}</span>
                                                @endforeach
                                            </td>

                                            <td>
                                                {{ isset($attendance_data['checkInTime']) ? $attendance_data['checkInTime'] : '' }}

                                                @if (!empty($attendance_data['late']) && is_numeric($attendance_data['late']))
                                                    @php
                                                        $lateMinutes = abs(intval($attendance_data['late']));
                                                        $lateHours = intval($lateMinutes / 60);
                                                        $lateRemainder = $lateMinutes % 60;
                                                    @endphp

                                                    <br><span class="late-status fs-10 fw-bolder">
                                                        {{ 'Late By: ' . ($lateHours ? $lateHours . ' Hr ' : '') . ($lateRemainder ? $lateRemainder . ' Min' : '') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td>{{ isset($attendance_data['checkOutTime']) ? $attendance_data['checkOutTime'] : '' }}
                                                @php
                                                    // Remove commas and ensure numeric
                                                    $earlyExit = (float) str_replace(',', '', $attendance_data['earlyExit']);

                                                    // Calculate hours and minutes
                                                    $hours = intdiv($earlyExit, 60);
                                                    $minutes = $earlyExit % 60;
                                                @endphp
                                                @if (!empty($attendance_data['earlyExit']) && is_numeric($attendance_data['earlyExit']))
                                                    <br>
                                                    <span class="late-status fs-10 fw-bolder">
                                                        Early Exit By:
                                                        {{ $hours > 0 ? $hours . ' Hr ' : '' }}
                                                        {{ $minutes > 0 ? $minutes . ' Min' : '' }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td>

                                                @php
                                                    $workingHour = $attendance_data['workingHour'] ?? 0;

                                                    if (is_numeric($workingHour) && $workingHour > 0) {
                                                        $hours = floor($workingHour);
                                                        $minutesDecimal = $workingHour - $hours;
                                                        $minutes = round($minutesDecimal * 60);
                                                        $workingHourBase = $hours + ($minutes / 100);
                                                    } else {
                                                        $workingHourBase = null;
                                                    }
                                                @endphp

                                                {{ $workingHourBase !== null ? number_format($workingHourBase, 2) : '-' }}

                                                @if (!empty($attendance_data['OT']) && is_numeric($attendance_data['OT']))
                                                    @php
                                                        $ot = $attendance_data['OT'];

                                                        if (strpos($ot, '.') !== false) {
                                                            [$otHours, $otMinutes] = explode('.', $ot);
                                                            $otMinutes = str_pad($otMinutes, 2, '0', STR_PAD_RIGHT);
                                                        } else {
                                                            $otHours = $ot;
                                                            $otMinutes = 0;
                                                        }
                                                    @endphp

                                                    <br>
                                                    <span class="overtime-status fs-10 fw-bolder">
                                                        OT: {{ $otHours }} Hour{{ $otMinutes > 0 ? ' ' . $otMinutes . ' Min' : '' }}
                                                    </span>
                                                @endif


                                                {{--@if (!empty($attendance_data['OT']) && is_numeric($attendance_data['OT']))
                                                    @php
                                                        $otTime = explode('.', CentralLogics::convertHourMins($attendance_data['OT']));
                                                        $otHours = $otTime[0] ?? 0;
                                                        $otMinutes = isset($otTime[1]) ? str_pad($otTime[1], 2, '0', STR_PAD_RIGHT) : 0;
                                                    @endphp
                                                    <br>
                                                    <span class="overtime-status fs-10 fw-bolder">
                                                        OT: {{ $otHours }} Hour{{ $otMinutes > 0 ? ' ' . $otMinutes . ' Min' : '' }}
                                                    </  span>
                                                @endif--}}

                                            </td>
                                            <td>
                                                {{  $attendance_data['updatedBy'] ?? '-' }}
                                            </td>

                                            <td>{{ isset($attendance_data['previousCheckInTime']) ? $attendance_data['previousCheckInTime'] : '-' }}
                                                @if ($attendance_data['previousLate'])
                                                    @php
                                                        $prevlateMinutes = intval($attendance_data['previousLate']);
                                                        $prelateHours = intval($prevlateMinutes / 60);
                                                        $prelateRemainder = $prevlateMinutes % 60;
                                                    @endphp

                                                    <br><span class="late-status fs-10 fw-bolder">
                                                        {{ 'Late By: ' . ($prelateHours ? $prelateHours . ' Hr ' : '') . ($prelateRemainder ? $prelateRemainder . ' Min' : '') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td>{{ isset($attendance_data['previousCheckOutTime']) ? $attendance_data['previousCheckOutTime'] : '-' }}
                                                @if ($attendance_data['previousExit'])

                                                    @php
                                                        $prevexitMinutes = intval($attendance_data['previousExit']);
                                                        $preexitHours = intval($prevexitMinutes / 60);
                                                        $preexitRemainder = $prevexitMinutes % 60;
                                                    @endphp

                                                    <br><span class="late-status fs-10 fw-bolder">
                                                        {{ 'Late By: ' . ($preexitHours ? $preexitHours . ' Hr ' : '') . ($preexitRemainder ? $preexitRemainder . ' Min' : '') }}
                                                    </span>
                                                @endif
                                            </td>
                                         <td>
                                            <?php
                                                $shiftData = CentralLogics::getResolverData($emp, $attendance_data['date']);
                                                $shiftData2 = CentralLogics::getResolverDataMultiple($emp, $attendance_data['date']);
                                            ?>
                                            <div class="dropdown">
                                                <button class="btn btn-light p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i> <!-- Bootstrap Icons -->
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if ($emp->emp_date_of_joining <= $attendance_data['date'])
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="javascript:void(0);"
                                                           onclick="openEditModel(this);"
                                                           data-date="{{ $attendance_data['date'] }}"
                                                           data-emp-id="{{ $emp->emp_id }}"
                                                           data-code="{{ $emp->emp_code }}"
                                                           data-dept_shifts='@json(
                                                                $shiftData2["resolved_from"] === "department"
                                                                    ? $shiftData2["shifts"]
                                                                    : []
                                                            )'
                                                           data-name="{{ $emp->emp_full_name }}"
                                                           data-shift_name="{{ $shiftData['shift_name'] }}"
                                                           data-shift_start_time="{{ $shiftData['shift_start'] }}"
                                                           data-shift_end_time="{{ $shiftData['shift_end'] }}"
                                                           data-punch_in="{{ $attendance_data['checkInTime'] }}"
                                                           data-punch_out="{{ $attendance_data['checkOutTime'] }}"
                                                           data-total_work="{{ $attendance_data['workingHour'] }}"
                                                           data-mark_abs="{{ $attendance_data['al_is_absent'] ?? '' }}"
                                                           data-remark="{{ $attendance_data['attendance_remark'] ?? '' }}">
                                                           <i class="feather feather-edit"></i> Edit
                                                        </a>
                                                    @endif
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="javascript:void(0);"
                                                           onclick="openViewModel(this)"
                                                           data-date="{{ $attendance_data['date'] }}"
                                                           data-dept_shifts='@json(
                                                                $shiftData2["resolved_from"] === "department"
                                                                    ? $shiftData2["shifts"]
                                                                    : []
                                                            )'
                                                           data-shift_name="{{ $emp->fh_shift_type && $emp->fh_shift_type->pst_name ? $emp->fh_shift_type->pst_name : '--' }}"
                                                           data-shift_start_time="{{ $emp->fh_shift_type && $emp->fh_shift_type->pst_start_time ? \Carbon\Carbon::parse($emp->fh_shift_type->pst_start_time)->format('Y-m-d H:i:s') : '--' }}"
                                                           data-shift_end_time="{{ $emp->fh_shift_type && $emp->fh_shift_type->pst_end_time ? \Carbon\Carbon::parse($emp->fh_shift_type->pst_end_time)->format('Y-m-d H:i:s') : '--' }}"
                                                           data-break="{{ $emp->fh_shift_type && $emp->fh_shift_type->pst_break_duration_minutes ? $emp->fh_shift_type->pst_break_duration_minutes : '--' }}"
                                                           data-paid="{{ $emp->fh_shift_type && $emp->fh_shift_type->pst_is_break_paid ? $emp->fh_shift_type->pst_is_break_paid : '--' }}"
                                                           data-punch_in="{{ $attendance_data['checkInTime'] }}"
                                                           data-punch_out="{{ $attendance_data['checkOutTime'] }}"
                                                           data-overtime="{{ $attendance_data['OT'] }}"
                                                           data-total_work="{{ $attendance_data['workingHour'] }}"
                                                           data-in_location="{{ $attendance_data['checkInLocation'] }}"
                                                           data-out_location="{{ $attendance_data['checkOutLocation'] }}"
                                                           data-in_photo='{{ json_encode($attendance_data['checkInPhoto']) }}'
                                                           data-out_photo='{{ json_encode($attendance_data['checkOutPhoto']) }}'>
                                                           <i class="feather feather-eye"></i> View
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
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@4/dist/fp.min.js"></script>

<script>

    $(document).ready(function() {
        // Initialize Bootstrap popover with hover trigger
        var presentCountDetails = [].slice.call(document.querySelectorAll('[data-bs-toggle="presentCountDetails"]'));
        presentCountDetails.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl, {
                trigger: 'hover' // Set trigger to hover
            });
        });

        var totalWorkedCountDetails = [].slice.call(document.querySelectorAll('[data-bs-toggle="totalWorkedCountDetails"]'));
        totalWorkedCountDetails.map(function(popoverTriggerE2) {
            return new bootstrap.Popover(popoverTriggerE2, {
                trigger: 'hover' // Set trigger to hover
            });
        });

    });

    // document.addEventListener('DOMContentLoaded', function () {
    //     const checkIn = document.getElementById('punch_in');
    //     const checkOut = document.getElementById('punch_out');

    //     function validateTime() {
    //         const inTime = checkIn.value;
    //         const outTime = checkOut.value;

    //         if (inTime && outTime && inTime >= outTime) {
    //             Swal.fire({
    //                 icon: 'warning',
    //                 title: 'Invalid Time',
    //                 text: 'Check Out time must be after Check In time.',
    //                 confirmButtonColor: '#d33',
    //                 confirmButtonText: 'OK'
    //             });

    //             checkOut.value = ''; // Clear invalid time
    //         }
    //     }

    //     checkIn.addEventListener('change', validateTime);
    //     checkOut.addEventListener('change', validateTime);
    // });

    const deletepermissions = {{ $permission->check_route_permission('admin/attendance/history/delete', 118) ? 'true' : 'false' }};

    function openViewModel(context) {
        var button = $(event.relatedTarget);
        var date = $(context).data('date');
        var shift_name = $(context).data('shift_name');
        var shift_start_time = $(context).data('shift_start_time');
        var shift_end_time = $(context).data('shift_end_time');
        var break_duration = $(context).data('break');
        var paid = $(context).data('paid');
        var overtime = $(context).data('overtime');
        var punch_in = $(context).data('punch_in') || "00:00";  // Default to 00:00 if empty
        var punch_out = $(context).data('punch_out') || "00:00";
        var total_work = $(context).data('total_work') || "0.00";
        var in_location = $(context).data('in_location');
        var out_location = $(context).data('out_location');

        var in_photo = $(context).data('in_photo'); // JSON array
        var out_photo = $(context).data('out_photo'); // JSON array

        // Ensure data is properly parsed
        try {
            in_photo = typeof in_photo === "string" ? JSON.parse(in_photo) : in_photo;
            out_photo = typeof out_photo === "string" ? JSON.parse(out_photo) : out_photo;
        } catch (error) {
            console.error("Error parsing in_photo/out_photo:", error);
            in_photo = [];
            out_photo = [];
        }

        var inPhotoStr = in_photo.length > 0 ? in_photo.join(", ") : "No Photo Available";
        var outPhotoStr = out_photo.length > 0 ? out_photo.join(", ") : "No Photo Available";

        // Convert YYYY-MM-DD to DD-MMM-YYYY (08-Nov-2024)
        function formatDate(inputDate) {
            let months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            let [year, month, day] = inputDate.split("-");
            return `${day}-${months[parseInt(month) - 1]}-${year}`;
        }

        var formattedDate = formatDate(date);

        var convertToAmPm = time => new Date(time).toLocaleString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        // Format and update the shift times
        var shift_data2 = $(context).data('dept_shifts');
        if (Array.isArray(shift_data2) && shift_data2.length > 1) {
            let shiftTextArr = [];

            shift_data2.forEach(shift => {
                shiftTextArr.push(
                    `${shift.shift_name} (${shift.shift_start} - ${shift.shift_end})`
                );
            });

            // Multiple department shifts
            $('#gs_text').text('Department Shifts :');
            $('#genral_shift').text(shiftTextArr.join(' | '));

        } else if (Array.isArray(shift_data2) && shift_data2.length === 1) {
            // Single shift from department
            $('#gs_text').text('');
            $('#genral_shift').removeClass('mx-3').text(
                `${shift_data2[0].shift_name} (${shift_data2[0].shift_start} - ${shift_data2[0].shift_end})`
            );

        } else {
            // Fallback (old single shift vars)
            $('#gs_text').text('');
            $('#genral_shift').text(
                `${shift_name} (${formatTime(shift_start_time)} - ${formatTime(shift_end_time)})`
            );
        }

        $('#viewshowmodal').modal('show');
        $('#date1').text(formattedDate);
        $('#time_shift').text(shift_start_time);
        $('#shift_name1').text(shift_name);
        $('#shift_name2').text(shift_name);
        // $('#genral_shift').text(shift_start_time + ' To ' + shift_end_time);
        $('#punch_in1').text(punch_in);
        $('#punch_out1').text(punch_out);
        $('#punch_in2').text(punch_in);
        $('#punch_out2').text(punch_out);
        $('#total_work').text(total_work);
        $('#break').text(break_duration);
        $('#over_time').text(overtime);
        $('#location_punch_in1').text(in_location);
        $('#location_punch_out1').text(out_location);
        $('#punchin_photo').text(inPhotoStr);
        $('#punchout_photo').text(outPhotoStr);
    }
    
    function formatTime(timeStr) {
        let date = new Date(timeStr);
        let hours = String(date.getHours()).padStart(2, '0');
        let minutes = String(date.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function extractTime(value) {
        if (!value) return '';
        if (value.includes(' ')) {
            return value.split(' ')[1].substring(0,5); // HH:mm
        }
        return value.substring(0,5);
    }
    
    function openEditModel(context) {
        console.log('nansda -> ' + JSON.stringify($(context).data()));
        var id = $(context).data('emp-id');
        var code = $(context).data('code');
        var name = $(context).data('name');
        var date = $(context).data('date');
        var shift_name = $(context).data('shift_name');
        var shift_start_time = $(context).data('shift_start_time');
        var shift_end_time = $(context).data('shift_end_time');
        var punch_in = $(context).data('punch_in');
        var punch_out = $(context).data('punch_out');
        var reason = $(context).data('remark');
        var mark_as_absent = $(context).data('mark_abs');
        var shift_data2 = $(context).data('dept_shifts');
    
        // Convert YYYY-MM-DD to DD-MMM-YYYY (08-Nov-2024)
        function formatDate(inputDate) {
            let months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            let [year, month, day] = inputDate.split("-");
            return `${day}-${months[parseInt(month) - 1]}-${year}`;
        }
    
        var formattedDate = formatDate(date);
    
        // Populate modal fields
        $('#emp_id').val(id);
        $('#emp_code').text(code);
        $('#emp_name').text(name);
        $('#date').text(formattedDate);

        $('#fallback_date').val(date);
        $('#check_in_date').val(date);
        $('#check_out_date').val(date);    

        $("#punch_date").val(date);
        if (Array.isArray(shift_data2) && shift_data2.length > 0) {
            let shiftNames = [];
            let shiftStarts = [];
            let shiftEnds = [];
            console.log(shift_data2);
            shift_data2.forEach(shift => {
                shiftNames.push(shift.shift_name);
                shiftStarts.push(shift.shift_start);
                shiftEnds.push(shift.shift_end);
            });

            $('#shift_name').text(shiftNames.join('  |  '));
            $('#genral_shift_start').text(shiftStarts.join('  |  '));
            $('#genral_shift_end').text(shiftEnds.join('  |  '));

        } else {
            $('#shift_name').text(shift_name);
            $('#genral_shift_start').text(formatTime(shift_start_time));
            $('#genral_shift_end').text(formatTime(shift_end_time));
        }

        
        $('#reason').text(reason);
        $('#punch_in').val(extractTime(punch_in));
        $('#punch_out').val(extractTime(punch_out));
        $('#mark_as_absent').prop('checked', mark_as_absent == 1);
        
        // Load attendance history
        openAttendanceModal(id, date);
    }

    function formatTo24Hour(dateTime) {
        if (!dateTime) return '';
        const date = new Date(dateTime);
        return date.toLocaleTimeString('en-GB', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    function openAttendanceModal(empId, punchDate) {
        fetch("{{ route('attendance.history') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                emp_id: empId,
                date: punchDate
            })
        })
        .then(res => res.json())
        .then(res => {
            if(res.status) {
                loadAttendanceHistory(res.data);
            }
        })
        .catch(error => {
            console.error('Error loading attendance history:', error);
        });
        
        // Show the modal using proper Bootstrap 5 method
        var modalElement = document.getElementById('showmodal');
        var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        modal.show();
    }

    // Function to properly close the modal
    function closeModal() {
        var modalElement = document.getElementById('showmodal');
        var modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    }

    // Add event listener for modal hidden event to clean up
    document.addEventListener('DOMContentLoaded', function() {
        var modalElement = document.getElementById('showmodal');
        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function () {
                // Clear form or reset any states if needed
                $('#attendanceHistoryBody').empty();
            });
        }
    });

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

    function convertTo24HourFormat(time) {
        const [hours, minutesPart] = time.split(':');
        const [minutes, period] = minutesPart.split(' ');
        let hours24 = parseInt(hours, 10);

        if (period.toLowerCase() === 'pm' && hours24 !== 12) {
            hours24 += 12;
        }
        if (period.toLowerCase() === 'am' && hours24 === 12) {
            hours24 = 0;
        }

        return `${hours24.toString().padStart(2, '0')}:${minutes}`;
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

    function customValidation(e) {
        e.preventDefault();
        // Custom validation: Ensure at least one of "Punch In" or "Punch Out" is filled
        const inTime = $('#punch_in').val();
        const outTime = $('#punch_out').val();
        const c_in_date = $('#check_in_date').val();
        const c_out_date = $('#check_out_date').val();
        const isAbsent = $('#mark_as_absent').is(':checked');
        const form = $('#attendanceUpdateForm')[0];
        const permissions = {{ 
            $permission->check_route_permission('admin/attendance/byattendance-update', 115) 
            || 
            $permission->check_route_permission('admin/attendance/byattendance-update', 117) 
            ? 'true' : 'false' 
        }};
        if (!permissions) {
            // Show error message if both are empty
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Permission denied for update attendance.',
                showConfirmButton: false,
                timer: 3000
            });
            closeModal();
            return; // Prevent form submission
        }

        if (!isAbsent) {
            if (!inTime && !outTime) {
                // Show error message if both are empty
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'At least one of the fields (Check In or Check Out) is required.',
                    showConfirmButton: false,
                    timer: 3000
                });
                return; // Prevent form submission
            }
            if ((inTime && !c_in_date) || (outTime && !c_out_date)) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Date is required while filling time.',
                    showConfirmButton: false,
                    timer: 3000
                });
                return; // Prevent form submission
            }
        }

        if (form.checkValidity() === false) {
            form.reportValidity();
            return;
        }

        document.getElementById("attendanceUpdateForm").submit();
    }

    // function customValidation(e) {
    //     e.preventDefault();
        
    //     // ========== DEVICE TRACKING CODE START ==========
    //     // Ensure device_id is captured before submission
    //     let deviceId = document.getElementById('device_id')?.value;
    //     let deviceFingerprint = document.getElementById('device_fingerprint')?.value;
        
    //     if (!deviceId || deviceId === '') {
    //         // Device ID not captured yet, capture it first
    //         Swal.fire({
    //             toast: true,
    //             position: 'top-end',
    //             icon: 'info',
    //             title: 'Capturing device information...',
    //             showConfirmButton: false,
    //             timer: 1500
    //         });
            
    //         // Generate device fingerprint
    //         if (typeof FingerprintJS !== 'undefined') {
    //             FingerprintJS.load().then(fp => {
    //                 fp.get().then(result => {
    //                     document.getElementById('device_id').value = result.visitorId;

    //                     const platform = getOS();
    //                     const browser = getBrowserName();

    //                     document.getElementById('platform').value = platform;
    //                     document.getElementById('browser').value = browser;

    //                     const deviceInfo = {
    //                         browser: browser,
    //                         platform: platform,
    //                         userAgent: navigator.userAgent,
    //                         screen: `${screen.width}x${screen.height}`,
    //                         time: new Date().toISOString()
    //                     };

    //                     document.getElementById('device_fingerprint').value = JSON.stringify(deviceInfo);

    //                     customValidation(e);
    //                 });
    //             }).catch(error => {
    //                 console.error('Fingerprint error:', error);
    //                 // Proceed without device tracking
    //                 proceedWithValidation(e);
    //             });
    //         } else {
    //             console.warn('FingerprintJS not loaded');
    //             proceedWithValidation(e);
    //         }
    //         return;
    //     }
    //     // ========== DEVICE TRACKING CODE END ==========
        
    //     // Call the validation function
    //     proceedWithValidation(e);
    // }

    // ✅ MAIN FUNCTION (Button Click)
    // function customValidation(e) {
    //     e.preventDefault();

    //     // 🔥 STEP 1: Permission Check (TOP PE)
    //     if (navigator.permissions) {

    //         navigator.permissions.query({ name: 'geolocation' }).then(function(result) {

    //             if (result.state === 'granted' || result.state === 'prompt') {
    //                 // 👉 Continue flow
    //                 handleDeviceFlow(e);
    //             } 
    //             else if (result.state === 'denied') {
    //                 Swal.fire({
    //                     icon: 'error',
    //                     title: 'Location Blocked ❌',
    //                     text: 'Please enable location from browser settings',
    //                 });
    //             }

    //         }).catch(() => {
    //             handleDeviceFlow(e);
    //         });

    //     } else {
    //         // 👉 Old browser fallback
    //         handleDeviceFlow(e);
    //     }
    // }


    // // ✅ STEP 2: DEVICE HANDLING
    // function handleDeviceFlow(e) {

    //     let deviceId = document.getElementById('device_id')?.value;

    //     if (!deviceId) {

    //         Swal.fire({
    //             toast: true,
    //             position: 'top-end',
    //             icon: 'info',
    //             title: 'Capturing device information...',
    //             showConfirmButton: false,
    //             timer: 1500
    //         });

    //         if (typeof FingerprintJS !== 'undefined') {

    //             FingerprintJS.load().then(fp => {
    //                 fp.get().then(result => {

    //                     // ✅ Device ID
    //                     document.getElementById('device_id').value = result.visitorId;

    //                     const platform = getOS();
    //                     const browser = getBrowserName();

    //                     document.getElementById('platform').value = platform;
    //                     document.getElementById('browser').value = browser;

    //                     const deviceInfo = {
    //                         browser: browser,
    //                         platform: platform,
    //                         userAgent: navigator.userAgent,
    //                         screen: `${screen.width}x${screen.height}`,
    //                         time: new Date().toISOString()
    //                     };

    //                     document.getElementById('device_fingerprint').value = JSON.stringify(deviceInfo);

    //                     // 👉 NEXT: GEO
    //                     getLocationAndSubmit(e);
    //                 });

    //             }).catch(error => {
    //                 console.error('Fingerprint error:', error);
    //                 getLocationAndSubmit(e);
    //             });

    //         } else {
    //             getLocationAndSubmit(e);
    //         }

    //         return;
    //     }

    //     // 👉 Already device exists
    //     getLocationAndSubmit(e);
    // }


    // // ✅ STEP 3: GEO LOCATION + FINAL SUBMIT
    // function getLocationAndSubmit(e) {

    //     if (!navigator.geolocation) {
    //         Swal.fire('Error', 'Geolocation not supported ❌', 'error');
    //         return;
    //     }

    //     Swal.fire({
    //         title: 'Fetching location...',
    //         text: 'Please allow location access 📍',
    //         allowOutsideClick: false,
    //         didOpen: () => Swal.showLoading()
    //     });

    //     navigator.geolocation.getCurrentPosition(

    //         function(position) {

    //             // ✅ SET LAT LONG
    //             document.getElementById('latitude').value = position.coords.latitude;
    //             document.getElementById('longitude').value = position.coords.longitude;

    //             console.log("LAT:", position.coords.latitude);
    //             console.log("LNG:", position.coords.longitude);

    //             Swal.close();

    //             // ✅ FINAL SUBMIT
    //             if (typeof proceedWithValidation === "function") {
    //                 proceedWithValidation(e);
    //             } else {
    //                 document.getElementById('attendanceUpdateForm').submit();
    //             }
    //         },

    //         function(error) {

    //             if (error.code === error.PERMISSION_DENIED) {
    //                 Swal.fire({
    //                     icon: 'error',
    //                     title: 'Location Permission Denied ❌',
    //                     text: 'Please allow location to mark attendance',
    //                 });
    //             } else {
    //                 Swal.fire('Error', 'Location not available ❌', 'error');
    //             }
    //         },

    //         {
    //             enableHighAccuracy: true,
    //             timeout: 10000,
    //             maximumAge: 0
    //         }
    //     );
    // }

    // Separate function for actual validation
    function proceedWithValidation(e) {
        // Custom validation: Ensure at least one of "Punch In" or "Punch Out" is filled
        const inTime = $('#punch_in').val();
        const outTime = $('#punch_out').val();
        const c_in_date = $('#check_in_date').val();
        const c_out_date = $('#check_out_date').val();
        const isAbsent = $('#mark_as_absent').is(':checked');
        const form = $('#attendanceUpdateForm')[0];
        const permissions = {{ 
            $permission->check_route_permission('admin/attendance/byattendance-update', 115) 
            || 
            $permission->check_route_permission('admin/attendance/byattendance-update', 117) 
            ? 'true' : 'false' 
        }};
        
        if (!permissions) {
            // Show error message if both are empty
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Permission denied for update attendance.',
                showConfirmButton: false,
                timer: 3000
            });
            closeModal();
            return; // Prevent form submission
        }

        if (!isAbsent) {
            if (!inTime && !outTime) {
                // Show error message if both are empty
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'At least one of the fields (Check In or Check Out) is required.',
                    showConfirmButton: false,
                    timer: 3000
                });
                return; // Prevent form submission
            }
            if ((inTime && !c_in_date) || (outTime && !c_out_date)) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Date is required while filling time.',
                    showConfirmButton: false,
                    timer: 3000
                });
                return; // Prevent form submission
            }
        }

        if (form.checkValidity() === false) {
            form.reportValidity();
            return;
        }

        // Log device info before submit (for debugging)
        const deviceId = document.getElementById('device_id')?.value;
        console.log('Submitting with Device ID:', deviceId);
        
        document.getElementById("attendanceUpdateForm").submit();
    }

    function getBrowserName() {
        const ua = navigator.userAgent;
        if (ua.includes("Chrome")) return "Chrome";
        if (ua.includes("Firefox")) return "Firefox";
        if (ua.includes("Safari")) return "Safari";
        if (ua.includes("Edge")) return "Edge";
        return "Unknown";
    }

    function getOS() {
        const platform = navigator.platform.toLowerCase();
        if (platform.includes("win")) return "Windows";
        if (platform.includes("mac")) return "MacOS";
        if (platform.includes("linux")) return "Linux";
        return "Unknown";
    }

    function formatDates2(inputDate) {
        if (!inputDate) return '';

        // string clean
        inputDate = String(inputDate).trim();

        // Case 1: already YYYY-MM-DD
        if (/^\d{4}-\d{2}-\d{2}$/.test(inputDate)) {
            return inputDate;
        }

        // fallback (try Date object)
        let d = new Date(inputDate);
        if (!isNaN(d)) {
            let y = d.getFullYear();
            let m = String(d.getMonth() + 1).padStart(2, '0');
            let day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        return '';
    }
    
    function loadAttendanceHistory(historyData) {
        let tbody = document.getElementById("attendanceHistoryBody");
        let editHistoryDiv = document.getElementById("edit_history");
        let markAbsentCheckbox = document.getElementById("mark_as_absent");

        markAbsentCheckbox.checked = false;
        tbody.innerHTML = "";

        if (!Array.isArray(historyData) || historyData.length === 0) {
            editHistoryDiv.style.display = "none";
            return;
        }

        const latest = historyData[0];

        console.log('latest',  latest);
        if (latest && latest.editable) {
            editHistoryDiv.style.display = "block";

            const fallbackDate = $('#fallback_date').val();
            if (latest && latest.al_is_absent !== undefined && latest.al_is_absent !== null) {
                markAbsentCheckbox.checked = latest.al_is_absent === true || latest.al_is_absent == 1;
            }

            if (latest.al_check_in_time) {
                $('#check_in_date').val(formatDates2(latest.al_check_in_time));
            } else {
                $('#check_in_date').val(fallbackDate);
            }

            if (latest.al_check_out_time) {
                $('#check_out_date').val(formatDates2(latest.al_check_out_time));
            } else {
                $('#check_out_date').val(fallbackDate);
            }
        } else {
            editHistoryDiv.style.display = "none";
        }
    
        historyData.forEach((item, index) => {
            tbody.innerHTML += `
                <tr id="row-${item.al_id}">
                    <td style="text-align:center;">${index+1}</td>
                    <td style="text-align:center;">${item.al_code}</td>
                    <td style="text-align:center;">${item.al_check_in_time ?? '-'}</td>
                    <td style="text-align:center;">${item.al_check_out_time ?? '-'}</td>
                    <td style="text-align:center;">${item.al_updated_by}</td>
                    <td style="text-align:center;">${item.al_create_time}</td>
                    <td style="text-align:center;">${item.al_reason ?? '-'}</td>
                    <td style="text-align:center;">
                        <a href="javascript:void(0)" 
                          class="text-danger" 
                          onclick="deleteHistory(${item.al_id})">
                          X
                        </a>
                    </td>
                </tr>
            `;
        });
    }


    
    function deleteHistory(id) {
        // if (!deletepermissions) {
        //     // Show error message if both are empty
        //     Swal.fire({
        //         toast: true,
        //         position: 'top-end',
        //         icon: 'error',
        //         title: 'Permission denied for delete attendance.',
        //         showConfirmButton: false,
        //         timer: 3000
        //     });
        //     closeModal();
        //     return; // Prevent form submission

        // }

        Swal.fire({
            title: 'Are you sure?',
            text: "This record will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('attendance.history.delete') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(res => res.json())
                .then(res => {
                    if(res.status) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
    
                        // ✅ Fade out & remove row without reload
                        let row = document.querySelector(`#row-${id}`);
                        if(row){
                            row.style.transition = "opacity 0.4s";
                            row.style.opacity = 0;
                            setTimeout(() => row.remove(), 400);
                        }
                    } else {
                        Swal.fire(
                            'Oops!',
                            res.message,
                            'error'
                        );
                    }
                })
                .catch(err => {
                    console.error("Delete error:", err);
                    Swal.fire(
                        'Oops!',
                        'Something went wrong!',
                        'error'
                    );
                });
            }
        });
    }
</script>

@section("script")
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script>
    $(document).ready(function () {

        const modalEl = document.getElementById('showmodal');

        modalEl.addEventListener('shown.bs.modal', function () {
            console.log("after the modal is shown");

            $('#punch_in').timepicker({
                timeFormat: 'HH:mm', // Use 24-Hour Format
                interval: 15, // Interval of 15 minutes
                minTime: '00:00', // Minimum time
                maxTime: '23:45', // Maximum time
                startTime: '00:00', // Timepicker start time
                dynamic: false, // Disable dynamic time updates
                dropdown: true, // Enable dropdown list
                scrollbar: true // Enable scrollbar
            });

            $('#punch_out').timepicker({
                timeFormat: 'HH:mm', // Use 24-Hour Format
                interval: 15, // Interval of 15 minutes
                minTime: '00:00', // Minimum time
                maxTime: '23:45', // Maximum time
                startTime: '00:00', // Timepicker start time
                dynamic: false, // Disable dynamic time updates
                dropdown: true, // Enable dropdown list
                scrollbar: true // Enable scrollbar
            });
        });

    })
</script>
@endsection
