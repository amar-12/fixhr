@extends('admin.layout.master')
<script src="{{ asset('assets/js/cities.js') }}"></script>
@section('title')
    Business Settings
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
  background: rgba(0,0,0,0.7);
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
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

@section('script')
    <script>
        $(document).ready(function() {
            // Event listener for when the currency dropdown value changes
            $('#currency').on('change', function() {
                // Clear the error message when the currency is changed
                $('#currency_error').text(''); // Clear the error message
            });
            $('#currencyModal').on('hidden.bs.modal', function() {
                // Get the original currency value as a string
                var value = JSON.stringify('{{ $accDetail->b_currency }}'); // Convert to string
                if (typeof value === 'string') {
                    value = JSON.parse(value); // Convert string to array if it's a JSON string
                }

                // Make sure it's an array
                if (!Array.isArray(value)) {
                    value = value ? [value] : []; // If it's not an array, convert it to one
                }

                // Iterate over the values and select them in SumoSelect
                $.each(value, function(index, value1) {
                    $('#currency').SumoSelect().sumo.selectItem(String(value1));
                });
                $('#currency').trigger('change'); // Trigger the change event for SumoSelect

                // Clear any previous error messages
                $('#currency_error').text('');
            });

            $('#currencySubmitBtn').on('click', function() {
                // Get the form data
                var formData = $('#currencyForm').serialize();

                // AJAX request
                $.ajax({
                    url: "{{ route('account.update') }}", // Ensure this route is correct
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $('#currencySubmitBtn').attr('disabled',
                            'disabled'); // Disable the submit button
                    },
                    success: function(response) {
                        // Handle success response
                        $('#currencySubmitBtn').attr('disabled',
                            false); // Disable the submit button
                        if (response.status === 'success') {
                            $('#currencyModal').modal('hide'); // Hide the modal
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                timer: 3000,
                            }).then(() => {
                                location.reload();
                            });
                            location.reload(); // Reload the page to see the updated currency
                        } else {
                            Swal.fire({
                                icon: 'error',
                                text: response.message,
                                timer: 3000,
                            }).then(() => {
                                // location.reload();
                            });
                            // Handle validation errors
                            // alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        // Handle AJAX errors
                        $('#currencySubmitBtn').attr('disabled',
                            false); // Enable the submit button

                        // Clear previous error messages
                        $('#currency_error').text('');

                        // Check if there are validation errors
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessage = '';

                            // Loop through the errors and display them
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] +
                                    '<br>'; // Concatenate error messages with line breaks
                            });

                            // Show the error messages in the designated area
                            $('#currency_error').html(errorMessage);
                        } else {
                            // Handle other types of errors (optional)
                            $('#currency_error').text(
                                'An unexpected error occurred. Please try again.');
                        }

                    }
                });
            });
        });
    </script>



    <script>
    $(document).ready(function () {
        $('#timezoneSubmitBtn').on('click', function (e) {
                e.preventDefault();
                let formData = $('#timezoneForm').serialize();

                $('#timezone_error').text('');

                $.ajax({
                    type: 'POST',
                url: '{{ route("settings.updateTimezone") }}',
                    data: formData,
                success: function (response) {
                        if (response.status === 'success') {
                            $('#timezoneModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message
                            });
                        }
                    },
                error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                        let errorMsg = errors.timezone ? errors.timezone[0] : 'Validation error';

                            $('#timezone_error').text(errorMsg);

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorMsg
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                text: 'Something went wrong. Please try again.'
                            });
                        }
                    }
                });
            });
        });
    </script>



@endsection
@section('content')
    <style>
        /* Set the map's size */
        #map {
            height: 400px;
            width: 100%;
        }

        /* Adjust the search input style */
        #searchInput {
            width: 100%;
            margin-bottom: 10px;
        }

        #editAddressNameId {
            width: 100%;
            margin-bottom: 10px;

        }

        #mapeditload {
            height: 400px;
            width: 100%;

        }

        .pac-container {
            z-index: 10000 !important;
            /* Set a high z-index for the autocomplete dropdown */
        }
    </style>
    <div class=" p-0 my-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/settings/business') }}">Settings </a></li>
            <li class="active"><span><b>Business Settings</b></span></li>
        </ol>
    </div>

    <div class="">
        <p class="text-muted">Change Your Profile and Business Settings</p>
    </div>

    <div class="row row-sm">
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="nav-icon ion ion-images mx-1"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="#" data-bs-target="#bLogo" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Logo</h5>
                                </a>
                                <p class="my-auto">{{ $accDetail->b_logo ? 'Added' : 'Not Added' }}</p>
                            </div>
                            <div class="my-auto"><a href="#" data-bs-target="#bLogo" data-bs-toggle="modal"><i
                                        class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="nav-icon fa fa-id-card-o"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="#" data-bs-target="#modaldemo4" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Category</h5>
                                </a>
                                <p class="my-auto">
                                    {{ isset($accDetail->fh_business_category) ? $accDetail->fh_business_category->m_name : '' }}
                                </p>
                                <p class="my-auto">{{-- $accDetail->b_name --}}</p>
                            </div>
                            <div class="my-auto"><a href="#" data-bs-target="#modaldemo4" data-bs-toggle="modal"><i
                                        class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Logo Upload --}}
            <div class="modal fade" id="bLogo" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content tx-size-sm">
                        <div class="modal-header border-0">
                            <h4 class="modal-title">Logo</h4><button aria-label="Close" class="btn-close"
                                data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form method="post" enctype="multipart/form-data" action="{{ route('account.update') }}"> @csrf
                            <p class=" fs-13 px-4 mt-3 pb-3 border-bottom " style="color: rgb(110, 104, 88)">Please upload
                                the logo of your business in png, jpg or jpeg formate, this logo will be visible in payment
                                slip.</p>
                            <div class="modal-body">
                                <input type="text" name="POST_TYPE" value="LOGO" hidden>
                                <h3 class="card-title">File Upload</h3>
                                <input type="file" name="image[]" class="dropify"
                                    data-default-file="{{ $accDetail->b_logo ? $accDetail->b_logo : '' }}" data-height="180"
                                    style="display: block; margin: 0 auto;" onchange="checkDocFormat(this)"
                                    accept=".jpg, .png, image/jpeg, image/png" />
                            </div>
                            <div class="modal-footer py-1">
                                <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                                <button type="submit" class="btn btn-outline-primary savebtn me-0">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <script>
                    function checkDocFormat(input) {
                        var file = input.files[0]; // Get the selected file
                        var filename = file.name; // Get the filename

                        var extension = filename.split('.').pop().toLowerCase();

                        var allowedFormats = ['png', 'jpeg', 'jpg'];

                        if (allowedFormats.indexOf(extension) === -1) {
                            console.log("Invalid file format!");
                            alert("Invalid file format! Please upload a PNG or JPEG file.");
                            $(input).val('');
                        }
                    }
                </script>
            </div>

            {{-- Business Name --}}
            <div class="modal fade" id="modaldemo4" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                    <div class="modal-content tx-size-sm">
                        <div class="modal-header">
                            <h4 class="modal-title">Category</h4><button aria-label="Close" class="btn-close"
                                data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form method="POST" action="{{ route('account.update') }}"> @csrf
                            <div class="modal-body">
                                <div class="form-group co-lg">
                                    <input type="text" name="POST_TYPE" value="CATEGORY" hidden>
                                    <label class="form-label mb-0 mt-2">Category <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control custom-select select2"
                                        data-placeholder="Select Department" name="business_category">
                                        <option label="Select Employee" value="{{-- $Bname->id --}}">
                                            {{-- $Bname->name --}}</option>
                                        @foreach ($businessCategory as $m_name => $m_id)
                                            <option value="{{ $m_id }}"
                                                @if ($accDetail->b_category_id == $m_id) selected @endif>{{ $m_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <label class="form-label mb-0 mt-2">Business Name*</label>
                                <input class="form-control" placeholder="Software Industry" type="text"
                                    name="business_name" value="{{ $accDetail->b_name }}" required> --}}
                            </div>
                            <div class="modal-footer py-1">
                                <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                                <button type="submit" class="btn btn-outline-primary savebtn me-0">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6" data-bs-backdrop="static" data-bs-backdrop="static">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon mdi mdi-account-edit"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-target="#nameUpdateModal" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Business Name ({{$accDetail->b_unique_id}})</h5>
                                </a>

                                <p class="my-auto" class="my-auto"> {{ \Str::limit($accDetail->b_name, 70) }}</p>
                            </div>
                            <div class="my-auto">
                                <a href="#" data-bs-target="#nameUpdateModal" data-bs-toggle="modal">
                                    <i class="fa fa-angle-double-right fs-20 my-auto mx-1 "></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--  Name --}}
            {{-- @foreach ($branch as $item) --}}

            <div class="modal fade" id="nameUpdateModal" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                    <div class="modal-content tx-size-sm">
                        <div class="modal-header">
                            <h4 class="modal-title ">Name</h4><button aria-label="Close" class="btn-close"
                                data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                                <span aria-hidden="true">×</span></button>
                        </div>
                        <form method="post" action="{{ route('account.update') }}"> @csrf
                            <div class="modal-body">
                                <p>You Can Update Your Business Name</p>
                                <input type="text" name="POST_TYPE" value="NAME" hidden>
                                <label class="form-label mb-0 mt-2">Name <span class="text-danger">*</span></label>
                                <input class="form-control" placeholder="Enter Name" type="text" name="business_name" maxlength="255"
                                    value="{{ $accDetail->b_name }}" required  pattern="[^.]*" title="Periods (.) are not allowed">
                            </div>
                            <div class="modal-footer py-1">
                                <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                                <button type="submit" class="btn btn-outline-primary savebtn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-phone mx-1"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-target="#bphone" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Phone Number</h5>
                                </a>
                                <p class="my-auto">
                                    {{ isset($accDetail->fh_admin) ? $accDetail->fh_admin->emp_phone : '' }}</p>
                            </div>
                            <div class="my-auto"> <a class="text-muted" href="#" data-bs-target="#bphone"
                                    data-bs-toggle="modal"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--  KYB --}}
            <div class="modal fade" id="bphone" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                    <div class="modal-content tx-size-sm">
                        <div class="modal-header ">
                            <h4 class="modal-title">Phone Number</h4>
                            <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                                <span aria-hidden="true">&times;</span></button>
                        </div>
                        <form method="POST" action="{{ route('account.update') }}"> @csrf
                            <div class="modal-body">
                                <p>You Can Update Your Phone Number To Continue</p>
                                <input type="text" name="POST_TYPE" value="PHONE" hidden>
                                <input type="text" name="adminID" value="{{ $accDetail->fh_admin->emp_id }}" hidden>
                                <label class="form-label mb-0 mt-2">Phone Number <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" id="phoneInput" placeholder="Enter Phone Number"
                                    type="text" name="phone" value="{{ $accDetail->fh_admin->emp_phone }}" required maxlength="10"
                                    minlength="10" required pattern="\d{10}" title="Please enter a valid 10-digit phone number">
                                <span id="error-message" style="color: red;"></span>
                            </div>
                            <div class="modal-footer py-1">
                                <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                                <button class="btn btn-outline-primary savebtn me-0" id="phoneNumberSubmitBtn"
                                    type="sumbit">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Get the input element by its id
            var phoneInput = document.getElementById('phoneInput');
            var phoneNumSubmitBtn = document.getElementById('phoneNumberSubmitBtn');

            // Get the error message span element by its id
            var errorMessage = document.getElementById('error-message');

            // Add an event listener for input changes
            phoneInput.addEventListener('input', function() {
                // Check if the input length is less than 10
                if (phoneInput.value.length < 10) {
                    // Display an error message
                    errorMessage.textContent = 'Error: Phone number must be 10 digits**';
                    phoneNumSubmitBtn.disabled = true;
                } else {
                    // Clear the error message if the input is valid
                    errorMessage.textContent = '';
                    phoneNumSubmitBtn.disabled = false;
                }
            });
        </script>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-envelope-o"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="#">
                                    <h5 class="my-auto text-dark">Email Address</h5>
                                </a>
                                <p class="my-auto">{{ $accDetail->fh_admin->emp_email }}</p>
                            </div>
                            <!-- <div class="my-auto"> <a href="#" data-bs-target="#emailupdateModal"
                                        data-bs-toggle="modal">
                                        <i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div> -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="emailupdateModal" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                    <div class="modal-content tx-size-sm">
                        <div class="modal-header border">
                            <h4 class="modal-title ">Email</h4><button aria-label="Close" class="btn-close"
                                data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form method="POST" action="{{ route('account.update') }}"> @csrf
                            <div class="modal-body">
                                <input type="text" name="POST_TYPE" value="EMAIL" hidden>
                                <input type="text" name="adminID" value="{{ $accDetail->fh_admin->emp_id }}" hidden>
                                <p>You Can Update Your Email To Continue</p>
                                <p class="my-auto" class="mb-0 pb-0 text-dark fs-13 mt-1 ">Email</p>
                                <input class="form-control" placeholder="Enter Email" type="email" name="email"
                                    value="{{ $accDetail->fh_admin->emp_email }}" readonly>
                            </div>
                            <div class="modal-footer py-1">
                                <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                                <button class="btn btn-outline-primary savebtn me-0" type="sumbit">Update</button>
                                {{-- <a href="#" class="btn btn-outline-primary btn-sm">Continue</a> --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6" data-bs-backdrop="static">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="nav-icon fa fa-suitcase"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="#" data-bs-target="#btypeModal" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Type</h5>
                                </a>
                                <p class="my-auto">
                                    {{ isset($accDetail->fh_business_type) ? $accDetail->fh_business_type->m_name : '' }}
                                </p>
                            </div>
                            <div class="my-auto"> <a href="#" data-bs-target="#btypeModal"
                                    data-bs-toggle="modal"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="btypeModal" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                    <div class="modal-content tx-size-sm">
                        <div class="modal-header">
                            <h4 class="modal-title">Type</h4><button aria-label="Close" class="btn-close"
                                data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form method="post" action="{{ route('account.update') }}"> @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" name="POST_TYPE" value="BUSINESS_TYPE" hidden>
                                    <label class="form-label mb-0 mt-2">Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control custom-select select2"
                                        data-placeholder="Select Department" name="business_type">
                                        <option label="Select Employee" value="{{-- $Btypename->id --}}">
                                            {{-- $Btypename->name --}}</option>
                                        @foreach ($businessType as $m_name => $m_id)
                                            <option value="{{ $m_id }}"
                                                @if ($accDetail->b_type_id == $m_id) selected @endif>{{ $m_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer py-1">
                                <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                                <button type="submit" class="btn btn-outline-primary savebtn me-0">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{--  Manage Business --}}

        </div>

        <div class="col-xl-6" data-bs-backdrop="static">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="nav-icon fa fa-map-signs"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="#">
                                </a>
                                <h5 class="my-auto text-dark">Business Address&nbsp; <i style="color:1877f2;"
                                        class="fa fa-flag"></i></h5>
                                <p class="my-auto">{{ $accDetail->b_address }} </p>
                            </div>
                            {{-- <div class="my-auto"> <a href="#"  id="create_template_btn" data-id=''
                                data-country='{{ $accDetail->b_country_id }}' data-state='{{ $accDetail->b_state_id }}'
                                data-city='{{ $accDetail->b_city_id }}' data-pin_code='{{ $accDetail->b_pin_code }}'
                                data-business_address='{{ $accDetail->b_address }}'
                                data-bs-target="#updateempmodal" data-bs-toggle="modal">
                                <i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div> --}}
                            <div class="my-auto"> <a href="#" onclick="openEditBusinessAddress(this)"
                                    id="create_template_btn" data-id='{{ $accDetail->b_id }}'
                                    data-country='{{ $accDetail->b_country_id }}'
                                    data-state='{{ $accDetail->b_state_id }}' data-city='{{ $accDetail->b_city_id }}'
                                    data-pin_code='{{ $accDetail->b_pin_code }}'
                                    data-business_address='{{ $accDetail->b_address }}'
                                    data-b_latitude='{{ $accDetail->b_latitude }}'
                                    data-b_longitude='{{ $accDetail->b_longitude }}' data-bs-target="#"
                                    data-bs-toggle="modal">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="nav-icon zmdi zmdi-receipt mx-1"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="#">
                                    <h5 class="my-auto text-dark">GSTIN</h5>
                                </a>
                                <p class="my-auto">{{ $accDetail->b_gst_no }}</p>
                            </div>
                            <div class="my-auto"> <a href="#" data-bs-target="#gstNumber" data-bs-toggle="modal">
                                    <i class="fa fa-percentage fs-20 my-auto"></i></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6" data-bs-backdrop="static" data-bs-backdrop="static">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon mdi mdi-account-edit"></i></span>
                        </div>
                        <div class="col-10  d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-target="#empCodeModal" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Employee Code</h5>
                                </a>
                                <p class="my-auto" class="my-auto">
                                    {{ isset($accDetail->fh_emp_code_type) ? $accDetail->fh_emp_code_type->m_name : '' }}
                                </p>
                            </div>
                            <div class="my-auto">
                                <a href="#" data-bs-target="#empCodeModal" data-bs-toggle="modal">
                                    <i class="fa fa-angle-double-right fs-20 my-auto mx-1 "></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--  Name --}}
            {{-- @foreach ($branch as $item) --}}

            <div class="modal fade" id="empCodeModal" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                    <div class="modal-content tx-size-sm">
                        <div class="modal-header">
                            <h4 class="modal-title ">Employee Code</h4><button aria-label="Close" class="btn-close"
                                data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                                <span aria-hidden="true">×</span></button>
                        </div>
                        <form method="POST" id="empForm" action="{{ route('account.update') }}"> @csrf
                            <div class="modal-body">
                                <input type="text" name="POST_TYPE" value="EMP_CODE" hidden>

                                <p>Please checked employee code type</p>
                                <div class="d-flex">
                                    @foreach ($empCodeType as $key => $item)
                                        <div class="me-2">
                                            <input type="radio" name="empCodeType" value="{{ $item }}"
                                                {{ $item == $accDetail->b_emp_code_type ? 'checked' : '' }}
                                                id="empCodeType{{ $key }}" onclick="showInputBox(this.value)"
                                                required>
                                            <label for="empCodeType{{ $key }}">{{ $key }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="inputBox"
                                    style=" display: {{ $accDetail->b_emp_code_type == '190' ? 'block' : 'none' }} ;">
                                    <label class="form-label mt-2">Emp Code <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="empCodeValue" id="empCodeValue"
                                        value="{{ $accDetail->b_emp_code }}" />
                                    <span class="text-danger" id="empCodeError"></span>
                                </div>
                            </div>
                            <div class="modal-footer py-1">
                                <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                                <button type="submit" class="btn btn-outline-primary savebtn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        {{-- --------------- for business ownerr name start  ----------------------- --}}
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-user mx-1"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-target="#bownername" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Administrator</h5>
                                </a>
                                <p class="my-auto">
                                    {{ isset($owenerName->emp_full_name) ? $owenerName->emp_full_name : '' }}</p>
                            </div>
                            <div class="my-auto"> <a class="text-muted" href="#" data-bs-target="#bownername"
                                    data-bs-toggle="modal"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--  KYB --}}
            <div class="modal fade" id="bownername" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                    <div class="modal-content tx-size-sm">
                        <div class="modal-header ">
                            <h4 class="modal-title">Administrator</h4>
                            <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                                <span aria-hidden="true">&times;</span></button>
                        </div>
                        <form method="POST" action="{{ route('account.update') }}"> @csrf
                            <div class="modal-body">
                                <p>You Can Update Your Administrator To Continue</p>
                                <input type="text" name="POST_TYPE" value="OWNER_NMAE" hidden>
                                <input type="text" name="adminID" value="{{ $owenerName->emp_id }}" hidden>
                                <label class="form-label mb-0 mt-2">Administrator<span
                                        class="text-danger">*</span></label>
                                <input class="form-control" id="ownerNamInput" placeholder="Enter Administrator"
                                    type="text" name="ownername" value="{{ $owenerName->emp_full_name }}" required>
                                <span id="error-message-owner" style="color: red;"></span>
                            </div>
                            <div class="modal-footer py-1">
                                <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                                <button class="btn btn-outline-primary savebtn me-0" id="ownerNameSubmitBtn"
                                    type="sumbit">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- Currency --}}
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-money mx-1"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-target="#currencyModal" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Currency</h5>
                                </a>
                                <p class="my-auto">
                                    {{ $accDetail->fh_currency->c_currency_code ?? 'Not Set' }} -
                                    {{ $accDetail->fh_currency->c_currency_symbol ?? '' }}
                                    ({{ $accDetail->fh_currency->c_name ?? '' }})
                                </p>
                            </div>
                            <div class="my-auto">
                                <a href="#" data-bs-target="#currencyModal" data-bs-toggle="modal">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        {{-- Time Zone  --}}

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-clock-o mx-1"></i>
                            </span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-target="#timezoneModal" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Time Zone</h5>
                                </a>
                                <p class="my-auto">
                                    {{ $accDetail->fh_timezone->zone_name ?? 'Not Set' }}
                                    ( {{ $accDetail->fh_timezone->offset ?? '' }})
                                </p>
                            </div>
                            <div class="my-auto">
                                <a href="#" data-bs-target="#timezoneModal" data-bs-toggle="modal">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Business Policy Document  --}}


        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-book mx-1"></i>

                            </span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Regulatory Document</h5>
                                </a>
                                <p class="my-auto">
                                    Business Policy ( {{ $folderIds }} )
                                </p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ route('regulatory.folder.index') }}">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

           <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-book mx-1"></i>

                            </span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-toggle="modal">
                                    <h5 class="my-auto text-dark">Bank Master</h5>
                                </a>
                                <p class="my-auto">
                                    Bank(s)
                                </p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ route('business.bank.index') }}">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
        
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-envelope"></i>
                            </span>
                        </div>
        
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#emailConfigModal">
                                    <h5 class="my-auto text-dark">Email Configuration</h5>
                                </a>
                            </div>
        
                            <div class="my-auto">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#emailConfigModal">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </a>
                            </div>
                        </div>
        
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Configuration Modal -->
        <div class="modal fade"
             id="emailConfigModal"
             tabindex="-1"
             aria-labelledby="emailConfigModalLabel"
             aria-hidden="true">

            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <form action="{{ route('settings.email.config.update') }}" method="POST">
                        @csrf

                        <input type="hidden"
                               name="POST_TYPE"
                               value="EMAIL_CONFIGURATION">

                        <div class="modal-header">
                            <h5 class="modal-title" id="emailConfigModalLabel">
                                Email Configuration
                            </h5>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close">
                            </button>
                        </div>

                        <div class="modal-body">

                            <div class="row">

                                <!-- Mail Driver -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Mail Driver <span class="text-danger">*</span>
                                    </label>

                                    <select class="form-control"
                                            name="mailer"
                                            id="mailer"
                                            required>

                                        <option value="">Select Driver</option>

                                        <option value="smtp"
                                            {{ old('mailer', $emailConfig->mailer ?? '') == 'smtp' ? 'selected' : '' }}>
                                            SMTP
                                        </option>

                                        <option value="sendmail"
                                            {{ old('mailer', $emailConfig->mailer ?? '') == 'sendmail' ? 'selected' : '' }}>
                                            Sendmail
                                        </option>

                                        <option value="mailgun"
                                            {{ old('mailer', $emailConfig->mailer ?? '') == 'mailgun' ? 'selected' : '' }}>
                                            Mailgun
                                        </option>

                                        <option value="ses"
                                            {{ old('mailer', $emailConfig->mailer ?? '') == 'ses' ? 'selected' : '' }}>
                                            Amazon SES
                                        </option>

                                        <option value="postmark"
                                            {{ old('mailer', $emailConfig->mailer ?? '') == 'postmark' ? 'selected' : '' }}>
                                            Postmark
                                        </option>

                                        <option value="log"
                                            {{ old('mailer', $emailConfig->mailer ?? '') == 'log' ? 'selected' : '' }}>
                                            Log
                                        </option>

                                        <option value="array"
                                            {{ old('mailer', $emailConfig->mailer ?? '') == 'array' ? 'selected' : '' }}>
                                            Array
                                        </option>

                                    </select>
                                </div>

                            </div>

                            <!-- SMTP -->
                            <div id="smtpFields" style="display:none;">
                                <div class="row">

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            SMTP Host
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               name="host"
                                               value="{{ old('host', $emailConfig->host ?? '') }}"
                                               placeholder="smtp.gmail.com">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            SMTP Port
                                        </label>

                                        <input type="number"
                                               class="form-control"
                                               name="port"
                                               value="{{ old('port', $emailConfig->port ?? '') }}"
                                               placeholder="587">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            Encryption
                                        </label>

                                        <select class="form-control"
                                                name="encryption">

                                            <option value="">
                                                Select Encryption
                                            </option>

                                            <option value="tls"
                                                {{ old('encryption', $emailConfig->encryption ?? '') == 'tls' ? 'selected' : '' }}>
                                                TLS
                                            </option>

                                            <option value="ssl"
                                                {{ old('encryption', $emailConfig->encryption ?? '') == 'ssl' ? 'selected' : '' }}>
                                                SSL
                                            </option>

                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            SMTP Username
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               name="username"
                                               value="{{ old('username', $emailConfig->username ?? '') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            SMTP Password
                                            @if($emailConfig)
                                                <small class="text-muted">
                                                    (Leave blank to keep existing password)
                                                </small>
                                            @endif
                                        </label>

                                        <input type="password"
                                               class="form-control"
                                               name="password"
                                               placeholder="Enter SMTP Password">
                                    </div>

                                </div>
                            </div>

                            <!-- Sendmail -->
                            <div id="sendmailFields" style="display:none;">
                                <div class="row">

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">
                                            Sendmail Path
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               name="sendmail_path"
                                               value="{{ old('sendmail_path', $emailConfig->sendmail_path ?? '/usr/sbin/sendmail -bs') }}"
                                               placeholder="/usr/sbin/sendmail -bs">
                                    </div>

                                </div>
                            </div>

                            <!-- Mailgun -->
                            <div id="mailgunFields" style="display:none;">
                                <div class="row">

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            Mailgun Domain
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               name="mailgun_domain"
                                               value="{{ old('mailgun_domain', $emailConfig->mailgun_domain ?? '') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            Mailgun Secret
                                        </label>

                                        <input type="password"
                                               class="form-control"
                                               name="mailgun_secret"
                                               placeholder="Enter Mailgun Secret">
                                    </div>

                                </div>
                            </div>

                            <!-- SES -->
                            <div id="sesFields" style="display:none;">
                                <div class="row">

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            AWS Key
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               name="ses_key"
                                               value="{{ old('ses_key', $emailConfig->ses_key ?? '') }}">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            AWS Secret
                                        </label>

                                        <input type="password"
                                               class="form-control"
                                               name="ses_secret"
                                               placeholder="Enter AWS Secret">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            AWS Region
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               name="ses_region"
                                               value="{{ old('ses_region', $emailConfig->ses_region ?? '') }}"
                                               placeholder="ap-south-1">
                                    </div>

                                </div>
                            </div>

                            <!-- Postmark -->
                            <div id="postmarkFields" style="display:none;">
                                <div class="row">

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">
                                            Postmark Token
                                        </label>

                                        <input type="password"
                                               class="form-control"
                                               name="postmark_token"
                                               placeholder="Enter Postmark Token">
                                    </div>

                                </div>
                            </div>

                            <!-- Common Fields -->
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        From Email Address
                                    </label>

                                    <input type="email"
                                           class="form-control"
                                           name="from_address"
                                           value="{{ old('from_address', $emailConfig->from_address ?? '') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        From Name
                                    </label>

                                    <input type="text"
                                           class="form-control"
                                           name="from_name"
                                           value="{{ old('from_name', $emailConfig->from_name ?? '') }}">
                                </div>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button"
                                    class="btn btn-outline-danger"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="btn btn-outline-primary">
                                {{ $emailConfig ? 'Update Configuration' : 'Save Configuration' }}
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

        <!-- JavaScript -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const mailer = document.getElementById('mailer');

                function toggleMailFields() {

                    document.getElementById('smtpFields').style.display = 'none';
                    document.getElementById('sendmailFields').style.display = 'none';
                    document.getElementById('mailgunFields').style.display = 'none';
                    document.getElementById('sesFields').style.display = 'none';
                    document.getElementById('postmarkFields').style.display = 'none';

                    switch (mailer.value) {

                        case 'smtp':
                            document.getElementById('smtpFields').style.display = 'block';
                            break;

                        case 'sendmail':
                            document.getElementById('sendmailFields').style.display = 'block';
                            break;

                        case 'mailgun':
                            document.getElementById('mailgunFields').style.display = 'block';
                            break;

                        case 'ses':
                            document.getElementById('sesFields').style.display = 'block';
                            break;

                        case 'postmark':
                            document.getElementById('postmarkFields').style.display = 'block';
                            break;
                    }
                }

                toggleMailFields();

                mailer.addEventListener('change', toggleMailFields);
            });
        </script>



        {{-- Time Zone Model  --}}

        <div class="modal fade" id="timezoneModal" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header">
                        <h4 class="modal-title">Time Zone</h4>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="timezoneForm">
                        @csrf
                        <input type="hidden" name="POST_TYPE" value="TIMEZONE">
                        <div class="modal-body">
                            <label class="form-label mb-0 mt-2">Time Zone <span class="text-danger">*</span></label>
                            <select name="timezone" id="timezone" class="form-control custom-select search_test" required>
                                <option value="">Select Time Zone</option>
                                @foreach ($time_zones as $zone)
                                    <option value="{{ $zone->tz_id }}" @if ($accDetail->b_timezone == $zone->tz_id) selected @endif>
                                        {{ $zone->zone_name }} (UTC {{ $zone->offset }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="timezone_error"></span>
                            @error('timezone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="modal-footer py-1">
                            <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                            <button type="button" class="btn btn-outline-primary savebtn me-0" id="timezoneSubmitBtn">Update</button>

                        </div>
                    </form>
                </div>
            </div>
        </div>



        {{-- Currency Modal --}}
        <div class="modal fade" id="currencyModal" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header">
                        <h4 class="modal-title">Currency</h4>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="currencyForm"> {{-- Changed to use AJAX --}}
                        @csrf
                        <input type="hidden" name="POST_TYPE" value="CURRENCY">
                        <div class="modal-body">
                            <label class="form-label mb-0 mt-2">Currency <span
                                    class="text-danger">*</span></label>
                            <select name="currency" id="currency" class="form-control custom-select search_test"
                                required>
                                <option value="">Select Currency</option>
                                @foreach ($country as $item)
                                    <option value="{{ $item->c_id }}" @if ($accDetail->b_currency == $item->c_id) selected @endif>
                                        {{ $item->c_currency_code }} - {{ $item->c_currency_symbol }}
                                        ({{ $item->c_name }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="currency_error"></span>
                            @error('currency')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="modal-footer py-1">
                            <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                            <button type="button" class="btn btn-outline-primary savebtn me-0"
                                id="currencySubmitBtn">Update</button> {{-- Changed to type="button" --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            // Get the input element by its id
            var ownerNamInput = document.getElementById('ownerNamInput');
            var phoneNumSubmitBtn = document.getElementById('ownerNameSubmitBtn');

            // Get the error message span element by its id
            var errorMessage = document.getElementById('error-message-owner');

            // Add an event listener for input changes
            ownerNamInput.addEventListener('input', function() {
                // Regular expression to allow only alphabets (both uppercase and lowercase) and spaces
                var regex = /^[A-Za-z\s]+$/;

                // Check if the input matches the regex and length is less than or equal to 10
                if (!regex.test(ownerNamInput.value) || ownerNamInput.value.length > 250) {
                    // Display an error message if invalid input is detected
                    errorMessage.textContent = 'Error: Only alphabets and spaces are allowed (max 250 characters).';
                    phoneNumSubmitBtn.disabled = true;
                } else {
                    // Clear the error message if the input is valid
                    errorMessage.textContent = '';
                    phoneNumSubmitBtn.disabled = false;
                }
            });
        </script>

        {{-- =--------------- for business ownerr name end  ------------------------ --}}



    </div>

    {{--  Email --}}

    {{--  Type --}}

    {{-- GST Number --}}
    <div class="modal fade" id="gstNumber" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2">GST Number</h4><button aria-label="Close" class="btn-close"
                        data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="col-lg">
                        <p class="mb-0 pb-0 text-dark fs-13 mt-1 ">GST Number</p>
                        <input class="form-control" placeholder="eg. 22XXXXXXXXA1Z5" type="text" required>
                        <p class="mb-0 pb-0 text-muted fs-12 mt-5 ">By continuing you agree to <a href="#"
                                class="text-primary">Terms & Conditions</a></p>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                    <button class="btn btn-outline-primary savebtn">Continue</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Business Account detail --}}
    <div class="modal fade" id="bAccName" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <div>
                        <h4 class="modal-title ms-2">Business Account Details</h4><button aria-label="Close"
                            class="btn-close" data-bs-dismiss="modal"></button><br />
                        <p class="mb-0 pb-0 fs-13 ms-2 " style="color: rgb(110, 104, 88)">Provide Business Acount
                            Detail
                            to
                            get Instant Refound in the
                            case of payout or transaction failures</p>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="col-lg">
                        <p class="mb-0 pb-0 text-dark fs-12 mt-5 ">Account Holder Name</p>
                        <input class="form-control" placeholder="Holder Name" type="text" required>

                        <p class="mb-0 pb-0 text-dark fs-12 mt-5 ">Account Number</p>
                        <input class="form-control" placeholder="Bank Account Number" type="password" required>

                        <p class="mb-0 pb-0 text-dark fs-12 mt-5 ">Confirm Account Number</p>
                        <input class="form-control" placeholder="Confirm Bank Account Number" type="text" required>

                        <p class="mb-0 pb-0 text-dark fs-12 mt-5 ">IFSC Code</p>
                        <input class="form-control" placeholder="IFSC Code" type="text" required>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                    <button class="btn btn-outline-primary savebtn">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- LARGE MODAL -->
    <div class="modal fade " id="updateempmodal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title">Business Address</h4><button aria-label="Close" type="reset"
                        class="btn-close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="post" action="{{ route('account.update') }}"> @csrf
                    <div class="modal-body">
                        <p>You Can Update Your Address To Continue</p>
                        <input type="text" name="POST_TYPE" value="ADDRESS" hidden>

                        {{-- <label class="form-label mb-0 mt-2">Country*</label>
                        <select name="country" id="getCountryId"
                            onchange="getState(this.value)"class=" form-control custom-select select2 w-100 border rounded"
                            required>
                            <option value="">Select Country Name</option>
                            @foreach ($country as $countryItem)
                                <option value="{{ $countryItem->c_id }}"
                                    @if ($accDetail->b_country_id == $countryItem->c_id) selected @endif>{{ $countryItem->c_name }}</option>
                            @endforeach
                        </select>

                        <label class="form-label mb-0 mt-2">State*</label>
                        <select name="state" id="getStateId" onchange="getCity(this.value)"
                            class="custom-select select2 form-control w-100 border rounded" required>
                            <option value="">Select State</option>
                            @foreach ($state as $item)
                                <option value="{{ $item->s_id }}"
                                    {{ $item->s_id == $accDetail->b_state_id ? 'selected' : '' }}>{{ $item->s_name }}
                                </option>
                            @endforeach
                        </select>

                        <label class="form-label mb-0 mt-2">City*</label>
                        <select id="getCityId" name="city"
                            class="custom-select select2 form-control w-100 border rounded" required>
                            <option value="">Select City</option>
                            @foreach ($city as $item)
                                <option value="{{ $item->ct_id }}"
                                    {{ $item->ct_id == $accDetail->b_city_id ? 'selected' : '' }}>{{ $item->ct_name }}
                                </option>
                            @endforeach
                        </select> --}}

                        <label class="form-label mb-0 mt-2">Zip Code <span class="text-danger">*</span></label>
                        <input class="form-control" placeholder="Zip Code" id="updatePinCode" name="pincode"
                            maxlength="6" type="text" value="{{ $accDetail->b_pin_code }}" required
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);">

                        {{-- <label class="form-label mb-0 mt-2">Address Line* &nbsp; <i style="color:1877f2;"
                                class="fa fa-flag"></i></label>
                        <textarea class="form-control" id="upateAddressLine" placeholder="Address Line 1" rows="3" name="address"
                            maxlength="200">{{ $accDetail->b_address }}</textarea> --}}

                        <label class="form-label mb-0 mt-2">Business Address <span class="text-danger">*</span></label>

                        <input class="form-control " id="editAddressNameId" type="text" placeholder="Address Name"
                            value="{{ $accDetail->b_address }}" maxlength="200" name="address" required>
                        <span class="text-danger" id="ulocation-name"></span>


                        <div class="row">
                            <div class="col-6">
                                <input class="form-control" type="text" id="longituder2" name="longitude"
                                    value="{{ $accDetail->b_longitude }}" placeholder="Longitude" readonly>
                                <span class="text-danger" id="ulongitude-name"></span>

                            </div>
                            <div class="col-6">
                                <input class="form-control" type="text" id="latituder2" name="latitude"
                                    value="{{ $accDetail->b_latitude }}" placeholder="Latitude" readonly>
                                <span class="text-danger" id="ulatitude-name"></span>

                            </div>
                        </div>
                        <div class="m-1" id="mapeditload"></div>

                    </div>
                    <div class="modal-footer d-flex py-1">
                        <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                        <button class="btn btn-outline-primary savebtn me-0" type="sumbit">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END LARGE MODAL -->

    {{-- Switch Business --}}
    <div class="col-xl-6" style="padding-left: 0px;">
        <div class="card custom-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-2 my-auto">
                        <span class="settings-icon bg-primary-transparent text-primary border-primary">
                            <i class="nav-icon mdi mdi-account-switch"></i>
                    </div>
                    <div class="col-10 d-flex justify-content-between">
                        <div class="my-auto">
                            <a href="#" data-bs-target="#switch_business" data-bs-toggle="modal">
                                <h5 class="my-auto text-dark">Switch Business</h5>
                            </a>
                            <p class="my-auto">
                            </p>
                        </div>
                        <div class="my-auto">
                            <a href="#" data-bs-target="#switch_business" data-bs-toggle="modal">
                                <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--
    <div class="modal fade" id="switch_business" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Switch Business</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Business Emails</label>
                        <div id="email-wrapper">
                            @if(!empty($switchEmails))
                                @foreach($switchEmails as $key => $email)
                                    <div class="d-flex mb-2 email-row">
                                        <input type="email" 
                                               name="emails[]" 
                                               class="form-control email-input" 
                                               value="{{ $email }}" 
                                               placeholder="Enter email">

                                        @if($key == 0)
                                            <button type="button" class="btn btn-success ms-2 add-email">+</button>
                                        @else
                                            <button type="button" class="btn btn-danger ms-2 remove-email">-</button>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="d-flex mb-2 email-row">
                                    <input type="email" name="emails[]" class="form-control email-input" placeholder="Enter email">
                                    <button type="button" class="btn btn-success ms-2 add-email">+</button>
                                </div>
                            @endif
                        </div>
                        <small id="email-error" class="text-danger"></small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" id="saveBusiness">Save</button>
                </div>

            </div>
        </div>
    </div>

    <script>

        let maxEmails = 5;

        //Validation
        $(document).on('input', '.email-input', function () {
            let value = $(this).val().trim();
            if (value === '') {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        //Add email
        $(document).on('click', '.add-email', function () {
            let count = $('.email-row').length;
            // Max limit
            if (count >= maxEmails) {
                $('#email-error').text('Maximum 5 emails allowed');
                return;
            }
            // Check empty fields before adding
            let isValid = true;
            $('.email-input').each(function () {
                if ($(this).val().trim() === '') {
                    $(this).addClass('is-invalid');
                    isValid = false;
                }
            });

            if (!isValid) {
                $('#email-error').text('Fill all emails first');
                return;
            }

            $('#email-error').text('');

            $('#email-wrapper').append(`
                <div class="d-flex mb-2 email-row">
                    <input type="email" name="emails[]" class="form-control email-input" placeholder="Enter email">
                    <button type="button" class="btn btn-danger ms-2 remove-email">-</button>
                </div>
            `);
        });

        //Remove email
        $(document).on('click', '.remove-email', function () {
            $(this).closest('.email-row').remove();
            $('#email-error').text('');
        });

        //Submit validation
        $('#saveBusiness').click(function () {

            let emails = [];
            let hasError = false;

            $('.email-input').each(function () {
                let value = $(this).val().trim();

                if (value === '') {
                    $(this).addClass('is-invalid');
                    hasError = true;
                } else {
                    $(this).removeClass('is-invalid');
                    emails.push(value);
                }
            });

            // Required check
            if (emails.length === 0) {
                $('#email-error').text('At least one email is required');
                return;
            }

            // Max check
            if (emails.length > 5) {
                $('#email-error').text('Max 5 emails allowed');
                return;
            }

            // Duplicate check
            let uniqueEmails = [...new Set(emails)];
            if (uniqueEmails.length !== emails.length) {
                $('#email-error').text('Duplicate emails not allowed');
                return;
            }

            $('#email-error').text('');

            // AJAX call
            $.ajax({
                url: '{{ route("save.switch.email")}}',
                type: 'POST',
                data: {
                    emails: emails,
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {

                    Swal.fire({
                        icon: res.status ? 'success' : 'error',
                        title: res.status ? 'Success' : 'Error',
                        text: res.message, // dynamic message
                        timer: 1500,
                        showConfirmButton: false
                    });

                    if (res.status) {
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function (xhr) {
                    let message = 'Email not saved!';
                    if (xhr.responseJSON) {

                        // First priority: custom message (like your own email error)
                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        // Second: Laravel validation errors
                        else if (xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors)[0][0];
                        }
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });
                }
            });
        });

        function showInputBox(value) {
            var inputField = document.getElementById("empCodeValue");
            if (value == '190') {
                document.getElementById("inputBox").style.display = "block";
                inputField.required = true;
            } else if (value == '191') {
                document.getElementById("inputBox").style.display = "none";
                inputField.required = false;
            }
        }
        let map;
        let editMap;
        let longitudeEdit;
        let latitudeEdit;
        let addressEdit;

        function openEditBusinessAddress(context) {
            var context = $(context);
            var id = context.data('id');
            var country = context.data('country');
            var state = context.data('state');
            var city = context.data('city');
            var pin_code = context.data('pin_code');
            var business_address = context.data('business_address');
            var longitude = context.data('b_longitude');
            var latitude = context.data('b_latitude');
            addressEdit = business_address;
            longitudeEdit = longitude;
            latitudeEdit = latitude;
            $('#editAddressNameId').val(addressEdit);

            $('#longituder2').val(longitude);
            $('#latituder2').val(latitude);
            $('#updateempmodal').modal('show');
        }

        function initMap() {
            // Create a map centered on a default location (you can change this)
            //
            const defaultLocation = {
                lat: 28.6139,
                lng: 77.2090
            };

            // Initialize the map
            map = new google.maps.Map(document.getElementById("map"), {
                center: defaultLocation,
                zoom: 12 // Set the initial zoom level
            });

            // Create a search box and link it to the UI element
            const input = document.getElementById("searchInput");
            const searchBox = new google.maps.places.SearchBox(input);

            // Bias the SearchBox results towards current map's viewport
            map.addListener("bounds_changed", function() {
                searchBox.setBounds(map.getBounds());
            });

            // Listen for the event fired when the user selects a prediction and retrieve more details
            searchBox.addListener("places_changed", function() {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
                    return;
                }

                // For each place, get the location and display it on the map
                const bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    if (!place.geometry) {
                        // console.log("Returned place contains no geometry");
                        return;
                    }

                    // Create a marker for each place
                    const marker = new google.maps.Marker({
                        map,
                        title: place.name,
                        position: place.geometry.location
                    });

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });

                // Fit the map to the bounds of the places found
                map.fitBounds(bounds);
                const selectedPlace = places[0]; // Assuming you are interested in the first place
                if (selectedPlace && selectedPlace.geometry && selectedPlace.geometry.location) {
                    const latitude = selectedPlace.geometry.location.lat();
                    const longitude = selectedPlace.geometry.location.lng();
                    document.getElementById('longituder1').value = longitude;
                    document.getElementById('latituder1').value = latitude;
                    // LoadAuto(latitude,longitude);
                }
            });
        }
        // only use edit set

        const mapModalEdit = document.getElementById('updateempmodal');
        mapModalEdit.addEventListener('shown.bs.modal', function() {

            // Initialize map after modal is shown
            const defaultLocation = {
                lat: latitudeEdit,
                lng: longitudeEdit
            };

            // Initialize the map
            const map = new google.maps.Map(document.getElementById("mapeditload"), {
                center: defaultLocation,
                zoom: 12 // Set the initial zoom level
            });

            // Create a search box and link it to the UI element
            const input = document.getElementById("editAddressNameId");
            const searchBox = new google.maps.places.SearchBox(input);

            // Bias the SearchBox results towards the map's viewport
            map.addListener("bounds_changed", function() {
                searchBox.setBounds(map.getBounds());
            });

            // Listen for the event fired when the user selects a prediction and retrieve more details
            searchBox.addListener("places_changed", function() {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
                    return;
                }

                // For each place, get the location and display it on the map
                const bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    if (!place.geometry) {
                        // console.log("Returned place contains no geometry");
                        return;
                    }

                    // Create a marker for each place
                    const marker = new google.maps.Marker({
                        map,
                        title: place.name,
                        position: place.geometry.location
                    });

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });

                // Fit the map to the bounds of the places found
                map.fitBounds(bounds);

                const selectedPlace = places[0]; // Assuming you are interested in the first place
                if (selectedPlace && selectedPlace.geometry && selectedPlace.geometry.location) {
                    const latitude = selectedPlace.geometry.location.lat();
                    const longitude = selectedPlace.geometry.location.lng();

                    // Update input fields with the selected location
                    document.getElementById('longituder2').value = longitude;
                    document.getElementById('latituder2').value = latitude;
                }
            });

            // currentEdit time value getset
            if (navigator.Geo - Location) {
                navigator.Geo - Location.getCurrentPosition(
                    function(position) {
                        const userLocation = {
                            lat: latitudeEdit,
                            lng: longitudeEdit
                        };

                        // Place a marker at the user's location
                        const marker = new google.maps.Marker({
                            position: userLocation,
                            map: map,
                            title: addressEdit
                        });

                        // Set map center to user's location
                        map.setCenter(userLocation);
                    },
                    // function() {
                    //     handleLocationError(true, map.getCenter());
                    // }
                );
            } else {
                // Browser doesn't support Geo-Location
                // handleLocationError(false, map.getCenter());
            }

            google.maps.event.addDomListener(window, 'load');
        });
    </script>
    --}}

    <div class="modal fade" id="switch_business" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" style="width: fit-content;">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-account-switch me-2"></i>Switch Business</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="mb-2 fw-semibold">Business Emails <span class="text-muted">(Max 5)</span></label>

                    <style>
                        .email-row {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            margin-bottom: 12px;
                            flex-wrap: wrap;
                        }
                        .email-input {
                            width: 280px;
                            height: 42px;
                        }
                        .btn-fixed {
                            min-width: 105px;
                            width: 105px;
                            height: 42px;
                        }
                        .btn-icon {
                            width: 42px;
                            height: 42px;
                            padding: 0;
                            font-size: 18px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }
                        .verified-badge {
                            min-width: 105px;
                            width: 105px;
                            height: 42px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            background: #28a745;
                            color: #fff;
                            border-radius: 6px;
                            font-size: 14px;
                            gap: 5px;
                            padding: 0 12px;
                            flex-shrink: 0;
                        }
                        .send-otp-btn {
                            min-width: 105px;
                            width: 105px;
                            height: 42px;
                            flex-shrink: 0;
                        }
                        .verify-otp-btn {
                            min-width: 105px;
                            width: 105px;
                            height: 38px;
                            flex-shrink: 0;
                        }
                        .cancel-otp-btn {
                            min-width: 105px;
                            width: 105px;
                            height: 38px;
                            flex-shrink: 0;
                        }
                        .add-email {
                            width: 42px;
                            height: 42px;
                            margin-left: 0;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }
                        .remove-email {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }
                        .otp-box {
                            display: flex;
                            gap: 10px;
                            align-items: center;
                            margin-bottom: 12px;
                            margin-left: 0;
                            padding: 10px;
                            background: #f8f9fa;
                            border-radius: 8px;
                            border-left: 3px solid #007bff;
                            flex-wrap: wrap;
                        }
                        .otp-input {
                            width: 180px;
                            height: 38px;
                            font-family: monospace;
                            letter-spacing: 2px;
                        }
                        .email-row.invalid .email-input {
                            border-color: #dc3545;
                            background-color: #fff0f0;
                        }
                        .loading-spinner {
                            display: inline-block;
                            width: 14px;
                            height: 14px;
                            border: 2px solid #fff;
                            border-radius: 50%;
                            border-top-color: transparent;
                            animation: spin 0.6s linear infinite;
                            margin-right: 5px;
                        }
                        @keyframes spin {
                            to { transform: rotate(360deg); }
                        }
                        .btn-primary:disabled {
                            cursor: not-allowed;
                            opacity: 0.65;
                        }
                        @media (max-width: 768px) {
                            .email-input {
                                width: 100%;
                            }
                            .otp-input {
                                width: 100%;
                            }
                            .btn-fixed, .send-otp-btn, .verified-badge {
                                width: auto;
                                min-width: 95px;
                            }
                            .otp-box {
                                flex-wrap: wrap;
                            }
                        }
                    </style>

                    <div id="email-wrapper">
                        {{-- OLD EMAILS --}}
                        @if(!empty($switchEmails))
                            @foreach($switchEmails as $email)
                                <div class="email-row mb-2" data-email="{{ $email }}">
                                    <input type="email" class="form-control email-input" value="{{ $email }}" disabled>
                                    <div class="verified-badge">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <small id="email-error" class="text-danger d-block mt-2"></small>
                    <div class="help-text mt-1">
                        <small class="text-muted"><i class="fas fa-info-circle"></i> Each email requires OTP verification. Click "Send OTP" → enter code → verify.</small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBusiness">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>

            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let verifiedEmails = @json($switchEmails ?? []);
            let maxEmails = 5;
            let otpStorage = {}; // Store OTP temporarily (in real app, backend handles this)
            
            // Helper: Show toast notification
            function showToast(message, type = 'success') {
                let toastContainer = $('#toast-container');
                if (!toastContainer.length) {
                    $('body').append('<div id="toast-container" style="position:fixed;bottom:20px;right:20px;z-index:9999"></div>');
                    toastContainer = $('#toast-container');
                }
                
                const toastId = 'toast_' + Date.now();
                const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                
                toastContainer.append(`
                    <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0 show" role="alert" style="min-width:250px;margin-top:10px">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas ${icon} me-2"></i> ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);
                
                setTimeout(() => {
                    $(`#${toastId}`).remove();
                }, 3000);
            }
            
            // Generate random OTP (6-digit)
            function generateOTP() {
                return Math.floor(100000 + Math.random() * 900000).toString();
            }
            
            // Reset + button (only on last verified row)
            function resetAddButtons() {
                $('.add-email').remove();
                
                let lastVerified = $('#email-wrapper .email-row').filter(function() {
                    return $(this).find('.verified-badge').length;
                }).last();
                
                if (lastVerified.length && $('#email-wrapper .email-row').length < maxEmails) {
                    lastVerified.append('<button type="button" class="btn btn-success btn-icon add-email"><i class="fas fa-plus"></i></button>');
                    $('#email-error').text('');
                } else if ($('#email-wrapper .email-row').length >= maxEmails) {
                    $('#email-error').text('Maximum 5 emails allowed');
                }
            }
            
            // Add new email row
            function addNewEmailRow() {
                if ($('#email-wrapper .email-row').length >= maxEmails) {
                    showToast('Maximum 5 emails allowed', 'error');
                    return;
                }
                
                $('#email-wrapper').append(`
                    <div class="email-row mb-2" data-state="unverified">
                        <input type="email" class="form-control email-input" placeholder="Enter email">
                        <button type="button" class="btn btn-primary btn-fixed send-otp-btn">
                            <i class="fas fa-paper-plane"></i> Send OTP
                        </button>
                        <button type="button" class="btn btn-danger btn-icon remove-email">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `);
                
                resetAddButtons();
            }
            
            // Send OTP
            function sendOTP(button) {
                let row = button.closest('.email-row');
                let email = row.find('.email-input').val().trim();
                
                if (!email) {
                    showToast('Please enter an email address', 'error');
                    row.find('.email-input').focus();
                    return;
                }
                
                if (!email.includes('@') || !email.includes('.')) {
                    showToast('Please enter a valid email address', 'error');
                    row.find('.email-input').focus();
                    return;
                }
                
                if (row.find('.verified-badge').length) {
                    showToast('This email is already verified', 'error');
                    return;
                }
                
                if (row.next('.otp-box').length) {
                    showToast('OTP already sent. Please check or cancel.', 'error');
                    return;
                }
                
                // Show loading state
                const originalHtml = button.html();
                button.html('<span class="loading-spinner"></span> Sending...');
                button.prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('send.email.otp') }}",
                    method: "POST",
                    data: {
                        email: email,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        button.html(originalHtml);
                        button.prop('disabled', false);
                        
                        if (response.status) {
                            row.after(`
                                <div class="otp-box">
                                    <input type="text" class="form-control otp-input" placeholder="Enter 6-digit OTP" maxlength="6" autocomplete="off">
                                    <button type="button" class="btn btn-primary verify-otp-btn">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                    <button type="button" class="btn btn-secondary cancel-otp-btn">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            `);
                            
                            showToast(response.message, 'success');
                            row.next('.otp-box').find('.otp-input').focus();
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        button.html(originalHtml);
                        button.prop('disabled', false);
                        let errorMsg = 'Failed to send OTP. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showToast(errorMsg, 'error');
                    }
                });
            }
            
            // Verify OTP
            function verifyOTP(button) {
                let otpBox = button.closest('.otp-box');
                let row = otpBox.prev('.email-row');
                let email = row.find('.email-input').val().trim();
                let otp = otpBox.find('.otp-input').val().trim();
                
                if (!otp) {
                    showToast('Please enter OTP', 'error');
                    return;
                }
                
                if (otp.length !== 6) {
                    showToast('OTP must be 6 digits', 'error');
                    return;
                }
                
                $.ajax({
                    url: "{{ route('verify.email.otp') }}",
                    method: "POST",
                    data: {
                        email: email,
                        otp: otp,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.status) {
                            if (!verifiedEmails.includes(email)) {
                                verifiedEmails.push(email);
                            }
                            
                            row.find('.send-otp-btn').remove();
                            row.find('.remove-email').remove();
                            row.find('.email-input').prop('disabled', true);
                            row.append('<div class="verified-badge"><i class="fas fa-check-circle"></i> Verified</div>');
                            otpBox.remove();
                            showToast(res.message, 'success');
                            resetAddButtons();
                        } else {
                            showToast(res.message, 'error');
                            otpBox.find('.otp-input').val('').focus();
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Verification failed. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showToast(errorMsg, 'error');
                    }
                });
            }
            
            // Cancel OTP
            function cancelOTP(button) {
                let otpBox = button.closest('.otp-box');
                let row = otpBox.prev('.email-row');
                let email = row.find('.email-input').val().trim();
                
                if (email && otpStorage[email]) {
                    delete otpStorage[email];
                }
                otpBox.remove();
                showToast('OTP verification cancelled', 'info');
            }
            
            // Remove email row
            function removeEmail(button) {
                let row = button.closest('.email-row');
                let email = row.find('.email-input').val();
                
                // Remove from verifiedEmails if present
                if (email && verifiedEmails.includes(email)) {
                    verifiedEmails = verifiedEmails.filter(e => e !== email);
                }
                
                // Remove associated OTP box
                let nextBox = row.next('.otp-box');
                if (nextBox.length) {
                    if (email) delete otpStorage[email];
                    nextBox.remove();
                }
                
                row.remove();
                showToast('Email removed', 'info');
                resetAddButtons();
                
                // If no rows left, add an empty row
                if ($('#email-wrapper .email-row').length === 0) {
                    addNewEmailRow();
                }
            }
            
            // Add new email (strict check)
            function handleAddEmail() {
                let lastRow = $('#email-wrapper .email-row').last();
                
                // Strict check: last row must be verified
                if (!lastRow.find('.verified-badge').length) {
                    $('#email-error').text('Please verify current email before adding new one');
                    showToast('Please verify current email before adding new one', 'error');
                    return;
                }
                
                if ($('#email-wrapper .email-row').length >= maxEmails) {
                    $('#email-error').text('Max 5 emails allowed');
                    showToast('Maximum 5 emails allowed', 'error');
                    return;
                }
                
                $('#email-error').text('');
                addNewEmailRow();
            }
            
            // Save all verified emails
            function saveEmails() {
                let allValid = true;
                let currentVerifiedEmails = [];
                
                $('#email-wrapper .email-row').each(function() {
                    let email = $(this).find('.email-input').val();
                    if (!email) return;
                    
                    if ($(this).find('.verified-badge').length) {
                        currentVerifiedEmails.push(email);
                        $(this).find('.email-input').removeClass('is-invalid');
                    } else {
                        allValid = false;
                        $(this).find('.email-input').addClass('is-invalid');
                        $(this).addClass('invalid');
                    }
                });
                
                if (!allValid) {
                    $('#email-error').text('Please verify all emails before saving');
                    showToast('Please verify all emails before saving', 'error');
                    return;
                }
                
                if (currentVerifiedEmails.length === 0) {
                    $('#email-error').text('At least one verified email is required');
                    showToast('At least one verified email is required', 'error');
                    return;
                }
                
                $('#saveBusiness').html('<span class="loading-spinner"></span> Saving...').prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('save.switch.email') }}",
                    method: "POST",
                    data: {
                        emails: currentVerifiedEmails,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        showToast(res.message, res.status ? 'success' : 'error');
                        
                        if (res.status) {
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            $('#saveBusiness').html('<i class="fas fa-save"></i> Save Changes').prop('disabled', false);
                            if (res.invalid_emails && res.invalid_emails.length) {
                                $('#email-error').text(res.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        $('#saveBusiness').html('<i class="fas fa-save"></i> Save Changes').prop('disabled', false);
                        let errorMsg = 'Failed to save. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                            $('#email-error').text(errorMsg);
                        }
                        showToast(errorMsg, 'error');
                    }
                });
            }
            
            // ============================================================
            // EVENT LISTENERS
            // ============================================================
            
            // Send OTP
            $(document).on('click', '.send-otp-btn', function() {
                sendOTP($(this));
            });
            
            // Verify OTP
            $(document).on('click', '.verify-otp-btn', function() {
                verifyOTP($(this));
            });
            
            // Cancel OTP
            $(document).on('click', '.cancel-otp-btn', function() {
                cancelOTP($(this));
            });
            
            // Remove email
            $(document).on('click', '.remove-email', function() {
                removeEmail($(this));
            });
            
            // Add email (strict)
            $(document).on('click', '.add-email', function() {
                handleAddEmail();
            });
            
            // Save button
            $('#saveBusiness').click(function() {
                saveEmails();
            });
            
            // Remove invalid class on input
            $(document).on('input', '.email-input', function() {
                $(this).removeClass('is-invalid');
                $(this).closest('.email-row').removeClass('invalid');
                $('#email-error').text('');
            });
            
            // Modal open: reset + button and add first row if needed
            $('#switch_business').on('shown.bs.modal', function() {
                if ($('#email-wrapper .email-row').length === 0) {
                    addNewEmailRow();
                } else {
                    resetAddButtons();
                }
                $('#email-error').text('');
                $('.email-input').removeClass('is-invalid');
                $('.email-row').removeClass('invalid');
            });
            
            // Modal close: cleanup
            $('#switch_business').on('hidden.bs.modal', function() {
                otpStorage = {};
            });
            
            // Initial setup
            resetAddButtons();
            
            // If no rows at all (no old emails and no new row), add one
            if ($('#email-wrapper .email-row').length === 0) {
                addNewEmailRow();
            }
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script> <!-- Load the Google Maps JavaScript API with your API key -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('credentials')['MAP_API_KEY'] }}&libraries=places&callback=initMap"
        async defer></script>
    <script src="{{ asset('https://code.jquery.com/jquery-3.6.0.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#empForm').on('submit', function(event) {
                // Prevent the form from submitting
                event.preventDefault();

                // Clear previous error messages
                $('#empCodeError').text('');
                const selectedValue = document.querySelector('input[name="empCodeType"]:checked').value;
                // Get form values
                var empCodeInput = $('#empCodeValue');

                // Check if the selected value is '190'
                var isValid = true;
                var empCode = $('#empCodeValue').val();

                if (selectedValue === '190') {
                    // Make the input field required
                    empCodeInput.prop('required', true);

                    // Validate empCode
                    if (!empCode) {
                        $('#empCodeError').text('Employee Code is required');
                        isValid = false;
                    } else if (empCode.length > 3) {
                        $('#empCodeError').text('Employee Code should not exceed 3 characters');
                        isValid = false;
                    } else if (!/^[a-zA-Z]+$/.test(empCode)) {
                        $('#empCodeError').text('Employee Prefix should contain only alphabets');
                        isValid = false;
                    }
                } else {
                    // Remove the required attribute
                    empCodeInput.prop('required', false);
                }

                // If the form is valid, submit it
                if (isValid) {
                    this.submit();
                }
            });
        });

        function getState(countryValue) {
            $stateId = $('#getStateId');
            $cityId = $('#getCityId');
            $.ajax({
                url: "{{ route('getCityStateCountry') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    country: countryValue
                },
                dataType: 'json',
                cache: true,
                success: function(result) {
                    var state = result.states;
                    $stateId.html('');
                    $cityId.html('');
                    var defaultOption = $('<option>').val('').text('Select State').attr('selected', true);
                    $stateId.append(defaultOption);

                    state.forEach(function(element) {
                        var option = $('<option>').val(element.s_id).text(element.s_name);
                        $stateId.append(option);
                    });
                }
            });
        }

        function getCity(stateValue) {
            $cityId = $('#getCityId');
            $.ajax({
                url: "{{ route('getCityStateCountry') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    state: stateValue
                },
                dataType: 'json',
                cache: true,
                success: function(result) {
                    var city = result.city;
                    var defaultOptioncity = $('<option>').val('').text('Select City').attr('selected', true);
                    $cityId.html('');
                    $cityId.append(defaultOptioncity);

                    city.forEach(function(element) {
                        var option = $('<option>').val(element.ct_id).text(element.ct_name);
                        $cityId.append(option);
                    });
                }
            });
        }

        $(document).ready(function() {
            // Initialize Select2 globally for elements with the class 'select2'
            $('.select2').select2();

            // Reinitialize Select2 when the modal is shown
            $('#addPolicyCategoryModal').on('shown.bs.modal', function() {
                // Destroy existing Select2 instance if it exists
                $('.select2').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                // Initialize Select2 again within the modal
                $('.select2').select2({
                    dropdownParent: $('#addPolicyCategoryModal')
                });
            });
        });
    </script>

    @if(session('swal_message'))
        <script>
        Swal.fire({
            icon: "{{ session('swal_icon') }}",
            title: "{{ session('swal_title') }}",
            text: "{{ session('swal_message') }}",
            confirmButtonText: 'OK'
        });
        </script>
    @endif
@endsection
