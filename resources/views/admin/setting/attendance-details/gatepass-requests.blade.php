@extends('admin.layout.master')
@section('title')
Gate Pass
@endsection
@section('css')

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
@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "gate-pass-table-dynamic",
                url: "{{ route('requests.gate-pass') }}",
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
<div>
    <div class=" p-0 pb-4">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a class="text-white">Requests</a></li>
            <li class="active"><span><b>Gate Pass</b></span></li>
        </ol>
    </div>

    <!-- ROW -->
    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Gate Pass</h4>
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

                        {{-- <div class="col-sm-2" style=" margin-top: 10px;">
                            <div id="approval-buttons" class="justify-content-end gap-3 m-5">
                                <label class="custom-control custom-checkbox-md mx-3">Select All &nbsp;&nbsp;
                                    <input type="checkbox" id="selectAll" class="custom-control-input-success"
                                        name="example-checkbox1" value="option1" onclick="selectAllCheckboxes(this)">
                                    <span class="custom-control-label-md success"></span>
                                </label>

                            </div>
                        </div> --}}


                        <div class="col-sm-1">
                            <button class="custom-button w-100" type="button" onclick="toggleFilters()"
                                style="margin-top: 28px;">
                                <!-- Custom SVG: 2 horizontal lines with knobs -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5">
                                    <!-- Top slider -->
                                    <line x1="3" y1="8" x2="21" y2="8" stroke-linecap="round" />
                                    <circle cx="10" cy="8" r="1.5" fill="currentColor" />

                                    <!-- Bottom slider -->
                                    <line x1="3" y1="16" x2="21" y2="16" stroke-linecap="round" />
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




                        {{-- <div class="col-sm-1" style="margin-top: 30px;">
                            <div class="form-group">
                                <button class="btn btn-outline-danger" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal"
                                            data-bs-target="#addLeaveBalanceFile">
                                            Upload File
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('leave.downloadExcel') }}">
                                            Export Format
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div> --}}

                        <div class="row">
                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="row">
                                    <div class="col-sm-2">
                                        <label for="branchFilter" class="form-label">Branch</label>
                                        <select id="gate-passbranchFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($branch as $branchF)
                                            <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-2">
                                        <label for="departmentFilter" class="form-label">Department</label>
                                        <select id="gate-passdepartmentFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($departments as $departmentF)
                                            <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}</option>
                                            @endforeach

                                        </select>
                                    </div>

                                    <div class="col-sm-2">
                                        <label for="designationFilter" class="form-label">Designation</label>
                                        <select id="gate-passdesignationFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($designations as $designationF)
                                            <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-2">
                                        <label for="activeFilter" class="form-label">Employee Status</label>
                                        <select id="gate-passactiveFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            <option value="71">Active</option>
                                            <option value="72">Inactive</option>
                                        </select>
                                    </div>

                                    {{-- Gate Pass Status --}}
                                    <div class="col-md">
                                        <label for="gate_pass_statusFilter" class="form-label">Gate Pass Status</label>
                                        <select id="gate_pass_statusFilter" data-filter
                                            class="form-select search-txt filter_border filter_border">
                                            <option value="">All</option>
                                            @foreach ($gate_pass_status as $status)
                                            <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-2">
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
                        <table class="table display  table-hover table-vcenter text-wrap border-bottom"
                            id="gate-pass-table-dynamic">
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

</div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    //excel upload file sweet alert
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>