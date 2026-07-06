@extends('admin.layout.master')
<script src="{{ asset('assets/js/cities.js') }}"></script>
@section('title')
    Approval Stage
@endsection


<style>
    .image-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        align-items: flex-start;
    }

    .image-preview {
        position: relative;
        flex: 0 1 calc(25% - 8px);
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 6px;
        overflow: hidden;
        aspect-ratio: 1 / 1;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Tooltip on hover */
    .image-preview .tooltip {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        font-size: 12px;
        padding: 4px 6px;
        text-align: center;
        opacity: 0;
        transition: opacity 0.3s;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .image-preview:hover .tooltip {
        opacity: 1;
    }
</style>

@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/account') }}">Settings</a></li>
                    <li class="active"><span><b>Approval Stage</b></span></li>

                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <!-- Add Folder Button -->
                                <button class="btn btn-outline-primary" id="addApprovalBtn">
                                    Add
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
                    <h4 class="card-title">Approval Stage</h4>
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
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="approval-table-dynamic">

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


    {{-- approval flow model  --}}

    <div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Approval Flow</h5>
                    <button type="button" class="btn" data-bs-dismiss="modal">X</button>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approvalForm">
                    @csrf
                    <input type="hidden" name="afc_id" id="afc_id">

                    <div class="modal-body">
                        <!-- Approval Stage -->
                        <div class="mb-3">
                            <label>Approval Module</label>
                            <select name="afc_approval_id" id="afc_approval_id" class="form-control" required>
                                <option value="">-- Select Module --</option>
                                @foreach ($approvalFlowmodule as $module)
                                    <option value="{{ $module->m_id }}">{{ $module->m_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Approval Status (Multi Select with Select2) -->
                        <div class="mb-3">
                            <label>Approval Stage</label>
                            <select name="afc_approval_status_id[]" id="afc_approval_status_id" class="form-control"
                                multiple required>
                                @foreach ($approvalFlowstatus as $status)
                                    <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {

            // Initialize DataTable
            datatable({
                tableId: "approval-table-dynamic",
                url: "{{ route('approval.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            // Initialize Select2
            $('#afc_approval_status_id, #afc_approval_id').select2({
                placeholder: "-- Select --",
                width: '100%',
                allowClear: true
            });

            // Open Add Modal
            $('#addApprovalBtn').click(function() {
                $('#approvalForm')[0].reset();
                $('#afc_id').val('');
                $('#afc_approval_id, #afc_approval_status_id').val(null).trigger('change');
                $('#modalTitle').text('Add Approval Flow');
                $('#saveBtn').text('Save');
                $('#approvalForm').attr('action', "{{ route('approval.store') }}");
                $('#approvalForm').attr('method', 'POST');
                $('#approvalForm').find('input[name="_method"]').remove();
                $('#approvalModal').modal('show');
            });

            // Open Edit Modal
            $(document).on('click', '.edit-approval', function() {
                let id = $(this).data('id');
                $('#afc_id').val(id);
                $('#afc_approval_id').val($(this).data('approval')).trigger('change');

                let statuses = $(this).data('status');
                if (typeof statuses === 'string') {
                    try {
                        statuses = JSON.parse(statuses);
                    } catch (e) {
                        statuses = [statuses];
                    }
                }
                $('#afc_approval_status_id').val(statuses).trigger('change');

                $('#modalTitle').text('Edit Approval Flow');
                $('#saveBtn').text('Update');
                $('#approvalForm').attr('action', `/admin/approval-flows/update/${id}`);
                $('#approvalForm').attr('method', 'POST');

                if ($('#approvalForm input[name="_method"]').length === 0) {
                    $('#approvalForm').append('<input type="hidden" name="_method" value="PUT">');
                }

                $('#approvalModal').modal('show');
            });

            // Submit Form (works for both add & edit)
            $('#approvalForm').submit(function(e) {
                e.preventDefault();
                $('#saveBtn').prop('disabled', true);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#saveBtn').prop('disabled', false);
                        $('#approvalModal').modal('hide');
                        Swal.fire('Success', res.message, 'success').then(() => location
                        .reload());
                    },
                    error: function(xhr) {
                        $('#saveBtn').prop('disabled', false);
                        let message = 'An error occurred.';
                        if (xhr.status === 422) {
                            message = Object.values(xhr.responseJSON.errors).map(err =>
                                `<p>${err[0]}</p>`).join('');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            });

            // Delete Approval Flow
            $(document).on('click', '.delete-approval', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This approval flow will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/approval-flows/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE'
                            },
                            success: function(res) {
                                Swal.fire('Deleted', res.message ||
                                        'Approval flow deleted.', 'success')
                                    .then(() => location.reload());
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete approval flow.',
                                    'error');
                            }
                        });
                    }
                });
            });

        });
    </script>
@endsection
