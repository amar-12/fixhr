@extends('admin.layout.master')
@section('title', 'Adhoc Payments/Deductions')

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
            <li class="active"><span><b>Adhoc Payments/Deductions</b></span></li>
        </ol>
    </div>

    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Adhoc Payments/Deductions</div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header border-0">
                    <h5 class="card-title">Adhoc Components List</h5>
                </div>

                <form method="POST" action="{{ route('adhoc.store') }}">
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


                        {{-- Selection --}}
                        <div class="row g-2 mb-3">
                            <div class="col-md-2">
                                <label class="form-label">Year</label>
                                <select name="year" class="form-select">
                                    @if (!empty($financialYear))
                                        <option value="{{ $financialYear }}">{{ $financialYear }}</option>
                                    @else
                                        <option value="">-- No Financial Year Found --</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Payroll Period</label>
                                <select name="payroll_period_id" class="form-select">
                                    <option value="{{ $payrollPeriod->pp_id }}">{{ $payrollPeriod->pp_name }}</option>
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

                                               {{-- Current code --}}
                                               <td>

                                                <input type="text" name="earning[{{ $component->ac_id }}]"
                                                    class="form-control text-end earning-input" placeholder="0.00"
                                                    value="{{ $saved && $saved->earning_amount > 0 ? number_format((float)$saved->earning_amount, 2, '.', '') : '' }}">
                                               </td>

                                               <td>

                                                 <input type="text" name="deduction[{{ $component->ac_id }}]"
                                                    class="form-control text-end deduction-input" placeholder="0.00"
                                                    value="{{ $saved && $saved->deduction_amount > 0 ? number_format((float)$saved->deduction_amount, 2, '.', '') : '' }}">
                                               </td>


                                                {{-- Remarks --}}
                                                <td>
                                                    <input type="text" name="remarks[{{ $component->ac_id }}]"
                                                        class="form-control" placeholder="Optional remarks…"
                                                        value="{{ $saved->remarks ?? '' }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No adhoc data available.
                                            </td>
                                        </tr>
                                    @endforelse

                                    {{-- Totals --}}
                                    <tr class="bg-light fw-bold">
                                        <td class="text-end">TOTAL</td>
                                        <td class="text-end" id="total-earning">00</td>
                                        <td class="text-end" id="total-deduction">00</td>
                                        <td></td>
                                    </tr>
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
            let totalEarning = 0,
                totalDeduction = 0;

            document.querySelectorAll('.earning-input').forEach(input => {
                totalEarning += parseFloatOrZero(input.value);
            });

            document.querySelectorAll('.deduction-input').forEach(input => {
                totalDeduction += parseFloatOrZero(input.value);
            });

            // document.getElementById('total-earning').innerText = totalEarning.toFixed(2);
            // document.getElementById('total-deduction').innerText = totalDeduction.toFixed(2);

            document.getElementById('total-earning').innerText = Math.round(totalEarning);
            document.getElementById('total-deduction').innerText = Math.round(totalDeduction);


        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('earning-input') || e.target.classList.contains('deduction-input')) {
                calculateTotals();
            }
        });

        document.addEventListener('DOMContentLoaded', calculateTotals);
    </script>
@endsection
