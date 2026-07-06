@php
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use App\Models\Designation;
use App\Models\MasterTable;
use ChandraHemant\HtkcUtils\CommonUtils;

$user = Auth::user();
$bId = $user->emp_b_id;

$departments = CommonUtils::getCustomModelData(new Department(), [
['method' => 'where', 'args' => ['d_b_id', $bId]],
['method' => 'orderBy', 'args' => ['d_name']],
]);

$designations = CommonUtils::getCustomModelData(new Designation(), [
['method' => 'where', 'args' => ['dg_b_id', $bId]],
['method' => 'orderBy', 'args' => ['dg_name']],
]);

$statuses = CommonUtils::getCustomModelData(new MasterTable(), [
['method' => 'where', 'args' => ['m_group', 'STATUS']],
['method' => 'orderBy', 'args' => ['m_name']],
]);
@endphp


@extends('admin.layout.master')

@section('title', 'Attendance - Add/Edit')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<style>

    .dimmer {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100px;
    }

    .sk-circle {
    margin: 40px auto;
    width: 60px;
    height: 60px;
    position: relative;
    }

    .sk-circle .sk-child {
    width: 100%;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    }

    .sk-circle .sk-child:before {
    content: '';
    display: block;
    margin: 0 auto;
    width: 15%;
    height: 15%;
    background-color: #0d6efd; /* Bootstrap primary blue */
    border-radius: 100%;
    animation: sk-circleBounceDelay 1.2s infinite ease-in-out both;
    }

    .sk-circle .sk-circle2 { transform: rotate(30deg); }
    .sk-circle .sk-circle3 { transform: rotate(60deg); }
    .sk-circle .sk-circle4 { transform: rotate(90deg); }
    .sk-circle .sk-circle5 { transform: rotate(120deg); }
    .sk-circle .sk-circle6 { transform: rotate(150deg); }
    .sk-circle .sk-circle7 { transform: rotate(180deg); }
    .sk-circle .sk-circle8 { transform: rotate(210deg); }
    .sk-circle .sk-circle9 { transform: rotate(240deg); }
    .sk-circle .sk-circle10 { transform: rotate(270deg); }
    .sk-circle .sk-circle11 { transform: rotate(300deg); }
    .sk-circle .sk-circle12 { transform: rotate(330deg); }

    .sk-circle .sk-circle2:before { animation-delay: -1.1s; }
    .sk-circle .sk-circle3:before { animation-delay: -1s; }
    .sk-circle .sk-circle4:before { animation-delay: -0.9s; }
    .sk-circle .sk-circle5:before { animation-delay: -0.8s; }
    .sk-circle .sk-circle6:before { animation-delay: -0.7s; }
    .sk-circle .sk-circle7:before { animation-delay: -0.6s; }
    .sk-circle .sk-circle8:before { animation-delay: -0.5s; }
    .sk-circle .sk-circle9:before { animation-delay: -0.4s; }
    .sk-circle .sk-circle10:before { animation-delay: -0.3s; }
    .sk-circle .sk-circle11:before { animation-delay: -0.2s; }
    .sk-circle .sk-circle12:before { animation-delay: -0.1s; }

    @keyframes sk-circleBounceDelay {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
    }


    #swal-progress-bar {
        transition: width 0.4s ease;
        line-height: 25px;
        color: #fff;
    }

    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
    }

    input.form-control {
        text-align: center;
    }

    .table th,
    .table td {
        padding: 1px !important;
        text-align: center;
        vertical-align: middle;
    }

    .table {
        border-collapse: collapse;
    }

    .table-bordered th,
    .table-bordered td {
        padding: 5px !important;
    }

    input.form-control {
        padding: 3px !important;
        height: 30px;
        font-size: 14px;
    }

    .text-end.mt-3 {
        margin-bottom: 15px;
    }

    .table-responsive {
        padding-top: 15px;
    }

    .text-end.mt-3 {
        margin-bottom: 15px;
        padding-right: 10px;
    }

    .table tbody tr:hover {
        background-color: #007bff !important;
        cursor: pointer;
    }

    .filter-container label,
    .filter-container span {
        font-size: 12px;
    }

    .filter-container {
        display: flex;
        align-items: center;
        gap: 5px;
        padding-bottom: 10px;
    }

    .filter-container label {
        color: #fff;
    }

    .filter-container select {
        width: 75px;
        font-size: 14px;
    }
</style>
<style>
    .btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }


    .dark-card {
        background-color: #1c1c2e;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        color: #ffffff;
        transition: 0.3s ease-in-out;
    }

    .dark-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
    }

    .info-card h6 {
        font-size: 0.875rem;
        color: #ccc;
        margin-bottom: 0.5rem;
    }

    .info-card h3 {
        font-size: 1.75rem;
        font-weight: 700;
    }

    .icon-box {
        font-size: 2rem;
        padding: 12px;
        border-radius: 8px;
        background-color: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Optional Gradient Backgrounds */
    .bg-gradient-1 {
        background: linear-gradient(145deg, #2d2d4d, #1e1e2f);
    }

    .bg-gradient-2 {
        background: linear-gradient(145deg, #2f4c3a, #1e2e1f);
    }

    .bg-gradient-3 {
        background: linear-gradient(145deg, #4c2f2f, #2e1e1e);
    }

    .bg-gradient-4 {
        background: linear-gradient(145deg, #2f3f4c, #1e2f3e);
    }

    .bg-gradient-5 {
        background: linear-gradient(145deg, #4c3a2f, #2e1f1e);
    }

    .bg-gradient-6 {
        background: linear-gradient(145deg, #4c2f4a, #2e1e2e);
    }

    .text-pink {
        color: #ff4da6 !important;
    }
</style>
<style>
    select.form-control-sm {
        background-color: #2e2e4d;
        color: #ffffff !important;
        border: 1px solid #444;
    }

    select.form-control-sm option {
        background-color: #2e2e4d;
        color: #ffffff !important;
    }
</style>



@endsection

@section('content')



<div class="page-header d-flex justify-content-between align-items-center flex-wrap mb-3">
    {{-- Breadcrumb / Title --}}
    <div class="page-leftheader">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="#">Dashboard</a></li>
            <li class="active"><span>Salary Process</span></li>
        </ol>
    </div>

    {{-- Right: Toggle Buttons --}}
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" id="toggle-unprocessed">
            🗂 To Be Processed ({{ $remainingCount }})
        </button>
        <button class="btn btn-outline-success" id="toggle-processed">
            ✅ Processed ({{ $totalProcessedCount }})
        </button>
    </div>
</div>



<div class="row">
    <div class="col-xl-12 col-md-12 col-lg-12">

        <div class="row g-3 mb-4">

            {{-- keep your existing “Total / Processed / To Process” cards if desired --}}
            <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <span class="font-weight-semibold">Total Employees</span>
                                <h3 class="mb-0 mt-1 text-success">{{ $totalEmployeeCount }}</h3>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-success-transparent my-auto pt-3 float-end">
                                    <i class="las la-user-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- dynamic employee‑status cards --}}
            @foreach($employeeStatusCounts as $stat)
            <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <span class="font-weight-semibold">
                                    {{ $stat->status_name }} Employees
                                </span>
                                <h3 class="mb-0 mt-1 text-primary">
                                    {{ $stat->total }}
                                </h3>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-primary-transparent my-auto pt-3 float-end">
                                    <i class="las la-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <span class="font-weight-semibold">To Process</span>
                                <h3 class="mb-0 mt-1 text-secondary">{{ $remainingCount }}</h3>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-secondary-transparent my-auto pt-3 float-end">
                                    <i class="las la-hourglass-half"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <span class="font-weight-semibold">Total Processed</span>
                                <h3 class="mb-0 mt-1 text-danger">{{ $totalProcessedCount }}</h3>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-danger-transparent my-auto pt-3 float-end">
                                    <i class="las la-user-cog"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- Salary Not‑Configured card --}}
            <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <span class="font-weight-semibold">Awaiting Salary Setup</span>
                                <h3 class="mb-0 mt-1 text-warning">{{ $salaryNotConfiguredCount }}</h3>
                            </div>
                            <div class="col-5">
                                <div class="icon1 bg-warning-transparent my-auto pt-3 float-end">
                                    <i class="las la-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>






        <div class="row">
            <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                <div class="row">
                    <div class="col-md">
                        <label for="filter-department" class="text-white">Department:</label>
                        <select id="filter-department" name="department" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach ($departments as $department)
                            <option value="{{ $department->d_name }}" {{ request('department')==$department->d_name ?
                                'selected' : '' }}>
                                {{ $department->d_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md">
                        <label for="filter-designation" class="text-white">Designation:</label>
                        <select id="filter-designation" name="designation" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach ($designations as $designation)
                            <option value="{{ $designation->dg_name }}" {{ request('designation')==$designation->dg_name
                                ? 'selected' : '' }}>
                                {{ $designation->dg_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md">
                        <label for="filter-status" class="text-white">Status:</label>
                        <select id="filter-status" name="status" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach ($statuses as $status)
                            <option value="{{ $status->m_name }}" {{ request('status')==$status->m_name ? 'selected' :
                                '' }}>
                                {{ $status->m_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>





        {{-- ───────────────────────────────────────────────────────────────────────────
        TO BE PROCESSED (inner status‑wise tabs)
        ───────────────────────────────────────────────────────────────────────────--}}
        <div class="card" id="unprocessed-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center gap-4">
                    <h4 class="card-title mb-0">To Be Processed</h4>
                    @if ($payroll)
                    <div><strong>Payroll Name:</strong> {{ $payroll->pp_name }}</div>
                    <div><strong>Month:</strong> {{ \Carbon\Carbon::parse($payroll->pp_start_date)->format('F Y') }}
                    </div>
                    <div><strong>Duration:</strong> {{ \Carbon\Carbon::parse($payroll->pp_start_date)->format('d-m-Y')
                        }} to {{ \Carbon\Carbon::parse($payroll->pp_end_date)->format('d-m-Y') }}</div>
                    @endif
                </div>

                <div class="d-flex align-items-center">
                    {{-- <label for="searchFilter" class="form-label me-2">Search</label> --}}
                    <input type="text" id="searchFilter" placeholder="Search" class="form-control" data-search />
                </div>

                <div class="d-flex align-items-center">
                    {{-- <label for="mt_statusFilter" class="form-label me-2">Status</label> --}}
                    <select id="mt_statusFilter" data-filter class="form-select search-txt filter_border">
                        <option value="">All ({{ $attendanceSummary->count() }})</option>
                        @php
                        $orderedStatuses = collect($statuses)
                        ->sortBy('m_name')
                        ->sortBy(fn($s) => $s->m_name !== 'Active');
                        @endphp
                        @foreach($orderedStatuses as $stat)
                        @php
                        $statusName = $stat->m_name;
                        $rows = $attendanceByStatus->get($statusName, collect());
                        @endphp
                        <option value="{{ $stat->m_id }}">{{ $statusName }} ({{ $rows->count() }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center">
                    {{-- Show entries dropdown placed at last --}}
                    <select id="customLengthMenu" class=" form-select p-2 search_test ms-2" style="width: 100%"
                        data-length>
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="card-body">
                <form class="process-salaries-form" method="POST" action="{{ route('process-salaries') }}">
                    @csrf
                    <input type="hidden" name="pp_id" value="{{ $payroll->pp_id }}">


                    <div class="table-responsive">
                        <table class="table table-bordered status-table w-100" id="unprocessed-employee-table">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th><input type="checkbox" class="select-all" data-slug="all"></th>
                                    <th>Emp Code</th>
                                    <th>Emp Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Total Days</th>
                                    <th>Salaried Days</th>
                                    <th>Emp Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-sm-6">
                            <div id="custom-show-entries" data-show-entries></div>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-end">
                            <ul data-pagination class="custom-pagination"></ul>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-outline-primary process-btn" {{
                            $attendanceSummary->isEmpty() ? 'disabled' : '' }}>
                            Process Selected
                        </button>

                        <div id="process-loader" style="display: none; margin-top: 10px;">
                            <div class="progress" style="height: 20px;">
                                <div id="process-progress-bar"
                                    class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 0%;">0%</div>
                            </div>
                        </div>


                    </div>
                </form>
            </div>

        </div>


        {{-- Processed Salaries Card --}}
        <div class="card d-none" id="processed-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Processed</h4>
            </div>

            <div class="card-body">
                @if (count($processedEmployees) > 0)
                <form class="unprocess-form" method="POST" action="{{ route('unprocess-salaries') }}">
                    @csrf
                    <input type="hidden" name="pp_id" value="{{ $payroll->pp_id }}">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped w-100 status-table" id="unprocessedTable">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="select-all" data-slug="unprocessed" checked></th>
                                    <th>S.No.</th>
                                    <th>Emp Code</th>
                                    <th>Emp Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Status</th>
                                    <th>Month Days</th>
                                    <th>Salaried Days</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($processedEmployees as $index => $employee)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_employees[]"
                                            value="{{ $employee->ps_emp_id }}" class="select-employee" checked>
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $employee->employee->emp_code }}</td>
                                    <td>{{ $employee->employee->emp_full_name }}</td>
                                    <td>{{ $employee->employee->fh_department->d_name }}</td>
                                    <td>{{ $employee->employee->fh_designation->dg_name }}</td>
                                    <td>{{ $employee->employee->fh_employee_status->m_name }}</td>
                                    <td>{{ $employee->ps_workable_days }}</td>
                                    <td>{{ $employee->ps_total_days_worked }}</td>
                                    <td><span class="badge bg-success">Processed</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-outline-danger" {{ $processedEmployees->isEmpty() ?
                            'disabled' : '' }}>
                            Unprocess Selected
                        </button>
                    </div>
                </form>
                <div class="text-center my-3 d-none" id="unprocess-loader">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Unprocessing...</span>
                    </div>
                    <p class="mt-2 text-danger fw-semibold">Unprocessing salaries, please wait...</p>
                </div>

                @else
                <div class="table-light text-center fw-semibold">
                    No processed employees found
                </div>
                @endif
            </div>
        </div>



    </div>
</div>

</div>

@endsection
@section('script')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    // Checkbox select all logic
    $('.select-all').on('change', function () {
        const slug = $(this).data('slug');
        $(`#attendanceTable-${slug} .select-employee`).prop('checked', this.checked);
    });


    document.addEventListener('DOMContentLoaded', () => {

        /* ───────────────────────── CONFIGS ───────────────────────── */
        const selectedEmployees = new Set();       // keep IDs across pages/tabs
        const dtOpts = {
            pageLength   : 25,
            lengthChange : false,
            paging       : true,
            searching    : true,
            ordering     : true,
            responsive   : true
        };

        /* ────────────── INITIALISE MAIN / PROCESSED TABLES ───────── */
        if ($('#processedTable').length) $('#processedTable').DataTable(dtOpts);

        /* first visible status‑table (usually “Active”) */
        const $firstVisible = $('.tab-pane.show.active .status-table');
        if ($firstVisible.length && !$.fn.dataTable.isDataTable($firstVisible[0])) {
            $firstVisible.DataTable(dtOpts);
        }

        /* lazy‑init each table the first time its tab is shown */
        $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', e => {
            const $pane  = $($(e.target).attr('data-bs-target'));
            const $table = $pane.find('.status-table');

            if (!$table.length) return;

            if (!$.fn.dataTable.isDataTable($table[0])) {
                $table.DataTable(dtOpts);
            } else {
                $table.DataTable().columns.adjust().draw();
            }
        });

        /* adjust columns when toggling back to Unprocessed view */
        $('#toggle-unprocessed').on('click', () => {
            const $tbl = $('#statusTabContent .tab-pane.show.active .status-table');
            if ($tbl.length && $.fn.dataTable.isDataTable($tbl[0])) {
                $tbl.DataTable().columns.adjust();
            }
        });

        /* ──────────────────── GLOBAL FILTER (optional) ─────────────────── */
        $.fn.dataTable.ext.search.push((settings, data) => {
            if (settings.nTable.id !== 'attendanceTable') return true;
            const deptF = ($('#filter-department').val() || '').toLowerCase().trim();
            const desgF = ($('#filter-designation').val() || '').toLowerCase().trim();
            const statF = ($('#filter-status').val()      || '').toLowerCase().trim();

            const deptC = (data[4] || '').toLowerCase().trim();
            const desgC = (data[5] || '').toLowerCase().trim();
            const statC = (data[6] || '').toLowerCase().trim();

            return (!deptF || deptC.includes(deptF)) &&
                (!desgF || desgC.includes(desgF)) &&
                (!statF || statC.includes(statF));
        });

        $('#filter-department, #filter-designation, #filter-status')
            .on('change', () => $('#attendanceTable').DataTable().draw());

        /* ───────────────── CHECKBOX SYNC (rows) ───────────────── */
        $(document).on('change', '.status-table .select-employee', function () {
            const empId = this.value;
            this.checked ? selectedEmployees.add(empId)
                        : selectedEmployees.delete(empId);

            const $tbl = $(this).closest('table').DataTable();
            const total  = $tbl.rows().nodes().to$().find('.select-employee').length;
            const marked = $tbl.rows().nodes().to$().find('.select-employee:checked').length;
            $($tbl.table().container()).find('.select-all').prop('checked', total && total === marked);
        });

        /* ───────────────── SELECT‑ALL (header) ───────────────── */
        $(document).on('click', '.select-all', function () {
            const isChecked = this.checked;
            const api       = $(this).closest('table').DataTable();

            $(api.rows().nodes()).find('.select-employee').each(function () {
                this.checked = isChecked;
                isChecked ? selectedEmployees.add(this.value)
                        : selectedEmployees.delete(this.value);
            });
        });

        /* ───────── restore checks on every draw (paging / search) ───────── */
        $(document).on('draw.dt', '.status-table', function () {
            const api  = $(this).DataTable();
            const rows = api.rows().nodes().to$();

            rows.find('.select-employee').each(function () {
                this.checked = selectedEmployees.has(this.value);
            });

            const total  = rows.find('.select-employee').length;
            const marked = rows.find('.select-employee:checked').length;
            $($.fn.dataTable.tables({ api: true, visible: false }).table().container())
                .find(`#${api.table().node().id}`).closest('table')
                .find('.select-all').prop('checked', total && total === marked);
        });

        /* ─────────────────── TOGGLE: Unprocessed ↔ Processed ────────────────── */
        const unBtn  = $('#toggle-unprocessed')[0];
        const prBtn  = $('#toggle-processed')[0];
        const unCard = $('#unprocessed-card')[0];
        const prCard = $('#processed-card')[0];
        const setActive = (on, off) => { on.classList.add('active'); off.classList.remove('active'); };

        if (unBtn && prBtn) {
            unBtn.addEventListener('click', () => { unCard.classList.remove('d-none'); prCard.classList.add('d-none'); setActive(unBtn, prBtn); });
            prBtn.addEventListener('click', () => { prCard.classList.remove('d-none'); unCard.classList.add('d-none'); setActive(prBtn, unBtn); });
            setActive(unBtn, prBtn);  // default view
        }

        /* ─────────────────────── PROCESS‑SALARIES AJAX ─────────────────────── */
        // $(document).on('submit', '.process-salaries-form', function (e) {
        //     e.preventDefault();

        //     const btn = $(this).find('.process-btn');
        //     if (!selectedEmployees.size) { alert('Please select at least one employee.'); return; }

        //     const formData = new FormData();
        //     formData.append('_token', '{{ csrf_token() }}');
        //     formData.append('pp_id',  '{{ $payroll->pp_id }}');
        //     selectedEmployees.forEach(id => formData.append('selected_employees[]', id));

        //     $.ajax({
        //         url         : '{{ route('process-salaries') }}',
        //         type        : 'POST',
        //         data        : formData,
        //         processData : false,
        //         contentType : false,
        //         beforeSend  : () => btn.text('Processing…').prop('disabled', true),
        //         success     : () => location.reload(),
        //         error       : xhr => { alert('Error processing salaries.'); console.error(xhr.responseText); },
        //         complete    : () => btn.text('Process Selected').prop('disabled', false)
        //     });
        // });
        $(document).on('submit', '.process-salaries-form', function (e) {
            e.preventDefault();

            if (!selectedEmployees.size) {
                alert('Please select at least one employee.');
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('pp_id', '{{ $payroll->pp_id }}');
            selectedEmployees.forEach(id => formData.append('selected_employees[]', id));

            Swal.fire({
                title: 'Processing Salaries...',
                html: `
                    <div class="dimmer active">
                        <div class="sk-circle">
                            <div class="sk-circle1 sk-child"></div>
                            <div class="sk-circle2 sk-child"></div>
                            <div class="sk-circle3 sk-child"></div>
                            <div class="sk-circle4 sk-child"></div>
                            <div class="sk-circle5 sk-child"></div>
                            <div class="sk-circle6 sk-child"></div>
                            <div class="sk-circle7 sk-child"></div>
                            <div class="sk-circle8 sk-child"></div>
                            <div class="sk-circle9 sk-child"></div>
                            <div class="sk-circle10 sk-child"></div>
                            <div class="sk-circle11 sk-child"></div>
                            <div class="sk-circle12 sk-child"></div>
                        </div>
                    </div>
                `,
                width: '400px',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    // Ajax request
                    $.ajax({
                        url: '{{ route('process-salaries') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: () => {
                            setTimeout(() => {
                                Swal.close();
                                location.reload();
                            }, 500);
                        },
                        error: xhr => {
                            Swal.fire('Error', 'Salary processing failed!', 'error');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });

        });



        $(document).on('submit', '.unprocess-form', function (e) {
        e.preventDefault();

        const form = $(this);
        const formData = new FormData(this);

        Swal.fire({
            title: 'Unprocessing Salaries...',
            html: `
                <div class="dimmer active">
                    <div class="sk-circle">
                        <div class="sk-circle1 sk-child"></div>
                        <div class="sk-circle2 sk-child"></div>
                        <div class="sk-circle3 sk-child"></div>
                        <div class="sk-circle4 sk-child"></div>
                        <div class="sk-circle5 sk-child"></div>
                        <div class="sk-circle6 sk-child"></div>
                        <div class="sk-circle7 sk-child"></div>
                        <div class="sk-circle8 sk-child"></div>
                        <div class="sk-circle9 sk-child"></div>
                        <div class="sk-circle10 sk-child"></div>
                        <div class="sk-circle11 sk-child"></div>
                        <div class="sk-circle12 sk-child"></div>
                    </div>
                </div>
            `,
            width: '400px',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                // Send Ajax
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: () => {
                        setTimeout(() => {
                            Swal.close();
                            location.reload();
                        }, 500);
                    },
                    error: xhr => {
                        Swal.fire('Error', 'Unprocessing failed!', 'error');
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });



        $(document).ready(function() {
            // Initialize datatable
            datatable({
                tableId: "unprocessed-employee-table",
                url: "{{ route('payroll.process-salary', ['id' => $payroll->pp_id]) }}",
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

    });

</script>

@endsection
