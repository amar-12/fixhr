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
    .summary-info {
        display: flex;
        gap: 20px;
        font-size: 14px;
        white-space: nowrap;
        padding: 15px 0;
    }

    .arrow-up {
        text-align: center;
        font-size: 20px;
        color: #007bff;
    }

    .simple-icon {
        cursor: pointer;
        font-size: 18px;
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

    .vertical-text {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        font-size: 16px;
        color: #007bff;
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
                    <span>Employee Name : {{ $employee->emp_full_name .' - '. $employee->emp_code }} </span>
                </h4>
                <h4 class="card-title">Salary Master</h4>

                <input type="hidden" name="emp_id" value="{{ $emp_actual_id }}">
                <input type="hidden" name="business_id" value="{{ $business_id }}">
                <input type="hidden" name="deduction_employer_mode" value="1"> <!-- Added -->
                <input type="hidden" name="payroll_mode" value="1"> <!-- Added -->

                <div class="card-body">
                    <div class="row">
                        <!-- Monthly Gross -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Monthly Gross <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="es_monthly_gross" placeholder="0"
                                value="{{ old('es_monthly_gross', $smhistoryLastData->sm_gross_pay ?? '') }}"
                                maxlength="10" required>
                        </div>

                        <!-- Annual Gross -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Annual Gross <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="annual_gross" placeholder="0"
                                value="{{ old('annual_gross', $smhistoryLastData->sm_annual_gross ?? '') }}"
                                maxlength="10" required>
                        </div>

                        <!-- Monthly CTC -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-money-bill"></i> Monthly CTC <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="es_monthly_ctc" placeholder="0"
                                value="{{ old('es_monthly_ctc', $emp_salary->es_monthly_ctc ?? '') }}" maxlength="10"
                                required>
                        </div>

                        <!-- Annual CTC -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-calendar-alt"></i> Annual CTC <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="es_annual_ctc" placeholder="0"
                                value="{{ old('es_annual_ctc', $emp_salary->es_annual_ctc ?? '') }}" maxlength="10"
                                required>
                        </div>

                        <!-- Per Day Wage -->
                        <div class="col-md-2">
                            <label class="form-label mb-0 mt-2">
                                <i class="fa fa-calendar-alt"></i> Per Day Wage <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="es_per_day_wage" placeholder="0"
                                value="{{ old('es_per_day_wage', $emp_salary->es_perday_salary ?? '0') }}"
                                maxlength="10" required>
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
                            <input type="text" class="form-control" name="remark" placeholder="Remark"
                                value="{{ old('remark', $smhistoryLastData->sm_remark ?? '') }}"
                                pattern="^[A-Za-z][A-Za-z0-9\s]*$"
                                title="Only letters and numbers allowed. Must start with a letter." required>
                        </div>

                        <!-- ESIC Validation Toggle -->
                        {{-- <div class="col-md-2 mt-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="esic_validation_enabled"
                                    name="esic_validation_enabled" value="1" {{ isset($emp_salary) &&
                                    $emp_salary->es_esic_validation_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="esic_validation_enabled">
                                    Enable ESIC Validation
                                </label>
                            </div>
                        </div> --}}

                        <!-- PF Validation Toggle -->
                        {{-- <div class="col-md-2 mt-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pf_validation_enabled"
                                    name="pf_validation_enabled" value="1" {{ isset($emp_salary) &&
                                    $emp_salary->es_pf_validation_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="pf_validation_enabled">
                                    Enable PF Validation
                                </label>
                            </div>
                        </div> --}}
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
                        <h5>Earnings</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            @foreach ($earnings as $earning)
                            @if ($earning->sa_is_active)
                           @php
                            $earningTypeId = $earning->sa_earning_type_id;

                            // First check from emp_earnings array which is now properly loaded
                            $existingValue = '';

                            // This should work now since emp_earnings has es_e_type_id as keys
                            if(isset($emp_earnings[$earningTypeId])) {
                                $existingValue = $emp_earnings[$earningTypeId];
                            }

                            // dd($emp_earnings);
                            // If not found, check from smhistoryLastData
                            if(empty($existingValue) && !empty($smhistoryLastData)) {
                                $valueFromHistory = match((int)$earningTypeId) {
                                    360 => $smhistoryLastData->sm_basic ?? '',      // Basic Salary
                                    361 => $smhistoryLastData->sm_hra ?? '',        // HRA
                                    362 => $smhistoryLastData->sm_dear_allow ?? '', // Dearness Allowance
                                    363 => $smhistoryLastData->sm_conv_allow ?? '', // Conveyance Allowance
                                    364 => $smhistoryLastData->sm_other_allow ?? '', // Other Allowance
                                    default => ''
                                    };

                                    if(!empty($valueFromHistory)) {
                                        $existingValue = $valueFromHistory;
                                        }
                                        }

                                        // Check for old input (after form validation)
                                        $value = old('earnings.' . $earning->sa_id, $existingValue);

                            // If still empty, use 0
                            if(empty($value)) {
                                $value = '0';
                            }

                            // Format the value
                            $value = is_numeric($value) ? number_format($value, 2, '.', '') : $value;
                        @endphp

                            <div class="col-md-6 mb-3">
                                <label>
                                    <i class="fa fa-dollar-sign"></i> {{ $earning->sa_title }}
                                    <span class="text-danger">*</span>
                                    <i class="fa fa-info-circle text-primary ms-1 earning-title"
                                    data-bs-toggle="tooltip" data-bs-placement="top">
                                </i>
                            </label>
                             <input type="text" class="form-control calculated-value numericInput special-364"
                                    name="earnings[{{ $earning->sa_id }}]"
                                    value="{{ $value }}"
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
                            <!-- Other Allowance -->
                            <div class="col-md-6">
                                <label>Other Allowance</label>
                                <input type="text" class="form-control earning-input" name="es_rem_allowance"
                                    placeholder="0"
                                    value="{{ old('es_rem_allowance', $smhistoryLastData->sm_other_allow ?? '0') }}"
                                    maxlength="10" required>
                            </div>

                            <!-- Total Earnings -->
                            <div class="col-md-6">
                                <label>Total Earnings</label>
                                <input type="text" class="form-control" id="total_employee_earning"
                                    name="total_employee_earning" placeholder="0"
                                    value="{{ old('total_employee_earning', $smhistoryLastData->sm_total_earning ?? '') }}"
                                    maxlength="10" required>
                                <small class="text-danger" id="total_earning_error" style="display:none;"></small>
                            </div>
                        </div>

                        <br>

                        <!-- Monthly Net Salary (Take Home) Input में ID जोड़ें -->
                        <div class="col-md-6 mb-3">
                            <label>Take Home Salary (Net Pay) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="monthly_net_salary" name="monthly_net_salary"
                                placeholder="0"
                                value="{{ old('monthly_net_salary', $smhistoryLastData->sm_net_pay ?? '') }}"
                                maxlength="10" required>
                            <small class="text-danger" id="net_salary_error" style="display:none;"></small>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Deductions (Employee) Section -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Deductions (Employee)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($deductions as $deduction)
                            @php
                            $typeId = $deduction->std_deduction_type_id;
                            $isPF = $typeId == 351;
                            $isESIC = $typeId == 352;
                            $showField = ($isPF && $employee->emp_is_pf_enabled != 121) ||
                            ($isESIC && $employee->emp_esic_limit != 121);
                            @endphp
                            <div class="col-md-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">{{ $deduction->deduction_type_name }}</label>
                                </div>
                                <!-- Deductions Inputs (Employee) -->
                                <input type="text" class="form-control deduction-input"
                                    name="deductions[{{ $deduction->std_id }}]"
                                    value="{{ old('deductions.'.$deduction->std_id, $emp_deductions[$typeId] ?? '0') }}"
                                    placeholder="0" maxlength="9" {{ !$showField ? 'style=display:none;' : '' }}>
                                <input type="hidden" name="deduction_type[{{ $deduction->std_id }}]"
                                    value="{{ $typeId }}">
                            </div>
                            @endforeach
                        </div>

                        <div class="col-md-12">
                            <label>Total Deductions</label>
                            <input type="text" class="form-control" id="total_employee_deduction"
                                name="total_employee_deduction" placeholder="0"
                                value="{{ old('total_employee_deduction', $smhistoryLastData->sm_employee_total_ded ?? '') }}"
                                maxlength="10">
                            <small class="text-danger" id="deduction_error" style="display:none;"></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions (Employer) Section -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Deductions (Employer)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($employerDeductions as $deduction)
                            @php
                            $typeId = $deduction->std_deduction_type_id;
                            $isPF = $typeId == 351;
                            $isESIC = $typeId == 352;
                            $showField = ($isPF && $employee->emp_is_pf_enabled != 121) ||
                            ($isESIC && $employee->emp_esic_limit != 121);
                            @endphp
                            <div class="col-md-12 mb-3">
                                <label class="mb-0">{{ $deduction->deduction_type_name }}</label>
                                <input type="text" class="form-control employer-deduction-input"
                                    name="employerDeductions[{{ $deduction->std_id }}]"
                                    value="{{ old('employerDeductions.'.$deduction->std_id, $emplyer_deductions[$typeId] ?? '0') }}"
                                    placeholder="0" maxlength="9" {{ !$showField ? 'style=display:none;' : '' }}>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Total Deductions</label>
                                <input type="text" class="form-control" id="total_employer_deduction"
                                    name="total_employer_deduction" placeholder="0"
                                    value="{{ old('total_employer_deduction', $smhistoryLastData->sm_employer_total_ded ?? '') }}"
                                    maxlength="10">
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
                <h5>Summary</h5>
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
                    <span><strong>Revision Remark:</strong> {{ $smhistoryLastData->sm_remark }}</span>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
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
                                        data-annual_gross="{{ $smhist->sm_annual_gross }}"
                                        data-net_pay="{{ $smhist->sm_net_pay }}">
                                        👁
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('export.smhistory', ['id' => $smhist->sm_id]) }}"
                                        class="simple-icon">⬇️</a>
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
            <button type="button" onclick="window.history.back()" class="btn btn-outline-danger btn-lg">Cancel</button>
            <button type="submit" class="btn btn-outline-info btn-lg" id="saveButton" {{ !empty($smhistoryLastData)
                ? 'disabled' : '' }}>Save</button>
        </div>
    </div>
</form>

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


@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Date picker initialization
flatpickr('.flatpickr', {
    dateFormat: "Y-m-d",
    minDate: "today",
    allowInput: true,
    altInput: true,
    altFormat: "d-M-Y"
});

// Reset form function
function resetForm() {
    document.getElementById("smform").reset();
    setupInputFields(); // Reinitialize after reset
}

// ========== GLOBAL VARIABLES ==========
let isValidationPassed = false;
let saveButton = null;

// ========== INPUT FIELD ENHANCEMENT ==========
function setupInputFields() {
    // Setup all number inputs with placeholder behavior
    const numberInputs = document.querySelectorAll('input[type="text"][name^="earnings["], input[name^="deductions["], input[name^="employerDeductions["], input[name="es_rem_allowance"], input[name="total_employee_earning"], input[name="total_employee_deduction"], input[name="total_employer_deduction"], input[name="monthly_net_salary"], input[name="es_monthly_ctc"], input[name="es_annual_ctc"], input[name="annual_gross"], input[name="es_monthly_gross"], input[name="es_per_day_wage"]');

    numberInputs.forEach(input => {
        // Clear placeholder on focus
        input.addEventListener('focus', function() {
            if (this.value === '0' || this.value === '0.00') {
                this.value = '';
            }
        });

        // Restore 0 if empty on blur
        input.addEventListener('blur', function() {
            if (this.value === '' || isNaN(parseFloat(this.value))) {
                this.value = '0';
            }
            // Remove leading zeros and format to 2 decimal places
            const numValue = parseFloat(this.value);
            if (!isNaN(numValue)) {
                this.value = numValue.toFixed(2);
            }
        });

        // Allow only numbers and decimal point
        input.addEventListener('input', function(e) {
            // Remove any non-numeric characters except decimal point
            this.value = this.value.replace(/[^0-9.]/g, '');

            // Ensure only one decimal point
            const decimalCount = (this.value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                this.value = this.value.substring(0, this.value.lastIndexOf('.'));
            }

            // Limit to 2 decimal places
            if (this.value.includes('.')) {
                const parts = this.value.split('.');
                if (parts[1].length > 2) {
                    this.value = parts[0] + '.' + parts[1].substring(0, 2);
                }
            }
        });
    });
}

// ========== CALCULATION FUNCTIONS ==========
// Calculate Annual Gross (Monthly Gross * 12)
function calculateAnnualGross() {
    const monthlyGrossInput = document.querySelector('input[name="es_monthly_gross"]');
    const annualGrossInput = document.querySelector('input[name="annual_gross"]');

    if (monthlyGrossInput && annualGrossInput) {
        const monthlyGross = parseFloat(monthlyGrossInput.value) || 0;
        const annualGross = monthlyGross * 12;
        annualGrossInput.value = annualGross.toFixed(2);
    }
}

// Calculate Per Day Wage (Monthly Gross / 26)
function calculatePerDayWage() {
    const monthlyGrossInput = document.querySelector('input[name="es_monthly_gross"]');
    const perDayWageInput = document.querySelector('input[name="es_per_day_wage"]');

    if (monthlyGrossInput && perDayWageInput) {
        const monthlyGross = parseFloat(monthlyGrossInput.value) || 0;
        const perDayWage = monthlyGross / 26;
        perDayWageInput.value = perDayWage.toFixed(2);
    }
}

// Calculate Total Employer Deductions
function calculateTotalEmployerDeductions() {
    const employerDeductionInputs = document.querySelectorAll('input[name^="employerDeductions["]');
    let totalEmployerDeductions = 0;

    employerDeductionInputs.forEach(input => {
        if (input.style.display !== 'none') {
            const value = parseFloat(input.value) || 0;
            totalEmployerDeductions += value;
        }
    });

    const totalEmployerDeductionInput = document.querySelector('input[name="total_employer_deduction"]');
    if (totalEmployerDeductionInput) {
        totalEmployerDeductionInput.value = totalEmployerDeductions.toFixed(2);
    }

    return totalEmployerDeductions;
}

// Calculate CTC (Monthly Gross + Total Employer Deductions)
function calculateCTC() {
    const monthlyGrossInput = document.querySelector('input[name="es_monthly_gross"]');
    const monthlyCTCInput = document.querySelector('input[name="es_monthly_ctc"]');
    const annualCTCInput = document.querySelector('input[name="es_annual_ctc"]');

    if (monthlyGrossInput && monthlyCTCInput && annualCTCInput) {
        const monthlyGross = parseFloat(monthlyGrossInput.value) || 0;
        const totalEmployerDeductions = calculateTotalEmployerDeductions();

        // Monthly CTC = Monthly Gross + Total Employer Deductions
        const monthlyCTC = monthlyGross + totalEmployerDeductions;
        monthlyCTCInput.value = monthlyCTC.toFixed(2);

        // Annual CTC = Monthly CTC * 12
        const annualCTC = monthlyCTC * 12;
        annualCTCInput.value = annualCTC.toFixed(2);
    }
}

// Calculate Sum of All Allowances (Earnings Components + Other Allowance)
function calculateSumOfAllowances() {
    const earningsInputs = document.querySelectorAll('input[name^="earnings["]');
    const earningsInputs2 = document.querySelectorAll('input.calculated-value[data-sa_id]');
    const otherAllowanceInput = document.querySelector('input[name="es_rem_allowance"]');

    let totalAllowances = 0;

    // Sum all earnings inputs
    earningsInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        totalAllowances += value;
    });

    // Add other allowance
    const otherAllowanceValue = parseFloat(otherAllowanceInput.value) || 0;
    totalAllowances += otherAllowanceValue;

    return totalAllowances;
}


// Calculate Net Salary
function calculateNetSalary() {
    const totalEarningInput = document.querySelector('#total_employee_earning');
    const totalDeductionInput = document.querySelector('#total_employee_deduction');
    const netSalaryInput = document.querySelector('#monthly_net_salary');

    if (totalEarningInput && totalDeductionInput && netSalaryInput) {
        const totalEarnings = parseFloat(totalEarningInput.value) || 0;
        const totalDeductions = parseFloat(totalDeductionInput.value) || 0;
        const netSalary = totalEarnings - totalDeductions;

        netSalaryInput.value = netSalary.toFixed(2);
    }
}

// ========== VALIDATION FUNCTIONS ==========
// Validate if Sum of Allowances equals Total Earnings
function validateAllowancesSum() {
    const totalAllowances = calculateSumOfAllowances();
    const totalEarningInput = document.querySelector('#total_employee_earning');
    const totalEarnings = parseFloat(totalEarningInput.value) || 0;

    const errorElement = document.querySelector('#total_earning_error');

    // Reset error
    errorElement.style.display = 'none';
    errorElement.textContent = '';

    if (Math.abs(totalAllowances - totalEarnings) > 0.01) {
        errorElement.textContent = `Sum of all allowances (${totalAllowances.toFixed(2)}) does not match Total Earnings (${totalEarnings.toFixed(2)})`;
        errorElement.style.display = 'block';
        return false;
    }

    return true;
}

// Main Validation Function
function validateAllCalculations() {
    isValidationPassed = false;

    // 1. Validate Allowances Sum = Total Earnings (ONLY VALIDATION NEEDED)
    const allowancesValidation = validateAllowancesSum();
    if (!allowancesValidation) {
        updateSaveButton();
        return false;
    }

    // 2. Check if Total Earnings equals Monthly Gross
    const totalEarningInput = document.querySelector('#total_employee_earning');
    const monthlyGrossInput = document.querySelector('input[name="es_monthly_gross"]');
    const totalEarnings = parseFloat(totalEarningInput.value) || 0;
    const monthlyGross = parseFloat(monthlyGrossInput.value) || 0;

    if (Math.abs(totalEarnings - monthlyGross) > 0.01) {
        updateSaveButton();
        return false;
    }

    isValidationPassed = true;
    updateSaveButton();
    return true;
}

// ========== DEDUCTIONS CALCULATION ==========
function calculateTotalDeductions() {
    const deductionInputs = document.querySelectorAll('input[name^="deductions["]');
    let totalDeductions = 0;

    deductionInputs.forEach(input => {
        if (input.style.display !== 'none') {
            const value = parseFloat(input.value) || 0;
            totalDeductions += value;
        }
    });

    const totalDeductionInput = document.querySelector('#total_employee_deduction');
    if (totalDeductionInput) {
        totalDeductionInput.value = totalDeductions.toFixed(2);
    }

    calculateNetSalary();
    validateAllCalculations();
}

// ========== SAVE BUTTON CONTROL ==========
function updateSaveButton() {
    if (!saveButton) {
        saveButton = document.querySelector('#saveButton');
    }

    if (saveButton) {
        // Check if there's existing data (from PHP)
        const hasExistingDataAttr = saveButton.hasAttribute('disabled') &&
                                   saveButton.getAttribute('disabled') !== 'false';

        // ENABLE button when validation passes, regardless of existing data
        // (We want to allow saving when validation is correct)
        if (isValidationPassed) {
            saveButton.disabled = false;
            saveButton.style.opacity = '1';
            saveButton.style.cursor = 'pointer';
            saveButton.title = "Click to save payroll data";
        } else {
            saveButton.disabled = true;
            saveButton.style.opacity = '0.6';
            saveButton.style.cursor = 'not-allowed';

            // Show appropriate error message
            const totalAllowances = calculateSumOfAllowances();
            const totalEarnings = parseFloat(document.querySelector('#total_employee_earning').value) || 0;
            const monthlyGross = parseFloat(document.querySelector('input[name="es_monthly_gross"]').value) || 0;

            if (Math.abs(totalAllowances - totalEarnings) > 0.01) {
                saveButton.title = "Sum of allowances must equal Total Earnings";
            } else if (Math.abs(totalEarnings - monthlyGross) > 0.01) {
                saveButton.title = "Total Earnings must equal Monthly Gross";
            } else {
                saveButton.title = "Cannot save - Please fix validation errors";
            }
        }
    }
}

// ========== MONTHLY GROSS CHANGE HANDLER ==========
function handleMonthlyGrossChange() {
    const monthlyGrossInput = document.querySelector('input[name="es_monthly_gross"]');

    // 1. Update Total Earnings to match Monthly Gross
    const totalEarningInput = document.querySelector('#total_employee_earning');
    if (totalEarningInput) {
        totalEarningInput.value = monthlyGrossInput.value;
    }

    // 2. Calculate Annual Gross
    calculateAnnualGross();

    // 3. Calculate Per Day Wage
    calculatePerDayWage();

    // 4. Calculate CTC
    calculateCTC();

    // 5. Calculate Net Salary (based on current deductions)
    calculateNetSalary();

    // 6. Validate all calculations
    validateAllCalculations();
}

// ========== EARNINGS COMPONENTS CHANGE HANDLER ==========
function handleEarningsChange() {
    // Validate all calculations
    validateAllCalculations();
}

// ========== TOTAL EARNINGS CHANGE HANDLER ==========
function handleTotalEarningsChange() {
    const totalEarningInput = document.querySelector('#total_employee_earning');
    const monthlyGrossInput = document.querySelector('input[name="es_monthly_gross"]');

    if (totalEarningInput && monthlyGrossInput) {
        // Update monthly gross to match total earnings
        monthlyGrossInput.value = totalEarningInput.value;

        // Trigger other calculations
        calculateAnnualGross();
        calculatePerDayWage();
        calculateCTC();
        calculateNetSalary();

        // Validate all calculations
        validateAllCalculations();
    }
}

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

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    // Setup input fields
    setupInputFields();

    // Get save button
    saveButton = document.querySelector('#saveButton');

    // ========== MONTHLY GROSS EVENT LISTENER ==========
    const monthlyGrossInput = document.querySelector('input[name="es_monthly_gross"]');
    if (monthlyGrossInput) {
        monthlyGrossInput.addEventListener('input', function() {
            handleMonthlyGrossChange();
        });

        monthlyGrossInput.addEventListener('change', function() {
            handleMonthlyGrossChange();
        });
    }

    // ========== EARNINGS COMPONENTS EVENT LISTENERS ==========
    const earningsInputs = document.querySelectorAll('input[name^="earnings["]');
    const otherAllowanceInput = document.querySelector('input[name="es_rem_allowance"]');

    earningsInputs.forEach(input => {
        input.addEventListener('input', handleEarningsChange);
        input.addEventListener('change', handleEarningsChange);
    });

    if (otherAllowanceInput) {
        otherAllowanceInput.addEventListener('input', handleEarningsChange);
        otherAllowanceInput.addEventListener('change', handleEarningsChange);
    }

    // ========== TOTAL EARNINGS EVENT LISTENER ==========
    const totalEarningInput = document.querySelector('#total_employee_earning');
    if (totalEarningInput) {
        totalEarningInput.addEventListener('input', handleTotalEarningsChange);
        totalEarningInput.addEventListener('change', handleTotalEarningsChange);
    }

    // ========== EMPLOYER DEDUCTIONS EVENT LISTENERS ==========
    const employerDeductionInputs = document.querySelectorAll('input[name^="employerDeductions["]');
    employerDeductionInputs.forEach(input => {
        input.addEventListener('input', function() {
            calculateTotalEmployerDeductions();
            calculateCTC();
            validateAllCalculations();
        });
    });

    // ========== EMPLOYEE DEDUCTIONS EVENT LISTENERS ==========
    const deductionInputs = document.querySelectorAll('input[name^="deductions["]');
    deductionInputs.forEach(input => {
        input.addEventListener('input', function() {
            calculateTotalDeductions();
        });
    });

    // ========== NET SALARY EVENT LISTENER ==========
    const netSalaryInput = document.querySelector('#monthly_net_salary');
    if (netSalaryInput) {
        netSalaryInput.addEventListener('input', function() {
            validateAllCalculations();
        });
    }

    // ========== FORM SUBMISSION ==========
    const form = document.querySelector('#smform');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Run final validation
            if (!validateAllCalculations()) {
                e.preventDefault();

                // Show specific error message for allowances sum
                const totalAllowances = calculateSumOfAllowances();
                const totalEarnings = parseFloat(totalEarningInput.value) || 0;

                if (Math.abs(totalAllowances - totalEarnings) > 0.01) {
                    alert(`Cannot submit form!\n\nSum of all allowances (${totalAllowances.toFixed(2)}) does not match Total Earnings (${totalEarnings.toFixed(2)}).\n\nPlease adjust your allowances to match the Total Earnings.`);
                }
                return false;
            }

            if (!isValidationPassed) {
                e.preventDefault();
                alert('Cannot submit form. Please fix validation errors.');
                return false;
            }
        });
    }

    // ========== INITIAL CALCULATIONS ==========
    // Set initial values to 0 if empty
    const initialInputs = document.querySelectorAll('input[name^="earnings["], input[name^="deductions["], input[name^="employerDeductions["]');
    initialInputs.forEach(input => {
        if (!input.value || input.value === '' || isNaN(parseFloat(input.value))) {
            input.value = '0.00';
        } else {
            // Format existing values
            const numValue = parseFloat(input.value);
            if (!isNaN(numValue)) {
                input.value = numValue.toFixed(2);
            }
        }
    });

    // Format main fields
    const mainFields = [
        'es_monthly_gross',
        'annual_gross',
        'es_monthly_ctc',
        'es_annual_ctc',
        'total_employee_earning',
        'total_employee_deduction',
        'total_employer_deduction',
        'monthly_net_salary',
        'es_per_day_wage'
    ];

    mainFields.forEach(fieldName => {
        const field = document.querySelector(`input[name="${fieldName}"]`);
        if (field && field.value) {
            const numValue = parseFloat(field.value);
            if (!isNaN(numValue)) {
                field.value = numValue.toFixed(2);
            }
        }
    });

    // Initialize Total Earnings with Monthly Gross value
    if (monthlyGrossInput && monthlyGrossInput.value) {
        const monthlyGrossValue = parseFloat(monthlyGrossInput.value) || 0;
        if (totalEarningInput) {
            totalEarningInput.value = monthlyGrossValue.toFixed(2);
        }
    }

    // Now run all calculations
    calculateAnnualGross();
    calculatePerDayWage();
    calculateTotalEmployerDeductions();
    calculateCTC();
    calculateTotalDeductions();
    calculateNetSalary();
    validateAllCalculations();

    // Initial button state
    updateSaveButton();

    // Show initial validation message if needed
    setTimeout(() => {
        const totalAllowances = calculateSumOfAllowances();
        const totalEarnings = parseFloat(totalEarningInput.value) || 0;

        // if (Math.abs(totalAllowances - totalEarnings) > 0.01) {
        //     alert(`ATTENTION: Sum of all allowances (${totalAllowances.toFixed(2)}) does not match Total Earnings (${totalEarnings.toFixed(2)}).\n\nPlease adjust your allowances to match the Total Earnings.`);
        // }
    }, 1000);
});


// View Salary History Modal Function
// View Salary History Modal Function (employeeSalaryHistoryModal के लिए)
function openSalaryHistoryModal(element) {
    // Get all data attributes
    const data = element.dataset;

    // Format numbers function
    function formatCurrency(value) {
        if (!value) return '₹ 0.00';
        const num = parseFloat(value);
        if (isNaN(num)) return '₹ 0.00';
        return '₹ ' + num.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Populate modal fields
    document.getElementById('from_date').textContent = data.from_date || 'N/A';
    document.getElementById('to_date').textContent = data.to_date || 'N/A';

    // Set values for employeeSalaryHistoryModal
    document.getElementById('monthlyCtc').textContent = formatCurrency(data.monthly);
    document.getElementById('annualCtc').textContent = formatCurrency(data.annual);
    document.getElementById('basicSalary').textContent = formatCurrency(data.basic);
    document.getElementById('empHRA').textContent = formatCurrency(data.hra);
    document.getElementById('empDearAllow').textContent = formatCurrency(data.dear_allow);
    document.getElementById('empConvAllow').textContent = formatCurrency(data.conv_allow);
    document.getElementById('empOtherAllow').textContent = formatCurrency(data.other_allow);
    document.getElementById('empEpf').textContent = formatCurrency(data.employee_epf);
    document.getElementById('empEsic').textContent = formatCurrency(data.employee_esic);
    document.getElementById('empLwf').textContent = formatCurrency(data.employee_lwf);
    document.getElementById('empDeductions').textContent = formatCurrency(data.employee_total_ded);
    document.getElementById('employerEpf').textContent = formatCurrency(data.employer_epf);
    document.getElementById('employerEsic').textContent = formatCurrency(data.employer_esic);
    document.getElementById('employerLwf').textContent = formatCurrency(data.employer_lwf);
    document.getElementById('employerTDeductions').textContent = formatCurrency(data.employer_total_ded);
    document.getElementById('empEarnings').textContent = formatCurrency(data.total_earning);
    document.getElementById('empGrossPay').textContent = formatCurrency(data.gross_pay);
    document.getElementById('empNetPay').textContent = formatCurrency(data.net_pay);

    // Show modal using Bootstrap 5 with proper event handling
    const modalElement = document.getElementById('employeeSalaryHistoryModal');
    if (modalElement) {
        // Remove any existing event listeners to prevent multiple bindings
        $(modalElement).off('shown.bs.modal hidden.bs.modal');

        // Show modal
        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        // Add event listener for when modal is fully hidden
        $(modalElement).on('hidden.bs.modal', function() {
            // Clean up any modal-related issues
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });
    }
}

// Attach click event to all view buttons when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all view buttons
    const viewButtons = document.querySelectorAll('.openBtn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            openSalaryHistoryModal(this);
        });
    });

    // Also handle jQuery click for backward compatibility
    $(document).on('click', '.openBtn', function(e) {
        e.preventDefault();
        openSalaryHistoryModal(this);
    });
});

// Print function for employeeSalaryHistoryModal
function printEmployeeSalaryHistory() {
    const printWindow = window.open('', '_blank');
    const modalContent = document.getElementById('employeeSalaryHistoryModal').innerHTML;

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Salary Details - Print</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .print-header { text-align: center; margin-bottom: 30px; }
                .print-header h2 { color: #007bff; }
                .user-info { display: flex; align-items: center; margin-bottom: 20px; }
                .avatar { width: 80px; height: 80px; border-radius: 50%; margin-right: 20px; }
                .table-print { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .table-print td { padding: 10px; border: 1px solid #ddd; }
                .table-print .vertical-text { writing-mode: vertical-rl; transform: rotate(180deg); }
                .employer-cell { background-color: #f0f8ff; font-weight: bold; text-align: center; }
                .text-blue { color: #007bff; }
                @media print {
                    body { -webkit-print-color-adjust: exact; }
                    .no-print { display: none !important; }
                    .modal-footer { display: none !important; }
                    .btn-close { display: none !important; }
                }
            </style>
        </head>
        <body>
            ${modalContent}
        </body>
        </html>
    `);

    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

// Print modal content function
// Print modal content function
function printModalContent() {
    const printWindow = window.open('', '_blank');
    const modalContent = document.getElementById('salaryHistoryModal').innerHTML;

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Salary Details - Print</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .print-header { text-align: center; margin-bottom: 30px; }
                .print-header h2 { color: #007bff; }
                .section { margin-bottom: 20px; }
                .section-title { background-color: #f8f9fa; padding: 10px; font-weight: bold; border-bottom: 2px solid #007bff; }
                .table-print { width: 100%; border-collapse: collapse; }
                .table-print td { padding: 8px; border-bottom: 1px solid #ddd; }
                .table-print .total-row { background-color: #f2f2f2; font-weight: bold; }
                .net-pay { background-color: #28a745 !important; color: white; padding: 15px; text-align: center; border-radius: 5px; }
                .net-pay h3 { margin: 0; }
                @media print {
                    body { -webkit-print-color-adjust: exact; }
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            ${modalContent}
        </body>
        </html>
    `);

    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

// Attach click event to all view buttons when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all view buttons
    const viewButtons = document.querySelectorAll('.openBtn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openSalaryHistoryModal(this);
        });
    });

    // Also handle jQuery click for backward compatibility
    $(document).off('click', '.openBtn').on('click', '.openBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openSalaryHistoryModal(this);
    });

    // Handle modal close button
    $(document).on('click', '#employeeSalaryHistoryModal .btn-close, #employeeSalaryHistoryModal .cancel', function(e) {
        e.preventDefault();
        const modal = bootstrap.Modal.getInstance(document.getElementById('employeeSalaryHistoryModal'));
        if (modal) {
            modal.hide();
        }
    });

    // Prevent body scroll when modal is open
    $(document).on('show.bs.modal', '.modal', function() {
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        $('body').css('padding-right', scrollbarWidth);
    });

    $(document).on('hidden.bs.modal', '.modal', function() {
        $('body').css('padding-right', '');
    });
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
@endsection
