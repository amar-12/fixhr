@extends('admin.layout.master')
@section('title')
    {{ $title }}
@endsection
@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .payroll-card {
            transition: all 0.3s ease;
            height: 100%;
        }

        .payroll-card:hover {
            box-shadow: 0 0 25px rgba(0, 123, 255, 0.15);
            transform: translateY(-6px);
            background-color: #ffffff;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 0.75rem;
            padding: 0.4em 0.65em;
        }

        .payroll-h3 {
            font-weight: 600;
            font-size: 1.25rem;
            margin-top: 1.25rem;
        }

        .payroll-date {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .select-with-scroll {
            max-height: 150px;
            overflow-y: auto;
        }

    </style>

@endsection

@section('content')
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="">Payroll</a></li>
                        <li class="active"><span><b>{{ $title }}</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <!-- ROW -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="card-title mb-0">Salary Process</h4>
                    <div class="d-flex gap-2 flex-wrap">


                    </div>
                </div>

                <div class="card-body" style="min-height: 600px;">
                    <div class="form-wrapper">

                        <div class="row">
                            <div class="col-lg-2 col-md-2 col-sm-3">
                            <div>
                                <label class="form-label" for="statusFilter">Year</label>
                                <select size="2" id="statusFilter" class="form-select search_test select-with-scroll" data-length>
                                    @foreach ($financialYears as $year)
                                    <option value="{{ $year->fy_id }}" @if ($currentFinancialYear && $year->fy_id == $currentFinancialYear->fy_id)
                                        selected @endif>
                                        {{ $year->fy_year }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            </div>

                            <div class="col-lg-1 col-md-1 col-sm-12 mt-1">
                                <button id="createPayrollBtn" type="button" class="btn btn-info btn-add-payroll mt-5"
                                    data-bs-toggle="modal" data-bs-target="#addPayrollPeriodModal">
                                    Add Payroll Period
                                </button>
                            </div>



                        </div>
                    </div>




                    @php
                        // Defining months for each quarter
                        $quarters = [
                            'Q1' => ['January', 'February', 'March'],
                            'Q2' => ['April', 'May', 'June'],
                            'Q3' => ['July', 'August', 'September'],
                            'Q4' => ['October', 'November', 'December'],
                        ];

                        // Initializing arrays to hold payrolls for each quarter
                        $quarterlyPayrolls = [];

                        // Loop through quarters and filter payrolls for each quarter
                        foreach ($quarters as $quarter => $months) {
                            $quarterlyPayrolls[$quarter] = $payrollPeriods->filter(function ($payroll) use ($months) {
                                return in_array($payroll->month_name, $months);
                            });
                        }

                        // Separate payrolls that don't belong to any quarter (in case of future use or other cases)
                        $otherPayrolls = $payrollPeriods->reject(function ($payroll) use ($quarters) {
                            foreach ($quarters as $months) {
                                if (in_array($payroll->month_name, $months)) {
                                    return true;
                                }
                            }
                            return false;
                        });
                    @endphp

                    <div class="row g-4 mt-4" id="payroll-container">
                        {{-- Loop through each quarter and show payrolls --}}
                        @foreach ($quarterlyPayrolls as $quarter => $payrolls)
                            @if ($payrolls->isNotEmpty())
                                <div class="col-12 col-md-3 col-lg-3"> <!-- 3 columns per quarter on larger screens -->
                                    <div
                                        class="bg-white border-start border-4 border-info p-4 rounded shadow-sm transition position-relative">
                                        <h5 class="fw-bold text-secondary mb-3">{{ $quarter }}
                                            ({{ implode(' - ', $quarters[$quarter]) }})
                                            <span class="text-muted fw-semibold">{{ $currentFinancialYear->fy_year }}</span>
                                        </h5>

                                        @foreach ($payrolls as $payroll)
                                            <div class="mb-3 pb-2 border-bottom">
                                                <span class="badge bg-success float-end">
                                                    {{ $payroll->pp_is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                <div class="fw-bold">{{ $payroll->month_name }} ({{ $payroll->fy_year }})
                                                </div>
                                                <div class="text-muted payroll-date" style="font-size: 0.9rem;">
                                                    {{ date('d M Y', strtotime($payroll->pp_start_date)) }}

                                                    {{ date('d M Y', strtotime($payroll->pp_end_date)) }}
                                                </div>
                                                <div class="text-muted payroll-date" style="font-size: 0.9rem;">
                                                    {{ \Carbon\Carbon::parse($payroll->pp_start_date)->format('d M Y') }} -
                                                    {{ \Carbon\Carbon::parse($payroll->pp_end_date)->format('d M Y') }}
                                                </div>

                                                <div class="progress my-2" style="height: 5px;">
                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                        style="width: {{ $payroll->pp_is_processed ? '100%' : '40%' }};">
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between text-muted"
                                                    style="font-size: 0.9rem;">
                                                    <div>
                                                        <strong>Total Empl:</strong>
                                                        <span id="totalEmpl">{{ $payroll->fh_employee_count }}</span>
                                                    </div>
                                                </div>



                                                <div class="mt-auto d-flex flex-wrap gap-2">
                                                    <button class="btn btn-outline-info sbtn-sm edit-payroll-period"
                                                        data-id="{{ $payroll->pp_id }}"
                                                        data-name="{{ $payroll->pp_name }}"
                                                        data-payroll_quarter="${payroll.pp_quarter_id}"
                                                        data-year="{{ $payroll->pp_fy_id }}"
                                                        data-month="{{ $payroll->pp_month_id }}"
                                                        data-type="{{ $payroll->pp_type_id }}"
                                                        data-start_date="{{ $payroll->pp_start_date }}"
                                                        data-end_date="{{ $payroll->pp_end_date }}"
                                                        data-payment_date="{{ $payroll->pp_payment_date }}"
                                                        data-payslip_date="{{ $payroll->pp_payslip_date }}"
                                                        data-active="{{ $payroll->pp_is_active }}"
                                                        data-description="{{ $payroll->pp_description }}"
                                                        data-bs-toggle="modal" data-bs-target="#addPayrollPeriodModal"
                                                        title="Edit Payroll Period">
                                                        <i class="fas fa-pen"></i>
                                                    </button>

                                                    <button class="btn btn-outline-danger btn-sm delete-payroll-period"
                                                        data-id="{{ $payroll->pp_id }}"
                                                        {{ $payroll->pp_is_freezed == 121 ? '' : 'disabled' }}
                                                        title="Delete Payroll Period">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                    <button class="btn btn-outline-info btn-sm freeze-attendance"
                                                        data-id="{{ $payroll->pp_id }}"
                                                        {{ $payroll->pp_is_freezed == 121 ? '' : 'disabled' }}
                                                        title="Freeze Attendance">
                                                        <i class="fas fa-snowflake"></i>
                                                    </button>

                                                    <button class="btn btn-outline-info btn-sm process-salary"
                                                        data-id="{{ $payroll->pp_id }}"
                                                        {{ $payroll->pp_is_freezed == 121 ? 'disabled' : '' }}
                                                        title="Process Salary">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </button>

                                                    @if ($payroll->pp_is_freezed == 120)
                                                        <form
                                                            action="{{ route('unfreeze.payroll.attendance', $payroll->pp_id) }}"
                                                            method="POST" class="unfreeze-form d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-warning btn-sm confirm-unfreeze"
                                                                title="Unfreeze Attendance">
                                                                <i class="fas fa-unlock text-dark"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Other payrolls, if any --}}
                        @foreach ($otherPayrolls as $payroll)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div
                                    class="bg-white border-start border-4 border-primary p-4 rounded shadow-sm transition position-relative">
                                    <span class="badge bg-success position-absolute top-0 end-0 m-3">
                                        {{ $payroll->pp_is_active ? 'Active' : 'Inactive' }}
                                    </span>

                                    <h5 class="fw-bold text-secondary mb-1">{{ $payroll->month_name }}</h5>
                                    <div class="text-muted mb-2" style="font-size: 0.85rem;">
                                        {{ $payroll->fy_year }}
                                    </div>

                                    <div class="text-muted mb-2 payroll-date" style="font-size: 0.9rem;">
                                        {{ \Carbon\Carbon::parse($payroll->pp_start_date)->format('d M Y') }} -
                                        {{ \Carbon\Carbon::parse($payroll->pp_end_date)->format('d M Y') }}
                                    </div>


                                    <div class="progress mb-3" style="height: 5px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $payroll->pp_is_processed ? '100%' : '40%' }};">`
                                        </div>
                                    </div>

                                    <div class="mt-auto d-flex flex-wrap gap-2">
                                        {{-- Buttons as you already have --}}
                                        <!-- Keep all your buttons here unchanged -->
                                    </div>
                                </div>
                            </div>
                        @endforeach
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



    <!-- Payroll Period Modal -->
    <div class="modal fade" id="addPayrollPeriodModal" tabindex="-1" aria-labelledby="addPayrollPeriodLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg"> <!-- Changed modal-lg to modal-xl for more width -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPayrollPeriodLabel">Add Payroll Period</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addPayrollPeriodForm" action="{{ route('payroll.period.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="pp_id" id="pp_id">
                        <div class="row">

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="">-- select --</option>
                                        @foreach ($financialYears as $year)
                                        <option value="{{ $year->fy_id }}">{{ $year->fy_year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="payroll_type" class="form-label">Payroll Type <span class="text-danger">*</span></label>
                                    <select class="form-select search_test" id="payroll_type" name="payroll_type" required>
                                        @foreach ($paymentCycle as $payCycle)
                                        <option value="{{ $payCycle->m_id }}" {{ $payCycle->m_name == 'Monthly' ? 'selected' : '' }}>
                                            {{ $payCycle->m_name }}
                                        </option>
                                        @endforeach
                                    </select>                            
                                </div>
                            </div> --}}


                             <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="payroll_type" class="form-label">
                                        Payroll Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="payroll_type" name="payroll_type" required>
                                        @if ($paymentCycle)
                                        <option value="{{ $paymentCycle->m_id }}" selected>
                                            {{ $paymentCycle->m_name }}
                                        </option>
                                        @else
                                        <option value="">Select Payroll Type</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                          <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="payroll_quarter" class="form-label">Quarter</label>
                                    <select class="form-select" id="payroll_quarter" name="payroll_quarter" required>
                                        <option value="">-- select --</option>
                                        @foreach ($payrollQuarter as $pQuarter)
                                        <option value="{{ $pQuarter->m_id }}">{{ $pQuarter->m_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="month" class="form-label">Month</label>
                                    <select class="form-select" id="month" name="month" required>
                                        {{-- <option value="">-- select --</option> --}}
                                        {{-- @foreach ($monthName as $month)
                                            <option value="{{ $month->m_id }}">{{ $month->m_name }}</option>
                                        @endforeach --}}
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Payroll Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attendance_start_date" class="form-label">Attendance Start Date</label>
                                    <input type="date" class="form-control" id="attendance_start_date"
                                        name="attendance_start_date">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attendance_end_date" class="form-label">Attendance End Date</label>
                                    <input type="date" class="form-control" id="attendance_end_date"
                                        name="attendance_end_date">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_of_payment" class="form-label">Date of Payment <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_of_payment"
                                        name="date_of_payment" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payslip_online_date" class="form-label">Payslip Online Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="payslip_online_date"
                                        name="payslip_online_date" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description"></textarea>
                                </div>
                            </div>

                            {{-- <div class="col-xl-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <div class="form-check form-switch" style="display: flex; align-items: center;">
                                    <input type="hidden" name="is_active" value="0"
                                        style="position: absolute; width: 0; height: 0; visibility: hidden;">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" checked style="margin-left: 0; height: 1.5em; width: 3em;">
                                </div>
                            </div> --}}

                         <div class="col-md-6">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" id="bank_name"
                                value="{{ $business->b_bank_name ?? '' }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Account Number</label>
                            <input type="text" class="form-control" name="account_number" id="account_number"
                                value="{{ $business->b_acc_no ?? '' }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code" id="ifsc_code"
                                value="{{ $business->b_ifsc_code ?? '' }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Bank Address</label>
                            <input type="text" class="form-control" name="bank_address" id="bank_address"
                                value="{{ $business->b_bank_add ?? '' }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cheque Number</label>
                            <input type="text" class="form-control" name="cheque_number" id="cheque_number"
                                value="{{ $business->b_cheque_no ?? '' }}">
                        </div>


                            <div class="col-md-12 text-end mt-3">
                                <button type="submit" class="btn btn-outline-primary">Save Payroll Period</button>
                                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection



@section('script')

    @if(isset($showPayrollError) && $showPayrollError)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Payroll Master Settings Missing!',
                    text: 'Please configure Payroll Master Settings before using Payroll Period.',
                    confirmButtonText: 'Go to Settings',
                    confirmButtonColor: '#3085d6',
                    showCancelButton: false,
                    allowOutsideClick: false,  // 🚫 disable outside click
                    allowEscapeKey: false,     // 🚫 disable ESC key
                    allowEnterKey: true,       // ✅ allow Enter to confirm
                    width: 450,
                    background: '#fff',
                    backdrop: 'rgba(0,0,0,0.6)'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ url('/admin/settings/payroll') }}";
                    }
                });
            });
        </script>
    @endif

    <script>
        $(document).ready(function () {

            // Dynamically generate quarters based on financial year start month
            function generateQuarters(startMonth) {
                const allMonths = [
                    'January', 'February', 'March',
                    'April', 'May', 'June',
                    'July', 'August', 'September',
                    'October', 'November', 'December'
                ];

                const startIndex = allMonths.indexOf(startMonth);
                const rotated = [...allMonths.slice(startIndex), ...allMonths.slice(0, startIndex)];

                return {
                    Q1: rotated.slice(0, 3),
                    Q2: rotated.slice(3, 6),
                    Q3: rotated.slice(6, 9),
                    Q4: rotated.slice(9, 12)
                };
            }


            function fetchAndRenderPayrolls(financialYearId) {
                $.ajax({
                    url: '{{ route('payroll.period.list') }}',
                    type: 'GET',
                    data: {
                        year: financialYearId
                    },
                    success: function (response) {
                        const container = $('#payroll-container');
                        container.empty();

                        if (!response.data || response.data.length === 0) {
                            container.html(`
                                <div class="col-12">
                                    <p class="text-center text-muted">No payroll periods found for selected year.</p>
                                </div>
                            `);
                            return;
                        }

                        // Get start month of financial year from the first record
                        const startMonth = response.data[0]?.fy_start_month || 'April';
                        const quarters = generateQuarters(startMonth);

                        const grouped = {
                            Q1: [],
                            Q2: [],
                            Q3: [],
                            Q4: [],
                            others: []
                        };

                        response.data.forEach(item => {
                            let matched = false;
                            for (let q in quarters) {
                                if (quarters[q].includes(item.month_name)) {
                                    grouped[q].push(item);
                                    matched = true;
                                    break;
                                }
                            }
                            if (!matched) grouped.others.push(item);
                        });

                        for (let q in grouped) {
                            if (grouped[q].length === 0) continue;

                            const quarterLabel = (q === 'others') ? 'Others' :
                                `${q} (${quarters[q].join(' - ')})`;

                            let html = `
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="bg-white border-start border-4 border-info p-4 rounded shadow-sm">
                                        <h5 class="fw-bold text-secondary mb-3">${quarterLabel}</h5>
                            `;

                            grouped[q].forEach(payroll => {
                                const dateOptions = { day: '2-digit', month: 'short', year: 'numeric' };

                                const startDate = new Date(payroll.pp_start_date).toLocaleDateString('en-GB', dateOptions);
                                const endDate = new Date(payroll.pp_end_date).toLocaleDateString('en-GB', dateOptions);
                                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                                html += `
                                    <div class="pb-2 border-bottom" style="margin-bottom: 50px;">
                                        <div class="fw-bold">${payroll.month_name} (${payroll.fy_year})</div>

                                        <div class="text-muted mb-2 payroll-date" style="font-size:0.9rem;">
                                            ${(() => { const s = payroll.pp_start_date; return `${s.slice(8,10)}-${s.slice(5,7)}-${s.slice(0,4)}`; })()}
                                            –
                                            ${(() => { const s = payroll.pp_end_date; return `${s.slice(8,10)}-${s.slice(5,7)}-${s.slice(0,4)}`; })()}
                                        </div>


                                        <div class="progress my-2" style="height: 5px;">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                style="width: ${payroll.pp_is_processed ? '100%' : '40%'};">
                                            </div>
                                        </div>
                                        <div class="mt-auto d-flex flex-wrap gap-2">
                                            <button class="btn btn-outline-info btn-sm edit-payroll-period"
                                                data-id="${payroll.pp_id}"
                                                data-name="${payroll.pp_name}"
                                                data-payroll_quarter="${payroll.pp_quarter_id}"
                                                data-year="${payroll.pp_fy_id}"
                                                data-month="${payroll.pp_month_id}"
                                                data-type="${payroll.pp_type_id}"
                                                data-start_date="${payroll.pp_start_date}"
                                                data-end_date="${payroll.pp_end_date}"
                                                data-payment_date="${payroll.pp_payment_date}"
                                                data-payslip_date="${payroll.pp_payslip_date}"
                                                data-active="${payroll.pp_is_active}"
                                                data-description="${payroll.pp_description}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addPayrollPeriodModal"
                                                title="Edit Payroll Period"
                                                ${(payroll.pp_is_processed !== 121 && payroll.pp_is_freezed !== 121) ? '' : 'disabled'}>
                                                <i class="fas fa-pen"></i>
                                            </button>

                                            <button class="btn btn-outline-danger btn-sm delete-payroll-period"
                                                data-id="${payroll.pp_id}"
                                                ${(payroll.pp_is_freezed === 121 && payroll.pp_is_processed === 121) ? '' : 'disabled'}
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                            <button class="btn btn-outline-info btn-sm freeze-attendance"
                                                data-id="${payroll.pp_id}"
                                                ${payroll.pp_is_freezed === 121 ? '' : 'disabled'}
                                                title="Freeze Attendance">
                                                <i class="fas fa-snowflake"></i>
                                            </button>

                                            <button class="btn btn-outline-info btn-sm process-salary"
                                                data-id="${payroll.pp_id}"
                                                ${payroll.pp_is_freezed === 121 ? 'disabled' : ''}
                                                title="Process Salary">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>

                                            ${payroll.pp_is_freezed === 121 ? '' : `
                                                <form action="/unfreeze/payroll/attendance/${payroll.pp_id}" method="POST" class="unfreeze-form d-inline">
                                                    <input type="hidden" name="_token" value="${csrfToken}">
                                                    <button type="submit" class="btn btn-warning btn-sm confirm-unfreeze" data-id="${payroll.pp_id}" title="Unfreeze Attendance">
                                                        <i class="fas fa-unlock text-dark"></i>
                                                    </button>
                                                </form>
                                            `}
                                        </div>
                                    </div>
                                `;
                            });

                            html += `</div></div>`;
                            container.append(html);
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        $('#payroll-container').html(`
                            <div class="col-12">
                                <p class="text-danger">Something went wrong while fetching payroll data.</p>
                            </div>
                        `);
                    }
                });
            }

            // Update month dropdown on edit modal
        $('#payroll-container').on('click', '.edit-payroll-period', function () {
                const modalTitle = document.getElementById('addPayrollPeriodLabel');
                const form = document.getElementById('addPayrollPeriodForm');

            //   function formatDate(dateStr) {
            //     if (!dateStr) return '';

            //     const date = new Date(dateStr);

            //     const year = date.getUTCFullYear();
            //     const month = String(date.getUTCMonth() + 1).padStart(2, '0');
            //     const day = String(date.getUTCDate()).padStart(2, '0');

            //     return `${year}-${month}-${day}`;
            // }
            
            function formatDate(dateStr) {
                if (!dateStr) return '';
            
                // check if string is in YYYY-MM-DD or DD-MM-YYYY
                let parts;
                if (dateStr.includes('-')) {
                    parts = dateStr.split('-');
                } else if (dateStr.includes('/')) {
                    parts = dateStr.split('/');
                }
            
                // If format is DD-MM-YYYY
                if (parts[0].length === 2) {
                    return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                }
            
                // If format is YYYY-MM-DD
                if (parts[0].length === 4) {
                    return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
                }
            
                return '';
            }



                modalTitle.textContent = "Edit Payroll Period";

                document.getElementById('pp_id').value = this.dataset.id ?? '';
                document.getElementById('name').value = this.dataset.name ?? '';
                document.getElementById('year').value = this.dataset.year ?? '';
                loadMonthsForYear(this.dataset.year, this.dataset.month);
                document.getElementById('payroll_type').value = this.dataset.type ?? '';
                document.getElementById('payroll_quarter').value = this.dataset.payroll_quarter ?? '';
                document.getElementById('description').value = this.dataset.description ?? '';

                document.getElementById('attendance_start_date').value = formatDate(this.dataset.start_date);
                document.getElementById('attendance_end_date').value = formatDate(this.dataset.end_date);
                document.getElementById('date_of_payment').value = formatDate(this.dataset.payment_date);
                document.getElementById('payslip_online_date').value = formatDate(this.dataset.payslip_date);

                document.getElementById('pp_is_active').checked = this.dataset.active === "1";
            });


            // Redirect to salary processing
            $('#payroll-container').on('click', '.process-salary', function () {
                const payrollId = $(this).data('id');
                const url = "{{ route('payroll.process-salary', ':id') }}".replace(':id', payrollId);
                window.location.href = url;
            });

            // Initial load
            const defaultYear = $('#statusFilter').val();
            fetchAndRenderPayrolls(defaultYear);

            $('#statusFilter').on('change', function () {
                const selectedYear = $(this).val();
                fetchAndRenderPayrolls(selectedYear);
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalTitle = document.getElementById('addPayrollPeriodLabel');
            const form = document.getElementById('addPayrollPeriodForm');

        //   function formatDate(dateStr) {
        //         if (!dateStr) return '';

        //         const date = new Date(dateStr);

        //         const year = date.getUTCFullYear();
        //         const month = String(date.getUTCMonth() + 1).padStart(2, '0');
        //         const day = String(date.getUTCDate()).padStart(2, '0');

        //         return `${year}-${month}-${day}`;
        //     }
        
        function formatDate(dateStr) {
    if (!dateStr) return '';

    // check if string is in YYYY-MM-DD or DD-MM-YYYY
    let parts;
    if (dateStr.includes('-')) {
        parts = dateStr.split('-');
    } else if (dateStr.includes('/')) {
        parts = dateStr.split('/');
    }

    // If format is DD-MM-YYYY
    if (parts[0].length === 2) {
        return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
    }

    // If format is YYYY-MM-DD
    if (parts[0].length === 4) {
        return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
    }

    return '';
}



            document.querySelectorAll('.edit-payroll-period').forEach(button => {
                button.addEventListener('click', function() {
                    modalTitle.textContent = "Edit Payroll Period";

                    document.getElementById('pp_id').value = this.dataset.id;
                    document.getElementById('name').value = this.dataset.name;
                    document.getElementById('year').value = this.dataset.year;
                    // document.getElementById('month').value = this.dataset.month;
                    loadMonthsForYear(this.dataset.year, this.dataset.month); // ⬅️ KEY CHANGE
                    document.getElementById('payroll_type').value = this.dataset.type;
                    document.getElementById('payroll_quarter').value = this.dataset.payroll_quarter;

                    // Use vanilla JS to get the description
                    var description = this.dataset.description;
                    document.getElementById('description').textContent =
                        description; // Set description text

                    document.getElementById('attendance_start_date').value = formatDate(this.dataset
                        .start_date);
                    document.getElementById('attendance_end_date').value = formatDate(this.dataset
                        .end_date);
                    document.getElementById('date_of_payment').value = formatDate(this.dataset
                        .payment_date);
                    document.getElementById('payslip_online_date').value = formatDate(this.dataset
                        .payslip_date);

                    document.getElementById('is_active').checked = this.dataset.active === "1";
                });
            });


            document.getElementById('createPayrollBtn')?.addEventListener('click', function() {
                modalTitle.textContent = "Add Payroll Period";
                form.reset();
                document.getElementById('pp_id').value = "";
            });

        });
    </script>

    @if (session('success'))
        <div class="alert alert-success d-none">
            {{ session('success') }}
        </div>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: @json(session('success')),
                confirmButtonColor: '#3085d6',
                timer: 3000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if (session('error'))
        <div class="alert alert-danger d-none">
            {{ session('error') }}
        </div>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: @json(session('error')),
                confirmButtonColor: '#d33',
                timer: 3000,
                timerProgressBar: true
            });
        </script>
    @endif


    <script>
        document.addEventListener("DOMContentLoaded", function() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
                tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                    new bootstrap.Tooltip(tooltipTriggerEl)
                });
            });
    </script>


        @if (session('pendingEmployees'))
        <div class="alert alert-warning">
            {{ session('pendingEmployees') }}
        </div>
        @endif

        @if (session('pendingMissPunch'))
        <div class="alert alert-warning">
            {{ session('pendingMissPunch') }}
        </div>
        @endif



        @if (session('process_success'))
        <script>
            Swal.fire({
                            title: 'Salary Processed', // 🔁 changed from 'Success!' to 'Salary Processed'
                            text: "{{ session('process_success') }}", // e.g., "Salary processed successfully!"
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
        </script>
          @endif

        @if (session('unfreeze_success'))
        <script>
            Swal.fire({
                    title: 'Unfreeze Successful',
                    text: "{{ session('unfreeze_success') }}",
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
        </script>
        @endif

    <script>
        $(document).on('click', '.confirm-unfreeze', function(e) {
            e.preventDefault();

            // Get the pp_id from data-id
            const pp_id = $(this).data('id');
            console.log("pp_id:", pp_id); // Verify the pp_id in the console

            if (!pp_id) {
                Swal.fire({
                    title: 'Error',
                    text: 'Payroll ID not found.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to unfreeze the payroll attendance!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Unfreeze!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('unfreeze.payroll.attendance', '') }}/${pp_id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            location.reload(); // Reload to apply redirect and handle flash message
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error',
                                text: xhr.responseJSON?.message || "An error occurred while unfreezing.",
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#d33'
                            });
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.delete-payroll-period', function() {
            console.log('Delete button clicked');
            let periodId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This action will delete the payroll period permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/payroll-period/delete/' + periodId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message ||
                                'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    </script>



    <script>
        $(document).ready(function() {
            $('.process-salary').click(function() {
                var payrollId = $(this).data('id'); // Payroll ID le raha hai

                // Laravel ka route helper se correct URL generate karo
                var url = "{{ route('payroll.process-salary', ':id') }}".replace(':id', payrollId);

                window.location.href = url; // Redirect karega correct page pe
            });
        });
    </script>

    <script>
        $(document).on('click', '.freeze-attendance', function() {
            let payrollId = $(this).data('id'); // Get payroll ID from button

            $.ajax({
                url: "/payroll/attendance/retrieve", // Directly use route URL
                type: 'GET',
                data: {
                    payroll_id: payrollId
                }, // Pass payroll_id
                dataType: "json", // Expect JSON response
                beforeSend: function() {
                    Swal.fire({
                        title: 'Fetching Attendance...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    window.location.href = "/payroll/attendance/retrieve?payroll_id=" + payrollId;
                },
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        console.log(response.data); // Debugging

                        // Populate modal with HTML response
                        $('#attendanceModal .modal-body').html(response.html);
                        $('#attendanceModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                   // Swal.fire('Error', 'Something went wrong. Try again later.', 'error');
                }
            });
        });
    </script>


    <script>
        function handleError(xhr) {
            if (xhr.status === 422) { // Laravel Validation Error
                let errors = xhr.responseJSON.errors;
                let errorMessages = '';

                // Loop through validation errors and format them
                Object.keys(errors).forEach(field => {
                    errorMessages += `${errors[field][0]}<br>`; // Taking only first error per field
                });

                // Show validation errors in SweetAlert
                Swal.fire({
                    title: 'Validation Error!',
                    html: errorMessages, // HTML enabled to show line breaks
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });

            } else if (xhr.status === 409) { // Custom Conflict Error (Duplicate Payroll Period)
                Swal.fire({
                    title: 'Duplicate Entry!',
                    text: xhr.responseJSON.message, // Message from Laravel response
                    icon: 'error',
                    confirmButtonText: 'OK'
                });

            } else { // Any other server error
                Swal.fire({
                    title: 'Something went wrong!',
                    text: 'Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }

        // Submit Form via AJAX
        $('#payrollForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('payroll.period.create') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Reload page on success
                        });
                    }
                },
                error: function(xhr) {
                    handleError(xhr); // Call the error handling function
                }
            });
        });
    </script>
    <script>
        function loadMonthsForYear(fyId, selectedMonthId = null) {
            const monthDropdown = document.getElementById('month');
            monthDropdown.innerHTML = '<option value="">-- select --</option>';

            if (fyId) {
                fetch(`/payroll/get-months/${fyId}`)
                    .then(response => {
                        if (!response.ok) throw new Error("HTTP error " + response.status);
                        return response.json();
                    })
                    .then(data => {
                        data.forEach(month => {
                            const option = document.createElement('option');
                            option.value = month.value;
                            option.textContent = month.label;

                            if (selectedMonthId && month.value == selectedMonthId) {
                                option.selected = true;
                            }

                            monthDropdown.appendChild(option);
                        });
                    })
                    .catch(err => console.error('Error loading months:', err));
            }
        }


        document.getElementById('year').addEventListener('change', function() {
            const fyId = this.value;
            loadMonthsForYear(fyId); // no pre-selected value
        });


        // Fetch start and end Date base on month and payroll type selection Attendance
        document.getElementById('month').addEventListener('change', setAttendanceDates);
        document.getElementById('payroll_type').addEventListener('change', setAttendanceDates);

        function setAttendanceDates() {
            const monthSelect = document.getElementById('month');
            const payrollType = document.getElementById('payroll_type').selectedOptions[0]?.text;
            const selectedOption = monthSelect.options[monthSelect.selectedIndex];

            if (!selectedOption || !payrollType) return;

            // Extracting month and year from option text like "April 2025"
            const [monthName, year] = selectedOption.textContent.split(" ");
            const monthIndex = new Date(`${monthName} 1, ${year}`).getMonth(); // 0-based

            const startDate = new Date(year, monthIndex, 1);

            let endDate;
            if (payrollType === 'Monthly') {
                endDate = new Date(year, monthIndex + 1, 0); // last day of month
            } else if (payrollType === 'Weekly') {
                endDate = new Date(year, monthIndex, 7); // first week
            } else if (payrollType === 'Daily') {
                endDate = new Date(startDate); // same day
            } else {
                return;
            }

            // Format YYYY-MM-DD
            const formatDate = (date) => {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            };

            document.getElementById('attendance_start_date').value = formatDate(startDate);
            document.getElementById('attendance_end_date').value = formatDate(endDate);
        }


        // document.getElementById('year').addEventListener('change', function() {
        //     const fyId = this.value;
        //     const monthDropdown = document.getElementById('month');
        //     monthDropdown.innerHTML = '<option value="">-- select --</option>';

        //     if (fyId) {
        //         fetch(`/payroll/get-months/${fyId}`)
        //             .then(response => {
        //                 if (!response.ok) throw new Error("HTTP error " + response.status);
        //                 return response.json();
        //             })
        //             .then(data => {
        //                 console.log("Months loaded:", data); // DEBUG: check data
        //                 data.forEach(month => {
        //                     const option = document.createElement('option');
        //                     option.value = month.value;
        //                     option.textContent = month.label;
        //                     monthDropdown.appendChild(option);
        //                 });
        //             })
        //             .catch(err => console.error('Error loading months:', err));
        //     }
        // });
    </script>
@endsection
