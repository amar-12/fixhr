@extends('admin.layout.master')
@section('title', 'Salary Allowance')

@section('css')

@endsection
@section('content')

    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="">Payroll</a></li>
                    <li class="active"><span><b>Withheld Salary</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            {{-- <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addSalaryAllowanceBtn">Add </button>
                            </div>--}}
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#withheldSalaryModal">
                                <i class="fa fa-ban"></i> Withhold Salary
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bradcrumbs End --}}

 <div class="row mt-5">
    <div class="col-xl-12 col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <h4 class="card-title">Withheld Salary</h4>
            </div>
            <div class="card-body">
                @csrf
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

                <div class="table-responsive">
                    <table class="table display table-hover table-vcenter text-wrap border-bottom"
                        id="attendance-shift-type-table-dynamic">
                        <thead>
                            <tr>
                                @foreach ($columns as $column)
                                <th style="font-size: 13px">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
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
<!-- Withheld Salary Modal -->
<div class="modal fade" id="withheldSalaryModal" tabindex="-1" aria-labelledby="withheldSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="withheldSalaryForm" method="POST" action="{{ route('withheld.salary.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="withheldSalaryModalLabel">Withhold Salary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <!-- Payroll Period -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payroll Period <span class="text-danger">*</span></label>
                            <select name="payroll_period_id" id="payroll_period_id" class="form-control select2 required-field">
                                <option value="">-- Select Payroll Period --</option>
                                @foreach($payroll_periods as $period)
                                    <option value="{{ $period->pp_id }}">{{ $period->pp_name }}</option>
                                @endforeach
                            </select>
                            <small id="payroll_period_id_error" class="text-danger"></small>
                        </div>

                        <!-- Select Type (Employee or Department) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apply To <span class="text-danger">*</span></label>
                            <select id="apply_type" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="employee">Employee</option>
                                <option value="department">Department</option>
                            </select>
                        </div>

                        <!-- Employee Selection -->
                        <div class="col-md-6 mb-3 apply-section employee-section d-none">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control select2">
                                <option value="">-- Select Employee --</option>
                                @foreach($employeeList as $emp)
                                    <option value="{{ $emp->emp_id }}">{{ $emp->emp_full_name }} ({{ $emp->emp_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department Selection -->
                        <div class="col-md-6 mb-3 apply-section department-section d-none">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-control select2">
                                <option value="">-- Select Department --</option>
                                @foreach($departmentList as $dept)
                                    <option value="{{ $dept->d_id }}">{{ $dept->d_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Reason -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" rows="3" class="form-control required-field" placeholder="Enter reason for withholding salary"></textarea>
                            <small id="reason_error" class="text-danger"></small>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="withheldSaveBtn" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
@section('script')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            $('#apply_type').on('change', function() {
                let type = $(this).val();
                $('.apply-section').addClass('d-none');
                if (type === 'employee') {
                    $('.employee-section').removeClass('d-none');
                } else if (type === 'department') {
                    $('.department-section').removeClass('d-none');
                }
            });

            // Simple validation before submit
            $('#withheldSalaryForm').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let isValid = true;

                form.find('.required-field').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    Swal.fire({ icon: 'error', text: 'Please fill in all required fields.' });
                    return;
                }

                // Submit via AJAX
                $.ajax({
                    url: form.attr('action'),
                    method: "POST",
                    data: form.serialize(),
                    beforeSend: () => $('#withheldSaveBtn').prop('disabled', true),
                    success: (response) => {
                        if (response.status) {
                            $('#withheldSalaryModal').modal('hide');
                            Swal.fire({ icon: 'success', text: response.message, timer: 2000, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', text: response.message });
                        }
                    },
                    error: () => Swal.fire({ icon: 'error', text: 'Something went wrong. Try again.' }),
                    complete: () => $('#withheldSaveBtn').prop('disabled', false),
                });
            });
        });
    </script>


    <script type="text/javascript">
        $(function() {
            // CSRF Token Setup (for all AJAX requests)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            initializeDatatable();

            // Initialize Select2
            initializeSelect2();

            // Handle Create or Update Shift Type
            $('#salaryAllowanceForm').on('submit', handleSalaryAllowanceSubmit);
            // Handle Delete Salary Allowance
            $(document).on('click', '.delete-salary-allowance', handleDeleteSalaryAllowance);

        });


        // Initialize DataTable
        function initializeDatatable() {
            datatable({
                tableId: "attendance-shift-type-table-dynamic",
                url: "{{ route('payroll.holdSalary.list') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]'
            });
        }


        // Initialize Select2
        function initializeSelect2() {
            $('.select2').select2();

            $('#salaryAllowanceModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#salaryAllowanceModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
        }


        // Reset form fields
        function resetForm() {
            // Reset the form itself
            $('#salaryAllowanceForm')[0].reset();

            // Manually reset any fields that require special handling
            $('#sa_id').val('');
            $('#name').val('');
            $('#des').val('');
            $('#calculation_type').val('').trigger('change');
            $('#earning_type_id').val('').trigger('change');
            $('#payroll_heading_id').val('').trigger('change');
            $('#calculation_value').val('');
            $('#sequence_valu').val('');

            // Reset checkboxes
            $('#pf_check').prop('checked', false);
            $('#pf_condition').prop('checked', false);
            $('#esic_check').prop('checked', false);
            $('#calculate_basis').prop('checked', false);
            $('#tax_check').prop('checked', false);
            $('#payslip_check').prop('checked', false);
            $('#status_check').prop('checked', false);

            $('#payslip').val('');

            $('#salaryAllowanceModal').find('input, select').each(function() {
                const fieldId = $(this).attr('id');
                $('#' + fieldId + '_error').text('');
            });
        }



    </script>


@endsection
