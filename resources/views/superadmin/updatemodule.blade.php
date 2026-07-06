@extends('superadmin.layout.master')
@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Update Pricing Details</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                padding: 20px;
                background-color: #f8f9fa;
            }

            .card {
                margin-bottom: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .table-container {
                background-color: white;
                border-radius: 5px;
                padding: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .form-group {
                margin-bottom: 15px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1 class="mb-4 text-center">Update Pricing Details</h1>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Filter Options</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="menuModule">Menu Module</label>
                                    <input type="text" class="form-control" value="{{ $module->mdl_name }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="featureName">Feature Name</label>
                                    <input type="text" class="form-control"
                                        value="{{ optional($module_selected_features?->first())->mdf_name ?? 'No feature found' }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="featureCode">Feature Code</label>
                                    <input type="text" class="form-control"
                                        value="{{ optional($module_selected_features?->first())->mdf_code ?? 'No feature found' }}">
                                </div>
                            </div>

                        </div>
                        {{-- <div class="text-end mt-3">
                            <button type="button" class="btn btn-outline-primary" id="searchBtn">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div> --}}
                    </form>
                </div>
            </div>

            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Feature Pricing List</h4>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFeatureModal">
                        <i class="fas fa-plus"></i> Add New Feature
                    </button>
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
                                                    <th>ID</th>
                                                    <th>Module</th>
                                                    <th>Feature Name</th>
                                                    <th>Feature Code</th>
                                                    <th>Description</th>
                                                    <th>Price</th>
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

                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be added via JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>


        <!-- Bootstrap Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Feature Pricing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="" id="editform">
                            @csrf
                            <input type="hidden" name="moduleFeatureId" id="moduleFeatureId">
                            <div class="modal-body">
                                <div class="col-lg">
                                    <p class="mb-0 pb-0 text-dark fs-13 mt-1">Module</p>
                                    <input class="form-control" type="number" placeholder="Enter Module" name="module"
                                        id="editmodule">

                                    <p class="mb-0 pb-0 text-dark fs-13 mt-1">Feature Name</p>
                                    <input class="form-control" type="text" placeholder="Enter Feature Name"
                                        name="name" id="name">

                                    <p class="mb-0 pb-0 text-dark fs-13 mt-1">Feature Code</p>
                                    <input class="form-control" type="text" placeholder="Enter Feature Code"
                                        name="code" id="editcode">

                                    <p class="mb-0 pb-0 text-dark fs-13 mt-1">Feature Description</p>
                                    <input class="form-control" type="text" placeholder="Enter Description"
                                        name="description" id="editdescription">

                                    <p class="mb-0 pb-0 text-dark fs-13 mt-1">Price</p>
                                    <input class="form-control" type="number" step="0.01" placeholder="Enter Price"
                                        name="price" id="editprice">

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


        <!-- Add Feature Modal -->
        <div class="modal fade" id="addFeatureModal" tabindex="-1" aria-labelledby="addFeatureModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="addFeatureModalLabel">Add New Feature</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addFeatureForm">
                            @csrf
                            <div class="form-group">
                                <label for="addMdlId">Module</label>
                                <select class="form-select" id="addMdlId" required>
                                    <option value="">Select Module</option>
                                    @foreach ($module_list as $list)
                                        <option value="{{ $list->mdl_id }}">{{ $list->mdl_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="addName">Feature Name </label>
                                <input type="text" class="form-control" id="addName">
                            </div>
                            <div class="form-group">
                                <label for="addCode">Feature Code</label>
                                <input type="text" class="form-control" id="addCode">
                            </div>
                            <div class="form-group">
                                <label for="addDescription">Description</label>
                                <textarea class="form-control" id="addDescription" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="addPrice">Price</label>
                                <input type="number" name="price" step="0.01" class="form-control"
                                    id="addPrice">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="addFeatureBtn">Add Feature</button>
                    </div>
                </div>
            </div>
        </div>



        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- jQuery for AJAX -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#addFeatureBtn').click(function() {
                    const featureData = {
                        mdf_mdl_id: $('#addMdlId').val(),
                        mdf_name: $('#addName').val(),
                        mdf_code: $('#addCode').val(),
                        mdf_description: $('#addDescription').val(),
                        mdf_price: parseFloat($('#addPrice').val())
                    };
                    $.ajax({
                        url: "{{ route('features.store') }}",
                        method: 'POST',
                        dataType: 'json',
                        data: featureData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Module Feature created successfully.",
                                    icon: "success"
                                }).then(() => {
                                    $('#addFeatureModal').modal('hide');
                                    $('#addFeatureForm')[0].reset();
                                    $('#users-table').DataTable().ajax.reload(null, false);


                                    $('.modal-backdrop').remove();
                                    $('body').removeClass('modal-open');
                                    $('body').css('padding-right', '');
                                });


                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Failed to add feature: ' + (response.message ||
                                        'Unknown error'),
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            }

                        },
                        error: function(xhr, status, error) {
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;


                                $('#addPrice').removeClass('is-invalid');
                                $('#priceError').text('');

                                if (errors && errors.mdf_price) {

                                    $('#addPrice').addClass('is-invalid');
                                    $('#priceError').text(errors.mdf_price[0]);

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Validation Error',
                                        text: errors.mdf_price[0]
                                    });
                                }
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Failed to add feature. Please try again.',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            }
                        }




                    });
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                $('#users-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('modules.get-modules-features') }}',
                    columns: [{
                            data: 'mdf_id',
                            name: 'mdf_id',
                            render: function(data) {
                                return data ? data : '----';
                            }
                        }, {
                            data: 'mdf_mdl_id',
                            name: 'mdf_mdl_id',
                            render: function(data) {
                                return data ? data : '----';
                            }
                        },
                        {
                            data: 'mdf_name',
                            name: 'mdf_name',
                            render: function(data) {
                                return data ? data : '----';
                            }
                        },
                        {
                            data: 'mdf_code',
                            name: 'mdf_code',
                            render: function(data) {
                                return data ? data : '----';
                            }
                        },
                        {
                            data: 'mdf_description',
                            name: 'mdf_description',
                            render: function(data) {
                                return data ? data : '----';
                            }
                        },

                        {
                            data: 'mdf_price',
                            name: 'mdf_price',
                            render: function(data) {
                                return data ? data : '----';
                            }
                        },
                        {

                            data: 'mdf_id',
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

            });



            $(document).on("click", ".edit-btn", function() {
                var id = $(this).attr('data-update-id');
                $("#moduleFeatureId").val(id);
                var base_url = window.location.origin;


                var url = base_url + "/superadmin/module/edit-feature/" + id;

                // Fetch user data via AJAX
                $.ajax({
                    url: url,
                    type: "GET",
                    success: function(response) {
                        if (response.status == true) {
                            $("#editcode").val(response.result.mdf_code);
                            $("#name").val(response.result.mdf_name);
                            $("#editdescription").val(response.result.mdf_description);
                            $("#editprice").val(response.result.mdf_price);
                            $("#editmodule").val(response.result.mdf_mdl_id);
                        }

                    },
                    error: function(xhr) {
                        Swal.fire("Error fetching user:", xhr.responseText);
                    }
                });

                // Show the modal
                var myModal = new bootstrap.Modal(document.getElementById('editModal'), {
                    keyboard: false
                });
                myModal.show();

            });


            $(document).ready(function() {
                $("#editform").submit(function(e) {
                    e.preventDefault();

                    var id = $("#moduleFeatureId").val();


                    var base_url = window.location.origin;

                    var url = base_url + "/superadmin/module/edit-feature/" + id;

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
                                    title: 'Feature updated successfully!',
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
                                $("#editModal").modal("hide");
                                    $('#users-table').DataTable().ajax.reload(null, false);
                                    $('.modal-backdrop').remove();
                                    $('body').removeClass('modal-open');
                                    $('body').css('padding-right', '');
                            }
                            else{
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: (response.message ||
                                        'Unknown error'),
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire("Error updating user:", xhr.responseText);
                        }
                    });
                });
            });

            $(document).on('click', '#delete-data', function() {
                var id = $(this).attr('data-id');
                var url = '/superadmin/module/delete-feature/' + id;
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
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
                            Swal.fire("Error", response.message || 'Failed to delete item',
                                "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire("Error", xhr.responseJSON?.message || error, "error");
                    }
                });


            });
        </script>
    </body>

    </html>
@endsection
