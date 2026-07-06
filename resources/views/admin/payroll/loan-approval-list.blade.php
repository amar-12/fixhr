<?php
use App\Models\MasterTable;

$masterTable = new MasterTable();
?>
<!-- View file content -->
@extends('admin.layout.master')

@section('title', 'Loan Approval')

@section('content')
    <style>
        .is-invalid {
            border: 1px solid red;
        }
        .text-danger {
            color: red;
        }
    </style>

    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a class="text-white">Requests</a></li>
                <li class="active"><span><b>Loan/Advance Approval</b></span></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Loan/Advance Approval </h4>
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

                            <div class="row">
                                <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label for="loanTypeFilter" class="form-label">Loan/Advance Type</label>
                                            <select id="loanTypeFilter" data-filter
                                                class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach (MasterTable::where('m_type', 'loan_type')->get() as $type)
                                                    <option value="{{ $type->m_id }}">{{ $type->m_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-3">
                                            <label for="designationFilter" class="form-label">Employee Name</label>
                                            <select id="empNameFilter" data-filter
                                                class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($empNameFilter as $emp)
                                                    <option value="{{ $emp->emp_id }}">{{ $emp->emp_full_name }}
                                                        {{ $emp->emp_code ? '(' . $emp->emp_code . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-3">
                                            <label for="statusFilter" class="form-label">Status</label>
                                            <select id="statusFilter" data-filter
                                                class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                <option value="pending">Pending</option>
                                                <option value="active">Active</option>
                                                <option value="settled">Completed</option>
                                            </select>
                                        </div>

                                                 <div class="col-sm-2">
                                            <label for="toDate" class="form-label">Month</label>
                                            <input type="month" data-filter id="mt_monthFilter" name="monthFilter"
                                                value="{{ now()->format('Y-m') }}" placeholder="To Date"
                                                class="form-control filter_border" />
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
                            <table class="table display table-hover table-vcenter text-wrap border-bottom"
                                id="loan-accounts-table">
                                <thead>
                                    <tr>
                                        @foreach ($columns as $column)
                                            <th style="font-size: 13px">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
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

    <!-- Loan Details Modal -->
    <div class="modal fade" id="loanModal" tabindex="-1" role="dialog" aria-labelledby="loanModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0 d-flex justify-content-between align-items-center">
                    <h4 class="modal-title" id="modalTitle">Loan Details</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="loanModalBody"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary approve-loan-btn">Approve</button>
                    <button type="button" class="btn btn-outline-danger  reject-loan-btn">Reject</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "loan-accounts-table",
                url: "{{ route('loan.approval.list') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            // Handle view loan button click
            $(document).on('click', '.view-loan-btn', function() {
                let loanId = $(this).data('id');
                $('#loanModal').data('loan-id', loanId);

                $.ajax({
                    url: "{{ route('loan.details', '') }}/" + loanId,
                    type: 'GET',
                    success: function(response) {
                        $('#loanModalBody').html(response);
                        $('#loanModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to load loan details', 'error');
                    }
                });
            });

            // Handle loan approval
            $(document).on('click', '.approve-loan-btn', function () {
                let loanId = $('#loanModal').data('loan-id');

                // Get the total loan amount dynamically from the DOM
                // This avoids the PHP variable hardcoding issue
                const totalLoan = parseFloat($('#total-loan-amount').text().replace(/[^0-9.]/g, '')) || 0;
                const lnr_b_id = $('#lnr_b_id').val();

                // Collect form data
                let formData = {
                    _token: '{{ csrf_token() }}',
                    status: 'approved',
                    rate: $('#rate').length ? $('#rate').val() : null,
                    lnr_b_id: lnr_b_id,
                    installments: []
                };

                // Collect installment amounts and remaining balances
                let runningBalance = totalLoan;
                $('.installmentAmountValue').each(function (index) {
                    const amount = parseFloat($(this).val()) || 0;
                    const remainingBalance = parseFloat(
                        $(this).closest('tr').find('.remainingBalance').text()
                        .replace(/[^0-9.]/g, '')
                    ) || 0;

                    // Validate calculation consistency
                    // const calculatedBalance = runningBalance - amount;
                    // if (Math.abs(remainingBalance - calculatedBalance) > 0.01) {
                    //     Swal.fire('Error', `Balance mismatch in installment ${index + 1}`, 'error');
                    //     return false;
                    // }

                    formData.installments.push({
                        installment_number: index + 1,
                        amount: amount.toFixed(2),
                        remaining_balance: remainingBalance.toFixed(2)
                    });

                    runningBalance = remainingBalance;
                });

                // Get installment count dynamically
                const installmentCount = $('.installmentAmountValue').length;

                // If validation failed
                if (formData.installments.length !== installmentCount) return;

                Swal.fire({
                    title: 'Approve Loan',
                    text: 'Are you sure you want to approve this loan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    /* if (result.isConfirmed) {
                         $.ajax({
                             url: "{{ route('save.loan.details', '') }}/" + loanId,
                             type: 'POST',
                             data: formData,
                             success: function (detailsResponse) {
                                 if (detailsResponse.success) {
                                     $.ajax({
                                         url: "{{ route('loan.approve', '') }}/" + loanId,
                                         type: 'POST',
                                         data: { _token: '{{ csrf_token() }}' },
                                         success: function (response) {
                                             if (response.success) {
                                                 Swal.fire('Approved', response.message, 'success');
                                                 $('#loanModal').modal('hide');
                                                 location.reload();
                                             } else {
                                                 Swal.fire('Error', response.message, 'error');
                                             }
                                         },
                                         error: function (xhr) {
                                             Swal.fire('Error', 'Approval failed', 'error');
                                         }
                                     });
                                 } else {
                                     Swal.fire('Error', detailsResponse.message, 'error');
                                 }
                             },
                             error: function (xhr) {
                                 Swal.fire('Error', 'Failed to save details', 'error');
                             }
                         });
                     }  */

                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('save.loan.details', '') }}/" + loanId,
                            type: 'POST',
                            data: formData,
                            success: function (detailsResponse) {
                                if (detailsResponse.success === true) {
                                    $.ajax({
                                        url: "{{ route('loan.approve', '') }}/" + loanId,
                                        type: 'POST',
                                        data: { _token: '{{ csrf_token() }}' },
                                        success: function (response) {
                                            if (response.success === true) {
                                                Swal.fire({
                                                    title: 'Approved',
                                                    text: response.message,
                                                    icon: 'success'
                                                });
                                                $('#loanModal').modal('hide');
                                                location.reload();
                                            } else {
                                                Swal.fire({
                                                    title: 'Error',
                                                    text: response.message,
                                                    icon: 'error'
                                                });
                                            }
                                        },
                                        error: function () {
                                            Swal.fire({
                                                title: 'Error',
                                                text: 'Approval failed',
                                                icon: 'error'
                                            });
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: detailsResponse.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to save details',
                                    icon: 'error'
                                });
                            }
                        });
                    }

                });
            });

            // Handle loan rejection
            $(document).on('click', '.reject-loan-btn', function () {
                let loanId = $('#loanModal').data('loan-id');

                // Close the existing modal first
                $('#loanModal').modal('hide');

                setTimeout(() => {
                    Swal.fire({
                        title: 'Reject Loan',
                        text: 'Are you sure you want to reject this loan?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Reject',
                        cancelButtonText: 'Cancel',
                        input: 'textarea',
                        inputPlaceholder: 'Enter rejection reason',
                        inputAttributes: {
                            'aria-label': 'Enter rejection reason',
                            'rows': 4,
                            'class': 'swal2-textarea',
                            'style': 'resize: vertical; min-height: 100px; opacity: 1 !important;'
                        },
                        showLoaderOnConfirm: true,
                        preConfirm: (reason) => {
                            if (!reason) {
                                Swal.showValidationMessage('You need to enter a rejection reason!');
                                return false;
                            }
                            return reason;
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            $.ajax({
                                url: "{{ route('loan.reject', '') }}/" + loanId,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    reason: result.value
                                },
                                success: function (response) {
                                    if (response.success) {
                                        Swal.fire('Rejected', response.message, 'success');
                                        location.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function (xhr) {
                                    Swal.fire('Error', 'Failed to reject loan', 'error');
                                }
                            });
                        }
                    });
                }, 300); // Short delay to ensure previous modal is fully closed
            });
        });
    </script>
@endsection
