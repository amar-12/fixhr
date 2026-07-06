@extends('admin.layout.master')

@section('title', 'Holiday Policy Settings')

@section('css')
    <style>
        .error-message {
            color: red;
            display: none;
            font-size: 0.9em;
        }

        .is-invalid {
            border: 1px solid red;
        }
    </style>

@endsection

@section('content')

    {{-- Breadcrumb Start --}}
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between p-4">
            <div>
                <h4 class="text-primary">Holiday Policy Settings</h4>
                <ol class="breadcrumb1 breadcrumb1-bg-none m-0 p-0 fs-14">
                    <li class="breadcrumb-item1"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item1"><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a>
                    </li>
                    <li class="breadcrumb-item1 active"><span><b>Holiday Policy</b></span></li>
                </ol>
            </div>
            <div>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                    data-bs-target="#createHolidayModal">Create Holiday</button>
            </div>
        </div>
    </div>
    {{-- Breadcrumb End --}}

    {{-- Layout Body --}}
    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-body pt-5">
                    @csrf
                    {{-- Table Filters and Export --}}
                    <div class="row pe-0">

                        <div class="col-7 row">
                            {{-- Show Entries Select --}}
                            <div class="col-auto">
                                <div class="form-group row align-items-center">
                                    <label class="col-form-label col-auto pe-0">Show entries</label>
                                    <div class="col-auto">
                                        <select class="form-select" data-length>
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Search Input --}}
                            <div class="col">
                                <div class="input-group">
                                    {{-- <label class="form-label" for="searchFilter">Search</label> --}}
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <span><i class="fa fa-search"></i></span>
                                        </div>
                                    </div>
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>

                            <div class="col-3">
                                <select id="financialYearFilter" data-filter class="form-select">
                                    <option value="">All Financial Year</option>
                                    @foreach($financialYears as $fy)
                                        <option value="{{ $fy->fy_id }}">
                                            {{ $fy->fy_year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Export Button --}}
                        <div class="col-5 d-flex justify-content-end align-items-end">
                            <div class="mb-4">
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                        id="defaultDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true"
                                        aria-expanded="false">
                                        <i class="fa fa-download me-2"></i> Export As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="csv">CSV</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="excel">Excel</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="pdf">PDF</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="copy">Copy</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" data-export="print">Print</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-nowrap border-bottom"
                            id="holiday-policy-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size: 13px">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>

                    {{-- Pagination and Show entries --}}
                    <div class="row mt-5">
                        <div class="col-sm-6">
                            <div id="custom-show-entries" data-show-entries></div>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-end">
                            <ul data-pagination class="custom-pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Holiday Policy Add Modal Start --}}
    <div class="modal fade" id="createHolidayModal" tabindex="-1" role="dialog" data-bs-backdrop="static"
        aria-hidden="true" aria-labelledby="createHolidayModal">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:14px;">Add Holiday Policy</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" data-bs-dismiss="modal">&times;</span>
                    </button>
                </div>

                <form action="{{ route('store.policy-holiday') }}" method="post">
                    @csrf
                    <input type="hidden" name="hiddenHolidayCreate" id="hiddenHolidayCreate">

                    <div class="modal-body" id="frmProduct">
                        <div id="HolidayRows">
                            <div class="row holiday-row mb-">

                                {{-- Holiday Name --}}
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <p class="form-label">Holiday Name <span class="text-danger">*</span></p>
                                        <input type="text" class="form-control holidayName" id="holiday_name_1"
                                            placeholder="Enter Holiday Name" name="holiday_name[]" required />
                                    </div>
                                </div>

                                {{-- Policy Type --}}
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <p class="form-label">Policy Type <span class="text-danger">*</span></p>
                                        <select name="policy_type[]" id="policyNameInsert"
                                            class="form-control form-select travelType select2 PolicyType header-text"
                                            data-placeholder="Enter Policy Type" required aria-label="text"
                                            tabindex="1">
                                            <option label="Policy Type"></option>
                                            @foreach ($type as $key => $type_name)
                                                <option value="{{ $type_name->m_id }}"
                                                    data-attendence_id="{{ $type_name->m_id }}">
                                                    {{ $type_name->m_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Day Type --}}
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <p class="form-label">Day Type <span class="text-danger">*</span></p>
                                        <select name="day_type[]" onchange="showHideInputs(this)"
                                            class="form-control form-select dayTypeSelect select2 header-text"
                                            data-placeholder="Enter Day Type" required aria-label="text" tabindex="1">
                                            <option label="Day Type"></option>
                                            @foreach ($day_type_id as $key => $day_type_name)
                                                <option value="{{ $day_type_name->m_id }}"
                                                    data-attendence_id="{{ $day_type_name->m_id }}">
                                                    {{ $day_type_name->m_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>

                                {{-- Day Segment Type --}}
                                <div class="col-xl-2 segmentDiv">
                                    <div class="form-group">
                                        <p class="form-label">Day Segment Type <span class="text-danger">*</span></p>
                                        <select name="day_segment_type[]" id="daySegmentTypeInsert"
                                            class="form-control form-select travelType select2 DaySegmentType header-text"
                                            data-placeholder="Enter Day Segment Type" aria-label="text" tabindex="1">
                                            <option label="Day Segment Type"></option>
                                            @foreach ($day_segment_type_id as $key => $day_segment_type_name)
                                                <option value="{{ $day_segment_type_name->m_id }}"
                                                    data-attendence_id="{{ $day_segment_type_name->m_id }}">
                                                    {{ $day_segment_type_name->m_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Holiday Date --}}
                                <div class="col-xl-2 singleDateDiv">
                                    <p class="form-label">Date <span class="text-danger">*</span></p>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input class="form-control single_date" type="date" name="holiday_date[]">
                                        </div>
                                    </div>
                                </div>

                                {{-- Holiday From --}}
                                <div class="col-xl-2 fromDateDiv">
                                    <p class="form-label">From <span class="text-danger">*</span></p>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input class="form-control form_date" type="date" name="holiday_from[]">
                                        </div>
                                    </div>
                                </div>

                                {{-- Holiday To --}}
                                <div class="col-xl-2 toDateDiv">
                                    <p class="form-label">To <span class="text-danger">*</span></p>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input class="form-control to_date" type="date" name="holiday_to[]">
                                        </div>
                                        <small id="date-error-msg" class="text-danger d-none">Cannot be before start
                                            date.</small>
                                    </div>
                                </div>

                                {{-- Add Row --}}
                                <div class="col-xl-1 mt-5">
                                    <button type="button" class="btn btn-outline-primary mt-1" id="addHolidayBtn">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <a class="btn btn-outline-danger  cancel" type="reset" id="close-btn"
                            data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-outline-primary " data-bs-toggle="tooltip"
                            data-bs-placement="top" id="submitButtonHoliday" title="Save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Holiday Policy Add Modal End --}}


    {{-- Edit modal start --}}
    <div class="container">
        <div class="modal fade" id="showmodal" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" style="font-size:14px;">Edit Holiday Policy</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" data-bs-dismiss="modal">&times;</span>
                        </button>
                    </div>

                    <form action="{{ route('update.policy-holiday') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id" id="updateId">

                            <div class="row" id="update-row">
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <p for="updateName">Holiday Policy Name <span class="text-danger">*</span></p>
                                        <input type="text" class="form-control" id="updateName"
                                            name="update_holiday_name" required>
                                    </div>
                                </div>

                                <div class="col-xl-2">
                                    <p for="updatetype">Policy Type <span class="text-danger">*</span></p>
                                    <select name="update_policy_type" id="updatetype" class="form-control select2"
                                        required>
                                        <option label="Policy Type Name" selected disabled></option>
                                        @foreach ($type as $key => $typename)
                                            <option value="{{ $typename->m_id }}">
                                                {{ $typename->m_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Day Type --}}
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <p class="form-label">Day Type <span class="text-danger">*</span></p>
                                        <select name="day_type_update" onchange="showHideInputs(this)" id="dayTypeUpdate"
                                            class="form-control form-select dayTypeSelect select2 header-text"
                                            data-placeholder="Enter Day Type" required aria-label="text" tabindex="1">
                                            <option label="Day Type"></option>
                                            @foreach ($day_type_id as $key => $day_type_name)
                                                <option value="{{ $day_type_name->m_id }}"
                                                    data-attendence_id="{{ $day_type_name->m_id }}">
                                                    {{ $day_type_name->m_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Day Segment Type --}}
                                <div class="col-xl-2 segmentDiv">
                                    <div class="form-group">
                                        <p class="form-label">Day Segment Type <span class="text-danger">*</span></p>
                                        <select name="day_segment_type_update" id="daySegmentTypeUpdate"
                                            class="form-control form-select travelType select2 DaySegmentType header-text"
                                            data-placeholder="Enter Day Segment Type" aria-label="text" tabindex="1">
                                            <option label="Day Segment Type"></option>
                                            @foreach ($day_segment_type_id as $key => $day_segment_type_name)
                                                <option value="{{ $day_segment_type_name->m_id }}"
                                                    data-attendence_id="{{ $day_segment_type_name->m_id }}">
                                                    {{ $day_segment_type_name->m_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xl-2 fromDateDiv">
                                    <p for="updateFrom">From <span class="text-danger">*</span></p>
                                    <input type="date" class="form-control" id="updateFrom"
                                        name="holiday_from" required>
                                </div>

                                <div class="col-xl-2 toDateDiv">
                                    <p for="updateTo">To <span class="text-danger">*</span></p>
                                    <input type="date" class="form-control" id="updateTo"
                                        name="holiday_to" required>
                                    <small id="dateError" class="text-danger d-none">Cannot be before start date.</small>
                                </div>

                                {{-- Holiday Date --}}
                                <div class="col-xl-2 singleDateDiv">
                                    <p class="form-label">Date <span class="text-danger">*</span></p>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input class="form-control single_date" id="holidayDateUpdate" type="date"
                                                name="holiday_date">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-danger "
                                data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-outline-primary" type="submit" id="updateButton">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- Edit modal end --}}


    {{-- view modal start --}}
    <div class="container">
        <div class="modal fade" id="viewshowmodal" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header ">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="font-size:18px;">View Holiday
                            Policy
                        </h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" data-bs-dismiss="modal">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody class="text-center">
                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Policy Holiday Name
                                            </span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="policy_holiday"></td>
                                    </tr>
                                    {{-- <tr>
                                    <td class="py-2 px-0"><span class="font-weight-semibold w-50">Attendence Policy
                                            Name </span></td>
                                    <td class="py-2 px-0">:</td>
                                    <td class="py-2 px-0" id="attendence_policy"></td>
                                </tr> --}}
                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Policy Type
                                            </span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="type_name"></td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Start Date </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="start_date"></td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">End Date </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="end_date"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- view modal end --}}


    {{-- delete modal start --}}
    <div class="modal fade" id="editDeleteModel" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Confirm Deletion</h5>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('delete.policy-holiday') }}" method="POST" onsubmit="disableDeleteButton()">
                    @csrf
                    @method('DELETE')
                    <input type="text" id="holiday_policy_id" name="holiday_id" hidden>
                    <div class="modal-body">
                        <h4 class="mt-5">Are you sure you want to delete <span id="assign_emp"></span>&nbsp;holiday?
                        </h4>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-outline-danger " id="deleteButton">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- delete modal end --}}

@endsection

@section('script')

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script type="text/javascript">
        // Initialize day type handlers for existing and future rows
        $(document).on('change', '.dayTypeSelect', function() {
            showHideInputs(this);
        });

        // $(document).ready(function() {
        //     datatable({
        //         tableId: "holiday-policy-table-dynamic",
        //         url: "{{ route('get.policy-holiday') }}",
        //         dataLength: '[data-length]',
        //         dataSearch: '[data-search]',
        //         dataFilter: '[data-filter]',
        //         dataExport: '[data-export]',
        //         dataDateFilter: '[data-date-filter]',
        //         dataShowEntries: '[data-show-entries]',
        //         dataPagination: '[data-pagination]',
        //         dataStateSave: false
        //     });
        // });

        $(document).ready(function () {

            const table = datatable({
                tableId: "holiday-policy-table-dynamic",
                url: "{{ route('get.policy-holiday') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false,

                // 👇 IMPORTANT PART
                ajaxData: function (d) {
                    d.financial_year = $('#financialYearFilter').val();
                }
            });

            // 👇 onchange reload table
            $('#financialYearFilter').on('change', function () {
                table.ajax.reload();
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveButton = document.getElementById('submitButtonHoliday');
            const form = saveButton.closest('form');

            saveButton.addEventListener('click', function() {
                // Disable the button to prevent multiple clicks
                saveButton.disabled = true;

                // Optionally show a loading state
                saveButton.textContent = 'Saving...';

                // Allow the form to be submitted
                form.submit();
            });
        });

        // ******** Disable Previous Date
        $(document).ready(function() {
            // Disable past dates on page load
            // let today = new Date().toISOString().split("T")[0];
            // $('input[name="holiday_from[]"], input[name="holiday_to[]"]').attr("min", today);

            // Update the min date of 'To' field based on 'From' field selection
            $(document).on("change", 'input[name="holiday_from[]"]', function() {
                let fromDate = $(this).val();
                let toDateInput = $(this).closest(".holiday-row").find('input[name="holiday_to[]"]');
                toDateInput.attr("min", fromDate); // Ensure 'To' date is after 'From' date
            });

            // Ensure 'To' date is not before 'From' date
            $(document).on("change", 'input[name="holiday_to[]"]', function() {
                let fromDate = $(this).closest(".holiday-row").find('input[name="holiday_from[]"]').val();
                let toDate = $(this).val();
                let errorMessage = $(this).closest(".holiday-row").find(".date-error-msg");

                if (toDate < fromDate) {
                    errorMessage.removeClass("d-none");
                    $(this).val(""); // Clear incorrect date
                } else {
                    errorMessage.addClass("d-none");
                }
            });
        });
    </script>

    <script>
        var number = 2;

        function initializeRowValidation(row) {
            const holidayFrom = $(row).find('input[name="holiday_from[]"]');
            const holidayTo = $(row).find('input[name="holiday_to[]"]');
            const errorMessageDiv = $(row).find('.error-message');

            function validateDateRange() {
                const fromDate = new Date(holidayFrom.val());
                const toDate = new Date(holidayTo.val());

                if (holidayFrom.val() && holidayTo.val() && toDate < fromDate) {
                    errorMessageDiv.text('Cannot be before start date.');
                    errorMessageDiv.show();
                    return false;
                }
                errorMessageDiv.hide();
                return true;
            }

            // Trigger validation on change
            holidayFrom.on('change', validateDateRange);
            holidayTo.on('change', validateDateRange);
        }

        function initializeAllRows() {
            $('.holiday-row').each(function() {
                initializeRowValidation(this);
            });
        }

        // Initialize validation for default row on page load
        $(document).ready(function() {
            initializeAllRows();
        });

        function validateDatesInRow(row) {
            const holidayFrom = $(row).find('input[name="holiday_from[]"]');
            const holidayTo = $(row).find('input[name="holiday_to[]"]');
            const errorMessageDiv = $(row).find('.date-error-msg'); // Select error message from the same row
            const submitButton = document.getElementById('submitButtonHoliday');
            const addHolidayBtn = $('#addHolidayBtn');

            function validateDateRange() {
                const fromDate = new Date(holidayFrom.val());
                const toDate = new Date(holidayTo.val());

                if (holidayFrom.val() && holidayTo.val() && toDate < fromDate) {
                    errorMessageDiv.removeClass('d-none'); // Show error in correct row
                    addHolidayBtn.prop('disabled', true); // Disable button
                    submitButton.disabled = true;
                    return false;
                }

                errorMessageDiv.addClass('d-none'); // Hide error if correct
                addHolidayBtn.prop('disabled', false);
                submitButton.disabled = false;
                return true;
            }

            holidayFrom.on('change', validateDateRange);
            holidayTo.on('change', validateDateRange);
        }

        // Add new row
        document.getElementById('addHolidayBtn').addEventListener('click', function() {
            let allFilled = true;
            const requiredFields = document.querySelectorAll('#HolidayRows .form-control[required]');

            requiredFields.forEach(function(field) {
                if (field.value.trim() === "") {
                    allFilled = false;
                }
            });

            if (!allFilled) {
                alert("Please fill in all required fields before adding a new row.");
                return;
            }

            let PolicyTypeOptions = `@foreach ($type as $key => $type_name)
            <option value="{{ $type_name->m_id }}" data-attendence_id="{{ $type_name->m_id }}">
                {{ $type_name->m_name }}
            </option>
        @endforeach`;

            let html = `
            <div class="row holiday-row mb-3">
                <div class="col-xl-2">
                    <div class="form-group">
                        <p class="form-label">Holiday Name <span class="text-danger">*</span></p>
                        <input type="text" class="form-control holidayName" placeholder="Enter Holiday Name" name="holiday_name[]" required />
                    </div>
                </div>

                <div class="col-xl-2">
                    <div class="form-group">
                        <p class="form-label">Policy Type <span class="text-danger">*</span></p>
                        <select name="policy_type[]" class="form-control form-select select2 PolicyType" data-placeholder="Enter Policy Type" required>
                            <option label="Policy Type"></option>
                            ${PolicyTypeOptions}
                        </select>
                    </div>
                </div>

                {{-- Day Type --}}
                <div class="col-xl-2">
                    <div class="form-group">
                        <p class="form-label">Day Type <span class="text-danger">*</span></p>
                        <select name="day_type[]" onchange="showHideInputs(this)"
                            class="form-control form-select dayTypeSelect select2 header-text"
                            data-placeholder="Enter Day Type" required aria-label="text" tabindex="1">
                            <option label="Day Type"></option>
                            @foreach ($day_type_id as $key => $day_type_name)
                            <option value="{{ $day_type_name->m_id }}"
                                data-attendence_id="{{ $day_type_name->m_id }}">
                                {{ $day_type_name->m_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Day Segment Type --}}
                <div class="col-xl-2 segmentDiv">
                    <div class="form-group">
                        <p class="form-label">Day Segment Type <span class="text-danger">*</span></p>
                        <select name="day_segment_type[]" id="daySegmentTypeInsert"
                            class="form-control form-select travelType select2 DaySegmentType header-text"
                            data-placeholder="Enter Day Segment Type" required aria-label="text" tabindex="1">
                            <option label="Day Segment Type"></option>
                            @foreach ($day_segment_type_id as $key => $day_segment_type_name)
                            <option value="{{ $day_segment_type_name->m_id }}"
                                data-attendence_id="{{ $day_segment_type_name->m_id }}">
                                {{ $day_segment_type_name->m_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Holiday Date --}}
                <div class="col-xl-2 singleDateDiv">
                    <p class="form-label">Date <span class="text-danger">*</span></p>
                    <div class="form-group">
                        <div class="input-group">
                            <input class="form-control form_date" type="date" name="holiday_date[]">
                        </div>
                    </div>
                </div>

                {{-- Holiday From --}}
                <div class="col-xl-2 fromDateDiv">
                    <p class="form-label">From <span class="text-danger">*</span></p>
                    <div class="form-group">
                        <div class="input-group">
                            <input class="form-control form_date" type="date" name="holiday_from[]">
                        </div>
                    </div>
                </div>

                {{-- Holiday To --}}
                <div class="col-xl-2 toDateDiv">
                    <p class="form-label">To <span class="text-danger">*</span></p>
                    <div class="form-group">
                        <div class="input-group">
                            <input class="form-control to_date" type="date" name="holiday_to[]">
                        </div>
                        <small id="date-error-msg" class="text-danger d-none">Cannot be before start
                            date.</small>
                    </div>
                </div>

                <div class="col-xl-1 mt-5">
                    <button type="button" class="btn btn-outline-danger  removeRow"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;
            document.getElementById('HolidayRows').insertAdjacentHTML('beforeend', html);
            $('.select2').select2();

            // Initialize validation for the newly added row
            const rows = document.querySelectorAll('.holiday-row');
            initializeRowValidation(rows[rows.length - 1]);
            showHideInputs(rows[rows.length - 1]);
        });

        // Dynamically attach validation logic to rows being removed and validate rows
        $('#HolidayRows').on('input', 'input[name="holiday_from[]"], input[name="holiday_to[]"]', function() {
            const row = $(this).closest('.holiday-row');
            validateDatesInRow(row);
        });

        function checkForDuplicatesAndAlert() {
            // If the user is still typing in a date input, force it to commit
            if (document.activeElement && document.activeElement.tagName === 'INPUT') {
                document.activeElement.blur();
            }

            const duplicateIndices = [];
            const combinationSet = new Set();
            let foundDuplicate = false;

            $('.holiday-row').each(function(index) {
                // Use explicit selectors by name to avoid mixing single-date / from-date inputs
                const fromInput = $(this).find('input[name="holiday_from[]"]');
                const toInput = $(this).find('input[name="holiday_to[]"]');
                const singleDateInput = $(this).find('input[name="holiday_date[]"]');

                // Decide what the "from" and "to" are for this row:
                // - If this row uses a single date, treat it as both from/to
                // - Otherwise use holiday_from[] and holiday_to[]
                let fromVal = '';
                let toVal = '';

                if (singleDateInput.length && singleDateInput.val()) {
                    fromVal = singleDateInput.val().trim();
                    toVal = singleDateInput.val().trim();
                } else if (fromInput.length && toInput.length) {
                    fromVal = (fromInput.val() || '').trim();
                    toVal = (toInput.val() || '').trim();
                } else {
                    // Nothing useful in this row; skip it
                    return;
                }

                // If either is empty, skip duplicate checking for this row (avoid false duplicates)
                if (!fromVal && !toVal) {
                    return;
                }

                const combo = `${fromVal}-${toVal}`;

                if (combinationSet.has(combo)) {
                    foundDuplicate = true;
                    duplicateIndices.push(index);
                } else {
                    combinationSet.add(combo);
                }
            });

            if (foundDuplicate) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Duplicate holiday date (from/to) found. Please correct it.',
                    timer: 3000
                });

                // Clear only the relevant inputs in duplicate rows
                duplicateIndices.forEach(function(idx) {
                    const row = $('.holiday-row').eq(idx);
                    row.find('input[name="holiday_from[]"]').val('').trigger('change');
                    row.find('input[name="holiday_to[]"]').val('').trigger('change');
                    row.find('input[name="holiday_date[]"]').val('').trigger('change');
                });

                return false;
            }

            // no duplicates
            return true;
        }

        document.getElementById('addHolidayBtn').addEventListener('click', function() {
            const isValid = checkForDuplicatesAndAlert();

            // Re-validate all rows
            let allRowsValid = true;
            $('.holiday-row').each(function() {
                if (!initializeRowValidation(this)) {
                    allRowsValid = false;
                }
            });

            if (isValid && allRowsValid) {
                addNewHolidayRow(); // Add a new row if no errors exist
            }
        });

        // Remove row
        document.getElementById('HolidayRows').addEventListener('click', function(e) {
            if (e.target.classList.contains('removeRow') || e.target.closest('.removeRow')) {
                const row = e.target.closest('.holiday-row'); // Get the closest parent row
                if (document.querySelectorAll('.holiday-row').length > 1) {
                    row.remove(); // Remove the row
                } else {
                    alert('You need at least one row.'); // Prevent deletion if it's the only row
                }
            }
        });

        // Reset rows on modal close
        $('#createHolidayModal').on('hidden.bs.modal', function() {
            $('#HolidayRows').html(`
                <div class="row holiday-row mb-3">

                    {{-- Holiday Name --}}
                    <div class="col-xl-2">
                        <div class="form-group">
                            <p class="form-label">Holiday Name <span class="text-danger">*</span></p>
                            <input type="text" class="form-control holidayName" id="holiday_name_1"
                                placeholder="Enter Holiday Name" name="holiday_name[]" required />
                        </div>
                    </div>

                    {{-- Policy Type --}}
                    <div class="col-xl-2">
                        <div class="form-group">
                            <p class="form-label">Policy Type <span class="text-danger">*</span></p>
                            <select name="policy_type[]" id="policyNameInsert"
                                class="form-control form-select travelType select2 PolicyType header-text"
                                data-placeholder="Enter Policy Type" required aria-label="text"
                                tabindex="1">
                                <option label="Policy Type"></option>
                                @foreach ($type as $key => $type_name)
                                    <option value="{{ $type_name->m_id }}"
                                        data-attendence_id="{{ $type_name->m_id }}">
                                        {{ $type_name->m_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Day Type --}}
                    <div class="col-xl-2">
                        <div class="form-group">
                            <p class="form-label">Day Type <span class="text-danger">*</span></p>
                            <select name="day_type[]" onchange="showHideInputs(this)"
                                class="form-control form-select dayTypeSelect select2 header-text"
                                data-placeholder="Enter Day Type" required aria-label="text" tabindex="1">
                                <option label="Day Type"></option>
                                @foreach ($day_type_id as $key => $day_type_name)
                                    <option value="{{ $day_type_name->m_id }}"
                                        data-attendence_id="{{ $day_type_name->m_id }}">
                                        {{ $day_type_name->m_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- Day Segment Type --}}
                    <div class="col-xl-2 segmentDiv">
                        <div class="form-group">
                            <p class="form-label">Day Segment Type <span class="text-danger">*</span></p>
                            <select name="day_segment_type[]" id="daySegmentTypeInsert"
                                class="form-control form-select travelType select2 DaySegmentType header-text"
                                data-placeholder="Enter Day Segment Type" aria-label="text" tabindex="1">
                                <option label="Day Segment Type"></option>
                                @foreach ($day_segment_type_id as $key => $day_segment_type_name)
                                    <option value="{{ $day_segment_type_name->m_id }}"
                                        data-attendence_id="{{ $day_segment_type_name->m_id }}">
                                        {{ $day_segment_type_name->m_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Holiday Date --}}
                    <div class="col-xl-2 singleDateDiv">
                        <p class="form-label">Date <span class="text-danger">*</span></p>
                        <div class="form-group">
                            <div class="input-group">
                                <input class="form-control single_date" type="date" name="holiday_date[]">
                            </div>
                        </div>
                    </div>

                    {{-- Holiday From --}}
                    <div class="col-xl-2 fromDateDiv">
                        <p class="form-label">From <span class="text-danger">*</span></p>
                        <div class="form-group">
                            <div class="input-group">
                                <input class="form-control form_date" type="date" name="holiday_from[]">
                            </div>
                        </div>
                    </div>

                    {{-- Holiday To --}}
                    <div class="col-xl-2 toDateDiv">
                        <p class="form-label">To <span class="text-danger">*</span></p>
                        <div class="form-group">
                            <div class="input-group">
                                <input class="form-control to_date" type="date" name="holiday_to[]">
                            </div>
                            <small id="date-error-msg" class="text-danger d-none">Cannot be before start
                                date.</small>
                        </div>
                    </div>

                    <div class="col-xl-1 mt-5">
                        <button type="button" class="btn btn-outline-primary mt-1" id="addHolidayBtn"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
            `);
            $('.select2').select2();
            initializeAllRows(); // Reinitialize rows after reset
            $('#addHolidayBtn').prop('disabled', false); // Reset button state
        });
    </script>

    <script>
        // for edit
        document.addEventListener("DOMContentLoaded", function() {
            const fromDateInput = document.getElementById('updateFrom');
            const toDateInput = document.getElementById('updateTo');
            const dateError = document.getElementById('dateError');
            const updateButton = document.getElementById('updateButton');

            // Disable past dates
            const today = new Date().toISOString().split('T')[0];
            // fromDateInput.setAttribute("min", today);
            // toDateInput.setAttribute("min", today);

            function validateDates() {
                const fromDate = new Date(fromDateInput.value);
                const toDate = new Date(toDateInput.value);

                if (toDateInput.value && fromDateInput.value && toDate < fromDate) {
                    dateError.classList.remove('d-none');
                    toDateInput.classList.add('is-invalid');
                    updateButton.disabled = true;
                } else {
                    dateError.classList.add('d-none');
                    toDateInput.classList.remove('is-invalid');
                    checkFormValidity();
                }

                // Ensure "To" date can't be before "From" date
                // toDateInput.setAttribute("min", fromDateInput.value);
            }

            function checkFormValidity() {
                if (fromDateInput.value && toDateInput.value && toDateInput.value >= fromDateInput.value) {
                    updateButton.disabled = false;
                } else {
                    updateButton.disabled = true;
                }
            }

            fromDateInput.addEventListener('change', validateDates);
            toDateInput.addEventListener('change', validateDates);
        });
    </script>

    <script>
        $('#departmentID').on('input', function() {
            $('#department-error').html('');
        });

        $('#editDepartment').on('input', function() {
            $('#department-update-error').html('');
        });

        $(document).ready(function() {
            // Initialize Select2 globally for elements with the class 'select2'
            $('.select2').select2();

            // Reinitialize Select2 when the modal is shown
            $('#createHolidayModal').on('shown.bs.modal', function() {
                // Destroy existing Select2 instance if it exists
                $('.select2').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                // Initialize Select2 again within the modal
                $('.select2').select2({
                    dropdownParent: $('#createHolidayModal')
                });
            });
        });

        $(document).ready(function() {
            $('#updatepolicyName').select2({
                width: '100%' // Optional, depending on your layout
            });
        });

        $("#add_holiday").on('click', function() {
            $('#form_holiday').trigger('reset');
            $('#show_item_insert').empty();
            $('#createTemplate').modal('show');
        });

        document.getElementById("close-btn").addEventListener("click", function() {
            document.getElementById("frmProduct").reset();
        });
        var loader = '';

        function ItemDeleteModel(context) {

            var id = $(context).data('id');
            var name = $(context).data('holiday_name')
            $('#holiday_policy_id').val(id);
            $('#assign_emp').text(name);
        }

        function disableDeleteButton() {
            const deleteButton = document.getElementById('deleteButton');
            deleteButton.disabled = true; // Disable the button
            deleteButton.innerHTML = 'Processing...'; // Optionally change button text
        }

        var holidayData = [];

        function openEditModel(context) {
            var id = $(context).data('id');
            var holiday_name = $(context).data('holiday_name');
            var holiday_type = $(context).data('type_name');
            var holiday_day_type = $(context).data('day-type');
            var holiday_segment = $(context).data('day-segment');
            var holiday_from = $(context).data('holiday_from');
            var holiday_to = $(context).data('holiday_to');

            var formattedFromDate = formatDateForInput(holiday_from);
            var formattedToDate = formatDateForInput(holiday_to);

            $('#showmodal').modal('show');

            $('#updateId').val(id);
            $('#updateName').val(holiday_name);
            $('#holidayDateUpdate').val(formattedFromDate);
            $('#updateFrom').val(formattedFromDate);
            $('#updateTo').val(formattedToDate);
            $('#dayTypeUpdate').val(holiday_day_type).trigger('change');
            $('#daySegmentTypeUpdate').val(holiday_segment).trigger('change');
            // Select the correct attendance policy option
            $('#updatetype').val(holiday_type).trigger('change');

            if (holiday_day_type == 202) {
                $('#update-row').find('.segmentDiv, .singleDateDiv').show();
                $('#update-row').find('.fromDateDiv, .toDateDiv').hide();
                console.log($('#update-row').find('.fromDateDiv, .toDateDiv'));
            } else {
                $('#update-row').find('.segmentDiv, .singleDateDiv').hide();
                $('#update-row').find('.fromDateDiv, .toDateDiv').show();
            }
        }

        // Function to convert any date format to 'YYYY-MM-DD' for HTML date input
        function formatDateForInput(date) {
            var d = new Date(date);
            var month = '' + (d.getMonth() + 1);
            var day = '' + d.getDate();
            var year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }

        function openViewModel(context) {
            var id = $(context).data('id');
            var holiday_name = $(context).data('holiday_name');
            var type_name = $(context).data('type_name');
            var holiday_from = $(context).data('holiday_from');
            var holiday_to = $(context).data('holiday_to');


            // Convert date to desired format (YYYY-MM-DD)
            var formattedFromDate = formatDate(holiday_from);
            var formattedToDate = formatDate(holiday_to);

            // Open the modal
            $('#viewshowmodal').modal('show');

            // Set the values in the modal
            $('#updateIdView').val(id);
            $('#policy_holiday').text(holiday_name);
            $('#type_name').text(type_name);
            $('#start_date').text(formattedFromDate);
            $('#end_date').text(formattedToDate);
        }

        // Helper function to format the date (Adjust as per your needs)
        function formatDate(dateString) {
            var date = new Date(dateString);
            var day = ("0" + date.getDate()).slice(-2);
            var month = ("0" + (date.getMonth() + 1)).slice(-2);
            var year = date.getFullYear();

            return year + '-' + month + '-' + day;
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        toast: 'swal2-toast-green-glow'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ session('error') }}',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif

            showHideInputs();
        });

        function showHideInputs(element) {
            console.log(element);
            // Get the selected value and find the closest holiday-row
            const day_type_id = element ? $(element).val() : null;
            const row = element ? $(element).closest('.holiday-row') : $('.holiday-row');

            if (day_type_id == 202) {
                row.find('.segmentDiv, .singleDateDiv').show();
                row.find('.fromDateDiv, .toDateDiv').hide();
            } else {
                row.find('.segmentDiv, .singleDateDiv').hide();
                row.find('.fromDateDiv, .toDateDiv').show();
            }
        }
    </script>

@endsection
