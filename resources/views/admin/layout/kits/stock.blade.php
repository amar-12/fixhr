@extends('admin.layout.master')
@section('title', 'Stock')
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
                    <li class="active"><span><b> Stock</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <!-- START ROW -->
    <div class="row mt-3">
        <!-- Total -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <div class="text-start">
                                <span class="font-weight-semibold">Total Stock</span>
                                <h3 class="mb-0 mt-1 text-success">{{ $total_kits }}</h3>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="icon1 bg-success-transparent my-auto pt-3 float-end">
                                <i class="las la-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kits in Stock -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">In Stock</span>
                                <h3 class="mb-0 mt-1 text-warning">{{ $in_stock_kits }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-warning-transparent my-auto pt-3 float-end">
                                <i class="las la-warehouse"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Kits -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Assigned Stock </span>
                                <h3 class="mb-0 mt-1 text-primary">{{ $assigned_kits }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-primary-transparent my-auto pt-3 float-end">
                                <i class="las la-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Damaged Kits -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Damaged Stock</span>
                                <h3 class="mb-0 mt-1 text-danger">{{ $damaged_kits }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-danger-transparent my-auto pt-3 float-end">
                                <i class="las la-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Replaced Kits -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Replaced Stock</span>
                                <h3 class="mb-0 mt-1 text-info">{{ $replaced_kits }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-info-transparent my-auto pt-3 float-end">
                                <i class="las la-sync-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lost / Missing Kits (Optional) -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Lost / Missing</span>
                                <h3 class="mb-0 mt-1 text-secondary">{{ $lost_kits }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-secondary-transparent my-auto pt-3 float-end">
                                <i class="las la-question-circle"></i>
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
                    <h4 class="card-title">Stock</h4>
                    <!-- Right Side Buttons -->
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle"></i> Add
                        </button>

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

                        <div
                            class="col-sm-1"style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px; ">
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

                                <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown" style="min-width: 220px;">
                                    <li class="mb-2">
                                        <button
                                            type="button"class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#stockItemsModalTable">
                                            <i class="bi bi-eye me-1"></i> View Items
                                        </button>
                                    </li>
                                    <li class="mb-2">
                                        <button
                                            type="button"class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#unitModaltable">
                                            <i class="bi bi-rulers me-1"></i> View Units
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            type="button"class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#categoryModaltable">
                                            <i class="bi bi-tags me-1"></i> View Categories
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- FILTER SECTION -->
                        <div id="filterContainer" class="row mt-3" style="display:none;">
                            <div class="col-md-2">
                                <label class="form-label">Name</label>
                                <select class="form-select filter_border" data-filter id="kitNameFilter">
                                    <option value="">All</option>
                                    @foreach ($kits_data->unique('name') as $k)
                                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select class="form-select filter_border" data-filter id="kitTypeFilter">
                                    <option value="">All</option>
                                    @foreach ($kits_data->unique('type') as $k)
                                        <option value="{{ $k->id }}">{{ ucfirst($k->type) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Size</label>
                                <select class="form-select filter_border" data-filter id="kitSizeFilter">
                                    <option value="">All</option>
                                    @foreach ($kits_data->unique('size') as $k)
                                        <option value="{{ $k->id }}">{{ $k->size }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select class="form-select filter_border" data-filter id="statusFilter">
                                    <option value="">All</option>
                                    <option value="in_stock">In Stock</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="lost">Lost</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Date Range</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary-subtle text-primary border-0">
                                        <i class="las la-calendar-alt fs-5"></i>
                                    </span>
                                    <input type="text" id="fromDate" data-filter name="fromDate"
                                        class="form-control border-0" placeholder="From Date">
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

                    <!-- TABLE -->
                    <table class="table table-hover table-vcenter text-wrap border-bottom mt-3" id="kit-stock-table">
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
            </div>
        </div>
    </div>

    <!-- Stock Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Create Category </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <div class="mb-3">
                            <input type="hidden" id="categoryId" name="sc_id">
                            <label for="categoryName" class="form-label">Category Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryName" name="sc_name"
                                placeholder="Enter category name" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="sc_description" rows="3"
                                placeholder="Enter category description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="saveCategory()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Category Table Modal -->
    <div class="modal fade" id="categoryModaltable" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5> Categories</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>

                    <!-- Category List Header -->
                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill d-flex align-items-center"
                        data-bs-toggle="modal" data-bs-target="#categoryModal" data-bs-dismiss="modal">
                        <i class="bi bi-plus me-1"></i> Add
                    </button>

                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped" id="categoryTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $i => $cat)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $cat->sc_name }}</td>
                                    <td>{{ $cat->sc_description }}</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal"
                                            onclick="editCategory({{ $cat->sc_id }}, '{{ addslashes($cat->sc_name) }}', '{{ addslashes($cat->sc_description ?? '') }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Unit Modal -->
    <div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unitModalLabel">Crate Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="unitForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="hidden" id="unit_id" name="su_id">
                                    <label for="su_name" class="form-label">Unit Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="su_name" id="su_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="su_short_name" class="form-label">Short Name</label>
                                    <input type="text" name="su_short_name" id="su_short_name" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="su_description" class="form-label">Description</label>
                                    <textarea name="su_description" id="su_description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="saveUnit()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Units Table Modal -->
    <div class="modal fade" id="unitModaltable" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Units</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>

                    <!-- Add Unit Button -->
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill d-flex align-items-center"
                        data-bs-toggle="modal" data-bs-target="#unitModal" data-bs-dismiss="modal">
                        <i class="bi bi-plus me-1"></i> Add
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped" id="unitTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Short Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($units as $i => $unit)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $unit->su_name }}</td>
                                    <td>{{ $unit->su_short_name }}</td>
                                    <td>{{ $unit->su_description }}</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal"
                                            onclick="editUnit({{ $unit->su_id }}, '{{ addslashes($unit->su_name) }}', '{{ addslashes($unit->su_short_name ?? '') }}', '{{ addslashes($unit->su_description ?? '') }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Item Modal -->
    <div class="modal fade" id="stockItemModal" tabindex="-1" aria-labelledby="stockItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="stockItemModalLabel">Create Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="stockItemForm">
                        @csrf
                        <input type="hidden" id="item_id" name="item_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="item_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" id="item_description" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Size <span class="text-danger">*</span></label>
                                <input type="text" name="size" id="size" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->sc_id }}">{{ $cat->sc_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <select name="stock_unit_id" id="stock_unit_id" class="form-control" required>
                                    <option value="">-- Select Unit --</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->su_id }}">{{ $unit->su_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="stockItemSaveBtn">Save</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Stock Items Table Modal -->
    <div class="modal fade" id="stockItemsModalTable" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Items</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>

                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill ms-2"
                        data-bs-toggle="modal" data-bs-target="#stockItemModal" onclick="resetStockItemForm()">
                        <i class="bi bi-plus me-1"></i>Add
                    </button>
                </div>

                <div class="modal-body">
                    <table id="stockItemsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Size</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($kits_data as $i => $item)
                                <tr id="itemRow{{ $item->id }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->description ?? '-' }}</td>
                                    <td>{{ $item->size ?? '-' }}</td>
                                    <td>{{ $item->fh_category->sc_name ?? '-' }}</td>
                                    <td>{{ $item->fh_unit->su_name ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#stockItemModal"
                                            onclick="openStockItemModal(
                                            {{ $item->id }},
                                            '{{ addslashes($item->name) }}',
                                            '{{ addslashes($item->description) }}',
                                            '{{ addslashes($item->size) }}',
                                            {{ $item->kt_category_id }},
                                            {{ $item->kt_unit_id }}
                                        )">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="deleteStockItem({{ $item->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Damaged Modal -->
    <div class="modal fade" id="damagedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Damaged Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body bg-light rounded-3">
                            <p class="mb-2 fw-bold">Stock Name: <span id="damagedKitName">Example Stock</span></p>
                            <p class="mb-0 fw-bold">Total Quantity: <span
                                    id="damagedTotalQty"class="badge bg-success">10</span></p>
                        </div>
                    </div>

                    <form id="damagedForm">
                        <input type="hidden" name="id" id="damagedId">
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="damagedForm" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lost Modal -->
    <div class="modal fade" id="lostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lost Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body bg-light rounded-3">
                            <p class="mb-2 fw-bold">Stock Name: <span id="lostKitName">Example Stock</span></p>
                            <p class="mb-0 fw-bold">Total Quantity: <span
                                    id="lostTotalQty"class="badge bg-success">10</span></p>
                        </div>
                    </div>


                    <form id="lostForm">
                        <input type="hidden" name="id" id="lostId">
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="lostForm" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Replaced Modal -->
    <div class="modal fade" id="replacedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Replaced Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body bg-light rounded-3">
                            <p class="mb-2 fw-bold">Stock Name: <span id="replacedKitName">Example Stock</span></p>
                            <p class="mb-0 fw-bold">Total Quantity: <span
                                    id="replacedTotalQty"class="badge bg-success">10</span></p>
                        </div>
                    </div>

                    <form id="replacedForm">
                        <input type="hidden" name="id" id="replacedId">
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="replacedForm" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Modal -->
    <div class="modal fade" id="assignedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body bg-light rounded-3">
                            <p class="mb-2 fw-bold">Stock Name: <span id="assignedKitName">Example Stock</span></p>
                            <p class="mb-0 fw-bold">Total Quantity: <span
                                    id="assignedTotalQty"class="badge bg-success">10</span></p>
                        </div>
                    </div>

                    <form id="assignedForm">
                        <input type="hidden" name="id" id="assignedId">
                        <input type="hidden" name="emp_id" id="assignedEmpId">

                        <div class="mb-3">
                            <label>Select Employee</label>
                            <select id="employeeDropdown" class="form-control search_test" required>
                                <option value="">-- Select Employee --</option>
                                @foreach ($employee as $emp)
                                    <option value="{{ $emp->emp_id }}">
                                        {{ $emp->emp_full_name }} ({{ $emp->emp_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="assignedForm" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Stock Items  --}}

    <div class="modal fade" id="addModal">
        <div class="modal-dialog modal-lg">
            <form id="addForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Stock</h5>
                    </div>

                    <div class="row p-3">

                        <!-- Stock -->
                        <div class="col-lg-3">
                            <label>Stock <span class="text-danger">*</span></label>
                            <select name="kit_id" class="form-control search_test" required>
                                @foreach ($kits_data as $kit)
                                    <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Opening Qty -->
                        <div class="col-lg-3">
                            <label>Opening Item <span class="text-danger">*</span></label>
                            <input type="number" name="opening_qty" id="opening_qty" class="form-control" required>
                        </div>

                        <!-- Price Per Unit -->
                        <div class="col-lg-3">
                            <label>Price Per Unit <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_unit" id="price_per_unit" class="form-control"
                                required>
                        </div>

                        <!-- Total Price -->
                        <div class="col-lg-3">
                            <label>Total Price <span class="text-danger">*</span></label>
                            <input type="number" name="total_price" id="total_price" class="form-control" readonly
                                required>
                        </div>

                        <!-- Payable Dropdown -->
                        <div class="col-lg-3">
                            <label>Payable <span class="text-danger">*</span></label>
                            <select name="is_payable" id="is_payable" class="form-control" required>
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <label>Discount Type</label>
                            <select name="discount_type" id="discount_type" class="form-control">
                                <option value="percentage">Percentage</option>
                                <option value="amount">Amount</option>
                            </select>
                        </div>

                        <!-- Discount Value -->
                        <div class="col-lg-3">
                            <label>Discount Value</label>
                            <input type="number" name="discount_value" id="discount_value" class="form-control">
                        </div>


                        <!-- Note -->
                        <div class="col-lg-12 mt-3">
                            <label>Note</label>
                            <textarea name="note" class="form-control"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            // Auto calculate total price
            function calculateTotal() {
                let qty = parseFloat($("#opening_qty").val()) || 0;
                let price = parseFloat($("#price_per_unit").val()) || 0;
                $("#total_price").val(qty * price);
            }
            $("#opening_qty, #price_per_unit").on("input", calculateTotal);


            // Show/Hide Payable Section + Add/Remove Required Attributes
            $("#is_payable").change(function() {

                if ($(this).val() === "yes") {

                    $("#payable_section").show();

                    // Add required when YES
                    $("#discount_type").attr("required", true);
                    $("#discount_value").attr("required", true);

                } else {

                    $("#payable_section").hide();

                    // Remove required when NO
                    $("#discount_type").removeAttr("required");
                    $("#discount_value").removeAttr("required");

                    $("#discount_value").val("");
                }
            });


            // Validate Discount Values
            function validateDiscount() {
                if ($("#is_payable").val() === "no") return true;

                let type = $("#discount_type").val();
                let value = parseFloat($("#discount_value").val()) || 0;
                let total = parseFloat($("#total_price").val()) || 0;

                if (type === "percentage") {
                    if (value < 1 || value > 100) {
                        alert("Percentage must be between 1 and 100");
                        $("#discount_value").val("");
                        return false;
                    }
                }

                if (type === "amount") {
                    if (value < 1 || value > total) {
                        alert("Amount must be between 1 and " + total);
                        $("#discount_value").val("");
                        return false;
                    }
                }

                return true;
            }

            $("#discount_value, #discount_type").on("input change", validateDiscount);


            // Final Form Submit Validation
            $("#addForm").submit(function(e) {
                if (!validateDiscount()) {
                    e.preventDefault();
                    return false;
                }
            });

        });
    </script>


    <script>
        function calculateTotal() {
            let qty = parseFloat($('#opening_qty').val()) || 0;
            let price = parseFloat($('#price_per_unit').val()) || 0;
            $('#total_price').val(qty * price);
        }

        $('#opening_qty, #price_per_unit').on('input', calculateTotal);
    </script>

    <script>
        $('#employeeDropdown').on('change', function() {
            $('#assignedEmpId').val($(this).val());
        });
    </script>

    <script>
        $(document).ready(function() {
            const csrfToken = '{{ csrf_token() }}';
            window.openCreateKitModal = function() {
                $(".modal").modal("hide");

                setTimeout(() => {
                    $("#kitForm")[0].reset();
                    $("#kit_id").val("");

                    $("#kitModalLabel").text("Create New Stock");
                    $("#saveKitBtn").html("Save Kit").prop("disabled", false);
                    $("#saveKitBtn .spinner-border").addClass("d-none");

                    $("#kitModal").modal("show");
                }, 250);
            };

            window.openEditKitModal = function(id) {
                $(".modal").modal("hide");
                $.ajax({
                    url: `/kits/edit/${id}`,
                    type: "GET",
                    success: function(kit) {

                        setTimeout(() => {
                            $("#kit_id").val(kit.id);
                            $("#name").val(kit.name);
                            $("#type").val(kit.type);
                            $("#size").val(kit.size);
                            $("#per_unit_price").val(kit.per_unit_price);
                            $("#total_qty").val(kit.total_qty);
                            $("#description").val(kit.description || "");

                            $("#kitModalLabel").text("Edit Stock");
                            $("#saveKitBtn").html("Update Stock");

                            $("#kitModal").modal("show");
                        }, 250);
                    },
                    error: function(xhr) {
                        Swal.fire("Error", xhr.responseJSON?.message || "Failed to load kit.",
                            "error");
                    }
                });
            };

            window.deleteKit = function(id) {
                Swal.fire({
                    title: "Delete Stock?",
                    text: "This action cannot be undone.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete"
                }).then(result => {
                    if (result.isConfirmed) {

                        $.ajax({
                            url: `/kits/delete/${id}`,
                            type: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken
                            },
                            success: function(res) {
                                kitTable.ajax.reload(null, false);

                                Swal.fire("Deleted!", res.message || "Kit deleted.",
                                    "success");
                                window.location.reload();

                            },
                            error: function(xhr) {
                                Swal.fire("Error", xhr.responseJSON?.message ||
                                    "Delete failed.", "error");
                            }
                        });
                    }
                });
            };

            $("#kitForm").on("submit", function(e) {
                e.preventDefault();

                const id = $("#kit_id").val();
                const url = id ? `/kits/update/${id}` : `/kits/store`;

                const btn = $("#saveKitBtn");
                const spinner = btn.find(".spinner-border");

                btn.prop("disabled", true);
                spinner.removeClass("d-none");

                $.ajax({
                    url: url,
                    type: "POST",
                    data: $(this).serialize(),
                    headers: {
                        "X-CSRF-TOKEN": csrfToken
                    },
                    success: function(res) {
                        $("#kitModal").modal("hide");
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: res.message || "Stock saved successfully",
                            timer: 2000,
                            showConfirmButton: false
                        });
                        window.location.reload();
                    },
                    error: function(xhr) {
                        let msg = "Something went wrong!";

                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors)[0][0];
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }

                        Swal.fire("Error", msg, "error");
                    },
                    complete: function() {
                        btn.prop("disabled", false);
                        spinner.addClass("d-none");
                    }
                });
            });

        });
    </script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                }
            });

            datatable({
                tableId: "kit-stock-table",
                url: "{{ route('kit.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: true
            });

            $('#addForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('kit-stock.store') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,

                    beforeSend: function() {
                        $('#addForm button[type="submit"]')
                            .prop('disabled', true).text('Submitting...');
                    },

                    success: function(response) {
                        if (response.status === true) {
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            window.location.reload();

                            $('#addModal').modal('hide');
                            $('#addForm')[0].reset();

                            if (typeof table !== 'undefined') {
                                table.ajax.reload();
                            }
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message
                            });
                        }
                    },

                    error: function(xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Something went wrong. Please try again."
                        });
                    },

                    complete: function() {
                        $('#addForm button[type="submit"]')
                            .prop('disabled', false).text('Submit');
                    }
                });
            });

            $(document).on("click", ".editBtn", function() {
                $("#edit_id").val($(this).data("id"));
                $("#edit_opening_qty").val($(this).data("opening"));
                $("#edit_available_qty").val($(this).data("available"));
                $("#edit_assigned_qty").val($(this).data("assigned"));
                $("#edit_damaged_qty").val($(this).data("damaged"));
                $("#edit_lost_qty").val($(this).data("lost"));
                $("#edit_replaced_qty").val($(this).data("replaced"));
                $("#edit_note").val($(this).data("note"));

                $("#editModal").modal("show");
            });

            $("#editForm").submit(function(e) {
                e.preventDefault();
                let id = $("#edit_id").val();

                $.ajax({
                    url: "/kit-stock/update/" + id,
                    type: "PUT",
                    data: $(this).serialize(),

                    success: function(response) {
                        if (response.status === true) {
                            Swal.fire({
                                icon: "success",
                                title: "Updated!",
                                text: response.message
                            });

                            $("#editModal").modal("hide");
                            $('#kit-stock-table').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: response.message
                            });
                        }
                    },

                    error: function() {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Unable to update record."
                        });
                    }
                });
            });

            $(document).on("click", ".deleteBtn", function() {
                let id = $(this).data("id");
                Swal.fire({
                    title: "Are you sure?",
                    text: "This item will be permanently deleted.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/kit-stock/delete/" + id,
                            type: "DELETE",
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },

                            success: function(response) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted!",
                                    text: response.message
                                });
                                $('#kit-stock-table')
                                    .DataTable().ajax.reload(null, false);
                            },

                            error: function(xhr) {
                                if (xhr.status === 422) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Cannot Delete!",
                                        text: xhr.responseJSON.message
                                    });
                                    return;
                                }

                                Swal.fire({
                                    icon: "error",
                                    title: "Error!",
                                    text: "Unable to delete record."
                                });
                            }
                        });
                    }
                });
            });

            // for replacement
            $('#replacedForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('replacement.store') }}",
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success SweetAlert
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload the page after the alert
                                location.reload();
                            });

                            $('#replacedModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';

                        $.each(errors, function(key, value) {
                            errorMsg += value + '\n';
                        });

                        // Show error SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMsg.replace(/\n/g, "<br>")
                        });
                    }
                });
            });

            // for Damage
            $('#damagedForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: "{{ route('damaged.store') }}",
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
                            }).then(() => {
                                location.reload();
                            });
                            $('#damagedModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';

                        $.each(errors, function(key, value) {
                            errorMsg += value + '\n';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMsg.replace(/\n/g, "<br>")
                        });
                    }
                });
            });

            //  for lost
            $('#lostForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('kitlost.store') }}",
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
                            }).then(() => {
                                location.reload();
                                $('#lostModal').modal('hide');
                                $('#lostTable').DataTable().ajax.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';
                        $.each(errors, function(key, value) {
                            errorMsg += value + '\n';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMsg.replace(/\n/g, "<br>")
                        });
                    }
                });
            });

            // for assing
            $('#assignedForm').on('submit', function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('kit.assign.store') }}",
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#assignSubmitBtn').prop('disabled', true).text('Processing...');
                    },
                    success: function(response) {
                        $('#assignSubmitBtn').prop('disabled', false).text('Save');

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false

                            }).then(() => {
                                location.reload();
                                $('#assignedForm')[0].reset();
                                $('#assignedModal').modal('hide');;
                            });

                        }
                    },
                    error: function(xhr) {
                        $('#assignSubmitBtn').prop('disabled', false).text('Save');

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong.'
                        });
                    }
                });
            });



        });
    </script>
@endsection


@push('scripts')
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


    <script>
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // for Category
        $('#categoryModaltable').on('shown.bs.modal', function() {
            if (!$.fn.DataTable.isDataTable('#categoryTable')) {
                $('#categoryTable').DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    lengthChange: false,
                    pageLength: 5,
                    language: {
                        search: "Search categories:",
                        paginate: {
                            next: "Next",
                            previous: "Prev"
                        }
                    }
                });
            }
        });

        // Destroy DataTable on modal close
        $('#categoryModaltable').on('hidden.bs.modal', function() {
            const tableId = $(this).find('table').attr('id');
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }
        });

        // Prevent form submit on Enter
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });

        // Save / Update Stock Category
        function saveCategory() {
            $.ajax({
                url: '{{ route('stock.categories.save') }}',
                method: 'POST',
                data: {
                    id: $('#categoryId').val() || null,
                    sc_name: $('#categoryName').val(),
                    sc_description: $('#categoryDescription').val(),
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            resetCategoryModal();
                            $('#categoryModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed', 'error');
                    }
                },
                error: function(xhr) {
                    let msg = 'Validation error.';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        function updateCategory() {
            saveCategory(); // same endpoint for update
        }

        // Edit Category
        function editCategory(id, name, description) {
            $('#categoryModalLabel').text('Edit Category');
            $('#categoryId').val(id);
            $('#categoryName').val(name);
            $('#categoryDescription').val(description);
            $('#categoryModal').modal('show');
            $('.modal-footer .btn-info').text('Update').attr('onclick', 'updateCategory()');
        }

        // Reset Category Modal
        function resetCategoryModal() {
            $('#categoryModalLabel').text('Create Category');
            $('#categoryId').val('');
            $('#categoryForm')[0].reset();
            $('.modal-footer .btn-info').text('Save').attr('onclick', 'saveCategory()');
        }

        // Reset modal on close
        $('#categoryModal').on('hidden.bs.modal', resetCategoryModal);

        // for Unit
        $('#unitModaltable').on('shown.bs.modal', function() {
            if (!$.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    lengthChange: false,
                    pageLength: 5,
                    language: {
                        search: "Search units:",
                        paginate: {
                            next: "Next",
                            previous: "Prev"
                        }
                    }
                });
            }
        });

        // Destroy DataTable on modal close
        $('#unitModaltable').on('hidden.bs.modal', function() {
            const tableId = $(this).find('table').attr('id');
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }
        });

        // Prevent form submit on Enter
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });

        // Save / Update Stock Unit
        function saveUnit() {
            $.ajax({
                url: '{{ route('stock.units.save') }}', // replace with your route
                method: 'POST',
                data: {
                    su_id: $('#unit_id').val() || null,
                    su_name: $('#su_name').val(),
                    su_short_name: $('#su_short_name').val(),
                    su_description: $('#su_description').val(),
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            resetUnitModal();
                            $('#unitModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed', 'error');
                    }
                },
                error: function(xhr) {
                    let msg = 'Validation error.';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        function updateUnit() {
            saveUnit(); // same endpoint for update
        }

        // Edit Unit
        function editUnit(id, name, short_name, description) {
            $('#unitModalLabel').text('Edit Unit');
            $('#unit_id').val(id);
            $('#su_name').val(name);
            $('#su_short_name').val(short_name);
            $('#su_description').val(description);
            $('#unitModal').modal('show');
            $('.modal-footer .btn-success').text('Update').attr('onclick', 'updateUnit()');
        }

        // Reset Unit Modal
        function resetUnitModal() {
            $('#unitModalLabel').text('Create Unit');
            $('#unit_id').val('');
            $('#unitForm')[0].reset();
            $('.modal-footer .btn-success').text('Save Unit').attr('onclick', 'saveUnit()');
        }

        // Reset modal on close
        $('#unitModal').on('hidden.bs.modal', resetUnitModal);


        // For Stock Items
        const tableId = '#stockItemsTable';

        $('#stockItemsModalTable').on('shown.bs.modal', function() {
            if (!$.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    lengthChange: false,
                    pageLength: 5,
                    language: {
                        search: "Search items:",
                        paginate: {
                            next: "Next",
                            previous: "Prev"
                        }
                    }
                });
            }
        });

        // Destroy DataTable on modal close
        $('#stockItemsModalTable').on('hidden.bs.modal', function() {

            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }
        });

        // Delete Item
        window.deleteStockItem = function(id) {

            Swal.fire({
                title: 'Are you sure?',
                text: "This item will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('stock.items.delete', ':id') }}".replace(':id', id),
                    method: "DELETE",
                    data: {
                        _token: csrfToken
                    },

                    success: function(response) {

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                $("#itemRow" + id).remove();
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to delete item!'
                            });
                        }
                    },

                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Something went wrong!';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            });
        };


        // Prevent ENTER from submitting forms except textarea
        $(document).on('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') e.preventDefault();
        });

        // Reset Form
        window.resetStockItemForm = function() {
            $('#stockItemModalLabel').text('Create Item');
            $('#item_id').val('');
            $('#stockItemForm')[0].reset();
            $('#stockItemSaveBtn')
                .text('Save')
                .off('click')
                .on('click', submitStockItem);
        };

        // Edit Stock Item
        function openStockItemModal(id, name, description, size, kt_category_id, kt_unit_id) {
            $('#stockItemModalLabel').text('Edit Item');
            $('#item_id').val(id);
            $('#item_name').val(name);
            $('#item_description').val(description);
            $('#size').val(size);
            $('#category_id').val(kt_category_id);
            $('#stock_unit_id').val(kt_unit_id);
            $('#stockItemModal').modal('show');
            $('.modal-footer .btn-success')
                .text('Update')
                .attr('onclick', 'updateStockItem()');
        }


        // Save New
        function saveStockItem() {
            submitStockItem();
        }

        // Update
        function updateStockItem() {
            submitStockItem();
        }

        // Save / Update
        function submitStockItem() {

            let formData = {
                item_id: $('#item_id').val(),
                name: $('#item_name').val(),
                description: $('#item_description').val(),
                size: $('#size').val(),
                category_id: $('#category_id').val(),
                unit_id: $('#stock_unit_id').val(),
                per_unit_price: $('#per_unit_price').val(),
                total_qty: $('#total_qty').val(),
                _token: csrfToken
            };

            $.ajax({
                url: "{{ route('stock.items.save') }}",
                method: "POST",
                data: formData,
                success: function(response) {

                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: response.message,
                        }).then(() => {
                            resetStockItemForm();
                            $('#stockItemModal').modal('hide');
                            location.reload();
                        });

                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: response.message || "Something went wrong!"
                        });
                    }
                },
                error: function(xhr) {
                    let msg = "Validation failed";

                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors)
                            .flat()
                            .join("\n");
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        html: msg.replace(/\n/g, "<br>")
                    });
                }
            });
        }


        // Reset modal on close
        $('#stockItemModal').on('hidden.bs.modal', resetStockItemForm);
    </script>

    <script>
        $(document).ready(function() {

            // Assigned Button
            $(document).on('click', '.assignedBtn', function() {
                var id = $(this).data('id');
                var kit = $(this).data('kit');
                var total = $(this).data('total');

                $('#assignedId').val(id);
                $('#assignedKitName').text(kit);
                $('#assignedTotalQty').text(total);

                var modal = new bootstrap.Modal(document.getElementById('assignedModal'));
                modal.show();
            });

            // Damaged Button
            $(document).on('click', '.damagedBtn', function() {
                var id = $(this).data('id');
                var kit = $(this).data('kit');
                var total = $(this).data('total');

                $('#damagedId').val(id);
                $('#damagedKitName').text(kit);
                $('#damagedTotalQty').text(total);

                var modal = new bootstrap.Modal(document.getElementById('damagedModal'));
                modal.show();
            });

            // Lost Button
            $(document).on('click', '.lostBtn', function() {
                var id = $(this).data('id');
                var kit = $(this).data('kit');
                var total = $(this).data('total');

                $('#lostId').val(id);
                $('#lostKitName').text(kit);
                $('#lostTotalQty').text(total);

                var modal = new bootstrap.Modal(document.getElementById('lostModal'));
                modal.show();
            });

            // Replaced Button
            $(document).on('click', '.replacedBtn', function() {
                var id = $(this).data('id');
                var kit = $(this).data('kit');
                var total = $(this).data('total');

                $('#replacedId').val(id);
                $('#replacedKitName').text(kit);
                $('#replacedTotalQty').text(total);

                var modal = new bootstrap.Modal(document.getElementById('replacedModal'));
                modal.show();
            });


        });
    </script>

    <script>
        $(document).ready(function() {
            function validateQuantity(inputSelector, totalSelector) {
                $(inputSelector).on('input', function() {
                    let total = parseInt($(totalSelector).text()); // total available
                    let value = parseInt($(this).val());

                    if (value > total) {
                        $(this).val(total); // reset to max allowed
                        alert('Quantity cannot be greater than total available stock: ' + total);
                    } else if (value < 1) {
                        $(this).val(1); // minimum 1
                    }
                });
            }
            validateQuantity('#assignedForm input[name="quantity"]', '#assignedTotalQty');
            validateQuantity('#damagedForm input[name="quantity"]', '#damagedTotalQty');
            validateQuantity('#lostForm input[name="quantity"]', '#lostTotalQty');
            validateQuantity('#replacedForm input[name="quantity"]', '#replacedTotalQty');

        });
    </script>
@endpush
