@extends('admin.layout.master')
@section('title')
{{ $title }}
@endsection
@section('css')
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
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    th, td {
        padding: 12px 15px;
        text-align: center;
        border: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
    }

    td {
        background-color: #f9f9f9;
    }

    tr:nth-child(even) {
        background-color: #f1f1f1;
    }

    tr:hover {
        background-color: #e0e0e0;
    }
</style>
@endsection

@section('content')
<!-- PAGE HEADER -->
<div class="page-header d-xl-flex d-block">
    <div class="page-leftheader">
        <div class="page-title">Add Payroll</div>
    </div>
    <div class="page-rightheader ms-md-auto">
        <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
            <div class="btn-list">
                <button class="btn btn-outline-danger me-3" data-bs-toggle="modal" data-bs-target="#excelmodal">
                    <i class="las la-file-excel"></i> Download Monthly Excel Report
                </button>
                <button class="btn btn-light" data-bs-toggle="tooltip" data-bs-placement="top" title="E-mail"> <i
                        class="feather feather-mail"></i> </button>
                <button class="btn btn-light" data-bs-placement="top" data-bs-toggle="tooltip" title="Contact"> <i
                        class="feather feather-phone-call"></i> </button>
                <button class="btn btn-outline-primary" data-bs-placement="top" data-bs-toggle="tooltip" title="Info"> <i
                        class="feather feather-info"></i> </button>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE HEADER -->

<!-- ROW -->
<form action="{{ route('save.employee.payroll') }}" method="post">
    @csrf

    <div class="row">
        <!-- Salary Information -->
        <div class="card mb-4">
            <div class="card-body">
                <h4><center type="button" data-toggle="collapse" data-target="#salaryInfoCardBody" aria-expanded="false" aria-controls="salaryInfoCardBody">Employee Name : {{ $employee->emp_full_name }} </center></h4>
                <h4 class="card-title">
                    Salary Information
                </h4>
                <input type="hidden" name="emp_id" value="{{ $emp_actual_id }}">
                <input type="hidden" name="business_id" value="{{ $business_id }}">
                <div id="salaryInfoCardBody" class="card-body collapse show">
                    <div class="row">
                        <div class="row mb-3 ">
                            <div class="col-md-3">
                                <label class="form-label"><strong>Calculation Mode</strong></label>
                                <div>
                                    <label class="me-3">
                                        <input type="radio" name="payroll_mode" value="auto"
                                            {{ (isset($smhistoryLastData->sm_is_employer_deduction) && $smhistoryLastData->sm_is_employer_deduction == 'auto') || !isset($smhistoryLastData) ? 'checked' : '' }}
                                            onchange="toggleCalculationMode(this.value)"> Auto
                                    </label>
                                    <label>
                                        <input type="radio" name="payroll_mode" value="manual"
                                            {{ isset($smhistoryLastData->sm_is_employer_deduction) && $smhistoryLastData->sm_is_employer_deduction == 'manual' ? 'checked' : '' }}
                                            onchange="toggleCalculationMode(this.value)"> Manual
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="row mb-3 ">
                            <div class="col-md-4">
                                <label class="form-label"><strong>Add employer deductions to the employee's monthly CTC.</strong></label>
                                <div>
                                    <label class="me-3">
                                        <input type="radio" name="deduction_employer_mode" value="Yes" checked onchange="toggleEmployerDedMode(this.value)"> Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="deduction_employer_mode" value="No" onchange="toggleEmployerDedMode(this.value)"> No
                                    </label>
                                </div>
                            </div>
                        </div> -->
                        <div class="row align-items-center">
                            <!-- Monthly CTC -->
                            <div class="col-md-1">
                                <label class="form-label mb-0 mt-2">
                                    <i class="fa fa-money-bill"></i> Monthly CTC <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control numericInput" name="es_monthly_ctc" id="es_monthly_ctc"
                                    placeholder="0" value="{{ old('es_monthly_ctc', $emp_salary->es_monthly_ctc ?? '') }}"
                                    oninput="calculateCTC('monthly')" required>
                            </div>
                            <!-- Annual CTC -->
                            <div class="col-md-1">
                                <label class="form-label mb-0 mt-2">
                                    <i class="fa fa-calendar-alt"></i> Annual CTC <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control numericInput" name="es_annual_ctc" id="es_annual_ctc" placeholder="0" value="{{ $emp_salary->es_annual_ctc ?? '' }}" oninput="calculateCTC('annual')" required>
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
                            <div class="col-md-6 mb-3">
                                <label><i class="fa fa-dollar-sign"></i> {{ $earning->sa_title }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control calculated-value numericInput"
                                    name="earnings[{{ $earning->sa_id }}]"
                                    value="{{ $emp_earnings[$earning->sa_id] ?? '' }}"
                                    data-calculation-type="{{ $earning->sa_calculation_type }}"
                                    data-threshold="{{ $earning->sa_threshold_value }}"
                                    data-percentage="{{ $earning->sa_percentage ?? 0 }}"
                                    data-fixed="{{ $earning->sa_fixed_value ?? 0 }}"
                                    data-earning-type-id="{{ $earning->sa_earning_type_id }}"
                                    data-earning-cal-type-id="{{ $earning->sa_calculation_type }}"
                                    id="earning_{{ $earning->sa_id }}" placeholder="Final Value" {{ $earning->sa_calculation_type != 348 ? 'readonly' : '' }} required>
                            </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <!-- Other Allowance Row -->
                            <div class="col-md-6">
                                <label><strong>Other Allowance</strong></label>
                                <input type="text" class="form-control numericInput" name="es_rem_allowance" id="other_allowance" placeholder="Other Allowance" value="{{ !empty($smhistoryLastData->sm_other_allow) ? $smhistoryLastData->sm_other_allow : '' }}" required>
                            </div>
                            <!-- Total Earnings Row -->
                            <div class="col-md-6">
                                <label><strong>Total Earnings</strong></label>
                                <input type="text" class="form-control numericInput" name="total_employee_earning" id="total_earnings" value="{{ !empty($smhistoryLastData->sm_total_earning) ? $smhistoryLastData->sm_total_earning : '' }}" placeholder="Total Earnings" readonly required>
                            </div>
                        </div><br>
                        <div class="row" >
                            <div class="col-md-6 mb-3">
                                <label>Gross Pay <span class="text-danger">*</span></label>
                                <input type="text" class="form-control numericInput" name="monthly_gross" id="monthly_gross" readonly placeholder="0" value="{{ !empty($smhistoryLastData->sm_gross_pay) ? $smhistoryLastData->sm_gross_pay : '' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Take Home Salary(Net Pay) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control numericInput" name="monthly_net_salary" id="monthly_net_salary" readonly placeholder="0" value="{{ !empty($smhistoryLastData->sm_net_pay) ? $smhistoryLastData->sm_net_pay : '' }}" required>
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
                            <label class="float-right">Total : <span id="total_emp_ded"></span> </label>
                        </h5>
                    </div>
                    <div id="deductionsCardBody" class="card-body collapse show">
                        <div class="row">
                            @foreach ($deductions as $deduction)
                                @if($deduction->deduction_type_name == 'EPF' && $employee->emp_is_pf_enabled == 121)
                                    @continue
                                @endif
                                @if($deduction->deduction_type_name == 'ESIC ' && $employee->emp_esic_limit == 121)
                                    @continue
                                @endif
                                <div class="col-md-12 mb-3">
                                    <label>{{ $deduction->deduction_type_name }}</label>

                                    <input type="text" class="form-control deduction-value numericInput"
                                        name="deductions[{{ $deduction->std_id }}]"
                                        value="{{ $emp_deductions[$deduction->std_id] ?? '' }}"
                                        data-threshold="{{ $deduction->std_threshold }}"
                                        data-employee-rate="{{ $deduction->std_employee_contri_rate_amount }}"
                                        data-employer-rate="{{ $deduction->std_employer_contri_rate_amount }}"
                                        data-deduction-cycle="{{ $deduction->std_deduction_cycle_id }}"
                                        data-deduction-type-id="{{ $deduction->std_deduction_type_id }}"
                                        data-sa-consider-for-pf="{{ $deduction->salaryAllowance->sa_consider_for_pf ?? 0 }}"
                                        placeholder="Deduction Value" readonly>
                                </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label><strong>Total Deductions</strong></label>
                                <input type="text" class="form-control numericInput" name="total_employee_deduction"
                                    id="total_employee_deduction" readonly placeholder="0" value="{{ !empty($smhistoryLastData->sm_employee_total_ded) ? $smhistoryLastData->sm_employee_total_ded : '' }}">
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
                            <label class="float-right">Total : <span id="total_employer_deds"></span> </label>
                        </h5>
                    </div>
                    <div id="deductionsCardBodyE" class="card-body collapse show">
                        <div class="row">
                            @foreach ($employerDeductions as $deduction)
                                @if($deduction->deduction_type_name == 'EPF' && $employee->emp_is_pf_enabled == 121)
                                    @continue
                                @endif
                                @if($deduction->deduction_type_name == 'ESIC ' && $employee->emp_esic_limit == 121)
                                    @continue
                                @endif
                                <div class="col-md-12 mb-3">
                                    <label>{{ $deduction->deduction_type_name }}</label>
                                    <input type="text" class="form-control employer-deduction-value numericInput"
                                        name="employerDeductions[{{ $deduction->std_id }}]"
                                        value="{{ $emp_deductions[$deduction->std_id] ?? '' }}"
                                        data-threshold="{{ $deduction->std_threshold }}"
                                        data-employee-rate="{{ $deduction->std_employee_contri_rate_amount }}"
                                        data-employer-rate="{{ $deduction->std_employer_contri_rate_amount }}"
                                        data-deduction-cycle="{{ $deduction->std_deduction_cycle_id }}"
                                        data-deduction-type-id="{{ $deduction->std_deduction_type_id }}"
                                        data-sa-consider-for-pf="{{ $deduction->salaryAllowance->sa_consider_for_pf ?? 0 }}"
                                        placeholder="Deduction Value" readonly>

                                </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label><strong>Total Deductions</strong></label>
                                <input type="text" class="form-control numericInput" name="total_employer_deduction"
                                    id="total_employer_deduction" value="{{ !empty($smhistoryLastData->sm_employer_total_ded) ? $smhistoryLastData->sm_employer_total_ded : '' }}" readonly placeholder="0">
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
                    Summary
                    <!-- Add a button to toggle the collapse -->
                    <button class="btn btn-link float-right" type="button" data-toggle="collapse" data-target="#summaryCardBody" aria-expanded="false" aria-controls="summaryCardBody">
                        <i class="fa fa-chevron-down" style="line-height: 0.5;"></i>
                    </button>
                </h5>
            </div>
            <div id="summaryCardBody" class="card-body collapse show">
                <div class="row d-flex align-items-center">
                    <table style="width:100%">
                        <tr>
                            @if($smhistory->first()->sm_monthly_ctc) <th>Monthly CTC</th> @endif
                            @if($smhistory->first()->sm_annual_ctc) <th>Annual CTC</th> @endif
                            @if($smhistory->first()->sm_basic) <th>Basic</th> @endif
                            @if($smhistory->first()->sm_hra) <th>HRA</th> @endif
                            @if($smhistory->first()->sm_dear_allow) <th>DA</th> @endif
                            @if($smhistory->first()->sm_conv_allow) <th>CA</th> @endif
                            @if($smhistory->first()->sm_med_allow) <th>MA</th> @endif
                            @if($smhistory->first()->sm_edu_allow) <th>EA</th> @endif
                            @if($smhistory->first()->sm_spec_allow) <th>SA</th> @endif
                            @if($smhistory->first()->sm_other_allow) <th>OA</th> @endif
                            @if($smhistory->first()->sm_employee_epf) <th>EPFO(E)</th> @endif
                            @if($smhistory->first()->sm_employee_esic) <th>ESIC(E)</th> @endif
                            @if($smhistory->first()->sm_employee_lwf) <th>LWF(E)</th> @endif
                            @if($smhistory->first()->sm_employee_total_ded) <th>Total(E)</th> @endif
                            @if($smhistory->first()->sm_employer_epf) <th>EPFO(C)</th> @endif
                            @if($smhistory->first()->sm_employer_esic) <th>ESIC(C)</th> @endif
                            @if($smhistory->first()->sm_employer_lwf) <th>LWF(C)</th> @endif
                            @if($smhistory->first()->sm_employer_total_ded) <th>Total(C)</th> @endif
                            @if($smhistory->first()->sm_total_earning) <th>Total Earning</th> @endif
                            @if($smhistory->first()->sm_gross_pay) <th>Gross Pay</th> @endif
                            @if($smhistory->first()->sm_net_pay) <th>Net Pay</th> @endif
                        </tr>

                        @foreach($smhistory as $smhist)
                            <tr>
                                @if($smhist->sm_monthly_ctc ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_monthly_ctc }}</td> @endif
                                @if($smhist->sm_annual_ctc ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_annual_ctc }}</td> @endif
                                @if($smhist->sm_basic ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_basic }}</td> @endif
                                @if($smhist->sm_hra ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_hra }}</td> @endif
                                @if($smhist->sm_dear_allow ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_dear_allow }}</td> @endif
                                @if($smhist->sm_conv_allow ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_conv_allow }}</td> @endif
                                @if($smhist->sm_med_allow ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_med_allow }}</td> @endif
                                @if($smhist->sm_edu_allow ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_edu_allow }}</td> @endif
                                @if($smhist->sm_spec_allow ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_spec_allow }}</td> @endif
                                @if($smhist->sm_other_allow ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_other_allow }}</td> @endif
                                @if($smhist->sm_employee_epf ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_employee_epf }}</td> @endif
                                @if($smhist->sm_employee_esic ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_employee_esic }}</td> @endif
                                @if($smhist->sm_employee_lwf ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_employee_lwf }}</td> @endif
                                @if($smhist->sm_employee_total_ded ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_employee_total_ded }}</td> @endif
                                @if($smhist->sm_employer_epf ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_employer_epf }}</td> @endif
                                @if($smhist->sm_employer_esic ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_employer_esic }}</td> @endif
                                @if($smhist->sm_employer_lwf ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_employer_lwf }}</td> @endif
                                @if($smhist->sm_employer_total_ded ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_employer_total_ded }}</td> @endif
                                @if($smhist->sm_total_earning ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_total_earning }}</td> @endif
                                @if($smhist->sm_gross_pay ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_gross_pay }}</td> @endif
                                @if($smhist->sm_net_pay ?? 'N/A' != 'N/A') <td>{{ $smhist->sm_net_pay }}</td> @endif
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Buttons -->
        <div class="text-end">
            <button class="btn btn-outline-primary btn-lg submitBtn">Save</button>
            <button type="button" onclick="window.history.back()" class="btn btn-outline-danger  btn-lg">Cancel</button>
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
function allowDecimalInput(event) {
    let input = event.target;
    input.value = input.value.replace(/[^0-9.]/g, ''); // Allow only numbers and dot
    if ((input.value.match(/\./g) || []).length > 1) {
        input.value = input.value.replace(/\.+$/, ''); // Remove extra dots
    }
}

// Attach event listener to all elements with class "numericInput"
document.querySelectorAll(".numericInput").forEach(input => {
    input.addEventListener("input", allowDecimalInput);
});
</script>

<script>
    var checkMode = document.querySelector('input[name="payroll_mode"]:checked')?.value || 'auto';
    var dedEmpMode = document.querySelector('input[name="deduction_employer_mode"]:checked')?.value || 'Yes';
    var isPFEnabled = '{{ $employee->emp_is_pf_enabled }}';
    var isESICEnabled = '{{ $employee->emp_esic_limit }}';
    $(document).ready(function () {
        const conveyAllowId = 44;
        const $earningField = $('#earning_' + conveyAllowId);
        const $submitBtn = $('.submitBtn');
        const $otherAllow = $('#other_allowance');
        const $errorMessage = $earningField.next('span');

        $("input[name='payroll_mode']").change(function() {
            checkMode = $(this).val();
        });

        // Optimize the keyup event for earning field
        $earningField.on('keyup', function() {
            const otherAllow = $otherAllow.val();
            if (checkMode === 'auto') {
                if (otherAllow == 0) {
                    if ($errorMessage.length === 0) {
                        $earningField.after('<span style="color:red;">Value not supported</span>');
                        $submitBtn.prop('disabled', true);
                    }
                } else {
                    $submitBtn.prop('disabled', false);
                    $errorMessage.remove();
                }
            }
        });

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

    // --- CTC Calculation and Earnings ---
    function calculateCTC(type) {
        let monthlyInput = document.getElementById("es_monthly_ctc");
        let annualInput = document.getElementById("es_annual_ctc");

        if (type === 'monthly') {
            let monthlyCTC = parseFloat(monthlyInput.value) || 0;
            annualInput.value = (monthlyCTC * 12).toFixed(2);
        } else if (type === 'annual') {
            let annualCTC = parseFloat(annualInput.value) || 0;
            monthlyInput.value = (annualCTC / 12).toFixed(2);
        }

        if (checkMode === 'auto') {
            calculateEarnings();
            calculateEarningsAndOtherAllowance();
            calculateDeductions();
            calculateNetSalary();
        }
    }

    function calculateEarnings() {
        if (checkMode === 'auto') {
            // Get the monthly CTC value
            let monthlyCTC = parseFloat(document.getElementById("es_monthly_ctc")?.value) || 0;
            let totalEarnings = 0;
            let basicSalary = 0;

            // Calculate Basic Salary (Earning Type ID: 360)
            document.querySelectorAll(".calculated-value").forEach(input => {
                let saEarningTypeId = parseInt(input.dataset.earningTypeId) || 0;
                let threshold = parseFloat(input.dataset.threshold) || 0;

                if (saEarningTypeId === 360) {
                    basicSalary = (monthlyCTC * (threshold / 100)); // Calculate Basic Salary based on CTC
                    input.value = basicSalary.toFixed(2);
                    input.setAttribute("value", basicSalary.toFixed(2));
                }
            });

            // Calculate other earnings based on Basic/CTC/Flat
            document.querySelectorAll(".calculated-value").forEach(input => {
                let saEarningTypeId = parseInt(input.dataset.earningTypeId) || 0;
                let threshold = parseFloat(input.dataset.threshold) || 0;
                let calTypeId = parseInt(input.dataset.earningCalTypeId) || 0;
                let calculatedValue = 0;

                if (saEarningTypeId === 360) {
                    calculatedValue = basicSalary; // Already calculated
                } else if (calTypeId === 346) {
                    calculatedValue = (monthlyCTC * (threshold / 100)); // % of CTC
                } else if (calTypeId === 348) {
                    calculatedValue = threshold; // Flat Amount
                } else {
                    calculatedValue = (basicSalary * (threshold / 100)); // % of Basic
                }

                if (monthlyCTC == 0) {
                    input.setAttribute("value", 0);
                } else {
                    totalEarnings += calculatedValue;
                    input.value = calculatedValue.toFixed(2);
                    input.setAttribute("value", calculatedValue.toFixed(2));
                }
            });

            // Get Other Allowance Value if exists
            let otherAllowance = parseFloat(document.getElementById("other_allowance")?.value) || 0;

            // Update Total Earnings and Gross Pay in UI
            document.getElementById("total_earnings").value = totalEarnings.toFixed(2);

            // Calculate net salary and gross pay
            let monthlyNetSalary = parseFloat(document.getElementById("monthly_net_salary").value) || 0;
            let totalEmployeeDeduction = parseFloat(document.getElementById("total_employee_deduction").value) || 0;
            let totalEmployerDeduction = parseFloat(document.getElementById("total_employer_deduction").value) || 0;

            // if (dedEmpMode === 'No') {
            //     let grPay = grossPay + totalEmployerDeduction; // Ensure gross pay calculation here
            //     let netPay = monthlyNetSalary + totalEmployerDeduction; // Add Employer Deduction to Net Salary
            //     console.log("Net Pay (No Deductions Mode):", netPay);
            //     document.getElementById('monthly_gross').value = grPay.toFixed(2);
            //     document.getElementById('monthly_net_salary').value = netPay.toFixed(2);
            // } else {
            //     let grPay = monthlyCTC - totalEmployerDeduction;
            //     let netPay = grPay - totalEmployeeDeduction;
            //     console.log("Net Pay (With Deductions Mode):", netPay);
            //     document.getElementById('monthly_gross').value = grPay.toFixed(2);
            //     document.getElementById('monthly_net_salary').value = netPay.toFixed(2);
            // }
            let grPay = monthlyCTC - totalEmployerDeduction;
            console.log('grPay -> ' + grPay);
            document.getElementById('monthly_gross').value = grPay.toFixed(2);

            // Trigger other related calculations
            calculateNetSalary();
            calculateEarningsAndOtherAllowance(); // To adjust Other Allowance dynamically if needed
        }
    }


    function calculateNetSalary() {
        let totalEarnings = parseFloat(document.getElementById('total_earnings').value) || 0;
        let totalDeductions = parseFloat(document.getElementById('total_employee_deduction').value) || 0;
        let totalEmployerDeduction = parseFloat(document.getElementById('total_employer_deduction').value) || 0;
        let netSalary = totalEarnings - (totalDeductions + totalEmployerDeduction);

        document.getElementById('monthly_net_salary').value = netSalary.toFixed(2);
    }


    function calculateEarningsAndOtherAllowance() {
        let monthlyCTC = parseFloat(document.getElementById("es_monthly_ctc")?.value) || 0;
        let totalEarnings = 0;

        // Sum all earnings excluding Other Allowance
        document.querySelectorAll(".calculated-value").forEach(input => {
            totalEarnings += parseFloat(input.value) || 0;
        });

        if (checkMode === 'auto') {
            // Calculate and update Other Allowance
            let otherAllowance = Math.max(monthlyCTC - totalEarnings, 0);
            document.getElementById('other_allowance').value = otherAllowance.toFixed(2);

            // Update final Total Earnings
            let finalTotalEarnings = totalEarnings + otherAllowance;
            document.getElementById('total_earnings').value = finalTotalEarnings.toFixed(2);

            document.getElementById("total_earnings_head").innerHTML = finalTotalEarnings.toFixed(2);
        }
    }

    function calculateDeductions() {
        let totalDeductions = 0, totalEmployerContribution = 0;

        let monthlyCTC = parseFloat(document.getElementById("es_monthly_ctc")?.value) || 0;
        let grossSalary = parseFloat(document.getElementById("monthly_gross")?.value) || 0;
        let earningBasic = 0;

        // ---- Null check: If monthly CTC is 0 or null, exit early to avoid wrong calculation ----
        if (monthlyCTC === 0) {
            console.warn("Monthly CTC is zero or not defined, skipping deduction calculation.");
            return; // Stop calculation if monthly CTC is invalid
        }

        // ✅ Get Basic Salary
        document.querySelectorAll(".calculated-value").forEach(input => {
            if (parseInt(input.dataset.earningTypeId) === 360) {
                earningBasic = parseFloat(input.value) || 0;
            }
        });

        if (checkMode === 'auto') {
            // ✅ Calculate Employee Deductions
            document.querySelectorAll(".deduction-value").forEach(input => {
                let threshold = parseFloat(input.dataset.threshold) || 0;
                let employeeRate = parseFloat(input.dataset.employeeRate) || 0;
                let employerRate = parseFloat(input.dataset.employerRate) || 0;
                let deductionTypeId = parseInt(input.dataset.deductionTypeId) || 0;
                let employeeContribution = 0;
                let esicThreshold = 0;
                let isESICApplicable;

                if (deductionTypeId === 351) {
                    // Provident Fund / Fixed
                    employeeContribution = (earningBasic * employeeRate) / 100;
                } else if (deductionTypeId === 352) {
                    // Fixed
                    employeeContribution = (monthlyCTC * employeeRate) / 100;
                    esicThreshold = threshold;
                } else {
                    // General Deductions
                    employeeContribution = (earningBasic * employeeRate) / 100;
                }

                isESICApplicable = monthlyCTC > esicThreshold;
                if (isESICApplicable && deductionTypeId === 352) {
                    employeeContribution = 0;
                }

                totalDeductions += employeeContribution;

                // ✅ Safely update Deduction field
                if (input) {
                    input.value = employeeContribution.toFixed(2);
                    input.setAttribute("value", employeeContribution.toFixed(2));
                }
            });

            // ✅ Calculate Employer Deductions
            document.querySelectorAll(".employer-deduction-value").forEach(input => {
                let threshold = parseFloat(input.dataset.threshold) || 0;
                let employeeRate = parseFloat(input.dataset.employeeRate) || 0;
                let employerRate = parseFloat(input.dataset.employerRate) || 0;
                let deductionTypeId = parseInt(input.dataset.deductionTypeId) || 0;
                let employerContribution = 0;
                let esicThreshold1 = 0;
                let isESICApplicable1;

                if (deductionTypeId === 351) {
                    employerContribution = (earningBasic * employerRate) / 100;
                } else if (deductionTypeId === 352) {
                    employerContribution = (monthlyCTC * employerRate) / 100;
                    esicThreshold1 = threshold;
                } else {
                    employerContribution = (earningBasic * employerRate) / 100;
                }

                isESICApplicable1 = monthlyCTC > esicThreshold1;
                if (isESICApplicable1 && deductionTypeId === 352) {
                    employerContribution = 0;
                }
                totalEmployerContribution += employerContribution;

                // ✅ Safely update Deduction field
                if (input) {
                    input.value = employerContribution.toFixed(2);
                    input.setAttribute("value", employerContribution.toFixed(2));
                }
            });

            // ✅ Safely update Total Deductions & Employer Contribution if fields exist
            const totalEmployeeDeductionEl = document.getElementById("total_employee_deduction");
            // const totalEmployerContributionEl = document.getElementById("total_employer_contribution");
            const totalEmployerContributionEl = document.getElementById("total_employer_deduction");
            const monthlyNetSalaryEl = document.getElementById("monthly_net_salary");
            const totalEarningsEl = document.getElementById("total_earnings");

            if (totalEmployeeDeductionEl) totalEmployeeDeductionEl.value = totalDeductions.toFixed(2);
            if (totalEmployerContributionEl) totalEmployerContributionEl.value = totalEmployerContribution.toFixed(2);
            document.getElementById("total_emp_ded").innerHTML = totalDeductions.toFixed(2);
            document.getElementById("total_employer_deds").innerHTML = totalEmployerContribution.toFixed(2);

            // ✅ Safe Net Salary Calculation
            let totalEarnings = parseFloat(totalEarningsEl?.value) || 0;
            let netSalary = totalEarnings - (totalDeductions + totalEmployerContribution);

        if (monthlyNetSalaryEl) monthlyNetSalaryEl.value = netSalary.toFixed(2);
        }
    }

    function updateGrossPay() {
        let totalEarnings = parseFloat(document.getElementById('total_earnings').value) || 0;
        let otherAllowance = parseFloat(document.getElementById('other_allowance').value) || 0;
        let totalEmployerDeduction = parseFloat(document.getElementById('total_employer_deduction').value) || 0;
        let grossPay = totalEarnings - totalEmployerDeduction; // Since other allowance is now part of total earnings
        document.getElementById('monthly_gross').value = grossPay.toFixed(2);
    }

    // --- Initialization on Page Load ---
    document.addEventListener("DOMContentLoaded", function () {
        calculateEarnings();
        calculateEarningsAndOtherAllowance();
        calculateDeductions();
        calculateNetSalary();
        updateGrossPay();


        // Add event listeners for dynamic recalculation
        document.getElementById("es_monthly_ctc").addEventListener("input", function () {
            calculateEarnings();
            calculateEarningsAndOtherAllowance();
            calculateDeductions();
        });

        document.querySelectorAll(".calculated-value").forEach(input => {
            input.addEventListener("input", function () {
                calculateEarningsAndOtherAllowance();
                calculateDeductions();
            });
        });

        document.querySelectorAll(".deduction-value").forEach(input => {
            input.addEventListener("input", calculateDeductions);
        });

        document.querySelectorAll(".employer-deduction-value").forEach(input => {
            input.addEventListener("input", calculateDeductions);
        });

        document.getElementById("total_earnings").addEventListener("input", calculateDeductions);
    });

</script>

<script>
    let initialValues = {}; // Stores initial values for auto mode

    function storeInitialValues() {
        // Store only if not already set
        const earningFields = document.querySelectorAll('.calculated-value');
        const deductionFields = document.querySelectorAll('.deduction-value');
        const empDeductionFields = document.querySelectorAll('.employer-deduction-value');
        const otherAllowance = document.getElementById('other_allowance');
        const totalEarnings = document.getElementById('total_earnings');
        const totalDeductions = document.getElementById('total_employee_deduction');
        const grossPay = document.getElementById('monthly_gross');
        const netPay = document.getElementById('monthly_net_salary');
        const totalEmployerDeductions = document.getElementById('total_employer_deduction');

        if (Object.keys(initialValues).length === 0) {
            initialValues = {
                earnings: Array.from(earningFields).map(input => input.value),
                deductions: Array.from(deductionFields).map(input => input.value),
                deductionsEmployer: Array.from(empDeductionFields).map(input => input.value),
                otherAllowance: otherAllowance?.value || '',
                totalEarnings: totalEarnings?.value || '',
                totalDeductions: totalDeductions?.value || '',
                grossPay: grossPay?.value || '',
                netPay: netPay?.value || '',
                totalEmployerDeductions: totalEmployerDeductions?.value || '',
            };
        }
    }

    function toggleCalculationMode(mode) {
        const earningFields = document.querySelectorAll('.calculated-value');
        const deductionFields = document.querySelectorAll('.deduction-value');
        const empDeductionFields = document.querySelectorAll('.employer-deduction-value');
        const otherAllowance = document.getElementById('other_allowance');
        const totalEarnings = document.getElementById('total_earnings');
        const totalDeductions = document.getElementById('total_employee_deduction');
        const totalEmployerDeductions = document.getElementById('total_employer_deduction');
        const grossPay = document.getElementById('monthly_gross');
        const netPay = document.getElementById('monthly_net_salary');

        if (mode === 'manual') {
            storeInitialValues(); // 🟢 Store initial values only once

            // Make fields editable and clear values
            earningFields.forEach(input => {
                input.removeAttribute('readonly');
                // input.value = '';
            });

            deductionFields.forEach(input => {
                input.removeAttribute('readonly');
                // input.value = '';
            });

            empDeductionFields.forEach(input => {
                input.removeAttribute('readonly');
                // input.value = '';
            });

            if (otherAllowance) {
                otherAllowance.removeAttribute('readonly');
                // otherAllowance.value = '';
            }

            if (totalEarnings) {
                totalEarnings.removeAttribute('readonly');
                // totalEarnings.value = '';
            }

            if (totalDeductions) {
                totalDeductions.removeAttribute('readonly');
                // totalDeductions.value = '';
            }

            if (totalEmployerDeductions) {
                totalEmployerDeductions.removeAttribute('readonly');
                // totalEmployerDeductions.value = '';
            }

            if (grossPay) {
                grossPay.removeAttribute('readonly');
                // grossPay.value = '';
            }

            if (netPay) {
                netPay.removeAttribute('readonly');
                // netPay.value = '';
            }

        } else {
            // 🔵 Auto Mode: Restore previous values and make fields readonly
            earningFields.forEach((input, index) => {
                input.value = initialValues.earnings?.[index] || '';
                // input.setAttribute('readonly', true);
                if (input.getAttribute('data-earning-cal-type-id') == "348") {
                    input.removeAttribute('readonly');
                } else {
                    input.setAttribute('readonly', true);
                }
            });

            deductionFields.forEach((input, index) => {
                input.value = initialValues.deductions?.[index] || '';
                input.setAttribute('readonly', true);
            });

            empDeductionFields.forEach((input, index) => {
                input.value = initialValues.deductionsEmployer?.[index] || '';
                input.setAttribute('readonly', true);
            });

            if (otherAllowance) {
                otherAllowance.value = initialValues.otherAllowance || '';
                otherAllowance.setAttribute('readonly', true);
            }

            if (totalEarnings) {
                totalEarnings.value = initialValues.totalEarnings || '';
                totalEarnings.setAttribute('readonly', true);
            }

            if (totalDeductions) {
                totalDeductions.value = initialValues.totalDeductions || '';
                totalDeductions.setAttribute('readonly', true);
            }

            if (totalEmployerDeductions) {
                totalEmployerDeductions.value = initialValues.totalEmployerDeductions || '';
                totalEmployerDeductions.setAttribute('readonly', true);
            }

            if (grossPay) {
                grossPay.value = initialValues.grossPay || '';
                grossPay.setAttribute('readonly', true);
            }

            if (netPay) {
                netPay.value = initialValues.netPay || '';
                netPay.setAttribute('readonly', true);
            }
        }
    }

    // function toggleEmployerDedMode(mode) {
    //     // Parse the input values into numbers
    //     let monthlyCTC = parseFloat(document.getElementById("es_monthly_ctc")?.value) || 0;
    //     let monthly_gross = parseFloat(document.getElementById("monthly_gross").value) || 0;
    //     let monthlyNetSalary = parseFloat(document.getElementById("monthly_net_salary").value) || 0;
    //     let totalEmployeeDeduction = parseFloat(document.getElementById("total_employee_deduction").value) || 0;
    //     let totalEmployerDeduction = parseFloat(document.getElementById("total_employer_deduction").value) || 0;

    //     if (mode === 'No') {
    //         console.log(mode);
    //         // Calculate gross and net pay when 'No' mode is active
    //         let grPay = monthly_gross + totalEmployerDeduction;
    //         let netPay = monthlyNetSalary + totalEmployerDeduction;
    //         // Update the values in the input fields
    //         document.getElementById('monthly_gross').value = grPay.toFixed(2);
    //         document.getElementById('monthly_net_salary').value = netPay.toFixed(2);

    //     } else {
    //         console.log(mode);
    //         // Calculate gross and net pay when 'No' mode is active
    //         // let grPay = monthly_gross - totalEmployerDeduction;
    //         // let netPay = monthlyNetSalary - totalEmployerDeduction;
    //         let grPay = monthlyCTC - totalEmployerDeduction;
    //         let netPay = grPay - totalEmployeeDeduction;
    //         // Update the values in the input fields
    //         document.getElementById('monthly_gross').value = grPay.toFixed(2);
    //         document.getElementById('monthly_net_salary').value = netPay.toFixed(2);
    //     }
    // }

    // // 🌟 Page Load: Set Default Mode and Store Initial Values
    document.addEventListener('DOMContentLoaded', function () {
        const defaultMode = document.querySelector('input[name="payroll_mode"]:checked')?.value || 'auto';
        storeInitialValues(); // Store initial values on page load
        toggleCalculationMode(defaultMode);

        const esMonthlyCTC = document.getElementById("es_monthly_ctc");
        let monthlyCTInput = 0;
        if (esMonthlyCTC) {
            monthlyCTInput = parseFloat(esMonthlyCTC.value) || 0;
        }
        const inputsCal = document.querySelectorAll('input[data-calculation-type="348"]');
        if (monthlyCTInput <= 0) {
            inputsCal.forEach(function(input) {
                input.value = 0;
                input.setAttribute('value', 0);
            });
        }
    });
</script>

@endsection
