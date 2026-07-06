<?php
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title') Payroll Sheet Report @endsection
@if (session('swal'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                icon: '{{ session('swal.icon') }}',
                title: '{{ session('swal.title') }}',
                text: '{{ session('swal.text') }}',
            });
        });
    </script>
@endif


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
                <li class="active"><span><b>Payroll Sheet Report</b></span></li>
            </ol>
        </div>
        <!-- END ROW -->

        <!-- ROW -->
        <div class="row pt-5">
            <nav class="navbar navbar-expand-lg navbar-light bg-white" style="padding-bottom:770px;">
                <div class="container-fluid">
                    <span class="navbar-brand fw-bold me-5">
                        <span class="h3">Payroll Sheet</span>
                        <span class="h4 text-muted ms-5"><i class="feather feather-calendar"></i> {{ now()->format('F d, Y') }}</span>
                    </span>

                  <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            {{-- <div class="dropdown me-3">
                                        <!-- Button to open dropdown -->
                                        <button class="btn-sm btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="feather feather-filter text-primary"></i>  Filter
                                        </button>

                                        <!-- Dropdown Form -->
                                        <div class="dropdown-menu p-4 shadow" aria-labelledby="filterDropdown" style="min-width: 500px;">
                                            <h5 class="mb-3">Employee Filter</h5>
                                            <hr class="bg-dark">
                                            <form >
                                                @csrf
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

                                                    <div class="col-md-6">
                                                        <div class="mb-4">
                                                            <label for="status" class="form-label fw-bold">Employee Status:</label>
                                                            <select name="status" id="status" class="form-select shadow-sm">
                                                                <option value="" selected disabled>Select Employee Status</option>
                                                                @foreach ($status as $item)
                                                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-outline-secondary me-2 px-4">Close</button>
                                                </div>
                                            </form>
                                        </div>
                            </div> --}}

                    <form method="POST" action="{{ route('payroll.sheet.report.export') }}" id="payrollFilterForm">
                        @csrf
                        <div class="row">
                            <!-- Payroll Period Filter -->
                            <div class="col-md-4">
                                <label for="payrollPeriodFilter" class="form-label fw-bold">Payroll Period:</label>
                                <select name="payroll_period" id="payrollPeriodFilter" class="form-control" required>
                                    <option value="">Select Payroll Period</option>
                                    @foreach ($payrollPeriods as $period)
                                    <option value="{{ $period->pp_id }}">
                                        {{ $period->pp_name }} ({{ date('F Y', strtotime($period->pp_start_date)) }} - {{ date('F Y',
                                        strtotime($period->pp_end_date)) }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select Fields Dropdown -->
                            <div class="col-md-4 mt-4">
                                <div class="dropdown">
                                    <button class="btn btn-outline-warning dropdown-toggle" type="button" id="selectFieldDropdown"
                                        data-bs-toggle="dropdown">
                                        Select Fields
                                    </button>
                                    <div class="dropdown-menu p-3 shadow" style="max-height: 400px; overflow-y: auto; width: 300px;">
                                        <h5 class="mb-3">Select Fields</h5>
                                        <div class="d-flex gap-2 mb-3">
                                            <button type="button" id="selectAllRows" class="btn btn-outline-success btn-sm">Select
                                                All</button>
                                            <button type="button" id="unselectAllRows" class="btn btn-outline-dark btn-sm">Unselect
                                                All</button>
                                        </div>
                                        <hr>

                                        @foreach ($exportableFields as $key => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $key }}"
                                                id="{{ Str::slug($key, '_') }}" name="columns[]" />
                                            <label class="form-check-label" for="{{ Str::slug($key, '_') }}">{{ $label }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Export Button -->
                            <div class="col-md-4 mt-4 d-flex align-items-start">
                                <button class="btn btn-outline-success" type="submit">Export</button>
                            </div>
                        </div>
                    </form>

                    </ul>



                  </div>
                </div>
              </nav>
        </div>
    </div>


@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JavaScript for Select All / Unselect All -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('selectAllRows').addEventListener('click', function () {
            document.querySelectorAll('input[name="columns[]"]').forEach(cb => cb.checked = true);
        });

        document.getElementById('unselectAllRows').addEventListener('click', function () {
            document.querySelectorAll('input[name="columns[]"]').forEach(cb => cb.checked = false);
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select all checkboxes
        document.getElementById('selectAllRows').addEventListener('click', function() {
            event.stopPropagation();  // Prevent dropdown from closing
            const checkboxes = document.querySelectorAll('.form-check-input');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        });

        // Unselect all checkboxes
        document.getElementById('unselectAllRows').addEventListener('click', function() {
            event.stopPropagation();  // Prevent dropdown from closing
            const checkboxes = document.querySelectorAll('.form-check-input');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });

        const exportForm = document.getElementById('payrollSheetRecord');
        if (exportForm) {
            exportForm.addEventListener('submit', function (event) {
                event.preventDefault();

                // Initialize FormData for form data collection
                const exportFormData = new FormData(this);

                // Collect values from dropdown filters
                const additionalFormInputs = document.querySelectorAll('#branch, #department, #designation, #grade, #status');
                additionalFormInputs.forEach(input => {
                    if (input.value) {
                        exportFormData.append(input.name, input.value);
                    }
                });

                // Collect data from checked checkboxes
                const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
                checkboxes.forEach(checkbox => {
                    exportFormData.append(checkbox.name, checkbox.value);
                });

                // Create a hidden form to submit
                const hiddenForm = document.createElement('form');
                hiddenForm.method = 'POST';
                hiddenForm.action = '{{ route("payroll.sheet.report.export") }}';
                hiddenForm.style.display = 'none';

                // Append CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                hiddenForm.appendChild(csrfInput);

                // Append all form data
                for (const [key, value] of exportFormData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    hiddenForm.appendChild(input);
                }

                document.body.appendChild(hiddenForm);
                hiddenForm.submit(); // Submit the form to trigger the download
            });
        }
    });
</script>
