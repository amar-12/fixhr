<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;

$user = Auth::user();
$travelTypeFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'TRAVEL_TYPE']]]);
$travelModeFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'TRAVEL_MODE']]]);
$travelVehiclesFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'VEHICLE']]]);
$policyCategory = CommonUtils::getCustomModelData(new PolicyTadaCategory(), [['method' => 'where', 'args' => ['ptc_b_id', $user->emp_b_id]], ['method' => 'where', 'args' => ['ptc_status', 1]]]);
// $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->where('ptc_status', 1)->get();
?>
@extends('admin.layout.master')

@section('title', 'Travel Vehicle')

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "travel-vehicle-table-dynamic",
                url: "{{ route('admin.travel.vehicle') }}",
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
    </script>
@endsection

@section('content')
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                      <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                    <li class="active"><span><b> Travel Vehicle & Allowance</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" 
                                        data-bs-toggle="modal" onclick="openAddTravelVehicle();" data-bs-target="#addTravelVehicleModal">Add New
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bradcrumbs End --}}

    @if (session('success_html'))
        <div class="alert alert-success" id="success-message" style="width: 35%; margin-left: 1032px; margin-top: 10px;">
            {!! session('success_html') !!}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" id="error-message" style="width: 35%; margin-left: 1032px; margin-top: 10px;">
            {{ session('error') }}
        </div>
    @endif


    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div>
                        <h4 class="card-title"><span>Travel Vehicle & Allowance</span></h4>
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
                                <button class="btn btn-info" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>

                                 <ul class="dropdown-menu p-2" aria-labelledby="travelVehicleDropdown" style="min-width: 220px;">
                   
                                <li>
                                    <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                        data-bs-toggle="modal" data-bs-target="#addTravelVehicleModalFile">
                                        <i class="las la-file-upload"></i> Upload File
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                        href="{{ route('vehicle.downloadExcel') }}">
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
                                        <label for="travelTypeFilter" class="form-label">Travel Type</label>
                                        <select id="travel-vehicletravelTypeFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($travelTypeFilter as $travelTypeF)
                                                <option value="{{ $travelTypeF->m_id }}">{{ $travelTypeF->m_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="travelModeFilter" class="form-label">Travel Mode</label>
                                        <select id="travel-vehicletravelModeFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($travelModeFilter as $trvelModelF)
                                                <option value="{{ $trvelModelF->m_id }}">{{ $trvelModelF->m_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="travelVehiclesFilter" class="form-label">Travel Vehicle</label>
                                        <select id="travel-vehicletravelVehiclesFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($travelVehiclesFilter as $tVF)
                                                <option value="{{ $tVF->m_id }}">{{ $tVF->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="policyCategoryFilter" class="form-label">Policy Category</label>
                                        <select id="travel-vehiclepolicyCategoryFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($policyCategory as $item)
                                                <option value="{{ $item->ptc_id }}">
                                                    {{ $item->ptc_name }}
                                                </option>
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
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="travel-vehicle-table-dynamic">
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


    {{-- for model file upload strat --}}
    <div class="modal fade" id="addTravelVehicleModalFile" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Travel Vehicle File</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('vehicle.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="text" id="editVehicalId" name="editTravelVehicle" hidden>
                            <input type="text" id="travelMode" hidden>
                            <input type="text" id="travelVehicle" hidden>
                            <input type="text" id="travelClass" hidden>
                            <input type="text" id="travelOwner" hidden>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Upload File :</label>
                                    <input type="file" name="import_file" id="import_file" class="form-control"
                                        accept=".xlsx, .csv" required>

                                </div>
                            </div>
                            <br>
                            <div style="display: flex; align-items: center;">
                                <p class="fw-bold" style="margin: 0;">Note -</p>
                                <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer d-flex justify-content-end">@csrf
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn"
                            id="bulkImportVehicle">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- for model file upload end --}}

    <div class="modal fade" id="addTravelVehicleModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Create Travel Vehicle</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="addTravelVehicleForm" action="{{ route('admin.save.travel.vehicle') }}">
                    <div class="modal-body">
                        <div class="row">
                            <input type="text" id="editId" name="editTravelVehicle" hidden>
                            <input type="text" id="travelMode" hidden>
                            <input type="text" id="travelVehicle" hidden>
                            <input type="text" id="travelClass" hidden>
                            <input type="text" id="travelOwner" hidden>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Select Travel Type <span class="text-red">*</span> :</label>
                                    <select name="travel_type_id" class="form-control custom-select select2 travelType"
                                        id="travel_type_id" data-placeholder="Select Travel Type"
                                        onchange="travelTypeChange(this);" required>
                                        <option label="Select Travel Type"></option>
                                        @foreach ($travelTypes as $ttype)
                                            <option value="{{ $ttype->pttt_id }}">{{ $ttype->fh_travel_type->m_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Select Mode <span class="text-red">*</span> :</label>
                                    <select name="travel_mode_id" id="travel_mode"
                                        class="form-control custom-select select2 traveMode"
                                        data-placeholder="Select Travel Mode" onchange="travelModeChange(this)" required>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Select Vehicle <span class="text-red">*</span> :</label>
                                    <select name="vehicle_id" id="vehicle"
                                        class="form-control custom-select select2 vehicle"
                                        data-placeholder="Select Vehicle" onchange="travelVehicleChange(this)" required>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Select Claim Type <span class="text-red">*</span> :</label>
                                    <select name="claim_type" id="claim_type" class="form-control custom-select select2"
                                        onchange="travelClaimTypeChange(this)" data-placeholder="Select Claim Type"
                                        required>
                                        <option label="Claim Type"></option>
                                        <option value="154">Actual</option>
                                        <option value="155">Policy</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Select Policy Category <span class="text-red">*</span>
                                        :</label>
                                    <select name="policy_category" id="policy_category"
                                        class="form-control custom-select select2"
                                        data-placeholder="Select Policy Category" required>
                                        <option label="Policy Category"></option>
                                        @foreach ($policyCategory as $item)
                                            <option value="{{ $item->ptc_id }}">
                                                {{ $item->ptc_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="" id="vehicle_class_div">
                                    </div>

                                    <div class="" id="vehicle_owner_div">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="taEligibilityAmountKm">Eligibility Amount/Km</label>
                                    <input type="number" min="0" class="form-control" step="0.1"
                                        min="0" name="taEligibilityAmountKm" id="taEligibilityAmountKm"
                                        placeholder="Enter title">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div>
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <label class="form-label" for="conveyance">Conveyance :</label>
                                        </div>
                                        <div class="col mt-3">
                                            <div class="form-group">
                                                <input type="checkbox" name="conveyance" id="conveyance"
                                                    class="form-check-input">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer d-flex justify-content-end">@csrf
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- modal for delete confirmation --}}
    {{-- <div>
        <div class="modal fade" id="travelDeletebtn" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Confirm Deletion</h5>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="{{ route('admin.delete.travel.vehicle') }}" method="POST"> @csrf
                        <input type="text" id="travel_id" name="travel_id" hidden>
                        <div class="modal-body text-center">
                            <h4 class="mt-5">Are you sure want to delete, travel vehicle of Sno.
                            <span class="text-primary" id="assign_sno"></span> ?</h4>
                        </div>
                        <div class="modal-footer">
                            <a class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
                            <button type="submit" class="btn btn-danger" id="">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}

    <script>
        // Remove messages after 10 seconds (10000 milliseconds)
        setTimeout(function() {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');

            if (successMsg) successMsg.remove();
            if (errorMsg) errorMsg.remove();
        }, 7000);
    </script>

    <script>
        function travelDeleteModel(context) {
            var id = $(context).data('id');
            var sno = $(context).data('sno');
            $('#travel_id').val(id);
            $('#assign_sno').text(sno);
        }

        function travelTypeChange(e) {
            var travelTypeId = e.value;
            var travelModeData = $('#travelMode').val();
            populateModeDropdown(travelTypeId, travelModeData);
        }

        function travelModeChange(e) {
            var selectedMode = e.value;
            var travelVehicleData = $('#travelVehicle').val();
            // if(selectedMode && travelVehicleData)
            populateVehicleDropdown(selectedMode, travelVehicleData);
        }

        function travelVehicleChange(e) {
            var vehicle_id = e.value;
            var travelClassData = $('#travelClass').val();
            var travelOwnerData = $('#travelOwner').val();
            // var vehicle_mode = $(e).find('option:selected').data('vehicle_mode');
            getVehicleClassAndOwner(vehicle_id, travelClassData, travelOwnerData);
        }

        function openAddTravelVehicle() {
            $('#travel_type_id option').removeAttr('selected');
            $('#claim_type option').removeAttr('selected');
            $('#policy_category').val('');
            $('#taEligibilityAmountKm').val('');

            $('#vehicle_class_div').html('');
            $('#vehicle_owner_div').html('');
            $('#travel_mode').html('');
            $('#vehicle').html('');
            $('#travel_mode', '#vehicle', '#vehicle_owner', '#vehicle_class').val(null);
            $('#travel_type_id').val(null).trigger('change'); // Reset and trigger change for Select2
            $('#claim_type').val(null).trigger('change');
            $('#conveyance').prop('checked', false);
            $('#modal-title').html('Create Travel Vehicle');
            $('.select2').select2();
        }

        function openEditTravelVehicle(e) {
            $('#travel_type_id option').removeAttr('selected');
            $('#claim_type option').removeAttr('selected');
            $('#vehicle_class_div').html('');
            $('#vehicle_owner_div').html('');
            $('#travel_mode').html('');
            $('#vehicle').html('');
            $('#travel_type_id', '#travel_mode', '#vehicle', '#claim_type', '#vehicle_owner', '#vehicle_class').val(null);
            $('#modal-title').html('Edit Travel Vehicle');

            var travelVehicleId = $(e).data('id');
            var travelType = $(e).data('travel_type');
            var travelMode = $(e).data('mode');
            var travelVehicle = $(e).data('vehicle');
            var travelClaim = $(e).data('claim');
            var travelOwner = $(e).data('owner');
            var travelClass = $(e).data('class');
            var conveyance = $(e).data('conveyance');
            var policyCategory = $(e).data('policy_category');
            var el_amount = $(e).data('el_amount');
            $('#editId').val(travelVehicleId);

            $('#travelMode').val(travelMode);
            $('#travelVehicle').val(travelVehicle);
            $('#travelOwner').val(travelOwner);
            $('#travelClass').val(travelClass);
            // $('#conveyance').val(conveyance);
            if (conveyance === 1) {
                $('#conveyance').prop('checked', true);
            } else {
                $('#conveyance').prop('checked', false);
            }
            $('#policy_category').val(policyCategory).trigger('change');
            if (travelClaim == 155) {
                $('#taEligibilityAmountKm').prop('readonly', false).prop('required', true);
                $('#taEligibilityAmountKm').val(el_amount);
            } else {
                $('#taEligibilityAmountKm').prop('readonly', true).prop('required', false);
            }

            setDropdownValue('#travel_type_id', travelType);
            setDropdownValue('#claim_type', travelClaim);
            $('.select2').select2();
        }

        function setDropdownValue(selector, value) {
            $(selector + ' option').each(function() {
                if ($(this).val() === value.toString()) {
                    $(this).attr('selected', 'selected');
                    $(selector).val($(this).val()).trigger('change');
                    return false; // Break the loop once the match is found
                }
            });
        }

        function travelClaimTypeChange(value) {
            var claimTypeData = $('#claim_type').val();
            if (claimTypeData == 155) {
                $('#taEligibilityAmountKm').prop('readonly', false).prop('required', true);
            } else {
                $('#taEligibilityAmountKm').val('');
                $('#taEligibilityAmountKm').prop('readonly', true).prop('required', false);
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        $(document).on('click', '.deleteTravelVehicle', function() {
            const travelVID = $(this).data('id');

            var url = "{{ route('admin.delete.travel.vehicle') }}";
            // url = url.replace(':id', travelVID);
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this travel vehical!',
                timer: 3000,
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
                            'travel_id': travelVID,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // $(`#travelAllowance${travelAllowanceId}`).remove();
                            if (response.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.success,
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
                                    title: 'Not Deleted!',
                                    text: response.error,
                                    icon: 'info',
                                    timer: 3000, // 3 seconds
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    didClose: () => {
                                        // location.reload();
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });

        function populateModeDropdown(travelTypeId, selectedModeId = null) {
            $.ajax({
                url: "{{ route('admin.get.travel.vehicle') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    REQUEST_TYPE: 'TRAVEL_MODE',
                    travelType: travelTypeId,
                },
                dataType: 'json',
                cache: true,
                success: function(data) {
                    if (data.status === true) {
                        $('#travel_mode').html('');
                        $('#vehicle_class').html('');
                        var defaultOption = $('<option>').val('').text('Select Travel Mode');
                        $('#travel_mode').append(defaultOption);
                        $('#travel_mode').attr('required', true);
                        data.result.forEach(function(element) {
                            var option = $('<option>').val(element.pttm_id).text(element.fh_travel_mode
                                .m_name);
                            if (selectedModeId && selectedModeId == element.pttm_id) {
                                option.attr('selected', true);
                            }
                            $('#travel_mode').append(option);
                        });
                        if (selectedModeId) {
                            $('#travel_mode').val(selectedModeId).trigger('change');
                        }
                    } else {
                        $('#travel_mode').html('');
                    }
                },
                error: function(error) {
                    console.error('Error fetching travel mode:', error);
                }
            });
        }

        function populateVehicleDropdown(modeTypeId, selectedVehicleId = null) {
            $.ajax({
                url: "{{ route('admin.get.travel.vehicle') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    REQUEST_TYPE: 'VEHICLE',
                    mode_type_id: modeTypeId,
                },
                dataType: 'json',
                cache: true,
                success: function(data) {
                    if (data.status == true) {
                        $('#vehicle_class').html('');
                        $('#vehicle_class_div').html('');
                        $('#vehicle_owner_div').html('');
                        $('#vehicle').html('');
                        var defaultOption = $('<option>').val('').text('Select Vehicle');
                        $('#vehicle').append(defaultOption);
                        $('#vehicle').attr('required', true);
                        data.result.forEach(function(element) {
                            var option = $('<option>').val(element.m_id).text(element
                                .m_name); //.attr('data-vehicle_mode', element.m_description)
                            if (selectedVehicleId && selectedVehicleId == element.m_id) {
                                option.attr('selected', 'selected');
                            }
                            $('#vehicle').append(option);
                        });
                        if (selectedVehicleId) {
                            $('#vehicle').val(selectedVehicleId).trigger('change');
                        }
                    } else {
                        $('#vehicle').html('');
                    }
                },
                error: function(error) {
                    console.error('Error fetching vehicle:', error);
                }
            });
        }

        function getVehicleClassAndOwner(vehicle_id, selectedClassId = null, selectedOwnerId = null) {
            return $.ajax({
                url: "{{ route('admin.get.travel.vehicle') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    REQUEST_TYPE: 'TRAVEL_CLASS_TRAVEL_OWNER',
                    vehicle_id: vehicle_id,
                },
                dataType: 'json',
                cache: true,
            }).then(data => {
                if (data.status == true) {
                    $('#vehicle_class_div').html('');
                    $('#vehicle_owner_div').html('');

                    if (data.result.some(item => item.m_group == 'TRAVEL_CLASS')) {
                        $('#vehicle_owner_div').removeClass('col-12');
                        $('#vehicle_class_div').addClass('col-12');
                        if (selectedClassId && selectedOwnerId) {
                            var classSelectHtml = `<div class="form-group">
                                    <label class="form-label">Select Vehicle Class :</label>
                                    <select name="vehicle_class_id" id="vehicle_class" class="form-control custom-select select2 vehicleClasss" data-placeholder="Select Vehicle Class" required>
                                        <option label="Vehicle Class"></option>
                                    </select>
                                </div>`;
                        } else {
                            var classSelectHtml = `<div class="form-group">
                                    <label class="form-label">Select Vehicle Class :</label>
                                    <select name="vehicle_class_id[]" id="vehicle_class" class="form-control custom-select select2 vehicleClasss" data-placeholder="Select Vehicle Class" required multiple="multiple">
                                        <option label="Vehicle Class"></option>
                                    </select>
                                </div>`;
                        }
                        $('#vehicle_class_div').append(classSelectHtml);
                        data.result.forEach(function(element) {
                            if (element.m_group == 'TRAVEL_CLASS') {
                                var option = $('<option>').val(element.m_id).text(element.m_name);
                                if (selectedClassId && selectedClassId == element.m_id) {
                                    option.attr('selected', 'selected');
                                }
                                $('#vehicle_class').append(option);
                            }
                        });
                    }

                    if (data.result.some(item => item.m_group == 'VEHICLE_OWNER')) {
                        $('#vehicle_class_div').removeClass('col-12');
                        $('#vehicle_owner_div').addClass('col-12');
                        if (selectedClassId && selectedOwnerId) {
                            var ownerSelectHtml = `<div class="form-group">
                                <label class="form-label">Select Vehicle Owner :</label>
                                <select name="vehicle_owner_id" id="vehicle_owner" class="form-control custom-select select2 vehicleOwner" data-placeholder="Select Vehicle Owner" required>
                                    <option label="Vehicle Owner"></option>
                                </select>
                            </div>`;
                        } else {
                            var ownerSelectHtml = `<div class="form-group">
                                <label class="form-label">Select Vehicle Owner :</label>
                                <select name="vehicle_owner_id[]" id="vehicle_owner" class="form-control custom-select select2 vehicleOwner" data-placeholder="Select Vehicle Owner" required multiple="multiple">
                                    <option label="Vehicle Owner"></option>
                                </select>
                            </div>`;
                        }
                        $('#vehicle_owner_div').append(ownerSelectHtml);
                        data.result.forEach(function(element) {
                            if (element.m_group == 'VEHICLE_OWNER') {
                                var option = $('<option>').val(element.m_id).text(element.m_name);
                                if (selectedOwnerId && selectedOwnerId == element.m_id) {
                                    option.attr('selected', 'selected');
                                }
                                $('#vehicle_owner').append(option);
                            }
                        });
                    }
                    $('.select2').select2();
                } else {
                    $('#vehicle_class_div').html('');
                    $('#vehicle_owner_div').html('');
                    // $('#vehicle_class_div').html('<option value="" selected>Select Vehicle Class</option>');
                    // $('#vehicle_owner_div').html('<option value="" selected>Select Vehicle Owner</option>');
                }
            }).catch(error => {
                console.error('Error fetching vehicle class and owner:', error);
            });
        }

        $('#addTravelVehicleForm').submit(function(event) {
            let data = new FormData(this);
            event.preventDefault();

            $.ajax({
                url: '{{ route('admin.save.travel.vehicle') }}',
                method: 'POST',
                data: data,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#saveUptBtn').attr('disabled', 'disabled');
                },
                success: function(data) {
                    if (data.status == true) {
                        Swal.fire({
                            icon: 'success',
                            text: data.message,
                            timer: 3000,
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            text: data.message,
                            timer: 3000,
                        });
                    }
                    $('#saveUptBtn').attr('disabled', false);
                    window.location.href = '{{ route('admin.travel.vehicle') }}';
                },
                error: function(xhr, status, error) {
                    var response = JSON.parse(xhr.responseText); // Parse the JSON response
                    var errorMessage = response.errors ? response.errors.join('\n') :
                        'An error occurred'; // Extract the error message
                    Swal.fire({
                        icon: 'error',
                        text: errorMessage,
                        timer: 3000
                    });
                    $('#saveUptBtn').attr('disabled', false);
                }
            });
        });

        $(document).ready(function() {
            // Initialize Select2 globally for elements with the class 'select2'
            $('.select2').select2();

            // Reinitialize Select2 when the modal is shown
            $('#addTravelVehicleModal').on('shown.bs.modal', function() {
                // Destroy existing Select2 instance if it exists
                $('.select2').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                // Initialize Select2 again within the modal
                $('.select2').select2({
                    dropdownParent: $('#addTravelVehicleModal')
                });
            });
        });



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
