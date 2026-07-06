@extends('admin.layout.master')
@section('title', 'Financial Year')
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

    {{-- Breadcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/financial-year') }}">Financial Year Settings</a></li>
                    <li class="active"><span><b>Financial Year</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addFinancialYearBtn">Add Financial
                                    Year</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breadcrumbs End --}}

    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header border-0">
                    <h4 class="card-title">Financial & Calender Year</h4>
                </div>

                <div class="card-body">
                    @csrf
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

                        </div>

                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="payroll-policy-table-dynamic">
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


    <!-- Modal for Financial Year -->
    <div class="modal fade" id="financialYearModal" tabindex="-1" role="dialog" aria-labelledby="financialYearModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="financialYearModalTitle">Add Financial Year</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <form id="financialYearForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="fy_id" id="fy_id">
                        <div class="row">
                           <x-input type="text" id="fy_year" label="Financial Year" astric="*" required
                                pattern="^[0-9]{4}-[0-9]{4}$" title="2024-2025" name="fy_year"
                                placeholder="Financial Year" />
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-input type="date" id="fy_start_date" label="Start Date" astric="*"
                                    name="fy_start_date" required />
                            </div>
                            <div class="col-md-6">
                                <x-input type="date" id="fy_end_date" label="End Date" astric="*"
                                    name="fy_end_date" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" value="1" id="fy_is_current"
                                    name="fy_is_current">
                                <label class="form-check-label" for="fy_is_current">
                                    Is Current Financial Year
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">

                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Setup CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Init DataTable (adjust if needed)
            datatable({
                tableId: "payroll-policy-table-dynamic",
                url: "{{ route('financial-year.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            // Auto-fill start and end dates from fy_year
            $('#fy_year').on('input', function() {
                const value = this.value.trim();
                const parts = value.split('-');
                if (parts.length === 2) {
                    const startYear = parseInt(parts[0]);
                    const endYear = parseInt(parts[1]);
                    if (!isNaN(startYear) && !isNaN(endYear)) {
                        $('#fy_start_date').val(`${startYear}-04-01`);
                        $('#fy_end_date').val(`${endYear}-03-31`);
                    }
                }
            });

            // Open modal for Add
            $('#addFinancialYearBtn').on('click', function() {
                $('#financialYearForm')[0].reset();
                $('#fy_id').val('');
                $('#fy_year').prop('readonly', false);
                $('#fy_is_current').prop('checked', false);
                $('#financialYearModalTitle').text('Add Financial Year');
                $('#saveBtn').text('Save');
                $('#financialYearModal').modal('show');
            });

            // Submit form
            $('#financialYearForm').on('submit', function(e) {
                e.preventDefault();
                $('#saveBtn').attr('disabled', true);

                $.ajax({
                    url: "{{ route('financial-year.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#saveBtn').attr('disabled', false);
                        $('#financialYearModal').modal('hide');
                        Swal.fire('Success!', response.success, 'success');
                        $('#payroll-policy-table-dynamic').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        $('#saveBtn').attr('disabled', false);
                        let errors = xhr.responseJSON.errors;
                        let messages = '';
                        $.each(errors, function(key, value) {
                            messages += `<p>${value[0]}</p>`;
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: messages
                        });
                    }
                });
            });

            // Edit financial year
            $(document).on('click', '.edit-financial-year', function() {
                const data = $(this).data();

                $('#fy_id').val(data.id);
                $('#fy_year').val(data.year).prop('readonly', true);
                $('#fy_start_date').val(data.start_date);
                $('#fy_end_date').val(data.end_date);
                $('#fy_is_current').prop('checked', data.is_current == 1);

                $('#financialYearModalTitle').text('Update Financial Year');
                $('#saveBtn').text('Update');
                $('#financialYearModal').modal('show');
            });

            // Delete financial year
            $(document).on('click', '.delete-financial-year', function() {
                let id = $(this).attr('data-id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will not be able to recover this financial year!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('financial-year.destroy', ':id') }}".replace(':id', id);
                        $.ajax({
                            url: url,
                            method: 'DELETE',
                            success: function(response) {
                                Swal.fire('Deleted!', response.success, 'success');
                                $('#payroll-policy-table-dynamic').DataTable().ajax
                                    .reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

@endsection
