<?php


use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$user = Auth::user();
$branchFilter = CommonUtils::getCustomModelData(new Branch(), [['method' => 'where', 'args' => ['br_b_id', $user->emp_b_id]]]);
$departmentFilter = CommonUtils::getCustomModelData(new Department(), [['method' => 'where', 'args' => ['d_b_id', $user->emp_b_id]]]);
$designationFilter = CommonUtils::getCustomModelData(new Designation(), [['method' => 'where', 'args' => ['dg_b_id', $user->emp_b_id]]]);

$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    Attendance Dashboard
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/gh/emn178/chartjs-plugin-labels/src/chartjs-plugin-labels.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-doughnutlabel/2.0.3/chartjs-plugin-doughnutlabel.js">
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "attendance-table-dynamic",
                url: "{{ route('attendance.dashboard') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });
        });
    </script>
@endsection
@section('css')
    <style>
        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            font-size: 12px;
            /* Reduced font size */
        }

        .chart-inner {
            padding: 0px;
        }

        #BA-chart-job-error {
            margin: 0 auto;
            width: 100%;
            height: 100%;
        }

        .list-group {
            overflow-y: auto;
            scrollbar-width: thin;
            /* Firefox */
            scrollbar-color: #ccc transparent;
            /* Firefox */
            font-size: 12px;
            /* Reduced font size */
        }

        .list-group::-webkit-scrollbar {
            width: 6px;
            /* Scrollbar width for WebKit browsers */
        }

        .list-group::-webkit-scrollbar-thumb {
            background-color: #ccc;
            /* Scrollbar color */
            border-radius: 10px;
            /* Rounded scrollbar */
        }

        .data {
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 80ms ease-in;
            position: relative;
            font-size: 12px;
            /* Reduced font size */
        }

        .custom-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .btn-primary.dropdown-toggle:hover .feather-filter {
            color: #1877f2 !important;
        }

        .border-hover {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .border-hover:hover {
            border-color: #3f51b5;
            box-shadow: 0 5px 15px rgba(63, 81, 181, 0.2);
        }

        .birthday-marquee-container {
            height: auto;
            overflow: hidden;
            position: relative;
            font-size: 12px;
            /* Reduced font size */
        }

        .birthday-marquee {
            animation: scroll-up 20s linear infinite;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .birthday-marquee:hover {
            animation-play-state: paused;
        }

        @keyframes scroll-up {
            0% {
                transform: translateY(100%);
            }

            100% {
                transform: translateY(-100%);
            }
        }

        .page-rightheader .input-group-text {
            border: 1px solid #1877f2;
            background: #1877f2;
            font-size: 12px;
            /* Reduced font size */
        }
    </style>
@endsection

@section('content')
    <div class=" p-0 ">
        <div class="container-fluid" id="confetti">
            <div class="page-header d-flex">
                <div class="page-leftheader">
                    <div class="page-title"><i class="feather feather-user-check sidemenu_icon"> <b
                                style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif">Attendance
                                Dashboard</b></i>
                    </div>
                </div>
                <div class="page-rightheader ms-auto" wire:ignore>
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-flex ms-auto">
                            <div class="header-datepicker me-3">
                                <div class="input-group">
                                    <input class="form-control bg-light fc-datepicker d-none"
                                        data-currentdate="{{ date('d M Y') }}" type="text" id="dashboardTimePicker" style="width: 150px !important;border: 1px solid #1877f2;border-left: none;border-radius: 0 7px 7px 0;height: 38px;"
                                        onchange="dashboardCountAjax(this)">

                                    <div class="input-group-prepend">
                                        <div class="input-group-text"style="background-color: #1877f2; border: solid 1px #1877f2 !important; border-radius: 7px 0px 0px 7px;">
                                            <i class="feather feather-calendar"></i>
                                        </div>
                                    </div>

                                    <form id="filter-form" method="POST">
                                        @csrf
                                        <input type="date" name="date"
                                            class="form-control input-small fw-bolder bg-light" id="filter-date"
                                            data-currentdate="{{ date('d M Y') }}" placeholder="Select Date"
                                            value="{{ request('date', \Carbon\Carbon::now()->toDateString()) }}"style="width: 150px !important;border: 1px solid #1877f2;border-left: none;border-radius: 0 7px 7px 0;height: 38px;">
                                    </form>
                                </div>
                            </div>
                            <script>
                                function dashboardTimeFormate() {
                                    var element = document.getElementById('dashboardTimePicker');
                                    element.value = element.getAttribute('data-currentdate');
                                }
                                window.onload = dashboardTimeFormate;
                            </script>
                            <div class="header-datepicker me-3 d-none d-md-block">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text d-xl-block"
                                            style="background-color: #1877f2; border: solid 1px #1877f2;">
                                            <i class="feather feather-clock mt-1" style="color: #fff"></i>
                                        </div>
                                    </div><!-- input-group-prepend -->
                                    <input id="tpBasic" type="text" placeholder=""
                                        class="form-control input-small fw-bolder bg-light"style="width: 150px !important;border: 1px solid #1877f2;border-left: none;border-radius: 0 7px 7px 0;height: 38px;">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card data justify-content-between border-hover">
                    <div class="d-flex justify-content-between align-items-center px-3 pt-4">
                        <h5>Attendance Overview</h5>
                        <i class="feather feather-users text-primary h3 me-3"></i>
                    </div>
                    <div class="row text-center mb-3 ps-4">
                        <div class="col-xl-4 col-md-4 col-lg-4" style="cursor:pointer;" onclick="window.location='{{ route('employee.index') }}'">
                            <div>
                                <h1 class="mb-1 mt-1 text-primary font-weight-bold" id="allEmployeeCount">
                                    {{ $allEmployeeCount }}
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>Active Employee</span></h6>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 col-lg-4" style="cursor:pointer;" onclick="window.location='{{ route('attendance.daily-attendance') }}'">
                            <div>
                                <h1 class="mb-1 mt-1 text-success font-weight-bold" id="full_day_present_count">
                                    {{ $full_day_present }}
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>Present</span></h6>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 col-lg-4" style="cursor:pointer;" onclick="window.location='{{ route('attendance.daily-attendance') }}'">
                            <div>
                                <h1 class="mb-1 mt-1 text-danger font-weight-bold" id="absent_count">{{ $absent }}
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>Absent</span></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card data justify-content-between border-hover">
                    <div class="d-flex justify-content-between align-items-center px-3 pt-4">
                        <h5>Attendance Metrics</h5>
                        <i class="feather feather-clock text-primary h3 me-3"></i>
                    </div>
                    <div class="row text-center mb-3 ps-4">
                        <div class="col-xl-4 col-md-4 col-lg-4" style="cursor:pointer;" onclick="window.location='{{ route('attendance.daily-attendance') }}'">
                            <div>
                                <h1 class="mb-1 mt-1mb-0 mt-1 text-orange font-weight-bold" id="late_coming_count">
                                    {{ $late_coming }}
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>Late Coming</span></h6>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 col-lg-4" style="cursor:pointer;" onclick="window.location='{{ route('attendance.daily-attendance') }}'">
                            <div>
                                <h1 class="mb-1 mt-1 text-secondary font-weight-bold" id="half_day_count">
                                    {{ $half_day }}
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>Half Day</span></h6>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 col-lg-4" style="cursor:pointer;" onclick="window.location='{{ route('attendance.daily-attendance') }}'">
                            <div>
                                <h1 class="mb-1 mt-1 text-pink font-weight-bold" id="leave_count">{{ $leave_count }}
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>Leave</span></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card data justify-content-between border-hover">
                    <div class="d-flex justify-content-between align-items-center px-3 pt-4">
                        <h5>Recruitement Overview</h5>
                        <i class="feather feather-briefcase text-primary h3 me-3"></i>
                    </div>
                    <div class="row text-center mb-3 ps-4">
                        <div class="col-xl-4 col-md-4 col-lg-4">
                            <div>
                                <h1 class="mb-1 mt-1 text-success font-weight-bold">0
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>Applicants</span></h6>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 col-lg-4">
                            <div>
                                <h1 class="mb-1 mt-1 text-primary font-weight-bold">0
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>On Process</span></h6>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 col-lg-4">
                            <div>
                                <h1 class="mb-1 mt-1 text-purple font-weight-bold">0
                                </h1>
                            </div>
                            <div>
                                <h6 class="mb-2"><span>Vacancies</span></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card data chart-donut1 border-hover">
                    <div class="card-header  border-0">
                        <h4 class="card-title">Employees</h4>
                    </div>
                    <div class="chart-inner">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card data chart-donut1 border-hover">
                    <div class="card-header border-0 d-flex justify-content-between align-items-start">
                        <h4 class="card-title mb-0">Today's Attendance</h4>
                    </div>
                    <div class="chart-inner">
                        <canvas id="BA-chart-job-error" style="width: 88%;"></canvas>

                        <div class="row mb-3 mx-auto text-center">
                            <div class="col-lg-12 col-md-12 mx-auto d-block">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <div class="font-weight-semibold"
                                            style="display: flex !important;justify-content: center;">
                                            <span class="dot-label bg-success me-2 my-auto"></span>Present :
                                            {{ $full_day_present }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 mt-3 mt-md-0">
                                        <div class="font-weight-semibold"
                                            style="display: flex !important;justify-content: center;">
                                            <span class="dot-label badge-danger me-2 my-auto"></span>Absent :
                                            {{ $absent }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 mt-3 mt-md-0">
                                        <div class="font-weight-semibold text-center"
                                            style="display: flex !important;justify-content: center;">
                                            <span class="dot-label bg-secondary me-2 my-auto"></span>Leave :
                                            {{ $leave_count }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card border-hover">
                    <div class="card-header border-bottom-0">
                        <h3 class="card-title">Recent Activity</h3>
                    </div>
                    <div class="tab-menu-heading table_tabs mt-2 p-0 ">
                        <div class="tabs-menu1">
                            <!-- Tabs -->
                            <ul class="nav panel-tabs">
                                <li class="ms-sm-4"><a href="#tab5" class="active" data-bs-toggle="tab">Leave</a></li>
                                <li><a href="#tab6" data-bs-toggle="tab">Mispunch</a></li>
                                <li><a href="#tab7" data-bs-toggle="tab">GatePass</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-body tabs-menu-body table_tabs1 p-0 border-0">
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab5">
                                <div class="card-body" style="overflow-y: auto;height:22vh;">
                                    @if ($leave->isNotEmpty())
                                        <ul class="timeline">
                                            @if ($leave && !$leave->isEmpty())
                                                @foreach ($leave as $leaverequest)
                                                    <li class="danger">
                                                        <a target="_blank" href="{{ route('requests.leave') }}"
                                                            class="d-block text-decoration-none">
                                                            <span class="font-weight-semibold fs-15 mb-2 ms-3">Leave
                                                                Approval
                                                                Request</span>
                                                            <p class="mb-0 pb-0 text-muted fs-11 pt-1 ms-3">
                                                                From : {{ $leaverequest->fh_employee?->emp_full_name }}
                                                                <br>
                                                                Reason : {{ $leaverequest->lvr_reason }}
                                                            </p>
                                                            <span class="text-muted ms-3 fs-11">
                                                                Duration :
                                                                {{ $leaverequest->lvr_start_date->format('d-M-Y') }} To
                                                                {{ $leaverequest->lvr_end_date->format('d-M-Y') }}
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    @else
                                        <div class="text-center py-3">
                                            <img src="assets/no-data.gif" alt="No Data" class="img-fluid"
                                                style="width:25%;height:25%;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane" id="tab6">
                                <div class="card-body" style="overflow-y: auto;height: 22vh;">
                                    @if ($mispunch_request->isNotEmpty())
                                        <ul class="timeline">
                                            @if ($mispunch_request && !$mispunch_request->isEmpty())
                                                @foreach ($mispunch_request as $mispunch)
                                                    <li class="primary">
                                                        <a target="_blank" href="{{ url('admin/requests/mis-punch') }}"
                                                            class="d-block text-decoration-none">
                                                            <span class="font-weight-semibold fs-15 mb-2 ms-3">Mispunch
                                                                Request</span>
                                                            <p class="mb-0 pb-0 text-muted fs-11 pt-1 ms-3">
                                                                From : {{ $mispunch->employee?->emp_full_name }}<br>
                                                                Reason : {{ $mispunch->fh_mispunch_reason->m_name ?? '' }}
                                                            </p>
                                                            <span class="text-muted ms-3 fs-11">Duration :
                                                                {{ $mispunch->ae_date->format('d-M-Y') }}</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    @else
                                        <div class="text-center py-3">
                                            <img src="assets/no-data.gif" alt="No Data" class="img-fluid"
                                                style="width:25%;height:25%;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane " id="tab7">
                                <div class="card-body" style="overflow-y: auto;height:22vh;">
                                    @if ($gatepass_request->isNotEmpty())
                                        <ul class="timeline">
                                            @if ($gatepass_request && !$gatepass_request->isEmpty())
                                                @foreach ($gatepass_request as $gatepass)
                                                    <li class="pink">
                                                        <a target="_blank" href="{{ route('requests.gate-pass') }}"
                                                            class="d-block text-decoration-none">
                                                            <span class="font-weight-semibold fs-15 ms-3">Gate Pass
                                                                Request</span>
                                                            <p class="mb-0 pb-0 text-muted pt-1 fs-11 ms-3">
                                                                From : {{ $gatepass->fh_employee?->emp_full_name }}<br>
                                                                Reason : {{ $gatepass->gtp_reason }}
                                                            </p>
                                                            <span class="text-muted ms-3 fs-11">Duration :
                                                                {{ $gatepass->gtp_date->format('d-M-Y') }}</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    @else
                                        <div class="text-center py-3">
                                            <img src="assets/no-data.gif" alt="No Data" class="img-fluid"
                                                style="width:25%;height:25%;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card data justify-content-between border-hover">
                    <!-- Card Header -->
                    <div class="d-flex justify-content-between align-items-center px-3 pt-4">
                        <h5>Exit & Clearance Overview</h5>
                        <i class="feather feather-log-out text-primary h3 me-3"></i>
                    </div>
                    <!-- Card Content -->
                    <div class="row text-center mb-3 ps-4">
                        <!-- Total Exits -->
                        <div class="col-md-3">
                            <h1 class="mb-1 mt-1 text-primary font-weight-bold">58</h1>
                            <h6 class="mb-2"><span>Total Exits</span></h6>
                        </div>

                        <!-- Pending Approvals -->
                        <div class="col-md-3">
                            <h1 class="mb-1 mt-1 text-warning font-weight-bold">57</h1>
                            <h6 class="mb-2"><span>Pending</span></h6>
                        </div>

                        <!-- In Clearance -->
                        <div class="col-xl-3 col-md-3 col-lg-3">
                            <h1 class="mb-1 mt-1 text-info font-weight-bold">10</h1>
                            <h6 class="mb-2"><span>In Clearance</span></h6>
                        </div>

                        <!-- Completed -->
                        <div class="col-md-3">
                            <h1 class="mb-1 mt-1 text-success font-weight-bold">10</h1>
                            <h6 class="mb-2"><span>Completed</span></h6>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card data overflow-hidden border-hover">
                    <div class="card-header border-0">
                        <h4 class="card-title">Upcoming Holiday's</h4>
                    </div>
                    @if ($upcoming_holiday->isNotEmpty())
                        <marquee direction="up" scrollamount="4" onmouseover="stop()" onmouseout="start()">
                            <div p-4 m-0 border-hover>
                                <div id="holidayList">
                                    @foreach ($upcoming_holiday as $holiday)
                                        <li class="item list-group-item border-0">
                                            <div class="list-group-item d-flex pt-3 pb-1 border-hover"
                                                style="border-radius: 8px; ! important">
                                                <div class="me-3 me-xs-0">
                                                    <div class="calendar-icon icons">
                                                        <div class="date_time bg-success-transparent">
                                                            <span
                                                                class="date">{{ $holiday->phl_start_date->format('d') }}</span>
                                                            <span
                                                                class="month">{{ $holiday->phl_start_date->format('M') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ms-1">
                                                    <div class="h5 fs-14 mb-1">{{ $holiday->phl_name }}</div>
                                                    <small
                                                        class="text-muted">{{ $holiday->fh_master_table->m_name ?? '' }}</small>
                                                </div>
                                                @php
                                                    $startDate = \Carbon\Carbon::parse($holiday->phl_start_date);
                                                    $today = \Carbon\Carbon::today();
                                                    $daysLeft = $today->diffInDays($startDate, false);
                                                @endphp

                                                <p class="float-end mb-0 fs-13 ms-auto bradius my-auto">
                                                    @if ($daysLeft === 0)
                                                        Today
                                                    @elseif ($daysLeft === 1)
                                                        Tomorrow
                                                    @elseif ($daysLeft > 50)
                                                        Coming Soon
                                                    @elseif ($daysLeft > 1)
                                                        {{ $daysLeft }} days left
                                                    @else
                                                        Started {{ abs($daysLeft) }}
                                                        day{{ abs($daysLeft) > 1 ? 's' : '' }} ago
                                                    @endif
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </div>
                            </div>
                        </marquee>
                    @else
                        <div class="text-center py-3">
                            <img src="assets/no-data.gif" alt="No Data" class="img-fluid"
                                style="width:25%;height:25%;">
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-xl-4 col-md-6 col-lg-6">
                <div class="card border-hover">
                    <div class="card-header border-0 pt-2">
                        <h4 class="card-title pb-2">Upcoming Birthdays</h4>
                    </div>
                    <!-- <div style="max-height: 400px; overflow-y: auto;" class="data"> -->
                    <div class="data">
                        @if ($upcoming_birthday->isNotEmpty())
                            <div class="birthday-marquee-container">
                                <ul class="list-group birthday-marquee">
                                    @foreach ($upcoming_birthday as $birth)
                                        @php
                                            $dob = \Carbon\Carbon::parse($birth->emp_dob);
                                            $today = \Carbon\Carbon::today();

                                            $nextBirthday = $dob->copy()->year($today->year);
                                            if ($nextBirthday->isBefore($today)) {
                                                $nextBirthday->addYear();
                                            }

                                            $daysLeft = $today->diffInDays($nextBirthday);
                                        @endphp
                                        <li class="item list-group-item border-0">
                                            <div class="card p-4 m-0 border-hover">
                                                <div class="d-flex comming_events calendar-icon icons">
                                                    <span class="date_time bg-success-transparent bradius me-3">
                                                        <span class="date fs-18">{{ $dob->format('d') }}</span>
                                                        <span class="month fs-10">{{ $dob->format('M') }}</span>
                                                    </span>
                                                    <div class="me-5 mt-0 mt-sm-1 d-block">
                                                        <h6 class="mb-1">{{ $birth->emp_full_name }}</h6>
                                                        <small>Birthday</small>
                                                    </div>
                                                    <!-- <p class="float-end mb-0 fs-13 ms-auto bradius my-auto">{{ $daysLeft === 0 ? 'Today' : $daysLeft . ' Days To Go' }}</p> -->

                                                    <p class="float-end mb-0 fs-13 ms-auto bradius my-auto">
                                                        @if ($daysLeft == 0)
                                                            Today
                                                        @elseif($daysLeft > 50)
                                                            Coming Soon
                                                        @else
                                                            {{ $daysLeft }} Days To Go
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <img src="assets/no-data.gif" alt="No Data" class="img-fluid"
                                    style="width:25%;height:25%;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

 @livewire('subscription.subscription-expiry-modal')
    </div>

    {{-- ***************** for branch wise employee bar chart js ******************* --}}

    <script>
        $(document).ready(function() {
            $('#filter-date').on('change', function() {
                var selectedDate = $(this).val();

                $.ajax({
                    url: "{{ url('/attendance-dashboard') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        date: selectedDate
                    },
                    success: function(response) {
                        // Update Counters
                        $('#sine').text(response.sine);
                        $('#allEmployeeCount').text(response.allEmployeeCount);
                        $('#attendance').text(response.attendance);
                        $('#full_day_present_count').text(response.full_day_present);
                        $('#absent_count').text(response.absent);
                        $('#late_coming_count').text(response.late_coming);
                        $('#half_day_count').text(response.half_day);
                        $('#leave_count').text(response.leave_count);
                        // Update Payroll Summary
                        $('#processed_count').text(response.processedCount);
                        $('#net_pay_total').text(response.sine + ' ' + Number(response
                            .netPayTotal).toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));


                        // Update Today's Attendance Doughnut Chart (Present, Absent, Leave)
                        if (window.BAChartJobErr) {
                            var fullDayPresent = response.full_day_present;
                            var absent = response.absent;
                            var leave = response.leave_count;
                            var total = fullDayPresent + absent + leave;

                            if (total === 0) {
                                fullDayPresent = 1;
                                absent = 1;
                                leave = 1;
                                total = 3;
                            }

                            window.BAChartJobErr.data.labels = ['Present', 'Absent', 'Leave'];
                            window.BAChartJobErr.data.datasets[0].data = [fullDayPresent,
                                absent, leave
                            ];
                            window.BAChartJobErr.options.plugins.doughnutlabel.labels = [{
                                text: 'Total: ' + total
                            }];
                            window.BAChartJobErr.update();
                        }

                        // Update Holidays & Birthdays
                        $('#upcoming_holidays_container').html(response.upcoming_holiday);
                        $('#upcoming_birthdays_container').html(response.upcoming_birthday);
                    }
                });
            });
        });
    </script>

    <script>
        window.addEventListener('load', function() {
            var fullDayPresent = {{ $full_day_present }};
            var absent = {{ $absent }};
            var leave = {{ $leave_count }};
            var total = fullDayPresent + absent + leave;

            if (total === 0) {
                fullDayPresent = 1;
                absent = 1;
                leave = 1;
                total = 3;
            }

            var ctx = document.getElementById('BA-chart-job-error').getContext('2d');
            window.BAChartJobErr = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Leave'],
                    datasets: [{
                        data: [fullDayPresent, absent, leave],
                        backgroundColor: ["#0dcd94", "#f7284a", "#f1c40f"],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.raw || 0;
                                    return `${label}: ${value}`;
                                }
                            }
                        },
                        doughnutlabel: {
                            labels: [{
                                text: 'Total: ' + total,
                                font: {
                                    size: '16'
                                }
                            }]
                        },
                        labels: {
                            render: 'label',
                            fontColor: '#000',
                            position: 'outside'
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('barChart').getContext('2d');

            // Dynamic Laravel data
            const labels = @json($labels);
            const totalData = @json($total);
            const activeData = @json($active);
            const inactiveData = @json($inactive);

            window.barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Total',
                            data: totalData,
                            backgroundColor: 'rgba(0, 123, 255, 0.85)',
                            borderColor: '#007bff',
                            borderWidth: 1,
                            borderRadius: 8,
                            barThickness: 10, // Reduced width
                            maxBarThickness: 15, // Max width when screen is large
                            hoverBackgroundColor: 'rgba(0, 123, 255, 1)'
                        },
                        {
                            label: 'Active',
                            data: activeData,
                            backgroundColor: 'rgba(40, 167, 69, 0.85)',
                            borderColor: '#28a745',
                            borderWidth: 1,
                            borderRadius: 8,
                            barThickness: 10,
                            maxBarThickness: 15,
                            hoverBackgroundColor: 'rgba(40, 167, 69, 1)'
                        },
                        {
                            label: 'Inactive',
                            data: inactiveData,
                            backgroundColor: 'rgba(220, 53, 69, 0.85)',
                            borderColor: '#dc3545',
                            borderWidth: 1,
                            borderRadius: 8,
                            barThickness: 10,
                            maxBarThickness: 15,
                            hoverBackgroundColor: 'rgba(220, 53, 69, 1)'
                        }
                    ]
                },
                options: {
                    // responsive: true,
                    // maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 15,
                                font: {
                                    size: 13,
                                    family: 'Poppins, sans-serif'
                                },
                                color: '#333'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Employee Count by Headquarter',
                            font: {
                                size: 18,
                                weight: 'bold',
                                family: 'Poppins, sans-serif'
                            },
                            color: '#212529',
                            padding: {
                                bottom: 13
                            }
                        },
                        tooltip: {
                            backgroundColor: '#343a40',
                            titleColor: '#fff',
                            bodyColor: '#f8f9fa',
                            borderColor: '#6c757d',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(200, 200, 200, 0.3)'
                            },
                            ticks: {
                                color: '#495057',
                                font: {
                                    size: 13
                                }
                            },
                            title: {
                                display: true,
                                text: 'Number of Employees',
                                color: '#212529',
                                font: {
                                    weight: 'bold',
                                    size: 13
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#495057',
                                font: {
                                    size: 100
                                }
                            },
                        }
                    },
                    animation: {
                        duration: 1200,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        });
    </script>

    <script>
        function updateTime() {
            const inputElement = document.getElementById('tpBasic');
            const now = new Date();

            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12;
            const timeString = `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;

            inputElement.placeholder = timeString;
        }

        setInterval(updateTime, 1000);
        updateTime();
    </script>
@endsection
