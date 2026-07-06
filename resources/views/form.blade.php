@extends('admin.layout.master')
@section('title', 'Approval Flow')
@section('content')
    <div class="p-0 mb-4">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/settings/tada-settings/approval-list') }}">Privilege</a></li>
            <li class="active"><span><b>Approval Settings</b></span></li>
        </ol>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">

        <!-- Left Side -->
        <div class="page-header d-xl-flex d-block mb-0">
            <div class="page-leftheader">
                <div class="page-title">Approval Settings</div>
            </div>
        </div>

        <!-- Right Side Button -->
        <div>
            <button 
                type="button" 
                class="btn btn-outline-primary"
                data-bs-toggle="modal" 
                data-bs-target="#expiryModal">
                Add Expiry Day
            </button>
        </div>

    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ isset($masterDataModule) ? $masterDataModule?->m_name : '' }} Approval Flow</h4>
        </div>

       <div class="container mt-5">
            <div class="row">
                <!-- LEFT : FORM -->
                <div class="col-md-8">
                    <div class="text-center">
                        <form id="createApprovalFlowForm" action="{{ route('form.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="deleted_row_ids" id="deleted_row_ids">
                            <input type="hidden" name="moduleId" id="moduleId" value="{{ $moduleId }}">

                            <div id="dynamic-rows">
                                <!-- Employee Block -->
                                <div class="card mb-4 dynamic-row" data-index="0">
                                    <div class="card-body">
                                        <input type="hidden" id="id_0" name="rows[0][id]" value="">

                                        <div class="form-group mb-3 text-start">
                                            <label for="employee_0">Employee <span class="text-danger">*</span></label>
                                            <select id="employee_0" name="rows[0][employee]" class="form-control select2 approval-module-select2"></select>
                                        </div>

                                        <!-- Approver Section -->
                                        <div class="card border-0 bg-light">
                                            <div class="card-header fw-bold">Approvers</div>
                                            <div class="card-body">
                                                <!-- Always visible header -->
                                                <div class="row fw-bold text-muted mb-2 approver-label-row">
                                                    <div class="col-md-5">Approving Manager <span class="text-danger">*</span></div>
                                                    <div class="col-md-5">Status <span class="text-danger">*</span></div>
                                                    <div class="col-md-2"></div>
                                                </div>

                                                <!-- Dynamic Approvers -->
                                                <div id="approverContainer_0" class="approver-container"></div>

                                                <!-- Add Approver Button -->
                                                <button type="button" class="btn btn-outline-primary mt-3 addApprover" data-row="0">
                                                    + Add Approver
                                                </button>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="btn btn-outline-primary mb-4 btn-lg" id="submitBtn">Save</button>
                        </form>
                    </div>
                </div>

                <!-- RIGHT : UNASSIGNED EMPLOYEES -->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                     <div class="bg-primary text-white p-3 rounded shadow-sm">
                            <h5 class="mb-0">Unassigned Employees</h5>
                        </div>


                        <div class="card-body p-2" style="max-height: 450px; overflow-y: auto;">
                            @if($unAssignedEmployees->isEmpty())
                                <div class="text-center text-muted py-3">
                                    All employees are assigned 🎉
                                </div>
                            @else
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="25%">Emp Code</th>
                                            <th>Employee Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unAssignedEmployees as $i => $emp)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $emp->emp_code }}</td>
                                                <td>{{ $emp->emp_full_name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approver Template -->
        <template id="approverTemplate">
            <div class="approver-row row align-items-end mb-3">
                <div class="col-md-5">
                    <select class="form-control select2-manager approver-manager"></select>
                </div>
                <div class="col-md-5">
                    <select class="form-select status-select select2 approver-status">
                        <option value="">--- Select Status ---</option>
                        @foreach($status as $stats)
                            <option value="{{ $stats->m_id }}">{{ $stats->m_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger   remove-approver">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>
    <!-- <div class="modal fade" id="expiryModal" tabindex="-1" aria-labelledby="expiryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('approval.expiry.save') }}" method="POST">
                @csrf
                    <div class="modal-body">
                        <input type="hidden" name="moduleId" value="{{ $moduleId }}">
                        <div class="form-group mb-3 text-start">
                            <label for="exp_rej_day" class="form-label">
                                Auto reject {{ isset($masterDataModule) ? $masterDataModule?->m_name : '' }} within days
                            </label>
                            <input 
                                type="text" 
                                class="form-control numericInput" 
                                id="exp_rej_day" 
                                name="exp_rej_day"
                                value="{{ $appExpRej->aer_day ?? '' }}"
                                required
                                placeholder="7">
                        </div>
                        <div class="form-group mb-3 text-start">
                            <label for="noti_day" class="form-label">
                                Notification before days
                            </label>
                            <input 
                                type="text" 
                                class="form-control numericInput" 
                                id="noti_day" 
                                name="noti_day"
                                value="{{ $appExpRej->aer_noti_day ?? '' }}"
                                required
                                placeholder="5">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-outline-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div> -->

    <div class="modal fade" id="expiryModal" tabindex="-1" aria-labelledby="expiryModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
          <form action="{{ route('approval.expiry.save') }}" method="POST">
            @csrf
            <input type="hidden" name="moduleId" value="{{ $moduleId }}">
            <div class="modal-header border-bottom px-4 py-3 align-items-start">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h6 class="mb-0 fw-semibold" id="expiryModalLabel">Expiry Settings</h6>
                        <small class="text-muted">Configure auto-reject &amp; notification rules</small>
                    </div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4 d-flex flex-column gap-3">
                <div class="text-start">
                    <label for="exp_rej_day" class="form-label fw-medium mb-1">
                        <i class="ti ti-calendar-x text-primary me-1"></i>
                        Auto reject <strong>{{ $masterDataModule?->m_name ?? '' }}</strong> within
                    </label>
                    <div class="input-group">
                        <input type="text"
                            class="form-control numericInput"
                            id="exp_rej_day"
                            name="exp_rej_day"
                            value="{{ $appExpRej->aer_day ?? '' }}"
                            placeholder="7"
                            required>
                        <span class="input-group-text text-muted">Days</span>
                    </div>
                    <div class="form-text">Request pending beyond this duration will be auto-rejected.</div>
                </div>
                <div class="text-start">
                    <label for="noti_day" class="form-label fw-medium mb-1">
                        <i class="ti ti-bell-ringing text-primary me-1"></i>
                        Send notification before
                    </label>
                    <div class="input-group">
                        <input type="text"
                            class="form-control numericInput"
                            id="noti_day"
                            name="noti_day"
                            value="{{ $appExpRej->aer_noti_day ?? '' }}"
                            placeholder="5"
                            required>
                        <span class="input-group-text text-muted">Days</span>
                    </div>
                    <div class="form-text">Approver will be reminded this days before auto-rejection.</div>
                </div>
                <div class="alert alert-primary d-flex gap-2 py-2 px-3 mb-0 rounded-3" role="alert">
                    <i class="ti ti-info-circle flex-shrink-0 mt-1"></i>
                    <small>Notification will be sent before the auto-reject deadline to remind the approver.</small>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3 gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Close
                </button>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="ti ti-device-floppy me-1"></i> Save
                </button>
            </div>
          </form>
        </div>
      </div>
    </div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: @json(session('success')),
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
        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: @json(session('error')),
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
$(document).ready(function () {
    let deletedRowIds = [];
    let rowIndex = $('.dynamic-row').length;

    function allowDecimalInput(event) {
        let input = event.target;
        input.value = input.value.replace(/[^0-9.]/g, '');
        if ((input.value.match(/\./g) || []).length > 1) {
            input.value = input.value.replace(/\.+$/, '');
        }
    }

    document.querySelectorAll(".numericInput").forEach(input => {
        input.addEventListener("input", allowDecimalInput);
    });

    const expRejInput = document.getElementById('exp_rej_day');
    const notiDayInput = document.getElementById('noti_day');

    // exp_rej_day validation
    expRejInput.addEventListener('input', function () {
        // allow only numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value !== '') {
            let num = parseInt(this.value);
            // max limit
            if (num > 180) {
                this.value = 180;
            }
        }
        validateNotificationDays();
    });

    // noti_day validation
    notiDayInput.addEventListener('input', function () {
        // allow only numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        validateNotificationDays();
    });

    // notification day should be smaller than exp reject day
    function validateNotificationDays() {
        let expDays = parseInt(expRejInput.value) || 0;
        let notiDays = parseInt(notiDayInput.value) || 0;
        if (notiDays >= expDays && expDays > 0) {
            notiDayInput.value = expDays - 1;
        }
    }

    // min validation on blur
    expRejInput.addEventListener('blur', function () {
        let value = this.value.trim();
        if (value === '') return;
        let num = parseInt(value);
        // minimum 1
        if (num < 1) {
            this.value = 1;
        }
        validateNotificationDays();
    });

    notiDayInput.addEventListener('blur', function () {
        let value = this.value.trim();
        if (value === '') return;
        let num = parseInt(value);
        // minimum 1
        if (num < 1) {
            this.value = 1;
        }
        validateNotificationDays();
    });

    const setupSelect2 = (selector, url, placeholder = 'Search...') => {
        $(selector).select2({
            placeholder: placeholder,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: params => ({ search: params.term }),
                processResults: data => ({
                    results: data.map(item => ({ id: item.id, text: item.name }))
                }),
                cache: true
            }
        });
    };

    const setupManagerSelect2 = () => {
        $('.select2-manager').each(function () {
            if (!$(this).hasClass("select2-hidden-accessible")) {
                setupSelect2(this, '/ajax/managers', 'Search manager...');
            }
        });
    };

    // Initialize the first employee select
    setupSelect2(`#employee_0`, '/ajax/employees');
    setupManagerSelect2();

    // Add New Employee Row
    $('.btn-add-row').on('click', function () {
        const lastRow = $('.dynamic-row').last();
        const lastIndex = parseInt(lastRow.data('index'));

        const empVal = lastRow.find('.approval-module-select2').val();
        if (!empVal) {
            Swal.fire('Error', 'Please select an employee before adding a new row.', 'error');
            return;
        }

        const newIndex = lastIndex + 1;
        const newRow = lastRow.clone();
        newRow.attr('data-index', newIndex);

        // Clear inputs and update names/ids
        newRow.find('input, select').each(function () {
            const nameAttr = $(this).attr('name');
            const idAttr = $(this).attr('id');

            if (nameAttr) {
                const updatedName = nameAttr.replace(/\d+/, newIndex);
                $(this).attr('name', updatedName).val('');
            }

            if (idAttr) {
                const updatedId = idAttr.replace(/\d+/, newIndex);
                $(this).attr('id', updatedId);
            }

            if ($(this).is('select')) {
                $(this).empty(); // Reset select2
            }
        });

        // Update the approver container ID and clear it
        const approverContainer = newRow.find('.approver-container');
        approverContainer.empty().attr('id', `approverContainer_${newIndex}`);

        // Update addApprover button
        newRow.find('.addApprover').attr('data-row', newIndex);

        $('#dynamic-rows').append(newRow);

        setupSelect2(`#employee_${newIndex}`, '/ajax/employees');
    });

    // Remove employee row
    $(document).on('click', '#submitBtn', function () {
        const row = $(this).closest('.dynamic-row');
        const rowId = row.find('input[type="hidden"]').val();
        if (rowId) deletedRowIds.push(rowId);
        row.remove();
        $('#deleted_row_ids').val(deletedRowIds.join(','));
    });

    // Add Approver to Specific Row
    $(document).on('click', '.addApprover', function () {
        const row = $(this).data('row');
        const container = $(`#approverContainer_${row}`);
        const index = container.children().length;

        const template = $('#approverTemplate').html();
        const $template = $(template);

        $template.find('.approver-manager').attr('name', `rows[${row}][approvers][${index}][manager]`);
        $template.find('.approver-status').attr('name', `rows[${row}][approvers][${index}][status]`);

        container.append($template);
        setupManagerSelect2();
    });

    // Remove Approver
    $(document).on('click', '.remove-approver', function () {
        $(this).closest('.approver-row').remove();
    });

    // Form Submission with Validation
    $('#createApprovalFlowForm').on('submit', function (e) {
        e.preventDefault();

        let isValid = true;
        let selectedEmployees = [];

        $('.dynamic-row').each(function () {
            const rowIndex = $(this).data('index');
            const empVal = $(`#employee_${rowIndex}`).val();

            if (!empVal) {
                Swal.fire('Validation Error', 'Please select an employee.', 'error');
                isValid = false;
                return false;
            }

            if (selectedEmployees.includes(empVal)) {
                Swal.fire('Validation Error', 'Duplicate employees are not allowed.', 'error');
                isValid = false;
                return false;
            }

            selectedEmployees.push(empVal);

            // Validate Approvers
            const approverContainer = $(`#approverContainer_${rowIndex}`);
            const approverRows = approverContainer.find('.approver-row');

            if (approverRows.length === 0) {
                Swal.fire('Validation Error', 'Each employee must have at least one approver.', 'error');
                isValid = false;
                return false;
            }

            const managerStatusPairs = new Set();

            approverRows.each(function () {
                const manager = $(this).find('.approver-manager').val();
                const status = $(this).find('.approver-status').val();

                if (!manager || !status) {
                    Swal.fire('Validation Error', 'Each approver must have a manager and a status selected.', 'error');
                    isValid = false;
                    return false;
                }

                const pair = `${manager}-${status}`;
                if (managerStatusPairs.has(pair)) {
                    Swal.fire('Validation Error', 'Duplicate manager-status combinations are not allowed.', 'error');
                    isValid = false;
                    return false;
                }

                managerStatusPairs.add(pair);
            });
        });

        if (!isValid) return;

        // Submit form via AJAX
        $('#submitBtn').prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message || 'Form submitted successfully!',
                    icon: 'success',
                });
            },
            error: function(xhr) {
                // Display SweetAlert error if the message is the specific one
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON.error,
                        icon: 'error',
                    });
                    return false;
                }
            }
        });

        // $.ajax({
        //     url: $(this).attr('action'),
        //     method: $(this).attr('method'),
        //     data: $(this).serialize(),
        //     success: function (response) {
        //         Swal.fire('Success', response.message, 'success').then(() => {
        //             window.location.href = "{{ route('travel.approval.list') }}";
        //         });
        //     },
        //     error: function () {
        //         $('#submitBtn').prop('disabled', false);
        //         Swal.fire('Error', 'Something went wrong while submitting the form.', 'error');
        //     }
        // });
    });
});

$(document).on('change', '.approval-module-select2', function () {
    const empId = $(this).val();
    const moduleId = $('#moduleId').val();
    console.log('moduleId-> '+ moduleId);
    if (!empId) return;

    $.ajax({
       // url: `/ajax/employee-approval/${empId}`,
        url: '{{ route("ajax.employee.approval", [":empId", ":moduleId"]) }}'
      .replace(":empId", empId)
      .replace(":moduleId", moduleId),
        method: 'GET',
        success: function (res) {
            const $container = $('#approverContainer_0');
            $container.empty(); // Clear previous rows

            console.log(res.approvers);
            if (res.approvers && res.approvers.length > 0) {
                res.approvers.forEach((approver, index) => {
                    const template = $('#approverTemplate').html();
                    const $template = $(template);

                    // Set name attributes
                    $template.find('.select2-manager').attr('name', `rows[0][approvers][${index}][manager]`);
                    $template.find('.status-select').attr('name', `rows[0][approvers][${index}][status]`);

                    // Append to DOM first
                    $container.append($template);

                    const $managerSelect = $template.find('.select2-manager');

                    // Pre-select manager
                    if (approver.manager_id && approver.manager_name) {
                        // Pre-select the manager
                        $managerSelect.append(
                            new Option(approver.manager_name, approver.manager_id, true, true)
                        );
                    }

                    // Reinitialize Select2 for the manager field (enabling search)
                    $managerSelect.select2({
                        ajax: {
                            url: '/ajax/employees',
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    search: params.term
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map(emp => ({
                                        id: emp.id,
                                        text: emp.name
                                    }))
                                };
                            },
                            cache: true
                        },
                        placeholder: 'Select Manager',
                        minimumInputLength: 1,
                        width: '100%'
                    });

                    // Set status value
                    $template.find('.status-select').val(approver.status_id);
                });
            }
        }
        // error: function (err) {
        //     console.warn(err);
        //     Swal.fire('Notice', 'No existing approval flow found for selected employee.', 'info');
        //     $('#approverContainer_0').empty();
        // }
    });
});

</script>
@endsection
