@extends('admin.layout.master')

@section('title', 'Gate Pass Details')

@section('content')
    <input type="hidden" id="ajaxCall" value="{{ url('/') }}">
    <div class="page-header d-md-flex d-block ">
        <div class="page-leftheader ">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/requests/gate-pass">Gate Pass</a></li>
                        <li class="active"><span><b>Gate Pass Details</b></span></li>
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
    src="{{ isset($data->fh_employee) && $data->fh_employee->emp_profile_photo
        ?  $data->fh_employee->emp_profile_photo

        : asset('assets/imgs/user.png') }}">

                                {{-- <img class="avatar avatar-xxl brround" alt="img"
                                    src="{{ isset($data->fh_employee) ? asset('uploads/employee_profile/' . $data->fh_employee->emp_profile_photo) : '' }}"> --}}
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
                    @if (count($data->fh_plan_approval_log) > 0)
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
                    @elseif(count($data->fh_approval_log2) > 0)
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
                                <h4 class="card-title">Gate Pass Request</h4>
                            </div>
                        </div>

                        <div class="card-body pt-3">
                            <div class="row">
                                @php
                                    $details = [
                                        'Applied Date' => $data->created_at
                                            ? $data->created_at->format('d-m-Y H:i:s')
                                            : 'N/A',
                                        'Date' => $data->gtp_date ? $data->gtp_date->format('d-m-Y') : 'N/A',
                                        'Out Time' => $data->gtp_out_time ?? 'N/A',
                                        'In Time' => $data->gtp_in_time ?? 'N/A',
                                        'Destination' => $data->gtp_destination ?? 'N/A',
                                        'Reason' => $data->gtp_reason ?? 'N/A',
                                        'Status' => optional($data->fh_approval_status)->m_name ?? 'N/A',
                                    ];
                                @endphp

                                @foreach ($details as $label => $value)
                                    <div class="col-xl-4 mb-3">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0">{{ $label }}:</h6>
                                            <div class="ms-2">{{ $value }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                        @if ($approvalData)
                            <div class="card-header p-3">
                                <h3 class="card-title">Gate Pass Approval Or Reject</h3>
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
                                                        data-gtp_id="{{ md5($data->gtp_id) }}"
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
                                                        data-gtp_id="{{ md5($data->gtp_id) }}"
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
                            <x-approval-form :moduleName="$data?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$data->gtp_id" :moduleId="$data->gtp_module_id" actionUrl="{{route('approve.gate-pass')}}" />
                        @endif
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <ul class="timeline">
                                @foreach ($gatepassApprovalLogs as $item)
                                    @php
                                        $jsonData = $item->fh_status->m_other;
                                        $decoded = json_decode($jsonData, true);

                                        $color = $decoded['color'] ?? '#6c757d';
                                        $icon = $decoded['web_icon'] ?? 'fa fa-check';

                                        $statusName = $item->fh_status->m_name;
                                        $remark = $item->log_description;

                                        $emp_name = optional($item->fh_employee)->emp_full_name;
                                        $emp_code = optional($item->fh_employee)->emp_code;
                                        $emp_designation = optional($item->fh_employee->fh_designation)->dg_name;
                                    @endphp

                                    <li class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }}">

                                        <a href="javascript:void(0);" class="font-weight-semibold fs-15 mb-2 ms-3">
                                            <span class="badge" style="background-color:{{ $color }}">
                                                <i class="{{ $icon }}"></i>
                                                {{ $statusName }}
                                            </span>
                                        </a>

                                        <a href="javascript:void(0);" class="text-muted float-end fs-12">
                                            On {{ \Carbon\Carbon::parse($item->created_at)->format('l') }}
                                        </a>

                                        <br>

                                        <span class="text-muted float-end ms-3 fs-14">
                                            <i class="fa fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($item->created_at)->format('d-M-Y') }}

                                            <i class="ms-3 fa fa-clock-o"></i>
                                            {{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}
                                        </span>

                                        <p class="mb-0 pb-0 text-muted fs-18 pt-1 ms-3">
                                            {{ $emp_name }}
                                            <span class="fs-14">({{ $emp_code }})</span>
                                        </p>

                                        <span class="mb-0 pb-0 text-muted fs-14 ms-3">
                                            {{ $emp_designation }}
                                        </span>

                                        <br>

                                        <span class="text-muted ms-3 fs-14">
                                            Remark : {{ $remark }}
                                        </span>

                                    </li>
                                @endforeach
                            </ul>
                        </div>
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

            planId = dataAttributes["gtp_id"];

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
                    POST_TYPE: 'GATEPASS_REQUEST_APPROVAL',
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
