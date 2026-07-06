@extends('admin.layout.master')
@section('title', 'Employee Exit System')
@section('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-container {
            display: flex;
            justify-content: space-around;
            margin: 20px;
        }

        .custom-card {
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            width: 200px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .custom-card.blue {
            border: 2px solid #0d6efd;
            color: #0d6efd;
        }

        .custom-card.orange {
            border: 2px solid #f4a261;
            color: #f4a261;
        }

        .custom-card.green {
            border: 2px solid #2ecc71;
            color: #2ecc71;
        }

        .custom-card .number {
            font-size: 2em;
            font-weight: bold;
        }

        .custom-card .icon {
            margin-top: 10px;
            font-size: 1.5em;
        }
    </style>
@endsection


@section('content')
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;font-size: 12px; background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                {{-- <li><a href="{{ url('/admin/reimburse') }}">Offboarding</a></li> --}}
                <li class="active"><span><b>Offboarding</b></span></li>
            </ol>
        </div>
        <div class="row">
            <!-- Total Exits -->
            <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold text-muted">Total Exits</span>
                            <h4 class="mb-0 text-primary mt-1">58</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                            <i class="las la-sign-out-alt fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold text-muted">Pending Approvals</span>
                            <h4 class="mb-0 text-warning mt-1">57</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                            <i class="las la-hourglass-half fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Clearance -->
            <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold text-muted">In Clearance</span>
                            <h4 class="mb-0 text-info mt-1">10</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                            <i class="las la-clipboard-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="col-6 col-sm-4 col-md-3 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold text-muted">Completed</span>
                            <h4 class="mb-0 text-success mt-1">10</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                            <i class="las la-check-double fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

                                    <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown"
                                        style="min-width: 220px;">
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

                         

                            <div class="row g-3 mb-3" id="filterContainer" style="display: block;">

                                <!-- Asset Tag -->
                                <div class="col-6 col-sm-4 col-md-2">
                                    <label for="assetTagFilter" class="form-label fw-semibold">Asset Tag</label>
                                    <select id="assetTagFilter" name="asset_tag" class="form-select filter_border"
                                        data-filter>
                                        <option value="">All</option>
                                        <option value="AST-1001">AST-1001</option>
                                        <option value="AST-1002">AST-1002</option>
                                        <option value="AST-1003">AST-1003</option>
                                        <option value="AST-1004">AST-1004</option>
                                    </select>
                                </div>

                                <!-- Model Number -->
                                <div class="col-6 col-sm-4 col-md-2">
                                    <label for="modelNumberFilter" class="form-label fw-semibold">Model Number</label>
                                    <select id="modelNumberFilter" name="model_number" class="form-select filter_border"
                                        data-filter>
                                        <option value="">All</option>
                                        <option value="MD-2021">MD-2021</option>
                                        <option value="MD-2022">MD-2022</option>
                                        <option value="MD-2023">MD-2023</option>
                                        <option value="MD-2024">MD-2024</option>
                                    </select>
                                </div>

                                <!-- Serial Number -->
                                <div class="col-6 col-sm-4 col-md-2">
                                    <label for="serialNumberFilter" class="form-label fw-semibold">Serial Number</label>
                                    <select id="serialNumberFilter" name="serial_number"
                                        class="form-select filter_border" data-filter>
                                        <option value="">All</option>
                                        <option value="SN-001-A">SN-001-A</option>
                                        <option value="SN-002-B">SN-002-B</option>
                                        <option value="SN-003-C">SN-003-C</option>
                                        <option value="SN-004-D">SN-004-D</option>
                                    </select>
                                </div>

                                <!-- Asset Type -->
                                <div class="col-6 col-sm-4 col-md-2">
                                    <label for="assetTypeFilter" class="form-label fw-semibold">Asset Type</label>
                                    <select id="assetTypeFilter" name="asset_type" class="form-select filter_border"
                                        data-filter>
                                        <option value="">All</option>
                                        <option value="1">Laptop</option>
                                        <option value="2">Desktop</option>
                                        <option value="3">Printer</option>
                                        <option value="4">Monitor</option>
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">Date Range</label>
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

                        <table class="table table-hover table-vcenter text-wrap border-bottom"
                            id="reimburse-table-dynamic">
                            <thead>
                                <tr>
                                    {{-- @foreach ($columns as $column)
                                        <th style="font-size: 12px; width: {{ $column['width'] }};">
                                            {{ $column['name'] }}
                                        </th>
                                    @endforeach --}}
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
    </div>
@endsection
@section('script')
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
