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
    .weekly-wage-highlight {
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .weekly-wage-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .calculated-value:read-only {
        background-color: #f8f9fa;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    
    .daily-breakdown {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 15px;
        border-radius: 8px;
        margin-top: 15px;
        color: white;
    }
    
    .daily-breakdown h6 {
        margin-bottom: 15px;
        font-weight: bold;
        color: white;
    }
    
    .daily-breakdown .form-label {
        color: white;
        opacity: 0.9;
    }
    
    .daily-breakdown input {
        background-color: rgba(255,255,255,0.9);
        font-weight: bold;
    }
    
    .highlight-field {
        background-color: #e8f4f8 !important;
        font-weight: bold;
    }
    
    /* Added for better debugging */
    .error-message {
        color: red;
        font-size: 12px;
        margin-top: 5px;
    }
</style>
@endsection

@section('content')
@php
    $w = $weeklyFormPrefill ?? [];
@endphp
<!-- PAGE HEADER -->
<div class="page-header d-xl-flex d-block">
    <div class="page-leftheader">
        <div class="page-title">Add Weekly Wage Payroll</div>
    </div>
</div>

<form action="{{ route('save.weekly.daily.wise') }}" method="post" id="smform">
    @csrf
    <div class="row">
        <!-- Weekly Wage Master Card -->
        <div class="card mb-4 weekly-wage-highlight">
            <div class="card-body">
                <h4 style="text-align: center; font-weight: bold; margin-top: 20px;">
                    <span>Employee Name : {{ $employee->emp_full_name .' - '. $employee->emp_code }} </span>
                </h4>
                <h4 class="card-title">Weekly Wage Master</h4>

                <input type="hidden" name="emp_id" value="{{ $emp_actual_id }}">
                <input type="hidden" name="business_id" value="{{ $business_id }}">
                <input type="hidden" name="wage_type" value="weekly">
                {{-- Kept for save / proration logic; not shown in UI --}}
                <input type="hidden" name="es_weekly_gross" id="es_weekly_gross" value="{{ $w['weekly_gross'] ?? '' }}">
                <input type="hidden" name="es_weekly_ctc" id="es_weekly_ctc" value="{{ $w['weekly_ctc'] ?? '' }}">
                @php
                    $weeklyPfValidation = isset($emp_salary)
                        ? (int) ($emp_salary->es_pf_validation_enabled ?? 1)
                        : 1;
                    $weeklyEsicValidation = isset($emp_salary)
                        ? (int) ($emp_salary->es_esic_validation_enabled ?? 1)
                        : 1;
                @endphp
                <input type="hidden" name="pf_validation_enabled" id="pf_validation_enabled" value="{{ $weeklyPfValidation }}">
                <input type="hidden" name="esic_validation_enabled" id="esic_validation_enabled" value="{{ $weeklyEsicValidation }}">

                <div id="salaryInfoCardBody" class="card-body collapse show">
                    {{-- Working days fixed at 1 --}}
                    <input type="hidden" name="working_days" id="working_days" value="1">

                    <div class="row align-items-end g-2 g-lg-3">
                        <!-- Per Day Wage -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Per Day Wage <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control numericInput" name="es_per_day_wage" id="es_per_day_wage"
                                placeholder="0"
                                value="{{ $w['per_day_wage'] ?? ($emp_salary->es_perday_salary ?? '') }}"
                                maxlength="10" required>
                        </div>

                        <!-- Per Day Gross -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Per Day Gross <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control numericInput" name="per_day_gross" id="per_day_gross"
                                placeholder="0"
                                value="{{ $w['per_day_gross'] ?? '' }}"
                                maxlength="10" required>
                        </div>

                        <!-- Per Day CTC -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Per Day CTC
                            </label>
                            <input type="text" class="form-control numericInput" name="per_day_ctc" id="per_day_ctc"
                                placeholder="0" value="{{ $w['per_day_ctc'] ?? '' }}" readonly>
                        </div>

                        <!-- W.E.F (beside Per Day CTC) -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-calendar-alt"></i> W.E.F <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="wef" class="form-control"
                                value="{{ \Carbon\Carbon::parse($smhistoryLastData?->wef ?? now())->format('Y-m-d') }}">
                            <input type="hidden" name="financial_year"
                                value="{{ $financialYears->firstWhere('fy_is_current', 1)?->fy_id }}">
                        </div>

                        <!-- Remark (same row, after W.E.F) -->
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
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
                <div class="card mb-4 weekly-wage-card">
                    <div class="card-header">
                        <h5>
                            Earnings (Weekly Basis)
                            <button class="btn btn-link float-right testq" type="button" data-toggle="collapse"
                                data-target="#earningsCardBody" aria-expanded="false">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                        </h5>
                        <h5 id="earningsHeader" class="float-right colShowData">
                            <label>Total Earnings (= Per Day Gross) : <span id="total_earnings_head"></span> </label>
                        </h5>
                    </div>
                    <div id="earningsCardBody" class="card-body collapse show">
                        <div class="row">
                            @foreach ($earnings as $earning)
                            @if($earning->sa_is_active)
                            @php
                                $savedEarning = $emp_earnings_with_ids[$earning->sa_id] ?? null;
                                $savedEarningValue = $savedEarning['amount'] ?? '';
                                $savedEarningId = $savedEarning['id'] ?? '';
                                $wmPref = $w['wm'] ?? 4.33;

                                if ($savedEarningValue === '' || $savedEarningValue === null) {
                                    if (!empty($smhistoryLastData)) {
                                        $earningTypeId = $earning->sa_earning_type_id;
                                        if ($earningTypeId == 360 && !empty($smhistoryLastData->sm_basic)) {
                                            $savedEarningValue = $smhistoryLastData->sm_basic;
                                        } elseif ($earningTypeId == 361 && !empty($smhistoryLastData->sm_hra)) {
                                            $savedEarningValue = $smhistoryLastData->sm_hra;
                                        } elseif ($earningTypeId == 362 && !empty($smhistoryLastData->sm_dear_allow)) {
                                            $savedEarningValue = $smhistoryLastData->sm_dear_allow;
                                        } elseif ($earningTypeId == 363 && !empty($smhistoryLastData->sm_conv_allow)) {
                                            $savedEarningValue = $smhistoryLastData->sm_conv_allow;
                                        } elseif ($earningTypeId == 364) {
                                            if (\Illuminate\Support\Str::contains(strtoupper($earning->sa_title), 'EDUCATION') && !empty($smhistoryLastData->sm_edu_allow)) {
                                                $savedEarningValue = $smhistoryLastData->sm_edu_allow;
                                            }
                                            if (\Illuminate\Support\Str::contains(strtoupper($earning->sa_title), 'MEDICAL') && !empty($smhistoryLastData->sm_med_allow)) {
                                                $savedEarningValue = $smhistoryLastData->sm_med_allow;
                                            }
                                            if ($savedEarningValue === '' && !empty($smhistoryLastData->sm_other_allow)) {
                                                $savedEarningValue = $smhistoryLastData->sm_other_allow;
                                            }
                                        }
                                    }
                                    if (($savedEarningValue === '' || $savedEarningValue === null) && isset($emp_salary->es_id)) {
                                        $earningTypeId = $earning->sa_earning_type_id;
                                        if ($earningTypeId == 360) {
                                            $savedEarningValue = $w['weekly_basic'] ?? round(($emp_salary->es_base_salary ?? 0) / $wmPref, 2);
                                        } elseif ($earningTypeId == 361) {
                                            $savedEarningValue = $w['weekly_hra'] ?? '';
                                        } elseif ($earningTypeId == 363) {
                                            $savedEarningValue = $w['weekly_conveyance'] ?? '';
                                        } elseif ($earningTypeId == 364) {
                                            if (\Illuminate\Support\Str::contains(strtoupper($earning->sa_title), 'MEDICAL')) {
                                                $savedEarningValue = $w['weekly_medical'] ?? '';
                                            } elseif ($savedEarningValue === '' || $savedEarningValue === null) {
                                                $savedEarningValue = $w['weekly_special'] ?? '';
                                            }
                                        }
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

                                <input type="hidden" name="earning_ids[{{ $earning->sa_id }}]" value="{{ $savedEarningId }}">

                                <input type="text" class="form-control calculated-value numericInput special-364 manual-earning"
                                    name="earnings[{{ $earning->sa_id }}]"
                                    value="{{ $savedEarningValue }}"
                                    data-sa_id="{{ $earning->sa_id }}"
                                    data-calculation-type="{{ $earning->sa_calculation_type }}"
                                    data-threshold="{{ $earning->sa_threshold_value }}"
                                    data-percentage="{{ $earning->sa_percentage ?? 0 }}"
                                    data-fixed="{{ $earning->sa_fixed_value ?? 0 }}"
                                    data-earning-type-id="{{ $earning->sa_earning_type_id }}"
                                    data-payroll-heading-id="{{ $earning->sa_payroll_heading_id }}"
                                    id="earning_{{ $earning->sa_id }}" maxlength="9" placeholder="0">
                            </div>
                            @endif
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Total Earnings (= Per Day Gross)</label>
                                <input type="text" class="form-control numericInput" name="total_employee_earning"
                                    id="total_earnings" placeholder="0" value="{{ $w['total_employee_earning'] ?? '' }}" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Take Home (Net Pay) <span class="text-muted small">(Per Day Gross − Employee Deductions)</span></label>
                                <input type="text" class="form-control numericInput" name="monthly_net_salary"
                                    id="monthly_net_salary" placeholder="0" value="{{ $w['monthly_net_salary'] ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions (Employee) -->
            <div class="col-md-3">
                <div class="card mb-4 weekly-wage-card">
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
                        </div>
                    </div>
                    <div id="deductionsCardBody" class="card-body collapse show">
                        <div class="row">
                            @foreach ($deductions as $deduction)
                            @php
                            $typeId = $deduction->std_deduction_type_id;
                            $isPF = $typeId == 351;
                            $isESIC = $typeId == 352;
                            $savedDeductionValue = $emp_deductions[$typeId] ?? '';
                            @endphp
                            <div class="col-md-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">
                                        {{ $deduction->deduction_type_name }}
                                        <i class="fa fa-info-circle text-primary ms-1" title="{{ $deduction->std_description ?? '' }}"></i>
                                    </label>
                                </div>
                                <input type="text" class="form-control deduction-value numericInput deduction-input-{{ $typeId }}"
                                    name="deductions[{{ $deduction->std_id }}]"
                                    value="{{ $savedDeductionValue }}"
                                    data-threshold="{{ $deduction->std_threshold }}"
                                    data-employee-rate="{{ $deduction->std_employee_contri_rate_amount }}"
                                    data-deduction-type-id="{{ $typeId }}"
                                    maxlength="9" placeholder="0" readonly
                                    @if(($isPF && (int) ($employee->emp_is_pf_enabled ?? 0) !== 120) ||
                                        ($isESIC && (int) ($employee->emp_esic_limit ?? 0) !== 120))
                                        style="display: none;"
                                    @endif>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Total Deductions</label>
                                <input type="text" class="form-control numericInput" name="total_employee_deduction"
                                    id="total_employee_deduction" placeholder="0" value="{{ $w['total_employee_deduction'] ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions (Employer) -->
            <div class="col-md-3">
                <div class="card mb-4 weekly-wage-card">
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
                                    @if(($isPF && (int) ($employee->emp_is_pf_enabled ?? 0) !== 120) ||
                                        ($isESIC && (int) ($employee->emp_esic_limit ?? 0) !== 120))
                                        style="display: none;"
                                    @endif>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Total Deductions</label>
                                <input type="text" class="form-control numericInput" name="total_employer_deduction"
                                    id="total_employer_deduction" placeholder="0" value="{{ $w['total_employer_deduction'] ?? '' }}" readonly>
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
                    <span><strong>Remark:</strong> {{ $smhistoryLastData->sm_remark ?? '' }}</span>
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
                                <th>Per Day Gross</th>
                                <th>Per Day CTC</th>
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
                            $workingDays = $smhist->sm_working_days ?? 1;
                            $perDayGross = $smhist->sm_per_day_gross ?? ($smhist->sm_gross_pay / $workingDays);
                            $perDayCTC = $smhist->sm_per_day_ctc ?? ($smhist->sm_weekly_ctc / $workingDays);
                            @endphp
                            <tr>
                                <td class="arrow-up">↑</td>
                                <td>{{ $smhist->financial_years->fy_year }}</td>
                                <td>{{ $formattedDate1 }}</td>
                                <td>{{ $formattedDate2 }}</td>
                                <td>{{ $smhist->sm_per_day_wage ?? ($smhist->sm_per_day_gross ?? 'N/A') }}</td>
                                <td>₹ {{ number_format($perDayGross, 2) }}</td>
                                <td>₹ {{ number_format($perDayCTC, 2) }}</td>
                                <td>
                                    <span class="simple-icon openBtn" style="cursor: pointer"
                                        data-from_date="{{ $formattedDate1 }}"
                                        data-to_date="{{ $formattedDate2 }}"
                                        data-per_day_wage="{{ $smhist->sm_per_day_wage }}"
                                        data-working_days="{{ $smhist->sm_working_days }}"
                                        data-per_day_gross="{{ $perDayGross }}"
                                        data-per_day_ctc="{{ $perDayCTC }}"
                                        data-basic="{{ $smhist->sm_basic }}"
                                        data-hra="{{ $smhist->sm_hra }}"
                                        data-conveyance="{{ $smhist->sm_conv_allow ?? '0' }}"
                                        data-medical="{{ $smhist->sm_med_allow ?? '0' }}"
                                        data-other_allowance="{{ $smhist->sm_other_allow ?? '0' }}"
                                        data-total_earning="{{ $smhist->sm_total_earning }}"
                                        data-net_pay="{{ $smhist->sm_net_pay }}"
                                        data-pf_employee="{{ $smhist->sm_employee_epf ?? '0' }}"
                                        data-esic_employee="{{ $smhist->sm_employee_esic ?? '0' }}"
                                        data-professional_tax="0"
                                        data-pf_employer="{{ $smhist->sm_employer_epf ?? '0' }}"
                                        data-esic_employer="{{ $smhist->sm_employer_esic ?? '0' }}"
                                        data-employee_deductions="{{ $smhist->sm_employee_total_ded ?? '0' }}"
                                        data-employer_deductions="{{ $smhist->sm_employer_total_ded ?? '0' }}">
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
            <button id="saveButton" type="submit" class="btn btn-outline-info btn-lg cus-save">
                Save
            </button>
        </div>

        <!-- Preview Modal -->
        <div class="modal fade" id="employeeSalaryHistoryModal" tabindex="-1" role="dialog" aria-labelledby="employeeSalaryHistoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header border-0">
                        <h4 class="modal-title" id="modalTitle">Weekly Wage Preview</h4>
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
                                    <span class="avatar avatar-xxl brround" id="empAvtar" style="background-image: url('{{ isset($employee) && $employee->emp_profile_photo ? $employee->emp_profile_photo : asset('assets/imgs/user.png') }}');"></span>
                                </div>
                                <div class="col-11 text-left">
                                    <h1 class="px-4 pt-6 mt-6 mb-0"> {{ $employee->emp_full_name }} </h1>
                                    <span class="font-weight-semibold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $employee->fh_designation->dg_name ?? '' }}</span><br>
                                    <span class="font-weight-semibold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $employee->emp_email }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody class="text-center">
                                        <tr>
                                            <td rowspan="2" class="text-blue employer-cell"><h4 class="m-0 vertical-text"></h4></td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Per Day Wage</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold" id="perDayWage"></td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Per Day Gross</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold text-success" id="perDayGross"></td>
                                         </tr>
                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Per Day CTC</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold text-primary" id="perDayCTC"></td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Take Home (Net)</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold" id="takeHomeNetModal"></td>
                                         </tr>
                                        <tr style="border-top: 1px solid #ddd;"><td colspan="7"></td></tr>
                                        <tr>
                                            <td class="text-blue employer-cell"><h4 class="m-0 vertical-text">Weekly Summary</h4></td>
                                            <td class="py-2 px-0"><span class="w-50">Basic Salary</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="basicSalary"></td>
                                            <td class="py-2 px-0"><span class="w-50">Take Home (Net)</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="perDayNet"></td>
                                         </tr>
                                        <tr style="border-top: 1px solid #ddd;"><td colspan="7"></td></tr>
                                        <tr>
                                            <td rowspan="3" class="text-blue employer-cell"><h4 class="m-0 vertical-text">Components</h4></td>
                                            <td class="py-2 px-0"><span class="w-50">HRA</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empHRA"></td>
                                            <td class="py-2 px-0"><span class="w-50">Conveyance</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empConvAllow"></td>
                                         </tr>
                                        <tr>
                                            <td class="py-2 px-0"><span class="w-50">Medical Allowance</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empMedicalAllow"></td>
                                            <td class="py-2 px-0"><span class="w-50">Other Allowance</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empOtherAllow"></td>
                                         </tr>
                                        <tr style="border-top: 1px solid #ddd;"><td colspan="7"></td></tr>
                                        <tr>
                                            <td rowspan="2" class="text-blue employer-cell"><h4 class="m-0 vertical-text">Employee</h4></td>
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
                                        <tr style="border-top: 1px solid #ddd;"><td colspan="7"></td></tr>
                                        <tr>
                                            <td rowspan="2" class="text-blue employer-cell"><h4 class="m-0 vertical-text">Employer</h4></td>
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
                                        <tr style="border-top: 1px solid #ddd;"><td colspan="7"></td></tr>
                                        <tr>
                                            <td rowspan="2" class="text-blue employer-cell"><h4 class="m-0 vertical-text"></h4></td>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total Earning</span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0 font-weight-semibold" id="empEarnings"></td>
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
                        <button type="button" class="btn btn-outline-danger cancel" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Employee PF/ESIC Status (120 = enabled, same as daily salary master)
    let isPFEnabled = {{ (int) ($employee->emp_is_pf_enabled ?? 0) }};
    let isESICEnabled = {{ (int) ($employee->emp_esic_limit ?? 0) }};
    let pfValidationEnabled = parseInt($('#pf_validation_enabled').val(), 10);
    if (isNaN(pfValidationEnabled)) pfValidationEnabled = 1;
    let esicValidationEnabled = parseInt($('#esic_validation_enabled').val(), 10);
    if (isNaN(esicValidationEnabled)) esicValidationEnabled = 1;
    
    // ==================== HELPER FUNCTIONS ====================
    
    // Format number to 2 decimal places
    function formatNumber(value) {
        if (!value || isNaN(value)) return '0.00';
        return parseFloat(value).toFixed(2);
    }
    
    // Parse numeric input value
    function parseNumericValue(value) {
        if (!value) return 0;
        return parseFloat(value.toString().replace(/,/g, '')) || 0;
    }
    
    // ==================== CORE CALCULATION FUNCTIONS ====================
    
    // 1. Calculate Per Day Gross from Per Day Wage
    function calculatePerDayGrossFromWage() {
        let perDayWage = parseNumericValue($('#es_per_day_wage').val());
        if (perDayWage > 0) {
            $('#per_day_gross').val(formatNumber(perDayWage));
        }
        return parseNumericValue($('#per_day_gross').val());
    }
    
    // 2. Weekly gross hidden = per day gross (working days fixed at 1)
    function calculateWeeklyGross() {
        let perDayGross = parseNumericValue($('#per_day_gross').val());
        $('#es_weekly_gross').val(formatNumber(perDayGross));
        return perDayGross;
    }
    
    // 3. Total earnings always equals per day gross (saved as total_employee_earning)
    function syncTotalEarningsToPerDayGross() {
        let perDayGross = parseNumericValue($('#per_day_gross').val());
        $('#total_earnings').val(formatNumber(perDayGross));
        $('#total_earnings_head').text(formatNumber(perDayGross));
        return perDayGross;
    }

    // Basic is dynamic earning component with type id 360.
    function getWeeklyBasicFromEarnings() {
        let basic = 0;
        $('.manual-earning').each(function() {
            let earningTypeId = parseInt($(this).data('earning-type-id')) || 0;
            if (earningTypeId === 360) {
                basic += parseNumericValue($(this).val());
            }
        });
        return basic;
    }
    
    // 4. Calculate Employee Deductions (PF on weekly basic; ESIC % from statutory_deductions, base = per day gross)
    function calculateEmployeeDeductions() {
        let basicSalary = getWeeklyBasicFromEarnings();
        let weeklyGross = parseNumericValue($('#es_weekly_gross').val());
        let perDayGross = parseNumericValue($('#per_day_gross').val());
        let totalEmpDed = 0;
        const isPFChecked = pfValidationEnabled === 1 && isPFEnabled === 120;
        const isESICChecked = esicValidationEnabled === 1 && isESICEnabled === 120;
        
        $('.deduction-value').each(function() {
            let rate = parseNumericValue($(this).data('employee-rate'));
            let typeId = parseInt($(this).data('deduction-type-id')) || 0;
            let threshold = parseNumericValue($(this).data('threshold'));
            let contribution = 0;
            
            if (typeId === 351) { // PF - on Basic (weekly)
                if (isPFChecked) {
                    if (basicSalary > 0) {
                        contribution = (basicSalary * rate) / 100;
                        if (threshold > 0 && basicSalary > threshold) {
                            contribution = (threshold * rate) / 100;
                        }
                    }
                }
            } else if (typeId === 352) { // ESIC employee: per day gross × rate% (rate from statutory_deductions for this business)
                if (isESICChecked && perDayGross > 0 && rate > 0) {
                    let withinCeiling = true;
                    if (threshold > 0 && weeklyGross > 0) {
                        withinCeiling = weeklyGross <= threshold;
                    }
                    if (withinCeiling) {
                        contribution = (perDayGross * rate) / 100;
                    }
                }
            } else { // Other deductions (Professional Tax, etc.)
                if (basicSalary > 0) {
                    contribution = (basicSalary * rate) / 100;
                }
            }
            
            $(this).val(formatNumber(contribution));
            totalEmpDed += contribution;
        });
        
        $('#total_employee_deduction').val(formatNumber(totalEmpDed));
        $('#total_emp_ded').text(formatNumber(totalEmpDed));
        return totalEmpDed;
    }
    
    // 5. Calculate Employer Deductions
    function calculateEmployerDeductions() {
        let basicSalary = getWeeklyBasicFromEarnings();
        let weeklyGross = parseNumericValue($('#es_weekly_gross').val());
        let perDayGross = parseNumericValue($('#per_day_gross').val());
        let totalEmployerDed = 0;
        const isPFChecked = pfValidationEnabled === 1 && isPFEnabled === 120;
        const isESICChecked = esicValidationEnabled === 1 && isESICEnabled === 120;
        
        $('.employer-deduction-value').each(function() {
            let rate = parseNumericValue($(this).data('employer-rate'));
            let typeId = parseInt($(this).data('deduction-type-id')) || 0;
            let threshold = parseNumericValue($(this).data('threshold'));
            let contribution = 0;
            
            if (typeId === 351) { // PF Employer - on Basic (weekly)
                if (isPFChecked && basicSalary > 0) {
                    contribution = (basicSalary * rate) / 100;
                    if (threshold > 0 && basicSalary > threshold) {
                        contribution = (threshold * rate) / 100;
                    }
                }
            } else if (typeId === 352) { // ESIC employer: per day gross × employer rate% (from statutory_deductions)
                if (isESICChecked && perDayGross > 0 && rate > 0) {
                    let withinCeiling = true;
                    if (threshold > 0 && weeklyGross > 0) {
                        withinCeiling = weeklyGross <= threshold;
                    }
                    if (withinCeiling) {
                        contribution = (perDayGross * rate) / 100;
                    }
                }
            } else { // Other employer deductions
                if (basicSalary > 0) {
                    contribution = (basicSalary * rate) / 100;
                }
            }
            
            $(this).val(formatNumber(contribution));
            totalEmployerDed += contribution;
        });
        
        $('#total_employer_deduction').val(formatNumber(totalEmployerDed));
        $('#total_employer_deds').text(formatNumber(totalEmployerDed));
        return totalEmployerDed;
    }
    
    // 6. Take home = Per Day Gross − Employee deductions (single net field)
    function calculateNetPay() {
        let gross = parseNumericValue($('#per_day_gross').val());
        let totalEmpDed = parseNumericValue($('#total_employee_deduction').val());
        let netPay = gross - totalEmpDed;
        $('#monthly_net_salary').val(formatNumber(netPay));
        return netPay;
    }
    
    // 7. Per day CTC = Total earnings (= per day gross) + Employer deductions
    function calculatePerDayCTC() {
        let totalEarnings = parseNumericValue($('#total_earnings').val());
        let totalEmployerDed = parseNumericValue($('#total_employer_deduction').val());
        let perDayCtc = totalEarnings + totalEmployerDed;
        $('#per_day_ctc').val(formatNumber(perDayCtc));
        $('#es_weekly_ctc').val(formatNumber(perDayCtc));
        return perDayCtc;
    }
    
    // 9. Main Calculate Function - Calls all calculations in correct order
    function calculateAll() {
        console.log('=== Calculating All Values ===');
        
        // Step 1: Calculate Per Day Gross from Wage (if Per Day Gross is empty)
        let perDayGross = parseNumericValue($('#per_day_gross').val());
        let perDayWage = parseNumericValue($('#es_per_day_wage').val());
        if (perDayGross === 0 && perDayWage > 0) {
            $('#per_day_gross').val(formatNumber(perDayWage));
        }
        
        // Step 2: Hidden weekly gross = per day gross
        calculateWeeklyGross();
        
        // Step 3: Total earnings = per day gross
        syncTotalEarningsToPerDayGross();
        
        // Step 4: Calculate Employee Deductions
        calculateEmployeeDeductions();
        
        // Step 5: Calculate Employer Deductions
        calculateEmployerDeductions();
        
        // Step 6: Take home (net)
        calculateNetPay();
        
        // Step 7: Per day CTC = total earnings + employer deductions
        calculatePerDayCTC();
        
        console.log('Calculation complete — Per Day CTC:', $('#per_day_ctc').val());

        applyPfEsicDeductionVisibility();
    }

    // Match daily: hide PF/ESIC rows when employee does not have them enabled; zero values when hidden
    function applyPfEsicDeductionVisibility() {
        const showPf = isPFEnabled === 120;
        const showEsic = isESICEnabled === 120;

        document.querySelectorAll('.deduction-value[data-deduction-type-id="351"], .employer-deduction-value[data-deduction-type-id="351"]').forEach(function(el) {
            el.style.display = showPf ? '' : 'none';
            if (!showPf) {
                el.value = '0.00';
            }
        });
        document.querySelectorAll('.deduction-value[data-deduction-type-id="352"], .employer-deduction-value[data-deduction-type-id="352"]').forEach(function(el) {
            el.style.display = showEsic ? '' : 'none';
            if (!showEsic) {
                el.value = '0.00';
            }
        });

        if (!showPf || !showEsic) {
            calculateEmployeeDeductions();
            calculateEmployerDeductions();
            calculateNetPay();
            calculatePerDayCTC();
        }
    }
    
    // ==================== FORM VALIDATION BEFORE SUBMIT ====================
    $('#smform').on('submit', function(e) {
        // Validate required fields
        let perDayWage = $('#es_per_day_wage').val();
        let remark = $('#remark').val();
        
        if (!perDayWage || parseFloat(perDayWage) <= 0) {
            e.preventDefault();
            alert('Please enter Per Day Wage');
            $('#es_per_day_wage').focus();
            return false;
        }
        
        if (!remark || remark.trim() === '') {
            e.preventDefault();
            alert('Please enter Remark');
            $('#remark').focus();
            return false;
        }
        
        // Show loading state on button
        let saveBtn = $('#saveButton');
        saveBtn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        saveBtn.prop('disabled', true);
        
        return true;
    });
    
    // ==================== EVENT LISTENERS ====================
    
    // When Per Day Wage changes - Update Per Day Gross
    $('#es_per_day_wage').on('input', function() {
        let perDayWage = parseNumericValue($(this).val());
        $('#per_day_gross').val(formatNumber(perDayWage));
        calculateAll();
    });
    
    // When Per Day Gross changes directly
    $('#per_day_gross').on('input', function() {
        calculateAll();
    });
    
    // When any manual earning changes (PF/basic still from components; totals follow per day gross)
    $(document).on('input', '.manual-earning', function() {
        calculateAll();
    });
    
    // Format numeric inputs as user types
    $(document).on('input', '.numericInput', function() {
        let value = $(this).val();
        value = value.replace(/[^0-9.]/g, '');
        let parts = value.split('.');
        if (parts.length > 2) value = parts[0] + '.' + parts.slice(1).join('');
        $(this).val(value);
    });
    
    // ==================== PREVIEW MODAL ====================
    $(document).on('click', '.openBtn', function() {
        $('#employeeSalaryHistoryModal').modal('show');
        $('#from_date').text($(this).data('from_date'));
        $('#to_date').text($(this).data('to_date'));
        $('#perDayWage').text('₹ ' + formatNumber($(this).data('per_day_wage')));
        $('#perDayGross').text('₹ ' + formatNumber($(this).data('per_day_gross')));
        $('#perDayCTC').text('₹ ' + formatNumber($(this).data('per_day_ctc')));
        let netPay = parseNumericValue($(this).data('net_pay'));
        $('#takeHomeNetModal').text('₹ ' + formatNumber(netPay));
        $('#basicSalary').text('₹ ' + formatNumber($(this).data('basic')));
        $('#perDayNet').text('₹ ' + formatNumber(netPay));
        
        $('#empHRA').text('₹ ' + formatNumber($(this).data('hra')));
        $('#empConvAllow').text('₹ ' + formatNumber($(this).data('conveyance')));
        $('#empMedicalAllow').text('₹ ' + formatNumber($(this).data('medical')));
        $('#empOtherAllow').text('₹ ' + formatNumber($(this).data('other_allowance')));
        $('#empEpf').text('₹ ' + formatNumber($(this).data('pf_employee')));
        $('#empEsic').text('₹ ' + formatNumber($(this).data('esic_employee')));
        $('#empProfTax').text('₹ ' + formatNumber($(this).data('professional_tax')));
        $('#empDeductions').text('₹ ' + formatNumber($(this).data('employee_deductions')));
        $('#employerEpf').text('₹ ' + formatNumber($(this).data('pf_employer')));
        $('#employerEsic').text('₹ ' + formatNumber($(this).data('esic_employer')));
        $('#employerOther').text('₹ ' + formatNumber($(this).data('other_contributions')));
        $('#employerTDeductions').text('₹ ' + formatNumber($(this).data('employer_deductions')));
        $('#empEarnings').text('₹ ' + formatNumber($(this).data('total_earning')));
        $('#empNetPay').text('₹ ' + formatNumber($(this).data('net_pay')));
    });
    
    // ==================== INITIAL CALCULATION ====================
    calculateAll();
    
    // ==================== COLLAPSE HEADERS ====================
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
});
</script>
@endsection