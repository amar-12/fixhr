<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterTable;
use App\Models\Employee;

$user = Auth::user();
$employeeFilter = CommonUtils::getCustomModelData(new Employee(), [['method' => 'where', 'args' => ['emp_b_id', $user->emp_b_id]]]);
$branchFilter = CommonUtils::getCustomModelData(new Branch(), [['method' => 'where', 'args' => ['br_b_id', $user->emp_b_id]]]);
$gradeFilter = CommonUtils::getCustomModelData(new Grade(), [['method' => 'where', 'args' => ['g_b_id', $user->emp_b_id]]]);
$statusFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'APPROVAL_STATUS']]]);

$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    {{ $title }}
@endsection
@section('css')
    <style>
        @import url(https://fonts.googleapis.com/css?family=Open+Sans:600,400,300,300italic);

        .frame {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            height: 400px;
            margin-top: -200px;
            margin-left: -200px;
            border-radius: 2px;
            box-shadow: 1px 2px 10px 0 rgba(0, 0, 0, 0.3);
            background: #4CB6DE;
            color: #fff;
            font-family: 'Open Sans', Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .quote {
            position: relative;
            margin-top: 90px;
            padding: 0 30px;
        }

        .quote::before {
            content: '„';
            position: absolute;
            top: -100px;
            left: 7px;
            font-family: Arial;
            font-size: 250px;
            color: #6AC2E3;
            line-height: 35px;
        }

        .quote p {
            position: relative;
            font-size: 24px;
            line-height: 35px;
            margin: 20px 0;
        }

        .quote .author {
            font-weight: 300;
            font-style: italic;
            font-size: 20px;
            line-height: 28px;
        }

        .tooltipo {
            position: relative;
            display: inline-block;
            background: #41cbff;
            padding: 3px 7px 3px 6px;
            margin: -10px 0;
            cursor: pointer;
        }

        .tooltipo:hover .info,
        .tooltipo:focus .info {
            visibility: visible;
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }

        .info {
            position: absolute;
            bottom: 30px;
            left: -145px;
            background: #000;
            width: 300px;
            font-size: 16px;
            line-height: 24px;
            visibility: hidden;
            opacity: 0;
            transform: translate3d(0, -20px, 0);
            transition: all 0.5s ease-out;
        }

        .info::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 14px;
            bottom: -14px;
            left: 0;
        }

        .info::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            transform: rotate(45deg);
            bottom: -5px;
            left: 50%;
            margin-left: -5px;
            background: #286F8A;
        }

        .pronounce {
            display: block;
            background: #fff;
            color: #286F8A;
            padding: 8px 17px 10px 17px;
            line-height: 16px;
        }

        .pronounce .fa {
            margin-left: 10px;
            cursor: pointer;
            transition: all 0.2s ease-out;
        }

        .pronounce .fa:hover {
            transform: scale(1.15);
        }

        .text {
            display: block;
            padding: 13px 17px;
        }

    </style>
@endsection


@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "travel-request-table-dynamic",
                url: "{{ route(Route::currentRouteName()) }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false,
                drawCallback: function(settings) {
                    // Destroy existing popovers (if any)
                    $('[data-bs-toggle="popover"]').popover('dispose');

                    // Re-initialize popovers after each draw
                    $('[data-bs-toggle="popover"]').popover({
                        trigger: 'hover' // Example option, adjust as needed
                    });
                }
            });
        });
    </script>
          <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <script>
        $(function() {
            $('#fromDate').daterangepicker({
                startDate: moment().startOf('month'), // May 1, 2025
                endDate: moment().endOf('month'), // May 31, 2025
                minDate: moment('2000-01-01'), // Allow all past dates
                maxDate: moment().add(5, 'years'), // Allow future dates
                opens: 'left',
                autoApply: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Current Fiscal Period': [moment().startOf('quarter'), moment().endOf('quarter')],
                },
                locale: {
                    format: 'MMM D, YYYY'
                }
            });
        });
    </script>
@endsection

@section('content')
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/ta-da-request/{{ $titleRoute }}">Travel Management</a></li>
                        <li class="active"><span><b>{{ $title }}</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW -->
    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header border-0">
                    <h4 class="card-title">Travel Request</h4>
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





                        <div class="row ">
                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="row">
                                    <div class="col-md">
                                        <label for="branchFilter" class="form-label">Employee</label>
                                        <select id="travel_employeeFilter" data-filter class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($employeeFilter as $employeeF)
                                                <option value="{{ $employeeF->emp_id }}">
                                                    {{ ($employeeF->emp_code ? $employeeF->emp_code . ' - ' : '') . $employeeF->emp_full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="departmentFilter" class="form-label">Branch</label>
                                        <select id="travel_branchFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($branchFilter as $branchF)
                                                <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="designationFilter" class="form-label">Grade</label>
                                        <select id="travel_gradeFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($gradeFilter as $gradeF)
                                                <option value="{{ $gradeF->g_id }}">{{ $gradeF->g_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="activeFilter" class="form-label">Status</label>
                                        <select id="travel_statusFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($statusFilter as $statusF)
                                                <option value="{{ $statusF->m_id }}">{{ $statusF->m_name }}</option>
                                            @endforeach

                                        </select>
                                    </div>


                                       <div class="col-md-3">
                                            <div class="form-group">
                                                <p class="form-label">Date Range</p>
                                                <div class="input-group mb-3"
                                                    style="border-radius: 50px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                                    <span class="input-group-text bg-primary-subtle text-primary border-0"
                                                        style="border-radius: 50px 0 0 50px; padding: 0.5rem 1rem;">
                                                        <i class="las la-calendar-alt fs-5"></i>
                                                    </span>
                                                    <input type="text" id="fromDate" name="fromDate"
                                                        class="form-control border-0"
                                                        style="border-radius: 0 50px 50px 0; padding-left: 1rem;"
                                                        data-date-filter="from-date" placeholder="Select date">
                                                </div>
                                            </div>
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
                            id="travel-request-table-dynamic">
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

    {{-- Advance Model --}}
    <div class="modal fade" id="createTravelAdvanceModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title" id="modalTitle">Add Advance</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" method="POST" action="{{ route('advancelog.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input id="advanceID" name="advance_id" type="hidden" class="form-control">
                        <input id="travelID" name="plan_id" type="hidden" class="form-control">
                        <label for="travel" class="form-label mb-1 mt-3">Travel ID<span
                                class="text-red">*</span></label>
                        <input id="travel" name="plan" type="text" class="form-control" readonly>

                        <label for="editPurpose" class="form-label mb-1 mt-3">Advance Amount<span
                                class="text-red">*</span></label>
                        <input id="advance_amt" name="requested_amount" type="number" class="form-control"
                            placeholder="Enter Advance Amount" required min="0">

                        <label for="remark" class="form-label mb-1 mt-3">Remark<span class="text-red">*</span></label>
                        <textarea name="remark" id="remark" rows="5" class="form-control" placeholder="Enter Remark" required></textarea>
                    </div>

                    <div class="modal-footer d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary saveUptBtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('advanceform');
            const editIdInput = document.getElementById('advanceID');

            // Function to set form action
            function setFormAction() {
                if (editIdInput.value) {
                    form.action = "{{ route('advancelog.update') }}"; // Corrected single quote in the route name
                } else {
                    form.action = "{{ route('advancelog.store') }}"; // Corrected single quote in the route name
                }
            }

            // Call setFormAction when the modal is shown
            $('#createTravelAdvanceModal').on('shown.bs.modal', setFormAction);
        });


        function openEditRole(context) {
            $('#department-update-error').html('');

            const id = context.dataset.id;
            const departmentId = context.dataset.amount;
            const purpose = context.dataset.remark;

            // Set values for editing
            $('#travel').val(id);
            $('#advance_amt').val(amount);
            $('#remark').val(remark);

            // Show the modal
            new bootstrap.Modal(document.getElementById('createTravelAdvanceModal')).show();
        }
    </script> --}}



    <script>
        $(document).on('click', '.action-btns', function(event) {
            event.preventDefault();

            var planId = $(this).data('id');
            var uniqueId = $(this).data('unique-id');
            var advanceId = $(this).data('advance-id');
            var amount = $(this).data('amount');
            var remark = $(this).data('remark');

            $('#travelID').val(planId);
            $('#travel').val(uniqueId);
            $('#advanceID').val(uniqueId);
            $('#advance_amt').val(amount);
            $('#remark').val(remark);
        });

        //*************** js for only accept positivbe numbers start
        document.getElementById('advance_amt').addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
        //*************** js for only accept positivbe numbers end

        // Form submission using AJAX
        $(document).ready(function() {
            $('#advanceform').on('submit', function(event) {
                event.preventDefault();

                let formDataArray = $(this).serializeArray();
                let formDataObject = {};

                // Convert form data to an object
                $.each(formDataArray, function(index, field) {
                    if (field.value) {
                        formDataObject[field.name] = field.value;
                    }
                });

                // Prepare FormData for file upload (if applicable)
                let ajaxData = new FormData();
                $.each(formDataObject, function(key, value) {
                    ajaxData.append(key, value);
                });

                // Determine the form's action URL (store or update)
                let formAction = $('#advanceform').attr('action');

                $.ajax({
                    url: formAction, // Use the dynamic action URL
                    method: 'post',
                    data: ajaxData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(data) {
                        Swal.fire({
                            position: 'top-end',
                            icon: data.status ? 'success' : 'warning',
                            title: data.status ?
                                'Advance Amount created successfully' : data.message,
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: {
                                toast: 'swal2-toast-green-glow'
                            }
                        });

                        if (data.status) {
                            window.location.href = '{{ route('travel.request.index2') }}';
                            $('#createTravelAdvanceModal').modal('hide');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'Error occurred',
                            text: error,
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                });
            });
        });
    </script>
@endsection
