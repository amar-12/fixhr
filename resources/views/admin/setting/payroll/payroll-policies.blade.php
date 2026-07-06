@extends('admin.layout.master')
@section('title', 'Payroll Policies')
@section('content')
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/payroll') }}">Payroll Structure Policy</a></li>
                    <li class="active"><span><b>Payroll Structure</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addPayrollPolicyBtn">Add Payroll Structure</button>
                            </div>
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
                    <h4 class="card-title">Structure List</h4>
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

                        <div class="col-md-1 col-sm-4 pt-5 mt-1" align="right">
                            <div class="btn-group">
                                <button class="btn btn-outline-danger dropdown-toggle" type="button" id="defaultDropdown"
                                    data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                    Export As
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
                        <div class="col-md-8 col-sm-4"></div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <div class="form-group mb-3">
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table display table-vcenter text-wrap border-bottom"
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

    <!-- MODAL -->
    <div class="modal fade" id="payrollPolicyModal" tabindex="-1" role="dialog" aria-labelledby="payrollPolicyModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payrollPolicyModalTitle">Add Payroll Structure</h5>
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
                                placeholder="Description" astric="*" astric="true"  />
                        </div>
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_basic_salary_percentage" label="Basic Salary (%) of CTC"
                                    placeholder="Basic Salary (%) of CTC" name="ps_basic_salary_percentage" astric="*"
                                    required min="0" />
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_hra_allowance_percentage" label="HRA (%) of Basic"
                                    placeholder="HRA (%) of Basic" name="ps_hra_allowance_percentage" astric="*"
                                    required min="0" />
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_conveyance_allowance_threshhold" label="Conveyance Treshhold"
                                    placeholder="Conveyance Treshhold" name="ps_conveyance_allowance_threshhold" astric="*"
                                    required min="0" />
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_medical_allowance_threshhold" label="Medical Treshhold"
                                    placeholder="Medical Treshhold" name="ps_medical_allowance_threshhold" astric="*" required min="0" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_employee_pf_percentage" label="Employee PF (%)"
                                    placeholder="Employee PF (%)" name="ps_employee_pf_percentage" astric="*"
                                    required min="0" />
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_employer_pf_percentage" label="Employer PF (%)"
                                    placeholder="Employer PF (%)" name="ps_employer_pf_percentage" astric="*"
                                    required min="0" />
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_employee_esic_percentage" label="Employee ESIC (%)"
                                    placeholder="Employee ESIC (%)" name="ps_employee_esic_percentage" astric="*"
                                    required min="0" />
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_employer_esic_percentage" label="Employer ESIC (%)"
                                    placeholder="Employer ESIC (%)" name="ps_employer_esic_percentage" astric="*" required min="0" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_pf_threshhold" label="PF Treshhold"
                                    placeholder="PF Treshhold" name="ps_pf_threshhold" astric="*"
                                    required min="0" />
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-input id="ps_esic_threshhold" label="ESIC Treshhold"
                                    placeholder="ESIC Treshhold" name="ps_esic_threshhold" astric="*"
                                    required min="0" />
                            </div>

                        </div>



                    </div>
                    <!-- Note below inputs -->
                    {{-- <div class="form-text text-muted mt-3 ps-3">
                        Note: All percentages are based on the CTC.
                    </div> --}}

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
@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    $(document).ready(function() {
        datatable({
            tableId: "payroll-policy-table-dynamic",
            url: "{{ route('payroll-policies.index') }}",
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
        $(document).ready(function() {
            // CSRF Token Setup
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
                        $('#payrollPolicyModal').modal('hide');
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

            $(document).on('click', '#addPayrollPolicyBtn', function() {
                $('#saveBtn').attr('disabled', false);
                $('#payrollPolicyModalTitle').html('Add  Payroll Structure');
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
                $('#payrollPolicyModal').modal('show');
            });

            // Edit Policy - Set form values
            $(document).on('click', '.edit-policy', function() {
                $('#saveBtn').attr('disabled', false);
                var id = $(this).data('id');
                // var b_id = $(this).data('b_id');
                var name = $(this).data('name');
                var description = $(this).data('description');
                var ps_basic_salary_percentage = $(this).data('ps_basic_salary_percentage');
                var ps_hra_allowance_percentage = $(this).data('ps_hra_allowance_percentage');
                var ps_conveyance_allowance_threshhold = $(this).data('ps_conveyance_allowance_threshhold');
                var ps_medical_allowance_threshhold = $(this).data('ps_medical_allowance_threshhold');

                var ps_employee_pf_percentage = $(this).data('ps_employee_pf_percentage');
                var ps_employer_pf_percentage = $(this).data('ps_employer_pf_percentage');
                var ps_employee_esic_percentage = $(this).data('ps_employee_esic_percentage');
                var ps_employer_esic_percentage = $(this).data('ps_employer_esic_percentage');
                var ps_pf_threshhold = $(this).data('ps_pf_threshhold');
                var ps_esic_threshhold = $(this).data('ps_esic_threshhold');

                // Set the form values
                $('#payrollPolicyModalTitle').html('Update Payroll Structure');
                $('#saveBtn').html('Update');
                $('#ps_id').val(id);
                // $('#ap_b_id').val(b_id);
                $('#ps_name').val(name);
                $('#ps_description').val(description);
                $('#ps_basic_salary_percentage').val(ps_basic_salary_percentage);
                $('#ps_hra_allowance_percentage').val(ps_hra_allowance_percentage);
                $('#ps_conveyance_allowance_threshhold').val(ps_conveyance_allowance_threshhold);
                $('#ps_medical_allowance_threshhold').val(ps_medical_allowance_threshhold);

                $('#ps_employee_pf_percentage').val(ps_employee_pf_percentage);
                $('#ps_employer_pf_percentage').val(ps_employer_pf_percentage);
                $('#ps_employee_esic_percentage').val(ps_employee_esic_percentage);
                $('#ps_employer_esic_percentage').val(ps_employer_esic_percentage);
                $('#ps_pf_threshhold').val(ps_pf_threshhold);
                $('#ps_esic_threshhold').val(ps_esic_threshhold);


                $('#payrollPolicyModal').modal('show');
            });
        });


    </script>
@endsection
