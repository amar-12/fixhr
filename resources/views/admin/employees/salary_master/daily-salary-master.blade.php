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

    .employer-cell,
    .employee-cell {
        background-color: #f0f8ff;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }

    .summary-info {
        display: flex;
        gap: 15px;
        font-size: 14px;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
        flex-wrap: wrap;
    }

    .daily-wage-highlight {
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .daily-wage-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .working-days-input {
        border: 2px solid #28a745;
        font-weight: bold;
    }

    .calculated-value:read-only {
        background-color: #f8f9fa;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<!-- PAGE HEADER -->
<div class="page-header d-xl-flex d-block">
    <div class="page-leftheader">
        <div class="page-title">Add Daily Wage Payroll</div>
    </div>
</div>

<form action="{{ route('save.employee.payroll') }}" method="post" id="smform">
    @csrf
    <div class="row">
        <!-- Daily Wage Master Card -->
       <div class="card mb-4 daily-wage-highlight">
            <div class="card-body">
                <h4 style="text-align: center; font-weight: bold; margin-top: 20px;">
                    <span>Employee Name : {{ $employee->emp_full_name .' - '. $employee->emp_code }} </span>
                </h4>
                <h4 class="card-title">Daily Wage Master</h4>

                <input type="hidden" name="emp_id" value="{{ $emp_actual_id }}">
                <input type="hidden" name="business_id" value="{{ $business_id }}">
                <input type="hidden" name="wage_type" value="daily">

                <div id="salaryInfoCardBody" class="card-body collapse show">
                    <div class="row align-items-center">

                        <!-- Per Day Wage -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Per Day Wage <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control numericInput" name="es_per_day_wage" id="es_per_day_wage"
                                placeholder="0"
                                value="{{ !empty($smhistoryLastData->sm_per_day_wage) ? $smhistoryLastData->sm_per_day_wage : ($emp_salary->es_per_day_wage ?? '') }}"
                                maxlength="10" required>
                        </div>

                        <!-- Working Days in Month -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-calendar"></i> Working Days <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control working-days-input" name="working_days" id="working_days"
                                placeholder="1" min="1" max="31"
                                value="{{ !empty($smhistoryLastData->sm_working_days) ? $smhistoryLastData->sm_working_days : '1' }}"
                                required readonly>
                        </div>

                        <!-- Monthly Gross -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Per Day Gross
                            </label>
                            <input type="text" class="form-control numericInput" name="es_monthly_gross" id="es_monthly_gross"
                                placeholder="0" readonly
                                value="{{ !empty($smhistoryLastData->sm_gross_pay) ? $smhistoryLastData->sm_gross_pay : ($emp_salary->es_monthly_gross ?? '') }}">
                        </div>

                        <!-- Annual Gross -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Annual Gross
                            </label>
                            <input type="text" class="form-control numericInput" name="annual_gross" id="annual_gross"
                                placeholder="0" maxlength="10" readonly
                                value="{{ !empty($smhistoryLastData->sm_annual_gross) ? $smhistoryLastData->sm_annual_gross : ($emp_salary->es_annual_gross ?? '') }}">
                        </div>

                        <!-- Monthly CTC -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Per Day CTC
                            </label>
                            <input type="text" class="form-control numericInput" name="es_monthly_ctc" id="es_monthly_ctc"
                                placeholder="0" maxlength="10" readonly
                                value="{{ old('es_monthly_ctc', $emp_salary->es_monthly_ctc ?? '') }}">
                        </div>

                        <!-- Annual CTC -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-calendar-alt"></i> Annual CTC
                            </label>
                            <input type="text" class="form-control numericInput" name="es_annual_ctc" id="es_annual_ctc"
                                placeholder="0" maxlength="10" readonly value="{{ $emp_salary->es_annual_ctc ?? '' }}">
                        </div>

                        <!-- W.E.F -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-calendar-alt"></i> W.E.F <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="wef" class="form-control"
                                value="{{ \Carbon\Carbon::parse($smhistoryLastData?->wef ?? now())->format('Y-m-d') }}">
                            <input type="hidden" name="financial_year"
                                value="{{ $financialYears->firstWhere('fy_is_current', 1)?->fy_id }}">
                        </div>

                        <!-- Remark -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-comment"></i> Remark <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="remark" id="remark" placeholder="Remark"
                                value="{{ $smhistoryLastData->sm_remark ?? '' }}" required>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Earnings and Deductions Section -->
        <div class="row">
            <!-- Earnings Section -->
            <div class="col-md-6">
                <div class="card mb-4 daily-wage-card">
                    <div class="card-header">
                        <h5>
                            Earnings (Per Day Basis)
                            <button class="btn btn-link float-right testq" type="button" data-toggle="collapse"
                                data-target="#earningsCardBody" aria-expanded="false">
                                <i class="fa fa-chevron-down"></i>
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
                            @php
                                // Get saved earning value for this specific earning type
                                $savedEarningValue = $emp_earnings[$earning->sa_id] ?? '';
                                // If no saved value, check if there's calculation data from history
                                if (empty($savedEarningValue) && !empty($smhistoryLastData)) {
                                    // Map earning type IDs to history fields if needed
                                    $earningTypeId = $earning->sa_earning_type_id;
                                    if ($earningTypeId == 360 && !empty($smhistoryLastData->sm_basic)) {
                                        $savedEarningValue = $smhistoryLastData->sm_basic;
                                    } elseif ($earningTypeId == 361 && !empty($smhistoryLastData->sm_hra)) {
                                        $savedEarningValue = $smhistoryLastData->sm_hra;
                                    } elseif ($earningTypeId == 362 && !empty($smhistoryLastData->sm_conveyance)) {
                                        $savedEarningValue = $smhistoryLastData->sm_conveyance;
                                    } elseif ($earningTypeId == 363 && !empty($smhistoryLastData->sm_medical)) {
                                        $savedEarningValue = $smhistoryLastData->sm_medical;
                                    }
                                }
                            @endphp
                            <div class="col-md-6 mb-3">
                                <label>
                                    <i class="fa fa-dollar-sign"></i> {{ $earning->sa_title }}
                                    <span class="text-danger">*</span>
                                    <i class="fa fa-info-circle text-primary ms-1 earning-title"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $earning->sa_description ?? '' }}"></i>
                                </label>
                                <input type="text" class="form-control calculated-value numericInput special-364"
                                    name="earnings[{{ $earning->sa_id }}]"
                                    value="{{ $savedEarningValue }}"
                                    data-sa_id="{{ $earning->sa_id }}"
                                    data-calculation-type="{{ $earning->sa_calculation_type }}"
                                    data-threshold="{{ $earning->sa_threshold_value }}"
                                    data-percentage="{{ $earning->sa_percentage ?? 0 }}"
                                    data-fixed="{{ $earning->sa_fixed_value ?? 0 }}"
                                    data-earning-type-id="{{ $earning->sa_earning_type_id }}"
                                    data-payroll-heading-id="{{ $earning->sa_payroll_heading_id }}"
                                    id="earning_{{ $earning->sa_id }}" maxlength="9" placeholder="0" required>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Other Allowance</label>
                                <input type="text" class="form-control numericInput" name="es_rem_allowance"
                                    id="other_allowance" placeholder="0" readonly
                                    value="{{ !empty($smhistoryLastData->sm_other_allow) ? $smhistoryLastData->sm_other_allow : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label>Total Earnings (Monthly)</label>
                                <input type="text" class="form-control numericInput" name="total_employee_earning"
                                    id="total_earnings" placeholder="0" readonly
                                    value="{{ !empty($smhistoryLastData->sm_total_earning) ? $smhistoryLastData->sm_total_earning : '' }}">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Take Home Salary (Net Pay)</label>
                                <input type="text" class="form-control numericInput" name="monthly_net_salary"
                                    id="monthly_net_salary" placeholder="0" readonly
                                    value="{{ !empty($smhistoryLastData->sm_net_pay) ? $smhistoryLastData->sm_net_pay : '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions (Employee) -->
            <div class="col-md-3">
                <div class="card mb-4 daily-wage-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Deductions (Employee)
                            <button class="btn btn-link testq2" type="button" data-toggle="collapse"
                                data-target="#deductionsCardBody" aria-expanded="false">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                        </h5>
                        <div class="d-flex align-items-center ms-auto">
                            <h5 id="dedEmpHeader" class="mb-0 me-3">
                                <label class="mb-0"><span id="total_emp_ded"></span></label>
                            </h5>
                            <div>
                                <input type="checkbox" id="esicValidationToggle" checked>
                                <input type="hidden" name="esic_validation_enabled" id="esicValidationEnabledInput" value="1">
                            </div>
                        </div>
                    </div>
                    <div id="deductionsCardBody" class="card-body collapse show">
                        <div class="row">
                            @foreach ($deductions as $deduction)
                            @php
                            $typeId = $deduction->std_deduction_type_id;
                            $isPF = $typeId == 351;
                            $isESIC = $typeId == 352;
                            // Get saved deduction value
                            $savedDeductionValue = $emp_deductions[$typeId] ?? '';
                            @endphp
                            <div class="col-md-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">
                                        {{ $deduction->deduction_type_name }}
                                        <i class="fa fa-info-circle text-primary ms-1" title="{{ $deduction->std_description ?? '' }}"></i>
                                    </label>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input toggle-form" type="checkbox"
                                            data-type="{{ $typeId }}"
                                            {{ ($isPF && $employee->emp_is_pf_enabled != 121) ||
                                               ($isESIC && $employee->emp_esic_limit != 121) ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <input type="text" class="form-control deduction-value numericInput deduction-input-{{ $typeId }}"
                                    name="deductions[{{ $deduction->std_id }}]"
                                    value="{{ $savedDeductionValue }}"
                                    data-threshold="{{ $deduction->std_threshold }}"
                                    data-employee-rate="{{ $deduction->std_employee_contri_rate_amount }}"
                                    data-deduction-type-id="{{ $typeId }}"
                                    maxlength="9" placeholder="0" readonly
                                    @if(($isPF && $employee->emp_is_pf_enabled == 121) ||
                                        ($isESIC && $employee->emp_esic_limit == 121))
                                        style="display: none;"
                                    @endif>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Total Deductions</label>
                                <input type="text" class="form-control numericInput" name="total_employee_deduction"
                                    id="total_employee_deduction" placeholder="0" readonly
                                    value="{{ !empty($smhistoryLastData->sm_employee_total_ded) ? $smhistoryLastData->sm_employee_total_ded : '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions (Employer) -->
            <div class="col-md-3">
                <div class="card mb-4 daily-wage-card">
                    <div class="card-header">
                        <h5>
                            Deductions (Employer)
                            <button class="btn btn-link float-right testq3" type="button" data-toggle="collapse"
                                data-target="#deductionsCardBodyE" aria-expanded="false">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                        </h5>
                        <h5 id="dedEmployerHeader" class="float-right colShowData">
                            <label><span id="total_employer_deds"></span></label>
                        </h5>
                    </div>
                    <div id="deductionsCardBodyE" class="card-body collapse show">
                        <div class="row">
                            @foreach ($employerDeductions as $deduction)
                            @php
                            $typeId = $deduction->std_deduction_type_id;
                            $isPF = $typeId == 351;
                            $isESIC = $typeId == 352;
                            // Get saved employer deduction value
                            $savedEmployerDeductionValue = $emplyer_deductions[$typeId] ?? '';
                            @endphp
                            <div class="col-md-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">{{ $deduction->deduction_type_name }}
                                        <i class="fa fa-info-circle text-primary ms-1" title="{{ $deduction->std_description ?? '' }}"></i>
                                    </label>
                                </div>
                                <input type="text" class="form-control employer-deduction-value numericInput deduction2-input-{{ $typeId }}"
                                    name="employerDeductions[{{ $deduction->std_id }}]"
                                    value="{{ $savedEmployerDeductionValue }}"
                                    data-threshold="{{ $deduction->std_threshold }}"
                                    data-employer-rate="{{ $deduction->std_employer_contri_rate_amount }}"
                                    data-deduction-type-id="{{ $deduction->std_deduction_type_id }}"
                                    maxlength="9" placeholder="0" readonly
                                    @if(($isPF && $employee->emp_is_pf_enabled == 121) ||
                                        ($isESIC && $employee->emp_esic_limit == 121))
                                        style="display: none;"
                                    @endif>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Total Deductions</label>
                                <input type="text" class="form-control numericInput" name="total_employer_deduction"
                                    id="total_employer_deduction" placeholder="0" readonly
                                    value="{{ !empty($smhistoryLastData->sm_employer_total_ded) ? $smhistoryLastData->sm_employer_total_ded : '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        @if(!empty($smhistory->first()->sm_monthly_ctc))
        <div class="card mb-4">
            <div class="card-header">
                <h5>
                    Salary History
                    <button class="btn btn-link" type="button" data-toggle="collapse"
                        data-target="#summaryCardBody" aria-expanded="false">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                </h5>
            </div>

            @php
            $formattedWef = '';
            if (!empty($smhistoryLastData)) {
                $wef = $smhistoryLastData->created_at;
                $wef1 = Carbon::parse($wef);
                $formattedWef = $wef1->format('d-M-Y');
            }
            @endphp

            <div class="px-3">
                <div class="summary-info">
                    <span><strong>Emp. Name:</strong> {{ $employee->emp_full_name }}</span>
                    <span><strong>DOJ:</strong> {{ $formattedDate }}</span>
                    <span><strong>WEF:</strong> {{ $formattedWef }}</span>
                    <span><strong>Remark:</strong> {{ $smhistoryLastData->sm_remark }}</span>
                </div>
            </div>

            <div id="summaryCardBody" class="card-body collapse show">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th></th>
                                <th>Financial Year</th>
                                <th>Effective From</th>
                                <th>Effective Till</th>
                                <th>Per Day Wage</th>
                                <th>Monthly CTC</th>
                                <th>Gross Pay</th>
                                <th>View</th>
                                <th>Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($smhistory as $smhist)
                            @php
                            $dateString1 = $smhist->wef;
                            $date1 = Carbon::parse($dateString1);
                            $formattedDate1 = $date1->format('d-M-Y');
                            $date2 = Carbon::parse($smhist->financial_years->fy_end_date);
                            $formattedDate2 = $date2->format('d-M-Y');
                            @endphp
                            <tr>
                                <td class="arrow-up">↑</td>
                                <td>{{ $smhist->financial_years->fy_year }}</td>
                                <td>{{ $formattedDate1 }}</td>
                                <td>{{ $formattedDate2 }}</td>
                                <td>{{ $smhist->sm_per_day_wage ?? 'N/A' }}</td>
                                <td>{{ $smhist->sm_monthly_ctc }}</td>
                                <td>{{ $smhist->sm_gross_pay }}</td>
                                <td>
                                    <span class="simple-icon openBtn" style="cursor: pointer"
                                        data-from_date="{{ $formattedDate1 }}"
                                        data-to_date="{{ $formattedDate2 }}"
                                        data-monthly="{{ $smhist->sm_monthly_ctc }}"
                                        data-annual="{{ $smhist->sm_annual_ctc }}"
                                        data-per_day="{{ $smhist->sm_per_day_wage }}"
                                        data-working_days="{{ $smhist->sm_working_days }}"
                                        data-basic="{{ $smhist->sm_basic }}"
                                        data-hra="{{ $smhist->sm_hra }}"
                                        data-conveyance="{{ $smhist->sm_conveyance ?? '0' }}"
                                        data-medical="{{ $smhist->sm_medical ?? '0' }}"
                                        data-special_allowance="{{ $smhist->sm_special_allowance ?? '0' }}"
                                        data-bonus="{{ $smhist->sm_bonus ?? '0' }}"
                                        data-incentives="{{ $smhist->sm_incentives ?? '0' }}"
                                        data-other_allowance="{{ $smhist->sm_other_allow ?? '0' }}"
                                        data-total_earning="{{ $smhist->sm_total_earning }}"
                                        data-gross_pay="{{ $smhist->sm_gross_pay }}"
                                        data-net_pay="{{ $smhist->sm_net_pay }}"
                                        data-pf_employee="{{ $smhist->sm_pf_employee ?? '0' }}"
                                        data-esic_employee="{{ $smhist->sm_esic_employee ?? '0' }}"
                                        data-professional_tax="{{ $smhist->sm_professional_tax ?? '0' }}"
                                        data-tds="{{ $smhist->sm_tds ?? '0' }}"
                                        data-pf_employer="{{ $smhist->sm_pf_employer ?? '0' }}"
                                        data-esic_employer="{{ $smhist->sm_esic_employer ?? '0' }}"
                                        data-gratuity="{{ $smhist->sm_gratuity ?? '0' }}"
                                        data-other_contributions="{{ $smhist->sm_other_contributions ?? '0' }}"
                                        data-employee_deductions="{{ $smhist->sm_employee_total_ded ?? '0' }}"
                                        data-employer_deductions="{{ $smhist->sm_employer_total_ded ?? '0' }}"
                                        data-monthly_ctc="{{ $smhist->sm_monthly_ctc }}">
                                        👁
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('export.smhistory', ['id' => $smhist->sm_id]) }}"
                                        class="simple-icon" title="Download PDF">⬇️</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Buttons -->
        <div class="text-end">
            <button type="button" onclick="window.history.back()" class="btn btn-outline-danger btn-lg">
                Cancel
            </button>
            <button id="saveButton" type="submit" class="btn btn-outline-info btn-lg cus-save"
                {{ !empty($smhistoryLastData) ? 'disabled' : '' }}>
                Save
            </button>
        </div>

 <!-- Preview Modal -->
 <div class="modal fade" id="employeeSalaryHistoryModal" tabindex="-1" role="dialog" aria-labelledby="employeeSalaryHistoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

     <div class="modal-dialog modal-dialog-centered modal-lg">
         <div class="modal-content tx-size-sm">
             <div class="modal-header border-0">
                 <h4 class="modal-title" id="modalTitle">Daily Wage Preview</h4>
                 <button type="button" aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="card user-pro-list overflow-hidden mt-3" id="avtarDiv">
                 <div class="card-body py-5">
                     <h4 style="color:white !important; text-align:center;">From <span id="from_date"></span> To
                         <span id="to_date"></span>
                     </h4>
                     <div class="row user-pic text-left">
                         <div class="col-1 pt-7">
                             <span class="avatar avatar-xxl brround" id="empAvtar" style="background-image:  url('{{ isset($employee) && $employee->emp_profile_photo ? $employee->emp_profile_photo : asset('assets/imgs/user.png') }}');"></span>
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
                                 <!-- Daily Wage Information -->
                                 <tr>
                                     <td rowspan="2" class="text-blue employer-cell">
                                         <h4 class="m-0 vertical-text"></h4>
                                     </td>
                                     <td class="py-2 px-0"><span class="font-weight-semibold w-50">Per Day Wage</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0 font-weight-semibold" id="perDayWage"></td>
                                     <td class="py-2 px-0"><span class="font-weight-semibold w-50">Working Days</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0 font-weight-semibold" id="workingDays"></td>
                                 </tr>

                                 <tr>
                                     <td class="py-2 px-0"><span class="font-weight-semibold w-50">Monthly CTC</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0" id="monthlyCtc"></td>
                                     <td class="py-2 px-0"><span class="font-weight-semibold w-50">Annual CTC</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0" id="annualCtc"></td>
                                 </tr>

                                 <!-- Horizontal Line -->
                                 <tr style="border-top: 1px solid #ddd;">
                                     <td colspan="7"></td>
                                 </tr>

                                 <!-- Earnings Components -->
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
                                     <td class="py-2 px-0"><span class="w-50">Conveyance Allowance</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0" id="empConvAllow"></td>
                                     <td class="py-2 px-0"><span class="w-50">Medical Allowance</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0" id="empMedicalAllow"></td>
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
                                     <td colspan="7"></td>
                                 </tr>

                                 <!-- Employee Deductions -->
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
                                     <td class="py-2 px-0"><span class="w-50">Professional Tax</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0" id="empProfTax"></td>
                                     <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total Deductions</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0 font-weight-semibold" id="empDeductions"></td>
                                 </tr>

                                 <!-- Horizontal Line -->
                                 <tr style="border-top: 1px solid #ddd;">
                                     <td colspan="7"></td>
                                 </tr>

                                 <!-- Employer Deductions -->
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
                                     <td class="py-2 px-0"><span class="w-50">Other Contributions</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0" id="employerOther"></td>
                                     <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total Deductions</span></td>
                                     <td class="py-2 px-0">:</td>
                                     <td class="py-2 px-0 font-weight-semibold" id="employerTDeductions"></td>
                                 </tr>

                                 <!-- Horizontal Line -->
                                 <tr style="border-top: 1px solid #ddd;">
                                     <td colspan="7"></td>
                                 </tr>

                                 <!-- Summary -->
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
                                     <td class="py-2 px-0"><span class="font-weight-semibold w-50"></span></td>
                                     <td class="py-2 px-0"></td>
                                     <td class="py-2 px-0" id=""></td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>

             <div class="modal-footer d-flex justify-content-end">
                 <button type="button" class="btn btn-outline-danger cancel" data-bs-dismiss="modal">Cancel</button>
             </div>
         </div>
     </div>
 </div>

    </div>
</form>
@endsection

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

@section('script')
<script>
    // Initialize Flatpickr
    flatpickr('.flatpickr', {
        dateFormat: "Y-m-d",
        minDate: "today",
        allowInput: true,
        altInput: true,
        altFormat: "d-M-Y",
        disableMobile: false
    });

    let isPFEnabled = {{ $employee->emp_is_pf_enabled ?? 0}};
    let isESICEnabled = {{ $employee->emp_esic_limit ?? 0 }};
    let esicValidationEnabled = true;

    // Show modal with complete data
    $(document).on('click', '.openBtn', function() {
        $('#employeeSalaryHistoryModal').modal('show');

        // Basic information
        $('#from_date').text($(this).data('from_date'));
        $('#to_date').text($(this).data('to_date'));
        $('#perDayWage').text(formatCurrency($(this).data('per_day')));
        $('#workingDays').text($(this).data('working_days'));
        $('#monthlyCtc').text(formatCurrency($(this).data('monthly')));
        $('#annualCtc').text(formatCurrency($(this).data('annual')));

        // Earnings Components
        $('#basicSalary').text(formatCurrency($(this).data('basic')));
        $('#empHRA').text(formatCurrency($(this).data('hra')));
        $('#empConvAllow').text(formatCurrency($(this).data('conveyance')));
        $('#empMedicalAllow').text(formatCurrency($(this).data('medical')));
        $('#empSpecialAllow').text(formatCurrency($(this).data('special_allowance')));
        $('#empOtherAllow').text(formatCurrency($(this).data('other_allowance')));

        // Employee Deductions
        $('#empEpf').text(formatCurrency($(this).data('pf_employee')));
        $('#empEsic').text(formatCurrency($(this).data('esic_employee')));
        $('#empProfTax').text(formatCurrency($(this).data('professional_tax')));
        $('#empDeductions').text(formatCurrency($(this).data('employee_deductions')));

        // Employer Deductions
        $('#employerEpf').text(formatCurrency($(this).data('pf_employer')));
        $('#employerEsic').text(formatCurrency($(this).data('esic_employer')));
        $('#employerOther').text(formatCurrency($(this).data('other_contributions')));
        $('#employerTDeductions').text(formatCurrency($(this).data('employer_deductions')));

        // Summary
        $('#empEarnings').text(formatCurrency($(this).data('total_earning')));
        $('#empGrossPay').text(formatCurrency($(this).data('gross_pay')));
        $('#empNetPay').text(formatCurrency($(this).data('net_pay')));
        $('#finalMonthlyCtc').text(formatCurrency($(this).data('monthly_ctc') || $(this).data('monthly')));
    });

    // Function to format currency
    function formatCurrency(amount) {
        if (!amount || amount === '0' || amount === '0.00') return '₹0.00';
        const num = parseFloat(amount);
        return '₹' + num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Show modal with complete data
    $(document).on('click', '.openBtn', function() {
        $('#employeeSalaryHistoryModal').modal('show');

        // Basic information
        $('#from_date').text($(this).data('from_date'));
        $('#to_date').text($(this).data('to_date'));
        $('#perDayWage').text(formatCurrency($(this).data('per_day')));
        $('#workingDays').text($(this).data('working_days'));
        $('#monthlyCtc').text(formatCurrency($(this).data('monthly')));
        $('#annualCtc').text(formatCurrency($(this).data('annual')));

        // Earnings
        $('#empEarnings').text(formatCurrency($(this).data('total_earning')));
        $('#otherAllowance').text(formatCurrency($(this).data('other_allowance') || '0.00'));
        $('#empGrossPay').text(formatCurrency($(this).data('gross_pay')));

        // Deductions
        $('#totalEmployeeDeductions').text(formatCurrency($(this).data('employee_deductions') || '0.00'));
        $('#totalEmployerDeductions').text(formatCurrency($(this).data('employer_deductions') || '0.00'));
        $('#totalDeductions').text(formatCurrency($(this).data('employee_deductions') || '0.00'));

        // Final summary
        $('#empNetPay').text(formatCurrency($(this).data('net_pay')));
        $('#finalMonthlyCtc').text(formatCurrency($(this).data('monthly_ctc') || $(this).data('monthly')));

        // Populate earnings breakdown
        populateEarningsBreakdown($(this));

        // Populate deductions breakdown
        populateDeductionsBreakdown($(this));
    });

    // Function to format currency
    function formatCurrency(amount) {
        if (!amount || amount === '0' || amount === '0.00') return '₹0.00';
        const num = parseFloat(amount);
        return '₹' + num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Function to populate earnings breakdown
    function populateEarningsBreakdown(element) {
        const earningsBreakdown = $('#earningsBreakdown');
        earningsBreakdown.empty();

        const earningsData = {
            'Basic Salary': element.data('basic') || '0.00',
            'House Rent Allowance': element.data('hra') || '0.00',
            'Conveyance Allowance': element.data('conveyance') || '0.00',
            'Medical Allowance': element.data('medical') || '0.00',
            'Special Allowance': element.data('special_allowance') || '0.00',
            'Bonus': element.data('bonus') || '0.00',
            'Incentives': element.data('incentives') || '0.00'
        };

        let earningsArray = Object.entries(earningsData);
        let rows = [];

        for (let i = 0; i < earningsArray.length; i += 2) {
            let row = '<tr>';

            // First earning in row
            const [type1, amount1] = earningsArray[i];
            row += `<td class="text-left">${type1}</td>`;
            row += `<td class="text-right">${formatCurrency(amount1)}</td>`;

            // Second earning in row (if exists)
            if (i + 1 < earningsArray.length) {
                const [type2, amount2] = earningsArray[i + 1];
                row += `<td class="text-left">${type2}</td>`;
                row += `<td class="text-right">${formatCurrency(amount2)}</td>`;
            } else {
                row += '<td class="text-left">-</td><td class="text-right">-</td>';
            }

            row += '</tr>';
            rows.push(row);
        }

        earningsBreakdown.html(rows.join(''));
    }

    // Function to populate deductions breakdown
    function populateDeductionsBreakdown(element) {
        const employeeDeductions = $('#employeeDeductionsBreakdown');
        const employerDeductions = $('#employerDeductionsBreakdown');

        employeeDeductions.empty();
        employerDeductions.empty();

        const employeeDeductionsData = {
            'Provident Fund (PF)': element.data('pf_employee') || '0.00',
            'ESIC': element.data('esic_employee') || '0.00',
            'Professional Tax': element.data('professional_tax') || '0.00',
            'TDS': element.data('tds') || '0.00'
        };

        const employerDeductionsData = {
            'Provident Fund (PF)': element.data('pf_employer') || '0.00',
            'ESIC': element.data('esic_employer') || '0.00',
            'Gratuity': element.data('gratuity') || '0.00',
            'Other Contributions': element.data('other_contributions') || '0.00'
        };

        // Populate employee deductions
        let hasEmployeeDeductions = false;
        Object.entries(employeeDeductionsData).forEach(([type, amount]) => {
            if (parseFloat(amount) > 0) {
                employeeDeductions.append(`
                    <tr>
                        <td class="text-left">${type}</td>
                        <td class="text-right">${formatCurrency(amount)}</td>
                    </tr>
                `);
                hasEmployeeDeductions = true;
            }
        });

        // Populate employer deductions
        let hasEmployerDeductions = false;
        Object.entries(employerDeductionsData).forEach(([type, amount]) => {
            if (parseFloat(amount) > 0) {
                employerDeductions.append(`
                    <tr>
                        <td class="text-left">${type}</td>
                        <td class="text-right">${formatCurrency(amount)}</td>
                    </tr>
                `);
                hasEmployerDeductions = true;
            }
        });

        // If no deductions, show message
        if (!hasEmployeeDeductions) {
            employeeDeductions.append('<tr><td colspan="2" class="text-center">No Employee Deductions</td></tr>');
        }

        if (!hasEmployerDeductions) {
            employerDeductions.append('<tr><td colspan="2" class="text-center">No Employer Deductions</td></tr>');
        }
    }

    // Allow decimal input
    function allowDecimalInput(event) {
        let input = event.target;
        input.value = input.value.replace(/[^0-9.]/g, '');
        if ((input.value.match(/\./g) || []).length > 1) {
            input.value = input.value.replace(/\.+$/, '');
        }
    }

    document.querySelectorAll(".numericInput").forEach(input => {
        input.addEventListener("input", allowDecimalInput);
    });

    // Calculate from Per Day Wage
    function calculateFromDailyWage() {
        const perDayWage = parseFloat(document.getElementById('es_per_day_wage').value.replace(/,/g, '')) || 0;
        const workingDays = parseFloat(document.getElementById('working_days').value) || 26;

        if (perDayWage > 0) {
            const monthlyGross = perDayWage * workingDays;
            const annualGross = monthlyGross * 12;

            document.getElementById('es_monthly_gross').value = monthlyGross.toFixed(2);
            document.getElementById('annual_gross').value = annualGross.toFixed(2);
            document.getElementById('es_monthly_ctc').value = monthlyGross.toFixed(2);
            document.getElementById('es_annual_ctc').value = annualGross.toFixed(2);

            calculateEarningsFromGross(monthlyGross);
            calculateEmpDeductions();
            updateValues();
        } else {
            document.getElementById('es_monthly_gross').value = "0.00";
            document.getElementById('annual_gross').value = "0.00";
            document.getElementById('es_monthly_ctc').value = "0.00";
            document.getElementById('es_annual_ctc').value = "0.00";
        }
    }

    // Calculate earnings from monthly gross (manual basic input)
    function calculateEarningsFromGross(monthlyGross) {
        let totalEarnings = 0;

        // Read manual basic salary from input
        const basicInput = document.querySelector('[data-earning-type-id="360"]');
        let basicSalary = parseFloat(basicInput?.value) || 0;

        document.querySelectorAll('.calculated-value').forEach(function(input) {
            const calcType = input.dataset.calculationType;
            const threshold = parseFloat(input.dataset.threshold) || 0;
            const earningTypeId = parseInt(input.dataset.earningTypeId);

            // Skip basic salary input (we already read it manually)
            if (earningTypeId === 360) {
                totalEarnings += basicSalary; // Include in total
                return;
            }

            let value = 0;

            // Only calculate if the input doesn't already have a saved value
            if (!input.value || parseFloat(input.value) === 0) {
                switch (calcType) {
                    case '346': // % of Monthly Gross
                        value = (monthlyGross * threshold) / 100;
                        break;
                    case '347': // % of Basic
                        value = (basicSalary * threshold) / 100;
                        break;
                    case '348': // Flat amount
                        value = threshold;
                        break;
                }
                input.value = value.toFixed(2);
            } else {
                // Use the existing saved value
                value = parseFloat(input.value) || 0;
            }

            totalEarnings += value;
        });

        // Calculate other allowance as the remainder
        let otherAllowance = monthlyGross - totalEarnings;
        document.getElementById('other_allowance').value = otherAllowance > 0 ? otherAllowance.toFixed(2) : '0.00';
        document.getElementById('total_earnings').value = monthlyGross.toFixed(2);
    }

    // Calculate employee and employer deductions
    function calculateEmpDeductions() {
        let monthlyGross = parseFloat(document.getElementById("es_monthly_gross")?.value) || 0;
        let earningBasic = 0;

        // Get basic salary
        document.querySelectorAll(".calculated-value").forEach(input => {
            if (parseInt(input.dataset.earningTypeId) === 360) {
                earningBasic = parseFloat(input.value) || 0;
            }
        });

        // Employee Deductions - only calculate if no saved value exists
        document.querySelectorAll(".deduction-value").forEach(input => {
            // If input already has a value (saved data), don't recalculate
            if (input.value && parseFloat(input.value) > 0) {
                return;
            }

            let rate = parseFloat(input.dataset.employeeRate) || 0;
            let typeId = parseInt(input.dataset.deductionTypeId) || 0;
            let esicThreshold = parseFloat(input.dataset.threshold) || 0;
            let contribution = 0;

            if (typeId === 351) { // PF
                contribution = (isPFEnabled == 120 ? (earningBasic * rate) / 100 : 0);
            } else if (typeId === 352) { // ESIC
                let esicBase = Math.min(monthlyGross, esicThreshold);
                contribution = (isESICEnabled == 120 ? (esicBase * rate) / 100 : 0);
            } else {
                contribution = (earningBasic * rate) / 100;
            }

            input.value = contribution.toFixed(2);
        });

        // Employer Deductions - only calculate if no saved value exists
        document.querySelectorAll(".employer-deduction-value").forEach(input => {
            // If input already has a value (saved data), don't recalculate
            if (input.value && parseFloat(input.value) > 0) {
                return;
            }

            let rate = parseFloat(input.dataset.employerRate) || 0;
            let typeId = parseInt(input.dataset.deductionTypeId) || 0;
            let esicThreshold = parseFloat(input.dataset.threshold) || 0;
            let contribution = 0;

            if (typeId === 351) { // PF
                contribution = (isPFEnabled == 120 ? (earningBasic * rate) / 100 : 0);
            } else if (typeId === 352) { // ESIC
                let esicBase = Math.min(monthlyGross, esicThreshold);
                contribution = (isESICEnabled == 120 ? (esicBase * rate) / 100 : 0);
            } else {
                contribution = (earningBasic * rate) / 100;
            }

            input.value = contribution.toFixed(2);
        });

        calculateTotal(".deduction-value", "total_employee_deduction");
        calculateTotal(".employer-deduction-value", "total_employer_deduction");
        updateSalaryFields();

        // Update CTC with employer contribution
        const totalEarnings = parseFloat(document.getElementById("total_earnings")?.value) || 0;
        const totalEmployerDeduction = parseFloat(document.getElementById("total_employer_deduction")?.value) || 0;
        const newCTC = totalEarnings + totalEmployerDeduction;

        document.getElementById("es_monthly_ctc").value = newCTC.toFixed(2);
        document.getElementById("es_annual_ctc").value = (newCTC * 12).toFixed(2);

        // ESIC Validation
        if (esicValidationEnabled) {
            validateESIC(monthlyGross);
        }
    }

    // ESIC Validation
    function validateESIC(monthlyGross) {
        const esicInputs = document.querySelectorAll('input[data-deduction-type-id="352"]');
        let hasError = false;

        esicInputs.forEach((input, index) => {
            const esicThreshold = parseFloat(input.getAttribute("data-threshold")) || 0;
            const role = input.getAttribute("data-role") || `role${index}`;
            const errorId = `esic-error-${role}`;
            let errorSpan = document.getElementById(errorId) || document.createElement("span");

            errorSpan.id = errorId;
            errorSpan.style.color = "red";
            errorSpan.style.fontSize = "12px";

            if (!document.getElementById(errorId)) {
                input.parentNode.appendChild(errorSpan);
            }

            if (monthlyGross > esicThreshold && parseFloat(input.value) > 0) {
                errorSpan.textContent = `ESIC wage limit is Rs. ${esicThreshold}/- pm`;
                hasError = true;
            } else {
                errorSpan.textContent = '';
            }
        });

        document.getElementById('saveButton').disabled = hasError;
    }

    // Calculate totals
    function calculateTotal(selector, outputId) {
        let total = Array.from(document.querySelectorAll(selector))
            .filter(input => input.offsetParent !== null)
            .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
        document.getElementById(outputId).value = total.toFixed(2);
    }

    // Update salary fields
    function updateSalaryFields() {
        const monthlyGross = parseFloat(document.getElementById('es_monthly_gross')?.value) || 0;
        const totalEmployeeDeduction = parseFloat(document.getElementById('total_employee_deduction')?.value) || 0;
        const netPay = monthlyGross - totalEmployeeDeduction;
        document.getElementById('monthly_net_salary').value = netPay.toFixed(2);
    }

    // Update header values
    function updateValues() {
        let totalEarnings = parseFloat(document.getElementById("total_earnings")?.value) || 0;
        let totalEmployeeDeduction = parseFloat(document.getElementById("total_employee_deduction")?.value) || 0;
        let totalEmployerDeduction = parseFloat(document.getElementById("total_employer_deduction")?.value) || 0;

        document.getElementById("total_earnings_head").innerHTML = totalEarnings.toFixed(2);
        document.getElementById("total_emp_ded").innerHTML = totalEmployeeDeduction.toFixed(2);
        document.getElementById("total_employer_deds").innerHTML = totalEmployerDeduction.toFixed(2);
    }

    // ESIC Validation Toggle
    document.getElementById('esicValidationToggle').addEventListener('change', function() {
        esicValidationEnabled = this.checked;
        document.getElementById('esicValidationEnabledInput').value = esicValidationEnabled ? 1 : 0;

        if (!esicValidationEnabled) {
            document.querySelectorAll('[id^="esic-error-"]').forEach(span => {
                span.textContent = '';
            });
            document.getElementById('saveButton').disabled = false;
        } else {
            const monthlyGross = parseFloat(document.getElementById('es_monthly_gross').value) || 0;
            validateESIC(monthlyGross);
        }
    });

    // Toggle deductions
    $('.toggle-form').on('change', function () {
        const typeId = $(this).data('type');
        const isChecked = $(this).is(':checked');

        if (typeId == 351) {
            isPFEnabled = isChecked ? 120 : 0;
        }
        if (typeId == 352) {
            isESICEnabled = isChecked ? 120 : 0;
        }

        const $empInput = $('.deduction-value[data-deduction-type-id="' + typeId + '"]');
        const $employerInput = $('.employer-deduction-value[data-deduction-type-id="' + typeId + '"]');

        if (isChecked) {
            $empInput.show();
            $employerInput.show();
        } else {
            $empInput.hide().val('0');
            $employerInput.hide().val('0');

            if (typeId == 352) {
                document.querySelectorAll('[id^="esic-error-"]').forEach(span => {
                    span.textContent = '';
                });
            }
        }

        calculateEmpDeductions();
    });

    // Document ready
    $(document).ready(function () {
        // Hide/show headers on collapse
        $('#earningsHeader, #dedEmpHeader, #dedEmployerHeader').hide();

        $('.testq').on('click', function() {
            $('#earningsHeader').toggle($('#earningsCardBody').hasClass('show'));
        });

        $('.testq2').on('click', function() {
            $('#dedEmpHeader').toggle($('#deductionsCardBody').hasClass('show'));
        });

        $('.testq3').on('click', function() {
            $('#dedEmployerHeader').toggle($('#deductionsCardBodyE').hasClass('show'));
        });

        // Zero value handling
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

        // Per day wage input listener
        $('#es_per_day_wage, #working_days').on('input', function() {
            calculateFromDailyWage();
        });

        // Manual earning input changes
        $('.calculated-value').on('input', function() {
            const monthlyGross = parseFloat(document.getElementById('es_monthly_gross').value) || 0;
            let totalEarnings = 0;

            document.querySelectorAll('.calculated-value').forEach(input => {
                totalEarnings += parseFloat(input.value) || 0;
            });

            let otherAllowance = monthlyGross - totalEarnings;
            document.getElementById('other_allowance').value = otherAllowance > 0 ? otherAllowance.toFixed(2) : '0.00';
            document.getElementById('total_earnings').value = monthlyGross.toFixed(2);

            calculateEmpDeductions();
        });

        // Initial calculations
        if (isPFEnabled == 120) {
            document.querySelectorAll('.deduction-value[data-deduction-type-id="351"]').forEach(el => el.style.display = '');
            document.querySelectorAll('.employer-deduction-value[data-deduction-type-id="351"]').forEach(el => el.style.display = '');
        }

        if (isESICEnabled == 120) {
            document.querySelectorAll('.deduction-value[data-deduction-type-id="352"]').forEach(el => el.style.display = '');
            document.querySelectorAll('.employer-deduction-value[data-deduction-type-id="352"]').forEach(el => el.style.display = '');
        }

        // Initialize with saved data
        calculateFromDailyWage();
        updateValues();
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
</script>
@endsection
