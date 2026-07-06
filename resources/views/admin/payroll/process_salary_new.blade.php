@extends('admin.layout.master')
@section('title', 'Attendance - Add/Edit')

@section('css')
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

@endsection
@section('content')
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="">Payroll</a></li>
                        <li class="active"><span><b>{{ 'Attendance Vault' }}</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header border-0">
                    <h4 class="card-title">Employee Salary Process</h4>
                    {{-- <div class="d-lg-flex d-block ms-auto">
                        <div class="btn-list">
                            <button type="button" class="btn btn-outline-primary" id="addEmpSalaryBtn">Add Salary</button>
                        </div>
                    </div> --}}
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

                        <div class="col-sm-4">
                        </div>

                        <div class="col-sm-2" style=" margin-top: 10px;">
                            <div id="approval-buttons" class="justify-content-end gap-3 m-5" style="padding-left: 62px;">
                                <label class="custom-control custom-checkbox-md mx-3">Select All &nbsp;&nbsp;
                                    <input type="checkbox" id="selectAll" class="custom-control-input-success"
                                        name="example-checkbox1" value="option1" onclick="selectAllCheckboxes(this)">
                                    <span class="custom-control-label-md success"></span>
                                </label>

                            </div>
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

                        <div class="row">
                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="row">
                                    <div class="col-md">
                                        <label for="branchFilter" class="form-label">Branch</label>
                                        <select id="branchFilter" data-filter class="form-select search-txt">
                                            <option value="">All</option>
                                            @foreach ($branch as $branchF)
                                                <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="departmentFilter" class="form-label">Department</label>
                                        <select id="departmentFilter" data-filter class="form-select search-txt">
                                            <option value="">All</option>
                                            @foreach ($departments as $departmentF)
                                                <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="designationFilter" class="form-label">Designation</label>
                                        <select id="designationFilter" data-filter class="form-select search-txt">
                                            <option value="">All</option>
                                            @foreach ($designations as $designationF)
                                                <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="activeFilter" class="form-label">Employee Status</label>
                                        <select id="activeFilter" data-filter class="form-select search-txt">
                                            <option value="">All</option>
                                            <option value="71">Active</option>
                                            <option value="72">Inactive</option>
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="toDate" class="form-label">Date</label>
                                        <span class="bg-light border-0 rounded-start-4">

                                        </span>
                                        <input type="date" id="fromDate" name="fromDate" class="form-control"
                                            data-date-filter="from-date">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table display table-vcenter text-wrap border-bottom"
                            id="manage-salary-table-dynamic">
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
@endsection

<script>
    function toggleFilters() {
        const container = document.getElementById('filterContainer');
        container.style.display = container.style.display === 'none' ? 'flex' : 'none';
    }
</script>

{{-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("select-all").addEventListener("click", function() {
            document.querySelectorAll(".select-employee").forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    });
</script> --}}

@section('js')

    {{-- <script>
        $(document).ready(function() {
            // Initialize DataTable
            let attendanceTable = $('#attendanceTable').DataTable({
                "pageLength": 25 // Default to 25 as per your filter
            });

            // Apply filter
            $('#entriesFilter').on('change', function() {
                let selectedValue = parseInt($(this).val());
                console.log("Selected Value:", selectedValue);

                // Update page length
                attendanceTable.page.len(selectedValue).draw();
            });
        });
    </script> --}}


    {{--
    <script>
        $(document).ready(function() {
            $('#process-salaries-btn').click(function() {
                var selectedEmployees = [];

                // Collect selected employee IDs
                $('.select-employee:checked').each(function() {
                    selectedEmployees.push($(this).val());
                });

                if (selectedEmployees.length === 0) {
                    alert("Please select at least one employee.");
                    return;
                }

                // Create form data
                var formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");

                // ✅ Append each employee ID separately
                selectedEmployees.forEach(empId => {
                    formData.append('selected_employees[]', empId);
                });

                $.ajax({
                    url: "{{ route('process-salaries') }}", // Laravel route name
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#process-salaries-btn').text('Processing...').prop('disabled', true);
                    },
                    success: function(response) {
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert("Error processing salaries.");
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        $('#process-salaries-btn').text('Process Salaries').prop('disabled',
                            false);
                    }
                });
            });
        });
    </script> --}}


    <script>
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "manage-salary-table-dynamic",
                url: "{{ route('payroll.process-salary', [$payroll->pp_id]) }}",

                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false,
            });

        });
    </script>


@endsection
