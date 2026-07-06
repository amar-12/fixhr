@extends('admin.layout.master')
@section('title', 'Adhoc Components')
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
                    <li class="active"><span><b>Adhoc Components</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addAdhocComponentBtn">Add Adhoc
                                    Component
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
                    <h4 class="card-title">Component List</h4>
                </div>

                <div class="card-body">
                    @csrf
                    {{-- Filters --}}
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

                    {{-- Table --}}
                    <div class="">
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
    </div>


    <!-- Modal for Adhoc Component -->
    <div class="modal fade" id="adhocComponentModal" tabindex="-1" role="dialog" aria-labelledby="adhocComponentModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adhocComponentModalTitle">Add Adhoc Component</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <form id="adhocComponentForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="ac_id" id="ac_id">
                        <input type="hidden" name="businessId" value={{ $businessId }} id="businessId">

                        <!-- Payroll Heading Dropdown -->
                        <div class="row">
                            <div class="col-md-12">
                                {{-- <label for="payroll_heading_id" class="form-label">Payroll Heading <span class="text-danger">*</span></label> --}}
                                <label for="payroll_heading_id" class="form-label">Adhoc Name <span
                                        class="text-danger">*</span></label>
                                <select name="payroll_heading_id" id="payroll_heading_id" class="form-select" required>
                                    <option value="" disabled selected>Select a Heading</option>
                                    @foreach ($payrollHeadings as $heading)
                                        <option value="{{ $heading->m_id }}">{{ $heading->m_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Subheading Name -->
                        <div class="row mt-3">
                            <x-input type="text" id="adhoc_component_name" label="Adhoc Component Name"
                                astric="*" required name="adhoc_component_name"
                                placeholder="Enter Adhoc Component Name" />
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="saveComponentBtn" class="btn btn-outline-primary">Save
                            changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Setup CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            datatable({
                tableId: "payroll-policy-table-dynamic",
                url: "{{ route('adhoc-components.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            // Open modal for Add Component
            $('#addAdhocComponentBtn').on('click', function() {
                $('#adhocComponentForm')[0].reset();
                $('#ac_id').val('');
                $('#component_is_active').prop('checked', false);
                $('#adhocComponentModalTitle').text('Add Adhoc Component');
                $('#saveComponentBtn').text('Save');
                $('#adhocComponentModal').modal('show');
            });

            // Submit form (Create or Update)
            $('#adhocComponentForm').on('submit', function(e) {
                e.preventDefault();
                $('#saveComponentBtn').attr('disabled', true);

                const fd = new FormData(document.getElementById('adhocComponentForm'));
                const ac_id = $('#ac_id').val();
                const isUpdate = ac_id !== '';
                const method = isUpdate ? 'PUT' : 'POST';
                const url = isUpdate ?
                    "{{ route('adhoc-components.updateAdhoc', ':id') }}".replace(':id', ac_id) :
                    "{{ route('adhoc-components.store') }}";

                // // Append _method hidden input if updating
                // if (isUpdate) {
                //     fd.append('_method', 'PUT');
                // }

                $.ajax({
                    url: url,
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#saveComponentBtn').attr('disabled', false);
                        $('#adhocComponentModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $('#payroll-policy-table-dynamic').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        $('#saveComponentBtn').attr('disabled', false);

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            let messages = '';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                messages += `<p>${value[0]}</p>`;
                            });

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: messages,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Something went wrong. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                });
            });


            // Edit Component
            $(document).on('click', '.edit-adhoc-component', function() {
                const data = $(this).data();

                $('#ac_id').val(data.id);
                $('#payroll_heading_id').val(data.heading_id);
                $('#adhoc_component_name').val(data.name);
                $('#component_is_active').prop('checked', data.is_active == 1);

                $('#adhocComponentModalTitle').text('Update Adhoc Component');
                $('#saveComponentBtn').text('Update');
                $('#adhocComponentModal').modal('show');
            });

            // Delete Component
            $(document).on('click', '.delete-adhoc-component', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will not be able to recover this component!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ url('/adhoc-components/delete/:id') }}".replace(':id', id);

                        $.ajax({
                            url: url,
                            type: 'POST', // Using POST instead of DELETE
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                                _method: "DELETE"
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message ??
                                        'Component deleted successfully.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                $('#payroll-policy-table-dynamic').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message ||
                                        'Failed to delete component.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });



        });

        function deleteComponent(event, id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this component!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.forms["deleteCompForm" + id].submit();
                }
            });
        }
    </script>
@endsection

@endsection
