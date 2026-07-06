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

@section('title', 'Lodging')

@section('content')

    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                   <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                    <li class="active"><span><b>Lodging </b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" 
                                            data-bs-toggle="modal" data-bs-target="#lodgingModal">Add New
                                </button>
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
                        <h4 class="card-title"><span>Lodging</span></h4>
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

                                <ul class="dropdown-menu p-2" aria-labelledby="lodgingDropdown"
                                    style="min-width: 220px;">
                                  
                                    <li>
                                        <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#ExcelModal">
                                            <i class="las la-file-upload"></i> Upload File
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                            href="{{ route('lodging.downloadExcel') }}">
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
                                        <select id="lodging_policyCategoryFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($policyCategoryFilter as $pcF)
                                                <option value="{{ $pcF->ptc_id }}">{{ $pcF->ptc_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="travelTypeFilter" class="form-label">Travel Type</label>
                                        <select id="lodging_travelTypeFilter" data-filter
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
                                        <label for="designationFilter" class="form-label">Travel Mode</label>
                                        <select id="lodging_designationFilter" data-filter
                                            class="form-select search-txt filter_border">
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
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="travel-lodging">
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
    <div class="modal fade" id="lodgingModal" tabindex="-1" role="dialog" aria-labelledby="largemodal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lodgingModalLabel">Create Lodging</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="lodgingForm">
                    <div class="modal-body"> @csrf
                        <input type="hidden" name="lodging_id" id="lodging_id">

                        <!-- Other Fields -->
                        <div class="form-group">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="policy_category">Policy Category <span
                                            class="text-danger">*</span></label>
                                    <select name="policy_category" id="policy_category"
                                        class="form-control custom-select select2 enableButton">
                                        <option value="" selected>Select Policy Category </option>
                                        @foreach ($policyCategory as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="travel_type">Travel Type <span class="text-danger">*</span></label>
                                    <select name="travel_type" id="travel_type"
                                        class="form-control custom-select select2 enableButton">
                                        <option value="" selected>Select Travel Type </option>
                                        @foreach ($travelTypes as $key => $ttype)
                                            <option value="{{ $ttype->pttt_id }}">{{ $ttype->fh_travel_type->m_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="city_type">City Type <span class="text-danger">*</span></label>
                                    <select name="city_type" id="city_type"
                                        class="form-control custom-select select2 enableButton">
                                        <option value="" selected>Select City Type </option>
                                        @foreach ($cityType as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <label>Lodging With Bill <span class="text-danger">*</span></label>
                                            <div
                                                style="border-top: 1px solid #9ba5ca; width: 100%; margin-top: 2px; margin-bottom: 10px;">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label for="lodging_single_with_bill">S. Occupancy</label>
                                            <input type="number" name="lodging_single_with_bill" min="0"
                                                id="lodging_single_with_bill" class="form-control"
                                                placeholder="S. Occupancy">
                                        </div>
                                        <div class="col-md-7">
                                            <label for="lodging_double_with_bill">D. Occupancy (per person)</label>
                                            <input type="number" name="lodging_double_with_bill" min="0"
                                                id="lodging_double_with_bill" class="form-control"
                                                placeholder="D. Occupancy (per person)">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <label>Lodging Without Bill <span class="text-danger">*</span></label>
                                            <div
                                                style="border-top: 1px solid #9ba5ca; width: 100%; margin-top: 2px; margin-bottom: 10px;">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label for="lodging_single_without_bill">S. Occupancy</label>
                                                <input type="number" name="lodging_single_without_bill" min='0'
                                                    id="lodging_single_without_bill" class="form-control"
                                                    placeholder="S. Occupancy">
                                            </div>
                                            <div class="col-md-7">
                                                <label for="lodging_double_without_bill">D. Occupancy (per person)</label>
                                                <input type="number" name="lodging_double_without_bill" min='0'
                                                    id="lodging_double_without_bill" class="form-control"
                                                    placeholder="D. Occupancy (per person)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-outline-danger" data-bs-dismiss="modal">Close</a>
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
                    <h5 class="modal-title" id="DaModalLabel">Upload Lodging File</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('lodging.import') }}" method="POST" enctype="multipart/form-data">
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
                tableId: "travel-lodging",
                url: "{{ route('admin.travel.lodging') }}",
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
            // Handle form submission for create/update
            $('#lodgingForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#lodging_id').val();
                const policy_category = $('#policy_category').val();
                const travel_type = $('#travel_type').val();
                const city_type = $('#city_type').val();
                const lodging_single_with_bill = $('#lodging_single_with_bill').val();
                const lodging_double_with_bill = $('#lodging_double_with_bill').val();
                const lodging_single_without_bill = $('#lodging_single_without_bill').val();
                const lodging_double_without_bill = $('#lodging_double_without_bill').val();

                if (!policy_category) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select Policy Category',
                        timer: 3000,
                    });
                    return;
                }
                if (!travel_type) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select Travle Type',
                        timer: 3000,
                    });
                    return;
                }
                if (!city_type) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please select City Type',
                        timer: 3000,
                    });
                    return;
                }
                if (!lodging_single_with_bill) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please enter lodging single with bill amount',
                        timer: 3000,
                    });
                    return;
                }
                if (!lodging_double_with_bill) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please enter lodging double with bill amount',
                        timer: 3000,
                    });
                    return;
                }
                if (!lodging_single_without_bill) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please enter lodging single without bill amount',
                        timer: 3000,
                    });
                    return;
                }
                if (!lodging_double_without_bill) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please enter lodging double without bill amount',
                        timer: 3000,
                    });
                    return;
                }
                $.ajax({
                    url: "{{ route('create.update.lodging') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#saveBtn').attr('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#lodgingModal').modal('hide');
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
                    }
                });
            });

            // Edit policy
            window.editLodging = function(button) {
                // Retrieve data attributes from the button
                const id = $(button).data('id');
                const policyCategory = $(button).data('ptl_ptc_id');
                const travelType = $(button).data('ptl_pttt_id');
                const cityType = $(button).data('ptl_ct_type_id');
                const lodgingSingleWithBill = $(button).data('ptl_sngl_w_bill');
                const lodgingDoubleWithBill = $(button).data('ptl_dbl_w_bill');
                const lodgingSingleWithoutBill = $(button).data('ptl_sngl_wo_bill');
                const lodgingDoubleWithoutBill = $(button).data('ptl_dbl_wo_bill');

                // Populate modal form fields
                $('#lodging_id').val(id);
                $('#policy_category').val(policyCategory).trigger('change');
                $('#travel_type').val(travelType).trigger('change');
                $('#city_type').val(cityType).trigger('change');
                $('#city_type').val(cityType);
                $('#lodging_single_with_bill').val(lodgingSingleWithBill);
                $('#lodging_double_with_bill').val(lodgingDoubleWithBill);
                $('#lodging_single_without_bill').val(lodgingSingleWithoutBill);
                $('#lodging_double_without_bill').val(lodgingDoubleWithoutBill);

                // Update modal title and show it
                $('#lodgingModalLabel').text('Update Lodging');
                $('#saveBtn').html('Update');
                $('#lodgingModal').modal('show');
                $('#saveBtn').attr('disabled', false);
            }


            // Reset form when modal is closed
            $('#lodgingModal').on('hidden.bs.modal', function() {
                $('#policy_category').val('').trigger('change');
                $('#travel_type').val('').trigger('change');
                $('#city_type').val('').trigger('change');
                $('#lodging_id').val('');
                $('#lodging_single_with_bill').val('');
                $('#lodging_double_with_bill').val('');
                $('#lodging_single_without_bill').val('');
                $('#lodging_double_without_bill').val('');
                $('#lodgingModalLabel').text('Create Lodging');
                $('#saveBtn').html('Save');
                $('#saveBtn').attr('disabled', false);
            });

            // Delete policy
            // window.deletePolicy = function(id) {
            //     if (confirm('Are you sure you want to delete this policy ?')) {
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

            $(document).on('click', '.deleteLodging', function() {
                const logId = $(this).data('id');
                var url = "{{ route('delete.lodging') }}";
                Swal.fire({
                    title: 'Are you sure ?',
                    text: 'You will not be able to recover this lodging!',
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
                                'id': btoa(logId),
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Lodging has been deleted successfully.',
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
            $('#lodgingModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#lodgingModal')
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
