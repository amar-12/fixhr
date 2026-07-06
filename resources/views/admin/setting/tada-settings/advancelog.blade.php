@extends('admin.layout.master')

@section('title', 'Travel Type Settings')

@section('content')
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/admin') }}">Dashboard</a></li>
            <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
            <li class="active"><span><b>Advance Approval</b></span></li>
        </ol>
    </div>

    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Advance List</div>
            <p class="text-muted">Approve requested advance</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Advance List</h3>
        </div>
        <div class="card-body p-2">
            @error('travel_type_id')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="table-responsive">
                <table class="table table-vcenter text-nowrap border-bottom" id="responsive-datatable">
                    <thead>
                        <tr>
                            <th class="border-bottom-0 w-10">S.No.</th>
                            <th class="border-bottom-0 text-center">Plan Unique ID</th>
                            <th class="border-bottom-0 text-center">Plan Name</th>
                            <th class="border-bottom-0 text-center">Requested Amount</th>
                            <th class="border-bottom-0 text-center">Received Amount</th>
                            <th class="border-bottom-0 text-center">Remark</th>
                            <th class="border-bottom-0 text-center">Updated At</th>
                            <th class="border-bottom-0 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($advance_log as $key => $log)
                            <tr>
                                <td class="font-weight-semibold">{{ ++$key }}</td>
                                <td class="font-weight-semibold text-center">
                                    {{ $log->fh_tada_request_plan->trp_unique_id ?? '' }}
                                </td>

                                <td class="font-weight-semibold text-center">
                                    {{ $log->fh_tada_request_plan->trp_name ?? '' }}
                                </td>
                                <td class="font-weight-semibold text-center">
                                    {{ $log->adl_requested_amount ?? '' }}
                                </td>
                                <td class="font-weight-semibold text-center">
                                    {{ $log->adl_reimburse_amount ?? '' }}
                                </td>
                                <td class="font-weight-semibold text-center">
                                    {{ $log->adl_remark ?? '' }}
                                </td>
                                <td class="font-weight-semibold text-center">
                                    <span class="fs-11 fw-bold">W.E.F.</span>
                                    <span
                                        class="with-effect-from-badge fs-10">{{ date('d-M-Y h:i A', strtotime($log->updated_at)) }}</span>
                                </td>
                                <td class="d-flex justify-content-center">
                                    @php
                                        // Check if the logged-in user has the required travel approval access
                                        $hasApprovalAccess = $approvalData->isNotEmpty();
                                    @endphp

                                    {{-- Check if the user has approval access --}}
                                    @if ($hasApprovalAccess)
                                        <button class="btn action-btns btn-sm btn-primary editBtn"
                                            data-id="{{ $log->adl_id }}" data-plan_id="{{ $log->adl_trp_id }}"
                                            data-requested_amount="{{ $log->adl_requested_amount }}"
                                            data-received_amount="{{ $log->adl_reimburse_amount }}"
                                            data-remark="{{ $log->adl_remark }}">
                                            <i class='feather feather-edit'></i>
                                        </button>
                                    @else
                                        {{-- If no approval access, check if the record exists in $AdvancUpdate --}}
                                        @if ($AdvancUpdate->contains('adl_id', $log->adl_id))
                                            <button class="btn action-btns btn-sm btn-primary editBtn"
                                                data-id="{{ $log->adl_id }}" data-plan_id="{{ $log->adl_trp_id }}"
                                                data-requested_amount="{{ $log->adl_requested_amount }}"
                                                data-received_amount="{{ $log->adl_reimburse_amount }}"
                                                data-remark="{{ $log->adl_remark }}">
                                                <i class='feather feather-edit'></i>
                                            </button>
                                        @endif
                                    @endif

                                    <button class="btn action-btns btn-sm btn-danger">
                                        <i class="feather feather-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    {{-- Grade Creation Modal --}}
    <div class="modal fade" id="createTravelTypeModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title" id="modalTitle">Create Advance Lodging</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="addTUpdateTavelTypeForm" action="{{ route('advancelog.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div>
                            <div class="d-flex align-items-center justify-content-between">
                                <label for="plan_id" class="form-label">Travel Unique ID <span
                                        class="text-red">*</span></label>
                            </div>

                            <select name="plan_id" id="plan_id" class="form-control form-select select2"
                                data-placeholder="Travel Type" required>
                                <option label="Travel Type"></option>
                                @foreach ($tadaPlanList as $key => $tadaPlanList)
                                    <option value="{{ $tadaPlanList->trp_id }}" data-m_id="{{ $tadaPlanList->trp_id }}">
                                        {{ $tadaPlanList->trp_unique_id }}
                                    </option>
                                @endforeach
                            </select>

                            <label for="requested_amount" class="form-label mb-1 mt-3">Requested Amount<span
                                    class="text-red">*</span></label>
                            <input type="number" name="requested_amount" id="requested_amount" class="form-control"
                                placeholder="Requested Amount">
                        </div>
                        @php
                            // Check if the logged-in user has the required travel approval access
                            $hasApprovalAccess = $approvalData->isNotEmpty();
                        @endphp

                        @if ($hasApprovalAccess)
                            <label for="received_amount" class="form-label mb-1 mt-3">Received Amount<span
                                    class="text-red">*</span></label>
                            <input type="number" name="received_amount" id="received_amount" class="form-control"
                                placeholder="Received Amount">
                        @endif

                        <label for="remark" class="form-label mb-1 mt-3">Remark<span class="text-red">*</span></label>
                        <textarea name="remark" id="remark" rows="5" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary saveUptBtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Delete Grade Modal --}}
    <div class="modal fade" id="deleteTravelTypeModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <h4 class="mt-5">Are you sure want to delete<span class="text-primary" id="delete_name_id"></span>
                        Travel Type ?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    <form method="POST" action="{{ route('delete.travel.type') }}"> @csrf
                        <input type="hidden" name="travelTypeId" id="deleteTravelTypeId">
                        <button type="submit" class="btn btn-outline-danger ">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <style>
        .swal2-container-green-glow {
            box-shadow: 0 0 10px rgba(0, 128, 0, 0.5);
            /* green glow */
            border: 1px solid #008000;
            /* green border */
        }

        .swal2-toast {
            box-shadow: 0 0 10px rgba(0, 128, 0, 0.5);
            /* green glow */
            border: 1px solid #008000;
            /* green border */
        }

        .swal2-toast-green-glow {
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.8), 0 0 25px rgba(0, 255, 0, 0.5);
            /* Neon green glow */
            border-radius: 5px;
            border: 2px solid #00ff00;
            /* Bright neon green border */
            background-color: #004d00;
            /* Dark green background for contrast */
        }
    </style>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        // Reset the form when clicking the 'Create Travel Plan' button
        document.getElementById('createTravelTypeBtn').addEventListener('click', function() {
            document.getElementById('addTUpdateTavelTypeForm').reset();
            $('#plan_id').val(null).trigger('change'); // Clear the travel type selection
            document.getElementById('modalTitle').textContent = 'Create Advance Lodging';
            $('#addTUpdateTavelTypeForm').attr('action',
                '{{ route('advancelog.store') }}'); // Reset to store route

            // Show the modal
            $('#createTravelTypeModal').modal('show');
        });




        // Edit button logic to populate form fields with existing data
        $(document).on('click', '.editBtn', function() {
            // Show the modal
            $('#createTravelTypeModal').modal('show');

            var id = $(this).data('id');
            var travelTypeId = $(this).data('plan_id');
            var requestedAmount = $(this).data('requested_amount');
            var receivedAmount = $(this).data('received_amount') || '';
            var remark = $(this).data('remark');

            // Set the selected plan_id in the select2 dropdown
            $('#plan_id').val(travelTypeId).trigger('change');

            // Set the requested and received amounts
            $('#requested_amount').val(requestedAmount);
            $('#received_amount').val(receivedAmount);
            $('#remark').val(remark);

            $('#addTUpdateTavelTypeForm').attr('action', '/update_advance_log/' +
                id);

            document.getElementById('modalTitle').textContent = 'Update Advance Lodging';

            // Initialize select2
            $('.select2').select2();
        });



        // Form submission using AJAX
        $('#addTUpdateTavelTypeForm').on('submit', function(event) {
            event.preventDefault();

            let formDataArray = $(this).serializeArray();
            let formDataObject = {};

            // Convert form data to an object
            $.each(formDataArray, function(index, field) {
                if (field.value) {
                    formDataObject[field.name] = field.value;
                }
            });

            // Prepare FormData for file upload (if applicable)
            let ajaxData = new FormData();
            $.each(formDataObject, function(key, value) {
                ajaxData.append(key, value);
            });

            // Handle file uploads
            let files = $('#addTUpdateTavelTypeForm').find('input[type="file"]')[0]?.files;
            if (files) {
                $.each(files, function(i, file) {
                    ajaxData.append('trp_document', file);
                });
            }

            // Determine the form's action URL (store or update)
            let formAction = $('#addTUpdateTavelTypeForm').attr('action');

            $.ajax({
                url: formAction, // Use the dynamic action URL
                method: 'post',
                data: ajaxData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(data) {
                    Swal.fire({
                        position: 'top-end',
                        icon: data.status ? 'success' : 'warning',
                        title: data.status ? 'Advance lodging created successfully' : data
                            .message,
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            toast: 'swal2-toast-green-glow'
                        }
                    });

                    if (data.status) {
                        window.location.href = '{{ route('admin.setting.tada-settings.advancelog') }}';
                        $('#createTravelTypeModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error occurred',
                        text: error,
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        });

        // Initialize Select2 when the modal is fully shown
        // $('#createTravelTypeModal').on('shown.bs.modal', function() {
        //     // Reinitialize Select2
        //     if ($('#plan_id').hasClass('select2-hidden-accessible')) {
        //         $('#plan_id').select2('destroy');
        //     }
        //     $('.select2').select2();
        // });

        $('#createTravelTypeModal').on('shown.bs.modal', function() {
            if (!$(this).data('select2-initialized')) {
                $('.select2').select2({
                    dropdownParent: $('#createTravelTypeModal')
                });
                $(this).data('select2-initialized', true);
            }
        });
    </script>


@endsection
