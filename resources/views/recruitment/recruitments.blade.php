@extends('admin.layout.master')
@section('title', 'Recruitment')

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
           #skill-table tbody tr:hover {
        background-color: rgb(236, 236, 236);
        /* light gray background */
        transition: background-color 0.2s ease-in-out;
        cursor: pointer;
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
                    <li><a href="{{ url('/recruitment') }}">Recruitment</a></li>
                    <li class="active"><span><b>Recruitments</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <x-button type="button" class="btn btn-outline-primary create-button" data-bs-toggle="modal"
                                    data-bs-target="#recruitmentModal" data-title="Create Recruitment">
                                    Create Recruitment
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bradcrumbs End --}}

    <x-modal id="recruitmentModal" title="Create Recruitment" formId="createRecruitmentForm"
        action="{{ route('recruitments.store') }}" method="POST" enctype="multipart/form-data" size="modal-lg"
        submitButtonText="Save Recruitment" submitButtonId="saveRecruitmentButton">

        <!-- Job Title -->
        <div class="row">
            <div class="col-lg-12">
                <x-input id="r_title" name="r_title" type="text" label="Job Title" placeholder="Title" maxlength="30"
                    required />
            </div>
        </div>


        <!-- Description -->
        <div class="mb-3">
            <label for="description" class="form-label">Job Description</label>
            <textarea class="summernote" name="r_description" astric="*">
            {{-- <p name="summernote"></p> --}}
            </textarea>
            <span id="r_description_error" class="text-danger"></span>
        </div>

        <!-- Job Type, Start Date, End Date -->
        <div class="row">
            <div class="col-lg-4">
                <x-select id="r_job_type" name="r_job_type" class="sumo_search" label="Job Type" :options="[
                    'Full-time' => 'Full-time',
                    'Part-time' => 'Part-time',
                    'Contract' => 'Contract',
                    'Internship' => 'Internship',
                ]"
                    required />
            </div>
            <div class="col-lg-4">
                <x-input id="r_start_date" name="r_start_date" type="date" label="Start Date" value="{{ date('Y-m-d') }}"
                    required />
            </div>

            <div class="col-lg-4">
                <x-input id="r_end_date" name="r_end_date" type="date" label="End Date"
                    value="{{ date('Y-m-d', strtotime('+2 days')) }}" required />
            </div>
        </div>

        <!-- Job Position and Managers , Vacancy -->
        <div class="row">
            <div class="col-md-4">

                <x-select id="r_dg_id" name="r_dg_id[]" class="sumo_search" label="Job Designation" :options="$designations"
                    selected="false" astric="true" multiple required />
            </div>
            <div class="col-md-4">
                <x-select id="r_managers" name="r_managers[]" class="sumo_search" label="Reporting Manager"
                    :options="$employees" astric="true" required multiple />
            </div>

            <div class="col-lg-4">
                <x-input id="r_vacancy" name="r_vacancy" type="number" label="No. of Vacancy" value="0" min="1"
                    placeholder="Enter Vacancy" required />
            </div>
        </div>




        <!-- Hidden Survey Templates, Skills, Education, Experience -->
        <div class="row">
            <div class="col-lg-4 d-none">
                <x-select id="surveyTemplates" name="surveyTemplates" label="Survey Templates" :options="['1' => 'Template 1', '2' => 'Template 2']" />
            </div>

            <div class="col-lg-4">
                <x-select id="r_skills" name="r_skills[]" class="sumo_search" label="Skills" :options="$skills" multiple
                    required />
            </div>

            <div class="col-lg-4">
                <x-input id="r_education" name="r_education" type="text" label="Education Qualification"
                    placeholder="e.g. B.Tech, MBA" required />
            </div>

            <div class="col-lg-4">
                <x-input id="r_experience" name="r_experience" type="number" label="Minimum Experience (Years)"
                    min="0" placeholder="e.g. 2" required />
            </div>
        </div>

        <!-- Salary, Languages, Shift -->
        <div class="row">
            <div class="col-lg-4">
                <x-input id="r_salary" name="r_salary" type="text" label="Pay/Salary Range"
                    placeholder="e.g. ₹20,000 - ₹25,000" />
            </div>

            <div class="col-lg-4">
                <x-select id="r_languages" name="r_languages[]" class="sumo_search" label="Languages" :options="[
                    'English' => 'English',
                    'Hindi' => 'Hindi',
                    'Spanish' => 'Spanish',
                    'French' => 'French',
                ]"
                    multiple required />
            </div>


            {{-- <table class="table">
                <thead>
                    <tr>
                        <th>S. No</th>
                        <th>Language</th>
                        <th>Read</th>
                        <th>Write</th>
                        <th>Speak</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $languages = ['English', 'Hindi', 'Other'];
                    @endphp
                    @foreach ($languages as $index => $lang)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $lang }}</td>
                        <td><input type="checkbox" name="language[{{ $lang }}][read]" /></td>
                        <td><input type="checkbox" name="language[{{ $lang }}][write]" /></td>
                        <td><input type="checkbox" name="language[{{ $lang }}][speak]" /></td>
                    </tr>
                    @endforeach
                </tbody>
            </table> --}}


            <div class="col-lg-4">
                <x-select id="r_shift" name="r_shift" class="sumo_search" label="Job Shift" :options="[
                    'Day' => 'Day',
                    'Night' => 'Night',
                    'Rotational' => 'Rotational',
                ]"
                    placeholder="Select Shift" required />
            </div>
        </div>

        <!-- Schedule, Branch, Location -->
        <div class="row">
            <div class="col-lg-6">
                <x-input id="r_schedule" name="r_schedule" type="text" label="Shift Schedule"
                    placeholder="e.g. 9 AM - 5 PM" required />
            </div>

            <div class="col-lg-6">
                <x-select id="r_br_id" name="r_br_id" label="Branch" class="sumo_search" :options="$branch"
                    placeholder="Select Branch" required />
            </div>



            <div class="col-lg-12">
                <div class="mb-3">
                    <label for="r_location" class="form-label">Job Location <span class="text-danger">*</span></label>
                    <textarea id="r_location" name="r_location" class="form-control" placeholder="e.g. Raipur, Remote" rows="3"
                        required></textarea>
                </div>
            </div>

        </div>


    </x-modal>


    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header px-3">
                    <h4 class="card-title">Recruitment List</h4>
                </div>
                <div class="card-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-1 col-sm-4">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length
                                    style="width: 100px">
                                    <option value="5" style="width: 100px">5</option>
                                    <option value="10" style="width: 100px">10</option>
                                    <option value="25" style="width: 100px">25</option>
                                    <option value="50" style="width: 100px">50</option>
                                    <option value="100" style="width: 100px">100</option>
                                </select>
                            </div>
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
                        <div class="col-md-8 col-sm-4"></div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <div class="form-group mb-3">
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="skill-table">
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
    <!-- INTERNAL JS -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "skill-table",
                url: "{{ route('recruitments.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]'
            });
            // initializeSelect2();
        });
        // Initialize Select2
        function initializeSelect2() {
            $('.select2').select2();

            $('#recruitmentModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#recruitmentModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
        }

        $(document).on('click', '.edit-button', function(e) {
            let editdata = $(this).data('edit-data');
            // Check if summernote is initialized and set the content
            if ($('.summernote').length > 0) {
                $('.summernote').summernote('code', editdata.r_description);
            } else {
                console.error("Summernote editor is not initialized.");
            }
        });

        function copyLink(url) {
            // Create a temporary input element to hold the URL
            var tempInput = document.createElement("input");
            tempInput.value = url;
            document.body.appendChild(tempInput);

            // Select and copy the text
            tempInput.select();
            tempInput.setSelectionRange(0, 99999); // For mobile devices

            // Execute the copy command
            document.execCommand("copy");

            // Remove the temporary input element
            document.body.removeChild(tempInput);
            Swal.fire({
                icon: 'success',
                text: 'Link copied to clipboard!',
                timer: 3000
            });
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('r_start_date');
            const endDateInput = document.getElementById('r_end_date');

            function updateEndDateMin() {
                const startDate = new Date(startDateInput.value);
                if (!isNaN(startDate)) {
                    // Add 2 days to start date
                    startDate.setDate(startDate.getDate() + 2);
                    const minEndDate = startDate.toISOString().split('T')[0];
                    endDateInput.min = minEndDate;

                    // Auto-correct the end date if it's too early
                    if (new Date(endDateInput.value) < new Date(minEndDate)) {
                        endDateInput.value = minEndDate;
                    }
                }
            }

            startDateInput.addEventListener('change', updateEndDateMin);

            // Initialize on page load
            updateEndDateMin();
        });
    </script>
    <script src="{{ asset('assets/plugins/wysiwyag/jquery.richtext.js') }}"></script>
    <script src="{{ asset('assets/js/form-editor.js') }}"></script>
    <script src="{{ asset('assets/plugins/summer-note/summernote1.js') }}"></script>
    <script src="{{ asset('assets/js/summernote.js') }}"></script>
    <script src="{{ asset('assets/plugins/quill/quill.min.js') }}"></script>
    <script src="{{ asset('assets/js/form-editor2.js') }}"></script>
    <script src="{{ asset('assets/js/ajax-handler.js') }}"></script>

@endsection
