@extends('admin.layout.master')

@section('title', 'Travel Type')
@section('css')
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
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                    <li class="active"><span><b>Travel Type</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="createTravelTypeBtn"
                                    data-bs-toggle="modal" data-bs-target="#createTravelTypeModal">Create Travel
                                    Type</button>
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
                    <h4 class="card-title">Travel Type</h4>
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

    {{-- <div class="card mt-5">
        <div class="card-header">
            <h3 class="card-title">Travel Type List</h3>
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
                            <th class="border-bottom-0 text-center">Travel Type</th>
                            <th class="border-bottom-0 text-center">Approval Type</th>
                            <th class="border-bottom-0 text-center">Status</th>
                            <th class="border-bottom-0 text-center">Updated At</th>
                            <th class="border-bottom-0 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tadaTravelTypeData as $key => $item)
                            <tr>
                                <td class="font-weight-semibold">{{ ++$key }}</td>
                                <td class="font-weight-semibold text-center">
                                    {{ isset($item->fh_travel_type) ? $item->fh_travel_type->m_name : '' }}
                                </td>
                                <td class="font-weight-semibold text-center">
                                    {{ isset($item->fh_approval_type) ? $item->fh_approval_type->m_name : '' }}
                                </td>
                                <td class="font-weight-semibold text-center">
                                    {{ $item->pttt_status == 1 ? 'Active' : 'Inactive' }}
                                </td>
                                <td class="font-weight-semibold text-center">
                                    <span class="fs-11 fw-bold">W.E.F. </span>
                                    <span
                                        class="with-effect-from-badge fs-10">{{ date('d-M-Y h:i A', strtotime($item->updated_at)) }}</span>
                                </td>
                                <td class="d-flex justify-content-center">
                                    <button class="btn action-btns btn-sm btn-primary editBtn"
                                        data-pttt_id="{{ $item->pttt_id }}"
                                        data-master_travel_type_id={{ $item->pttt_type_id }}
                                        data-pttt_name=" {{ isset($item->fh_travel_type) ? $item->fh_travel_type->m_name : '' }}"
                                        data-master_approval_type_id={{ $item->pttt_approval_type_id }}
                                        data-pttt_approval_name=" {{ isset($item->fh_approval_type) ? $item->fh_approval_type->m_name : '' }}"
                                        data-pttt_status={{ $item->pttt_status }}>
                                        <i class='feather feather-edit'></i>
                                    </button>
                                    <button class="btn action-btns btn-sm btn-danger"
                                        onclick="openDeleteTravelTypeModal(this)" data-pttt_id="{{ $item->pttt_id }}"
                                        data-pttt_name=" {{ isset($item->fh_travel_type) ? $item->fh_travel_type->m_name : '' }}">
                                        <i class="feather feather-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div> --}}

    {{-- Grade Creation Modal --}}
    <div class="modal fade" id="createTravelTypeModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title" id="modalTitle">Create Travel Type</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="addTUpdateTavelTypeForm">
                    <input type="hidden" name="travelTypeId" id="editTravelTypeId"> @csrf
                    <div class="modal-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <label for="travelType" class="form-label">Travel Type <span
                                    class="text-red">*</span></label>
                            <div class="d-flex align-items-center">
                                <label class="custom-switch ms-3">
                                    <input type="checkbox" class="custom-switch-input mt-2" name="travel_type_status"
                                        id="travel_type_status" onclick="changeToggleText(this)">
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description" id="toggle_text">Disable</span>
                                </label>
                            </div>
                        </div>

                        <select name="travel_type_id" id="travel_type_id"
                            class="form-control form-select travelType select2" data-placeholder="Travel Type" required>
                            <option label="Travel Type"></option>
                            @foreach ($travelTypes as $key => $mttype)
                                <option value="{{ $mttype->m_id }}">{{ $mttype->m_name }}</option>
                            @endforeach
                        </select>

                        <label for="approvalType" class="form-label mb-1 mt-3">Approval Type <span
                                class="text-red">*</span></label>
                        <select name="approval_type_id" id="approval_type_id"
                            class="form-control form-select approvalType select2" data-placeholder="Approval Type"
                            required>
                            <option label="Approval Type"></option>
                            @foreach ($approvalTypes as $key => $aptype)
                                <option value="{{ $aptype->m_id }}">{{ $aptype->m_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form method="POST" action="{{ route('delete.travel.type') }}"> @csrf
                        <input type="hidden" name="travelTypeId" id="deleteTravelTypeId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            datatable({
                tableId: "payroll-policy-table-dynamic",
                url: "{{ route('travel.type.list') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            function updateToggleText(status) {
                const textElement = $('#toggle_text');
                textElement.html(status ? 'Enable' : 'Disable').css('font-weight', 'bold');
            }

            function populateTravelTypeForm(data = {}) {
                $('#editTravelTypeId').val(data.travelTypeId || '');
                $('#travel_type_id').val(data.mstTravelTypeId || null).trigger('change');
                $('#approval_type_id').val(data.mstApprovalTypeId || null).trigger('change');
                $('#travel_type_status').prop('checked', !!data.travelTypeStatus);
                updateToggleText(!!data.travelTypeStatus);
                document.getElementById('modalTitle').textContent = data.modalTitle || 'Create Travel Type';
            }

            function changeToggleText(context) {
                updateToggleText(context.checked);
            }

            document.getElementById('createTravelTypeBtn').addEventListener('click', function() {
                document.getElementById('addTUpdateTavelTypeForm').reset();
                populateTravelTypeForm({
                    modalTitle: 'Create Travel Type',
                    travelTypeStatus: false
                });
            });

            $(document).on('click', '.editBtn, .edit-travel-type', function() {
                const $btn = $(this);
                $('#createTravelTypeModal').modal('show');

                const travelTypeId = $btn.data('pttt_id') || $btn.data('id');
                const mstTravelTypeId = $btn.data('master_travel_type_id') || $btn.data('type_id');
                const mstApprovalTypeId = $btn.data('master_approval_type_id') || $btn.data(
                    'approval_type_id');
                const travelTypeStatus = $btn.data('pttt_status') !== undefined ? $btn.data('pttt_status') :
                    $btn.data(
                        'status');

                populateTravelTypeForm({
                    travelTypeId: travelTypeId,
                    mstTravelTypeId: mstTravelTypeId,
                    mstApprovalTypeId: mstApprovalTypeId,
                    travelTypeStatus: travelTypeStatus,
                    modalTitle: 'Update Travel Type'
                });
            });

            function openDeleteTravelTypeModal(context) {
                document.getElementById('delete_name_id').textContent = context.dataset.pttt_name;
                document.getElementById('deleteTravelTypeId').value = context.dataset.pttt_id;
                new bootstrap.Modal(document.getElementById('deleteTravelTypeModal')).show();
            }

            $('#addTUpdateTavelTypeForm').on('submit', function(event) {
                event.preventDefault();
                $('#saveUptBtn').attr('disabled', true);

                $.ajax({
                    url: '{{ route('creatOrUpdate.travel.type') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(data) {
                        Swal.fire({
                            position: 'top-end',
                            icon: data.status ? 'success' : 'warning',
                            title: data.message,
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: {
                                toast: 'swal2-toast-green-glow'
                            },
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal
                                .stopTimer);
                                toast.addEventListener('mouseleave', Swal
                                    .resumeTimer);
                            }
                        });

                        $('#createTravelTypeModal').modal('hide');
                        window.location.href = '{{ route('travel.type.list') }}';
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
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal
                                .stopTimer);
                                toast.addEventListener('mouseleave', Swal
                                    .resumeTimer);
                            }
                        });
                    }
                });
            });

            $(document).ready(function() {
                $('.select2').select2();

                $('#createTravelTypeModal').on('shown.bs.modal', function() {
                    $('.select2').each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                    });

                    $('.select2').select2({
                        dropdownParent: $('#createTravelTypeModal')
                    });
                });
            });
        });
    </script>

@endsection
