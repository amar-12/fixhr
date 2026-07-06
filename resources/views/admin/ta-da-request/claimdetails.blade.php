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
            /* Adjust map height */
            border-radius: 10px;
            /* Add rounded corners */
        }
    </style>
    <style>
        .outer-border {

            /* Customize the border color and width */
            border-radius: 8px;
            padding: 12px;
            /* Optional padding for better spacing */
        }

        .outer-border:nth-child(odd) {
            border: 2px solid #1f1ced;
            /* Light green for odd items */
        }

        .outer-border:nth-child(even) {
            border: 2px solid #34e0f0;
            /* Light red for even items */
        }

        body {
            background-color: #f7f8fa;
            font-family: 'Arial', sans-serif;
            font-size: 12px !important;
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
            /* background-color: #012169 !important; */
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
            /* Light green for odd items */
        }

        .info-container:nth-child(even) {
            background: linear-gradient(0.25turn, #e4f8e8, #a0e4dd);
            /* Light red for even items */
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            /* Align items to the start (top) */
            margin-bottom: 8px;
            /* Space between rows */
        }

        .info-label {
            flex-shrink: 0;
            /* Prevent the label from shrinking */
            width: 150px;
            /* Fixed width for labels */

            font-size: 17px;
            text-align: left;
        }

        .info-value {
            flex-grow: 1;
            /* Allow the value to grow */
            word-wrap: break-word;
            font-size: 16px;
            text-align: left;
            font-weight: bold;
        }

        /* Claim Css */
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
            /* Align with the icon */
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

        /* ami */
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

        /*  */

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

            /* The animation finishes at 50% */
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
            /* Adjust the width as needed */
        }
    </style>
    <style>
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

        /* Style for the path to make it more clickable */
        .leaflet-interactive {
            cursor: pointer;
            transition: stroke-width 0.2s;
        }

        .leaflet-interactive:hover {
            stroke-width: 6;
        }

        /* Location scroll container styles */
        .location-scroll-container {
            max-height: 350px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .location-scroll-container::-webkit-scrollbar {
            width: 8px;
        }

        .location-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .location-scroll-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .location-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .location-scroll-container .list-group-item {
            border-left: none;
            border-right: none;
            border-radius: 0;
            padding: 10px 15px;
        }

        .location-scroll-container .list-group-item:first-child {
            border-top: none;
        }

        .location-scroll-container .list-group-item:last-child {
            border-bottom: none;
        }
    </style>
@endsection
@section('content')
    @php
        $rawPlanData = $claimData->fh_tada_request_plan;
        $rawExpenseData = $rawPlanData->fh_tada_expenses;
        $rawTravelTypeLocal = $rawPlanData->fh_policy_tada_travel_type->fh_travel_type->m_id == 124;
        $rawTravelDetails = $rawPlanData->fh_tada_request_details;
        $rawTravelDetailSumAmt = $rawTravelTypeLocal
            ? $rawTravelDetails->sum('trd_net_amount')
            : App\Models\TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function ($query) {
                $query->where('pttv_claim_type_id', 155);
            })
                ->where('trd_trp_id', $claimData->fh_tada_request_plan->trp_id) // Assuming you are filtering based on the related detail's ID.
        ->sum('trd_net_amount');
$jsonData = $claimData->fh_claim_status->m_other;

// Decode the JSON to an associative array
$decodedData = json_decode($jsonData, true); // true for associative array

// Now access the color value
$color = $decodedData['color'];
$icon = $decodedData['web_icon'];
    @endphp
    <input type="hidden" id="ajaxCall" value="{{ url('/') }}">
    {{-- Breadcrumbs Start --}}
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/ta-da-request/claim">TA &amp; DA Requests</a></li>
                        <li class="active"><span><b>Claim Details</b></span></li>
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
                    <h5 class="modal-title" id="largemodalLabel">Travel Path</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Travel Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <span class="info-label">Travel ID:</span>
                                                <span
                                                    class="info-value">{{ $claimData->fh_tada_request_plan->trp_unique_id ?? 'N/A' }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Claim ID:</span>
                                                <span class="info-value">{{ $claimData->tc_unique_id ?? 'N/A' }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Travel Destination:</span>
                                                <span
                                                    class="info-value">{{ $claimData->fh_tada_request_plan->trp_destination ?? 'N/A' }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Travel Type:</span>
                                                <span
                                                    class="info-value">{{ $claimData->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <span class="info-label">Purpose:</span>
                                                <span
                                                    class="info-value">{{ $claimData->fh_tada_request_plan->fh_travel_purpose->tp_name ?? 'N/A' }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Applied Date:</span>
                                                <span
                                                    class="info-value">{{ isset($claimData->created_at) ? $claimData->created_at->format('d-m-Y') : 'N/A' }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Trip Name:</span>
                                                <span
                                                    class="info-value">{{ $claimData->fh_tada_request_plan->trp_name ?? 'N/A' }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Travel Start:</span>
                                                <span
                                                    class="info-value">{{ \Carbon\Carbon::parse($claimData->fh_tada_request_plan->trp_start_date . ' ' . $claimData->fh_tada_request_plan->trp_start_time)->format('d-M-Y h:i A') ?? 'N/A' }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Travel End:</span>
                                                <span
                                                    class="info-value">{{ \Carbon\Carbon::parse($claimData->fh_tada_request_plan->trp_end_date . ' ' . $claimData->fh_tada_request_plan->trp_end_time)->format('d-M-Y h:i A') ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Distance Display -->
                    <div class="text-center mb-3">
                        <h5>
                            Total Distance: <strong><span id="totalDistance">0 km</span></strong>
                        </h5>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div id="map" style="height: 400px;"></div> <!-- Map container -->
                        </div>
                        <div class="col-md-4">
                            <h5>Employee Travel History</h5>
                            <div class="location-scroll-container">
                                <ul class="list-group" id="locationList"></ul>
                            </div>
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
                                src="{{ isset($claimData->fh_employee) && !empty($claimData->fh_employee->emp_profile_photo)
                                    ? $claimData->fh_employee->emp_profile_photo
                                    : asset('assets/imgs/user.png') }}">

                            {{-- <img class="avatar avatar-xxl brround" alt="img"
                                src="{{ isset($claimData->fh_employee) ? asset('uploads/employee_profile/' . $claimData->fh_employee->emp_profile_photo) : '' }}"> --}}
                        </div>
                        <div class="pro-user mt-3">
                            <h5 class="pro-user-username text-dark mb-1 fs-16">
                                {{ $claimData->fh_employee->emp_full_name ?? 'N/A' }}</h5>
                            <h6 class="pro-user-desc text-muted fs-12">
                                {{ $claimData->fh_employee->fh_designation->dg_name ?? 'N/A' }}</h6>
                        </div>
                    </div>
                    <h5 class="mb-2 mt-4 font-weight-semibold">Basic Details</h5>
                    <div class="table-responsive">
                        <table class="table text-nowrap">
                            @php
                                $claimDetails = [
                                    'Emp Code' => $claimData->fh_employee->emp_code ?? 'N/A',
                                    'Email ID' => $claimData->fh_employee->emp_email ?? 'N/A',
                                    'Contact No' => $claimData->fh_employee->emp_phone ?? 'N/A',
                                    'Branch' => $claimData->fh_branch->br_name ?? 'N/A',
                                    // 'Department' => $claimData->fh_employee->fh_department->d_name ?? 'N/A',
                                ];
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
                                    <td class="py-1"> <span
                                            class="badge {{ $claimData->fh_employee->emp_status == 71 ? 'badge-success-light' : 'badge-warning-light' }}">{{ $claimData->fh_employee->emp_status == 71 ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- Claim Details Start --}}
            <div class="card outer-border position-relative example-4 ">
                <span id="claimStatusBadge" class="badge badge-claim position-absolute"
                    style="background-color: {{ $color }}">
                    <i id="claimStatusIcon" class="{{ $icon }}">&nbsp;</i>
                    <span id="claimStatusName">{{ $claimData->fh_claim_status->m_name ?? 'N/A' }}</span>
                </span>

                {{-- <span class="badge badge-claim position-absolute" style="background-color: {{$color}}"><i class="{{$icon}}">&nbsp;</i>{{ $claimData->fh_claim_status->m_name ?? 'N/A' }}</span> --}}
                <div class="card-header px-2">
                    <div class="card-title">
                        <h5 class="mb-2 mt-4 font-weight-semibold">Claim Details</h5>
                    </div>
                </div>
                <div class="card-body">
                    {{-- <h5 class="mb-2 mt-4 font-weight-semibold">Claim Details</h5> --}}
                    <div class="table-responsive">
                        <table class="table text-nowrap">
                            @php
                                $claimDetails = [
                                    'Travel ID' => $claimData->fh_tada_request_plan->trp_unique_id ?? 'N/A',
                                    'Claim ID' => $claimData->tc_unique_id ?? 'N/A',
                                    'Travel Destination' => $claimData->fh_tada_request_plan->trp_destination ?? 'N/A',
                                    'Travel Type' =>
                                        $claimData->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type
                                            ->m_name ?? 'N/A',
                                    'Purpose' => $claimData->fh_tada_request_plan->fh_travel_purpose->tp_name ?? 'N/A',
                                    'Applied Date' => isset($claimData->created_at)
                                        ? $claimData->created_at->format('d-m-Y')
                                        : 'N/A',
                                    'Trip Name' => $claimData->fh_tada_request_plan->trp_name ?? 'N/A',
                                    'Travel Start' =>
                                        \Carbon\Carbon::parse(
                                            $claimData->fh_tada_request_plan->trp_start_date .
                                                ' ' .
                                                $claimData->fh_tada_request_plan->trp_start_time,
                                        )->format('d-M-Y h:i A') ?? 'N/A',
                                    'Travel End' =>
                                        \Carbon\Carbon::parse(
                                            $claimData->fh_tada_request_plan->trp_end_date .
                                                ' ' .
                                                $claimData->fh_tada_request_plan->trp_end_time,
                                        )->format('d-M-Y h:i A') ?? 'N/A',
                                ];
                                $chunks = array_chunk($claimDetails, 1, true); // Split array into chunks of 3 items
                            @endphp
                            <tbody>
                                @foreach ($chunks as $chunk)
                                    <tr>
                                        @foreach ($chunk as $key => $value)
                                            <td class="py-1"> <span>{{ $key }}</span> </td>
                                            <td class="py-1">:</td>
                                            <td class="py-1 text-wrap"> <span><b>{{ $value }}</b></span> </td>
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
                            <li class="ms-auto p-2 font-weight-bold fs-18"> Claim Amount Requested:
                                ₹
                                <u>{{ $claimData->tc_claimed_amount . '/-' }}</u>
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
                                                <li><a href="#tabSelfTravelExp" data-bs-toggle="tab"><i
                                                            class="fa fa-cube"></i>&nbsp;Travel Expenses</a></li>
                                                @if (!$rawTravelTypeLocal)
                                                    <li><a href="#tabSelfLodgingExp" data-bs-toggle="tab"><i
                                                                class="fa fa-cogs"></i>&nbsp;Lodging Expenses</a></li>
                                                @endif
                                                <li><a href="#tabSelfMealExp" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;Meal Expenses</a></li>
                                                <li><a href="#tabSelfMiscellaneousExp" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;Miscellaneous Expenses</a></li>
                                                @if ($claimData->fh_deduction_log != null && count($claimData->fh_deduction_log) > 0)
                                                    <li><a href="#tabSelfDeductionExp" data-bs-toggle="tab"><i
                                                                class="fa fa-tasks"></i>&nbsp;Deduction</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                    @php

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
                                        $companyExpenseTypes = $claimData->fh_tada_request_plan->fh_tada_expenses
                                            ->where('te_paid_by', 'company')
                                            ->where('te_type_id', 158);
                                    @endphp
                                    <div class="panel-body tabs-menu-body">
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="tabSelfExpSummary">
                                                <!-- ROW -->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="card overflow-hidden">
                                                            <div class="card-body">
                                                                <div class="card-body ps-0 pe-0">
                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <span>Claim ID.</span><br>
                                                                            <strong> {{ $claimData->tc_unique_id }}
                                                                            </strong>
                                                                        </div>
                                                                        <div class="col-sm-6 text-end">
                                                                            <span>Claimed Date</span> <br>
                                                                            <strong>
                                                                                {{ Carbon::parse($claimData->created_at)->format('d-M-Y') }}</strong>
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                                <div class="table-responsive push">
                                                                    <table
                                                                        class="table table-bordered table-hover text-nowrap">
                                                                        <tr class=" ">
                                                                            <th class="text-center" style="width: 1%">
                                                                                S.No.</th>
                                                                            <th>Expenses</th>
                                                                            <th class="text" style="width: 1%">Amount
                                                                            </th>
                                                                            <th class="text" style="width: 1%">Deviation
                                                                            </th>
                                                                            <th class="text" style="width: 1%">Payable
                                                                                Amount</th>
                                                                            @if (($approvalData || $canApprove) && !$displayDeductionHandler)
                                                                                <th class="text" style="width: 1%">
                                                                                    Deduction</th>
                                                                                <!-- Added new column for Deduction -->
                                                                            @endif
                                                                        </tr>

                                                                        @foreach ($claimData->fh_tada_request_plan->fh_tada_expenses->where('te_paid_by', 'self')->groupBy('fh_expense_type.m_name') as $expenseType => $expenses)
                                                                            @php
                                                                                $exp_type_id = $expenses
                                                                                    ->pluck('te_type_id')
                                                                                    ->first();
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{ $loop->iteration }}</td>
                                                                                <td class="tdExpenseType">
                                                                                    {{ $expenseType }}</td>
                                                                                <td>₹
                                                                                    {{ round($expenses->sum('te_amount') + $expenses->sum('te_taxes')) }}
                                                                                </td>
                                                                                <td>₹
                                                                                    {{ round($expenses->sum('te_deviation')) }}
                                                                                </td>
                                                                                <td>₹
                                                                                    {{ round($expenses->sum('te_amount') + $expenses->sum('te_taxes')) - round($expenses->sum('te_deviation')) }}
                                                                                </td>
                                                                                @if (($approvalData || $canApprove) && !$displayDeductionHandler)
                                                                                    <td>
                                                                                        <!-- Added input field for deductions -->
                                                                                        <input type="number"
                                                                                            name="deduction[{{ $exp_type_id }}]"
                                                                                            id="{{ $exp_type_id . '_deduction' }}"
                                                                                            data-payableAmount="{{ round($expenses->sum('te_amount') + $expenses->sum('te_taxes')) - round($expenses->sum('te_deviation')) }}"
                                                                                            class="form-control deduction-input"
                                                                                            step="0.01" min="0"
                                                                                            placeholder="₹ 0">
                                                                                    </td>
                                                                                @endif
                                                                            </tr>
                                                                        @endforeach

                                                                        @if ($rawPlanData->trp_is_details_added)
                                                                            <tr>
                                                                                <td colspan="4"
                                                                                    class="font-weight-semibold text-end">
                                                                                    TA</td>
                                                                                <td>₹ {{ round($rawTravelDetailSumAmt) }}
                                                                                </td>
                                                                                <td><!-- Optional deduction input for TA -->
                                                                                </td>
                                                                            </tr>
                                                                        @endif

                                                                        <tr>
                                                                            <td colspan="4"
                                                                                class="font-weight-semibold text-end">DA
                                                                            </td>
                                                                            <td>₹ {{ $claimData->tc_da_amount ?? 0 }}</td>
                                                                            <td><!-- Optional deduction input for DA -->
                                                                            </td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td colspan="4"
                                                                                class="font-weight-semibold text-end">
                                                                                Subtotal<span> Total Expense Amount (Inc.
                                                                                    Taxes), Travel Allowance, and DA) after
                                                                                    Deduction of Deviation.</td>
                                                                            <td>₹ {{ $claimData->tc_amount }}</span></td>
                                                                            <td></td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td colspan="4"
                                                                                class="font-weight-semibold text-end">
                                                                                Advance</td>
                                                                            <td>₹
                                                                                {{ $claimData->fh_tada_request_plan->trp_advance_allowance ?? 0 }}
                                                                            </td>
                                                                            <td></td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td colspan="4"
                                                                                class="font-weight-semibold text-end">
                                                                                Deduction</td>
                                                                            <td>₹
                                                                                {{ $claimData->tc_deduction_amount ?? 0 }}
                                                                            </td>
                                                                            <td></td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td colspan="4"
                                                                                class="font-weight-bold text-uppercase text-end h4 mb-0">
                                                                                Net Payable Amount</td>
                                                                            <td class="font-weight-bold h4 mb-0">₹
                                                                                {{ $claimData->tc_amount - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0)) }}
                                                                            </td>
                                                                            <td></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- END ROW -->
                                                @if ($actionRoute !== 'claim-request.request.show' && $actionRoute !== 'claim-request-is-paid.request.show')
                                                    @if ($approvalData && !$displayDeductionHandler)
                                                        <div class="card-header">
                                                            <h3 class="card-title">Approval Or Reject</h3>
                                                        </div>
                                                        <div class="card-body">

                                                            {{-- @if (!$claimData->tc_deduction_amount) --}}
                                                            {{-- <div class="row">
                                                                <div
                                                                    class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                                    <button
                                                                        class="btn btn-outline-primary mx-3 addDeductionBtn {{ $claimData->tc_deduction_amount ? 'd-none' : '' }}">Add
                                                                        Deduction</button>
                                                                </div>
                                                            </div> --}}
                                                            {{-- @endif --}}

                                                            <form id="approvalForm">

                                                                <div class="form-group">

                                                                    <div id="deductionAmountDiv"></div>

                                                                    <div class="row">
                                                                        <div class="col-md-12 col-lg-2">
                                                                            <label
                                                                                class="form-label mb-0 mt-2">Message</label>
                                                                        </div>
                                                                        <div class="col-md-12 col-lg-12">
                                                                            <textarea rows="2" name="message" class="form-control" maxlength="255" id="actionMessage">Approved</textarea>
                                                                        </div>
                                                                    </div>

                                                                    <div class="card-footer mt-3">
                                                                        <div class="row">
                                                                            <div
                                                                                class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                                                <a href="javascript:void(0);"
                                                                                    data-approval_status="{{ $approvalData->fh_approver_status->m_id }}"
                                                                                    data-approval_type="0"
                                                                                    data-approval_action_type="{{ $approvalData->pa_type }}"
                                                                                    data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                                                    data-tc_id="{{ md5($claimData->tc_id) }}"
                                                                                    data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                                                    data-is_last_approval="{{ $approvalData->pa_last }}"
                                                                                    data-emp_d_id="{{ optional($claimData->fh_employee)->emp_d_id }}"
                                                                                    class="btn btn-danger actionBtn mx-3">Reject</a>

                                                                                <a href="javascript:void(0);"
                                                                                    data-approval_status="{{ $approvalData->fh_approver_status->m_id }}"
                                                                                    data-approval_type="1"
                                                                                    data-approval_action_type="{{ $approvalData->pa_type }}"
                                                                                    data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                                                    data-tc_id="{{ md5($claimData->tc_id) }}"
                                                                                    data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                                                    data-emp_d_id="{{ optional($claimData->fh_employee)->emp_d_id }}"
                                                                                    data-is_last_approval="{{ $approvalData->pa_last }}"class="btn btn-success actionBtn">{{ $approvalData->fh_approver_status->m_name }}</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    @elseif ($canApprove && !$displayDeductionHandler)
                                                        <x-approval-form :moduleName="$claimData?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn"
                                                            :primaryId="$claimData->tc_id" :moduleId="$claimData->tc_module_id"
                                                            actionUrl="{{ route('approve.claim') }}" />
                                                    @endif
                                                @endif

                                                <div class="col-xl-12 col-md-12 col-lg-6">
                                                    <ul id="claimLogList" class="timeline ">
                                                        @foreach ($combinedLog->sortBy('updated_at') as $item2)
                                                            @php

                                                                $className = get_class($item2);
                                                                if ($className == 'App\Models\ApprovalLog') {
                                                                    $jsonData2 = $item2->fh_status->m_other;
                                                                    $item2DecodedData = json_decode($jsonData2, true); // true for associative array
                                                                    $item2Color = $item2DecodedData['color'];
                                                                    $item2Icon = $item2DecodedData['web_icon'];
                                                                    $item2StatusName = $item2->fh_status->m_name;
                                                                    $remark = $item2->log_description;

                                                                    $emp_name = $item2->fh_employee->emp_full_name;
                                                                    $emp_code = $item2->fh_employee->emp_code;
                                                                    $emp_designation =
                                                                        $item2->fh_employee->fh_designation->dg_name;
                                                                } else {
                                                                    $item2StatusName =
                                                                        $item2->dlog_requester_action == 1
                                                                            ? 'Accepted'
                                                                            : 'Declined';
                                                                    $item2Color =
                                                                        $item2->dlog_requester_action == 1
                                                                            ? '#4CAF50'
                                                                            : '#F44336';
                                                                    $item2Icon =
                                                                        $item2->dlog_requester_action == 1
                                                                            ? 'fa fa-check'
                                                                            : 'fa fa-times';
                                                                    $remark = $item2->dlog_remarks;

                                                                    $emp_name = $claimData->fh_employee->emp_full_name;
                                                                    $emp_code = $claimData->fh_employee->emp_code;
                                                                    $emp_designation =
                                                                        $claimData->fh_employee->fh_designation
                                                                            ->dg_name;
                                                                }
                                                            @endphp
                                                            <li
                                                                class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }}">
                                                                <a href="javascript:void(0);"
                                                                    class="font-weight-semibold fs-15 mb-2 ms-3">
                                                                    <span class="badge "
                                                                        style="background-color:{{ $item2Color }}">
                                                                        <i
                                                                            class="{{ $item2Icon }}">&nbsp;</i>{{ $item2StatusName }}
                                                                    </span></a>
                                                                <a href="javascript:void(0);"
                                                                    class="text-muted float-end fs-12">On
                                                                    {{ \Carbon\Carbon::parse($item2->created_at)->format('l') }}</a><br>
                                                                <span class="text-muted float-end ms-3 fs-14"> <i
                                                                        class="fa fa-calendar"></i>
                                                                    {{ \Carbon\Carbon::parse($item2->created_at)->format('d-M-Y') }}
                                                                    <i class=" ms-3 fa fa-clock-o"></i>
                                                                    {{ \Carbon\Carbon::parse($item2->created_at)->format('h:i A') }}</span>
                                                                <p class="mb-0 pb-0 text-muted fs-18 pt-1 ms-3">
                                                                    {{ $emp_name }} &nbsp; <span class="fs-14">
                                                                        {{ '(' . $emp_code . ')' }}</span>
                                                                </p>
                                                                <span
                                                                    class="mb-0 pb-0 text-muted fs-14 ms-3">{{ $emp_designation }}</span><br>
                                                                <span class="text-muted ms-3 fs-14">Remark :
                                                                    {{ $remark }}</span>
                                                                <div>
                                                                    @if (isset($item2->fh_deductionLog->dlog_additional_info) && $item2->fh_deductionLog->dlog_additional_info)
                                                                        <span class="text-muted ms-3 fs-14">Deduction :
                                                                        </span>
                                                                        @foreach (json_decode($item2->fh_deductionLog->dlog_additional_info) as $key => $keyItem)
                                                                            <span
                                                                                class="text-muted ms-3 fs-14">{{ \App\Models\MasterTable::find($key)->m_name }}
                                                                                : {{ $keyItem }}</span>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                @if ($actionRoute == 'claim-request.request.show' && $displayDeductionHandler)
                                                    <div class="card-header">
                                                        <h3 class="card-title">Accept Or Decline</h3>
                                                    </div>
                                                    <div class="card-body">

                                                        <form id="deductionForm">
                                                            <div class="form-group">
                                                                <div class="row">
                                                                    <div class="col-md-12 col-lg-2">
                                                                        <label class="form-label mb-0 mt-2">Message</label>
                                                                    </div>
                                                                    <div class="col-md-12 col-lg-12">
                                                                        <textarea rows="2" name="empAcceptanceMsg" class="form-control" id="empAcceptanceMsg"></textarea>
                                                                    </div>
                                                                </div>

                                                                <div class="card-footer mt-3">
                                                                    <div class="row">
                                                                        <div
                                                                            class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                                            <a href="javascript:void(0);"
                                                                                class="btn btn-danger  mx-3 handleDeductionBtn"
                                                                                data-tc_id="{{ md5($claimData->tc_id) }}"
                                                                                data-dlog_id="{{ md5($displayDeductionHandler->dlog_id) }}"
                                                                                data-action="0">Decline</a>

                                                                            <a href="javascript:void(0);"
                                                                                class="btn btn-success handleDeductionBtn"
                                                                                data-tc_id="{{ md5($claimData->tc_id) }}"
                                                                                data-dlog_id="{{ md5($displayDeductionHandler->dlog_id) }}"
                                                                                data-action="1">Accept</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="tab-pane" id="tabSelfDailyAllowanceDetails">
                                                <!-- Lodging Expenses -->
                                                @if (json_decode($claimData->tc_da_calculation_message, true))
                                                    @foreach (json_decode($claimData->tc_da_calculation_message, true) as $info)
                                                        <div class="card-body  outer-border position-relative pb-0 my-5">
                                                            <span class="badge badge-success position-absolute">₹
                                                                {{ $info['amount'] }}/-
                                                            </span>
                                                            <div class="row mt-2">
                                                                <div class="col-md-6">
                                                                    <ul class="">
                                                                        @if ($info['hours'])
                                                                            <li class="mt-1">
                                                                                <span
                                                                                    class="font-weight-semibold fs-18 ms-3">
                                                                                    <i class="fa fa-clock-o"
                                                                                        aria-hidden="true"></i>
                                                                                    <span
                                                                                        class="badge badge badge-info-light">Total
                                                                                        Hours:
                                                                                        {{ number_format($info['hours'], 2) }}</span>
                                                                                </span>
                                                                            </li>
                                                                        @endif
                                                                        @if ($info['stay'])
                                                                            <li class="mt-1">
                                                                                <span
                                                                                    class="font-weight-semibold fs-18 ms-3">
                                                                                    <i class="fa fa-bed"
                                                                                        aria-hidden="true"></i>
                                                                                    <span
                                                                                        class="badge badge badge-info-light">Stay:
                                                                                        {{ $info['stay'] ? 'Yes' : 'No' }}</span>
                                                                                    @if ($info['stay'])
                                                                                        <span
                                                                                            class="badge badge badge-info-light">Arranged
                                                                                            By:
                                                                                            {{ $info['stay_arranged_by'] }}</span>
                                                                                    @endif
                                                                                </span>
                                                                            </li>
                                                                        @endif

                                                                        @if ($info['day_start'] || $info['day_end'])
                                                                            <li class="mt-1">
                                                                                <span
                                                                                    class="font-weight-semibold fs-16 ms-3">
                                                                                    <small class="text-muted">
                                                                                        @if ($info['day_start'])
                                                                                            Day Start:
                                                                                            &nbsp;{{ Carbon::parse($info['day_start'])->format('d-M-Y g:i A') }}
                                                                                        @endif ||
                                                                                        @if ($info['day_end'])
                                                                                            Day End:
                                                                                            &nbsp;{{ Carbon::parse($info['day_end'])->format('d-M-Y g:i A') }}
                                                                                        @endif
                                                                                    </small>
                                                                                </span>
                                                                            </li>
                                                                        @endif

                                                                        @if ($info['start_date'] || $info['end_date'])
                                                                            <li class="mt-1">
                                                                                <span
                                                                                    class="font-weight-semibold fs-16 ms-3">
                                                                                    <small class="text-muted">
                                                                                        @if ($info['start_date'])
                                                                                            Start Date:
                                                                                            &nbsp;{{ Carbon::parse($info['start_date'])->format('d-M-Y') }}
                                                                                        @endif ||
                                                                                        @if ($info['end_date'])
                                                                                            End Date:
                                                                                            &nbsp;{{ Carbon::parse($info['end_date'])->format('d-M-Y') }}
                                                                                        @endif
                                                                                    </small>
                                                                                </span>
                                                                            </li>
                                                                        @endif

                                                                        @if ($info['total_distance'])
                                                                            <li class="mt-1">
                                                                                <span
                                                                                    class="font-weight-semibold fs-16 ms-3">
                                                                                    <small class="text-muted">Total
                                                                                        Distance:
                                                                                        &nbsp;{{ $info['total_distance'] }}</small>
                                                                                </span>
                                                                            </li>
                                                                        @endif
                                                                        @if ($info['days'])
                                                                            <li class="mt-1">
                                                                                <span
                                                                                    class="font-weight-semibold fs-16 ms-3">
                                                                                    <small class="text-muted">Days:
                                                                                        &nbsp;{{ $info['days'] }}</small>
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
                                            <div class="tab-pane" id="tabSelfTravelExp">
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
                                                                                <li class="py-0 my-0"> <span
                                                                                        class="font-weight-semibold fs-16 ms-3">
                                                                                        <small class="text-muted"><i
                                                                                                class="fa fa-calendar"></i>&nbsp;{{ $expense->te_from_date ? Carbon::parse($expense->te_from_date)->format('d-M-Y') : '' }}
                                                                                            <br> <i
                                                                                                class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_from_time ? Carbon::parse($expense->te_from_time)->format('h:i A') : '' }}</small></span>
                                                                                </li>
                                                                                <li class="primary mt-6 py-0 my-0"> <span
                                                                                        class="font-weight-semibold fs-16 ms-3">
                                                                                        <small class="text-muted"><i
                                                                                                class="fa fa-calendar"></i>&nbsp;{{ $expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d-M-Y') : '' }}
                                                                                            <br> <i
                                                                                                class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_to_time ? Carbon::parse($expense->te_to_time)->format('h:i A') : '' }}</small></span>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        @if ($expense->te_round_trip == 1)
                                                                            <div class="col-md-1"
                                                                                style="margin-top:2.1rem; padding-left: 2.6rem">
                                                                                <i class="fa fa-arrow-up"
                                                                                    style="font-size: 20px; color: #007bff;"></i><br>
                                                                                <i class="fa fa-arrow-down"
                                                                                    style="font-size: 20px; color: #007bff; margin-top: 5px;"></i>
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
                                                                        <label class="form-label">Travel
                                                                            Mode:&nbsp;</label>
                                                                        <span class="font-weight-bold">
                                                                            {{ $expense->fh_policy_tada_travel_mode->fh_travel_mode->m_name ?? '' }}
                                                                            @php
                                                                                $class =
                                                                                    $expense
                                                                                        ->fh_policy_tada_travel_vehicle
                                                                                        ->fh_travel_class->m_name ?? '';
                                                                                $vehicle =
                                                                                    $expense
                                                                                        ->fh_policy_tada_travel_vehicle
                                                                                        ->fh_vehicle->m_name ?? '';
                                                                            @endphp

                                                                            @if ($class || $vehicle)
                                                                                ({{ $class }} {{ $vehicle }})
                                                                            @endif

                                                                            @if (!empty($expense->trd_name))
                                                                                ({{ $expense->trd_name }} )
                                                                            @endif
                                                                        </span>
                                                                    </div>
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
                                                                        <!--<div class="d-flex justify-content-end">
                                                                                <label
                                                                                    class="form-label mb-0">Tax:&nbsp;</label>
                                                                                <span class="font-weight-bold">₹
                                                                                    {{ $expense->te_taxes }}/-</span>
                                                                            </div>-->
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
                                                                                    data-bs-target="#documentsModal{{ $loop->index . 'tabSelfTravelExp' }}"
                                                                                    class="text-primary">
                                                                                    <u>View Documents</u>
                                                                                </a>
                                                                                <!-- Modal -->
                                                                                @component('admin.components.document-modal', [
                                                                                    'id' => $loop->index . 'tabSelfTravelExp',
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
                                                                            $subExpense = $expense->fh_sub_expense;
                                                                        @endphp
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Expense Name
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span
                                                                                class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{ $subExpense->tes_head }}</span>
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
                                                                                $displayName = substr(
                                                                                    $document,
                                                                                    $position,
                                                                                );
                                                                            } else {
                                                                                // If "PlanDetail_" is not found, use the full file name
                                                                                $displayName = basename($document);
                                                                            }
                                                                        @endphp
                                                                        <li class="col-4"> <a
                                                                                href="{{ asset($document) }}"
                                                                                target="_blank" class="text-primary"> *
                                                                                {{ $displayName }} - View file
                                                                                {{ $dkey + 1 }},
                                                                            </a> </li>
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
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
                                                                                <small class="text-muted"><i
                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d-M-Y') : '' }}<i
                                                                                        class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_to_time ? Carbon::parse($expense->te_to_time)->format('h:i A') : '' }}
                                                                                    @if ($expense->te_additional_info && json_decode($expense->te_additional_info, true))
                                                                                        <span
                                                                                            class="badge badge-md badge-primary-light ms-1">{{ json_decode($expense->te_additional_info, true)['lodging']['totalDays'] }}
                                                                                            days
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
                                                                        <!-- <div class="d-flex justify-content-end">
                                                                                <label
                                                                                    class="form-label mb-0">Tax:&nbsp;</label>
                                                                                <span class="font-weight-bold">₹
                                                                                    {{ $expense->te_taxes }}/-</span>
                                                                            </div>-->
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
                                                                                data-bs-target="#documentsModal{{ $loop->index . 'tabSelfLodgingExp' }}"
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
                                                                            $subExpense = $expense->fh_sub_expense;
                                                                        @endphp
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Expense Name
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span
                                                                                class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{ $subExpense->tes_head }}</span>
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
                                                                    <li class="col-4"> <a href="{{ asset($document) }}"
                                                                            target="_blank" class="text-primary"> *
                                                                            {{ $displayName }} - View file
                                                                            {{ $dkey + 1 }},
                                                                        </a> </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>
                                            <div class="tab-pane" id="tabSelfMealExp">
                                                @if (count($selfMealExpenseType) > 0)
                                                    @foreach ($selfMealExpenseType as $expense)
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
                                                                                    data-bs-target="#documentsModal{{ $loop->index . 'tabSelfMealExp' }}"
                                                                                    class="text-primary">
                                                                                    <u>View Documents</u>
                                                                                </a>
                                                                                <!-- Modal -->
                                                                                @component('admin.components.document-modal', [
                                                                                    'id' => $loop->index . 'tabSelfMealExp',
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
                                                                <!-- <div class="col-md-3">
                                                                        <div class="d-flex ">
                                                                            <label class="form-label mb-0">Tax:&nbsp;</label>
                                                                            <span class="font-weight-bold">₹
                                                                                {{ $expense->te_taxes }}/-</span>
                                                                        </div>
                                                                    </div>-->
                                                                @if (isset($expense->fh_sub_expense))
                                                                    @php
                                                                        $subExpense = $expense->fh_sub_expense;
                                                                    @endphp
                                                                    <div class="col-md-3">
                                                                        <div class="d-flex ">
                                                                            <label class="form-label">Expense Name :&nbsp;
                                                                            </label>
                                                                            <span
                                                                                class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{ $subExpense->tes_head }}</span>
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
                                                                @if (is_array($documents) && count($documents) > 0)
                                                                    @foreach ($documents as $dkey => $document)
                                                                        @php
                                                                            $position = strpos($document, 'Expense_');
                                                                            if ($position !== false) {
                                                                                $displayName = substr(
                                                                                    $document,
                                                                                    $position,
                                                                                );
                                                                            } else {
                                                                                // If "PlanDetail_" is not found, use the full file name
                                                                                $displayName = basename($document);
                                                                            }
                                                                        @endphp
                                                                        <li class="col-4"> <a
                                                                                href="{{ asset($document) }}"
                                                                                target="_blank" class="text-primary"> *
                                                                                {{ $displayName }} - View file
                                                                                {{ $dkey + 1 }},
                                                                            </a> </li>
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>
                                            <div class="tab-pane " id="tabSelfMiscellaneousExp">
                                                @if (count($selfOtherExpenseType) > 0)
                                                    @foreach ($selfOtherExpenseType as $expense)
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
                                                                        @if ($expense->te_from_date)
                                                                            <li class="mt-1">
                                                                                <span
                                                                                    class="font-weight-semibold fs-16 ms-3"><small
                                                                                        class="text-muted"><i
                                                                                            class="fa fa-calendar"></i>&nbsp;{{ $expense->te_from_date ? Carbon::parse($expense->te_from_date)->format('d-M-Y') : '' }}<i
                                                                                            class=" ms-3 fa fa-clock-o"></i>&nbsp;
                                                                                        {{ $expense->te_from_time ? Carbon::parse($expense->te_from_time)->format('h:i A') : '' }}M&nbsp;To</small></span>
                                                                            </li>
                                                                        @endif
                                                                        @if ($expense->te_to_date)
                                                                            <li class="mt-1">
                                                                                <span
                                                                                    class="font-weight-semibold fs-16 ms-3"><small
                                                                                        class="text-muted"><i
                                                                                            class="fa fa-calendar"></i>&nbsp;{{ $expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d-M-Y') : '' }}&nbsp;<i
                                                                                            class=" ms-3 fa fa-clock-o"></i>&nbsp;
                                                                                        {{ $expense->te_to_time ? Carbon::parse($expense->te_to_time)->format('h:i A') : '' }}<span
                                                                                            class="badge badge-md badge-primary-light ms-1"
                                                                                            hidden>135
                                                                                            days</span></small></span>
                                                                            </li>
                                                                        @endif
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
                                                                        <!--<div class="d-flex justify-content-end">
                                                                                <label
                                                                                    class="form-label mb-0">Tax:&nbsp;</label>
                                                                                <span class="font-weight-bold">₹
                                                                                    {{ $expense->te_taxes }}/-</span>
                                                                            </div>-->
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
                                                                                    data-bs-target="#documentsModal{{ $loop->index . 'tabSelfMiscellaneousExp' }}"
                                                                                    class="text-primary">
                                                                                    <u>View Documents</u>
                                                                                </a>
                                                                                <!-- Modal -->
                                                                                @component('admin.components.document-modal', [
                                                                                    'id' => $loop->index . 'tabSelfMiscellaneousExp',
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
                                                                            $subExpense = $expense->fh_sub_expense;
                                                                        @endphp
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Expense Name
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span
                                                                                class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{ $subExpense->tes_head }}</span>
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
                                                                    <li class="col-4"> <a href="{{ asset($document) }}"
                                                                            target="_blank" class="text-primary"> *
                                                                            {{ $displayName }} - View file
                                                                            {{ $dkey + 1 }},
                                                                        </a> </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>
                                            @if ($claimData->fh_deduction_log != null && count($claimData->fh_deduction_log) > 0)
                                                <div class="tab-pane" id="tabSelfDeductionExp">
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table
                                                                class="table table-striped card-table table-vcenter text-nowrap mb-0">
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
                                                                    @foreach ($claimData->fh_deduction_log as $index => $deductionItem)
                                                                        <tr>
                                                                            <td>{{ $index + 1 }}</td>
                                                                            <td>{{ $deductionItem->fh_employee->emp_full_name }}
                                                                            </td>
                                                                            <td>{{ $deductionItem->dlog_remarks }}</td>
                                                                            <td>
                                                                                @php
                                                                                    $dlog_id =
                                                                                        $deductionItem->dlog_id ?? null;
                                                                                    $deduction = \App\Models\DeductionLog::where(
                                                                                        'dlog_id',
                                                                                        $dlog_id,
                                                                                    )->first();
                                                                                @endphp
                                                                                @php
                                                                                    $filteredInfo = array_filter(
                                                                                        json_decode(
                                                                                            $deduction->dlog_additional_info,
                                                                                            true,
                                                                                        ),
                                                                                        fn($v) => $v != 0,
                                                                                    );
                                                                                @endphp

                                                                                @foreach ($filteredInfo as $key => $keyItem)
                                                                                    {{ \App\Models\MasterTable::find($key)->m_name ?? 'Unknown' }}
                                                                                    : ₹{{ $keyItem }}@if (!$loop->last)
                                                                                        ,
                                                                                    @endif
                                                                                @endforeach
                                                                            </td>
                                                                            <td>{{ $deductionItem->dlog_deduction_amount ?? 0 }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div><!-- bd -->
                                                    </div><!-- bd -->
                                                </div>
                                            @endif

                                            <div class="tab-pane" id="tabSelfTravelDetails">
                                                @if (count($rawTravelDetails) > 0)
                                                    @foreach ($rawTravelDetails as $travelDetailsItem)
                                                        @if ($travelDetailsItem->trd_segments != null)
                                                            <div
                                                                class="card-body my-5 outer-border position-relative pb-0">
                                                                <span class="badge badge-success position-absolute">₹
                                                                    {{ $travelDetailsItem->trd_net_amount }}/ Rs.-</span>
                                                                <div class="row mt-2">
                                                                    <div class="col-md-7">
                                                                        <div class="row">
                                                                            {{-- <div class="col-md-5">
                                                                                    <ul class="">
                                                                                        <li class="py-0 my-0"> <span
                                                                                                class="font-weight-semibold fs-16 ms-3">
                                                                                                <small class="text-muted"><i
                                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $rawPlanData->trp_start_date ? Carbon::parse($rawPlanData->trp_start_date)->format('d-M-Y') : '' }}
                                                                                                    <br> <i
                                                                                                        class="ms-3 fa fa-clock-o"></i>&nbsp;{{ $rawPlanData->trp_start_time ? Carbon::parse($rawPlanData->trp_start_time)->format('h:i A') : '' }}</small></span>
                                                                                        </li>
                                                                                        <li class="primary mt-6 py-0 my-0">
                                                                                            <span
                                                                                                class="font-weight-semibold fs-16 ms-3"><small
                                                                                                    class="text-muted"><i
                                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $rawPlanData->trp_end_date ? Carbon::parse($rawPlanData->trp_end_date)->format('d-M-Y') : '' }}
                                                                                                    <br> <i
                                                                                                        class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $rawPlanData->trp_end_time ? Carbon::parse($rawPlanData->trp_end_time)->format('h:i A') : '' }}</small></span>
                                                                                        </li>
                                                                                    </ul>
                                                                                </div> --}}
                                                                            <div class="col-md-12">
                                                                                <p><b>Tap Location Longitute Latituted</b>
                                                                                </p>
                                                                                <ul class="timeline"
                                                                                    style="max-height: 200px; overflow-y: auto; ">
                                                                                    @foreach (json_decode($travelDetailsItem->trd_segments) as $location)
                                                                                        <ul class="timeline ">
                                                                                            <li
                                                                                                class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }} my-2 py-0">
                                                                                                <span
                                                                                                    class="font-weight-semibold fs-16 ms-3">{{ $location->time ?? 'N/A' }}</span>
                                                                                                <span
                                                                                                    class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                                    <span
                                                                                                        class="mb-0 pb-0 text-muted fs-12">
                                                                                                        {{ $location->latitude ?? 'N/A' }}
                                                                                                        &nbsp;{{ $location->longitude ?? 'N/A' }}
                                                                                                        &nbsp;
                                                                                                        ({{ $location->location ?? 'N/A' }})
                                                                                                    </span></span>
                                                                                            </li>
                                                                                        </ul>
                                                                                    @endforeach
                                                                                </ul>
                                                                                <button
                                                                                    class="btn btn-info mt-3 travel-path-btn"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#largemodal"
                                                                                    data-total-distance="{{ $travelDetailsItem->trd_total_distance }}"
                                                                                    data-locations='@json($travelDetailsItem->trd_segments)'>
                                                                                    View Travel Path
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-1 pb-5">
                                                                        <span
                                                                            class="border-start border-info h-100 d-inline-block"></span>
                                                                    </div>
                                                                    <div class="col-md-4  text-end">
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Mode: &nbsp;
                                                                            </label>
                                                                            <span class="font-weight-bold">
                                                                                {{ isset($travelDetailsItem->fh_policy_tada_travel_mode->fh_travel_mode->m_name) ? $travelDetailsItem->fh_policy_tada_travel_mode->fh_travel_mode->m_name : 'N/A' }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Vehicle: &nbsp;
                                                                            </label>
                                                                            <span class="font-weight-bold">
                                                                                {{ isset($travelDetailsItem->fh_policy_tada_travel_vehicle->fh_vehicle->m_name) ? $travelDetailsItem->fh_policy_tada_travel_vehicle->fh_vehicle->m_name : 'N/A' }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Distance: &nbsp;
                                                                            </label>
                                                                            <span class="font-weight-bold">
                                                                                {{ isset($travelDetailsItem->trd_total_distance) ? number_format($travelDetailsItem->trd_total_distance, 3) . ' KM' : 'N/A' }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Claim Type: &nbsp;
                                                                            </label>
                                                                            <span class="font-weight-bold">
                                                                                {{ isset($travelDetailsItem->fh_policy_tada_travel_vehicle->fh_claim_type->m_name) ? $travelDetailsItem->fh_policy_tada_travel_vehicle->fh_claim_type->m_name : 'N/A' }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Applied On: &nbsp;
                                                                            </label>
                                                                            <span class="font-weight-bold">
                                                                                {{ $rawPlanData->created_at ? Carbon::parse($rawPlanData->created_at)->format('d-M-Y') : '' }}</span>
                                                                        </div>
                                                                        <div class="offset-md-5 bg-gray-100 border "
                                                                            style=" box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                                                            <div class="d-flex justify-content-end">
                                                                                <label
                                                                                    class="form-label mb-0">Amount:&nbsp;</label>
                                                                                <span class="font-weight-bold">₹
                                                                                    {{ $travelDetailsItem->trd_net_amount }}/-</span>
                                                                            </div>
                                                                            {{-- ******** for international trip ******* --}}
                                                                            @if ($claimData->fh_tada_request_plan->fh_policy_tada_travel_type->pttt_type_id == 126)
                                                                                <div class="d-flex justify-content-end">
                                                                                    <label class="form-label mb-0">Country
                                                                                        Code:&nbsp;</label>
                                                                                    <span class="font-weight-bold">
                                                                                        {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_country_code }}/-</span>
                                                                                </div>
                                                                                <div class="d-flex justify-content-end">
                                                                                    <label
                                                                                        class="form-label mb-0">Conversion
                                                                                        Rate To INR:&nbsp;</label>
                                                                                    <span class="font-weight-bold">
                                                                                        {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_conversion_rate }}/-</span>
                                                                                </div>
                                                                                <div class="d-flex justify-content-end">
                                                                                    <label class="form-label mb-0">Amount
                                                                                        In INR:&nbsp;</label>
                                                                                    <span class="font-weight-bold">₹
                                                                                        {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_foreign_amount }}/-</span>
                                                                                </div>
                                                                            @endif
                                                                            {{-- <div class="d-flex justify-content-end">
                                                                                <label
                                                                                    class="form-label mb-0">Tax:&nbsp;</label>
                                                                                <span class="font-weight-bold">₹
                                                                                    {{ $travelDetailsItem->te_taxes }}/-</span>
                                                                            </div> --}}
                                                                        </div>
                                                                        <div class="d-flex justify-content-end mt-2">
                                                                            <label class="form-label">Documents
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span> @php
                                                                                $documents = json_decode(
                                                                                    $travelDetailsItem->trd_documents,
                                                                                    true,
                                                                                ); // Decode JSON into an array
                                                                            @endphp

                                                                                @if (is_array($documents) && count($documents) > 0)
                                                                                    <a href="#" type="button"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#documentsModal{{ $loop->index . 'tabSelfTravelDetails' }}"
                                                                                        class="text-primary">
                                                                                        <u>View Documents</u>
                                                                                    </a>
                                                                                    <!-- Modal -->
                                                                                    @component('admin.components.document-modal', [
                                                                                        'id' => $loop->index . 'tabSelfTravelDetails',
                                                                                        'documents' => $documents, // Pass the decoded documents array
                                                                                        'componentString' => 'travelDetailsItem_',
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
                                                                                class="text-muted">{{ $travelDetailsItem->trd_remarks }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <span class="form-label">Documents: </span>
                                                                <ul class="text-start row">
                                                                    @foreach ($documents as $dkey => $document)
                                                                        @php
                                                                            $position = strpos(
                                                                                $document,
                                                                                'travelDetailsItem_',
                                                                            );
                                                                            if ($position !== false) {
                                                                                $displayName = substr(
                                                                                    $document,
                                                                                    $position,
                                                                                );
                                                                            } else {
                                                                                // If "PlanDetail_" is not found, use the full file name
                                                                                $displayName = basename($document);
                                                                            }
                                                                        @endphp
                                                                        <li class="col-4"> <a
                                                                                href="{{ asset($document) }}"
                                                                                target="_blank" class="text-primary"> *
                                                                                {{ $displayName }} - View file
                                                                                {{ $dkey + 1 }},
                                                                            </a> </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <div
                                                                class="card-body my-5 outer-border position-relative pb-0">
                                                                <span class="badge badge-success position-absolute">₹
                                                                    {{ $travelDetailsItem->trd_net_amount }}/-</span>
                                                                <div class="row mt-2">
                                                                    <div class="col-md-7">
                                                                        <div class="row">
                                                                            {{-- <div class="col-md-5">
                                                                                    <ul class="">
                                                                                        <li class="py-0 my-0"> <span
                                                                                                class="font-weight-semibold fs-16 ms-3">
                                                                                                <small class="text-muted"><i
                                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $rawPlanData->trp_start_date ? Carbon::parse($rawPlanData->trp_start_date)->format('d-M-Y') : '' }}
                                                                                                    <br> <i
                                                                                                        class="ms-3 fa fa-clock-o"></i>&nbsp;{{ $rawPlanData->trp_start_time ? Carbon::parse($rawPlanData->trp_start_time)->format('h:i A') : '' }}</small></span>
                                                                                        </li>
                                                                                        <li class="primary mt-6 py-0 my-0">
                                                                                            <span
                                                                                                class="font-weight-semibold fs-16 ms-3"><small
                                                                                                    class="text-muted"><i
                                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $rawPlanData->trp_end_date ? Carbon::parse($rawPlanData->trp_end_date)->format('d-M-Y') : '' }}
                                                                                                    <br> <i
                                                                                                        class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $rawPlanData->trp_end_time ? Carbon::parse($rawPlanData->trp_end_time)->format('h:i A') : '' }}</small></span>
                                                                                        </li>
                                                                                    </ul>
                                                                                </div> --}}
                                                                            <div class="col-md-12">
                                                                                <p><b>Manual Detail</b></p>
                                                                                <ul class="timeline ">
                                                                                    <li class=" primary py-0 my-0"> <span
                                                                                            class="font-weight-semibold fs-16 ms-3">Source</span>
                                                                                        <p
                                                                                            class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                            {{ $travelDetailsItem->trd_source }}
                                                                                        </p>
                                                                                    </li>
                                                                                    <li class="success mt-6 py-0 my-0">
                                                                                        <span
                                                                                            class="font-weight-semibold fs-16 ms-3">Destination</span>
                                                                                        <p
                                                                                            class="mb-0 pb-0 text-muted fs-14 ms-3 mt-1">
                                                                                            {{ $travelDetailsItem->trd_destination }}
                                                                                        </p>
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
                                                                            <label class="form-label">Travel
                                                                                Mode:&nbsp;</label>
                                                                            <span class="font-weight-bold">
                                                                                {{ $travelDetailsItem->fh_policy_tada_travel_mode->fh_travel_mode->m_name ?? '' }}
                                                                                @if (!empty($travelDetailsItem->trd_name))
                                                                                    ({{ $travelDetailsItem->trd_name }})
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Distance: :&nbsp;
                                                                            </label>
                                                                            <span class="font-weight-bold">
                                                                                {{ isset($travelDetailsItem->trd_total_distance) ? number_format($travelDetailsItem->trd_total_distance, 3) . ' KM' : 'N/A' }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Applied On: :&nbsp;
                                                                            </label>
                                                                            <span class="font-weight-bold">
                                                                                {{ $rawPlanData->created_at ? Carbon::parse($rawPlanData->created_at)->format('d-M-Y') : '' }}</span>
                                                                        </div>
                                                                        <div class="offset-md-5 bg-gray-100 border "
                                                                            style=" box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                                                            <div class="d-flex justify-content-end">
                                                                                <label
                                                                                    class="form-label mb-0">Amount:&nbsp;</label>
                                                                                <span class="font-weight-bold">₹
                                                                                    {{ $travelDetailsItem->trd_net_amount }}/-</span>
                                                                            </div>
                                                                            {{-- <div class="d-flex justify-content-end">
                                                                                    <label
                                                                                        class="form-label mb-0">Tax:&nbsp;</label>
                                                                                    <span class="font-weight-bold">₹
                                                                                        {{ $travelDetailsItem->te_taxes }}/-</span>
                                                                                </div> --}}
                                                                        </div>

                                                                        <div class="d-flex justify-content-end mt-2">
                                                                            <label class="form-label">Documents
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span> @php
                                                                                $documents = json_decode(
                                                                                    $travelDetailsItem->trd_documents,
                                                                                    true,
                                                                                ); // Decode JSON into an array
                                                                            @endphp

                                                                                @if (is_array($documents) && count($documents) > 0)
                                                                                    <a href="#" type="button"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#documentsModal{{ $loop->index . 'tabSelfTravelDetails' }}"
                                                                                        class="text-primary">
                                                                                        <u>View Documents</u>
                                                                                    </a>
                                                                                    <!-- Modal -->
                                                                                    @component('admin.components.document-modal', [
                                                                                        'id' => $loop->index . 'tabSelfTravelDetails',
                                                                                        'documents' => $documents, // Pass the decoded documents array
                                                                                        'componentString' => 'travelDetailsItem_',
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
                                                                                class="text-muted">{{ $travelDetailsItem->trd_remarks }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
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
                                                <li><a href="#tabCompanyLodgingExp" data-bs-toggle="tab"><i
                                                            class="fa fa-cogs"></i>&nbsp;Lodging Expenses</a></li>
                                                <li><a href="#tabCompanyMealExp" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;Meal Expenses</a></li>
                                                <li><a href="#tabCompanyOtherExp" data-bs-toggle="tab"><i
                                                            class="fa fa-tasks"></i>&nbsp;Other Expenses</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    @php
                                        $rawExpenseData = $claimData->fh_tada_request_plan->fh_tada_expenses;
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
                                        $companyExpenseTypes = $claimData->fh_tada_request_plan->fh_tada_expenses
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
                                                                    {{-- ******** for international trip ******* --}}
                                                                    @if ($claimData->fh_tada_request_plan->fh_policy_tada_travel_type->pttt_type_id == 126)
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label mb-0">Country
                                                                                Code:&nbsp;</label>
                                                                            <span class="font-weight-bold">
                                                                                {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_country_code }}/-</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label mb-0">Conversion Rate
                                                                                To INR:&nbsp;</label>
                                                                            <span class="font-weight-bold">
                                                                                {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_conversion_rate }}/-</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label mb-0">Amount In
                                                                                INR:&nbsp;</label>
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
                                                                            $subExpense = $expense->fh_sub_expense;
                                                                        @endphp
                                                                        <div class="d-flex justify-content-end">
                                                                            <label class="form-label">Expense Name
                                                                                :&nbsp;
                                                                            </label>
                                                                            <span
                                                                                class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{ $subExpense->tes_head }}</span>
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
                                                                    <li class="col-4"> <a href="{{ asset($document) }}"
                                                                            target="_blank" class="text-primary"> *
                                                                            {{ $displayName }} - View file
                                                                            {{ $dkey + 1 }},
                                                                        </a> </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    No Expense Added
                                                @endif
                                            </div>
                                            <div class="tab-pane" id="tabCompanyLodgingExp">
                                                <!-- Lodging Expenses -->
                                                @if (count($companyLodgingExpenseType) > 0)
                                                    @foreach ($companyLodgingExpenseType as $expenseType => $expense)
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
                                                                            <span
                                                                                class="font-weight-semibold fs-16 ms-3"><small
                                                                                    class="text-muted"><i
                                                                                        class="fa fa-calendar"></i>&nbsp;{{ $expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d-M-Y') : '' }}&nbsp;<i
                                                                                        class=" ms-3 fa fa-clock-o"></i>&nbsp;{{ $expense->te_to_time ? Carbon::parse($expense->te_to_time)->format('h:i A') : '' }}
                                                                                    @if ($expense->te_additional_info && json_decode($expense->te_additional_info, true))
                                                                                        <span
                                                                                            class="badge badge-md badge-primary-light ms-1">{{ json_decode($expense->te_additional_info, true)['lodging']['totalDays'] }}
                                                                                            days</span>
                                                                                </small></span>
                                                    @endif
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
                                                        <label class="form-label mb-0">Amount:&nbsp;</label>
                                                        <span class="font-weight-bold">₹
                                                            {{ $expense->te_amount }}/-</span>
                                                    </div>
                                                    <div class="d-flex justify-content-end">
                                                        <label class="form-label mb-0">Tax:&nbsp;</label>
                                                        <span class="font-weight-bold">₹
                                                            {{ $expense->te_taxes }}/-</span>
                                                    </div>
                                                    {{-- ******** for international trip ******* --}}
                                                    @if ($claimData->fh_tada_request_plan->fh_policy_tada_travel_type->pttt_type_id == 126)
                                                        <div class="d-flex justify-content-end">
                                                            <label class="form-label mb-0">Country Code:&nbsp;</label>
                                                            <span class="font-weight-bold">
                                                                {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_country_code }}/-</span>
                                                        </div>
                                                        <div class="d-flex justify-content-end">
                                                            <label class="form-label mb-0">Conversion Rate To
                                                                INR:&nbsp;</label>
                                                            <span class="font-weight-bold">
                                                                {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_conversion_rate }}/-</span>
                                                        </div>
                                                        <div class="d-flex justify-content-end">
                                                            <label class="form-label mb-0">Amount In INR:&nbsp;</label>
                                                            <span class="font-weight-bold">₹
                                                                {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_foreign_amount }}/-</span>
                                                        </div>
                                                    @endif
                                                    <div class="d-flex justify-content-end">
                                                        <label class="form-label mb-0">Deviation:&nbsp;</label>
                                                        <span class="font-weight-bold">₹
                                                            {{ $expense->te_deviation ?? 0 }}/-</span>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-end mt-2">
                                                    <label class="form-label">Documents :&nbsp;
                                                    </label>
                                                    @php
                                                        $documents = json_decode($expense->te_document, true); // Decode JSON into an array
                                                    @endphp

                                                    @if (is_array($documents) && count($documents) > 0)
                                                        <a href="#" type="button" data-bs-toggle="modal"
                                                            data-bs-target="#documentsModal{{ $loop->index . 'tabCompanyLodgingExp' }}"
                                                            class="text-primary">
                                                            <u>View Documents</u>
                                                        </a>
                                                        <!-- Modal -->
                                                        @component('admin.components.document-modal', [
                                                            'id' => $loop->index . 'tabCompanyLodgingExp',
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
                                                    <span class="text-muted">{{ $expense->te_remarks }}</span>
                                                </div>
                                                @if (isset($expense->fh_sub_expense))
                                                    @php
                                                        $subExpense = $expense->fh_sub_expense;
                                                    @endphp
                                                    <div class="d-flex justify-content-end">
                                                        <label class="form-label">Expense Name
                                                            :&nbsp;
                                                        </label>
                                                        <span
                                                            class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{ $subExpense->tes_head }}</span>
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
                                                <li class="col-4"> <a href="{{ asset($document) }}" target="_blank"
                                                        class="text-primary"> *
                                                        {{ $displayName }} - View file {{ $dkey + 1 }},
                                                    </a> </li>
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
                                                            <label class="form-label mb-0">Amount:&nbsp;</label>
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
                                                    {{-- ******** for international trip ******* --}}
                                                    @if ($claimData->fh_tada_request_plan->fh_policy_tada_travel_type->pttt_type_id == 126)
                                                        <div class="col-md-3">
                                                            <div class="d-flex ">
                                                                <label class="form-label mb-0">Country Code:&nbsp;</label>
                                                                <span class="font-weight-bold">
                                                                    {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_country_code }}/-</span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="d-flex ">
                                                                <label class="form-label mb-0">Conversion Rate To
                                                                    INR:&nbsp;</label>
                                                                <span class="font-weight-bold">
                                                                    {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_conversion_rate }}/-</span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="d-flex ">
                                                                <label class="form-label mb-0">Amount In INR:&nbsp;</label>
                                                                <span class="font-weight-bold">₹
                                                                    {{ $claimData->fh_tada_request_plan->fh_tada_expenses->te_foreign_amount }}/-</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if (isset($expense->fh_sub_expense))
                                                        @php
                                                            $subExpense = $expense->fh_sub_expense;
                                                        @endphp
                                                        <div class="col-md-3">
                                                            <div class="d-flex ">
                                                                <label class="form-label">Expense Name :&nbsp;
                                                                </label>
                                                                <span
                                                                    class="text-muted">({{ $subExpense->tes_code }})&nbsp;{{ $subExpense->tes_head }}</span>
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
                                                        <li class="col-4"> <a href="{{ asset($document) }}"
                                                                target="_blank" class="text-primary"> *
                                                                {{ $displayName }} - View file {{ $dkey + 1 }},
                                                            </a> </li>
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
                                                                <p class="mb-0 pb-0 text-muted fs-17 ms-3 mt-1">
                                                                    <span class="badge badge badge-info-light">
                                                                        {{ $expense->te_name }}</span> </span>
                                                                </p>
                                                            </li>
                                                            <li class="mt-1">
                                                                <span class="font-weight-semibold fs-16 ms-3"><small
                                                                        class="text-muted"><i
                                                                            class="fa fa-calendar"></i>&nbsp;{{ $expense->te_from_date ? Carbon::parse($expense->te_from_date)->format('d-M-Y') : '' }}<i
                                                                            class=" ms-3 fa fa-clock-o"></i>&nbsp;
                                                                        {{ $expense->te_from_time ? Carbon::parse($expense->te_from_time)->format('h:i A') : '' }}&nbsp;To</small></span>
                                                            </li>
                                                            <li class="mt-1">
                                                                <span class="font-weight-semibold fs-16 ms-3"><small
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
                                                                <label class="form-label mb-0">Amount:&nbsp;</label>
                                                                <span class="font-weight-bold">₹
                                                                    {{ $expense->te_amount }}/-</span>
                                                            </div>
                                                            <div class="d-flex justify-content-end">
                                                                <label class="form-label mb-0">Tax:&nbsp;</label>
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
                                                                $subExpense = $expense->fh_sub_expense;
                                                            @endphp
                                                            <div class="d-flex justify-content-end">
                                                                <label class="form-label">Expense Name
                                                                    :&nbsp;
                                                                </label>
                                                                <span class="text-muted"
                                                                    style="max-width: 300px; word-wrap: break-word; white-space: normal;">
                                                                    ({{ $subExpense->tes_code }})
                                                                    &nbsp;{{ $subExpense->tes_head }}
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
                                                        <li class="col-4"> <a href="{{ asset($document) }}"
                                                                target="_blank" class="text-primary"> *
                                                                {{ $displayName }} - View file {{ $dkey + 1 }},
                                                            </a> </li>
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
        /*$(document).ready(function() {
                var map = null; // Store map instance

                $('#largemodal').on('shown.bs.modal', function(event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var locations = button.data('locations'); // Retrieve travel path data
                    $('#totalDistance').text(button.data('total-distance')); // Set total distance
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

                    // Add markers and populate the list
                    travelLocations.forEach(function(location, index) {
                        var marker = L.marker([location.latitude, location.longitude]).addTo(map)
                            .bindPopup(`<b>${location.location ?? "Unknown Location"}</b>`);

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
            });*/
    </script>

    <script>
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
                        const coordsString = coordinates.map(coord => `${coord[1]},${coord[0]}`).join(
                            ';');
                        const response = await fetch(
                            `https://router.project-osrm.org/route/v1/driving/${coordsString}?overview=full&geometries=geojson`
                            );
                        const data = await response.json();

                        // Store individual legs for segment highlighting
                        if (data.routes[0].legs) {
                            routeSegments = data.routes[0].legs.map(leg => {
                                return leg.steps.map(step => {
                                    return step.geometry.coordinates.map(coord => [
                                        coord[1], coord[0]
                                    ]);
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
                        getOffsetPosition(location.latitude, location.longitude, positionCount[
                            positionKey]) : [location.latitude, location.longitude];

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
                    const formattedTime = location.time.replace(/\b(am|pm)\b/i, match => match
                        .toUpperCase());
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
                            const group = new L.featureGroup([markers[idx], markers[idx +
                                1]]);
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
                            selectedIcon.options.html = selectedIcon.options.html.replace('">',
                                '" style="box-shadow: 0 0 10px yellow;">');
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
                                const distance = segmentLine.distanceTo(
                                    clickedLatLng);

                                if (distance < minDistance) {
                                    minDistance = distance;
                                    closestSegment = segment;
                                }
                            });

                            if (closestSegment) {
                                const segmentBounds = L.polyline(closestSegment)
                            .getBounds();
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

    <script>
        $(document).ready(function() {

            window.Echo.private("claim-approval.{{ $claimData->tc_id }}").listen('ApprovalEvent', (event) => {
                // console.log('this is event',event);
                // if((event.current_approver_id == "{{ md5(Auth::user()->emp_id) }}" ||  event.next_approver_id == "{{ md5(Auth::user()->emp_id) }}") && event.request_id == "{{ md5($claimData->tc_id) }}"){
                //     var baseUrl = $('#ajaxCall').val();
                //     window.location.href = baseUrl + "/admin/ta-da-request/claim/show/{{ md5($claimData->tc_id) }}";
                // }
                $.ajax({
                    url: '{{ route('claim.request.show', ['id' => md5($claimData->tc_id)]) }}',
                    method: 'GET',
                    beforeSend: function() {
                        console.log('Reverb Calling Request...');
                    },
                    success: function(response) {
                        // console.log('Reverb Response Received...', response);
                        $('#tabSelfExpSummary').html(response.html);
                        $('#claimStatusBadge').css('background-color', response.color);
                        $('#claimStatusIcon').attr('class', response.icon);
                        $('#claimStatusName').text(response.tc_request_status_name);

                        $('#tabSelfExpSummary').html(response.html);
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

            var deductionAmount = "{{ $claimData->tc_deduction_amount }}";
            var tcId = "{{ md5($claimData->tc_id) }}";
            if (deductionAmount) {
                $('.addDeductionBtn').click();
                $('#deductionAmount').val(deductionAmount);
            }
        });
        var tcId = '';
        $(document).on('click', '.actionBtn', function() {
            var dataAttributes = {};
            $.each(this.attributes, function() {
                if (this.name.startsWith('data-')) {
                    var key = this.name.slice(5); // remove 'data-' prefix
                    dataAttributes[key] = this.value;
                }
            });

            tcId = dataAttributes["tc_id"];

            dataAttributes['message'] = $('#actionMessage').val();
            if (dataAttributes['message'] == '') {
                Swal.fire({
                    icon: "warning",
                    text: 'Message is required.',
                    timer: 3000,
                });
                return false;
            }
            const deductions = {};
            const payableAmounts = {};
            const inputs = document.querySelectorAll('.deduction-input');
            let deductionAmount = 0;
            var isValid = true;
            inputs.forEach(input => {
                const $input = $(input);
                // Get payable amount using jQuery
                const payableAmount = parseFloat($input.data(
                'payableamount')); // Ensure payableAmount is treated as a number
                const key = input.name.match(/\[(.*?)\]/)[1]; // Extracts the exp_type_id
                const value = parseFloat(input.value) || 0; // Gets the value or defaults to 0
                if (value < 0) {
                    isValid = false;
                    Swal.fire({
                        icon: "error",
                        text: 'Deduction amount must be positive.',
                        timer: 3000,
                    });
                }
                payableAmounts[key] = payableAmount;
                deductions[key] = value; // Creates key-value pair
                deductionAmount += value;
            });
            if (!isValid) {
                return false;
            }
            if ($('#deductionAmount').length > 0 && $('#deductionAmount').val() == '') {
                Swal.fire({
                    icon: "warning",
                    text: 'Deduction Amount Required',
                    timer: 3000,
                });
                return false;
            }
            dataAttributes['deduction_amount'] = deductionAmount; // $('#deductionAmount').val();
            dataAttributes['deduction_info'] = deductions;
            dataAttributes['payable_amount'] = payableAmounts;

            $.ajax({
                url: '{{ route('admin.approval-handler') }}',
                method: "post",
                data: {
                    _token: '{{ csrf_token() }}',
                    POST_TYPE: 'CLAIM_REQUEST_APPROVAL',
                    data: dataAttributes
                },
                dataType: "json",
                beforeSend: function() {
                    $("#gloabal-overlay").show();
                    $(".actionBtn").attr("disabled", true);
                },
                success: function(data) {
                    $("#gloabal-overlay").hide();
                    $(".actionBtn").attr("disabled", false);
                    if (data.status == true) {
                        Swal.fire({
                            icon: "success",
                            text: data.message,
                            timer: 3000,
                        });
                        var baseUrl = $('#ajaxCall').val();
                        window.location.href = baseUrl + "/admin/ta-da-request/claim/show/" + tcId;
                    } else {
                        Swal.fire({
                            icon: "warning",
                            text: data.message,
                            timer: 3000,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $("#gloabal-overlay").hide();
                    Swal.fire({
                        icon: "error",
                        text: error,
                        timer: 3000,
                    });
                },
            });

        });

        $('.tdExpenseType').on('click', function() {
            var tdText = $(this).text();
            document.querySelector(`a[href="#tabSelf${tdText}Exp"]`).click();
        });

        $('.deduction-input').on('input', function() {
            var deductionJson = {};
            // Get the value of the current input field
            let deductionValue = $(this).val();

            // Get the name attribute of the current input field
            let deduction_expense_id = $(this).attr('id');

            deductionJson["'" + deduction_expense_id + "'"] = deductionValue;
        });

        $(document).on('click', '.addDeductionBtn', function() {
            if ($('#deductionAmount').length == 0) {
                html = `<div class="row" id="deductionAmountRow">
                    <div class="col-md-10 col-lg-10">
                        <label class="form-label mb-0 mt-2">Deduction Amount</label>
                    </div>
                    <div class="col-md-12 col-lg-12">
                        <div class="input-group">
                        <input type="number" name="deductionAmount" min="0" step="1" oninput="validity.valid||(value='');" class="form-control " id="deductionAmount" placeholder="Enter Deduction Amount">
                        <button type="button" class="btn btn-danger" id="removeDeductionInput"><i class="feather feather-trash"></i></button>
                        </div>
                    </div>
                </div>`;
                $('#deductionAmountDiv').append(html);
            }
        });

        $(document).on('click', '#removeDeductionInput', function() {
            $("#deductionAmountRow").remove();
        })



        $(document).on('click', '.handleDeductionBtn', function() {
            var dataAttributes = {};
            $.each(this.attributes, function() {
                if (this.name.startsWith('data-')) {
                    var key = this.name.slice(5); // remove 'data-' prefix
                    dataAttributes[key] = this.value;
                }
            });

            tcId = dataAttributes["tc_id"];

            if ($('#empAcceptanceMsg').length > 0) {
                dataAttributes['remarks'] = $('#empAcceptanceMsg').val();
                if (dataAttributes['remarks'] == '') {
                    Swal.fire({
                        icon: "warning",
                        text: 'Message is required.',
                        timer: 3000,
                    });
                    return false;
                }
            }

            $.ajax({
                url: "{{ route('admin.deduction-handler') }}",
                method: "post",
                data: {
                    _token: '{{ csrf_token() }}',
                    data: dataAttributes
                },
                dataType: "json",
                beforeSend: function() {
                    $("#gloabal-overlay").show();
                    $(".handleDeductionBtn").attr("disabled", true);
                },
                success: function(data) {
                    $("#gloabal-overlay").hide();
                    $(".handleDeductionBtn").attr("disabled", false);
                    if (data.status == true) {
                        Swal.fire({
                            icon: "success",
                            text: data.message,
                            timer: 3000,
                        });
                        var baseUrl = $('#ajaxCall').val();
                        window.location.href = baseUrl + "/admin/ta-da-request/claim-request/show/" +
                            tcId;
                    } else {
                        Swal.fire({
                            icon: "warning",
                            text: data.message,
                            timer: 3000,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.log('error', xhr, status, error);
                    $("#gloabal-overlay").hide();
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
