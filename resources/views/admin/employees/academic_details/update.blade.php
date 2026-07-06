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
                    <h4 class="card-title">Update Academic Details</h4>
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

                    <form action="{{ route('academic.update', $academicDetails[0]->ad_emp_id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="ud_issued_by">Employee Name </label>
                                <input type="hidden" name="ud_issued_by" id="ud_issued_by" value="{{ $ad_emp_id }}">
                                <input type="text" class="form-control" required readonly
                                    value="{{ $emp_details->employee->emp_full_name }} - ({{ $emp_details->employee->emp_code }})">
                            </div>

                        </div>

                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered" id="academicTable" style="min-width: 1200px;">
                                <thead class="table-light">
                                        <tr>
                                        <th style="width: 2% !important;">#</th>
                                        <th >Qualification</th>
                                        <th >Course / Degree</th>
                                        <th >Specialization</th>
                                        <th >University / Board</th>
                                        <th >Institute Name</th>
                                        <th >Year of Passing</th>
                                        <th >Marks Type</th>
                                        <th >Marks Obtained</th>
                                        <th style="width: 2%;">Upload Document</th>
                                        <th>
                                            <button type="button" class="btn btn-sm" onclick="addRow()">➕</button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($academicDetails as $index => $detail)
                                        <tr>
                                            <td class="serial">{{ $index + 1 }}</td>
                                            <td>
                                                <select name="qualification[]" class="form-control qualification" required>
                                                    <option value="" disabled hidden>Select Qualification</option>
                                                    @foreach ($qua_master as $data)
                                                        <option value="{{ $data->id }}"
                                                            {{ $detail->ad_qua_id == $data->id ? 'selected' : '' }}>
                                                            {{ $data->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <select name="course_name[]" class="form-control course_name" required>
                                                    <option value="{{ $detail->ad_course_degree }}" selected>
                                                        {{ $detail->courseDegree->name }} </option>
                                                </select>
                                            </td>

                                            <td>
                                                {{-- Dropdown (shown only if ad_specialization is numeric) --}}
                                                <select name="specialization[]" class="form-control specialization"
                                                    style="{{ is_numeric($detail->ad_specialization) ? '' : 'display: none;' }}"
                                                    {{ is_numeric($detail->ad_specialization) ? '' : 'disabled' }}>
                                                    <option value="{{ $detail->ad_specialization }}" selected>
                                                        {{ $detail->specialization->name ?? 'Select Specialization' }}
                                                    </option>
                                                    {{-- Add more options if required --}}
                                                </select>

                                                {{-- Text Input (shown only if ad_specialization is not numeric) --}}
                                                <input type="text" name="specialization[]"
                                                    class="form-control specialization_input"
                                                    value="{{ $detail->ad_specialization }}"
                                                    style="{{ is_numeric($detail->ad_specialization) ? 'display: none;' : '' }}"
                                                    {{ is_numeric($detail->ad_specialization) ? 'disabled' : '' }}>
                                            </td>



                                            <td>
                                                {{-- Dropdown --}}
                                                <select name="university_board[]" class="form-control university_board"
                                                    style="{{ is_numeric($detail->ad_university_board) ? '' : 'display: none;' }}">
                                                    <option value="{{ $detail->ad_university_board }}" selected>
                                                        {{ $detail->board->name ?? 'Select Board' }}
                                                    </option>
                                                    {{-- You can loop all boards here if needed --}}
                                                </select>

                                                {{-- Text Input --}}
                                                <input type="text" name="university_board[]"
                                                    class="form-control university_board_input"
                                                    value="{{ $detail->ad_university_board }}"
                                                    style="{{ is_numeric($detail->ad_university_board) ? 'display: none;' : '' }}"
                                                    {{ is_numeric($detail->ad_university_board) ? 'disabled' : '' }}>
                                            </td>


                                            <td>
                                                <input type="text" name="institute_name[]" class="form-control"
                                                    value="{{ $detail->ad_institute_name }}" required>
                                            </td>

                                            <td>
                                                <input type="number" name="year_of_passing[]" class="form-control"
                                                    min="1900" max="2099" value="{{ $detail->ad_year_of_passing }}"
                                                    required>
                                            </td>

                                            <td>
                                                <select name="marks_type[]" class="form-control" required>
                                                    <option value="percentage"
                                                        {{ $detail->ad_marks_type == 'percentage' ? 'selected' : '' }}>
                                                        Percentage</option>
                                                    <option value="cgpa"
                                                        {{ $detail->ad_marks_type == 'cgpa' ? 'selected' : '' }}>CGPA
                                                    </option>
                                                    <option value="grade"
                                                        {{ $detail->ad_marks_type == 'grade' ? 'selected' : '' }}>Grade
                                                    </option>
                                                    <option value="marks"
                                                        {{ $detail->ad_marks_type == 'marks' ? 'selected' : '' }}>Marks
                                                    </option>
                                                </select>
                                            </td>


                                            <td>
                                                <input type="text" name="marks_obtained[]" class="form-control"
                                                    value="{{ $detail->ad_marks_obtained }}" required>
                                            </td>

                                            <td style="   font-size: 12px;">
                                                <div class="d-flex flex-column align-items-center">
                                                    @if ($detail->ad_document_upload)
                                                        <a href="{{ asset($detail->ad_document_upload) }}" target="_blank" class="mb-1 text-success text-decoration-underline">
                                                            View File
                                                        </a>
                                                    @else
                                                        <span class="text-muted mb-1">No file</span>
                                                    @endif

                                                    <label class="d-flex justify-content-center align-items-center" style="cursor: pointer;">
                                                        <i class="bi bi-pencil-square fs-5 {{ $detail->ad_document_upload ? 'text-success' : 'text-primary' }}"></i>
                                                        <input type="file" name="image[]" accept="image/*" class="d-none file-input" onchange="handleFileIconChange(this)">
                                                        <input type="hidden" name="existing_image[]" value="{{ $detail->ad_document_upload }}">
                                                    </label>
                                                </div>
                                            </td>



                                            <td>
                                                <button type="button" class="btn btn-sm text-danger"
                                                    onclick="removeRow(this)">❌</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Update</button>
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
                icon.classList.remove('text-primary');
            } else {
                icon.classList.remove('text-success');
                icon.classList.add('text-primary');
            }
        }

        function addRow() {
            const table = document.querySelector('#academicTable tbody');
            const firstRow = table.rows[0];
            const newRow = firstRow.cloneNode(true);

            newRow.querySelectorAll('input, select').forEach(el => {
                if (el.type === 'file') {
                    const newInput = el.cloneNode();
                    newInput.value = '';
                    el.replaceWith(newInput);
                } else {
                    el.value = '';
                }

                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                }
            });

            newRow.querySelector('.university_board').style.display = '';
            newRow.querySelector('.university_board_input').style.display = 'none';
            newRow.querySelector('.specialization').style.display = '';
            newRow.querySelector('.specialization_input').style.display = 'none';

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
                            const specSelect = row.find('.specialization');
                            const specInput = row.find('.specialization_input');

                            if ($.isEmptyObject(data)) {
                                specSelect.hide().prop('disabled', true);
                                specInput.show().prop('disabled', false);
                            } else {
                                specSelect.empty().append(
                                    '<option value="" disabled selected hidden>Select Specialization</option>'
                                );
                                $.each(data, function(key, value) {
                                    specSelect.append(
                                        `<option value="${key}">${value}</option>`);
                                });

                                specSelect.show().prop('disabled', false);
                                specInput.hide().prop('disabled', true);
                            }
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            function toggleField($td, inputClass, selectClass) {
                var $input = $td.find(inputClass);
                var $select = $td.find(selectClass);
                var value = $input.val();

                if ($.isNumeric(value)) {
                    $select.show().prop('disabled', false);
                    $input.hide().prop('disabled', true);
                } else {
                    $select.hide().prop('disabled', true);
                    $input.show().prop('disabled', false);
                }
            }

            $('td').each(function() {
                toggleField($(this), '.university_board_input', '.university_board');
                toggleField($(this), '.specialization_input', '.specialization');
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
