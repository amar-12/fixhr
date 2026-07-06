@extends('admin.layout.master')
@section('title')
    {{$pageTitle}}
@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
    /* General Card Shadow */
    .card {
        border: none;
        border-radius: 10px;
    }

    .card-header {
        background-color: #f9f9f9;
        font-weight: 600;
    }

    /* Icons and Badges */
    .bi-info-circle {
        color: #6c757d;
        margin-right: 5px;
    }

    .badge {
        font-size: 12px;
        font-weight: 500;
    }

    /* Deduction Summary Styling */
    .fs-4 {
        font-size: 1.1rem !important;
        color: #6c757d;
    }

    .fs-5 {
        font-size: 1.3rem !important;
    }

    .text-primary {
        color: #007bff !important;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

/* Active Employees Count */
    .fs-1 {
        font-size: 2.5rem !important;
        color: #28a745 !important;
    }

/* Circle Icon Styling */
    .icon-circle {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 50px; /* Circle size */
        height: 50px; /* Circle size */
        border-radius: 50%; /* Make it circular */
        background-color: rgba(100, 100, 255, 0.1); /* Light purple (EPF) */
    }

    .icon-circle.text-success {
        background-color: rgba(0, 255, 100, 0.1); /* Light green (ESI) */
    }

    .icon-circle.text-danger {
        background-color: rgba(255, 100, 100, 0.1); /* Light red (TDS) */
    }

    .icon-circle i {
        font-size: 1.5rem; /* Icon size */
    }

    .vertical-line {
        height: 100%;
        background-color: #808080;
    }


    .card-custom {
            border-left: 4px solid #007bff;
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 15px;
    }

    .period-title {
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    .small-text {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .vertical-line {
        border-left: 2px solid #808080;
        height: 100%;
        margin-right: 10px;
    }

    .tax-title {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 15px;
    }

    .payroll-amount {
        font-size: 1.2rem;
        font-weight: bold;
    }

    .badge-custom {
        background-color: #e9ecef;
        color: #495057;
        font-size: 1rem;
        padding: 8px 12px;
        border-radius: 5px;
    }

    .section-spacing {
        margin-top: 10px;
    }
</style>
@endsection
@section('content')
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />


 <!-- Page Container -->
 <div class="container-fluid pt-5">
    <!-- Process Pay Run Section -->
    <div id="payRunSection">
        <div class="pb-5">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Process Pay Run for <strong>September 2023</strong>
                    <span class="badge bg-primary ms-2">READY</span>
                </h3>
            </div>

            <div class="card shadow-sm mb-3" style="border-left: 4px solid #007bff;">
                <div class="row mt-3 card-body">
                    <div class="col-md-4 text-center">
                        <p class="fw-bold pb-1 h5">EMPLOYEES' NET PAY</p>
                        <p class="text-muted badge bg-light h5">YET TO PROCESS</p>
                    </div>
                    <div class="col-auto d-flex align-items-center p-0">
                        <div class="vr" style="height: 100%; background-color: #808080;"></div> <!-- Vertical Line -->
                    </div>
                    <div class="col-md-4 text-center">
                        <p class="fw-bold pb-1 h5">PAYMENT DATE</p>
                        <p class="text-muted h5">29/09/2023</p>
                    </div>
                    <div class="col-md-1 text-center">
                        <p class="fw-bold pb-1 h5">NO. OF EMPLOYEES</p>
                        <p class="text-muted h5">27</p>
                    </div>
                    <div class="col-md-1 text-center"></div>
                    <div class="col-md-1 text-center">
                        <button class="btn btn-outline-primary" id="createPayRunBtn">Create Pay Run</button>
                    </div>

                    <p class="text-muted mt-5 mb-3">
                        <i class="bi bi-info-circle"></i> Please process and approve this pay run before <strong>29/09/2023</strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Deduction Summary and Employee Summary -->
        <div class="row">
            <!-- Deduction Summary -->
            <div class="col-md-8">
                <div class="card shadow-sm" style="border-left: 4px solid #007bff;">
                    <div class="card-header badge bg-light text-dark">
                        <h3 class="mb-0">Deduction Summary <span class="text-muted">(Previous Month)</span></h3>
                    </div>
                    <div class="card-body row text-center">
                        <!-- EPF -->
                        <div class="col-md-4 pt-2">
                            <div class="text-center">
                                <div class="pt-2">
                                <div class="icon-circle text-primary">
                                    <i class="bi bi-feather"></i>
                                </div>
                                </div>
                                <br>
                                <span class="fs-4 fw-bold">EPF</span>
                                <p class="fs-5 fw-bold text-primary">₹389,563.89</p>
                                <a href="#" class="text-decoration-none">View Details</a>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center p-0">
                            <div class="vr vertical-line"></div> <!-- Vertical Line -->
                        </div>
                        <!-- ESI -->
                        <div class="col-md-4 pt-2">
                            <div class="text-center">
                                <div class="pt-2">
                                <div class="icon-circle text-success">
                                    <i class="bi bi-calendar2-event"></i>
                                </div>
                                </div>
                                <br>
                                <span class="fs-4 fw-bold">ESI</span>
                                <p class="fs-5 fw-bold text-success">₹1,910.00</p>
                                <a href="#" class="text-decoration-none">View Details</a>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center p-0">
                            <div class="vr vertical-line"></div> <!-- Vertical Line -->
                        </div>
                        <!-- TDS Deduction -->
                        <div class="col-md-3 pt-2">
                            <div class="text-center">
                                <div class="pt-2">
                                <div class="icon-circle text-danger">
                                    <i class="bi bi-percent"></i>
                                </div>
                                </div>
                                <br>
                                <span class="fs-4 fw-bold">TDS DEDUCTION</span>
                                <p class="fs-5 fw-bold text-danger">₹2,108,251.00</p>
                                <a href="#" class="text-decoration-none">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Summary -->
            <div class="col-md-4">
                <div class="card shadow-sm text-center" style="border-left: 4px solid #007bff;">
                    <div class="card-header badge bg-light text-dark mb-3">
                        <h3 class="mb-0">Employee Summary</h3>
                    </div>
                    <div class="card-body">
                        <p>ACTIVE EMPLOYEES</p>
                        <p class=" fw-bold text-success" style="font-size: 4.5em">27</p>
                        <a href="#" class="text-decoration-none pt-5">View Employees</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- Pay Run Processing Section -->
    <div id="processingSection" style="display: none;" >
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <button class="btn btn-outline-primary" id="backToPayRunSection" onclick="showPayRunSection()">
                    Back to Regular Pay Run
                </button>
            </div>
        </div>
        <div class="row g-3 mb-5">
            <div class="col-xl-5 col-lg-5 col-md-5 text-center">
                <div class="card-custom shadow-sm h-100">
                    <div class="row align-items-center">
                        <div class="col-1"></div>
                        <div class="col-4">
                            <p class="period-title fw-bold pt-3">Period: August 2022</p>
                        </div>
                        <div class="col-1">
                            <div class="d-flex align-items-center p-0">
                                <div class="vr fw-bold" style="height: 21px; color: #000000; width: 3px;"></div>
                            </div>
                        </div>
                        <div class="col-4 pl-5">
                            <div class="d-flex align-items-center">
                                <p class="period-title fw-bold">31 Base Day</p>
                            </div>
                        </div>
                        <div class="col-2"></div>
                    </div>


                    <div class="row ">
                        <div class="col-1"></div>
                        <div class="col-5">
                            <div class="section-spacing">
                                <p class="payroll-amount">₹56,60,936.69</p>
                                <small class="small-text">PAYROLL COST</small>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="section-spacing">
                                <p class="payroll-amount">₹36,58,484.00</p>
                                <small class="small-text">EMPLOYEE NET PAY </small>
                            </div>
                        </div>
                        <div class="col-1"></div>
                    </div>
                </div>
            </div>

            <!-- Middle Column: Pay Day -->
            <div class="col-xl-3 col-lg-3 col-md-3">
                <div class="card-custom shadow-sm text-center h-100">
                    <p class="small-text pt-3">Pay Day</p>
                    <p class="fw-bold h3">26</p>
                    <p class="small-text">August 2022</p>
                    <hr style="border: none; height: 1px; background-color: black; margin: 0;">
                    <p class="fw-bold h4 pt-5">10 Employee</p>
                </div>
            </div>

            <!-- Right Column: Taxes & Deductions -->
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="card-custom shadow-sm align-items-center h-100">
                    <h2 class="tax-title text-center pt-3">Taxes & Deductions</h2>
                    <div class="section-spacing">
                        <p class="mb-1 text-center">Taxes: <span class="fw-bold">₹18,11,647.00</span></p>
                        <p class="mb-1 text-center">Pre-Taxes Deduction: <span class="fw-bold">₹1,88,605.69</span></p>
                        <p class="text-center">Post-Taxes Deduction: <span class="fw-bold">₹2,200.00</span></p>
                    </div>
                </div>
            </div>
        </div>


        <div class="row pt-5">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">All Employee</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <!-- Show Entries -->
                            <div class="col-md-1 col-sm-4">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length>
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Export Button -->
                            <div class="col-md-2 col-sm-4">
                                <p class="form-label invisible">Export</p>
                                <div class="btn-group">
                                    <button class="btn btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Export As
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Search Input -->
                            <div class="col-md-2 offset-md-7">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control" />
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table display table-vcenter text-wrap border-bottom">
                                <thead>
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Total Days</th>
                                        <th>Worked Days</th>
                                        <th>Monthly Salary</th>
                                        <th>Earnings</th>
                                        <!-- <th>Taxes</th>
                                        <th>Benefits</th>
                                        <th>Reimbursements</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($employee_salaries as $emp_sal)
                                    <tr>
                                    <td>{{ $emp_sal->emp_full_name }}</td>
                                    <td>{{ $emp_sal->total_days_in_month }}</td>
                                    <td>{{ $emp_sal->total_month_working_days }}</td>
                                    <td>₹{{ $emp_sal->monthly_salary }}</td>
                                    <td>₹{{ $emp_sal->totalEarnings }}</td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row mt-3">
                            <div class="col-sm-6" id="custom-show-entries"></div>
                            <div class="col-sm-6 d-flex justify-content-end">
                                <ul data-pagination class="custom-pagination"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


</div>
@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('createPayRunBtn').addEventListener('click', function () {
        // Hide Process Pay Run Section
        document.getElementById('payRunSection').style.display = 'none';

        // Show Pay Run Processing Section
        document.getElementById('processingSection').style.display = 'block';
    });

    function showPayRunSection() {
        document.getElementById('processingSection').style.display = 'none';
        document.getElementById('payRunSection').style.display = 'block';
    }
</script>
@endsection
