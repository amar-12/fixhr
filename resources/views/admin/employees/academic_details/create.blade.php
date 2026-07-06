@extends('admin.layout.master')
@section('title', 'Academic Details')
@section('css')
     <style>
        .form-control-sm {
            font-size: 13px;
        }

        .form-control,
        .form-select {
            font-size: 12px;

        }

        .file-input+i,
        .bi-pencil-square {
            cursor: pointer;
        }

        .table-sm th,
        .table-sm td {
            padding: 6px;
        }
    </style>
@endsection
@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="active"><span><b>Academic Details</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row mt-5" id="academicFormSection">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Add Academic Details</h4>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('academic.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf


                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="ud_issued_by">Employee Name </label>
                            

                                @if ($employees->count() === 1)
                                    <input type="hidden" name="ud_issued_by" value="{{ $employees[0]->emp_id }}">
                                    <input type="text" class="form-control" value="{{ $employees[0]->emp_full_name }} ({{ $employees[0]->emp_code }})" disabled>
                                @else
                                <select name="ud_issued_by" id="ud_issued_by" class="form-control search_test" required>
                                     <option value="" disabled selected>Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->emp_id }}">
                                            {{ $employee->emp_full_name }} ({{ $employee->emp_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @endif
                            </div>
                        </div>

                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered" id="academicTable" style="min-width: 1200px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 2% !important;">#</th>
                                        <th >Qualification</th>
                                        <th >Course/Degree</th>
                                        <th >Specialization</th>
                                        <th >University/Board</th>
                                        <th >Institute Name</th>
                                        <th >Year of Passing</th>
                                        <th >Marks Type</th>
                                        <th >Marks Obtained</th>
                                        <th style="width: 2%;">Upload</th>
                                        <th>
                                            <button type="button" class="btn btn-sm" onclick="addRow()">➕</button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="serial">1</td>
                                        <td>
                                            <!-- Qualification Dropdown -->
                                            <select name="qualification[]" class="form-select qualification" required>
                                                <option value="" disabled selected hidden>Select</option>
                                                @foreach ($qua_master as $data)
                                                    <option value="{{ $data->id }}">{{ $data->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <!-- Course Dropdown -->
                                            <select name="course_name[]" class="form-select course_name" required>
                                                <option value="" disabled selected hidden>Select</option>
                                            </select>
                                        </td>


                                        <td>
                                            <!-- Course Specialization -->
                                            <select name="specialization[]" class="form-select specialization" required>
                                                <option value="" disabled selected hidden>Select
                                                </option>
                                            </select>

                                            <input type="text" name="specialization[]"
                                                class="form-control form-control-sm specialization_input"
                                                style="display: none;" required disabled>
                                        </td>

                                        <td>
                                            <!-- University / Board -->
                                            <select name="university_board[]" class="form-select university_board"
                                                required>
                                                <option value="" disabled selected hidden>Select
                                                </option>
                                            </select>

                                            <input type="text" name="university_board[]"
                                                class="form-control form-control-sm university_board_input"
                                                style="display: none;" required disabled>
                                        </td>

                                        <td>
                                            <input type="text" name="institute_name[]"
                                                class="form-control form-control-sm" required>
                                        </td>

                                        <td>
                                            <input type="number" name="year_of_passing[]"
                                                class="form-control form-control-sm year_of_passing" min="1900"
                                                max="2099" required>
                                        </td>

                                        <td>
                                            <select name="marks_type[]" class="form-select marks-type-select" required>
                                                <option value="" disabled selected hidden>Select </option>
                                                <option value="percentage">Percentage</option>
                                                <option value="cgpa">CGPA</option>
                                                <option value="grade">Grade</option>
                                                <option value="marks">Marks</option>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text" name="marks_obtained[]"
                                                class="form-control form-control-sm marks-obtained"
                                                placeholder="e.g., 82% or 7.8" required>
                                        </td>

                                        <td>
                                            <label class="d-flex justify-content-center align-items-center"
                                                style="cursor: pointer;">
                                                <i class="bi bi-pencil-square fs-5 text-primary"></i>
                                                <input type="file" name="image[]" accept="image/*"
                                                    class="d-none file-input" onchange="handleFileIconChange(this)">
                                            </label>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm text-danger"
                                                onclick="removeRow(this)">❌</button>
                                        </td>
                                    </tr>
                                </tbody>

                            </table>
                        </div>

                         <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


@section('script')
    <script>
      function handleFileIconChange(input) {
            const icon = input.previousElementSibling;
            if (input.files.length > 0) {
                icon.classList.add('text-success');
            } else {
                icon.classList.remove('text-success');
            }
        }

        function addRow() {
            const table = document.querySelector('#academicTable tbody');
            const firstRow = table.rows[0];
            const newRow = firstRow.cloneNode(true);

            // Clear all inputs and selects
            newRow.querySelectorAll('input, select').forEach(el => {
                if (el.type === 'file') {
                    const newInput = el.cloneNode();
                    newInput.value = '';
                    el.replaceWith(newInput);
                } else {
                    el.value = '';
                }
            });

            // Reset file upload button styling
            const fileButton = newRow.querySelector('button');
            fileButton.classList.remove('btn-success');
            fileButton.classList.add('btn-outline-primary');

                // Update serial number
            const newSerial = table.rows.length + 1;
            newRow.querySelector('.serial').textContent = newSerial;

            table.appendChild(newRow);
        }

        function removeRow(button) {
            const row = button.closest('tr');
            const table = row.closest('tbody');
            if (table.rows.length > 1) {
                row.remove();
            }
        }
    </script>
    <script>
        $(document).ready(function() {

            $(document).on('change', '.qualification', function() {
                const row = $(this).closest('tr');
                const qualificationId = $(this).val();

                if (qualificationId) {
                    // Populate course
                    $.ajax({
                        url: "{{ route('academic.get.courses') }}",
                        type: "GET",
                        data: {
                            qualification: qualificationId
                        },
                        success: function(data) {
                            const courseSelect = row.find('.course_name');
                            courseSelect.empty().append(
                                '<option value="" disabled selected hidden>Select Course</option>'
                            );
                            $.each(data, function(key, value) {
                                courseSelect.append(
                                    `<option value="${key}">${value}</option>`);
                            });
                        }
                    });


                    $.ajax({
                        url: "{{ route('academic.get.bords') }}",
                        type: "GET",
                        data: {
                            qualification: qualificationId
                        },
                        success: function(data) {
                            const boardSelect = row.find('.university_board');
                            const boardInput = row.find('.university_board_input');

                            if ($.isEmptyObject(data)) {

                                boardSelect.hide().prop('disabled', true);
                                boardInput.show().prop('disabled', false);

                            } else {
                                // Populate and show select, hide input
                                boardSelect.empty().append(
                                    '<option value="" disabled selected hidden>Select University/Board</option>'
                                );
                                $.each(data, function(key, value) {
                                    boardSelect.append(
                                        `<option value="${key}">${value}</option>`);
                                });

                                boardSelect.show().prop('disabled', false);
                                boardInput.hide().prop('disabled', true);
                            }
                        }
                    });



                }
            });

            $(document).on('change', '.course_name', function() {
                const row = $(this).closest('tr');
                const courseId = $(this).val();

                if (courseId) {
                    $.ajax({
                        url: "{{ route('academic.get.specialization') }}",
                        type: "GET",
                        data: {
                            qualification: courseId
                        },

                        success: function(data) {
                            const boardSelect = row.find('.specialization');
                            const boardInput = row.find('.specialization_input');

                            if ($.isEmptyObject(data)) {

                                boardSelect.hide().prop('disabled', true);
                                boardInput.show().prop('disabled', false);

                            } else {
                                // Populate and show select, hide input
                                boardSelect.empty().append(
                                    '<option value="" disabled selected hidden>Select Specialization</option>'
                                );
                                $.each(data, function(key, value) {
                                    boardSelect.append(
                                        `<option value="${key}">${value}</option>`);
                                });

                                boardSelect.show().prop('disabled', false);
                                boardInput.hide().prop('disabled', true);
                            }
                        }

                    });
                }
            });

            $(document).on('change', '.qualification', function() {
                const row = $(this).closest('tr');
                const courseId = $(this).val();

                if (courseId) {
                    $.ajax({
                        url: "{{ route('academic.get.specialization') }}",
                        type: "GET",
                        data: {
                            qualification: courseId
                        },

                        success: function(data) {
                            const boardSelect = row.find('.specialization');
                            const boardInput = row.find('.specialization_input');

                            if ($.isEmptyObject(data)) {

                                boardSelect.hide().prop('disabled', true);
                                boardInput.show().prop('disabled', false);

                            } else {
                                // Populate and show select, hide input
                                boardSelect.empty().append(
                                    '<option value="" disabled selected hidden>Select Specialization</option>'
                                );
                                $.each(data, function(key, value) {
                                    boardSelect.append(
                                        `<option value="${key}">${value}</option>`);
                                });

                                boardSelect.show().prop('disabled', false);
                                boardInput.hide().prop('disabled', true);
                            }
                        }

                    });
                }
            });

        });
    </script>
    <script>
        $(document).on('change', '.marks-type-select', function() {
            var selectedType = $(this).val();
            var $input = $(this).closest('tr').find('.marks-obtained');

            if (selectedType === 'percentage') {
                $input.attr('placeholder', 'e.g., 82%');
            } else if (selectedType === 'cgpa') {
                $input.attr('placeholder', 'e.g., 7.8');
            } else if (selectedType === 'grade') {
                $input.attr('placeholder', 'e.g., A+');
            } else if (selectedType === 'marks') {
                $input.attr('placeholder', 'e.g., 78 out of 100');
            } else {
                $input.attr('placeholder', 'Enter marks');
            }
        });
    </script>
@endsection
