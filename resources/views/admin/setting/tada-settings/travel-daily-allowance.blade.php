<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelMode;
use App\Models\PolicyTadaTravelType;
use App\Models\MasterTable;

$user = Auth::user();
$policyCategoryFilter = CommonUtils::getCustomModelData(new PolicyTadaCategory(), [['method' => 'where', 'args' => ['ptc_b_id', $user->fh_business->b_id]]]);
$travelTypeFilter = CommonUtils::getCustomModelData(new PolicyTadaTravelType(), [['method' => 'where', 'args' => ['pttt_b_id', $user->fh_business->b_id]]]);
$cityFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'CITY_TYPE']]]);
?>
@extends('admin.layout.master')

@section('title', 'Daily Allowance')

@section('content')

    {{-- Bradcrumbs Start --}}
        <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                          <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                    <li class="active"><span><b>Daily Allowance </b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addUniformBtn" data-bs-toggle="modal" data-bs-target="#DaModal">Add
                                    New</button>
                            </div>
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
                <div class="card-header d-flex">
                    <div>
                        <h4 class="card-title"><span>Daily Allowance</span></h4>
                    </div>
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

                        <div class="col-sm-6">
                        </div>




                        <div class="col-sm-1">
                            <button class="custom-button w-100" type="button" onclick="toggleFilters()"
                                style="margin-top: 28px;">
                                <!-- Custom SVG: 2 horizontal lines with knobs -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5">
                                    <!-- Top slider -->
                                    <line x1="3" y1="8" x2="21" y2="8"
                                        stroke-linecap="round" />
                                    <circle cx="10" cy="8" r="1.5" fill="currentColor" />

                                    <!-- Bottom slider -->
                                    <line x1="3" y1="16" x2="21" y2="16"
                                        stroke-linecap="round" />
                                    <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                </svg>
                                Filters
                            </button>
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
                                <button class="btn btn-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>

                                <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown" style="min-width: 220px;">
                                 
                                        <li>
                                            <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                                data-bs-toggle="modal" data-bs-target="#ExcelModal">
                                                <i class="las la-file-upload"></i> Upload File
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                                href="{{ route('daily-allowance.downloadExcel') }}">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li>
                                </ul>


                            </div>
                        </div>





                        <div class="row ">
                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="row">
                                    <div class="col-md">
                                        <label for="policyCategoryFilter" class="form-label">Policy Category</label>
                                        <select id="tada_policyCategoryFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($policyCategoryFilter as $pcF)
                                                <option value="{{ $pcF->ptc_id }}">{{ $pcF->ptc_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="travelTypeFilter" class="form-label">Travel Type</label>
                                        <select id="tada_travelTypeFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($travelTypeFilter as $travelTypeF)
                                                <option value="{{ $travelTypeF->pttt_id }}">
                                                    {{ $travelTypeF->fh_travel_type->m_name }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="cityFilter" class="form-label">Travel Mod</label>
                                        <select id="tada_cityFilter" data-filter class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($cityFilter as $cityF)
                                                <option value="{{ $cityF->m_id }}">{{ $cityF->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>




                                </div>
                            </div>
                        </div>


                        <script>
                            function toggleFilters() {
                                const container = document.getElementById('filterContainer');
                                container.style.display = container.style.display === 'none' ? 'flex' : 'none';
                            }
                        </script>



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

                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="travel-daily-allowance">
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

    <!-- Create/Update Modal -->
    <div class="modal fade" id="DaModal" tabindex="-1" role="dialog" aria-labelledby="largemodal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="DaModalLabel">Add Daily Allowance</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="dailyAllowanceForm">
                    <div class="modal-body"> @csrf
                        <input type="hidden" name="da_id" id="da_id">

                        <!-- Other Fields -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="policy_category">Policy Category <span
                                            class="text-danger">*</span></label>
                                    <select name="policy_category" id="policy_category"
                                        class="form-control custom-select select2 enableButton">
                                        <option value="" selected disabled>Select Policy Category </option>
                                        @foreach ($policyCategory as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="travel_type">Travel Type <span class="text-danger">*</span></label>
                                    <select name="travel_type" id="travel_type"
                                        class="form-control custom-select select2 enableButton">
                                        <option value="" selected disabled>Select Travel Type </option>
                                        @foreach ($travelTypes as $key => $ttype)
                                            <option value="{{ $ttype->pttt_id }}">{{ $ttype->fh_travel_type->m_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <!-- resources/views/components/select.blade.php -->
                                    <div class="form-group">
                                        <label class="" for="ptda_da_cal_type_id">Daily Allowance Type <span
                                                class="text-danger">*</span></label>
                                        <select name="ptda_da_cal_type_id" id="ptda_da_cal_type_id"
                                            class="form-control custom-select select2"
                                            onchange="dailyAllowanceType(this)">
                                            <option value="" selected disabled>Select DA Type</option>
                                            @foreach ($dailyAllowanceOption as $value => $option)
                                                <option value="{{ $value }}"
                                                    {{ isset($selected) && $selected == $value ? 'selected' : '' }}>
                                                    {{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label id="distance_range_1" style="display:none;">Range 1</label>
                                <div id="hourInputField" class="col-md" style="display:none;">
                                    <label for="hours">Hours <span class="text-danger">*</span> </label>
                                    <input type="number" id="hours" name="hours" min="0"
                                        placeholder="Enter Hours" class="form-control"
                                        oninput="validatePositiveNumber(this)">
                                </div>

                                <!-- KM Input Field -->
                                <div id="kmInputField1" class="col-md" style="display:none;">
                                    <label for="km1">KM From <span class="text-danger">*</span></label>
                                    <input type="number" id="km1" name="km1" min="0"
                                        placeholder="Enter KM From" class="form-control"
                                        oninput="validatePositiveNumber(this)">
                                </div>
                                <div id="kmInputField2" class="col-md" style="display:none;">
                                    <label for="km2">KM To <span class="text-danger">*</span></label>
                                    <input type="number" id="km2" name="km2" min="0"
                                        placeholder="Enter KM To" class="form-control"
                                        oninput="validatePositiveNumber(this)">
                                </div>
                                <div id="daAmountField" class="col-md" style="display:none;">
                                    <label for="daAmount">DA Amount <span class="text-danger">*</span> </label>
                                    <input type="number" id="daAmount" name="daAmount" min="0" maxlength="10"
                                        placeholder="Enter DA Amount" class="form-control"
                                        oninput="validatePositiveNumber(this)">
                                </div>

                            </div>

                            <div class="row">
                                <label id="distance_range_2" style="display:none;">Range 2</label>
                                <!-- KM Input Field -->
                                <div id="kmInputField3" class="col-md" style="display:none;">
                                    <label for="km3">KM From</label>
                                    <input type="number" id="km3" name="km3" min="0"
                                        placeholder="Enter KM From" class="form-control"
                                        oninput="validatePositiveNumber(this)">
                                </div>
                                <div id="kmInputField4" class="col-md" style="display:none;">
                                    <label for="km4">KM To</label>
                                    <input type="number" id="km4" name="km4" min="0"
                                        placeholder="Enter KM To" class="form-control"
                                        oninput="validatePositiveNumber(this)">
                                </div>
                                <div id="daAmountField2" class="col-md" style="display:none;">
                                    <label for="daAmount2">DA Amount </label>
                                    <input type="number" id="daAmount2" name="daAmount2" min="0" maxlength="10"
                                        placeholder="Enter DA Amount" class="form-control"
                                        oninput="validatePositiveNumber(this)">
                                </div>

                            </div>

                            <div class="row mt-2" id="additionalSettings" style="display:none;">
                                <div class="col-md">
                                    <label>
                                        <input type="checkbox" id="toggleSettings" name="toggleSettings"
                                            onclick="toggleAdditionalSettings()"> Show Additional Settings
                                    </label>
                                </div>
                            </div>

                            <div class="row">
                                <div id="distanceInputField" class="col-md-4" style="display:none;">
                                    <label for="distance">Distance <span class="text-danger">*</span> </label>
                                    <input type="number" id="distance" name="distance" min="0"
                                        placeholder="Enter Distance" class="form-control"
                                        oninput="validatePositiveNumber(this)">
                                </div>

                                <div id="lodgingInputField" class="col-md-4" style="display:none;">
                                    <label for="lodging_type">Lodging <span class="text-danger">*</span></label>
                                    <select name="lodging_type" id="lodging_type"
                                        class="form-control custom-select select2 enableButton">
                                        <option value="" selected disabled>Select Lodging </option>
                                        <option value="1">Applicable </option>
                                        <option value="0">Not Applicable </option>
                                    </select>
                                </div>

                                <div id="halfDaInputField" class="col-md-4" style="display:none;">
                                    <label for="half_da_type">Half DA <span class="text-danger">*</span></label>
                                    <select name="half_da_type" id="half_da_type"
                                        class="form-control custom-select select2 enableButton">
                                        <option value="" selected disabled>Select Half DA </option>
                                        <option value="1">Applicable </option>
                                        <option value="0">Not Applicable </option>
                                    </select>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-outline-danger " data-bs-dismiss="modal">Close</a>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Upload excel file -->
    <div class="modal fade" id="ExcelModal" tabindex="-1" role="dialog" aria-labelledby="largemodal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <h5 class="modal-title" id="DaModalLabel">Upload Daily Allowance File</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('daily-allowance.import') }}" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="upload_file">Upload File :</label>
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
                    <div class="modal-footer">
                        <a class="btn btn-outline-danger " data-bs-dismiss="modal">Close</a>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "travel-daily-allowance",
                url: "{{ route('admin.travel.daily-allowance') }}",
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

        function toggleAdditionalSettings() {
            var toggleCheckbox = document.getElementById('toggleSettings');
            var distanceInputField = document.getElementById('distanceInputField');
            var lodgingInputField = document.getElementById('lodgingInputField');
            var halfDaInputField = document.getElementById('halfDaInputField');

            let distance = $('#distance');
            let lodging_type = $('#lodging_type');
            let half_da_type = $('#half_da_type');

            if (toggleCheckbox.checked) {
                distanceInputField.style.display = 'block';
                lodgingInputField.style.display = 'block';
                halfDaInputField.style.display = 'block';

                // Add required attribute
                distance.attr('required', 'required');
                lodging_type.attr('required', 'required');
                half_da_type.attr('required', 'required');
            } else {
                distanceInputField.style.display = 'none';
                lodgingInputField.style.display = 'none';
                halfDaInputField.style.display = 'none';

                // Remove required attribute
                distance.removeAttr('required');
                lodging_type.removeAttr('required');
                half_da_type.removeAttr('required');

                // Reset values
                $('#distance').val('').trigger('change');
                $('#lodging_type').val('').trigger('change');
                $('#half_da_type').val('').trigger('change');
            }
        }

        $(document).ready(function() {
            // Handle form submission for create/update
            $('#dailyAllowanceForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#da_id').val();
                const policy_category = $('#policy_category').val();
                const travel_type = $('#travel_type').val();
                const daAmount = $('#daAmount').val();
                const ptda_da_cal_type_id = $('#ptda_da_cal_type_id').val();
                const distance = $('#distance').val();
                const lodging_type = $('#lodging_type').val();
                const half_da_type = $('#half_da_type').val();
                var toggleCheckbox = document.getElementById('toggleSettings');

                if (!policy_category) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select Policy Category',
                        timer: 3000,
                    });
                    $('#saveBtn').attr('disabled', false);
                    return;
                }
                if (!travel_type) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select Travel Type',
                        timer: 3000,
                    });
                    $('#saveBtn').attr('disabled', false);
                    return;
                }
                if (!ptda_da_cal_type_id) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select DA type',
                        timer: 3000,
                    });
                    $('#saveBtn').attr('disabled', false);
                    return;
                } else {
                    if (ptda_da_cal_type_id == '238') {

                    } else if (ptda_da_cal_type_id == '239') {
                        // console.log("Hours field and DA amount field show");
                        if (!$('#hours').val()) {
                            Swal.fire({
                                icon: 'error',
                                text: 'Please enter Hours',
                                timer: 3000,
                            });
                            $('#saveBtn').attr('disabled', false);
                            return;
                        }
                    } else if (ptda_da_cal_type_id == '140') {
                        // console.log("KM field and DA amount field show");
                        if (!$('#km1').val()) {
                            Swal.fire({
                                icon: 'error',
                                text: 'Please enter KM From',
                                timer: 3000,
                            });
                            $('#saveBtn').attr('disabled', false);
                            return;
                        }
                        if (!$('#km2').val()) {
                            Swal.fire({
                                icon: 'error',
                                text: 'Please enter KM To',
                                timer: 3000,
                            });
                            $('#saveBtn').attr('disabled', false);
                            return;
                        }
                    }
                    if (!daAmount) {
                        Swal.fire({
                            icon: 'error',
                            text: 'Please enter DA Amount',
                            timer: 3000,
                        });
                        $('#saveBtn').attr('disabled', false);
                        return;
                    }
                }

                if (toggleCheckbox.checked) {
                    if (!distance) {
                        Swal.fire({
                            icon: 'error',
                            text: 'Please enter Distance',
                            timer: 3000,
                        });
                        $('#saveBtn').attr('disabled', false);
                        return;
                    }

                    if (!lodging_type) {
                        Swal.fire({
                            icon: 'error',
                            text: 'Please select Lodging',
                            timer: 3000,
                        });
                        $('#saveBtn').attr('disabled', false);
                        return;
                    }

                    if (!half_da_type) {
                        Swal.fire({
                            icon: 'error',
                            text: 'Please select Half DA',
                            timer: 3000,
                        });
                        $('#saveBtn').attr('disabled', false);
                        return;
                    }
                }

                $.ajax({
                    url: "{{ route('create.update.daily-allowance') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#saveBtn').attr('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#DaModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
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
                    error: function(xhr, status, error) {
                        $('#saveBtn').attr('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            text: 'An error occurred while saving the data. Please try again later.',
                            timer: 3000,
                        });
                    }
                });
            });

            // Edit policy
            window.editDA = function(button) {
                // Retrieve data attributes from the button
                const id = $(button).data('id');
                const policyCategory = $(button).data('ptda_ptc_id');
                const travelType = $(button).data('ptda_pttt_id');
                const daAmount = $(button).data('ptda_da_amount');
                const daAmount2 = $(button).data('ptda_da_amount2');
                const dailyAllowanceType = $(button).data('ptda_da_cal_type_id');
                const limit = $(button).data('ptda_da_cal_limit')
                const distance = $(button).data('ptda_distance')
                const lodgingType = $(button).data('ptda_lodging')
                const halfDA = $(button).data('ptda_half_da')
                const valid = $(button).data('valid')

                // Populate modal form fields
                $('#da_id').val(id);
                $('#policy_category').val(policyCategory).trigger('change');
                $('#travel_type').val(travelType).trigger('change');
                $('#ptda_da_cal_type_id').val(dailyAllowanceType).trigger('change');
                $('#distance').val(distance);
                $('#lodging_type').val(lodgingType).trigger('change');
                $('#half_da_type').val(halfDA).trigger('change');
                if (dailyAllowanceType == 240) {
                    var values = limit.split('|');
                    var kmval1 = values[0]; // This will be '23'
                    var kmval2 = values[1]; // This will be '25'
                    var kmval3 = values[2] !== undefined ? values[2] : 0; // Check if undefined
                    var kmval4 = values[3] !== undefined ? values[3] : 0; // Check if undefined
                    console.log(kmval1, kmval2, kmval3, kmval4);
                    $('#km1').val(kmval1);
                    $('#km2').val(kmval2);
                    $('#km3').val(kmval3);
                    $('#km4').val(kmval4);
                    $('#daAmount2').val(daAmount2);
                } else if (dailyAllowanceType == 239) {
                    $('#hours').val(limit);
                }
                $('#daAmount').val(daAmount);

                if (distance !== undefined && distance !== '' &&
                    lodgingType !== undefined && lodgingType !== '' &&
                    halfDA !== undefined && halfDA !== '' && valid == true) {
                    $('#toggleSettings').prop('checked', true);
                } else {
                    $('#toggleSettings').prop('checked', false);
                }
                toggleAdditionalSettings();

                // Update modal title and show it
                $('#DaModalLabel').text('Update Daily Allowance');
                $('#saveBtn').html('Update');
                $('#DaModal').modal('show');
                $('#saveBtn').attr('disabled', false);
            }

            // Reset form when modal is closed
            $('#DaModal').on('hidden.bs.modal', function() {
                $('#toggleSettings').prop('checked', false).trigger('change');
                $('#additionalSettings').hide();
                toggleAdditionalSettings();
                $('#policy_category').val('').trigger('change');
                $('#travel_type').val('').trigger('change');
                $('#lodging_type').val('').trigger('change');
                $('#half_da_type').val('').trigger('change');
                $('#ptda_da_cal_type_id').val('').trigger('change');
                $('#da_id').val('');
                $('#distance').val('');
                $('#DaModalLabel').text('Add Daily Allowance');
                $('#saveBtn').html('Save');
                $('#saveBtn').attr('disabled', false);
            });

            // Delete policy
            // window.deletePolicy = function(id) {
            //     if (confirm('Are you sure you want to delete this policy?')) {
            //         $.ajax({
            //             url: `/policies/${id}`,
            //             method: 'DELETE',
            //             success: function() {
            //                 alert('Policy deleted successfully!');
            //                 fetchPolicies();
            //             }
            //         });
            //     }
            // };

            $(document).on('click', '.deleteDA', function() {
                const daId = $(this).data('id');
                var url = "{{ route('delete.daily-allowance') }}";
                Swal.fire({
                    title: 'Are you sure ?',
                    text: 'You will not be able to recover this daily allowance!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                'id': btoa(daId),
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Daily Allowance has been deleted successfully.',
                                        icon: 'success',
                                        timer: 3000, // 3 seconds
                                        timerProgressBar: true,
                                        showConfirmButton: false,
                                        didClose: () => {
                                            location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        text: response.message,
                                        timer: 3000,
                                    });
                                }
                            }
                        });
                    }
                });
            });

            $('.select2').select2();
            // // Initialize Select2 on modal shown
            $('#DaModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#DaModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });

            $('.enableButton').on('change', function() {
                if ($(this).val() !== '') {
                    $('#saveBtn').prop('disabled', false);
                } else {
                    $('#saveBtn').prop('disabled', true);
                }
            });
        });

        function validatePositiveNumber(input) {
            if (input.value < 0) {
                input.value = 0; // Reset to 0 if a negative number is entered
            }
        }

        function dailyAllowanceType(e) {
            var selectedValue = e.value;

            var distance_range_1 = document.getElementById('distance_range_1');
            var distance_range_2 = document.getElementById('distance_range_2');
            var daAmount2 = document.getElementById('daAmount2');
            // Get the elements for the fields
            const additionalSettings = document.getElementById('additionalSettings');
            var daAmountField = document.getElementById('daAmountField');
            var hourInputField = document.getElementById('hourInputField');
            // var distanceInputField = document.getElementById('distanceInputField');
            // var lodgingInputField = document.getElementById('lodgingInputField');
            // var halfDaInputField = document.getElementById('halfDaInputField');
            var kmInputField1 = document.getElementById('kmInputField1');
            var kmInputField2 = document.getElementById('kmInputField2');

            // Hide all fields initially
            daAmountField.style.display = 'none';
            hourInputField.style.display = 'none';
            // distanceInputField.style.display = 'none';
            // lodgingInputField.style.display = 'none';
            // halfDaInputField.style.display = 'none';
            kmInputField1.style.display = 'none';
            kmInputField2.style.display = 'none';
            // Set values of input fields to
            document.getElementById('daAmount').value = ''; // Clear DA Amount field
            document.getElementById('hours').value = ''; // Clear Hour Input field
            document.getElementById('km1').value = ''; // Clear first KM Input field
            document.getElementById('km2').value = ''; // Clear second KM Input field

            distance_range_1.style.display = 'none';
            distance_range_2.style.display = 'none';
            document.getElementById('km3').value = '';
            document.getElementById('km4').value = '';
            document.getElementById('daAmount2').value = ''; // Clear DA Amount field

            // Show relevant fields based on the selected value
            var isChecked = document.getElementById('toggleSettings').checked;
            if (selectedValue === '238') {
                daAmountField.style.display = 'block'; // Show only DA Amount
                distanceInputField.style.display = 'none';
                lodgingInputField.style.display = 'none';
                halfDaInputField.style.display = 'none';
                additionalSettings.style.display = 'none';

                if (isChecked) {
                    distanceInputField.style.display = 'none';
                    lodgingInputField.style.display = 'none';
                    halfDaInputField.style.display = 'none';
                }
                distance_range_1.style.display = 'none';
                distance_range_2.style.display = 'none';
                kmInputField3.style.display = 'none';
                kmInputField4.style.display = 'none';
                daAmountField2.style.display = 'none';
            } else if (selectedValue === '239') {
                daAmountField.style.display = 'block'; // Show DA Amount and Hour Input
                hourInputField.style.display = 'block';
                additionalSettings.style.display = 'block';

                if (isChecked) {
                    distanceInputField.style.display = 'block';
                    lodgingInputField.style.display = 'block';
                    halfDaInputField.style.display = 'block';
                } else {
                    distanceInputField.style.display = 'none';
                    lodgingInputField.style.display = 'none';
                    halfDaInputField.style.display = 'none';
                }
                distance_range_1.style.display = 'none';
                distance_range_2.style.display = 'none';
                kmInputField3.style.display = 'none';
                kmInputField4.style.display = 'none';
                daAmountField2.style.display = 'none';
            } else if (selectedValue === '240') {
                daAmountField.style.display = 'block'; // Show DA Amount, Hour, and KM Input
                kmInputField1.style.display = 'block';
                kmInputField2.style.display = 'block';
                distance_range_1.style.display = 'block';
                distance_range_2.style.display = 'block';
                kmInputField3.style.display = 'block';
                kmInputField4.style.display = 'block';
                daAmountField2.style.display = 'block';
                distanceInputField.style.display = 'none';
                lodgingInputField.style.display = 'none';
                halfDaInputField.style.display = 'none';
                additionalSettings.style.display = 'none';

                if (isChecked) {
                    distanceInputField.style.display = 'none';
                    lodgingInputField.style.display = 'none';
                    halfDaInputField.style.display = 'none';
                }
            }
        }

        //excel upload file sweet alert
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
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

        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif
    </script>
@endsection
