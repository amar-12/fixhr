<?php
$statusFilter = ['1' => 'Active', '0' => 'Inactive'];

?>
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
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header border-0">
                    <h4 class="card-title">Employee Payroll</h4>
                    {{-- <div class="d-lg-flex d-block ms-auto">
                        <div class="btn-list">
                            <button type="button" class="btn btn-outline-primary" id="addEmpSalaryBtn">Add Salary</button>
                        </div>
                    </div> --}}
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-1">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-2">
                            <div class="form-group">
                                <p class="form-label"> Status</p>
                                <select id="activeFilter" class="search_test custom-heighlight" data-filter>
                                    <option value="">All</option>
                                    <option value="71">Active</option>
                                    <option value="72">Inactive</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <div class="form-group mb-3">
                                    <input type="search" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">

                        </div>



                        <div class="col-md-2"
                            style="display: flex;justify-content: end;margin-top: 28px;margin-left: 116px;">
                            <div class="form-group">
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
                            <div class="btn-list ms-3">
                                <div>
                                    <button class="btn btn-info" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
                                    </button>

                                    <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown"
                                        style="min-width: 220px;">
                                        {{-- <li>
                                            <a class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                                id="approveBtn" value="1">
                                                <i class="las la-check-circle"></i> Approve
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                                id="rejectBtn" value="0">
                                                <i class="las la-times-circle"></i> Reject
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a href="{{ route('employee.salary.export') }}"
                                                class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li>


                                        {{-- <li>
                                            <a href="{{ route('salary-master.sample.export') }}"
                                                class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li> --}}

                                        <li>
                                            <button
                                                class="dropdown-item text-secondary fw-semibold d-flex align-items-center gap-2 empImportBtn"
                                                type="button">
                                                <i class="las la-file-import"></i> Import Salary
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="manage-salary-table-dynamic">
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


    <!-- Employee Salary Import Modal -->
    <div class="modal fade" id="employeeSalaryImportModal" tabindex="-1" aria-labelledby="employeeSalaryImportModalTitle"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeSalaryImportModalTitle">Import Employee Salary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <form id="empMasterSubmit" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="financial_year" class="form-label">Financial Year</label>
                            <select id="financial_year" name="financial_year" class="form-select" required>
                                <option value="">Select financial year</option>
                                @foreach ($financialYears as $fyear)
                                    <option value="{{ $fyear->fy_id }}">{{ $fyear->fy_year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="salaryFile" class="form-label">Upload Salary File</label>
                            <input type="file" name="file" id="salaryFile" class="form-control"
                                accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Accepted formats: .xlsx, .xls, .csv</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- MODAL -->
    <div class="modal fade" id="empSalaryModal" tabindex="-1" role="dialog" aria-labelledby="empSalaryModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="empSalaryModalTitle">Add Employee Payroll</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="payrollPolicyForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="ps_id" id="ps_id">
                        <div class="row">
                            <x-input type="text" id="ps_name" label="Policy Name" name="ps_name"
                                placeholder="Policy Name" astric="*" required min="0" />
                        </div>
                        <div class="row">
                            <x-textarea id="ps_description" label="Description" name="ps_description"
                                placeholder="Description" astric="*" astric="true" />
                        </div>
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_basic_salary_percentage" label="Basic Salary (%)"
                                    placeholder="Basic Salary (%)" name="ps_basic_salary_percentage" astric="*"
                                    required min="0" />
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_hra_allowance_percentage" label="House Rent Allowance (%)"
                                    placeholder="House Rent Allowance (%)" name="ps_hra_allowance_percentage"
                                    astric="*" required min="0" />
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_conveyance_allowance_threshhold" label="Conveyance Allowance (%)"
                                    placeholder="Conveyance Allowance (%)" name="ps_conveyance_allowance_threshhold"
                                    astric="*" required min="0" />
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_medical_allowance_threshhold" label="Medical Allowance (%)"
                                    placeholder="Medical Allowance (%)" name="ps_medical_allowance_threshhold"
                                    astric="*" required min="0" />
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END MODAL -->
@endsection
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('script')
    <script>
        function showPayrollSettingAlert() {
            Swal.fire({
                icon: 'warning',
                title: 'Payroll Master Settings Missing!',
                text: 'Please configure Payroll Master Settings before editing employee payroll.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            // Show Import Modal
            $(document).on('click', '.empImportBtn', function() {
                $('#employeeSalaryImportModal').modal('show');
            });

            // Handle Import Form Submission
            $('#empMasterSubmit').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                $('#saveBtn').attr('disabled', true).text('Importing...');

                $.ajax({
                    url: '{{ route('employees.salary.import') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Something went wrong';
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: msg
                        });
                    },
                    complete: function() {
                        $('#saveBtn').attr('disabled', false).text('Import');
                    }
                });
            });

            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        });


        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "manage-salary-table-dynamic",
                url: "{{ route('employee.salaries') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false,
            });


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Create or Update Policy
            $('#payrollPolicyForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#ps_id').val(); // Get the ID of the policy


                $.ajax({
                    url: "{{ url('admin/settings/payroll/payroll-policies') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#saveBtn').attr('disabled', true);
                    },
                    success: function(response) {
                        $('#saveBtn').attr('disabled', false);
                        $('#empSalaryModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            text: response.success,
                            timer: 3000,
                            didClose: () => {
                                $('#payrollPolicyForm')[0].reset();
                                location.reload(); // Reload the page after deletion
                            }
                        });
                    },
                    error: function(response) {
                        $('#saveBtn').attr('disabled', false);
                        let errors = response.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            alert(key + ": " + value);
                        });
                    }
                });
            });

            // Delete Policy
            $(document).on('click', '.delete-policy', function() {
                var id = $(this).attr('data-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will not be able to recover this payroll-policy!',
                    // timer: 3000,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "{{ route('payroll-policies.destroy', ':id') }}";
                        url = url.replace(':id', id);
                        $.ajax({
                            url: url,
                            method: 'DELETE',
                            success: function(response) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.success,
                                    icon: 'success',
                                    timer: 3000, // 3 seconds
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    didClose: () => {
                                        location
                                            .reload(); // Reload the page after deletion
                                    }
                                });
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#addEmpSalaryBtn', function() {
                $('#saveBtn').attr('disabled', false);
                $('#empSalaryModalTitle').html('Add Employee Payroll');
                $('#saveBtn').html('Save');
                $('#ps_id').val('');
                // $('#ap_b_id').val(b_id);
                $('#ps_name').val('');
                $('#ps_description').val('');
                $('#ps_basic_salary_percentage').val('');
                $('#ps_hra_allowance_percentage').val('');
                $('#ps_conveyance_allowance_threshhold').val('');
                $('#ps_medical_allowance_threshhold').val('');
                $('#ap_holiday_overtime_rate').val('');
                $('#ps_employee_pf_percentage').val('');
                $('#ps_employer_pf_percentage').val('');
                $('#ps_employee_esic_percentage').val('');
                $('#ps_employer_esic_percentage').val('');
                $('#ps_pf_threshhold').val('');
                $('#ps_esic_threshhold').val('');
                $('#empSalaryModal').modal('show');
            });

            // Edit Policy - Set form values
            $(document).on('click', '.edit-salary', function() {

                $('#saveBtn').attr('disabled', false);
                var id = $(this).data('id');
                // var b_id = $(this).data('b_id');
                var name = $(this).data('name');
                var description = $(this).data('description');
                var ps_basic_salary_percentage = $(this).data('ps_basic_salary_percentage');
                var ps_hra_allowance_percentage = $(this).data('ps_hra_allowance_percentage');
                var ps_conveyance_allowance_threshhold = $(this).data('ps_conveyance_allowance_threshhold');
                var ps_medical_allowance_threshhold = $(this).data('ps_medical_allowance_threshhold');




                // Set the form values
                $('#empSalaryModalTitle').html('Update Payroll Policies');
                $('#saveBtn').html('Update');
                $('#ps_id').val(id);
                // $('#ap_b_id').val(b_id);
                $('#ps_name').val(name);
                $('#ps_description').val(description);
                $('#ps_basic_salary_percentage').val(ps_basic_salary_percentage);
                $('#ps_hra_allowance_percentage').val(ps_hra_allowance_percentage);
                $('#ps_conveyance_allowance_threshhold').val(ps_conveyance_allowance_threshhold);
                $('#ps_medical_allowance_threshhold').val(ps_medical_allowance_threshhold);


                $('#empSalaryModal').modal('show');
            });

        });
    </script>
@endsection
