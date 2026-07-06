<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$branchFilter = CommonUtils::getCustomModelData(new Branch(), [['method' => 'where', 'args' => ['br_b_id', $user->emp_b_id]]]);
$departmentFilter = CommonUtils::getCustomModelData(new Department(), [['method' => 'where', 'args' => ['d_b_id', $user->emp_b_id]]]);
$designationFilter = CommonUtils::getCustomModelData(new Designation(), [['method' => 'where', 'args' => ['dg_b_id', $user->emp_b_id]]]);
$gradeFilter = CommonUtils::getCustomModelData(new Grade(), [['method' => 'where', 'args' => ['g_b_id', $user->emp_b_id]]]);

$permission = new RolePermissionLogics();
?>

@extends('admin.layout.master')
@section('title')
    Device Verification
@endsection
@section('css')
<style>
    /* Action buttons styling */
    #verify-selected-btn,
    #reject-selected-btn {
        min-width: 90px;
        height: 30px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 30px;
        border: none;
        transition: all 0.3s ease;
        margin: 0 5px;
    }
    
    .badge_rounded {
        border-radius: 30px !important;
    }
    #verify-selected-btn {
        background-color: #28a745;
        color: white;
    }
    
    #verify-selected-btn:hover:not(:disabled) {
        background-color: #218838;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }
    
    #reject-selected-btn {
        background-color: #dc3545;
        color: white;
    }
    
    #reject-selected-btn:hover:not(:disabled) {
        background-color: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }
    
    #verify-selected-btn:disabled,
    #reject-selected-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    /* Make global loader transparent only on this page */
    #global-loader {
        background: none !important;
    }
    input[type="checkbox"] {
      transform: scale(1.2);         /* Increase size */
      margin: 10px;                  /* Add spacing */
      accent-color: #1877f2;         /* Change check color (modern browsers) */
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
    .card-custom {
        padding: 10px !important;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            font-size: 13px;
            margin-bottom: 0.25rem;
        }
        
        .custom-button,
        .export-button {
            font-size: 12px;
            padding: 6px 10px;
        }
        
        #verify-selected-btn,
        #reject-selected-btn {
            min-width: 70px;
            height: 28px;
            font-size: 12px;
            margin: 0 2px;
        }
        
        .dropdown-menu-export {
            min-width: 120px;
            font-size: 12px;
        }
        
        .table-responsive {
            font-size: 12px;
        }
        
        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1rem;
        }
        
        .form-group {
            margin-bottom: 0.75rem;
        }
        
        .custom-button,
        .export-button {
            font-size: 11px;
            padding: 5px 8px;
        }
        
        #verify-selected-btn,
        #reject-selected-btn {
            min-width: 60px;
            height: 26px;
            font-size: 11px;
            margin: 0 1px;
        }
        
        .table-responsive {
            font-size: 11px;
        }
        
        .table th,
        .table td {
            padding: 0.25rem 0.125rem;
        }
        
        .breadcrumb {
            font-size: 12px;
        }
        
        .card-title {
            font-size: 1.1rem;
        }
    }

    /* Ensure proper spacing on all devices */
    .row.g-3 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 0.75rem;
    }

    /* Ensure dropdowns work properly on mobile */
    .dropdown-menu {
        max-height: 200px;
        overflow-y: auto;
    }

    .editable {
        cursor: pointer;
        padding: 2px 5px;
        border-radius: 4px;
    }

    .editable:hover {
        background-color: #f1f1f1;
    }
</style>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        var table = $('#selfie-verification-table').DataTable({
            processing: false, // Disable default DataTables processing overlay
            serverSide: true,
            ajax: {
                url: "{{ route('admin.requests.selfie_verification.datatable') }}",
                data: function(d) {
                    // Add filter values to the request
                    d.daily_branchFilter = $('#daily_branchFilter').val();
                    d.daily_departmentFilter = $('#daily_departmentFilter').val();
                    d.daily_designationFilter = $('#daily_designationFilter').val();
                    d.daily_activeFilter = $('#daily_activeFilter').val();
                    d.fromDate = $('#fromDate').val();
                }
            },
            dom: 'rtip',
            buttons: [
                {
                    extend: 'excel',
                    title: 'Device Verification List',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'csv',
                    title: 'Device Verification List',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdf',
                    title: 'Device Verification List',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    },
                    customize: function (doc) {
                        doc.styles.tableHeader.alignment = 'left';
                        doc.styles.tableHeader.fontSize = 12;
                        doc.defaultStyle.fontSize = 10;
                    }
                },
                {
                    extend: 'print',
                    title: 'Device Verification List',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ],
            pageLength: 5,
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });

        // Show global loader only on search
        var lastSearch = '';
        table.on('preXhr.dt', function(e, settings, data) {
            var currentSearch = data.search.value;
            // Show loader only if search value changed and is not empty
            if (lastSearch !== currentSearch && currentSearch !== '') {
                $('#global-loader').show();
            }
        });
        table.on('xhr.dt', function() {
            $('#global-loader').fadeOut('slow');
            lastSearch = table.search();
        });

        // Add checkboxes to the last column after data is loaded
        table.on('draw', function() {
            $('#selfie-verification-table tbody tr').each(function() {
                var row = table.row(this);
                var data = row.data();
                if (data) {
                    var id = data[0]; // ID column
                    // Only add checkbox if not already present
                    if (!$(this).find('.row-checkbox').length) {
                        $(this).find('td').last().html('<input type="checkbox" class="row-checkbox" value="'+id+'">');
                    }
                }
            });
            // Uncheck select-all on redraw and reset button states
            $('#select-all').prop('checked', false);
            $('#verify-selected-btn').prop('disabled', true);
            $('#reject-selected-btn').prop('disabled', true);
        });

        // Select All logic
        $('#select-all').on('change', function() {
            var checked = $(this).prop('checked');

            $('.row-checkbox').prop('checked', checked);

            // Enable/Disable buttons
            var anyChecked = $('.row-checkbox:checked').length > 0;
            $('#verify-selected-btn').prop('disabled', !anyChecked);
            $('#reject-selected-btn').prop('disabled', !anyChecked);
        });

        // Combined event handler for row checkboxes
        $(document).on('change', '.row-checkbox', function() {
            // Enable/disable Verify Selected and Reject Selected buttons
            var anyChecked = $('.row-checkbox:checked').length > 0;
            $('#verify-selected-btn').prop('disabled', !anyChecked);
            $('#reject-selected-btn').prop('disabled', !anyChecked);
        });

        function handleBulkAction(action) {
            var ids = [];
            $('.row-checkbox:checked').each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) return;

            let confirmBox = Promise.resolve({ isConfirmed: true });

            // Only confirm for reject
            if (action === 'reject') {
                confirmBox = Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to reject selected devices?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, Reject'
                });
            }

            confirmBox.then((result) => {
                if (!result.isConfirmed) return;

                let $btn = action === 'verify'
                    ? $('#verify-selected-btn')
                    : $('#reject-selected-btn');

                let originalText = $btn.text();

                $btn.prop('disabled', true).text(action === 'verify' ? 'Verifying...' : 'Rejecting...');

                $.ajax({
                    url: "{{ route('admin.requests.selfie_verification.bulk_action') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
                        action: action
                    },
                    success: function(res) {
                        table.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message,
                            showConfirmButton: false,
                            timer: 2000,
                            toast: true,
                            position: 'top-end'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong'
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            });
        }

        $('#verify-selected-btn').on('click', function() {
            handleBulkAction('verify');
        });

        $('#reject-selected-btn').on('click', function() {
            handleBulkAction('reject');
        });

        // When custom length menu changes, update DataTables page length
        $('#customLengthMenu').on('change', function() {
            table.page.len($(this).val()).draw();
        });

        // Custom search box
        $('#searchFilter').on('keyup change', function() {
            table.search(this.value).draw();
        });

        // Filter handlers
        $('[data-filter]').on('change', function() {
            table.ajax.reload();
        });

        // Date filter handler
        $('[data-date-filter]').on('change', function() {
            table.ajax.reload();
        });

        // Custom export dropdown handler
        $('.export-action').on('click', function(e) {
            e.preventDefault();
            var type = $(this).data('export-type');
            switch(type) {
                case 'csv': table.button('.buttons-csv').trigger(); break;
                case 'excel': table.button('.buttons-excel').trigger(); break;
                case 'pdf': table.button('.buttons-pdf').trigger(); break;
                case 'copy': table.button('.buttons-copy').trigger(); break;
                case 'print': table.button('.buttons-print').trigger(); break;
            }
        });


    });

    // Click → convert to input
    $(document).on('click', '.edit-row', function () {
        var row = $(this).closest('tr');

        row.find('.editable').each(function () {
            var value = $(this).text().trim();

            $(this).html(`
                <input type="text" class="form-control form-control-sm edit-input" value="${value}">
            `);
        });

        // toggle icons
        row.find('.edit-row').addClass('d-none');
        row.find('.save-row').removeClass('d-none');
    });

    $(document).on('click', '.save-row', function () {
        var row = $(this).closest('tr');
        var id = $(this).data('id');

        // get updated values
        var device_id = row.find('.device_id input').val();
        var device_name = row.find('.device_name input').val();
        var device_model = row.find('.device_model input').val();

        $.ajax({
            url: "{{ route('admin.requests.selfie_verification.inline_update') }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                sv_device_id: device_id,
                sv_device_name: device_name,
                sv_device_modal: device_model
            },
            success: function (res) {

                // replace input with text
                row.find('.device_id').html(device_id);
                row.find('.device_name').html(device_name);
                row.find('.device_model').html(device_model);

                // toggle icons
                row.find('.edit-row').removeClass('d-none');
                row.find('.save-row').addClass('d-none');

                Swal.fire({
                    icon: 'success',
                    text: 'Updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                let errorMessage = 'Update failed';
                if (xhr.responseJSON) {

                    // single message
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    // multiple validation errors
                    if (xhr.responseJSON.errors) {
                        let errors = Object.values(xhr.responseJSON.errors)
                            .map(err => err[0]) // first error of each field
                            .join('<br>');

                        errorMessage = errors;
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage,
                });
            }
        });
    });

    function toggleFilters() {
         const container = document.getElementById('filterContainer');
         container.style.display = container.style.display === 'none' ? 'flex' : 'none';
             }

</script>
@endsection
@section('content')
<div>
    <div class="p-0 pb-4">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a class="text-white">Requests</a></li>
            <li class="active"><span><b>Selfie Verification</b></span></li>
        </ol>
    </div>
    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card card-custom">
                <div class="card-header border-0">
                    <h4 class="card-title">Selfie Verification</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Show entries - Responsive -->
                        <div class="col-lg-1 col-md-2 col-sm-3 col-6">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test w-100" data-length>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Search - Responsive -->
                        <div class="col-lg-2 col-md-2 col-sm-4 col-6">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control" data-search />
                            </div>
                        </div>

                        <!-- Spacer - Responsive -->
                        <div class="col-lg-5 col-md-5 col-sm-5 d-none d-sm-block">
                        </div>

                        <!-- Filters button - Responsive -->
                        <div class="col-lg-1 col-md-1 col-sm-2 col-6 mt-2">
                            <div class="form-group">
                                <p class="form-label d-none d-md-block">&nbsp;</p>
                                <button class="custom-button w-100" type="button" onclick="toggleFilters()">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5" class="d-none d-sm-inline">
                                        <line x1="3" y1="8" x2="21" y2="8" stroke-linecap="round" />
                                        <circle cx="10" cy="8" r="1.5" fill="currentColor" />
                                        <line x1="3" y1="16" x2="21" y2="16" stroke-linecap="round" />
                                        <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                    </svg>
                                    <span class="d-sm-none">Filter</span>
                                    <span class="d-none d-sm-inline">Filters</span>
                                </button>
                            </div>
                        </div>

                        <!-- Export button - Responsive -->
                        <div class="col-lg-1 col-md-1 col-sm-2 col-6 mt-2">
                            <div class="form-group">
                                <p class="form-label d-none d-md-block">&nbsp;</p>
                                <div class="dropdown">
                                    <button class="export-button dropdown-toggle w-130" type="button" id="exportDropdown"
                                        data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                        <i class="fa fa-download me-1 me-sm-2"></i>
                                        <span class="d-none d-sm-inline">Export As</span>
                                        <span class="d-sm-none">Export</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="exportDropdown">
                                        <li><a class="dropdown-item export-action" href="#" data-export-type="csv">CSV</a></li>
                                        <li><a class="dropdown-item export-action" href="#" data-export-type="excel">Excel</a></li>
                                        <li><a class="dropdown-item export-action" href="#" data-export-type="pdf">PDF</a></li>
                                        <li><a class="dropdown-item export-action" href="#" data-export-type="copy">Copy</a></li>
                                        <li><a class="dropdown-item export-action" href="#" data-export-type="print">Print</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons - Responsive -->
                        <div class="col-lg-2 col-md-2 col-sm-4 col-12 mt-3">
                            <div class="form-group">
                                <p class="form-label d-none d-md-block">&nbsp;</p>
                                <div class="d-flex flex-wrap justify-content-start justify-content-sm-end">
                                    <button id="verify-selected-btn" disabled class="btn-verify">Verify</button>
                                    <button id="reject-selected-btn" disabled class="btn-reject">Reject</button>
                                </div>
                            </div>
                        </div>
                    </div>

                        <div class="row">
                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="row g-3">
                                    <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                                        <label for="branchFilter" class="form-label">Branch</label>
                                        <select id="daily_branchFilter" data-filter class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($branchFilter as $branchF)
                                                <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                                        <label for="departmentFilter" class="form-label">Department</label>
                                        <select id="daily_departmentFilter" data-filter class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($departmentFilter as $departmentF)
                                                <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                                        <label for="designationFilter" class="form-label">Designation</label>
                                        <select id="daily_designationFilter" data-filter class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            @foreach ($designationFilter as $designationF)
                                                <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                                        <label for="activeFilter" class="form-label">Status</label>
                                        <select id="daily_activeFilter" data-filter class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            <option value="71">Active</option>
                                            <option value="72">Inactive</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                                        <label for="toDate" class="form-label">Date</label>
                                        <input type="date" id="fromDate" value="" class="form-control filter_border" data-date-filter="from-date" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="selfie-verification-table">
                            <thead>
                                <tr>
                                    <th>S No.</th>
                                    <th>Emp Code</th>
                                    <th>Emp Name</th>
                                    <th>Device ID</th>
                                    <th>Device Name</th>
                                    <th>Device Model</th>
                                    <th>Status</th>
                                    <th>Requested Date</th>
                                    <th>Time</th>
                                    <th><input type="checkbox" id="select-all"></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="row mt-5">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12 mb-3 mb-sm-0">
                            <div id="custom-show-entries" data-show-entries></div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12 d-flex justify-content-start justify-content-sm-end">
                            <ul data-pagination class="custom-pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
