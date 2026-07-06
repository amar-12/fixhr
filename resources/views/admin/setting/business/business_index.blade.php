@extends('admin.layout.master')
<script src="{{ asset('assets/js/cities.js') }}"></script>
@section('title')
    Projects
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
                    <li class="active"><span><b>Projects</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <!-- Add Folder Button -->
                                <button class="btn btn-outline-primary" id="addProjectBtn">
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
                    <h4 class="card-title">Structure</h4>
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
                            id="project-table-dynamic">
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


    <!-- Folders Display -->
    {{-- <div class="row row-sm">
    @foreach ($projects as $project)
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-folder-open mx-1"></i>
                            </span>
                        </div>

                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="{{ route('projects.show', ['id' => $project->ps_id]) }}" class="text-dark">
                                    <h5 class="my-auto">{{ $project->ps_name }}</h5>
                                </a>

                                <p class="my-auto">
                                    {{ Str::limit($project->ps_description, 60, '...') }}
                                </p>
                            </div>

                            <div class="my-auto d-flex">
                                <button class="btn btn-sm edit-project"
                                    data-id="{{ $project->ps_id }}"
                                    data-name="{{ $project->ps_name }}"
                                    data-description="{{ $project->ps_description }}">
                                    <i class="feather feather-edit"></i>
                                </button>

                                <button class="btn btn-sm delete-project"
                                    data-id="{{ $project->ps_id }}">
                                    <i class="feather feather-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div> --}}


    <!-- Add/Edit Modal -->
    <div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Project</h5>
                    <button type="button" class="btn" data-bs-dismiss="modal">X</button>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="projectForm">
                    @csrf
                    <input type="hidden" name="ps_id" id="ps_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" name="ps_name" id="ps_name" class="form-control"
                                placeholder="Project Name" required>
                        </div>

                        <div class="mb-3">
                            <textarea name="ps_description" id="ps_description" class="form-control" placeholder="Project Description"
                                rows="4" required></textarea>
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

            $(document).ready(function() {
                datatable({
                    tableId: "project-table-dynamic",
                    url: "{{ route('projects.index') }}",
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

            // Open Add Modal
            $('#addProjectBtn').click(function() {
                $('#projectForm')[0].reset();
                $('#ps_id').val('');
                $('#modalTitle').text('Add Project');
                $('#saveBtn').text('Save');
                $('#projectModal').modal('show');
            });

            // Open Edit Modal
            $(document).on('click', '.edit-project', function() {
                $('#ps_id').val($(this).data('id'));
                $('#ps_name').val($(this).data('name'));
                $('#ps_description').val($(this).data('description'));
                $('#modalTitle').text('Edit Project');
                $('#saveBtn').text('Update');
                $('#projectModal').modal('show');
            });

            // Submit Form (Add/Edit)
            $('#projectForm').submit(function(e) {
                e.preventDefault();
                $('#saveBtn').prop('disabled', true);

                $.ajax({
                    url: "{{ route('project.store') }}", // Adjust route name as needed
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#saveBtn').prop('disabled', false);
                        $('#projectModal').modal('hide');

                        Swal.fire('Success', response.message, 'success').then(() => {
                            location
                                .reload(); // Optionally use AJAX to refresh table instead
                        });
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

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: message
                        });
                    }
                });
            });

            // Delete Project
            $(document).on('click', '.delete-project', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This project will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/projects/${id}`, // Adjust path to match your routes
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                Swal.fire('Deleted', res.success, 'success').then(
                                    () => {
                                        location.reload();
                                    });
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete project.',
                                    'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
