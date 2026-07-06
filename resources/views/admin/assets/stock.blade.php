@extends('admin.layout.master')
@section('title', 'Stock Assets')
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

        .custom-button svg {
            width: 16px;
            height: 16px;
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
    @if (session('error'))
        <div class="alert alert-danger fade-message">
            {{ session('error') }}
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const msg = document.querySelector('.fade-message');
                if (msg) {
                    setTimeout(() => {
                        msg.style.opacity = '0';
                        setTimeout(() => msg.remove(), 500);
                    }, 2000);
                }
            });
        </script>
    @endif

    {{-- Breadcrumbs --}}
    <div class="mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/reimburse') }}">Assets</a></li>
                    <li class="active"><span><b>Stock Assets</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <!-- START ROW -->
    <div class="row mt-3">

        <!-- Total Assets -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <div class="text-start">
                                <span class="font-weight-semibold">Total Assets</span>
                                <h3 class="mb-0 mt-1 text-success">{{ $total_stock }}</h3>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="icon1 bg-success-transparent my-auto pt-3 float-end">
                                <i class="las la-laptop"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stocks -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Stocks</span>
                                <h3 class="mb-0 mt-1 text-primary">{{ $newstock }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-primary-transparent my-auto pt-3 float-end">
                                <i class="las la-box"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Assign</span>
                                <h3 class="mb-0 mt-1 text-success">{{ $assignstock }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-success-transparent my-auto pt-3 float-end">
                                <i class="las la-share"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrap -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Scrap</span>
                                <h3 class="mb-0 mt-1 text-danger">{{ $scrapstock }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-danger-transparent my-auto pt-3 float-end">
                                <i class="las la-trash"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Service</span>
                                <h3 class="mb-0 mt-1 text-warning">{{ $servicestock }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-warning-transparent my-auto pt-3 float-end">
                                <i class="las la-tools"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Replace -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start">
                                <span class="font-weight-semibold">Replace</span>
                                <h3 class="mb-0 mt-1 text-info">{{ $replacestock }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-info-transparent my-auto pt-3 float-end">
                                <i class="las la-exchange-alt"></i>
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
                <div class="card-header border-0">
                    <h4 class="card-title">Stock Assets</h4>
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

                                <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown" style="min-width: 220px;">
                                    <!-- Add Asset -->
                                    <li>
                                        <a href="{{ route('assets.create') }}"
                                            class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                            <i class="bi bi-plus-circle"></i> Add Asset
                                        </a>
                                    </li>

                                    <!-- Configuration -->
                                    {{-- <li>
                                        <a class="dropdown-item text-secondary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#configurationModal">
                                            <i class="bi bi-gear"></i> Configuration
                                        </a>
                                    </li> --}}


                                    <li>
                                        <a class="dropdown-item text-secondary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#emailModal">
                                            <i class="bi bi-envelope"></i> Emails
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        </div>

                        <div class="row" id="filterContainer" style="display: none; margin-bottom: 21px;">
                            <div class="col-md-2">
                                <label for="assetTagFilter" class="form-label">Asset Tag</label>
                                <select id="assetTagFilter" name="asset_tag"
                                    class="form-select  filter_border" data-filter>
                                    <option value="">All</option>
                                    @foreach ($assetTag->unique('asset_tag') as $asset)
                                        <option value="{{ $asset->asset_tag }}">{{ $asset->asset_tag }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="modelNumberFilter" class="form-label">Model Number</label>
                                <select id="modelNumberFilter" name="model_number"
                                    class="form-select  filter_border" data-filter>
                                    <option value="">All</option>
                                    @foreach ($assetTag->unique('model_number') as $asset)
                                        <option value="{{ $asset->model_number }}">{{ $asset->model_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="serialNumberFilter" class="form-label">Serial Number</label>
                                <select id="serialNumberFilter" name="serial_number"
                                    class="form-select  filter_border" data-filter>
                                    <option value="">All</option>
                                    @foreach ($assetTag->unique('serial_number') as $asset)
                                        <option value="{{ $asset->serial_number }}">{{ $asset->serial_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="assetTypeFilter" class="form-label">Asset Type</label>
                                <select id="assetTypeFilter" name="asset_type"
                                    class="form-select  filter_border" data-filter>
                                    <option value="">All</option>
                                    @foreach ($noofassets as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
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

                    <table class="table table-hover table-vcenter text-wrap border-bottom" id="reimburse-table-dynamic">
                        <thead>
                            <tr>
                                @foreach ($columns as $column)
                                    <th style="font-size: 12px; width: {{ $column['width'] }};">
                                        {{ $column['name'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>


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

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Service Emails</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="emailForm">
                        @csrf <!-- Laravel CSRF token -->
                        <div class="mb-3">
                            <label for="email1" class="form-label">To</label>
                            <input type="email" class="form-control" id="email1" name="email1"
                                placeholder="Enter first email">
                        </div>
                        <div class="mb-3">
                            <label for="email2" class="form-label">Cc</label>
                            <input type="email" class="form-control" id="email2" name="email2"
                                placeholder="Enter second email">
                        </div>
                    </form>
                    <div id="responseMessage" class="mt-2 text-success"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="saveEmails" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Configuration Modal -->
    <div class="modal fade" id="configurationModal" tabindex="-1" aria-labelledby="configurationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="configurationModalLabel">
                        <i class="bi bi-gear me-2"></i>
                        Asset Configuration Management
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Configuration Tabs -->
                    <ul class="nav nav-tabs" id="configTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="asset-types-tab" data-bs-toggle="tab"
                                data-bs-target="#asset-types" type="button" role="tab">
                                <i class="bi bi-tags me-1"></i>
                                Asset Types
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="components-tab" data-bs-toggle="tab"
                                data-bs-target="#components" type="button" role="tab">
                                <i class="bi bi-gear me-1"></i>
                                Components
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dropdown-options-tab" data-bs-toggle="tab"
                                data-bs-target="#dropdown-options" type="button" role="tab">
                                <i class="bi bi-list-ul me-1"></i>
                                Dropdown Options
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="configTabContent">
                        <!-- Asset Types Tab -->
                        <div class="tab-pane fade show active" id="asset-types" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Asset Types Management</h6>
                                <button type="button" class="btn btn-primary btn-sm" id="addAssetTypeBtn">
                                    <i class="bi bi-plus me-1"></i>
                                    Add Asset Type
                                </button>
                            </div>
                            <div id="assetTypesList" class="row">
                                <!-- Asset types will be loaded here -->
                            </div>
                        </div>

                        <!-- Components Tab -->
                        <div class="tab-pane fade" id="components" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Components Management</h6>
                                <button type="button" class="btn btn-primary btn-sm" id="addComponentBtn">
                                    <i class="bi bi-plus me-1"></i>
                                    Add Component
                                </button>
                            </div>
                            <div id="componentsList" class="row">
                                <!-- Components will be loaded here -->
                            </div>
                        </div>

                        <!-- Dropdown Options Tab -->
                        <div class="tab-pane fade" id="dropdown-options" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Dropdown Options Management</h6>
                                <button type="button" class="btn btn-primary btn-sm" id="addDropdownCategoryBtn">
                                    <i class="bi bi-plus me-1"></i>
                                    Add Category
                                </button>
                            </div>
                            <div id="dropdownOptionsList" class="row">
                                <!-- Dropdown options will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    {{-- <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Assets</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignForm" method="POST" action="{{ route('assets.assign') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Employee</label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">Choose an employee...</option>
                            </select>
                        </div>
                        <div id="selectedAssets"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Assets</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}

    <!-- Single Asset Assignment Modal -->
    <div class="modal fade" id="assignSingleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignSingleForm" method="POST" action="{{ route('assets.assign') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">

                            <strong> Asset :</strong>
                            <span id="singleAssetName"></span> -
                            <span id="singleAssetTag"></span>
                        </div>

                        {{-- Employee Dropdown with Select2 --}}
                        <div class="mb-3">
                            <label class="form-label">Select Employee</label>
                            <select name="employee_id" id="employee_id" class="form-select select2" style="width: 100%;"
                                required>
                                <option value="">Choose an employee...</option>
                            </select>
                        </div>

                        {{-- Remark Field --}}
                        <div class="mb-3">
                            <label for="remark" class="form-label">Remark</label>
                            <textarea name="remark" id="remark" class="form-control" rows="2" placeholder="Enter any remark..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-person-plus me-1"></i>
                            Assign Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Scrap Modal -->
    <div class="modal fade" id="scrapModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Scrap Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="scrapForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="alert alert-warning">

                            <strong> Asset :</strong>
                            <span id="scrapAssetTagName"></span> -
                            <span id="scrapAssetTag"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Scrap Value (₹)</label>
                            <input type="number" name="scrap_value" class="form-control" min="0" step="0.01"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Scrapping</label>
                            <textarea name="scrap_reason" class="form-control" rows="3" placeholder="Why is this asset being scrapped?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-trash me-1"></i>
                            Scrap Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Service Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog modal-lg"> <!-- Large modal for enough width -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Service Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="serviceForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong> Asset :</strong>
                            <span id="serviceAssetName"></span> -
                            <span id="serviceAssetTag"></span>

                        </div>

                        <div class="row">
                            <!-- Service Type -->
                            <div class="col-lg-4">
                                <label class="form-label">Service Type</label>
                                <select name="service_type" class="form-select" required>
                                    <option value="Preventive">Preventive</option>
                                    <option value="Corrective">Corrective</option>
                                    <option value="Calibration">Calibration</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="General Repair">General Repair</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- Service Location -->
                            <div class="col-lg-4">
                                <label class="form-label">Service Location</label>
                                <select name="service_location" id="service_location" class="form-select" required>
                                    <option value="">Select Service Location</option>
                                    <option value="Local">Local</option>
                                    @foreach ($branch as $row)
                                        <option value="{{ $row->br_id }}">{{ $row->br_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Vendor Name -->
                            <div class="col-lg-4 basic-fields">
                                <label class="form-label">Vendor Name (Optional)</label>
                                <input type="text" name="vendor_name" class="form-control">
                            </div>

                            <!-- Service Cost -->
                            <div class="col-lg-4 basic-fields">
                                <label class="form-label">Service Cost (Optional)</label>
                                <input type="number" name="service_cost" step="0.01" class="form-control">
                            </div>

                            <!-- Start Date -->
                            <div class="col-lg-4 basic-fields">
                                <label class="form-label">Service Start Date</label>
                                <input type="date" name="service_start_date" class="form-control">
                            </div>

                            <!-- End Date -->
                            <div class="col-lg-4 basic-fields">
                                <label class="form-label">Service End Date</label>
                                <input type="date" name="service_end_date" class="form-control">
                            </div>

                            <!-- File Upload (Always Visible) -->
                            <div class="col-lg-12 mt-3">
                                <label class="form-label">Upload File</label>
                                <input type="file" name="service_file" class="form-control">
                            </div>

                            <!-- HQ Fields (Courier) -->
                            <div id="hq-fields" class="row mt-3 d-none">
                                <div class="col-lg-4">
                                    <label class="form-label">Courier Name</label>
                                    <input type="text" name="courier_name" class="form-control">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Docket No.</label>
                                    <input type="text" name="docket_no" class="form-control">
                                </div>
                            </div>

                            <!-- Service Notes -->
                            <div class="col-lg-12 mt-3">
                                <label class="form-label">Service Notes</label>
                                <textarea name="issue_description" class="form-control" rows="3"
                                    placeholder="Describe the issue or service required..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-tools me-1"></i>
                            Send Service Request
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Duplicate Asset Modal -->
    <div class="modal fade" id="duplicateAssetModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Duplicate Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="duplicateAssetForm" method="POST" action="{{ route('assets.duplicate') }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Original Asset :</strong>
                            <span id="duplicateAssetname"></span> -
                            <span id="duplicateAssetTag"></span>
                        </div>

                        <input type="hidden" name="assets_id" id="assets_id">
                        <input type="hidden" name="assets_tag" id="assets_tag">

                        <div class="row">
                            {{-- 2. Vendor --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Vendor</label>
                                <input type="text" name="vendor_name" id="dupVendorName" class="form-control">
                            </div>

                            {{-- 3. PO No --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">PO No.</label>
                                <input type="text" name="po_no" id="dupPoNo" class="form-control">
                            </div>

                            {{-- 4. PO Date --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">PO Date</label>
                                <input type="date" name="purchase_date" id="dupPurchaseDate" class="form-control">
                            </div>

                            {{-- 5. Invoice No --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Invoice No.</label>
                                <input type="text" name="invoice_no" id="dupInvoiceNo" class="form-control">
                            </div>

                            {{-- 6. Invoice Date --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" name="invoice_date" id="dupInvoiceDate" class="form-control">
                            </div>

                            {{-- 7. Upload Invoice --}}
                            {{-- <div class="col-lg-4 mb-3">
                                <label class="form-label">Upload Invoice</label>
                                <input type="file" name="invoice_file" id="dupInvoiceFile" class="form-control"
                                    accept=".pdf,.jpg,.jpeg,.png">
                            </div> --}}

                            {{-- 8. Purchase Amount --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Amount (₹)</label>
                                <input type="number" name="purchase_value" id="dupPurchaseValue" class="form-control"
                                    step="0.01" min="0">
                            </div>

                            {{-- 9. Warranty (Months) --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Warranty (Months)</label>
                                <input type="number" name="warranty_months" id="dupWarrantyMonths" class="form-control"
                                    min="0" max="120">
                            </div>

                            {{-- 10. Serial Number --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" id="dupSerialNumber" class="form-control">
                            </div>

                            {{-- 11. Model Number --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Model Number</label>
                                <input type="text" name="model_number" id="dupModelNumber" class="form-control">
                            </div>

                            {{-- 12. Notes --}}
                            <div class="col-lg-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" id="dupNotes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-files me-1"></i> Duplicate Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize Select2 inside modal
                $('#employee_id').select2({
                    dropdownParent: $('#assignSingleModal'),
                    placeholder: "Choose an employee...",
                    allowClear: true
                });
            });

            $(document).on('click', '.duplicate-asset-btn', function() {
                let assetId = $(this).data('asset-id');
                let assetTag = $(this).data('asset-tag');
                let assetName = $(this).data('asset-name');
                let serialNumber = $(this).data('serial-number');
                let modelNumber = $(this).data('model-number');
                let purchaseValue = $(this).data('purchase-value');
                let purchaseDate = $(this).data('purchase-date');
                let warrantyMonths = $(this).data('warranty-months');
                let invoiceNo = $(this).data('invoice-no');
                let invoiceDate = $(this).data('invoice-date');
                let vendorName = $(this).data('vendor-name');

                // Fill modal fields
                $('#assets_id').val(assetId);
                $('#assets_tag').val(assetTag);
                $('#duplicateAssetTag').text(assetTag);
                $('#duplicateAssetname').text(assetName);

                $('#dupSerialNumber').val(serialNumber);
                $('#dupModelNumber').val(modelNumber);
                $('#dupPurchaseValue').val(purchaseValue);
                $('#dupPurchaseDate').val(purchaseDate);
                $('#dupWarrantyMonths').val(warrantyMonths);
                $('#dupInvoiceNo').val(invoiceNo);
                $('#dupInvoiceDate').val(invoiceDate);
                $('#dupVendorName').val(vendorName);

                // Show modal
                $('#duplicateAssetModal').modal('show');
            });

            document.addEventListener('DOMContentLoaded', function() {
                const serviceLocation = document.getElementById('service_location');
                const hqFields = document.getElementById('hq-fields');

                serviceLocation.addEventListener('change', function() {
                    if (this.value === 'Local' || this.value === '') {
                        hqFields.classList.add('d-none'); // Local me courier fields hide
                    } else {
                        hqFields.classList.remove('d-none'); // HQ/Branch me courier fields show
                    }
                });
            });


            $('#saveEmails').click(function(e) {
                e.preventDefault(); // prevent default form submit

                let email1 = $('#email1').val();
                let email2 = $('#email2').val();
                let token = $('input[name="_token"]').val();

                $.ajax({
                    url: "{{ route('emails.store') }}",
                    type: "POST",
                    data: {
                        _token: token,
                        email1: email1,
                        email2: email2
                    },
                    success: function(response) {
                        // SweetAlert success
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,

                        });

                        $('#emailForm')[0].reset();
                        $('#emailModal').modal('hide');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Laravel validation errors
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = '';
                            $.each(errors, function(key, value) {
                                errorMessages += value[0] + '\n';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorMessages,

                            });
                        } else {
                            // Other errors
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong while storing emails!',

                            });
                        }
                    }
                });
            });

            $('#emailModal').on('show.bs.modal', function() {
                $.ajax({
                    url: "{{ route('emails.get') }}",
                    type: "GET",
                    success: function(response) {
                        if (response) {
                            $('#email1').val(response.email1);
                            $('#email2').val(response.email2);
                        } else {
                            $('#emailForm')[0].reset();
                        }
                    },
                    error: function() {
                        $('#emailForm')[0].reset();
                    }
                });
            });
        </script>
    @endpush

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
        $(document).ready(function() {
            datatable({
                tableId: "reimburse-table-dynamic",
                url: "{{ route('assets.stock') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: true
            });

            function downloadSheet(sheetType) {
                const fromDateEl = document.getElementById('fromDate');
                const toDateEl = document.getElementById('toDate');
                const statusEl = document.getElementById('sheetStatusFilter');

                const fromDate = fromDateEl ? fromDateEl.value : '';
                const toDate = toDateEl ? toDateEl.value : '';
                const status = statusEl ? statusEl.value : '';

                const baseUrl = '{{ url('') }}'; // Laravel base URL
                const url = `${baseUrl}/${sheetType}?` + new URLSearchParams({
                    fromDate: fromDate,
                    toDate: toDate,
                    status: status
                }).toString();

                window.location.href = url;
            }


            // Configuration Management
            let assetTypes = [];
            let components = [];
            let dropdownOptions = {};
            let fieldTypes = [];

            // Load configuration data when modal opens
            $('#configurationModal').on('show.bs.modal', function() {
                loadConfigurationData();
            });

            function loadConfigurationData() {
                // Load Asset Types
                $.ajax({
                    url: '/api/asset-types',
                    method: 'GET',
                    success: function(data) {
                        assetTypes = data;
                        renderAssetTypes();
                    },
                    error: function(xhr) {
                        showToast('Error loading asset types: ' + (xhr.responseJSON?.message ||
                            'Unknown error'), 'error');
                    }
                });

                // Load Components
                $.ajax({
                    url: '/api/components',
                    method: 'GET',
                    success: function(data) {
                        components = data;
                        renderComponents();
                    },
                    error: function(xhr) {
                        showToast('Error loading components: ' + (xhr.responseJSON?.message ||
                            'Unknown error'), 'error');
                    }
                });

                // Load Dropdown Options
                $.ajax({
                    url: '/api/dropdown-options',
                    method: 'GET',
                    success: function(data) {
                        dropdownOptions = data;
                        renderDropdownOptions();
                    },
                    error: function(xhr) {
                        showToast('Error loading dropdown options: ' + (xhr.responseJSON?.message ||
                            'Unknown error'), 'error');
                    }
                });

                // Load Field Types
                $.ajax({
                    url: '/api/field-types',
                    method: 'GET',
                    success: function(data) {
                        fieldTypes = data;
                    },
                    error: function(xhr) {
                        showToast('Error loading field types: ' + (xhr.responseJSON?.message ||
                            'Unknown error'), 'error');
                    }
                });
            }

            function renderAssetTypes() {
                const container = $('#assetTypesList');
                container.empty();

                if (assetTypes.length === 0) {
                    container.html(`
                                <div class="col-12">
                                    <div class="text-center py-4">
                                        <i class="bi bi-tags display-4 text-muted"></i>
                                        <h6 class="mt-2">No Asset Types</h6>
                                        <p class="text-muted">Create your first asset type to get started.</p>
                                    </div>
                                </div>
                            `);
                    return;
                }

                assetTypes.forEach(function(assetType) {
                    const structureType = assetType.components && assetType.components.length > 0 ?
                        'Component-Based' : 'Simple';
                    const fieldCount = assetType.fields ? assetType.fields.length : 0;
                    const componentCount = assetType.components ? assetType.components.length : 0;

                    container.append(`
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">${assetType.name}</h6>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary edit-asset-type" data-id="${assetType.id}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger delete-asset-type" data-id="${assetType.id}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <span class="badge ${structureType === 'Component-Based' ? 'bg-info' : 'bg-secondary'} mb-2">${structureType}</span>
                                            <p class="card-text small text-muted">${assetType.description || 'No description'}</p>
                                            <div class="small">
                                                ${structureType === 'Component-Based' ? 
                                                    `<i class="bi bi-gear me-1"></i>${componentCount} Components` : 
                                                    `<i class="bi bi-list me-1"></i>${fieldCount} Fields`
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                });
            }

            function renderComponents() {
                const container = $('#componentsList');
                container.empty();

                if (components.length === 0) {
                    container.html(`
                                <div class="col-12">
                                    <div class="text-center py-4">
                                        <i class="bi bi-gear display-4 text-muted"></i>
                                        <h6 class="mt-2">No Components</h6>
                                        <p class="text-muted">Create reusable components for complex asset types.</p>
                                    </div>
                                </div>
                            `);
                    return;
                }

                components.forEach(function(component) {
                    container.append(`
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">${component.name}</h6>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary edit-component" data-id="${component.id}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger delete-component" data-id="${component.id}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="card-text small text-muted">${component.description || 'No description'}</p>
                                        <div class="small">
                                            <i class="bi bi-list me-1"></i>${component.fields_count || 0} Fields
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                });
            }

            function renderDropdownOptions() {
                const container = $('#dropdownOptionsList');
                container.empty();

                if (Object.keys(dropdownOptions).length === 0) {
                    container.html(`
                                <div class="col-12">
                                    <div class="text-center py-4">
                                        <i class="bi bi-list-ul display-4 text-muted"></i>
                                        <h6 class="mt-2">No Dropdown Categories</h6>
                                        <p class="text-muted">Create dropdown option categories for reuse in forms.</p>
                                    </div>
                                </div>
                            `);
                    return;
                }

                Object.keys(dropdownOptions).forEach(function(category) {
                    const options = dropdownOptions[category];
                    const optionCount = options.length;

                    container.append(`
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">${category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary edit-dropdown-category" data-category="${category}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger delete-dropdown-category" data-category="${category}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <span class="badge bg-primary mb-2">${optionCount} Options</span>
                                            <div class="small">
                                                ${options.slice(0, 5).map(opt => `<span class="badge bg-light text-dark me-1">${opt.label}</span>`).join('')}
                                                ${optionCount > 5 ? `<span class="text-muted">... +${optionCount - 5} more</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                });
            }

            // Asset assignment functionality
            $.ajax({
                url: '{{ route('employees.assignment-list') }}',
                method: 'GET',
                success: function(employees) {
                    let select = $('#employee_id');
                    select.empty().append('<option value="">Choose an employee...</option>');

                    employees.forEach(function(employee) {
                        select.append(
                            `<option value="${employee.emp_id}">
                            ${employee.emp_full_name} (${employee.emp_code})
                         </option>`
                        );
                    });

                    // Refresh select2 with new data
                    select.trigger('change');
                },
                error: function(xhr) {
                    showToast('Error loading employees: ' + (xhr.responseJSON?.message ||
                        'Unknown error'), 'error');
                }
            });


            // Handle assign button clicks
            $(document).on('click', '.assign-btn', function() {
                const assetIds = [];
                const assetTags = [];

                $('.asset-checkbox:checked').each(function() {
                    assetIds.push($(this).val());
                    assetTags.push($(this).data('tag'));
                });

                if (assetIds.length === 0) {
                    showToast('Please select at least one asset to assign.', 'error');
                    return;
                }

                // Clear previous asset IDs
                $('input[name="asset_ids[]"]').remove();

                // Add selected asset IDs to form
                assetIds.forEach(function(id) {
                    $('#assignForm').append(
                        `<input type="hidden" name="asset_ids[]" value="${id}">`);
                });

                // Show selected assets
                $('#selectedAssets').html(`
                                <div class="alert alert-info">
                                    <strong>Selected Assets:</strong><br>
                                    ${assetTags.join(', ')}
                                </div>
                            `);

                $('#assignModal').modal('show');
            });

            // Handle select all checkbox
            $('#selectAll').change(function() {
                $('.asset-checkbox').prop('checked', this.checked);
                updateAssignButton();
            });

            // Handle individual checkboxes
            $(document).on('change', '.asset-checkbox', function() {
                updateAssignButton();
            });

            function updateAssignButton() {
                const checkedCount = $('.asset-checkbox:checked').length;
                if (checkedCount > 0) {
                    $('.assign-btn').removeClass('d-none').text(
                        `Assign ${checkedCount} Asset${checkedCount > 1 ? 's' : ''}`);
                } else {
                    $('.assign-btn').addClass('d-none');
                }
            }

            // Handle single asset assignment
            $(document).on('click', '.assign-single-btn', function() {
                const assetId = $(this).data('asset-id');
                const assetTag = $(this).data('asset-tag');
                const assetsName = $(this).data('asset-name'); // <-- fixed

                $('#assignSingleForm input[name="asset_ids[]"]').remove();
                $('#assignSingleForm').append(
                    `<input type="hidden" name="asset_ids[]" value="${assetId}">`
                );
                $('#singleAssetTag').text(assetTag);
                $('#singleAssetName').text(assetsName);

                // Show modal
                $('#assignSingleModal').modal('show');
            });


            // Handle scrap modal
            $('#scrapModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const assetId = button.data('asset-id');
                const assetTag = button.data('asset-tag');
                const assetsName = button.data('asset-name');

                const modal = $(this);
                modal.find('#scrapAssetTag').text(assetTag);
                modal.find('#scrapAssetTagName').text(assetsName);
                modal.find('#scrapForm').attr('action', `/assets/${assetId}/scrap`);
            });

            // Handle service modal
            $('#serviceModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const assetId = button.data('asset-id');
                const assetTag = button.data('asset-tag');
                const assetsName = button.data('asset-name');

                const modal = $(this);
                modal.find('#serviceAssetTag').text(assetTag);
                modal.find('#serviceAssetName').text(assetsName);
                modal.find('#serviceForm').attr('action', `/assets/${assetId}/send-to-service`);
            });

            // Configuration CRUD Operations
            $(document).on('click', '#addAssetTypeBtn', function() {
                showAssetTypeForm();
            });

            function showAssetTypeForm() {
                const formHtml = `
                                <div class="asset-type-form">
                                    <form id="assetTypeCreateForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Asset Type Name</label>
                                                    <input type="text" name="name" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" name="description" class="form-control" placeholder="Optional">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label">Asset Type Structure</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check p-3 border rounded">
                                                        <input class="form-check-input" type="radio" name="structure_type" value="simple" checked>
                                                        <label class="form-check-label">
                                                            <strong>Simple Asset</strong><br>
                                                            <small class="text-muted">Single asset with specification fields</small>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check p-3 border rounded">
                                                        <input class="form-check-input" type="radio" name="structure_type" value="component_based">
                                                        <label class="form-check-label">
                                                            <strong>Component-Based Asset</strong><br>
                                                            <small class="text-muted">Asset made up of multiple components</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Simple Asset Fields -->
                                        <div id="simple-fields-section" class="structure-section">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="text-primary mb-0">Specification Fields</h6>
                                                <button type="button" class="btn btn-outline-primary btn-sm add-simple-field">
                                                    <i class="bi bi-plus me-1"></i>Add Field
                                                </button>
                                            </div>
                                            <div class="simple-fields-container">
                                                <!-- Fields will be added here -->
                                            </div>
                                        </div>

                                        <!-- Component-Based Fields -->
                                        <div id="component-fields-section" class="structure-section" style="display: none;">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="text-primary mb-0">Select Components</h6>
                                            </div>
                                            <div class="row component-selection">
                                                <!-- Components will be loaded here -->
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between mt-4">
                                            <button type="button" class="btn btn-secondary cancel-asset-type">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Create Asset Type</button>
                                        </div>
                                    </form>
                                </div>
                            `;

                $('#assetTypesList').html(formHtml);

                // Add initial field for simple assets
                addSimpleField();

                // Load components for component-based selection
                loadComponentsForSelection();

                // Handle structure type changes
                $('input[name="structure_type"]').change(function() {
                    if ($(this).val() === 'simple') {
                        $('#simple-fields-section').show();
                        $('#component-fields-section').hide();
                    } else {
                        $('#simple-fields-section').hide();
                        $('#component-fields-section').show();
                        loadComponentsForSelection();
                    }
                });

                // Handle form submission
                $('#assetTypeCreateForm').on('submit', function(e) {
                    e.preventDefault();

                    // Validate visible fields only
                    const structureType = $('input[name="structure_type"]:checked').val();
                    let isValid = true;

                    // Validate basic fields
                    if (!$('input[name="name"]').val().trim()) {
                        showToast('Asset Type Name is required', 'error');
                        $('input[name="name"]').focus();
                        return;
                    }

                    if (structureType === 'simple') {
                        // Validate simple fields
                        const visibleFields = $('#simple-fields-section:visible .field-row');
                        visibleFields.each(function() {
                            const fieldName = $(this).find('input[name*="[name]"]').val();
                            const fieldType = $(this).find('select[name*="[field_type_id]"]').val();

                            if (!fieldName || !fieldType) {
                                isValid = false;
                                showToast('All field names and types are required', 'error');
                                return false;
                            }
                        });
                    } else {
                        // Validate component selection
                        const selectedComponents = $('.component-selection input[type="checkbox"]:checked');
                        if (selectedComponents.length === 0) {
                            isValid = false;
                            showToast('Please select at least one component', 'error');
                            return;
                        }
                    }

                    if (!isValid) return;

                    // Collect form data
                    const formData = {
                        name: $('input[name="name"]').val(),
                        description: $('input[name="description"]').val(),
                        structure_type: structureType
                    };

                    if (structureType === 'simple') {
                        formData.fields = [];
                        $('#simple-fields-section:visible .field-row').each(function(index) {
                            const fieldData = {
                                name: $(this).find('input[name*="[name]"]').val(),
                                field_type_id: $(this).find('select[name*="[field_type_id]"]')
                                    .val(),
                                is_required: $(this).find('input[name*="[is_required]"]').is(
                                    ':checked'),
                                attributes: {
                                    placeholder: $(this).find('input[name*="[placeholder]"]')
                                        .val()
                                }
                            };

                            // Add dropdown options if field type requires them
                            const fieldType = $(this).find(
                                'select[name*="[field_type_id]"] option:selected');
                            if (fieldType.data('requires-options')) {
                                const optionsSource = $(this).find(
                                    'select[name*="[options_source]"]').val();
                                if (optionsSource === 'category') {
                                    fieldData.options_category = $(this).find(
                                        'select[name*="[options_category]"]').val();
                                } else {
                                    fieldData.dropdown_options = [];
                                    $(this).find('.option-row').each(function() {
                                        const value = $(this).find('input[name*="[value]"]')
                                            .val();
                                        const label = $(this).find('input[name*="[label]"]')
                                            .val();
                                        if (value && label) {
                                            fieldData.dropdown_options.push({
                                                value,
                                                label
                                            });
                                        }
                                    });
                                }
                            }

                            formData.fields.push(fieldData);
                        });
                    } else {
                        formData.components = [];
                        $('.component-selection input[type="checkbox"]:checked').each(function() {
                            formData.components.push($(this).val());
                        });
                    }

                    // Submit via AJAX
                    $.ajax({
                        url: '{{ route('asset-types.store') }}',
                        method: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showToast('Asset type created successfully!', 'success');
                            $('#assetTypeCreateForm')[0].reset();
                            $('.simple-fields-container').empty();
                            loadConfigurationData(); // Reload the configuration data
                        },
                        error: function(xhr) {
                            const errors = xhr.responseJSON?.errors;
                            if (errors) {
                                Object.values(errors).forEach(errorArray => {
                                    errorArray.forEach(error => showToast(error,
                                        'error'));
                                });
                            } else {
                                showToast('Error creating asset type!', 'error');
                            }
                        }
                    });
                });
            }

            function cancelAssetTypeForm() {
                loadConfigurationData(); // Reload the asset types list
            }

            function addSimpleField() {
                const fieldIndex = $('.simple-fields-container .field-row').length;
                const fieldHtml = `
                                <div class="field-row mb-4 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-secondary mb-0">Field ${fieldIndex + 1}</h6>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-simple-field">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Field Name</label>
                                                <input type="text" name="fields[${fieldIndex}][name]" class="form-control" 
                                                    placeholder="e.g., Brand, Model" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Field Type</label>
                                                <select name="fields[${fieldIndex}][field_type_id]" class="form-select field-type-select" required>
                                                    <option value="">Select Type</option>
                                                    ${fieldTypes.map(type => 
                                                        `<option value="${type.id}" data-requires-options="${type.requires_options}">${type.label}</option>`
                                                    ).join('')}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Required</label>
                                                <div class="form-check mt-2">
                                                    <input type="checkbox" name="fields[${fieldIndex}][is_required]" class="form-check-input" value="1" checked>
                                                    <label class="form-check-label">Required field</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Placeholder</label>
                                                <input type="text" name="fields[${fieldIndex}][placeholder]" class="form-control" 
                                                    placeholder="Enter placeholder">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dropdown Options Section -->
                                    <div class="dropdown-options-section" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Options Source</label>
                                                    <select name="fields[${fieldIndex}][options_source]" class="form-select options-source-select">
                                                        <option value="custom">Custom Options</option>
                                                        <option value="category">Use Existing Category</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 category-select-section" style="display: none;">
                                                <div class="mb-3">
                                                    <label class="form-label">Category</label>
                                                    <select name="fields[${fieldIndex}][options_category]" class="form-select">
                                                        <option value="">Select Category</option>
                                                        ${Object.keys(dropdownOptions).map(category => 
                                                            `<option value="${category}">${category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>`
                                                        ).join('')}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="custom-options-section">
                                            <label class="form-label">Custom Options</label>
                                            <div class="options-container">
                                                <div class="option-row mb-2">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <input type="text" name="fields[${fieldIndex}][dropdown_options][0][value]" 
                                                                class="form-control" placeholder="Value (e.g., dell)">
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="text" name="fields[${fieldIndex}][dropdown_options][0][label]" 
                                                                class="form-control" placeholder="Label (e.g., Dell)">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="button" class="btn btn-outline-danger btn-sm remove-option">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-success btn-sm add-option">
                                                <i class="bi bi-plus me-1"></i>Add Option
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;

                $('.simple-fields-container').append(fieldHtml);
            }

            function loadComponentsForSelection() {
                const container = $('.component-selection');
                container.empty();

                if (components.length === 0) {
                    container.html(`
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No components available. Create components first in the Components tab.
                                    </div>
                                </div>
                            `);
                    return;
                }

                let html = '';
                components.forEach(function(component) {
                    html += `
                            <div class="col-md-4 mb-3">
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="checkbox" name="components[]" 
                                        value="${component.id}" id="comp_${component.id}">
                                    <label class="form-check-label" for="comp_${component.id}">
                                        <strong>${component.name}</strong><br>
                                        <small class="text-muted">${component.description || 'No description'}</small><br>
                                        <small class="text-info">${component.fields_count || 0} fields</small>
                                    </label>
                                </div>
                            </div>
                        `;
                });

                container.html(html);
            }

            // Handle field type changes for dropdown options
            $(document).on('change', '.field-type-select', function() {
                const requiresOptions = $(this).find('option:selected').data('requires-options');
                const fieldRow = $(this).closest('.field-row');
                const dropdownSection = fieldRow.find('.dropdown-options-section');

                if (requiresOptions) {
                    dropdownSection.show();
                } else {
                    dropdownSection.hide();
                }
            });

            // Handle options source changes
            $(document).on('change', '.options-source-select', function() {
                const fieldRow = $(this).closest('.field-row');
                const categorySection = fieldRow.find('.category-select-section');
                const customSection = fieldRow.find('.custom-options-section');

                if ($(this).val() === 'category') {
                    categorySection.show();
                    customSection.hide();
                } else {
                    categorySection.hide();
                    customSection.show();
                }
            });

            // Add simple field
            $(document).on('click', '.add-simple-field', function() {
                addSimpleField();
            });

            // Remove simple field
            $(document).on('click', '.remove-simple-field', function() {
                if ($('.simple-fields-container .field-row').length > 1) {
                    $(this).closest('.field-row').remove();
                    updateSimpleFieldNumbers();
                } else {
                    showToast('At least one field is required', 'error');
                }
            });

            // Add option to dropdown field
            $(document).on('click', '.add-option', function() {
                const container = $(this).siblings('.options-container');
                const fieldRow = $(this).closest('.field-row');
                const fieldIndexMatch = fieldRow.find('input[name*="fields["]').attr('name').match(
                    /fields\[(\d+)\]/);
                const currentFieldIndex = fieldIndexMatch ? fieldIndexMatch[1] : 0;
                const optionIndex = container.find('.option-row').length;

                const optionHtml = `
                            <div class="option-row mb-2">
                                <div class="row">
                                    <div class="col-md-5">
                                        <input type="text" name="fields[${currentFieldIndex}][dropdown_options][${optionIndex}][value]" 
                                            class="form-control" placeholder="Value (e.g., dell)">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="fields[${currentFieldIndex}][dropdown_options][${optionIndex}][label]" 
                                            class="form-control" placeholder="Label (e.g., Dell)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-option">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                container.append(optionHtml);
            });

            // Remove option
            $(document).on('click', '.remove-option', function() {
                $(this).closest('.option-row').remove();
            });

            function updateSimpleFieldNumbers() {
                $('.simple-fields-container .field-row').each(function(index) {
                    $(this).find('.text-secondary').text(`Field ${index + 1}`);
                });
            }

            $(document).on('click', '.edit-asset-type', function() {
                const id = $(this).data('id');
                window.location.href = `/asset-types/${id}/edit`;
            });

            $(document).on('click', '.delete-asset-type', function() {
                const id = $(this).data('id');
                if (confirm('Are you sure you want to delete this asset type?')) {
                    $.ajax({
                        url: `/asset-types/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            showToast('Asset type deleted successfully!', 'success');
                            loadConfigurationData();
                        },
                        error: function(xhr) {
                            showToast('Error deleting asset type: ' + (xhr.responseJSON
                                ?.message || 'Unknown error'), 'error');
                        }
                    });
                }
            });

            $(document).on('click', '#addComponentBtn', function() {
                window.location.href = '{{ route('components.create') }}';
            });

            $(document).on('click', '.edit-component', function() {
                const id = $(this).data('id');
                window.location.href = `/components/${id}/edit`;
            });

            $(document).on('click', '.delete-component', function() {
                const id = $(this).data('id');
                if (confirm('Are you sure you want to delete this component?')) {
                    $.ajax({
                        url: `/components/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            showToast('Component deleted successfully!', 'success');
                            loadConfigurationData();
                        },
                        error: function(xhr) {
                            showToast('Error deleting component: ' + (xhr.responseJSON
                                ?.message || 'Unknown error'), 'error');
                        }
                    });
                }
            });

            $(document).on('click', '#addDropdownCategoryBtn', function() {
                window.location.href = '{{ route('dropdown-options.create') }}';
            });

            $(document).on('click', '.edit-dropdown-category', function() {
                const category = $(this).data('category');
                window.location.href = `/dropdown-options/${category}/edit`;
            });

            $(document).on('click', '.delete-dropdown-category', function() {
                const category = $(this).data('category');
                if (confirm('Are you sure you want to delete this dropdown category?')) {
                    $.ajax({
                        url: `/dropdown-options/${category}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            showToast('Dropdown category deleted successfully!', 'success');
                            loadConfigurationData();
                        },
                        error: function(xhr) {
                            showToast('Error deleting dropdown category: ' + (xhr.responseJSON
                                ?.message || 'Unknown error'), 'error');
                        }
                    });
                }
            });

            function showToast(message, type) {
                const toastClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const toast = $(`
                            <div class="alert ${toastClass} alert-dismissible fade show position-fixed" 
                                style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                                ${message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);

                $('body').append(toast);

                setTimeout(function() {
                    toast.alert('close');
                }, 3000);
            }
        });
    </script>
@endsection
