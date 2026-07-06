<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterTable;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;

$user = Auth::user();
$employeeFilter = CommonUtils::getCustomModelData(new Employee(), [['method' => 'where', 'args' => ['emp_b_id', $user->emp_b_id]]]);
$branchFilter = CommonUtils::getCustomModelData(new Branch(), [['method' => 'where', 'args' => ['br_b_id', $user->emp_b_id]]]);
$gradeFilter = CommonUtils::getCustomModelData(new Grade(), [['method' => 'where', 'args' => ['g_b_id', $user->emp_b_id]]]);
$statusFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'APPROVAL_STATUS']]]);
$Department = CommonUtils::getCustomModelData(new Department(), [['method' => 'where', 'args' => ['d_b_id', $user->emp_b_id]]]);
$Designation = CommonUtils::getCustomModelData(new Designation(), [['method' => 'where', 'args' => ['dg_b_id', $user->emp_b_id]]]);
$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    {{ $title }}
@endsection
@section('css')
    <style>
        .stat-card {
            border-radius: 16px;
            background: white;
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            height: 4px;
            width: 100%;
            top: 0;
            left: 0;
            background: linear-gradient(90deg, #0d6efd, #20c997);
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.09);
        }

        .stat-title {
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .stat-value {
            font-size: 15px;
            line-height: 1;
        }

        .stat-sub {
            font-size: 0.81rem;
        }

        .stat-badge {
            font-size: 0.68rem;
            padding: 4px 0px;
            border-radius: 999px;
            font-weight: 600;
        }

        .icon-box {
            width: 45px;
            height: 47px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 1.35rem;
        }

        .icon-trip {
            background: linear-gradient(135deg, #0d6efd, #6ea8fe);
            color: white;
        }

        .icon-spend {
            background: linear-gradient(135deg, #198754, #75b798);
            color: white;
        }

        .icon-pending {
            background: linear-gradient(135deg, #ffc107, #ffda6a);
            color: #664d03;
        }

        .icon-reimburse {
            background: linear-gradient(135deg, #0dcaf0, #6edff6);
            color: #055160;
        }

        .chart-card {
            border-radius: 14px;
            border: 1px solid #eef2f6;
        }

        .dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .bg-purple {
            background: #8b5cf6;
        }

        @media (max-width: 576px) {
            .stat-value {
                font-size: 15px;
            }
        }
    </style>
@endsection
@section('content')
    <style>
        .page-header .form-control:focus {
            box-shadow: none;
        }

        .page-header .input-group {
            transition: all 0.2s ease-in-out;
        }

        .page-header .input-group:hover {
            transform: translateY(-1px);
        }
    </style>

    <div class="p-0 pb-4">
        <div class="container-fluid" id="confetti">
            <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <!-- Page Title -->
                <div class="page-leftheader">
                    <h4 class="page-title d-flex align-items-center gap-2 mb-0 fw-semibold">
                        <i class="feather feather-home fs-5 text-primary"></i>
                        <span style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto">
                            Dashboard
                        </span>
                    </h4>
                </div>

                <!-- Filters -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="btn-group btn-group-sm" role="group" style="    height: 42px;">
                        <button class="btn filter-btn active" id="monthly"
                            style="border-radius: 50px 0 0 50px; font-weight: 500; padding: 0.5rem 1.1rem;">
                            Monthly
                        </button>

                        <button class="btn filter-btn" id="claimWise"
                            style="border-radius: 0 50px 50px 0; font-weight: 500; padding: 0.5rem 1.1rem; border-left: none;">
                            Claim Wise
                        </button>
                    </div>
                    <!-- Date Picker -->
                    <div class="input-group shadow-sm rounded-pill overflow-hidden" style="max-width: 180px;">
                        <span class="input-group-text bg-primary text-white border-0">
                            <i class="feather feather-calendar"></i>
                        </span>
                        <input type="text" class="form-control border-0" id="dashboardDate" value="{{ date('d M Y') }}"
                            onchange="dashboardCountAjax(this)">
                    </div>

                    <!-- Time Picker -->
                    <div class="input-group shadow-sm rounded-pill overflow-hidden" style="max-width: 180px;">
                        <span class="input-group-text bg-primary text-white border-0">
                            <i class="feather feather-clock"></i>
                        </span>
                        <input type="text" class="form-control border-0" id="dashboardTime" placeholder="HH : MM">
                    </div>
                    <!-- Form for Date Range -->
                    <form id="dateForm" action="{{ route('dashboard') }}" method="get" class="d-flex align-items-center">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden" style="max-width: 180px;">
                            <span class="input-group-text bg-primary text-white border-0">
                                <i class="las la-calendar-alt"></i>
                            </span>
                            <input type="text" class="form-control border-0" id="fromDate" name="dateRange"
                                placeholder="Select Date Range">
                        </div>
                        <button type="submit" class="btn btn-primary ms-2 rounded-pill">Apply</button>
                    </form>

                    <script>
                        $(document).ready(function() {
                            // Optional: You can still trigger the date picker on click
                            $('#fromDate').on('click', function() {
                                $(this).focus(); // Focus to open picker if using a datepicker plugin
                            });
                        });
                    </script>


                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 row mb-4">
        <!-- TOTAL TRIPS -->
        <div class="col-md-2 col-sm-6">
            <div class="card stat-card p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-title text-uppercase">Trips</div>
                        <div class="stat-value mt-1" data-count="{{ $total_trip }}">0</div>
                        <div class="d-flex align-items-center gap-2 stat-sub mt-1">
                            <span class="stat-badge bg-primary-subtle text-primary">
                                <i class="bi bi-calendar-check"></i>
                                {{ $currentTrips }}
                            </span>

                            <span class="text-muted"></span>
                        </div>
                    </div>
                    <div class="icon-box icon-trip">
                        <i class="bi bi-airplane-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- PENDING APPROVALS -->
        <div class="col-md-2 col-sm-6">
            <div class="card stat-card p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-title text-uppercase">Pending</div>
                        <div class="stat-value mt-1" data-count="{{ $pending_approvals }}">0</div>
                        <div class="d-flex align-items-center gap-2 stat-sub mt-1">
                            <span class="stat-badge bg-warning-subtle text-warning">
                                <i class="bi bi-hourglass-split"></i>{{ $currenyt_pending_approvals }}
                            </span>
                            <span class="text-muted"> awaiting</span>
                        </div>
                    </div>
                    <div class="icon-box icon-pending">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved APPROVALS -->
        <div class="col-md-2 col-sm-6">
            <div class="card stat-card p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-title text-uppercase">Approved</div>
                        <div class="stat-value mt-1" data-count="{{ $approved_approvals }}">0</div>
                        <div class="d-flex align-items-center gap-2 stat-sub mt-1">
                            <span class="stat-badge bg-success-subtle text-success">
                                <i class="bi bi-check-circle"></i> {{ $approved_rem }}
                            </span>
                            <span class="text-muted"> Reimbursed</span>
                        </div>
                    </div>
                    <div class="icon-box icon-approved">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOTAL SPEND -->
        <div class="col-md-2 col-sm-6">
            <div class="card stat-card p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-title text-uppercase">Spend</div>
                        <div class="stat-value mt-1" data-count="{{ $totaltSpend }}" data-currency>
                            ₹{{ number_format($totaltSpend, 2) }}</div>
                        <div class="d-flex align-items-center gap-2 stat-sub mt-1">
                            <span class="stat-badge bg-primary-subtle text-primary">
                                <i class="bi bi-currency-rupee"></i>
                                {{ number_format($currentSpend, 2) }}
                            </span>
                            <span class="text-muted"></span>
                        </div>
                    </div>
                    <div class="icon-box icon-spend">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deduction / Saving -->
        <div class="col-md-2 col-sm-6">
            <div class="card stat-card p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-title text-uppercase">Gap</div>
                        <div class="stat-value mt-1" data-count="{{ $total_saving }}" data-currency>
                            ₹{{ number_format($total_saving, 2) }}</div>
                        <div class="d-flex align-items-center gap-2 stat-sub mt-1">
                            <span class="stat-badge bg-primary-subtle text-primary"><i class="bi bi-currency-rupee"></i>
                                {{ number_format($current_saving, 2) }}
                            </span>
                            <span class="text-muted"></span>
                        </div>
                    </div>
                    <div class="icon-box icon-spend">
                        <i class="bi bi-piggy-bank fs-4"></i>
                    </div>

                </div>
            </div>
        </div>


        <!-- REIMBURSEMENTS -->
        <div class="col-md-2 col-sm-6">
            <div class="card stat-card p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-title text-uppercase">Reimbursements</div>
                        <div class="stat-value mt-1" id="totalReimburse" data-count="{{ $total_reimburse }}"
                            data-currency>
                            ₹{{ number_format($total_reimburse, 2) }}
                        </div>

                        <div class="stat-value mt-1 d-none" id="monthReimbursed" data-count="{{ $total_reimburse }}"
                            data-currency>
                            ₹{{ number_format($total_reimburse, 2) }}
                        </div>


                        <div class="d-flex align-items-center gap-2 stat-sub mt-1">
                            <span class="stat-badge bg-success-subtle text-success">
                                ₹ {{ number_format($current_reimburse, 2) }}
                            </span>
                        </div>
                    </div>
                    <div class="icon-box icon-reimburse">
                        <i class="bi bi-arrow-repeat fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- COMPLETE CHARTS SECTION - SINGLE PAGE, FIXED HEIGHTS -->
    <div class="row g-4 mb-4">

        <style>
            .filter-btn {
                background-color: #f8f9fa;
                color: #495057;
                border: 1px solid #dee2e6;
                transition: all 0.2s ease;
            }

            .filter-btn:hover {
                background-color: #e9ecef;
                color: #212529;
            }

            .filter-btn.active {
                background-color: #0d6efd;
                color: white;
            }
        </style>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div
                    class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center px-4 pt-3 pb-2">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-graph-up-arrow text-primary me-1"></i>
                        Travel Distribution
                    </h6>

                    <!-- IMPROVED FILTER BUTTONS -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn filter-btn active" data-filter="all"
                            style="border-radius: 50px 0 0 50px; font-weight: 500; padding: 0.5rem 1.1rem;">
                            All
                        </button>
                        <button class="btn filter-btn" data-filter="local"
                            style="font-weight: 500; padding: 0.5rem 1.1rem; border-left: none;">
                            Local
                        </button>
                        <button class="btn filter-btn" data-filter="outstation"
                            style="font-weight: 500; padding: 0.5rem 1.1rem; border-left: none;">
                            Outstation
                        </button>
                        <button class="btn filter-btn" data-filter="international"
                            style="border-radius: 0 50px 50px 0; font-weight: 500; padding: 0.5rem 1.1rem; border-left: none;">
                            International
                        </button>
                    </div>
                </div>

                <div class="card-body p-4" style="height: 380px;">
                    <canvas id="lineChart" style="height:340px;"></canvas>

                    <!-- CUSTOM LEGEND -->
                    <div class="w-100 d-flex flex-wrap justify-content-center gap-3 mt-3">
                        <div class="legend-pill d-flex align-items-center gap-2 px-3 py-1">
                            <span class="dot" style="background:#007bff"></span>
                            <span class="text-muted small">Local</span>
                            <span class="fw-semibold ms-1">{{ $counts['local'] ?? 0 }}</span>
                        </div>

                        <div class="legend-pill d-flex align-items-center gap-2 px-3 py-1">
                            <span class="dot" style="background:#28a745"></span>
                            <span class="text-muted small">Outstation</span>
                            <span class="fw-semibold ms-1">{{ $counts['outstation'] ?? 0 }}</span>
                        </div>

                        <div class="legend-pill d-flex align-items-center gap-2 px-3 py-1">
                            <span class="dot" style="background:#ffc107"></span>
                            <span class="text-muted small">International</span>
                            <span class="fw-semibold ms-1">{{ $counts['international'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DONUT CHART -->
        <style>
            .chart-card {
                transition: all 0.25s ease;
            }

            .chart-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
            }

            /* Center text inside donut */
            .chart-center-text {
                position: absolute;
                text-align: center;
                pointer-events: none;
                top: 47%;
                transform: translateY(-50%);
            }

            /* Legend pill */
            .legend-pill {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 4px 10px;
                border-radius: 20px;
                background: #f8f9fa;
                font-size: 13px;
            }

            .legend-pill .dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
            }
        </style>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 text-center chart-card">
                <div class="card-header bg-transparent border-0 text-start pb-0">
                    <h6 class="fw-semibold mb-1">
                        <i class="bi bi-pie-chart-fill text-primary me-1"></i>
                        Travel Distribution
                    </h6>
                </div>

                <div class="card-body d-flex flex-column align-items-center justify-content-center position-relative"
                    style="height:30vh;">

                    <!-- Center text -->
                    <div class="chart-center-text">
                        <h5 class="fw-bold mb-0">{{ $counts['local'] + $counts['outstation'] + $counts['international'] }}
                        </h5>
                        <small class="text-muted">Total Trips</small>
                    </div>

                    <!-- Chart -->
                    <canvas id="donutChart" style="max-width:340px;height:260px;"></canvas>

                    <!-- Legend -->
                    <div class="mt-4 w-100 d-flex flex-wrap justify-content-center gap-3">
                        @foreach ($donutChartData as $item)
                            @php
                                $colors = [
                                    'local' => '#007bff', // blue
                                    'outstation' => '#28a745', // green
                                    'international' => '#ffc107', // yellow
                                ];

                                $labelKey = strtolower($item['label']);
                                $color = $colors[$labelKey] ?? '#6c757d'; // fallback gray
                            @endphp

                            <div class="legend-pill d-flex align-items-center gap-2">
                                <span class="dot" style="background: {{ $color }}"></span>

                                <span class="text-muted small">
                                    {{ ucfirst($item['label']) }}
                                </span>

                                <span class="fw-semibold ms-1">
                                    {{ number_format($item['percent'], 1) }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- BAR CHART - Full Width -->
        <div class="col-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="fw-semibold mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-bar-chart-fill text-primary"></i>
                                Department-wise Spending & Trips

                            </h5>
                            {{-- <small class="text-muted">Current month breakdown • Updated Jan 28, 2026</small> --}}
                        </div>
                    </div>
                </div>

                <div class="card-body px-4 pb-4">
                    <div style="height:380px">
                        <canvas id="tripChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">

                <!-- Header -->
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4"
                    style="display: flex; justify-content: space-between;">
                    <h5 class="fw-semibold mb-1 d-flex align-items-center gap-2" id="chartTitle">
                        <i class="bi bi-bar-chart-fill text-primary"></i>
                        <span id="chartTitleText">Monthly Spend vs Reimburse</span>
                    </h5>
                </div>

                <!-- Monthly Chart -->
                <div class="card-body px-4 pb-4" id="tripTwoDiv">
                    <div style="height:370px">
                        <canvas id="tripCharttwo"></canvas>
                    </div>
                </div>

                <!-- Claim Wise Chart -->
                <div class="card-body px-4 pb-4 d-none" id="tadaDiv">
                    <div style="height:370px">
                        <canvas id="tadaChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
        <!-- Travel Details Table Section -->
        <style>
            .dot {
                display: inline-block;
                width: 12px;
                height: 12px;
                border-radius: 50%;
            }

            canvas {
                width: 100% !important;
                height: 90% !important;
            }
        </style>

        {{-- Average Net payable By travel Type And Vehicle   --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                {{-- Header --}}
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between">
                    <h5 class="fw-semibold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary"></i>
                        <span>Average Net payable By travel Type And Vehicle </span>
                    </h5>
                </div>

                {{-- Chart --}}
                <div class="card-body px-4 pb-4" id="tadaDiv">
                    <div style="height:370px">
                        <canvas id="travelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Claimed vs Net Payable By Grade  --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between">
                    <h5 class="fw-semibold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary"></i>
                        <span>Claimed vs Net Payable By Grade </span>
                    </h5>
                </div>

                <div class="card-body px-4 pb-4" id="tadaDiv">
                    <div style="height:370px">
                        <canvas id="netpayvsclaimedamt"></canvas>
                    </div>
                </div>
            </div>
        </div>


        {{-- Top 10 Employees By Net Payable  --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between">
                    <h5 class="fw-semibold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary"></i>
                        <span>Top 10 Employees By Net Payable </span>
                    </h5>
                </div>

                <div class="card-body px-4 pb-4">
                    <div style="height:370px">
                        <canvas id="topemployees"></canvas>
                    </div>
                </div>
            </div>
        </div>


        {{-- Monthly Claims Summary --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between">
                    <h5 class="fw-semibold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary"></i>
                        <span>Monthly Claims Summary</span>
                    </h5>
                </div>

                <div class="card-body px-4 pb-4">
                    <div style="height:370px">
                        <table class="table table-bordered mb-0 table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:20%;">Month</th>
                                    <th>Claim Count</th>
                                    <th>Claimed Amount</th>
                                    <th>Net Payable Amount</th>
                                    <th>Percent Gap</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($monthlyClaims as $claim)
                                    <tr>
                                        <td style="width:20%;">{{ \Carbon\Carbon::parse($claim->month)->format('M - Y') }}</td>
                                        <td>{{ $claim->claim_count }}</td>
                                        <td>₹{{ number_format($claim->total_claimed, 2) }}</td>
                                        <td>₹{{ number_format($claim->total_paid, 2) }}</td>
                                        <td>{{ number_format($claim->percent_difference, 1) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Expense Categories By Amount  --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between">
                    <h5 class="fw-semibold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary"></i>
                        <span>Top Expense Categories By Amount </span>
                    </h5>
                </div>

                <div class="card-body px-4 pb-4">
                    <div style="height:370px">
                        <table class="table table-bordered mb-0 table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:20%;">Expense Type</th>
                                    <th>Total Amount</th>
                                    <th>Total Claims</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expenseSummary as $type => $data)
                                    <tr>
                                        <td style="width:20%;">{{ $type }}</td>
                                        <td>₹{{ number_format($data['total_amount'], 2) }}</td>
                                        <td>{{ $data['total_claims'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No expense data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        {{-- High Gap And Large Claims --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between">
                    <h5 class="fw-semibold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary"></i>
                        <span>High Gap And Large Claims</span>
                    </h5>
                </div>

                <div class="card-body px-4 pb-4">
                    <div style="height:370px; overflow-y:auto;">
                       <table class="table table-bordered table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:16%;">Claim ID</th>
                                    <th style="width:18%;">Employee</th>
                                    <th style="width:16%;">Travel Type</th>
                                    <th style="width:16%;" class="text-end">Claimed Amount</th>
                                    <th style="width:16%;" class="text-end">Net Payable</th>
                                    <th style="width:18%;" class="text-end">Absolute Gap</th>
                                </tr>
                            </thead>
                        
                            <tbody>
                                @forelse ($highclaimsdata as $claim)
                                    <tr>
                                        <td>{{ $claim['claim_id'] ?? '-' }}</td>
                        
                                        <td>
                                            <strong>{{ $claim['employee_name'] ?? '-' }}</strong>
                                        </td>
                        
                                        <td>{{ $claim['travel_type'] ?? '-' }}</td>
                        
                                        <td class="text-end">
                                            ₹ {{ number_format($claim['claimed_amount'] ?? 0, 2) }}
                                        </td>
                        
                                        <td class="text-end">
                                            ₹ {{ number_format($claim['net_payable_amount'] ?? 0, 2) }}
                                        </td>
                        
                                        <td class="text-end">
                                            ₹ {{ number_format($claim['absolute_gap'] ?? 0, 2) }}
                                        </td>
                                    </tr>
                        
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No claims found for the selected period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <!-- ROW -->
        <div>
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">

                    <div class="card-header border-0">
                        <h4 class="card-title">Claim Submitters</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-1">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test"
                                        style="width: 100%" data-length>
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

                            <div class="col-sm-6">
                            </div>


                            <div class="col-sm-1">
                                <button class="custom-button w-100" type="button" onclick="toggleFilters()"
                                    style="margin-top: 28px;">
                                    <!-- Custom SVG: 2 horizontal lines with knobs -->
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <!-- Top slider -->
                                        <line x1="3" y1="8" x2="21" y2="8"
                                            stroke-linecap="round" />
                                        <circle cx="10" cy="8" r="1.5" fill="currentColor" />

                                        <!-- Bottom slider -->
                                        <line x1="3" y1="16" x2="21" y2="16"
                                            stroke-linecap="round" />
                                        <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                    </svg>
                                    Filters
                                </button>
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

                            <div class="row" id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="col-md">
                                    <label for="branchFilter" class="form-label">Employee</label>
                                    <select id="travel_employeeFilter" data-filter
                                        class="form-select search_test">
                                        <option value="">All</option>
                                        @foreach ($employeeFilter as $employeeF)
                                            <option value="{{ $employeeF->emp_id }}">
                                                {{ ($employeeF->emp_code ? $employeeF->emp_code . ' - ' : '') . $employeeF->emp_full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="travel_department" class="form-label">Department</label>
                                    <select id="travel_department" data-filter="travel_department"
                                        class="form-select search_test">
                                        <option value="">All</option>
                                        @foreach ($Department as $branchF)
                                            <option value="{{ $branchF->d_id }}">{{ $branchF->d_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="travel_designation" class="form-label">Designation</label>
                                    <select id="travel_designation" data-filter="travel_designation"
                                        class="form-select  search_test">
                                        <option value="">All</option>
                                        @foreach ($Designation as $gradeF)
                                            <option value="{{ $gradeF->dg_id }}">{{ $gradeF->dg_name }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md">
                                    <label for="activeFilter" class="form-label">Status</label>
                                    <select id="travel_statusFilter" data-filter
                                        class="form-select  search_test">
                                        <option value="">All</option>
                                        @foreach ($statusFilter as $statusF)
                                            <option value="{{ $statusF->m_id }}">{{ $statusF->m_name }}</option>
                                        @endforeach

                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <p class="form-label">Date Range</p>
                                        <div class="input-group mb-3"
                                            style="border-radius: 50px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                            <span class="input-group-text bg-primary-subtle text-primary border-0"
                                                style="border-radius: 50px 0 0 50px; padding: 0.5rem 1rem;">
                                                <i class="las la-calendar-alt fs-5"></i>
                                            </span>
                                            <input type="text" id="tabledatafromDate" name="tabledatafromDate"
                                                class="form-control border-0"
                                                style="border-radius: 0 50px 50px 0; padding-left: 1rem;"
                                                data-date-filter="from-date" placeholder="Select date">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <script>
                                function toggleFilters() {
                                    const container = document.getElementById('filterContainer');
                                    container.style.display = container.style.display === 'none' ? 'flex' : 'none';
                                }
                            </script>
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
                            </style>
                        </div>

                        <div class="">
                            <table class="table display table-hover table-vcenter text-wrap border-bottom"
                                id="travel-request-table-dynamic">
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
    @endsection
    @section('script')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
        <script>
            $(function() {
                $('#fromDate').daterangepicker({
                    startDate: moment().startOf('month'), // May 1, 2025
                    endDate: moment().endOf('month'), // May 31, 2025
                    minDate: moment('2000-01-01'), // Allow all past dates
                    maxDate: moment().add(5, 'years'), // Allow future dates
                    opens: 'left',
                    autoApply: true,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')],
                        'This Year': [moment().startOf('year'), moment().endOf('year')],
                        'Current Fiscal Period': [moment().startOf('quarter'), moment().endOf('quarter')],
                    },
                    locale: {
                        format: 'MMM D, YYYY'
                    }
                });
            });
        </script>

        <script>
            $(function() {
                $('#tabledatafromDate').daterangepicker({
                    startDate: moment().startOf('month'), // May 1, 2025
                    endDate: moment().endOf('month'), // May 31, 2025
                    minDate: moment('2000-01-01'), // Allow all past dates
                    maxDate: moment().add(5, 'years'), // Allow future dates
                    opens: 'left',
                    autoApply: true,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')],
                        'This Year': [moment().startOf('year'), moment().endOf('year')],
                        'Current Fiscal Period': [moment().startOf('quarter'), moment().endOf('quarter')],
                    },
                    locale: {
                        format: 'MMM D, YYYY'
                    }
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const timeInput = document.getElementById('dashboardTime');
                const now = new Date();
                timeInput.value =
                    String(now.getHours()).padStart(2, '0') + ':' +
                    String(now.getMinutes()).padStart(2, '0');
            });
        </script>

        {{-- Travel Type Distribution --}}
        <script>
            let chart;

            const fullChartData = @json($lineChartData);

            function renderChart(data) {
                const ctx = document.getElementById('lineChart').getContext('2d');

                if (chart) {
                    chart.destroy();
                }

                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        // 🟢 HOVER & INTERACTION
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        hover: {
                            mode: 'nearest',
                            intersect: false
                        },

                        plugins: {
                            legend: {
                                display: false // keep your custom legend
                            },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(0,0,0,0.85)',
                                padding: 10,
                                titleFont: {
                                    size: 13
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    label: function(ctx) {
                                        return `${ctx.dataset.label}: ${ctx.raw.toLocaleString()}`;
                                    }
                                }
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    callback: value => +value
                                }
                            }
                        },

                        animation: {
                            duration: 900,
                            easing: 'easeOutQuart'
                        }
                    }

                });
            }

            function buildDatasets(filter = 'all') {
                const colors = {
                    local: '#007bff',
                    outstation: '#28a745',
                    international: '#ffc107'
                };

                let datasets = [];

                if (filter === 'all' || filter === 'local') {
                    datasets.push({
                        label: 'Local',
                        data: fullChartData.local,
                        borderColor: colors.local,
                        backgroundColor: 'rgba(0,123,255,0.2)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    });
                }

                if (filter === 'all' || filter === 'outstation') {
                    datasets.push({
                        label: 'Outstation',
                        data: fullChartData.outstation,
                        borderColor: colors.outstation,
                        backgroundColor: 'rgba(40,167,69,0.2)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    });
                }

                if (filter === 'all' || filter === 'international') {
                    datasets.push({
                        label: 'International',
                        data: fullChartData.international,
                        borderColor: colors.international,
                        backgroundColor: 'rgba(255,193,7,0.2)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    });
                }

                return {
                    labels: fullChartData.labels,
                    datasets: datasets
                };
            }

            // initial render
            renderChart(buildDatasets());

            // filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const filter = this.dataset.filter;
                    renderChart(buildDatasets(filter));
                });
            });
        </script>

        {{-- // ===== DONUT CHART ===== --}}
        <script>
            const donutLabels = @json(array_column($donutChartData, 'label'));
            const donutData = @json(array_column($donutChartData, 'count'));
            const donutPercent = @json(array_column($donutChartData, 'percent'));

            const donutColors = [
                '#007bff',
                '#28a745',
                '#ffc107'
            ];

            new Chart(document.getElementById('donutChart'), {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutData,
                        backgroundColor: donutColors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '60%',
                    animation: {
                        animateRotate: true,
                        duration: 1200
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#000',
                            bodyColor: '#555',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    const percent = donutPercent[context.dataIndex];
                                    return `${context.label}: ${context.raw} (${percent}%)`;
                                }
                            }
                        }
                    }
                }
            });
        </script>

        <script>
            $(document).ready(function() {
                // Initialize DataTable
                datatable({
                    tableId: "travel-request-table-dynamic",
                    url: "{{ route('dashboard') }}",
                    dataLength: '[data-length]',
                    dataSearch: '[data-search]',
                    dataFilter: '[data-filter]',
                    dataExport: '[data-export]',
                    dataDateFilter: '[data-date-filter]',
                    dataShowEntries: '[data-show-entries]',
                    dataPagination: '[data-pagination]',
                    drawCallback: function(settings) {
                        $('.totalSum').text(settings.json.totalClaimAmount || 0);
                        $('[data-bs-toggle="popover"]').popover('dispose');
                        $('[data-bs-toggle="popover"]').popover({
                            trigger: 'hover'
                        });
                    }
                });

            });
        </script>

        <script>
            const deptLabels = @json($deptLabels);
            const deptTrips = @json($deptTrips);
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('tripChart');
                if (!ctx) {
                    console.error('Canvas #tripChart not found');
                    return;
                }

                if (!deptLabels.length || !deptTrips.length) {
                    console.warn('Chart data is empty', deptLabels, deptTrips);
                }

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [{
                            label: 'Number of Trips',
                            data: deptTrips,
                            backgroundColor: 'rgba(59,130,246,0.8)',
                            borderRadius: 8,
                            maxBarThickness: 30
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'Trips'
                                }
                            }
                        }
                    }
                });
            });
        </script>


        <script>
            // ── Animated Counter ────────────────────────────────────────
            function animateValue(el, start, end, duration, isCurrency = false) {
                let startTime = null;
                const step = (timestamp) => {
                    if (!startTime) startTime = timestamp;
                    const progress = Math.min((timestamp - startTime) / duration, 1);
                    const current = Math.floor(progress * (end - start) + start);

                    el.textContent = isCurrency ?
                        '₹' + current.toLocaleString('en-IN') :
                        current.toLocaleString('en-IN');

                    if (progress < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            }

            // ── Initialize Counters ─────────────────────────────────────
            document.addEventListener("DOMContentLoaded", () => {
                document.querySelectorAll('.stat-value').forEach(el => {
                    const val = Number(el.dataset.count);
                    const curr = el.hasAttribute('data-currency');
                    animateValue(el, 0, val, 1400, curr);
                });
            });

            // ── Bar Chart ───────────────────────────────────────────────
            let barChart = null;

            function createBarChart() {
                const ctx = document.getElementById('barChart')?.getContext('2d');
                if (!ctx) return;

                const departments = ['HR', 'Sales', 'IT', 'Finance', 'Operations', 'Marketing'];
                const values = departments.map(() => Math.floor(Math.random() * 70000) + 25000);

                if (barChart) barChart.destroy();

                barChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: departments,
                        datasets: [{
                            data: values,
                            backgroundColor: '#3b82f6',
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => '₹ ' + ctx.raw.toLocaleString('en-IN')
                                }
                            }
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: v => '₹' + v.toLocaleString('en-IN', {
                                        notation: 'compact'
                                    })
                                },
                                grid: {
                                    color: '#e5e7eb'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            function refreshBarChart() {
                createBarChart();
            }

            // ── Placeholder for other charts (add real data later) ──────
            document.addEventListener("DOMContentLoaded", () => {
                createBarChart();

                // Example line chart stub (replace with real data)
                new Chart(document.getElementById('lineChart'), {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                        datasets: [{
                            label: 'Spend',
                            data: [12000, 19000, 15000, 22000, 28000, 34000, 41000],
                            borderColor: '#3b82f6',
                            tension: 0.35,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Example donut chart stub
                new Chart(document.getElementById('donutChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Domestic', 'International'],
                        datasets: [{
                            data: [65, 35],
                            backgroundColor: ['#3b82f6', '#8b5cf6'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            });
        </script>

        {{-- claim wise remburse amount  --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // ─── LINE CHART ───────────────────────────────────────
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const months = Array.from({
                    length: 12
                }, (_, i) => i + 1);
                const labels = months.map(m => monthNames[m - 1]);

                const spendData = @json($monthWiseSpend ?? []);
                const reimburseData = @json($monthWiseReimburse ?? []);

                const spendValues = months.map(m => spendData[m] ?? 0);
                const reimburseValues = months.map(m => reimburseData[m] ?? 0);

                const ctxLine = document.getElementById('tripCharttwo').getContext('2d');

                new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Monthly Spend',
                                data: spendValues,
                                backgroundColor: 'rgb(255, 159, 64, 0.8)',
                            },
                            {
                                label: 'Monthly Reimburse',
                                data: reimburseValues,
                                backgroundColor: 'rgb(75, 192, 192)',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.85)',
                                titleFont: {
                                    size: 13
                                },
                                bodyFont: {
                                    size: 13
                                },
                                padding: 10,
                                callbacks: {
                                    label: ctx => `${ctx.dataset.label}: ₹ ${ctx.raw.toLocaleString('en-IN')}`
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 15
                                }
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => '₹ ' + v.toLocaleString('en-IN')
                                }
                            }
                        }
                    }
                });

                // ─── BAR CHART ────────────────────────────────────────
                const ctxBar = document.getElementById('tadaChart').getContext('2d');

                new Chart(ctxBar, {
                    type: 'bar',


                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Monthly Spend',
                                data: spendValues,
                                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                                borderColor: 'rgb(54, 162, 235)',
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 7,
                                fill: true,
                                borderRadius: 8,
                                maxBarThickness: 25
                            },
                            {
                                label: 'Monthly Reimburse',
                                data: reimburseValues,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgb(75, 192, 192)',
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 7,
                                fill: true,
                                borderRadius: 8,
                                maxBarThickness: 25
                            }
                        ]
                    },


                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 15
                                }
                            },
                            title: {
                                display: true,
                                text: 'Month-wise Claimed vs Reimbursed',
                                font: {
                                    size: 16
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => '₹ ' + v.toLocaleString('en-IN')
                                }
                            },
                            x: {
                                stacked: false
                            }
                        }
                    }
                });

                // ─── BUTTONS AND DIVS ───────────────────────────────
                const monthlyBtn = document.getElementById('monthly');
                const claimWiseBtn = document.getElementById('claimWise');

                const lineDiv = document.getElementById('tripTwoDiv'); // Monthly
                const barDiv = document.getElementById('tadaDiv'); // Claim Wise
                const chartTitleText = document.getElementById('chartTitleText');

                function switchChart(type) {
                    if (type === 'monthly') {
                        lineDiv.classList.remove('d-none');
                        barDiv.classList.add('d-none');
                        monthlyBtn.classList.add('active');
                        claimWiseBtn.classList.remove('active');
                        chartTitleText.textContent = 'Monthly Spend vs Reimburse';
                    } else if (type === 'claimWise') {
                        lineDiv.classList.add('d-none');
                        barDiv.classList.remove('d-none');
                        monthlyBtn.classList.remove('active');
                        claimWiseBtn.classList.add('active');
                        chartTitleText.textContent = 'Claimed vs Reimbursed Amount';
                    }
                }

                // Initial state
                switchChart('monthly');

                // Button events
                monthlyBtn.addEventListener('click', () => switchChart('monthly'));
                claimWiseBtn.addEventListener('click', () => switchChart('claimWise'));
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const monthlyBtn = document.getElementById('monthly');
                const claimWiseBtn = document.getElementById('claimWise');

                const lineDiv = document.getElementById('tripTwoDiv'); // Monthly chart
                const barDiv = document.getElementById('tadaDiv'); // Claim Wise chart
                const chartTitleText = document.getElementById('chartTitleText');

                // Stat card elements
                const totalReimburse = document.getElementById('totalReimburse'); // Monthly value
                const monthReimbursed = document.getElementById('monthReimbursed'); // Claim-wise value

                function switchChart(type) {
                    if (type === 'monthly') {
                        // Charts
                        lineDiv.classList.remove('d-none');
                        barDiv.classList.add('d-none');
                        chartTitleText.textContent = 'Monthly Spend vs Reimburse';

                        // Buttons
                        monthlyBtn.classList.add('active');
                        claimWiseBtn.classList.remove('active');

                        // Stat card toggle
                        totalReimburse.classList.remove('d-none');
                        monthReimbursed.classList.add('d-none');

                    } else if (type === 'claimWise') {
                        // Charts
                        lineDiv.classList.add('d-none');
                        barDiv.classList.remove('d-none');
                        chartTitleText.textContent = 'Claimed vs Reimbursed Amount';

                        // Buttons
                        monthlyBtn.classList.remove('active');
                        claimWiseBtn.classList.add('active');

                        // Stat card toggle
                        totalReimburse.classList.add('d-none');
                        monthReimbursed.classList.remove('d-none');
                    }
                }

                // Initial state
                switchChart('monthly');

                // Button events
                monthlyBtn.addEventListener('click', () => switchChart('monthly'));
                claimWiseBtn.addEventListener('click', () => switchChart('claimWise'));
            });
        </script>


        {{-- netpay by travel type and vehicle  --}}
       <script>
            document.addEventListener('DOMContentLoaded', function() {

                const canvas = document.getElementById('travelChart');
                if (!canvas) {
                    console.error('Canvas #travelChart not found');
                    return;
                }

                const ctx = canvas.getContext('2d');

                const travelTypes = @json($travelTypes ?? []);
                const datasets = @json($datasets ?? []);

                if (!travelTypes.length || !datasets.length) {
                    console.warn('Chart data is empty');
                }

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: travelTypes,
                        datasets: datasets.map(dataset => ({
                            ...dataset,
                            borderRadius: 8,
                            BarThickness: 60
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        boxWidth: 12,
                                        boxHeight: 12,
                                        padding: 15,
                                        generateLabels: function(chart) {
                                            const datasets = chart.data.datasets;
                                
                                            return datasets
                                                .filter((ds, i) => i % 2 === 0) // sirf first dataset (Claimed) lo
                                                .map((ds, i) => ({
                                                    text: ds.label.replace(' (Claimed)', ''),
                                                    fillStyle: ds.backgroundColor,
                                                    strokeStyle: ds.backgroundColor,
                                                    lineWidth: 2,
                                                    hidden: !chart.isDatasetVisible(i * 2),
                                                    datasetIndex: i * 2
                                                }));
                                        }
                                    }
                                }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            }
                        }
                    }
                });

            });
        </script>

        {{-- net pay vs gread pay by gred  --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const canvas = document.getElementById('netpayvsclaimedamt');

                if (!canvas) {
                    console.error('Canvas #netpayvsclaimedamt not found');
                    return;
                }

                const grades = @json($grades);
                const claimedAmounts = @json($claimedAmounts);
                const payableAmounts = @json($payableAmounts);

                if (!grades.length || !claimedAmounts.length || !payableAmounts.length) {
                    console.warn('Chart data is empty', grades, claimedAmounts, payableAmounts);
                }

                const ctx = canvas.getContext('2d');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: grades,
                        datasets: [{
                                label: 'Claimed Amount (₹)',
                                data: claimedAmounts,
                                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                                borderRadius: 8,
                                maxBarThickness: 25
                            },
                            {
                                label: 'Payable Amount (₹)',
                                data: payableAmounts,
                                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                                borderRadius: 8,
                                maxBarThickness: 25
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {


                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 15
                                }
                            },
                            title: {
                                display: true,
                                text: 'Grade Wise TADA Summary'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'Amount (₹)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Grades'
                                }
                            }
                        }
                    }
                });

            });
        </script>

        {{-- top 10 employee netpay --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const canvas = document.getElementById('topemployees');

                if (!canvas) {
                    console.error('Canvas #topemployees not found');
                    return;
                }

                const employees = @json($topemployee);
                const empAmounts = @json($emp_amounts);

                if (!employees.length || !empAmounts.length) {
                    console.warn('Chart data is empty', employees, empAmounts);
                }

                const ctx = canvas.getContext('2d');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: employees,
                        datasets: [{
                            label: 'Net Payable Amount (₹)',
                            data: empAmounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderRadius: 8,
                            maxBarThickness: 30
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true
                            },
                            title: {
                                display: true,
                                text: 'Top 10 Employees by Net Payable'
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'Amount (₹)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Employees'
                                }
                            }
                        }
                    }
                });

            });
        </script>
    @endsection
