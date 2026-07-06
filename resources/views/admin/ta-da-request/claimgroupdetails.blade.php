@php
    use Carbon\Carbon;
@endphp
@extends('admin.layout.master')
@section('title')
    Claim
@endsection
@section('css')
 <!-- Leaflet CSS -->
 <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
 <style>
        #map {
            height: 500px;
            border-radius: 10px;
        }

        .outer-border {
            border-radius: 8px;
            padding: 12px;
        }

        .acc-header a {
           padding: 12px;
           margin-top: 0px !important;
        }

        .outer-border:nth-child(odd) {
            border: 2px solid #1f1ced;
        }

        .outer-border:nth-child(even) {
            border: 2px solid #34e0f0;
        }

        body {
            background-color: #f7f8fa;
            font-family: 'Arial', sans-serif;
            font-size: 12px;
        }

        .card {
            border-radius: 8px #1f1ced;
        }

        .cardBody {
            border-radius: 8px #0e0aee !important;
            padding: 15px;
        }

        .nav-tabs .nav-link.active {
            background-color: #90b5ffc6;
            border-color: #e9ecef;
            color: #040303;
        }


        .badge-success {
            background-color: #28a745 !important;
            top: -13px;
            left: 20px;
            font-size: 18px;
        }

        .badge-claim {
            top: -13px;
            left: 20px;
            font-size: 18px;
            overflow: hidden;
        }


        .text-secondary {
            color: #263879 !important;

        }

        .mb-3 {
            margin-bottom: 1.5rem !important;
            margin-top: 1.1rem;
        }

        .info-container {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            padding: 15px;
        }


        .info-container:nth-child(odd) {
            background: linear-gradient(0.25turn, #faf7d4, #fde199);
        }

        .info-container:nth-child(even) {
            background: linear-gradient(0.25turn, #e4f8e8, #a0e4dd);
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .info-label {
            flex-shrink: 0;
            width: 150px;
            font-size: 17px;
            text-align: left;
        }

        .info-value {
            flex-grow: 1;
            word-wrap: break-word;
            font-size: 16px;
            text-align: left;
            font-weight: bold;
        }

        .track-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 1000px;
            width: 100%;
        }

        .track-card h3 {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .transaction {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }

        .transaction:last-child {
            border-bottom: none;
        }

        .transaction-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .transaction-header .icon {
            font-size: 24px;
            color: #007bff;
            margin-right: 10px;
        }

        .transaction-info {
            display: flex;
            flex-direction: column;
        }

        .transaction-id {
            font-weight: bold;
            color: #333;
        }

        .transaction-date {
            font-size: 0.9em;
            color: #777;
        }

        .transaction-body {
            padding-left: 34px;
        }

        .transaction-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .transaction-detail .label {
            font-weight: bold;
            color: #555;
        }

        .transaction-detail .value {
            font-weight: bold;
        }

        .status-success {
            color: #28a745;
        }

        .status-pending {
            color: #ffc107;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .track-card {
                padding: 15px;
            }

            .transaction-header .icon {
                font-size: 20px;
            }

            .transaction-detail {
                font-size: 0.9em;
            }
        }

        .animated-border {
            position: relative;
            padding: 0;
            background-color: #fff;
            border-radius: 10px;
            border: 2px solid #1f1ced;
            /* Added solid border */
            overflow: hidden;
            z-index: 1;
        }

        .animated-border::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(90deg, #eafcff, #c0d3ff, #eafcff, #c0d3ff);
            background-size: 420% 420%;
            z-index: -1;
            border-radius: 15px;
            animation: gradientAnimation 10s ease infinite;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .example-4 {
            outline-width: 1px;
            outline-offset: 0;
            outline-color: rgba(0, 130, 206, 0.75);
            outline-style: solid;
            animation: animateOutline 4s ease infinite;
        }

        @keyframes animateOutline {
            0% {
                outline-width: 1px;
                outline-offset: 0;
                outline-color: rgba(0, 130, 206, 0);
            }

            10% {
                outline-color: rgba(0, 130, 206, 0.75);
            }

            50% {
                outline-width: 5px;
                outline-offset: 4px;
                outline-color: rgba(0, 130, 206, 0);
            }

            100% {
                outline-width: 5px;
                outline-offset: 4px;
                outline-color: rgba(102, 102, 102, 0);
            }
        }
        .deduction-input {
            width: 100px;
        }

        .custom-marker {
            background: transparent;
            border: none;
        }

        .location-item {
            cursor: pointer;
            transition: all 0.2s;
        }

        .location-item:hover {
            background-color: #f8f9fa;
        }

        .location-item.active {
            background-color: #e7f1ff;
            border-left: 4px solid #0d47a1;
        }

        .leaflet-interactive {
            cursor: pointer;
            transition: stroke-width 0.2s;
        }

        .leaflet-interactive:hover {
            stroke-width: 6;
        }

        /* Deduction Status Styles */
        .deduction-status .alert {
            border-left: 4px solid;
            border-radius: 8px;
        }

        .deduction-status .alert-warning {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }

        .deduction-status .alert-success {
            border-left-color: #28a745;
            background-color: #d4edda;
        }

        .deduction-status .alert-danger {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }

        .deduction-status .alert-info {
            border-left-color: #17a2b8;
            background-color: #d1ecf1;
        }

        .employee-acceptance .card-header {
            background-color: #fff3cd;
            border-bottom: 2px solid #ffc107;
        }

        .approval-section .card-header {
            background-color: #d4edda;
            border-bottom: 2px solid #28a745;
        }

        /* Deduction Status Top Styles */
        .deduction-status-top {
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .deduction-status-top .alert {
            margin-bottom: 0;
            border-radius: 0;
        }

        .approval-status .alert {
            border-left: 4px solid;
            border-radius: 8px;
        }

        .approval-status .alert-success {
            border-left-color: #28a745;
            background-color: #d4edda;
        }

        .approval-status .alert-danger {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }

        .approval-status .alert-info {
            border-left-color: #17a2b8;
            background-color: #d1ecf1;
        }

        .approval-status .alert-warning {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }

        .checkbox-custom {
            width: 16px;
            height: 16px;
        }
        .checkbox-custom-one {
            margin-left: 10px;
            width: 16px;
            height: 16px;
        }
        .checkbox-lg {
            transform: scale(1.5);
            margin-right: 5px;
        }
    </style>
@endsection
@section('content')
    @foreach ($claimData as $claim)
        @php
            $rawPlanData = $claim->fh_tada_request_plan;
            $rawExpenseData = $rawPlanData->fh_tada_expenses;
            $rawTravelTypeLocal = $rawPlanData->fh_policy_tada_travel_type->fh_travel_type->m_id == 124;
            $rawTravelDetails = $rawPlanData->fh_tada_request_details;

            $rawTravelDetailSumAmt = $rawTravelTypeLocal
                ? $rawTravelDetails->sum('trd_net_amount')
                : App\Models\TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function ($query) {
                    $query->where('pttv_claim_type_id', 155);
                })->where('trd_trp_id', $rawPlanData->trp_id)->sum('trd_net_amount');

            $jsonData = $claim->fh_claim_status->m_other;
            $decodedData = json_decode($jsonData, true);
            $color = $decodedData['color'] ?? '#000';
            $icon = $decodedData['web_icon'] ?? '';
        @endphp
    @endforeach

    <input type="hidden" id="ajaxCall" value="{{ url('/') }}">
    {{-- Breadcrumbs Start --}}
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/ta-da-request/claim">TA &amp; DA Requests</a></li>
                        <li class="active"><span><b>Claim Group Details</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    {{-- Breadcrumbs End --}}

     <!-- MODAL -->
     <div class="modal fade" id="largemodal" tabindex="-1" role="dialog" aria-labelledby="largemodalLabel"
     aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="largemodalLabel">Employee Travel Path</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body">
                    <h2 class="text-center">Employee Travel Path</h2>
                        <!-- Total Distance Display -->
                        <div class="text-center mb-3">
                            <h5>Total Distance: <span id="totalDistance">0 km</span></h5>
                        </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div id="map" style="height: 450px;"></div> <!-- Map container -->
                        </div>
                        <div class="col-md-4">
                            <h5>Employee Travel History</h5>
                            <ul class="list-group" id="locationList" style="max-height: 450px; overflow-y: auto; overflow-x: hidden;"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL -->

    <!-- ROW -->
    <div class="row">
        {{-- Employee Detail Card Start --}}
        <div class="col-xl-3 col-md-12 col-lg-12">
            <div class="card outer-border position-relative example-4 user-pro-list overflow-hidden p-0">
                <div class="card-body ">
                    <div class="text-center">
                        <div class="widget-user-image mx-auto text-center">
                            <img class="avatar avatar-xxl brround" alt="img"
                            src="{{ isset($claimData->first()->fh_employee) && !empty($claimData->first()->fh_employee->emp_profile_photo)
                                ?  $claimData->first()->fh_employee->emp_profile_photo
                                : asset('assets/imgs/user.png') }}">
                        </div>
                        <div class="pro-user mt-3">
                            <h5 class="pro-user-username text-dark mb-1 fs-16">
                                {{ $claimData->first()->fh_employee->emp_full_name ?? 'N/A' }}</h5>
                            <h6 class="pro-user-desc text-muted fs-12">
                                {{ $claimData->first()->fh_employee->fh_designation->dg_name ?? 'N/A' }}</h6>
                        </div>
                    </div>
                    <h5 class="mb-2 mt-4 font-weight-semibold">Basic Details</h5>
                    <div class="table-responsive">
                        <table class="table text-nowrap">
                            @php
                                $employee = $claimData->first()->fh_employee ?? null;
                                $claimDetails = [
                                    'Emp Code' => $employee->emp_code ?? 'N/A',
                                    'Email ID' => $employee->emp_email ?? 'N/A',
                                    'Contact No' => $employee->emp_phone ?? 'N/A',
                                    'Branch' => $claimData->first()->fh_branch->br_name ?? 'N/A',
                                    // 'Department' => $employee->fh_department->d_name ?? 'N/A',
                                ];
                                // removed legacy variable reference; handled locally where needed
                            @endphp
                            <tbody>
                                @foreach ($claimDetails as $key => $value)
                                    <tr>
                                        <td class="py-1"> <span>{{ $key }}</span> </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1 text-wrap"> <span><b>{{ $value }}</b></span> </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="py-1"> <span class="w-50">Status</span> </td>
                                    <td class="py-1">:</td>
                                    <td class="py-1"> <span class="badge {{ $employee->emp_status == 71 ? 'badge-success-light' : 'badge-warning-light' }}">{{$employee->emp_status == 71 ? 'Active' : 'Inactive'}}</span> </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Claim Details Start --}}
            @php
                // Get single Claim ID (from the first claim object)
                $singleClaim = $claimData->first();

                // Get all Travel IDs (non-null and unique)
                $travelIds = $claimData->pluck('fh_tada_request_plan.trp_unique_id')->filter()->unique()->toArray();

                // Convert Travel IDs to comma-separated string
                $allTravelIds = implode(', ', $travelIds);

                // Set default values for badge color/icon
                $color = optional(json_decode(optional($singleClaim->fh_claim_status)->m_other, true))['color'] ?? '#999';
                $icon = optional(json_decode(optional($singleClaim->fh_claim_status)->m_other, true))['web_icon'] ?? 'fa fa-info-circle';
            @endphp

            {{-- Claim Details Start --}}
            <div class="card outer-border position-relative example-4">
                <div class="card-header px-2">
                    <div class="card-title">
                        <h5 class="mb-2 mt-4 font-weight-semibold">Claim Details</h5>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap">
                            @php
                                $claimDetails = [
                                    'Claim ID' => $singleClaim->tc_unique_id ?? 'N/A',
                                    'All Travel IDs' => $allTravelIds ?: 'N/A',
                                    'Applied Date' => isset($singleClaim->created_at)
                                        ? $singleClaim->created_at->format('d-m-Y')
                                        : 'N/A',
                                ];
                                $chunks = array_chunk($claimDetails, 1, true);
                            @endphp
                            <tbody>
                                @foreach ($chunks as $chunk)
                                    <tr>
                                        @foreach ($chunk as $key => $value)
                                            <td class="py-1"><span>{{ $key }}</span></td>
                                            <td class="py-1">:</td>
                                            <td class="py-1 text-wrap"><span><b>{{ $value }}</b></span></td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- Claim Details End --}}
        </div>
        {{-- Employee Detail Card End --}}


        {{-- Claim Detail Card Start --}}
        <div class="col-xl-9 col-md-12 col-lg-12 ">
            {{-- Claim Paid By Start Selft OR Company Start --}}
            <div class="panel panel-primary">
                <div class="tab-menu-heading hremp-tabs1 p-0 border-bottom">
                    <div class="tabs-menu1">
                        <!-- Tabs -->
                        <ul class="nav panel-tabs">
                            <li class=""><a href="#tabSelf" class="active" data-bs-toggle="tab">Self</a></li>
                            <li><a href="#tabCompany" data-bs-toggle="tab">Company</a></li>
                            <li class="ms-auto p-2 font-weight-bold fs-18">
                                Claim Amount Requested: ₹
                                <u>{{ $claimData->sum('tc_claimed_amount') . '/-' }}</u>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="panel-body tabs-menu-body hremp-tabs1 p-0">
                    <div class="tab-content">
                        {{-- Claim Paid By Self Start --}}
                            <div class="tab-pane active " id="tabSelf">
                            <div class="card-body">
                                <div class="panel panel-primary tabs-style-3">
                                    <div class="tab-menu-heading">
                                        <div class="tabs-menu ">
                                            <!-- Tabs -->
                                            <ul class="nav panel-tabs">
                                                <li class=""><a href="#tabSelfExpSummary" class="active"
                                                        data-bs-toggle="tab"><i class="fa fa-laptop"></i>&nbsp;Claim
                                                        Summmary</a></li>
                                                <li><a href="#tabSelfDailyAllowanceDetails" data-bs-toggle="tab"><i
                                                    class="fa fa-tasks"></i>&nbsp;DA Details</a></li>
                                                <li><a href="#tabSelfTravelDetails" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;TA (Travel Details)</a></li>
                                                @if (!$rawTravelTypeLocal)
                                                <li><a href="#tabSelfLodgingExp" data-bs-toggle="tab"><i
                                                            class="fa fa-cogs"></i>&nbsp;Lodging Expenses</a></li>
                                                @endif
                                                <li><a href="#tabSelfMealExp" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;Meal Expenses</a></li>
                                                <li><a href="#tabSelfMiscellaneousExp" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;Miscellaneous Expenses</a></li>
                                                @php
                                                    $hasDeductions = $claimData->filter(function ($claim) {
                                                        return $claim->fh_deduction_log && $claim->fh_deduction_log->count() > 0;
                                                    })->isNotEmpty();
                                                @endphp

                                                @if ($hasDeductions)
                                                    <li>
                                                        <a href="#tabSelfDeductionExp" data-bs-toggle="tab">
                                                            <i class="fa fa-tasks"></i>&nbsp;Deduction
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                    @foreach ($claimData as $claim)
                                        @php
                                            $rawExpenseData = $claim->fh_tada_request_plan->fh_tada_expenses ?? collect();

                                            $selfLodgingExpenseType = $rawExpenseData
                                                ->where('te_paid_by', 'self')
                                                ->where('te_type_id', 158);

                                            $selfTravelExpenseType = $rawExpenseData
                                                ->where('te_paid_by', 'self')
                                                ->where('te_type_id', 159);

                                            $selfMealExpenseType = $rawExpenseData
                                                ->where('te_paid_by', 'self')
                                                ->where('te_type_id', 160);

                                            $selfOtherExpenseType = $rawExpenseData
                                                ->where('te_paid_by', 'self')
                                                ->where('te_type_id', 161);

                                            $companyExpenseTypes = $rawExpenseData
                                                ->where('te_paid_by', 'company')
                                                ->where('te_type_id', 158);
                                        @endphp
                                    @endforeach
                                    <div class="panel-body tabs-menu-body">
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="tabSelfExpSummary">
                                            @php
                                                $pendingClaims = $claimData->where('fh_claim_status.m_id', 140);
                                                $hasPendingClaims = $pendingClaims->count() > 0;

                                                // Check if any pending claim has approval data
                                                $hasApprovalDataForBulk = false;
                                                $firstPendingClaim = null;
                                                $approvalData = null;

                                                if ($hasPendingClaims) {
                                                    $firstPendingClaim = $pendingClaims->first();

                                                    // Try to get approval data using the standard method
                                                    $approvalData = \App\Helpers\ApprovalHelper::getApprovalOrRejectionData(
                                                        $firstPendingClaim->tc_trp_id,
                                                        $firstPendingClaim->tc_status,
                                                        $firstPendingClaim->tc_am_id,
                                                        NULL,
                                                        146
                                                    );

                                                    // If no approval data found from the helper, check if this is the last approval
                                                    if (!$approvalData) {
                                                        // For last approval, we need to check if the current user is the final approver
                                                        // Check NextApprovalDetail to see if this is the last approval
                                                        $nextApprovalDetail = \App\Models\NextApprovalDetail::where('nxt_tc_id', $firstPendingClaim->tc_id)->first();

                                                        if ($nextApprovalDetail && $nextApprovalDetail->nxt_is_last == 1) {
                                                            // Check if the current user is the approver for this sequence
                                                            $processApprover = \App\Models\ProcessApprover::where('pa_am_id', $nextApprovalDetail->nxt_am_id)
                                                                ->where('pa_sequence', $nextApprovalDetail->nxt_approver_sequence)
                                                                ->where('pa_type', $nextApprovalDetail->nxt_approval_type)
                                                                ->where('pa_emp_id', Auth::user()->emp_id)
                                                                ->first();

                                                            if ($processApprover) {
                                                                // Create mock approval data for the last approval
                                                                $approvalData = (object)[
                                                                    'fh_approver_status' => (object)[
                                                                        'm_id' => $firstPendingClaim->tc_status
                                                                    ],
                                                                    'pa_type' => $processApprover->pa_type,
                                                                    'pa_sequence' => $processApprover->pa_sequence,
                                                                    'pa_am_id' => $processApprover->pa_am_id,
                                                                    'pa_last' => $nextApprovalDetail->nxt_is_last,
                                                                    'tc_id' => $firstPendingClaim->tc_id
                                                                ];
                                                                $hasApprovalDataForBulk = true;
                                                            }
                                                        }
                                                    } else {
                                                        $hasApprovalDataForBulk = true;
                                                    }
                                                }
                                            @endphp

                                          <!--Previous Start Code-->
                                          {{--  @if($hasPendingClaims && $hasApprovalDataForBulk)
                                            <div>
                                               Select All <input type="checkbox" id="selectAllTravelIds">
                                            </div>
                                            @endif --}}
                                            <!--Previous Code End-->

  {{--
  Previous Code - 23-08-2025
  @php
        $showSelectAll = false; // default
    @endphp

    @foreach ($claimData as $claim)
        @php
            // Get approval data for THIS claim only
            $claimApprovalData = \App\Helpers\ApprovalHelper::getApprovalOrRejectionData(
                $claim->tc_trp_id,
                $claim->tc_status,
                $claim->tc_am_id,
                null,
                146
            );

            if (!$claimApprovalData) {
                $nextApprovalDetail = \App\Models\NextApprovalDetail::where('nxt_tc_id', $claim->tc_id)->first();

                if ($nextApprovalDetail && $nextApprovalDetail->nxt_is_last == 1) {
                    $processApprover = \App\Models\ProcessApprover::where('pa_am_id', $nextApprovalDetail->nxt_am_id)
                        ->where('pa_sequence', $nextApprovalDetail->nxt_approver_sequence)
                        ->where('pa_type', $nextApprovalDetail->nxt_approval_type)
                        ->where('pa_emp_id', Auth::user()->emp_id)
                        ->first();

                    if ($processApprover) {
                        $claimApprovalData = (object)[
                            'fh_approver_status' => (object)[
                                'm_id' => $claim->tc_status
                            ],
                            'pa_type' => $processApprover->pa_type,
                            'pa_sequence' => $processApprover->pa_sequence,
                            'pa_am_id' => $processApprover->pa_am_id,
                            'pa_last' => $nextApprovalDetail->nxt_is_last
                        ];
                    }
                }
            }

            // Check if THIS claim has a deduction handler
            $claimHasDeductionHandler = $claim->fh_deduction_log->whereNull('dlog_requester_action')->isNotEmpty();

            // If condition matched for any claim, enable the flag
            if ($claimApprovalData && !$claimHasDeductionHandler && $claim->tc_stage_completed != 1) {
                $showSelectAll = true;
            }
        @endphp
    @endforeach

    @if($showSelectAll)
        <div>
           Select All <input type="checkbox" id="selectAllTravelIds">
        </div>
    @endif  --}}


    @php
        $showSelectAll = true; // assume true, then disqualify if any deduction exists
        $bulkClaimApprovalData = null; // store first eligible claim's approval data
        $firstEligibleClaim = null;
    @endphp

    @foreach ($claimData as $claim)
        @php
            // Get approval data for THIS claim only
            $claimApprovalData = \App\Helpers\ApprovalHelper::getApprovalOrRejectionData(
                $claim->tc_trp_id,
                $claim->tc_status,
                $claim->tc_am_id,
                null,
                146
            );

            if (!$claimApprovalData) {
                $nextApprovalDetail = \App\Models\NextApprovalDetail::where('nxt_tc_id', $claim->tc_id)->first();

                if ($nextApprovalDetail && $nextApprovalDetail->nxt_is_last == 1) {
                    $processApprover = \App\Models\ProcessApprover::where('pa_am_id', $nextApprovalDetail->nxt_am_id)
                        ->where('pa_sequence', $nextApprovalDetail->nxt_approver_sequence)
                        ->where('pa_type', $nextApprovalDetail->nxt_approval_type)
                        ->where('pa_emp_id', Auth::user()->emp_id)
                        ->first();

                    if ($processApprover) {
                        $claimApprovalData = (object)[
                            'fh_approver_status' => (object)[
                                'm_id' => $claim->tc_status
                            ],
                            'pa_type' => $processApprover->pa_type,
                            'pa_sequence' => $processApprover->pa_sequence,
                            'pa_am_id' => $processApprover->pa_am_id,
                            'pa_last' => $nextApprovalDetail->nxt_is_last
                        ];
                    }
                }
            }

            // Deduction condition - check for pending deductions
            $claimHasDeductionHandler = $claim->fh_deduction_log
                ->whereNull('dlog_requester_action')
                ->isNotEmpty();

            // Check if deductions have been handled (accepted/declined)
            $deductionsHandled = $claim->fh_deduction_log
                ->whereNotNull('dlog_requester_action')
                ->isNotEmpty();
            // Determine latest handled deduction action if any
            $latestHandled = $deductionsHandled
                ? $claim->fh_deduction_log->whereNotNull('dlog_requester_action')->sortByDesc('updated_at')->first()
                : null;
            $deductionDeclined = $latestHandled && (int)($latestHandled->dlog_requester_action) === 0;
            $deductionAccepted = $latestHandled && (int)($latestHandled->dlog_requester_action) === 1;

            // Finalized state for this claim
            $isFinalized = ($claim->tc_next_approver == 1 && $claim->tc_stage_completed == 1);

            // If THIS claim has pending deductions or is finalized, then disable Select All
            // If deduction is declined, allow Select All only for the current (first) approver; disable for others
            $isCurrentApproverFirst = ($claimApprovalData && isset($claimApprovalData->pa_sequence) && (int)$claimApprovalData->pa_sequence === 1);
            if (
                $claimHasDeductionHandler
                || $isFinalized
                // REMOVED: || ($deductionDeclined && (!$isCurrentApproverFirst))
            ) {
                $showSelectAll = false;
            }

            // Store first eligible claim's data for bulk action buttons
            // Only eligible if no pending deductions and not finalized

           //if ($claimApprovalData && !$claimHasDeductionHandler && !$isFinalized && !$bulkClaimApprovalData)
            if ($claimApprovalData && !$claimHasDeductionHandler && $claim->tc_stage_completed != 1 && !$isFinalized && !$bulkClaimApprovalData) {
                // If there were deductions, ensure they are handled
                // FIXED: When deduction is declined, allow bulk approval for all approvers
                if (
                    $claim->fh_deduction_log->count() > 0
                    && (
                        !$deductionsHandled
                        // REMOVED: || ($deductionDeclined && !$isCurrentApproverFirst)
                    )
                ) {
                    continue; // Not eligible for bulk in these cases
                }

                $bulkClaimApprovalData = $claimApprovalData;
                $firstEligibleClaim = $claim;
            }
        @endphp
    @endforeach

    {{--@if($showSelectAll && $bulkClaimApprovalData && $firstEligibleClaim) --}}
    @if($bulkClaimApprovalData && $firstEligibleClaim)
        <div class="d-flex justify-content-end me-2">
            <!--<input type="checkbox" class="checkbox-custom checkbox-lg" id="selectAllTravelIds"> -->
            <label class="custom-control custom-checkbox-md mx-2" style="font-size:15px;">Select All &nbsp;&nbsp;
                <input type="checkbox" id="selectAllTravelIds" class="custom-control-input-success">
                <span class="custom-control-label-md success"></span>
            </label>
        </div>
    @endif

    <div class="row">
        @foreach ($claimData as $index => $claim)
            @php
                // Calculate amounts for each claim individually
                $plan = $claim->fh_tada_request_plan;
                $daAmount = $claim->tc_da_amount ?? 0;
                $totalAmount = $claim->tc_amount?? 0;
                $advance = $plan->trp_advance_allowance ?? 0;
                $deduction = $claim->tc_deduction_amount ?? 0;
                $netPayable = $totalAmount - ($advance + $deduction);

                // Get approval data for THIS claim only
                $claimApprovalData = \App\Helpers\ApprovalHelper::getApprovalOrRejectionData(
                    $claim->tc_trp_id,
                    $claim->tc_status,
                    $claim->tc_am_id,
                    null,
                    146
                );

                // If no approval data found from the helper, check if this is the last approval
                if (!$claimApprovalData) {
                    // For last approval, we need to check if the current user is the final approver
                    $nextApprovalDetail = \App\Models\NextApprovalDetail::where('nxt_tc_id', $claim->tc_id)->first();

                    if ($nextApprovalDetail && $nextApprovalDetail->nxt_is_last == 1) {
                        // Check if the current user is the approver for this sequence
                        $processApprover = \App\Models\ProcessApprover::where('pa_am_id', $nextApprovalDetail->nxt_am_id)
                            ->where('pa_sequence', $nextApprovalDetail->nxt_approver_sequence)
                            ->where('pa_type', $nextApprovalDetail->nxt_approval_type)
                            ->where('pa_emp_id', Auth::user()->emp_id)
                            ->first();

                        if ($processApprover) {
                            // Create mock approval data for the last approval
                            $claimApprovalData = (object)[
                                'fh_approver_status' => (object)[
                                    'm_id' => $claim->tc_status
                                ],
                                'pa_type' => $processApprover->pa_type,
                                'pa_sequence' => $processApprover->pa_sequence,
                                'pa_am_id' => $processApprover->pa_am_id,
                                'pa_last' => $nextApprovalDetail->nxt_is_last
                            ];
                        }
                    }
                }

                // Check if THIS claim has a deduction handler
                $claimHasDeductionHandler = $claim->fh_deduction_log->whereNull('dlog_requester_action')->isNotEmpty();

                // Group expenses for this claim
                $selfExpensesGrouped = optional($plan->fh_tada_expenses)
                    ->where('te_paid_by', 'self')
                    ->groupBy('fh_expense_type.m_name');
            @endphp

            <div class="card-body claim-container" id="claim-{{ $claim->tc_id }}" data-claim-id="{{ $claim->tc_id }}">
                <div class="accordion" id="accordion-{{ $index }}">
                    <div class="acc-card">
                        <!-- Claim Header -->
                        <div class="acc-header d-flex align-items-center" id="heading-{{ $index }}">
                            <h5 class="mb-0 flex-grow-1">
                                <a class="d-flex align-items-center justify-content-between w-100"
                                   data-bs-toggle="collapse"
                                   href="#collapse-{{ $index }}"
                                   aria-expanded="false"
                                   aria-controls="collapse-{{ $index }}">
                                    <span>
                                        <i  class="fa fa-random me-2" style="font-size:14px !important;"></i>
                                        <!--{{ $plan->fh_tada_claim->tc_id ?? 'N/A' }}-->
                                        Travel ID: {{ $plan->trp_unique_id ?? 'N/A' }} |
                                        Net Payable: ₹{{ number_format($netPayable, 2) }} |
                                        Status: <span class="claim-status badge
                                            @if($claim->fh_claim_status->m_id == 157) bg-success
                                            @elseif($claim->fh_claim_status->m_id == 170) bg-danger
                                            @else bg-warning @endif">
                                            {{ $claim->fh_claim_status->m_name ?? 'N/A' }}
                                        </span>
                                    </span>
                                    <i class="fe fe-chevron-right acc-angle"></i>
                                </a>
                            </h5>
                            <div class="checkbox-placeholder me-2 text-end" style="width: 20px;">
                                {{--@if($claimApprovalData && !$claimHasDeductionHandler)--}}
                                @if($claimApprovalData && !$claimHasDeductionHandler && $claim->tc_stage_completed != 1)
                                    <input type="checkbox"
                                           name="selected_travel_ids[]"
                                           value="{{ $claim->tc_id }}"
                                           class="travel-id-checkbox checkbox-custom-one checkbox-lg">
                                @endif
                            </div>
                        </div>

                        <!-- Deduction Status Message at Top -->
                        @if($claim->fh_deduction_log->count() > 0)
                            @php
                                $pendingDeductions = $claim->fh_deduction_log->whereNull('dlog_requester_action');
                                $handledDeductions = $claim->fh_deduction_log->whereNotNull('dlog_requester_action');
                                // Use the latest PENDING deduction for the pending message
                                // moved to local scope later; avoid duplicate/unused assignment
                            @endphp

                            @if($claimHasDeductionHandler)
                            @if($pendingDeductions->count() > 0)
                                @php
                                    $lp = $pendingDeductions->sortByDesc(function($x){
                                        return $x->updated_at ?? $x->created_at;
                                    })->first();
                                    $showBy = false;
                                    if ($lp && isset($lp->fh_employee) && isset($lp->fh_employee->emp_id) && isset(Auth::user()->emp_id)) {
                                        $showBy = ((int)$lp->fh_employee->emp_id === (int)Auth::user()->emp_id);
                                    }
                                @endphp

                                <div class="approval-status deduction-status-top p-3">
                                    <div class="alert alert-warning mb-0">
                                        <h6><i class="fa fa-exclamation-triangle me-2"></i>Deduction Pending - Action Required</h6>
                                        <p class="mb-2">
                                            <div class="row">
                                                <div class="col-lg-3 col-md-3 col-sm-12">
                                                    <strong>Travel ID:</strong> {{ $claim->fh_tada_request_plan->trp_unique_id ?? 'N/A' }}
                                                </div>
                                                <div class="col-lg-3 col-md-3 col-sm-12">
                                                    <strong>Deduction Amount:</strong> ₹{{ $lp->dlog_deduction_amount ?? 0 }}/-<br>
                                                </div>
                                                @if($showBy)
                                                <div class="col-lg-3 col-md-3 col-sm-12">
                                                   <strong>Deduction By:</strong> {{ $lp->fh_employee->emp_full_name ?? 'N/A' }}<br>
                                                </div>
                                                @endif
                                                <div class="col-lg-3 col-md-3 col-sm-12">
                                                   <strong>Reason:</strong> {{ $lp->dlog_remarks ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </p>

                                        <p class="mb-0 text-danger">
                                            <i class="fa fa-info-circle me-1"></i>
                                            <strong>Status:</strong> Employee must accept or decline this deduction before approval can proceed.
                                        </p>
                                    </div>
                                </div>
                            @elseif($handledDeductions->count() > 0)
                                @php
                                    $latestHandledDeduction = $handledDeductions->sortByDesc('created_at')->first();
                                    $deductionAction = $latestHandledDeduction->dlog_requester_action == 1 ? 'Accepted' : 'Declined';
                                    $deductionActionClass = $latestHandledDeduction->dlog_requester_action == 1 ? 'success' : 'danger';
                                @endphp
                                <div class="deduction-status-top p-3">
                                    <div class="alert alert-{{ $deductionActionClass }} mb-0">
                                        <h6><i class="fa fa-check-circle me-2"></i>Deduction {{ $deductionAction }}</h6>
                                        <p class="mb-2">
                                            <strong>Travel ID:</strong> {{ $claim->fh_tada_request_plan->trp_unique_id ?? 'N/A' }}<br>
                                            <strong>Deduction Amount:</strong> ₹{{ $latestHandledDeduction->dlog_deduction_amount ?? 0 }}/-<br>
                                            <strong>Deduction By:</strong> {{ $latestHandledDeduction->fh_employee->emp_full_name ?? 'N/A' }}<br>
                                            <strong>Employee Response:</strong> {{ $deductionAction }} on {{ $latestHandledDeduction->updated_at ? \Carbon\Carbon::parse($latestHandledDeduction->updated_at)->format('d-M-Y h:i A') : 'N/A' }}
                                        </p>
                                        @if($latestHandledDeduction->dlog_requester_remarks)
                                            <p class="mb-0">
                                                <strong>Employee Remarks:</strong> {{ $latestHandledDeduction->dlog_requester_remarks }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                        @endif

                        <!-- Claim Details -->
                        <div id="collapse-{{ $index }}" class="collapse" aria-labelledby="heading-{{ $index }}" data-parent="#accordion-{{ $index }}">
                            <div class="acc-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card overflow-hidden">
                                            <div class="card-body">
                                                <div class="table-responsive push">
                                                    <table class="table table-bordered table-hover text-nowrap">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center" style="width: 1%">S.No.</th>
                                                                <th>Expenses</th>
                                                                <th class="text-end" style="width: 1%">Amount</th>
                                                                <th class="text-end" style="width: 1%">Deviation</th>
                                                                <th class="text-end" style="width: 1%">Payable Amount</th>
                                                                {{--@if($claimApprovalData && !$claimHasDeductionHandler)--}}
                                                                @if($claimApprovalData && !$claimHasDeductionHandler && $claim->tc_stage_completed != 1)
                                                                    <th class="text-end" style="width: 1%">Deduction</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($selfExpensesGrouped as $expenseType => $expenses)
                                                                @php
                                                                    $exp_type_id = $expenses->pluck('te_type_id')->first();
                                                                    $totalAmount = round($expenses->sum('te_amount') + $expenses->sum('te_taxes'));
                                                                    $totalDeviation = round($expenses->sum('te_deviation'));
                                                                    $payableAmount = $totalAmount - $totalDeviation;
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ $expenseType }}</td>
                                                                    <td class="text-end">₹{{ number_format($totalAmount, 2) }}</td>
                                                                    <td class="text-end">₹{{ number_format($totalDeviation, 2) }}</td>
                                                                    <td class="text-end">₹{{ number_format($payableAmount, 2) }}</td>
                                                                    {{--@if($claimApprovalData && !$claimHasDeductionHandler)--}}
                                                                    @if($claimApprovalData && !$claimHasDeductionHandler && $claim->tc_stage_completed != 1)
                                                                        <td>
                                                                            <input type="number"
                                                                                   name="deduction[{{ $exp_type_id }}]"
                                                                                   class="form-control deduction-input text-end"
                                                                                   data-payableamount="{{ $payableAmount }}"
                                                                                   step="0.01"
                                                                                   min="0"
                                                                                   max="{{ $payableAmount }}"
                                                                                   placeholder="₹0.00">
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @endforeach

                                                            <!-- Summary Rows -->
                                                            @if($plan && $plan->trp_is_details_added)
                                                                <tr>
                                                                    <td colspan="4" class="font-weight-semibold text-end">TA</td>
                                                                    <td class="text-end">₹{{ number_format($rawTravelDetailSumAmt, 2) }}</td>
                                                                    {{--@if($claimApprovalData && !$claimHasDeductionHandler)--}}
                                                                    @if($claimApprovalData && !$claimHasDeductionHandler && $claim->tc_stage_completed != 1)
                                                                        <td></td>
                                                                    @endif
                                                                </tr>
                                                            @endif

                                                            <tr>
                                                                <td colspan="4" class="font-weight-semibold text-end">DA</td>
                                                                <td class="text-end">₹{{ number_format($daAmount, 2) }}</td>
                                                                @if($claimApprovalData && !$claimHasDeductionHandler)
                                                                    <td></td>
                                                                @endif
                                                            </tr>

                                                            <tr>
                                                                <td colspan="4" class="font-weight-semibold text-end">
                                                                    Subtotal
                                                                    <small class="d-block text-muted">Total Expense Amount (Inc. Taxes), Travel Allowance, and DA</small>
                                                                </td>
                                                                {{-- <td class="text-end">₹{{ number_format($totalAmount, 2) }}</td> --}}
                                                                   <td class="text-end">₹{{ number_format($claim->tc_amount, 2) }}</td>
                                                                @if($claimApprovalData && !$claimHasDeductionHandler)
                                                                    <td></td>
                                                                @endif
                                                            </tr>

                                                            <tr>
                                                                <td colspan="4" class="font-weight-semibold text-end">Advance</td>
                                                                <td class="text-end">₹{{ number_format($advance, 2) }}</td>
                                                                @if($claimApprovalData && !$claimHasDeductionHandler)
                                                                    <td></td>
                                                                @endif
                                                            </tr>

                                                            <tr>
                                                                <td colspan="4" class="font-weight-semibold text-end">Deduction</td>
                                                                <td class="text-end">₹{{ number_format($deduction, 2) }}</td>
                                                                @if($claimApprovalData && !$claimHasDeductionHandler)
                                                                    <td></td>
                                                                @endif
                                                            </tr>

                                                            <tr>
                                                                <td colspan="4" class="font-weight-bold text-uppercase text-end h4 mb-0">Net Payable Amount</td>
                                                                <td class="font-weight-bold text-end h4 mb-0">₹{{ number_format($netPayable, 2) }}</td>
                                                                @if($claimApprovalData && !$claimHasDeductionHandler)
                                                                    <td></td>
                                                                @endif
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approval Section - Only for approvable claims -->
                                {{--$isEligibleForApproval = $claimApprovalData && !$claimHasDeductionHandler; --}}
                                @php
                                    // Determine individual approver eligibility (decoupled from bulk/select-all)
                                    $isEligibleForApproval = $claimApprovalData && !$claimHasDeductionHandler && $claim->tc_stage_completed != 1;

                                    // Check if deductions have been handled (if any existed)
                                    $deductionsHandled = $claim->fh_deduction_log->whereNotNull('dlog_requester_action')->isNotEmpty();
                                    $hadDeductions = $claim->fh_deduction_log->count() > 0;
                                    $latestHandledDeduction = $deductionsHandled ? $claim->fh_deduction_log->whereNotNull('dlog_requester_action')->sortByDesc('updated_at')->first() : null;
                                    $deductionDeclined = $latestHandledDeduction && (int)$latestHandledDeduction->dlog_requester_action === 0;
                                    $deductionAccepted = $latestHandledDeduction && (int)$latestHandledDeduction->dlog_requester_action === 1;

                                    // Base rule: show approval if no pending deductions AND (no deductions OR handled)
                                    $canShowApproval = !$claimHasDeductionHandler && (!$hadDeductions || $deductionsHandled);

                                    // FIXED LOGIC: If deduction was declined by employee:
                                    // - Allow approval for ALL approvers (not just the first one)
                                    // - The deduction was declined, so the claim should proceed normally
                                    if ($deductionDeclined) {
                                        // When deduction is declined, all approvers should be able to continue
                                        // The deduction is effectively cancelled, so normal approval flow applies
                                        $canShowApproval = !$claimHasDeductionHandler;
                                    }

                                    // Check if claim is already approved/rejected
                                    $isApproved = $claim->fh_claim_status->m_id == 157;
                                    $isRejected = $claim->fh_claim_status->m_id == 170;
                                    $isPending = $claim->fh_claim_status->m_id == 140;
                                @endphp

                                {{-- Approval Section - Only show if eligible and can show approval --}}
                               {{-- @if($isEligibleForApproval && $canShowApproval)
                                    <div class="mt-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">Approval Or Reject</h3>
                                            </div>
                                            <div class="card-body">
                                                <form class="individualApprovalForm" id="approvalForm-{{ $claim->tc_id }}">
                                                    <div class="form-group">
                                                        <label class="form-label">Approval Message</label>
                                                        <textarea rows="3"
                                                                  name="message"
                                                                  class="form-control individual-message"
                                                                  maxlength="255">Approved</textarea>
                                                    </div>

                                                    <div class="d-flex justify-content-end mt-3">
                                                        <button type="button"
                                                                class="btn btn-danger individualActionBtn mx-2"
                                                                data-tc_id="{{ md5($claim->tc_id) }}"
                                                                data-approval_status="{{ $claimApprovalData->fh_approver_status->m_id }}"
                                                                data-approval_type="0"
                                                                data-approval_action_type="{{ $claimApprovalData->pa_type }}"
                                                                data-approval_sequence="{{ $claimApprovalData->pa_sequence }}"
                                                                data-module_id="{{ md5($claimApprovalData->pa_am_id) }}"
                                                                data-is_last_approval="{{ $claimApprovalData->pa_last ?? 0 }}"
                                                                data-emp_d_id="{{ optional($claim->fh_employee)->emp_d_id }}"
                                                                data-original-text="Reject">
                                                            <i class="fa fa-times-circle me-1"></i> Reject
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-success individualActionBtn"
                                                                data-tc_id="{{ md5($claim->tc_id) }}"
                                                                data-approval_status="{{ $claimApprovalData->fh_approver_status->m_id }}"
                                                                data-approval_type="1"
                                                                data-approval_action_type="{{ $claimApprovalData->pa_type }}"
                                                                data-approval_sequence="{{ $claimApprovalData->pa_sequence }}"
                                                                data-module_id="{{ md5($claimApprovalData->pa_am_id) }}"
                                                                data-is_last_approval="{{ $claimApprovalData->pa_last ?? 0 }}"
                                                                data-emp_d_id="{{ optional($claim->fh_employee)->emp_d_id }}"
                                                                data-original-text="Approve">
                                                            <i class="fa fa-check-circle me-1"></i> Approve
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif --}}

                                <div class="col-xl-12 col-md-12 col-lg-6">
                                    {{--if(!($claimApprovalData && !$claimHasDeductionHandler)) --}}
                                    @if(!($claimApprovalData && !$claimHasDeductionHandler && $claim->tc_stage_completed != 1))
                                    <ul id="claimLogList" class="timeline">
                                        @foreach ($claim->combined_log->sortBy('updated_at') as $item2)
                                            @php
                                                $className = get_class($item2);
                                                if ($className == 'App\Models\ApprovalLog') {
                                                    $jsonData2 = $item2->fh_status->m_other;
                                                    $item2DecodedData = json_decode($jsonData2, true);
                                                    $item2Color = $item2DecodedData['color'];
                                                    $item2Icon = $item2DecodedData['web_icon'];
                                                    $item2StatusName = $item2->fh_status->m_name;
                                                    $remark = $item2->log_description;

                                                    $emp_name = $item2->fh_employee->emp_full_name;
                                                    $emp_code = $item2->fh_employee->emp_code;
                                                    $emp_designation = $item2->fh_employee->fh_designation->dg_name;
                                                } else {
                                                    $item2StatusName = ($item2->dlog_requester_action == 1) ? 'Accepted' : 'Declined';
                                                    $item2Color = ($item2->dlog_requester_action == 1) ? '#4CAF50' : '#F44336';
                                                    $item2Icon = ($item2->dlog_requester_action == 1) ? 'fa fa-check' : 'fa fa-times';
                                                    $remark = $item2->dlog_remarks;

                                                    $emp_name = $claim->fh_employee->emp_full_name;
                                                    $emp_code = $claim->fh_employee->emp_code;
                                                    $emp_designation = $claim->fh_employee->fh_designation->dg_name;
                                                }
                                            @endphp

                                            <li class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }}">
                                                <a href="javascript:void(0);" class="font-weight-semibold fs-15 mb-2 ms-3">
                                                    <span class="badge" style="background-color:{{$item2Color}}">
                                                        <i class="{{$item2Icon}}">&nbsp;</i>{{ $item2StatusName }}
                                                    </span>
                                                </a>
                                                <a href="javascript:void(0);" class="text-muted float-end fs-12">On
                                                    {{ \Carbon\Carbon::parse($item2->created_at)->format('l') }}</a><br>
                                                <span class="text-muted float-end ms-3 fs-14">
                                                    <i class="fa fa-calendar"></i>
                                                    {{ \Carbon\Carbon::parse($item2->created_at)->format('d-M-Y') }}
                                                    <i class="ms-3 fa fa-clock-o"></i>
                                                    {{ \Carbon\Carbon::parse($item2->created_at)->format('h:i A') }}
                                                </span>
                                                <p class="mb-0 pb-0 text-muted fs-18 pt-1 ms-3">
                                                    {{ $emp_name }} &nbsp; <span class="fs-14">{{ '(' . $emp_code . ')' }}</span>
                                                </p>
                                                <span class="mb-0 pb-0 text-muted fs-14 ms-3">{{ $emp_designation }}</span><br>
                                                <span class="text-muted ms-3 fs-14">Remark : {{ $remark }}</span>
                                                <div>
                                                    @if (isset($item2->fh_deductionLog->dlog_additional_info) && $item2->fh_deductionLog->dlog_additional_info)
                                                        <span class="text-muted ms-3 fs-14">Deduction : </span>
                                                        @foreach (json_decode($item2->fh_deductionLog->dlog_additional_info) as $key=> $keyItem)
                                                            <span class="text-muted ms-3 fs-14">{{ \App\Models\MasterTable::find($key)->m_name }} : {{ $keyItem }}</span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>

                                <!-- Employee Acceptance Section -->
                                @if($claimHasDeductionHandler && $actionRoute == 'claim-request.request.show')
                                    @php
                                        $pendingDeduction = $claim->fh_deduction_log->whereNull('dlog_requester_action')->first();
                                    @endphp
                                    <div class="employee-acceptance mt-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">
                                                    <i class="fa fa-exclamation-triangle text-warning me-2"></i>
                                                    Action Required: Accept Or Decline Deduction
                                                </h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-warning mb-3">
                                                    <h6><i class="fa fa-info-circle me-2"></i>Deduction Details</h6>
                                                    <p class="mb-2">
                                                        <strong>Travel ID:</strong> {{ $claim->fh_tada_request_plan->trp_unique_id ?? 'N/A' }}<br>
                                                        <strong>Deduction Amount:</strong> ₹{{ $pendingDeduction->dlog_deduction_amount ?? 0 }}/-<br>
                                                        <strong>Deduction By:</strong> {{ $pendingDeduction->fh_employee->emp_full_name ?? 'N/A' }}<br>
                                                        <strong>Reason:</strong> {{ $pendingDeduction->dlog_remarks ?? 'N/A' }}
                                                    </p>
                                                    <p class="mb-0 text-danger">
                                                        <i class="fa fa-exclamation-triangle me-1"></i>
                                                        <strong>Important:</strong> You must accept or decline this deduction before the claim can proceed to the next approval stage.
                                                    </p>
                                                </div>

                                                <form class="deductionAcceptanceForm">
                                                    <div class="form-group">
                                                        <label class="form-label">Your Response Message (Optional)</label>
                                                        <textarea rows="3"
                                                                  name="empAcceptanceMsg"
                                                                  class="form-control"
                                                                  id="empAcceptanceMsg"
                                                                  placeholder="Please provide any comments regarding this deduction..."></textarea>
                                                    </div>

                                                    <div class="d-flex justify-content-end mt-3">
                                                        <button type="button"
                                                                class="btn btn-danger handleDeductionBtn mx-2"
                                                                data-tc_id="{{ md5($claim->tc_id) }}"
                                                                data-dlog_id="{{ md5($pendingDeduction->dlog_id) }}"
                                                                data-action="0">
                                                            <i class="fa fa-times-circle me-1"></i> Decline Deduction
                                                        </button>

                                                        <button type="button"
                                                                class="btn btn-success handleDeductionBtn"
                                                                data-tc_id="{{ md5($claim->tc_id) }}"
                                                                data-dlog_id="{{ md5($pendingDeduction->dlog_id) }}"
                                                                data-action="1">
                                                            <i class="fa fa-check-circle me-1"></i> Accept Deduction
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Removed nested foreach loop that was causing duplicate data --}}
       {{-- @if($showSelectAll && $bulkClaimApprovalData && $firstEligibleClaim) --}}
        @if($bulkClaimApprovalData && $firstEligibleClaim)
            @php
                // Check if any claims had deductions that were handled
                $claimsWithHandledDeductions = $claimData->filter(function($claim) {
                    $hadDeductions = $claim->fh_deduction_log->count() > 0;
                    $deductionsHandled = $claim->fh_deduction_log->whereNotNull('dlog_requester_action')->isNotEmpty();
                    return $hadDeductions && $deductionsHandled;
                });

                $hasHandledDeductions = $claimsWithHandledDeductions->count() > 0;
            @endphp

            <!-- BULK APPROVAL FORM -->
            <form id="bulkApprovalForm">
                <div class="form-group">
                    @if($hasHandledDeductions)
                        <!--<div class="alert alert-info mb-3">
                            <h6><i class="fa fa-info-circle me-2"></i>Bulk Approval Notice</h6>
                            <p class="mb-0">
                                Some claims have deductions that have been handled by employees.
                                You can now proceed with bulk approval for all eligible claims.
                            </p>
                        </div>-->
                    @endif

                    <div id="deductionAmountDiv"></div>
                    <div class="row">
                        <div class="col-md-12 col-lg-2">
                            <label class="form-label mb-0 mt-2">Message</label>
                        </div>
                        <div class="col-md-12 col-lg-12">
                            <textarea rows="2" name="message" class="form-control" maxlength="255" id="bulkActionMessage">Approved</textarea>
                        </div>
                    </div>

                    <div class="card-footer mt-3">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                <input type="hidden" name="claim_ids" value="{{ $claimData->pluck('tc_id')->implode(',') }}">

                                <!-- Reject All -->
                                <button type="button"
                                    data-tc_id="{{ md5($firstEligibleClaim->tc_id) }}"
                                    data-approval_status="{{ $bulkClaimApprovalData->fh_approver_status->m_id ?? '' }}"
                                    data-approval_type="0"
                                    data-approval_action_type="{{ $bulkClaimApprovalData->pa_type ?? '' }}"
                                    data-approval_sequence="{{ $bulkClaimApprovalData->pa_sequence ?? '' }}"
                                    data-module_id="{{ md5($bulkClaimApprovalData->pa_am_id ?? '') }}"
                                    data-is_last_approval="{{ $bulkClaimApprovalData->pa_last ?? 0 }}"
                                    data-emp_d_id="{{ optional($firstEligibleClaim->fh_employee)->emp_d_id }}"
                                    data-original-text="Reject"
                                    class="btn btn-danger bulkActionBtn mx-3">Reject All</button>

                                <!-- Approve All -->
                                <button type="button"
                                    data-tc_id="{{ md5($firstEligibleClaim->tc_id) }}"
                                    data-approval_status="{{ $bulkClaimApprovalData->fh_approver_status->m_id ?? '' }}"
                                    data-approval_type="1"
                                    data-approval_action_type="{{ $bulkClaimApprovalData->pa_type ?? '' }}"
                                    data-approval_sequence="{{ $bulkClaimApprovalData->pa_sequence ?? '' }}"
                                    data-module_id="{{ md5($bulkClaimApprovalData->pa_am_id ?? '') }}"
                                    data-emp_d_id="{{ optional($firstEligibleClaim->fh_employee)->emp_d_id }}"
                                    data-is_last_approval="{{ $bulkClaimApprovalData->pa_last ?? 0 }}"
                                    data-original-text="Approve"
                                    class="btn btn-success bulkActionBtn">Approve All</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endif


    </div>
</div>
                                            <div class="tab-pane" id="tabSelfDailyAllowanceDetails">
                                                <!-- Lodging Expenses -->
                                                @foreach ($claimData as $index => $claim)
                                                <div class="card-body">
                                                    <div aria-multiselectable="true" class="accordion" id="accordion-{{ $index }}" role="tablist">
                                                        <div class="acc-card mb-4">
                                                            <div class="acc-header" id="heading-{{ $index }}" role="tab">
                                                                <h5 class="mb-0">
                                                                    <a aria-controls="collapse-{{ $index }}" aria-expanded="true" data-bs-toggle="collapse" href="#collapse-{{ $index }}" class="d-flex justify-content-between align-items-center">
                                                                        <span>
                                                                            <i class="fa fa-random me-2"></i>
                                                                            Travel ID: {{ $claim->fh_tada_request_plan->trp_unique_id ?? '' }}&nbsp;|&nbsp;
                                                                            @php
                                                                                $daAmount = $claim->tc_da_amount ?? 0;
                                                                            @endphp
                                                                            DA Amount : ₹ {{ $daAmount }}
                                                                        </span>
                                                                        <span class="acc-angle"><i class="fe fe-chevron-right"></i></span>
                                                                    </a>
                                                                </h5>
                                                            </div>
                                                            <div aria-labelledby="heading-{{ $index }}" class="collapse" data-parent="#accordion-{{ $index }}" id="collapse-{{ $index }}" role="tabpanel">
                                                                <div class="acc-body">
                                                                    @if (json_decode($claim->tc_da_calculation_message,true))
                                                                        @foreach (json_decode($claim->tc_da_calculation_message,true) as $info)
                                                                            <div class="card-body  outer-border position-relative pb-0 my-5">
                                                                                <span class="badge badge-success position-absolute">₹
                                                                                    {{ $info['amount'] }}/-
                                                                                </span>
                                                                                <div class="row mt-2">
                                                                                    <div class="col-md-6">
                                                                                        <ul class="">
                                                                                            @if (($info['hours']))
                                                                                                <li class="mt-1">
                                                                                                    <span class="font-weight-semibold fs-18 ms-3">
                                                                                                        <i class="fa fa-clock-o"
                                                                                                            aria-hidden="true"></i>
                                                                                                        <span
                                                                                                            class="badge badge badge-info-light">Total Hours: {{ number_format($info['hours'],2) }}</span>
                                                                                                    </span>
                                                                                                </li>
                                                                                            @endif
                                                                                            @if ($info['stay'])
                                                                                                <li class="mt-1">
                                                                                                    <span class="font-weight-semibold fs-18 ms-3"> <i class="fa fa-bed"  aria-hidden="true"></i>
                                                                                                    <span class="badge badge badge-info-light">Stay: {{ $info['stay'] ? 'Yes' :  'No'}}</span>
                                                                                                    @if($info['stay'])
                                                                                                        <span class="badge badge badge-info-light">Arranged By: {{ $info['stay_arranged_by'] }}</span>
                                                                                                        @endif
                                                                                                    </span>
                                                                                                </li>
                                                                                            @endif

                                                                                            @if($info['day_start'] || $info['day_end'])
                                                                                                <li class="mt-1">
                                                                                                    <span
                                                                                                        class="font-weight-semibold fs-16 ms-3">
                                                                                                        <small class="text-muted">
                                                                                                        @if($info['day_start'])Day Start: &nbsp;{{ Carbon::parse($info['day_start'])->format('d-M-Y g:i A') }}@endif ||
                                                                                                        @if(($info['day_end']))Day End: &nbsp;{{ Carbon::parse($info['day_end'])->format('d-M-Y g:i A') }}@endif
                                                                                                        </small>
                                                                                                    </span>
                                                                                                </li>
                                                                                            @endif

                                                                                            @if($info['start_date'] || $info['end_date'])
                                                                                                <li class="mt-1">
                                                                                                    <span
                                                                                                        class="font-weight-semibold fs-16 ms-3">
                                                                                                        <small class="text-muted">
                                                                                                        @if($info['start_date'])Start Date: &nbsp;{{ Carbon::parse($info['start_date'])->format('d-M-Y') }}@endif ||
                                                                                                        @if(($info['end_date']))End Date: &nbsp;{{ Carbon::parse($info['end_date'])->format('d-M-Y') }}@endif
                                                                                                        </small>
                                                                                                    </span>
                                                                                                </li>
                                                                                            @endif

                                                                                            @if($info['total_distance'])
                                                                                                <li class="mt-1">
                                                                                                     <span
                                                                                                        class="font-weight-semibold fs-16 ms-3">
                                                                                                        <small class="text-muted">Total Distance: &nbsp;{{ $info['total_distance'] }}</small>
                                                                                                    </span>
                                                                                                </li>
                                                                                            @endif
                                                                                            @if($info['days'])
                                                                                                <li class="mt-1">
                                                                                                     <span
                                                                                                        class="font-weight-semibold fs-16 ms-3">
                                                                                                        <small class="text-muted">Days: &nbsp;{{ $info['days'] }}</small>
                                                                                                    </span>
                                                                                                </li>
                                                                                            @endif
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        No Expense Added
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>

                                            <div class="tab-pane" id="tabSelfTravelDetails">
                                                @foreach ($claimData as $index => $claim)
                                                    @php
                                                        $rawPlanData = $claim->fh_tada_request_plan;
                                                        $rawTravelDetails = $rawPlanData->fh_tada_request_details ?? [];
                                                    @endphp
                                                    <div class="card-body">
                                                        <div aria-multiselectable="true" class="accordion" id="travelAccordion-{{ $index }}" role="tablist">
                                                            <div class="acc-card mb-4">
                                                                <div class="acc-header" id="travelHeading-{{ $index }}" role="tab">
                                                                    <h5 class="mb-0">
                                                                        <a aria-controls="travelCollapse-{{ $index }}"
                                                                           aria-expanded="false"
                                                                           data-bs-toggle="collapse"
                                                                           href="#travelCollapse-{{ $index }}"
                                                                           class="d-flex justify-content-between align-items-center">
                                                                            <span>
                                                                                <i class="fa fa-random me-2"></i>
                                                                                <!--<i class="fa fa-route me-2"></i>-->
                                                                                TA (Travel Details) : {{ $rawPlanData->trp_unique_id ?? '' }}
                                                                            </span>
                                                                            <span class="acc-angle"><i class="fe fe-chevron-right"></i></span>
                                                                        </a>
                                                                    </h5>
                                                                </div>

                                                                <div aria-labelledby="travelHeading-{{ $index }}"
                                                                     class="collapse"
                                                                     data-parent="#travelAccordion-{{ $index }}"
                                                                     id="travelCollapse-{{ $index }}"
                                                                     role="tabpanel">
                                                                    <div class="acc-body">
                                                                        @if (count($rawTravelDetails) > 0)
                                                                            @foreach ($rawTravelDetails as $tdIndex => $travelDetailsItem)
                                                                                <div class="card mb-3">
                                                                                    <div class="card-header bg-light py-2">
                                                                                        <h6 class="mb-0">
                                                                                            <i class="fa fa-map-marker-alt me-2"></i>
                                                                                            Trip {{ $tdIndex + 1 }} -
                                                                                            @if($travelDetailsItem->trd_segments)
                                                                                                Tracked Locations
                                                                                            @else
                                                                                                Manual Entry
                                                                                            @endif
                                                                                        </h6>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        @if ($travelDetailsItem->trd_segments != null)
                                                                                            <div class="position-relative pb-0">
                                                                                                <div class="row mt-2">
                                                                                                    <div class="col-md-7">
                                                                                                        <div class="row">
                                                                                                            <div class="col-md-12">
                                                                                                                <p class="font-weight-bold">
                                                                                                                    <i class="fa fa-map-marked-alt"></i> Location History
                                                                                                                </p>
                                                                                                                <ul class="timeline" style="max-height: 200px; overflow-y: auto;">
                                                                                                                    @foreach (json_decode($travelDetailsItem->trd_segments) as $locIndex => $location)
                                                                                                                        <li class="{{ $locIndex % 2 == 0 ? 'primary' : 'success' }} my-2 py-0">
                                                                                                                            <span class="font-weight-semibold fs-16 ms-3">
                                                                                                                                <i class="far fa-clock"></i> {{ $location->time ?? 'N/A' }}
                                                                                                                            </span>
                                                                                                                            <span class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                                                                <i class="fa fa-map-pin"></i>
                                                                                                                                {{ $location->latitude ?? 'N/A' }}, {{ $location->longitude ?? 'N/A' }}
                                                                                                                                ({{ $location->location ?? 'N/A' }})
                                                                                                                            </span>
                                                                                                                        </li>
                                                                                                                    @endforeach
                                                                                                                </ul>
                                                                                                                <button class="btn btn-info mt-3 travel-path-btn"
                                                                                                                        data-bs-toggle="modal"
                                                                                                                        data-bs-target="#largemodal"
                                                                                                                        data-total-distance="{{ $travelDetailsItem->trd_total_distance }}"
                                                                                                                        data-locations='@json($travelDetailsItem->trd_segments)'>
                                                                                                                    <i class="fa fa-route"></i> View Travel Path
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-md-1 pb-5">
                                                                                                        <span class="border-start border-info h-100 d-inline-block"></span>
                                                                                                    </div>
                                                                                                    <div class="col-md-4 text-end">
                                                                                                        <div class="travel-details-box">
                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Mode:</span>
                                                                                                                {{ $travelDetailsItem->fh_policy_tada_travel_mode->fh_travel_mode->m_name ?? 'N/A' }}
                                                                                                            </div>
                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Vehicle:</span>
                                                                                                                {{ $travelDetailsItem->fh_policy_tada_travel_vehicle->fh_vehicle->m_name ?? 'N/A' }}
                                                                                                            </div>
                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Distance:</span>
                                                                                                                {{ isset($travelDetailsItem->trd_total_distance) ? number_format($travelDetailsItem->trd_total_distance, 3) . ' KM' : 'N/A' }}
                                                                                                            </div>
                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Claim Type:</span>
                                                                                                                {{ $travelDetailsItem->fh_policy_tada_travel_vehicle->fh_claim_type->m_name ?? 'N/A' }}
                                                                                                            </div>
                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Applied On:</span>
                                                                                                                {{ $rawPlanData->created_at ? \Carbon\Carbon::parse($rawPlanData->created_at)->format('d-M-Y') : '' }}
                                                                                                            </div>

                                                                                                            <div class="amount-box bg-light p-2 mt-2 mb-2 border rounded">
                                                                                                                <div class="d-flex justify-content-between">
                                                                                                                    <span>Amount:</span>
                                                                                                                    <span>₹{{ $travelDetailsItem->trd_net_amount }}/-</span>
                                                                                                                </div>
                                                                                                                @if ($rawPlanData->fh_policy_tada_travel_type->pttt_type_id == 126)
                                                                                                                    <div class="d-flex justify-content-between mt-1">
                                                                                                                        <span>Country:</span>
                                                                                                                        <span>{{ $claim->fh_tada_request_plan->fh_tada_expenses->te_country_code ?? 'N/A' }}</span>
                                                                                                                    </div>
                                                                                                                    <div class="d-flex justify-content-between mt-1">
                                                                                                                        <span>Conversion Rate:</span>
                                                                                                                        <span>{{ $claim->fh_tada_request_plan->fh_tada_expenses->te_conversion_rate ?? 'N/A' }}</span>
                                                                                                                    </div>
                                                                                                                    <div class="d-flex justify-content-between mt-1">
                                                                                                                        <span>Amount (INR):</span>
                                                                                                                        <span>₹{{ $claim->fh_tada_request_plan->fh_tada_expenses->te_foreign_amount ?? 'N/A' }}/-</span>
                                                                                                                    </div>
                                                                                                                @endif
                                                                                                            </div>

                                                                                                            @php
                                                                                                                $documents = json_decode($travelDetailsItem->trd_documents, true);
                                                                                                                $docCount = is_array($documents) ? count($documents) : 0;
                                                                                                            @endphp

                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Documents:</span>
                                                                                                                @if ($docCount > 0)
                                                                                                                    <a href="#"
                                                                                                                       class="text-primary"
                                                                                                                       data-bs-toggle="modal"
                                                                                                                       data-bs-target="#documentsModal{{ $index }}{{ $tdIndex }}Travel">
                                                                                                                        View ({{ $docCount }})
                                                                                                                    </a>
                                                                                                                    @component('admin.components.document-modal', [
                                                                                                                        'id' => $index . $tdIndex . 'Travel',
                                                                                                                        'documents' => $documents,
                                                                                                                        'componentString' => 'travelDetailsItem_',
                                                                                                                    ])
                                                                                                                    @endcomponent
                                                                                                                @else
                                                                                                                    <span class="text-muted">None</span>
                                                                                                                @endif
                                                                                                            </div>

                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Remarks:</span>
                                                                                                                <span class="text-muted">{{ $travelDetailsItem->trd_remarks }}</span>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                                                @if ($docCount > 0)
                                                                                                    <div class="row mt-3">
                                                                                                        <div class="col-12">
                                                                                                            <div class="border-top pt-2">
                                                                                                                <h6><i class="fa fa-file-download"></i> Download Documents:</h6>
                                                                                                                <div class="row">
                                                                                                                    @foreach ($documents as $dkey => $document)
                                                                                                                        <div class="col-md-4 col-6 mb-2">
                                                                                                                            @php
                                                                                                                                $displayName = basename($document);
                                                                                                                            @endphp
                                                                                                                            <a href="{{ asset($document) }}"
                                                                                                                               target="_blank"
                                                                                                                               class="text-primary">
                                                                                                                                <i class="fa fa-download"></i>
                                                                                                                                Doc {{ $dkey + 1 }} ({{ Str::limit($displayName, 20) }})
                                                                                                                            </a>
                                                                                                                        </div>
                                                                                                                    @endforeach
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                @endif
                                                                                            </div>
                                                                                        @else
                                                                                            <div class="position-relative pb-0">
                                                                                                <div class="row mt-2">
                                                                                                    <div class="col-md-7">
                                                                                                        <div class="row">
                                                                                                            <div class="col-md-12">
                                                                                                                <p class="font-weight-bold">
                                                                                                                    <i class="fa fa-edit"></i> Manual Travel Details
                                                                                                                </p>
                                                                                                                <ul class="timeline">
                                                                                                                    <li class="primary py-0 my-0">
                                                                                                                        <span class="font-weight-semibold fs-16 ms-3">
                                                                                                                            <i class="fa fa-map-marker"></i> Source
                                                                                                                        </span>
                                                                                                                        <p class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                                                            {{ $travelDetailsItem->trd_source }}
                                                                                                                        </p>
                                                                                                                    </li>
                                                                                                                    <li class="success mt-6 py-0 my-0">
                                                                                                                        <span class="font-weight-semibold fs-16 ms-3">
                                                                                                                            <i class="fa fa-flag-checkered"></i> Destination
                                                                                                                        </span>
                                                                                                                        <p class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                                                            {{ $travelDetailsItem->trd_destination }}
                                                                                                                        </p>
                                                                                                                    </li>
                                                                                                                </ul>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-md-1 pb-5">
                                                                                                        <span class="border-start border-info h-100 d-inline-block"></span>
                                                                                                    </div>
                                                                                                    <div class="col-md-4 text-end">
                                                                                                        <div class="travel-details-box">
                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Distance:</span>
                                                                                                                {{ isset($travelDetailsItem->trd_total_distance) ? number_format($travelDetailsItem->trd_total_distance, 3) . ' KM' : 'N/A' }}
                                                                                                            </div>
                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Applied On:</span>
                                                                                                                {{ $rawPlanData->created_at ? \Carbon\Carbon::parse($rawPlanData->created_at)->format('d-M-Y') : '' }}
                                                                                                            </div>

                                                                                                            <div class="amount-box bg-light p-2 mt-2 mb-2 border rounded">
                                                                                                                <div class="d-flex justify-content-between">
                                                                                                                    <span>Amount:</span>
                                                                                                                    <span>₹{{ $travelDetailsItem->trd_net_amount }}/-</span>
                                                                                                                </div>
                                                                                                            </div>

                                                                                                            @php
                                                                                                                $documents = json_decode($travelDetailsItem->trd_documents, true);
                                                                                                                $docCount = is_array($documents) ? count($documents) : 0;
                                                                                                            @endphp

                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Documents:</span>
                                                                                                                @if ($docCount > 0)
                                                                                                                    <a href="#"
                                                                                                                       class="text-primary"
                                                                                                                       data-bs-toggle="modal"
                                                                                                                       data-bs-target="#documentsModal{{ $index }}{{ $tdIndex }}Travel">
                                                                                                                        View ({{ $docCount }})
                                                                                                                    </a>
                                                                                                                    @component('admin.components.document-modal', [
                                                                                                                        'id' => $index . $tdIndex . 'Travel',
                                                                                                                        'documents' => $documents,
                                                                                                                        'componentString' => 'travelDetailsItem_',
                                                                                                                    ])
                                                                                                                    @endcomponent
                                                                                                                @else
                                                                                                                    <span class="text-muted">None</span>
                                                                                                                @endif
                                                                                                            </div>

                                                                                                            <div class="detail-item">
                                                                                                                <span class="font-weight-bold">Remarks:</span>
                                                                                                                <span class="text-muted">{{ $travelDetailsItem->trd_remarks }}</span>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <div class="alert alert-info mb-0">
                                                                                <i class="fa fa-info-circle me-2"></i>
                                                                                No travel details found for this claim
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                          <!--  <div class="tab-pane" id="tabSelfTravelExp">
                                                @if (count($selfTravelExpenseType) > 0)
                                                    @foreach ($selfTravelExpenseType as $expense)
                                                        <div class="card-body my-5 outer-border position-relative pb-0">
                                                            <span class="badge badge-success position-absolute">₹
                                                                {{ $expense->te_amount + $expense->te_taxes }}/-</span>
                                                            <div class="row mt-2">
                                                                <div class="col-md-7">
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <ul class="">
                                                                                <li class="py-0 my-0"> <span class="font-weight-semibold fs-16 ms-3">
                                                                                    <small class="text-muted"><i
                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $expense->te_from_date ? Carbon::parse($expense->te_from_date)->format('d-M-Y') : '' }}
                                                                                        <br> <i
                                                                                            class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_from_time ? Carbon::parse($expense->te_from_time)->format('h:i A') : '' }}</small></span>
                                                                                </li>
                                                                                <li class="primary mt-6 py-0 my-0"> <span class="font-weight-semibold fs-16 ms-3">
                                                                                    <small class="text-muted"><i
                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d-M-Y') : '' }}
                                                                                        <br> <i
                                                                                            class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_to_time ? Carbon::parse($expense->te_to_time)->format('h:i A') : '' }}</small></span>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        @if ($expense->te_round_trip == 1)
                                                                        <div class="col-md-1" style="margin-top:2.1rem; padding-left: 2.6rem">
                                                                            <i class="fa fa-arrow-up" style="font-size: 20px; color: #007bff;"></i><br>
                                                                            <i class="fa fa-arrow-down" style="font-size: 20px; color: #007bff; margin-top: 5px;"></i>
                                                                        </div>
                                                                        @endif
                                                                        <div class="col-md-7">
                                                                            <ul class="timeline ">
                                                                                <li class=" primary py-0 my-0"> <span
                                                                                        class="font-weight-semibold fs-16 ms-3">Source</span>
                                                                                    <p
                                                                                        class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                        {{ $expense->te_from_location }}
                                                                                    </p>
                                                                                </li>
                                                                                <li class="success mt-6 py-0 my-0"> <span
                                                                                        class="font-weight-semibold fs-16 ms-3">Destination</span>
                                                                                    <p
                                                                                        class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                        {{ $expense->te_to_location }}</p>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-1 pb-5">
                                                                    <span
                                                                        class="border-start border-info h-100 d-inline-block"></span>
                                                                </div>
                                                                <div class="col-md-4  text-end">
                                                                    <div class="d-flex justify-content-end">
                                                                        <label class="form-label">Applied On: :&nbsp;
                                                                        </label>
                                                                        <span class="font-weight-bold">
                                                                            {{ $expense->te_date ? Carbon::parse($expense->te_date)->format('d-M-Y') : '' }}</span>
                                                                    </div>
                                                                    <div class="offset-md-5 bg-gray-100 border "
                                                                        style=" box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Amount:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_amount }}/-</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Tax:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_taxes }}/-</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="d-flex justify-content-end mt-2">
                                                                        <label class="form-label">Documents
                                                                            :&nbsp;
                                                                        </label>
                                                                        <span> @php
                                                                            $documents = json_decode(
                                                                                $expense->te_document,
                                                                                true,
                                                                            ); // Decode JSON into an array
                                                                        @endphp

                                                                            @if (is_array($documents) && count($documents) > 0)
                                                                                <a href="#" type="button"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#documentsModal{{ $loop->index .'tabSelfTravelExp' }}"
                                                                                    class="text-primary">
                                                                                    <u>View Documents</u>
                                                                                </a>
                                                                                <!-- Modal -->
                                                                                @component('admin.components.document-modal', [
                                                                                    'id' => $loop->index .'tabSelfTravelExp',
                                                                                    'documents' => $documents, // Pass the decoded documents array
                                                                                    'componentString' => 'Expense_',
                                                                                ])
                                                                                @endcomponent
                                                                            @else
                                                                                N/A
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    <div class="d-flex justify-content-end">
                                                                        <label class="form-label">Remarks
                                                                            :&nbsp;
                                                                        </label>
                                                                        <span
                                                                            class="text-muted">{{ $expense->te_remarks }}</span>
                                                                    </div>
                                                                    @if (isset($expense->fh_sub_expense))
                                                                        @php
                                                                            $subExpense = $expense->fh_sub_expense
                                                                        @endphp
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Expense Name
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span
                                                                            class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{$subExpense->tes_head}}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <span class="form-label">Documents: </span>
                                                            <ul class="text-start row">
                                                                @if (is_array($documents) && count($documents) > 0)
                                                                @foreach ($documents as $dkey => $document)
                                                                    @php
                                                                        $position = strpos($document, 'Expense_');
                                                                        if ($position !== false) {
                                                                            $displayName = substr($document, $position);
                                                                        } else {
                                                                            // If "PlanDetail_" is not found, use the full file name
                                                                            $displayName = basename($document);
                                                                        }
                                                                    @endphp
                                                                    <li class="col-4">  <a href="{{ asset($document) }}" target="_blank" class="text-primary"> *
                                                                        {{ $displayName }} - View file {{ $dkey + 1 }},
                                                                    </a>   </li>
                                                                @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>-->

                                            <div class="tab-pane" id="tabSelfMealExp">
                                                @foreach ($claimData as $index => $claim)
                                                    @php
                                                        // Get meal expenses for this specific claim (type ID = 160)
                                                        $selfMealExpenses = optional($claim->fh_tada_request_plan->fh_tada_expenses)
                                                            ->where('te_paid_by', 'self')
                                                            ->where('te_type_id', 160)
                                                            ->sortBy('te_date');
                                                    @endphp

                                                    <div class="card-body">
                                                        <div aria-multiselectable="true" class="accordion" id="mealAccordion-{{ $index }}" role="tablist">
                                                            <div class="acc-card mb-4">
                                                                <div class="acc-header" id="mealHeading-{{ $index }}" role="tab">
                                                                    <h5 class="mb-0">
                                                                        <a aria-controls="mealCollapse-{{ $index }}"
                                                                           aria-expanded="false"
                                                                           data-bs-toggle="collapse"
                                                                           href="#mealCollapse-{{ $index }}"
                                                                           class="d-flex justify-content-between align-items-center">
                                                                            <span>
                                                                                <!--<i class="fa fa-utensils me-2"></i>-->
                                                                                <i class="fa fa-random me-2"></i>
                                                                                {{ $claim->fh_tada_request_plan->trp_unique_id ?? '' }} - Meal Expenses
                                                                            </span>
                                                                            <span class="acc-angle"><i class="fa fa-chevron-right"></i></span>
                                                                        </a>
                                                                    </h5>
                                                                </div>

                                                                <div aria-labelledby="mealHeading-{{ $index }}"
                                                                     class="collapse"
                                                                     data-parent="#mealAccordion-{{ $index }}"
                                                                     id="mealCollapse-{{ $index }}"
                                                                     role="tabpanel">
                                                                    <div class="acc-body">
                                                                        @if ($selfMealExpenses && $selfMealExpenses->count() > 0)
                                                                            @foreach ($selfMealExpenses as $expenseIndex => $expense)
                                                                                <div class="card mb-3">
                                                                                    <div class="card-header bg-light py-2">
                                                                                        <h6 class="mb-0">
                                                                                            <i class="fa fa-calendar-alt me-2"></i>
                                                                                            Meal Expense - {{ $expense->te_date ? \Carbon\Carbon::parse($expense->te_date)->format('d M Y') : 'N/A' }}&nbsp;
                                                                                            <span class="badge bg-success float-end">
                                                                                                ₹{{ $expense->te_amount + $expense->te_taxes }}/-
                                                                                            </span>
                                                                                        </h6>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        <div class="row">
                                                                                            <!-- Date -->
                                                                                            <div class="col-md-3 col-6 mb-3">
                                                                                                <div class="d-flex align-items-center">
                                                                                                    <span class="font-weight-bold me-2"><i class="fa fa-calendar"></i> Date:</span>
                                                                                                    <span>{{ $expense->te_date ? \Carbon\Carbon::parse($expense->te_date)->format('d-M-Y') : 'N/A' }}</span>
                                                                                                </div>
                                                                                            </div>

                                                                                            <!-- Amount -->
                                                                                            <div class="col-md-3 col-6 mb-3">
                                                                                                <div class="d-flex align-items-center">
                                                                                                    <span class="font-weight-bold me-2"><i class="fas fa-money-bill-wave me-1"></i> Amount:</span>
                                                                                                    <span>₹{{ $expense->te_amount }}/-</span>
                                                                                                </div>
                                                                                            </div>

                                                                                            <!-- Tax -->
                                                                                           <!-- <div class="col-md-3 col-6 mb-3">
                                                                                                <div class="d-flex align-items-center">
                                                                                                    <span class="font-weight-bold me-2"><i class="fa fa-percentage"></i> Tax:</span>
                                                                                                    <span>₹{{ $expense->te_taxes }}/-</span>
                                                                                                </div>
                                                                                            </div>-->

                                                                                            <!-- Total -->
                                                                                            <div class="col-md-3 col-6 mb-3">
                                                                                                <div class="d-flex align-items-center">
                                                                                                    <span class="font-weight-bold me-2"><i class="fa fa-calculator"></i> Total:</span>
                                                                                                    <span>₹{{ $expense->te_amount + $expense->te_taxes }}/-</span>
                                                                                                </div>
                                                                                            </div>

                                                                                            <!-- Expense Type -->
                                                                                            @if (isset($expense->fh_sub_expense))
                                                                                                <div class="col-md-3 mb-3">
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="font-weight-bold me-2"><i class="fa fa-tag"></i> Type:</span>
                                                                                                        <span>
                                                                                                            ({{ $expense->fh_sub_expense->tes_code ?? 'N/A' }})
                                                                                                            {{ $expense->fh_sub_expense->tes_head ?? 'N/A' }}
                                                                                                        </span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endif

                                                                                            <!-- Documents -->
                                                                                            <div class="col-md-3 mb-3">
                                                                                                @php
                                                                                                    $documents = json_decode($expense->te_document, true);
                                                                                                    $docCount = is_array($documents) ? count($documents) : 0;
                                                                                                @endphp
                                                                                                <div class="d-flex align-items-center">
                                                                                                    <span class="font-weight-bold me-2"><i class="fa fa-file-alt"></i> Documents:</span>
                                                                                                    @if ($docCount > 0)
                                                                                                        <a href="#"
                                                                                                           class="text-primary"
                                                                                                           data-bs-toggle="modal"
                                                                                                           data-bs-target="#documentsModal{{ $index }}{{ $expenseIndex }}Meal">
                                                                                                            View ({{ $docCount }})
                                                                                                        </a>
                                                                                                        <!-- Modal Component -->
                                                                                                        @component('admin.components.document-modal', [
                                                                                                            'id' => $index . $expenseIndex . 'Meal',
                                                                                                            'documents' => $documents,
                                                                                                            'componentString' => 'Expense_',
                                                                                                        ])
                                                                                                        @endcomponent
                                                                                                    @else
                                                                                                        <span class="text-muted">None</span>
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>

                                                                                            <!-- Remarks -->
                                                                                            <div class="col-6 mb-3">
                                                                                                <div class="d-flex">
                                                                                                    <span class="font-weight-bold me-2"><i class="fa fa-comment"></i> Remarks:</span>
                                                                                                    <span class="text-muted">{{ $expense->te_remarks ?: 'None' }}</span>
                                                                                                </div>
                                                                                            </div>

                                                                                            <!-- Document List -->
                                                                                            @if ($docCount > 0)
                                                                                                <div class="col-12 mt-3">
                                                                                                    <div class="border-top pt-2">
                                                                                                        <h6><i class="fa fa-file-download"></i> Download Documents:</h6>
                                                                                                        <div class="row">
                                                                                                            @foreach ($documents as $dkey => $document)
                                                                                                                <div class="col-md-4 col-6 mb-2">
                                                                                                                    @php
                                                                                                                        $displayName = basename($document);
                                                                                                                    @endphp
                                                                                                                    <a href="{{ asset($document) }}"
                                                                                                                       target="_blank"
                                                                                                                       class="text-primary">
                                                                                                                        <i class="fa fa-download"></i>
                                                                                                                        Doc {{ $dkey + 1 }} ({{ Str::limit($displayName, 20) }})
                                                                                                                    </a>
                                                                                                                </div>
                                                                                                            @endforeach
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <div class="alert alert-info mb-0">
                                                                                <i class="fa fa-info-circle me-2"></i>
                                                                                @if (optional($claim->fh_tada_request_plan->fh_tada_expenses)->where('te_paid_by', 'self')->count() > 0)
                                                                                    Found self-paid expenses but none are meal type (ID: 160)
                                                                                @else
                                                                                    No meal expenses found for this claim
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach

                                                @if ($claimData->count() == 0)
                                                    <div class="alert alert-warning">
                                                        <i class="fa fa-exclamation-triangle me-2"></i>
                                                        No claims found in the system
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="tab-pane" id="tabSelfMiscellaneousExp">
                                                @if ($claimData->count() > 0)
                                                    @foreach ($claimData as $claimIndex => $claim)
                                                        @php
                                                            // Get all expenses for this claim
                                                            $expenses = optional($claim->fh_tada_request_plan->fh_tada_expenses);

                                                            // Filter only self-paid miscellaneous expenses (type ID = 161)
                                                            $selfMiscExpenses = $expenses->where('te_paid_by', 'self')
                                                                                       ->where('te_type_id', 161)
                                                                                       ->sortBy('te_date');
                                                        @endphp

                                                        <div class="card-body">
                                                            <div aria-multiselectable="true" class="accordion" id="miscAccordion-{{ $claimIndex }}" role="tablist">
                                                                <div class="acc-card mb-4">
                                                                    <div class="acc-header" id="miscHeading-{{ $claimIndex }}" role="tab">
                                                                        <h5 class="mb-0">
                                                                            <a aria-controls="miscCollapse-{{ $claimIndex }}"
                                                                               aria-expanded="false"
                                                                               data-bs-toggle="collapse"
                                                                               href="#miscCollapse-{{ $claimIndex }}"
                                                                               class="d-flex justify-content-between align-items-center">
                                                                                <span>
                                                                                    <i class="fa fa-random me-2"></i>
                                                                                    {{ $claim->fh_tada_request_plan->trp_unique_id ?? '' }} - Miscellaneous Expenses
                                                                                </span>
                                                                                <span class="acc-angle"><i class="fe fe-chevron-right"></i></span>
                                                                            </a>
                                                                        </h5>
                                                                    </div>

                                                                    <div aria-labelledby="miscHeading-{{ $claimIndex }}"
                                                                         class="collapse"
                                                                         data-parent="#miscAccordion-{{ $claimIndex }}"
                                                                         id="miscCollapse-{{ $claimIndex }}"
                                                                         role="tabpanel">
                                                                        <div class="acc-body">
                                                                            @if ($selfMiscExpenses && $selfMiscExpenses->count() > 0)
                                                                                @foreach ($selfMiscExpenses as $expenseIndex => $expense)
                                                                                    <div class="card mb-3">
                                                                                        <div class="card-header bg-light py-2">
                                                                                            <h6 class="mb-0">
                                                                                                <i class="fa fa-tag me-2"></i>
                                                                                                {{ $expense->te_name ?? 'Miscellaneous Expense' }} &nbsp;
                                                                                                <span class="badge bg-success float-end">
                                                                                                    ₹{{ $expense->te_amount + $expense->te_taxes }}/-
                                                                                                </span>
                                                                                            </h6>
                                                                                        </div>
                                                                                        <div class="card-body">
                                                                                            <div class="row">
                                                                                                <!-- Expense Name -->
                                                                                                <div class="col-md-3 mb-3">
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="font-weight-bold me-2"><i class="fa fa-tag"></i> Expense:</span>
                                                                                                        <span>{{ $expense->te_name ?? 'N/A' }}</span>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <!-- Applied Date -->
                                                                                                <div class="col-md-3 mb-3">
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="font-weight-bold me-2"><i class="fa fa-calendar"></i> Applied On:</span>
                                                                                                        <span>{{ $expense->te_date ? Carbon::parse($expense->te_date)->format('d-M-Y') : 'N/A' }}</span>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <!-- Date Range -->
                                                                                                @if($expense->te_from_date || $expense->te_to_date)
                                                                                                <div class="col-md-3 mb-3">
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="font-weight-bold me-2"><i class="far fa-calendar-alt"></i> Date Range:</span>
                                                                                                        <span>
                                                                                                            @if($expense->te_from_date)
                                                                                                                {{ Carbon::parse($expense->te_from_date)->format('d-M-Y') }}
                                                                                                                @if($expense->te_from_time)
                                                                                                                    {{ Carbon::parse($expense->te_from_time)->format('h:i A') }}
                                                                                                                @endif
                                                                                                            @endif

                                                                                                            @if($expense->te_to_date)
                                                                                                                to {{ Carbon::parse($expense->te_to_date)->format('d-M-Y') }}
                                                                                                                @if($expense->te_to_time)
                                                                                                                    {{ Carbon::parse($expense->te_to_time)->format('h:i A') }}
                                                                                                                @endif
                                                                                                            @endif
                                                                                                        </span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                @endif

                                                                                                <!-- Amount -->
                                                                                                <div class="col-md-3 col-6 mb-3">
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="font-weight-bold me-2"><i class="fa fa-money"></i> Amount:</span>
                                                                                                        <span>₹{{ $expense->te_amount }}/-</span>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <!-- Tax -->
                                                                                               <!-- <div class="col-md-3 col-6 mb-3">
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="font-weight-bold me-2"><i class="fa fa-percentage"></i> Tax:</span>
                                                                                                        <span>₹{{ $expense->te_taxes }}/-</span>
                                                                                                    </div>
                                                                                                </div>-->

                                                                                                <!-- Total -->
                                                                                                <div class="col-md-3 col-6 mb-3">
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="font-weight-bold me-2"><i class="fa fa-calculator"></i> Total:</span>
                                                                                                        <span>₹{{ $expense->te_amount + $expense->te_taxes }}/-</span>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <!-- Expense Type -->
                                                                                                @if (isset($expense->fh_sub_expense))
                                                                                                    <div class="col-md-3 mb-3">
                                                                                                        <div class="d-flex align-items-center">
                                                                                                            <span class="font-weight-bold me-2"><i class="fa fa-tags"></i> Type:</span>
                                                                                                            <span>
                                                                                                                ({{ $expense->fh_sub_expense->tes_code ?? 'N/A' }})
                                                                                                                {{ $expense->fh_sub_expense->tes_head ?? 'N/A' }}
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                @endif

                                                                                                <!-- Documents -->
                                                                                                <div class="col-md-3 mb-3">
                                                                                                    @php
                                                                                                        $documents = json_decode($expense->te_document, true);
                                                                                                        $docCount = is_array($documents) ? count($documents) : 0;
                                                                                                    @endphp
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="font-weight-bold me-2"><i class="fa fa-file"></i> Documents:</span>
                                                                                                        @if ($docCount > 0)
                                                                                                            <a href="#"
                                                                                                               class="text-primary"
                                                                                                               data-bs-toggle="modal"
                                                                                                               data-bs-target="#documentsModal{{ $claimIndex }}{{ $expenseIndex }}Misc">
                                                                                                                View ({{ $docCount }})
                                                                                                            </a>
                                                                                                            <!-- Modal Component -->
                                                                                                            @component('admin.components.document-modal', [
                                                                                                                'id' => $claimIndex . $expenseIndex . 'Misc',
                                                                                                                'documents' => $documents,
                                                                                                                'componentString' => 'Expense_',
                                                                                                            ])
                                                                                                            @endcomponent
                                                                                                        @else
                                                                                                            <span class="text-muted">None</span>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </div>

                                                                                                <!-- Remarks -->
                                                                                                <div class="col-3 mb-3">
                                                                                                    <div class="d-flex">
                                                                                                        <span class="font-weight-bold me-2"><i class="fa fa-comment"></i> Remarks:</span>
                                                                                                        <span class="text-muted">{{ $expense->te_remarks ?: 'None' }}</span>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <!-- Document List -->
                                                                                                @if ($docCount > 0)
                                                                                                    <div class="col-12 mt-3">
                                                                                                        <div class="border-top pt-2">
                                                                                                            <h6><i class="fa fa-file-download"></i> Download Documents:</h6>
                                                                                                            <div class="row">
                                                                                                                @foreach ($documents as $dkey => $document)
                                                                                                                    <div class="col-md-4 col-6 mb-2">
                                                                                                                        @php
                                                                                                                            $displayName = basename($document);
                                                                                                                        @endphp
                                                                                                                        <a href="{{ asset($document) }}"
                                                                                                                           target="_blank"
                                                                                                                           class="text-primary">
                                                                                                                            <i class="fa fa-download"></i>
                                                                                                                            Doc {{ $dkey + 1 }} ({{ Str::limit($displayName, 20) }})
                                                                                                                        </a>
                                                                                                                    </div>
                                                                                                                @endforeach
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            @else
                                                                                <div class="mb-0">
                                                                                    <i class="fa fa-info-circle me-2"></i>
                                                                                    No miscellaneous expenses found.
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="alert alert-warning">
                                                        <i class="fa fa-exclamation-triangle me-2"></i>
                                                        No claims found in the system
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="tab-pane" id="tabSelfLodgingExp">
                                                <!-- Lodging Expenses -->
                                                @if (count($selfLodgingExpenseType) > 0)
                                                    @foreach ($selfLodgingExpenseType as $expenseType => $expense)
                                                        <div class="card-body  outer-border position-relative pb-0 my-5">
                                                            <span class="badge badge-success position-absolute">₹
                                                                {{ ($expense->te_amount ?? 0) + ($expense->te_taxes ?? 0) }}/-
                                                            </span>
                                                            <div class="row mt-2">
                                                                <div class="col-md-6">
                                                                    <ul class="">
                                                                        <li class="mt-1">
                                                                            <span class="font-weight-semibold fs-18 ms-3">
                                                                                <i class="fa fa-bed"
                                                                                    aria-hidden="true"></i>
                                                                                <span
                                                                                    class="badge badge badge-info-light">{{ $expense->te_hotel_name }}</span>
                                                                            </span>
                                                                        </li>
                                                                        <li class="mt-1">
                                                                            <p class="mb-0 pb-0  fs-14 ms-3"> <i
                                                                                    class="fa fa-map-marker"
                                                                                    aria-hidden="true"></i>
                                                                                &nbsp;{{ $expense->te_to_location }} </p>
                                                                        </li>
                                                                        <li class="mt-1">
                                                                            <span class="font-weight-semibold fs-16 ms-3">
                                                                                <smal><i class="fe fe-users"></i> Occupancy
                                                                                    :
                                                                                    <b>{{ ucfirst($expense->te_occupancy) }}</b>
                                                                                    </small>
                                                                            </span>
                                                                        </li>
                                                                        <li class="mt-1">
                                                                            <span
                                                                                class="font-weight-semibold fs-16 ms-3"><small
                                                                                    class="text-muted"><i
                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $expense->te_from_date ? Carbon::parse($expense->te_from_date)->format('d-M-Y') : '' }}<i
                                                                                        class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_from_time ? Carbon::parse($expense->te_from_time)->format('h:i A') : '' }}&nbsp;To</small></span>
                                                                        </li>
                                                                        <li class="mt-1">
                                                                            <span class="font-weight-semibold fs-16 ms-3">
                                                                                <small class="text-muted"><i class="fa fa-calendar"></i>&nbsp;{{ $expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d-M-Y') : '' }}<i class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_to_time ? Carbon::parse($expense->te_to_time)->format('h:i A') : '' }}
                                                                                    @if($expense->te_additional_info && json_decode($expense->te_additional_info, true))
                                                                                        <span class="badge badge-md badge-primary-light ms-1">{{json_decode($expense->te_additional_info, true)['lodging']['totalDays']}} days
                                                                                        </span>
                                                                                    @endif
                                                                                </small>
                                                                            </span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="col-md-6  text-end">
                                                                    <div class="d-flex justify-content-end">
                                                                        <label class="form-label">Applied On: :&nbsp;
                                                                        </label>
                                                                        <span
                                                                            class="font-weight-bold">{{ $expense->te_date ? Carbon::parse($expense->te_date)->format('d-M-Y') : '' }}</span>
                                                                    </div>
                                                                    <div class="offset-md-8 bg-gray-100 border "
                                                                        style=" box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Amount:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_amount }}/-</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Tax:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_taxes }}/-</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Deviation:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_deviation ?? 0 }}/-</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="d-flex justify-content-end mt-2">
                                                                        <label class="form-label">Documents :&nbsp;
                                                                        </label>
                                                                        @php
                                                                            $documents = json_decode(
                                                                                $expense->te_document,
                                                                                true,
                                                                            ); // Decode JSON into an array
                                                                        @endphp

                                                                        @if (is_array($documents) && count($documents) > 0)
                                                                            <a href="#" type="button"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#documentsModal{{ $loop->index .'tabSelfLodgingExp' }}"
                                                                                class="text-primary">
                                                                                <u>View Documents</u>
                                                                            </a>
                                                                            <!-- Modal -->
                                                                            @component('admin.components.document-modal', [
                                                                                'id' => $loop->index . 'tabSelfLodgingExp',
                                                                                'documents' => $documents, // Pass the decoded documents array
                                                                                'componentString' => 'Expense_',
                                                                            ])
                                                                            @endcomponent
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </div>
                                                                    <div class="d-flex justify-content-end">
                                                                        <label class="form-label">Remarks :&nbsp; </label>
                                                                        <span
                                                                            class="text-muted">{{ $expense->te_remarks }}</span>
                                                                    </div>
                                                                    @if (isset($expense->fh_sub_expense))
                                                                        @php
                                                                            $subExpense = $expense->fh_sub_expense
                                                                        @endphp
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Expense Name
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span
                                                                            class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{$subExpense->tes_head}}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <span class="form-label">Documents: </span>
                                                            <ul class="text-start row">
                                                                @foreach ($documents as $dkey => $document)
                                                                    @php
                                                                        $position = strpos($document, 'Expense_');
                                                                        if ($position !== false) {
                                                                            $displayName = substr($document, $position);
                                                                        } else {
                                                                            // If "PlanDetail_" is not found, use the full file name
                                                                            $displayName = basename($document);
                                                                        }
                                                                    @endphp
                                                                    <li class="col-4">  <a href="{{ asset($document) }}" target="_blank" class="text-primary"> *
                                                                        {{ $displayName }} - View file {{ $dkey + 1 }},
                                                                    </a>   </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>

                                            @foreach($claimData as $claim)
                                                @if ($claim->fh_deduction_log != null && count($claim->fh_deduction_log) > 0)
                                                    <div class="tab-pane" id="tabSelfDeductionExp">
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-striped card-table table-vcenter text-nowrap mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>ID</th>
                                                                            <th>Name</th>
                                                                            <th>Remark</th>
                                                                            <th>Expenses</th>
                                                                            <th>Deduction Amount</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($claim->fh_deduction_log as $index => $deductionItem)
                                                                            <tr>
                                                                                <td>{{$index+1}}</td>
                                                                                <td>{{$deductionItem->fh_employee->emp_full_name}}</td>
                                                                                <td>{{$deductionItem->dlog_remarks}}</td>
                                                                                <td>
                                                                                    @php
                                                                                        $dlog_id = $deductionItem->dlog_id ?? null;
                                                                                        $deduction = \App\Models\DeductionLog::where('dlog_id', $dlog_id)->first();
                                                                                    @endphp
                                                                                    @php
                                                                                        $filteredInfo = array_filter(json_decode($deduction->dlog_additional_info, true), fn($v) => $v != 0);
                                                                                    @endphp

                                                                                    @foreach ($filteredInfo as $key => $keyItem)
                                                                                        {{ \App\Models\MasterTable::find($key)->m_name ?? 'Unknown' }} : ₹{{ $keyItem }}@if (!$loop->last), @endif
                                                                                    @endforeach
                                                                                </td>
                                                                                <td>{{$deductionItem->dlog_deduction_amount ?? 0}}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div><!-- bd -->
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Claim Paid By Self End --}}

                        {{-- Claim Paid By Company Start --}}
                        <div class="tab-pane " id="tabCompany">
                            <div class="card-body">
                                <div class="panel panel-primary tabs-style-3">
                                    <div class="tab-menu-heading">
                                        <div class="tabs-menu ">
                                            <!-- Tabs -->
                                            <ul class="nav panel-tabs">
                                                <li><a href="#tabCompanyTravelExp" class="active" data-bs-toggle="tab"><i
                                                            class="fa fa-cube"></i>&nbsp;Travel Expenses</a></li>
                                                <li><a href="#tabCompanyMealExp" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;Meal Expenses</a></li>
                                                <li><a href="#tabCompanyOtherExp" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;Other Expenses</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    @php
                                        // Get the first claim from the collection
                                        $firstClaim = $claimData->first();

                                        // Check if the request plan exists before accessing it
                                        $rawExpenseData = $firstClaim->fh_tada_request_plan->fh_tada_expenses ?? collect();

                                        $companyLodgingExpenseType = $rawExpenseData
                                            ->where('te_paid_by', 'company')
                                            ->where('te_type_id', 158);

                                        $companyTravelExpenseType = $rawExpenseData
                                            ->where('te_paid_by', 'company')
                                            ->where('te_type_id', 159);

                                        $companyMealExpenseType = $rawExpenseData
                                            ->where('te_paid_by', 'company')
                                            ->where('te_type_id', 160);

                                        $companyOtherExpenseType = $rawExpenseData
                                            ->where('te_paid_by', 'company')
                                            ->where('te_type_id', 161);

                                        $companyExpenseTypes = $rawExpenseData
                                            ->where('te_paid_by', 'company')
                                            ->where('te_type_id', 158);
                                    @endphp
                                    <div class="panel-body tabs-menu-body">
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="tabCompanyTravelExp">
                                                @if (count($companyTravelExpenseType) > 0)
                                                    @foreach ($companyTravelExpenseType as $expense)
                                                        <div class="card-body my-5 outer-border position-relative pb-0">
                                                            <span class="badge badge-success position-absolute">₹
                                                                {{ $expense->te_amount + $expense->te_taxes }}/-</span>
                                                            <div class="row mt-2">
                                                                <div class="col-md-7">
                                                                    <div class="row">
                                                                        <div class="col-md-5">
                                                                            <ul class="">
                                                                                <li class="py-0 my-0"> <span
                                                                                        class="font-weight-semibold fs-16 ms-3">
                                                                                        <small class="text-muted"><i
                                                                                                class="fa fa-calendar"></i>&nbsp;{{ $expense->te_from_date ? Carbon::parse($expense->te_from_date)->format('d-M-Y') : '' }}
                                                                                            <br> <i
                                                                                                class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_from_time ? Carbon::parse($expense->te_from_time)->format('h:i A') : '' }}</small></span>
                                                                                </li>
                                                                                <li class="primary mt-6 py-0 my-0"> <span
                                                                                        class="font-weight-semibold fs-16 ms-3"><small
                                                                                            class="text-muted"><i
                                                                                                class="fa fa-calendar"></i>&nbsp;{{ $expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d-M-Y') : '' }}
                                                                                            <br> <i
                                                                                                class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_to_time ? Carbon::parse($expense->te_to_time)->format('h:i A') : '' }}</small></span>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        <div class="col-md-7">
                                                                            <ul class="timeline ">
                                                                                <li class=" primary py-0 my-0"> <span
                                                                                        class="font-weight-semibold fs-16 ms-3">Source</span>
                                                                                    <p
                                                                                        class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                        {{ $expense->te_from_location }}
                                                                                    </p>
                                                                                </li>
                                                                                <li class="success mt-6 py-0 my-0"> <span
                                                                                        class="font-weight-semibold fs-16 ms-3">Destination</span>
                                                                                    <p
                                                                                        class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                        {{ $expense->te_to_location }}</p>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-1 pb-5">
                                                                    <span
                                                                        class="border-start border-info h-100 d-inline-block"></span>
                                                                </div>
                                                                <div class="col-md-4  text-end">
                                                                    <div class="d-flex justify-content-end">
                                                                        <label class="form-label">Applied On: :&nbsp;
                                                                        </label>
                                                                        <span class="font-weight-bold">
                                                                            {{ $expense->te_date ? Carbon::parse($expense->te_date)->format('d-M-Y') : '' }}</span>
                                                                    </div>
                                                                    <div class="offset-md-5 bg-gray-100 border "
                                                                        style=" box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Amount:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_amount }}/-</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Tax:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_taxes }}/-</span>
                                                                        </div>
                                                                    </div>
                                                             {{--******** for international trip *******--}}
                                                                    @if ($claimData->fh_tada_request_plan->fh_policy_tada_travel_type->pttt_type_id == 126)
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Country Code:&nbsp;</label>
                                                                            <span class="font-weight-bold">
                                                                                {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_country_code }}/-</span>
                                                                            </div>
                                                                            <div class="d-flex justify-content-end">
                                                                                <label
                                                                                class="form-label mb-0">Conversion Rate To INR:&nbsp;</label>
                                                                            <span class="font-weight-bold">
                                                                                {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_conversion_rate }}/-</span>
                                                                            </div>
                                                                            <div class="d-flex justify-content-end">
                                                                                <label
                                                                                class="form-label mb-0">Amount In INR:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_foreign_amount }}/-</span>
                                                                        </div>
                                                                    @endif

                                                                    <div class="d-flex justify-content-end mt-2">
                                                                        <label class="form-label">Documents
                                                                            :&nbsp;
                                                                        </label>
                                                                        <span> @php
                                                                            $documents = json_decode(
                                                                                $expense->te_document,
                                                                                true,
                                                                            ); // Decode JSON into an array
                                                                        @endphp

                                                                            @if (is_array($documents) && count($documents) > 0)
                                                                                <a href="#" type="button"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#documentsModal{{ $loop->index . 'tabCompanyTravelExp' }}"
                                                                                    class="text-primary">
                                                                                    <u>View Documents</u>
                                                                                </a>
                                                                                <!-- Modal -->
                                                                                @component('admin.components.document-modal', [
                                                                                    'id' => $loop->index . 'tabCompanyTravelExp',
                                                                                    'documents' => $documents, // Pass the decoded documents array
                                                                                    'componentString' => 'Expense_',
                                                                                ])
                                                                                @endcomponent
                                                                            @else
                                                                                N/A
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    <div class="d-flex justify-content-end">
                                                                        <label class="form-label">Remarks
                                                                            :&nbsp;
                                                                        </label>
                                                                        <span
                                                                            class="text-muted">{{ $expense->te_remarks }}</span>
                                                                    </div>
                                                                    @if (isset($expense->fh_sub_expense))
                                                                        @php
                                                                            $subExpense = $expense->fh_sub_expense
                                                                        @endphp
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Expense Name
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span
                                                                            class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{$subExpense->tes_head}}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <span class="form-label">Documents: </span>
                                                            <ul class="text-start row">
                                                                @foreach ($documents as $dkey => $document)
                                                                    @php
                                                                        $position = strpos($document, 'Expense_');
                                                                        if ($position !== false) {
                                                                            $displayName = substr($document, $position);
                                                                        } else {
                                                                            // If "PlanDetail_" is not found, use the full file name
                                                                            $displayName = basename($document);
                                                                        }
                                                                    @endphp
                                                                    <li class="col-4">  <a href="{{ asset($document) }}" target="_blank" class="text-primary"> *
                                                                        {{ $displayName }} - View file {{ $dkey + 1 }},
                                                                    </a>   </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>
                                             <div class="tab-pane" id="tabCompanyMealExp">
                                                @if (count($companyMealExpenseType) > 0)
                                                    @foreach ($companyMealExpenseType as $expense)
                                                        <div class="card-body  outer-border position-relative pb-0 mb-3">
                                                            <span class="badge badge-success position-absolute">₹
                                                                {{ $expense->te_amount + $expense->te_taxes }}/-</span>
                                                            <div class="row mt-2">
                                                                <div class="col-md-3">
                                                                    <div class="d-flex ">
                                                                        <label class="form-label">Applied On: :&nbsp;
                                                                        </label>
                                                                        <span class="font-weight-bold">
                                                                            {{ $expense->te_date ? Carbon::parse($expense->te_date)->format('d-M-Y') : '' }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="d-flex">
                                                                        <label class="form-label">Documents
                                                                            :&nbsp;
                                                                        </label>
                                                                        <span>
                                                                            @php
                                                                                $documents = json_decode(
                                                                                    $expense->te_document,
                                                                                    true,
                                                                                ); // Decode JSON into an array
                                                                            @endphp

                                                                            @if (is_array($documents) && count($documents) > 0)
                                                                                <a href="#" type="button"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#documentsModal{{ $loop->index . 'tabCompanyMealExp' }}"
                                                                                    class="text-primary">
                                                                                    <u>View Documents</u>
                                                                                </a>
                                                                                <!-- Modal -->
                                                                                @component('admin.components.document-modal', [
                                                                                    'id' => $loop->index . 'tabCompanyMealExp',
                                                                                    'documents' => $documents, // Pass the decoded documents array
                                                                                    'componentString' => 'Expense_',
                                                                                ])
                                                                                @endcomponent
                                                                            @else
                                                                                N/A
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="d-flex ">
                                                                        <label
                                                                            class="form-label mb-0">Amount:&nbsp;</label>
                                                                        <span class="font-weight-bold">₹
                                                                            {{ $expense->te_amount }}/-</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="d-flex ">
                                                                        <label class="form-label mb-0">Tax:&nbsp;</label>
                                                                        <span class="font-weight-bold">₹
                                                                            {{ $expense->te_taxes }}/-</span>
                                                                    </div>
                                                                </div>
                                                                {{--******** for international trip *******--}}
                                                                @if ($claimData->fh_tada_request_plan->fh_policy_tada_travel_type->pttt_type_id == 126)
                                                                <div class="col-md-3">
                                                                    <div class="d-flex ">
                                                                        <label
                                                                        class="form-label mb-0">Country Code:&nbsp;</label>
                                                                    <span class="font-weight-bold">
                                                                        {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_country_code }}/-</span>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <div class="d-flex ">
                                                                        <label
                                                                        class="form-label mb-0">Conversion Rate To INR:&nbsp;</label>
                                                                    <span class="font-weight-bold">
                                                                        {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_conversion_rate }}/-</span>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <div class="d-flex ">
                                                                        <label
                                                                        class="form-label mb-0">Amount In INR:&nbsp;</label>
                                                                    <span class="font-weight-bold">₹
                                                                        {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_foreign_amount }}/-</span>
                                                                    </div>
                                                                </div>
                                                                @endif
                                                                @if (isset($expense->fh_sub_expense))
                                                                    @php
                                                                        $subExpense = $expense->fh_sub_expense
                                                                    @endphp
                                                                    <div class="col-md-3">
                                                                        <div class="d-flex ">
                                                                            <label class="form-label">Expense Name :&nbsp;
                                                                            </label>
                                                                            <span
                                                                            class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{$subExpense->tes_head}}</span>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                                <div class="col-md">
                                                                    <div class="d-flex ">
                                                                        <label class="form-label">Remarks :&nbsp;
                                                                        </label>
                                                                        <span class="text-muted">
                                                                            {{ $expense->te_remarks }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <span class="form-label">Documents: </span>
                                                            <ul class="text-start row">
                                                                @foreach ($documents as $dkey => $document)
                                                                    @php
                                                                        $position = strpos($document, 'Expense_');
                                                                        if ($position !== false) {
                                                                            $displayName = substr($document, $position);
                                                                        } else {
                                                                            // If "PlanDetail_" is not found, use the full file name
                                                                            $displayName = basename($document);
                                                                        }
                                                                    @endphp
                                                                    <li class="col-4">  <a href="{{ asset($document) }}" target="_blank" class="text-primary"> *
                                                                        {{ $displayName }} - View file {{ $dkey + 1 }},
                                                                    </a>   </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>
                                            <div class="tab-pane " id="tabCompanyOtherExp">
                                                @if (count($companyOtherExpenseType) > 0)
                                                    @foreach ($companyOtherExpenseType as $expense)
                                                        <div class="card-body  outer-border position-relative pb-0 mb-3">
                                                            <span class="badge badge-success position-absolute">₹
                                                                {{ $expense->te_amount + $expense->te_taxes }}/-
                                                            </span>
                                                            <div class="row mt-2">
                                                                <div class="col-md-6">
                                                                    <ul class="">
                                                                        <li class="mt-1">
                                                                            <span class="font-weight-semibold  ms-3">
                                                                                <smal>
                                                                                    Expense Name :</smal>
                                                                            </span>
                                                                            <p
                                                                                class="mb-0 pb-0 text-muted fs-17 ms-3 mt-1">
                                                                                <span class="badge badge badge-info-light">
                                                                                    {{ $expense->te_name }}</span> </span>
                                                                            </p>
                                                                        </li>
                                                                        <li class="mt-1">
                                                                            <span
                                                                                class="font-weight-semibold fs-16 ms-3"><small
                                                                                    class="text-muted"><i
                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $expense->te_from_date ? Carbon::parse($expense->te_from_date)->format('d-M-Y') : '' }}<i
                                                                                        class=" ms-3 fa fa-clock-o"></i>&nbsp;
                                                                                    {{ $expense->te_from_time ? Carbon::parse($expense->te_from_time)->format('h:i A') : '' }}&nbsp;To</small></span>
                                                                        </li>
                                                                        <li class="mt-1">
                                                                            <span
                                                                                class="font-weight-semibold fs-16 ms-3"><small
                                                                                    class="text-muted"><i
                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d-M-Y') : '' }}<i
                                                                                        class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_to_time ? Carbon::parse($expense->te_to_time)->format('h:i A') : '' }}<span
                                                                                        class="badge badge-md badge-primary-light ms-1"
                                                                                        hidden>135
                                                                                        days</span></small></span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="col-md-6  text-end">
                                                                    <div class="d-flex justify-content-end">
                                                                        <label class="form-label">Applied On:
                                                                            :&nbsp;
                                                                        </label>
                                                                        <span class="font-weight-bold">
                                                                            {{ $expense->te_date }}</span>
                                                                    </div>
                                                                    <div class="offset-md-8 bg-gray-100 border "
                                                                        style=" box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Amount:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_amount }}/-</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label
                                                                                class="form-label mb-0">Tax:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_taxes }}/-</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="d-flex justify-content-end mt-2">
                                                                        <label class="form-label">Documents
                                                                            :&nbsp;
                                                                        </label>
                                                                        <span>
                                                                            @php
                                                                                $documents = json_decode(
                                                                                    $expense->te_document,
                                                                                    true,
                                                                                ); // Decode JSON into an array
                                                                            @endphp

                                                                            @if (is_array($documents) && count($documents) > 0)
                                                                                <a href="#" type="button"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#documentsModal{{ $loop->index . 'tabCompanyOtherExp' }}"
                                                                                    class="text-primary">
                                                                                    <u>View Documents</u>
                                                                                </a>
                                                                                <!-- Modal -->
                                                                                @component('admin.components.document-modal', [
                                                                                    'id' => $loop->index . 'tabCompanyOtherExp',
                                                                                    'documents' => $documents, // Pass the decoded documents array
                                                                                    'componentString' => 'Expense_',
                                                                                ])
                                                                                @endcomponent
                                                                            @else
                                                                                N/A
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    <div class="d-flex justify-content-end">
                                                                        <label class="form-label">Remarks
                                                                            :&nbsp;
                                                                        </label>
                                                                        <span class="text-muted">
                                                                            {{ $expense->te_remarks }} </span>
                                                                    </div>
                                                                    @if (isset($expense->fh_sub_expense))
                                                                        @php
                                                                            $subExpense = $expense->fh_sub_expense
                                                                        @endphp
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Expense Name
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span class="text-muted" style="max-width: 300px; word-wrap: break-word; white-space: normal;">
                                                                                ({{ $subExpense->tes_code }})&nbsp;{{$subExpense->tes_head}}
                                                                            </span>

                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <span class="form-label">Documents: </span>
                                                            <ul class="text-start row">
                                                                @foreach ($documents as $dkey => $document)
                                                                    @php
                                                                        $position = strpos($document, 'Expense_');
                                                                        if ($position !== false) {
                                                                            $displayName = substr($document, $position);
                                                                        } else {
                                                                            // If "PlanDetail_" is not found, use the full file name
                                                                            $displayName = basename($document);
                                                                        }
                                                                    @endphp
                                                                    <li class="col-4">  <a href="{{ asset($document) }}" target="_blank" class="text-primary"> *
                                                                        {{ $displayName }} - View file {{ $dkey + 1 }},
                                                                    </a>   </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Claim Paid By Company End --}}
                    </div>
                </div>
            </div>
            {{-- Claim Paid By Start Selft OR Company End --}}
        </div>
        {{-- Claim Detail Card End --}}
    </div>
    <!-- END ROW -->
@endsection
@section('script')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>

        $(document).on('click', '.individualActionBtn', function() {
            // Store references to clicked elements
            const $clickedBtn = $(this);
            const $form = $clickedBtn.closest('.individualApprovalForm');
            const $claimContainer = $form.closest('.card-body');
            const inputs = document.querySelectorAll('.deduction-input');

            // Get all data attributes
            const dataAttributes = {};
            $.each(this.attributes, function() {
                if (this.name.startsWith('data-')) {
                    const key = this.name.slice(5);
                    dataAttributes[key] = this.value;
                }
            });

            // Validate message
            const message = $form.find('.individual-message').val().trim();
            if (!message) {
                Swal.fire({ icon: "warning", text: 'Message is required', timer: 3000 });
                return false;
            }

            // Prepare deductions (scoped to this claim only)
            const deductions = {};
            const payableAmounts = {};
            let deductionAmount = 0;
            let isValid = true;

            if (dataAttributes['approval_type'] == 1) {
                inputs.forEach(input => {
                    const $input = $(input);
                    // Get payable amount using jQuery
                    const payableAmount = parseFloat($input.data('payableamount')); // Ensure payableAmount is treated as a number
                    const key = input.name.match(/\[(.*?)\]/)[1]; // Extracts the exp_type_id
                    const value = parseFloat(input.value) || 0; // Gets the value or defaults to 0
                    if(value < 0){
                        isValid = false;
                        Swal.fire({
                            icon: "error",
                            text: 'Deduction amount must be positive.',
                            timer: 3000,
                        });
                    }
                    payableAmounts[key] = payableAmount;
                    deductions[key] = value; // Creates key-value pair
                    deductionAmount+=value;
                });

                if (!isValid) return false;
            }

            // Prepare request data
            const requestData = {
                _token: '{{ csrf_token() }}',
                POST_TYPE: 'CLAIM_REQUEST_APPROVAL',
                data: {
                    approval_status: dataAttributes['approval_status'],
                    approval_type: dataAttributes['approval_type'],
                    approval_action_type: dataAttributes['approval_action_type'],
                    approval_sequence: dataAttributes['approval_sequence'],
                    tc_id: dataAttributes['tc_id'],
                    module_id: dataAttributes['module_id'],
                    emp_d_id: dataAttributes['emp_d_id'],
                    is_last_approval: dataAttributes['is_last_approval'],
                    message: message,
                    deduction_amount: deductionAmount,
                    deduction_info: deductions,
                    payable_amount : payableAmounts,
                }
            };

            // Show loading state for this button only
            $clickedBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing');

            $.ajax({
                url: '{{ route("admin.approval-handler") }}',
                method: 'POST',
                data: requestData,
                dataType: 'json',
                success: function(response) {
                    $clickedBtn.prop('disabled', false).html($clickedBtn.data('original-text'));

                    if (response.status) {
                        Swal.fire({
                            icon: "success",
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            // Only hide/update the approved claim
                            if (response.approved) {
                                $form.html(`
                                    <div class="alert alert-success mb-0">
                                        <i class="fa fa-check-circle"></i> ${response.message}
                                    </div>
                                `);
                            }

                            // Optional: Update claim status display
                            $claimContainer.find('.claim-status').text('Approved').addClass('text-success');

                            // Only reload if specifically requested
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        });
                    } else {
                        Swal.fire({ icon: "error", text: response.message, timer: 3000 });
                    }
                },
                error: function(xhr) {
                    $clickedBtn.prop('disabled', false).html($clickedBtn.data('original-text'));
                    const errorMessage = xhr.responseJSON?.message || 'An error occurred';
                    Swal.fire({ icon: "error", text: errorMessage, timer: 3000 });
                }
            });
        });

        $(document).ready(function() {
            // Select All checkbox functionality
            $('#selectAllTravelIds').change(function() {
                $('.travel-id-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Bulk Approval/Rejection Handler - UPDATED
            $(document).on('click', '.bulkActionBtn', function() {
                const $clickedBtn = $(this);
                const approvalType = $clickedBtn.data('approval_type');
                const message = $('#bulkActionMessage').val().trim();

                // Get all checked claims
                const selectedClaims = $('.travel-id-checkbox:checked');

                // If no claims selected
                if (selectedClaims.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Claims Selected',
                        text: 'Please select at least one claim to perform bulk action',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Prepare deduction data with support for multiple deductions per claim
                let deductions = {};
                let payableAmounts = {};
                let claimDeductionAmounts = {};
                let isValid = true;

                if (approvalType == 1) { // Only for approval
                    selectedClaims.each(function() {
                        const claimId = $(this).val();
                        deductions[claimId] = {};
                        payableAmounts[claimId] = {};
                        claimDeductionAmounts[claimId] = 0;

                        // Find all deduction inputs for this specific claim
                        $(this).closest('.claim-container').find('.deduction-input').each(function() {
                            const $input = $(this);
                            const expTypeId = $input.attr('name').match(/\[(.*?)\]/)[1];
                            const amount = parseFloat($input.val()) || 0;
                            const payable = parseFloat($input.data('payableamount'));

                            if (amount < 0 || amount > payable) {
                                isValid = false;
                                Swal.fire({
                                    icon: "error",
                                    text: `Deduction for expense type ${expTypeId} must be between 0 and ${payable}`,
                                    timer: 3000
                                });
                                return false;
                            }

                            deductions[claimId][expTypeId] = amount;
                            payableAmounts[claimId][expTypeId] = payable;
                            claimDeductionAmounts[claimId] += amount;
                        });

                        if (!isValid) return false;
                    });

                    if (!isValid) return;
                }

                // Confirmation dialog
                Swal.fire({
                    title: approvalType == 1 ? 'Approve Selected Claims?' : 'Reject Selected Claims?',
                    text: `You are about to ${approvalType == 1 ? 'approve' : 'reject'} ${selectedClaims.length} claim(s)`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        $clickedBtn.prop('disabled', true)
                            .html(`<i class="fa fa-spinner fa-spin me-1"></i> Processing ${selectedClaims.length} Claims...`);

                        // Get all selected claim IDs
                        const claimIds = selectedClaims.map(function() {
                            return $(this).val();
                        }).get();

                        // Prepare request data with the correct structure
                        const requestData = {
                            _token: '{{ csrf_token() }}',
                            POST_TYPE: 'CLAIM_REQUEST_APPROVAL',
                            data: {
                                approval_status: $clickedBtn.data('approval_status'),
                                approval_type: approvalType,
                                approval_action_type: $clickedBtn.data('approval_action_type'),
                                approval_sequence: $clickedBtn.data('approval_sequence'),
                                module_id: $clickedBtn.data('module_id'),
                                is_last_approval: $clickedBtn.data('is_last_approval'),
                                emp_d_id: $clickedBtn.data('emp_d_id'),
                                message: message,
                                claim_ids: claimIds,
                                deduction_amount: claimDeductionAmounts,
                                deduction_info: deductions,
                                payable_amount: payableAmounts
                            }
                        };

                        console.log("Bulk Request Data:", requestData); // For debugging

                        // Make AJAX call to the correct endpoint
                        $.ajax({
                            url: '{{ route("claim-group.bulk.approved") }}',
                            method: 'POST',
                            data: requestData,
                            dataType: 'json',
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message,
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        // Update UI for each processed claim
                                        selectedClaims.each(function() {
                                            const $checkbox = $(this);
                                            const $container = $checkbox.closest('.claim-container');

                                            // Remove checkbox and approval section
                                            $checkbox.closest('.checkbox-placeholder').remove();
                                            $container.find('.approval-section').remove();

                                            // Update status display
                                            $container.find('.claim-status')
                                                .removeClass('bg-warning')
                                                .addClass(approvalType == 1 ? 'bg-success' : 'bg-danger')
                                                .text(approvalType == 1 ? 'Approved' : 'Rejected');
                                        });

                                        // Hide bulk form if no pending claims left
                                        if($('.travel-id-checkbox').length === 0) {
                                            $('#bulkApprovalForm, #selectAllTravelIds').closest('div').remove();
                                        } else {
                                            // Reset select all checkbox
                                            $('#selectAllTravelIds').prop('checked', false);
                                        }

                                        // Reload the page to reflect changes
                                        setTimeout(() => {
                                            location.reload();
                                        }, 1000);
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                const errorMessage = xhr.responseJSON?.message || 'An error occurred';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage,
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                $clickedBtn.prop('disabled', false)
                                    .html(approvalType == 1 ?
                                        '<i class="fa fa-check-circle me-1"></i> Approve All' :
                                        '<i class="fa fa-times-circle me-1"></i> Reject All');
                            }
                        });
                    }
                });
            });

            // Auto-update UI when individual checkboxes change
            $(document).on('change', '.travel-id-checkbox', function() {
                const anyUnchecked = $('.travel-id-checkbox:not(:checked)').length > 0;
                $('#selectAllTravelIds').prop('checked', !anyUnchecked);
            });
        });

        /*$(document).ready(function() {
            // Select All checkbox functionality
            $('#selectAllTravelIds').change(function() {
                $('.travel-id-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Bulk Approval/Rejection Handler
            $(document).on('click', '.bulkActionBtn', function() {
                const $clickedBtn = $(this);
                const approvalType = $(this).data('approval_type');
                const message = $('#bulkActionMessage').val().trim();

                // Get all checked claims
                const selectedClaims = $('.travel-id-checkbox:checked');

                // If no claims selected
                if (selectedClaims.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Claims Selected',
                        text: 'Please select at least one claim to perform bulk action',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Prepare deduction data with support for multiple deductions per claim
                let deductions = {};
                let payableAmounts = {};
                let claimDeductionAmounts = {}; // New object for per-claim totals
                let isValid = true;

                if (approvalType == 1) { // Only for approval
                    selectedClaims.each(function() {
                        const claimId = $(this).val();
                        deductions[claimId] = {};
                        payableAmounts[claimId] = {};
                        claimDeductionAmounts[claimId] = 0; // Initialize per-claim total

                        // Find all deduction inputs for this specific claim
                        $(this).closest('.claim-container').find('.deduction-input').each(function() {
                            const $input = $(this);
                            const expTypeId = $input.attr('name').match(/\[(.*?)\]/)[1];
                            const amount = parseFloat($input.val()) || 0;
                            const payable = parseFloat($input.data('payableamount'));

                            if (amount < 0 || amount > payable) {
                                isValid = false;
                                Swal.fire({
                                    icon: "error",
                                    text: `Deduction for expense type ${expTypeId} must be between 0 and ${payable}`,
                                    timer: 3000
                                });
                                return false;
                            }

                            deductions[claimId][expTypeId] = amount;
                            payableAmounts[claimId][expTypeId] = payable;
                            claimDeductionAmounts[claimId] += amount; // Add to per-claim total
                        });

                        if (!isValid) return false;
                    });

                    if (!isValid) return;
                }

                // Confirmation dialog
                Swal.fire({
                    title: approvalType == 1 ? 'Approve Selected Claims?' : 'Reject Selected Claims?',
                    text: `You are about to ${approvalType == 1 ? 'approve' : 'reject'} ${selectedClaims.length} claim(s)`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        $clickedBtn.prop('disabled', true)
                            .html(`<i class="fa fa-spinner fa-spin me-1"></i> Processing ${selectedClaims.length} Claims...`);

                        // Prepare request data with the exact structure you need
                        const requestData = {
                            _token: '{{ csrf_token() }}',
                            POST_TYPE: 'CLAIM_REQUEST_APPROVAL',
                            data: {
                                approval_status: $clickedBtn.data('approval_status'),
                                approval_type: approvalType,
                                approval_action_type: $clickedBtn.data('approval_action_type'),
                                approval_sequence: $clickedBtn.data('approval_sequence'),
                                module_id: $clickedBtn.data('module_id'),
                                is_last_approval: $clickedBtn.data('is_last_approval'),
                                emp_d_id: $clickedBtn.data('emp_d_id'),
                                message: message,
                                claim_ids: selectedClaims.map(function() {
                                    return $(this).val();
                                }).get(),
                                deduction_amount: claimDeductionAmounts, // Per-claim totals
                                deduction_info: deductions,              // Detailed deductions
                                payable_amount: payableAmounts           // Payable amounts
                            }
                        };

                        console.log("Request Data:", requestData); // For debugging

                        // Make AJAX call
                        $.ajax({
                            url: '{{ route("claim-group.bulk.approved") }}',
                            method: 'POST',
                            data: requestData,
                            dataType: 'json',
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message,
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        // Update UI for each processed claim
                                        selectedClaims.each(function() {
                                            const $checkbox = $(this);
                                            const $container = $checkbox.closest('.claim-container');

                                            // Remove checkbox and approval section
                                            $checkbox.remove();
                                            $container.find('.approval-section').remove();

                                            // Update status display
                                            $container.find('.claim-status')
                                                .removeClass('bg-warning')
                                                .addClass(approvalType ? 'bg-success' : 'bg-danger')
                                                .text(approvalType ? 'Approved' : 'Rejected');
                                        });

                                        // Hide bulk form if no pending claims left
                                        if($('.travel-id-checkbox').length === 0) {
                                            $('#bulkApprovalForm, #selectAllTravelIds').remove();
                                        } else {
                                            // Reset select all checkbox
                                            $('#selectAllTravelIds').prop('checked', false);
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                const errorMessage = xhr.responseJSON?.message || 'An error occurred';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage,
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                $clickedBtn.prop('disabled', false)
                                    .html(approvalType ?
                                        '<i class="fa fa-check-circle me-1"></i> Approve All' :
                                        '<i class="fa fa-times-circle me-1"></i> Reject All');
                            }
                        });
                    }
                });
            });

            // Auto-update UI when individual checkboxes change
            $(document).on('change', '.travel-id-checkbox', function() {
                const anyUnchecked = $('.travel-id-checkbox:not(:checked)').length > 0;
                $('#selectAllTravelIds').prop('checked', !anyUnchecked);
            });
        });*/

        // Prevent form submission on enter key in textarea
        $('#approvalForm').on('keyup keypress', function(e) {
            const keyCode = e.keyCode || e.which;
            if (keyCode === 13 && !$(e.target).is('textarea')) {
                e.preventDefault();
                return false;
            }
        });

        $(document).ready(function() {
            var map = null;
            var markers = [];
            var polyline = null;
            var routeSegments = [];

            $('#largemodal').on('shown.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var locations = button.data('locations');
                $('#totalDistance').text(button.data('total-distance'));

                if (!locations || locations.length === 0) {
                    alert("No travel data available.");
                    return;
                }

                // Parse JSON if needed
                var travelLocations = typeof locations === "string" ? JSON.parse(locations) : locations;

                // Remove previous map instance if exists
                if (map !== null) {
                    map.remove();
                    markers = [];
                    routeSegments = [];
                }

                // Additional parsing if needed
                if (typeof travelLocations === "string") {
                    try {
                        travelLocations = JSON.parse(travelLocations);
                    } catch (error) {
                        console.error("Error parsing travelLocations JSON:", error);
                        return;
                    }
                }

                if (!Array.isArray(travelLocations)) {
                    console.error("travelLocations is not an array:", travelLocations);
                    return;
                }

                if (!travelLocations[0]?.latitude || !travelLocations[0]?.longitude) {
                    console.error("Invalid location data format:", travelLocations[0]);
                    return;
                }

                // Initialize map
                map = L.map('map').setView([travelLocations[0].latitude, travelLocations[0].longitude], 6);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Clear previous data
                $('#locationList').empty();

                // Check if circular route
                const isCircular = travelLocations.length >= 2 &&
                    travelLocations[0].latitude === travelLocations[travelLocations.length - 1].latitude &&
                    travelLocations[0].longitude === travelLocations[travelLocations.length - 1].longitude;

                // Function to get actual route using OSRM
                async function getRoute(coordinates) {
                    try {
                        const coordsString = coordinates.map(coord => `${coord[1]},${coord[0]}`).join(';');
                        const response = await fetch(`https://router.project-osrm.org/route/v1/driving/${coordsString}?overview=full&geometries=geojson`);
                        const data = await response.json();

                        // Store individual legs for segment highlighting
                        if (data.routes[0].legs) {
                            routeSegments = data.routes[0].legs.map(leg => {
                                return leg.steps.map(step => {
                                    return step.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                                }).flat();
                            });
                        }

                        return data.routes[0].geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    } catch (error) {
                        console.error("Error fetching route:", error);
                        return coordinates; // Fallback to straight line
                    }
                }

                // Function to offset duplicate coordinates
                const getOffsetPosition = (lat, lng, count) => {
                    const offset = 0.0015;
                    const angle = (count * 30) * (Math.PI / 180);
                    return [lat + (offset * Math.sin(angle)), lng + (offset * Math.cos(angle))];
                };

                const positionCount = {};
                const coordinates = [];

                // Process locations and create markers
                travelLocations.forEach((location, index) => {
                    if (isCircular && index === travelLocations.length - 1) return;

                    const positionKey = `${location.latitude},${location.longitude}`;
                    positionCount[positionKey] = (positionCount[positionKey] || 0) + 1;

                    coordinates.push([location.latitude, location.longitude]);

                    const markerPosition = positionCount[positionKey] > 1 ?
                        getOffsetPosition(location.latitude, location.longitude, positionCount[positionKey]) :
                        [location.latitude, location.longitude];

                    // Marker styling
                    const markerColor = index === 0 ? 'green' :
                                     (index === travelLocations.length - 1 && !isCircular) ? 'red' : 'blue';

                    const marker = L.marker(markerPosition, {
                        icon: L.divIcon({
                            className: 'custom-marker',
                            html: `<div style="background-color:${markerColor};
                                         width: 24px;
                                         height: 24px;
                                         border-radius: 50%;
                                         display: flex;
                                         align-items: center;
                                         justify-content: center;
                                         color: white;
                                         font-weight: bold;
                                         border: 2px solid white;">${index + 1}</div>`,
                            iconSize: [24, 24]
                        })
                    }).addTo(map);

                    // Store reference to marker
                    markers.push(marker);

                    // Popup content
                    const formattedTime = location.time.replace(/\b(am|pm)\b/i, match => match.toUpperCase());
                    marker.bindPopup(`
                        <b>${location.location ?? "Point " + (index + 1)}</b><br>
                        Coordinates: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}<br>
                        Time: ${formattedTime}<br>
                        Distance: ${location.distance.toFixed(2)} KM
                    `);

                    // List item with click handler
                    const listItem = $(`
                        <li class="list-group-item location-item" style="font-size: 12px !important;" data-index="${index}">
                            <strong>${index + 1}. ${location.location ?? "Point " + (index + 1)}</strong>
                            (${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)})<br>
                            <span class="badge bg-info text-white">Time: ${formattedTime}</span>
                            <span class="badge bg-secondary">Distance: ${location.distance.toFixed(2)} KM</span>
                        </li>
                    `);

                    // Click handler for list items
                    listItem.on('click', function() {
                        const idx = $(this).data('index');
                        highlightPoint(idx);

                        // Zoom to show both current and next point if available
                        if (idx < markers.length - 1) {
                            const group = new L.featureGroup([markers[idx], markers[idx + 1]]);
                            map.fitBounds(group.getBounds(), {
                                padding: [50, 50],
                                maxZoom: 12
                            });
                        } else {
                            map.setView(markers[idx].getLatLng(), 12);
                        }
                    });

                    $('#locationList').append(listItem);
                });

                // Function to highlight a point
                function highlightPoint(index) {
                    // Reset all markers and list items
                    markers.forEach((marker, i) => {
                        const icon = marker.getIcon();
                        const html = icon.options.html.replace('box-shadow: 0 0 10px yellow;', '');
                        icon.options.html = html;
                        marker.setIcon(icon);

                        if (i === index) {
                            // Add highlight to selected marker
                            const selectedIcon = marker.getIcon();
                            selectedIcon.options.html = selectedIcon.options.html.replace('">', '" style="box-shadow: 0 0 10px yellow;">');
                            marker.setIcon(selectedIcon);

                            // Open popup and pan to marker
                            marker.openPopup();
                            map.panTo(marker.getLatLng(), {
                                animate: true,
                                duration: 1
                            });
                        }
                    });

                    // Highlight list item
                    $('.location-item').removeClass('active');
                    $(`.location-item[data-index="${index}"]`).addClass('active');
                }

                // Get actual route path (not straight line)
                getRoute(coordinates).then(routeCoordinates => {
                    // Remove existing polyline if any
                    if (polyline) {
                        map.removeLayer(polyline);
                    }

                    // Draw the actual route with dark blue color
                    polyline = L.polyline(routeCoordinates, {
                        color: '#0000FF', // Dark blue color
                        weight: 5, // Slightly thicker
                        opacity: 0.9,
                        smoothFactor: 1
                    }).addTo(map);

                    // Add click handler to the path
                    polyline.on('click', function(e) {
                        // Zoom to the clicked segment
                        if (routeSegments.length > 0) {
                            // Find the closest segment to the clicked point
                            const clickedLatLng = e.latlng;
                            let closestSegment = null;
                            let minDistance = Infinity;

                            routeSegments.forEach(segment => {
                                const segmentLine = L.polyline(segment);
                                const distance = segmentLine.distanceTo(clickedLatLng);

                                if (distance < minDistance) {
                                    minDistance = distance;
                                    closestSegment = segment;
                                }
                            });

                            if (closestSegment) {
                                const segmentBounds = L.polyline(closestSegment).getBounds();
                                map.fitBounds(segmentBounds, {
                                    padding: [50, 50],
                                    maxZoom: 14
                                });
                            }
                        } else {
                            // Fallback: zoom to the entire path
                            map.fitBounds(polyline.getBounds(), {
                                padding: [50, 50],
                                maxZoom: 12
                            });
                        }
                    });

                    // Fit bounds to route
                    map.fitBounds(polyline.getBounds(), {
                        padding: [50, 50]
                    });
                });

                // Add keyboard navigation
                $(document).on('keydown', function(e) {
                    if (e.key === 'ArrowRight') {
                        const current = $('.location-item.active').data('index') || 0;
                        const next = (current + 1) % markers.length;
                        highlightPoint(next);

                        // Zoom to show both current and next point
                        if (next < markers.length - 1) {
                            const group = new L.featureGroup([markers[next], markers[next + 1]]);
                            map.fitBounds(group.getBounds(), {
                                padding: [50, 50],
                                maxZoom: 12
                            });
                        }
                    } else if (e.key === 'ArrowLeft') {
                        const current = $('.location-item.active').data('index') || 0;
                        const prev = (current - 1 + markers.length) % markers.length;
                        highlightPoint(prev);

                        // Zoom to show both current and previous point
                        if (prev > 0) {
                            const group = new L.featureGroup([markers[prev], markers[prev - 1]]);
                            map.fitBounds(group.getBounds(), {
                                padding: [50, 50],
                                maxZoom: 12
                            });
                        }
                    }
                });
            });
        });
    </script>

    <script src="{{ asset('assets/js/approval-form.js') }}"></script>
@endsection
