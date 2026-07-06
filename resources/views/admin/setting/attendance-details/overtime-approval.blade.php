<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$branchFilter = CommonUtils::getCustomModelData(new Branch(), [['method' => 'where', 'args' => ['br_b_id', $user->emp_b_id]]]);
$departmentFilter = CommonUtils::getCustomModelData(new Department(), [['method' => 'where', 'args' => ['d_b_id', $user->emp_b_id]]]);
$designationFilter = CommonUtils::getCustomModelData(new Designation(), [['method' => 'where', 'args' => ['dg_b_id', $user->emp_b_id]]]);
$gradeFilter = CommonUtils::getCustomModelData(new Grade(), [['method' => 'where', 'args' => ['g_b_id', $user->emp_b_id]]]);

$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    Overtime Approve
@endsection
@section('css')
@endsection

@section('script')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <script type="text/javascript">
        var otIDs = [];
        $(function() {
            $('#fromDate').daterangepicker({
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                minDate: moment('2000-01-01'),
                maxDate: moment().add(5, 'years'),
                opens: 'center',
                autoApply: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Current Fiscal Period': [moment().startOf('quarter'), moment().endOf('quarter')],
                },
                locale: {
                    format: 'MMM D, YYYY'
                }
            }).on('apply.daterangepicker', function(ev, picker) {
                // Date change hone par table refresh karo
                let table = $("#overtime-approval-table-dynamic").DataTable();
                if (table) {
                    table.ajax.reload();
                }
            });
        });

        $(document).ready(function () {
            // First initialize your custom datatable
            datatable({
                tableId: "overtime-approval-table-dynamic",
                url: "{{ route('approve.overtime') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false,
                drawCallback: function (settings) {
                    const json = settings.json;
                    if (json) {
                        $("#totalEmployees").text(json.totalEmployees);
                        $("#presentCount").text(json.presentCount);
                        $("#absentCount").text(json.absentCount);
                        $("#halfDayCount").text(json.halfDayCount);
                        $("#lateCount").text(json.lateCount);
                        $("#leaveCount").text(json.leaveCount);
                    }
                    const approvalButtons = $(".approval-buttons");

                    if (settings.json?.canApprove ||
                        (settings.json?.apHiBtn && typeof settings.json.apHiBtn === "object" && Object.keys(settings.json.apHiBtn).length > 0)) {
                        approvalButtons.css("display", "block");
                    } else {
                        approvalButtons.css("display", "none");
                    }

                    setTimeout(() => {
                        $('.employee-checkbox').each(function () {
                            let id = String($(this).data('id')).trim();
                            console.log(otIDs.includes(id));

                            if (otIDs.includes(id)) {
                                $(this).prop('checked', true);
                            }
                        });
                    }, 0);
                }
            });
        });

        function selectOTCheckbox(checkbox) {
            var rawId = $(checkbox).data('id');
            var id = String(rawId).trim();
            var checked = $(checkbox).is(':checked');

            if (checked) {
                if (!otIDs.includes(id)) otIDs.push(id);
            } else {
                otIDs = otIDs.filter(x => String(x).trim() !== id);
            }

            // Update select all checkbox
            $('#selectAll').prop(
                'checked',
                $('.employee-checkbox').length === $('.employee-checkbox:checked').length
            );

            console.log("Selected IDs:", otIDs);
        }

        function selectAllOT(el) {

            let checked = $(el).is(':checked');

            if (checked) {

                $.ajax({
                    url: "{{ route('approve.overtime') }}",
                    type: "GET",
                    data: {
                        get_ids: 1,
                        daily_branchFilter: $('#daily_branchFilter').val(),
                        daily_designationFilter: $('#daily_designationFilter').val(),
                        daily_departmentFilter: $('#daily_departmentFilter').val(),
                        daily_activeFilter: $('#daily_activeFilter').val(),
                        daily_approveFilter: $('#daily_approveFilter').val(),
                        fromDate: $('#fromDate').val()
                    },
                    success: function (response) {
                        // normalize ids to strings so comparisons match data-id string values
                        otIDs = Array.isArray(response.ids) ? response.ids.map(i => String(i).trim()) : [];
                        $('.employee-checkbox').prop('checked', true);
                        console.log("All IDs Loaded:", otIDs);
                    }
                });

            } else {
                otIDs = [];
                $('.employee-checkbox').prop('checked', false);
            }
        }
    </script>
@endsection
<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    table td {
        padding: 0;
    }
</style>

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

@section('content')
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/attendance/daily-attendance') }}">Attendance</a></li>
                <li class="active"><span><b>Overtime</b></span></li>
            </ol>
        </div>

        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Overtime</h4>
                        <div class="page-rightheader ms-auto">
                            <div class="align-items-end flex-wrap my-auto right-content breadcrumb-right">
                                <div class="d-flex">
                                </div>
                            </div>
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

                            <div class="col-sm-4">
                            </div>

                            <div class="col-sm-2" style="margin-top: 10px;">
                                <div id="approval-buttons" class="justify-content-end gap-3 m-5"
                                    style="text-align: right;">
                                    <label class="custom-control custom-checkbox-md mx-3">
                                        Select All &nbsp;&nbsp;
                                        <input type="checkbox" id="selectAll" class="custom-control-input-success"
                                            onclick="selectAllOT(this)">
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
                                    <ul class="dropdown-menu p-2" style="min-width: fit-content;">
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
                                    </ul>
                                </div>
                            </div>
                         
                            <div class="row" id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="col-sm-2">
                                    <label for="branchFilter" class="form-label">Branch</label>
                                    <select id="daily_branchFilter" data-filter class="form-select search-txt filter_border ">
                                        <option value="">All</option>
                                        @foreach ($branchFilter as $branchF)
                                            <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-2">
                                    <label for="departmentFilter" class="form-label">Department</label>
                                    <select id="daily_departmentFilter" data-filter class="form-select search-txt filter_border">
                                        <option value="">All</option>
                                        @foreach ($departmentFilter as $departmentF)
                                            <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-2">
                                    <label for="designationFilter" class="form-label">Designation</label>
                                    <select id="daily_designationFilter" data-filter class="form-select search-txt filter_border">
                                        <option value="">All</option>
                                        @foreach ($designationFilter as $designationF)
                                            <option value="{{ $designationF->dg_id }}">
                                                {{ $designationF->dg_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-2">
                                    <label for="activeFilter" class="form-label">Status</label>
                                    <select id="daily_activeFilter" data-filter class="form-select search-txt filter_border">
                                        <option value="">All</option>
                                        <option value="71">Active</option>
                                        <option value="72">Inactive</option>
                                    </select>
                                </div>

                                {{-- <div class="col-sm-2">
                                    <label for="toDate" class="form-label">Date</label>
                                    <input type="date" id="fromDate" value="{{ old('fromDate', date('Y-m-d')) }}" name="fromDate"
                                        class="form-control filter_border" data-date-filter="from-date" />
                                </div> --}}

                                   <div class="col-md">
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
                        </div>

                        <div class="table-responsive">
                            <table class="table display table-vcenter table-hover text-wrap border-bottom"
                                id="overtime-approval-table-dynamic">
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
    </div>

    <!-- ✅ Approval Modal -->
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
                    <button type="button" class="btn btn-outline-primary" id="confirmApproval">Confirm</button>
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
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
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
</script>
<script>
    var otIDs = [];
    var allSelected = false;

    // // function selectAllCheckboxes(el) {
    // //     allSelected = $(el).is(':checked');
    // //     let table = $("#overtime-approval-table-dynamic").DataTable();
        
    // //     // Get all checkboxes on current page
    // //     let currentPageCheckboxes = table.rows({ page: 'current' }).nodes().to$()
    // //                                 .find('.employee-checkbox');
        
    // //     // Check / Uncheck visually
    // //     currentPageCheckboxes.prop('checked', allSelected);

    // //     if (allSelected) {
    // //         // Collect all IDs from current page
    // //         otIDs = currentPageCheckboxes.map(function() {
    // //             return $(this).data('id');
    // //         }).get();
    // //     } else {
    // //         otIDs = [];
    // //     }

    // //     console.log("All Selected:", allSelected, "Total IDs:", otIDs.length, otIDs);
    // // }

    // function selectAllCheckboxes(el) {
    //     allSelected = $(el).is(':checked');
    //     let table = $("#overtime-approval-table-dynamic").DataTable();

    //     if (allSelected) {
    //         // Current page ke sab checkboxes check karo
    //         table.rows({ page: 'current' }).nodes().to$()
    //              .find('.employee-checkbox').prop('checked', true);

    //         // Current page ke IDs collect karo
    //         let currentIds = table.rows({ page: 'current' }).nodes().to$()
    //             .find('.employee-checkbox')
    //             .map(function() { return $(this).data('id'); }).get();

    //         // Merge without duplicates
    //         otIDs = [...new Set([...otIDs, ...currentIds])];

    //         console.log("✅ Select All Activated - Total Selected:", otIDs.length);
    //     } else {
    //         otIDs = [];
    //         table.rows({ page: 'current' }).nodes().to$()
    //              .find('.employee-checkbox').prop('checked', false);
    //         console.log("❌ Select All Deactivated");
    //     }
    // }

    // // Important: Maintain checked state on page change
    // $('#overtime-approval-table-dynamic').on('draw.dt', function () {
    //     let table = $("#overtime-approval-table-dynamic").DataTable();

    //     table.rows({ page: 'current' }).nodes().to$().each(function () {
    //         let $checkbox = $(this).find('.employee-checkbox');
    //         let id = $checkbox.data('id');

    //         if (allSelected || (id && otIDs.includes(id))) {
    //             $checkbox.prop('checked', true);
    //         } else {
    //             $checkbox.prop('checked', false);
    //         }
    //     });

    //     // Update Select All checkbox visual state
    //     let totalOnPage = $('.employee-checkbox').length;
    //     let checkedOnPage = $('.employee-checkbox:checked').length;
        
    //     if (allSelected) {
    //         $('#selectAll').prop('checked', true);
    //     } else {
    //         $('#selectAll').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
    //     }
    // });

    // function selectCheckbox(checkbox) {
    //     var id = $(checkbox).data('id');
    //     var checked = $(checkbox).is(':checked');

    //     if (checked) {
    //         if (!otIDs.includes(id)) otIDs.push(id);
    //     } else {
    //         otIDs = otIDs.filter(x => x !== id);
    //     }

    //     // Update select all checkbox
    //     $('#selectAll').prop(
    //         'checked',
    //         $('.employee-checkbox').length === $('.employee-checkbox:checked').length
    //     );

    //     console.log("Selected IDs:", otIDs);
    // }

    $(document).ready(function() {
        let approvalType = ""; // Store action type (Approve/Reject)
        let logStatus;
        let masterApproveBtnGlobal = @json($masterApproveBtn ?? null);
        // ✅ Open Modal on Approve/Reject Click
        // $("#approveBtn, #rejectBtn").click(function() {
        //     let isApprove = $(this).attr('id') === "approveBtn";
        //     approvalType = $(this).attr('value');
        //     actionType = isApprove ? "Approve" : "Reject";
        //     logStatus = isApprove ? 157 : 170;
        //     $("#approvalMessage").text(
        //         `Are you sure you want to ${actionType.toLowerCase()} this request?`);
        //     $("#approvalModal").modal("show");
        // });

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

        // ✅ Confirm Button Click
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

            // if (otIDs.length === 0) { //3.
            if (!allSelected && otIDs.length === 0) {
                Swal.fire({
                    icon: "warning",
                    text: "No attendance record selected for approval.",
                    timer: 3000,
                });
                $("#confirmApproval").attr("disabled", false);
                return false;
            }

            // Get DataTable instance
            let table = $("#overtime-approval-table-dynamic").DataTable();
            let settings = table.settings()[0];
            let apHiBtnData = settings.json?.apHiBtn || {};
            let canApprove = settings.json?.canApprove;
            let moduleId = settings.json?.moduleId;

            if (typeof apHiBtnData !== "undefined" && Object.keys(apHiBtnData).length > 0) {
                // Create an array of objects for each leave ID
                let formattedData = otIDs.map(otID => ({
                    approval_status: apHiBtnData.approval_status,
                    approval_type: approvalType,
                    approval_action_type: apHiBtnData.approval_action_type,
                    approval_sequence: apHiBtnData.approval_sequence,
                    ot_id: otID,
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
                        POST_TYPE: 'OVERTIME_REQUEST_APPROVAL',
                        data: formattedData,//4.
                        allSelected: allSelected,
                        filters: table.ajax.params()
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
                let formattedData = otIDs.map(otID => ({
                    log_module_id: moduleId.module_id,
                    log_request_id: otID,
                    log_description: remark,
                    log_status: logStatus,
                    deduction_amount: 0,
                    prefix: 'ot_',
                    model: 'OtApprovalStatus'
                }));

                // Send AJAX request for approval/rejection
                $.ajax({
                    url: "{{ route('admin.common-bulk-approval') }}",
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        data: formattedData,//5.
                        allSelected: allSelected,
                        filters: table.ajax.params()
                    },
                    beforeSend: function() {
                        $("#confirmApproval").attr("disabled", true);
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: "success",
                                text: response.message,
                                timer: 3000,
                                didClose: () => location.reload()
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                text: response.message || "Something went wrong!",
                            });
                        }
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
</script>
