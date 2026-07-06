@extends('admin.layout.master')
@section('title', 'Shift Policy')
@section('css')
@endsection
@section('content')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a></li>
                    <li class="active"><span><b>Shift Policy</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addShiftTypeBtn">Add Shift
                                    Policy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bradcrumbs End --}}

    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Shift Policy List</h4>
                </div>
                <div class="card-body">
                    @csrf
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

                            <div class="col-sm-7">
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

                                .validation-icon {
                                    font-size: 0.9rem;
                                    vertical-align: middle;
                                }
                            </style>

                        </div>
                    <div class="table-responsive">
                        <table class="table display  table-hover table-vcenter text-wrap border-bottom"
                            id="attendance-shift-type-table-dynamic">
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

    <!-- MODAL -->
    <div class="modal fade" id="shiftTypeModal" tabindex="-1" role="dialog" aria-labelledby="shiftTypeModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shiftTypeModalTitle">Add Attendance Policies</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="shiftTypePolicyForm"> @csrf
                    <div class="modal-body">
                        <input type="hidden" name="pst_id" id="pst_id">
                        <!-- Row 1: Policy Type, Shift Type, Shift Name, Shift Code -->
                        <div class="row mt-2 align-items-center">
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_ap_id" class="me-2 mb-0 w-50">Policy Type</label>
                                <select id="pst_ap_id" name="pst_ap_id" class="form-select w-100">
                                    <option value="">Select Policy Type</option>
                                    @foreach($attendancePolicyType as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_type_id" class="me-2 mb-0 w-50">Shift Type</label>
                                <select id="pst_type_id" name="pst_type_id" class="form-select w-100">
                                    <option value="">Select Shift Type</option>
                                    @foreach($shiftType as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_name" class="me-2 mb-0 w-50">Shift Name</label>
                                <input type="text" id="pst_name" name="pst_name" class="form-control w-100" maxlength="25" placeholder="Enter Shift Name">
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_code" class="me-2 mb-0 w-50">Shift Code</label>
                                <input type="text" id="pst_code" name="pst_code" class="form-control w-100" maxlength="25" placeholder="Enter Shift Code">
                            </div>
                        </div>
                        
                        <!-- Row 2: Start Time, End Time, Grace Time -->
                        <div class="row mt-2 align-items-center">
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_start_time" class="me-2 mb-0 w-50">Start Time</label>
                                <input type="time" id="pst_start_time" step="1" name="pst_start_time" class="form-control w-100" placeholder="Start Time" title="Set time in 24-hour format (HH:MM:SS)">
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_end_time" class="me-2 mb-0 w-50">End Time</label>
                                <input type="time" id="pst_end_time" step="1" name="pst_end_time" class="form-control w-100" title="Set time in 24-hour format (HH:MM:SS)">
                            </div>
                            <div class="col-xl-6 d-flex align-items-center">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" id="pst_allow_grace_time" name="pst_allow_grace_time" value="0">
                                    <label class="form-check-label" for="pst_allow_grace_time">Grace Time</label>
                                </div>
                                <div class="me-3" style="width: 120px;">
                                    <input type="number" class="form-control" id="pst_grace_time" name="pst_grace_time" min="0" max="1000" placeholder="Minutes">
                                </div>
                                <div>
                                    <small class="text-muted">(Default value comes from Employee Category Settings)</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Row 3: Shift & Break Duration -->
                        <div class="row mt-2 align-items-center">
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_shift_duration" class="me-2 mb-0" style="width: 40%;">Shift</label>
                                <div class="input-group w-100">
                                    <input type="number" id="pst_shift_duration" name="pst_shift_duration" class="form-control" placeholder="Duration" min="0" max="1000">
                                    <span class="input-group-text">Mins</span>
                                </div>
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_break_duration_minutes" class="me-2 mb-0" style="width: 40%;">Break</label>
                                <div class="input-group w-100">
                                    <input type="text" id="pst_break_duration_minutes" name="pst_break_duration_minutes" class="form-control" placeholder="Duration" min="0" max="1000">
                                    <span class="input-group-text">Mins</span>
                                </div>
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="pst_end_next_day" name="pst_end_next_day" value="0">
                                    <label class="form-check-label mb-0" for="pst_end_next_day">End Next Day</label>
                                </div>
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_end_by" class="me-2 mb-0" style="width: 40%;">End By</label>
                                <input type="time" id="pst_end_by" name="pst_end_by" class="form-control w-100" step="1" title="Set time in 24-hour format (HH:MM:SS)">
                            </div>
                        </div>
                            
                        <hr>

                        <!-- Row 4: Break1 & Break1 Duration -->
                        <div class="row mt-2 align-items-center">
                            <div class="col-xl-2 d-flex align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="pst_allow_break1" name="pst_allow_break1" value="0">
                                    <label class="form-check-label" for="pst_allow_break1">Break 1</label>
                                </div>
                            </div>
                            <div class="col-xl-2 d-flex align-items-center">
                                <label for="pst_break_begin_time1" class="me-2 mb-0" style="width: 40%;">Begin</label>
                                <input type="time" class="form-control w-100" step="1" id="pst_break_begin_time1" name="pst_break_begin_time1" title="Set time in 24-hour format (HH:MM:SS)">
                            </div>
                            <div class="col-xl-2 d-flex align-items-center">
                                <label for="pst_break_end_time1" class="me-2 mb-0" style="width: 40%;">End</label>
                                <input type="time" class="form-control w-100" step="1" id="pst_break_end_time1" name="pst_break_end_time1" title="Set time in 24-hour format (HH:MM:SS)">
                            </div>
                            <div class="col-xl-2 d-flex align-items-center">
                                <label for="pst_break1_duration" class="me-2 mb-0" style="width: 40%;">Duration</label>
                                <div class="input-group">
                                    <input type="number" id="pst_break1_duration" name="pst_break1_duration" class="form-control w-100" placeholder="Mins" min="0" max="1000">
                                </div>
                            </div>
                            
                            <div class="col-xl-4 d-flex align-items-center">
                                <label class="me-2 mb-0" style="width: 20%;">Break is</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pst_is_break_paid" id="paid" value="1" checked>
                                        <label class="form-check-label" for="paid">Paid</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pst_is_break_paid" id="unpaid" value="0">
                                        <label class="form-check-label" for="unpaid">Unpaid</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="row mt-2 align-items-center">
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_punch_begin_before" class="me-2 mb-0" style="width: 40%;">Checkin</label>
                                <div class="input-group w-100">
                                    <input type="number" id="pst_punch_begin_before" name="pst_punch_begin_before" class="form-control" placeholder="Checkin Before" min="0" max="1000">
                                    <span class="input-group-text">Mins</span>
                                </div>
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_punch_end_after" class="me-2 mb-0" style="width: 40%;">Checkout</label>
                                <div class="input-group w-100">
                                    <input type="number" id="pst_punch_end_after" name="pst_punch_end_after" class="form-control" placeholder="Checkout After" min="0" max="1000">
                                    <span class="input-group-text">Mins</span>
                                </div>
                            </div>
                            <div class="col-xl-3 d-flex align-items-center">
                                <label for="pst_break1_duration" class="me-2 mb-0" style="width: 30%;">Duration</label>
                                <div class="input-group w-100">
                                    <input type="number" id="pst_break1_duration" name="pst_break1_duration" class="form-control" placeholder="Break Duration" min="0" max="1000">
                                    <span class="input-group-text">Mins</span>
                                </div>
                            </div>
                        </div> -->

                        <!-- Row 6: Break 2 -->
                        <div class="row mt-2 align-items-center">
                            <div class="col-xl-2 d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="pst_allow_break2" id="pst_allow_break2" value="0">
                                    <label class="form-check-label" for="pst_allow_break2">Break 2</label>
                                </div>
                            </div>
                            <div class="col-xl-2 d-flex align-items-center">
                                <label for="pst_break_begin_time2" class="me-2 mb-0" style="width: 40%;">Begin</label>
                                <input type="time" class="form-control w-100" step="1" id="pst_break_begin_time2"
                                    name="pst_break_begin_time2" title="Set time in 24-hour format (HH:MM:SS)">
                            </div>
                            <div class="col-xl-2 d-flex align-items-center">
                                <label for="pst_break_end_time2" class="me-2 mb-0" style="width: 40%;">End</label>
                                <input type="time" class="form-control w-100" step="1" id="pst_break_end_time2"
                                    name="pst_break_end_time2" title="Set time in 24-hour format (HH:MM:SS)">
                            </div>
                        </div>
                        <hr>

                        <!-- Row 7: Checkin/Checkout -->
                        <div class="row">
                            <!-- Punch Begin Before -->
                            <div class="row align-items-center mb-3">
                                <div class="col-md-2">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="pst_allow_punch_begin_before" name="pst_allow_punch_begin_before"
                                            value="0">
                                        <span class="custom-control-label">Checkin Before</span>
                                    </label>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" class="form-control" id="pst_mins_punch_begin_before"
                                        name="pst_mins_punch_begin_before" min="0" max="1000" placeholder="Minutes">
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">(Default value comes from Master Settings)</small>
                                </div>
                                <div class="col-md-2">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="pst_allow_punch_end_after" name="pst_allow_punch_end_after"
                                            value="0">
                                        <span class="custom-control-label">Checkout After</span>
                                    </label>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" class="form-control" id="pst_mins_punch_end_after"
                                        name="pst_mins_punch_end_after" min="0" max="1000" placeholder="Minutes">
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">(Default is Next Day Shift Begin Time: Punch Begin
                                        Duration)</small>
                                </div>
                            </div>

                            <!-- Row 8: Partial Day -->
                            <div class="row mt-3">
                                <div class="col-12 d-flex align-items-center flex-wrap gap-3">
                                    <label class="custom-control custom-checkbox mb-0 d-flex align-items-center">
                                        <input type="checkbox" class="custom-control-input" id="pst_allow_partial_day" name="pst_allow_partial_day" value="0">
                                        <span class="custom-control-label ms-2">Partial Day On</span>
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm select2" id="pst_partial_day_type_id" name="pst_partial_day_type_id" style="min-width: 140px;">
                                            <option value="">Select</option>
                                            @foreach ($week as $key => $item)
                                                <option value="{{ $key }}">{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="pst_partial_day_begin_time" class="me-2 mb-0">Begins At</label>
                                        <input type="time" class="form-control" style="width: 120px;" step="1" id="pst_partial_day_begin_time" name="pst_partial_day_begin_time" title="Set time in 24-hour format (HH:MM:SS)">
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="pst_partial_day_end_time" class="me-2 mb-0">End At</label>
                                        <input type="time" class="form-control" style="width: 120px;" step="1" id="pst_partial_day_end_time" name="pst_partial_day_end_time" title="Set time in 24-hour format (HH:MM:SS)">
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap gap-2 ms-3" id="partial_week_1">
                                        @foreach ($recurrenceDay as $key => $item1)
                                            <div class="form-check d-flex align-items-center mb-0 me-2">
                                                <input class="form-check-input me-1" type="checkbox" name="week_off[]" value="{{ $key }}">
                                                <label class="form-check-label small fw-bold mb-0">
                                                    {{ $loop->iteration }}{{ $loop->iteration == 1 ? 'st' : ($loop->iteration == 2 ? 'nd' : ($loop->iteration == 3 ? 'rd' : 'th') ) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Row 9: Partial Day2 -->
                            <div class="row mt-3">
                                <div class="col-12 d-flex align-items-center flex-wrap gap-3">
                                    <label class="custom-control custom-checkbox mb-0 d-flex align-items-center">
                                        <input type="checkbox" class="custom-control-input" id="pst_allow_partial_day2" name="pst_allow_partial_day2" value="0">
                                        <span class="custom-control-label ms-2">Partial Day On</span>
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm select2" id="pst_partial_day_type_id2" name="pst_partial_day_type_id2" style="min-width: 140px;">
                                            <option value="">Select</option>
                                            @foreach ($week as $key => $item)
                                                <option value="{{ $key }}">{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="pst_partial_day_begin_time2" class="me-2 mb-0">Begins At</label>
                                        <input type="time" class="form-control" style="width: 120px;" step="1" id="pst_partial_day_begin_time2" name="pst_partial_day_begin_time2" title="Set time in 24-hour format (HH:MM:SS)">
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="pst_partial_day_end_time2" class="me-2 mb-0">End At</label>
                                        <input type="time" class="form-control" style="width: 120px;" step="1" id="pst_partial_day_end_time2" name="pst_partial_day_end_time2" title="Set time in 24-hour format (HH:MM:SS)">
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap gap-2 ms-3" id="partial_week_2">
                                        @foreach ($recurrenceDay as $key => $item1)
                                            <div class="form-check d-flex align-items-center mb-0 me-2">
                                                <input class="form-check-input me-1" type="checkbox" name="week_off2[]" value="{{ $key }}">
                                                <label class="form-check-label small fw-bold mb-0">
                                                    {{ $loop->iteration }}{{ $loop->iteration == 1 ? 'st' : ($loop->iteration == 2 ? 'nd' : ($loop->iteration == 3 ? 'rd' : 'th') ) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <hr>

                            <div class="row align-items-center mb-3">
                                <div class="col-md-3 d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" id="pst_hd_office_report_after" name="pst_hd_office_report_after" value="0">
                                    <label class="form-check-label mb-0" for="pst_hd_office_report_after">
                                        half day when report to office after
                                    </label>
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <label for="pst_hd_office_report_after_time" class="me-2 mb-0" style="min-width: 100px;">Begins At</label>
                                    <input type="time" class="form-control" step="1" id="pst_hd_office_report_after_time" name="pst_hd_office_report_after_time" title="Set time in 24-hour format (HH:MM:SS)">
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <label for="pst_session1_end_by" class="me-2 mb-0" style="min-width: 120px;">Session1 End By</label>
                                    <input type="time" class="form-control" step="1" id="pst_session1_end_by" name="pst_session1_end_by" title="Set time in 24-hour format (HH:MM:SS)">
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <label for="pst_session2_grace_time" class="me-2 mb-0" style="min-width: 80px;">Session2</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" step="1" id="pst_session2_grace_time" name="pst_session2_grace_time" title="Enter grace time in minutes" min="0" max="1000">
                                        <span class="input-group-text">Mins</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center mb-3">
                                <div class="col-md-3 d-flex align-items-center">
                                    <input type="checkbox" class="form-check-input me-2" id="pst_hd_office_report_before"
                                        name="pst_hd_office_report_before" value="0">
                                    <label class="form-check-label mb-0" for="pst_hd_office_report_before">
                                        half day when leave office before
                                    </label>
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <label for="pst_hd_office_report_before_time" class="me-2 mb-0" style="min-width: 100px;">Begins At</label>
                                    <input type="time" class="form-control" step="1" id="pst_hd_office_report_before_time" name="pst_hd_office_report_before_time" title="Set time in 24-hour format (HH:MM:SS)">
                                </div>
                            </div>

                            <!-- Face Mode/Face Track -->
                            <div class="row mt-2 align-items-center">
                                <div class="col-xl-4 d-flex align-items-center">
                                    <label class="me-2 mb-0" style="width: 30%;">Face Mode</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="radio" name="pst_is_high" id="pst_is_high" value="1" checked>
                                            <label class="form-check-label" for="pst_is_high">High</label>
                                        </div>
                                        <div class="form-check mb-0 ms-3">
                                            <input class="form-check-input" type="radio" name="pst_is_high" id="pst_is_high" value="0">
                                            <label class="form-check-label" for="pst_is_high">Low</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-5 d-flex align-items-center">
                                    <label class="me-2 mb-0" style="width: 30%;">Show Face Mark</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="radio" name="pst_is_face_track_yes" id="pst_is_face_track_yes" value="1" checked>
                                            <label class="form-check-label" for="pst_is_face_track_yes">Yes</label>
                                        </div>
                                        <div class="form-check mb-0 ms-3">
                                            <input class="form-check-input" type="radio" name="pst_is_face_track_yes" id="pst_is_face_track_yes" value="0">
                                            <label class="form-check-label" for="pst_is_face_track_yes">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="text-center py-4 bg-light borderm d-none">
        <a class="btn btn-outline-primary" data-bs-target="#modaldemo3" data-bs-toggle="modal" href="#">View Live Demo</a>
    </div>
    {{-- <x-select2 id="pst_ap_id1" name="pst_ap_id1" label="Attendance Policy Type"
    :options="$attendancePolicyType" placeholder="Select Attendance Policy Type"  /> --}}
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.getElementById('pst_break_begin_time1').addEventListener('change', calculateBreakDuration);
        document.getElementById('pst_break_end_time1').addEventListener('change', calculateBreakDuration);

        function calculateBreakDuration() {
            const begin = document.getElementById('pst_break_begin_time1').value;
            const end = document.getElementById('pst_break_end_time1').value;

            if (begin && end) {
                const [bh, bm] = begin.split(':').map(Number);
                const [eh, em] = end.split(':').map(Number);

                let duration = (eh * 60 + em) - (bh * 60 + bm);
                if (duration < 0) duration += 1440; // Handle next-day scenario

                document.getElementById('pst_break1_duration').value = duration;
            }
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i:S", // 24-hour format
                time_24hr: true
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const startTimeInput = document.getElementById("pst_start_time");
            const endTimeInput = document.getElementById("pst_end_time");
            const totalWorkHourContainer = document.getElementById("total_work_hour_container");
            const totalWorkHourElement = document.getElementById("total_work_hour");

            function calculateTotalWorkHours() {
                const startTime = startTimeInput.value;
                const endTime = endTimeInput.value;

                if (startTime && endTime) {
                    // Parse time values
                    const [startHour, startMinute] = startTime.split(":").map(Number);
                    const [endHour, endMinute] = endTime.split(":").map(Number);

                    // Calculate total minutes
                    const startTotalMinutes = startHour * 60 + startMinute;
                    const endTotalMinutes = endHour * 60 + endMinute;

                    let totalMinutes = endTotalMinutes - startTotalMinutes;

                    // Handle negative values (if end time is on the next day)
                    if (totalMinutes < 0) {
                        totalMinutes += 24 * 60;
                    }

                    const hours = Math.floor(totalMinutes / 60);
                    const minutes = totalMinutes % 60;

                    // Display total work hour
                    totalWorkHourElement.textContent = `${hours} Hr ${minutes.toString().padStart(2, "0")} Min`;

                    // Show the <small> tag
                    totalWorkHourContainer.classList.remove("d-none");
                } else {
                    // Hide the <small> tag if one of the times is not selected
                    totalWorkHourContainer.classList.add("d-none");
                }
            }

            // Add event listeners for both inputs
            startTimeInput.addEventListener("input", calculateTotalWorkHours);
            endTimeInput.addEventListener("input", calculateTotalWorkHours);
        });
    </script>
    <script type="text/javascript">
        $(function() {
            // CSRF Token Setup (for all AJAX requests)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            initializeDatatable();

            // Initialize Select2
            initializeSelect2();

            // Handle Create or Update Shift Type
            $('#shiftTypePolicyForm').on('submit', handleShiftTypeSubmit);

            // Handle Delete Shift Type
            $(document).on('click', '.delete-shift-type', handleDeleteShiftType);

            // Handle Add Shift Type Button
            $(document).on('click', '#addShiftTypeBtn', handleAddShiftType);

            // Handle Edit Shift Type Button
            $(document).on('click', '.edit-shift-type', handleEditShiftType);
        });

        function showError(id) {
            const $input = $('#' + id);
            $input.addClass('is-invalid');
        }

        function clearError(id) {
            const $input = $('#' + id);
            $input.removeClass('is-invalid');
            $input.next('.validation-icon').remove(); // if any custom icon
        }

        function validateAllPolicyFields() {
            let isValid = true;

            // Reusable group validator
            function validateGroup(checkboxId, fieldIds) {
                const isChecked = $(checkboxId).is(':checked');
                fieldIds.forEach(id => {
                    const value = ($('#' + id).val() || '').trim();
                    if (isChecked && !value) {
                        showError(id);
                        isValid = false;
                    } else {
                        clearError(id);
                    }
                });
            }

            // Validate all groups
            validateGroup('#pst_allow_grace_time', ['pst_grace_time']);
            validateGroup('#pst_allow_break1', ['pst_break_begin_time1', 'pst_break_end_time1', 'pst_break1_duration']);
            validateGroup('#pst_allow_break2', ['pst_break_begin_time2', 'pst_break_end_time2']);
            validateGroup('#pst_allow_punch_begin_before', ['pst_mins_punch_begin_before']);
            validateGroup('#pst_allow_punch_end_after', ['pst_mins_punch_end_after']);
            validateGroup('#pst_allow_partial_day', ['pst_partial_day_type_id', 'pst_partial_day_begin_time', 'pst_partial_day_end_time', 'pst_partial_day_type_id']);
            validateGroup('#pst_allow_partial_day2', ['pst_partial_day_type_id2', 'pst_partial_day_begin_time2', 'pst_partial_day_end_time2', 'pst_partial_day_type_id2']);
            validateGroup('#pst_hd_office_report_after', ['pst_hd_office_report_after_time', 'pst_session1_end_by']);
            validateGroup('#pst_hd_office_report_before', ['pst_hd_office_report_before_time']);

            return isValid;
        }

        function validateShiftTypeForm(formId = '#shiftTypePolicyForm') {
            const requiredFields = ['pst_ap_id', 'pst_type_id', 'pst_name', 'pst_code'];
            let isValid = true;

            requiredFields.forEach(id => {
                const val = $('#' + id).val().trim();
                if (!val) {
                    showError(id);
                    isValid = false;
                }
            });

            // Attach real-time error-clearing only once
            if (!$(formId).data('validation-attached')) {
                $(formId).data('validation-attached', true);
                $(formId).on('input change', 'input, select', function () {
                    const id = $(this).attr('id');
                    clearError(id);
                });
            }

            return isValid;
        }

        function validateShiftTypeForm1(formId = '#shiftTypePolicyForm') {
            const requiredFields = ['pst_start_time', 'pst_end_time'];
            let isValid = true;

            requiredFields.forEach(id => {
                const val = $('#' + id).val().trim();
                if (!val) {
                    showError(id);
                    isValid = false;
                }
            });
            // Attach real-time error-clearing only once
            if (!$(formId).data('validation-attached')) {
                $(formId).data('validation-attached', true);
                $(formId).on('input', function () {
                    const id = $(this).attr('id');
                    clearError(id);
                });
            }
            return isValid;
        }

        function validateShiftTypeForm2(formId = '#shiftTypePolicyForm') {
            const requiredFields = ['pst_shift_duration', 'pst_break_duration_minutes'];
            let isValid = true;

            requiredFields.forEach(id => {
                const val = $('#' + id).val().trim();
                if (!val) {
                    showError(id);
                    isValid = false;
                }
            });
            // Attach real-time error-clearing only once
            if (!$(formId).data('validation-attached')) {
                $(formId).data('validation-attached', true);
                $(formId).on('input', function () {
                    const id = $(this).attr('id');
                    clearError(id);
                });
            }

            function validateGroup(checkboxId, fieldIds) {
                const isChecked = $(checkboxId).is(':checked');
                fieldIds.forEach(id => {
                    const value = ($('#' + id).val() || '').trim();
                    if (isChecked && !value) {
                        showError(id);
                        isValid = false;
                    } else {
                        clearError(id);
                    }
                });
            }
            validateGroup('#pst_end_next_day', ['pst_end_by']);

            return isValid;
        }

        // Initialize DataTable
        function initializeDatatable() {
            datatable({
                tableId: "attendance-shift-type-table-dynamic",
                url: "{{ route('attendance-shift-type.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]'
            });
        }

        // Initialize Select2
        function initializeSelect2() {
            $('.select2').select2();

            $('#shiftTypeModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#shiftTypeModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
        }

        // Handle Create or Update Shift Type Form Submission
        function handleShiftTypeSubmit(e) {
            e.preventDefault();
            var id = $('#pst_id').val(); // Get the ID of the shift-type if applicable
            console.log('msmsmdm');

            if (!validateShiftTypeForm()) return;
            // if (!validateAllPolicyFields()) {
            //     e.preventDefault(); // Prevent form submission if validation fails
            // }

            if ($('#pst_type_id').val() == 244) {
                if (!validateShiftTypeForm1()) return;
                if (!validateAllPolicyFields()) return;
            } else if ($('#pst_type_id').val() == 246) {
                if (!validateShiftTypeForm2()) return;
            }

            $.ajax({
                url: "{{ url('admin/settings/attendance/attendance-shift-type') }}",
                method: "POST",
                data: $(this).serialize(),
                beforeSend: function() {
                    $('#saveBtn').attr('disabled', true); // Disable the button to prevent multiple submissions
                },
                success: function(response) {
                    handleSuccess(response);
                },
                error: function(xhr) {
                    handleError(xhr);
                },
                complete: function() {
                    $('#saveBtn').attr('disabled', false); // Re-enable the button
                }
            });
        }

        // Clear errors as the user interacts with input fields
        $('input, select').on('input change', function() {
            const fieldId = $(this).attr('id');
            $('#' + fieldId + '_error').text(''); // Clear the error message
        });

        // Handle success response
        function handleSuccess(response) {
            $('#shiftTypeModal').modal('hide'); // Hide the modal on success

            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.success,
                timer: 3000,
                timerProgressBar: true,
                didClose: () => {
                    resetForm(); // Reset the form
                    location.reload(); // Reload the page
                }
            });
        }

        // Handle error response
        function handleError(xhr) {
            if (xhr.status === 422) { // Validation error
                let errors = xhr.responseJSON.errors;
                showValidationErrors(errors); // Show validation errors
            } else {
                Swal.fire({
                    title: 'Something went wrong!',
                    text: 'Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }

        // Show validation errors
        function showValidationErrors(errors) {
            $('.text-danger').text('');
            $.each(errors, function(key, value) {
                $('#' + key + '_error').text(value[0]);
                $('#' + key + '_error').show();
            });
        }

        // Reset form fields
        function resetForm() {
            // Reset the form
            $('#shiftTypePolicyForm')[0].reset();

            // Manually reset any fields that require special handling (e.g., Select2, checkboxes)
            $('#pst_id').val('');
            $('#pst_ap_id').val('').trigger('change'); // Reset Select2 field
            $('#pst_type_id').val('').trigger('change'); // Reset Select2 field
            $('#pst_name').val(''); // Reset text input
            $('#pst_start_time').val(''); // Reset time input
            $('#pst_end_time').val(''); // Reset time input
            $('#pst_break_duration_minutes').val(''); // Reset number input
            $('#pst_is_break_paid').prop('checked', false); // Reset checkbox

            // Reset additional fields
            $('#pst_location').val(''); // Reset location field
            $('#pst_description').val(''); // Reset description field

            // You can add more fields as needed. For example:
            // $('#pst_other_field').val('');  // Reset any other field

            // Trigger any necessary change events for custom elements like Select2
            $('#pst_ap_id').trigger('change');
            $('#pst_type_id').trigger('change');
            $('#shiftTypeModal').find('input, select').each(function() {
                const fieldId = $(this).attr('id'); // Get the field ID
                $('#' + fieldId + '_error').text(''); // Clear the corresponding error message
            });

        }


        // Handle Delete Shift Type
        function handleDeleteShiftType() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this shift policy!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('attendance-shift-type.destroy', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        method: "DELETE",
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.success,
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                didClose: () => {
                                    location.reload(); // Reload the page after deletion
                                }
                            });
                        },
                        error: function(xhr) {
                            var errorMessage =
                                'An error occurred while deleting the shift policy. Please try again.';

                            // Check if the response contains an error message from the server
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }

                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }


        // Handle Add Shift Type
        function handleAddShiftType() {
            resetForm();
            $('#shiftTypeModalTitle').html('Add Shift Type');
            $('#saveBtn').html('Save');
            $('#shiftTypeModal').modal('show');
            handleCheckboxChange();
            // Unbind previous events to prevent multiple bindings
            // $('input, select').off('input change');
        }

        // Handle Edit Shift Type
        function handleEditShiftType() {
            const shiftData = $(this).data('shift');
            let weekOffStr = shiftData.week_off ?? '{}';
            let weekOffArray = weekOffStr.replace(/[{}]/g, '').split(',');

            let weekOffStr2 = shiftData.week_off2 ?? '{}';
            let weekOffArray2 = weekOffStr2.replace(/[{}]/g, '').split(',');

            $('#shiftTypeModalTitle').html('Update Shift Type');
            $('#saveBtn').html('Update');

            // Fill form fields with data
            $('#pst_id').val(shiftData.id);
            $('#pst_ap_id').val(shiftData.ap_id).trigger('change');
            $('#pst_type_id').val(shiftData.type_id).trigger('change');
            $('#pst_name').val(shiftData.name);
            $('#pst_code').val(shiftData.code);
            $('#pst_start_time').val(shiftData.start_time);
            $('#pst_end_time').val(shiftData.end_time);
            $('#pst_allow_grace_time').prop('checked', shiftData.allow_grace_time == 1);
            $('#pst_grace_time').val(shiftData.grace_time);
            $('#pst_shift_duration').val(shiftData.shift_duration);
            $('#pst_break_duration_minutes').val(shiftData.break_duration_minutes);
            $('#pst_end_next_day').prop('checked', shiftData.end_next_day == 1);
            $('#pst_end_by').val(shiftData.end_by);
            $('#pst_allow_break1').prop('checked', shiftData.allow_break1 == 1);
            $('#pst_break_begin_time1').val(shiftData.break_begin_time1);
            $('#pst_break_end_time1').val(shiftData.break_end_time1);
            // $('#pst_punch_begin_before').val(shiftData.punch_begin_before);
            // $('#pst_punch_end_after').val(shiftData.punch_end_after);
            $('#pst_break1_duration').val(shiftData.break1_duration);
            $('#pst_allow_break2').prop('checked', shiftData.allow_break2 == 1);
            $('#pst_break_begin_time2').val(shiftData.break_begin_time2);
            $('#pst_break_end_time2').val(shiftData.break_end_time2);
            $('#pst_allow_punch_begin_before').prop('checked', shiftData.allow_punch_begin_before == 1);
            $('#pst_mins_punch_begin_before').val(shiftData.mins_punch_begin_before);
            $('#pst_allow_punch_end_after').prop('checked', shiftData.allow_punch_end_after == 1);
            $('#pst_mins_punch_end_after').val(shiftData.mins_punch_end_after)
            $('#pst_allow_partial_day').prop('checked', shiftData.allow_partial_day == 1);
            $('#pst_partial_day_type_id').val(shiftData.partial_day_type_id).trigger('change');
            $('#pst_partial_day_begin_time').val(shiftData.partial_day_begin_time);
            $('#pst_partial_day_end_time').val(shiftData.partial_day_end_time);

            $('#pst_allow_partial_day2').prop('checked', shiftData.allow_partial_day2 == 1);
            $('#pst_partial_day_type_id2').val(shiftData.partial_day_type_id2).trigger('change');
            $('#pst_partial_day_begin_time2').val(shiftData.partial_day_begin_time2);
            $('#pst_partial_day_end_time2').val(shiftData.partial_day_end_time2);

            $('#pst_hd_office_report_after').prop('checked', shiftData.hd_office_report_after == 1);
            $('#pst_hd_office_report_after_time').val(shiftData.hd_office_report_after_time);
            $('#pst_session1_end_by').val(shiftData.session1_end_by);
            $('#pst_session2_grace_time').val(shiftData.session2_grace_time);
            $('#pst_hd_office_report_before').prop('checked', shiftData.hd_office_report_before == 1);
            $('#pst_hd_office_report_before_time').val(shiftData.hd_office_report_before_time);

            weekOffArray.forEach(function (val) {
                $(`input[name="week_off[]"][value="${val}"]`).prop('checked', true);
            });

            weekOffArray2.forEach(function (val) {
                $(`input[name="week_off2[]"][value="${val}"]`).prop('checked', true);
            });
            
            $(`input[name="pst_is_break_paid"][value="${shiftData.is_paid}"]`).prop('checked', true);
            $(`input[name="pst_is_high"][value="${shiftData.is_high}"]`).prop('checked', true);
            $(`input[name="pst_is_face_track_yes"][value="${shiftData.is_face_track_yes}"]`).prop('checked', true);

                handleCheckboxChange(); $('#shiftTypeModal').modal('show');
            }

            // Function to handle checkbox changes and update the required/readonly/disabled state
            function handleCheckboxChange() {

                // Handle pst_allow_grace_time
                if ($('#pst_allow_grace_time').prop('checked')) {
                    $('#pst_grace_time').prop('readonly', false);
                } else {
                    $('#pst_grace_time').prop('readonly', true).val('');
                    $('#pst_grace_time_error').text('');
                }

                // Handle End Next Day
                if ($('#pst_end_next_day').prop('checked')) {
                    $('#pst_end_by').prop('readonly', false);
                } else {
                    $('#pst_end_by').prop('readonly', true).val('');
                }

                // Handle pst_allow_break1
                if ($('#pst_allow_break1').prop('checked')) {
                    $('#pst_break_begin_time1').prop('readonly', false);
                    $('#pst_break_end_time1').prop('readonly', false);
                    // $('#pst_punch_begin_before').prop('readonly', false);
                    // $('#pst_punch_end_after').prop('readonly', false);
                    $('#pst_break1_duration').prop('readonly', false);
                } else {
                    // Disable the time inputs and optionally clear the values
                    $('#pst_break_begin_time1').prop('readonly', true).val('');
                    $('#pst_break_end_time1').prop('readonly', true).val('');
                    // $('#pst_punch_begin_before').prop('readonly', true).val('');
                    // $('#pst_punch_end_after').prop('readonly', true).val('');
                    $('#pst_break1_duration').prop('readonly', true).val('');
                }

                // Handle pst_allow_break2
                if ($('#pst_allow_break2').prop('checked')) {
                    $('#pst_break_begin_time2').prop('readonly', false);
                    $('#pst_break_end_time2').prop('readonly', false);
                } else {
                    // Disable the time inputs and optionally clear the values
                    $('#pst_break_begin_time2').prop('readonly', true).val('');
                    $('#pst_break_end_time2').prop('readonly', true).val('');
                }

                // Handle pst_allow_punch_begin_before
                if ($('#pst_allow_punch_begin_before').prop('checked')) {
                    $('#pst_mins_punch_begin_before').prop('readonly', false);
                } else {
                    $('#pst_mins_punch_begin_before').prop('readonly', true).val('');
                }

                // Handle pst_allow_punch_end_after
                if ($('#pst_allow_punch_end_after').prop('checked')) {
                    $('#pst_mins_punch_end_after').prop('readonly', false);
                } else {
                    $('#pst_mins_punch_end_after').prop('readonly', true).val('');
                }

                // Handle pst_allow_partial_day
                if ($('#pst_allow_partial_day').prop('checked')) {
                    $('#pst_partial_day_type_id').prop('disabled', false);
                    $('#pst_partial_day_begin_time').prop('readonly', false);
                    $('#pst_partial_day_end_time').prop('readonly', false);
                    $('#partial_week_1 input[type="checkbox"]').prop('disabled', false);
                } else {
                    $('#pst_partial_day_type_id').val('').change();
                    $('#pst_partial_day_type_id').prop('disabled', true).val('');
                    $('#pst_partial_day_begin_time').prop('readonly', true).val('');
                    $('#pst_partial_day_end_time').prop('readonly', true).val('');
                    $('#partial_week_1 input[type="checkbox"]').prop('checked', false).prop('disabled', true);
                }

                // Handle pst_allow_partial_day2
                if ($('#pst_allow_partial_day2').prop('checked')) {
                    $('#pst_partial_day_type_id2').prop('disabled', false);
                    $('#pst_partial_day_begin_time2').prop('readonly', false);
                    $('#pst_partial_day_end_time2').prop('readonly', false);
                    $('#partial_week_2 input[type="checkbox"]').prop('disabled', false);
                } else {
                    $('#pst_partial_day_type_id2').val('').change();
                    $('#pst_partial_day_type_id2').prop('disabled', true).val('');
                    $('#pst_partial_day_begin_time2').prop('readonly', true).val('');
                    $('#pst_partial_day_end_time2').prop('readonly', true).val('');
                    $('#partial_week_2 input[type="checkbox"]').prop('checked', false).prop('disabled', true);
                }

                // Mark half day when report to office after
                if ($('#pst_hd_office_report_after').prop('checked')) {
                    $('#pst_hd_office_report_after_time').prop('readonly', false);
                    $('#pst_session1_end_by').prop('readonly', false);
                    $('#pst_session2_grace_time').prop('readonly', false);
                } else {
                    $('#pst_hd_office_report_after_time').prop('readonly', true).val('');
                    $('#pst_session1_end_by').prop('readonly', true).val('');
                    $('#pst_session2_grace_time').prop('readonly', true).val('');
                }

                // Mark half day when leave office before
                if ($('#pst_hd_office_report_before').prop('checked')) {
                    $('#pst_hd_office_report_before_time').prop('readonly', false);
                } else {
                    $('#pst_hd_office_report_before_time').prop('readonly', true).val('');
                }
            }

            // Bind the change event for the checkboxes
            $('#pst_allow_punch_begin_before').on('change', handleCheckboxChange);
            $('#pst_allow_break').on('change', handleCheckboxChange);
            $('#pst_allow_punch_end_after').on('change', handleCheckboxChange);
            $('#pst_allow_grace_time').on('change', handleCheckboxChange);
            $('#pst_allow_partial_day').on('change', handleCheckboxChange);
            $('#pst_allow_partial_day2').on('change', handleCheckboxChange);
            $('#pst_hd_office_report_after').on('change', handleCheckboxChange);
            $('#pst_hd_office_report_before').on('change', handleCheckboxChange);
            $('#pst_allow_break2').on('change', handleCheckboxChange);
            $('#pst_allow_break1').on('change', handleCheckboxChange);
            $('#pst_end_next_day').on('change', handleCheckboxChange);

            // Run on page load to set initial state
            $(document).ready(function() {
                handleCheckboxChange(); // Initialize the form fields based on the current checkbox state

                $('#pst_type_id').on('change', function(e){
                    if ($('#pst_type_id').val() == 244) {
                        $('#pst_allow_grace_time').prop('disabled', false);
                        $('#pst_allow_punch_begin_before').prop('disabled', false);
                        $('#pst_allow_partial_day').prop('disabled', false);
                        $('#pst_allow_partial_day2').prop('disabled', false);
                        $('#pst_hd_office_report_after').prop('disabled', false);
                        $('#pst_hd_office_report_before').prop('disabled', false);

                        $('#pst_start_time').prop('disabled', false);
                        $('#pst_end_time').prop('disabled', false);

                        //True Check
                        $('#pst_shift_duration').prop('disabled', true);
                        $('#pst_break_duration_minutes').prop('disabled', true);
                        $('#pst_end_next_day').prop('disabled', true);

                    } else if ($('#pst_type_id').val() == 246) {
                        $('#pst_allow_grace_time').prop('disabled', true);
                        $('#pst_allow_punch_begin_before').prop('disabled', true);
                        $('#pst_allow_partial_day').prop('disabled', true);
                        $('#pst_allow_partial_day2').prop('disabled', true);
                        $('#pst_hd_office_report_after').prop('disabled', true);
                        $('#pst_hd_office_report_before').prop('disabled', true);

                        $('#pst_start_time').prop('disabled', true);
                        $('#pst_end_time').prop('disabled', true);

                        //False Check
                        $('#pst_shift_duration').prop('disabled', false);
                        $('#pst_break_duration_minutes').prop('disabled', false);
                        $('#pst_end_next_day').prop('disabled', false);
                    }
                });
            });
    </script>
@endsection
