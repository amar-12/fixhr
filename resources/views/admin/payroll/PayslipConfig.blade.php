@extends('admin.layout.master')
@section('title', 'Payslip Configuration')
@section('css')
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

@endsection
@section('content')

    {{-- Breadcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/payroll/settings') }}">Payroll Settings</a></li>
                    <li class="active"><span><b>Payslip Configuration</b></span></li>
                </ol>
            </div>

            <div class="col-md-6"></div>

            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addPayslipConfigBtn">
                                    Add Payslip Config
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breadcrumbs End --}}

    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header border-0">
                    <h4 class="card-title">Payslip Configuration</h4>
                </div>

                <div class="card-body">
                    @csrf
                    <div class="row">
                        <div class="col-sm-1">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test" style="width: 100%"
                                    data-length>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                    data-search />
                            </div>
                        </div>
                        <div class="col-sm-7">
                        </div>
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
                    </div>

                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="payslip-config-table">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size: 13px">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
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


    <!-- Modal for Payslip Configuration -->
    <div class="modal fade" id="payslipConfigModal" tabindex="-1" role="dialog" aria-labelledby="payslipConfigModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payslipConfigModalTitle">Add Payslip Configuration</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="payslipConfigForm">
                    @csrf
                    <input type="hidden" name="pc_id" id="pc_id">
                    <div class="modal-body">
                        <div class="row mb-3">
                        </div>
                        <div class="row">
                            <!-- Employee Info Section -->
                            <div class="col-lg-3">
                                <h6 class="fw-bold mb-2">Employee Info</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_employee_code"
                                        name="pc_show_employee_code" value="1">
                                    <label class="form-check-label" for="pc_show_employee_code">Employee Code</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_employee_name"
                                        name="pc_show_employee_name" value="1">
                                    <label class="form-check-label" for="pc_show_employee_name">Employee Name</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_designation"
                                        name="pc_show_designation" value="1">
                                    <label class="form-check-label" for="pc_show_designation">Designation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_department"
                                        name="pc_show_department" value="1">
                                    <label class="form-check-label" for="pc_show_department">Department</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_branch"
                                        name="pc_show_branch" value="1">
                                    <label class="form-check-label" for="pc_show_branch">Branch</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_doj" name="pc_show_doj"
                                        value="1">
                                    <label class="form-check-label" for="pc_show_doj">Date of Joining</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_ip_uan"
                                        name="pc_show_ip_uan" value="1">
                                    <label class="form-check-label" for="pc_show_ip_uan">IP / UAN Numbers</label>
                                </div>
                            </div>

                            <!-- Payroll Details Section -->
                            <div class="col-lg-3">
                                <h6 class="fw-bold mb-2">Payroll Details</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_bank_details"
                                        name="pc_show_bank_details" value="1">
                                    <label class="form-check-label" for="pc_show_bank_details">Bank Details</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_month"
                                        name="pc_show_month" value="1">
                                    <label class="form-check-label" for="pc_show_month">Month</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_earnings_breakdown"
                                        name="pc_show_earnings_breakdown" value="1">
                                    <label class="form-check-label" for="pc_show_earnings_breakdown">Earnings
                                        Breakdown</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_net_pay"
                                        name="pc_show_net_pay" value="1">
                                    <label class="form-check-label" for="pc_show_net_pay">Net Pay</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_total_ctc"
                                        name="pc_show_total_ctc" value="1">
                                    <label class="form-check-label" for="pc_show_total_ctc">Total CTC</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_round_off_net_salary"
                                        name="pc_round_off_net_salary" value="1">
                                    <label class="form-check-label" for="pc_round_off_net_salary">Round-off Net
                                        Salary</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_net_salary_in_words"
                                        name="pc_show_net_salary_in_words" value="1">
                                    <label class="form-check-label" for="pc_show_net_salary_in_words">Net Salary in
                                        Words</label>
                                </div>
                            </div>

                            <!-- Attendance Section -->
                            <div class="col-lg-3">
                                <h6 class="fw-bold mb-2">Attendance</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_working_days"
                                        name="pc_show_working_days" value="1">
                                    <label class="form-check-label" for="pc_show_working_days">Working Days</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_month_days"
                                        name="pc_show_month_days" value="1">
                                    <label class="form-check-label" for="pc_show_month_days">Month Days</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_days_present"
                                        name="pc_show_days_present" value="1">
                                    <label class="form-check-label" for="pc_show_days_present">Days Present</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_salary_days"
                                        name="pc_show_salary_days" value="1">
                                    <label class="form-check-label" for="pc_show_salary_days">Salary Days</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_lwp_days"
                                        name="pc_show_lwp_days" value="1">
                                    <label class="form-check-label" for="pc_show_lwp_days">LWP Days</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_leaves_taken"
                                        name="pc_show_leaves_taken" value="1">
                                    <label class="form-check-label" for="pc_show_leaves_taken">Leaves Taken</label>
                                </div>
                            </div>

                            <!-- Deductions & Other -->
                            <div class="col-lg-3">
                                <h6 class="fw-bold mb-2">Deductions & Other</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="pc_show_employee_deductions_breakdown"
                                        name="pc_show_employee_deductions_breakdown" value="1">
                                    <label class="form-check-label" for="pc_show_employee_deductions_breakdown">Employee
                                        Deductions</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="pc_show_employer_deductions_breakdown"
                                        name="pc_show_employer_deductions_breakdown" value="1">
                                    <label class="form-check-label" for="pc_show_employer_deductions_breakdown">Employer
                                        Deductions</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_signature"
                                        name="pc_show_signature" value="1">
                                    <label class="form-check-label" for="pc_show_signature">Signature</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pc_show_disclaimer"
                                        name="pc_show_disclaimer" value="1">
                                    <label class="form-check-label" for="pc_show_disclaimer">Disclaimer</label>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Setup CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            datatable({
                tableId: "payslip-config-table",
                url: "{{ route('payslip-config.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            // Open modal for Add
            $('#addPayslipConfigBtn').on('click', function() {
                $('#payslipConfigForm')[0].reset();
                $('#pc_id').val('');
                $('#payslipConfigModalTitle').text('Add Payslip Configuration');
                $('#saveBtn').text('Save');

                // Reset all checkboxes
                $('input[type="checkbox"]').prop('checked', false);

                $('#payslipConfigModal').modal('show');
            });

            // Submit form
            $('#payslipConfigForm').on('submit', function(e) {
                e.preventDefault();
                $('#saveBtn').attr('disabled', true);

                $.ajax({
                    url: "{{ route('payslip-config.storeOrUpdate') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#saveBtn').attr('disabled', false);
                        $('#payslipConfigModal').modal('hide');
                        Swal.fire('Success!', response.message, 'success');
                        $('#payslip-config-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        $('#saveBtn').attr('disabled', false);
                        let errors = xhr.responseJSON.errors;
                        let messages = '';
                        $.each(errors, function(key, value) {
                            messages += `<p>${value[0]}</p>`;
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: messages
                        });
                    }
                });
            });

            // Edit payslip config
            $(document).on('click', '.edit-payslip-config', function() {
                const data = $(this).data();

                $('#pc_id').val(data.id);
                $('#pc_b_id').val(data.b_id);

                // All checkbox field keys
                const checkboxes = [
                    'employee_code', 'employee_name', 'designation', 'department',
                    'branch', 'ip_uan', 'bank_details', 'month', 'doj',
                    'earnings_breakdown', 'employee_deductions_breakdown',
                    'employer_deductions_breakdown',
                    'net_pay', 'total_ctc', 'round_off_net_salary', 'net_salary_in_words',
                    'working_days', 'month_days', 'days_present', 'salary_days', 'lwp_days',
                    'leaves_taken',
                    'signature', 'disclaimer'
                ];

                checkboxes.forEach(function(key) {
                    const field = `pc_show_${key}`;
                    $(`#${field}`).prop('checked', data[key] == 1);
                });

                $('#payslipConfigModalTitle').text('Update Payslip Configuration');
                $('#saveBtn').text('Update');
                $('#payslipConfigModal').modal('show');
            });
        });
    </script>

@endsection
@endsection
