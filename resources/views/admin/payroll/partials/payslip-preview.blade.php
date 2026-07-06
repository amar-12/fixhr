<div class="payslip-preview">
    <div class="row">
        <div class="col-12">
            <div class="p-4 border rounded bg-white">
                <!-- Header -->
                <div class="row mb-4 border-bottom pb-3">
                    <div class="col-8">
                        <h3 class="text-primary mb-1">PAYSLIP PREVIEW</h3>
                        <p class="text-muted mb-0">Confidential Statement</p>
                    </div>
                    <div class="col-4 text-end">
                        <h5 class="fw-bold mb-1">
                            {{ optional($processedSalary->payrollPeriod->month)->m_name ?? 'N/A' }}
                            {{ optional($processedSalary->payrollPeriod->financialYear)->fy_year ?? date('Y') }}
                        </h5>
                        <p class="text-muted mb-0">Payroll Period</p>
                    </div>
                </div>
                <!-- Employee Info -->
                <div class="row mb-4">
                    <div class="col-6">
                        <p class="mb-1"><strong>Employee:</strong> {{ $processedSalary->employee->emp_full_name }}</p>
                        <p class="mb-1"><strong>Employee Code:</strong> {{ $processedSalary->employee->emp_code }}</p>
                        <p class="mb-0"><strong>Department:</strong> {{ $processedSalary->employee->fh_department->d_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Designation:</strong> {{ $processedSalary->employee->fh_designation->dg_name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Payment Date:</strong> {{ date('d M, Y') }}</p>
                        <p class="mb-0"><strong>Days Worked:</strong> {{ $processedSalary->ps_total_days_worked }}</p>
                    </div>
                </div>

                <!-- Earnings & Deductions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="border-bottom pb-2 mb-3 text-success">
                                <i class="fas fa-arrow-up me-2"></i> Earnings
                            </h6>

                            {{-- <div class="d-flex justify-content-between mb-2">
                                <span>Basic Salary</span>
                                <span class="fw-bold">₹{{ number_format($processedSalary->ps_basic_salary, 2) }}</span>
                            </div> --}}

                            @if($processedSalary->earnings->isNotEmpty())
                                @foreach($processedSalary->earnings as $earning)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $earning->ps_earning_type }}</span>
                                    <span class="fw-bold text-success">+ ₹{{ number_format($earning->ps_e_amount, 2) }}</span>
                                </div>
                                @endforeach
                            @endif

                            <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                                <span>Total Earnings</span>
                                <span>₹{{ number_format($processedSalary->ps_earnings, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="border-bottom pb-2 mb-3 text-danger">
                                <i class="fas fa-arrow-down me-2"></i> Deductions
                            </h6>

                            @if($processedSalary->deductions->isNotEmpty())
                                @php
                                    $employeeDeductions = $processedSalary->deductions->where('ps_d_category', 'employee');
                                @endphp

                                @foreach($employeeDeductions as $deduction)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $deduction->ps_deduction_type }}</span>
                                    <span class="fw-bold text-danger">- ₹{{ number_format($deduction->ps_d_amount, 2) }}</span>
                                </div>
                                @endforeach
                            @endif

                            <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold">
                                <span>Total Deductions</span>
                                <span>₹{{ number_format($processedSalary->ps_employee_deductions, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Payable -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="bg-light p-4 rounded border">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-0">Net Payable Amount</h5>
                                    <p class="text-muted mb-0">Amount to be credited to bank account</p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h2 class="text-primary mb-0">₹{{ number_format($processedSalary->ps_monthly_net_salary, 2) }}</h2>
                                    <small class="text-muted">{{ $processedSalary->ps_currency ?? 'INR' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Notes -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="border-top pt-3">
                            <p class="small text-muted mb-0">
                                <strong>Note:</strong> This is a system generated payslip. Please contact HR department for any discrepancies.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
