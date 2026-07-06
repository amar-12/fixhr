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
    Employee
@endsection
@section('css')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "employee-table-dynamic",
                url: "{{ url()->full() }}",
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
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="active"><span><b>Employee</b></span></li>
            </ol>
        </div>

        <!-- Bootstrap Modal -->
        <div class="modal fade" id="employeeModal" tabindex="-1" role="dialog" aria-labelledby="employeeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header border-0">
                        <h4 class="modal-title" id="modalTitle">Employee Detail Preview</h4>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="card user-pro-list overflow-hidden mt-3" id="avtarDiv">
                        <div class="card-body py-5">
                            <div class="row user-pic text-left">
                                <div class="col-1 pt-3">
                                    <span class="avatar avatar-xxl brround" id="empAvtar"
                                        style="background-image: url("{{ asset('assets/imgs/user.png') }}");"></span>
                                </div>
                                <div class="col-11 text-left">
                                    <h1 class="px-4 pt-6 mt-6 mb-0" id="empName"></h1>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody class="text-center">
                                        <tr>
                                            {{-- <td class="py-2 px-0"><span class="font-weight-semibold w-50">Name </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id=""></td> --}}

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Email </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empEmail"></td>

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Role </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empRole"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">DOB </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empBirth"></td>

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Phone </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empPhone"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Marital Status
                                                </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empMarital"></td>

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Department </span>
                                            </td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empDepartment"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">DOJ </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empJoining"></td>

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Designation
                                                </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empDesignation"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Status </span>
                                            </td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empStatus"></td>

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Grade </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empGrade"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Attendance Mode
                                                </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empMode"></td>

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Type </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empType"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Assign Shift
                                                </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empShift"></td>

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Code </span>
                                            </td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empCode"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Branch </span>
                                            </td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empBranch"></td>

                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Gender </span>
                                            </td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empGender"></td>
                                        </tr>

                                        <tr>
                                            <td class="py-2 px-0"><span class="font-weight-semibold w-50">Policy Category
                                                </span></td>
                                            <td class="py-2 px-0">:</td>
                                            <td class="py-2 px-0" id="empPolicy"></td>
                                        </tr>

                                        {{-- <tr >
                                        </tr> --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- START ROW -->
        <div class="row">
            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <div class="text-start"> <span class="font-weight-semibold">Total Employees</span>
                                    <h3 class="mb-0 mt-1 text-success"> {{ $allEmployeeCount }} </h3>
                                </div>
                            </div>
                            <div class="col-5 ">
                                <div class="icon1 bg-success-transparent my-auto pt-3 float-end"> <i
                                        class="las la-users"></i>
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
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Male
                                        Employees</span>
                                    <h3 class="mb-0 mt-1 text-primary "> {{ $maleEmployeesCount }}</h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-primary-transparent my-auto pt-3 float-end"> <i
                                        class="las la-male"></i>
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
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">Female
                                        Employees</span>
                                    <h3 class="mb-0 mt-1 text-secondary"> {{ $femaleEmployeesCount }}</h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-secondary-transparent my-auto float-end pt-3"> <i
                                        class="las la-female"></i>
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
                                <div class="mt-0 text-start"> <span class="font-weight-semibold">New
                                        Employees</span>
                                    <h3 class="mb-0 mt-1 text-danger"> {{ $newEmployeesCount }}
                                    </h3>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-danger-transparent my-auto pt-3 float-end"> <i
                                        class="las la-user-friends"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- END ROW -->
        @if (session('import_errors_blade'))
            <div class="alert d-flex align-items-center mt-3">
                <p>There were errors in the import. You can download the error file from the link below:</p>
                <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()" class="ms-2 mb-4 btn btn-outline-danger ">Download Error File</a>
            </div>
        @endif
        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">

                    <div class="card-header border-0">
                        <h4 class="card-title">Employee List </h4>

                        <div class="page-rightheader ms-auto">
                            <div class="align-items-end flex-wrap my-auto right-content breadcrumb-right">
                                <div class="d-flex">
                                <div class="btn-list">
                                        <!-- @if ($permission->check_route_permission('admin/employee/form', 115)) -->
                                            <!-- <a class="btn btn-outline-primary my-auto" href="{{ route('salary.generate') }}">Generate Salary</a> -->
                                        <!-- @endif -->
                                    </div>

                                    <div class="btn-list">
                                        @if ($permission->check_route_permission('admin/employee/form', 115))
                                            <a class="btn btn-outline-primary my-auto" href="{{ route('employee.form') }}">Add New
                                                Employee</a>
                                        @endif
                                    </div>
                                    <div class="btn-list ms-3">
                                        <!-- Three-dot action icon with dropdown -->
                                        <div>
                                            <button class="btn btn-outline-danger " type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                                @if ($permission->check_route_permission('admin/employee/form', 115))
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)"
                                                            data-bs-toggle="modal" data-bs-target="#addEmployeeFile">
                                                            Upload File
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('employee.downloadExcel') }}">
                                                            Export Format
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('employee.export.data') }}">
                                                            Export Employee Data
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">Branch</p>
                                    <select id="branchFilter" data-filter
                                        class="form-select-md p-2 search_test custom-heighlight">
                                        <option value="">All</option>
                                        @foreach ($branchFilter as $branchF)
                                            <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">Department</p>
                                    <select id="departmentFilter" class="form-select-md p-2 search_test custom-heighlight"
                                        data-filter>
                                        <option value="">All </option>
                                        @foreach ($departmentFilter as $departmentF)
                                            <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">Designation</p>
                                    <select id="designationFilter"
                                        class=" form-select-md p-2 search_test custom-heighlight" data-filter>
                                        <option value="">All</option>
                                        @foreach ($designationFilter as $designationF)
                                            <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">Grade</p>
                                    <select id="gradeFilter" class=" form-select-md p-2 search_test custom-heighlight"
                                        data-filter>
                                        <option value="">All</option>
                                        @foreach ($gradeFilter as $gradeF)
                                            <option value="{{ $gradeF->g_id }}">{{ $gradeF->g_name }}</option>
                                        @endforeach

                                    </select>

                                </div>
                            </div>

                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">Employee Status</p>
                                    <select id="activeFilter" class=" form-select-md p-2 search_test custom-heighlight"
                                        data-filter>
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>

                                    </select>

                                </div>
                            </div>


                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">From Date</p>
                                    <div class="form-group mb-3">
                                        <input type="date" id="fromDate" placeholder="From Date"
                                            class="form-control" data-date-filter="from-date" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">To Date</p>
                                    <div class="form-group mb-3">
                                        <input type="date" id="toDate" placeholder="To Date" class="form-control"
                                            data-date-filter="to-date" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <div class="form-group mb-3">
                                        <input type="text" id="searchFilter" placeholder="Search"
                                            class="form-control" data-search />
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-1 col-sm-4">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test"
                                        style="width: 100px" data-length>
                                        <option value="5" style="width: 100px">5</option>
                                        <option value="10" style="width: 100px">10</option>
                                        <option value="25" style="width: 100px">25</option>
                                        <option value="50" style="width: 100px">50</option>
                                        <option value="100" style="width: 100px">100</option>
                                        @if ($allEmployeeCount > 100)
                                            <option value="{{ $allEmployeeCount }}" style="width: 100px">All</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-9 col-sm-4"></div>

                            <div class="col-md-2 col-sm-4 pt-5" align="right">
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
                            <button type="reset" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- for model file upload end --}}
        <!-- Check for error messages -->
    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).on('click', '.openBtn', function() {
        $('#employeeModal').modal('show');
        // var empId = $(this).data('id');
        var empName = $(this).data('name');
        var empCode = $(this).data('code');
        var empBirth = $(this).data('birth');
        var empRole = $(this).data('role');
        var empType = $(this).data('type');
        var empBranch = $(this).data('branch');
        var empDepartment = $(this).data('department');
        var empPhone = $(this).data('phone');
        var empEmail = $(this).data('email');
        var empDesignation = $(this).data('designation');
        var empGrade = $(this).data('grade');
        var empGender = $(this).data('gender');
        var empPolicy = $(this).data('policy');
        var empMarital = $(this).data('marital');
        var empJoining = $(this).data('joining');
        var empStatus = $(this).data('status'); //data-status="pennddi"
        var empMode = $(this).data('mode');
        var empShift = $(this).data('shift');
        var empProfile = $(this).data('profile');

        let baseUrl = '{{ asset('uploads/employee_profile') }}';
        let profileImageUrl = empProfile ? baseUrl + '/' + empProfile : '{{ asset('assets/imgs/user.png') }}';
        // console.log(profileImageUrl);

        $('#empName').text(empName);
        $('#empCode').text(empCode);
        $('#empRole').text(empRole);
        $('#empBirth').text(empBirth);
        $('#empType').text(empType);
        $('#empBranch').text(empBranch);
        $('#empDepartment').text(empDepartment);
        $('#empPhone').text(empPhone);
        $('#empEmail').text(empEmail);
        $('#empDesignation').text(empDesignation);
        $('#empGrade').text(empGrade);
        $('#empGender').text(empGender);
        $('#empPolicy').text(empPolicy);
        $('#empMarital').text(empMarital);
        $('#empJoining').text(empJoining);
        $('#empStatus').text(empStatus);
        $('#empMode').text(empMode);
        $('#empShift').text(empShift);
        $('#empAvtar').css('background-image', 'url(' + profileImageUrl + ')');
    });


    document.addEventListener('DOMContentLoaded', function() {
        // Success message
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                showConfirmButton: false,
                timer: 5000,
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

        // Error message
        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        // Error messages (if multiple validation errors or custom messages are passed)
        @if ($errors->any())
            let errorMessages = '';
            @foreach ($errors->all() as $error)
                errorMessages += '{{ $error }}' + '<br>';
            @endforeach
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: 'Validation Errors',
                html: errorMessages, // Display the list of errors
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif
    });
</script>
