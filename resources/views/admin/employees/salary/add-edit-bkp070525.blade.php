@extends('admin.layout.master')
@section('title')
{{ $title }}
@endsection
@section('css')
<?php

use Carbon\Carbon;

$dateString = $employee->emp_date_of_joining;
$date = Carbon::parse($dateString);
$formattedDate = $date->format('d-M-Y');
?>
<style>
    @import url(https://fonts.googleapis.com/css?family=Open+Sans:600,400,300,300italic);

    .frame {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 400px;
        height: 400px;
        margin-top: -200px;
        margin-left: -200px;
        border-radius: 2px;
        box-shadow: 1px 2px 10px 0 rgba(0, 0, 0, 0.3);
        background: #4CB6DE;
        color: #fff;
        font-family: 'Open Sans', Helvetica, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .quote {
        position: relative;
        margin-top: 90px;
        padding: 0 30px;
    }

    .quote::before {
        content: '„';
        position: absolute;
        top: -100px;
        left: 7px;
        font-family: Arial;
        font-size: 250px;
        color: #6AC2E3;
        line-height: 35px;
    }

    .quote p {
        position: relative;
        font-size: 24px;
        line-height: 35px;
        margin: 20px 0;
    }

    .quote .author {
        font-weight: 300;
        font-style: italic;
        font-size: 20px;
        line-height: 28px;
    }

    .tooltipo {
        position: relative;
        display: inline-block;
        background: #41cbff;
        padding: 3px 7px 3px 6px;
        margin: -10px 0;
        cursor: pointer;
    }

    .tooltipo:hover .info,
    .tooltipo:focus .info {
        visibility: visible;
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    .info {
        position: absolute;
        bottom: 30px;
        left: -145px;
        background: #000;
        width: 300px;
        font-size: 16px;
        line-height: 24px;
        visibility: hidden;
        opacity: 0;
        transform: translate3d(0, -20px, 0);
        transition: all 0.5s ease-out;
    }

    .info::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 14px;
        bottom: -14px;
        left: 0;
    }

    .info::after {
        content: '';
        position: absolute;
        width: 10px;
        height: 10px;
        transform: rotate(45deg);
        bottom: -5px;
        left: 50%;
        margin-left: -5px;
        background: #286F8A;
    }

    .pronounce {
        display: block;
        background: #fff;
        color: #286F8A;
        padding: 8px 17px 10px 17px;
        line-height: 16px;
    }

    .pronounce .fa {
        margin-left: 10px;
        cursor: pointer;
        transition: all 0.2s ease-out;
    }

    .pronounce .fa:hover {
        transform: scale(1.15);
    }

    .text {
        display: block;
        padding: 13px 17px;
    }
</style>

<style>
.fa-info-circle {
    position: relative;
    cursor: pointer;
}

.fa-info-circle::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 120%;
    top: 50%;
    transform: translateY(-50%);
    background-color: black;
    color: white;
    padding: 4px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    white-space: nowrap;
    font-size: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease-in-out;
    z-index: 1000;
}

.fa-info-circle:hover::after {
    opacity: 1;
    visibility: visible;
}
</style>

<style>
    .timeline {
        position: relative;
        width: 2px;
        background: black;
        height: 50px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
    }
    .arrow {
        width: 0;
        height: 0;
        border-left: 9px solid transparent;
        border-right: 9px solid transparent;
        border-bottom: 9px solid blue;
    }
    .employer-cell, .employee-cell {
        background-color: #f0f8ff; /* Light Blue */
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }

    .vertical-text {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        font-size: 16px;
        color: #007bff; /* Blue */
    }

</style>
@endsection

@section('content')
<!-- PAGE HEADER -->
<div class="page-header d-xl-flex d-block">
    <div class="page-leftheader">
        <div class="page-title">Add Payroll</div>
    </div>
</div>
<!-- END PAGE HEADER -->

<!-- ROW -->
<form action="{{ route('save.employee.payroll') }}" method="post" id="smform">
    @csrf

    <div class="row">
        <!-- Salary Information -->
        <div class="card mb-4">
            <div class="card-body">
                <!--<h4><center type="button" data-toggle="collapse" data-target="#salaryInfoCardBody" aria-expanded="false" aria-controls="salaryInfoCardBody">-->
                <!--    Employee Name : {{ $employee->emp_full_name }}-->
                <!--    <button class="btn btn-link float-center" type="button" data-toggle="collapse" data-target="#salaryInfoCardBody" aria-expanded="false" aria-controls="salaryInfoCardBody">-->
                <!--                <i class="fa fa-chevron-down" style="line-height: 0.5;"></i>-->
                <!--            </button>-->
                <!--    </center></h4>-->
                <h4 style="text-align: center; font-weight: bold !important; margin-top: 20px;">
                    <span>Employee Name : {{ $employee->emp_full_name }}</span>
                </h4>
                <h4 style="text-align: center;margin-top: 6px;margin-right: 66px;">
                    <span>Employee Code    : {{ $employee->emp_code }}</span>
                </h4>
                <h4 class="card-title">
                    Salary Information
                </h4>
                <input type="hidden" name="emp_id" value="{{ $emp_actual_id }}">
                <input type="hidden" name="business_id" value="{{ $business_id }}">
                <div id="salaryInfoCardBody" class="card-body collapse show">
                    <div class="row">
                        <div class="row align-items-center">
                            <!-- Monthly CTC -->
                            <div class="col-md-1">
                                <label class="form-label mb-0 mt-2">
                                    <i class="fa fa-money-bill"></i> Monthly CTC <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control numericInput" name="es_monthly_ctc" id="es_monthly_ctc"
                                    placeholder="0" maxlength="10" value="{{ old('es_monthly_ctc', $emp_salary->es_monthly_ctc ?? '') }}" oninput="calculateCTCAnnual('monthly')"required>
                            </div>
                            <!-- Annual CTC -->
                            <div class="col-md-1">
                                <label class="form-label mb-0 mt-2">
                                    <i class="fa fa-calendar-alt"></i> Annual CTC <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control numericInput" name="es_annual_ctc" id="es_annual_ctc" placeholder="0" maxlength="10" value="{{ $emp_salary->es_annual_ctc ?? '' }}" oninput="calculateCTCAnnual('annual')" required>
                            </div>
                            <!-- <div class="col-md-3"></div>
                            <div class="col-md-3"></div>
                            <div class="col-md-12"></div> <br>
                            <div class="col-md-1">
                                <label class="form-label mb-0 mt-2">
                                    <i class="fa fa-calendar-alt"></i> W.E.F <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="with_effect_from" id="with_effect_from"value="" required>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label mb-0 mt-2">
                                    <i class="fa fa-calendar-alt"></i> Revision from <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="revision-from" id="revision-from" placeholder="" value="" required>
                            </div> -->
                            <!-- Financial Year -->
                            <div class="col-md-1">
                                <label class="form-label mb-0 mt-2">
                                    <i class="fa fa-calendar-alt"></i> Financial Year <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="financial_year" id="financial_year" required>
                                    <option value="">Select Financial Year</option>
                                    @foreach($financialYears as $year)
                                        <option value="{{ $year->fy_id }}" {{ !empty($smhistoryLastData) && $smhistoryLastData->sm_fy_id == $year->fy_id ? 'selected' : '' }}>{{ $year->fy_year }} </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Revision Remark -->
                            <div class="col-md-1">
                                <label class="form-label mb-0 mt-2">
                                    <i class="fa fa-calendar-alt"></i> Remark <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="remark" id="remark" placeholder="Remark" value="{{ $smhistoryLastData->sm_remark ?? '' }}" pattern="^[A-Za-z][A-Za-z0-9\s]*$" title="Only letters and numbers allowed. Must start with a letter." required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings and Deductions Side by Side -->
        <div class="row">
            <!-- Earnings Section -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>
                            Earnings
                            <!-- Add a button to toggle the collapse -->
                            <button class="btn btn-link float-right testq" type="button" data-toggle="collapse" data-target="#earningsCardBody" aria-expanded="false" aria-controls="earningsCardBody">
                                <i class="fa fa-chevron-down" style="line-height: 0.5;"></i>
                            </button>
                        </h5>
                        <h5 id="earningsHeader" class="float-right colShowData">
                            <label>Total Earnings : <span id="total_earnings_head"></span> </label>
                        </h5>
                    </div>
                    <div id="earningsCardBody" class="card-body collapse show">
                        <div class="row">
                            @foreach ($earnings as $earning)
                            @if($earning->sa_is_active)
                            <div class="col-md-6 mb-3">
                                <label>
                                    <i class="fa fa-dollar-sign"></i> {{ $earning->sa_title }}
                                    <span class="text-danger">*</span>
                                    <i class="fa fa-info-circle text-primary ms-1 earning-title"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                    </i>
                                </label>
                                    <input type="text" class="form-control calculated-value numericInput special-364"
                                            name="earnings[{{ $earning->sa_id }}]"
                                            value=""
                                            data-sa_id="{{ $earning->sa_id }}"
                                            data-calculation-type="{{ $earning->sa_calculation_type }}"
                                            data-threshold="{{ $earning->sa_threshold_value }}"
                                            data-percentage="{{ $earning->sa_percentage ?? 0 }}"
                                            data-fixed="{{ $earning->sa_fixed_value ?? 0 }}"
                                            data-earning-type-id="{{ $earning->sa_earning_type_id }}"
                                            data-payroll-heading-id="{{ $earning->sa_payroll_heading_id }}"
                                            data-earning-cal-type-id="{{ $earning->sa_calculation_type }}"
                                            id="earning_{{ $earning->sa_id }}" maxlength="9" placeholder="0" required>


                            </div>
                            @endif
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <!-- Other Allowance Row -->
                            <div class="col-md-6">
                                <label><strong>Other Allowance</strong></label>
                                <input type="text" class="form-control numericInput" name="es_rem_allowance" id="other_allowance" placeholder="Other Allowance" value="{{ !empty($smhistoryLastData->sm_other_allow) ? $smhistoryLastData->sm_other_allow : '' }}" maxlength="10" required>
                                <small id="oAllowError" class="text-danger" style="display: none;"></small>
                            </div>
                            <!-- Total Earnings Row -->
                            <div class="col-md-6">
                                <label><strong>Total Earnings</strong></label>
                                <input type="text" class="form-control numericInput" name="total_employee_earning" id="total_earnings" value="{{ !empty($smhistoryLastData->sm_total_earning) ? $smhistoryLastData->sm_total_earning : '' }}" maxlength="10" placeholder="Total Earnings" required>
                            </div>
                        </div><br>
                        <div class="row" >
                            <div class="col-md-6 mb-3">
                                <label>Gross Pay <span class="text-danger">*</span></label>
                                <input type="text" class="form-control numericInput" name="monthly_gross" id="monthly_gross" placeholder="0" value="{{ !empty($smhistoryLastData->sm_gross_pay) ? $smhistoryLastData->sm_gross_pay : '' }}" maxlength="10" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Take Home Salary(Net Pay) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control numericInput" name="monthly_net_salary" id="monthly_net_salary" placeholder="0" value="{{ !empty($smhistoryLastData->sm_net_pay) ? $smhistoryLastData->sm_net_pay : '' }}" maxlength="10" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions Section -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>
                            Deductions(Employee)
                            <button class="btn btn-link float-right testq2" type="button" data-toggle="collapse" data-target="#deductionsCardBody" aria-expanded="false" aria-controls="deductionsCardBody">
                                <i class="fa fa-chevron-down" style="line-height: 0.5;"></i>
                            </button>
                        </h5>
                        <h5 id="dedEmpHeader" class="float-right colShowData">
                            <label class="float-right"><span id="total_emp_ded"></span> </label>
                        </h5>
                    </div>
                    <div id="deductionsCardBody" class="card-body collapse show">
                        <div class="row">
                            @foreach ($deductions as $deduction)
                                @if($deduction->std_deduction_type_id == 351 && $employee->emp_is_pf_enabled == 121)
                                    @continue
                                @endif
                                @if($deduction->std_deduction_type_id == 352 && $employee->emp_esic_limit == 121)
                                    @continue
                                @endif
                                <div class="col-md-12 mb-3">
                                    <label>{{ $deduction->deduction_type_name }}
                                        <i class="fa fa-info-circle text-primary ms-1"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="">
                                        </i>
                                    </label>
                                    <input type="text" class="form-control deduction-value numericInput"
                                        name="deductions[{{ $deduction->std_id }}]"
                                        value="{{ $emp_deductions[$deduction->std_deduction_type_id] ?? '' }}"
                                        data-threshold="{{ $deduction->std_threshold }}"
                                        data-employee-rate="{{ $deduction->std_employee_contri_rate_amount }}"
                                        data-employer-rate="{{ $deduction->std_employer_contri_rate_amount }}"
                                        data-deduction-cycle="{{ $deduction->std_deduction_cycle_id }}"
                                        data-deduction-type-id="{{ $deduction->std_deduction_type_id }}"
                                        data-sa-consider-for-pf="{{ $deduction->salaryAllowance->sa_consider_for_pf ?? 0 }}"
                                        maxlength="9" placeholder="Deduction Value" readonly>
                                </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label><strong>Total Deductions</strong></label>
                                <input type="text" class="form-control numericInput" name="total_employee_deduction"
                                    id="total_employee_deduction" placeholder="0" maxlength="10" value="{{ !empty($smhistoryLastData->sm_employee_total_ded) ? $smhistoryLastData->sm_employee_total_ded : '' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>
                            Deductions(Employer)
                            <button class="btn btn-link float-right testq3" type="button" data-toggle="collapse" data-target="#deductionsCardBodyE" aria-expanded="false" aria-controls="deductionsCardBodyE">
                                <i class="fa fa-chevron-down" style="line-height: 0.5;"></i>
                            </button>
                        </h5>
                        <h5 id="dedEmployerHeader" class="float-right colShowData">
                            <label class="float-right"><span id="total_employer_deds"></span> </label>
                        </h5>
                    </div>
                    <div id="deductionsCardBodyE" class="card-body collapse show">
                        <div class="row">
                            @foreach ($employerDeductions as $deduction)
                                @if($deduction->std_deduction_type_id == 351 && $employee->emp_is_pf_enabled == 121)
                                    @continue
                                @endif
                                @if($deduction->std_deduction_type_id == 352 && $employee->emp_esic_limit == 121)
                                    @continue
                                @endif
                                <div class="col-md-12 mb-3">
                                    <label>{{ $deduction->deduction_type_name }}
                                        <i class="fa fa-info-circle text-primary ms-1"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="">
                                        </i>
                                    </label>
                                    <input type="text" class="form-control employer-deduction-value numericInput"
                                        name="employerDeductions[{{ $deduction->std_id }}]"
                                        value="{{ $emplyer_deductions[$deduction->std_deduction_type_id] ?? '' }}"
                                        data-threshold="{{ $deduction->std_threshold }}"
                                        data-employee-rate="{{ $deduction->std_employee_contri_rate_amount }}"
                                        data-employer-rate="{{ $deduction->std_employer_contri_rate_amount }}"
                                        data-deduction-cycle="{{ $deduction->std_deduction_cycle_id }}"
                                        data-deduction-type-id="{{ $deduction->std_deduction_type_id }}"
                                        data-sa-consider-for-pf="{{ $deduction->salaryAllowance->sa_consider_for_pf ?? 0 }}"
                                        maxlength="9" placeholder="Deduction Value" readonly>

                                </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label><strong>Total Deductions</strong></label>
                                <input type="text" class="form-control numericInput" name="total_employer_deduction"
                                    id="total_employer_deduction" value="{{ !empty($smhistoryLastData->sm_employer_total_ded) ? $smhistoryLastData->sm_employer_total_ded : '' }}" maxlength="10" placeholder="0" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Buttons -->
        <div class="text-end" style="padding-bottom: 10px; padding-right: 175px;">
            <center><button type="button" class="btn btn-outline-danger btn-lg" onclick="resetForm()">Clear</button></center>
        </div>
        <!-- Summary Section -->
        @if(!empty($smhistory->first()->sm_monthly_ctc))
        <div class="card mb-4">
            <div class="card-header">
                <h5>
                    Summary
                    <!-- Add a button to toggle the collapse -->
                    <button class="btn btn-link float-right" type="button" data-toggle="collapse" data-target="#summaryCardBody" aria-expanded="false" aria-controls="summaryCardBody">
                        <i class="fa fa-chevron-down" style="line-height: 0.5;"></i>
                    </button>
                </h5>
            </div> <br>
            <div>
                @php
                    $formattedWef = '';
                    if(!empty($smhistoryLastData)) {
                        $wef = $smhistoryLastData->created_at;
                        $wef1 = Carbon::parse($wef);
                        $formattedWef = $wef1->format('d-M-Y');
                    }
                @endphp
                <h5 style="display: flex; gap: 40px; font-weight: bold; white-space: nowrap;">
                    <span>Employee Name : {{ $employee->emp_full_name }}</span> |
                    <span>DoJ : {{ $formattedDate }}</span> |
                    <span>w.e.f : {{ $formattedWef }}</span> |
                    <span>Revision remark : {{ $smhistoryLastData->sm_remark }}</span>
                </h5>
            </div>
            <div id="summaryCardBody" class="card-body collapse show">
                <div class="row d-flex align-items-center">
                    <table style="width:100%">
                        <tr>
                            <th></th>
                            <th>Financial Year</th>
                            <th>Effective From</th>
                            <th>Effective To</th>
                            <th>Monthly CTC</th>
                            <th>Gross CTC</th>
                            <th>View</th>
                            <th>Download</th>
                        </tr>

                        @foreach($smhistory as $smhist)
                            @php
                                $dateString1 = $smhist->created_at;
                                $date1 = Carbon::parse($dateString1);
                                $formattedDate1 = $date1->format('d-M-Y');
                                $date2 = Carbon::parse($smhist->financial_years->fy_end_date);
                                $formattedDate2 = $date2->format('d-M-Y');
                            @endphp
                            <tr>

                                <td>
                                    <div class="track-arrow">
                                        <div class="timeline">
                                            <div class="arrow"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $smhist->financial_years->fy_year }}</td>
                                <td>{{ $formattedDate1 }}</td>
                                <td>{{ $formattedDate2 }}</td>
                                <td>{{ $smhist->sm_monthly_ctc }}</td>
                                <td>{{ $smhist->sm_gross_pay }}</td>
                                <td>
                                    <span class="btn-primary openBtn" style="padding: 5px;"
                                        data-from_date="{{ $formattedDate1 }}"
                                        data-to_date="{{ $formattedDate2 }}"
                                        data-monthly="{{ $smhist->sm_monthly_ctc }}"
                                        data-annual="{{ $smhist->sm_annual_ctc }}"
                                        data-basic="{{ $smhist->sm_basic }}"
                                        data-hra="{{ $smhist->sm_hra }}"
                                        data-dear_allow="{{ $smhist->sm_dear_allow }}"
                                        data-conv_allow="{{ $smhist->sm_conv_allow }}"
                                        data-other_allow="{{ $smhist->sm_other_allow }}"
                                        data-employee_epf="{{ $smhist->sm_employee_epf }}"
                                        data-employee_esic="{{ $smhist->sm_employee_esic }}"
                                        data-employee_lwf="{{ $smhist->sm_employee_lwf }}"
                                        data-employee_total_ded="{{ $smhist->sm_employee_total_ded }}"
                                        data-employer_epf="{{ $smhist->sm_employer_epf }}"
                                        data-employer_esic="{{ $smhist->sm_employer_esic }}"
                                        data-employer_lwf="{{ $smhist->sm_employer_lwf }}"
                                        data-employer_total_ded="{{ $smhist->sm_employer_total_ded }}"
                                        data-total_earning="{{ $smhist->sm_total_earning }}"
                                        data-gross_pay="{{ $smhist->sm_gross_pay }}"
                                        data-net_pay="{{ $smhist->sm_net_pay }}"
                                  >
                                    <i class="feather feather-eye"></i>
                                  </span>
                                </td>
                                <td>
                                    <div class="btn-group ms-2">
                                        <!-- <a class="btn btn-outline-primary">
                                            <i class="fa fa-download"></i>
                                        </a> -->
                                        <a href="{{ route('export.smhistory', ['id' => $smhist->sm_id]) }}" class="btn btn-outline-primary">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Buttons -->
        <div class="text-end">
            <button id="saveButton" type="submit" class="btn btn-outline-primary btn-lg cus-save">Save</button>
            <!-- <button class="btn btn-outline-primary btn-lg">Save</button> -->
            <button type="button" onclick="window.history.back()" class="btn btn-outline-danger  btn-lg">Cancel</button>
        </div>

        <!-- <div class="modal fade" id="employeeSalaryHistoryModal" tabindex="-1" role="dialog" aria-labelledby="employeeSalaryHistoryModalLabel" aria-hidden="true"> -->
        <div class="modal fade" id="employeeSalaryHistoryModal" tabindex="-1" role="dialog" aria-labelledby="employeeSalaryHistoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header border-0">
                        <h4 class="modal-title" id="modalTitle">Preview</h4>
                        <button type="button" aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="card user-pro-list overflow-hidden mt-3" id="avtarDiv">
                        <div class="card-body py-5">
                            <h4 style="color:white !important; text-align:center;">From <span id="from_date"></span> To <span id="to_date"> </h4>
                            <div class="row user-pic text-left">
                                <div class="col-1 pt-7">
                                    <span class="avatar avatar-xxl brround" id="empAvtar"
                                        style="background-image: url("{{ asset('assets/imgs/user.png') }}");"></span>
                                </div>
                                <div class="col-11 text-left">
                                    <h1 class="px-4 pt-6 mt-6 mb-0"> {{ $employee->emp_full_name }} </h1>
                                    <span class="font-weight-semibold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $employee->fh_designation->dg_name }}</span><br>
                                    <span class="font-weight-semibold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $employee->emp_email }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody class="text-center">
                                        <tr>
                                            <td rowspan="1" class="text-blue employer-cell">
                                                <h4 class="m-0 vertical-text"></h4>
                                            </td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Monthly CTC</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="monthlyCtc"></td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Annual CTC</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="annualCtc"></td>
                                        </tr>
                                        <!-- Horizontal Line -->
                                        <tr style="border-top: 1px solid #ddd;">
                                            <td colspan="6"></td>
                                        </tr>
                                        <tr>
                                            <td rowspan="3" class="text-blue employer-cell">
                                                <h4 class="m-0 vertical-text">Components</h4>
                                            </td>
                                            <td class="py-2 px-0"><span class="w-50">Basic Salary</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="basicSalary"></td>
                                            <td class="py-2 px-0"><span class="w-50">HRA</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empHRA"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="w-50">Dearness Allowance</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empDearAllow"></td>
                                            <td class="py-2 px-0"><span class="w-50">Conveyance Allowance</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empConvAllow"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="w-50">Other Allowance</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empOtherAllow"></td>
                                            <td class="py-2 px-0"><span class="w-50"></span></td>
                                            <td class="py-2 px-0"></td>
                                            <td class="py-2 px-0" id=""></td>
                                        </tr>

                                        <!-- Horizontal Line -->
                                        <tr style="border-top: 1px solid #ddd;">
                                            <td colspan="6"></td>
                                        </tr>
                                        <tr>
                                            <td rowspan="2" class="text-blue employer-cell">
                                                <h4 class="m-0 vertical-text">Employee</h4>
                                            </td>
                                            <td class="py-2 px-0"><span class="w-50">EPFO</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empEpf"></td>
                                            <td class="py-2 px-0"><span class="w-50">ESIC</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empEsic"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="w-50">LWF</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empLwf"></td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total Deductions</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold" id="empDeductions"></td>
                                        </tr>

                                        <!-- Horizontal Line -->
                                        <tr style="border-top: 1px solid #ddd;">
                                            <td colspan="6"></td>
                                        </tr>
                                        <tr>
                                            <td rowspan="2" class="text-blue employer-cell">
                                                <h4 class="m-0 vertical-text">Employer</h4>
                                            </td>
                                            <td class="py-2 px-0"><span class="w-50">EPFO</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="employerEpf"></td>
                                            <td class="py-2 px-0"><span class="w-50">ESIC</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="employerEsic"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-0"><span class="w-50">LWF</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="employerLwf"></td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total Deductions</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold" id="employerTDeductions"></td>
                                        </tr>

                                        <!-- Horizontal Line -->
                                        <tr style="border-top: 1px solid #ddd;">
                                            <td colspan="6"></td>
                                        </tr>

                                        <tr>
                                            <td rowspan="2" class="text-blue employer-cell">
                                                <h4 class="m-0 vertical-text"></h4>
                                            </td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total Earning</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold" id="empEarnings"></td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Gross Pay</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold" id="empGrossPay"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Net Pay</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold" id="empNetPay"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<!-- END ROW -->
@endsection
{{-- <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"> --}}

<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>


<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
@section('script')

<script>
    function resetForm() {
        document.getElementById("smform").reset();
        document.getElementById("other_allowance").value = "0";
        document.getElementById("total_earnings").value = "0";
        document.getElementById("monthly_gross").value = "0";
        document.getElementById("monthly_net_salary").value = "0";
        document.getElementById("total_employee_deduction").value = "0";
        document.getElementById("total_employer_deduction").value = "0";
        document.getElementById("es_monthly_ctc").value = "0";
        document.getElementById("es_annual_ctc").value = "0";
        document.getElementById("financial_year").value = "0";
        document.getElementById("remark").value = "";
        document.querySelectorAll(".deduction-value").forEach(input => {
            input.value = "0";
        });
        document.querySelectorAll(".employer-deduction-value").forEach(input => {
            input.value = "0";
        });
    }
</script>

<script>
    // Show data in popup modal
    $(document).on('click', '.openBtn', function() {
        $('#employeeSalaryHistoryModal').modal('show');
        var fromDate = $(this).data('from_date');
        var toDate = $(this).data('to_date');
        var empMonthly = $(this).data('monthly');
        var empAnnual = $(this).data('annual');
        var empBasic = $(this).data('basic');
        var empHra = $(this).data('hra');
        var empDearAllow = $(this).data('dear_allow');
        var empConvAllow = $(this).data('conv_allow');
        var empOtherAllow = $(this).data('other_allow');
        var empEpf = $(this).data('employee_epf');
        var empEsic = $(this).data('employee_esic');
        var empLwf = $(this).data('employee_lwf');
        var empTotalDeductions = $(this).data('employee_total_ded');
        var employerEpf = $(this).data('employer_epf');
        var employerEsic = $(this).data('employer_esic');
        var employerLwf = $(this).data('employer_lwf');
        var employerTotalDeduction = $(this).data('employer_total_ded');
        var empTotalEarning = $(this).data('total_earning');
        var empGrossPay = $(this).data('gross_pay');
        var empNetPay = $(this).data('net_pay');

        $('#from_date').text(fromDate);
        $('#to_date').text(toDate);
        $('#monthlyCtc').text(empMonthly);
        $('#annualCtc').text(empAnnual);
        $('#basicSalary').text(empBasic);
        $('#empHRA').text(empHra);
        $('#empDearAllow').text(empDearAllow);
        $('#empConvAllow').text(empConvAllow);
        $('#empOtherAllow').text(empOtherAllow);
        $('#empEpf').text(empEpf);
        $('#empEsic').text(empEsic);
        $('#empLwf').text(empLwf);
        $('#empDeductions').text(empTotalDeductions);
        $('#employerEpf').text(employerEpf);
        $('#employerEsic').text(employerEsic);
        $('#employerLwf').text(employerLwf);
        $('#employerTDeductions').text(employerTotalDeduction);
        $('#empEarnings').text(empTotalEarning);
        $('#empGrossPay').text(empGrossPay);
        $('#empNetPay').text(empNetPay);
    });
    // Save button validation
    document.getElementById('saveButton').addEventListener('click', function(event) {
        let form = this.closest('form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        this.disabled = true;
        this.innerText = 'Saving...';
        form.submit();
    });

    function allowDecimalInput(event) {
        let input = event.target;
        input.value = input.value.replace(/[^0-9.]/g, ''); // Allow only numbers and dot
        if ((input.value.match(/\./g) || []).length > 1) {
            input.value = input.value.replace(/\.+$/, ''); // Remove extra dots
        }
    }

    // --- CTC Calculation and Earnings ---
    function calculateCTCAnnual(type) {
        let monthlyInput = document.getElementById("es_monthly_ctc");
        let annualInput = document.getElementById("es_annual_ctc");

        if (type === 'monthly') {
            let monthlyCTC = parseFloat(monthlyInput.value.replace(/,/g, '')) || 0;
            annualInput.value = (monthlyCTC * 12).toFixed(2);
        } else if (type === 'annual') {
            let annualCTC = parseFloat(annualInput.value.replace(/,/g, '')) || 0;
            monthlyInput.value = (annualCTC / 12).toFixed(2);
        }
    }

    // Attach event listener to all elements with class "numericInput"
    document.querySelectorAll(".numericInput").forEach(input => {
        input.addEventListener("input", allowDecimalInput);
    });

    $(document).ready(function () {
        const toggleHeaderVisibility = function(headerId, collapseClass, toggleSelector) {
            $(toggleSelector).on('click', function() {
                $(headerId).toggle($('#' + collapseClass).hasClass('show'));
            });
        };
        $('#earningsHeader, #dedEmpHeader, #dedEmployerHeader').hide();
        // Toggle headers based on collapse state
        toggleHeaderVisibility('#earningsHeader', 'earningsCardBody', '.testq');
        toggleHeaderVisibility('#dedEmpHeader', 'deductionsCardBody', '.testq2');
        toggleHeaderVisibility('#dedEmployerHeader', 'deductionsCardBodyE', '.testq3');
    });

    function updateValues() {
        let totalEarnings = parseFloat(document.getElementById("total_earnings")?.value) || 0;
        let totalEmployeeDeduction = parseFloat(document.getElementById("total_employee_deduction")?.value) || 0;
        let totalEmployerDeduction = parseFloat(document.getElementById("total_employer_deduction")?.value) || 0;
        document.getElementById("total_earnings_head").innerHTML = totalEarnings.toFixed(2);
        document.getElementById("dedEmpHeader").innerHTML = 'Total : ' + totalEmployeeDeduction.toFixed(2);
        document.getElementById("dedEmployerHeader").innerHTML = 'Total : ' + totalEmployerDeduction.toFixed(2);
    }
    document.getElementById("total_earnings").addEventListener("input", updateValues);
    document.getElementById("total_employee_deduction").addEventListener("input", updateValues);
    document.getElementById("total_employer_deduction").addEventListener("input", updateValues);
    window.addEventListener('load', updateValues);
</script>

<script>

    // For Deductions info icon functionality
    function setInfoTooltipsDeductions() {
        const setTooltip = (input, text) => {
            const icon = input.closest('.mb-3')?.querySelector('.fa-info-circle');
            if (icon) icon.setAttribute('data-tooltip', text);
        };

        document.querySelectorAll('input.deduction-value').forEach(input => {
            const { deductionTypeId, employeeRate } = input.dataset;
            const tooltipMap = {
                '351': `${employeeRate}% of basic salary`,
                '352': `${employeeRate}% of gross ctc`
            };
            setTooltip(input, tooltipMap[deductionTypeId] || 'Earning Info');
        });

        document.querySelectorAll('input.employer-deduction-value').forEach(input => {
            const { deductionTypeId, employerRate } = input.dataset;
            const tooltipMap = {
                '351': `${employerRate}% of basic salary`,
                '352': `${employerRate}% of gross ctc`
            };
            setTooltip(input, tooltipMap[deductionTypeId] || 'Earning Info');
        });
    }

    // If component value already exists info icon functionality
    function setInfoTooltips() {
        const monCTC = parseFloat(document.getElementById('es_monthly_ctc').value) || 0;
        const allInputs = document.querySelectorAll('input[data-calculation-type]');

        const earningsMap = {};
        let basicVal = 0;

        allInputs.forEach(input => {
            const saId = input.dataset.sa_id;
            const value = parseFloat(input.value) || 0;
            earningsMap[saId] = value;

            if (input.dataset.earningTypeId === '360') {
                basicVal = value;
            }
        });

        allInputs.forEach(input => {
            const saId = input.dataset.sa_id;
            const calcType = input.dataset.calculationType;
            const value = earningsMap[saId] || 0;
            let tooltipText = '';

            switch (calcType) {
                case '346': // % of CTC
                    const percOfCTC = monCTC ? ((value * 100) / monCTC).toFixed(2) : 0;
                    tooltipText = `${percOfCTC}% of Monthly CTC`;
                    break;

                case '347': // % of Basic
                    const percOfBasic = basicVal ? ((value * 100) / basicVal).toFixed(2) : 0;
                    tooltipText = `${percOfBasic}% of Basic`;
                    break;

                case '348': // Flat value
                    tooltipText = `Flat amount ₹${value.toFixed(2)}`;
                    break;

                default:
                    tooltipText = 'Earning Info';
            }

            const icon = input.closest('.mb-3')?.querySelector('.fa-info-circle');
            if (icon) {
                icon.setAttribute('data-tooltip', tooltipText);
                // icon.setAttribute('title', tooltipText); // For Bootstrap tooltip
            }
        });
    }

    // If component value not exist info icon functionality
    function setInfoTooltipsAuto() {
        const setTooltip = (input, text) => {
            const icon = input.closest('.mb-3')?.querySelector('.fa-info-circle');
            if (icon) icon.setAttribute('data-tooltip', text);
        };

        document.querySelectorAll('input[data-calculation-type]').forEach(input => {
            const { calculationType, threshold } = input.dataset;
            const tooltipMap = {
                '348': `Flat amount ₹${threshold}`,
                '347': `${threshold}% of basic salary`,
                '346': `${threshold}% of monthly ctc`
            };
            setTooltip(input, tooltipMap[calculationType] || 'Earning Info');
        });
    }

    // Determine which function to call based on sa_id value
    function checkSaIdAndSetTooltips() {
        const firstFunctionSaId = 0; // The value for sa_id where first function should run
        const allInputs = document.querySelectorAll('input[data-calculation-type]');

        // Check if any input has sa_id with value 0, then call setInfoTooltips function
        let shouldRunFirstFunction = false;

        allInputs.forEach(input => {
            const saId = input.dataset.sa_id;
            if (parseInt(saId) === firstFunctionSaId) {
                shouldRunFirstFunction = true;
            }
        });

        // Run the appropriate function based on the sa_id value
        if (shouldRunFirstFunction) {
            setInfoTooltips();
        } else {
            setInfoTooltipsAuto();
            setInfoTooltipsDeductions();
        }
    }

    // Addition for employee and employer deductions
    // function calculateTotal(selector, targetId) {
    //     let total = 0;
    //     document.querySelectorAll(selector).forEach(input => {
    //         total += parseFloat(input.value) || 0;
    //     });
    //     document.getElementById(targetId).value = total.toFixed(2);
    // }

    // Total employee and employer deduction functionality code
    // function calculateEmpDeductions() {
    //     let getMonthlyCTC = parseFloat(document.getElementById("es_monthly_ctc")?.value) || 0;
    //     let getGrossSalary = parseFloat(document.getElementById("monthly_gross")?.value) || 0;
    //     let earningBasic = 0;

    //     // ✅ Get Basic Salary
    //     document.querySelectorAll(".calculated-value").forEach(input => {
    //         if (parseInt(input.dataset.earningTypeId) === 360) {
    //             earningBasic = parseFloat(input.value) || 0;
    //         }
    //     });

    //     // ✅ Calculate Employee Deductions
    //     document.querySelectorAll(".deduction-value").forEach(input => {
    //         let thresholdVal1 = parseFloat(input.dataset.threshold) || 0;
    //         let employeeRate1 = parseFloat(input.dataset.employeeRate) || 0;
    //         let deductionTypeId1 = parseInt(input.dataset.deductionTypeId) || 0;
    //         let employeeContribution = 0;

    //         if (deductionTypeId1 === 351) {
    //             // Provident Fund / Fixed
    //             employeeContribution = (earningBasic * employeeRate1) / 100;
    //         } else if (deductionTypeId1 === 352) {
    //             // Fixed
    //             employeeContribution = (getMonthlyCTC * employeeRate1) / 100;
    //         } else {
    //             // General Deductions
    //             employeeContribution = (earningBasic * employeeRate1) / 100;
    //         }

    //         // ✅ Safely update Deduction field
    //         if (input) {
    //             input.value = employeeContribution.toFixed(2);
    //             input.setAttribute("value", employeeContribution.toFixed(2));
    //         }
    //     });

    //     // ✅ Calculate Employer Deductions
    //     document.querySelectorAll(".employer-deduction-value").forEach(input => {
    //         let thresholdVal2 = parseFloat(input.dataset.threshold) || 0;
    //         let employerRate2 = parseFloat(input.dataset.employerRate) || 0;
    //         let deductionTypeId2 = parseInt(input.dataset.deductionTypeId) || 0;
    //         let employerContribution = 0;

    //         if (deductionTypeId2 === 351) {
    //             employerContribution = (earningBasic * employerRate2) / 100;
    //         } else if (deductionTypeId2 === 352) {
    //             employerContribution = (getMonthlyCTC * employerRate2) / 100;
    //         } else {
    //             employerContribution = (earningBasic * employerRate2) / 100;
    //         }

    //         // ✅ Safely update Deduction field
    //         if (input) {
    //             input.value = employerContribution.toFixed(2);
    //             input.setAttribute("value", employerContribution.toFixed(2));
    //         }
    //     });
    // }

    // Addition for employee and employer deductions
    function calculateTotal(selector, outputId) {
        let total = Array.from(document.querySelectorAll(selector))
            .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
        document.getElementById(outputId).value = total.toFixed(2);
    }

    function calculateEmpDeductions() {
        let getMonthlyCTC = parseFloat(document.getElementById("es_monthly_ctc")?.value) || 0;
        let getGrossSalary = parseFloat(document.getElementById("monthly_gross")?.value) || 0;
        let earningBasic = 0;

        // ✅ Get Basic Salary
        document.querySelectorAll(".calculated-value").forEach(input => {
            if (parseInt(input.dataset.earningTypeId) === 360) {
                earningBasic = parseFloat(input.value) || 0;
            }
        });

        // ✅ Calculate Employee Deductions
        document.querySelectorAll(".deduction-value").forEach(input => {
            let employeeRate1 = parseFloat(input.dataset.employeeRate) || 0;
            let deductionTypeId1 = parseInt(input.dataset.deductionTypeId) || 0;
            let employeeContribution = 0;

            if (deductionTypeId1 === 351) {
                employeeContribution = (earningBasic * employeeRate1) / 100;
            } else if (deductionTypeId1 === 352) {
                employeeContribution = (getMonthlyCTC * employeeRate1) / 100;
            } else {
                employeeContribution = (earningBasic * employeeRate1) / 100;
            }

            input.value = employeeContribution.toFixed(2);
            input.setAttribute("value", employeeContribution.toFixed(2));
        });

        // ✅ Calculate Employer Deductions
        document.querySelectorAll(".employer-deduction-value").forEach(input => {
            let employerRate2 = parseFloat(input.dataset.employerRate) || 0;
            let deductionTypeId2 = parseInt(input.dataset.deductionTypeId) || 0;
            let employerContribution = 0;

            if (deductionTypeId2 === 351) {
                employerContribution = (earningBasic * employerRate2) / 100;
            } else if (deductionTypeId2 === 352) {
                employerContribution = (getMonthlyCTC * employerRate2) / 100;
            } else {
                employerContribution = (earningBasic * employerRate2) / 100;
            }

            input.value = employerContribution.toFixed(2);
            input.setAttribute("value", employerContribution.toFixed(2));
        });

        // ✅ Automatically update totals
        calculateTotal(".deduction-value", "total_employee_deduction");
        calculateTotal(".employer-deduction-value", "total_employer_deduction");
    }

    function calculateCTC(type) {
        let totalEarnings = 0;
        let monthlyCTC = parseFloat(document.getElementById('es_monthly_ctc').value) || 0;
        let otherAllowance = 0;
        const esicInputs = document.querySelectorAll('input[data-deduction-type-id="352"]');
        let hisOtherAllow = '{{ $smhistoryLastData ? $smhistoryLastData->sm_other_allow : 0 }}';
        let hasInput = false;

        document.querySelectorAll('.calculated-value').forEach(function(input) {
            if (!input.readOnly) {
                let value = parseFloat(input.value);
                if (!isNaN(value) && value !== 0) {
                    hasInput = true;
                    totalEarnings += value;

                    if (input.dataset.payrollHeadingId == "419") {
                        otherAllowance += value;
                    }
                }
            }
        });
        // if (monthlyCTC == 0) {
        //     calculateEmpDeductions();
        // }
        // document.getElementById('monthly_gross').value = monthlyCTC.toFixed(2);
        document.getElementById('total_earnings').value = monthlyCTC.toFixed(2);
        let oAllow = monthlyCTC - otherAllowance;
        if (hasInput) {
            document.getElementById('other_allowance').value = oAllow.toFixed(2);
        } else {
            document.getElementById('other_allowance').value = hisOtherAllow;
        }

        const errorDiv = document.getElementById('oAllowError');
        const saveBtn = document.getElementById('saveButton');

        // Flag to track if any error found
        let hasError = false;

        if (oAllow < 0) {
            errorDiv.innerText = "Other Allowance cannot be negative.";
            errorDiv.style.display = 'block';
            hasError = true;
        } else {
            errorDiv.innerText = "";
            errorDiv.style.display = 'none';
        }

        // Check ESIC fields
        esicInputs.forEach((input, index) => {
            const esicThreshold = parseFloat(input.getAttribute("data-threshold")) || 0;
            const value = parseFloat(input.value) || 0;
            const role = input.getAttribute("data-role") || `role${index}`;

            let errorId = `esic-error-${role}`;
            let errorSpan = document.getElementById(errorId);

            if (!errorSpan) {
                errorSpan = document.createElement("span");
                errorSpan.id = errorId;
                errorSpan.style.color = "red";
                input.parentNode.appendChild(errorSpan);
            }

            if (monthlyCTC > esicThreshold && value > 0) {
                errorSpan.textContent = "Esic wage limit for coverage under the Act, effective from 01.01. 2017, is Rs. "+esicThreshold+"/- pm";
                hasError = true;
            } else {
                errorSpan.textContent = "";
            }
        });

        // Enable/disable save button based on error flag
        saveBtn.disabled = hasError;
    }

    document.addEventListener('DOMContentLoaded', function() {
        checkSaIdAndSetTooltips();

        // Event listeners
        // document.getElementById("es_monthly_ctc").addEventListener("input", calculateCTC);
        // document.addEventListener("input", function (e) {
        //     if (e.target.getAttribute("data-deduction-type-id") === "352") {
        //         calculateCTC();
        //         calculateEmpDeductions();
        //     }
        // });

        document.querySelectorAll('.calculated-value').forEach(function(input) {
            input.addEventListener('input', function() {
                calculateCTC('earnings');
                calculateEmpDeductions();
            });
        });

        // calculateCTC('monthly');

        document.getElementById('es_monthly_ctc').addEventListener('input', function() {
            calculateCTC('monthly');
            calculateEmpDeductions();
        });

        // Functionality to get data and auto fill in component input using ajax
        $('.special-364').each(function() {
            const saId = $(this).data('sa_id');
            const empActualId = '{{ $emp_actual_id }}';
            const businessId = '{{ $business_id }}';
            if (saId) {
                $.ajax({
                    url: "{{ route('employee.other.allowance') }}",
                    type: "get",
                    data: {
                        saId : saId,
                        empActualId : empActualId,
                        businessId : businessId
                    },
                    success: function(response) {
                        if (response.salaryEarn) {
                           $('#earning_'+saId).val(response.salaryEarn.es_e_amount);
                           setInfoTooltips();
                           setInfoTooltipsDeductions();
                       } else {
                            $('#earning_'+saId).val(0);
                       }

                    }
                });
            }
        });
    });

    // Set all input field value 0 functionality by class
    $(document).ready(function () {
        $(".numericInput").on("focus", function () {
            if ($(this).val() === "0") {
                $(this).val("");
            }
        });
        $(".numericInput").on("blur", function () {
            if ($.trim($(this).val()) === "") {
                $(this).val("0");
            }
        });
    });
</script>


@endsection
