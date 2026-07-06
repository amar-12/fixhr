@extends('admin.layout.master')
@section('title', 'Kit Damage')
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
                    <li class="active"><span><b>Kit Damage</b></span></li>
                </ol>
            </div>
        </div>
    </div>


    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Kit Damage Records</h4>
                </div>

                <div class="card-body">
                    <div class="row align-items-end">

                        <!-- Show Entries -->
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label class="form-label">Show entries</label>
                                <select id="customLengthMenu" class="form-select form-select-sm" data-length>
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

                        <div class="col-sm-6"></div>

                        <!-- Filters Button -->
                        <div class="col-sm-1">
                            <button class="custom-button w-100" type="button" onclick="toggleFilters()">
                                <i class="las la-filter"></i> Filters
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

                    <!-- FILTERS -->
                    <div id="filterContainer" class="row mt-3" style="display:none;">

                        <div class="col-md-2">
                            <label class="form-label">Kit Name</label>
                            <select class="form-select" data-filter id="kitFilter">
                                <option value="">All</option>
                                @foreach ($kits as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Damaged By</label>
                            <select class="form-select" data-filter id="employeeFilter">
                                <option value="">All</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->emp_id }}">{{ $emp->emp_full_name }}</option>
                                @endforeach
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
                    <table class="table table-hover table-vcenter text-wrap border-bottom mt-3" id="kit-damage-table">
                        <thead>
                            <tr>
                                @foreach ($columns as $c)
                                    <th style="font-size:12px;width:{{ $c['width'] }}">{{ $c['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>

                    <!-- Pagination -->
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

            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            datatable({
                tableId: "kit-damage-table",
                url: "{{ route('damage.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: true
            });
        });
    </script>
@endsection
