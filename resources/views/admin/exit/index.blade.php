@extends('admin.layout.master')
@section('title', 'Employee Exit Requests')

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

    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <!-- Display Error Message -->
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Display Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Smooth Auto Dismiss Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert) => {
                // Automatically dismiss after 4 seconds
                setTimeout(() => {
                    // Bootstrap fade out
                    alert.classList.remove('show');
                    alert.classList.add('hide');
                    // Remove from DOM after fade animation (150ms)
                    setTimeout(() => alert.remove(), 150);
                }, 4000);
            });
        });
    </script>

    {{-- Breadcrumbs --}}
    <div class="mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="active"><span><b>Employee Exit Requests</b></span></li>
                </ol>
            </div>
        </div>
    </div>


    <!-- START EMPLOYEE EXIT STATUS ROW -->
    <div class="row mt-4 g-3">

        @php
            $cards = [
                [
                    'title' => 'Total Exits',
                    'value' => $totalExits ?? 0,
                    'icon' => 'las la-user-clock',
                    'color' => 'success',
                ],
                [
                    'title' => 'Pending',
                    'value' => $resignationSubmitted ?? 0,
                    'icon' => 'las la-envelope-open-text',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Manager Approved',
                    'value' => $managerApproved ?? 0,
                    'icon' => 'las la-check-circle',
                    'color' => 'info',
                ],

                [
                    'title' => 'HR Approved',
                    'value' => $hrApproved ?? 0,
                    'icon' => 'las la-check-circle',
                    'color' => 'info',
                ],

                [
                    'title' => 'Clearance In Progress',
                    'value' => ($clearanceInProgress ?? 0) + ($documentsRelieving ?? 0),
                    'icon' => 'las la-tasks',
                    'color' => 'warning',
                ],
                [
                    'title' => 'Relieved',
                    'value' => $relieved ?? 0,
                    'icon' => 'las la-user-check',
                    'color' => 'success',
                ],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 flex-grow-1">
                <div class="card shadow-sm border-0 h-100 card-hover-effect">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="d-block mb-1">{{ $card['title'] }}</span>
                                <h3 class="mb-0 text-{{ $card['color'] }}">{{ $card['value'] }}</h3>
                            </div>
                            <div class="icon1 bg-{{ $card['color'] }}-transparent rounded-circle p-3 ms-3">
                                <i class="{{ $card['icon'] }} la-2x icon-hover"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <!-- END EMPLOYEE EXIT STATUS ROW -->

    <style>
        .card-hover-effect {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            min-width: 150px;
        }

        .card-hover-effect:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .icon1 {
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .icon-hover:hover {
            transform: scale(1.2) rotate(10deg);
        }

        .bg-success-transparent {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .bg-primary-transparent {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .bg-info-transparent {
            background-color: rgba(23, 162, 184, 0.1);
        }

        .bg-warning-transparent {
            background-color: rgba(255, 193, 7, 0.1);
        }

        /* Ensure all cards have same height */
        .row>.col-12>.card {
            height: 100%;
        }
    </style>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Employee Exit Requests</h4>
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
                        <!-- Search -->
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
                            <div class="row mb-3">

                              <div class="col-sm-2">
                                    <p class="form-label">Employee</p>
                                    <select class="form-select search_test filter_border"style="border-radius: 20px;"  id="employeeFilter" data-filter>
                                        <option value="">All</option>
                                        @foreach ($employee_type as $emp)
                                            <option value="{{ $emp->emp_id }}">{{ $emp->emp_full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-2">
                                    <p class="form-label">Exit Type</p>
                                    <select class="form-select search_test filter_border"style="border-radius: 20px;" id="exitTypeFilter" data-filter>
                                        <option value="">All</option>
                                        @foreach ($exit_resone as $type)
                                            <option value="{{ $type->m_id }}">{{ $type->m_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-2">
                                    <p class="form-label">Status</p>
                                    <select class="form-select search_test filter_border"style="border-radius: 20px;" id="statusFilter" data-filter>
                                        <option value="">All</option>
                                        <option value="RESIGNATION_SUBMITTED">Resignation Submitted</option>
                                        <option value="MANAGER_APPROVED">Manager Approved</option>
                                        <option value="HR_APPROVED">HR Approved</option>
                                        <option value="CLEARANCE_IN_PROGRESS">Clearance In Progress</option>
                                        <option value="DOCUMENTS_AND_RELIEVING">Documents & Relieving</option>
                                        <option value="RELIEVED">Relieved</option>
                                        <option value="EXIT_CLOSED">Closed</option>
                                    </select>
                                </div>

                                <div class="col-sm-2">
                                    <p class="form-label">Date Range</p>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary-subtle text-primary border-0">
                                            <i class="las la-calendar-alt fs-5"></i>
                                        </span>
                                        <input type="text" id="fromDate" data-filter class="form-control border-0"
                                            placeholder="From Date">
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

                    <table class="table table-hover table-vcenter text-wrap border-bottom" id="exit-table-dynamic">
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


    <div class="modal fade" id="revertModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="revertForm" action="{{ route('exit.revert') }}">
                @csrf
                <input type="hidden" name="id" id="revert_id">

                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title">Revert Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <label class="form-label">Remark <span class="text-danger">*</span></label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="Enter remark..." required minlength="5"
                            maxlength="500"></textarea>
                        <div class="invalid-feedback">
                            Please enter a remark (minimum 5 characters).
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary">Submit</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <script>
        $(function() {
            // Initialize date range picker
            $('#fromDate').daterangepicker({
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                minDate: moment('2000-01-01'),
                maxDate: moment().add(5, 'years'),
                opens: 'left',
                autoApply: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')
                    ],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Current Fiscal Period': [moment().startOf('quarter'), moment().endOf('quarter')],
                },
                locale: {
                    format: 'MMM D, YYYY'
                }
            });

            // Initialize datatable
            datatable({
                tableId: "exit-table-dynamic",
                url: "{{ route('admin.employee-exit.index') }}",
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



    <script>
        $(document).ready(function() {

            // 🔹 Populate modal hidden input with ID
            $('#revertModal').on('show.bs.modal', function(event) {
                let button = $(event.relatedTarget); // Button that triggered the modal
                let id = button.data('id'); // Extract info from data-* attributes
                $(this).find('#revert_id').val(id); // Set value in hidden input
            });

            // 🔹 Submit form via AJAX
            $('#revertForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        // Optionally, show a nicer alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Request reverted successfully'
                        });

                        $('#revertModal').modal('hide');

                        // Update your page content if needed
                        // For now, reload
                        location.reload();
                    },
                    error: function(xhr) {
                        let err = xhr.responseJSON?.message || 'Something went wrong';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err
                        });
                        console.log(xhr.responseText);
                    }
                });
            });

        });
    </script>

@endsection
