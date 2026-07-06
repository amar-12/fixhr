@extends('admin.layout.master')
@section('title', 'Leave Calendar')

@section('css')
    <style>
        .leave-details {
            font-size: 14px;
        }

        hr {
            border-top: 6px solid var(--primary);
        }

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

        .dataTables_length .select2 {
            width: 60% !important;
        }

        .fc-h-event {
            cursor: pointer;
        }
    </style>
@endsection

@section('content')

    {{-- Breadcrumbs Start --}}
    <div class="p-0 my-1">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a class="text-white">Leave</a></li>
            <li class="active"><span><b>Leave Calendar</b></span></li>
        </ol>
    </div>
    {{-- Breadcrumbs End --}}

    {{-- Employee Leave Calendar --}}
    <div class="card mt-4">
        <div class="card-body">

            {{-- Search Employee --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="emp_id" class="form-label">Employee</label>
                        <select class="form-control" id="emp_id" name="emp_id" onchange="loadCalendar(this.value)"
                            data-placeholder="Select Employee">
                            <option value="">-----Select-----</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" id="leave-calendar-section" style="display: none;">
                {{-- Details --}}
                <div class="col-md-12">

                    <div class="row">

                        {{-- Employee Profile --}}
                        <div class="col-xl-5 main-proifle px-5 pt-0">
                            <div class="box-widget widget-user">
                                <div class="widget-user-image d-sm-flex align-items-center">
                                    <span class="avatar"
                                        style="background-image: url({{ url('assets/images/users/7.jpg') }})">
                                        <span class="avatar-status bg-green"></span>
                                    </span>
                                    <div class="ms-sm-4 mt-4">
                                        <h4 class="pro-user-username mb-3 font-weight-semibold"></h4>
                                        <div class="d-flex mb-2">
                                            <span class="ri-building-line icons"></span>
                                            <div class="h6 mb-0 ms-3 mt-1"></div>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="ri-mail-line icons"></span>
                                            <div class="h6 mb-0 ms-3 mt-1"></div>
                                        </div>
                                        <div class="d-flex">
                                            <span class="ri-phone-line icons"></span>
                                            <div class="h6 mb-0 ms-3 mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Leave Details --}}
                        <div class="col-xl-7 col-lg-9 p-5 leave-details">
                            <div class="row">
                                <div class="col-md-6 row">
                                    <div class="col-md-6 fw-bold">Leave Type</div>
                                    <div class="col-md-6">: <span id="detail_category" class="ps-1"></span></div>
                                </div>
                                <div class="col-md-6 row">
                                    <div class="col-md-6 fw-bold">Status</div>
                                    <div class="col-md-6">: <span id="detail_status" class="ps-1"></span></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 row">
                                    <div class="col-md-6 fw-bold">From</div>
                                    <div class="col-md-6">: <span id="detail_from" class="ps-1"></span></div>
                                </div>

                                <div class="col-md-6 row">
                                    <div class="col-md-6 fw-bold">Leave Days</div>
                                    <div class="col-md-6">: <span id="detail_days" class="ps-1"></span></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 row">
                                    <div class="col-md-6 fw-bold">To</div>
                                    <div class="col-md-6">: <span id="detail_to" class="ps-1"></span></div>
                                </div>

                                <div class="col-md-6 row">
                                    <div class="col-md-6 fw-bold">Applied On</div>
                                    <div class="col-md-6">: <span id="detail_applied_on" class="ps-1"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 row">
                                    <div class="col-md-3 fw-bold">Remark</div>
                                    <div class="col-md-9 ps-1">: <span id="detail_reason" class="ps-1"></span></div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                {{-- Calendar --}}
                <div class="col-md-5">
                    <h4>Calendar</h4>
                    <hr class="my-0">
                    <div id="calendar" class="mt-4"></div>
                </div>

                {{-- Leave History --}}
                <div class="col-md-7">
                    <h4>Leave History</h4>
                    <hr class="my-0">
                    <div class="datatable mt-3">
                        <table class="table display table-vcenter table-hover text-wrap border-bottom"
                            id="leave-history-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Category</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Applied On</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                </hr>

                {{-- Leave Category --}}
                <div>
                    <ul class="pt-5 pb-3 d-flex justify-content-around gap-3 text-danger">
                        @foreach ($leaveCat as $cat)
                            <li>{{ $cat->m_name }}</li>
                        @endforeach
                    </ul>
                </div>
                {{-- Leave Category End --}}
            </div>
        </div>
    </div>

    {{-- Modal Start --}}
    <div class="modal fade" id="addLeave" tabindex="-1" aria-labelledby="addLeaveLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addLeaveLabel">Add Leave</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        onclick="$('#leaveForm').trigger('reset');">x</button>
                </div>
                <div class="modal-body">
                    <form method="post" id="leaveForm">
                        <div class="row">

                            {{-- Day Type --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="lvr_leave_day_type_id" class="form-label">
                                        Leave Day Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="lvr_leave_day_type_id" name="leave_day_type_id"
                                        data-placeholder="Select Day Type"
                                        onchange="toggleDayTypeOptions(this.value) && validateFields('lvr_leave_day_type_id')">
                                        <option value="" selected disabled>-----Select-----</option>
                                        @foreach ($leaveType as $type)
                                            <option value="{{ $type->m_id }}">{{ $type->m_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Day Type End --}}

                            {{-- Day Segment --}}
                            <div class="col-md-12" id="segement-div" style="display: none">
                                <div class="form-group">
                                    <label for="lvr_day_segment_id" class="form-label">
                                        Day Segment <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="lvr_day_segment_id" name="leave_day_segment_id"
                                        data-placeholder="Select Day Segment"
                                        onchange="validateFields('lvr_day_segment_id')">
                                        <option value="" selected disabled>-----Select-----</option>
                                        @foreach ($daySegment as $segment)
                                            <option value="{{ $segment->m_id }}">{{ $segment->m_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Day Segment End --}}

                            {{-- Leave Category --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="lvr_cat_type_id" class="form-label">Leave Category <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="lvr_cat_type_id" name="leave_category_id"
                                        data-placeholder="Select Leave Category"
                                        onchange="validateFields('lvr_cat_type_id')">
                                        <option value="" selected disabled>-----Select-----</option>
                                    </select>
                                </div>
                            </div>
                            {{-- Leave Category End --}}

                            {{-- Leave Date --}}
                            <div class="col-md-12" id="single_date">
                                <div class="form-group">
                                    <label for="lvr_date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="lvr_date" name="lvr_date"
                                        placeholder="Select Date" readonly oninput="validateFields('lvr_date')">
                                </div>
                            </div>
                            {{-- Leave Date End --}}

                            {{-- Date Range --}}
                            <div class="row" id="date_range" style="display: none">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lvr_start_date" class="form-label">
                                            Start Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="lvr_start_date"
                                            name="leave_start_date" oninput="validateFields('lvr_start_date')">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lvr_end_date" class="form-label">
                                            End Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="lvr_end_date"
                                            name="leave_end_date" oninput="validateFields('lvr_end_date')">
                                    </div>
                                </div>
                            </div>
                            {{-- Date Range End --}}

                            {{-- Remark --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="lvr_reason" class="form-label">Remark</label>
                                    <textarea name="reason" id="lvr_reason" rows="3" class="form-control"></textarea>
                                </div>
                            </div>
                            {{-- Remark End --}}

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="$('#leaveForm').trigger('reset');">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveLeave()">Save Leave</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal End --}}
@endsection

@section('script')
    <script src="{{ asset('assets/js/common_select2.js') }}"></script>
    <script src="{{ asset('assets/fullcalendar-6.1.19/dist/index.global.min.js') }}"></script>
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
                    right: 'dayGridMonth' // View options
                },
                navLinks: true, // Enables clicking on dates/days
                editable: false, // Prevents drag-and-drop of existing events
                selectable: true, // Allows date selection for new events
                selectMirror: true, // Shows a preview when selecting dates
                selectOverlap: false, // Allow selecting dates that have events

                /**
                 * Handler for date selection - Creates new events
                 * @param {Object} arg - Contains selected date range info
                 */
                select: function select(arg) {
                    $("#lvr_date").val(arg.startStr);
                    $("#lvr_start_date").val(arg.startStr);
                    $("#lvr_end_date").val(arg.startStr);
                    $("#addLeave").modal("show");
                    calendar.unselect(); // Clear selection
                },

                /**
                 * Handler for event clicks - Handles event deletion
                 * @param {Object} arg - Contains clicked event info
                 */
                eventClick: function eventClick(arg) {
                    // FullCalendar uses an exclusive 'end' for all-day events.
                    // If the event is allDay and an end exists, display an inclusive end by subtracting one day.
                    const fmtOptions = { year: 'numeric', month: 'long', day: 'numeric' };
                    const startDt = arg.event.start ? new Date(arg.event.start) : null;
                    let endDt = arg.event.end ? new Date(arg.event.end) : null;
                    if (endDt && arg.event.allDay) {
                        // subtract one day to make the displayed end inclusive
                        endDt.setDate(endDt.getDate() - 1);
                    }
                    const startStr = startDt ? startDt.toLocaleDateString('en-US', fmtOptions) : '';
                    const endStr = endDt ? endDt.toLocaleDateString('en-US', fmtOptions) : startStr;

                    Swal.fire({
                        title: `Are you sure?`,
                        text: `You want to delete this leave - ${arg.event.title} From - ${startStr} To - ${endStr}. You won't be able to revert this!`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        $.ajax({
                            type: "DELETE",
                            url: "{{ route('employee-leave-calendar.destroy', ':id') }}".replace(':id', arg.event.id),
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: arg.event.id,
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire(
                                        "Deleted!",
                                        response.message || "Leave has been deleted.",
                                        "success"
                                    );
                                    calendar.refetchEvents();
                                    // Ensure the leave history table is present and load data
                                    initLeaveHistoryTable();
                                    if (leaveHistoryTable) leaveHistoryTable.ajax.reload();
                                } else {
                                    Swal.fire(
                                        "Warning!",
                                        response.message || "Leave could not be deleted.",
                                        "info"
                                    );
                                }
                            }
                        });
                    });
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

                    // Emit a DOM event so other UI (like a DataTable) can reload for this range
                    document.dispatchEvent(new CustomEvent('calendarRangeChanged', {
                        detail: {
                            start: window.fullCalendarRange.start,
                            end: window.fullCalendarRange.end,
                            emp_id: employeeId
                        }
                    }));
                },

                // Event source: call the EmpLeaveCalendar@show endpoint and use its `calendar` key
                events: function(fetchInfo, successCallback, failureCallback) {
                    // Build URL safely so Blade won't try to resolve a literal ':id'
                    const url = "{{ route('employee-leave-calendar.show', ':id') }}".replace(':id', employeeId);
                    const params = {
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                    };

                    $.ajax({
                        url: url,
                        data: params,
                        method: 'GET',
                        success: function(response) {
                            // Expecting { employee, table: {...}, calendar: [...] }
                            if (response && Array.isArray(response.calendar)) {
                                successCallback(response.calendar);
                            } else {
                                successCallback([]);
                            }
                        },
                        error: function(err) {
                            console.error('Failed to load calendar events', err);
                            failureCallback(err);
                        }
                    });
                }
            });

            calendar.render(); // Display the calendar
            // calendar.setOption('height', 700);
        }

        $(document).ready(function() {
            initManagerSelect2('#emp_id', 'Select Employee', ['emp_email', 'emp_phone']);
        });

        // Leave history DataTable instance
        let leaveHistoryTable;

        function initLeaveHistoryTable() {
            if ($.fn.DataTable.isDataTable('#leave-history-table')) {
                    leaveHistoryTable = $('#leave-history-table').DataTable();

                    // Ensure handlers are attached even if table was already initialized
                    // (prevent duplicate handlers by namespacing or removing previous ones)
                    $('#leave-history-table tbody').off('click.leaveRow').on('click.leaveRow', 'tr', function() {
                        if (!leaveHistoryTable) return;
                        const rowData = leaveHistoryTable.row(this).data();
                        if (!rowData) return;

                        $('#leave-history-table tbody tr').removeClass('table-primary');
                        $(this).addClass('table-primary');
                        renderLeaveDetails(rowData);
                    });

                    leaveHistoryTable.off('draw.selectFirst').on('draw.selectFirst', function() {
                        const firstRow = $('#leave-history-table tbody tr:visible').first();
                        if (firstRow && firstRow.length) {
                            $('#leave-history-table tbody tr').removeClass('table-primary');
                            firstRow.addClass('table-primary');
                            const rowData = leaveHistoryTable.row(firstRow).data();
                            if (rowData) renderLeaveDetails(rowData);
                            try { firstRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { firstRow[0].scrollIntoView(); }
                        }
                    });

                    return;
                }

            leaveHistoryTable = $('#leave-history-table').DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                ordering: true,
                ajax: function(data, callback, settings) {
                    const empId = $('#emp_id').val();
                    const range = window.fullCalendarRange || {};
                    if (!empId) {
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    // Forward DataTables parameters to the events endpoint
                    const payload = Object.assign({}, data, {
                        emp_id: empId,
                        start: range.start,
                        end: range.end,
                        mode: 'list'
                    });

                    $.ajax({
                        // Call the employee show endpoint and map its `table` result to DataTables format
                        url: "{{ route('employee-leave-calendar.show', ':id') }}".replace(':id', empId),
                        method: 'GET',
                        data: {
                            start: range.start,
                            end: range.end
                        },
                        success: function(res) {
                            try {
                                const table = (res && res.table) ? res.table : {
                                    data: [],
                                    recordsTotal: 0
                                };
                                const mapped = {
                                    draw: data.draw,
                                    recordsTotal: table.recordsTotal || (table.data ? table.data
                                        .length : 0),
                                    recordsFiltered: table.recordsTotal || (table.data ? table
                                        .data.length : 0),
                                    data: table.data || []
                                };
                                const leaveCatSelect = $("#lvr_cat_type_id");
                                const leaveBalance = res.leaveBalance;
                                const UplLeave = res.UplLeave && res.UplLeave.length ? res.UplLeave[0] : null;
                                leaveCatSelect.empty();
                                leaveCatSelect.append(
                                    $('<option>', {
                                        value: '',
                                        text: `-----Select-----`,
                                        disabled: true,
                                        selected: true
                                    })
                                );
                                $.each(leaveBalance.result, function(_, leave) {
                                    const category = leave.category_master_detail[0];
                                    const balance = parseFloat(leave.total_balance_remaining_leave);
                                    // append option, disable if balance is 0
                                    leaveCatSelect.append(
                                        $('<option>', {
                                            value: category.id,
                                            text: `${category.name} - ${balance}`,
                                            disabled: balance === 0
                                        })
                                    );
                                });
                                if (UplLeave && UplLeave.id && UplLeave.name) {
                                    leaveCatSelect.append(
                                        $('<option>', {
                                            value: UplLeave.id,
                                            text: UplLeave.name
                                        })
                                    );
                                }
                                callback(mapped);
                            } catch (e) {
                                callback({
                                    draw: data.draw,
                                    recordsTotal: 0,
                                    recordsFiltered: 0,
                                    data: []
                                });
                            }
                        },
                        error: function() {
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: []
                            });
                        }
                    });
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            // Calculate serial number for current page
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'category'
                    },
                    {
                        data: 'from'
                    },
                    {
                        data: 'to'
                    },
                    {
                        data: 'days'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'applied_on'
                    },
                    {
                        data: 'approved_by'
                    }
                ]
            });

            // Attach click handler to rows to show details (namespace events to avoid duplicates)
            $('#leave-history-table tbody').off('click.leaveRow').on('click.leaveRow', 'tr', function() {
                if (!leaveHistoryTable) return;
                const rowData = leaveHistoryTable.row(this).data();
                if (!rowData) return;

                // Highlight selected row
                $('#leave-history-table tbody tr').removeClass('table-primary');
                $(this).addClass('table-primary');

                // Render details into panel
                renderLeaveDetails(rowData);
            });

            // When the table draws, auto-select the first visible row and scroll to it
            leaveHistoryTable.off('draw.selectFirst').on('draw.selectFirst', function() {
                const firstRow = $('#leave-history-table tbody tr:visible').first();
                if (firstRow && firstRow.length) {
                    // Trigger selection (apply class and render details)
                    $('#leave-history-table tbody tr').removeClass('table-primary');
                    firstRow.addClass('table-primary');

                    const rowData = leaveHistoryTable.row(firstRow).data();
                    if (rowData) renderLeaveDetails(rowData);

                    // Smooth scroll into view within the table's container
                    try {
                        firstRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } catch (e) {
                        // fallback: jump without smooth behavior
                        firstRow[0].scrollIntoView();
                    }
                }
            });
        }

        // Attach click handler to rows to show details
        $('#leave-history-table tbody').on('click', 'tr', function() {
            if (!leaveHistoryTable) return;
            const rowData = leaveHistoryTable.row(this).data();
            if (!rowData) return;

            // Highlight selected row
            $('#leave-history-table tbody tr').removeClass('table-primary');
            $(this).addClass('table-primary');

            // Render details into panel
            renderLeaveDetails(rowData);
        });

        // Render selected leave details into the details panel
        function renderLeaveDetails(data) {
            // data is expected to be the row object used in DataTable mapping
            $('#detail_category').text(data.category || '');
            $('#detail_balance').text(data.balance ?? '');
            $('#detail_from').text(data.from || '');
            $('#detail_to').text(data.to || '');
            $('#detail_days').text(data.days ?? '');
            $('#detail_status').text(data.status || '');
            $('#detail_reason').text(data.reason || '');
            $('#detail_applied_on').text(data.applied_on || '');
        }

        // When employee selection changes, initialize calendar and table and load data
        function loadCalendar(employeeId) {
            if (!employeeId) {
                if (calendar) calendar.destroy();
                $('#leave-calendar-section').hide();
                return;
            }

            $.ajax({
                url: "{{ route('employee-leave-calendar.show', ':id') }}".replace(':id', employeeId),
                type: 'GET',
                success: function(response) {
                    $('.pro-user-username').text(response.employee.emp_full_name);
                    $('.box-widget .avatar').css('background-image', 'url(' + response.employee
                        .emp_profile_photo + ')');
                    $('.box-widget .icons').eq(0).next().text(response.employee.emp_code);
                    $('.box-widget .icons').eq(1).next().text(response.employee.emp_email);
                    $('.box-widget .icons').eq(2).next().text(response.employee.emp_phone);
                    const leaveCatSelect = $("#lvr_cat_type_id");
                    const leaveBalance = response.leaveBalance;
                    leaveCatSelect.empty();
                    leaveCatSelect.append(
                        $('<option>', {
                            value: '',
                            text: `-----Select-----`,
                            disabled: true,
                            selected: true
                        })
                    );
                    $.each(leaveBalance.result, function(_, leave) {
                        const category = leave.category_master_detail[0];
                        const balance = parseFloat(leave.total_balance_remaining_leave);
                        // append option, disable if balance is 0
                        leaveCatSelect.append(
                            $('<option>', {
                                value: category.id,
                                text: `${category.name} - ${balance}`,
                                disabled: balance === 0
                            })
                        );
                    });
                },
                error: function() {

                }
            });
            $('#leave-calendar-section').show();
            initCalendar(employeeId);

            // Ensure the leave history table is present and load data
            initLeaveHistoryTable();
            if (leaveHistoryTable) leaveHistoryTable.ajax.reload();
        }

        // Listen for calendarRangeChanged and reload the leave history table
        document.addEventListener('calendarRangeChanged', function(e) {
            if (!leaveHistoryTable) initLeaveHistoryTable();
            if (leaveHistoryTable) leaveHistoryTable.ajax.reload(null, false);
        });

        let fieldsTovalidate = ['lvr_leave_day_type_id', 'lvr_cat_type_id', 'lvr_date'];

        /**
         * Validate required fields by id(s).
         * Supports date-range validation when both 'lvr_start_date' and 'lvr_end_date' are provided.
         * Returns true if all validations pass, false otherwise.
         */
        function validateFields(...ids) {
            // Normalize: allow passing an array
            if (ids.length === 1 && Array.isArray(ids[0])) ids = ids[0];

            for (let i = 0; i < ids.length; i++) {
                const id = ids[i];
                const el = document.getElementById(id);
                if (!el) continue; // skip missing elements

                const val = (el.value || '').toString().trim();
                if (!val) {
                    // focus the first invalid element
                    try { el.focus(); } catch (e) {}
                    return false;
                }
            }

            // Additional check: if both start and end exist, ensure start <= end
            const startEl = document.getElementById('lvr_start_date');
            const endEl = document.getElementById('lvr_end_date');
            if (startEl && endEl) {
                const startVal = (startEl.value || '').toString().trim();
                const endVal = (endEl.value || '').toString().trim();
                if (startVal && endVal) {
                    const startDate = new Date(startVal);
                    const endDate = new Date(endVal);
                    if (startDate > endDate) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Date Range',
                            text: 'Start Date cannot be greater than End Date.'
                        });
                        startEl.value = endEl.value;
                        try { startEl.focus(); } catch (e) {}
                        return false;
                    }
                }
            }

            return true;
        }

        function toggleDayTypeOptions(id) {
            if (id == 202) {
                $('#segement-div').show();
                $('#date_range').hide();
                $('#single_date').show();
                fieldsTovalidate = ['lvr_leave_day_type_id', 'lvr_day_segment_id', 'lvr_cat_type_id', 'lvr_date'];
            } else if (id == 201) {
                $('#segement-div').hide();
                $('#single_date').hide();
                $('#date_range').show();
                fieldsTovalidate = ['lvr_leave_day_type_id', 'lvr_cat_type_id', 'lvr_start_date', 'lvr_end_date'];
            } else {
                fieldsTovalidate = ['lvr_leave_day_type_id', 'lvr_cat_type_id', 'lvr_date'];
                $('#segement-div').hide();
                $('#single_date').show();
                $('#date_range').hide();
            }
        }

        function saveLeave() {
            // Validate fields first
            if (!validateFields(...fieldsTovalidate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation failed',
                    text: 'Please fill all required fields.'
                });
                return;
            }

            // Confirm action with the user
            Swal.fire({
                title: 'Confirm leave',
                text: 'Are you sure you want to save this leave?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (!result.isConfirmed) return;

                // Prepare form data
                const form = document.getElementById('leaveForm');
                const fd = new FormData(form);

                fd.append('emp_id', $('#emp_id').val());

                // Show loading
                Swal.fire({
                    title: 'Saving...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: "{{ route('employee-leave-calendar.store') }}",
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.close();
                        Swal.fire({
                            icon: res.status ? 'success' : 'info',
                            title: res.status ? 'Saved' : 'Leave not saved',
                            text: res.message || 'Leave saved successfully.'
                        });

                        // Close modal and refresh calendar/table
                        $('#addLeave').modal('hide');
                        $('#leaveForm').trigger("reset");
                        if (calendar) calendar.refetchEvents();
                        if (leaveHistoryTable) leaveHistoryTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.close();
                        let msg = 'Failed to save leave.';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) console.log(xhr
                            .responseJSON.message);
                        else if (xhr && xhr.responseText) console.log(xhr.responseText);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            });
        }
    </script>
@endsection
