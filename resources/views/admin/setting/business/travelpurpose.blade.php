@extends('admin.layout.master')

@section('title', 'TA & DA Settings')

{{-- @section('script')

@endsection --}}

@section('content')
        {{-- Bradcrumbs Start --}}
<div class="p-0 mt-3">
    <div class="row">
        <div class="col-md-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                <li class="active"><span><b>Travel Purpose</b></span></li>
            </ol>
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-2">
            <div class="page-rightheader ms-md-auto">
                <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                    <div class="d-lg-flex d-block ms-auto">
                        <div class="btn-list">
                            <button type="button" class="btn btn-outline-primary" id="createDepartmentBtn" data-bs-toggle="modal"
                            data-bs-target="#createTravelPurposeModal">Add Travel Purpose</button>                    </div>
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
                    <h4 class="card-title">Travel Purpose</h4>
                </div>

                <div class="card-body">

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

                    @csrf
                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="department-table-dynamic">
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
                        {{-- <div class="col-sm-6">
                            <div id="custom-show-entries" data-show-entries></div>
                        </div> --}}
                        <div class="col-sm-6 d-flex justify-content-end">
                            <ul data-pagination class="custom-pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- Grade Creation Modal --}}
    <div class="modal fade" id="createTravelPurposeModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title" id="modalTitle">Create Travel Purpose</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="travelPurposeForm">
                    <input type="hidden" name="editid" id="editid">
                    @csrf
                    <div class="modal-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <label for="Department" class="form-label">Department Name <span
                                    class="text-red">*</span></label>
                        </div>

                        <select name="department" id="department" class="form-control form-select travelType select2"
                            data-placeholder="Department Name" required>
                            <option label="Department Name"></option>
                            @foreach ($department as $key => $dept)
                                <option value="{{ $dept->d_id }}" data-m_id="{{ $dept->d_id }}">
                                    {{ $dept->d_name }}
                                </option>
                            @endforeach
                        </select>

                        <label for="editPurpose" class="form-label mb-1 mt-3">Purpose <span class="text-red">*</span></label>
                        <input id="editPurpose" name="purpose" type="text" class="form-control"
                            placeholder="Enter Purpose Name" required>
                        <span class="text-danger" id="department-update-error"></span>
                    </div>
                    <div class="modal-footer d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary saveUptBtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    {{-- Delete Department Modal --}}
    <div class="modal fade" id="deleteDepartmentModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <h4 class="mt-5">Are you sure you want to delete <span id="deletedID" class="text-primary"></span>
                        purpose?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    {{-- <form method="POST" action="{{ route('delete.travelpurpose') }}"> --}}
                    @csrf
                    <input type="hidden" name="deleteId" id="deleteId">
                    <button type="submit" class="btn btn-outline-danger ">Delete</button>
                    {{-- </form> --}}
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#department-table-dynamic').DataTable({
            processing: true,
            serverSide: true,
            searching: false,      // Hide search input
            lengthChange: false,   // Hide "Show entries" dropdown
            ajax: {
                url: "{{ route('admin.travelpurpose') }}",
                type: 'GET'
            },
            columns: [
                { data: '0', name: 'S. No.' },
                { data: '1', name: 'Department Name' },
                { data: '2', name: 'Travel Purpose' },
                { data: '3', name: '' },
                { data: '4', name: 'Action' }
            ]
        });
    });
</script>




    <script>
        $('#departmentID').on('input', function() {
            $('#department-error').html('');
        });

        $('#editDepartment').on('input', function() {
            $('#department-update-error').html('');
        });

                    document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('travelPurposeForm');
                const editIdInput = document.getElementById('editid');
                const departmentSelect = document.getElementById('department');
                const purposeInput = document.getElementById('editPurpose');
                const saveButton = document.getElementById('saveUptBtn');

                // Function to reset the form
                function resetForm() {
                    form.reset(); // Resets all form fields
                    editIdInput.value = ''; // Clear edit ID
                    $('#department').val(null).trigger('change'); // Reset select2 dropdown
                    $('#department-update-error').text(''); // Clear error messages
                    saveButton.removeAttribute('disabled'); // Enable save button
                }

                // Set form action based on edit or create mode
                function setFormAction() {
                    if (editIdInput.value) {
                        form.action = "{{ route('update.travelpurpose') }}"; // Update route
                    } else {
                        form.action = "{{ route('add.travelpurpose') }}"; // Add route
                    }
                }

                // Attach event listener to the "Add Travel Purpose" button
                document.getElementById('createDepartmentBtn').addEventListener('click', function() {
                    resetForm(); // Clear the form for new data
                    setFormAction(); // Ensure the form is set to the add action
                });

                // When modal is shown, check and set form action
                $('#createTravelPurposeModal').on('shown.bs.modal', setFormAction);

                // Handle form submission via AJAX
                $('#travelPurposeForm').submit(function(e) {
                    e.preventDefault();

                    const url = $(this).attr("action");
                    const formData = new FormData(this);

                    saveButton.setAttribute('disabled', true);

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function() {
                            saveButton.setAttribute('disabled', true);
                        },
                        success: (response) => {
                            if (response.success) {
                                $('#createTravelPurposeModal').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    text: response.success,
                                    timer: 3000,
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    text: response.error,
                                    timer: 3000,
                                });
                                saveButton.removeAttribute('disabled');
                            }
                        },
                        error: function(response) {
                            const errors = response.responseJSON.errors;
                            if (errors?.department) {
                                $('#department-update-error').text(errors.department[0]);
                            } else if (errors?.purpose) {
                                $('#department-update-error').text(errors.purpose[0]);
                            } else {
                                $('#department-update-error').text('An unknown error occurred.');
                            }
                            saveButton.removeAttribute('disabled');
                        }
                    });
                });
            });

        function openDeleteDepartment(context) {
            document.getElementById('deleteId').textContent = context.dataset.id;
            document.getElementById('deletedID').value = context.dataset.department;
            new bootstrap.Modal(document.getElementById('deleteDepartmentModal')).show();
        }


        function openEditRole(context) {
            $('#department-update-error').html('');

            const id = context.dataset.id;
            const departmentId = context.dataset.department;
            const purpose = context.dataset.purpose;

            // Set values for editing
            $('#editid').val(id);
            $('#editPurpose').val(purpose);
            $('#department').val(departmentId);

            // Show the modal
            new bootstrap.Modal(document.getElementById('createTravelPurposeModal')).show();
        }





        $(document).ready(function() {
            // Initialize Select2 globally for elements with the class 'select2'
            $('.select2').select2();

            // Reinitialize Select2 when the modal is shown
            $('#createTravelPurposeModal').on('shown.bs.modal', function() {
                // Destroy existing Select2 instance if it exists
                $('.select2').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                // Initialize Select2 again within the modal
                $('.select2').select2({
                    dropdownParent: $('#createTravelPurposeModal')
                });
            });
        });


        $(document).on('click', '.delete-purpose', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this travel purpose!',
                // timer: 3000,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {

                    var url = "{{ route('delete.travelpurpose', ':id') }}";

                    url = url.replace(':id', id);
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },

                        success: function(response) {
                            if (response.success) {

                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.success,
                                    icon: 'success',
                                    timer: 3000, // 3 seconds
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    didClose: () => {
                                        location
                                            .reload(); // Reload the page after deletion
                                    }
                                });
                            } else {

                                Swal.fire({
                                    // title: 'Deleted!',
                                    text: response.error,
                                    icon: 'error',
                                    timer: 3000, // 3 seconds
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    didClose: () => {
                                        // location
                                        //     .reload(); // Reload the page after deletion
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection
