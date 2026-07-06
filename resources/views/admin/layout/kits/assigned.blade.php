@extends('admin.layout.master')
@section('title', 'Kit Assignments')

@section('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('css')
    <style>
        .fade-message {
            transition: opacity 0.5s ease;
        }

        .export-button,
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

        .export-button:hover,
        .custom-button:hover {
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
    </style>
@endsection

@section('content')
    {{-- Breadcrumbs --}}
    <div class="mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/kit') }}">Kits</a></li>
                    <li class="active"><span><b>Kit Assignments</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <!-- START ROW -->
    <div class="row mt-3">

        <!-- Total Assigned Kits -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <span class="font-weight-semibold">Total Assigned</span>
                            <h3 class="mb-0 mt-1 text-primary">{{ $totalAssigned }}</h3>
                        </div>
                        <div class="col-4">
                            <div class="icon1 bg-primary-transparent my-auto pt-3 float-end">
                                <i class="las la-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Returned Kits -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <span class="font-weight-semibold">Returned Kits</span>
                            <h3 class="mb-0 mt-1 text-success">{{ $totalReturned }}</h3>
                        </div>
                        <div class="col-4">
                            <div class="icon1 bg-success-transparent my-auto pt-3 float-end">
                                <i class="las la-undo"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lost Kits -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <span class="font-weight-semibold">Lost Kits</span>
                            <h3 class="mb-0 mt-1 text-danger">{{ $totalLost }}</h3>
                        </div>
                        <div class="col-4">
                            <div class="icon1 bg-danger-transparent my-auto pt-3 float-end">
                                <i class="las la-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Kits (optional: unique count) -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <span class="font-weight-semibold">Total Kits</span>
                            <h3 class="mb-0 mt-1 text-info">{{ $kits->count() }}</h3>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-info-transparent my-auto pt-3 float-end">
                                <i class="las la-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employees Assigned -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <span class="font-weight-semibold">Employees Assigned</span>
                            <h3 class="mb-0 mt-1 text-warning">{{ $employees->count() }}</h3>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-warning-transparent my-auto pt-3 float-end">
                                <i class="las la-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Assignments -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <span class="font-weight-semibold">Active Assignments</span>
                            <h3 class="mb-0 mt-1 text-secondary">
                                {{ $totalAssigned - $totalReturned - $totalLost }}
                            </h3>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-secondary-transparent my-auto pt-3 float-end">
                                <i class="las la-tasks"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- END ROW -->


    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Kit Assignments</h4>
                    <!-- Right Side Buttons -->
                    {{-- <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary fw-semibold" data-bs-toggle="modal"
                            data-bs-target="#addModal">
                            <i class="bi bi-plus-circle me-2"></i> Add Kit Stock
                        </button>

                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#kitDataModal">
                            View Kits
                        </button>
                    </div> --}}
                </div>

                <div class="card-body">
                    <div class="row align-items-end">

                        <!-- Show Entries -->
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label class="form-label">Show entries</label>
                                <select id="customLengthMenu" class="form-select form-select-sm search_test" data-length>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="col-sm-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                    data-search />
                            </div>
                        </div>

                        <!-- Empty Space -->
                        <div class="col-sm-6"></div>

                        <!-- Filters Button -->
                        <div class="col-sm-1">
                            <button class="custom-button w-100" type="button" onclick="toggleFilters()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.5">
                                    <line x1="3" y1="8" x2="21" y2="8"
                                        stroke-linecap="round" />
                                    <circle cx="10" cy="8" r="1.5" fill="currentColor" />
                                    <line x1="3" y1="16" x2="21" y2="16"
                                        stroke-linecap="round" />
                                    <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                </svg>
                                Filters
                            </button>
                        </div>

                        <!-- Export Button -->
                        <div class="col-sm-1 px-1">
                            <div class="dropdown">
                                <button class="export-button dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fa fa-download me-2"></i> Export As
                                </button>
                                <ul class="dropdown-menu dropdown-menu-export">
                                    <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <!-- FILTER SECTION -->
                    <div id="filterContainer" class="row mt-3" style="display:none;">

                        <div class="col-md-2">
                            <label class="form-label">Kit Name</label>
                            <select class="form-select filter_border" data-filter id="kitFilter">
                                <option value="">All</option>
                                @foreach ($kits as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Assigned To</label>
                            <select class="form-select filter_border" data-filter id="employeeFilter">
                                <option value="">All</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->emp_id }}">{{ $emp->emp_full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select filter_border" data-filter id="statusFilter">
                                <option value="">All</option>
                                <option value="assigned">Assigned</option>
                                <option value="returned">Returned</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary-subtle text-primary border-0">
                                    <i class="las la-calendar-alt fs-5"></i>
                                </span>
                                <input type="text" id="dateRange" data-filter class="form-control border-0"
                                    placeholder="Select Date">
                            </div>
                        </div>

                    </div>


                    <!-- TABLE -->
                    <table class="table table-hover table-vcenter text-wrap border-bottom mt-3" id="kit-assignment-table">
                        <thead>
                            <tr>
                                @foreach ($columns as $c)
                                    <th style="font-size:12px;width:{{ $c['width'] }}">{{ $c['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>


                    <!-- PAGINATION + ENTRIES -->
                    <div class="row mt-4">
                        <div class="col-sm-6">
                            <div id="custom-show-entries" data-show-entries></div>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-end">
                            <ul data-pagination class="custom-pagination"></ul>
                        </div>
                    </div>
                </div>

                <script>
                    function toggleFilters() {
                        const box = document.getElementById('filterContainer');
                        box.style.display = box.style.display === 'none' ? 'flex' : 'none';
                    }
                </script>

                <style>
                    .export-button,
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
                        transition: background-color .2s ease, box-shadow .2s ease;
                    }

                    .export-button:hover,
                    .custom-button:hover {
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
                </style>

            </div>
        </div>
    </div>


    <!-- RETURN QTY MODAL -->
    <div class="modal fade" id="returnQtyModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <form id="returnQtyForm" class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Return Quantity</h5>

                    <!-- Return Quantity Card -->
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body bg-light rounded-3">
                            <p class="mb-2 fw-bold">Kit Name: <span id="returnKitName">Example Kit</span></p>
                            <p class="mb-0 fw-bold">Total Quantity: <span id="returnTotalQty"
                                    class="badge bg-success">10</span></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="return_kit_id">

                    <div class="mb-3">
                        <label class="form-label">Return Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="return_qty" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Return Condition <span class="text-danger">*</span></label>
                        <select name="return_condition" class="form-select" required>
                            <option value="">Select</option>
                            <option value="good">Good</option>
                            <option value="damaged">Damaged</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success" type="submit">Submit</button>
                </div>

            </form>
        </div>
    </div>

    <!-- LOST QTY MODAL -->

    <div class="modal fade" id="lostQtyModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <form id="lostQtyForm" class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Lost Quantity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Card Section -->
                <div class="px-3 pt-2">
                    <div class="card shadow-sm border-0">
                        <div class="card-body bg-light rounded-3">
                            <p class="mb-2 fw-bold">Kit Name:
                                <span id="lostKitName">-</span>
                            </p>

                            <p class="mb-0 fw-bold">Available Quantity:
                                <span id="lostTotalQty" class="badge bg-secondary">0</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="lost_kit_id">
                    <div class="mb-1">
                        <label class="form-label">Lost Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" id="lost_qty_input" name="quantity" class="form-control"
                            required>
                        <small class="text-danger d-none" id="lostQtyError"></small>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Lost Cost <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="cost" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select" required>
                            <option value="">Select Reason</option>
                            <option value="misplaced">Misplaced</option>
                            <option value="stolen">Stolen</option>
                            <option value="damaged">Damaged Beyond Repair</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-danger" type="submit">Submit</button>
                </div>

            </form>
        </div>
    </div>




    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                }
            });

            $(document).on("click", ".returnQtyBtn", function() {
                let kitName = $(this).data("kit");
                let totalQty = $(this).data("total");

                $("#return_kit_id").val($(this).data("id"));
                $("#returnKitName").text(kitName);
                $("#returnTotalQty").text(totalQty);

                $("#returnQtyModal").modal("show");
            });

            $(document).on("click", ".lostQtyBtn", function() {
                let kitName = $(this).data("kit");
                let totalQty = $(this).data("total");

                $("#lost_kit_id").val($(this).data("id"));
                $("#lostKitName").text(kitName);
                $("#lostTotalQty").text(totalQty);

                $("#lostQtyModal").modal("show");
            });



            datatable({
                tableId: "kit-assignment-table",
                url: "{{ route('assignments.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: true
            });



            $('#lostQtyForm').on('submit', function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('assignments.lost') }}",
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());

                            $('#lostQtyModal').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },

                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMsg = '';

                        $.each(errors, function(key, value) {
                            errorMsg += value + '<br>';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMsg
                        });
                    }
                });
            });



            // RETURN QUANTITY AJAX SUBMIT
            $('#returnQtyForm').on('submit', function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('kit.return.qty') }}",
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());

                            $('#returnQtyModal').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },

                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMsg = '';

                        $.each(errors, function(key, value) {
                            errorMsg += value + '<br>';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMsg
                        });
                    }
                });
            });
        });
    </script>

@endsection
