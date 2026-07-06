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

        .employer-cell,
        .employee-cell {
            background-color: #f0f8ff;
            /* Light Blue */
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
            color: #007bff;
            /* Blue */
        }

        .feather-eye:hover {
            background-color: darkblue;
            border-radius: 4px;
            /* optional: for better appearance */
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
                    <h4 style="text-align: center; font-weight: bold !important; margin-top: 20px;">
                        <span>Employee Name : {{ $employee->emp_full_name . ' - ' . $employee->emp_code }} </span>
                    </h4>
                    <h4 class="card-title">
                        Salary Master
                    </h4>

                    {{-- <!-- Add this right after the "Salary Master" heading -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label mb-0 mt-2">
                            <i class="fa fa-calendar"></i> Payment Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" name="payment_type" id="payment_type" required>
                            <option value="monthly">Monthly</option>
                            <option value="daily">Daily</option>
                        </select>
                    </div>

                    <div class="col-md-2" id="working_days_field" style="display: none;">
                        <label class="form-label mb-0 mt-2">
                            <i class="fa fa-calendar-day"></i> Working Days <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" name="working_days" id="working_days" placeholder="26" min="1"
                            max="31" value="26">
                    </div>
                </div> --}}

                    <!-- Add this after the Monthly Gross field or wherever you want the checkbox -->
                    {{-- <div class="col-md-2">
                    <label class="form-label mb-0 mt-2">
                        <input type="checkbox" id="esicValidationToggle" checked>
                        Enable ESIC Validation
                    </label>
                </div> --}}

                    <input type="hidden" name="emp_id" value="{{ $emp_actual_id }}">
                    <input type="hidden" name="business_id" value="{{ $business_id }}">
                    <div id="salaryInfoCardBody" class="card-body collapse show">
                        <div class="row">
                            <div class="row align-items-center">
                                <!-- Monthly Gross -->
                                <div class="col-md-2">
                                    <label class="form-label mb-0 mt-2">
                                        <i class="fa fa-money-bill"></i> Monthly Gross <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control numericInput" name="es_monthly_gross"
                                        id="es_monthly_gross" placeholder="0"
                                        value="{{ !empty($smhistoryLastData->sm_gross_pay) ? $smhistoryLastData->sm_gross_pay : '' }}"
                                        maxlength="10" required>
                                </div>

                                {{-- <div class="col-md-2">
                                <label class="form-label mb-0 mt-2" id="gross_label">
                                    <i class="fa fa-money-bill"></i> <span id="gross_text">Monthly Gross</span> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control numericInput" name="es_monthly_gross"
                                    id="es_monthly_gross" placeholder="0"
                                    value="{{ !empty($smhistoryLastData->sm_gross_pay) ? $smhistoryLastData->sm_gross_pay : '' }}"
                                    maxlength="10" required>
                                <small class="text-muted" id="per_day_rate" style="display: none;">Per Day: ₹<span id="daily_rate">0</span></small>
                            </div>
 --}}

                                <!-- Annual Gross -->
                                <div class="col-md-2">
                                    <label class="form-label mb-0 mt-2">
                                        <i class="fa fa-money-bill"></i> Annual Gross <span class="text-danger">*</span>
                                    </label>


                                    <input type="text" class="form-control numericInput" name="annual_gross"
                                        id="annual_gross" placeholder="0" maxlength="10"
                                        value="{{ !empty($smhistoryLastData->sm_annual_gross) ? $smhistoryLastData->sm_annual_gross : '' }}"
                                        oninput="calculateCTCAnnual('annual')" required>

                                </div>

                                <!-- Monthly CTC -->
                                <div class="col-md-2">
                                    <label class="form-label mb-0 mt-2">
                                        <i class="fa fa-money-bill"></i> Monthly CTC <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control numericInput" name="es_monthly_ctc"
                                        id="es_monthly_ctc" placeholder="0" maxlength="10"
                                        value="{{ old('es_monthly_ctc', $emp_salary->es_monthly_ctc ?? '') }}" required>
                                </div>

                                <!-- Annual CTC -->
                                <div class="col-md-2">
                                    <label class="form-label mb-0 mt-2">
                                        <i class="fa fa-calendar-alt"></i> Annual CTC <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control numericInput" name="es_annual_ctc"
                                        id="es_annual_ctc" placeholder="0" maxlength="10"
                                        value="{{ $emp_salary->es_annual_ctc ?? '' }}" required>
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
                                        <i class="fa fa-calendar-alt"></i> Remark <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="remark" id="remark"
                                        placeholder="Remark" value="{{ $smhistoryLastData->sm_remark ?? '' }}"
                                        pattern="^[A-Za-z][A-Za-z0-9\s]*$"
                                        title="Only letters and numbers allowed. Must start with a letter." required>
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
                                <button class="btn btn-link float-right testq" type="button" data-toggle="collapse"
                                    data-target="#earningsCardBody" aria-expanded="false" aria-controls="earningsCardBody">
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
                                    @if ($earning->sa_is_active)
                                        <div class="col-md-6 mb-3">
                                            <label>
                                                <i class="fa fa-dollar-sign"></i> {{ $earning->sa_title }}
                                                <span class="text-danger">*</span>
                                                <i class="fa fa-info-circle text-primary ms-1 earning-title"
                                                    data-bs-toggle="tooltip" data-bs-placement="top">
                                                </i>
                                            </label>
                                            <input type="text"
                                                class="form-control calculated-value numericInput special-364"
                                                name="earnings[{{ $earning->sa_id }}]" value=""
                                                data-sa_id="{{ $earning->sa_id }}"
                                                data-calculation-type="{{ $earning->sa_calculation_type }}"
                                                data-threshold="{{ $earning->sa_threshold_value }}"
                                                data-percentage="{{ $earning->sa_percentage ?? 0 }}"
                                                data-fixed="{{ $earning->sa_fixed_value ?? 0 }}"
                                                data-earning-type-id="{{ $earning->sa_earning_type_id }}"
                                                data-payroll-heading-id="{{ $earning->sa_payroll_heading_id }}"
                                                data-earning-cal-type-id="{{ $earning->sa_calculation_type }}"
                                                id="earning_{{ $earning->sa_id }}" maxlength="9" placeholder="0"
                                                required>


                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="row mt-3">
                                <!-- Other Allowance Row -->
                                <div class="col-md-6">
                                    <label>Other Allowance</label>
                                    <input type="text" class="form-control numericInput" name="es_rem_allowance"
                                        id="other_allowance" placeholder="Other Allowance"
                                        value="{{ !empty($smhistoryLastData->sm_other_allow) ? $smhistoryLastData->sm_other_allow : '' }}"
                                        maxlength="10" required readonly>
                                    <small id="oAllowError" class="text-danger" style="display: none;"></small>
                                </div>
                                <!-- Total Earnings Row -->
                                <div class="col-md-6">
                                    <label>Total Earnings</label>
                                    <input type="text" class="form-control numericInput" name="total_employee_earning"
                                        id="total_earnings"
                                        value="{{ !empty($smhistoryLastData->sm_total_earning) ? $smhistoryLastData->sm_total_earning : '' }}"
                                        maxlength="10" placeholder="Total Earnings" required readonly>
                                </div>
                            </div><br>
                            <div class="row">
                                {{-- <div class="col-md-6 mb-3">
                                <label>Gross Pay <span class="text-danger">*</span></label>
                                <input type="text" class="form-control numericInput" name="monthly_gross"
                                    id="monthly_gross" placeholder="0"
                                    value="{{ !empty($smhistoryLastData->sm_gross_pay) ? $smhistoryLastData->sm_gross_pay : '' }}"
                                    readonly maxlength="10" required>
                            </div> --}}
                                <div class="col-md-6 mb-3">
                                    <label>Take Home Salary(Net Pay) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control numericInput" name="monthly_net_salary"
                                        id="monthly_net_salary" placeholder="0"
                                        value="{{ !empty($smhistoryLastData->sm_net_pay) ? $smhistoryLastData->sm_net_pay : '' }}"
                                        maxlength="10" readonly required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deductions Section -->
                <div class="col-md-3">
                    <div class="card mb-4">
                        <div class="card-header">
                            <!-- Left Side: Heading -->
                            <h5 class="mb-0">
                                Deductions(Employee)
                                <button class="btn btn-link testq2" type="button" data-toggle="collapse"
                                    data-target="#deductionsCardBody" aria-expanded="false"
                                    aria-controls="deductionsCardBody">
                                    <i class="fa fa-chevron-down" style="line-height: 0.5;"></i>
                                </button>
                            </h5>

                            <!-- Middle + Right: Wrap in flex -->
                            <div class="d-flex align-items-center ms-auto">
                                <!-- Middle: Total Deduction Value -->
                                <h5 id="dedEmpHeader" class="mb-0 me-3">
                                    <label class="mb-0"><span id="total_emp_ded"></span></label>
                                </h5>

                                <!-- Right Side: Checkbox -->
{{--
                                @php
                                    $esicEnabled = isset($emp_salary)
                                        ? ($emp_salary->es_esic_validation_enabled
                                            ? 1
                                            : 0)
                                        : 1;
                                @endphp

                                <div>
                                    <input type="checkbox" id="esicValidationToggle" {{ $esicEnabled ? 'checked' : '' }}>

                                    <input type="hidden" name="esic_validation_enabled" id="esicValidationEnabledInput"
                                        value="{{ $esicEnabled }}">
                                </div> --}}


                            </div>
                        </div>
                        <div id="deductionsCardBody" class="card-body collapse show">
                            <div class="row">
                                @foreach ($deductions as $deduction)
                                    @php
                                        $typeId = $deduction->std_deduction_type_id;
                                        $isPF = $typeId == 351;
                                        $isESIC = $typeId == 352;
                                    @endphp
                                    <div class="col-md-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="mb-0">
                                                {{ $deduction->deduction_type_name }}
                                                <i class="fa fa-info-circle text-primary ms-1" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="">
                                                </i>
                                            </label>
                                            {{-- <div class="form-check form-switch m-0">
                                                <input class="form-check-input toggle-form" type="checkbox"
                                                    data-type="{{ $typeId }}"
                                                    {{ ($isPF && $employee->emp_is_pf_enabled != 121) || ($isESIC && $employee->emp_esic_limit != 121)
                                                        ? 'checked'
                                                        : '' }}>
                                            </div> --}}
                                            @php
                                                    $isPF = $deduction->std_deduction_type_id == 351;
                                                    $isESIC = $deduction->std_deduction_type_id == 352;

                                                    // ESIC toggle logic: default checked if value missing, but obey saved 0/1
                                                    $esicEnabled = isset($emp_salary)
                                                        ? (isset($emp_salary->es_esic_validation_enabled)
                                                            ? (int)$emp_salary->es_esic_validation_enabled
                                                            : 1)
                                                        : 1;

                                                    // PF toggle logic: default checked if value missing, but obey saved 0/1
                                                    $pfEnabled = isset($emp_salary)
                                                        ? (isset($emp_salary->es_pf_validation_enabled)
                                                            ? (int)$emp_salary->es_pf_validation_enabled
                                                            : 1)
                                                        : 1;
                                                @endphp

                                            {{-- ✅ Replace form-switch toggle --}}
                                          <div class="m-0">
                                                @if ($isESIC)
                                                    <input type="checkbox"
                                                        id="esicValidationToggle"
                                                        class="esic-validation-toggle"
                                                        data-type="{{ $typeId }}"
                                                        {{ $esicEnabled == 1 ? 'checked' : '' }}>

                                                    <input type="hidden"
                                                        name="esic_validation_enabled"
                                                        id="esicValidationEnabledInput"
                                                        value="{{ $esicEnabled }}">
                                                @elseif ($isPF)
                                                    <input type="checkbox"
                                                        id="pfValidationToggle"
                                                        class="pf-validation-toggle"
                                                        data-type="{{ $typeId }}"
                                                        {{ $pfEnabled == 1 ? 'checked' : '' }}>

                                                    <input type="hidden"
                                                        name="pf_validation_enabled"
                                                        id="pfValidationEnabledInput"
                                                        value="{{ $pfEnabled }}">
                                                @endif
                                            </div>


                                        </div>
                                        <input type="text"
                                            class="form-control deduction-value numericInput deduction-input-{{ $typeId }}"
                                            name="deductions[{{ $deduction->std_id }}]"
                                            value="{{ $emp_deductions[$typeId] ?? '' }}"
                                            data-threshold="{{ $deduction->std_threshold }}"
                                            data-employee-rate="{{ $deduction->std_employee_contri_rate_amount }}"
                                            data-employer-rate="{{ $deduction->std_employer_contri_rate_amount }}"
                                            data-deduction-cycle="{{ $deduction->std_deduction_cycle_id }}"
                                            data-deduction-type-id="{{ $typeId }}"
                                            data-sa-consider-for-pf="{{ $deduction->salaryAllowance->sa_consider_for_pf ?? 0 }}"
                                            maxlength="9" placeholder="Deduction Value" readonly
                                            @if (($isPF && $employee->emp_is_pf_enabled == 121) || ($isESIC && $employee->emp_esic_limit == 121)) style="display: none;" @endif>

                                    </div>
                                @endforeach
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label>Total Deductions</label>
                                    <input type="text" class="form-control numericInput"
                                        name="total_employee_deduction" id="total_employee_deduction" placeholder="0"
                                        maxlength="10"
                                        value="{{ !empty($smhistoryLastData->sm_employee_total_ded) ? $smhistoryLastData->sm_employee_total_ded : '' }}"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deductions(Employer) Section -->
                <div class="col-md-3">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>
                                Deductions(Employer)
                                <button class="btn btn-link float-right testq3" type="button" data-toggle="collapse"
                                    data-target="#deductionsCardBodyE" aria-expanded="false"
                                    aria-controls="deductionsCardBodyE">
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
                                    @php
                                        $typeId = $deduction->std_deduction_type_id;
                                        $isPF = $typeId == 351;
                                        $isESIC = $typeId == 352;
                                    @endphp
                                    <div class="col-md-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="mb-0">{{ $deduction->deduction_type_name }}
                                                <i class="fa fa-info-circle text-primary ms-1" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="">
                                                </i>
                                            </label>
                                            <div>

                                            </div>
                                        </div>

                                        <input type="text"
                                            class="form-control employer-deduction-value numericInput deduction2-input-{{ $typeId }}"
                                            name="employerDeductions[{{ $deduction->std_id }}]"
                                            value="{{ $emplyer_deductions[$deduction->std_deduction_type_id] ?? '' }}"
                                            data-threshold="{{ $deduction->std_threshold }}"
                                            data-employee-rate="{{ $deduction->std_employee_contri_rate_amount }}"
                                            data-employer-rate="{{ $deduction->std_employer_contri_rate_amount }}"
                                            data-deduction-cycle="{{ $deduction->std_deduction_cycle_id }}"
                                            data-deduction-type-id="{{ $deduction->std_deduction_type_id }}"
                                            data-sa-consider-for-pf="{{ $deduction->salaryAllowance->sa_consider_for_pf ?? 0 }}"
                                            maxlength="9" placeholder="Deduction Value" readonly
                                            @if (($isPF && $employee->emp_is_pf_enabled == 121) || ($isESIC && $employee->emp_esic_limit == 121)) style="display: none;" @endif>

                                    </div>
                                @endforeach
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label>Total Deductions</label>
                                    <input type="text" class="form-control numericInput"
                                        name="total_employer_deduction" id="total_employer_deduction"
                                        value="{{ !empty($smhistoryLastData->sm_employer_total_ded) ? $smhistoryLastData->sm_employer_total_ded : '' }}"
                                        maxlength="10" placeholder="0" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Buttons -->
            <!-- <div class="text-end" style="padding-bottom: 10px; padding-right: 175px;">
                <center><button type="button" class="btn btn-outline-danger btn-lg" onclick="resetForm()">Clear</button></center>
            </div> -->
            <!-- Summary Section -->
            @if (!empty($smhistory->first()->sm_monthly_ctc))
                {{-- <div class="card mb-4">
            <div class="card-header">
                <h5>
                    Summary
                    <!-- Add a button to toggle the collapse -->
                    <button class="btn btn-link float-right" type="button" data-toggle="collapse"
                        data-target="#summaryCardBody" aria-expanded="false" aria-controls="summaryCardBody">
                        <i class="fa fa-chevron-down" style="line-height: 0.5;"></i>
                    </button>
                </h5>
            </div> <br>
            <div>
                @php
                $formattedWef = '';
                if(!empty($smhistoryLastData)) {
                $wef = $smhistoryLastData->wef;
                $wef1 = Carbon::parse($wef);
                $formattedWef = $wef1->format('d-M-Y');
                }
                @endphp
                <h5 style="display: flex; gap: 40px; font-size: 14px; white-space: nowrap;">
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

                        @foreach ($smhistory as $smhist)
                        @php
                        $dateString1 = $smhist->wef;
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
                                    data-from_date="{{ $formattedDate1 }}" data-to_date="{{ $formattedDate2 }}"
                                    data-monthly="{{ $smhist->sm_monthly_ctc }}"
                                    data-annual="{{ $smhist->sm_annual_ctc }}" data-basic="{{ $smhist->sm_basic }}"
                                    data-hra="{{ $smhist->sm_hra }}" data-dear_allow="{{ $smhist->sm_dear_allow }}"
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
                                    data-net_pay="{{ $smhist->sm_net_pay }}">
                                    <i class="feather feather-eye"></i>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group ms-2">
                                    <!-- <a class="btn btn-outline-primary">
                                            <i class="fa fa-download"></i>
                                        </a> -->
                                    <a href="{{ route('export.smhistory', ['id' => $smhist->sm_id]) }}"
                                        class="btn btn-outline-primary">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div> --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>
                            Summary
                            <button class="btn btn-link" type="button" data-toggle="collapse"
                                data-target="#summaryCardBody" aria-expanded="false" aria-controls="summaryCardBody">
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
                            <span style="padding-right: 13px;"><strong>Emp. Name : </strong>
                                {{ $employee->emp_full_name }}</span>
                            <span style="padding-right: 13px;"><strong>DOJ : </strong> {{ $formattedDate }}</span>
                            <span style="padding-right: 13px;"><strong>WEF : </strong> {{ $formattedWef }}</span>
                            <span style="padding-right: 13px;"><strong>Revision Remark : </strong>
                                {{ $smhistoryLastData->sm_remark }}</span>
                        </div>
                    </div>

                    <div id="summaryCardBody" class="card-body collapse show">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" style="    font-size: 13px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th></th>
                                        <th>Financial Year</th>
                                        <th>Effective From</th>
                                        <th>Effective Till</th>
                                        <th>Monthly CTC</th>
                                        <th>Gross CTC</th>
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
                                            <td>{{ $smhist->sm_monthly_ctc }}</td>
                                            <td>{{ $smhist->sm_gross_pay }}</td>
                                            <td>
                                                <span class="simple-icon openBtn" style="cursor: pointer"
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
                                                    data-annual_gross="{{ $smhist->sm_annual_gross }}"
                                                    data-net_pay="{{ $smhist->sm_net_pay }}">
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
                {{-- Back Button --}}
                <button type="button" onclick="window.history.back()" class="btn btn-outline-danger btn-lg">
                    Cancel
                </button>

                {{-- Conditional Submit Button --}}
                <button id="saveButton" type="submit" class="btn btn-outline-info btn-lg cus-save"
                    {{ !empty($smhistoryLastData) ? 'disabled' : '' }}>
                    Save
                </button>
            </div>

            <!-- <div class="modal fade" id="employeeSalaryHistoryModal" tabindex="-1" role="dialog" aria-labelledby="employeeSalaryHistoryModalLabel" aria-hidden="true"> -->
            <div class="modal fade" id="employeeSalaryHistoryModal" tabindex="-1" role="dialog"
                aria-labelledby="employeeSalaryHistoryModalLabel" aria-hidden="true" data-bs-backdrop="static"
                data-bs-keyboard="false">

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
                                <h4 style="color:white !important; text-align:center;">From <span id="from_date"></span>
                                    To
                                    <span id="to_date">
                                </h4>
                                <div class="row user-pic text-left">
                                    <div class="col-1 pt-7">
                                        <span class="avatar avatar-xxl brround" id="empAvtar"
                                            style="background-image:  url('{{ isset($employee) && $employee->emp_profile_photo ? $employee->emp_profile_photo : asset('assets/imgs/user.png') }}');"></span>
                                    </div>
                                    <div class="col-11 text-left">
                                        <h1 class="px-4 pt-6 mt-6 mb-0"> {{ $employee->emp_full_name }} </h1>
                                        <span
                                            class="font-weight-semibold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $employee->fh_designation->dg_name }}</span><br>
                                        <span
                                            class="font-weight-semibold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $employee->emp_email }}</span>
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
                                                <td class="py-2 px-0"><span class="font-weight-semibold w-50">Monthly
                                                        CTC</span></td>
                                                <td class="py-2 px-0">:</td>
                                                <td class="py-2 px-0" id="monthlyCtc"></td>
                                                <td class="py-2 px-0"><span class="font-weight-semibold w-50">Annual
                                                        CTC</span></td>
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
                                                <td class="py-2 px-0"><span class="w-50">Conveyance Allowance</span>
                                                </td>
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
                                                <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total
                                                        Deductions</span></td>
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
                                                <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total
                                                        Deductions</span></td>
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
                                                <td class="py-2 px-0"><span class="font-weight-semibold w-50">Total
                                                        Earning</span></td>
                                                <td class="py-2 px-0">:</td>
                                                <td class="py-2 px-0 font-weight-semibold" id="empEarnings"></td>
                                                <td class="py-2 px-0"><span class="font-weight-semibold w-50">Gross
                                                        Pay</span></td>
                                                <td class="py-2 px-0">:</td>
                                                <td class="py-2 px-0 font-weight-semibold" id="empGrossPay"></td>
                                            </tr>

                                            <tr>
                                                <td class="py-2 px-0"><span class="font-weight-semibold w-50">Net
                                                        Pay</span>
                                                </td>
                                                <td class="py-2 px-0">:</td>
                                                <td class="py-2 px-0 font-weight-semibold" id="empNetPay"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-danger  cancel"
                                data-bs-dismiss="modal">Cancel</button>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>


<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@section('script')
    <script>
        // Initialize Flatpickr with validation
        flatpickr('.flatpickr', {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
            altInput: true,
            altFormat: "d-M-Y",
            disableMobile: false,
            onReady: function(selectedDates, dateStr, instance) {
                // Clear if existing date is in the past
                const initialDate = selectedDates[0];
                if (initialDate && initialDate < new Date().setHours(0, 0, 0, 0)) {
                    instance.clear();
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                // Real-time validation
                const selectedDate = selectedDates[0];
                const today = new Date().setHours(0, 0, 0, 0);

                if (selectedDate < today) {
                    alert("Please select today or a future date");
                    instance.clear();
                }
            }
        });

    </script>

    <script>
        function resetForm() {
            document.getElementById("smform").reset();
            document.getElementById("other_allowance").value = "0";
            document.getElementById("total_earnings").value = "0";
            document.getElementById("es_monthly_gross").value = "0";
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
        let isPFEnabled = {{ $employee->emp_is_pf_enabled ?? 0 }};
        let isESICEnabled = {{ $employee->emp_esic_limit ?? 0 }};
        let isPfValidationEnabled = {{ $emp_salary->es_pf_validation_enabled ?? 1 }};
        console.log("initialized pf validation here: ", isPfValidationEnabled);
        const pfToggle = document.getElementById('pfValidationToggle');


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
            var empAnnualGross = $(this).data('annual_gross');
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


        function calculateFromMonthlyGross() {
            const grossInput = document.getElementById('es_monthly_gross');
            const annualGrossInput = document.getElementById('annual_gross');
            const monthlyCTCInput = document.getElementById('es_monthly_ctc');
            const annualCTCInput = document.getElementById('es_annual_ctc');

            const monthlyGross = parseFloat(grossInput.value.replace(/,/g, '')) || 0;

            if (monthlyGross > 0) {
                annualGrossInput.value = (monthlyGross * 12).toFixed(2);
                monthlyCTCInput.value = monthlyGross.toFixed(2);
                annualCTCInput.value = (monthlyGross * 12).toFixed(2);
            } else {
                annualGrossInput.value = "0.00";
                monthlyCTCInput.value = "0.00";
                annualCTCInput.value = "0.00";
            }


            // Continue with downstream calculations
            // if (typeof calculateCTC === "function") calculateCTC('monthly');
            // if (typeof calculateEmpDeductions === "function") calculateEmpDeductions();
            // if (typeof updateValues === "function") updateValues();

            calculateCTC('monthly'); // Earnings from gross

            calculateEmpDeductions(); // Deductions from gross

            updateValues();
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

        $(document).ready(function() {
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
                const {
                    deductionTypeId,
                    employeeRate
                } = input.dataset;
                const tooltipMap = {
                    '351': `${employeeRate}% of basic salary`,
                    '352': `${employeeRate}% of gross`
                };
                setTooltip(input, tooltipMap[deductionTypeId] || 'Earning Info');
            });

            document.querySelectorAll('input.employer-deduction-value').forEach(input => {
                const {
                    deductionTypeId,
                    employerRate
                } = input.dataset;
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
                const {
                    calculationType,
                    threshold
                } = input.dataset;
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
        // function calculateTotal(selector, outputId) {
        //     let total = Array.from(document.querySelectorAll(selector))
        //         .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
        //     document.getElementById(outputId).value = total.toFixed(2);
        // }

        function calculateTotal(selector, outputId) {
            let total = Array.from(document.querySelectorAll(selector))
                .filter(input => input.offsetParent !== null) // exclude hidden elements
                .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);

            document.getElementById(outputId).value = total.toFixed(2);
        }

        function updateSalaryFields() {
            const totalEarnings = parseFloat(document.getElementById('total_earnings')?.value) || 0;
            const monthly_gross = parseFloat(document.getElementById('es_monthly_gross')?.value) || 0;
            const totalEmployerDeduction = parseFloat(document.getElementById('total_employer_deduction')?.value) || 0;
            const totalEmployeeDeduction = parseFloat(document.getElementById('total_employee_deduction')?.value) || 0;

            // Calculate Net Pay
            const netPay = monthly_gross - totalEmployeeDeduction;
            document.getElementById('monthly_net_salary').value = netPay.toFixed(2);
        }


        function calculateEmpDeductions() {
            let getMonthlyGross = parseFloat(document.getElementById("es_monthly_gross")?.value) || 0;
            let earningBasic = 0;
            let earningDA = 0;
             console.log("PF Validation Enabled:", isPfValidationEnabled);

            document.querySelectorAll(".calculated-value").forEach(input => {
                if (parseInt(input.dataset.earningTypeId) === 360) {
                    earningBasic = parseFloat(input.value) || 0;
                }
            });

            document.querySelectorAll(".calculated-value").forEach(input => {
                if (parseInt(input.dataset.earningTypeId) === 362) {
                    earningDA = parseFloat(input.value) || 0;
                }
            });

            // Employee Deductions
            document.querySelectorAll(".deduction-value").forEach(input => {
                let rate = parseFloat(input.dataset.employeeRate) || 0;
                let typeId = parseInt(input.dataset.deductionTypeId) || 0;
                let pfThreshold = parseFloat(input.dataset.pfThreshold) || 15000;
                let esicThreshold = parseFloat(input.dataset.threshold) || 0;
                let contribution = 0;
                // if (typeId === 351) {
                //     // contribution = (isPFEnabled == 120 ? (earningBasic * rate) / 100 : 0);
                //     if (isPFEnabled == 120) {
                //         let pfBase = 0;
                //         if (earningBasic < 10090) {
                //             pfBase = earningBasic + earningDA;
                //         } else {
                //             pfBase = earningBasic;
                //         }
                //         pfBase = Math.min(pfBase, pfThreshold);
                //         contribution = (pfBase * rate) / 100;
                //         console.log("PF-Base:", pfBase, "Contibution:", contribution);

                //         if (contribution > 1800) {
                //             contribution = 1800;
                //         }
                //     } else {
                //         contribution = 0;
                //     }
                // }

                if (typeId === 351) {
                    if (isPFEnabled == 120) {
                        let pfBase = 0;

                        // ✅ If PF validation toggle is ON, use full validation logic
                        if (isPfValidationEnabled == 1) {
                            if (earningBasic < 10090) {
                                pfBase = earningBasic + earningDA;
                            } else {
                                pfBase = earningBasic;
                            }

                            // Apply threshold
                            pfBase = Math.min(pfBase, pfThreshold);

                            contribution = (pfBase * rate) / 100;

                            // Cap at ₹1800
                            if (contribution > 1800) {
                                contribution = 1800;
                            }
                        }
                        // 🚀 If PF validation is DISABLED → simple logic (Basic × rate)
                        else {
                            contribution = (earningBasic * rate) / 100;
                        }
                    } else {
                        contribution = 0;
                    }
                }

                else if (typeId === 352) {
                    // contribution = (isESICEnabled == 120 ? (getMonthlyGross * rate) / 100 : 0);
                    let esicBase = Math.min(getMonthlyGross, esicThreshold);
                    contribution = (esicBase * rate) / 100;
                } else {
                    contribution = (earningBasic * rate) / 100;
                }

                input.value = contribution.toFixed(2);
                input.setAttribute("value", contribution.toFixed(2));
            });

            // Employer Deductions
            document.querySelectorAll(".employer-deduction-value").forEach(input => {
                let rate = parseFloat(input.dataset.employerRate) || 0;
                let typeId = parseInt(input.dataset.deductionTypeId) || 0;
                let pfThreshold = parseFloat(input.dataset.pfThreshold) || 15000;
                let esicThreshold = parseFloat(input.dataset.threshold) || 0;
                let contribution = 0;

                // if (typeId === 351) {
                //     // Employer PF
                //     if (isPFEnabled == 120) {
                //         let pfBase = 0;

                //         // ✅ Apply 10090 rule
                //         if (earningBasic < 10090) {
                //             // If base salary < ₹10090 → Basic + DA
                //             pfBase = earningBasic + earningDA;
                //         } else {
                //             // Otherwise → only Basic
                //             pfBase = earningBasic;
                //         }
                //         // Apply PF threshold (like ₹15000)
                //         pfBase = Math.min(pfBase, pfThreshold);

                //         contribution = (pfBase * rate) / 100;
                //     } else {
                //         contribution = 0;
                //     }
                //     // contribution = (isPFEnabled == 120 ? (earningBasic * rate) / 100 : 0);
                // }

                // 🟢 PF (Employer)
                if (typeId === 351) {
                    if (isPFEnabled == 120) {
                        let pfBase = 0;

                        // ✅ Toggle ON → apply PF validation logic (same as employee)
                        if (isPfValidationEnabled == 1) {
                            pfBase = earningBasic < 10090 ? earningBasic + earningDA : earningBasic;
                            pfBase = Math.min(pfBase, pfThreshold);
                            contribution = (pfBase * rate) / 100;
                        }
                        // 🚀 Toggle OFF → Basic × Rate (no validation)
                        else {
                            contribution = (earningBasic * rate) / 100;
                        }
                    } else {
                        contribution = 0;
                    }
                }

                else if (typeId === 352) {
                    // contribution = (isESICEnabled == 120 ? (getMonthlyGross * rate) / 100 : 0);
                    let esicBase = Math.min(getMonthlyGross, esicThreshold);
                    contribution = (esicBase * rate) / 100;
                } else {
                    contribution = (earningBasic * rate) / 100;
                }

                input.value = contribution.toFixed(2);
                input.setAttribute("value", contribution.toFixed(2));
            });


            calculateTotal(".deduction-value", "total_employee_deduction");
            calculateTotal(".employer-deduction-value", "total_employer_deduction");
            updateSalaryFields();

            // Corrected: use total earnings + employer contribution for CTC
            const totalEarnings = parseFloat(document.getElementById("total_earnings")?.value) || 0;
            const totalEmployerDeduction = parseFloat(document.getElementById("total_employer_deduction")?.value) || 0;
            const newCTC = totalEarnings + totalEmployerDeduction;

            document.getElementById("es_monthly_ctc").value = newCTC.toFixed(2);
            document.getElementById("es_annual_ctc").value = (newCTC * 12).toFixed(2);
        }




        // let esicValidationEnabled = true;
        let esicValidationEnabled = document.getElementById('esicValidationEnabledInput')
            ? parseInt(document.getElementById('esicValidationEnabledInput').value) === 1
            : true;

        document.getElementById('esicValidationToggle').addEventListener('change', function() {
            esicValidationEnabled = this.checked;
            document.getElementById('esicValidationEnabledInput').value = esicValidationEnabled ? 1 : 0;

            if (!esicValidationEnabled) {
                // Clear all ESIC errors when validation is disabled
                document.querySelectorAll('[id^="esic-error-"]').forEach(span => {
                    span.textContent = '';
                });
                // Enable save button
                document.getElementById('saveButton').disabled = false;
            } else {
                // Re-run validation when enabled
                calculateCTC('monthly');
                calculateEmpDeductions();
            }
        });




        // let pfValidationEnabled = true; // default checked state
        let pfValidationEnabled = document.getElementById('pfValidationEnabledInput')
            ? parseInt(document.getElementById('pfValidationEnabledInput').value) === 1
            : true;
            console.log("PF Validation Enabled check 1:", pfValidationEnabled);

        document.getElementById('pfValidationToggle').addEventListener('change', function () {
            pfValidationEnabled = this.checked;
            document.getElementById('pfValidationEnabledInput').value = pfValidationEnabled ? 1 : 0;
            isPfValidationEnabled = pfValidationEnabled ? 1 : 0;

            calculateEmpDeductions();
            calculateCTC('monthly');
        });

        // ----------------------------------------------------------------
        // RUN CALCULATIONS ON PAGE LOAD ACCORDING TO DB
        // ----------------------------------------------------------------
        window.addEventListener('load', function () {
            calculateEmpDeductions();
            calculateCTC('monthly');
        });


        function calculateCTC(type) {
            let monthlyGross = parseFloat(document.getElementById('es_monthly_gross')?.value) || 0;
            let totalEarnings = 0;
            let otherAllowance = 0;
            const esicInputs = document.querySelectorAll('input[data-deduction-type-id="352"]');
            let hisOtherAllow = '{{ $smhistoryLastData ? $smhistoryLastData->sm_other_allow : 0 }}';
            let hasInput = false;

            const totalEmployerDeduction = parseFloat(document.getElementById("total_employer_deduction")?.value) || 0;
            const newCTC = monthlyGross + totalEmployerDeduction;

            // Update monthly CTC from monthly gross
            document.getElementById('es_monthly_ctc').value = newCTC.toFixed(2);
            document.getElementById('total_earnings').value = monthlyGross.toFixed(2);

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

            let oAllow = monthlyGross - otherAllowance;
            document.getElementById('other_allowance').value = hasInput ? oAllow.toFixed(2) : hisOtherAllow;

            // Show error if allowance negative
            const errorDiv = document.getElementById('oAllowError');
            errorDiv.innerText = oAllow < 0 ? "Other Allowance cannot be negative." : "";
            errorDiv.style.display = oAllow < 0 ? 'block' : 'none';

            // Disable save if allowance error
            document.getElementById('saveButton').disabled = oAllow < 0;

            // Recalculate ESIC warning
            setTimeout(() => {
                esicInputs.forEach((input, index) => {
                    const esicThreshold = parseFloat(input.getAttribute("data-threshold")) || 0;
                    const role = input.getAttribute("data-role") || `role${index}`;
                    const errorId = `esic-error-${role}`;
                    let errorSpan = document.getElementById(errorId) || document.createElement("span");
                    errorSpan.id = errorId;
                    errorSpan.style.color = "red";
                    if (!document.getElementById(errorId)) input.parentNode.appendChild(errorSpan);

                    // Check if validation is enabled before showing error
                    if (esicValidationEnabled && monthlyGross > esicThreshold && parseFloat(input.value) >
                        0) {
                        errorSpan.textContent =
                            `Esic wage limit for coverage under the Act, effective from 01.01.2017, is Rs. ${esicThreshold}/- pm`;
                    } else {
                        errorSpan.textContent = '';
                    }
                });

                // Only disable save button if validation is enabled
                if (esicValidationEnabled) {
                    const anyEsicError2 = Array.from(document.querySelectorAll('[id^="esic-error-"]'))
                        .some(span => span.textContent.includes("Esic wage limit"));
                    document.getElementById('saveButton').disabled = anyEsicError2;
                }
            }, 50);
        }



        document.addEventListener('DOMContentLoaded', function() {
            const monthlyGrossInput = document.getElementById('es_monthly_gross');
            if (monthlyGrossInput) {
                monthlyGrossInput.addEventListener('input', function() {
                    const monthlyGrossInput = parseFloat(document.getElementById("es_monthly_gross")
                    ?.value);

                    calculateFromMonthlyGross();
                    calculateEmpDeductions();
                });
            }

            checkSaIdAndSetTooltips();

            // =============================
            // 🔹 PF & ESIC Validation Toggle Script
            // =============================

            let isPfValidationEnabled = parseInt($("#pfValidationEnabledInput").val()) || 0;
            $("#pfValidationToggle").prop("checked", isPfValidationEnabled === 1);

            let isEsicValidationEnabled = parseInt($("#esicValidationEnabledInput").val()) || 0;

            // 🟢 PF Validation Toggle
            $(document).on("change", "#pfValidationToggle", function () {
                isPfValidationEnabled = $(this).is(":checked") ? 1 : 0;
                $("#pfValidationEnabledInput").val(isPfValidationEnabled);
                calculateEmpDeductions(); // Recalculate instantly
            });

            // 🔵 ESIC Validation Toggle
            $(document).on("change", "#esicValidationToggle", function () {
                isEsicValidationEnabled = $(this).is(":checked") ? 1 : 0;
                $("#esicValidationEnabledInput").val(isEsicValidationEnabled);
                calculateEmpDeductions(); // Recalculate instantly
            });





            // Whenever any earning input changes, recalculate everything
            document.querySelectorAll('.calculated-value').forEach(function(input) {
                input.addEventListener('input', function() {
                    calculateCTC('earnings'); // Update earnings (will keep total as gross)
                    calculateEmpDeductions(); // Refresh deductions
                });
            });

            // REMOVE this listener, it's no longer your base input:
            // document.getElementById('es_monthly_ctc').addEventListener('input', ...);

            // Auto fill other allowance values if any
            $('.special-364').each(function() {
                const saId = $(this).data('sa_id');
                const empActualId = '{{ $emp_actual_id }}';
                const businessId = '{{ $business_id }}';

                if (saId) {
                    $.ajax({
                        url: "{{ route('employee.other.allowance') }}",
                        type: "get",
                        data: {
                            saId: saId,
                            empActualId: empActualId,
                            businessId: businessId
                        },
                        success: function(response) {
                            if (response.salaryEarn) {
                                $('#earning_' + saId).val(response.salaryEarn.es_e_amount);
                                setInfoTooltips();
                                setInfoTooltipsDeductions();
                            } else {
                                $('#earning_' + saId).val(0);
                            }
                            calculateEmpDeductions();
                        }
                    });
                }
            });

            if (isPFEnabled == 120) {
                document.querySelectorAll('.deduction-value[data-deduction-type-id="351"]').forEach(el => el.style
                    .display = '');
                document.querySelectorAll('.employer-deduction-value[data-deduction-type-id="351"]').forEach(el =>
                    el.style.display =
                    '');
            }

            if (isESICEnabled == 120) {
                document.querySelectorAll('.deduction-value[data-deduction-type-id="352"]').forEach(el => el.style
                    .display = '');
                document.querySelectorAll('.employer-deduction-value[data-deduction-type-id="352"]').forEach(el =>
                    el.style.display =
                    '');
            }

            calculateCTC('monthly');
            calculateEmpDeductions();
            updateValues();
        });


        // Set all input field value 0 functionality by class
        $(document).ready(function() {
            $(".numericInput").on("focus", function() {
                if ($(this).val() === "0") {
                    $(this).val("");
                }
            });
            $(".numericInput").on("blur", function() {
                if ($.trim($(this).val()) === "") {
                    $(this).val("0");
                }
            });
        });
    </script>

    <script>
        let hasErrorC = false;
        $('.toggle-form').on('change', function() {
            const typeId = $(this).data('type');
            const isChecked = $(this).is(':checked');

            // ✅ Add this block here
            if (typeId == 351) {
                isPFEnabled = isChecked ? 120 : 0;
            }
            if (typeId == 352) {
                isESICEnabled = isChecked ? 120 : 0;
            }

            const employeeId = {{ $employee->emp_id }};
            const esicInputs2 = document.querySelectorAll('input[data-deduction-type-id="352"]');
            let monthlyGross = parseFloat(document.getElementById('es_monthly_gross').value) || 0;

            $.ajax({
                url: '{{ route('toggle.deduction') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type_id: typeId,
                    employeeId: employeeId,
                    enabled: isChecked ? 1 : 0,
                },
                success: function(response) {
                    const $empInput = $('.deduction-value[data-deduction-type-id="' + typeId + '"]');
                    const $employerInput = $('.employer-deduction-value[data-deduction-type-id="' +
                        typeId + '"]');

                    if (isChecked) {
                        $empInput.show();
                        $employerInput.show();

                        if (typeId == 352) {
                            hasErrorC = false;

                            setTimeout(() => {
                                esicInputs2.forEach((input, index) => {
                                    const esicThreshold = parseFloat(input.getAttribute(
                                        "data-threshold")) || 0;
                                    const value = parseFloat(input.value) || 0;
                                    const role = input.getAttribute("data-role") ||
                                        `role${index}`;

                                    let errorId = `esic-error-${role}`;
                                    let errorSpan = document.getElementById(errorId);

                                    if (!errorSpan) {
                                        errorSpan = document.createElement("span");
                                        errorSpan.id = errorId;
                                        errorSpan.style.color = "red";
                                        input.parentNode.appendChild(errorSpan);
                                    }

                                    // if (monthlyGross > esicThreshold && value > 0) {
                                    //     errorSpan.textContent = "Esic wage limit for coverage under the Act, effective from 01.01. 2017, is Rs. " + esicThreshold + "/- pm";
                                    //     hasErrorC = true;
                                    // } else {
                                    //     errorSpan.textContent = "";
                                    // }

                                    // Check if validation is enabled
                                    if (esicValidationEnabled && monthlyGross >
                                        esicThreshold && value > 0) {
                                        errorSpan.textContent =
                                            "Esic wage limit for coverage under the Act, effective from 01.01.2017, is Rs. " +
                                            esicThreshold + "/- pm";
                                        hasErrorC = true;
                                    } else {
                                        errorSpan.textContent = "";
                                    }

                                });


                                // Only disable if validation is enabled
                                // if (esicValidationEnabled) {
                                //     const anyEsicError = Array.from(document.querySelectorAll('[id^="esic-error-"]'))
                                //         .some(span => span.textContent.includes("Esic wage limit for coverage under the Act"));
                                //     document.getElementById('saveButton').disabled = anyEsicError;
                                // }

                            }, 50);


                            // ✅ Disable save button if any error exists
                            //     const anyEsicError = Array.from(document.querySelectorAll('[id^="esic-error-"]'))
                            //         .some(span => span.textContent.includes("Esic wage limit for coverage under the Act"));
                            //     document.getElementById('saveButton').disabled = anyEsicError;

                            // }, 50);


                            // document.getElementById('saveButton').disabled = hasErrorC;
                            if (esicValidationEnabled) {
                                document.getElementById('saveButton').disabled = hasErrorC;
                            }
                        }
                    } else {
                        $empInput.hide().val('0');
                        $employerInput.hide().val('0');

                        if (typeId == 352 || typeId == 351) {
                            if (typeId == 352) {
                                esicInputs2.forEach((input, index) => {
                                    const role = input.getAttribute("data-role") ||
                                        `role${index}`;
                                    let errorId = `esic-error-${role}`;
                                    let errorSpan = document.getElementById(errorId);
                                    if (errorSpan) errorSpan.textContent = "";
                                });
                            }
                            hasErrorC = false;
                            // document.getElementById('saveButton').disabled = false;
                        }
                    }
                    calculateEmpDeductions();
                },
                error: function() {
                    alert('Failed to toggle deduction setting.');
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            let saveButtonC = document.getElementById('saveButton');
            const inputsC = document.querySelectorAll('input');

            inputsC.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (!hasErrorC) {
                        saveButtonC.disabled = false;
                    }
                });
            });
        });
    </script>
@endsection
