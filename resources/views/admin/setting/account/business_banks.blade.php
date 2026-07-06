@extends('admin.layout.master')
@section('title', 'Business Bank Details')

@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-8">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/business') }}">Business Settings</a></li>
                    <li class="active"><span><b>Bank Details</b></span></li>
                </ol>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-outline-primary" id="addBankBtn">Add Bank</button>
            </div>
        </div>
    </div>

    <br>

    <!-- Bank List -->
    <div class="row row-sm">
        @forelse($banks as $bank)
            <div class="col-xl-6 col-lg-6 col-md-12 mb-3">
                <div class="card custom-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">{{ $bank->bb_bank_name }}</h5><br>
                                <p class="mb-1"><b>Account No:</b> {{ $bank->bb_bank_acc_no }}</p>
                                <p class="mb-1"><b>IFSC Code:</b> {{ $bank->bb_ifsc_code }}</p>
                                <p class="mb-1"><b>Bank Name:</b> {{ $bank->bb_bank_name }}</p>
                                <p class="mb-1"><b>Branch Name:</b> {{ $bank->bb_branch_name ?? '-' }}</p>
                                <p class="mb-1"><b>MICR:</b> {{ $bank->bb_micr ?? '-' }}</p>
                                <p class="mb-1"><b>Branch Code:</b> {{ $bank->bb_branch_code ?? '-' }}</p>
                                <p class="mb-1"><b>Bank A/C Number:</b> {{ $bank->bb_bank_acc_no ?? '-' }}</p>
                                <p class="mb-0">
                                    <span class="badge {{ $bank->bb_bank_status == 1 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $bank->bb_bank_status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column gap-2">
                                <button class="btn btn-sm btn-outline-primary edit-bank" data-id="{{ $bank->bb_id }}"
                                    data-account_code="{{ $bank->bb_account_code }}" data-ifsc="{{ $bank->bb_ifsc_code }}"
                                    data-bank_name="{{ $bank->bb_bank_name }}"
                                    data-branch_name="{{ $bank->bb_branch_name }}" data-micr="{{ $bank->bb_micr }}"
                                    data-branch_code="{{ $bank->bb_branch_code }}"
                                    data-account_no="{{ $bank->bb_bank_acc_no }}"
                                    data-account_type="{{ $bank->bb_account_type }}"
                                    data-status="{{ $bank->bb_bank_status }}">
                                    <i class="feather feather-edit"></i>
                                </button>


                                <button class="btn btn-sm btn-outline-danger delete-bank" data-id="{{ $bank->bb_id }}">
                                    <i class="feather feather-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No Bank Records Found.</div>
            </div>
        @endforelse
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="bankModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="bankModalTitle">Add Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="bankForm">
                    @csrf
                    <input type="hidden" name="bank_id" id="bank_id">

                    <div class="modal-body">
                        <div class="row">

                            <!-- Account Code -->
                            <div class="col-lg-4 mb-3">
                                <label for="account_code" class="form-label">Account Code</label>
                                <input type="text" name="account_code" id="account_code" class="form-control"
                                    placeholder="Enter Account Code" required>
                                <div class="invalid-feedback">Enter valid account code.</div>
                            </div>

                            <!-- IFSC Code -->
                            <div class="col-lg-4 mb-3">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control ifsc_api_check"
                                    placeholder="Enter IFSC Code" id="ifsc_code" oninput="checkIFSC(this.value)" required
                                    maxlength="11">
                                <div class="invalid-feedback">Enter valid IFSC.</div>
                            </div>

                            <!-- Bank Name -->
                            <div class="col-lg-4 mb-3">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    placeholder="Enter Bank Name" required minlength="3">
                                <div class="invalid-feedback">Enter valid bank name.</div>
                            </div>

                            <!-- Branch Name -->
                            <div class="col-lg-4 mb-3">
                                <label for="branch_name" class="form-label">Branch Name</label>
                                <input type="text" name="branch_name" id="branch_name" class="form-control"
                                    placeholder="Enter Branch Name" required minlength="3">
                                <div class="invalid-feedback">Enter valid branch name.</div>
                            </div>

                            <!-- MICR -->
                            <div class="col-lg-4 mb-3">
                                <label for="micr" class="form-label">MICR</label>
                                <input type="text" name="micr" id="micr" class="form-control"
                                    placeholder="Enter MICR" required pattern="\d{9}">
                                <div class="invalid-feedback">MICR must be 9 digits.</div>
                            </div>

                            <!-- Branch Code -->
                            <div class="col-lg-4 mb-3">
                                <label for="branch_code" class="form-label">Branch Code</label>
                                <input type="text" name="branch_code" id="branch_code" class="form-control"
                                    placeholder="Enter Branch Code" required minlength="2">
                                <div class="invalid-feedback">Enter valid branch code.</div>
                            </div>

                            <!-- Bank A/c Number -->
                            <div class="col-lg-4 mb-3">
                                <label for="bank_acc_no" class="form-label">Bank A/c Number</label>
                                <input type="text" name="bank_acc_no" id="bank_acc_no" class="form-control"
                                    placeholder="Enter Account Number" required pattern="\d{6,18}">
                                <div class="invalid-feedback">Enter valid account number.</div>
                            </div>

                            <!-- Account Type -->
                            <div class="col-lg-4 mb-3">
                                <label for="account_type" class="form-label">Account Type</label>
                                <select name="account_type" id="account_type" class="form-control" required>
                                    <option value="" disabled selected>Select Account Type</option>
                                    <option value="Salary">Salary</option>
                                    <option value="Personal">Personal</option>
                                </select>
                                <div class="invalid-feedback">Please select an account type.</div>
                            </div>

                            <!-- Status -->
                            <div class="col-lg-4 mb-3 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="status" id="status" class="form-check-input"
                                        value="1" checked>
                                    <label for="status" class="form-check-label">Active</label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="saveBankBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>


@endsection

@section('script')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function checkIFSC(ifsc) {
            if (ifsc.length !== 11) {
                // IFSC must be 11 characters
                return;
            }

            // Convert to uppercase
            ifsc = ifsc.toUpperCase();

            fetch("{{ url('/bank-details/fetch-ifsc') }}?ifsc=" + ifsc)
                .then(response => response.json())
                .then(res => {
                    if (res.status) {
                        // Fill the form fields
                        document.getElementById('bank_name').value = res.data.bank_name || '';
                        document.getElementById('branch_name').value = res.data.branch || '';
                        document.getElementById('micr').value = res.data.micr || '';
                        document.getElementById('branch_code').value = res.data.bank_code || '';
                    } else {
                        // If invalid, clear fields and maybe show error
                        document.getElementById('bank_name').value = '';
                        document.getElementById('branch_name').value = '';
                        document.getElementById('micr').value = '';
                        document.getElementById('branch_code').value = '';
                        console.warn(res.message);
                    }
                })
                .catch(err => {
                    console.error('Error fetching IFSC details:', err);
                });
        }
    </script>


    <script>
        $(document).ready(function() {
            function resetValidation() {
                $('#bankForm .form-control').removeClass('is-invalid');
            }

            function fillEditForm(data) {
                $('#bank_id').val(data.id);

                $('#bb_account_code').val(data.account_code);
                $('#bb_ifsc_code').val(data.ifsc);

                $('#bb_bank_name').val(data.bank_name);
                $('#bb_branch_name').val(data.branch_name);
                $('#bb_micr').val(data.micr);
                $('#bb_branch_code').val(data.branch_code);

                $('#bb_bank_acc_no').val(data.account_no);
                $('#bb_account_type').val(data.account_type);

                $('#bb_bank_address').val(data.address);
                $('#bb_cheque_no').val(data.cheque);

                $('#bb_bank_status').prop('checked', data.status == 1);
            }

            $('#addBankBtn').click(function() {
                $('#bankForm')[0].reset();
                resetValidation();

                $('#bank_id').val("");
                $('#bankModalTitle').text("Add Bank");
                $('#saveBankBtn').text("Save");

                $('#bankModal').modal('show');
            });

            $(document).on('click', '.edit-bank', function() {

                resetValidation();

                // Fill form fields
                $('#bank_id').val($(this).data('id'));

                $('#account_code').val($(this).data('account_code'));
                $('#ifsc_code').val($(this).data('ifsc'));
                $('#bank_name').val($(this).data('bank_name'));
                $('#branch_name').val($(this).data('branch_name'));
                $('#micr').val($(this).data('micr'));
                $('#branch_code').val($(this).data('branch_code'));

                $('#bank_acc_no').val($(this).data('account_no'));
                $('#account_type').val($(this).data('account_type'));

                // If you have these fields hidden inside form
                $('#address').val($(this).data('address'));
                $('#cheque').val($(this).data('cheque'));

                // Status checkbox
                let status = $(this).data('status');
                $('#status').prop('checked', status == 1 ? true : false);

                // Change modal text
                $('#bankModalTitle').text("Edit Bank");
                $('#saveBankBtn').text("Update");

                // Open modal
                $('#bankModal').modal('show');
            });


            $('#bankForm').submit(function(e) {
                e.preventDefault();

                if (!this.checkValidity()) {
                    $(this).addClass('was-validated');
                    return;
                }

                $('#saveBankBtn').prop('disabled', true);

                $.ajax({
                    url: "{{ route('business.bank.save') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {

                        $('#saveBankBtn').prop('disabled', false);
                        $('#bankModal').modal('hide');

                        Swal.fire("Success", res.message, "success")
                            .then(() => location.reload());
                    },
                    error: function(xhr) {

                        $('#saveBankBtn').prop('disabled', false);

                        let err = xhr.responseJSON?.errors;
                        let msg = xhr.responseJSON?.message || "Something went wrong.";

                        if (err) {
                            Object.keys(err).forEach(function(field) {
                                let input = $(`[name="${field}"]`);
                                input.addClass("is-invalid");
                                input.next('.invalid-feedback').text(err[field][0]);
                            });
                        }

                        Swal.fire("Error", msg, "error");
                    }
                });
            });

            $(document).on('click', '.delete-bank', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: 'Delete?',
                    text: 'This bank will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {

                        let url = "{{ route('business.bank.delete', ':id') }}".replace(":id", id);

                        $.ajax({
                            url: url,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: "DELETE"
                            },
                            success: function(res) {
                                Swal.fire("Deleted!", res.success, "success")
                                    .then(() => location.reload());
                            },
                            error: function() {
                                Swal.fire("Error", "Failed to delete bank.", "error");
                            }
                        });

                    }
                });
            });

        });
    </script>
@endsection
