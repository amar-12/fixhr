@extends('admin.layout.master')
@section('title')
    Leave
@endsection

@section('css')
    <style>
        .daterangepicker .ranges li.active {
            background-color: #0d6efd;
        }

        .daterangepicker td.active,
        .daterangepicker td.active:hover {
            background-color: #0d6efd;
        }
    </style>
@endsection
@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "leave-request-table-dynamic",
                url: "{{ route('requests.leave') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false,
                drawCallback: function(settings) {
                    const approvalButtons = $("#approval-buttons");

                    // Show or hide buttons based on settings.json.canApprove
                    if (settings.json?.canApprove || (settings.json?.apHiBtn && typeof settings.json
                            .apHiBtn === "object" && Object.keys(settings.json.apHiBtn).length > 0)) {
                        // console.log("✅ Showing Approval Buttons");
                        approvalButtons.addClass("d-flex").css("display", "flex");
                    } else {
                        // console.log("❌ Hiding Approval Buttons");
                        // approvalButtons.removeClass("d-flex").css("display", "none");
                        approvalButtons.addClass("d-flex").css("display", "flex");
                    }
                }
            });

            // 🔹 Now get the DataTable instance
            const table = $('#leave-request-table-dynamic').DataTable();

            // 🔹 Disable sorting for specific columns
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13].forEach(idx => {
                table.settings()[0].aoColumns[idx].bSortable = false;
            });

            // 🔹 Redraw table so changes take effect
            table.draw();
        });

        function sortTable(tableId, colIndex) {
            const table = document.getElementById(tableId);
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            // Determine current sort direction
            const currentDir = table.getAttribute("data-sort-dir-" + colIndex) || "asc";

            const newDir = currentDir === "asc" ? "desc" : "asc";

            rows.sort((a, b) => {
                let x = a.children[colIndex].innerText.trim();
                let y = b.children[colIndex].innerText.trim();

                // Detect type: numeric, date, or string
                const isNumeric = !isNaN(parseFloat(x)) && !isNaN(parseFloat(y));
                const isDate = !isNaN(Date.parse(x)) && !isNaN(Date.parse(y));

                if (isNumeric) {
                    x = parseFloat(x);
                    y = parseFloat(y);
                    } else if (isDate) {
                    x = new Date(x);
                    y = new Date(y);
                } else {
                    x = x.toLowerCase();
                    y = y.toLowerCase();
                }

                if (x < y) return newDir === "asc" ? -1 : 1;
                if (x > y) return newDir === "asc" ? 1 : -1;
                return 0;
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));

            // Update direction for that column
            table.setAttribute("data-sort-dir-" + colIndex, newDir);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <script>
        $(function() {
            $('#fromDate, #leave_dateFilter').daterangepicker({
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
@endsection

@section('content')
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a class="text-white">Requests</a></li>
                <li class="active"><span><b>Leave</b></span></li>
            </ol>
        </div>

        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Leave</h4>
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
                                <div id="approval-buttons" class="justify-content-end gap-3 m-5">
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

                            <div class="col-sm-1" style="margin-top: 30px;">
                                <div class="form-group filter_dots">
                                    <button class="btn btn-info" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown"
                                        style="min-width: 220px;">
                                        <li>
                                            <a class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                                id="approveBtn" value="1">
                                                <i class="las la-check-circle"></i> Approve
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2"
                                                id="rejectBtn" value="0">
                                                <i class="las la-times-circle"></i> Reject
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                                data-bs-toggle="modal" data-bs-target="#ExcelModal">
                                                <i class="las la-file-upload"></i> Upload Leave Request
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                                href="{{ route('leave-request.downloadLeaveRequestSampleExcel') }}">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Filters --}}
                            <div class="row" id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="col-md">
                                    <label for="branchFilter" class="form-label">Branch</label>
                                    <select id="leave_branchFilter" data-filter class="form-select search-txt filter_border">
                                        <option value="">All</option>
                                        @foreach ($branch as $branchF)
                                        <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="departmentFilter" class="form-label">Department</label>
                                    <select id="leave_departmentFilter" data-filter class="form-select search-txt filter_border">
                                        <option value="">All</option>
                                        @foreach ($departments as $departmentF)
                                        <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}
                                        </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="designationFilter" class="form-label">Designation</label>
                                    <select id="leave_designationFilter" data-filter class="form-select search-txt filter_border">
                                        <option value="">All</option>
                                        @foreach ($designations as $designationF)
                                        <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="activeFilter" class="form-label">Employee Status</label>
                                    <select id="leave_activeFilter" data-filter class="form-select search-txt filter_border filter_border">
                                        <option value="">All</option>
                                        <option value="71">Active</option>
                                        <option value="72">Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="leave_statusFilter" class="form-label">Leave Status</label>
                                    <select id="leave_statusFilter" data-filter class="form-select search-txt filter_border filter_border">
                                        <option value="">All</option>
                                        @foreach ($leave_status as $status)
                                            <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md">
                                    <div class="form-group">
                                        <p class="form-label">Applied Date Range</p>
                                        <div class="input-group mb-3"
                                            style="border-radius: 50px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                            <span class="input-group-text bg-primary-subtle text-primary border-0"
                                                style="border-radius: 50px 0 0 50px; padding: 0.5rem 1rem;">
                                                <i class="las la-calendar-alt fs-5"></i>
                                            </span>
                                            <input type="text" id="fromDate" name="fromDate" class="form-control border-0"
                                                style="border-radius: 0 50px 50px 0; padding-left: 1rem;" data-date-filter="from-date"
                                                placeholder="Select date">
                                        </div>
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

                    @if (session('import_errors_blade'))
                        <div class="alert d-flex align-items-center mt-3">
                            <p>There were errors in the import. You can download the error file from the link below:</p>
                            <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()"
                                class="ms-2 mb-4 btn btn-danger">Download Error File</a>
                        </div>
                    @endif

                    <div>
                        <table class="table display table-vcenter table-hover text-wrap border-bottom"
                            id="leave-request-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $key => $column)
                                        <th style="font-size: 13px" onclick="sortTable('leave-request-table-dynamic', {{ $key }})">{{ $column }}</th>
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

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="approvalMessage"></p>
                    <label for="approvalRemark" class="form-label">Enter Remark:</label>
                    <textarea class="form-control" id="approvalRemark" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-primary" id="confirmApproval" data-id="">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Revert Modal -->
    <div class="modal fade" id="revert" tabindex="-1" aria-labelledby="revertLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="revertLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="revertMessage"></p>
                    <label for="revertRemark" class="form-label">Enter Remark:</label>
                    <textarea class="form-control" id="revertRemark" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-primary" id="confirmRevert">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload excel file -->
    <div class="modal fade" id="ExcelModal" tabindex="-1" role="dialog" aria-labelledby="largemodal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <h5 class="modal-title" id="DaModalLabel">Upload Leave Approval File</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <form action="{{ route('leave-request.import') }}" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="upload_file">Upload File :</label>
                                    <input type="file" name="import_file" id="import_file" class="form-control"
                                        required accept=".xlsx, .csv">
                                    <br>
                                    <div style="display: flex; align-items: center;">
                                        <p class="fw-bold" style="margin: 0;">Note -</p>
                                        <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-outline-danger " data-bs-dismiss="modal">Close</a>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
    var leaveIds = [];

    function selectAllCheckboxes(element) {
        var isChecked = $(element).is(':checked');
        $('.testClass').prop('checked', isChecked);
        if (isChecked) {
            leaveIds = [];
            $('.testClass').each(function() {
                leaveIds.push($(this).val());
            });
        } else {
            leaveIds = [];
        }
    }

    function selectCheckbox(value) {
        var leaveId = $(value).val();
        var isChecked = $(value).is(':checked');

        if (isChecked) {
            leaveIds.push(leaveId);
        } else {
            var index = leaveIds.indexOf(leaveId);
            if (index !== -1) {
                leaveIds.splice(index, 1);
            }
        }

        var allChecked = true;
        $('.testClass').each(function() {
            if (!$(this).prop('checked')) {
                allChecked = false;
                return false;
            }
        });
        $('#selectAll').prop('checked', allChecked);
    }

    $(document).ready(function() {
        let approvalType = ""; // Store action type (Approve/Reject)
        let logStatus;
        let masterApproveBtnGlobal = @json($masterApproveBtn ?? null);

        $("#approveBtn").click(function() {
            let isApprove = $(this).attr('id') === "approveBtn";
            actionType = isApprove ? "Approve" : "Reject";
            approvalType = $(this).attr('value');
            console.log('masterApproveBtnGlobal1', masterApproveBtnGlobal);
            if (masterApproveBtnGlobal) {
                $("#confirmApproval").text(masterApproveBtnGlobal[1]?.m_name ?? 'Confirm');
                $("#confirmApproval").attr("data-id", masterApproveBtnGlobal[1]?.m_id ?? '141');
                logStatus = $("#confirmApproval").attr("data-id") || 157;
            } else {
                $("#confirmApproval").text('Approved');
                logStatus = isApprove ? 157 : 170;
            }

            $("#approvalMessage").text(
                `Are you sure you want to ${actionType.toLowerCase()} this request?`);
            $("#approvalModal").modal("show");
        });

        $("#rejectBtn").click(function() {
            let isApprove = $(this).attr('id') === "approveBtn";
            actionType = isApprove ? "Approve" : "Reject";
            approvalType = $(this).attr('value');
            console.log('masterApproveBtnGlobal2', masterApproveBtnGlobal);
            if (masterApproveBtnGlobal) {
                $("#confirmApproval").text(masterApproveBtnGlobal[0]?.m_name ?? 'Rejected');
                $("#confirmApproval").attr("data-id", masterApproveBtnGlobal[0]?.m_id ?? '170');
                logStatus = $("#confirmApproval").attr("data-id") || 170;
            } else {
                $("#confirmApproval").text('Rejected');
                logStatus = isApprove ? 157 : 170;
            }

            $("#approvalMessage").text(
                `Are you sure you want to ${actionType.toLowerCase()} this request?`);
            $("#approvalModal").modal("show");
        });

        // Confirm Button Click
        $("#confirmApproval").click(function() {
            let remark = $("#approvalRemark").val().trim();
            if (remark === "") {
                Swal.fire({
                    icon: "warning",
                    text: "Please enter a remark before proceeding.",
                    timer: 3000,
                });
                $("#confirmApproval").attr("disabled", false);
                return false;
            }

            if (leaveIds.length === 0) {
                Swal.fire({
                    icon: "warning",
                    text: "No leave selected for approval.",
                    timer: 3000,
                });
                $("#confirmApproval").attr("disabled", false);
                return false;
            }

            // Get DataTable instance
            let table = $("#leave-request-table-dynamic").DataTable();
            let settings = table.settings()[0];
            let apHiBtnData = settings.json?.apHiBtn || {};
            let canApprove = settings.json?.canApprove;
            let moduleId = settings.json?.moduleId;

            if (typeof apHiBtnData !== "undefined" && Object.keys(apHiBtnData).length > 0) {
                // Create an array of objects for each leave ID
                let formattedData = leaveIds.map(leaveId => ({
                    approval_status: apHiBtnData.approval_status,
                    approval_type: approvalType,
                    approval_action_type: apHiBtnData.approval_action_type,
                    approval_sequence: apHiBtnData.approval_sequence,
                    lvr_id: leaveId,
                    module_id: apHiBtnData.module_id,
                    is_last_approval: apHiBtnData.is_last_approval,
                    emp_d_id: apHiBtnData.emp_d_id,
                    message: remark
                }));
                // console.log("Formatted Data:", formattedData);

                // Send AJAX request for approval/rejection
                $.ajax({
                    url: '{{ route('admin.bulk-handler') }}',
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        POST_TYPE: 'LEAVE_REQUEST_APPROVAL',
                        data: formattedData
                    },
                    beforeSend: function() {
                        $("#confirmApproval").attr("disabled", true);
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: "success",
                            text: response.message,
                            timer: 3000,
                            didClose: () => location.reload()
                        });
                    },
                    error: function(error) {
                        Swal.fire({
                            icon: "error",
                            text: "Something went wrong!",
                            timer: 3000,
                        });
                    },
                    complete: function() {
                        $("#confirmApproval").attr("disabled", false);
                        $("#approvalModal").modal("hide");
                    }
                });
            } else {
                let formattedData = leaveIds.map(leaveId => ({
                    log_module_id: moduleId.module_id,
                    log_request_id: leaveId,
                    log_description: remark,
                    log_status: logStatus,
                    deduction_amount: 0,
                    prefix: 'lvr_',
                    model: 'LeaveRequest'
                }));

                // Send AJAX request for approval/rejection
                $.ajax({
                    url: '{{ route('admin.common-bulk-approval') }}',
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        data: formattedData
                    },
                    beforeSend: function() {
                        $("#confirmApproval").attr("disabled", true);
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: "success",
                            text: response.message,
                            timer: 3000,
                            didClose: () => location.reload()
                        });
                    },
                    error: function(error) {
                        Swal.fire({
                            icon: "error",
                            text: "Something went wrong!",
                            timer: 3000,
                        });
                    },
                    complete: function() {
                        $("#confirmApproval").attr("disabled", false);
                        $("#approvalModal").modal("hide");
                    }
                });
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    toast: 'swal2-toast-green-glow'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif
    });

    $(document).on('click', '.remove-btn', function() {
        var id = $(this).data('id');
        var url = "{{ url('admin/requests/leave-delete') }}";

        Swal.fire({
            title: 'Are you sure?',
            text: "Leave request cancel successfully, and leave balance updated.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: 'Removed!',
                                text: response.message,
                                icon: 'success'
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Failed!',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    let selectedRevertId = null;
    let selectedRevertUrl = null;
    
    // When "Revert" button clicked
    $(document).on('click', '.revert-button', function () {
        var button = $(this);
        var leaveId = button.data('id');
        var url = button.data('url');
        selectedRevertId = $(this).data('id');
        selectedRevertUrl = $(this).data('url');
    
        $('#revertMessage').text('Are you sure you want to revert this leave request?');
        $('#revertRemark').val('');
        $('#revert').modal('show');
    });
    
    // When "Confirm" inside modal is clicked
    $(document).on('click', '#confirmRevert', function () {
        const remark = $('#revertRemark').val().trim();
    
        if (remark === '') {
            Swal.fire({
                title: 'Required!',
                text: 'Please enter a remark before reverting.',
                icon: 'warning',
                confirmButtonText: 'Ok'
            });
            return;
        }
    
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
    
        $.ajax({
            url: selectedRevertUrl,
            method: 'POST',
            data: {
                id: selectedRevertId,
                remark: remark,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                $btn.prop('disabled', false).html('Confirm');
                $('#revert').modal('hide');
    
                if (response.status) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('Confirm');
                $('#revert').modal('hide');
    
                Swal.fire({
                    title: 'Oops!',
                    text: 'Something went wrong. Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            }
        });
    });
</script>
