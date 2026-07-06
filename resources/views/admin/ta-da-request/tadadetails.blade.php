@extends('admin.layout.master')
@section('title')
    Travel
@endsection
@section('css')
    <!-- Leaflet CSS -->
    {{-- <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" /> --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- <style>
        #map {
            height: 500px;
            width: 100%;
/*            border-radius: 10px;*/
        }
    </style> -->
    <style>
    /* Modal body full height and flex container for map & list */
    #largemodal .modal-body {
        min-height: 450px;
        display: flex;
        gap: 1rem;
        padding: 1rem 2rem;
    }
    #map {
        flex: 1 1 0;
        height: 100%;
        border-radius: 8px;
        box-shadow: 0 0 10px rgb(0 0 0 / 0.1);
    }
    #travelPathContainer {
        flex: 0 0 350px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 12px rgb(0 0 0 / 0.15);
        padding: 1rem;
        overflow-y: auto;
        max-height: 450px;
    }
    #travelPathContainer h5 {
        margin-bottom: 1rem;
        font-weight: 600;
        border-bottom: 2px solid #007bff;
        padding-bottom: 0.5rem;
    }
    #locationList {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    #locationList li {
        padding: 0.5rem;
        border-bottom: 1px solid #e0e0e0;
        font-size: 0.9rem;
    }
    #locationList li strong {
        color: #007bff;
    }
    /* Make sure modal-footer is fixed bottom for better UI */
    #largemodal .modal-footer {
        display: flex;
        justify-content: flex-end;
        padding: 1rem 2rem;
    }
    #travelModeSelect, #drawRoute {
        margin-bottom: 1rem;
        width: 100%;
    }
</style>
@endsection

@section('content')
    <input type="hidden" id="ajaxCall" value="{{ url('/') }}">
    <div class="page-header d-md-flex d-block ">
        <div class="page-leftheader ">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/ta-da-request/travel">Travel Managements</a></li>
                        <li class="active"><span><b>Travel Details</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @php
        $jsonData = $data->fh_approval_status->m_other;

        // Decode the JSON to an associative array
        $decodedData = json_decode($jsonData, true); // true for associative array

        // Now access the color value
        $color = $decodedData['color'];
        $icon = $decodedData['web_icon'];
    @endphp
    <!-- ROW -->
    <div class="row">
        <div class="col-xl-3 col-md-12 col-lg-12">
            <div class="">
                <div class="card user-pro-list overflow-hidden">
                    <div class="card-body ">
                        <div class="text-center">
                            <div class="widget-user-image mx-auto text-center">
                                <img class="avatar avatar-xxl brround" alt="img"
                                    src="{{ isset($data->fh_employee) && !empty($data->fh_employee->emp_profile_photo)
                                        ? $data->fh_employee->emp_profile_photo
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
                                            <span>{{ $data->fh_branch->br_name ?? 'N/A' }}</span>
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
                    <div class="card">
                        <div class="card-body border-0 p-0">
                            @if ($actionRoute != 'travel-request.request.show')
                                @if ($approvalData)
                                    <div class="card-header">
                                        <h3 class="card-title">Approval Or Reject</h3>
                                    </div>
                                    <div class="card-body">
                                        <form id="approvalForm">
                                            <div class="form-group">
                                                <div class="row">
                                                    <label class="form-label mb-0 mt-2">Message</label>
                                                    <div class="col-md-12 col-lg-12">
                                                        <textarea rows="2" name="message" class="form-control" id="actionMessage">Approved</textarea>
                                                    </div>
                                                </div>

                                                <div class="card-footer mt-3">
                                                    <div class="row">
                                                        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                            <button href="javascript:void(0);"
                                                                data-approval_status="{{ $approvalData->fh_approver_status->m_id }}"
                                                                data-approval_type="0"
                                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                                data-trp_id="{{ md5($data->trp_id) }}"
                                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                                data-emp_d_id="{{ optional($data->fh_employee)->emp_d_id }}"
                                                                class="btn btn-outline-danger  actionBtn mx-3">Reject</button>

                                                            <button href="javascript:void(0);"
                                                                data-approval_status="{{ $approvalData->fh_approver_status->m_id }}"
                                                                data-approval_type="1"
                                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                                data-trp_id="{{ md5($data->trp_id) }}"
                                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                                data-emp_d_id="{{ optional($data->fh_employee)->emp_d_id }}"
                                                                class="btn btn-success actionBtn">{{ $approvalData->fh_approver_status->m_name }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @elseif ($canApprove)
                                    <x-approval-form :moduleName="$data?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$data->trp_id" :moduleId="$data->trp_module_id"
                                        actionUrl="{{ route('approve.travel') }}" />
                                @endif
                            @endif
                        </div>
                    </div>
                    @if (count($data->fh_plan_approval_log) > 0)
                        <div class="d-flex gap-2 p-3">
                            <button class="btn btn-outline-primary btn-sm fw-bold" onclick="get_travel()">Travel Details</button>
                            <button class="btn btn-outline-primary btn-sm fw-bold" onclick="get_advance()">Advance Details</button>
                            <button class="btn btn-outline-primary btn-sm fw-bold" onclick="get_claim()">Claim Details</button>
                        </div>
                        <div class="card approval-details" id="travel_approval_details" style="display: none;">
                            <div class="card-header px-3">
                                <div class="card-title">Travel Approval Details</div>
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
                                            @if ($data->trp_stage_completed == 1)
                                                @foreach ($approvalLogTravel as $travelLog)
                                                    <tr>
                                                        <td class="col-md-4">
                                                            {{ $travelLog->fh_employee->emp_full_name ?? '' }}
                                                        </td>
                                                        <td class="col-md-4">{{ $travelLog->fh_status->m_name ?? '-' }}
                                                        </td>
                                                        <td class="col-md-4 text-nowrap">
                                                            {{ $travelLog->log_description ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                @foreach ($approvalDetailsTravel as $item)
                                                    @php
                                                        $log = $approvalLogTravel[$item->pa_emp_id] ?? null; // Get log if exists
                                                    @endphp
                                                    <tr>
                                                        <td class="col-md-4">{{ $item->fh_employee->emp_full_name ?? '' }}
                                                        </td>
                                                        <td class="col-md-4">{{ $log->fh_status->m_name ?? '-' }}</td>
                                                        <td class="col-md-4 text-nowrap">
                                                            {{ $log->log_description ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card approval-details" id="claim_approval_details" style="display: none;">
                            <div class="card-header px-3">
                                <div class="card-title">Claim Approval Details</div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-vcenter text-nowrap border-bottom">
                                        @if (!empty($data->fh_tada_claim))
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="col-md-4">Name</th>
                                                    <th class="col-md-4">Action</th>
                                                    <th class="col-md-4">Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($data->fh_tada_claim->tc_stage_completed == 1)
                                                    @foreach ($approvalLogClaim as $claimLog)
                                                        <tr>
                                                            <td class="col-md-4">
                                                                {{ $claimLog->fh_employee->emp_full_name ?? '' }}
                                                            </td>
                                                            <td class="col-md-4">{{ $claimLog->fh_status->m_name ?? '-' }}
                                                            </td>
                                                            <td class="col-md-4 text-nowrap">
                                                                {{ $claimLog->log_description ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    @foreach ($approvalDetailsClaim as $item)
                                                        @php
                                                            $log = $approvalLogClaim[$item->pa_emp_id] ?? null; // Get log if exists
                                                        @endphp
                                                        <tr>
                                                            <td class="col-md-4">
                                                                {{ $item->fh_employee->emp_full_name ?? '' }}
                                                            </td>
                                                            <td class="col-md-4">{{ $log->fh_status->m_name ?? '-' }}</td>
                                                            <td class="col-md-4 text-nowrap">
                                                                {{ $log->log_description ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        @else
                                            <tbody>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-5">
                                                        <div
                                                            class="d-flex flex-column align-items-center justify-content-center">
                                                            <span class="fw-semibold fs-5 text-secondary">Claim Data is Not
                                                                Available</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Advance Approval Modal -->
                        <div class="modal fade" id="advanceApprovalModal" tabindex="-1"
                            aria-labelledby="advanceApprovalLabel" aria-hidden="true" data-bs-backdrop="static"
                            data-bs-keyboard="false">

                            <div class="modal-dialog modal-dialog-centered"
                                style="max-width: fit-content; margin: 1.75rem auto;">
                                <div class="modal-content" style="min-width: 800px;">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="advanceApprovalLabel">Advance Approval Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-0">
                                        <div class="table-responsive" style="overflow-x: auto;">
                                            <table class="table table-bordered text-nowrap mb-0"
                                                style="white-space: nowrap; width: auto;">
                                                @if (count($data->fh_tada_advance_approval_log) > 0)
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>S.No.</th>
                                                            <th>Approvers</th>
                                                            <th>Status</th>
                                                            <th>Description</th>
                                                            <th>Requested</th>
                                                            <th>Reimbursed</th>
                                                            <th>Advance Remark</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- @foreach (collect($TadaAdvanceDetails)->sortBy('adl_id')->values() as $index => $advanceLog)
                                                            @php
                                                                $relatedApprovers = $approvalDetailsAdvance->filter(
                                                                    function ($approver) use ($advanceLog) {
                                                                        return $approver->pa_am_id ==
                                                                            $advanceLog['adl_am_id'];
                                                                    },
                                                                );

                                                                $logsForAdvance = $approvalLogAdvance->where(
                                                                    'log_request_id',
                                                                    $advanceLog['adl_id'],
                                                                );

                                                                // Split each part into its own list
                                                                $approverNames = [];
                                                                $statuses = [];
                                                                $descriptions = [];

                                                                foreach ($relatedApprovers as $approver) {
                                                                    $empName =
                                                                        optional($approver->fh_employee)
                                                                            ->emp_full_name ?? 'Unknown';
                                                                    $log = $logsForAdvance->firstWhere(
                                                                        'log_user_id',
                                                                        $approver->pa_emp_id,
                                                                    );

                                                                    $status =
                                                                        optional(optional($log)->fh_status)->m_name ??
                                                                        '-';
                                                                    $remark = $log->log_description ?? '-';

                                                                    $approverNames[] = $empName;
                                                                    $statuses[] = $status;
                                                                    $descriptions[] = $remark;
                                                                }

                                                                // Handle cases with no data
                                                                $approverColumn = count($approverNames)
                                                                    ? implode('<br>', $approverNames)
                                                                    : '<span class="text-muted">No approvers</span>';
                                                                $statusColumn = count($statuses)
                                                                    ? implode('<br>', $statuses)
                                                                    : '<span class="text-muted">-</span>';
                                                                $descColumn = count($descriptions)
                                                                    ? implode('<br>', $descriptions)
                                                                    : '<span class="text-muted">-</span>';
                                                            @endphp

                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td style="white-space: normal;">{!! $approverColumn !!}
                                                                </td>
                                                                <td style="white-space: normal;">{!! $statusColumn !!}
                                                                </td>
                                                                <td style="white-space: normal;">{!! $descColumn !!}
                                                                </td>
                                                                <td>₹{{ number_format($advanceLog['adl_requested_amount'] ?? 0, 2) }}
                                                                </td>
                                                                <td>₹{{ number_format($advanceLog['adl_reimburse_amount'] ?? 0, 2) }}
                                                                </td>
                                                                <td>{{ $advanceLog['adl_remark'] ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach --}}

                                                        @foreach (collect($TadaAdvanceDetails)->sortBy('adl_id')->values() as $index => $advanceLog)
                                                            @if ($advanceLog['adl_stage_completed'] == 1)
                                                                @php
                                                                    $approverNames = [];
                                                                    $statuses = [];
                                                                    $descriptions = [];

                                                                    $logsForAdvance = $approvalLogAdvance->where(
                                                                        'log_request_id',
                                                                        $advanceLog['adl_id'],
                                                                    );

                                                                    foreach ($logsForAdvance as $log) {
                                                                        $empName =
                                                                            optional($log->fh_employee)
                                                                                ->emp_full_name ?? 'Unknown';
                                                                        $status =
                                                                            optional($log->fh_status)->m_name ?? '-';
                                                                        $remark = $log->log_description ?? '-';

                                                                        $approverNames[] = $empName;
                                                                        $statuses[] = $status;
                                                                        $descriptions[] = $remark;
                                                                    }

                                                                    $approverColumn = count($approverNames)
                                                                        ? implode('<br>', $approverNames)
                                                                        : '<span class="text-muted">No approvers</span>';

                                                                    $statusColumn = count($statuses)
                                                                        ? implode('<br>', $statuses)
                                                                        : '<span class="text-muted">-</span>';

                                                                    $descColumn = count($descriptions)
                                                                        ? implode('<br>', $descriptions)
                                                                        : '<span class="text-muted">-</span>';
                                                                @endphp

                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td style="white-space: normal;">
                                                                        {!! $approverColumn !!}</td>
                                                                    <td style="white-space: normal;">
                                                                        {!! $statusColumn !!}</td>
                                                                    <td style="white-space: normal;">
                                                                        {!! $descColumn !!}</td>
                                                                    <td>₹{{ number_format($advanceLog['adl_requested_amount'] ?? 0, 2) }}
                                                                    </td>
                                                                    <td>₹{{ number_format($advanceLog['adl_reimburse_amount'] ?? 0, 2) }}
                                                                    </td>
                                                                    <td>{{ $advanceLog['adl_remark'] ?? '-' }}</td>
                                                                </tr>
                                                            @else
                                                                @php
                                                                    $relatedApprovers = $approvalDetailsAdvance->filter(
                                                                        function ($approver) use ($advanceLog) {
                                                                            return $approver->pa_am_id ==
                                                                                $advanceLog['adl_am_id'];
                                                                        },
                                                                    );

                                                                    $logsForAdvance = $approvalLogAdvance->where(
                                                                        'log_request_id',
                                                                        $advanceLog['adl_id'],
                                                                    );

                                                                    // Split each part into its own list
                                                                    $approverNames = [];
                                                                    $statuses = [];
                                                                    $descriptions = [];

                                                                    foreach ($relatedApprovers as $approver) {
                                                                        $empName =
                                                                            optional($approver->fh_employee)
                                                                                ->emp_full_name ?? 'Unknown';
                                                                        $log = $logsForAdvance->firstWhere(
                                                                            'log_user_id',
                                                                            $approver->pa_emp_id,
                                                                        );

                                                                        $status =
                                                                            optional(optional($log)->fh_status)
                                                                                ->m_name ?? '-';
                                                                        $remark = $log->log_description ?? '-';

                                                                        $approverNames[] = $empName;
                                                                        $statuses[] = $status;
                                                                        $descriptions[] = $remark;
                                                                    }

                                                                    // Handle cases with no data
                                                                    $approverColumn = count($approverNames)
                                                                        ? implode('<br>', $approverNames)
                                                                        : '<span class="text-muted">No approvers</span>';
                                                                    $statusColumn = count($statuses)
                                                                        ? implode('<br>', $statuses)
                                                                        : '<span class="text-muted">-</span>';
                                                                    $descColumn = count($descriptions)
                                                                        ? implode('<br>', $descriptions)
                                                                        : '<span class="text-muted">-</span>';
                                                                @endphp

                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td style="white-space: normal;">
                                                                        {!! $approverColumn !!}
                                                                    </td>
                                                                    <td style="white-space: normal;">
                                                                        {!! $statusColumn !!}
                                                                    </td>
                                                                    <td style="white-space: normal;">
                                                                        {!! $descColumn !!}
                                                                    </td>
                                                                    <td>₹{{ number_format($advanceLog['adl_requested_amount'] ?? 0, 2) }}
                                                                    </td>
                                                                    <td>₹{{ number_format($advanceLog['adl_reimburse_amount'] ?? 0, 2) }}
                                                                    </td>
                                                                    <td>{{ $advanceLog['adl_remark'] ?? '-' }}</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                @else
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-5">
                                                                <div
                                                                    class="d-flex flex-column align-items-center justify-content-center">
                                                                    <span class="fw-semibold fs-4 text-secondary">Advance
                                                                        Data is Not Available</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                @endif

                                            </table>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-danger"
                                            data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif (count($data->fh_approval_log2) > 0)
                        <div class="card ">
                            <div class="card-header px-3">
                                <div class="card-title">Travel Approval Details</div>
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
                                                    <td class="col-md-4">{{ $item->fh_employee->emp_full_name ?? '' }}
                                                    </td>
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
                        <div class="card-header">
                            <div>
                                <h4 class="card-title">Travel Plan</h4>
                            </div>
                        </div>
                        {{-- @dd($data); --}}
                        <div class="card-body pt-3">
                            <div class="row">
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Travel Id:</h6>
                                    <div class="ms-2">{{ $data->trp_unique_id ?? 'N/A' }}</div>
                                </div>
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Applied Date:</h6>
                                    <div class="ms-2">
                                        {{ $data->created_at ? $data->created_at->format('d-m-Y') : 'N/A' }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Travel Type:</h6>
                                    <div class="ms-2">
                                        {{ $data->fh_policy_tada_travel_type->fh_travel_type->m_name ?? 'N/A' }}
                                    </div>
                                </div>

                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Trip Name:</h6>
                                    <div class="ms-2">{{ $data->trp_name ?? 'N/A' }}</div>
                                </div>

                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Purpose:</h6>
                                    <div class="ms-2">{{ $data->fh_travel_purpose->tp_name ?? 'N/A' }}</div>
                                </div>
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Advance:</h6>
                                    <div class="ms-2">{{ $data->trp_advance_allowance ?? 'N/A' }}</div>
                                </div>
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Status:</h6>
                                    <div id="approvalBadge" class="badge ms-2"
                                        style="background-color:{{ $color }}">
                                        <i id="approvalIcon" class="{{ $icon }}">&nbsp;</i>
                                        {{ $data->fh_approval_status->m_name ?? 'N/A' }}
                                    </div>

                                    {{-- <div id="approvalBadge"  class="badge ms-2" style="background-color:{{$color}}"><i id="approvalIcon" class="{{$icon}}">&nbsp;</i>
                                        {{ $data->fh_approval_status->m_name ?? 'N/A' }}</div> --}}
                                </div>
                                @if ($data->trp_call_id != null)
                                    <div class="d-flex align-items-center mt-2 col-xl-4">
                                        <h6 class="mt-2">Call Id:</h6>
                                        <div class="ms-2">{{ $data->trp_call_id ?? 'N/A' }}</div>
                                    </div>
                                @endif
                                @if ($data->trp_remarks != null)
                                    <div class="d-flex align-items-center mt-2 col-xl-4">
                                        <h6 class="mt-2">Remarks:</h6>
                                        <div class="ms-2">{{ $data->trp_remarks ?? 'N/A' }}</div>
                                    </div>
                                @endif
                                @php
                                    $value = json_decode($data->trp_document) ?? 'N/A';
                                @endphp
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Documents:</h6>
                                    <div class="ms-2">
                                        @if (is_array($value) && count($value) > 0)
                                            <a href="#" type="button" data-bs-toggle="modal"
                                                data-bs-target="#documentsModaltrp_document" class="text-primary"><u>View
                                                    Documents</u></a>
                                            <!-- Modal -->
                                            @component('admin.components.document-modal', [
                                                'id' => 'trp_document',
                                                'documents' => $value,
                                                'componentString' => 'PlanDetail_',
                                            ])
                                            @endcomponent
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Travel Start :</h6>
                                    <div class="ms-2">
                                        {{ \Carbon\Carbon::parse($data->trp_start_date . ' ' . $data->trp_start_time)->format('d-M-Y h:i A') ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Travel End :</h6>
                                    <div class="ms-2">
                                        {{ \Carbon\Carbon::parse($data->trp_end_date . ' ' . $data->trp_end_time)->format('d-M-Y h:i A') ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-2 col-xl-4">
                                    <h6 class="mt-2">Destination:</h6>
                                    <div class="ms-2">{{ $data->trp_destination ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <ul class="text-start row">
                                @foreach ($value as $dkey => $document)
                                    @php
                                        $position = strpos($document, 'PlanDetail_');
                                        if ($position !== false) {
                                            $displayName = substr($document, $position);
                                        } else {
                                            // If "PlanDetail_" is not found, use the full file name
                                            $displayName = basename($document);
                                        }
                                    @endphp
                                    <li class="col-4"> <a href="{{ asset($document) }}" target="_blank"
                                            class="text-primary"> *
                                            {{ $displayName }} - View file {{ $dkey + 1 }},
                                        </a> </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @php
                    $groupedData = $data->fh_tada_request_details->groupBy(function ($item) {
                        return json_encode($item->fh_details_type); // Convert the object to a string
                    });
                @endphp
            </div>
            @if (count($groupedData) > 0)
                <div class="card">
                    <div class="panel panel-primary">
                        <div class="tab-menu-heading p-0">
                            <div class="tabs-menu1">
                                <!-- Tabs -->
                                <ul class="nav panel-tabs ">
                                    @foreach ($groupedData as $key => $details)
                                        @php
                                            $fh_details_type = json_decode($key); // Decode the string back to an object
                                        @endphp
                                        <li class="">
                                            <a href="#tab{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"
                                                data-bs-toggle="tab">
                                                {{ isset($fh_details_type->m_name) ? $fh_details_type->m_name : '' }}
                                                Details
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="panel-body tabs-menu-body">
                            <div class="tab-content">
                                @foreach ($groupedData as $key => $details)
                                    @php
                                        $fh_details_type = json_decode($key); // Decode the string back to an object
                                    @endphp
                                    <div class="tab-pane {{ $loop->first ? 'active' : '' }}"
                                        id="tab{{ $loop->index }}">
                                        <div class="row">
                                            <!-- Conditional Content -->
                                            @if ((isset($fh_details_type->m_id) ? $fh_details_type->m_id : '') == 182)
                                                <!-- Show data or elements specific to this condition -->
                                                @foreach ($details as $item)
                                                    <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-12">
                                                        <div class="card border p-0 shadow-none">
                                                            <div class="card-header">
                                                                <h3 class="card-title">Lodging {{ $loop->index + 1 }}</h3>
                                                            </div>
                                                            <div class="card-body pt-3">
                                                                <div class="teams">
                                                                    <div class="d-flex align-items-center">
                                                                        <h6 class="mt-2">Hotel Location:</h6>
                                                                        <div class="ms-2"><i
                                                                                class="feather feather-map-pin text-muted me-2"></i>
                                                                            {{ $item->trd_hotel_location ?? 'N/A' }}
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="h5 mb-1">
                                                                    <small class="text-muted"><i
                                                                            class="fa fa-calendar"></i>
                                                                        {{ $item->trd_start_date ? $item->trd_start_date->format('d-m-Y') ?? 'N/A' : 'N/A' }}
                                                                        <i class=" ms-3 fa fa-clock-o"></i>
                                                                        {{ $item->trd_start_time ?? 'N/A' }}</small>
                                                                    <span class="text-muted leave-to">To</span> <br>
                                                                    <small class="text-muted"><i
                                                                            class="fa fa-calendar"></i>
                                                                        {{ $item->trd_end_date ? $item->trd_end_date->format('d-m-Y') ?? 'N/A' : 'N/A' }}
                                                                        <i class=" ms-3 fa fa-clock-o"></i>
                                                                        {{ $item->trd_end_time ?? 'N/A' }}</small>
                                                                    @php
                                                                        $startDate = new DateTime(
                                                                            $item->trd_start_date,
                                                                        );
                                                                        $endDate = new DateTime($item->trd_end_date);

                                                                        $interval = $startDate->diff($endDate);

                                                                        $daysDifference = $interval->days + 1;
                                                                    @endphp
                                                                    <span
                                                                        class="badge badge-md badge-primary-light ms-1">{{ $daysDifference }}
                                                                        days</span>
                                                                </div>
                                                                <div class="teams">
                                                                    <div class="d-flex align-items-center mt-2">
                                                                        <h6 class="mt-2">Document:</h6>
                                                                        <div class="ms-2">
                                                                            @php
                                                                                $value =
                                                                                    json_decode($item->trd_documents) ??
                                                                                    'N/A';
                                                                            @endphp
                                                                            @if (is_array($value) && count($value) > 0)
                                                                                <a href="#" type="button"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#documentsModal{{ $loop->index }}"
                                                                                    class="text-primary"><u>View
                                                                                        Documents</u></a>
                                                                                <!-- Modal -->
                                                                                @component('admin.components.document-modal', [
                                                                                    'id' => $loop->index,
                                                                                    'documents' => $value,
                                                                                    'componentString' => 'PlanDetail_',
                                                                                ])
                                                                                @endcomponent
                                                                            @else
                                                                                N/A
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="teams">
                                                                    <div class="d-flex align-items-center mt-2">
                                                                        <h6 class="mt-2">Remark:</h6>
                                                                        <div class="ms-2">
                                                                            {{ $item->trd_remarks ?? 'N/A' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <!-- Show different data or elements based on this condition -->
                                                @foreach ($details as $item)
                                                    <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-12">
                                                        <div class="card border p-0 shadow-none">
                                                            <div class="card-header">
                                                                <h3 class="card-title">Travel {{ $loop->index + 1 }}</h3>
                                                            </div>
                                                            <div class="card-body pt-3">
                                                                <div class="teams">
                                                                    <div class="d-flex align-items-center">
                                                                        <h6 class="mt-2">Travel Vehicle:</h6>
                                                                        <div class="ms-2">
                                                                            {{ ($item->fh_policy_tada_travel_vehicle->fh_vehicle->m_name ?? 'N/A') . '(' . ($item->fh_policy_tada_travel_mode->fh_travel_mode->m_name ?? 'N/A') . ')' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if (empty($item->trd_segments))
                                                                    <div class="h5 mb-1">
                                                                        <small class="text-muted"><i
                                                                                class="fa fa-calendar"></i>
                                                                            {{ $item->trd_start_date ? $item->trd_start_date->format('d-m-Y') ?? ($data->trp_start_date ?? 'N/A') : $data->trp_start_date ?? 'N/A' }}
                                                                            <i class=" ms-3 fa fa-clock-o"></i>
                                                                            {{ $item->trd_start_time ?? ($data->trp_start_time ?? 'N/A') }}</small>
                                                                        <span class="text-muted leave-to">To</span> <br>
                                                                        <small class="text-muted"><i
                                                                                class="fa fa-calendar"></i>
                                                                            {{ $item->trd_end_date ? $item->trd_end_date->format('d-m-Y') ?? ($data->trp_end_date ?? 'N/A') : $data->trd_end_date ?? 'N/A' }}
                                                                            <i class=" ms-3 fa fa-clock-o"></i>
                                                                            {{ $item->trd_end_time ?? ($data->trp_end_time ?? 'N/A') }}</small>
                                                                        @php
                                                                            $startDate = new DateTime(
                                                                                $item->trd_start_date,
                                                                            );
                                                                            $endDate = new DateTime(
                                                                                $item->trd_end_date,
                                                                            );

                                                                            $interval = $startDate->diff($endDate);

                                                                            $daysDifference = $interval->days + 1;
                                                                        @endphp
                                                                        <span
                                                                            class="badge badge-md badge-primary-light ms-1">{{ $daysDifference }}
                                                                            days</span>
                                                                    </div>
                                                                    <ul class="timeline ">
                                                                        <li class="primary">
                                                                            <span
                                                                                class="font-weight-semibold fs-16 ms-3">Source</span>
                                                                            <p
                                                                                class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                {{ $item->trd_source ?? 'N/A' }}
                                                                            </p>
                                                                        </li>
                                                                        <li class="success mt-6">
                                                                            <span
                                                                                class="font-weight-semibold fs-16 ms-3">Destination</span>
                                                                            <p
                                                                                class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                {{ $item->trd_destination ?? 'N/A' }} </p>
                                                                        </li>
                                                                    </ul>
                                                                    <div class="teams">
                                                                        <div class="d-flex align-items-center mt-2">
                                                                            <h6 class="mt-2">Document:</h6>
                                                                            <div class="ms-2">
                                                                                @php
                                                                                    $value =
                                                                                        json_decode(
                                                                                            $item->trd_documents,
                                                                                        ) ?? 'N/A';
                                                                                @endphp
                                                                                @if (is_array($value) && count($value) > 0)
                                                                                    <a href="#" type="button"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#documentsModal{{ $loop->index }}"
                                                                                        class="text-primary"><u>View
                                                                                            Documents</u></a>
                                                                                    <!-- Modal -->
                                                                                    @component('admin.components.document-modal', [
                                                                                        'id' => $loop->index,
                                                                                        'documents' => $value,
                                                                                        'componentString' => 'PlanDetail_',
                                                                                    ])
                                                                                    @endcomponent
                                                                                @else
                                                                                    N/A
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="teams">
                                                                        <div class="d-flex align-items-center mt-2">
                                                                            <h6 class="mt-2">Remark:</h6>
                                                                            <div class="ms-2">
                                                                                {{ $item->trd_remarks ?? 'N/A' }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <div class="teams">
                                                                        <!--<div class="d-flex align-items-center">
                                                                            <h6 class="mt-2">Amount:</h6>
                                                                            <div class="ms-2">
                                                                                ({{ isset($item->trd_net_amount) ? '₹ ' . $item->trd_net_amount . ' /-' : 'N/A' }})
                                                                            </div>
                                                                        </div>-->
                                                                        <div class="d-flex align-items-center">
                                                                            @php
                                                                                $travel_distance = $item->trd_total_distance ?? 0;
                                                                                $eligibility = $item->fh_policy_tada_travel_allowance->pttv_eligibility ?? 0;
                                                                                $total_amount = $travel_distance > 0 ? ($travel_distance * $eligibility) : $eligibility;
                                                                            @endphp
                                                                            <h6 class="mt-2">
                                                                                Amount<small> ({{ $eligibility > 0 ? '₹' . number_format($eligibility, 2) . ' /km' : 'N/A' }}) </small>:
                                                                            </h6>
                                                                            <div class="ms-2">
                                                                                ₹ {{ number_format($total_amount, 2) }}
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex align-items-center">
                                                                            <h6 class="mt-2">Distance:</h6>
                                                                            <div class="ms-2">
                                                                                {{ isset($item->trd_total_distance) ? number_format($item->trd_total_distance, 2) . ' KM' : 'N/A' }}
                                                                                <!--{{ isset($lcTotalDistance) ? number_format($lcTotalDistance / 1000, 3) . ' KM' : 'N/A' }}-->
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h6 class="mt-2">Tap Location:</h6>
                                                                    <ul class="timeline"
                                                                        style="max-height: 200px; overflow-y: auto; ">
                                                                        @foreach (json_decode($item->trd_segments) as $location)
                                                                            <li
                                                                                class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }} my-0">
                                                                                <span
                                                                                    class="font-weight-semibold fs-16 ms-3">
                                                                                    {{ $location->time ? str_replace(['am', 'pm'], ['AM', 'PM'], $location->time) : 'N/A' }} (<small>{{ number_format($location->distance ?? 0, 2) }} KM</small>)
                                                                                </span>
                                                                                <p class="mb-0 pb-0 text-muted fs-12 ms-3">
                                                                                    {{ $location->latitude ?? 'N/A' }}
                                                                                    &nbsp;
                                                                                    {{ $location->longitude ?? 'N/A' }}
                                                                                    &nbsp;
                                                                                    ({{ $location->location ?? 'N/A' }})&nbsp;
                                                                                </p>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                    <button class="btn btn-info mt-3 travel-path-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#largemodal"
                                                                        data-locations='@json($item->trd_segments)'>
                                                                        View Travel Path
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>


                </div>
            @endif
        </div>
    </div>
    <!-- Button to Open Modal -->

    <!-- MODAL -->
    {{-- <div class="modal fade" id="largemodal" tabindex="-1" role="dialog" aria-labelledby="largemodalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="largemodalLabel">Employee Travel Path</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body">
                    <h2 class="text-center">Employee Travel Path</h2>
                    <div class="row">
                        <label for="travelModeSelect">Select Travel Mode:</label>
                            <select id="travelModeSelect">
                                <option value="DRIVING">Driving</option>
                                <option value="WALKING">Walking</option>
                                <option value="BICYCLING">Bicycling</option>
                                <option value="TRANSIT">Transit</option>
                            </select>

                            <!-- Trigger Route Button -->
                            <button id="drawRoute">Draw Route</button>
                        <div class="col-md-8">
                            <div id="map" style="height: 400px;"></div> <!-- Map container -->
                        </div>
                        <div class="col-md-4">
                            <h5>Employee Travel History</h5>
                            <ul class="list-group" id="locationList"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="modal fade" id="largemodal" tabindex="-1" role="dialog" aria-labelledby="largemodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="largemodalLabel">Employee Travel Path</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="location.reload()">×</button>
                </div>
                <div class="modal-body">
                    <!-- Left side: Map and controls -->
                    <div style="flex: 1 1 auto; display: flex; flex-direction: column; gap: 10px;">
                        <!-- <div id="travelModeButtons" class="btn-group" role="group" aria-label="Travel mode buttons">
                          <button type="button" class="btn btn-outline-primary active" data-mode="DRIVING">Driving</button>
                          <button type="button" class="btn btn-outline-primary" data-mode="WALKING">Walking</button>
                          <button type="button" class="btn btn-outline-primary" data-mode="BICYCLING">Bicycling</button>
                          <button type="button" class="btn btn-outline-primary" data-mode="TRANSIT">Transit</button>
                        </div> -->
                        <div id="map"></div>
                    </div>

                    <!-- Right side: Employee travel path list -->
                    <div id="travelPathContainer">
                        <h5>Employee Travel History</h5>
                        <ul id="locationList"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"  onclick="location.reload()">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    {{-- <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script> --}}
    <!-- <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script> -->
    <!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBept2iO1bVOoeXiM5_oy5H9Zv0FtDWo-Y&callback=initMap"></script> -->
    <!-- <script>
        $(document).ready(function() {
            var map = null; // Store map instance

            $('#largemodal').on('shown.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var locations = button.data('locations'); // Retrieve travel path data
                // console.log("locations", locations);

                if (!locations || locations.length === 0) {
                    alert("No travel data available.");
                    return;
                }

                // Parse JSON if needed
                var travelLocations = typeof locations === "string" ? JSON.parse(locations) : locations;

                // Remove previous map instance if it exists
                if (map !== null) {
                    map.remove();
                }

                // If travelLocations is still a string, parse it
                if (typeof travelLocations === "string") {
                    try {
                        travelLocations = JSON.parse(travelLocations);
                    } catch (error) {
                        console.error("Error parsing travelLocations JSON:", error);
                        return;
                    }
                }

                // Confirm it's an array
                if (!Array.isArray(travelLocations)) {
                    console.error("travelLocations is not an array after parsing:", travelLocations);
                    return;
                }

                if (!travelLocations[0] || !travelLocations[0].latitude || !travelLocations[0].longitude) {
                    console.error("Invalid location data format:", travelLocations[0]);
                    return;
                }
                map = L.map('map').setView([travelLocations[0].latitude, travelLocations[0].longitude], 6);

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Clear previous location list
                $('#locationList').empty();

                // Store travel path coordinates
                var travelPath = [];
                // var arrowIcon = L.icon({
                //     iconUrl: '{{asset("assets/images/navigation.png")}}',
                //     iconSize: [24, 24], // Adjust as needed
                //     iconAnchor: [12, 12], // Center the icon
                //     popupAnchor: [0, -12] // Adjust if needed
                // });

                // Add markers and populate the list
                travelLocations.forEach(function(location, index) {
                    var marker = L.marker([location.latitude, location.longitude]).addTo(map).bindPopup(`<b>${location.location ?? "Unknown Location"}</b>`);
                    // var marker = L.marker([location.latitude, location.longitude], { icon: arrowIcon }).addTo(map).bindPopup(`<b>${location.location ?? "Unknown Location"}</b>`);

                    travelPath.push([location.latitude, location.longitude]);

                    // Append location details to the list
                    $('#locationList').append(`
                <li class="list-group-item">
                    <strong>${index + 1}. ${location.location ?? "Unknown"}</strong>
                    (${location.latitude}, ${location.longitude})
                </li>`);
                });

                // Draw polyline (path)
                var polyline = L.polyline(travelPath, {
                    color: 'blue',
                    weight: 4
                }).addTo(map);

                // Adjust map view to fit all markers and path
                map.fitBounds(polyline.getBounds());
            });
        });
    </script> -->

    <!-- Google Maps JS API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBept2iO1bVOoeXiM5_oy5H9Zv0FtDWo-Y&callback=initMap"></script>

<!-- <script>
    let map;
    let directionsService;
    let directionsRenderer;
    let bounds;

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 14,
            center: { lat: 0, lng: 0 },
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true,
            polylineOptions: { strokeColor: 'blue' }
        });

        bounds = new google.maps.LatLngBounds();
    }

    $(document).ready(function () {
        $('#largemodal').on('shown.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            let locations = button.data('locations');

            // Parse if it's a string
            if (typeof locations === "string") {
                try {
                    while (typeof locations === "string") {
                        locations = JSON.parse(locations);
                    }
                } catch (e) {
                    console.error("Invalid travel data format:", e);
                    return;
                }
            }

            if (!Array.isArray(locations) || locations.length === 0) {
                alert("No travel data available.");
                return;
            }

            // Clear location list
            $('#locationList').empty();
            bounds = new google.maps.LatLngBounds();

            // Display location list
            locations.forEach((loc, index) => {
                $('#locationList').append(`
                    <li class="list-group-item">
                        <strong>${index + 1}. ${loc.location ?? "Unknown"}</strong>
                        (${loc.latitude}, ${loc.longitude})
                    </li>`);
            });

            // Clear old markers if needed (optional)

            // Plot Markers
            locations.forEach((loc, index) => {
                const position = new google.maps.LatLng(loc.latitude, loc.longitude);
                bounds.extend(position);

                let iconColor = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"; // Default
                if (index === 0) {
                    iconColor = "http://maps.google.com/mapfiles/ms/icons/green-dot.png"; // Start
                } else if (index === locations.length - 1) {
                    iconColor = "http://maps.google.com/mapfiles/ms/icons/red-dot.png"; // End
                }

                new google.maps.Marker({
                    position: position,
                    map: map,
                    title: loc.location ?? `Point ${index + 1}`,
                    icon: iconColor
                });
            });

            map.fitBounds(bounds);

            // Draw Route
            if (locations.length >= 2) {
                const origin = new google.maps.LatLng(locations[0].latitude, locations[0].longitude);
                const destination = new google.maps.LatLng(locations[locations.length - 1].latitude, locations[locations.length - 1].longitude);
                const waypoints = locations.slice(1, -1).map(loc => ({
                    location: new google.maps.LatLng(loc.latitude, loc.longitude),
                    stopover: true
                }));

                directionsService.route({
                    origin: origin,
                    destination: destination,
                    waypoints: waypoints,
                    travelMode: google.maps.TravelMode.DRIVING
                }, function (response, status) {
                    console.log("Route status:", status);
                    console.log("Route response:", response);

                    if (status === 'OK') {
                        directionsRenderer.setDirections(response);
                    } else {
                        console.error("Directions request failed:", status);
                    }
                });
            }
        });
    });
</script> -->

<script>
    let map;
    let directionsService;
    let directionsRenderer;
    let bounds;

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 14,
            center: { lat: 0, lng: 0 },
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: 'blue',
                strokeWeight: 5
            }
        });

        bounds = new google.maps.LatLngBounds();
    }

    function getSelectedTravelMode() {
        const activeBtn = document.querySelector('#travelModeButtons .btn.active');
        return activeBtn ? activeBtn.dataset.mode : 'DRIVING';
    }

    function drawRoute(locations, travelMode = 'DRIVING') {
        if (locations.length < 2) return;

        const origin = new google.maps.LatLng(locations[0].latitude, locations[0].longitude);
        const destination = new google.maps.LatLng(locations[locations.length - 1].latitude, locations[locations.length - 1].longitude);
        directionsService.route({
            origin: origin,
            destination: destination,
            travelMode: google.maps.TravelMode[travelMode]
        }, function (response, status) {
            if (status === 'OK') {
                directionsRenderer.setDirections(response);
            } else {
                alert('Directions request failed: ' + status);
            }
        });
    }

    $(document).ready(function () {
        $('#largemodal').on('shown.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            let locations = button.data('locations');

            if (typeof locations === "string") {
                try {
                    while (typeof locations === "string") {
                        locations = JSON.parse(locations);
                    }
                } catch (e) {
                    console.error("Invalid travel data format:", e);
                    alert("Invalid travel data format.");
                    return;
                }
            }

            if (!Array.isArray(locations) || locations.length === 0) {
                alert("No travel data available.");
                return;
            }

            $('#locationList').empty();
            bounds = new google.maps.LatLngBounds();
            map && directionsRenderer.set('directions', null); // Clear old route

            /*locations.forEach((loc, index) => {
                const position = new google.maps.LatLng(loc.latitude, loc.longitude);

                // let iconColor = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
                // if (index === 0) iconColor = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
                // else if (index === locations.length - 1) iconColor = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";

                // new google.maps.Marker({
                //     position: position,
                //     map: map,
                //     title: loc.location ?? `Point ${index + 1}`,
                //     icon: iconColor
                // });

                // Only add marker if it's the first or last location
                if (index === 0 || index === locations.length - 1) {
                    let iconColor = index === 0
                        ? "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
                        : "http://maps.google.com/mapfiles/ms/icons/red-dot.png";

                    new google.maps.Marker({
                        position: position,
                        map: map,
                        title: loc.location ?? `Point ${index + 1}`,
                        icon: iconColor
                    });
                }

                $('#locationList').append(`
                    <li class="list-group-item">
                        <strong>${index + 1}. ${loc.location ?? "Unknown"}</strong> (${loc.latitude}, ${loc.longitude})
                    </li>
                `);
            }); */


            /*let pathCoordinates = [];

            locations.forEach((loc, index) => {
                const position = new google.maps.LatLng(loc.latitude, loc.longitude);

                pathCoordinates.push(position);

                let iconColor = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
                if (index === 0) iconColor = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
                else if (index === locations.length - 1) iconColor = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";

                new google.maps.Marker({
                    position: position,
                    map: map,
                    title: loc.location ?? `Point ${index + 1}`,
                    icon: iconColor
                });

                $('#locationList').append(`
                    <li class="list-group-item">
                        <strong>${index + 1}. ${loc.location ?? "Unknown"}</strong> (${loc.latitude}, ${loc.longitude})
                    </li>
                `);
            });

            const pathLine = new google.maps.Polyline({
                path: pathCoordinates,
                geodesic: true,
                strokeColor: "#0000FF",
                strokeOpacity: 1.0,
                strokeWeight: 5
            });


            // Set the polyline on the map
            pathLine.setMap(map);


            map.fitBounds(bounds);

            // Automatically draw route with selected travel mode
            const selectedMode = getSelectedTravelMode();
            drawRoute(locations, selectedMode);*/

          /*
            let pathCoordinates = [];

            locations.forEach((loc, index) => {
                const position = new google.maps.LatLng(loc.latitude, loc.longitude);
                pathCoordinates.push(position);

                // Set marker icon and title
                let iconColor;
                let title;

                if (index === 0) {
                    iconColor = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
                    title = `Start: ${loc.location ?? `Point ${index + 1}`}`;
                } else if (index === locations.length - 1) {
                    iconColor = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
                    title = `End: ${loc.location ?? `Point ${index + 1}`}`;
                } else {
                    iconColor = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
                    title = loc.location ?? `Point ${index + 1}`;
                }

                // Add marker
                new google.maps.Marker({
                    position: position,
                    map: map,
                    title: title,
                    icon: iconColor
                });

                // Add to location list
                $('#locationList').append(`
                    <li class="list-group-item">
                        <strong>${index + 1}. ${title}</strong> (${loc.latitude}, ${loc.longitude})
                    </li>
                `);
            });

            // Optional: REMOVE polyline if Directions API is used
            // If you prefer the real route instead of straight lines, comment out below
            const pathLine = new google.maps.Polyline({
                path: pathCoordinates,
                geodesic: true,
                strokeColor: "#0000FF",
                strokeOpacity: 1.0,
                strokeWeight: 5
            });
            // pathLine.setMap(map);

            // Fit map to bounds (make sure bounds is defined elsewhere)
            // map.fitBounds(bounds);

            // Draw route using Directions API
            const selectedMode = getSelectedTravelMode();
            drawRoute(locations, selectedMode);

            function drawRoute(locations, selectedMode) {
                if (locations.length < 2) return;

                const start = locations[0];
                const end = locations[locations.length - 1];

                const waypoints = locations.slice(1, locations.length - 1).map(loc => ({
                    location: new google.maps.LatLng(loc.latitude, loc.longitude),
                    stopover: true
                }));

                const directionsService = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    suppressMarkers: true, // Use custom markers only
                    polylineOptions: {
                        strokeColor: "#0000FF", // Blue
                        strokeOpacity: 1.0,
                        strokeWeight: 5
                    }
                });
                directionsRenderer.setMap(map);

                directionsService.route(
                    {
                        origin: new google.maps.LatLng(start.latitude, start.longitude),
                        destination: new google.maps.LatLng(end.latitude, end.longitude),
                        waypoints: waypoints,
                        travelMode: google.maps.TravelMode[selectedMode],
                        optimizeWaypoints: false
                    },
                    (response, status) => {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);
                        } else {
                            console.error('Directions request failed due to ' + status);
                        }
                    }
                );
            }*/

           /* // Count show
            let pathCoordinates = [];

            locations.forEach((loc, index) => {
                const position = new google.maps.LatLng(loc.latitude, loc.longitude);
                pathCoordinates.push(position);

                // Label for marker
                const labelText = `${index + 1}`;
                let title = loc.location ?? `Point ${labelText}`;

                // Add marker with number label
                new google.maps.Marker({
                    position: position,
                    map: map,
                    title: title,
                    label: {
                        text: labelText,
                        color: "white",
                        fontSize: "12px",
                        fontWeight: "bold"
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 16,
                        fillColor: index === 0 ? "green" : (index === locations.length - 1 ? "red" : "blue"),
                        fillOpacity: 1,
                        strokeWeight: 1,
                        strokeColor: "#ffffff"
                    }
                });

                // Add to location list
                $('#locationList').append(`
                    <li class="list-group-item">
                        <strong>${labelText}. ${title}</strong> (${loc.latitude}, ${loc.longitude})
                    </li>
                `);
            });

            // Optional: straight line polyline (disable if using Directions API)
            const pathLine = new google.maps.Polyline({
                path: pathCoordinates,
                geodesic: true,
                strokeColor: "#0000FF",
                strokeOpacity: 1.0,
                strokeWeight: 5
            });
            // pathLine.setMap(map); // Optional

            // Draw route using Directions API
            const selectedMode = getSelectedTravelMode();
            drawRoute(locations, selectedMode);

            function drawRoute(locations, selectedMode) {
                if (locations.length < 2) return;

                const start = locations[0];
                const end = locations[locations.length - 1];

                const waypoints = locations.slice(1, locations.length - 1).map(loc => ({
                    location: new google.maps.LatLng(loc.latitude, loc.longitude),
                    stopover: true
                }));

                const directionsService = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    suppressMarkers: true, // We use custom numbered markers
                    polylineOptions: {
                        strokeColor: "#0000FF",
                        strokeOpacity: 1.0,
                        strokeWeight: 5
                    }
                });
                directionsRenderer.setMap(map);

                directionsService.route(
                    {
                        origin: new google.maps.LatLng(start.latitude, start.longitude),
                        destination: new google.maps.LatLng(end.latitude, end.longitude),
                        waypoints: waypoints,
                        travelMode: google.maps.TravelMode[selectedMode],
                        optimizeWaypoints: false
                    },
                    (response, status) => {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);
                        } else {
                            console.error('Directions request failed due to ' + status);
                        }
                    }
                );
            }*/

            /*
            Today Comment - 25-07-2025
            let pathCoordinates = [];

            // Check if route is circular
            const isCircular = locations.length >= 2 &&
                locations[0].latitude === locations[locations.length - 1].latitude &&
                locations[0].longitude === locations[locations.length - 1].longitude;

            // Function to slightly offset duplicate coordinates
            const getOffsetPosition = (lat, lng, index) => {
                const offset = 0.0015; // Approximately 100 meters
                const angle = (index * 30) * (Math.PI / 180);
                return new google.maps.LatLng(
                    lat + (offset * Math.sin(angle)),
                    lng + (offset * Math.cos(angle))
                );
            };

            // Track duplicate positions
            const positionCount = {};

            locations.forEach((loc, index) => {
                if (isCircular && index === locations.length - 1) return;

                const originalPosition = new google.maps.LatLng(loc.latitude, loc.longitude);
                const positionKey = `${loc.latitude},${loc.longitude}`;

                // Count how many times this position appears
                positionCount[positionKey] = (positionCount[positionKey] || 0) + 1;

                // Use original position for path, potentially offset position for marker
                pathCoordinates.push(originalPosition);
                const markerPosition = positionCount[positionKey] > 1 ?
                    getOffsetPosition(loc.latitude, loc.longitude, positionCount[positionKey]) :
                    originalPosition;

                // Label for marker
                const labelText = `${index + 1}`;
                let title = loc.location ?? `Point ${labelText}`;

                // Add marker with number label
                new google.maps.Marker({
                    position: markerPosition,
                    map: map,
                    title: title,
                    label: {
                        text: labelText,
                        color: "white",
                        fontSize: "12px",
                        fontWeight: "bold"
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 16,
                        fillColor: index === 0 ? "green" : (index === locations.length - 1 && !isCircular ? "red" : "blue"),
                        fillOpacity: 1,
                        strokeWeight: 1,
                        strokeColor: "#ffffff"
                    }
                });

                // Add to location list
               let formattedTime = loc.time.replace(/\b(am|pm)\b/i, match => match.toUpperCase());
                $('#locationList').append(`
                    <li class="list-group-item">
                        <strong>${labelText}. ${title}</strong> (${loc.latitude}, ${loc.longitude})<br>
                        <span class="badge bg-info text-white">Time: ${formattedTime}</span>
                        <span class="badge bg-secondary">Distance: ${(loc.distance).toFixed(2)} KM</span>
                    </li>
                `);
            });*/

            // Store markers in an array
            const markers = [];
            let pathCoordinates = [];

            // Check if route is circular
            const isCircular = locations.length >= 2 &&
                locations[0].latitude === locations[locations.length - 1].latitude &&
                locations[0].longitude === locations[locations.length - 1].longitude;

            // Function to slightly offset duplicate coordinates
            const getOffsetPosition = (lat, lng, index) => {
                const offset = 0.0015; // Approximately 100 meters
                const angle = (index * 30) * (Math.PI / 180);
                return new google.maps.LatLng(
                    lat + (offset * Math.sin(angle)),
                    lng + (offset * Math.cos(angle))
                );
            };

            // Track duplicate positions
            const positionCount = {};

            // Clear previous locations list
            $('#locationList').empty();

            locations.forEach((loc, index) => {
                if (isCircular && index === locations.length - 1) return;

                const originalPosition = new google.maps.LatLng(loc.latitude, loc.longitude);
                const positionKey = `${loc.latitude},${loc.longitude}`;

                // Count how many times this position appears
                positionCount[positionKey] = (positionCount[positionKey] || 0) + 1;

                // Use original position for path, potentially offset position for marker
                pathCoordinates.push(originalPosition);
                const markerPosition = positionCount[positionKey] > 1 ?
                    getOffsetPosition(loc.latitude, loc.longitude, positionCount[positionKey]) :
                    originalPosition;

                // Label for marker
                const labelText = `${index + 1}`;
                let title = loc.location ?? `Point ${labelText}`;

                // Create marker with appropriate color
                const marker = new google.maps.Marker({
                    position: markerPosition,
                    map: map,
                    title: title,
                    label: {
                        text: labelText,
                        color: "white",
                        fontSize: "12px",
                        fontWeight: "bold"
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 16,
                        fillColor: index === 0 ? "green" : (index === locations.length - 1 && !isCircular ? "red" : "blue"),
                        fillOpacity: 1,
                        strokeWeight: 1,
                        strokeColor: "#ffffff"
                    },
                    zIndex: index  // Ensure markers stack properly
                });

                // Store marker reference
                markers.push(marker);

                // Format time (AM/PM in uppercase)
                let formattedTime = loc.time.replace(/\b(am|pm)\b/i, match => match.toUpperCase());

                // Create list item with click handler
                const listItem = $(`
                    <li class="list-group-item location-item" data-index="${index}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${labelText}. ${title}</strong><br>
                                <small class="text-muted">${loc.latitude}, ${loc.longitude}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-info text-white">${formattedTime}</span><br>
                                <span class="badge bg-secondary">${(loc.distance).toFixed(2)} KM</span>
                            </div>
                        </div>
                    </li>
                `);

                // Add click handler to list item
                listItem.on('click', function() {
                    handleLocationClick(index, $(this));
                });

                // Add mouseover/mouseout effects
                listItem.hover(
                    function() {
                        $(this).css('background-color', '#f8f9fa');
                        markers[index].setIcon({
                            ...markers[index].getIcon(),
                            scale: 18,  // Slightly enlarge on hover
                            fillOpacity: 0.9
                        });
                    },
                    function() {
                        if (!$(this).hasClass('active')) {
                            $(this).css('background-color', '');
                        }
                        markers[index].setIcon({
                            ...markers[index].getIcon(),
                            scale: $(this).hasClass('active') ? 20 : 16,
                            fillOpacity: 1
                        });
                    }
                );

                $('#locationList').append(listItem);
            });

            // Function to handle location clicks
            function handleLocationClick(index, listItem) {
                // Remove active class from all items
                $('.location-item').removeClass('active');

                // Reset all markers to default appearance
                markers.forEach(marker => {
                    const icon = marker.getIcon();
                    marker.setIcon({
                        ...icon,
                        fillColor: getDefaultMarkerColor(marker),
                        scale: 16
                    });
                });

                // Add active class to clicked item
                listItem.addClass('active');

                // Get corresponding marker
                const clickedMarker = markers[index];

                // Highlight the marker (make it larger and yellow)
                clickedMarker.setIcon({
                    ...clickedMarker.getIcon(),
                    fillColor: '#ffc107',  // Yellow for highlight
                    scale: 20,             // Larger size
                    strokeWeight: 2        // Thicker border
                });

                // Center map on marker with padding for sidebar
                map.panTo(clickedMarker.getPosition());
            }

            // Helper function to get default marker color
            function getDefaultMarkerColor(marker) {
                const index = markers.indexOf(marker);
                if (index === 0) return "green";
                if (index === markers.length - 1 && !isCircular) return "red";
                return "blue";
            }


            // Draw route using Directions API
            const selectedMode = getSelectedTravelMode();
            drawRoute(locations, selectedMode, isCircular);

            function drawRoute(locations, selectedMode, isCircular) {
                if (locations.length < 2) return;

                const start = locations[0];
                const end = isCircular ? locations[0] : locations[locations.length - 1];

                const waypointSliceEnd = isCircular ? locations.length - 1 : locations.length - 1;

                const waypoints = locations.slice(1, waypointSliceEnd).map(loc => ({
                    location: new google.maps.LatLng(loc.latitude, loc.longitude),
                    stopover: true
                }));

                const directionsService = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    suppressMarkers: true,
                    polylineOptions: {
                        strokeColor: "#0000FF",
                        strokeOpacity: 1.0,
                        strokeWeight: 5
                    }
                });
                directionsRenderer.setMap(map);

                directionsService.route(
                    {
                        origin: new google.maps.LatLng(start.latitude, start.longitude),
                        destination: new google.maps.LatLng(end.latitude, end.longitude),
                        waypoints: waypoints,
                        travelMode: google.maps.TravelMode[selectedMode],
                        optimizeWaypoints: false
                    },
                    (response, status) => {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);
                        } else {
                            // console.error('Directions request failed due to ' + status);
                            new google.maps.Polyline({
                                path: pathCoordinates,
                                geodesic: true,
                                strokeColor: "#0000FF",
                                strokeOpacity: 1.0,
                                strokeWeight: 5,
                                map: map
                            });
                        }
                    }
                );
            }
        });

        // Update route on travel mode change
        $('#travelModeButtons .btn').on('click', function () {
            $('#travelModeButtons .btn').removeClass('active');
            $(this).addClass('active');

            const selectedMode = $(this).data('mode');
            const button = $('#largemodal').data('bs.modal')?._element;
            let locations = $(button).data('locations');

            if (typeof locations === "string") {
                try {
                    while (typeof locations === "string") {
                        locations = JSON.parse(locations);
                    }
                } catch (e) {
                    return;
                }
            }

            if (Array.isArray(locations) && locations.length >= 2) {
                drawRoute(locations, selectedMode);
            }
        });
    });
</script>

    <script>
        $(document).ready(function() {
            // window.Echo.private("travel-approval.{{ md5($data->trp_id) }}").listen('ApprovalEvent', (event) => {
            //     if((event.current_approver_id == "{{ md5(Auth::user()->emp_id) }}" || event.next_approver_id == "{{ md5(Auth::user()->emp_id) }}") && event.request_id == "{{ md5($data->trp_id) }}"){
            //         var baseUrl = $('#ajaxCall').val();
            //         window.location.href = baseUrl + "/admin/ta-da-request/travel/show/{{ md5($data->trp_id) }}";
            //     }
            // });


            window.Echo.private("travel-approval.{{ $data->trp_id }}").listen('TravelApprovalEvent', (
                event) => {
                // console.log('Travel Approval Event:', event);
                // alert("Travel approval has been updated!");
                $.ajax({
                    url: '{{ route($actionRoute, ['id' => md5($data->trp_id)]) }}',
                    method: 'GET',
                    beforeSend: function() {
                        console.log('Reverb Calling Request...');
                    },
                    success: function(response) {
                        // alert("workit");
                        // console.log('Reverb Response Received...', response);
                        $('#reverbDynamicSection').html(response.html);
                        // Update badge color by ID
                        $('#approvalBadge').css('background-color', response.color);

                        // Update icon by ID
                        $('#approvalIcon').attr('class', response
                            .icon); // Update the icon class

                        // Update text by ID
                        $('#approvalBadge').text(response.trp_request_status_name || 'N/A');
                    },
                    error: function(xhr, status, error) {
                        console.log('Error:', error);
                        alert('An error occurred while processing your request.');
                    },
                    complete: function() {
                        // This function runs after the request (success or failure)
                        console.log('Request completed');
                    }
                });
            });
        });

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

            planId = dataAttributes["trp_id"];

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
                    POST_TYPE: 'TRAVEL_REQUEST_APPROVAL',
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
                        window.location.href = baseUrl + "/admin/ta-da-request/travel/show/" + planId;
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
    <script>
        function get_travel() {
            $(".approval-details").hide(); // Hide all cards
            $("#travel_approval_details").toggle(); // Show the selected one
        }

        function get_claim() {
            $(".approval-details").hide(); // Hide all cards
            $("#claim_approval_details").toggle(); // Show the selected one
        }

        function get_advance() {
            $(".approval-details").hide(); // Hide all cards
            var modal = new bootstrap.Modal(document.getElementById('advanceApprovalModal'));
            modal.show();
        }
    </script>
    <!-- <script>
        let map;
        let directionsService;
        let directionsRenderer;
        let bounds;

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: { lat: 0, lng: 0 }, // Will be updated dynamically
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                suppressMarkers: true,
                polylineOptions: {
                    strokeColor: 'blue',
                    strokeWeight: 5
                }
            });

            bounds = new google.maps.LatLngBounds();
        }

        $(document).ready(function () {
            $('#largemodal').on('shown.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                let locations = button.data('locations');

                // Parse if it's a string
                if (typeof locations === "string") {
                    try {
                        while (typeof locations === "string") {
                            locations = JSON.parse(locations);
                        }
                    } catch (e) {
                        console.error("Invalid travel data format:", e);
                        alert("Invalid travel data format.");
                        return;
                    }
                }

                if (!Array.isArray(locations) || locations.length === 0) {
                    alert("No travel data available.");
                    return;
                }

                // Clear location list & bounds
                $('#locationList').empty();
                bounds = new google.maps.LatLngBounds();

                // Plot markers for all locations and add to list
                locations.forEach((loc, index) => {
                    const position = new google.maps.LatLng(loc.latitude, loc.longitude);
                    bounds.extend(position);

                    let iconColor = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
                    if (index === 0) iconColor = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
                    else if (index === locations.length - 1) iconColor = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";

                    new google.maps.Marker({
                        position: position,
                        map: map,
                        title: loc.location ?? `Point ${index + 1}`,
                        icon: iconColor
                    });

                    $('#locationList').append(`
                        <li class="list-group-item">
                            <strong>${index + 1}. ${loc.location ?? "Unknown"}</strong> (${loc.latitude}, ${loc.longitude})
                        </li>
                    `);
                });

                map.fitBounds(bounds);

                // Draw route only between first and last location (no waypoints)
                if (locations.length >= 2) {
                    const origin = new google.maps.LatLng(locations[0].latitude, locations[0].longitude);
                    const destination = new google.maps.LatLng(locations[locations.length - 1].latitude, locations[locations.length - 1].longitude);

                    directionsService.route({
                        origin: origin,
                        destination: destination,
                        travelMode: google.maps.TravelMode.DRIVING // you can make this dynamic if needed
                    }, function (response, status) {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);
                        } else {
                            alert('Directions request failed: ' + status);
                        }
                    });
                }
            });
        });
    </script> -->
@endsection
