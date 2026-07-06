<?php
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title') Attendance Report @endsection

@section('css')
<style>

    h5 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #007bff;
    }
</style>
@endsection

@section('content')
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/report/attendance-report') }}">Report</a></li>
                <li class="active"><span><b>Attendance Report</b></span></li>
            </ol>
        </div>
        <!-- END ROW -->

        <!-- ROW -->
        <div class="row pt-5">

            @if (session('error'))
            <div class="alert alert-warning alert-dismissible fade show text-white" role="alert" style="background-color: #ffc107; border-color: #ffc107;">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="color: #FFFFFF; opacity: 1;"></button>
            </div>
            @endif

            <nav class="navbar navbar-expand-lg navbar-light bg-white" style="padding-bottom:650px;">
                <div class="container-fluid">
                    <span class="navbar-brand fw-bold me-5">
                        <span class="h3">Attendance</span>
                    </span>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <p class="form-label">Type</p>
                                    <div class="form-group mb-3">
                                        <select name="month" id="monthlyReportType" class="form-control" required>
                                            <option value="MONTHLY_BASIC">Monthly Basic</option>
                                            <option value="MONTHLY_IN_OUT">Monthly In/Out</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <p class="form-label">Month</p>
                                    <div class="form-group mb-3">
                                        <select name="month" id="monthFilter" class="form-control" required>
                                            <option value="">Select Month</option>
                                            @for ($month = 1; $month <= 12; $month++)
                                                <option value="{{ $month }}" {{ $month == date('m') ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <p class="form-label">Year</p>
                                    <div class="form-group mb-3">
                                        <select name="year" id="yearFilter" class="form-control" required>
                                            <option value="">Select Year</option>
                                            @for ($year = date('Y'); $year >= date('Y') - 20; $year--)
                                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mt-5">
                                <div class="dropdown">
                                    <!-- Button to open dropdown -->
                                    <button class="btn-sm btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="feather feather-filter  text-primary"></i> Filter
                                    </button>

                                    <!-- Dropdown Form -->
                                    <div class="dropdown-menu p-4 shadow" aria-labelledby="filterDropdown" style="min-width: 500px;">
                                        <h5 class="mb-3">Employee Filter</h5>
                                        <hr class="bg-dark">
                                        {{-- <form > --}}
                                            {{-- @csrf --}}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-4">
                                                        <label for="branch" class="form-label fw-bold">Branch:</label>
                                                        <select name="branch" id="branch" class="form-select shadow-sm">
                                                            <option value="" selected disabled>Select Branch</option>
                                                            @foreach ($branch as $item)
                                                                <option value="{{ $item->br_id }}">{{ $item->br_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-4">
                                                        <label for="department" class="form-label fw-bold">Department:</label>
                                                        <select name="department" id="department" class="form-select shadow-sm">
                                                            <option value="" selected disabled>Select Department</option>
                                                            @foreach ($department as $item)
                                                                <option value="{{ $item->d_id }}">{{ $item->d_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-4">
                                                        <label for="designation" class="form-label fw-bold">Designation:</label>
                                                        <select name="designation" id="designation" class="form-select shadow-sm">
                                                            <option value="" selected disabled>Select Designation</option>
                                                            @foreach ($designation as $item)
                                                                <option value="{{ $item->dg_id }}">{{ $item->dg_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-4">
                                                        <label for="grade" class="form-label fw-bold">Grade:</label>
                                                        <select name="grade" id="grade" class="form-select shadow-sm">
                                                            <option value="" selected disabled>Select Grade</option>
                                                            @foreach ($grade as $item)
                                                                <option value="{{ $item->g_id }}">{{ $item->g_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>


                                            </div>

                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-outline-secondary me-2 px-4">Close</button>
                                            </div>
                                        {{-- </form> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-5">
                                <form action="{{ route('musterroll.report') }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    <input type="hidden" name="type" id="hiddenMonthlyReportType" value="">
                                    <input type="hidden" name="month" id="hiddenMonth" value="">
                                    <input type="hidden" name="year" id="hiddenYear" value="">
                                    <input type="hidden" name="branch" id="hiddenBranch" value="">
                                    <input type="hidden" name="department" id="hiddenDepartment" value="">
                                    <input type="hidden" name="designation" id="hiddenDesignation" value="">
                                    <input type="hidden" name="grade" id="hiddenGrade" value="">
                                    <input type="hidden" name="export_excel" value="1"> <!-- Add export_excel parameter -->

                                    <button type="submit" class="btn-sm btn-outline-success">Export</button>
                                </form>
                            </div>
                        </ul>


                        <!-- Loader (Initially hidden) -->
                        <div id="loader" style="display: none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Exporting...
                        </div>

                    </div>


                </div>
              </nav>
        </div>
    </div>


@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    document.getElementById('exportBtn').addEventListener('click', function(event) {
        // Prevent form submission if needed
        event.preventDefault();

        // Show the loader and hide the button
        document.getElementById('loader').style.display = 'inline-block'; // Show loader
        document.getElementById('exportBtn').style.display = 'none'; // Hide the export button

        // You can call your export function here, for example:
        // exportData();

        // Simulate the export process (you can replace this with your actual export function)
        setTimeout(function() {
            // After export is done, hide loader and show the button again
            document.getElementById('loader').style.display = 'none'; // Hide loader
            document.getElementById('exportBtn').style.display = 'inline-block'; // Show button
        }, 2000); // Simulate a 2-second export time (replace this with actual time for export)
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const monthlyReportType = document.getElementById("monthlyReportType");
        const monthFilter = document.getElementById("monthFilter");
        const yearFilter = document.getElementById("yearFilter");
        const hiddenMonth = document.getElementById("hiddenMonth");
        const hiddenYear = document.getElementById("hiddenYear");
        const hiddenMonthlyReportType = document.getElementById("hiddenMonthlyReportType");

        const branch = document.getElementById("branch");
        const department = document.getElementById("department");
        const designation = document.getElementById("designation");
        const grade = document.getElementById("grade");

        const hiddenBranch = document.getElementById("hiddenBranch");
        const hiddenDepartment = document.getElementById("hiddenDepartment");
        const hiddenDesignation = document.getElementById("hiddenDesignation");
        const hiddenGrade = document.getElementById("hiddenGrade");

        // Initialize hidden inputs with default values
        hiddenMonth.value = monthFilter.value;
        hiddenYear.value = yearFilter.value;

        hiddenBranch.value = branch.value;
        hiddenDepartment.value = department.value;
        hiddenDesignation.value = designation.value;
        hiddenGrade.value = grade.value;
        hiddenMonthlyReportType.value= monthlyReportType.value;

        // Update hidden inputs when dropdown values change
        monthFilter.addEventListener("change", function () {
            hiddenMonth.value = this.value;
        });

        yearFilter.addEventListener("change", function () {
            hiddenYear.value = this.value;
        });

        branch.addEventListener("change", function () {
            hiddenBranch.value = this.value;
        });

        department.addEventListener("change", function () {
            hiddenDepartment.value = this.value;
        });

        designation.addEventListener("change", function () {
            hiddenDesignation.value = this.value;
        });

        grade.addEventListener("change", function () {
            hiddenGrade.value = this.value;
        });

        monthlyReportType.addEventListener("change", function () {
            hiddenMonthlyReportType.value = this.value;
        });
    });
</script>

