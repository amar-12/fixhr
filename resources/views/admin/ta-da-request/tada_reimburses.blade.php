@extends('admin.layout.master')
@section('title')
    Reimburses
@endsection

@section('css')
@endsection
@section('content')
    @if (session('error'))
        <div class="alert alert-danger fade-message">
            {{ session('error') }}
        </div>
    @endif

    <style>
        .fade-message {
            transition: opacity 0.5s ease;
        }
    </style>

    <script>
        setTimeout(function() {
            let msg = document.querySelector('.fade-message');
            if (msg) {
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 2000); // remove after fade
            }
        }, 2000);
    </script>
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/reimburse') }}">Travel Management</a></li>
                    <li class="active"><span><b>Reimburse</b></span></li>
                </ol>
            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <div class="btn-group">
                                    <a class="btn btn-outline-primary"
                                        onclick="downloadSheet('download-expense-booking-sheet')">
                                        <i class="fa fa-download"></i> Expense Sheet
                                    </a>
                                </div>
                            </div>
                            <div class="btn-list">
                                <div class="btn-group ms-2">
                                    <a class="btn btn-outline-primary"
                                        onclick="downloadSheet('download-tada-payment-sheet')">
                                        <i class="fa fa-download"></i> Payment Sheet
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bradcrumbs End --}}



    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Reimburse </h4>
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
                                    <li>
                                        <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#addEmployeeFile">
                                            <i class="las la-file-upload"></i> Upload File
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('formate.exportReport', ['type' => 'reimbursement']) }}"
                                            class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2">
                                            <i class="las la-file-download"></i> Download Format
                                        </a>
                                    </li>


                                </ul>

                            </div>
                        </div>

                     
                                <div class="row" id="filterContainer" style="display: none; margin-bottom: 21px;"> 
                                    <div class="col-md-2">
                                        <label for="sheetStatusFilter" class="form-label">Status</label>
                                        <select id="reimburse_sheetStatusFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                            <option value="0">Inprocessed</option>
                                            <option value="1">Paid</option>

                                        </select>
                                    </div>

                                    <div class="col-md-2">
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
                    <div class="">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="reimburse-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size: 12px; width: {{ $column['width'] }};">{{ $column['name'] }}
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

            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="largemodal" tabindex="-1" role="dialog" aria-labelledby="largemodal"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="largemodal1">Reimburse Claim List</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap">
                            <thead>
                                <th>S.No.</th>
                                <th>Employee Name</th>
                                <th>Claim ID</th>
                                <th>Claim Payed Amount</th>
                                <th>Action</th>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END MODAL -->


    {{-- for model file upload strat --}}
    <div class="modal fade" id="addEmployeeFile" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Employee File</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('reimburse.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Upload File :</label>
                                    <input type="file" name="file" id="file" class="form-control" required
                                        accept=".xlsx, .csv">
                                    <br>
                                    <div style="display: flex; align-items: center;">
                                        <p class="fw-bold" style="margin: 0;">Note -</p>
                                        <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
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
            // Initialize DataTable
            datatable({
                tableId: "reimburse-table-dynamic",
                url: "{{ route('reimburse.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });
        });

        function openEditModal(value) {
            // const id = context.dataset.tr-id;
            var tr_id = value.getAttribute('data-tr-id'); // Corrected attribute name
            var tr_claims_id = value.getAttribute('data-tr-claims-id'); // Corrected attribute name

            $.ajax({
                url: `{{ route('reimburse.edit', ':tr_claims_id') }}`.replace(':tr_claims_id', tr_claims_id),
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        var tbody = $('#largemodal .table tbody');
                        tbody.empty(); // Clear existing rows

                        response.data.forEach(function(item, index) {
                            var row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + (item.fh_employee ? item.fh_employee.emp_full_name : 'N/A') +
                                '</td>' +
                                '<td>' + (item.tc_unique_id ? item.tc_unique_id : 'N/A') + '</td>' +
                                '<td>' + (item.tc_payed_amount ? item.tc_payed_amount.toFixed(0) :
                                    'N/A') + '</td>' +
                                '<td>' +
                                '<a class="" href="' +
                                '{{ route('claim-request-is-paid.request.show', ':id') }}'.replace(
                                    ':id', item.hashed_id) + '">' +
                                '    <i class="feather-eye fs-6" style="margin-top: 10px; color: black;"></i>' +
                                '</a>' +
                                '</td>' +
                                '</tr>';
                            tbody.append(row);
                        });

                        // Show the modal
                        $('#largemodal').modal('show');
                    } else {
                        alert('Failed to load reimburse claim details');
                        $('#largemodal').modal('hide');
                    }
                    // $('#editReimburseForm').html(response);
                    // $('#largemodal').modal('show');
                },
                error: function(xhr) {
                    console.error('An error occurred:', xhr.responseText);
                }
            });
        }

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

        /* function downloadSheet(sheetType) {
             const fromDate = document.getElementById('fromDate').value;
             const toDate = document.getElementById('toDate').value;
             const status = document.getElementById('sheetStatusFilter').value;

             const baseUrl = '{{ url('') }}'; // Use the correct base URL
             const url = `${baseUrl}/${sheetType}?` + new URLSearchParams({
                 fromDate: fromDate,
                 toDate: toDate,
                 status: status
             }).toString();

             // Redirect to the URL to trigger file download
             window.location.href = url;
         }*/
    </script>

    @if (session('alert'))
        <script>
            Swal.fire({
                icon: 'info',
                text: '{{ session('alert') }}',
                timer: 3000,
            });
        </script>
    @endif
@endsection
