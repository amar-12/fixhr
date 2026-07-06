<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelMode;
use App\Models\PolicyTadaTravelType;
use App\Models\MasterTable;

$user = Auth::user();
$policyCategoryFilter = CommonUtils::getCustomModelData(new PolicyTadaCategory(), [['method' => 'where', 'args' => ['ptc_b_id', $user->emp_b_id]]]);
$travelTypeFilter = CommonUtils::getCustomModelData(new PolicyTadaTravelType(), [['method' => 'where', 'args' => ['pttt_b_id', $user->emp_b_id]]]);
$cityFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'CITY_TYPE']]]);
?>
@extends('admin.layout.master')

@section('title', 'Daily Allowance & Lodging')

@section('content')
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
            <li class="active"><span><b>Daily Allowance & Lodging </b></span></li>
        </ol>
    </div>
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Daily Allowance & Lodging</div>
            <p class="text-muted">Set Eligiblity for Daily Allowance & Lodging</p>
        </div>
        <div class="page-rightheader ms-md-auto">
            <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                <div class="d-lg-flex d-block ms-auto">
                    <div class="btn-list">
                        {{-- data-bs-target="#modaldemo100" data-bs-toggle="modal" --}}
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#policyModal">
                            Add New Policy
                        </button>
                        {{-- <button class="btn btn-outline-primary" id="createNewTravelAllowance">Add Travel Allowance</button> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header d-flex">
                    <div>
                        <h4 class="card-title"><span>Daily Allowance & Lodging</span></h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md">
                            <div class="form-group">
                                <p class="form-label">Policy Category</p>
                                <select id="policyCategoryFilter" data-filter
                                    class="form-select-md p-2 search_test custom-heighlight">
                                    <option value="">All</option>
                                    @foreach ($policyCategoryFilter as $pcF)
                                        <option value="{{ $pcF->ptc_id }}">{{ $pcF->ptc_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-group">
                                <p class="form-label">Travel Type </p>
                                <select id="travelTypeFilter" class="form-select-md p-2 search_test custom-heighlight"
                                    data-filter>
                                    <option value="">All</option>
                                    @foreach ($travelTypeFilter as $travelTypeF)
                                        <option value="{{ $travelTypeF->pttt_id }}">
                                            {{ $travelTypeF->fh_travel_type->m_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-group">
                                <p class="form-label">Travel Mode</p>
                                <select id="cityFilter" class=" form-select-md p-2 search_test custom-heighlight"
                                    data-filter>
                                    <option value="">All</option>
                                    @foreach ($cityFilter as $cityF)
                                        <option value="{{ $cityF->m_id }}">{{ $cityF->m_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <div class="form-group mb-3">
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
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
                        <table class="table display table-vcenter text-wrap border-bottom"
                            id="travel-daily-allowance-and-lodging">
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
    <div class="modal fade" id="policyModal" tabindex="-1" role="dialog" aria-labelledby="largemodal"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="policyModalLabel">Create New Policy</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="travel-policy-form">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="policy_id" id="policy_id">

                        <!-- Other Fields -->
                        <div class="form-group">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="policy_category">Policy Category <span
                                            class="text-danger">*</span></label>
                                    <select name="policy_category" id="policy_category"
                                        class="form-control custom-select select2">
                                        <option value="" selected>Select Policy Category </option>
                                        @foreach ($policyCategory as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="travel_type">Travel Type <span class="text-danger">*</span></label>
                                    <select name="travel_type" id="travel_type"
                                        class="form-control custom-select select2">
                                        <option value="" selected>Select Travel Type </option>
                                        @foreach ($travelTypes as $key => $ttype)
                                            <option value="{{ $ttype->pttt_id }}">{{ $ttype->fh_travel_type->m_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="city_type">City Type <span class="text-danger">*</span></label>
                                    <select name="city_type" id="city_type" class="form-control custom-select select2">
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
                                            <label>DA <span class="text-danger">*</span></label>
                                            {{-- <hr class="mt-1 mb-3" style="border: 1px solid #000; width: 100%;"> --}}
                                            <div
                                                style="border-top: 1px solid #9ba5ca; width: 100%; margin-top: 2px; margin-bottom: 10px;">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="da_different_days">Full Day</label>
                                            <input type="number" name="da_same_day" id="da_same_day" min="0"
                                                class="form-control" placeholder="Same Day Returned">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="da_same_day">Same Day Returned</label>
                                            <input type="number" name="da_different_days" id="da_different_days"
                                                min="0" class="form-control" placeholder="Full Day">
                                        </div>
                                    </div>
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
                                        <div class="col-md-6">
                                            <label for="lodging_single_with_bill">S. Occupancy</label>
                                            <input type="number" name="lodging_single_with_bill" min="0"
                                                id="lodging_single_with_bill" class="form-control"
                                                placeholder="S. Occupancy">
                                        </div>
                                        <div class="col-md-6">
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
                                            <div class="col-md-6">
                                                <label for="lodging_single_without_bill">S. Occupancy</label>
                                                <input type="number" name="lodging_single_without_bill" min='0'
                                                    id="lodging_single_without_bill" class="form-control"
                                                    placeholder="S. Occupancy">
                                            </div>
                                            <div class="col-md-6">
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
                        <button type="submit" class="btn btn-outline-primary">Save</button>
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
                tableId: "travel-daily-allowance-and-lodging",
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
            $('#travel-policy-form').on('submit', function(e) {
                e.preventDefault();
                let id = $('#policy_id').val();
                const policy_category = $('#policy_category').val();
                const travel_type = $('#travel_type').val();
                const city_type = $('#city_type').val();
                const da_same_day = $('#da_same_day').val();
                const da_different_days = $('#da_different_days').val();
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
                if (!da_same_day) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please enter da same day amount',
                        timer: 3000,
                    });
                    return;
                }
                if (!da_different_days) {
                    Swal.fire({
                        icon: 'error',
                        text: 'Please enter da different day amount',
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
                    url: '{{ route('admin.create.update.lodging') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#policyModal').modal('hide');
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
            window.editPolicy = function(button) {
                // Retrieve data attributes from the button
                const id = $(button).data('id');
                const policyCategory = $(button).data('ptdal_ptc_id');
                const travelType = $(button).data('ptdal_pttt_id');
                const cityType = $(button).data('ptdal_ct_type_id');
                const daDifferentDays = $(button).data('ptdal_da_same_day_ret_elig');
                const sameDayDa = $(button).data('ptdal_da_per_day_elig');
                const lodgingSingleWithBill = $(button).data('ptdal_lodg_sngl_w_bill_elig');
                const lodgingDoubleWithBill = $(button).data('ptdal_lodg_dbl_w_bill_elig');
                const lodgingSingleWithoutBill = $(button).data('ptdal_lodg_sngl_wo_bill_elig');
                const lodgingDoubleWithoutBill = $(button).data('ptdal_lodg_dbl_wo_bill_elig');

                // Populate modal form fields
                $('#policy_id').val(id);
                $('#policy_category').val(policyCategory).trigger('change');
                $('#travel_type').val(travelType).trigger('change');
                $('#city_type').val(cityType).trigger('change');
                $('#city_type').val(cityType);
                $('#da_different_days').val(daDifferentDays);
                $('#da_same_day').val(sameDayDa);
                $('#lodging_single_with_bill').val(lodgingSingleWithBill);
                $('#lodging_double_with_bill').val(lodgingDoubleWithBill);
                $('#lodging_single_without_bill').val(lodgingSingleWithoutBill);
                $('#lodging_double_without_bill').val(lodgingDoubleWithoutBill);

                // Update modal title and show it
                $('#policyModalLabel').text('Update Policy');
                $('#policyModal').modal('show');
            }


            // Reset form when modal is closed
            $('#policyModal').on('hidden.bs.modal', function() {
                $('#travel-policy-form')[0].reset();
                $('#policy_id').val('');
                $('#policyModalLabel').text('Create New Policy');
            });

            // Delete policy
            window.deletePolicy = function(id) {
                if (confirm('Are you sure you want to delete this policy?')) {
                    $.ajax({
                        url: `/policies/${id}`,
                        method: 'DELETE',
                        success: function() {
                            alert('Policy deleted successfully!');
                            fetchPolicies();
                        }
                    });
                }
            };

            $(document).on('click', '.deleteTravelAllowance', function() {
                const daALId = $(this).data('id');
                var url = "{{ route('admin.delete.da.lodging') }}";
                // url = url.replace(':id', travelAllowanceId);
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will not be able to recover this daily allowance & lodging!',
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
                                'id': btoa(daALId),
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Daily allowance & Lodging has been deleted successfully.',
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
            $('#policyModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#policyModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
        });
    </script>
@endsection
