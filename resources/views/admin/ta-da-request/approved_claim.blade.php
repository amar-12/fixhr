<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\MasterTable;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\TadaClaim;

$user = Auth::user();
$employeeFilter = CommonUtils::getCustomModelData(new Employee(), [['method' => 'where', 'args' => ['emp_b_id', $user->emp_b_id]]]);
$branchFilter = CommonUtils::getCustomModelData(new Branch(), [['method' => 'where', 'args' => ['br_b_id', $user->emp_b_id]]]);
$gradeFilter = CommonUtils::getCustomModelData(new Grade(), [['method' => 'where', 'args' => ['g_b_id', $user->emp_b_id]]]);
$fromDateFilter = CommonUtils::getCustomModelData(new TadaClaim(), [['method' => 'where', 'args' => ['tc_b_id', $user->emp_b_id]]]);
$toDateFilter = CommonUtils::getCustomModelData(new TadaClaim(), [['method' => 'where', 'args' => ['tc_b_id', $user->emp_b_id]]]);
// $statusFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'APPROVAL_STATUS']]]);

$statusFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'APPROVAL_STATUS']], ['method' => 'whereIn', 'args' => ['m_id', [157, 451, 140, 172, 141, 412, 174, 170, 452]]]]);

$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    {{ $title }}
@endsection
@section('css')
    <style>
        /*#claim-request-table-dynamic tbody tr:hover {
            background-color: rgb(236, 236, 236);
            transition: background-color 0.2s ease-in-out;
            cursor: pointer;
        }*/
    </style>
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
                    <h4 class="card-title">Approved Claim </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="row justify-content-start">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <p class="form-label">Show entries</p>
                                        <select id="customLengthMenu" class="form-select-md p-2 search_test"
                                            style="width: 100%" data-length>
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <p class="form-label">Search</p>
                                        <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                            data-search />
                                    </div>
                                </div>
                            </div>
                        </div>









                        <div class="col-6">
                            <div class="row justify-content-end ms-5">
                                <div class="col-sm-2">
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
                                <div class="col-sm-2 "style="margin-top: 28px;"">
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
                               
        
        
                                <div class="col-sm-3" style="margin-top: 10px;">
                                    <div class="d-flex mt-5">
                                        {{-- Mark All --}}
                                        <label class="custom-control custom-checkbox-md mx-2">Select All &nbsp;&nbsp;
                                            <input type="checkbox" id="selectAll" class="custom-control-input-success"
                                                name="example-checkbox1" value="option1" onclick="selectAllCheckboxes(this)">
                                            <span class="custom-control-label-md success"></span>
                                        </label>
                                          </div>
                                    </div>
                            </div>


                                        <div class="row ">
                                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                                <div class="row">
                                                    <div class="col-md">
                                                        <label for="branchFilter" class="form-label">Employee</label>
                                                        <select id="claim_employeeFilter" data-filter
                                                            class="form-select search-txt filter_border">
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
                                                        <select id="claim_branchFilter" data-filter
                                                            class="form-select search-txt filter_border">
                                                            <option value="">All</option>
                                                            @foreach ($branchFilter as $branchF)
                                                                <option value="{{ $branchF->br_id }}">
                                                                    {{ $branchF->br_name }}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>

                                                    <div class="col-md">
                                                        <label for="designationFilter" class="form-label">Grade</label>
                                                        <select id="claim_gradeFilter" data-filter
                                                            class="form-select search-txt filter_border">
                                                            <option value="">All</option>
                                                            @foreach ($gradeFilter as $gradeF)
                                                                <option value="{{ $gradeF->g_id }}">{{ $gradeF->g_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md">
                                                        <label for="activeFilter" class="form-label">Status</label>
                                                        <select id="claim_activeFilter" data-filter
                                                            class="form-select search-txt filter_border">
                                                            <option value="">All</option>
                                                            @foreach ($statusFilter as $statusF)
                                                                <option value="{{ $statusF->m_id }}">
                                                                    {{ $statusF->m_name }}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <p class="form-label">Date Range</p>
                                                            <div class="input-group mb-3"
                                                                style="border-radius: 50px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                                                <span
                                                                    class="input-group-text bg-primary-subtle text-primary border-0"
                                                                    style="border-radius: 50px 0 0 50px; padding: 0.5rem 1rem;">
                                                                    <i class="las la-calendar-alt fs-5"></i>
                                                                </span>
                                                                <input type="text" id="fromDate" name="fromDate"
                                                                    class="form-control border-0"
                                                                    style="border-radius: 0 50px 50px 0; padding-left: 1rem;"
                                                                    data-date-filter="from-date"
                                                                    placeholder="Select date">
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
                                        <table class="table table-hover display table-vcenter text-wrap border-bottom"
                                            id="claim-request-table-dynamic">
                                            <thead>
                                                <tr>
                                                    @foreach ($columns as $column)
                                                        <th style="font-size:13px; width: {{ $column['width'] }};">
                                                            {{ $column['name'] }}
                                                        </th>
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
                                <div class="card-footer d-flex justify-content-end">
                                    <button class="btn btn-primary allPayedBtn" id="saveAllBtn"
                                        onclick="submitCheckboxButton()">Reimburse</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endsection
                @section('script')
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

                    <script type="text/javascript">
                        $(document).ready(function() {
                            // Initialize DataTable

                            datatable({
                                tableId: "claim-request-table-dynamic",
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

                        var claimIds = [];

                        function selectAllCheckboxes(element) {
                            var isChecked = $(element).is(':checked');
                            $('.testClass').prop('checked', isChecked);
                            if (isChecked) {
                                claimIds = [];
                                $('.testClass').each(function() {
                                    claimIds.push($(this).val());
                                });
                            } else {
                                claimIds = [];
                            }
                        }

                        function selectCheckbox(value) {
                            var claimId = $(value).val();
                            var isChecked = $(value).is(':checked');

                            if (isChecked) {
                                claimIds.push(claimId);
                            } else {
                                var index = claimIds.indexOf(claimId);
                                if (index !== -1) {
                                    claimIds.splice(index, 1);
                                }
                            }

                            var allChecked = true;
                            $('.testClass').each(function() {
                                if (!$(this).prop('checked')) {
                                    allChecked = false;
                                    return false;
                                }
                            });
                            $('#selectAll').prop('checked', allChecked);
                        }

                        function submitCheckboxButton() {
                            $('#saveAllBtn').prop('disabled', true);
                            if (claimIds.length === 0) {
                                Swal.fire({
                                    icon: 'error',
                                    text: "Please select at least one claim!",
                                    timer: 3000,
                                });
                                // alert('Please select at least one claim');
                                $('#saveAllBtn').prop('disabled', false);
                                return;
                            }

                            $.ajax({
                                type: 'POST',
                                url: '{{ route('claim.request.is.paid.store') }}', // Ensure this route is correct
                                data: {
                                    '_token': '{{ csrf_token() }}',
                                    'claim_ids': claimIds
                                },
                                success: function(response) {
                                    if (response.success) {
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
                                            text: "Claims not updated!",
                                            timer: 3000,
                                        });
                                        $('#saveAllBtn').prop('disabled', false);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    // $('#saveAllBtn').prop('disabled', false);
                                    // console.log(xhr.responseText);
                                    // Handle error response, e.g., show an error message to the user
                                }
                            });
                        }
                    </script>
                    <script>
                        $(document).on('click', '.revert-button', function(e) {
                            e.preventDefault();

                            let button = $(this);
                            let url = button.data('url'); // Get the URL from a data attribute on the button
                            let id = button.data('id'); // Get the ID from a data attribute on the button

                            Swal.fire({
                                title: 'Are you sure?',
                                text: 'This action cannot be Undo!',
                                icon: 'warning',
                                input: 'textarea', // Use a textarea input for the reason
                                inputPlaceholder: 'Please provide a reason for reverting...',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, revert it!',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    let reason = result.value; // This contains the remark entered by the user

                                    if (!reason || reason.trim() === "") {
                                        Swal.fire({
                                            icon: 'error',
                                            text: 'Please provide a reason for reverting!',
                                        });
                                        return;
                                    }

                                    $.ajax({
                                        url: url, // Use the correct actionUrl here
                                        method: 'POST',
                                        data: {
                                            '_token': '{{ csrf_token() }}',
                                            reason: reason,
                                            id: id,
                                        }, // Send the reason along with the request
                                        beforeSend: function() {
                                            button.attr('disabled', true);
                                        },
                                        success: function(response) {
                                            button.attr('disabled', false);
                                            Swal.fire({
                                                icon: 'success',
                                                text: response.message,
                                                timer: 3000,
                                            }).then(() => {
                                                location
                                                    .reload(); // Reload the page or dynamically update the UI
                                            });
                                        },
                                        error: function(xhr) {
                                            button.attr('disabled', false);
                                            let errorMessage = 'Something went wrong!';

                                            // Check if the response contains a message
                                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                                errorMessage = xhr.responseJSON.message;
                                            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                                                errorMessage = xhr.responseJSON.error;
                                            }

                                            Swal.fire({
                                                icon: 'error',
                                                text: errorMessage,
                                            });
                                        },
                                    });
                                }
                            });
                        });
                    </script>
                @endsection
