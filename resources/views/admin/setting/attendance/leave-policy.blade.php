@extends('admin.layout.master')
@section('title', 'Leave Policy')

@section('css')

@endsection

@section('content')
{{-- Bradcrumbs Start --}}
<div class="p-0 mt-3">
    <div class="row">
        <div class="col-md-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a></li>
                <li class="active"><span><b>Leave Policy</b></span></li>
            </ol>
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-2">
            <div class="page-rightheader ms-md-auto">
                <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                    <div class="d-lg-flex d-block ms-auto">
                        <div class="btn-list">
                            <a type="button" class="btn btn-outline-primary" href="{{ route('leave-policy.create') }}">Add Leave Policy</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Leave Policy</div>
                </div>
                <div class="card-body">
                    <div class="row">

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

                            <div class="col-sm-7">
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


                        <div>
                            <table class="table display table-hover table-vcenter text-wrap border-bottom" id="leave-policy-table-dynamic">
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
                                <div id="custom-show-entries" data-show-entries>
                                </div>
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

    <!-- MODAL -->
    <div class="modal fade" id="leaveTypeModal" tabindex="-1" role="dialog" aria-labelledby="leaveTypeModal" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveTypeModalTitle">Add Leave Type</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="leaveTypePolicyForm"> @csrf
                    <div class="modal-body">
                        <input type="hidden" name="pl_id" id="pl_id">

                        <div class="row mt-2">
                            <div class="col-xl-6 mb-4">
                                <x-input type="text" id="pl_name" name="pl_name" label="Leave Policy Name" placeholder="Leave Policy Name" required/>
                            </div>

                            <div class="col-xl-1 mt-5">
                                <button type="button" class="btn btn-outline-primary mt-1" id="addLeaveCatBtn"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="row">
                            <div id="leaveCategoryRows">
                                <div class="row leave-category-row mb-5">
                                    <div class="col-md-2">
                                        <label class="form-label" for="category_name">Leave Category <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="leave_cycle">Leave Cycle <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label" for="days">Days <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="unused_leave_rule">Unused Leave Rule <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="carry_forward_limit">Carry Forward Limit</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="applicable_to">Applicable To <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-1">
                                        <label>&nbsp;</label>
                                    </div>

                                    <input type="hidden" hidden value="" name="lvt_id">
                                    <div class="col-md-12 mt-5">
                                        <label class="custom-control custom-checkbox d-inline-block me-3">
                                            <input type="checkbox" class="custom-control-input" name="sandwich[]" id="sandwich_0" value="0" onclick="toggleFixedAmount(0)">
                                            <input type="hidden" name="hidden_sandwich[]" id="hidden_sandwich_0" value="0">
                                            <span class="custom-control-label"></span><b>Sandwich Applicable </b><span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="category_name[]" id="category_name_1" class="form-control custom-select select2 categoryName" data-placeholder="Select Category" required>
                                            <option class="text-muted" value="" label="Select Category"></option>
                                            @foreach ($leaveCategory as $item)
                                                <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="leave_cycle[]" id="leave_cycle_1" class="form-control select2" data-placeholder="Select Leave Cycle" required>
                                            <option class="text-muted" value="" label="Select Leave Cycle"></option>
                                            @foreach ($leaveCycle as $item)
                                                <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="number" name="days[]" id="days_1" class="form-control" value="0" min="0" step="any" oninput="dayValidation(this)" required>
                                    </div>
                                    <script>
                                        function dayValidation(data) {
                                            // const id = data.id;
                                            // // Extract the numeric part from the id
                                            // const numberPart = id.match(/\d+/); // This will match one or more digits

                                            // if (numberPart) {
                                            //     const numericValue = numberPart[0]; // Get the first match
                                            //     const a = $('#category_name_' + numericValue).val();
                                            //     const b = $('#leave_cycle_' + numericValue).val();
                                            //     console.log(numericValue, a, b); // This will log "1" for "days_1"
                                            // } else {
                                            //     console.log("No numeric part found in the ID.");
                                            // }

                                        }
                                    </script>
                                    <div class="col-md-2">
                                        <select name="unused_leave_rule[]" class="form-control select2" data-placeholder="Select Unused Leave Rule" required>
                                            <option class="text-muted" value="" label="Select Unused Leave Rule"></option>
                                            @foreach ($leaveUnused as $item)
                                                <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="carry_forward_limit[]" class="form-control" value="0" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="applicable_to[]" class="form-control select2" data-placeholder="Select Applicable To" required>
                                            <option class="text-muted" value="" label="Select Applicable To"></option>
                                            @foreach ($leaveApplicable as $item)
                                                <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <!-- END MODAL -->
@endsection

@section('script')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "leave-policy-table-dynamic",
                url: "{{ route('leave-policy.index') }}",
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
    </script>

    <script>

        $(function() {
            // Handle Delete Leave Policy
            $(document).on('click', '.delete-leave-policy', handleDeleteLeavePolicy);
        });

        $(document).ready(function() {
            // CSRF Token Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });


        //for delete leave policy
        function handleDeleteLeavePolicy() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this leave policy!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Generate the correct URL dynamically
                    var url = "{{ route('leave-policy.destroy', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        method: "POST",
                        data: {
                            _method: 'DELETE',  // Add this field to spoof the DELETE method
                            _token: $('meta[name="csrf-token"]').attr('content')  // CSRF token
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.success,
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                didClose: () => location.reload()
                            });
                        },
                        error: function(xhr) {
                            var errorMessage = 'This policy is associated with employees and cannot be deleted.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }

                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }

    </script>
@endsection
