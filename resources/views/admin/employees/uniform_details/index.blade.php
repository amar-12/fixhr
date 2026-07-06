@extends('admin.layout.master')
@section('title', 'Uniform Details')
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

        .form-control {
            font-size: 12px;

        }


        #colorBox {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            margin-top: 10px;
        }
    </style>
    <style>

    </style>
@endsection
@section('content')
    {{-- Breadcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="active"><span><b>Uniform Details</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addUniformBtn">Add
                                    New</button>
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
                    <h4 class="card-title">Uniform Details</h4>
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

                        <div class="col-sm-6">
                        </div>
                        <div class="col-sm-1">
                            <button class="custom-button w-100" type="button" onclick="toggleFilters()"
                                style="margin-top: 28px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5">
                                    <line x1="3" y1="8" x2="21" y2="8"
                                        stroke-linecap="round" />
                                    <circle cx="10" cy="8" r="1.5" fill="currentColor" />
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
                                            data-bs-toggle="modal" data-bs-target="#departmentBulkUpload">
                                            <i class="las la-file-upload"></i> Upload File
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                            href="{{ route('uniforms.export') }}">
                                            <i class="las la-file-download"></i> Export Format
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row ">
                            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="row">
                                    <div class="col-md">
                                        <label for="emp_Filter" class="form-label">Employee</label>
                                        <select id="emp_Filter" data-filter class="form-select  filter_border">
                                            <option value="">All</option>
                                            @foreach ($employee_list as $employee)
                                                <option value="{{ $employee->emp_id }}">
                                                    {{ $employee->emp_full_name }} - ({{ $employee->emp_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="col-md">
                                        <label for="ud_status_Filter" class="form-label">Status</label>
                                        <select id="ud_status_Filter" data-filter class="form-select  filter_border">
                                            <option value=""disabled selected hidden>Select Status</option>
                                            <option value="">All</option>
                                            <option value="New">New</option>
                                            <option value="Replace">Replace</option>
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
                                font-size: 12px;
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
                            id="uniform-details-table">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size:12px">{{ $column }}</th>
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


    <!-- resources/views/modals/uniform-detail-modal.blade.php -->

    <!-- Modal for Uniform Details -->
    <div class="modal fade" id="uniformDetailModal" tabindex="-1" role="dialog" aria-labelledby="uniformDetailModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uniformDetailModalTitle">Add Uniform Detail</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <form id="uniformDetailForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ud_id" id="ud_id">
                    <input type="hidden" id="ud_emp_id_check" name="ud_emp_id">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12">
                                <label for="ud_emp_id">Employee <span style="color:red">*</span></label>
                                <select id="ud_emp_id" name="ud_emp_id" class="form-control" required>
                                    <option value="" disabled selected hidden>Search</option>
                                    @foreach ($employee_list as $employee)
                                        <option value="{{ $employee->emp_id }}"
                                            @if (old('ud_emp_id', $selected_emp_id ?? '') == $employee->emp_id) selected @endif>
                                            {{ $employee->emp_full_name }} - ({{ $employee->emp_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('ud_emp_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_uniform_type">Uniform Type <span style="color:red">*</span></label>
                                <select id="ud_uniform_type" name="ud_uniform_type" class="form-control " required>
                                    <option value="" disabled selected hidden>Select</option>
                                    <option value="Summer">Summer</option>
                                    <option value="Winter">Winter</option>
                                    <option value="Safety Gear">Safety Gear</option>
                                    <option value="Formal">Formal</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_uniform_color">Uniform Color</label>
                                <input type="text" id="ud_uniform_color" name="ud_uniform_color"
                                    class="form-control">
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_shirt_size">Shirt Size <span style="color:red">*</span></label>
                                <select id="ud_shirt_size" name="ud_shirt_size" class="form-control " required>
                                    <option value="" disabled selected hidden>Select</option>
                                    <option value="XS">XS</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_shirt_color">Shirt Color</label>
                                <input type="text" id="ud_shirt_color" name="ud_shirt_color" class="form-control">
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_pant_size">Pant Size (in inches)<span style="color:red">*</span></label>
                                <select id="ud_pant_size" name="ud_pant_size" class="form-control " required>
                                    <option value="" disabled selected hidden>Select</option>
                                    <option value="28">28</option>
                                    <option value="30">30</option>
                                    <option value="32">32</option>
                                    <option value="34">34</option>
                                    <option value="36">36</option>
                                    <option value="38">38</option>
                                    <option value="40">40</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_pant_color">Pant Color</label>
                                <input type="text" id="ud_pant_color" name="ud_pant_color" class="form-control">
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_shoe_size">Shoe Size (UK) <span style="color:red">*</span></label>
                                <select id="ud_shoe_size" name="ud_shoe_size" class="form-control " required>
                                    <option value="" disabled selected hidden>Select</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_shoe_color">Shoe Color</label>
                                <input type="text" id="ud_shoe_color" name="ud_shoe_color"
                                    class="form-control form-control-color">
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_headgear_type">Headgear Type <span style="color:red">*</span></label>
                                <select id="ud_headgear_type" name="ud_headgear_type" class="form-control " required>
                                    <option value="" disabled selected hidden>Select</option>
                                    <option value="Cap">Cap</option>
                                    <option value="Helmet">Helmet</option>
                                    <option value="None">None</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_headgear_color">Headgear Color</label>
                                <input type="text" id="ud_headgear_color" name="ud_headgear_color"
                                    class="form-control form-control-color">
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_headgear_size">Headgear Size</label>
                                <select id="ud_headgear_size" name="ud_headgear_size" class="form-control ">
                                    <option value=""disabled selected hidden>Select</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_status">Status <span style="color:red">*</span></label>
                                <select id="ud_status" name="ud_status" class="form-control " required>
                                    <option value=""disabled selected hidden>Select Status</option>
                                    <option value="New">New</option>
                                    <option value="Replace">Replace</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_issued_by">Issued By <span style="color:red">*</span></label>
                                <select name="ud_issued_by" id="ud_issued_by" class="form-control " required>
                                    <option value=""disabled selected hidden>Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->emp_id }}"
                                            @if (old('ud_issued_by', $selected_ud_issued_by ?? '') == $employee->emp_id) selected @endif>
                                            {{ $employee->emp_full_name }} - ({{ $employee->emp_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_issue_date">Issue Date <span style="color:red">*</span></label>
                                <input type="date" id="ud_issue_date" name="ud_issue_date" class="form-control"
                                    required>
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_replacement_due_date">Replacement Due</label>
                                <input type="date" id="ud_replacement_due_date" name="ud_replacement_due_date"
                                    class="form-control">
                            </div>

                            <div class="col-lg-3">
                                <label for="ud_photo_path">Upload Photo</label>

                                <!-- Hidden File Input -->
                                <input type="file" id="ud_photo_path" name="ud_photo_path" class="form-control">

                                <!-- Pencil Icon Button -->
                                {{-- <button type="button" onclick="document.getElementById('ud_photo_path').click()"
                                    class="btn btn-outline-primary">
                                    <i class="fa fa-pencil"></i> Upload
                                </button> --}}
                            </div>

                            <div class="col-md-12">
                                <label for="ud_remarks">Remarks</label>
                                <textarea id="ud_remarks" name="ud_remarks" class="form-control" rows="2"
                                    placeholder="Any issues like Damaged, Not Returned, etc."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="saveUniformBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="departmentBulkUpload" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Department</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('uniforms.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Upload File :</label>
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
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
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
                tableId: "uniform-details-table",
                url: "{{ route('uniforms.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            // Open modal to add
            $('#addUniformBtn').on('click', function() {
                $('#uniformDetailForm')[0].reset();
                $('#ud_id').val('');
                $('#ud_emp_id').prop('disabled', false);
                $('#ud_issued_by').prop('disabled', false);
                $('#uniformDetailModalTitle').text('Add Uniform Detail');
                $('#saveUniformBtn').text('Save');
                $('#uniformDetailModal').modal('show');
            });

            // Submit form (Add / Update)
            $('#uniformDetailForm').on('submit', function(e) {
                e.preventDefault();
                $('#saveUniformBtn').attr('disabled', true);

                const id = $('#ud_id').val();
                const url = "{{ route('uniforms.storeOrUpdate', ':id') }}".replace(':id', id || '');

                // ✅ Define formData properly
                const formData = new FormData(this);

                $.ajax({
                    url: url,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#saveUniformBtn').attr('disabled', false);
                        $('#uniformDetailModal').modal('hide');
                        Swal.fire('Success!', response.success, 'success');
                        $('#uniform-details-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        $('#saveUniformBtn').attr('disabled', false);
                        let messages = '';
                        $.each(xhr.responseJSON.errors, function(key, value) {
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


            // Edit
            $(document).on('click', '.edit-uniform', function() {
                const data = $(this).data();

                $('#uniformDetailForm')[0].reset();
                $('#existingPhoto').hide(); // Hide previous image

                // Fill form fields
                $('#ud_id').val(data.id);
                $('#ud_emp_id_check').val(data.emp_id);
                $('#ud_emp_id').val(data.emp_id).prop('disabled', true);
                $('#ud_b_id').val(data.b_id);
                $('#ud_uniform_type').val(data.uniform_type);
                $('#ud_shirt_size').val(data.shirt_size);
                $('#ud_pant_size').val(data.pant_size);
                $('#ud_shoe_size').val(data.shoe_size);
                $('#ud_headgear_type').val(data.headgear_type);
                $('#ud_headgear_size').val(data.headgear_size);
                $('#ud_issue_date').val(data.issue_date);
                $('#ud_replacement_due_date').val(data.replacement_due_date);
                // $('#ud_issued_by').val(data.issued_by);
                $('#ud_issued_by').val(data.emp_id).prop('disabled', true);
                $('#ud_status').val(data.status);
                $('#ud_remarks').val(data.remarks);
                $('#ud_status').val(data.ud_status);
                $('#ud_uniform_color').val(data.ud_uniform);
                $('#ud_shirt_color').val(data.ud_shirt);
                $('#ud_pant_color').val(data.ud_pant);
                $('#ud_shoe_color').val(data.ud_shoe);
                $('#ud_headgear_color').val(data.ud_headgear);

                // Show existing image if present
                if (data.photo) {
                    $('#existingPhoto')
                        .attr('src', data.photo)
                        .show();
                }

                $('#uniformDetailModalTitle').text('Edit Uniform Detail');
                $('#saveUniformBtn').text('Update');
                $('#uniformDetailModal').modal('show');
            });

            // delete
            $(document).on('click', '.delete-uniform', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This document will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = "{{ route('uniforms.destroy', ':id') }}".replace(':id', id);
                        $.ajax({
                            url: url,
                            method: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            },
                            error: function(xhr) {
                                Swal.fire('Error',
                                    'Something went wrong while deleting.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
@endsection
