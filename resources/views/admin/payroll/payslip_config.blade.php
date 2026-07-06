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
                    <li><a href="#">Payroll Settings</a></li>
                    <li class="active"><span><b>Payslip Configuration</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payslipConfigModal">
                                    ⚙️ Configure Payslip
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
                    <h4 class="card-title">Payslip Configuration List</h4>
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
                    </div>

                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
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

<!-- Modal: Payslip Configuration -->
<div class="modal fade" id="payslipConfigModal" tabindex="-1" role="dialog" aria-labelledby="payslipConfigModal" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payslipConfigModalTitle">Payslip Configuration Settings</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
            </div>

            <form id="payslipConfigForm">
                @csrf
                <div class="modal-body">

                   <!-- Employee Info -->
                    <h6 class="fw-bold mb-2">Employee Info</h6>
                    <div class="row gy-1">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pc_show_employee_code" name="pc_show_employee_code"
                                    {{ $payslipConfiguration->pc_show_employee_code ? 'checked' : '' }}>
                                <label class="form-check-label" for="pc_show_employee_code">Show Employee Code</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pc_show_employee_name" name="pc_show_employee_name"
                                    {{ $payslipConfiguration->pc_show_employee_name ? 'checked' : '' }}>
                                <label class="form-check-label" for="pc_show_employee_name">Show Employee Name</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pc_show_department" name="pc_show_department"
                                    {{ $payslipConfiguration->pc_show_department ? 'checked' : '' }}>
                                <label class="form-check-label" for="pc_show_department">Show Department</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pc_show_designation" name="pc_show_designation"
                                    {{ $payslipConfiguration->pc_show_designation ? 'checked' : '' }}>
                                <label class="form-check-label" for="pc_show_designation">Show Designation</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pc_show_branch" name="pc_show_branch"
                                    {{ $payslipConfiguration->pc_show_branch ? 'checked' : '' }}>
                                <label class="form-check-label" for="pc_show_branch">Show Branch</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pc_show_bank_details" name="pc_show_bank_details"
                                    {{ $payslipConfiguration->pc_show_bank_details ? 'checked' : '' }}>
                                <label class="form-check-label" for="pc_show_bank_details">Show Bank Details</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pc_show_doj" name="pc_show_doj"
                                    {{ $payslipConfiguration->pc_show_doj ? 'checked' : '' }}>
                                <label class="form-check-label" for="pc_show_doj">Show Date of Joining</label>
                            </div>
                        </div>
                    </div>


                 <!-- Attendance Info -->
            <h6 class="fw-bold mt-3 mb-2">Attendance Info</h6>
            <div class="row gy-1">
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_month" name="pc_show_month"
                            {{ $payslipConfiguration->pc_show_month ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_month">Show Month</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_month_days" name="pc_show_month_days"
                            {{ $payslipConfiguration->pc_show_month_days ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_month_days">Show Month Days</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_salary_days" name="pc_show_salary_days"
                            {{ $payslipConfiguration->pc_show_salary_days ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_salary_days">Show Salary Days</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_present_days" name="pc_show_present_days"
                            {{ $payslipConfiguration->pc_show_present_days ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_present_days">Show Present Days</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_lwp_days" name="pc_show_lwp_days"
                            {{ $payslipConfiguration->pc_show_lwp_days ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_lwp_days">Show LWP Days</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_ip_uan" name="pc_show_ip_uan"
                            {{ $payslipConfiguration->pc_show_ip_uan ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_ip_uan">Show IP / UAN Numbers</label>
                    </div>
                </div>
            </div>

            <!-- Earnings & Deductions -->
            <h6 class="fw-bold mt-3 mb-2">Earnings & Deductions</h6>
            <div class="row gy-1">
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_earnings_breakdown" name="pc_show_earnings_breakdown"
                            {{ $payslipConfiguration->pc_show_earnings_breakdown ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_earnings_breakdown">Show Earnings Breakdown</label>
                    </div>
                    <div class="form-check">
                      <input type="checkbox" id="pc_show_employee_deductions_breakdown" name="pc_show_employee_deductions_breakdown"
                           {{ $payslipConfiguration->pc_show_employee_deductions_breakdown ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_employee_deductions_breakdown ">Show Employee Deductions</label>
                    </div>
                    <div class="form-check">
                       <input type="checkbox" id="pc_show_employer_deductions_breakdown" name="pc_show_employer_deductions_breakdown"
                           {{ $payslipConfiguration->pc_show_employer_deductions_breakdown ? 'checked' : '' }}>

                        <label class="form-check-label" for="pc_include_employer_deduction">Show Employer Deductions</label>
                    </div>
                </div>
            </div>

            <!-- Net Pay & Footer -->
            <h6 class="fw-bold mt-3 mb-2">Net Pay & Footer</h6>
            <div class="row gy-1">
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_include_ctc" name="pc_show_total_ctc"
                            {{ $payslipConfiguration->pc_show_total_ctc ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_include_ctc">Show Total CTC and Net Pay</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_round_off_net_salary" name="pc_round_off_net_salary"
                            {{ $payslipConfiguration->pc_round_off_net_salary ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_round_off_net_salary">Round-off Net Salary</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_net_salary_in_words" name="pc_show_net_salary_in_words"
                            {{ $payslipConfiguration->pc_show_net_salary_in_words ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_net_salary_in_words">Show Net Salary in Words</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_disclaimer" name="pc_show_disclaimer"
                            {{ $payslipConfiguration->pc_show_disclaimer ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_disclaimer">Show Disclaimer Note</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pc_show_generated_by" name="pc_show_generated_by"
                            {{ $payslipConfiguration->pc_show_signature ? 'checked' : '' }}>
                        <label class="form-check-label" for="pc_show_generated_by">Show Generated By & Print Date</label>
                    </div>
                </div>
            </div>

                </div>

                <input type="hidden" name="pc_id" value="{{ $payslipConfiguration->pc_id ?? '' }}">
                <div class="modal-footer">
                    <button type="submit" id="savePayslipSettings" class="btn btn-outline-success">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>




@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
        // Setup CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize DataTable with width settings
        datatable({
            tableId: "payroll-policy-table-dynamic",
            url: "{{ route('payslip-configuration.index') }}",
            dataLength: '[data-length]',
            dataSearch: '[data-search]',
            dataFilter: '[data-filter]',
            dataExport: '[data-export]',
            dataDateFilter: '[data-date-filter]',
            dataShowEntries: '[data-show-entries]',
            dataPagination: '[data-pagination]',
            dataStateSave: false,
            // Additional options to control width
            autoWidth: false, // disable automatic column width calculation
            responsive: true, // enable responsive layout
            // You can also customize column definitions if needed
            columnDefs: [
                { targets: '_all', width: 'auto' }
            ]
        });

        // Submit Payslip Config Form
        $('#payslipConfigForm').on('submit', function (e) {
            e.preventDefault();

            $('#savePayslipSettings').prop('disabled', true).text('Saving...');

            $.ajax({
                url: "{{ route('payslip-configuration.store') }}", // Laravel route helper
                method: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    $('#savePayslipSettings').prop('disabled', false).text('Save Settings');
                    $('#payslipConfigModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Payslip settings saved successfully!'
                    });
                },
                error: function (xhr) {
                    $('#savePayslipSettings').prop('disabled', false).text('Save Settings');

                    let errorMsg = 'Something went wrong. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            });
        });

         // Edit Payslip Configuration
        $(document).on('click', '.edit-adhoc-component', function () {
            const id = $(this).data('id');

            // Reset form and clear hidden ID
            $('#payslipConfigForm')[0].reset();
            $('#payslipConfigForm input[type="checkbox"]').prop('checked', false);
            $('#payslipConfigForm input[name="pc_id"]').remove();

            // ✅ Declare URL outside $.ajax
            let url = "{{ route('payslip-configuration.show', ':id') }}".replace(':id', id);

            $.ajax({
                url: url,
                method: 'GET',
                success: function (response) {
                    const config = response.data;

                    // Set checkbox values based on response
                    for (const key in config) {
                        if (config.hasOwnProperty(key)) {
                            const checkbox = $(`#payslipConfigForm input[name="${key}"]`);
                            if (checkbox.length && (config[key] == 1 || config[key] == true)) {
                                checkbox.prop('checked', true);
                            }
                        }
                    }

                    // Set hidden ID
                    $('#payslipConfigForm').append(`<input type="hidden" name="pc_id" value="${config.pc_id}">`);

                    // Update Modal Titles
                    $('#payslipConfigModalTitle').text('Update Payslip Configuration');
                    $('#savePayslipSettings').text('Update');

                    // Show the modal
                    $('#payslipConfigModal').modal('show');
                },
                error: function () {
                    Swal.fire('Error!', 'Could not load payslip configuration.', 'error');
                }
            });
        });


        // Delete Payslip Configuration
       $(document).on('click', '.delete-adhoc-component', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'You will not be able to recover this configuration!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = "{{ url('admin/settings/payroll/payslip-configuration') }}/" + id;

                $.ajax({
                    url: url,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire('Deleted!', response.message || 'Configuration deleted.', 'success');
                        $('#payroll-policy-table-dynamic').DataTable().ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire('Error!', 'Failed to delete configuration.', 'error');
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });


    });
    </script>




@endsection

@endsection
