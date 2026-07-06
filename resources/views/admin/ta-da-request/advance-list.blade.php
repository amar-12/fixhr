<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Models\MasterTable;
use App\Models\TadaRequestPlan;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$travelTypeFilter = CommonUtils::getCustomModelData(new MasterTable(), [['method' => 'where', 'args' => ['m_group', 'TRAVEL_TYPE']]]);
$planUniqueIdFilter = CommonUtils::getCustomModelData(new TadaRequestPlan(), [['method' => 'where', 'args' => ['trp_b_id', $user->emp_b_id]]]);
$empNameFilter = CommonUtils::getCustomModelData(new Employee(), [['method' => 'where', 'args' => ['emp_b_id', $user->emp_b_id]]]);

?>
@extends('admin.layout.master')

@section('title', 'Travel Advance List')

@section('content')
    <style>
        .is-invalid {
            border: 1px solid red;
        }

        .text-danger {
            color: red;
        }

        /*#employee-table-dynamic tbody tr:hover {
            background-color: rgb(236, 236, 236);
            transition: background-color 0.2s ease-in-out;
            cursor: pointer;
        }*/
    </style>


    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/admin') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/tada-settings') }}">Travel Management</a></li>
                    <li class="active"><span><b>Advance Approval</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <div class="btn-group">
                                    {{-- href="{{ url('download-adv-payment-sheet') }}" --}}
                                    <a class="btn btn-outline-primary" onclick="downloadPaymentSheet()"><i
                                            class="fa fa-download"></i> Payment Sheet</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bradcrumbs End --}}



    <!-- ROW -->
    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header px-4">
                    <h4 class="card-title">Advance</h4>
                </div>
                @error('travel_type_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
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





                        <div class="row ">
                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="row">
                                    <div class="col-md">
                                        <label for="travelTypeFilter" class="form-label">Travel Type</label>
                                        <select id="advance_travelTypeFilter" data-filter class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                                         @foreach ($travelTypeFilter as $ttF)
                                                            <option value="{{ $ttF->m_id }}">{{ $ttF->m_name }}</option>
                                                        @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="planUniqueIdFilter" class="form-label">Plan Unique Id</label>
                                        <select id="advance_planUniqueIdFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                               @foreach ($planUniqueIdFilter as $pIF)
                                                    <option value="{{ $pIF->trp_id }}">{{ $pIF->trp_unique_id }}</option>
                                                @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="empNameFilter" class="form-label">Employee Name</label>
                                        <select id="advance_empNameFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                           @foreach ($empNameFilter as $empNF)
                                                <option value="{{ $empNF->emp_id }}">{{ $empNF->emp_full_name }}
                                                    {{ $empNF->emp_code ? '(' . $empNF->emp_code . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md">
                                        <label for="sheetStatusFilter" class="form-label">Sheet Status</label>
                                        <select id="advance_sheetStatusFilter" data-filter
                                            class="form-select search-txt filter_border">
                                            <option value="">All</option>
                                           <option value="0">Pending</option>
                                           <option value="1">Processed</option>

                                        </select>
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

                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="employee-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size:13px; width: {{ $column['width'] }}">
                                            {{ $column['name'] }}
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

    <div class="modal fade" id="advanceModal" tabindex="-1" role="dialog" aria-labelledby="advanceModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0 d-flex justify-content-between align-items-center">
                    <h4 class="modal-title" id="modalTitle">Advance Requested</h4>
                    <div class="d-flex align-items-center">
                        <strong class="text-white mb-0 me-5" id="emp_tada_settlement_amt"></strong>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive" id="advanceModalBody">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .swal2-container-green-glow {
            box-shadow: 0 0 10px rgba(0, 128, 0, 0.5);
            /* green glow */
            border: 1px solid #008000;
            /* green border */
        }

        .swal2-toast {
            box-shadow: 0 0 10px rgba(0, 128, 0, 0.5);
            /* green glow */
            border: 1px solid #008000;
            /* green border */
        }

        .swal2-toast-green-glow {
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.8), 0 0 25px rgba(0, 255, 0, 0.5);
            /* Neon green glow */
            border-radius: 5px;
            border: 2px solid #00ff00;
            /* Bright neon green border */
            background-color: #004d00;
            /* Dark green background for contrast */
        }
    </style>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "employee-table-dynamic",
                url: "{{ route('advance.request.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                 dataStateSave: false,
                drawCallback: function(settings) {
                    // Destroy existing popovers (if any)
                    $('[data-bs-toggle="popover"]').popover('dispose');

                    // Re-initialize popovers after each draw
                    $('[data-bs-toggle="popover"]').popover({
                        trigger: 'hover' // Example option, adjust as needed
                    });
                }            });
        });

        function openViewModel(btn) {
            let id = $(btn).data('adl_trp_id');
            let is_claimed = $(btn).data('is_claimed') == 1;
            let is_paid = false;
            var url = "{{ route('advance.request.edit', ':id') }}".replace(':id', btoa(id));
            var emp_tada_settlement_amt = '';
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    var tableHtml = `
                        <div class="table-responsive">
                            <table class="table table-hover card-table table-vcenter text-nowrap mb-0">
                                <thead >
                                    <tr>
                                        <th>Advance ID</th>
                                        <th>Submitted To</th>
                                        <th>Status</th>
                                        <th>Requested Amount</th>
                                        <th>Emp Remark</th>
                                        <th>Amount</th>
                                        <th>Remark</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    const firstRecord = response[0];

                    if (firstRecord.emp_tada_settlement_amt != null && firstRecord.emp_tada_settlement_amt !==
                        0) {
                        emp_tada_settlement_amt = 'Pending Advance Settlement ' + firstRecord
                            .emp_tada_settlement_amt + " ₹ /-";
                    }
                    $('#emp_tada_settlement_amt').text(emp_tada_settlement_amt);
                    $.each(response, function(key, value) {
                        // Create table row

                        tableHtml += '<tr data-id="' + value.adl_id +
                            '" data-approval_status="' + value.approval_status +
                            '" data-approval_type="' + value.approval_type +
                            '" data-approval_action_type="' + value.approval_action_type +
                            '" data-approval_sequence="' + value.approval_sequence +
                            '" data-advance_id="' + value.advance_id +
                            '" data-module_id="' + value.module_id +
                            '" data-is_last_approval="' + value.is_last_approval +
                            '" data-rt="' + value.route +
                            '" data-message="' + value.message + '">' +
                            '<th>#' + value.adl_id + '</th>' +
                            '<td>' + value.approver_name + '</td>' +
                            '<td>' + value.approval_status_check + '</td>' +
                            '<td>' + value.adl_requested_amount + ' ₹ /-</td>' +
                            '<td>' + value.adl_remark + '</td>';


                        // Reimburse amount cell
                        tableHtml += '<td>';
                        tableHtml +=
                            '<input type="number" class="form-control form-control-sm amount-input" value="' +
                            value.adl_requested_amount + '" data-requested_amount="' + value
                            .adl_requested_amount + '" min="0" ' + (is_claimed ? 'disabled' : '') + (value.adl_stage_completed ? 'readonly' : '') +
                            '/>';
                        tableHtml += '</td>';

                        tableHtml += '<td>';
                            tableHtml +=
                                '<input type="text" class="form-control form-control-sm remark-input" maxlength="200"   placeholder="Remark"'+
                                (is_claimed ? 'disabled' : '') + (value.adl_stage_completed ? 'readonly' : '') + '/>';

                        tableHtml += '</td>';

                        // Approval status cell
                        tableHtml += '<td>';

                        if (value.approvalData || value.canApprove) {
                            if(value.approvalData){
                                tableHtml +=

                                '<span class="approve-btn-section">' +
                                '<button data-d="0" data-action-label="Rejected" class="btn btn-sm btn-secondary approve-btn me-2" ' +
                                (is_claimed ? 'disabled' : '') + '>Rejected</button>' +
                                '<button data-d="1" data-action-label="' + value.approvalData.fh_approver_status.m_name + '" class="btn btn-sm btn-primary approve-btn" ' +
                                (is_claimed ? 'disabled' : '') + '>' + value.approvalData.fh_approver_status.m_name + '</button>' +
                                '</span>';
                            } else {
                                let buttonsHtml = '';
                                value.masterApproveBtn?.forEach((btn, index) => {

                                    const btnClass = btn?.m_name != 'Rejected' ? 'btn-secondary me-2' : 'btn-danger';
                                    const btnLabel = btn?.m_name != 'Rejected' ? btn?.m_name : 'Rejected';
                                    const dataD = btn?.m_name != 'Rejected' ? '1' : '0';

                                    buttonsHtml += `
                                        <button data-d="${dataD}" data-action-label="${btnLabel}" class="btn btn-sm ${btnClass} approve-btn" ${is_claimed ? 'disabled' : ''}>
                                            ${btnLabel}
                                        </button>
                                    `;
                                });

                                tableHtml += `
                                    <span class="approve-btn-section">
                                        ${buttonsHtml}
                                    </span>
                                `;
                            }
                        } else {
                             tableHtml +=
                                 '<span class="text-warning"><i class="fa fa-clock">&nbsp;</i>' + value.approval_status_check + '</span>';
                             is_paid = false;
                         }

                        tableHtml += '</td>';
                        tableHtml += '<input type="hidden" id="route" value="' + value.route + '">';
                        // Close the row
                        tableHtml += '</tr>';
                    });

                    tableHtml += `</tbody>
                                    </table>
                                </div>`; // remove the "var" keyword

                    // Claimed or not cell
                    tableHtml += '<div>';
                    if (is_claimed && !is_paid) {
                        tableHtml +=
                            '<span class="text-danger">*Claim has already been approved; therefore advance approval cannot be processed at this time.</span>';
                    }
                    tableHtml += '</div>';

                    $('#advanceModalBody').html(
                        tableHtml); // assume #advanceModalBody is the ID of the modal body
                    $('#advanceModal').modal('show');
                }
            });
        }
        $(document).on('click', '.openBtn', function() {
            $('#advanceModal').modal('show');
        });

        $(document).ready(function() {
            // Handle the Approved button click
            $(document).on('click', '.approve-btn', function() {
                let $button = $(this);
                let approvalType = $button.data('d');
                // let approvalType2 = $button.data('dlab');
                let approvalType2 = $button.data('action-label');
                let actionText = approvalType == 1 ? 'Approve' : 'Reject';


                Swal.fire({
                    title: `Are you sure?`,
                    text: `Do you want to ${approvalType2} this request?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: `Yes, ${approvalType2}`,
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let row = $button.closest('tr');
                        let id = row.data('id');
                        let approval_status = row.data('approval_status');

                        // Find the input within the same row
                        let input = row.find('.amount-input');
                        let remarkInput = row.find('.remark-input');
                        let reimburseAmount = input.val();
                        let remarkValue = remarkInput.val();
                        let isValid = true;
                        let requstedAmount = input.data('requested_amount');

                        // Validate remark input
                        if (remarkValue === '' || remarkValue == null) {
                            isValid = false;
                            remarkInput.addClass('is-invalid');
                            row.find('.remarkInput-error').remove();
                            remarkInput.after(
                                '<div class="text-danger remarkInput-error">Please enter a remark.</div>'
                            );
                            remarkInput.focus();
                        } else {
                            remarkInput.removeClass('is-invalid');
                            row.find('.remarkInput-error').remove();
                        }

                        // Validate reimbursement amount
                        if (isNaN(reimburseAmount) || reimburseAmount < 0 || reimburseAmount ===
                            '') {
                            isValid = false;
                            input.addClass('is-invalid');
                            row.find('.input-error').remove();
                            input.after(
                                '<div class="text-danger input-error">Please enter a valid amount.</div>'
                            );
                            input.focus();
                        } else if (requstedAmount < reimburseAmount) {
                            isValid = false;
                            input.addClass('is-invalid');
                            row.find('.input-error').remove();
                            input.after(
                                '<div class="text-danger input-error">Amount not be greater than requested amount.</div>'
                            );
                            input.focus();
                        } else {
                            input.removeClass('is-invalid');
                            row.find('.input-error').remove();
                        }

                        if (isValid) {
                            let data = {
                                adl_id: id,
                                approval_status: row.data('approval_status'),
                                approval_type: approvalType,
                                approval_action_type: row.data('approval_action_type'),
                                approval_sequence: row.data('approval_sequence'),
                                advance_id: row.data('advance_id'),
                                module_id: row.data('module_id'),
                                is_last_approval: row.data('is_last_approval'),
                                message: remarkValue,
                                reimburse_amount: reimburseAmount,
                            };

                            $button.prop('disabled', true);
                            let url = row.data('rt');

                            $.ajax({
                                url: url == "approve.advance" ?
                                    "{{ route('approve.advance') }}" :
                                    "{{ route('admin.approval-handler') }}",
                                type: 'POST',
                                data: {
                                    adl_id: id,
                                    POST_TYPE: 'ADVANCE_REQUEST_APPROVAL',
                                    reimburse_amount: reimburseAmount,
                                    data: data,
                                    api: 0,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    $button.prop('disabled', false);
                                    $('#advanceModal').modal('hide');
                                    if (response.status == true) {
                                        Swal.fire({
                                            position: 'top-end',
                                            icon: 'success',
                                            title: 'Your advance has been ' + (approvalType2) +
                                                ' successfully!',
                                            toast: true,
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                            customClass: {
                                                toast: 'swal2-toast-green-glow'
                                            },
                                            didOpen: (toast) => {
                                                toast.addEventListener(
                                                    'mouseenter', Swal
                                                    .stopTimer);
                                                toast.addEventListener(
                                                    'mouseleave', Swal
                                                    .resumeTimer);
                                            }
                                        }).then(() => {
                                            location.reload();
                                        });

                                        row.find('.approve-btn-section').replaceWith(
                                            '<span class="text-success"><i class="fa fa-check">&nbsp;</i>Paid</span>'
                                        );
                                        input.replaceWith(reimburseAmount + ' ₹ /-');
                                        remarkInput.replaceWith(remarkValue);
                                    } else {
                                        Swal.fire({
                                            position: 'top-end',
                                            icon: 'error',
                                            title: 'Your advance was not reimbursed!',
                                            toast: true,
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                            customClass: {
                                                toast: 'swal2-toast-green-glow'
                                            },
                                            didOpen: (toast) => {
                                                toast.addEventListener(
                                                    'mouseenter', Swal
                                                    .stopTimer);
                                                toast.addEventListener(
                                                    'mouseleave', Swal
                                                    .resumeTimer);
                                            }
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    $button.prop('disabled', false);
                                    alert('Something went wrong. Please try again.');
                                }
                            });
                        }
                    }
                }); // end of Swal.then
            });

            // Dynamically remove error when the input becomes valid
            $(document).on('input', '.amount-input', function() {
                let input = $(this);
                let reimburseAmount = parseFloat(input.val());

                if (!isNaN(reimburseAmount) && reimburseAmount > 0) {
                    input.removeClass('is-invalid');
                    input.closest('tr').find('.input-error').remove();
                }
            });
        });

        function downloadPaymentSheet() {
            const travelType = document.getElementById('travelTypeFilter').value;
            const planUniqueId = document.getElementById('planUniqueIdFilter').value;
            const empName = document.getElementById('empNameFilter').value;
            const status = document.getElementById('sheetStatusFilter').value;

            const url = `{{ url('download-adv-payment-sheet') }}?` + new URLSearchParams({
                travel_type: travelType,
                plan_unique_id: planUniqueId,
                employee_id: empName,
                status: status
            }).toString();

            // Redirect to the URL to trigger file download
            window.location.href = url;
        }
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
