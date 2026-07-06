@extends('admin.layout.master')
@section('title', 'Scrap Assets')
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
                    <li class="active"><span><b>Scrap Assets</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Scrap Assets</h4>
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


                        {{-- <div class="col-sm-1" style="margin-top: 30px;">
                            <div class="form-group filter_dots">
                                <button class="btn btn-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                                    <li>
                                        <a class="dropdown-item text-secondary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#configurationModal">
                                            <i class="bi bi-gear"></i> Configuration
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div> --}}

                        <div class="row ">
                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">

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




    <script>
        $(document).ready(function() {

            datatable({
                tableId: "reimburse-table-dynamic",
                url: "{{ route('assets.scrap-list') }}",
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
                const statusEl = document.getElementById('sheetStatusFilter');

                const fromDate = fromDateEl ? fromDateEl.value : '';
                const status = statusEl ? statusEl.value : '';

                const baseUrl = '{{ url('') }}'; // Laravel base URL
                const url = `${baseUrl}/${sheetType}?` + new URLSearchParams({
                    fromDate: fromDate,
                    status: status
                }).toString();

                window.location.href = url;
            }



            // Configuration Management
            let assetTypes = [];
            let components = [];
            let dropdownOptions = {};
            let fieldTypes = [];


        });
    </script>
@endsection
