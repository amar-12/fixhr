@extends('admin.layout.master')

@section('title', 'Attendance - Add/Edit')

@section('css')
<style>
    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
    }

    input.form-control {
        text-align: center;
    }

    /* Reduce table header font size */
    .table thead th {
        font-size: 14px;
        /* Adjust header text size */
        padding: 5px;
        /* Reduce header padding */
    }

    /* Reduce table cell padding */
    .table td {
        padding: 5px !important;
        font-size: 13px;
        /* Adjust cell text size */
    }

    /* Make table more compact */
    .table {
        border-collapse: collapse;
        width: auto;
        /* table width content ke hisaab se */
        table-layout: auto;
    }

    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
        padding: 3px !important;
        /* Reduce padding further */
        font-size: 12px;
        /* Make text slightly smaller */
        white-space: nowrap;
        /* Prevent text wrapping */
    }

    .table thead th {
        font-size: 12px;
        /* Reduce header font size */
        padding: 4px !important;
        /* Reduce header padding */
    }

    .form-control {
        padding: 1px 2px;
        /* Minimal padding */
        font-size: 11px;
        /* Small input text */
        height: 25px;
        /* Reduce input height */
        text-align: center;
    }

    .table tbody tr:hover {
        background-color: #f2f2f2;
        /* Light blue background */
        cursor: pointer;
    }

    .table tbody tr {
        transition: background-color 0.3s ease;
    }

    .m-width {
        min-width: 55px !important;
    }

</style>
@endsection

@section('content')
<div class="page-header d-md-flex d-block">
    <div class="page-leftheader">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="active"><span>Attendance Vault</span></li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-xl-12 col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <h4 class="card-title">Employee Attendance Summary</h4>
            </div>
            <br>
            <div class="card">
                @php
                $firstPayroll = $attendanceDetails[0]['payroll'] ?? null;
                @endphp

                @if($firstPayroll)
                <div class="card">
                    <div class="card-body">
                        <div class="row m-2">
                            <div class="col-md-4">
                                <h6><strong>Payroll Name:</strong> {{ $firstPayroll->pp_name }}</h6>
                            </div>
                            <div class="col-md-4">
                                <h6><strong>Month:</strong>
                                    {{ \Carbon\Carbon::parse($firstPayroll->pp_start_date)->format('F Y') }}
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <h6><strong>Duration:</strong>
                                    {{ \Carbon\Carbon::parse($firstPayroll->pp_start_date)->format('d-m-Y') }} to
                                    {{ \Carbon\Carbon::parse($firstPayroll->pp_end_date)->format('d-m-Y') }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="card-body">
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(isset($attendanceDetails) && count($attendanceDetails) > 0)
                <form action="{{ route('freeze.payroll.attendance') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Emp Code</th>
                                    <th>Emp Name</th>
                                    <th class="m-width">Month Days</th>
                                    <th class="m-width">Present</th>
                                    <th class="m-width">ABS</th>
                                    <th class="m-width">WO</th>
                                    <th class="m-width">HD</th>
                                    <th class="m-width">Leave</th>
                                    <th class="m-width">HO</th>
                                    <th class="m-width">UPL</th>
                                    <th class="m-width">MSP</th>
                                    <th class="m-width">
                                        <button type="button" id="toggleLateCountBtn" class="btn btn-sm btn-outline-primary mt-1" onclick="toggleLateCounts()">Clear All</button><br><br>
                                        Late
                                    </th>
                                    <th class="m-width">Over Time</th>
                                    <th class="m-width">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @foreach($attendanceDetails as $attendance)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $attendance['employee']->emp_code }}</td>
                                    <td>{{ $attendance['employee']->emp_full_name }}</td>

                                    <input type="hidden" name="attendance[{{ $attendance['employee']->emp_id }}][payroll_id]" value="{{ $attendance['payroll_id'] }}">
                                    <input type="hidden" name="attendance[{{ $attendance['employee']->emp_id }}][branch_id]" value="{{ $attendance['employee']->emp_br_id }}">
                                    <input type="hidden" name="attendance[{{ $attendance['employee']->emp_id }}][dept_id]" value="{{ $attendance['employee']->emp_d_id }}">
                                    <input type="hidden" name="attendance[{{ $attendance['employee']->emp_id }}][business_id]" value="{{ $attendance['employee']->emp_b_id }}">

                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][total_days]" step="0.1" value="{{ $attendance['attendance_summary']['total_days'] }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][presentCount]" step="0.1" value="{{ $attendance['attendance_summary']['presentCount'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][absentCount]" step="0.1" value="{{ $attendance['attendance_summary']['absentCount'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][weekOffCount]" step="0.1" value="{{ $attendance['attendance_summary']['weekOffCount'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][halfDayCount]" step="0.1" value="{{ $attendance['attendance_summary']['halfDayCount'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][leaveCount]" step="0.1" value="{{ $attendance['attendance_summary']['leaveCount'] }}" oninput="validateInput(this)" readonly>
                                    </td>

                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][holidayCount]" step="0.1" value="{{ $attendance['attendance_summary']['holidayCount'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][UPL]" step="0.1" value="{{ $attendance['attendance_summary']['UPL'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][missedPunchCount]" step="0.1" value="{{ $attendance['attendance_summary']['missedPunchCount'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][lateCount]" step="0.1" value="{{ $attendance['attendance_summary']['lateCount'] }}" oninput="validateInput(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][overtimeCount]" step="0.1" value="{{ $attendance['attendance_summary']['overtimeCount'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                    <input type="hidden" name="attendance_hidden[{{ $attendance['employee']->emp_id }}][overtimeHours]" value="{{ $attendance['attendance_summary']['overtimeHours'] }}">
                                    <td>
                                        <input type="number" class="form-control" name="attendance[{{ $attendance['employee']->emp_id }}][total]" step="0.1" value="{{ $attendance['attendance_summary']['total'] }}" oninput="validateInput(this)" readonly>
                                    </td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" id="freezeBtn" class="btn btn-outline-danger">Freeze</button>
                    </div>
                </form>
                @else
                <div class="alert alert-warning">No attendance records found.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

<script>
    // Trigger validation on page load and when input changes
    $(document).ready(function() {
        validateTotalDays();

        $(document).on('input', 'input[name*="[total]"]', function() {
            validateTotalDays();
        });
    });

</script>


<script>
    function validateInput(input) {
        if (input.value < 0) {
            input.value = 0; // Reset to 0 if negative value entered
        }
    }

    function validateTotalDays() {
        let isValid = true;
        let errorMessage = "";

        $('#attendanceTable tbody tr').each(function() {
            const totalDays = parseFloat($(this).find('input[name*="[total_days]"]').val()) || 0;
            const total = parseFloat($(this).find('input[name*="[total]"]').val()) || 0;

            if (total > totalDays) {
                isValid = false;

                errorMessage = "⚠ Total cannot be greater than Month Days!";
            } else {
                $(this).css('background-color', ''); // Reset background
            }
        });

        if (!isValid) {
            $('#freezeBtn').prop('disabled', true);

            if ($('#totalError').length === 0) {
                $('<div id="totalError" class="alert alert-warning mt-2">' +
                    '<i class="fa fa-exclamation-triangle"></i> ' +
                    '<strong>Warning:</strong> Total days cannot be greater than month days!' +
                    '</div>').insertBefore('#attendanceTable');
            }
        } else {
            $('#freezeBtn').prop('disabled', false);
            $('#totalError').remove();
        }

    }

    $(document).ready(function() {
        // Initialize DataTable once
        // var table = $('.table').DataTable({
        //     dom: 'tp', // Only table and pagination
        //     retrieve: true
        // });

        var table = $('#attendanceTable').DataTable({
            // dom: 'Bfrtip',   // 'f' = search box visible
            dom: 'ftp'
            , retrieve: true
        });

        // Set min=0 to all number inputs
        $('input[type="number"]').attr('min', 0);

        // Handle form submit
        $('form').submit(function(event) {
            event.preventDefault(); // Stop default submission

            var allData = [];

            // Collect data from all rows (across all pages)
            table.rows().every(function() {
                var row = $(this.node());
                var empId = row.find('input[name^="attendance"]').first().attr('name').match(/\d+/)[0];

                var rowData = {
                    emp_id: empId
                    , payroll_id: row.find(`input[name="attendance[${empId}][payroll_id]"]`).val()
                    , branch_id: row.find(`input[name="attendance[${empId}][branch_id]"]`).val()
                    , dept_id: row.find(`input[name="attendance[${empId}][dept_id]"]`).val()
                    , business_id: row.find(`input[name="attendance[${empId}][business_id]"]`).val()
                    , total_days: row.find(`input[name="attendance[${empId}][total_days]"]`).val()
                    , presentCount: row.find(`input[name="attendance[${empId}][presentCount]"]`).val()
                    , halfDayCount: row.find(`input[name="attendance[${empId}][halfDayCount]"]`).val()
                    , leaveCount: row.find(`input[name="attendance[${empId}][leaveCount]"]`).val()
                    , absentCount: row.find(`input[name="attendance[${empId}][absentCount]"]`).val()
                    , weekOffCount: row.find(`input[name="attendance[${empId}][weekOffCount]"]`).val()
                    , holidayCount: row.find(`input[name="attendance[${empId}][holidayCount]"]`).val()
                    , UPL: row.find(`input[name="attendance[${empId}][UPL]"]`).val()
                    , missedPunchCount: row.find(`input[name="attendance[${empId}][missedPunchCount]"]`).val()
                    , lateCount: row.find(`input[name="attendance[${empId}][lateCount]"]`).val()
                    , overtimeCount: row.find(`input[name="attendance[${empId}][overtimeCount]"]`).val()
                    , total: row.find(`input[name="attendance[${empId}][total]"]`).val()
                    , overtimeHours: row.find(`input[name="attendance_hidden[${empId}][overtimeHours]"]`).val()
                };

                allData.push(rowData);
            });

            // Add hidden input to submit full JSON
            if ($('#all_attendance_data').length === 0) {
                $('<input>').attr({
                    type: 'hidden'
                    , id: 'all_attendance_data'
                    , name: 'all_attendance_data'
                    , value: JSON.stringify(allData)
                }).appendTo('form');
            } else {
                $('#all_attendance_data').val(JSON.stringify(allData));
            }

            this.submit(); // Now submit the form
        });
    });

</script>
<script>
    let lateCleared = false;
    const originalLateValues = {};

    function toggleLateCounts() {
        const table = $('#attendanceTable').DataTable(); // replace with your actual table ID
        const button = document.getElementById('toggleLateCountBtn');

        table.rows().every(function() {
            const row = this.node();
            const input = row.querySelector('input[name*="[lateCount]"]');
            if (input) {
                const key = input.name;
                if (!lateCleared) {
                    // Store original if not already stored
                    if (!(key in originalLateValues)) {
                        originalLateValues[key] = input.value;
                    }
                    input.value = 0;
                } else {
                    if (key in originalLateValues) {
                        input.value = originalLateValues[key];
                    }
                }
            }
        });

        lateCleared = !lateCleared;
        button.textContent = lateCleared ? "Rollback" : "Clear All";
        button.classList.toggle("btn-outline-primary", !lateCleared);
        button.classList.toggle("btn-outline-danger", lateCleared);
    }

    // Optional: if you redraw the table (e.g., change page), reapply state
    $(document).ready(function() {
        const table = $('#attendanceTable').DataTable();

        table.on('draw', function() {
            if (lateCleared) {
                table.rows().every(function() {
                    const row = this.node();
                    const input = row.querySelector('input[name*="[lateCount]"]');
                    if (input) input.value = 0;
                });
            }
        });
    });

</script>






@endsection
