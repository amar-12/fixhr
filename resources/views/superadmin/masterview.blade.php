@extends('superadmin.layout.master')
<style>
    .datatable {
        margin-top: 10px;
    }

    #create-button {
        margin-left: 10px;
    }

    .btn-primary:hover {
        background-color: #4f8ae1 !important;
        color: white !important;
    }
</style>
@section('content')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>


    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>




    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                    <li class="active"><span><b>Master Settings</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button class="btn btn-outline-primary " id="openFormButton" data-bs-toggle="modal"
                                    data-bs-target="#masterModal">Create Master</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW -->
    <div class="datatable">
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Masters List</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border-bottom" id="users-table">
                                <thead>
                                    <tr>
                                        <th>Master Id</th>
                                        <th>Master Group</th>
                                        <th>Master Name</th>
                                        <th>Master Alias Name</th>
                                        <th>Master Type</th>
                                        <th>Master Other</th>
                                        <th>Master Description</th>
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



    <div class="modal fade" id="masterModal" tabindex="-1" aria-labelledby="masterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="masterModalLabel">Create Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="masterCreate">
                        @csrf
                        <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Name</p>
                        <input class="form-control" type="text" placeholder="Enter Name" name="name" id="name">

                        <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Group</p>
                        <input class="form-control" type="text" placeholder="Enter Group" name="group" id="group">

                        <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Type</p>
                        <input class="form-control" type="text" placeholder="Enter Type" name="type" id="type">

                        <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Alias Name</p>
                        <input class="form-control" type="text" placeholder="Enter Alias Name" name="alias_name"
                            id="alias">

                        <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Description</p>
                        <input class="form-control" type="text" placeholder="Enter Description" name="description"
                            id="description">

                        <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Other</p>
                        <input class="form-control" type="text" placeholder="Enter Other" name="other" id="other">

                        <div class="mt-3">
                            <button type="submit" class="btn btn-outline-primary create-master">Submit</button>
                            <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" id="editform">
                        @csrf
                        <input type="hidden" id="userId">
                        <div class="modal-body">
                            <div class="col-lg">
                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Name</p>
                                <input class="form-control" type="text" placeholder="Enter Name" name="name"
                                    id="editname">

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Group</p>
                                <input class="form-control" type="text" placeholder="Enter Group" name="group"
                                    id="editgroup">

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Type</p>
                                <input class="form-control" type="text" placeholder="Enter Type" name="type"
                                    id="edittype">

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Description</p>
                                <input class="form-control" type="text" placeholder="Enter Description"
                                    name="description" id="editdescription">

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Other</p>
                                <input class="form-control" type="text" placeholder="Enter Other" name="other"
                                    id="editother">

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Master Alias</p>
                                <input class="form-control" type="text" placeholder="Enter Alias Name" name="alias"
                                    id="editalias">

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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('masters.data') }}',
                columns: [{
                        data: 'm_id',
                        name: 'm_id',
                        render: function(data) {
                            return data ? data : '----';
                        }
                    },
                    {
                        data: 'm_group',
                        name: 'm_group',
                        render: function(data) {
                            return data ? data : '----';
                        }
                    },
                    {
                        data: 'm_name',
                        name: 'm_name',
                        render: function(data) {
                            return data ? data : '----';
                        }
                    },
                    {
                        data: 'm_alias_name',
                        name: 'm_alias_name',
                        render: function(data) {
                            return data ? data : '----';
                        }
                    },
                    {
                        data: 'm_type',
                        name: 'm_type',
                        render: function(data) {
                            return data ? data : '----';
                        }
                    },
                    {
                        data: 'm_other',
                        name: 'm_other',
                        render: function(data) {
                            return data ? data : '----';
                        }
                    },
                    {
                        data: 'm_description',
                        name: 'm_description',
                        render: function(data) {
                            return data ? data : '----';
                        }
                    },
                    {

                        data: 'm_id',
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
                            `;
                        }
                    }
                ]
            });

            $(document).on('submit', '#masterCreate', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var url = '{{ route('superadmin.add-master') }}';
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    success: function(response) {
                        if (response.success==true) {
                            Swal.fire({
                                title: "Success!",
                                text: "Master created successfully.",
                                icon: "success"
                            }).then(() => {

                                $('#masterCreate')[0].reset();
                                $('#masterModal').modal('hide');
                                $('#users-table').DataTable().ajax.reload(null, false);


                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');
                                $('body').css('padding-right', '');

                            });
                        } else {
                            Swal.fire("Error", response.message || 'Failed to create master',
                                "error");
                                $('#masterCreate')[0].reset();
                                $('#masterModal').modal('hide');

                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');
                                $('body').css('padding-right', '');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire("Error", xhr.responseJSON?.message || error, "error");
                    }
                });
            });


            $(document).on('click', '#delete-data', function() {
                var id = $(this).attr('data-id');
                var url = '/superadmin/delete-master/' + id;



                $.ajax({
                    type: 'DELETE',
                    url: url,
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success == true) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "Your item has been deleted.",
                                icon: "success"
                            }).then(() => {
                                $('#users-table').DataTable().ajax.reload(null, false);
                            });


                            // $('#delete-data[data-id="' + id + '"]').closest('tr').remove();


                            // $('#users-table').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire("Error", response.message || 'Failed to delete item',
                                "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire("Error", xhr.responseJSON?.message || error, "error");
                    }
                });

            });





        });




        $(document).on("click", ".edit-btn", function() {
            var id = $(this).attr('data-update-id');
            $("#userId").val(id);
            var base_url = window.location.origin;
            var url = base_url + "/superadmin/edit.master/" + id;

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.status == true) {
                        $("#editname").val(response.result.m_name);
                        $("#editgroup").val(response.result.m_group);
                        $("#edittype").val(response.result.m_type);
                        $("#editdescription").val(response.result.m_description);

                        $("#editother").val(response.result.m_other);
                        $("#editalias").val(response.result.m_alias_name);
                    }

                },
                error: function(xhr) {
                    Swal.fire("Error fetching user:", xhr.responseText);
                }
            });


            var myModal = new bootstrap.Modal(document.getElementById('editModal'), {
                keyboard: false
            });
            myModal.show();

        });



        $("#editform").submit(function(e) {
            e.preventDefault();

            var id = $("#userId").val();
            var base_url = window.location.origin;
            var url = base_url + "/superadmin/edit.master/" + id;


            var formData = $(this).serialize();



            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.status==true) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Master updated successfully!',
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

                    if(response.status==false){
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Duplicate entry for this Master Group and Master Name is not allowed.',
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
                    $("#editModal").modal("hide");
                    $('#users-table').DataTable().ajax.reload(null, false);

                },
                error: function(xhr) {
                    Swal.fire("Error updating user:", xhr.responseText);
                }
            });
        });
    </script>
@endsection
