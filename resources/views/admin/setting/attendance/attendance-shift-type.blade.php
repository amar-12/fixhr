@extends('admin.layout.master')
@section('title', 'Shift Policy')

@section('css')
    <style>
        .display-none {
            display: none !important;
        }

        .validation-icon {
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .card.modal-custom {
            border: 1px solid #b7d0ff !important;
            /* background-color: #edf6ffd1 !important; */
        }

        .card-header.modal-custom {
            border-bottom: 1px solid #b7d0ff !important;
        }

        .dark-mode .card-header.modal-custom {
            border-bottom: 1px solid #444e60 !important;
        }

        .dark-mode .card {
            border: 1px solid #444e60 !important;
            background-color: #25274a !important;
            box-shadow: 0 0.15rem 2rem 0 #1c1c25;
        }
    </style>
@endsection

@section('content')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- Breadcrumb Start --}}
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between p-4">
            <div>
                <h4 class="text-primary">Shift Policy Settings</h4>
                <ol class="breadcrumb1 breadcrumb1-bg-none m-0 p-0 fs-14">
                    <li class="breadcrumb-item1"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item1"><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a>
                    </li>
                    <li class="breadcrumb-item1 active"><span><b>Shift Policy</b></span></li>
                </ol>
            </div>
            <div>
                <button type="button" class="btn btn-outline-primary" id="addShiftTypeBtn">Create Policy</button>
            </div>
        </div>
    </div>
    {{-- Breadcrumb End --}}

    {{-- Layout Body --}}
    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-body pt-5">
                    @csrf
                    {{-- Table Filters and Export --}}
                    <div class="row pe-0">

                        <div class="col-7 row">
                            {{-- Show Entries Select --}}
                            <div class="col-auto">
                                <div class="form-group row align-items-center">
                                    <label class="col-form-label col-auto pe-0">Show entries</label>
                                    <div class="col-auto">
                                        <select class="form-select" data-length>
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Search Input --}}
                            <div class="col">
                                <div class="input-group">
                                    {{-- <label class="form-label" for="searchFilter">Search</label> --}}
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <span><i class="fa fa-search"></i></span>
                                        </div>
                                    </div>
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                        </div>

                        {{-- Export Button --}}
                        <div class="col-5 d-flex justify-content-end align-items-end pe-0">
                            <div class="mb-4">
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                        id="defaultDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true"
                                        aria-expanded="false">
                                        <i class="fa fa-download me-2"></i> Export As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="csv">CSV</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="excel">Excel</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="pdf">PDF</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="copy">Copy</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="print">Print</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div>
                        <table class="table display  table-hover table-vcenter text-wrap border-bottom"
                            id="attendance-shift-type-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="width: max-content !important">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>

                    {{-- Pagination and Show entries --}}
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

    {{-- SHIFT SETTING MODAL START --}}
    <div class="modal fade" id="shiftTypeModal" tabindex="-1" aria-labelledby="shiftTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="shiftTypeModalLabel">Add Shift Policy
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body">

                    <form id="shiftTypePolicyForm">
                        @csrf
                        <input type="hidden" id="pst_id" name="pst_id" value="">

                        {{-- Basic Info --}}
                        <div class="card modal-custom">

                            <div class="card-header modal-custom justify-content-between p-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle me-2 fs-6"></i>
                                    <h5 class="mb-0">Basic Information</h5>
                                </div>

                                <div>
                                    <label class="custom-switch auto-shift-switch">
                                        <input type="checkbox" name="pst_auto_assign_shift" id="pst_auto_assign_shift"
                                            class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description me-2 fw-bold">
                                            Auto Assign Shift
                                        </span>
                                    </label>
                                </div>

                            </div>

                            <div class="card-body py-4">

                                <div class="row g-4">

                                    {{-- Attendance Policy --}}
                                    <div class="col-md-3">
                                        <label for="pst_ap_id" class="form-label">Attendance Policy Type <span
                                                class="text-danger">*</span></label>
                                        <select id="pst_ap_id" name="pst_ap_id" class="form-select select2"
                                            data-placeholder="Select">
                                            @foreach ($attendancePolicyType as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Shift Type --}}
                                    <div class="col-md-3">
                                        <label for="pst_type_id" class="form-label">Shift Type <span
                                                class="text-danger">*</span></label>
                                        <select id="pst_type_id" name="pst_type_id" class="form-select select2"
                                            data-placeholder="Select" onchange="changeState(this.value)">
                                            @foreach ($shiftType as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Shift Name --}}
                                    <div class="col-md-3">
                                        <label for="pst_name" class="form-label">Shift Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="pst_name" name="pst_name" class="form-control"
                                            placeholder="Enter Shift Name">
                                    </div>

                                    {{-- Shift Code --}}
                                    <div class="col-md-3">
                                        <label for="pst_code" class="form-label">Shift Code <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="pst_code" name="pst_code" class="form-control"
                                            placeholder="Enter Shift Code">
                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Shift Timings --}}
                        <div class="card modal-custom">

                            <div class="card-header modal-custom p-4">
                                <i class="bi bi-clock me-2 fs-6"></i>
                                <h5 class="mb-0">Shift Timings (Time should be 24 hrs format)</h5>
                            </div>

                            <div class="card-body py-4">

                                <div class="row g-4">

                                    {{-- Start Time --}}
                                    <div class="col-md-3 start-time-div">
                                        <label for="pst_start_time" class="form-label">Start Time <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="pst_start_time" name="pst_start_time"
                                            class="form-control time_format_24hrs" placeholder="HH:MM"
                                            title="Set time in 24-hour format (HH:MM)" onchange="validateOfficeTimes()">
                                    </div>

                                    {{-- End Time --}}
                                    <div class="col-md-3 end-time-div">
                                        <label for="pst_end_time" class="form-label">End Time <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="pst_end_time" name="pst_end_time"
                                            class="form-control time_format_24hrs" placeholder="HH:MM"
                                            title="Set time in 24-hour format (HH:MM)" onchange="validateOfficeTimes()">
                                    </div>

                                    {{-- Grace Time --}}
                                    <div class="col-md-3 grace-time-div">
                                        <label for="pst_name" class="form-label">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" id="pst_allow_grace_time"
                                                    name="pst_allow_grace_time" value="0">
                                                <label class="form-label form-check-label"
                                                    for="pst_allow_grace_time">Grace Time</label>
                                            </div>
                                        </label>
                                        <input type="number" class="form-control" id="pst_grace_time"
                                            name="pst_grace_time" min="0" max="1000" placeholder="Minutes">
                                    </div>

                                    {{-- Minimum Working Hours --}}
                                    <div class="col-md-3 min-work-hour-div">
                                        <label for="pst_min_work_hour" class="form-label">
                                            Minimum Working Till <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="pst_min_work_hour" name="pst_min_work_hour"
                                            class="form-control time_format_24hrs" placeholder="HH:MM"
                                            title="Set time in 24-hour format (HH:MM)">
                                        <div class="text-primary" id="tot_min_work_hrs"></div>
                                    </div>

                                    {{-- Open Shift Duration --}}
                                    <div class="col-md-3 shift-duration-div">
                                        <label for="pst_shift_duration" class="form-label">
                                            Shift Duration <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" id="pst_shift_duration" name="pst_shift_duration"
                                                class="form-control" placeholder="Duration" min="0"
                                                max="1000">
                                            <span class="input-group-text">Mins</span>
                                        </div>
                                    </div>

                                    {{-- Open Break Duration --}}
                                    <div class="col-md-3 open-break-duration-div">
                                        <label for="pst_break_duration_minutes" class="form-label">
                                            Break Duration <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" id="pst_break_duration_minutes"
                                                name="pst_break_duration_minutes" class="form-control"
                                                placeholder="Duration" min="0" max="1000">
                                            <span class="input-group-text">Mins</span>
                                        </div>
                                    </div>

                                    {{-- End By --}}
                                    <div class="col-md-3 end-by-div">
                                        <label for="pst_name" class="form-label">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" id="pst_end_next_day"
                                                    name="pst_end_next_day" value="0">
                                                <label class="form-label form-check-label" for="pst_end_next_day">Next Day End Time</label>
                                            </div>
                                        </label>
                                        <input type="text" id="pst_end_by" name="pst_end_by"
                                            class="form-control time_format_24hrs" placeholder="HH:MM"
                                            title="Set time in 24-hour format (HH:MM)">
                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Half Day Setting --}}
                        <div class="card modal-custom halfDay-config-div">

                            <div class="card-header modal-custom p-4">
                                <i class="bi bi-circle-half me-2 fs-6"></i>
                                <h5 class="mb-0">Half Day Setting</h5>
                            </div>

                            <div class="card-body py-4">

                                <div class="row g-4">

                                    {{-- First Half Begins At --}}
                                    <div class="col-md-6">
                                        <label for="pst_hd_office_report_after_time" class="form-label">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" id="pst_hd_office_report_after"
                                                    name="pst_hd_office_report_after" value="0">
                                                <label class="form-label form-check-label"
                                                    for="pst_hd_office_report_after">Half day when report to office after</label>
                                            </div>
                                        </label>
                                        <input type="text" class="form-control time_format_24hrs" id="pst_hd_office_report_after_time"
                                            name="pst_hd_office_report_after_time" placeholder="HH:MM" onchange="validateOfficeTimes()">
                                    </div>

                                    {{-- Second Half Ends At --}}
                                    <div class="col-md-6">
                                        <label for="pst_hd_office_report_before_time" class="form-label">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" id="pst_hd_office_report_before"
                                                    name="pst_hd_office_report_before" value="0">
                                                <label class="form-label form-check-label"
                                                    for="pst_hd_office_report_before">Half day when leave office before</label>
                                            </div>
                                        </label>
                                        <input type="text" class="form-control time_format_24hrs" id="pst_hd_office_report_before_time"
                                            name="pst_hd_office_report_before_time" placeholder="HH:MM" onchange="validateOfficeTimes()">
                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Break Configuration --}}
                        <div class="card modal-custom break-config-div">

                            <div class="card-header modal-custom p-4">
                                <i class="bi bi-cup-hot me-2 fs-6"></i>
                                <h5 class="mb-0">Break Configuration</h5>
                            </div>

                            <div class="card-body py-4">

                                <div class="row g-4">

                                    <div class="accordion" id="breakConfig">

                                        {{-- Break 1 --}}
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <div class="accordion-button collapsed" data-bs-toggle="collapse"
                                                    data-bs-target="#breakOne" aria-controls="breakOne">

                                                    <label class="custom-switch">
                                                        <input type="checkbox" name="pst_allow_break1"
                                                            id="pst_allow_break1" class="custom-switch-input">
                                                        <span class="custom-switch-indicator"></span>
                                                        <span class="custom-switch-description me-2 fw-bold">
                                                            Break 1
                                                        </span>
                                                    </label>

                                                </div>
                                            </h2>

                                            <div id="breakOne" class="accordion-collapse collapse"
                                                data-bs-parent="#accordionExample" data-toggle-id="pst_allow_break1">
                                                <div class="accordion-body">

                                                    <div class="row g-4">

                                                        {{-- Break 1 Begin Time --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_break_begin_time1" class="form-label">
                                                                Begin Time</label>
                                                            <input type="text" id="pst_break_begin_time1"
                                                                name="pst_break_begin_time1"
                                                                class="form-control time_format_24hrs" placeholder="HH:MM"
                                                                title="Set time in 24-hour format (HH:MM)">
                                                        </div>

                                                        {{-- Break 1 End Time --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_break_end_time1" class="form-label">End
                                                                Time</label>
                                                            <input type="text" id="pst_break_end_time1"
                                                                name="pst_break_end_time1"
                                                                class="form-control time_format_24hrs" placeholder="HH:MM"
                                                                title="Set time in 24-hour format (HH:MM)">
                                                        </div>

                                                        {{-- Break 1 Duration --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_break1_duration" class="form-label">Duration
                                                            </label>
                                                            <input type="number" id="pst_break1_duration" readonly
                                                                name="pst_break1_duration" class="form-control"
                                                                placeholder="Minutes" min="0" max="1000">
                                                        </div>

                                                        {{-- Break 1 Paid/Unpaid --}}
                                                        <div class="col-md-3">

                                                            <label for="pst_is_break_paid" class="form-label">Paid
                                                                Break</label>
                                                            <label class="custom-switch mt-2">
                                                                <input type="checkbox" name="pst_is_break_paid"
                                                                    id="pst_is_break_paid" class="custom-switch-input">
                                                                <span class="custom-switch-indicator"></span>
                                                            </label>

                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        {{-- Break 2 --}}
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <div class="accordion-button collapsed" data-bs-toggle="collapse"
                                                    data-bs-target="#breakTwo" aria-controls="breakTwo">
                                                    <label class="custom-switch">
                                                        <input type="checkbox" name="pst_allow_break2"
                                                            id="pst_allow_break2" class="custom-switch-input">
                                                        <span class="custom-switch-indicator"></span>
                                                        <span class="custom-switch-description me-2 fw-bold">
                                                            Break 2
                                                        </span>
                                                    </label>

                                                </div>
                                            </h2>

                                            <div id="breakTwo" class="accordion-collapse collapse"
                                                data-bs-parent="#accordionExample" data-toggle-id="pst_allow_break2">
                                                <div class="accordion-body">

                                                    <div class="row g-4">

                                                        {{-- Break 2 Begin Time --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_break_begin_time2" class="form-label">Begin
                                                                Time</label>
                                                            <input type="text" id="pst_break_begin_time2"
                                                                name="pst_break_begin_time2"
                                                                class="form-control time_format_24hrs" placeholder="HH:MM"
                                                                title="Set time in 24-hour format (HH:MM)">
                                                        </div>

                                                        {{-- Break 2 End Time --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_break_end_time2" class="form-label">End
                                                                Time</label>
                                                            <input type="text" id="pst_break_end_time2"
                                                                name="pst_break_end_time2"
                                                                class="form-control time_format_24hrs" placeholder="HH:MM"
                                                                title="Set time in 24-hour format (HH:MM)">
                                                        </div>

                                                        {{-- Break 2 Duration --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_break2_duration" class="form-label">Duration
                                                            </label>
                                                            <input type="number" id="pst_break2_duration"
                                                                name="pst_break2_duration" class="form-control"
                                                                placeholder="Minutes" min="0" max="1000"
                                                                readonly>
                                                        </div>

                                                        {{-- Break 2 Paid/Unpaid --}}
                                                        <div class="col-md-3">

                                                            <label for="pst_is_break_paid" class="form-label">Paid
                                                                Break</label>
                                                            <label class="custom-switch mt-2">
                                                                <input type="checkbox" class="custom-switch-input" checked
                                                                    disabled>
                                                                <span class="custom-switch-indicator"></span>
                                                            </label>

                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Checkin/Checkout Begin/End Configuration --}}
                        <div class="card modal-custom in-out-config-div">

                            <div class="card-header modal-custom p-4">
                                <i class="bi bi-door-open me-2 fs-6"></i>
                                <h5 class="mb-0">Checkin/Checkout Begin/End Configuration</h5>
                            </div>

                            <div class="card-body py-4">

                                <div class="row g-4">

                                    {{-- Checkin Begin Before --}}
                                    <div class="col-md-6">
                                        <label for="pst_mins_punch_begin_before" class="form-label">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox"
                                                    id="pst_allow_punch_begin_before" name="pst_allow_punch_begin_before"
                                                    value="0">
                                                <label class="form-label form-check-label"
                                                    for="pst_allow_punch_begin_before">Checkin Begin Before</label>
                                            </div>
                                        </label>
                                        <input type="number" class="form-control" id="pst_mins_punch_begin_before"
                                            name="pst_mins_punch_begin_before" min="0" max="1000"
                                            placeholder="Minutes">
                                    </div>

                                    {{-- Checkout End After --}}
                                    <div class="col-md-6">
                                        <label for="pst_allow_punch_end_after" class="form-label">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox"
                                                    id="pst_allow_punch_end_after" name="pst_allow_punch_end_after"
                                                    value="0">
                                                <label class="form-label form-check-label"
                                                    for="pst_allow_punch_end_after">Checkout End After</label>
                                            </div>
                                        </label>
                                        <input type="number" id="pst_mins_punch_end_after"
                                            name="pst_mins_punch_end_after" class="form-control" placeholder="Minutes"
                                            min="0" max="1000">
                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Partial Day Configuration --}}
                        <div class="card modal-custom partial-day-config-div">

                            <div class="card-header modal-custom p-4">
                                <i class="bi bi-calendar-day me-2 fs-6"></i>
                                <h5 class="mb-0">Partial Day Configuration</h5>
                            </div>

                            <div class="card-body py-4">

                                <div class="row g-4">

                                    <div class="accordion" id="breakConfig">

                                        {{-- Partial Day 1 --}}
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <div class="accordion-button collapsed" data-bs-toggle="collapse"
                                                    data-bs-target="#partialDayOne" aria-controls="partialDayOne">

                                                    <label class="custom-switch">
                                                        <input type="checkbox" name="pst_allow_partial_day"
                                                            id="pst_allow_partial_day" class="custom-switch-input">
                                                        <span class="custom-switch-indicator"></span>
                                                        <span class="custom-switch-description me-2 fw-bold">
                                                            Partial Day
                                                        </span>
                                                    </label>

                                                </div>
                                            </h2>

                                            <div id="partialDayOne" class="accordion-collapse collapse"
                                                data-bs-parent="#accordionExample" data-toggle-id="pst_allow_partial_day">
                                                <div class="accordion-body">

                                                    <div class="row g-4">

                                                        {{-- Partial Day On --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_break_begin_time1" class="form-label">
                                                                Partial Day On</label>
                                                            <select class="form-select form-select-sm select2"
                                                                id="pst_partial_day_type_id"
                                                                name="pst_partial_day_type_id" style="min-width: 140px;">
                                                                <option value="">Select</option>
                                                                @foreach ($week as $key => $item)
                                                                    <option value="{{ $key }}">{{ $item }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        {{-- Begins At --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_partial_day_begin_time"
                                                                class="form-label">Begins
                                                                At</label>
                                                            <input type="text"
                                                                class="form-control time_format_24hrs"id="pst_partial_day_begin_time"
                                                                name="pst_partial_day_begin_time" placeholder="HH:MM"
                                                                title="Set time in 24-hour format (HH:MM)">
                                                        </div>

                                                        {{-- Ends At --}}
                                                        <div class="col-md-3">
                                                            <label for="pst_partial_day_end_time" class="form-label">Ends
                                                                At</label>
                                                            <input type="text" class="form-control time_format_24hrs"
                                                                id="pst_partial_day_end_time"
                                                                name="pst_partial_day_end_time" placeholder="HH:MM"
                                                                title="Set time in 24-hour format (HH:MM)">
                                                        </div>

                                                        {{-- Occurrence --}}
                                                        <div class="col-md-3">

                                                            <label for="pst_is_break_paid"
                                                                class="form-label">Occurrence</label>

                                                            <div class="d-flex align-items-center flex-wrap justify-content-around"
                                                                id="partial_week_1">
                                                                @foreach ($recurrenceDay as $key => $item1)
                                                                    <div class="form-check d-flex align-items-center mb-0">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="week_off[]" value="{{ $key }}">
                                                                        <label
                                                                            class="form-label form-check-label small fw-bold mb-0 mx-1">
                                                                            {{ $loop->iteration }}{{ $loop->iteration == 1 ? 'st' : ($loop->iteration == 2 ? 'nd' : ($loop->iteration == 3 ? 'rd' : 'th')) }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-primary" id="saveBtn">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    {{-- SHIFT SETTING MODAL END --}}
@endsection

@section('script')
    @php
        $pageData = [
            'csrf' => csrf_token(),
            'routes' => [
                'indexUrl' => route('attendance-shift-type.index'),
                'deleteUrl' => route('attendance-shift-type.destroy', ':id'),
                'storeUrl' => url('admin/settings/attendance/attendance-shift-type'),
            ],
            'attendancePolicies' => $attendancePolicyType,
        ];
    @endphp

    <script>
        window.pageData = @json($pageData);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="text/javascript" src="{{ asset('assets/js/shift-policy-setting.js?v=1.3') }}"></script>

    <script type="text/javascript">
        function showError(id) {
            const $input = $('#' + id);
            $input.addClass('is-invalid');
        }

        function clearError(id) {
            const $input = $('#' + id);
            $input.removeClass('is-invalid');
            $input.next('.validation-icon').remove(); // if any custom icon
        }

        // Clear errors as the user interacts with input fields
        $('input, select').on('input change', function() {
            const fieldId = $(this).attr('id');
            $('#' + fieldId + '_error').text(''); // Clear the error message
        });
    </script>
@endsection
