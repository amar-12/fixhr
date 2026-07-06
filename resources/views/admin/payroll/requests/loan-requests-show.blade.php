@extends('admin.layout.master')

@section('title', 'Salary Advance Details')

@section('content')
<input type="hidden" id="ajaxCall" value="{{ url('/') }}">
<div class="page-header d-md-flex d-block ">
    <div class="page-leftheader ">
        <div class="py-0 bd-highlight">
            <div>
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="/admin/requests/loan-requests">Loan/Advance</a></li>
                    <li class="active"><span><b>Loan/Advance Details</b></span></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- ROW -->
<div class="row">
    <div class="col-xl-3 col-md-12 col-lg-12">
        <div class="">
            <div class="card user-pro-list overflow-hidden">
                <div class="card-body ">
                    <div class="text-center">
                        <div class="widget-user-image mx-auto text-center">
                            <img class="avatar avatar-xxl brround" alt="img" src="{{ isset($data->fh_employee) && $data->fh_employee->emp_profile_photo
                                    ?  $data->fh_employee->emp_profile_photo
                                    : asset('assets/imgs/user.png') }}">
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
                                        <span>{{ $data->fh_employee->fh_branch->br_name ?? 'N/A' }}</span>
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
                                            {{ $data->fh_employee && $data->fh_employee->emp_status == 71 ? 'Active' :
                                            'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <section id="reverbDynamicSection" class="p-0 m-0">
                @if (count($data->fh_plan_approval_log) > 0)
                <div class="card">
                    <div class="card-header px-3">
                        <div class="card-title">Approval Details</div>
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
                                    @foreach ($data->fh_plan_approval_log as $item)
                                    <tr>
                                        <td class="col-md-4">{{ $item->fh_employee->emp_full_name ?? '' }}</td>
                                        <td class="col-md-4">{{ $item->fh_status->m_name }}</td>
                                        <td class="col-md-4 text-nowrap">{{ $item->log_description }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @elseif(count($data->fh_approval_log2) > 0)
                <div class="card ">
                    <div class="card-header px-3">
                        <div class="card-title">Approval Details</div>
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
                                        <td class="col-md-4">{{ $item->fh_employee->emp_full_name ?? '' }}</td>
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
    <div class="col-xl-9 col-md-12 col-lg-12">
        <div class="row">
            <div class="col-xl-12 col-md-12">
                <div class="card border overflow-hidden">
                    <div class="card-header p-3">
                        <div>
                            <h4 class="card-title">Loan/Advance Request</h4>
                        </div>
                    </div>

                    <div class="card-body pt-3">
                        <div class="row">
                            @php
                            $details = [
                            'Applied Date' => $data->created_at
                            ? $data->created_at->format('d-m-Y H:i:s')
                            : 'N/A',
                            'Requested Amount' => $data->lnr_requested_amount ?? 'N/A',
                            'Installment Amount' => $data->lnr_installment_amount ?? 'N/A',
                            'Reason' => $data->lnr_description ?? 'N/A',
                            'Approval Status' => optional($data->fh_approval_status)->m_name ?? 'N/A',
                            ];
                            @endphp

                            @foreach ($details as $label => $value)
                            <div class="col-xl-4 mb-3">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0">{{ $label }}:</h6>
                                    <div class="ms-2">{{ $value }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                    </div>
                    <br>

                  @if($data->lnr_request_status == 141)

                   {{-- <div class="card-header p-3">
                        <div>
                            <h4 class="card-title">Advance Request</h4>
                        </div>
                    </div> --}}

                        <!-- Repayment Schedule -->
                        <div class="d-flex justify-content-between align-items-center mb-3 card-header p-3">
                            <h5 class="card-title">Repayment Schedule</h5>
                            <small class="text-muted">
                                Installment Start Date: {{ \Carbon\Carbon::parse($data->lnr_start_date)->format('d M Y') }}
                            </small>
                        </div>

                  <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Installment No</th>
                                    <th>Opening Balance</th>
                                    <th>EMI Amount</th>
                                    <th>Principal</th>
                                    <th>Monthly Interest</th>
                                    <th>Outstanding Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($loan->fh_payroll_loan_installments) && count($loan->fh_payroll_loan_installments) > 0)
                                @php $openingBalance = $loan->lnr_requested_amount; @endphp
                                @foreach($loan->fh_payroll_loan_installments as $key => $inst)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $inst->pli_installment_no }}</td>

                                    {{-- ✅ Opening Balance from previous installment --}}
                                    <td>{{ number_format($openingBalance, 2) }}</td>

                                    {{-- ✅ EMI Amount --}}
                                    <td>{{ number_format($inst->pli_amount, 2) }}</td>

                                    {{-- ✅ Principal (Opening - Remaining Balance) --}}
                                    <td>{{ number_format($openingBalance - $inst->pli_rem_bal, 2) }}</td>

                                    {{-- ✅ Interest (EMI - Principal) --}}
                                    <td>{{ number_format($inst->pli_amount - ($openingBalance - $inst->pli_rem_bal), 2) }}</td>

                                    {{-- ✅ Closing / Remaining Balance --}}
                                    <td>{{ number_format($inst->pli_rem_bal, 2) }}</td>

                                    <td>
                                        <span class="badge
                                                    {{ $inst->pli_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                                            {{ ucfirst($inst->pli_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @php
                                // अगले installment के लिए Opening Balance = Current Remaining Balance
                                $openingBalance = $inst->pli_rem_bal;
                                @endphp
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>



                    @endif



                    @if ($approvalData)
                    <div class="card-header p-3">
                        <h3 class="card-title">Salary Advance Request Approval Or Reject</h3>
                    </div>
                    <div class="card-body">
                        <form id="approvalForm">
                            <div class="form-group">
                                <div class="row">
                                    <label class="form-label mb-0 mt-2">Message</label>
                                    <div class="col-md-12 col-lg-12">
                                        <textarea rows="2" name="message" class="form-control"
                                            id="actionMessage"></textarea>
                                    </div>
                                </div>

                                <br>
                                <div class="col-xl-12 col-md-12">
                                    <h3>Salary Advance Details</h3>

                                    <div class="card shadow-sm border-0 mb-4">
                                        {{-- <div
                                            class="card-header d-flex justify-content-between align-items-center bg-light text-dark dark:bg-dark dark:text-white">
                                            <div>
                                                <strong>Salary Advance Application</strong>
                                                <span class="badge bg-warning text-dark ms-2">Pending Approval</span>
                                            </div>
                                            <span>Ref #{{ $data->lnr_id }}</span>
                                        </div> --}}

                                        <div class="card-body bg-white text-dark dark:bg-gray-900 dark:text-white">
                                            <!-- Employee and Business Info -->
                                            {{-- <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="avatar me-3">
                                                            <img src="{{ $data->fh_employee->profile_image ?? 'https://ui-avatars.com/api/?name=' . urlencode($data->fh_employee->emp_full_name ?? '') }}"
                                                                class="rounded-circle" width="40" height="40"
                                                                alt="Employee">
                                                        </div>
                                                        <div>
                                                            <strong>Employee:</strong> {{
                                                            $data->fh_employee->emp_full_name ?? '-' }}<br>
                                                            <small class="text-muted">Emp Code: {{
                                                                $data->fh_employee->emp_code ?? '-' }}</small><br>
                                                            <small class="text-muted">Gross Salary:
                                                                ₹{{
                                                                number_format($data->fh_employee_salary->es_monthly_gross
                                                                ?? 0, 2) }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <div class="d-flex flex-column align-items-end">
                                                        <span class="badge bg-info mb-2">{{ $data->fh_business->name ??
                                                            '-' }}</span>
                                                    </div>
                                                </div>
                                            </div> --}}

                                            <!-- Loan Details Card -->
                                            <div class="card border-primary mb-4">
                                                <div class="card-header text-white">
                                                    <strong>Loan/Advance Details</strong>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <small class="text-muted">Type</small>
                                                                <h5>Loan/Advance</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <small class="text-muted">Loan Amount</small>
                                                                <h5>₹{{ number_format($data->lnr_requested_amount, 2) }}
                                                                </h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <small class="text-muted">Monthly Installment</small>
                                                                <h5>₹<span id="installmentAmount">{{
                                                                        number_format($data->lnr_installment_amount, 2)
                                                                        }}</span></h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <small class="text-muted">Tenure</small>
                                                                <h5>{{ $data->lnr_installments }} months</h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lnr_b_id" id="lnr_b_id"
                                                value="{{ $data->lnr_b_id }}">
                                            <input type="hidden" id="total-loan-amount"
                                                value="{{ $data->lnr_requested_amount }}">

                                            @php
                                            $monthlySalary = $data->fh_employee_salary->es_monthly_gross ?? 0;
                                            @endphp

                                            @if($monthlySalary < $data->lnr_requested_amount)
                                                <div class="d-flex align-items-center text-warning small mb-2">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <div>
                                                        <strong>Note:</strong> Monthly salary is less than loan amount.
                                                        Please enter interest rate.
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Calculation Type</label>
                                                        <select id="calculationType" class="form-select">
                                                            <option value="compound">Compound Interest (Reducing
                                                                Balance)</option>
                                                            <option value="flat">Simple Interest (Flat Rate)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="rate" class="form-label fw-bold">Interest Rate (%
                                                            per annum)</label>
                                                        <div class="input-group">
                                                            <input type="number" step="0.01" name="rate" id="rate"
                                                                class="form-control" required
                                                                value="{{ old('rate', $data->rate ?? '') }}"
                                                                placeholder="12.00">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>

                                                     <div class="col-md-2">
                                                        <label for="monthlyRate" class="form-label fw-bold">Monthly Rate (%)</label>
                                                        <div class="input-group">
                                                            <input type="text" id="monthlyRate" class="form-control" readonly>
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                             <!-- Repayment Schedule -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0">Repayment Schedule</h5>
                                                <small class="text-muted">Installment Start Date:
                                                    {{ \Carbon\Carbon::parse($data->lnr_start_date)->format('d M Y') }}
                                                </small>
                                            </div>
                                          <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-primary">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Month</th>
                                                        <th>Opening Balance</th>
                                                        <th>EMI Amount</th>
                                                        <th>Principal</th>
                                                        <th>Monthly Interest</th>
                                                        <th>Outstanding Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="loanSchedule">
                                                    @php
                                                        $openingBalance = $data->lnr_requested_amount;
                                                        $monthlyInterestRate = $data->lnr_interest_rate ? ($data->lnr_interest_rate / 100) / 12 : 0;

                                                        // Compound EMI Formula: EMI = P * r * (1 + r)^n / ((1 + r)^n - 1)
                                                        $factor = pow(1 + $monthlyInterestRate, $data->lnr_installments);
                                                        $emiAmount = ($monthlyInterestRate > 0)
                                                            ? ($openingBalance * $monthlyInterestRate * $factor) / ($factor - 1)
                                                            : ($openingBalance / $data->lnr_installments);
                                                    @endphp

                                                    @for($i = 0; $i < $data->lnr_installments; $i++)
                                                        @php
                                                            $monthlyInterest = $openingBalance * $monthlyInterestRate;
                                                            $principal = $emiAmount - $monthlyInterest;
                                                            if ($principal > $openingBalance) {
                                                                $principal = $openingBalance;
                                                            }
                                                            $closingBalance = $openingBalance - $principal;
                                                        @endphp

                                                        <tr>
                                                            <td>{{ $i + 1 }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($data->lnr_start_date)->addMonths($i)->format('F Y') }}</td>
                                                            <td class="openingBalance">{{ number_format($openingBalance, 2) }}</td>
                                                            <td>
                                                                <input type="number" step="0.01"
                                                                    class="form-control form-control-sm installmentAmountValue"
                                                                    value="{{ number_format($emiAmount, 2, '.', '') }}">
                                                            </td>
                                                            <td class="principalAmount">{{ number_format($principal, 2) }}</td>
                                                            <td class="interestAmount">{{ number_format($monthlyInterest, 2) }}</td>
                                                            <td class="remainingBalance">{{ number_format($closingBalance, 2) }}</td>
                                                        </tr>

                                                        @php
                                                            $openingBalance = $closingBalance;
                                                        @endphp
                                                    @endfor
                                                </tbody>
                                            </table>
                                        </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer mt-3">
                                    <div class="row">
                                        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                            <!-- Reject Button -->
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="0"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-lnr_id="{{ md5($data->lnr_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($data->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-danger actionBtn mx-3">
                                                Reject
                                            </button>

                                            <!-- Approval Button -->
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="1"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-lnr_id="{{ md5($data->lnr_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($data->fh_employee)->emp_d_id }}"
                                                class="btn btn-success actionBtn">
                                                {{ $approvalData?->fh_approver_status?->m_name }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    @elseif ($canApprove)
                    <x-approval-form :moduleName="$data?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn"
                        :primaryId="$data->lnr_id" :moduleId="$data->lnr_module_id"
                        actionUrl="{{route('approve.loan-request')}}" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END ROW -->
@endsection
@section('script')


<script>
// ============================
// Loan Schedule Dynamic Script
// ============================

// On change of installment amount
$(document).on('input', '.installmentAmountValue', function () {
    recalculateBalances();
});

// --------------------
// Recalculate Balances
// --------------------
function recalculateBalances() {
    const rows = document.querySelectorAll('#loanSchedule tr');
    let balance = parseFloat($('#total-loan-amount').val() || '{{ $loan->lnr_requested_amount }}');
    let totalPaid = 0;

    rows.forEach((row) => {
        const input = row.querySelector('.installmentAmountValue');
        const installment = parseFloat(input?.value) || 0;
        totalPaid += installment;
        balance = Math.max(parseFloat('{{ $loan->lnr_requested_amount }}') - totalPaid, 0);

        // Update balance cell
        const balanceCell = row.querySelector('.remainingBalance');
        if (balanceCell) {
            balanceCell.innerText = balance.toFixed(2);
        }
    });

    recalculateLoanSchedule();
}

// ------------------------------
// Main Loan Schedule Calculation
// ------------------------------
function recalculateLoanSchedule() {
    const calcTypeSelect = document.getElementById('calculationType');
    const rateInput = document.getElementById('rate');

    const rows = document.querySelectorAll('#loanSchedule tr');
    const loanAmount = parseFloat('{{ $loan->lnr_requested_amount }}');
    let balance = loanAmount;

    if (!calcTypeSelect || !rateInput || !rateInput.value) {
        rows.forEach((row) => {
            const emi = parseFloat(row.querySelector('.installmentAmountValue').value) || 0;

            // Opening balance = current balance before deducting EMI
            row.querySelector('.openingBalance').innerText = balance.toFixed(2);

            row.querySelector('.principalAmount').innerText = emi.toFixed(2);
            row.querySelector('.interestAmount').innerText = '0.00';
            balance = Math.max(balance - emi, 0);
            row.querySelector('.remainingBalance').innerText = balance.toFixed(2);
        });
        return;
    }

    const calcType = calcTypeSelect.value;
    const annualRate = parseFloat(rateInput.value) / 100;
    const monthlyRate = annualRate / 12;

    rows.forEach((row) => {
        const emi = parseFloat(row.querySelector('.installmentAmountValue').value) || 0;

        // Opening balance before deductions
        row.querySelector('.openingBalance').innerText = balance.toFixed(2);

        let monthlyInterest = 0;
        let principal = 0;

        if (calcType === 'compound') {
            monthlyInterest = balance * monthlyRate;
            principal = Math.max(emi - monthlyInterest, 0);
            balance = Math.max(balance - principal, 0);
        } else if (calcType === 'flat') {
            monthlyInterest = loanAmount * monthlyRate;
            principal = Math.max(emi - monthlyInterest, 0);
            balance = Math.max(balance - principal, 0);
        }

        row.querySelector('.principalAmount').innerText = principal.toFixed(2);
        row.querySelector('.interestAmount').innerText = monthlyInterest.toFixed(2);
        row.querySelector('.remainingBalance').innerText = balance.toFixed(2);
    });
}


// -------------------------
// Calculate Installment EMI
// -------------------------
function calculateInstallmentAmount() {
    const loanAmount = parseFloat('{{ $loan->lnr_requested_amount }}');
    const rateInput = document.getElementById('rate');
    const calcTypeSelect = document.getElementById('calculationType');

    if (!rateInput || !calcTypeSelect || !rateInput.value) return;

    const rate = parseFloat(rateInput.value);
    const calcType = calcTypeSelect.value;
    const installments = {{ $loan->lnr_installments }};
    const annualRate = rate / 100;
    const monthlyRate = annualRate / 12;

    let newInstallmentAmount = 0;

    if (rate > 0) {
        if (calcType === 'compound') {
            if (monthlyRate > 0) {
                const factor = Math.pow(1 + monthlyRate, installments);
                newInstallmentAmount = loanAmount * monthlyRate * factor / (factor - 1);
            } else {
                newInstallmentAmount = loanAmount / installments;
            }
        } else if (calcType === 'flat') {
            const totalInterest = loanAmount * annualRate * (installments / 12);
            const totalRepayment = loanAmount + totalInterest;
            newInstallmentAmount = totalRepayment / installments;
        }
    } else {
        newInstallmentAmount = loanAmount / installments;
    }

    document.getElementById('installmentAmount').innerText = newInstallmentAmount.toFixed(2);

    const rows = document.querySelectorAll('#loanSchedule tr');
    rows.forEach((row) => {
        row.querySelector('.installmentAmountValue').value = newInstallmentAmount.toFixed(2);
    });

    recalculateLoanSchedule();
}

// --------------------------
// Event Listeners on Load
// --------------------------
document.addEventListener('DOMContentLoaded', function () {
    const rateInput = document.getElementById('rate');
    const monthlyRateInput = document.getElementById('monthlyRate'); // New monthly rate field

    if (rateInput) {
        rateInput.addEventListener('input', function () {
            updateMonthlyRate();
            calculateInstallmentAmount();
        });
    }

    const calcTypeSelect = document.getElementById('calculationType');
    if (calcTypeSelect) calcTypeSelect.addEventListener('change', calculateInstallmentAmount);

    document.querySelectorAll('.installmentAmountValue').forEach(input => {
        input.addEventListener('input', recalculateBalances);
    });

    if (rateInput && rateInput.value) {
        updateMonthlyRate();
        calculateInstallmentAmount();
    } else {
        recalculateLoanSchedule();
    }

    function updateMonthlyRate() {
        const annualRate = parseFloat(rateInput.value) || 0;
        const monthlyRate = annualRate / 12;
        if (monthlyRateInput) {
            monthlyRateInput.value = monthlyRate.toFixed(4); // Show up to 4 decimals
        }
    }
});


// ====================
// Approve Loan Handler
// ====================
$(document).on('click', '.approve-loan-btn', function () {
    let loanId = $(this).data('loan-id');
    const lnr_b_id = $('#lnr_b_id').val();

    let formData = {
        _token: '{{ csrf_token() }}',
        status: 'approved',
        rate: $('#rate').val() || 0,
        calculation_type: $('#calculationType').val() || 'flat',
        lnr_b_id: lnr_b_id,
        installments: []
    };

    $('#loanSchedule tr').each(function (index) {
        const $row = $(this);
        formData.installments.push({
            installment_number: index + 1,
            amount: parseFloat($row.find('.installmentAmountValue').val() || 0).toFixed(2),
            principal_amount: parseFloat($row.find('.principalAmount').text() || 0).toFixed(2),
            interest_amount: parseFloat($row.find('.interestAmount').text() || 0).toFixed(2),
            remaining_balance: parseFloat($row.find('.remainingBalance').text() || 0).toFixed(2)
        });
    });

    Swal.fire({
        title: 'Approve Loan',
        text: 'Are you sure you want to approve this loan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('save.loan.details', '') }}/" + loanId,
                type: 'POST',
                data: formData,
                success: function (detailsResponse) {
                    if (detailsResponse.success) {
                        $.ajax({
                            url: "{{ route('loan.approve', '') }}/" + loanId,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire('Approved', response.message, 'success');
                                    $('#loanModal').modal('hide');
                                    location.reload();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error', 'Approval failed', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Error', detailsResponse.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Failed to save details', 'error');
                }
            });
        }
    });
});

// ====================
// Reject Loan Handler
// ====================
$(document).on('click', '.reject-loan-btn', function () {
    let loanId = $('#loanModal').data('loan-id');
    $('#loanModal').modal('hide');

    setTimeout(() => {
        Swal.fire({
            title: 'Reject Loan',
            text: 'Are you sure you want to reject this loan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason',
            inputAttributes: {
                'aria-label': 'Enter rejection reason',
                'rows': 4,
                'class': 'swal2-textarea',
                'style': 'resize: vertical; min-height: 100px;'
            },
            preConfirm: (reason) => {
                if (!reason) {
                    Swal.showValidationMessage('You need to enter a rejection reason!');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                $.ajax({
                    url: "{{ route('loan.reject', '') }}/" + loanId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reason: result.value
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Rejected', response.message, 'success');
                            location.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Failed to reject loan', 'error');
                    }
                });
            }
        });
    }, 300);
});
</script>


<script>
    // Handle loan approval
    $(document).on('click', '.approve-loan-btn', function () {
        // let loanId = $('#loanModal').data('loan-id');
        let loanId = $(this).data('loan-id');

        // Get the total loan amount dynamically from the DOM
        // This avoids the PHP variable hardcoding issue
        const totalLoan = parseFloat($('#total-loan-amount').text().replace(/[^0-9.]/g, '')) || 0;
        const lnr_b_id = $('#lnr_b_id').val();

        // Collect form data
        let formData = {
            _token: '{{ csrf_token() }}',
            status: 'approved',
            rate: $('#rate').length ? $('#rate').val() : null,
            lnr_b_id: lnr_b_id,
            installments: []
        };

        // Collect installment amounts and remaining balances
        let runningBalance = totalLoan;
        $('.installmentAmountValue').each(function (index) {
            const amount = parseFloat($(this).val()) || 0;
            const remainingBalance = parseFloat(
                $(this).closest('tr').find('.remainingBalance').text()
                    .replace(/[^0-9.]/g, '')
            ) || 0;

            // Validate calculation consistency
            // const calculatedBalance = runningBalance - amount;
            // if (Math.abs(remainingBalance - calculatedBalance) > 0.01) {
            //     Swal.fire('Error', `Balance mismatch in installment ${index + 1}`, 'error');
            //     return false;
            // }

            formData.installments.push({
                installment_number: index + 1,
                amount: amount.toFixed(2),
                remaining_balance: remainingBalance.toFixed(2)
            });

            runningBalance = remainingBalance;
        });

        // Get installment count dynamically
        const installmentCount = $('.installmentAmountValue').length;

        // If validation failed
        if (formData.installments.length !== installmentCount) return;

        Swal.fire({
            title: 'Approve Loan',
            text: 'Are you sure you want to approve this loan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('save.loan.details', '') }}/" + loanId,
                    type: 'POST',
                    data: formData,
                    success: function (detailsResponse) {
                        if (detailsResponse.success) {
                            $.ajax({
                                url: "{{ route('loan.approve', '') }}/" + loanId,
                                type: 'POST',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (response) {
                                    if (response.success) {
                                        Swal.fire('Approved', response.message, 'success');
                                        $('#loanModal').modal('hide');
                                        location.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function (xhr) {
                                    Swal.fire('Error', 'Approval failed', 'error');
                                }
                            });
                        } else {
                            Swal.fire('Error', detailsResponse.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', 'Failed to save details', 'error');
                    }
                });
            }
        });
    });

    // Handle loan rejection
    $(document).on('click', '.reject-loan-btn', function () {
    let loanId = $('#loanModal').data('loan-id');

        // Close the existing modal first
        $('#loanModal').modal('hide');

        setTimeout(() => {
            Swal.fire({
                title: 'Reject Loan',
                text: 'Are you sure you want to reject this loan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                input: 'textarea',
                inputPlaceholder: 'Enter rejection reason',
                inputAttributes: {
                    'aria-label': 'Enter rejection reason',
                    'rows': 4,
                    'class': 'swal2-textarea',
                    'style': 'resize: vertical; min-height: 100px; opacity: 1 !important;'
                },
                showLoaderOnConfirm: true,
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('You need to enter a rejection reason!');
                        return false;
                    }
                    return reason;
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: "{{ route('loan.reject', '') }}/" + loanId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reason: result.value
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire('Rejected', response.message, 'success');
                                location.reload();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire('Error', 'Failed to reject loan', 'error');
                        }
                    });
                }
            });
        }, 300); // Short delay to ensure previous modal is fully closed
    });
</script>


<style>
    .avatar img {
        object-fit: cover;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .badge {
        font-weight: 500;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
</style>


<script>
    $(document).on('click', '.actionBtn', function() {
    $(".actionBtn").attr("disabled", true);

    // Collect all basic attributes
    var dataAttributes = {};
    $.each(this.attributes, function() {
        if (this.name.startsWith('data-')) {
            var key = this.name.slice(5);
            dataAttributes[key] = this.value;
        }
    });

    // Get message from textarea
    dataAttributes['message'] = $('#actionMessage').val();

    // Validate message
    if (dataAttributes['message'] == '') {
        Swal.fire({
            icon: "warning",
            text: 'Message is required.',
            timer: 3000,
        });
        $(".actionBtn").attr("disabled", false);
        return false;
    }

    // Collect loan details from form
    dataAttributes['lnr_b_id'] = $('#lnr_b_id').val();
    dataAttributes['rate'] = $('#rate').val() || 0;
     dataAttributes['calculation_type'] = $('#calculationType').val() || 'flat';

    // Collect installment data
    var installments = [];
    $('#loanSchedule tr').each(function(index) {
        var row = $(this);
        installments.push({
            installment_number: index + 1,
            amount: parseFloat(row.find('.installmentAmountValue').val() || 0).toFixed(2),
            principal_amount: parseFloat(row.find('.principalAmount').text() || 0).toFixed(2),
            interest_amount: parseFloat(row.find('.interestAmount').text() || 0).toFixed(2),
            remaining_balance: parseFloat(row.find('.remainingBalance').text().replace('₹', '').replace(',', '') || 0).toFixed(2)
        });
    });
    dataAttributes['installments'] = installments;

    // Prepare the complete data object
    var postData = {
        _token: '{{ csrf_token() }}',
        POST_TYPE: 'LOAN_REQUEST_APPROVAL',
        data: dataAttributes
    };

    $.ajax({
        url: '{{ route('admin.approval-handler') }}',
        method: "post",
        data: postData,
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
                window.location.reload();
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
                text: "Error: " + (xhr.responseJSON?.message || error),
                timer: 3000,
            });
        }
    });
});
</script>
<script src="{{ asset('assets/js/approval-form.js') }}"></script>
@endsection
