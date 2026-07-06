<?php
use App\Helpers\RolePermissionLogics;

$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    Branch Settings
@endsection

@section('css')
    <style>
        .rotate {
            transition: 500ms;
            transform: rotate(90deg);
            /* Adjust the desired rotation value */
        }

        .star-dot {
            color: red;
        }
    </style>

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

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "branch-table-dynamic",
                url: "{{ route('admin.branch') }}",
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

        $(document).ready(function() {
            // Initialize SumoSelect for the state dropdown
            $('#state').SumoSelect();

            $('#country').on('change', function() {

                const countryId = $(this).val(); // Get the selected country ID
                const stateDropdown = $('#state'); // Reference to state dropdown

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

            $('#edit_country').on('change', function() {

                const countryId = $(this).val(); // Get the selected country ID
                const stateDropdown = $('#edit_state'); // Reference to state dropdown
                const edit_state_hidden = $('#edit_state_hidden').val();
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
                                        `<option value="${state.id}" ${state.id == edit_state_hidden ? 'selected' : ''}>${state.name}</option>`
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
@endsection

@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/account') }}">Account Settings</a></li>
                    <li class="active"><span><b>Branch Settings</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <a id="addNewBranch" class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#branchName">Create Branch</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Branch</h4>
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

                        <div class="col-sm-1" style="margin-top: 30px;">
                            <div class="form-group filter_dots">
                                <button class="btn btn-info" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown"
                                    style="min-width: 220px;">
                                    @if ($permission->check_route_permission('admin/employee/form', 115))
                                        <li>
                                            <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                                data-bs-toggle="modal" data-bs-target="#branchBulkUpload">
                                                <i class="las la-file-upload"></i> Upload File
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                                href="{{ route('business.branch.downloadExcel') }}">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    @if (session('import_errors_blade'))
                        <div class="alert d-flex align-items-center mt-3">
                            <p>There were errors in the import. You can download the error file from the link below:</p>
                            <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()"
                                class="ms-2 mb-4 btn btn-danger">Download Error File</a>
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="branch-table-dynamic">
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

    <div class="modal fade" id="branchBulkUpload" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Branch</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('business.branch.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Upload File :</label>
                                    <input type="file" name="import_file" id="import_file" class="form-control"
                                        required accept=".xlsx, .csv">
                                    <br>
                                    <div style="display: flex; align-items: center;">
                                        <p class="fw-bold" style="margin: 0;">Note -</p>
                                        <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editBranchName" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2">Update Branch Settings</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="updateBranchFormId" action="{{ route('update.branch') }}"> 
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- LEFT SIDE (Form Fields) -->
                            <div class="col-lg-6 col-md-12">
                                <div class="p-2">
                                    <input type="hidden" id="editId" name="editBranchId">

                                    <p class="mb-0 text-dark fs-13 mt-1">Branch Name <span class="star-dot">*</span></p>
                                    <input class="form-control mb-2" id="editBranchNameId" placeholder="Branch Name" 
                                        type="text" name="branch" required>
                                    <span class="text-danger" id="ubranch-name-error"></span>

                                    <p class="mb-0 text-dark fs-13 mt-1">Branch Code <span class="star-dot">*</span></p>
                                    <input class="form-control mb-2" id="editBranchCode" name="code" 
                                        placeholder="Branch Code" type="text" required>
                                    <span class="text-danger" id="branch-code-error"></span>

                                    <p class="mb-0 text-dark fs-13 mt-1">Branch Email</p>
                                    <input class="form-control mb-2" id="editBranchEmailId" placeholder="Branch Email" 
                                        type="email" name="email">

                                    <x-select id="edit_country" name="edit_country" class="sumo_search" 
                                        label="Country" :options="$countries" selected="false" astric="true" required />
                                    <input type="hidden" id="edit_state_hidden">
                                    <x-select id="edit_state" name="edit_state" class="sumo_search" 
                                        label="State" :options="[]" selected="false" astric="true" required />

                                    <p class="mb-0 text-dark fs-13 mt-1">Google Address <span class="star-dot">*</span></p>
                                    <input class="form-control mb-2" id="editAddressNameId" 
                                        type="text" placeholder="Address Name" name="address" required>
                                    <span class="text-danger" id="ulocation-name"></span>

                                    <div class="row">
                                        <div class="col-6">
                                            <input class="form-control mb-2" type="text" id="longituder2" 
                                                name="longitude" placeholder="Longitude" readonly>
                                            <span class="text-danger" id="ulongitude-name"></span>
                                        </div>
                                        <div class="col-6">
                                            <input class="form-control mb-2" type="text" id="latituder2" 
                                                name="latitude" placeholder="Latitude" readonly>
                                            <span class="text-danger" id="ulatitude-name"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3 align-items-center">
                                        <div class="col-8">
                                            <p class="mb-0 text-dark fs-13 mt-1">Range Limit in meter</p>
                                            <input class="form-control" type="text" id="rangelimitID" 
                                                name="range_limit" placeholder="Set your range limit in meter">
                                        </div>
                                        <div class="col-4 d-flex justify-content-end">
                                            <label class="custom-switch mt-4">
                                                <input type="checkbox" name="is_active" 
                                                    class="custom-switch-input" id="isActiveCheckBox">
                                                <span class="custom-switch-indicator"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row mb-3 align-items-center">
                                        <div class="col-8">
                                            <p class="mb-0 text-dark fs-13 mt-1">Wifi MAC Address</p>
                                            <input class="form-control" type="text" id="wifiAddress" 
                                                name="wifi_address" placeholder="AA:BB:CC:DD:EE:FF">
                                        </div>
                                        <div class="col-4 d-flex justify-content-end">
                                            <label class="custom-switch mt-4">
                                                <input type="checkbox" name="is_wifi_restricted" 
                                                    class="custom-switch-input" id="isWifiRestrictedCheckBox">
                                                <span class="custom-switch-indicator"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <p class="mb-0 pb-0 text-muted fs-12 mt-4">
                                        By continuing you agree to 
                                        <a href="#" class="text-primary">Terms & Conditions</a>
                                    </p>
                                </div>
                            </div>

                            <!-- RIGHT SIDE (Google Map) -->
                            <div class="col-lg-6 col-md-12">
                                <div id="mapeditload" style="height:650px; width:100%; border:1px solid #007bff; border-radius:10px; overflow:hidden; box-shadow:0 0 10px rgba(0,123,255,0.2);"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="branchName" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2">Branch Settings</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="addBranchFormId" action="{{ route('add.branch') }}" onsubmit="return validateForm()">
                    <div class="modal-body">
                        <div class="row">
                            <!-- LEFT SIDE (Form Fields) -->
                            <div class="col-lg-6 col-md-12">
                                <div class="p-2">

                                    <p class="mb-0 text-dark fs-13 mt-1">Branch Name<span class="star-dot">*</span></p>
                                    <input class="form-control mb-2" name="branch" placeholder="Branch Name" type="text" required>
                                    <span class="text-danger" id="branch-name-error"></span>

                                    <p class="mb-0 text-dark fs-13 mt-1">Branch Code<span class="star-dot">*</span></p>
                                    <input class="form-control mb-2" name="code" placeholder="Branch Code" type="text" required>
                                    <span class="text-danger" id="branch-code-error"></span>

                                    <p class="mb-0 text-dark fs-13 mt-1">Branch Email</p>
                                    <input class="form-control mb-2" name="email" placeholder="Branch Email" type="email">

                                    <x-select id="country" name="country" class="sumo_search" label="Country"
                                        :options="$countries" selected="false" astric="true" required />

                                    <x-select id="state" name="state" class="sumo_search" label="State" :options="[]"
                                        selected="false" astric="true" required />

                                    <p class="mb-0 text-dark fs-13 mt-1">Google Address<span class="star-dot">*</span></p>
                                    <input class="form-control mb-2" type="text" id="searchInput" name="location"
                                        placeholder="Search Your location" required>
                                    <span class="text-danger" id="location-name"></span>

                                    <div class="row">
                                        <div class="col-6">
                                            <input class="form-control mb-2" type="text" id="longituder1" name="longitude"
                                                placeholder="Longitude" readonly required>
                                        </div>
                                        <div class="col-6">
                                            <input class="form-control mb-2" type="text" id="latituder1" name="latitude"
                                                placeholder="Latitude" readonly required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-8">
                                            <p class="mb-0 text-dark fs-13 mt-1">Range Limit in meter</p>
                                            <input class="form-control" type="text" name="range_limit"
                                                placeholder="Set your range limit in meter">
                                        </div>
                                        <div class="col-4 d-flex align-items-center justify-content-end">
                                            <label class="custom-switch mt-3">
                                                <input type="checkbox" name="is_active" class="custom-switch-input" id="isActiveCheckBox">
                                                <span class="custom-switch-indicator"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-8">
                                            <p class="mb-0 text-dark fs-13 mt-1">Wifi Address</p>
                                            <input class="form-control" type="text" name="wifi_address" placeholder="Enter your wifi address">
                                        </div>
                                        <div class="col-4 d-flex align-items-center justify-content-end">
                                            <label class="custom-switch mt-3">
                                                <input type="checkbox" name="is_wifi_restricted" class="custom-switch-input"
                                                    id="isWifiRestrictedCheckBox">
                                                <span class="custom-switch-indicator"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <p class="mb-0 pb-0 text-muted fs-12 mt-4">
                                        By continuing you agree to <a href="#" class="text-primary">Terms & Conditions</a>
                                    </p>
                                </div>
                            </div>

                            <!-- RIGHT SIDE (Google Map) -->
                            <div class="col-lg-6 col-md-12">
                                <div id="map" style="height:650px; width:100%; border:1px solid #007bff; border-radius:10px; overflow:hidden; box-shadow:0 0 10px rgba(0,123,255,0.2);"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        @csrf
                        <button type="reset" class="btn btn-outline-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function validateForm() {
            // Get the latitude and longitude values
            var latitude = document.getElementById('latituder1').value;
            var longitude = document.getElementById('longituder1').value;

            // Check if either latitude or longitude is empty
            if (latitude === '' || longitude === '') {
                alert('Please select a location on the map.');
                return false; // Prevent form submission
            }

            // If both latitude and longitude have values, allow the form submission
            return true;
        }
    </script>

    {{-- modal for delete confirmation --}}
    <div>
        <div class="modal fade" id="branchDeletebtn" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Confirm Deletion</h5>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="{{ route('delete.branch') }}" method="POST"> @csrf
                        <input type="text" id="branch_id" name="branch_id" hidden>
                        <div class="modal-body text-center">
                            <h4 class="mt-5">Are you sure want to Delete, <span class="text-primary"
                                    id="assign_branch">{{-- $item->branch_id ?? 0 --}}</span> branch ?</h4>
                        </div>
                        <div class="modal-footer">
                            <a class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</a>
                            <button type="submit" class="btn btn-outline-danger " id="">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // let map;
        // let editMap;
        // let longitudeEdit;
        // let latitudeEdit;
        // let addressEdit;

        function ItemDeleteModel(context) {
            var id = $(context).data('branch_id');
            var name = $(context).data('branch_name')
            $('#branch_id').val(id);
            $('#assign_branch').text(name);
        }

        function openEditDesignation(context) {
            var id = $(context).data('id');
            var branch_name = $(context).data('branch_name');
            var code = $(context).data('code');
            var branch_email = $(context).data('branch_email');
            var address = $(context).data('address');
            var longitude = $(context).data('longitude');
            var latitude = $(context).data('latitude');
            var isActive = $(context).data('isactive');
            var rangelimit = $(context).data('rangelimitset');
            var isWifiRestricted = $(context).data('iswifirestricted');
            var wifiAddress = $(context).data('wifiaddress');
            var editCountry = $(context).data('c_id');
            var editState = $(context).data('s_id');
            $('#edit_state_hidden').val(editState);

            if (editCountry) {
                $('#edit_country').val(editCountry);
                $('#edit_country')[0].sumo.reload(); // Reload SumoSelect to reflect the selected value
                $('#edit_country').trigger('change'); // Trigger change to fetch states
            }

            $('#editId').val(id);
            $('#editBranchNameId').val(branch_name);
            $('#editBranchCode').val(code);
            $('#editBranchEmailId').val(branch_email);
            $('#editAddressNameId').val(address);
            $('#longituder2').val(longitude);
            $('#latituder2').val(latitude);
            $('#rangelimitID').val(rangelimit);
            $('#isActiveCheckBox').prop('checked', isActive == 1);
            $('#wifiAddress').val(wifiAddress);
            $('#isWifiRestrictedCheckBox').prop('checked', isWifiRestricted == 1);

            addressEdit = address;
            longitudeEdit = longitude;
            latitudeEdit = latitude;
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
                    document.getElementById('longitude-name').innerHTML = '';
                    document.getElementById('latitude-name').innerHTML = '';
                    // console.log("Latitude:", latitude);
                    // console.log("Longitude:", longitude);

                    // LoadAuto(latitude,longitude);
                }
            });
        }

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script> <!-- Load the Google Maps JavaScript API with your API key -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('credentials')['MAP_API_KEY'] }}&libraries=places&callback=initMap"
        async defer></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // --- Configuration (change if your create modal IDs are different) ---
    const editModalId = 'editBranchName';
    const editMapDivId = 'mapeditload';
    const editAddressId = 'editAddressNameId';
    const editLatId = 'latituder2';
    const editLngId = 'longituder2';
    const editRangeId = 'rangelimitID';

    // Optional create modal IDs (change if you have create modal)
    const createModalId = 'createBranchModal'; // adjust if needed
    const createMapDivId = 'mapcreateload';
    const createAddressId = 'createAddressNameId';
    const createLatId = 'latituder';
    const createLngId = 'longituder';
    const createRangeId = 'rangeLimitCreate';

    // Default location if lat/lng missing
    const DEFAULT_LOCATION = { lat: 28.6139, lng: 77.2090 };

    // Keep instances per mapDiv so re-opening doesn't re-create messy maps
    const mapsStore = {}; // { mapDivId: { map, marker, circle, autocomplete } }

    // Wait until google.maps is available (polling, with timeout)
    // function waitForGoogleMaps(maxRetries = 40, interval = 100) {
    //     return new Promise((resolve, reject) => {
    //         let tries = 0;
    //         const id = setInterval(() => {
    //             tries++;
    //             if (window.google && window.google.maps && window.google.maps.places) {
    //                 clearInterval(id);
    //                 resolve();
    //             } else if (tries >= maxRetries) {
    //                 clearInterval(id);
    //                 reject(new Error('Google Maps JS not loaded after waiting.'));
    //             }
    //         }, interval);
    //     });
    // }

    function waitForGoogleMaps() {
        return new Promise((resolve, reject) => {
            if (window.google?.maps?.places) return resolve();
            const interval = setInterval(() => {
                if (window.google?.maps?.places) {
                    clearInterval(interval);
                    resolve();
                }
            }, 100);
            setTimeout(() => {
                clearInterval(interval);
                reject(new Error('Google Maps failed to load'));
            }, 10000);
        });
    }

    // Initialize or reuse a map in given container and bind to inputs
    // async function initMapForModal(mapDivId, addressInputId, latInputId, lngInputId, rangeInputId) {
    //     try {
    //         await waitForGoogleMaps();
    //     } catch (err) {
    //         console.error('Maps load error:', err);
    //         return;
    //     }

    //     const mapDiv = document.getElementById(mapDivId);
    //     const addressInput = document.getElementById(addressInputId);
    //     const latInput = document.getElementById(latInputId);
    //     const lngInput = document.getElementById(lngInputId);
    //     const rangeInput = document.getElementById(rangeInputId);

    //     if (!mapDiv || !addressInput) {
    //         console.warn('Map or address input not found for', mapDivId, addressInputId);
    //         return;
    //     }

    //     // If already initialized, just trigger resize & recenter
    //     if (mapsStore[mapDivId]) {
    //         const inst = mapsStore[mapDivId];
    //         // Update marker/circle positions from inputs if provided
    //         let lat = parseFloat(latInput?.value);
    //         let lng = parseFloat(lngInput?.value);
    //         if (!isNaN(lat) && !isNaN(lng)) {
    //             const pos = new google.maps.LatLng(lat, lng);
    //             inst.marker.setPosition(pos);
    //             inst.circle.setCenter(pos);
    //             inst.map.setCenter(pos);
    //         }
    //         // Update radius if present
    //         let r = parseFloat(rangeInput?.value);
    //         if (!isNaN(r)) inst.circle.setRadius(r);

    //         // Fix rendering inside modal
    //         google.maps.event.trigger(inst.map, 'resize');
    //         inst.map.setCenter(inst.circle.getCenter());
    //         return;
    //     }

    //     // Parse inputs or fallback to default
    //     let lat = parseFloat(latInput?.value);
    //     let lng = parseFloat(lngInput?.value);
    //     let position = (!isNaN(lat) && !isNaN(lng)) ? { lat, lng } : DEFAULT_LOCATION;

    //     // Create map
    //     const map = new google.maps.Map(mapDiv, {
    //         center: position,
    //         zoom: 16,
    //         mapTypeId: 'roadmap'
    //     });

    //     // Create marker (draggable)
    //     const marker = new google.maps.Marker({
    //         position,
    //         map,
    //         draggable: true
    //     });

    //     // Initial radius
    //     let radius = parseFloat(rangeInput?.value);
    //     if (isNaN(radius) || radius <= 0) radius = 100;

    //     // Create circle (editable center via marker; editable radius via built-in UI is limited)
    //     const circle = new google.maps.Circle({
    //         strokeColor: '#007bff',
    //         strokeOpacity: 0.9,
    //         strokeWeight: 2,
    //         fillColor: '#007bff',
    //         fillOpacity: 0.2,
    //         map,
    //         center: position,
    //         radius: radius,
    //         editable: true // allows resizing by dragging edge
    //     });

    //     // Bind circle center to marker
    //     circle.bindTo('center', marker, 'position');

    //     // Autocomplete (places)
    //     const autocomplete = new google.maps.places.Autocomplete(addressInput, { fields: ["geometry", "formatted_address", "name"] });
    //     autocomplete.bindTo('bounds', map);

    //     autocomplete.addListener('place_changed', function () {
    //         const place = autocomplete.getPlace();
    //         if (!place.geometry || !place.geometry.location) {
    //             console.warn('Place has no geometry', place);
    //             return;
    //         }
    //         const loc = place.geometry.location;
    //         marker.setPosition(loc);
    //         circle.setCenter(loc);
    //         if (place.geometry.viewport) map.fitBounds(place.geometry.viewport);
    //         else map.setCenter(loc);

    //         // Update lat/lng fields
    //         if (latInput) latInput.value = loc.lat().toFixed(6);
    //         if (lngInput) lngInput.value = loc.lng().toFixed(6);

    //         // Trigger resize fix
    //         google.maps.event.trigger(map, 'resize');
    //         map.setCenter(circle.getCenter());
    //     });

    //     // Marker drag updates inputs & circle center
    //     marker.addListener('dragend', function (ev) {
    //         const p = ev.latLng;
    //         if (latInput) latInput.value = p.lat().toFixed(6);
    //         if (lngInput) lngInput.value = p.lng().toFixed(6);
    //         circle.setCenter(p);
    //     });

    //     // Range input changes circle radius
    //     if (rangeInput) {
    //         rangeInput.addEventListener('input', function () {
    //             const v = parseFloat(this.value);
    //             if (!isNaN(v) && v > 0) {
    //                 circle.setRadius(v);
    //             }
    //         });
    //     }

    //     // When circle radius changed by user (drag edge), update input
    //     google.maps.event.addListener(circle, 'radius_changed', function () {
    //         const r = Math.round(circle.getRadius());
    //         if (rangeInput) rangeInput.value = r;
    //     });

    //     // Store instances for reuse
    //     mapsStore[mapDivId] = { map, marker, circle, autocomplete };

    //     // Workaround: ensure map renders correctly inside modal
    //     setTimeout(() => {
    //         google.maps.event.trigger(map, 'resize');
    //         map.setCenter(circle.getCenter());
    //     }, 300);
    // }

    async function initMapForModal(mapDivId, addressInputId, latInputId, lngInputId, rangeInputId = null) {
        try {
            await waitForGoogleMaps();
        } catch (err) {
            console.error(err);
            return;
        }

        const mapDiv = document.getElementById(mapDivId);
        const addressInput = document.getElementById(addressInputId);
        const latInput = document.getElementById(latInputId);
        const lngInput = document.getElementById(lngInputId);
        const rangeInput = rangeInputId ? document.getElementById(rangeInputId) : null;

        if (!mapDiv || !addressInput || !latInput || !lngInput) {
            console.warn('Missing required elements for map:', { mapDivId, addressInputId, latInputId, lngInputId });
            return;
        }

        // Reuse existing map if already created
        if (mapsStore[mapDivId]) {
            const inst = mapsStore[mapDivId];
            google.maps.event.trigger(inst.map, 'resize');

            // Update from current input values (important on Edit)
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            const radius = rangeInput ? parseFloat(rangeInput.value) || 100 : 100;

            if (!isNaN(lat) && !isNaN(lng)) {
                const pos = new google.maps.LatLng(lat, lng);
                inst.marker.setPosition(pos);
                inst.circle.setCenter(pos);
                inst.circle.setRadius(radius);
                inst.map.setCenter(pos);
                inst.map.setZoom(16);
            }
            return;
        }

        // Parse current coordinates
        let lat = parseFloat(latInput.value);
        let lng = parseFloat(lngInput.value);
        let center = (!isNaN(lat) && !isNaN(lng))
            ? { lat, lng }
            : DEFAULT_LOCATION;

        // Parse saved range (important for edit!)
        let savedRadius = rangeInput ? parseFloat(rangeInput.value) : 100;
        if (isNaN(savedRadius) || savedRadius <= 0) savedRadius = 100;

        // Create Map
        const map = new google.maps.Map(mapDiv, {
            center: center,
            zoom: 16,
            mapTypeId: 'roadmap'
        });

        // Marker (draggable)
        const marker = new google.maps.Marker({
            position: center,
            map: map,
            draggable: true,
            title: "Drag to adjust location"
        });

        // Range Circle (editable + shows saved radius on edit)
        const circle = new google.maps.Circle({
            strokeColor: '#007bff',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#007bff',
            fillOpacity: 0.15,
            map: map,
            center: center,
            radius: savedRadius,
            editable: true,
            draggable: true
        });

        // Sync circle center with marker
        circle.bindTo('center', marker, 'position');

        // Autocomplete Search
        const autocomplete = new google.maps.places.Autocomplete(addressInput, {
            fields: ["formatted_address", "geometry", "name"]
        });
        autocomplete.bindTo("bounds", map);

        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();
            if (!place.geometry?.location) return;

            const loc = place.geometry.location;
            map.setCenter(loc);
            map.setZoom(17);
            marker.setPosition(loc);
            circle.setCenter(loc);

            latInput.value = loc.lat().toFixed(6);
            lngInput.value = loc.lng().toFixed(6);
        });

        // Drag marker → update inputs + circle
        marker.addListener("dragend", (e) => {
            const pos = e.latLng;
            latInput.value = pos.lat().toFixed(6);
            lngInput.value = pos.lng().toFixed(6);
        });

        // Circle radius changed by dragging edge → update input
        circle.addListener("radius_changed", () => {
            const radius = Math.round(circle.getRadius());
            if (rangeInput) rangeInput.value = radius;
        });

        // Circle center dragged → update inputs
        circle.addListener("center_changed", () => {
            const center = circle.getCenter();
            latInput.value = center.lat().toFixed(6);
            lngInput.value = center.lng().toFixed(6);
        });

        // Range input → update circle
        if (rangeInput) {
            rangeInput.addEventListener("input", () => {
                const val = parseFloat(rangeInput.value);
                if (!isNaN(val) && val > 0) {
                    circle.setRadius(val);
                }
            });
        }

        // Store for reuse
        mapsStore[mapDivId] = { map, marker, circle, autocomplete };

        // Fix map size after modal opens
        setTimeout(() => {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(circle.getCenter());
        }, 300);
    }

    // Attach to Create Modal
    $('#branchName').on('shown.bs.modal', function () {
        initMapForModal('map', 'searchInput', 'latituder1', 'longituder1');
    });

    // Attach to Edit Modal ← This now shows the saved range circle!
    $('#editBranchName').on('shown.bs.modal', function () {
        initMapForModal('mapeditload', 'editAddressNameId', 'latituder2', 'longituder2', 'rangelimitID');
    });

    // Optional: Debug
    window.__debugMaps = mapsStore;

    // Helper to attach modal shown handler safely
    function attachModalHandler(modalId, mapDivId, addressId, latId, lngId, rangeId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            // console.log('Modal not found:', modalId);
            return;
        }
        modal.addEventListener('shown.bs.modal', function () {
            initMapForModal(mapDivId, addressId, latId, lngId, rangeId).catch(e => {
                console.error('initMapForModal error:', e);
            });
        });
    }

    // Attach to edit modal (your update modal)
    attachModalHandler(editModalId, editMapDivId, editAddressId, editLatId, editLngId, editRangeId);

    // Attach to create modal if exists (adjust IDs if you used different names)
    attachModalHandler(createModalId, createMapDivId, createAddressId, createLatId, createLngId, createRangeId);

    // Debug helper: show stored maps in console
    window.__mapsStore = mapsStore;

});
</script>

    <script>
        $('#addBranchId').on('input', function() {
            $('#branch-name-error').html('');
        });

        $('#addBranchEmailId').on('input', function() {
            $('#email-name-error').html('');
        });
        $('#addBranchAddressId').on('input', function() {
            $('#location-name').html('');
        });

        $('#editBranchNameId').on('input', function() {
            $('#ubranch-name-error').html('');
        });

        $('#editBranchCode').on('input', function() {
            $('#ubranch-code-error').html('');
        });

        $('#editBranchEmailId').on('input', function() {
            $('#uemail-name-error').html('');
        });
        $('#editAddressNameId').on('input', function() {
            $('#ulocation-name').html('');
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Success message
            @if (session('success'))
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    toast: true,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        toast: 'swal2-toast-green-glow'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif

            // Error message
            @if (session('error'))
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ session('error') }}',
                    toast: true,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif

            // Error messages (if multiple validation errors or custom messages are passed)
            @if ($errors->any())
                let errorMessages = '';
                @foreach ($errors->all() as $error)
                    errorMessages += '{{ $error }}' + '<br>';
                @endforeach
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: 'Validation Errors',
                    html: errorMessages, // Display the list of errors
                    toast: true,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif
        });

        document.getElementById('addNewBranch').addEventListener('click', function() {
            document.getElementById('addBranchFormId').reset();
            $('#branch-name-error').html('');
            $('#branch-code-error').html('');
            $('#email-name-error').html('');
            $('#location-name').html('');
            $('#longitude-name').html('');
            $('#latitude-name').html('');
        });
        $('#addBranchFormId').submit(function(e) {
            e.preventDefault();

            var url = $(this).attr("action");
            let formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                    // alert('Form submitted successfully');
                    
                    if (response.success) {
                        $('#branchName').modal('hide');
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
                    }
                },
                error: function(response) {
                    Swal.fire({
                        icon: 'error',
                        text: response.responseJSON.message,
                        timer: 3000,
                    });
                    var errors = response.responseJSON.errors;
                    if (errors.branch) {
                        $('#branch-name-error').text(errors.branch[0]);
                    }
                    if (errors.code) {
                        $('#branch-code-error').text(errors.code[0]);
                    }
                    if (errors.email) {
                        $('#email-name-error').text(errors.email[0]);
                    }
                    if (errors.location) {
                        $('#location-name').text(errors.location[0]);
                    }
                    if (errors.longitude) {
                        $('#longitude-name').text(errors.longitude[0]);
                    }
                    if (errors.longitude) {
                        $('#latitude-name').text(errors.latitude[0]);
                    }

                }
            });
        });
        $('#updateBranchFormId').submit(function(e) {
            e.preventDefault();
            var url = $(this).attr("action");
            let formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                    if (response.success) {
                        $('#editBranchName').modal('hide');
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
                    }
                },
                error: function(response) {
                    var errors = response.responseJSON.errors;
                    Swal.fire({
                        icon: 'error',
                        text: response.responseJSON.message,
                        timer: 3000,
                    });
                    if (errors.branch) {
                        $('#ubranch-name-error').text(errors.branch[0]);
                    }
                    if (errors.code) {
                        $('#ubranch-code-error').text(errors.code[0]);
                    }
                    if (errors.email) {
                        $('#uemail-name-error').text(errors.email[0]);
                    }
                    if (errors.address) {
                        $('#ulocation-name').text(errors.address[0]);
                    }
                    if (errors.longitude) {
                        $('#ulongitude-name').text(errors.longitude[0]);
                    }
                    if (errors.longitude) {
                        $('#ulatitude-name').text(errors.longitude[0]);
                    }

                }
            });
        });
    </script>
@endsection
