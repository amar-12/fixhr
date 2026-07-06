@extends('admin.layout.master')
@section('title', 'Recurring Payments/Deductions')

@section('content')
<div class="p-0 mt-3">
    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
        <li><a href="#">Payroll</a></li>
        <li class="active"><span><b>Recurring Payments/Deductions</b></span></li>
    </ol>
</div>

    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Recurring Payments/Deductions</div>
        </div>
        <div class="page-rightheader ms-md-auto">
            <button class="btn btn-outline-primary" id="addadhocBtn">Add Recurring Payment/Deduction</button>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header border-0">
                    <h4 class="card-title">Recurring Payment/Deduction</h4>
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


                            <div class="col-sm-2 d-flex align-items-start justify-content-end" style="margin-top: 28px;">
                                <!-- Export As Dropdown -->
                                <div class="form-group dropdown me-2">
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

                             <!-- Import Dropdown -->
                                <div class="btn-list">
                                    <div>
                                        <button class="btn btn-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu p-2" style="min-width: 220px;">
                                            <li>
                                             <a href="{{ route('adhoc.export.sample') }}"
                                                class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                            </li>
                                            {{-- <li>
                                                <button
                                                    class="dropdown-item text-secondary fw-semibold d-flex align-items-center gap-2 adhocDedImportBtn"
                                                    type="button">
                                                    <i class="las la-file-import"></i> Import Payments
                                                </button>
                                            </li> --}}
                                        </ul>
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


                        </div>

                    <div class="">
                        <table class="table display  table-hover table-vcenter text-wrap border-bottom"
                            id="payroll-policy-table-dynamic">
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
    <div class="modal fade" id="employeeSalaryImportModal" tabindex="-1"
        aria-labelledby="employeeSalaryImportModalTitle" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeSalaryImportModalTitle">Import Recurring Deductions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <form id="empMasterSubmit" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="financial_year" class="form-label">Financial Year</label>
                            <select id="financial_year" name="financial_year" class="form-select">
                                <option value="">Select financial year</option>
                                @foreach ($financialYears as $fyear)
                                    <option value="{{ $fyear->fy_id }}">{{ $fyear->fy_year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="salaryFile" class="form-label">Upload Adhoc Deduction File</label>
                            <input type="file" name="file" id="salaryFile" class="form-control" required>
                            <div class="form-text">Accepted formats: .xlsx</div>
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


    <div class="modal fade" id="adhocPaymentModal"  tabindex="-1" aria-labelledby="adhocTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="adhocSearchForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="adhocTransactionModalLabel">Add Recurring Payments/Deductions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="business_id" id="business_id" value="{{ $business_id }}">

                        <div class="row g-3">

                           @php
                                $financialYear = $financialYears->first();
                                 $currentMonth = \Carbon\Carbon::now()->subMonth()->format('Y-m');
                            @endphp

                            <!-- From Month -->
                            <div class="col-md-6">
                                <label class="form-label">From Month</label>
                                <input type="month" class="form-control" id="start_month" name="start_month"
                                    required
                                        value = "{{ $currentMonth }}"
                                    {{-- min="{{ \Carbon\Carbon::parse($financialYear->fy_start_date)->format('Y-m') }}" --}}
                                    min="{{ $currentMonth }}"

                                    max="{{ \Carbon\Carbon::parse($financialYear->fy_end_date)->format('Y-m') }}">
                            </div>

                            <!-- Till Month -->
                            <div class="col-md-6">
                                <label class="form-label">Till Month</label>
                                <input type="month" class="form-control" name="end_month" id="end_month"
                                    required
                                    value="{{ \Carbon\Carbon::parse($financialYear->fy_end_date)->format('Y-m') }}"
                                    min="{{ \Carbon\Carbon::parse($financialYear->fy_start_date)->format('Y-m') }}"
                                    max="{{ \Carbon\Carbon::parse($financialYear->fy_end_date)->format('Y-m') }}">
                            </div>



                            <!-- Apply To -->
                            <div class="col-md-4">
                                <label for="applyTo" class="form-label">Apply To</label>
                                <select class="form-select" id="applyTo" name="type" required>
                                    <option value="">-- Select --</option>
                                    <option value="employee">Employee</option>
                                    <option value="department">Department</option>
                                </select>
                            </div>

                            <!-- Employee Search -->
                            <div class="col-md-4 d-none" id="employeeSelect">
                                <label for="search" class="form-label">Search Employee</label>
                                <input type="text" id="search" class="form-control" placeholder="Search employee by name" autocomplete="off">
                                <div id="employeeDropdownContainer" class="mt-2"></div>
                            </div>

                            <!-- Department Select -->
                            <div class="col-md-4 d-none" id="departmentSelect">
                                <label for="department_id" class="form-label">Select Department</label>
                                <select class="form-select" name="department_id" id="department_id">
                                    <option value="">-- Select --</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->d_id }}">{{ $department->d_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payroll Period -->
                            {{-- <div class="col-md-4">
                                <label for="payroll_period_id" class="form-label">Payroll Period</label>
                                <select class="form-select" name="payroll_period_id" id="payroll_period_id" required>
                                    <option value="">-- Select --</option>
                                    @foreach($payrollPeriods as $period)
                                        @php
                                            $periodMonth = \Carbon\Carbon::parse($period->pp_start_date)->format('Y-m');
                                        @endphp
                                        <option value="{{ $period->pp_id }}" 
                                            {{ $periodMonth < $currentMonth ? 'disabled' : '' }}>
                                            {{ $period->pp_name }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div> --}}
                        </div>
                    </div>

                   <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-info">Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@section('script')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
           $(document).on('click', '.adhocDedImportBtn', function() {
            $('#employeeSalaryImportModal').modal('show');

            $('#empMasterSubmit').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: '{{ route('adhoc.import.sample') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: xhr.responseJSON?.message || 'Something went wrong'
                        });
                    }
                });
            });
        });
</script>

<script>
    $(document).ready(function() {
            // Setup CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Init DataTable (adjust if needed)
            datatable({
                tableId: "payroll-policy-table-dynamic",
                url: "{{ route('recurring.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });



        });

</script>
<script>
    $('#adhoc_heading_id').on('change', function () {
        let headingId = $(this).val();

        if (headingId) {
            $.ajax({
                url: '{{ route("get.adhoc.components") }}',
                type: 'GET',
                data: { heading_id: headingId },
                success: function (data) {
                    $('#adhoc_component_id').empty().append('<option value="">-- Select Component --</option>');
                    $.each(data, function (key, component) {
                        $('#adhoc_component_id').append(`<option value="${component.ac_id}">${component.ac_adhoc_component_name}</option>`);
                    });
                }
            });
        } else {
            $('#adhoc_component_id').empty().append('<option value="">-- Select Component --</option>');
        }
    });
</script>


<script>
    document.getElementById('applyTo').addEventListener('change', function () {
        const val = this.value;
        document.getElementById('employeeSelect').classList.toggle('d-none', val !== 'employee');
        document.getElementById('departmentSelect').classList.toggle('d-none', val !== 'department');
    });



    $('#addadhocBtn').on('click', function () {
        $('#adhocSearchForm')[0]?.reset?.();
        $('#employeeDropdownContainer').html('');
        $('#adhocPaymentModal').modal('show');
    });
</script>

<script>
    $('#adhocSearchForm').on('submit', function (e) { 
        e.preventDefault(); // Prevent default form submission
        
        const start_month = $('#start_month').val();
        const end_month = $('#end_month').val();  
        const applyTo = $('#applyTo').val();
        const employeeId = $('#employee_id').val(); // dynamically added
        const departmentId = $('#department_id').val();
        // const payrollPeriodId = $('#payroll_period_id').val(); // ⚠ If this exists, use it
        const businessId = $('#business_id').val();

        // Optional fields
        const year = $('[name="year"]').val() || '';
        const paymentProcess = $('[name="payment_process"]').val() || '';
        const payrollType = $('[name="payroll_type"]').val() || '';
        const month = $('[name="month"]').val() || '';

        // Validation
        if (!applyTo ||
            (applyTo === 'employee' && !employeeId) ||
            (applyTo === 'department' && !departmentId)) {
            alert("Please fill all required fields.");
            return;
        }

         // Month validation
        if (new Date(start_month) > new Date(end_month)) {
            Swal.fire("Invalid Date", "From Month cannot be after Till Month", "error");
            return;
        }

        // 🔹 Step 1: Check salary already processed or not
        $.ajax({
            url: "{{ route('adhoc.checkSalary') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                type: applyTo,
                employee_id: employeeId,
                department_id: departmentId,
                // payroll_period_id: payrollPeriodId,
                business_id: businessId
            },
            success: function (res) {
                if (!res.status) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: res.message
                    });
                    return;
                }

                // 🔹 Step 2: Build the redirect URL
                let url = "{{ route('recurring.form.view') }}";
                let params = new URLSearchParams();

                params.append("type", applyTo);
                // params.append("payroll_period_id", payrollPeriodId);
                if (businessId) params.append("business_id", businessId);
                if (applyTo === 'employee') {
                    params.append("employee_id", employeeId);
                } else if (applyTo === 'department') {
                    params.append("department_id", departmentId);
                }

                // Optional data append
                if (year) params.append("year", year);
                if (paymentProcess) params.append("payment_process", paymentProcess);
                if (payrollType) params.append("payroll_type", payrollType);
                if (month) params.append("month", month);
                if (start_month) params.append("start_month", start_month);
                if (end_month) params.append("end_month", end_month);



                // 🔹 Step 3: Redirect
                window.location.href = url + "?" + params.toString();
            }
        });
    });
</script>




<script>
  $(document).ready(function () {
    // Show/hide employee/department fields based on "Apply To"
    $('#applyTo').on('change', function () {
        let value = $(this).val();
        $('#employeeSelect').toggleClass('d-none', value !== 'employee');
        $('#departmentSelect').toggleClass('d-none', value !== 'department');
    });

    // Live employee search on input
    $('#search').on('input', function () {
        let query = $(this).val();
        if (query.length >= 1) {
            $.ajax({
                url: "{{ route('employee.search') }}",
                method: "POST",
                data: {
                    q: query,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    let container = $('#employeeDropdownContainer');
                    container.empty();

                    if (response.length > 0) {
                        response.forEach(emp => {
                            container.append(
                                `<div class="dropdown-item pointer border py-1 px-2 mb-1" data-id="${emp.emp_id}" data-name="${emp.emp_full_name}" data-code="${emp.emp_code}">
                                    ${emp.emp_full_name} (${emp.emp_code})
                                </div>`
                            );
                        });
                    } else {
                        container.html("<div class='text-muted'>No matches found</div>");
                    }
                }
            });
        } else {
            $('#employeeDropdownContainer').empty();
        }
    });

    // Set search field text on dropdown item click
    $(document).on('click', '#employeeDropdownContainer .dropdown-item', function () {
        let name = $(this).data('name');
        let code = $(this).data('code');
        let id = $(this).data('id');

        $('#search').val(`${name} (${code})`);
        $('#employeeDropdownContainer').empty();

        // Optional: Add a hidden input to store employee_id if needed
        if ($('#employee_id').length === 0) {
            $('<input>').attr({
                type: 'hidden',
                id: 'employee_id',
                name: 'employee_id',
                value: id
            }).appendTo('#adhocSearchForm');
        } else {
            $('#employee_id').val(id);
        }
    });
});

</script>




@endsection
