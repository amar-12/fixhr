@extends('admin.layout.master')
@section('title', 'Kit Details')
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
    <style>
        #colorBox {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            margin-top: 10px;
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
                    <li class="active"><span><b>Kit Details</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <a class="btn btn-outline-primary" href="{{ route('uniform_index.create') }}">Add</a>
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
                    <h4 class="card-title">Kit Details</h4>
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



                        <div class="col-sm-1" style="margin-top: 30px;">
                            <div class="form-group filter_dots">
                                <button class="btn btn-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                                            href="{{ route('academic.export') }}">
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
                                        <label for="designationFilter" class="form-label">Qualification</label>
                                        <select id="emp_qualifactionFilter" data-filter class="form-select  filter_border ">
                                            <option value="">All</option>
                                            {{-- @foreach ($emp_qulification as $data)
                                                <option value="{{ $data->id }}">
                                                    {{ $data->name }}
                                                </option>
                                            @endforeach --}}
                                        </select>
                                    </div>


                                    <div class="col-md">
                                        <label for="departmentFilter" class="form-label">Course/Degree</label>
                                        <select id="emp_courseFilter" data-filter class="form-select  filter_border ">
                                            <option value="">All</option>
                                            {{-- @foreach ($emp_course as $data)
                                                <option value="{{ $data->id }}">{{ $data->name }}
                                                </option>
                                            @endforeach --}}

                                        </select>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="academic-details-table">
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

    <div class="modal fade" id="uniformViewModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="margin-top: 20px;">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <h6 class="modal-title" id="employee-name-display">Kit Details</h6>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"> <span
                            aria-hidden="true">×</span> </button>
                </div>

                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Material</th>
                                    <th>Outfit</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Price/Unit</th>
                                    <th>Qty</th>
                                    <th>Payable</th>
                                    <th>Issued Date</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody id="uniform-view-body">
                                <tr>
                                    <td colspan="10" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer py-1">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            datatable({
                tableId: "academic-details-table",
                url: "{{ route('uniform_index.index') }}",
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


        $(document).on("click", ".uniformDeleteBtn", function() {
            let id = $(this).data("id");

            Swal.fire({
                title: "Are you sure?",
                text: "This uniform issue and all its items will be deleted.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    let deleteUrl = "{{ route('uniform_index.destroy', ':id') }}".replace(":id", id);

                    $.ajax({
                        url: deleteUrl,
                        type: "DELETE",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr("content")
                        },

                        success: function(response) {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: response.message
                            });

                            $("#uniform-table").DataTable().ajax.reload(null, false);
                        },

                        error: function(xhr) {
                            if (xhr.status === 422) {
                                Swal.fire({
                                    icon: "error",
                                    title: "Cannot Delete!",
                                    text: xhr.responseJSON.message
                                });
                                return;
                            }

                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: "Unable to delete record."
                            });
                        }
                    });
                }
            });
        });
    </script>

    {{-- <script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-uniform-btn')) {
            const userId = e.target.getAttribute('data-id');
            const tbody = document.getElementById('uniform-view-body');
            tbody.innerHTML = `<tr><td colspan="10" class="text-center">Loading...</td></tr>`;

            fetch(`/get-uniform/${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        tbody.innerHTML =
                            `<tr><td colspan="10" class="text-center text-muted">No uniform records found</td></tr>`;
                        return;
                    }

                    let rows = '';
                    data.forEach((item, index) => {
                        rows += `<tr>
                            <td>${index + 1}</td>
                            <td>${item.uit_material_id}</td>
                            <td>${item.uit_description_id}</td>
                            <td>${item.uit_color}</td>
                            <td>${item.uit_size}</td>
                            <td>${item.uit_price}</td>
                            <td>${item.uit_quantity}</td>
                            <td>${item.uit_payable}</td>
                            <td>${item.uit_issues_date}</td>
                        <td>
                            ${item.uit_image_path
                                ? `<a href="/${item.uit_image_path}" target="_blank">View</a>`
                                : '<span class="text-muted">No file</span>'}
                        </td>

                        </tr>`;
                    });

                    tbody.innerHTML = rows;
                    new bootstrap.Modal(document.getElementById('uniformViewModal')).show();
                })
                .catch(() => {
                    tbody.innerHTML =
                        `<tr><td colspan="10" class="text-center text-danger">Error loading data</td></tr>`;
                });
        }
    });
</script> --}}

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('view-uniform-btn')) {
                const userId = e.target.getAttribute('data-id');
                const tbody = document.getElementById('uniform-view-body');
                const employeeNameDisplay = document.getElementById('employee-name-display');

                tbody.innerHTML = `<tr><td colspan="10" class="text-center">Loading...</td></tr>`;
                employeeNameDisplay.textContent = '';

                fetch(`/get-uniform/${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !Array.isArray(data.uniform_details) || data.uniform_details.length ===
                            0) {
                            tbody.innerHTML =
                                `<tr><td colspan="10" class="text-center text-muted">No uniform records found</td></tr>`;
                            return;
                        }

                        // Set employee name
                        employeeNameDisplay.textContent = "Kit Details - " + (data.employee || '');


                        // Create table rows
                        let rows = '';
                        data.uniform_details.forEach((item, index) => {
                            rows += `<tr>
                        <td>${index + 1}</td>
                        <td>${item.uit_material_id}</td>
                        <td>${item.uit_description_id}</td>
                        <td>${item.uit_color}</td>
                        <td>${item.uit_size}</td>
                        <td>${item.uit_price}</td>
                        <td>${item.uit_quantity}</td>
                        <td>${item.uit_payable}</td>
                        <td>${item.uit_issues_date}</td>
                        <td>
                            ${item.uit_image_path
                                ? `<a href="/${item.uit_image_path}" target="_blank">View</a>`
                                : '<span class="text-muted">No file</span>'}
                        </td>
                    </tr>`;
                        });

                        tbody.innerHTML = rows;
                        new bootstrap.Modal(document.getElementById('uniformViewModal')).show();
                    })
                    .catch(() => {
                        tbody.innerHTML =
                            `<tr><td colspan="10" class="text-center text-danger">Error loading data</td></tr>`;
                    });
            }
        });
    </script>


@endsection
@endsection
