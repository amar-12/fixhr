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
    <div class="page-header d-xl-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Approval Settings</div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{$masterDataModule?->m_name}} Approval Flow</h4>
        </div>
        <form id="createApprovalFlowForm" method="POST" action="{{ route('form.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div id="dynamic-rows">
                    <input type="hidden" name="deleted_row_ids[]" id="deleted_row_ids">
                    <input type="hidden" name="moduleId" id="moduleId" value="{{$moduleId}}">
                    @if (count($existingRows) > 0)
                        @foreach ($existingRows as $index => $row)
                            <!-- Loop through existing rows -->
                            <div class="row dynamic-row">
                                <input id="id_{{ $index }}" name="rows[{{ $index }}][id]" type="hidden"
                                    value="{{ $row->eam_id }}">
                                <div class="form-group col-md">
                                    <label for="employee_{{ $index }}">Employee <span
                                            style="color:red;">*</span></label>
                                    <select id="employee_{{ $index }}"
                                        name="rows[{{ $index }}][employee]"
                                        class="form-control select2 approval-module-select2">
                                        <option value="{{ $row->eam_emp_id }}" selected>
                                            {{ $row?->fh_employee?->emp_code . ' - ' . $row?->fh_employee?->emp_full_name ?? 'Select Employee' }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md">
                                    <label for="approving_manager1_{{ $index }}">Approving Manager 1 <span
                                            style="color:red;">*</span></label>
                                    <select id="approving_manager1_{{ $index }}"
                                        name="rows[{{ $index }}][approving_manager1]" class="form-control select2">
                                        <option value="{{ $row->eam_approver_manager_1 }}" selected>
                                            {{ $row?->fh_employee_approver_manager_1?->emp_code . ' - ' . $row?->fh_employee_approver_manager_1?->emp_full_name ?? 'Select Approving Manager 1' }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md">
                                    <label for="approving_manager2_{{ $index }}">Approving Manager 2</label>
                                    <select id="approving_manager2_{{ $index }}"
                                        name="rows[{{ $index }}][approving_manager2]" class="form-control select2">
                                        <option value="{{ $row->eam_approver_manager_2 }}" selected>
                                            {{ $row?->fh_employee_approver_manager_2?->emp_code . ' - ' . $row->fh_employee_approver_manager_2?->emp_full_name ?? 'Select Approving Manager 2' }}
                                        </option>
                                    </select>
                                </div>
                                @if ($loop->first)
                                    <div class="form-group col-md-1 text-end align-self-end">
                                        <button type="button" class="btn btn-outline-primary btn-add-row">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="form-group col-1 text-end align-self-end">
                                        <button type="button" class="btn btn-outline-danger  btn-remove-row">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="row dynamic-row">
                            <input id="id_0" name="rows[0][id]" type="hidden" value="">
                            <div class="form-group col-md">
                                <label for="employee_0">Employee <span style="color:red;">*</span></label>
                                <select id="employee_0" name="rows[0][employee]"
                                    class="form-control select2 approval-module-select2">
                                </select>
                            </div>
                            <div class="form-group col-md">
                                <label for="approving_manager1_0">Approving Manager 1 <span
                                        style="color:red;">*</span></label>
                                <select id="approving_manager1_0" name="rows[0][approving_manager1]"
                                    class="form-control select2">
                                    <option value="" selected>
                                        Select Approving Manager 1
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md">
                                <label for="approving_manager2_0">Approving Manager 2</label>
                                <select id="approving_manager2_0" name="rows[0][approving_manager2]"
                                    class="form-control select2">
                                    <option value="" selected>Select Approving Manager 2
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md-1 text-end align-self-end">
                                <button type="button" class="btn btn-outline-primary btn-add-row">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" id="submitBtn" class="btn btn-outline-primary float-end mb-4">Submit</button>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let deletedRowIds = [];
            // let rowIndex = 1;
            let rowIndex = {{ count($existingRows) > 0 ? count($existingRows) : 1 }};
            // alert(rowIndex);
            // Helper function to initialize Select2 for dynamically added rows
            function setupSelect2(selector, url) {

                $(selector).select2({
                    placeholder: 'Search...',
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(function(item) {
                                    return {
                                        id: item.id,
                                        text: item.name
                                    };
                                })
                            };
                        },
                        cache: true
                    }
                });
            }
            setupSelect2('#approving_manager1_0', '/ajax/managers');
            setupSelect2('#approving_manager2_0', '/ajax/managers');
            setupSelect2('#employee_0', '/ajax/employees');

            // Add new row
            for (let i = 1; i < {{ count($existingRows) }}; i++) {
                // Update select2 for existing rows
                updateCaseSetupSelect2('employee_' + i, 'approving_manager1_' + i, 'approving_manager2_' + i, i);
            }

            function updateCaseSetupSelect2(approvalModule, approvingManagers1, approvingManagers2, index) {
                setupSelect2('#' + approvalModule, '/ajax/managers');
                setupSelect2('#' + approvingManagers1, '/ajax/managers');
                setupSelect2('#' + approvingManagers2, '/ajax/managers');
            }

            $(document).on('click', '.btn-add-row', function() {
                const previousRow = $('.dynamic-row').last();
                const previousApprovalModule = previousRow.find('.approval-module-select2').val();
                const previousApprovingManager1 = previousRow.find('select[name$="[approving_manager1]"]')
                    .val();
                const previousApprovingManager2 = previousRow.find('select[name$="[approving_manager2]"]')
                    .val();

                if (!previousApprovalModule) {
                    Swal.fire('Error', 'Employee is required.', 'error');
                    return;
                }
                if (!previousApprovingManager1) {
                    Swal.fire('Error', 'Approving Manager 1 is required.', 'error');
                    return;
                }
                if (previousApprovingManager1 === previousApprovingManager2) {
                    Swal.fire('Error', 'Approving Managers must be different.', 'error');
                    return;
                }

                // Add new row
                const newRow = `
                    <div class="row dynamic-row">
                        <input id="id_${rowIndex}" name="rows[${rowIndex}][id]" type="hidden" value="">
                        <div class="form-group col-md">
                            <label for="employee_${rowIndex}">Employee <span style="color:red;">*</span></label>
                            <select id="employee_${rowIndex}" name="rows[${rowIndex}][employee]" class="form-control select2 approval-module-select2">
                            </select>
                        </div>
                        <div class="form-group col-md">
                            <label for="approving_manager1_${rowIndex}">Approving Manager 1 <span style="color:red;">*</span></label>
                            <select id="approving_manager1_${rowIndex}" name="rows[${rowIndex}][approving_manager1]" class="form-control select2"></select>
                        </div>
                        <div class="form-group col-md">
                            <label for="approving_manager2_${rowIndex}">Approving Manager 2</label>
                            <select id="approving_manager2_${rowIndex}" name="rows[${rowIndex}][approving_manager2]" class="form-control select2"></select>
                        </div>
                        <div class="form-group col-1 text-end align-self-end">
                            <button type="button" class="btn btn-outline-danger  btn-remove-row">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;

                $('#dynamic-rows').append(newRow);
                setupSelect2(`#employee_${rowIndex}`, '/ajax/managers');
                setupSelect2(`#approving_manager1_${rowIndex}`, '/ajax/managers');
                setupSelect2(`#approving_manager2_${rowIndex}`, '/ajax/managers');

                rowIndex++;
            });


            // Remove a dynamic row
            $(document).on('click', '.btn-remove-row', function() {
                // Get the ID of the row to delete
                let rowId = $(this).closest('.dynamic-row').find('input[type="hidden"]').val();
                if (rowId) {
                    // Add the ID to the deletedRowIds array
                    deletedRowIds.push(rowId);
                }

                // Remove the row from the DOM
                $(this).closest('.dynamic-row').remove();
                $('#deleted_row_ids').val(deletedRowIds);
            });


            // Submit form via AJAX
            $('#createApprovalFlowForm').on('submit', function(e) {
                e.preventDefault();
                // Check if all required fields are filled
                let isValid = true; // Declare the isValid variable here
                let selectedApprovalModules = []; // Array to track selected employee values

                const data = $('.dynamic-row');
                data.each(function() {
                    // Get values from each dynamic row
                    const approvalModule = $(this).find('.approval-module-select2').val();
                    const approvingManager1 = $(this).find('select[name$="[approving_manager1]"]')
                        .val();
                    const approvingManager2 = $(this).find('select[name$="[approving_manager2]"]')
                        .val();

                    // Check if approvalModule or approvingManager1 are empty
                    if (!approvalModule || !approvingManager1) {
                        isValid = false;
                        Swal.fire('Error',
                            'Employee and Approving Manager 1 are required fields.',
                            'error');
                        return false; // Exit the loop and stop form submission
                    }

                    // Check if Approving Manager 1 and 2 are the same
                    if (approvingManager1 === approvingManager2) {
                        Swal.fire('Error', 'Approving Managers must be different.', 'error');
                        isValid = false;
                        return false; // Exit the loop and stop form submission
                    }

                    // Check for duplicate approvalModule values
                    if (selectedApprovalModules.includes(approvalModule)) {
                        Swal.fire('Error', 'Employee values must be unique.', 'error');
                        isValid = false;
                        return false; // Exit the loop and stop form submission
                    } else {
                        // If not duplicate, add to the selectedApprovalModules array
                        selectedApprovalModules.push(approvalModule);
                    }
                });

                if (!isValid) {
                    return; // Prevent the form submission if validation fails
                }
                const formData = $(this).serialize();
                // formData.append('deleted_row_ids', deletedRowIds); // Append the deleted row IDs
                // var baseUrl = $("#ajaxUrl").val();
                $.ajax({
                    url: $(this).attr('action'),
                    method: $(this).attr('method'),
                    data: formData,
                    beforeSend: function() {
                        $('#submitBtn').prop('disabled', true);
                    },
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success');
                        window.location.href = "{{route('travel.approval.list')}}";
                        // $('#dynamic-rows').html('');
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false);
                        Swal.fire('Error', 'Failed to submit the form.', 'error');
                    }
                });
            });
        });
    </script>
@endsection
