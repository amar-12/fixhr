@extends('superadmin.layout.master')
@section('content')
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>


    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .datatable {
            margin-top: 20px;
        }

        .custom-btn {
            background-color: #0d6efd;
            /* Bootstrap primary */
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 0.375rem;
            /* rounded-md */
            display: inline-block;
            text-align: center;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s ease;
        }

        .custom-btn:hover {
            background-color: #0b5ed7;
            /* Slightly darker on hover */
        }
    </style>
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li class="custom-btn">Super Admin Modules</li>


                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button class="btn btn-outline-primary " data-bs-toggle="modal" data-bs-target="#moduleModal">Create
                                    Module</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>


    <div class="datatable">


        <div class="modal fade" id="moduleModal" tabindex="-1" aria-labelledby="moduleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="moduleModalLabel">Create Module</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>

                    <div class="modal-body">
                        <form id="moduleForm">
                            @csrf
                            <input type="hidden" name="_method" id="formMethod" value="POST">
                            <input type="hidden" name="m_id" id="moduleId">


                            <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module Name</p>
                            <input class="form-control" type="text" placeholder="Enter Name" name="mdl_name"
                                id="createmodulename">
                            <p class="text-danger mb-0" id="mdl_name_error"></p>

                            <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module Code</p>
                            <input class="form-control" type="text" placeholder="Enter Code" name="mdl_code"
                                id="createmodulecode">
                            <p class="text-danger mb-0" id="mdl_code_error"></p>

                            <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module Description</p>
                            <input class="form-control" type="text" placeholder="Enter Description"
                                name="mdl_description" id="description">

                            <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module Price</p>
                            <input class="form-control" type="number" step="0.01" min="0"
                                placeholder="Enter Price" name="mdl_price" id="price">


                            <div class="mt-3">
                                <button type="submit" class="btn btn-outline-primary">Submit</button>
                                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="datatable">
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Modules List</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border-bottom" id="users-table">
                                <thead>
                                    <tr>
                                        <th>Module id</th>
                                        <th>Module Name</th>
                                        <th>Module Code</th>
                                        <th>Module Description</th>
                                        <th>Module Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" id="editform">
                        @csrf
                        <input type="hidden" id="userId">
                        <div class="modal-body">
                            <div class="col-lg">
                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module Name</p>
                                <input class="form-control" type="text" placeholder="Enter Name" name="name"
                                    id="editname">

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module Code</p>
                                <input class="form-control" type="text" placeholder="Enter Code" name="code"
                                    id="editCode">


                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module Description</p>
                                <input class="form-control" type="text" placeholder="Enter Description"
                                    name="description" id="editDescription">

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module Price</p>
                                <input class="form-control" type="number" step="0.01" placeholder="Enter Price"
                                    name="price" id="editPrice">



                                <div class="modal-footer">
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-outline-primary">Update</button>
                                        <button type="button" class="btn btn-warning"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('modules.get-modules') }}',
            columns: [{
                    data: 'mdl_id',
                    name: 'mdl_id',
                    render: function(data) {
                        return data ? data : '----';
                    }
                }, {
                    data: 'mdl_name',
                    name: 'mdl_name',
                    render: function(data) {
                        return data ? data : '----';
                    }
                },
                {
                    data: 'mdl_code',
                    name: 'mdl_code',
                    render: function(data) {
                        return data ? data : '----';
                    }
                },
                {
                    data: 'mdl_description',
                    name: 'mdl_description',
                    render: function(data) {
                        return data ? data : '----';
                    }
                },
                {
                    data: 'mdl_price',
                    name: 'mdl_price',
                    render: function(data) {
                        return data ? data : '----';
                    }
                },
                {

                    data: 'mdl_id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                                <button class="btn btn-outline-primary btn-sm edit-btn" data-update-id="${data}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button id="delete-data" class="btn btn-outline-danger  btn-sm delete-btn" data-id="${data}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                              <button class="btn btn-info btn-sm view-btn" data-view-id="${data}">
                                    <i class="fas fa-eye"></i>
                              </button>

                                `;
                    }
                }

            ]
        });

    });

    $(document).on('submit', '#moduleForm', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
        type: 'POST',
        url: '{{ route('module.create') }}',
        data: formData,
        success: function(response) {
            if (response.status == true) {
                Swal.fire({
                    title: "Success!",
                    text: "Module created successfully.",
                    icon: "success"
                }).then(() => {
                    $('#moduleForm')[0].reset();
                    $('#moduleModal').modal('hide');
                    $('#users-table').DataTable().ajax.reload(null, false);


                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        $('body').css('padding-right', '');

                    $(document).on('click', '#delete-data', function() {
                        var id = $(this).attr('data-id');
                        var url = '/superadmin/module/delete-module/' + id;

                    });
                });
            } else {
                if (response.message != '') {
                    Swal.fire({
                        title: "Error!",
                        text: response.message,
                        icon: "error"
                    });
                    $('#moduleForm')[0].reset();
                    $('#moduleModal').modal('hide');
                    // $('#users-table').DataTable().ajax.reload(null, false);
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                }

                if (response.errors) {
                    if (response.errors.mdl_name) {
                        $('#createmodulename').addClass('is-invalid');
                        $('#mdl_name_error')
                            .addClass('invalid-feedback')
                            .html(response.errors.mdl_name);
                    } else {
                        $('#createmodulename').removeClass('is-invalid');
                        $('#mdl_name_error').removeClass('invalid-feedback').html('');
                    }

                    if (response.errors.mdl_code) {
                        $('#createmodulecode').addClass('is-invalid');
                        $('#mdl_code_error')
                            .addClass('invalid-feedback')
                            .html(response.errors.mdl_code);
                    } else {
                        $('#createmodulecode').removeClass('is-invalid');
                        $('#mdl_code_error').removeClass('invalid-feedback').html('');
                    }
                }
            }
        },
        error: function(xhr, status, error) {
            Swal.fire(xhr.responseText || error);
        }
    });
});


                $(document).on('click', '#delete-data', function() {
                    var id = $(this).attr('data-id');
                    var url = '/superadmin/module/delete-module/' + id;

                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You will not be able to recover this module!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: 'DELETE',
                                url: url,
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: "Deleted!",
                                            text: "Your item has been deleted.",
                                            icon: "success"
                                        }).then(() => {
                                            $('#users-table').DataTable().ajax.reload(null, false);
                                        });
                                    } else {
                                        Swal.fire("Error", response.message || 'Failed to delete item', "error");
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire("Error", xhr.responseJSON?.message || error, "error");
                                }
                            });
                        }
                    });
                });





                $(document).on("click", ".edit-btn", function() {

                    var id = $(this).attr('data-update-id');
                    $("#userId").val(id);
                    var base_url = window.location.origin;
                    var url = base_url + "/superadmin/module/edit-module/" + id;

                    $.ajax({
                        url: url,
                        type: "GET",
                        success: function(response) {
                            if (response.status == true) {
                                $("#editname").val(response.result.mdl_name);
                                $("#editCode").val(response.result.mdl_code);
                                $("#editPrice").val(response.result.mdl_price);
                                $("#editDescription").val(response.result.mdl_description);

                            }

                        },
                        error: function(xhr) {
                            console.error("Error fetching user:", xhr.responseText);
                        }
                    });

                    // Show the modal
                    var myModal = new bootstrap.Modal(document.getElementById('editModal'), {
                        keyboard: false
                    });
                    myModal.show();

                });


                $(document).on("click", ".view-btn", function() {
                    var id = $(this).attr('data-view-id');
                    window.location.href = "/superadmin/module/update/" + id;

                });



                $(document).ready(function() {
                    $("#editform").submit(function(e) {
                        e.preventDefault();

                        var id = $("#userId").val();
                        var base_url = window.location.origin;
                        var url = base_url + "/superadmin/module/edit-module/" + id;

                        var formData = $(this).serialize();

                        $.ajax({
                            url: url,
                            type: "POST",
                            data: formData,
                            success: function(response) {

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Feature updated successfully!',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter',
                                            Swal
                                            .stopTimer);
                                        toast.addEventListener('mouseleave',
                                            Swal
                                            .resumeTimer);
                                    }
                                });
                                $("#editModal").modal("hide");
                                $('#users-table').DataTable().ajax.reload(null, false);

                            },
                            error: function(xhr) {
                                console.error("Error updating user:", xhr.responseText);
                            }
                        });


                    });
                });
</script>
