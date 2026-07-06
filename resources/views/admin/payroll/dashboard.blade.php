@extends('admin.layout.master')
@section('title')
    Salary Dashboard
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        a,
        a:hover,
        a:focus,
        a:active {
            text-decoration: none !important;
            outline: none;
        }

        /* Bootstrap nav / menu specific safety */
        .navbar a,
        .nav a,
        .nav-link,
        .dropdown-menu a {
            text-decoration: none !important;
        }

        body {
            background-color: #f9fafb;
        }

        .card-small {
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .summary-card {
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            color: white;
        }

        .border-left {
            border-left: 4px solid var(--bs-primary);
            padding-left: .75rem;
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: .75rem;
        }
    </style>
@endsection

@section('content')
    {{-- Breadcrumbs --}}


    <div>
        <div class="d-flex justify-content-between align-items-center p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none; font-size: 12px;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/reimburse') }}">Payroll</a></li>
                <li class="active"><span><b>Salary Dashboard</b></span></li>
            </ol>

            {{-- Financial Year & Month Filters --}}
            <form action="{{ route('salary.dashboard') }}" method="GET" class="d-flex align-items-center"
                style="gap: 8px;">

                {{-- Financial Year --}}
                <label for="financial_year" class="mb-0" style="font-size: 12px; font-weight: 600;">
                    Year:
                </label>

                <select name="financial_year" id="financial_year" class="form-select form-select-sm"
                    style="width: 130px; font-size: 12px;" onchange="this.form.submit()">

                    @foreach ($years as $fy)
                        <option value="{{ $fy->fy_id }}"
                            {{ request('financial_year') == $fy->fy_id || (!request('financial_year') && $fy->fy_is_current == 1) ? 'selected' : '' }}>
                            {{ $fy->fy_year }}
                        </option>
                    @endforeach
                </select>

                {{-- Month --}}
                <label for="month" class="mb-0" style="font-size: 12px; font-weight: 600;">
                    Month:
                </label>

                @php
                    $selectedMonth = request('month');
                @endphp

                <select name="month" id="month" class="form-select form-select-sm"
                    style="width: 130px; font-size: 12px;" onchange="this.form.submit()">

                    <option value="">All Months</option>

                    @foreach ($months as $month)
                        <option value="{{ $month->m_id }}" {{ $selectedMonth == $month->m_id ? 'selected' : '' }}>
                            {{ $month->m_name }}
                        </option>
                    @endforeach
                </select>

            </form>

        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-3">
                <div class="card card-small p-3">
                    <div class="section-title">1. Employee Summary</div>
                    <div class="small d-flex justify-content-between"><span>Total</span><span
                            class="fw-bold ">{{ $totalemployees }}</span></div>
                    <div class="small d-flex justify-content-between"><span>Paid</span><span
                            class="fw-bold ">{{ $paidemployee }}</span></div>
                    <div class="small d-flex justify-content-between"><span>Unpaid</span><span
                            class="fw-bold ">{{ $unpaidemployee }}</span></div>
                    <div class="small d-flex justify-content-between"><span>Paid Amount</span><span class="fw-bold ">₹
                            {{ number_format($paidamount, 2) }}</span></div>
                </div>

                <div class="card card-small p-3">
                    <div class="section-title">2. ESIC Contributions</div>
                    <div class="small d-flex justify-content-between"><span>Employer</span><span class="fw-bold">₹
                            {{ number_format($esic_employer_total, 2) }}</span></div>
                    <div class="small d-flex justify-content-between"><span>Employees</span><span class="fw-bold">₹
                            {{ number_format($esic_employee_total, 2) }}</span>
                    </div>
                    <div class="small d-flex justify-content-between"><span>Total</span><span class="fw-bold">₹
                            {{ number_format($esic_employee_total + $esic_employer_total, 2) }}</span>
                    </div>
                </div>

                <div class="card card-small p-3">
                    <div class="section-title">3. PF Contributions</div>
                    <div class="small d-flex justify-content-between"><span>Employer</span><span class="fw-bold ">₹
                            {{ number_format($pf_employer_total, 2) }}</span></div>
                    <div class="small d-flex justify-content-between"><span>Employee</span><span class="fw-bold">₹
                            {{ number_format($pf_employee_total, 2) }}</span>
                    </div>
                    <div class="small d-flex justify-content-between"><span>Total</span><span class="fw-bold">₹
                            {{ number_format($pf_employee_total + $pf_employer_total, 2) }}</span>

                    </div>
                </div>
            </div>


            <!-- Middle Column -->
            <div class="col-lg-6">
                <!-- Challan Status -->
                <div class="card card-small p-3 mb-3">
                    <div class="section-title">4. Challan Status - Current Month</div>
                    <div class="row g-2 text-center">
                        <div class="col-4 border rounded p-2" style="height:88px;">
                            <div class="d-flex justify-content-center align-items-center small fw-semibold">
                                PF <i class="bi bi-check-circle-fill  ms-1"></i>
                            </div>
                            <div class="fw-bold">₹ {{ number_format($pf_employee_total + $pf_employer_total, 2) }}</div>
                            <small class="text-muted"></small>

                        </div>
                        <div class="col-4 border rounded p-2">
                            <div class="d-flex justify-content-center align-items-center small fw-semibold">
                                ESIC <i class="bi bi-check-circle-fill  ms-1"></i>
                            </div>
                            <div class="fw-bold">₹ {{ number_format($esic_employee_total + $esic_employer_total, 2) }}
                            </div>
                            <small class="text-muted"></small>
                        </div>
                        <div class="col-4 border rounded p-2">
                            <div class="d-flex justify-content-center align-items-center small fw-semibold">
                                TDS <i class="bi bi-clock-fill  ms-1"></i>
                            </div>
                            <div class="fw-bold">₹ 0.00</div>
                            <small class="text-muted"></small>

                        </div>
                    </div>
                </div>

                <!-- Grid Modules 5-8 -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-small p-3">
                            <div class="section-title">5. Overtime</div>
                            <div class="small d-flex justify-content-between"><span>Amount</span><span class="fw-bold ">₹
                                    {{ $overtime_total_amount }}</span></div>
                            <div class="small d-flex justify-content-between"><span>Hours</span><spanclass="fw-bold">0.00
                                    </spanclass=>
                            </div>
                            <div class="small d-flex justify-content-between"><span>Avg Rate</span><span
                                    class="fw-bold">₹0.00/hr</span></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-small p-3">
                            <div style="margin-bottom:84px;">6. Late Contributions</div>

                            <div class="small d-flex justify-content-between">
                                <span>Late</span>
                                <span class="fw-bold">₹
                                    {{ number_format($late_employee_total + $late_employer_total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-small p-3">
                            <div class="section-title" style="margin-bottom: 36px;">7. F&F Status</div>
                            <div class="small d-flex justify-content-between"><span>Completed</span><span
                                    class="fw-bold ">{{ $relieved }}</span></div>
                            <div class="small d-flex justify-content-between"><span>Pending</span><span
                                    class="fw-bold ">{{ $pending }}</span></div>
                            {{-- <div class="small d-flex justify-content-between"><span>Amount</span><span class="fw-bold ">₹
                                    0.00</span></div> --}}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-small p-3">
                            <div class="section-title">8. Arrears</div>
                            <div class="small d-flex justify-content-between"><span>Salary</span><span class="fw-bold ">₹
                                    0.00</span></div>
                            <div class="small d-flex justify-content-between"><span>OT</span><span class="fw-bold ">₹
                                    0.00</span></div>
                            <div class="small d-flex justify-content-between"><span>Total</span><span class="fw-bold ">₹
                                    0.00</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-3">
                {{-- 9. Loan & Advance --}}
                <div class="card card-small p-3">
                    <div class="section-title">9. Loan & Advance</div>

                    <!-- Approved Loan Count -->
                    <div class="small d-flex justify-content-between">
                        <span>Approved</span>
                        <span class="fw-bold">{{ $totalApprovedLoans }}</span>
                    </div>
                    <!-- Total Approved Amount -->
                    <div class="small d-flex justify-content-between">
                        <span>Total Amt</span>
                        <span class="fw-bold">₹ {{ number_format($totalApprovedAmount, 2) }}</span>
                    </div>

                    <!-- Paid Amount -->
                    <div class="small d-flex justify-content-between">
                        <span>Payback</span>
                        <span class="fw-bold ">
                            ₹ {{ number_format($totalpayback, 2) }}
                        </span>
                    </div>

                    <!-- Outstanding Amount -->
                    <div class="small d-flex justify-content-between">
                        <span>Outstanding</span>
                        <span class="fw-bold">
                            ₹ {{ number_format($totalOutstandingAmount, 2) }}
                        </span>
                    </div>
                </div>

                {{-- 10. Week Off Payment --}}
                <div class="card card-small p-3">
                    <div class="section-title">10. Week Off Payment</div>
                    <div class="small d-flex justify-content-between"><span>Amount</span><span
                            class="fw-bold ">₹0.00</span></div>
                    <div class="small d-flex justify-content-between"><span>Employees</span><span
                            class="fw-bold">0</span></div>
                    <div class="small d-flex justify-content-between"><span>Avg/Employee</span><span
                            class="fw-bold">₹0.00</span></div>
                </div>

                {{-- 11. Leave Payment --}}
                <div class="card card-small p-3">
                    <div class="section-title">11. Leave Payment</div>
                    <div class="small d-flex justify-content-between"><span>Amount</span><span
                            class="fw-bold ">₹0.00</span></div>
                    <div class="small d-flex justify-content-between"><span>Days</span><span class="fw-bold">0</span>
                    </div>
                    <div class="small d-flex justify-content-between"><span>Rate/Day</span><span class="fw-bold">₹
                            0.00</span>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card summary-card p-3">
                    <div class="section-title text-white">Summary</div>

                    <div class="small d-flex justify-content-between">
                        <span>Gross Payroll</span>
                        <span class="fw-bold">
                            ₹{{ number_format($paidamount, 2) }}
                        </span>
                    </div>

                    <div class="small d-flex justify-content-between">
                        <span>Deductions</span>
                        <span class="fw-bold">
                            ₹{{ number_format(
                                $esic_employee_total +
                                    $esic_employer_total +
                                    $pf_employee_total +
                                    $pf_employer_total +
                                    $late_employee_total +
                                    $late_employer_total,
                                2,
                            ) }}
                        </span>
                    </div>

                    <hr class="border-light my-2">

                    <div class="small d-flex justify-content-between">
                        <span>Net Payment</span>
                        <span class="fw-bold">
                            ₹{{ number_format($paidamount, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
@endsection
