@extends('admin.layout.master')

@section('title', 'Employee Calendar')

@section('css')
    <style>
        table th:first-of-type,
        table th:nth-child(2) {
            width: auto !important;
        }

        .fc .fc-bg-event .fc-event-title {
            font-size: 1.25em !important;
            font-weight: bold !important;
        }

        .fc-daygrid-block-event .fc-event-title {
            font-size: 12px;
            font-weight: 500;
        }
    </style>
@endsection


@section('content')
    <div class="container-fluid">
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a class="text-white">Employee</a></li>
                <li class="active"><span><b>Employee Calendar</b></span></li>
            </ol>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="emp_id" class="form-label">Employee</label>
                            <select class="form-control" id="emp_id" name="emp_id"
                                onchange="loadCalendar(this.value)" data-placeholder="Select Employee">
                                <option value="">-----Select-----</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->emp_id }}">{{ $employee->emp_full_name }} - {{ $employee->emp_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-9 d-flex justify-content-end align-items-center">
                        <div>
                            <button class="btn btn-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown" style="min-width: 220px;">
                                <li>
                                    <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                        data-bs-toggle="modal" data-bs-target="#empCalendarBulkUpload">
                                        <i class="las la-file-upload"></i> Upload File
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                        href="{{ route('employee.calendar.downloadExcel') }}">
                                        <i class="las la-file-download"></i> Export Format
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if (session('import_errors_blade'))
                        <div class="alert d-flex align-items-center mt-3">
                            <p>There were errors in the import. You can download the error file from the link below:</p>
                            <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()"
                                class="ms-2 mb-4 btn btn-danger">Download Error File</a>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div id='calendar'></div>
            </div>
        </div>

        <!-- Modal -->
        {{-- Assign Shift Modal Start --}}
        <div class="modal fade" id="assignShift" tabindex="-1" aria-labelledby="assignShiftLabel" aria-hidden="true"
            data-bs-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="assignShiftLabel">Assign Shift</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <input type="hidden" id="emp_modal_id" name="emp_modal_id">

                            {{-- Shift --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pst_id" class="form-label">Shift Policy</label>
                                    <select class="form-control select2" id="pst_id" name="pst_id"
                                        data-placeholder="Select Shift">
                                        <option value="">-----Select-----</option>
                                        @foreach ($shiftPolicies as $shiftPolicy)
                                            <option value="{{ $shiftPolicy->pst_id }}">{{ $shiftPolicy->pst_name }} -
                                                {{ $shiftPolicy->pst_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Shift End --}}

                            {{-- Toggle Switch --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="toggle_date_range" class="form-label">Toggle Date</label>
                                    <div class="d-flex gap-2">
                                        <label class="form-check-label" for="toggle_date_range">
                                            Single Date
                                        </label>
                                        <label class="custom-switch">
                                            <input type="checkbox" id="toggle_date_range" name="custom-switch-checkbox1"
                                                class="custom-switch-input" onchange="toggleDateInputs()">
                                            <span class="custom-switch-indicator"></span>
                                        </label>
                                        <label class="form-check-label" for="toggle_date_range">
                                            Date Range
                                        </label>
                                    </div>
                                </div>
                            </div>
                            {{-- Toggle Switch End --}}

                            {{-- Assign Date --}}
                            <div class="col-md-12" id="single_date">
                                <div class="form-group">
                                    <label for="assign_date" class="form-label">Shift Assign Date</label>
                                    <input type="date" class="form-control" id="assign_date" name="assign_date"
                                        placeholder="Select Date" readonly>
                                </div>
                            </div>
                            {{-- Assign Date End --}}

                            {{-- Date Range --}}
                            <div class="row" id="date_range" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date">
                                    </div>
                                </div>
                            </div>
                            {{-- Date Range End --}}

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="saveShift()">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Assign Shift Modal End --}}

        {{-- Bulk Upload Modal Start --}}
        <div class="modal fade" id="empCalendarBulkUpload" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header border-0">
                        <h4 class="modal-title ms-2" id="modal-title">Upload Bulk Shift</h4>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('employee.calendar.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Upload File:</label>
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
                            <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-outline-primary savebtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Bulk Upload Modal End --}}
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/fullcalendar-6.1.19/dist/index.global.min.js') }}"></script>
    <!-- Add the CSS for Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- Add the JS for Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#emp_id').select2();
        });
    </script>
    <script>
        // Global calendar instance
        let calendar;

        /**
         * Initialize or reinitialize the FullCalendar instance
         * @param {string|number} employeeId - ID of the selected employee
         */
        // Initialize calendar only once
        function initCalendar(employeeId) {
            let calendarEl = document.getElementById('calendar');

            // Cleanup existing calendar instance if any
            if (calendar) {
                calendar.destroy(); // Prevent memory leaks
            }

            // Configure and create new calendar instance
            calendar = new FullCalendar.Calendar(calendarEl, {
                // Configure toolbar buttons and layout
                headerToolbar: {
                    left: 'prev,next today', // Navigation buttons
                    center: 'title', // Month/Week/Year display
                    right: 'dayGridMonth,timeGridWeek,timeGridDay' // View options
                },
                navLinks: true, // Enables clicking on dates/days
                editable: false, // Prevents drag-and-drop of existing events
                selectable: true, // Allows date selection for new events
                selectMirror: true, // Shows a preview when selecting dates
                selectConstraint: { // Only restrict selection of past dates
                    start: new Date().toISOString().split('T')[0] // Today's date
                },
                selectOverlap: true, // Allow selecting dates that have events

                /**
                 * Handler for date selection - Creates new events
                 * @param {Object} arg - Contains selected date range info
                 */
                select: function select(arg) {
                    $("#assign_date").val(arg.startStr);
                    $("#start_date").val(arg.startStr);
                    $("#end_date").val(arg.endStr);
                    $("#emp_modal_id").val(employeeId);
                    $('#assignShift').modal('show');

                    calendar.unselect(); // Clear selection
                },

                eventDidMount: function(info) {
                    if (info.event.extendedProps.resolved_from) {
                        // Append tooltip text in the DOM
                        info.el.setAttribute(
                            "data-tooltip",
                            "Shift Source: " + info.event.extendedProps.resolved_from + " Assignment"
                        );

                        // Fallback to default browser tooltip
                        info.el.setAttribute(
                            "title",
                            "Shift Source: " + info.event.extendedProps.resolved_from + " Assignment"
                        );
                    }
                },

                /**
                 * Handler for event clicks - Handles event deletion
                 * @param {Object} arg - Contains clicked event info
                 */
                eventClick: function eventClick(arg) {
                    if (arg.event.extendedProps.prefetched) {
                        // Prevent deletion of prefetched events
                        return;
                    }
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteAssignedShift(
                                arg.event.extendedProps.sc_id,
                                employeeId,
                                window.fullCalendarRange.start,
                                window.fullCalendarRange.end
                            );
                        }
                    })
                },

                /**
                 * Triggered when calendar dates are changed (navigation/view change)
                 * Fetches events for the new date range
                 * @param {Object} info - Contains new date range info
                 */
                datesSet: function(info) {
                    // Store the current full date range of this calendar view
                    window.fullCalendarRange = {
                        start: info.start.toISOString(),
                        end: info.end.toISOString()
                    };
                    fetchEvents(employeeId, window.fullCalendarRange.start, window.fullCalendarRange.end);
                }
            });

            calendar.render(); // Display the calendar
            calendar.setOption('height', 700);
        }

        /**
         * Fetch employee events from the server
         * @param {string|number} employeeId - ID of the employee
         * @param {string} startDate - ISO string of start date
         * @param {string} endDate - ISO string of end date
         */
        function fetchEvents(employeeId, startDate, endDate) {
            $.ajax({
                url: '{{ route('employee-calendar.create') }}',
                method: 'GET',
                data: {
                    employee_id: employeeId,
                    start: startDate,
                    end: endDate,
                },
                success: function(res) {
                    // Clear existing events
                    calendar.removeAllEvents();
                    calendar.addEventSource(res.events); // Add new events
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching events:', error);
                }
            });
        }

        // Called when employee is selected
        function loadCalendar(employeeId) {
            if (!employeeId) {
                if (calendar) calendar.destroy();
                return;
            }
            initCalendar(employeeId);
        }

        function toggleDateInputs() {
            const isChecked = document.getElementById('toggle_date_range').checked;
            document.getElementById('single_date').style.display = isChecked ? 'none' : '';
            document.getElementById('date_range').style.display = isChecked ? '' : 'none';
        }
        // Optionally, call on page load to set correct state
        document.addEventListener('DOMContentLoaded', function() {
            toggleDateInputs();
        });

        function saveShift() {
            let empId = $('#emp_modal_id').val();
            let pstId = $('#pst_id').val();
            let assignDate = $('#assign_date').val();
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            console.log(endDate);

            let toggle = $('#toggle_date_range').is(':checked');

            if (!empId || !pstId) {
                Swal.fire('Error', 'Employee and Shift Policy are required.', 'error');
                return;
            }
            $.ajax({
                url: '{{ route('employee-calendar.store') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    employee_id: empId,
                    pst_id: pstId,
                    assign_date: assignDate,
                    start_date: startDate,
                    end_date: endDate,
                    is_date_range: toggle ? 1 : 0,
                },
                success: function(res) {
                    console.log(res);
                    if (res.status == 'success') {
                        let shift = $('#pst_id option:selected').text();

                        if (shift) {
                            // calendar.addEventSource(res.events); // Add new events
                            fetchEvents(empId, window.fullCalendarRange.start,
                                window.fullCalendarRange.end);
                            $('#assignShift').modal('hide');
                            $('#pst_id').val('').trigger('change');
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: res.message,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving shift:', error);
                }
            });
        }

        function deleteAssignedShift(id, employeeId, start, end) {
            $.ajax({
                url: "{{ route('employee-calendar.destroy', ':id') }}".replace(':id', id),
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(res) {
                    console.log(res);
                    if (res.status == 'success') {
                        fetchEvents(employeeId, start, end);
                    } else if (res.status == 'warning') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning',
                            html: res.message,
                            confirmButtonText: 'OK'
                        })
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting assigned policy :',
                        error);
                }
            });
        }
    </script>
@endsection
