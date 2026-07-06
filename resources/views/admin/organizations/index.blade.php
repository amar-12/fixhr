@extends('admin.layout.master')
@section('title')
    {{$pageTitle}}
@endsection
@section('css')
@endsection


<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    /* #btnXyz:hover {
        color: #fff
    } */

    table td {
        padding: 0;
    }
</style>


@section('content')
<x-breadcrumb :breadcrumbs="$breadcrumbs" />
    <div class="mt-5">


        <!-- START ROW -->
        <div class="row">
            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="text-start"> <span class="font-weight-semibold">Total Active</span>
                                    <h3 class="mb-0 mt-1 text-success"> 0 </h3>
                                </div>
                            </div>
                            <div class="col-5 ">
                                <div class="icon1 bg-success-transparent my-auto pt-3 float-end"> <i class="fa fa-bank"></i>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">This Year</span>
                                    <h3 class="mb-0 mt-1 text-primary ">  0</h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-primary-transparent my-auto pt-3 float-end"> <i class="fa fa-bank"></i>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Total InActive</span>
                                    <h3 class="mb-0 mt-1 text-secondary"> 0 </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-secondary-transparent my-auto float-end pt-3"> <i class="fa fa-bank"></i>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">This Year</span>
                                    <h3 class="mb-0 mt-1 text-danger"> 0
                                    </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-danger-transparent my-auto pt-3 float-end"> <i
                                        class="fa fa-bank"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- END ROW -->

        <!-- ROW -->
        <div class="row mt-5">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Organizations List</h4>
                        <div class="page-rightheader ms-auto">
                            <div class="align-items-end flex-wrap my-auto right-content breadcrumb-right">
                                <div class="d-flex">
                                    <div class="btn-list">
                                        <a class="btn btn-outline-primary my-auto" href="{{ route('employee.form') }}"><i class="fa fa-plus"></i> Create</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="row">
                            <div class="col-md-1 col-sm-4">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length
                                        style="width: 100px">
                                        <option value="5" style="width: 100px">5</option>
                                        <option value="10" style="width: 100px">10</option>
                                        <option value="25" style="width: 100px">25</option>
                                        <option value="50" style="width: 100px">50</option>
                                        <option value="100" style="width: 100px">100</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-1 col-sm-4 pt-5 mt-1" align="right">
                                <div class="btn-group">
                                    <button class="btn btn-outline-danger dropdown-toggle" type="button" id="defaultDropdown"
                                        data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                        Export As
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
                            <div class="col-md-8 col-sm-4"></div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <div class="form-group mb-3">
                                        <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                            data-search />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table display table-vcenter text-wrap border-bottom"
                                id="employee-table-dynamic">
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

        {{-- for model file upload strat --}}
        <div class="modal fade" id="addEmployeeFile" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header border-0">
                        <h4 class="modal-title ms-2" id="modal-title">Upload Employee File</h4>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="{{ route('employee.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <input type="text" id="editId" name="editTravelVehicle" hidden>
                                <input type="text" id="travelMode" hidden>
                                <input type="text" id="travelVehicle" hidden>
                                <input type="text" id="travelClass" hidden>
                                <input type="text" id="travelOwner" hidden>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Upload File :</label>
                                        <input type="file" name="import_file" id="import_file" class="form-control" required accept=".xlsx, .csv">
                                        <br>
                                        <div style="display: flex; align-items: center;">
                                            <p class="fw-bold" style="margin: 0;">Note -</p>
                                            <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer d-flex justify-content-end">@csrf
                            <button type="reset" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- for model file upload end --}}


    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
     $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "employee-table-dynamic",
                url: "{{ route('organizations.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: true
            });
        });
</script>
