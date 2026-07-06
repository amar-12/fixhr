@php
    use Carbon\Carbon;
@endphp
@extends('admin.layout.master')
@section('title', 'Assign Shift Batch Wise')
@section('css')
    <style>
        table th:first-of-type,
        table th:nth-child(2) {
            width: auto !important;
        }

        .fc .fc-bg-event .fc-event-title {
            font-size: 1.25em !important;
            font-weight: bold !important;
        }

        .fc-daygrid-block-event .fc-event-title {
            font-size: 12px;
            font-weight: 500;
        }

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
    <style>
        /* Fix Select2 search input width and visibility in dropdown */
        .select2-container--default .select2-search--dropdown .select2-search__field {
            width: 100% !important;
            min-width: 100px !important;
            padding: 4px 8px !important;
        }

        /* Ensure dropdown adapts well inside modal */
        .select2-dropdown {
            z-index: 1051; /* Higher than Bootstrap modal (1050) */
        }

        /* Optional: better styling for multi-select tags */
        .select2-selection__rendered {
            line-height: 28px !important;
        }
        .select2-selection__choice {
            margin-top: 5px !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between py-5">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a class="text-white">Employee</a></li>
                <li class="active"><span><b>Assign Shift Batch Wise</b></span></li>
            </ol>
            <div>
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <x-button type="button" class="btn btn-outline-primary create-button"
                                    data-bs-toggle="modal" data-bs-target="#compOffFormModal" data-title="Create Comp Off"
                                    onclick="openBatchModal();">
                                    Create Batch
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- Filters --}}
                <div class="row">
                    <div class="col-12 col-md-6 row">
                        <div class="col-6 col-xl-3">
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

                        <div class="col-6 col-xl-4">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                    data-search />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 d-flex align-items-center justify-content-end gap-3">
                        <div>
                            <div class="dropdown">
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

                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table display table-hover table-vcenter text-wrap border-bottom"
                        id="compoff-policy-table-dynamic">
                        <thead>
                            <tr>
                                @foreach ($columns as $column)
                                    <th style="font-size: 13px">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>
                </div>

                {{-- Pagination --}}
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

    {{-- Modal Start --}}
    <div class="modal fade" id="createBatch" tabindex="-1" aria-labelledby="createBatchLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="createBatchForm" method="POST" action="" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="createBatchLabel">Create Batch</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <input type="hidden" id="emp_modal_id" name="emp_modal_id">

                            <div class="col-md-6">
                                <label for="sb_name" class="form-label">Batch Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sb_name') is-invalid @enderror"
                                    id="sb_name" name="sb_name" placeholder="Enter Batch Name"
                                    value="{{ old('sb_name') }}" autocomplete="off">
                                <div class="invalid-feedback" id="sb_name_error">
                                    @error('sb_name') {{ $message }} @else Please enter batch name. @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="sb_code" class="form-label">Batch Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sb_code') is-invalid @enderror"
                                    id="sb_code" name="sb_code" placeholder="e.g., Batch123 or Batch_123"
                                    value="{{ old('sb_code') }}" autocomplete="off">
                                <div class="invalid-feedback" id="sb_code_error">
                                    @error('sb_code') {{ $message }} @else Please enter batch code. @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="pst_id" class="form-label">Shift Policy <span class="text-danger">*</span></label>
                                <select class="form-select @error('pst_id') is-invalid @enderror"
                                    id="pst_id" name="pst_id">
                                    <option value="">----- Select Shift Policy -----</option>
                                    @foreach ($shiftPolicies as $shiftPolicy)
                                        <option value="{{ $shiftPolicy->pst_id }}"
                                            {{ old('pst_id') == $shiftPolicy->pst_id ? 'selected' : '' }}>
                                            {{ $shiftPolicy->pst_name }} - {{ $shiftPolicy->pst_code }}
                                            @if ($shiftPolicy->pst_start_time && $shiftPolicy->pst_end_time)
                                                ({{ Carbon::parse($shiftPolicy->pst_start_time)->format('h:i A') }} - {{ Carbon::parse($shiftPolicy->pst_end_time)->format('h:i A') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="pst_id_error">
                                    @error('pst_id') {{ $message }} @else Please select a shift policy. @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                    id="start_date" name="start_date" value="{{ old('start_date') }}">
                                <div class="invalid-feedback" id="start_date_error">
                                    @error('start_date') {{ $message }} @else Please select start date. @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                    id="end_date" name="end_date" value="{{ old('end_date') }}">
                                <div class="invalid-feedback" id="end_date_error">
                                    @error('end_date') {{ $message }} @else Please select end date. @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="row align-items-end g-3">
                            <!-- Select Employees (Left - Takes more space) -->
                            <div class="col-md-7" style="padding: 15px;">
                                <label for="emp_id" class="form-label">Select Employees</label>
                                <select class="form-select select2-employee @error('emp_id') is-invalid @enderror"
                                    id="emp_id" name="emp_id[]" multiple data-placeholder="Search and select employees...">
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->emp_id }}"
                                            {{ in_array($employee->emp_id, old('emp_id', [])) ? 'selected' : '' }}>
                                            {{ $employee->emp_full_name }} - {{ $employee->emp_code }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @error('emp_id') {{ $message }} @enderror
                                </div>
                            </div>

                            <!-- OR Upload File Section (Center + Right) -->
                            <div class="col-md-3">
                                <label for="emp_id" class="form-label">OR Upload File</label>
                                <input type="file" name="import_file" id="import_file"
                                    class="form-control @error('import_file') is-invalid @enderror"
                                    accept=".xlsx,.csv">
                                <div class="form-text mt-1">
                                    <small><strong>Note:</strong> Only .xlsx and .csv files allowed</small>
                                </div>
                                <div class="invalid-feedback">
                                    @error('import_file') {{ $message }} @enderror
                                </div>
                            </div>
                            <div class="col-md-2" style="padding: 20px;">
                                <label class="form-label">Download Sample</label>
                                <a href="{{ route('employee.batch.shift.download') }}"
                                   class="btn btn-warning shadow-sm" title="Download Template">
                                    <i class="las la-file-download fs-5"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Custom Error for Employee Assignment (from controller) -->
                        @if ($errors->has('employee_assignment'))
                            <div class="alert alert-danger mt-3">
                                <strong>{{ $errors->first('employee_assignment') }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBatchBtn">
                            <span class="submit-text">Save Changes</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reopen modal if there are validation errors -->
    @if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('createBatch'));
            modal.show();
        });
    </script>
    @endif
    {{-- Modal End --}}
@endsection

@section('script')
<!-- Add the CSS for Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<!-- Add the JS for Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#emp_id').select2({
            placeholder: "Select Employee",
            allowClear: true,
            width: '100%',
            dropdownParent: $("#createBatch"),  // Critical: attach to the modal
            // Optional: improve performance if you have many employees
            // minimumInputLength: 1,
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Success message
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                customClass: {
                    toast: 'swal2-toast-green-glow'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        // Error message
        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        // Error messages (if multiple validation errors or custom messages are passed)
        @if ($errors->any())
            let errorMessages = '';
            @foreach ($errors->all() as $error)
                errorMessages += '{{ $error }}' + '<br>';
            @endforeach
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: 'Validation Errors',
                html: errorMessages,
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif
    });
</script>

<script>
    $(document).ready(function() {
        $('#createBatchForm').on('submit', function(e) {
            e.preventDefault(); // Always prevent default first

            let isValid = true;

            // Clear all previous error states
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            // 1. Employee Assignment Check (Highest Priority)
            const selectedEmployees = $('#emp_id').val() || [];
            const hasFile = $('#import_file')[0].files.length > 0;

            if (selectedEmployees.length === 0 && !hasFile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Employee Required',
                    html: '<strong>Please assign at least one employee</strong><br>by selecting from the list <strong>OR</strong> uploading an Excel/CSV file.',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#d33',
                    allowOutsideClick: false,
                    didClose: () => {
                        // Focus on select2 input after swal closes
                        $('#emp_id').next('.select2-container').find('.select2-search__field').focus();
                    }
                });

                // Scroll to employee section
                $('#emp_id').next('.select2-container')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false; // Stop further validation
            }

            // 2. Batch Name Validation
            const batchName = $('#sb_name').val().trim();
            if (batchName === '') {
                $('#sb_name').addClass('is-invalid');
                $('#sb_name_error').text('Please enter batch name.');
                isValid = false;
            } else if (batchName.length < 3) {
                $('#sb_name').addClass('is-invalid');
                $('#sb_name_error').text('Batch name must be at least 3 characters.');
                isValid = false;
            }

            // 3. Batch Code Validation
            const batchCode = $('#sb_code').val().trim();
            const batchCodeRegex = /^[a-zA-Z0-9_-]+$/;
            const specificFormatRegex = /^[a-zA-Z]+[a-zA-Z0-9_-]*[0-9]+$/;

            if (batchCode === '') {
                $('#sb_code').addClass('is-invalid');
                $('#sb_code_error').text('Please enter batch code.');
                isValid = false;
            } else if (batchCode.length < 5) {
                $('#sb_code').addClass('is-invalid');
                $('#sb_code_error').text('Batch code must be at least 5 characters long.');
                isValid = false;
            } else if (!batchCodeRegex.test(batchCode)) {
                $('#sb_code').addClass('is-invalid');
                $('#sb_code_error').text('Batch code can only contain letters, numbers, underscores (_), and hyphens (-).');
                isValid = false;
            } else if (!specificFormatRegex.test(batchCode)) {
                $('#sb_code').addClass('is-invalid');
                $('#sb_code_error').text('Batch code must start with letters and end with numbers (e.g., Batch123, Batch_123, Batch-123).');
                isValid = false;
            }

            // 4. Shift Policy
            const shiftPolicy = $('#pst_id').val();
            if (!shiftPolicy) {
                $('#pst_id').addClass('is-invalid');
                $('#pst_id_error').text('Please select a shift policy.');
                isValid = false;
            }

            // 5. Dates
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            if (!startDate) {
                $('#start_date').addClass('is-invalid');
                $('#start_date_error').text('Please select start date.');
                isValid = false;
            }

            if (!endDate) {
                $('#end_date').addClass('is-invalid');
                $('#end_date_error').text('Please select end date.');
                isValid = false;
            }

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                if (end < start) {
                    $('#end_date').addClass('is-invalid');
                    $('#end_date_error').text('End date must be after or same as start date.');
                    isValid = false;
                }
            }

            // Final: If all valid → Submit form
            if (isValid) {
                this.submit(); // Allow actual form submission
            } else {
                // Scroll to first error field
                const firstError = $('.is-invalid').first();
                if (firstError.length) {
                    firstError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });

        // Real-time Validations (unchanged but cleaned)
        $('#sb_name').on('blur keyup', function() {
            const value = $(this).val().trim();
            if (value === '') {
                $(this).addClass('is-invalid');
                $('#sb_name_error').text('Please enter batch name.');
            } else if (value.length < 3) {
                $(this).addClass('is-invalid');
                $('#sb_name_error').text('Batch name must be at least 3 characters.');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        $('#sb_code').on('blur keyup', function() {
            const value = $(this).val().trim();
            const batchCodeRegex = /^[a-zA-Z0-9_-]+$/;
            const specificFormatRegex = /^[a-zA-Z]+[a-zA-Z0-9_-]*[0-9]+$/;

            if (value === '') {
                $(this).addClass('is-invalid');
                $('#sb_code_error').text('Please enter batch code.');
            } else if (value.length < 5) {
                $(this).addClass('is-invalid');
                $('#sb_code_error').text('Batch code must be at least 5 characters long.');
            } else if (!batchCodeRegex.test(value)) {
                $(this).addClass('is-invalid');
                $('#sb_code_error').text('Only letters, numbers, _, and - allowed.');
            } else if (!specificFormatRegex.test(value)) {
                $(this).addClass('is-invalid');
                $('#sb_code_error').text('Must start with letters and end with numbers.');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        $('#pst_id').on('change', function() {
            $(this).toggleClass('is-invalid', !$(this).val());
        });

        $('#start_date, #end_date').on('change', function() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            $('#start_date').toggleClass('is-invalid', !startDate);
            $('#end_date').toggleClass('is-invalid', !endDate);

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                if (end < start) {
                    $('#end_date').addClass('is-invalid');
                    $('#end_date_error').text('End date must be after start date.');
                } else {
                    $('#end_date').removeClass('is-invalid');
                }
            }
        });

        // Auto-clean batch code input
        $('#sb_code').on('input', function() {
            let value = $(this).val();
            value = value.replace(/[^a-zA-Z0-9_-]/g, '');
            $(this).val(value);
        });

        // Modal reset
        $('#createBatch').on('hidden.bs.modal', function() {
            $('#createBatchForm')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            $('#emp_id').val(null).trigger('change'); // Important for Select2
        });
    });
</script>
<script>
    function showEmps(d_id) {
        $.ajax({
            
        });
    }

    function openBatchModal() {
        $("#createBatch").modal("show");
    }
</script>
@endsection
