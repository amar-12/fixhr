@extends('admin.layout.master')
@section('title', 'Attendance Policy')

@section('css')
<style>
    .export-button {
        display: flex;
        align-items: center;
        gap: 6px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .export-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .dropdown-menu-export {
        font-size: 14px;
        min-width: 140px;
    }

    .dropdown-menu-export .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .custom-button {
        display: flex;
        align-items: center;
        gap: 6px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .custom-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .custom-button svg {
        width: 16px;
        height: 16px;
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
    }

    .custom-checkbox .form-check-input {
        width: 20px;
        height: 20px;
        margin-top: 0 !important;
        cursor: pointer;
        position: relative;
        z-index: 2;
    }

    .custom-checkbox .form-check-label {
        margin-bottom: 0;
        line-height: 1.4;
        cursor: pointer;
    }

    .card, .table-responsive {
        overflow: visible !important;
    }
</style>
@endsection
@section('content')
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a></li>
                    <li class="active"><span><b>Attendance Policy</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <x-button type="button" class="btn btn-outline-primary create-button" data-bs-toggle="modal"
                                    data-bs-target="#attendancePolicyForm" data-title="Create Attendance Policy">
                                    Create Attendance
                                    Policy
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bradcrumbs End --}}
    @php
        $selfieMethodId = collect($checkInMethod)->search('Selfie');
    @endphp
    <x-modal id="attendancePolicyForm" title="Create Attendance Policy" formId="createAttendancePolicyForm" action="{{ route('attendance-policies.store') }}" method="POST" size="modal-lg" submitButtonText="Create">
        <input type="hidden" name="ap_id" id="ap_id">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <x-input type="text" id="ap_name" label="Policy Name" name="ap_name" placeholder="Policy Name" required astric="*" />
                </div>
                <div>
                    <x-input type="number" id="ap_punch_duration" label="Difference between two punch (In Mins)" name="ap_punch_duration" placeholder="Minutes" min="0" max="180" step="1" required astric="*" />
                </div>
            </div>
            <div class="col-md-6 d-flex flex-column">
                <x-textarea id="ap_description" label="Description" name="ap_description" placeholder="Description" rows="5" required astric="*" />
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="form-check custom-checkbox">
                    <input type="hidden" name="ap_mark_absent_check" value="0">
                    <input type="checkbox" class="form-check-input" id="ap_mark_absent_check" name="ap_mark_absent_check" value="1">
                    <label class="form-check-label" for="ap_mark_absent_check">
                        Sunday will be considered absent if the employee is
                        <strong>absent</strong> on both Saturday and Monday
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label class="form-label mb-2">
                    Assign Check In Method <span class="text-danger">*</span>
                </label>

                <div class="d-flex flex-wrap gap-4">
                    @foreach ($checkInMethod as $index => $method)
                        <div class="form-check custom-checkbox">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="ap_checkin_method_ids[]"
                                value="{{ $index }}"
                                id="ap_checkin_method_ids{{ $index }}">

                            <label class="form-check-label"
                                   for="ap_checkin_method_ids{{ $index }}">
                                {{ $method }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <br>
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="form-check custom-checkbox">
                    <input type="hidden" name="ap_is_selfie_restricted" value="0">
                    <input type="checkbox" class="form-check-input" id="ap_is_selfie_restricted" name="ap_is_selfie_restricted" value="1">
                    <label class="form-check-label" for="ap_is_selfie_restricted">
                        Do you want to restricted selfie attendance in one device.
                    </label>
                </div>
            </div>
        </div>
    </x-modal>
    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Attendance Policy</h4>
                </div>
                <div class="card-body">
                    @csrf
                       <div class="row">
                            <div class="col-sm-1">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test" style="width: 100%"
                                        data-length>
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                            <div class="col-sm-7">
                            </div>
                            <div class="col-sm-1"
                                style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
                                <div class="form-group dropdown">
                                    <button class="export-button dropdown-toggle" type="button" id="defaultDropdown"
                                        data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                        <i class="fa fa-download me-2"></i> Export As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                        <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="attendance-policy-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size: 13px">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>
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
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "attendance-policy-table-dynamic",
                url: "{{ route('attendance-policies.index') }}",
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
        $(document).on('change', '#ap_attendance_regularization', function() {
            // Get the selected value from the original <select> element
            var value = $(this).val(); // This should give you the correct value

            $('#ap_limit_day').val(''); // Reset the value of #ap_limit_day
            var pSValue = $('#ap_limit_day_p_value').val();
            if (value == 373) {
                $('#limitDivId').attr('hidden', false); // Show the limitDivId
                // $('#limitDivId').find('input, select, textarea').prop('required',
                // true); // Set the required attribute on form elements inside limitDivId
                $('#ap_limit_day').val(pSValue);
                $('#ap_limit_day').prop('required', true);
            } else {
                $('#limitDivId').attr('hidden', true); // Hide the limitDivId
                $('#ap_limit_day').prop('required',
                    false); // Remove the required attribute from form elements inside limitDivId
            }
        });
        $(document).on('change', '#ap_mispunch_regularization', function() {
            // Get the selected value from the original <select> element
            var value = $(this).val(); // This should give you the correct value

            $('#ap_mispunch_limit_day').val(''); // Reset the value of #ap_limit_day
            var pSValue1 = $('#ap_mispunch_limit_day_p_value').val();
            if (value == 387) {
                $('#limitDivId1').attr('hidden', false); // Show the limitDivId1
                // $('#limitDivId1').find('input, select, textarea').prop('required',
                // true); // Set the required attribute on form elements inside limitDivId1
                $('#ap_mispunch_limit_day').val(pSValue1);
                $('#ap_mispunch_limit_day').prop('required', true);
            } else {
                $('#limitDivId1').attr('hidden', true); // Hide the limitDivId1
                $('#ap_mispunch_limit_day').prop('required',
                    false); // Remove the required attribute from form elements inside limitDivId1
            }
        });

        function validateInputLength(input) {
            let value = input.value;

            // Remove non-numeric characters (just in case)
            value = value.replace(/\D/g, '');

            // Ensure the value has a max length of 3 digits
            if (value.length > 3) {
                value = value.slice(0, 3);
            }

            // Set the value back to the input
            input.value = value;
        }
    </script>
    <script src="{{ asset('assets/js/ajax-handler.js') }}"></script>
    <script>
        const SELFIE_METHOD_ID = {{ $selfieMethodId ?? 'null' }};

        function handleSelfieLogic() {
            let selectedMethods = [];

            $('input[name="ap_checkin_method_ids[]"]:checked').each(function () {
                selectedMethods.push(parseInt($(this).val()));
            });

            let isSelfieSelected = selectedMethods.includes(SELFIE_METHOD_ID);

            if (isSelfieSelected) {
                // ✅ enable only (user control karega check/uncheck)
                $('#ap_is_selfie_restricted').prop('disabled', false);
            } else {
                // disable + uncheck
                $('#ap_is_selfie_restricted').prop('checked', false);
                $('#ap_is_selfie_restricted').prop('disabled', true);
            }
        }

        // change event
        $(document).on('change', 'input[name="ap_checkin_method_ids[]"]', function () {
            handleSelfieLogic();
        });

        // modal open
        $('#attendancePolicyForm').on('shown.bs.modal', function () {
            handleSelfieLogic();
        });

        $(document).on('click', '.edit-button', function() {
            var array = $(this).data('ap_checkin_method_ids');
            let data = $(this).data('edit-data');

            // reset
            $('input[name="ap_checkin_method_ids[]"]').prop('checked', false);

            $('#ap_is_selfie_restricted').prop('checked', data.ap_is_selfie_restricted == 1);

            array.forEach(element => {
                $(`#ap_checkin_method_ids${element}`).prop('checked', true);
            });

            handleSelfieLogic();
        });
    </script>
@endsection
