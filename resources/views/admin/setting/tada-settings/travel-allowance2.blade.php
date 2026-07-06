<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelMode;
use App\Models\PolicyTadaTravelType;
use App\Models\MasterTable;

$user = Auth::user();
$policyCategoryFilter = CommonUtils::getCustomModelData(new PolicyTadaCategory(), [['method' => 'where', 'args' => ['ptc_b_id', $user->emp_b_id]]]);
$travelTypeFilter = CommonUtils::getCustomModelData(new PolicyTadaTravelType(), [['method' => 'where', 'args' => ['pttt_b_id', $user->emp_b_id]]]);
$travelModeFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'TRAVEL_MODE']]]);
?>
@extends('admin.layout.master')

@section('title', 'Travel Allowance')
{{-- @section('script')

@endsection --}}
<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    /* #btnXyz:hover {
        color: #fff
    } */

    table td {
        padding: 0;
    }
</style>
@section('content')
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
            <li class="active"><span><b>Travel Allowance</b></span></li>
        </ol>
    </div>

    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Travel Allowance</div>
            <p class="text-muted">Create and activate Travel Allowance</p>
        </div>
        <div class="page-rightheader ms-md-auto">
            <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                <div class="d-lg-flex d-block ms-auto">
                    <div class="btn-list">
                        <button class="btn btn-outline-primary" id="createNewTravelAllowance">Add Travel Allowance</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex">
            <div>
                <h4 class="card-title"><span>Travel Allowance List</span></h4>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md">
                    <div class="form-group">
                        <p class="form-label">Policy Category</p>
                        <select id="policyCategoryFilter" data-filter class="form-select-md p-2 search_test custom-heighlight">
                            <option value="">All</option>
                            @foreach ($policyCategoryFilter as $pcF)
                                <option value="{{ $pcF->ptc_id }}">{{ $pcF->ptc_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md">
                    <div class="form-group">
                        <p class="form-label">Travel Type</p>
                        <select id="travelTypeFilter" class="form-select-md p-2 search_test custom-heighlight" data-filter>
                            <option value="">All</option>
                            @foreach ($travelTypeFilter as $travelTypeF)
                                <option value="{{ $travelTypeF->pttt_id }}">{{ $travelTypeF->fh_travel_type->m_name }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                </div>

                <div class="col-md">
                    <div class="form-group">
                        <p class="form-label">Travel Mode</p>
                        <select id="travelModeFilter" class=" form-select-md p-2 search_test custom-heighlight"
                            data-filter>
                            <option value="">All</option>
                            @foreach ($travelModeFilter as $trvelModelF)
                                <option value="{{ $trvelModelF->m_id }}">{{ $trvelModelF->m_name }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <div class="col-md">
                    <div class="form-group">
                        <p class="form-label">Search</p>
                        <div class="form-group mb-3">
                            <input type="text" id="searchFilter" placeholder="Search" class="form-control" data-search />
                        </div>
                    </div>
                </div>

            </div>
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

                <div class="col-md-9 col-sm-4"></div>

                <div class="col-md-2 col-sm-4 pt-5" align="right">
                    <div class="btn-group">
                        <button class="btn btn-outline-danger dropdown-toggle" type="button" id="defaultDropdown"
                            data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                            Export As
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

            </div>
            <div class="table-responsive">
                <table class="table display table-vcenter text-wrap border-bottom" id="permission-table-dynamic">
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

    <!-- Modal -->
    <div class="modal fade" id="travelAllowanceModal" aria-labelledby="travelAllowanceModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="travelAllowanceModalLabel">New Travel Allowance</h5>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="travelAllowanceId">
                    <div class="form-group">
                        <label for="taPolicyCategory">Policy Category <span class="text-danger">*</span></label>
                        <select name="taPolicyCategory" id="taPolicyCategory" class="form-control custom-select select2">
                            <option value="">Select Policy Category</option>
                            @foreach ($policyCategory as $item)
                                <option value="{{ $item->ptc_id }}">
                                    {{ $item->ptc_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="taTravelType">Travel Type <span class="text-danger">*</span></label>
                        <select name="taTravelType" id="taTravelType" class="form-control custom-select select2">
                            <option value="">Select Travel Type</option>
                            @foreach ($travelType as $item)
                                <option value="{{ $item->pttt_id }}">
                                    {{ $item->fh_travel_type->m_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="taTravelMode">Travel Mode <span class="text-danger">*</span></label>
                        <select name="travelMode" id="taTravelMode" class="form-control custom-select select2">
                            <option value="">Select Travel Mode</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="taTravelMode">Travel Claim Type <span class="text-danger">*</span></label>
                        <select name="taTravelClaimType" id="taTravelClaimType" class="form-control custom-select select2">
                            <option value="">Select Claim Type</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="taTravelVehicleList">Travel Vehicle List <span class="text-danger">*</span></label>
                        <select name="taTravelVehicleList" id="taTravelVehicleList"
                            class="form-control custom-select select2">
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="taEligibilityAmountKm">Eligibility Amount/Km</label>
                        <input type="number" min="0" class="form-control" name="taEligibilityAmountKm"
                            id="taEligibilityAmountKm" placeholder="Enter title">
                    </div>

                    <div class="form-group">
                        <label for="taRemarks">Remarks</label>
                        <textarea class="form-control" name="taRemarks" id="taRemarks" placeholder="Enter Remarks"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>

                    {{-- <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button> --}}
                    <button type="button" id="savetravelAllowance" class="btn btn-outline-primary">Save changes</button>
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
                tableId: "permission-table-dynamic",
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        function resetData() {
            $('#travelAllowanceId').val('');
            $('#taPolicyCategory').val('').trigger('change');
            $('#taTravelType').val('').trigger('change');
            $('#taTravelMode').val('').trigger('change');
            $('#taTravelVehicleList').val('').trigger('change');
            $('#taEligibilityAmountKm').val('');
            $('#taRemarks').val('');
            $('#travelAllowanceModal').modal('show');
        }

        $(document).ready(function() {
            $('#createNewTravelAllowance').click(function() {
                resetData();
                $('#travelAllowanceModalLabel').text('Create New Travel Allowance');
            });

            $(document).on('click', '.edittravelAllowance', function() {
                resetData();
                const travelAllowanceId = $(this).data('id');
                const ptta_ptc_id = $(this).data('ptta_ptc_id');
                const ptta_pttt_id = $(this).data('ptta_pttt_id');
                const ptta_pttm_id = $(this).data('ptta_pttm_id');
                const ptta_pttv_id = $(this).data('ptta_pttv_id');
                const ptta_eligibility = $(this).data('ptta_eligibility');
                const ptta_claim_type_id = $(this).data('ptta_claim_type_id')
                const ptta_remarks = $(this).data('ptta_remarks');
                $('#travelAllowanceId').val(travelAllowanceId);
                $('#taPolicyCategory').val(ptta_ptc_id).trigger('change');

                // Load travel modes based on travel type
                $('#taTravelType').val(ptta_pttt_id).trigger('change', {
                    selected_ptta_pttm_id: ptta_pttm_id,
                    selected_ptta_claim_type_id: ptta_claim_type_id,
                    selected_ptta_pttv_id: ptta_pttv_id,
                });

                // setTimeout(function() {
                //     $('#taTravelMode').val(ptta_pttm_id).trigger('change');
                // }, 500);

                // setTimeout(function() {
                //     $('#taTravelClaimType').val(ptta_claim_type_id).trigger('change');
                // }, 500);

                // setTimeout(function() {
                //     $('#taTravelVehicleList').val(ptta_pttv_id).trigger('change');
                // }, 500);

                $('#taEligibilityAmountKm').val(ptta_eligibility);
                $('#taRemarks').val(ptta_remarks);

                $('#travelAllowanceModalLabel').text('Edit Travel Allowance');
                $('#travelAllowanceModal').modal('show');
            });

            $('#savetravelAllowance').click(function() {
                $('#savetravelAllowance').prop('disabled', true).text('Saving...');
                const travelAllowanceId = $('#travelAllowanceId').val();
                const taPolicyCategory = $('#taPolicyCategory').val();
                const taTravelType = $('#taTravelType').val();
                const taTravelMode = $('#taTravelMode').val();
                const taTravelClaimType = $('#taTravelClaimType').val();
                const taTravelVehicleList = $('#taTravelVehicleList').val();
                const taEligibilityAmountKm = $('#taEligibilityAmountKm').val();
                const taRemarks = $('#taRemarks').val();
                if (!taPolicyCategory) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select Policy Category',
                        timer: 3000,
                    });
                    $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                    return;
                }

                if (!taTravelType) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select Travel Type',
                        timer: 3000,
                    });
                    $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                    return;
                }

                if (!taTravelMode) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select Travel Mode',
                        timer: 3000,
                    });
                    $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                    return;
                }
                if(taTravelClaimType == 154 && !travelAllowanceId){
                    if (!taTravelVehicleList || taTravelVehicleList.length === 0) {
                        // Show the SweetAlert if no option is selected
                        Swal.fire({
                            icon: 'error',
                            text: 'Please select Travel Vehicle List',
                            timer: 3000,
                        });
                        $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                        return;
                    }
                }else{
                    if (!taTravelVehicleList){
                        Swal.fire({
                            icon: 'error',
                            text: 'Please select Travel Vehicle List',
                            timer: 3000,
                        });
                        $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                        return;
                    }
                }

                if ($('#taEligibilityAmountKm').prop('readonly')) {
                    // skip validation if the field is readonly
                } else if (!taEligibilityAmountKm) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please enter Eligibility Amount/Km',
                        timer: 3000,
                    });
                    $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                    return;
                }

                if (!taRemarks) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please enter Remarks',
                        timer: 3000,
                    });
                    $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                    return;
                }

                if (travelAllowanceId) {
                    $.ajax({
                        url: `/admin/settings/tada-settings/travel-allowance`,
                        method: 'POST',
                        data: {
                            ptta_id: travelAllowanceId,
                            ptta_ptc_id: taPolicyCategory,
                            ptta_pttt_id: taTravelType,
                            ptta_pttm_id: taTravelMode,
                            ptta_pttv_id: taTravelVehicleList,
                            ptta_eligibility: taEligibilityAmountKm,
                            ptta_claim_type_id:taTravelClaimType,
                            ptta_remarks: taRemarks,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response) {
                                if (response.success) {
                                    $('#travelAllowanceModal').modal('hide');
                                    Swal.fire({
                                        icon: 'success',
                                        text: 'Travel Allowance Updated Successfully!',
                                        timer: 3000,
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        text: response.error,
                                        timer: 3000,
                                    }).then(() => {
                                        // location.reload();
                                    });
                                    $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                                }
                            }
                        }
                    });
                } else {
                    $.ajax({
                        url: "{{ route('travel-allowance.store') }}",
                        method: 'POST',
                        data: {
                            ptta_ptc_id: taPolicyCategory,
                            ptta_pttt_id: taTravelType,
                            ptta_pttm_id: taTravelMode,
                            ptta_pttv_id: taTravelVehicleList,
                            ptta_claim_type_id:taTravelClaimType,
                            ptta_eligibility: taEligibilityAmountKm,
                            ptta_remarks: taRemarks,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response) {
                                if (response.success) {
                                    $('#travelAllowanceModal').modal('hide');
                                    Swal.fire({
                                        icon: 'success',
                                        text: 'Travel Allowance Created Successfully!',
                                        timer: 3000,
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        text: response.error,
                                        timer: 3000,
                                    }).then(() => {
                                        // location.reload();
                                    });
                                    $('#savetravelAllowance').prop('disabled', false).text('Save Changes');
                                }
                            }
                        }
                    });
                }
            });

            $(document).on('click', '.deleteTravelAllowance', function() {
                const travelAllowanceId = $(this).data('id');
                var url = "{{ route('travel-allowance.destroy', ':id') }}";
                url = url.replace(':id', travelAllowanceId);
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will not be able to recover this travel allowance!',
                    timer: 3000,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                $(`#travelAllowance${travelAllowanceId}`).remove();
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Travel allowance has been deleted successfully.',
                                    icon: 'success',
                                    timer: 3000, // 3 seconds
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    didClose: () => {
                                        location.reload();
                                    }
                                });
                            }
                        });
                    }
                });
            });

            // Load Travel Modes based on Travel Type
            $('#taTravelType').on('change', function(event, data) {
                // Ensure data is defined and access custom data
                var selected_ptta_pttm_id = null;
                var selected_ptta_pttv_id = null;
                var selected_ptta_claim_type_id = null;
                if (data && data.selected_ptta_pttm_id && data.selected_ptta_pttv_id && data.selected_ptta_claim_type_id) {
                    selected_ptta_pttm_id = data.selected_ptta_pttm_id
                    selected_ptta_pttv_id = data.selected_ptta_pttv_id;
                    selected_ptta_claim_type_id = data.selected_ptta_claim_type_id;
                }
                var travelTypeId = $(this).val();
                var url = '{{ route('getTravelModes', ':id') }}';
                url = url.replace(':id', btoa(travelTypeId));
                if (travelTypeId) {
                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#taTravelMode').empty().append('<option value="">Select Travel Mode</option>');
                            $.each(data, function(key, value) {
                                $('#taTravelMode').append('<option value="' + value.pttm_id +
                                    '" ' + (value.pttm_id == selected_ptta_pttm_id ? "selected" : "") +
                                    ' >' + value.fh_travel_mode.m_name + '</option>'
                                );
                            });
                            $('#taTravelMode').val(selected_ptta_pttm_id).trigger('change', {
                                selected_ptta_pttv_id: selected_ptta_pttv_id,
                                selected_ptta_claim_type_id: selected_ptta_claim_type_id,
                            });
                        }
                    });
                } else {
                    $('#taTravelMode').empty().append('<option value="">Select Travel Mode</option>');
                }
            });

            $('#taTravelMode').on('change', function(event, data) {
                var selected_ptta_pttv_id = null;
                var selected_ptta_claim_type_id = null;
                if (data && data.selected_ptta_pttv_id && data.selected_ptta_claim_type_id) {
                    selected_ptta_pttv_id = data.selected_ptta_pttv_id,
                    selected_ptta_claim_type_id = data.selected_ptta_claim_type_id
                }
                var travelModeId = $(this).val();
                if (travelModeId) {
                    $.ajax({
                        url: "{{ route('get.travelLists') }}",
                        type: "GET",
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: btoa(travelModeId),
                            REQUEST_TYPE: 'GET_CLAIM_TYPE',
                        },
                        dataType: "json",
                        success: function(data) {
                            $('#taTravelClaimType').empty().append(
                                '<option value="">Select Travel Claim Type</option>');
                            $.each(data, function(key, value) {
                                $('#taTravelClaimType').append(
                                    `<option value="${value}">${key}</option>`
                                );
                            });
                            if ((selected_ptta_pttv_id != null) && (selected_ptta_claim_type_id != null)) {

                                $('#taTravelClaimType').val(selected_ptta_claim_type_id).trigger('change', {
                                    selected_ptta_pttv_id: selected_ptta_pttv_id,
                                    selected_ptta_claim_type_id: selected_ptta_claim_type_id,
                                });
                                // $('#taTravelClaimType').val(selected_ptta_claim_type_id).trigger('change');
                            }
                        }
                    });
                } else {
                    $('#taTravelClaimType').empty().append(
                        '<option value="">Select Travel Claim Type</option>');
                }
            });

            $('#taTravelClaimType').on('change', function(event, data) {
                var selected_ptta_pttv_id = null;
                var travelClaimType = $(this).val();
                var travelModeId = $('#taTravelMode').val();
                var eligibility = $('#taEligibilityAmountKm');
                if (data && data.selected_ptta_pttv_id && data.selected_ptta_claim_type_id) {
                    selected_ptta_pttv_id = data.selected_ptta_pttv_id
                }
                if (travelClaimType == 154) { // 154 == 'By Actual'
                    eligibility.prop('readonly', true);
                    eligibility.attr('placeholder', 'By Actual');
                    eligibility.val('');
                    var idVal = $('#travelAllowanceId').val();
                    if (idVal || (data && data.selected_ptta_pttv_id && data.selected_ptta_claim_type_id )) {
                        $('#taTravelVehicleList').removeAttr('multiple').attr('name', 'taTravelVehicleList').select2({
                            placeholder: "Select Travel Vehicle List",
                            allowClear: true
                        });
                    }else{
                        $('#taTravelVehicleList').attr('multiple', 'multiple').attr('name', 'taTravelVehicleList[]').select2({
                            placeholder: "Select Travel Vehicle List",
                            allowClear: true
                        });
                    }
                } else {
                    eligibility.prop('readonly', false);
                    eligibility.attr('placeholder', 'Enter Eligibility');
                    $('#taTravelVehicleList').removeAttr('multiple').attr('name', 'taTravelVehicleList').select2({
                        placeholder: "Select Travel Vehicle List",
                        allowClear: true
                    });
                }

                if (travelModeId) {
                    $.ajax({
                        url: "{{ route('get.travelLists') }}",
                        type: "GET",
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: btoa(travelModeId),
                            travel_claim_type: btoa(travelClaimType),
                            REQUEST_TYPE: 'GET_VEHICLE_LIST',
                            // REQUEST_TYPE: 'GET_VEHICLE',
                        },
                        dataType: "json",
                        success: function(data) {
                            $('#taTravelVehicleList').empty();
                            $.each(data, function(key, value) {
                                $('#taTravelVehicleList').append(
                                    `<option value="${value.pttv_id}">${value.name}</option>`
                                );
                            });
                            if (selected_ptta_pttv_id != null) {
                                $('#taTravelVehicleList').val(selected_ptta_pttv_id).trigger('change');
                            }
                        }
                    });
                } else {
                    $('#taTravelVehicleList').empty();
                }
            });

            // $('#taTravelVehicleList').change(function() {
            //     var travelVehicleId = $(this).val();
            //     if (travelVehicleId) {
            //         $.ajax({
            //             url: "{{ route('get.travelLists') }}",
            //             type: "GET",
            //             data: {
            //                 _token: '{{ csrf_token() }}',
            //                 id: btoa(travelVehicleId),
            //                 REQUEST_TYPE: 'GET_VEHICLE_TYPE',
            //             },
            //             dataType: "json",
            //             success: function(data) {
            //                 var eligibility = $('#taEligibilityAmountKm');
            //                 if (data.vehicleClaimId == 154) { // 154 == 'By Actual'
            //                     eligibility.prop('readonly', true);
            //                     eligibility.attr('placeholder', 'By Actual');
            //                     eligibility.val('');
            //                 } else {
            //                     eligibility.prop('readonly', false);
            //                     eligibility.attr('placeholder', 'Enter Eligibility');
            //                     // eligibility.val('');
            //                 }
            //             }
            //         });
            //     } else {
            //         $('#taTravelVehicleList').empty();
            //     }
            // });

            $('.select2').select2();
            // Initialize Select2 on modal shown
            $('#travelAllowanceModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#travelAllowanceModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
        });
    </script>
@endsection
