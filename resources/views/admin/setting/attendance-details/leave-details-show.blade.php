<?php
use Carbon\Carbon;
?>
@extends('admin.layout.master')

@section('title', 'Leaves Summary')
@section('css')
<style>
   #daily-attendance-table-dynamic_filter{
     display:none;
   }
   #daily-attendance-table-dynamic_length{
    display:none;
   }
   .filter_border{
     border-radius: 20px !important;
   }
</style>

@endsection

@section('script')
<script>
$(document).ready(function () {
    $(document).ready(function () {
        let table = $('#daily-attendance-table-dynamic').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('leave.details.show', md5($employee->emp_id)) }}",
                data: function (d) {
                    d.from_date = $('#fromDate').val();
                    d.to_date = $('#toDate').val();
                    d.month = $('#monthFilter').val();
                    d.year = $('#yearFilter').val();
                    d.search_filter = $('#searchFilter').val();
                },
                dataSrc: 'data',
            },
            columns: [
                { data: 's_no', name: 's_no' },
                { data: 'leave_type', name: 'leave_type' },
                { data: 'from', name: 'from' },
                { data: 'to', name: 'to' },
                { data: 'days', name: 'days' },
                { data: 'reason', name: 'reason' },
                { data: 'applied_on', name: 'applied_on' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[0, 'asc']],
            responsive: true,
            drawCallback: function (settings) {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        // Trigger redraw on filter change
        $('#fromDate, #toDate, #monthFilter, #yearFilter, #searchFilter').on('change keyup', function () {
            table.draw();
        });

        $('#customLengthMenu').on('change', function () {
            table.page.len($(this).val()).draw();
        });
    });

    $(document).on('click', '.view-leave-btn', function () {
        var empId = $(this).data('empid');
        var startDate = formatDate($(this).data('start'));
        var endDate = formatDate($(this).data('end'));
        var days = $(this).data('days');
        var reason = $(this).data('reason');
        var status = $(this).data('status');
        var type = $(this).data('type');
        var leaveType = $(this).data('leave-type');
        var createdAt = formatDate($(this).data('created-at'));

        var statusMap = {
            140: { label: 'Requested', class: 'warning' },
            157: { label: 'Approved', class: 'success' },
            170: { label: 'Rejected', class: 'danger' },
        };

        var typeMap = {
            235: 'First Half',
            236: 'Second Half',
        };

        var statusObj = statusMap[status] || { label: 'Unknown', class: 'secondary' };
        var statusHtml = `<span class="badge badge-${statusObj.class}">${statusObj.label}</span>`;

        $('#modal-start').text(startDate);
        $('#modal-end').text(endDate);
        $('#modal-days').text(days);
        $('#modal-reason').text(reason);
        $('#modal-status').html(statusHtml);
        $('#modal-leave-type').text(leaveType);
        $('#modal-created-at').text(createdAt);
        $('#modal-segment').text(typeMap[type] || 'Full Day');
    });

    function formatDate(dateStr) {
        var date = new Date(dateStr);
        if (isNaN(date)) return dateStr;
        var options = { day: '2-digit', month: 'long', year: 'numeric' };
        return date.toLocaleDateString('en-GB', options);
    }
});
</script>
@endsection


@section('content')
    <input type="hidden" id="ajaxCall" value="{{ url('/') }}">
    <div class="page-header d-md-flex d-block ">
        <div class="page-leftheader ">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/attendance/leave-management">Leave Management</a></li>
                        <li class="active"><span><b>Leaves Summary</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW -->
    <div class="row">
      <div class="col-xl-3 col-md-12 col-lg-12">
            <div class="">
                <div class="card user-pro-list overflow-hidden">
                    <div class="card-body ">
                        <div class="text-center">
                            <div class="widget-user-image mx-auto text-center">
                                <img class="avatar avatar-xxl brround" alt="img"
                                    src="{{ isset($employee) && $employee->emp_profile_photo
                                        ?  $employee->emp_profile_photo

                                        : asset('assets/imgs/user.png') }}">
                            </div>
                            <div class="pro-user mt-3">
                                <h5 class="pro-user-username text-dark mb-1 fs-16">
                                    {{ $employee->emp_full_name ?? 'N/A' }}</h5>
                                <h6 class="pro-user-desc text-muted fs-12">
                                    {{ $employee->fh_designation->dg_name ?? 'N/A' }}</h6>
                            </div>
                        </div>
                        <h5 class="mb-2 mt-4 font-weight-semibold">Basic Details</h5>
                        <div class="table-responsive">
                            <table class="table text-nowrap">
                                <tbody>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Emp Code</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $employee->emp_code ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Email ID</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $employee->emp_email ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Contact No</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $employee->emp_phone ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Branch</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $employee->fh_branch->br_name ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Department</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span>{{ $employee->fh_department->d_name ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-1">
                                            <span class="w-50">Status</span>
                                        </td>
                                        <td class="py-1">:</td>
                                        <td class="py-1">
                                            <span class="badge {{ $employee && $employee->emp_status == 71 ? 'badge-success-light' : 'badge-warning-light' }}">
                                                {{ $employee && $employee->emp_status == 71 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header  border-0">
                    <h4 class="card-title">Leaves Overview</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="myPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-md-12 col-lg-12">  
            <div class="card">
                <div class="card-header  border-0">
                    <h4 class="card-title">Leaves Summary</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-xl-2 col-lg-2">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-1 search_test" style="width: 50%"
                                    data-length>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2 offset-lg-2">
                            <div class="form-group">
                                <label class="form-label">From:</label>
                                <div class="input-group">
                                    <input class="form-control fc-datepicker filter_border" name="from_date" id="fromDate" type="date">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">To:</label>
                                <div class="input-group">
                                    <input class="form-control fc-datepicker filter_border" name="to_date" id="toDate" type="date">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-lg-2 col-xl-2">
                            <label class="form-label">Month:</label>
                            <select id="monthFilter" class="form-select filter_border">
                                <option value="">All</option>
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Year:</label>
                            <select id="yearFilter" name="year" class="form-select filter_border">
                                <option value="">All</option>
                                @for($y = date('Y'); $y >= 2010; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                
                        {{-- <div class="col-sm-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" name="other_search" placeholder="Search" class="form-control filter_border"
                                 />
                            </div>
                        </div> --}}
                    </div>
                 
                    <div class="table-responsive">
                        <table class="table  table-vcenter text-nowrap table-bordered border-bottom" id="daily-attendance-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th class="border-bottom-0">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="row mt-5">
                        <div class="col-sm-6">
                            <div id="custom-show-entries" data-show-entries></div>
                            <ul data-pagination class="custom-pagination"></ul>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-end">
                            
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    <!-- END ROW -->
   
    <!-- LEAVE APPLICATION MODAL -->
    <div class="modal fade" id="leaveapplictionmodal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Application Details</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td class="font-weight-semibold">Status</td>
                                    <td>:</td>
                                    <td><span id="modal-status"></span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-semibold">Leave Type</td>
                                    <td>:</td>
                                    <td><span id="modal-leave-type"></span></td>
                                </tr>
                            
                                <tr>
                                    <td class="font-weight-semibold">Applied On</td>
                                    <td>:</td>
                                    <td><span id="modal-created-at"></span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-semibold">Leave Days</td>
                                    <td>:</td>
                                    <td><span id="modal-days"></span> days</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-semibold">Day Segment</td>
                                    <td>:</td>
                                    <td><span id="modal-segment"></span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-semibold">Start Date</td>
                                    <td>:</td>
                                    <td><span id="modal-start"></span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-semibold">End Date</td>
                                    <td>:</td>
                                    <td><span id="modal-end"></span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-semibold">Reason</td>
                                    <td>:</td>
                                    <td><span id="modal-reason"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="javascript:void(0);" class="btn btn-primary" data-bs-dismiss="modal">Close</a>
                </div>
            </div>
        </div>
    </div>
    <!-- END LEAVE APPLICATION MODAL -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    /*document.addEventListener('DOMContentLoaded', function() {
        const leaveSummary = @json($leaveSummary);
   
        // Prepare data
        const labels = Object.keys(leaveSummary);
        const remainingValues = labels.map(label => leaveSummary[label].remaining);
        const usedValues = labels.map(label => leaveSummary[label].used);
        const allocatedValues = labels.map(label => leaveSummary[label].allocated);

        // Get canvas and set responsive dimensions
        const canvas = document.getElementById('myPieChart');
        const ctx = canvas.getContext('2d');
        
        // Create donut chart with center hover text
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: remainingValues,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', 
                        '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                cutout: '70%',  // Creates the donut hole
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label;
                                const remaining = context.raw;
                                const used = usedValues[context.dataIndex];
                                const allocated = allocatedValues[context.dataIndex];
                                return [
                                    `${label}`,
                                    `Allocated: ${allocated} days`,
                                    `Used: ${used} days`,
                                    `Remaining: ${remaining} days`
                                ];
                            }
                        }
                    }
                },
                onHover: (event, chartElements) => {
                    const canvasPosition = Chart.helpers.getRelativePosition(event, chart);
                    const centerText = document.getElementById('centerText');
                    
                    if (chartElements.length > 0) {
                        const index = chartElements[0].index;
                        centerText.innerHTML = `
                            <strong>${labels[index]}</strong><br>
                            Used: ${usedValues[index]}<br>
                            Remaining: ${remainingValues[index]}
                        `;
                    } else {
                        centerText.innerHTML = `
                            <strong>Total Leaves</strong><br>
                            Used: ${usedValues.reduce((a,b) => a+b, 0)}<br>
                            Remaining: ${remainingValues.reduce((a,b) => a+b, 0)}
                        `;
                    }
                }
            }
        });

        // Add center text container
        const centerText = document.createElement('div');
        centerText.id = 'centerText';
        centerText.style.position = 'absolute';
        centerText.style.top = '35%';
        centerText.style.left = '50%';
        centerText.style.transform = 'translate(-50%, -50%)';
        centerText.style.textAlign = 'justify';
        centerText.style.fontSize = '12px';
        centerText.style.color = '#666';
        centerText.innerHTML = `
            <strong>Total Leaves</strong><br>
            Used: ${usedValues.reduce((a,b) => a+b, 0)}<br>
            Remaining: ${remainingValues.reduce((a,b) => a+b, 0)}
        `;
        
        const container = canvas.parentNode;
        container.style.position = 'relative';
        container.appendChild(centerText);
    });  */
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const leaveSummary = @json($leaveSummary);
   
        // Prepare data
        const labels = Object.keys(leaveSummary);
        const remainingValues = labels.map(label => leaveSummary[label].remaining);
        const usedValues = labels.map(label => leaveSummary[label].used);
        const allocatedValues = labels.map(label => leaveSummary[label].allocated);

        // Get canvas and set responsive dimensions
        const canvas = document.getElementById('myPieChart');
        const ctx = canvas.getContext('2d');
        
        // Create center text element first
        const centerText = document.createElement('div');
        centerText.id = 'centerText';
        centerText.style.position = 'absolute';
        centerText.style.top = '50%';
        centerText.style.left = '50%';
        centerText.style.transform = 'translate(-50%, -50%)';
        centerText.style.textAlign = 'center';
        centerText.style.fontSize = '14px';
        centerText.style.color = '#333';
        centerText.style.pointerEvents = 'none'; // Critical for tooltip functionality
        centerText.style.lineHeight = '1.5';
        centerText.innerHTML = `
            <div style="font-weight:bold;margin-bottom:5px;">Total Leaves</div>
            <div>Used: ${usedValues.reduce((a,b) => a+b, 0)} days</div>
            <div>Remaining: ${remainingValues.reduce((a,b) => a+b, 0)} days</div>
        `;
        
        // Wrap canvas in a container for positioning
        const container = canvas.parentNode;
        container.style.position = 'relative';
        container.appendChild(centerText);

        // Create donut chart
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: remainingValues,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', 
                        '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { 
                            font: { size: 12 },
                            usePointStyle: true,
                            padding: 20,
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label;
                                const remaining = context.raw;
                                const used = usedValues[context.dataIndex];
                                const allocated = allocatedValues[context.dataIndex];
                                return [
                                    `${label}`,
                                    `Allocated: ${allocated} days`,
                                    `Used: ${used} days`,
                                    `Remaining: ${remaining} days`
                                ];
                            }
                        }
                    }
                },
                onHover: (event, chartElements) => {
                    // Only update center text if tooltip is enabled
                    if (chart.getActiveElements().length > 0) {
                        const activeElement = chart.getActiveElements()[0];
                        const index = activeElement.index;
                        centerText.innerHTML = `
                            <div style="font-weight:bold;margin-bottom:5px;">${labels[index]}</div>
                            <div>Used: ${usedValues[index]} days</div>
                            <div>Remaining: ${remainingValues[index]} days</div>
                        `;
                    } else {
                        centerText.innerHTML = `
                            <div style="font-weight:bold;margin-bottom:5px;">Total Leaves</div>
                            <div>Used: ${usedValues.reduce((a,b) => a+b, 0)} days</div>
                            <div>Remaining: ${remainingValues.reduce((a,b) => a+b, 0)} days</div>
                        `;
                    }
                }
            }
        });

        // Handle window resize to maintain center position
        window.addEventListener('resize', function() {
            centerText.style.top = '50%';
            centerText.style.left = '50%';
        });
    });
</script>

<style>
   

#centerText {
    display:none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255,255,255,0.9);
    padding: 10px 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    min-width: 120px;
    pointer-events: none;
    z-index: 1;
}

</style>

@endsection

@section('script')

    
@endsection
