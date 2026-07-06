@extends('admin.layout.master')
@section('title', 'Candidates')
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

        #candidate-table tbody tr:hover {
            background-color: rgb(236, 236, 236);
            /* light gray background */
            transition: background-color 0.2s ease-in-out;
            cursor: pointer;
        }
    </style>
@endsection
@section('content')
    <input type="hidden" value="virendra k" id="hidden_type_val">
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/recruitment') }}">Recruitment</a></li>
                    <li class="active"><span><b>Candidates</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <x-button type="button" class="btn btn-outline-primary create-button" data-bs-toggle="modal"
                                    data-bs-target="#createCandidateModal" data-title="Create Candidate" hidden>
                                    Create Candidate
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bradcrumbs End --}}

    <x-modal id="createCandidateModal" title="Create Candidate" formId="createCandidateForm"
        action="{{ route('candidates.store') }}" method="POST" enctype="multipart/form-data" size="modal-lg"
        submitButtonText="Save Candidate" submitButtonId="saveCandidateButton">

        <div class="row">
            <!-- Personal Information Section -->
            <div class="profile-upload">
                @csrf
                <div class="image-container">
                    <img id="profile-image-preview" src="{{ asset('uploads/employee_profile/user.png') }}"
                        alt="Profile Image" class="profile-image" />
                    <button type="button" id="remove-image-btn" class="btn btn-outline-danger  d-none"
                        onclick="removeImage()">Remove</button>
                </div>
                <label for="profile-image-input" class="upload-label">
                    Upload Image
                </label>
                <input type="file" id="profile-image-input" name="profile_image"
                    accept="image/jpeg, image/jpg, image/png" class="d-none" onchange="uploadAndPreviewImage(event)" />
            </div>
            <div class="col-12 col-md-6">
                <x-input id="rc_name" name="rc_name" type="text" label="Name" placeholder="Name" maxlength="30"
                    astric="*" required />
            </div>
            <div class="col-12 col-md-6">
                <x-input id="rc_portfolio" name="rc_portfolio" type="text" label="Portfolio" placeholder="Portfolio"
                    maxlength="30" />
            </div>
            <div class="col-12 col-md-6">
                <x-input id="rc_email" name="rc_email" type="email" label="Email" placeholder="Email" maxlength="30"
                    astric="*" required />
            </div>
            <div class="col-12 col-md-6">
                <x-input id="rc_mobile" name="rc_mobile" type="text" label="Mobile" placeholder="Mobile" maxlength="15"
                    astric="*" required />
            </div>
            <div class="col-12 col-md-6">
                <x-input id="rc_dob" name="rc_dob" type="date" label="Date of Birth" placeholder="Date of Birth" />
            </div>
            <div class="col-12 col-md-6">
                <x-select id="rc_gender" name="rc_gender" class="sumo_search" label="Gender" astric="*"
                    :options="$gender" selected="false" astric="true" />
            </div>

            <!-- Recruitment & Job Position Section -->
            <div class="col-12 col-md-6">
                <x-select id="rc_recruitment_id" name="rc_recruitment_id" class="sumo_search" label="Recruitment"
                    :options="$recruitment" required />
            </div>
            <div class="col-12 col-md-6">
                <x-select id="rc_dg_id" name="rc_dg_id" class="sumo_search" label="Job Position" :options="$designations"
                    required />
            </div>

            <!-- Address & Location Section -->
            <div class="col-12 col-md-6">
                <x-textarea id="rc_address" label="Address" name="rc_address" placeholder="Address" required />
            </div>
            <div class="col-12 col-md-6">
                <x-select id="rc_source" name="rc_source" class="sumo_search" label="Source" :options="$country"
                    required />
            </div>
            <div class="col-12 col-md-6">
                <x-select id="rc_country" name="rc_country" class="sumo_search" label="Country" :options="$country"
                    required />
                <input type="hidden" id="rc_state_edit_value" name="rc_state_edit_value">
                {{-- <x-input id="rc_state_edit_value" name="rc_state_edit_value" type="hidden" label="hidden"
                    placeholder="hidden" /> --}}
            </div>
            <div class="col-12 col-md-6">
                <x-select id="rc_state" name="rc_state" class="sumo_search" label="State" :options="[]"
                    required />
            </div>
            <div class="col-12 col-md-6">
                <x-input id="rc_city" name="rc_city" type="text" label="City" placeholder="City" required />
            </div>
            <div class="col-12 col-md-6">
                <x-input id="rc_zip" name="rc_zip" type="text" label="Zip Code" placeholder="Zip Code" />
            </div>

            <!-- Resume & Referral Section -->
            {{-- <div class="col-12 col-md-6">
                <x-input id="rc_resume" name="rc_resume" type="file" label="Resume" placeholder="Resume" />
            </div> --}}
            <div class="col-12 col-md-6">
                <div class="oh-input__group">
                    <label class="oh-label " for="id_resume" title="">
                        Resume
                    </label>
                    <input type="hidden" id="rc_resume_edit" name="rc_resume_edit">
                    Currently: <a id="rc_resume_link" name="rc_resume_link" target="_blank" href="">show</a><br>
                    Change:
                    <input type="file" name="rc_resume" class="oh-input w-100 form-control" placeholder="rc_resume"
                        accept=".pdf" id="rc_resume">
                </div>
            </div>

            <div class="col-12 col-md-6">
                <x-select id="rc_referral" name="rc_referral" class="sumo_search" label="Referral" :options="$employees"
                    required />
            </div>
        </div>

    </x-modal>


    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Candidate List</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-1 col-sm-4">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length
                                    style="width: 100px">
                                    <option value="5" style="width: 100px">5</option>
                                    <option value="10" style="width: 100px">10</option>
                                    <option value="25" style="width: 100px">25</option>
                                    <option value="50" style="width: 100px">50</option>
                                    <option value="100" style="width: 100px">100</option>
                                </select>
                            </div>
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
                        <div class="col-md-8 col-sm-4"></div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <div class="form-group mb-3">
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="candidate-table">
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
@endsection
@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "candidate-table",
                url: "{{ route('candidates.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]'
            });
        });

        $(document).ready(function() {
            $('.sumo_search').SumoSelect({
                search: true,
                searchText: 'Search'
                // triggerChangeCombined: true,
            });
            // Initialize SumoSelect for the state dropdown

            $('#rc_country').on('change', function() {

                const countryId = $(this).val(); // Get the selected country ID
                const stateDropdown = $('#rc_state'); // Reference to state dropdown


                let rc_state_edit_value = $('#rc_state_edit_value').val();
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
                                if (rc_state_edit_value) {
                                    stateDropdown.val(
                                        rc_state_edit_value); // Set the selected state
                                    rc_state_edit_value = 0;

                                }
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

            preview.src = '/uploads/employee_profile/user.png'; // Reset to default image
            input.value = ''; // Clear the file input
            removeBtn.classList.add('d-none'); // Hide the remove button
        }
    </script>

    <script src="{{ asset('assets/js/ajax-handler.js') }}"></script>

@endsection
