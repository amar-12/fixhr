@extends('admin.layout.master')

@section('title', 'Daily Attendance Details')

@section('content')
    <input type="hidden" id="ajaxCall" value="{{ url('/') }}">
    <div class="page-header d-md-flex d-block ">
        <div class="page-leftheader ">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/attendance/daily-attendance">Attendance</a></li>
                        <li class="active"><span><b>Daily Attendance</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW -->
    <div class="row">
        <div class="col-xl-3 col-md-12 col-lg-12">
            <div class="">
                <div class="card user-pro-list overflow-hidden">
                    <div class="card-body ">
                        <div class="text-center">
                            <div class="widget-user-image mx-auto text-center">
                                <img class="avatar avatar-xxl brround" alt="img"
                                    src="{{ isset($data->fh_employee) && $data->fh_employee->emp_profile_photo ? $data->fh_employee->emp_profile_photo : asset('assets/imgs/user.png') }}">
                            </div>
                            <div class="pro-user mt-3">
                                <h5 class="pro-user-username text-dark mb-1 fs-16">
                                    {{ $data->fh_employee->emp_full_name ?? 'N/A' }}</h5>
                                <h6 class="pro-user-desc text-muted fs-12">
                                    {{ $data->fh_employee->fh_designation->dg_name ?? 'N/A' }}</h6>
                            </div>
                        </div>
                        <h5 class="mb-2 mt-4 font-weight-semibold">Basic Details</h5>
                        <div class="table-responsive">
                            <table class="table text-nowrap">
                                <tbody>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Emp Code</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $data->fh_employee->emp_code ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Email ID</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $data->fh_employee->emp_email ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Contact No</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $data->fh_employee->emp_phone ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Branch</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $data->fh_employee->fh_branch->br_name ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Department</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $data->fh_employee->fh_department->d_name ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Status</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span
                                                class="badge {{ $data->fh_employee && $data->fh_employee->emp_status == 71 ? 'badge-success-light' : 'badge-warning-light' }}">
                                                {{ $data->fh_employee && $data->fh_employee->emp_status == 71 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <section id="reverbDynamicSection" class="p-0 m-0">
                    @if ($data->fh_plan_approval_log && count($data->fh_plan_approval_log) > 0)
                        <div class="card ">
                            <div class="card-header px-3">
                                <div class="card-title">Approval Details</div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-vcenter text-nowrap border-bottom">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="col-md-4">Name</th>
                                                <th class="col-md-4">Action</th>
                                                <th class="col-md-4">Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data->fh_plan_approval_log as $item)
                                                <tr>
                                                    <td class="col-md-4">{{ $item->fh_employee->emp_full_name ?? '' }}</td>
                                                    <td class="col-md-4">{{ $item->fh_status->m_name }}</td>
                                                    <td class="col-md-4 text-nowrap">{{ $item->log_description }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @elseif($data->fh_approval_log2 && count($data->fh_approval_log2) > 0)
                        <div class="card ">
                            <div class="card-header px-3">
                                <div class="card-title">Approval Details</div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-vcenter text-nowrap border-bottom">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="col-md-4">Name</th>
                                                <th class="col-md-4">Action</th>
                                                <th class="col-md-4">Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data->fh_approval_log2 as $item)
                                                <tr>
                                                    <td class="col-md-4">{{ $item->fh_employee->emp_full_name ?? '' }}</td>
                                                    <td class="col-md-4">{{ $item->fh_status->m_name }}</td>
                                                    <td class="col-md-4 text-nowrap">{{ $item->log_description }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </div>
        <div class="col-xl-9 col-md-12 col-lg-12 ">
            <div class="row">

                <div class="col-xl-12 col-md-12">
                    <div class="card border overflow-hidden">
                        <div class="card-header p-3">
                            <div>
                                <h4 class="card-title">Daily Attendance Request</h4>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-body pt-3">
                                <div class="row">
                                    @php
                                        $details = [
                                            'Attendance Date' => $data->atd_date ? $data->atd_date->format('d-m-Y') : 'N/A',
                                            'Applied Date' => $data->created_at ? $data->created_at->format('d-m-Y H:i:s') : 'N/A',
                                            'Work Mode Type' => $data?->fh_attendance_work_mode?->m_name ?? 'N/A',
                                            'Check In Method' => $data?->fh_attendance_checkin_type?->m_name ?? 'N/A',
                                            'In Time' => $data->atd_check_in_time ? $data->atd_check_in_time->format('H:i:s') : 'N/A',
                                            'Out Time' => $data->atd_check_out_time ? $data->atd_check_out_time->format('H:i:s') : 'N/A',
                                            'Total Working Hours' => $data->atd_total_worked_hours ?  gmdate('H:i:s', ($data->atd_total_worked_hours ?? 0) * 3600)  : 'N/A',
                                            'Late Duration' => $data->atd_late_duration ? gmdate('H:i:s', ($data->atd_late_duration ?? 0) * 60) : 'N/A',
                                            'Early Exit' => $data->atd_early_exit_duration ? gmdate('H:i:s', ($data->atd_early_exit_duration ?? 0) * 60) : 'N/A',
                                            'Overtime Hours' => $data->atd_overtime_hours ? gmdate('H:i:s', ($data->atd_is_overtime ?? 0) * 3600) : 'N/A',
                                            'Punch In Location' => $data->atd_punchin_location ?? 'N/A',
                                            'Punch In Longitude' => $data->atd_longitude_punchin ?? 'N/A',
                                            'Punch In Latitude' => $data->atd_latitude_punchin ?? 'N/A',
                                            'Punch Out Location' => $data->atd_punchout_location ?? 'N/A',
                                            'Punch Out Longitude' => $data->atd_longitude_punchout ?? 'N/A',
                                            'Punch Out Latitude' => $data->atd_latitude_punchout ?? 'N/A',
                                            'Status' => $data?->fh_attendance_status?->m_name,
                                            'Punch In Photo' => !empty($data->atd_punchin_photo) && ($data?->fh_attendance_checkin_type?->m_id == 314) ? json_decode($data->atd_punchin_photo, true) : [],
                                            'Punch Out Photo' => !empty($data->atd_punchout_photo) && ($data?->fh_attendance_checkin_type?->m_id == 314) ? json_decode($data->atd_punchout_photo, true) : [],
                                            'Approval Status' => $data?->fh_approval_status?->m_name,
                                        ];

                                        $data->co_quantity > 0 ? $details['Comp Off Quantity'] = $data->co_quantity ?? 0 : null;
                                    @endphp

                                    @foreach ($details as $label => $value)
                                        {{-- Combine In Time + Out Time --}}
                                        @if ($label == 'In Time')
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="text-muted">In Time</div>
                                                        <div class="font-weight-bold">{{ $value }}</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <div class="text-muted">Out Time</div>
                                                        <div class="font-weight-bold">{{ $details['Out Time'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($label == 'Out Time')
                                            @continue

                                        {{-- Total Workng Hours + Overtime Hours --}}
                                        @elseif ($label == "Total Working Hours")
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="text-muted">Total Working Hours</div>
                                                        <div class="font-weight-bold">{{ $details['Total Working Hours'] ?? 'N/A' }}</div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between">
                                                        <div class="text-muted">Overtime Hours</div>
                                                        <div class="font-weight-bold">{{ $details["Overtime Hours"] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($label == "Overtime Hours")
                                            @continue

                                        {{-- Late Duration + Early Exit --}}
                                        @elseif ($label == 'Late Duration')
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="text-muted">Early Exit</div>
                                                        <div class="font-weight-bold">{{ $details["Early Ext"] ?? "N/A" }}</div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between">
                                                        <div class="text-muted">Late Duration</div>
                                                        <div class="font-weight-bold">{{ $details['Late Duration'] ?? "N/A" }}</div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        @elseif ($label == "Early Exit")
                                            @continue

                                        {{-- Punch In Location + Longitude --}}
                                        @elseif ($label == 'Punch In Location')
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="text-muted">Punch In Location</div>
                                                        <div class="font-weight-bold">{{ $value }}</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <div class="text-muted">Punch In Longitude</div>
                                                        <div class="font-weight-bold">{{ $details['Punch In Longitude'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($label == 'Punch In Longitude')
                                            @continue

                                        {{-- Punch In Latitude + Punch Out Location --}}
                                        @elseif ($label == 'Punch In Latitude')
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="text-muted">Punch In Latitude</div>
                                                        <div class="font-weight-bold">{{ $value }}</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <div class="text-muted">Punch Out Location</div>
                                                        <div class="font-weight-bold">{{ $details['Punch Out Location'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($label == 'Punch Out Location')
                                            @continue

                                        {{-- Punch Out Longitude + Punch Out Latitude --}}
                                        @elseif ($label == 'Punch Out Longitude')
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="text-muted">Punch Out Longitude</div>
                                                        <div class="font-weight-bold">{{ $value }}</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <div class="text-muted">Punch Out Latitude</div>
                                                        <div class="font-weight-bold">{{ $details['Punch Out Latitude'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($label == 'Punch Out Latitude')
                                            @continue

                                        {{-- Image Cards --}}
                                        @elseif ($label == 'Punch In Photo' || $label == 'Punch Out Photo')
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <h6 class="text-muted mb-1">{{ $label }}</h6>
                                                    <div class="font-weight-bold">
                                                        @if (!empty($value) && is_array($value))
                                                            @foreach ($value as $photo)
                                                                <a href="{{ asset($photo) }}" target="_blank">
                                                                    <img src="{{ asset($photo) }}" alt="{{ $label }}" class="img-thumbnail rounded"
                                                                         style="max-width: 80px; max-height: 80px; transition: transform 0.3s;"
                                                                         onmouseover="this.style.transform='scale(1.2)'"
                                                                         onmouseout="this.style.transform='scale(1)'">
                                                                </a>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted">N/R</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                        {{-- Status Cards --}}
                                        @elseif ($label == 'Status' || $label == 'Approval Status')
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <h6 class="text-muted mb-1">{{ $label }}</h6>
                                                    <span class="badge badge-pill badge-info px-3 py-2">{{ $value ?? 'N/A' }}</span>
                                                </div>
                                            </div>

                                        {{-- Default field card --}}
                                        @else
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="border rounded p-3 bg-light">
                                                    <h6 class="text-muted mb-1">{{ $label }}</h6>
                                                    <div class="font-weight-bold">{{ $value ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                </div>
                            </div>
                        </div>

                        @if ($approvalData)
                            <div class="card-header p-3">
                                <h3 class="card-title">Attendance Approval Or Reject</h3>
                            </div>
                            <div class="card-body">
                                <form id="approvalForm">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="form-label mb-0 mt-2">Message</label>
                                            <div class="col-md-12 col-lg-12">
                                                <textarea rows="2" name="message" class="form-control" id="actionMessage"></textarea>
                                            </div>
                                        </div>

                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                    <!-- Reject Button -->

                                                    <button type="button"
                                                        data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                        data-approval_type="0"
                                                        data-approval_action_type="{{ $approvalData->pa_type }}"
                                                        data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                        data-atd_id="{{ md5($data->atd_id) }}"
                                                        data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($data->fh_employee)->emp_d_id }}"
                                                        class="btn btn-outline-danger  actionBtn mx-3">
                                                        Reject
                                                    </button>

                                                    <!-- Approval Button -->
                                                    <button type="button"
                                                        data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                        data-approval_type="1"
                                                        data-approval_action_type="{{ $approvalData->pa_type }}"
                                                        data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                        data-atd_id="{{ md5($data->atd_id) }}"
                                                        data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($data->fh_employee)->emp_d_id }}"
                                                        class="btn btn-success actionBtn">
                                                        {{ $approvalData?->fh_approver_status?->m_name }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @elseif ($canApprove)
                            <x-approval-form :moduleName="$data?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$data->atd_id" :moduleId="$data->atd_module_id"
                                actionUrl="{{ route('approve.attendance') }}" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END ROW -->
@endsection

@section('script')
    <script>
        $(document).on('click', '.actionBtn', function() {
            $(".actionBtn").attr("disabled", true);
            var planId = '';
            var dataAttributes = {};
            $.each(this.attributes, function() {
                if (this.name.startsWith('data-')) {
                    var key = this.name.slice(5); // remove 'data-' prefix
                    dataAttributes[key] = this.value;
                }
            });

            planId = dataAttributes["atd_id"];

            dataAttributes['message'] = $('#actionMessage').val();

            if (dataAttributes['message'] == '') {
                Swal.fire({
                    icon: "warning",
                    text: 'Message is required.',
                    timer: 3000,
                });
                $(".actionBtn").attr("disabled", false);
                return false;
            }

            $.ajax({
                url: '{{ route('admin.approval-handler') }}',
                method: "post",
                data: {
                    _token: '{{ csrf_token() }}',
                    POST_TYPE: 'Attendance_REQUEST_APPROVAL',
                    data: dataAttributes
                },
                dataType: "json",
                beforeSend: function() {
                    $("#gloabal-overlay").show();
                    $(".actionBtn").attr("disabled", true);
                },
                success: function(data) {
                    $("#gloabal-overlay").hide();
                    if (data.status == true) {
                        Swal.fire({
                            icon: "success",
                            text: data.message,
                            timer: 3000,
                        });
                        var baseUrl = $('#ajaxCall').val();
                        window.location.reload();

                    } else {
                        Swal.fire({
                            icon: "warning",
                            text: data.message,
                            timer: 3000,
                        });
                        $('.actionBtn').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $("#gloabal-overlay").hide();
                    $('.actionBtn').prop('disabled', false);
                    Swal.fire({
                        icon: "error",
                        text: error,
                        timer: 3000,
                    });
                },
            });
        });
    </script>
    <script src="{{ asset('assets/js/approval-form.js') }}"></script>
@endsection
