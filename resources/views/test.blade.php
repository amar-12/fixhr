@extends('admin.layout.master')
@section('title')
    {{ 'Test Face Attendance' }}
@endsection
@section('css')
    <style>
        .profile-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ccc;
        }

        .upload-label {
            margin-top: 10px;
            cursor: pointer;
            color: #007bff;
        }

        .btn-danger {
            margin-top: 10px;
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header px-4">
                        <h4 class="card-title">Job Application Form</h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="POST" id="applicationForm" action="{{ route('application.form.submit') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="rc_recruitment" name="rc_recruitment" value="{{$decryptedId}}">
                            <div class="row">
                                <div class="profile-upload">
                                    @csrf
                                    <div class="image-container">
                                        <img id="profile-image-preview"
                                            src="{{ asset('uploads/employee_profile/user.png') }}" alt="Profile Image"
                                            class="profile-image" />
                                        <button type="button" id="remove-image-btn" class="btn btn-outline-danger  d-none"
                                            onclick="removeImage()">Remove</button>
                                    </div>
                                    <label for="profile-image-input" class="upload-label">
                                        Upload Image
                                    </label>
                                    <input type="file" id="profile-image-input" name="profile_image"
                                        accept="image/jpeg, image/jpg, image/png" class="d-none"
                                        onchange="uploadAndPreviewImage(event)" />
                                </div>


                                <x-input id="rc_name" name="rc_name" type="text" label="Name" placeholder="Name"
                                    maxlength="30" requried />
                                <x-select id="rc_dg_id" name="rc_dg_id" class="sumo_search" label="Job Position"
                                    :options="$designations" selected="false" astric="true" />
                                <div class="col-md-6">
                                    <x-input id="rc_email" name="rc_email" type="email" label="Email"
                                        placeholder="Email" maxlength="30" requried />
                                </div>
                                <div class="col-md-6">
                                    <x-input id="rc_mobile" name="rc_mobile" type="text" label="Mobile"
                                        placeholder="Mobile" maxlength="15" requried />
                                </div>
                                <x-input id="rc_resume" name="rc_resume" type="file" label="Resume" placeholder="Resume"
                                    maxlength="15" requried />

                                <x-select id="rc_gender" name="rc_gender" class="sumo_search" label="Gender"
                                    :options="$gender" selected="false" astric="true" />
                                <x-textarea id="rc_address" label="Address" name="rc_address" placeholder="Address"
                                    astric="*"  />

                                <div class="col-md-6">
                                    <x-select id="rc_country" name="rc_country" class="sumo_search" label="Country"
                                        :options="$country" selected="false" astric="true" />
                                </div>
                                <div class="col-md-6">
                                    <x-select id="rc_state" name="rc_state" class="sumo_search" label="State"
                                        :options="[]" selected="false" astric="true" />
                                </div>
                                <div class="col-md-6">
                                    <x-input id="rc_city" name="rc_city" type="text" label="City" placeholder="City"
                                        requried />
                                </div>

                                <div class="col-md-6">
                                    <x-input id="rc_zip" name="rc_zip" type="text" label="Zip Code"
                                        placeholder="Zip Code" requried />
                                </div>
                            </div>
                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-outline-primary btn-block">Apply</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header px-4">
                        <h4 class="card-title">Job Description</h4>
                    </div>
                    <div class="card-body">
                        {!! $recruitment->r_description ?? '' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function uploadAndPreviewImage(event) {
            const input = event.target;
            const file = input.files[0];
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            const preview = document.getElementById('profile-image-preview');
            const removeBtn = document.getElementById('remove-image-btn');

            if (file) {
                // Check file type
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Only JPEG, JPG, and PNG are allowed.');
                    input.value = '';
                    return;
                }

                // Preview the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    removeBtn.classList.remove('d-none'); // Show the remove button
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            const preview = document.getElementById('profile-image-preview');
            const input = document.getElementById('profile-image-input');
            const removeBtn = document.getElementById('remove-image-btn');

            preview.src = '/path-to-default-image.jpg'; // Reset to default image
            input.value = ''; // Clear the file input
            removeBtn.classList.add('d-none'); // Hide the remove button
        }

        $(document).ready(function() {
            // Initialize SumoSelect for the state dropdown
            $('#rc_state').SumoSelect();

            $('#rc_country').on('change', function() {

                const countryId = $(this).val(); // Get the selected country ID
                const stateDropdown = $('#rc_state'); // Reference to state dropdown

                if (countryId) {
                    // Clear previous options
                    stateDropdown.empty();

                    // Add loading indicator
                    stateDropdown.append('<option value="">Loading...</option>');

                    // AJAX call to fetch states
                    $.ajax({
                        url: '{{ route('get.states') }}', // Update with your route
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'), // Add CSRF token
                            countryId: countryId
                        },
                        beforeSend: function() {
                            // Show loading indicator
                            stateDropdown.append('<option value="">Loading...</option>');
                        },
                        success: function(response) {
                            // Clear any previous options
                            stateDropdown.empty();

                            // Check if states exist and populate the dropdown
                            if (response.states && response.states.length > 0) {
                                stateDropdown.append('<option value="">Select State</option>');
                                response.states.forEach(function(state) {
                                    stateDropdown.append(
                                        `<option value="${state.id}">${state.name}</option>`
                                    );
                                });
                            } else {
                                stateDropdown.append(
                                    '<option value="">No states available</option>');
                            }

                            // Refresh SumoSelect to reflect new options
                            stateDropdown[0].sumo.reload();
                        },
                        error: function() {
                            stateDropdown.empty();
                            stateDropdown.append(
                                '<option value="">Error loading states</option>');

                            // Refresh SumoSelect to reflect error state
                            stateDropdown[0].sumo.reload();
                        }
                    });
                } else {
                    stateDropdown.empty();
                    stateDropdown.append('<option value="">Select a country first</option>');

                    // Refresh SumoSelect to reflect new state
                    stateDropdown[0].sumo.reload();
                }
            });
        });
    </script>
    <script src="{{ asset('assets/js/ajax-handler.js') }}"></script>
@endsection
