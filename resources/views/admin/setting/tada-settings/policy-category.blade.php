@extends('admin.layout.master')

@section('title', 'Policy Category')

@section('css')
    <style>
        .disable-alt {
            background-color: #eee !important;
            pointer-events: none !important;
        }

        .select2-container.read-only .select2-selection {
            pointer-events: none;
            background-color: #eee;
        }
    </style>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "policy-category-table-dynamic",
                url: "{{ route('admin.travel.policy.category') }}",
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

        function openAddPolicyCategory() {
            $('#editId').val('');
            $('#cat_name').val('');
            $('#grade option').removeAttr('selected');
            $('#department option').removeAttr('selected');
            $('#designation option').removeAttr('selected');
            $('#status option').removeAttr('selected');
            $('#modal-title').html('Add Policy Category');

            // Reset and trigger change for Select2
            $('#grade').val(null).trigger('change');
            $('#department').val(null).trigger('change');
            $('#designation').attr('multiple', 'multiple').attr('name', 'designation[]');
            $('#designation').val([]).trigger('change');
            $('#travel_type').val([]).trigger('change');
            $('#status').val(null).trigger('change');
            $('.select2').select2();
            $('select.sumo_search')[0].sumo.unSelectAll();
        }

        function openEditPolicyCategory(e) {
            // Unselect all items in the role select dropdown
            $('select.sumo_search')[0].sumo.unSelectAll();
            // Set the modal title
            $('#modal-title').html('Edit Policy Category');

            // Get data attributes from the clicked element
            var travelVehicleId = $(e).data('id');
            var categoryName = $(e).data('category_name');
            var grade = $(e).data('grade');
            var department = $(e).data('department');
            var designation = $(e).data('designation'); // Expecting this to be an array
            var travelType = $(e).data('travel_type');
            var status = $(e).data('status');

            // Set the values for the other fields
            $('#editId').val(travelVehicleId);
            $('#cat_name').val(categoryName);
            $('#grade').val(grade);
            $('#department').val(department);
            $('#travel_type').val(travelType);
            $('#status').val(status);

            // Ensure designation is treated as an array
            if (typeof designation === 'string') {
                designation = JSON.parse(designation); // Convert from string to array if needed
            }

            if (!Array.isArray(designation)) {
                designation = designation ? [designation] : []; // Convert to array if it's not
            }

            // Select each designation in the SumoSelect based on value
            $.each(designation, function(index, value) {
                $('select.sumo_search')[0].sumo.selectItem(String(value));
            });

            // Refresh SumoSelect UI to show selected items
            $('#designation').SumoSelect('refresh');

            // Optional: If you're using another select2 dropdown
            $('.select2').select2();
        }
    </script>
@endsection

@section('content')

    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                    <li class="active"><span><b>Policy Category</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                    onclick="openAddPolicyCategory();" data-bs-target="#addPolicyCategoryModal">Add New
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bradcrumbs End --}}




    <div class="card mt-5">
        <div class="card-header d-flex">
            <div>
                <h4 class="card-title"><span>Policy Category List</span></h4>
            </div>
            {{-- <div class="ms-auto">
                <button class="btn text-white btn-info btn-sm" id="addPolicyCategoryFieldBtn"><i class="fe fe-plus bold"></i></button>
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
                        <input type="text" id="searchFilter" placeholder="Search" class="form-control" data-search />
                    </div>
                </div>

                <div class="col-sm-6">
                </div>

                <div class="col-sm-1">
                    <button class="custom-button w-100" type="button" onclick="toggleFilters()" style="margin-top: 28px;">
                        <!-- Custom SVG: 2 horizontal lines with knobs -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5">
                            <!-- Top slider -->
                            <line x1="3" y1="8" x2="21" y2="8" stroke-linecap="round" />
                            <circle cx="10" cy="8" r="1.5" fill="currentColor" />

                            <!-- Bottom slider -->
                            <line x1="3" y1="16" x2="21" y2="16" stroke-linecap="round" />
                            <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                        </svg>
                        Filters
                    </button>
                </div>

                <div class="col-sm-1" style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
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
                        <button class="btn btn-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>

                        <ul class="dropdown-menu p-2" aria-labelledby="policyCategoryDropdown" style="min-width: 220px;">

                            <li>
                                <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#ExcelModal">
                                    <i class="las la-file-upload"></i> Upload File
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                    href="{{ route('policy-category.downloadExcel') }}">
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
                                <label for="branchFilter" class="form-label">Grade Type</label>
                                <select id="tada_branchFilter" data-filter class="form-select search-txt filter_border">
                                    <option value="">All</option>
                                    @foreach ($grades as $gradeF)
                                        <option value="{{ $gradeF->g_id }}">{{ $gradeF->g_name }}</option>
                                    @endforeach
                                </select>



                            </div>

                            <div class="col-md">
                                <label for="departmentFilter" class="form-label">Deparment</label>
                                <select id="tada_departmentFilter" data-filter
                                    class="form-select search-txt filter_border">
                                    <option value="">All</option>
                                    @foreach ($departments as $departmentF)
                                        <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}</option>
                                    @endforeach

                                </select>



                            </div>

                            <div class="col-md">
                                <label for="designationFilter" class="form-label">Designation</label>
                                <select id="tada_designationFilter" data-filter
                                    class="form-select search-txt filter_border">
                                    <option value="">All</option>
                                    @foreach ($designations as $designationF)
                                        <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}</option>
                                    @endforeach
                                </select>



                            </div>

                            <div class="col-md">
                                <label for="travelTypeFilter" class="form-label">Travel Type</label>
                                <select id="tada_travelTypeFilter" data-filter
                                    class="form-select search-txt filter_border">
                                    <option value="">All</option>
                                    @foreach ($travelTypes as $val)
                                        <option value="{{ $val->pttt_id }}">{{ $val->fh_travel_type->m_name }}</option>
                                    @endforeach
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
                <table class="table display  table-hover table-vcenter text-wrap border-bottom"
                    id="policy-category-table-dynamic">
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

    <div class="modal fade" id="addPolicyCategoryModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Add Policy Category</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="addPolicyCategoryForm" method="POST">@csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="text" id="editId" name="editPolicyCategory" hidden>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"> Category Name <span class="text-danger">*</span></label>
                                    <input name="cat_name" id="cat_name" class="form-control CategoryName"
                                        value="" placeholder="Enter Category Name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <p class="form-label">Grade <span class="text-danger">*</span></p>
                                    <select name="grade" id="grade"
                                        class="form-control custom-select select2 grade" data-placeholder="Select Grade"
                                        required>
                                        <option label="Select Grade"></option>
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade->g_id }}">{{ $grade->g_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"> Department <span class="text-danger">*</span></label>
                                    <select name="department" id="department"
                                        class="form-control custom-select select2 department"
                                        data-placeholder="Select Department" required>
                                        <option label="Select Department"></option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->d_id }}">{{ $department->d_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"> Designation <span class="text-danger">*</span></label>
                                    <select name="designation[]" id="designation"
                                        class="form-select-md  sumo_search designation"
                                        data-placeholder="Select Designation" required multiple>
                                        @foreach ($designations as $designation)
                                            <option value="{{ $designation->dg_id }}">{{ $designation->dg_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"> Travel Type <span class="text-danger">*</span></label>
                                    <select name="travelTypeId[]" id="travel_type" multiple
                                        class="form-control custom-select select2 travelType"
                                        data-placeholder="Select Travel Types" required>
                                        <option label="Select Travel Type"></option>
                                        @foreach ($travelTypes as $ttype)
                                            <option value="{{ $ttype->pttt_id }}">{{ $ttype->fh_travel_type->m_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"> Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control custom-select select2"
                                        data-placeholder="Select Status" required>
                                        <option label="Select Status"></option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
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

    <!-- Upload excel file -->
    <div class="modal fade" id="ExcelModal" tabindex="-1" role="dialog" aria-labelledby="largemodal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <h5 class="modal-title" id="DaModalLabel">Upload Policy Category File</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('policy-category.import') }}" method="POST" enctype="multipart/form-data">
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
                        <a class="btn btn-danger" data-bs-dismiss="modal">Close</a>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#addPolicyCategoryForm').submit(function(event) {
            let data = new FormData(this);
            event.preventDefault();

            $.ajax({
                url: '{{ route('admin.create.update.policy.category') }}',
                method: 'POST',
                data: data,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#saveUptBtn').attr('disabled', 'disabled');
                },
                success: function(data) {
                    if (data.status == true) {
                        Swal.fire({
                            icon: 'success',
                            text: data.message,
                            timer: 3000,
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            text: data.message,
                            timer: 3000,
                        });
                    }
                    $('#saveUptBtn').attr('disabled', false);
                    window.location.href = '{{ route('admin.travel.policy.category') }}';
                },
                error: function(xhr, status, error) {
                    var response = JSON.parse(xhr.responseText); // Parse the JSON response
                    var errorMessage = response.errors ? response.errors.join('\n') :
                        'An error occurred'; // Extract the error message
                    Swal.fire({
                        icon: 'error',
                        text: errorMessage,
                        timer: 3000
                    });
                    $('#saveUptBtn').attr('disabled', false);
                }
            });
        });




        $(document).ready(function() {
            // Initialize Select2 globally for elements with the class 'select2'
            $('.select2').select2();

            // Reinitialize Select2 when the modal is shown
            $('#addPolicyCategoryModal').on('shown.bs.modal', function() {
                // Destroy existing Select2 instance if it exists
                $('.select2').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                // Initialize Select2 again within the modal
                $('.select2').select2({
                    dropdownParent: $('#addPolicyCategoryModal')
                });
            });
        });

        //excel upload file sweet alert
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
    </script>
@endsection
