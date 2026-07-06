@extends('admin.layout.master')
@section('title', 'Recurring Payments / Deductions')

@section('css')
    <style>
        .export-button {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .export-button:hover {
            background-color: #f1f1f1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu-export {
            font-size: 13px;
            min-width: 120px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
        }

        table th,
        table td {
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('content')
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="#">Payroll</a></li>
            <li class="active"><span><b>Recurring Payments / Deductions</b></span></li>
        </ol>
    </div>

    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Recurring Payments / Deductions</div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header border-0">
                    <h5 class="card-title">Adhoc Components List</h5>
                </div>

                <form method="POST" action="{{ route('store.recurring.transaction') }}">
                    @csrf
                    <div class="card-body">

                        {{-- Filters --}}
                        <div class="row">
                            <div class="col-md-1 col-sm-4">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length
                                        style="width: 100px">
                                        <option value="5" style="width: 100px">5</option>
                                        <option value="10" style="width: 100px">10</option>
                                        <option value="25" style="width: 100px">25</option>
                                        <option value="50" style="width: 100px">50</option>
                                        <option value="100" style="width: 100px">100</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <div class="form-group mb-3">
                                        <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                            data-search />
                                    </div>
                                </div>
                            </div>


                            <style>
                                .export-button {
                                    display: flex;
                                    align-items: center;
                                    gap: 6px;
                                    background-color: white;
                                    border: 1px solid #ddd;
                                    border-radius: 999px;
                                    padding: 8px 14px;
                                    font-size: 14px;
                                    cursor: pointer;
                                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                                    transition: background-color 0.2s ease, box-shadow 0.2s ease;
                                }

                                .export-button:hover {
                                    background-color: #f1f1f1;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                }

                                .dropdown-menu-export {
                                    font-size: 14px;
                                    min-width: 140px;
                                }

                                .dropdown-menu-export .dropdown-item:hover {
                                    background-color: #f8f9fa;
                                }

                                .custom-button {
                                    display: flex;
                                    align-items: center;
                                    gap: 6px;
                                    background-color: white;
                                    border: 1px solid #ddd;
                                    border-radius: 999px;
                                    padding: 8px 14px;
                                    font-size: 14px;
                                    cursor: pointer;
                                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                                    transition: background-color 0.2s ease, box-shadow 0.2s ease;
                                }

                                .custom-button:hover {
                                    background-color: #f1f1f1;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                }

                                .custom-button svg {
                                    width: 16px;
                                    height: 16px;
                                }
                            </style>

                            <div class="col-md-7 col-sm-4"></div>
                            <div class="col-sm-1"
                                style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
                                <div class="form-group dropdown">
                                    <button class="export-button dropdown-toggle" type="button" id="defaultDropdown"
                                        data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                        <i class="fa fa-download me-2"></i> Export As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                        <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                            @php
                                $currentMonth = \Carbon\Carbon::now();

                                // Agar current month April (4) ya uske baad hai → FY starts this year
                                if ($currentMonth->month >= 4) {
                                    $fyStart = $currentMonth->year;
                                    $fyEnd = $currentMonth->year + 1;
                                } else {
                                    // Agar month Jan-Mar hai → FY starts last year
                                    $fyStart = $currentMonth->year - 1;
                                    $fyEnd = $currentMonth->year;
                                }

                                $currentFinancialYear = $fyStart . '-' . $fyEnd;
                            @endphp

                        {{-- Selection --}}
                        <div class="row g-2 mb-3">
                           <div class="col-md-2">
                                <label class="form-label">Year</label>
                                <select name="year" class="form-select">
                                    <option value="{{ $currentFinancialYear }}">{{ $currentFinancialYear }}</option>
                                </select>
                            </div>


                            <div class="col-md-5">
                                <label class="form-label">{{ $type === 'department' ? 'Department' : 'Employee' }}</label>
                                <input type="text" class="form-control"
                                    value="{{ $type === 'department' ? $department->d_name : $employee->emp_code . ' - ' . $employee->emp_full_name }}"
                                    readonly>
                                <input type="hidden" name="{{ $type === 'department' ? 'department_id' : 'employee_id' }}"
                                    value="{{ $type === 'department' ? $department->d_id : $employee->emp_id }}">
                            </div>

                            <input type="hidden" name="business_id" value="{{ $business_id }}">

                             <input type="hidden" name="start_month" value="{{ $startMonth }}">
                                <input type="hidden" name="end_month" value="{{ $endMonth }}">

                                 <div class="col-md-3">
                                    <label class="form-label">Month Range</label>
                                    <input type="text" class="form-control"
                                        value="{{ \Carbon\Carbon::parse($startMonth)->format('M Y') }} to {{ \Carbon\Carbon::parse($endMonth)->format('M Y') }}"
                                        readonly>
                                </div>

                        </div>

                        <div class="">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 30%">Description</th>
                                        <th style="width: 20%">Earning</th>
                                        <th style="width: 20%">Deduction</th>
                                        <th style="width: 30%">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groupedAdhocComponents as $headingId => $components)
                                        @php
                                            $firstComponent = $components->first();
                                            $payrollHeading = optional($firstComponent->payrollHeading->first())->m_name;
                                            $headingName = strtoupper($payrollHeading ?? 'HEADING ' . $headingId);
                                        @endphp

                                        {{-- Heading row --}}
                                        <tr class="bg-light-primary">
                                            <td colspan="4" class="text-start fw-bold ps-4">{{ $headingName }}</td>
                                        </tr>

                                        {{-- Components under this heading --}}
                                        @foreach ($components as $component)
                                            @php
                                                $saved = $existingTransactions[$component->ac_id] ?? null;

                                                // dd($saved);
                                            @endphp
                                            <tr>
                                                {{-- Description --}}
                                                <td class="text-start">{{ $component->ac_adhoc_component_name }}</td>

                                                {{-- Earning --}}
                                                <td>
                                                    <input
                                                        type="text"
                                                        name="earning[{{ $component->ac_id }}]"
                                                        class="form-control text-end earning-input"
                                                        placeholder="0.00"
                                                        value="{{ $saved && $saved->rtd_earning_amount > 0 ? number_format($saved->rtd_earning_amount, 2) : '' }}">
                                                </td>

                                                {{-- Deduction --}}
                                                <td>
                                                    <input
                                                        type="text"
                                                        name="deduction[{{ $component->ac_id }}]"
                                                        class="form-control text-end deduction-input"
                                                        placeholder="0.00"
                                                        value="{{ $saved && $saved->rtd_deduction_amount > 0 ? number_format($saved->rtd_deduction_amount, 2) : '' }}">
                                                </td>

                                                {{-- Remarks --}}
                                                <td>
                                                    <input
                                                        type="text"
                                                        name="remarks[{{ $component->ac_id }}]"
                                                        class="form-control"
                                                        placeholder="Optional remarks…"
                                                        value="{{ $saved->remarks ?? '' }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No adhoc data available.</td>
                                        </tr>
                                    @endforelse

                                    {{-- Totals --}}
                                    <tr class="bg-light fw-bold">
                                        <td class="text-end">TOTAL</td>
                                        <td class="text-end" id="total-earning">0.00</td>
                                        <td class="text-end" id="total-deduction">0.00</td>
                                        <td></td>
                                    </tr>

                                    <input type="hidden" name="total_earning" id="hidden_total_earning">
                                    <input type="hidden" name="total_deduction" id="hidden_total_deduction">

                                </tbody>
                            </table>
                        </div>

                        {{-- Buttons --}}
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-outline-primary me-2">Save</button>
                            <button type="button" class="btn btn-outline-danger"
                                onclick="window.history.back()">Cancel</button>
                        </div>

                        {{-- Pagination UI Placeholder --}}
                        <div class="row mt-4">
                            <div class="col-sm-6">
                                <div id="custom-show-entries" data-show-entries></div>
                            </div>
                            <div class="col-sm-6 d-flex justify-content-end">
                                <ul data-pagination class="custom-pagination"></ul>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
       function parseFloatOrZero(val) {
            if (!val || val === '') return 0;
            let cleanVal = val.toString().replace(/,/g, '').trim();
            let parsed = parseFloat(cleanVal);
            return isNaN(parsed) ? 0 : parsed;
        }

        function calculateTotals() {
            let totalEarning = 0;
            let totalDeduction = 0;

            document.querySelectorAll('.earning-input').forEach(input => {
                let val = parseFloatOrZero(input.value);
                totalEarning += val;

                // Optional: Format the input value on blur to show 2 decimals
            });

            document.querySelectorAll('.deduction-input').forEach(input => {
                let val = parseFloatOrZero(input.value);
                totalDeduction += val;
            });

            // Ensure 2 decimal places
            let formattedEarning = totalEarning.toFixed(2);
            let formattedDeduction = totalDeduction.toFixed(2);

            document.getElementById('total-earning').innerText = formattedEarning;
            document.getElementById('total-deduction').innerText = formattedDeduction;
            document.getElementById('hidden_total_earning').value = formattedEarning;
            document.getElementById('hidden_total_deduction').value = formattedDeduction;
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('earning-input') || e.target.classList.contains('deduction-input')) {
                calculateTotals();
            }
        });

        document.addEventListener('DOMContentLoaded', calculateTotals);
    </script>


<script>

    document.querySelector('form').addEventListener('submit', function(e) {
        // Before submitting, remove inputs with 0 or empty values
        document.querySelectorAll('tr').forEach(row => {
            let earningInput = row.querySelector('.earning-input');
            let deductionInput = row.querySelector('.deduction-input');

            if (earningInput && deductionInput) {
                let earningVal = parseFloatOrZero(earningInput.value);
                let deductionVal = parseFloatOrZero(deductionInput.value);

                // If both are 0, remove their name attributes so they are not submitted
                if (earningVal === 0 && deductionVal === 0) {
                    earningInput.removeAttribute('name');
                    deductionInput.removeAttribute('name');
                    let remarksInput = row.querySelector('input[name^="remarks"]');
                    if (remarksInput) remarksInput.removeAttribute('name');
                }
            }
        });

        // Recalculate totals just in case
        calculateTotals();
    });

    // Allow only valid numeric values while typing
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('earning-input') || e.target.classList.contains('deduction-input')) {

            // Remove invalid characters (anything except digits, dot, hyphen)
            e.target.value = e.target.value.replace(/[^\d.-]/g, '');

            calculateTotals(); // Recalculate totals after change
        }
    });

    // Clean pasted values (Excel formats, currency formats, extra spaces, etc.)
    document.addEventListener('paste', function(e) {
        if (e.target.classList.contains('earning-input') || e.target.classList.contains('deduction-input')) {
            e.preventDefault();

            let pastedData = (e.clipboardData || window.clipboardData).getData('text');

            // Clean formatting -> removes commas, currency symbols, spaces, etc.
            pastedData = pastedData
                .replace(/[^\d.-]/g, '') // remove all non-digit except dot and minus
                .replace(/,/g, '')       // remove commas
                .trim();

            e.target.value = pastedData;

            calculateTotals(); // Recalculate totals
        }
    });

</script>
@endsection
