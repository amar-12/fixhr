<?php
use App\Helpers\CentralLogics;
use App\Models\AttendanceRecord;
use Illuminate\Support\Carbon;
?>
@extends('admin.layout.master')
@section('title')
    Attendance By
@endsection
@section('css')
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
        <div class="p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/settings/attendance-details') }}">Summary Attendance</a></li>
                <li class="active"><span><b>Attendance By</b></span></li>
            </ol>
        </div>

        <div class="page-header d-xl-flex d-block">
            <div class="page-leftheader">
                <div class="page-title">Attendance By <span
                        class="text-primary">{{ $emp->emp_fname . ' ' . $emp->emp_mname . ' ' . $emp->emp_lname }}
                        ({{ $emp->emp_code }})</span>
                </div>
            </div>
            <input type="text" id="currentDayGet" value="{{ date('Y-m') }}" hidden>

        </div>



        {{-- Edit modal start --}}
        <div class="container">
            <div class="modal fade" id="showmodal" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" style="font-size:17px;"><span id="emp_code"></span> - <span
                                    id="emp_name"></span>(<span id="time_shift"></span>)</h5>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true" data-bs-dismiss="modal">&times;</span>
                            </button>
                        </div>

                        <form action="{{ route('edit.attendence') }}" method="post">
                            @csrf
                            <div class="modal-body">
                                <div class="row">
                                    <div class="mb-5">
                                        <span class="my-5"><span class="fw-bold fs-14">Shift Name :
                                                <span id="shift_name"></span></span><br>
                                            <span class="my-5"><span class="fw-bold fs-14">Shift Start : <span
                                                id="genral_shift_start"></span> <br>
                                            <span class="my-5"><span class="fw-bold fs-14">Shift End : <span
                                                id="genral_shift_end"></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <input type="hidden" id="emp_id" name="id" class="form-control">
                                    <input type="hidden" id="date" name="punch_date" class="form-control">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Punch In</label>
                                            <div class="input-group">
                                                <input type="text" name="in_time" class="form-control timepicker"
                                                    pattern="^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(?:[APap][mM])?$"
                                                    id="punch_in">
                                                <div class="input-group-text">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Punch Out</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control timepicker" id="punch_out"
                                                    name="out_time" required
                                                    pattern="^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(?:[APap][mM])?$">
                                                <div class="input-group-text">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label">Reason*</label>
                                            <div class="input-group">
                                                <textarea rows="3" id="reason" class="form-control" name="reason" placeholder="Enter Reason" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-danger " data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-outline-primary" type="submit">Update</button>
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
                            <h5 class="modal-title" id="exampleModalLongTitle" style="font-size:18px;">Attendance Details
                            </h5>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true" data-bs-dismiss="modal">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card-header border-bottom-0 d-block">
                                        <h5 class="">Timesheet: <span class="fs-14 mx-3 text-muted"
                                                id="time_shift"></span></h5>
                                        <h6 class=""><span class="fs-14 text-dark">General Shift: </span>
                                            <span class="fs-14 text-dark" id="genral_shift"></span>
                                        </h6>
                                    </div>

                                    <div class="col-sm-12 my-auto" style="height: 260px">
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="p-3 text-center border border-muted">
                                                    <h6 class="mb-1 fs-14 font-weight-semibold" id="punch_in"></h6>
                                                    <small class="text-muted fs-14">Punch In</small>
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
                                                    <h6 class="mb-1 fs-14 font-weight-semibold" id="punch_out"></h6>
                                                    <small class="text-muted fs-14">Punch Out</small>
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
                                            <h4 class="my-5">Timeline</h4>
                                        </div>
                                        <div class="col-sm-12 mt-5">
                                            <div class="tl-content tl-content-active">
                                                {{-- <div class="tl-header d-none">
                                                    <span class="tl-marker"></span>
                                                    <div class="row">
                                                        <div class="col-10">
                                                            <p class="tl-title">First Half
                                                                <br>
                                                                <span class="text-dark fs-12 d-none">Remark:
                                                                    <span class="text-muted"></span>
                                                                </span>
                                                                <br>
                                                            </p><p>
                                                        </p></div>
                                                    </div>
                                                </div> --}}
                                                <div class="tl-header ">
                                                    <span class="tl-marker"></span>
                                                    <div class="row">
                                                        <div class="col-10">
                                                            <p class="tl-title">Punch In at <span id="punch_in"></span> |
                                                                <span id="shift_name"></span>
                                                                <br>
                                                                <a target="_blank">
                                                                    <span class="text-muted fs-12 "><i
                                                                            class="fa fa-map-marker mx-1"></i>
                                                                        <span id="location_punch_in"></span>
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
                                                <div class="tl-header d-none" id="tapList">
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
                                                <div class="tl-header ">
                                                    <span class="tl-marker"></span>
                                                    <div class="row">
                                                        <div class="col-10">
                                                            <p class="tl-title">Punch Out at
                                                                <span id="punch_out"></span> | <span
                                                                    id="shift_name"></span>
                                                                <br>
                                                                <a target="_blank">
                                                                    <span class="text-muted fs-12 "><i
                                                                            class="fa fa-map-marker mx-1"></i>
                                                                        <span id="location_punch_out"></span>
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
                                                <div class="tl-header d-none">
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
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table display table-vcenter text-wrap border-bottom"
                                id="attendance-by-table-dynamic">
                                <thead>
                                    <tr>
                                        @foreach($columns as $colum)
                                            <th>{{$colum}}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthlyAttendanceData as $attendance_data)
                                            <tr>
                                                <td>{{ $attendance_data['user_friendly_date'] }}</td>
                                                <td>{{ $attendance_data['day'] }}</td>
                                                <td> <span style="color: {{ isset($attendance_data['statusColor']) ? $attendance_data['statusColor'] : '' }}">{{ $attendance_data['status'] }}</span></td>
                                                <td>{{$attendance_data['checkInTime']}}
                                                    @if ($attendance_data['late'])
                                                    <br><span class="late-status fs-10 fw-bolder">
                                                        {{ 'Late By: '.$attendance_data['late'].' Min' }}
                                                    </span>
                                                @endif
                                                </td>
                                                <td>{{$attendance_data['checkOutTime']}}
                                                    @if ($attendance_data['earlyExit'])
                                                    <br><span class="late-status fs-10 fw-bolder">
                                                        Early Exit By:
                                                        {{
                                                            $attendance_data['earlyExit']. ' Min '
                                                        }}
                                                    </span>
                                                @endif
                                                </td>
                                                <td>{{$attendance_data['workingHour']}}
                                                    @if ($attendance_data['OT'])
                                                    <br><span class="overtime-status fs-10 fw-bolder">
                                                        {{ 'OT: '.$attendance_data['OT'].' Hour' }}
                                                    </span>
                                                @endif
                                            </td>
                                                <td>{{$attendance_data['action']}}</td>
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

