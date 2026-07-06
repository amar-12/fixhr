<?php
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    Employee Report
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
                <li class="active"><span><b>Employee Report</b></span></li>
            </ol>
        </div>
        <!-- END ROW -->

        <!-- ROW -->
        <div class="row pt-5">
            <nav class="navbar navbar-expand-lg navbar-light bg-white" style="padding-bottom:770px;">
                <div class="container-fluid">
                    <span class="navbar-brand fw-bold me-5">
                        <span class="h3">Employee</span>
                        <span class="h4 text-muted ms-5"><i class="feather feather-calendar"></i>
                            {{ now()->format('F d, Y') }}</span>
                    </span>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <div class="dropdown me-3">
                                <!-- Button to open dropdown -->
                                <button class="btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="feather feather-filter text-primary"></i> Filter
                                </button>

                                <!-- Dropdown Form -->
                                <div class="dropdown-menu p-4 shadow" aria-labelledby="filterDropdown"
                                    style="min-width: 500px;">
                                    <h5 class="mb-3">Employee Filter</h5>
                                    <hr class="bg-dark">
                                    <form>
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label for="branch" class="form-label fw-bold">Branch:</label>
                                                    <select name="branch" id="branch" class="form-select shadow-sm">
                                                        <option value="" selected disabled>Select Branch</option>
                                                        @foreach ($branch as $item)
                                                            <option value="{{ $item->br_id }}">{{ $item->br_name }}
                                                            </option>
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
                                                    <select name="designation" id="designation"
                                                        class="form-select shadow-sm">
                                                        <option value="" selected disabled>Select Designation</option>
                                                        @foreach ($designation as $item)
                                                            <option value="{{ $item->dg_id }}">{{ $item->dg_name }}
                                                            </option>
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
                                                            <option value="{{ $item->g_id }}">{{ $item->g_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label for="status" class="form-label fw-bold">Employee
                                                        Status:</label>
                                                    <select name="status" id="status" class="form-select shadow-sm">
                                                        <option value="" selected disabled>Select Employee Status
                                                        </option>
                                                        @foreach ($status as $item)
                                                            <option value="{{ $item->m_id }}">{{ $item->m_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="button"
                                                class="btn btn-outline-secondary me-2 px-4">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>


                            <div class="dropdown me-3 mx-5">
                                <!-- Select Row Button to open dropdown -->
                                <button class="btn-sm btn-outline-warning dropdown-toggle" type="button"
                                    id="selectRowDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span> Select Field</span>
                                </button>

                                <!-- Select Row Dropdown Form -->
                                <div class="dropdown-menu p-4 shadow" aria-labelledby="selectRowDropdown"
                                    style="min-width: 300px;">
                                    <h5 class="mb-3">Select Row</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <button type="submit" id="selectAllRows"
                                            class="btn btn-outline-success btn-sm px-3 me-3">Select All Rows</button>
                                        <button type="submit" id="unselectAllRows"
                                            class="btn btn-outline-dark btn-sm px-3">Unselect All Rows</button>
                                    </div>
                                    <hr class="bg-dark">

                                    <!-- Checkbox List -->
                                    <form>
                                        @csrf
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_code"
                                                id="code" name="columns[]" />
                                            <label class="form-check-label" for="code">Employee Code</label>
                                        </div>
                                         <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_full_name"
                                                id="name" name="columns[]" />
                                            <label class="form-check-label" for="name">Name</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_email"
                                                id="email" name="columns[]" />
                                            <label class="form-check-label" for="email">Email</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_phone"
                                                id="phone" name="columns[]" />
                                            <label class="form-check-label" for="phone">Phone</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_job_status"
                                                id="emp_type" name="columns[]" />
                                            <label class="form-check-label" for="emp_type">Job Type</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_dob"
                                                id="dob" name="columns[]" />
                                            <label class="form-check-label" for="dob">Date Of Birth</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_marital_status_id"
                                                id="marital_status" name="columns[]" />
                                            <label class="form-check-label" for="marital_status">Marital Status</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_status"
                                                id="status_check" name="columns[]" />
                                            <label class="form-check-label" for="status">Status</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_type_id"
                                                id="job_status" name="columns[]" />
                                            <label class="form-check-label" for="job_status">Employee Type</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_br_id"
                                                id="branch_check" name="columns[]" />
                                            <label class="form-check-label" for="branch">Branch</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_d_id"
                                                id="department_check" name="columns[]" />
                                            <label class="form-check-label" for="department">Department</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_dg_id"
                                                id="designation_check" name="columns[]" />
                                            <label class="form-check-label" for="designation">Designation</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_role_id"
                                                id="role" name="columns[]" />
                                            <label class="form-check-label" for="role">Role</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_grade_id"
                                                id="grade_check" name="columns[]" />
                                            <label class="form-check-label" for="grade">Grade</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                value="emp_supervisor_id" id="emp_supervisor_id"
                                                name="columns[]" />
                                            <label class="form-check-label" for="reporting_manager">Reporting
                                                Manager</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_work_mode_id"
                                                id="work_mode" name="columns[]" />
                                            <label class="form-check-label" for="work_mode Mode">Work Mode</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_checkin_method_id"
                                                id="method" name="columns[]" />
                                            <label class="form-check-label" for="method">Checking Method</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_esic_limit"
                                                id="esic_limit" name="columns[]" />
                                            <label class="form-check-label" for="esic_limit">Esic Limit</label>
                                        </div>
                                        {{-- <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_pt_id"
                                                id="tax" name="columns[]" />
                                            <label class="form-check-label" for="tax">Policy Tax</label>
                                        </div> --}}
                                        {{-- <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_pt_id"
                                                id="bonus" name="columns[]" />
                                            <label class="form-check-label" for="bonus">Policy Bonus</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_pt_id"
                                                id="leave" name="columns[]" />
                                            <label class="form-check-label" for="leave">Policy Leave</label>
                                        </div> --}}
                                        {{-- <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="emp_pwo_id"
                                                id="week_off" name="columns[]" />
                                            <label class="form-check-label" for="week_off">Policy Week Off</label>
                                        </div> --}}

                                        <!-- Actions -->
                                        <div class="d-flex justify-content-end mt-5">
                                            <button type="button"
                                                class="btn btn-outline-secondary me-2 px-4">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <form class="d-flex mb-2" id="exportEmployeeRecord" method="POST">
                                @csrf
                                {{-- <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"> --}}
                                <button class="btn-sm btn-outline-success mx-5" type="submit">Export</button>
                            </form>

                        </ul>



                    </div>
                </div>
            </nav>
        </div>
    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportForm = document.getElementById('exportEmployeeRecord');
        if (exportForm) {
            exportForm.addEventListener('submit', function(event) {
                event.preventDefault();

                // Initialize FormData for form data collection
                const exportFormData = new FormData(this);

                // Collect values from dropdown filters
                const additionalFormInputs = document.querySelectorAll(
                    '#branch, #department, #designation, #grade, #status');
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
                hiddenForm.action = '{{ route('employee.report.export') }}';
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

    document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('selectAllRows').addEventListener('click', function(event) {
        event.stopPropagation();
        const checkboxes = document.querySelectorAll('form input[type="checkbox"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = true;
        });
    });


    document.getElementById('unselectAllRows').addEventListener('click', function(event) {
        event.stopPropagation();
        const checkboxes = document.querySelectorAll('form input[type="checkbox"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = false;
        });
    });



    document.querySelector('.btn-outline-secondary').addEventListener('click', function() {
        const dropdownMenu = document.querySelector('.dropdown-menu');
        if (dropdownMenu) {
            dropdownMenu.classList.remove('show');
        }
    });
});

</script>



