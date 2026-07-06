<?php
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    Bank Sheet Report
@endsection
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
                <li class="active"><span><b>Bank Sheet Report</b></span></li>
            </ol>
        </div>
        <!-- END ROW -->
        <!-- ROW -->
        <div class="row pt-5">
            <nav class="navbar navbar-expand-lg navbar-light bg-white" style="padding-bottom:770px;">
                <div class="container-fluid">
                    <span class="navbar-brand fw-bold me-5">
                        <span class="h3">Bank Sheet</span>
                        <span class="h4 text-muted ms-5"><i class="feather feather-calendar"></i>
                            {{ now()->format('F d, Y') }}</span>
                    </span>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <div class="container-fluid">
                            <form id="bankSheetRecord" method="POST" action="{{ route('bank.sheet.report.export') }}">
                                @csrf
                                <div class="row g-3 align-items-end">
                                    <!-- Payroll Period -->
                                    <div class="col-md-4">
                                        <label for="payroll_period" class="form-label fw-bold">Payroll Period:</label>
                                        <select name="payroll_period" id="payroll_period" class="form-select shadow-sm"
                                            required>
                                            <option value="" selected disabled>Select Payroll Period</option>
                                            @foreach ($payrollPeriods as $period)
                                                <option value="{{ $period->pp_id }}">
                                                    {{ $period->pp_name }}
                                                    ({{ \Carbon\Carbon::parse($period->pp_start_date)->format('F Y') }} -
                                                    {{ \Carbon\Carbon::parse($period->pp_end_date)->format('F Y') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Select Field -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Select Field</label>
                                        <div class="dropdown w-100">
                                            <button class="btn btn-outline-warning w-100 dropdown-toggle" type="button"
                                                id="selectRowDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Field
                                            </button>
                                            <div class="dropdown-menu p-4 shadow" aria-labelledby="selectRowDropdown"
                                                style="min-width: 300px;">
                                                <h5 class="mb-3">Select Row</h5>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <button type="button" id="selectAllRows"
                                                        class="btn btn-outline-success btn-sm px-3 me-3">Select All</button>
                                                    <button type="button" id="unselectAllRows"
                                                        class="btn btn-outline-dark btn-sm px-3">Unselect All</button>
                                                </div>
                                                <hr class="bg-dark">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="emp_code"
                                                        id="code" name="columns[]" />
                                                    <label class="form-check-label" for="code">Emp Code</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="emp_full_name"
                                                        id="name" name="columns[]" />
                                                    <label class="form-check-label" for="name">Name</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        value="emp_documents_ref_file" id="emp_documents_ref_file"
                                                        name="columns[]" />
                                                    <label class="form-check-label" for="emp_documents_ref_file">Account
                                                        No.</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="es_monthly_ctc"
                                                        id="amount" name="fields[]" />
                                                    <label class="form-check-label" for="amount">Amount</label>
                                                </div>
                                                <div class="d-flex justify-content-end mt-4">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary px-4">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Export Button -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold invisible">Export</label>
                                        <button class="btn btn-outline-success w-100" type="submit">
                                            <i class="feather feather-download me-1"></i>Export</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    @if (session('swal'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: '{{ session('swal.icon') }}',
                    title: '{{ session('swal.title') }}',
                    text: '{{ session('swal.text') }}',
                    confirmButtonColor: '#3085d6'
                });
            });
        </script>
    @endif
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkboxes
        document.getElementById('selectAllRows').addEventListener('click', function() {
            event.stopPropagation(); // Prevent dropdown from closing
            const checkboxes = document.querySelectorAll('.form-check-input');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        });
        // Unselect all checkboxes
        document.getElementById('unselectAllRows').addEventListener('click', function() {
            event.stopPropagation(); // Prevent dropdown from closing
            const checkboxes = document.querySelectorAll('.form-check-input');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });
        const exportForm = document.getElementById('bankSheetRecord');
        if (exportForm) {
            exportForm.addEventListener('submit', function(event) {
                event.preventDefault();
                // Initialize FormData for form data collection
                const exportFormData = new FormData(this);
                // Collect values from dropdown filters
                const additionalFormInputs = document.querySelectorAll(
                    '#branch, #department, #designation, #grade, #status, #payroll_period');
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
                hiddenForm.action = '{{ route('bank.sheet.report.export') }}';
                hiddenForm.style.display = 'none';
                // Append CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content');
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
