@extends('admin.layout.master')
@section('title', 'Family Details')
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
                    <li class="active"><span><b>Family Details</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row mt-5" id="academicFormSection">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Add Family Details</h4>
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
                    <form action="{{ route('family.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="fd_emp_id">Employee Name</label>
                                @if ($employees->count() === 1)
                                    <input type="hidden" name="fd_emp_id" value="{{ $employees[0]->emp_id }}">
                                    <input type="text" class="form-control" value="{{ $employees[0]->emp_full_name }} ({{ $employees[0]->emp_code }})" disabled>
                                @else
                                       <select name="fd_emp_id" id="fd_emp_id" class="form-control search_test" required>
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

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="familyTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th style="width: 20% !important">Family Member Name</th>
                                        <th style="width: 15% !important">Relation</th>
                                        <th style="width: 15% !important">DOB</th>
                                        <th style="width: 15% !important">Dependency</th>
                                        <th  style="width: 20% !important">Occupation</th>
                                        <th style="width: 15% !important">Contact</th>
                                        <th>
                                            <button type="button" class="btn btn-sm" onclick="addFamilyRow()">➕</button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="serial">1</td>

                                        <td>
                                            <input type="text" name="fd_name[]" class="form-control" required>
                                        </td>

                                        <td>
                                            <select name="fd_relation[]" class="form-select" required>
                                                <option value="" disabled selected>Select</option>
                                              <option value="Father">Father</option>
                                                <option value="Mother">Mother</option>
                                                <option value="Spouse">Spouse</option>
                                                <option value="Son">Son</option>
                                                <option value="Daughter">Daughter</option>
                                                <option value="Husband">Husband</option>
                                                <option value="Wife">Wife</option>
                                                <option value="Brother">Brother</option>
                                                <option value="Sister">Sister</option>
                                                <option value="Uncle">Uncle</option>
                                                <option value="Aunt">Aunt</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="date" name="fd_dob[]" class="form-control"  max="2099-12-31" required>
                                        </td>

                                        <td>
                                            <select name="fd_dependency[]" class="form-select" required>
                                                <option value="" disabled selected>Select</option>
                                                <option value="D">Dependent</option>
                                                <option value="I">Independent</option>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text" name="fd_occupation[]" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="tel" name="fd_contact[]" class="form-control" pattern="\d{10}"
                                                maxlength="10" minlength="10" title="Please enter a valid 10-digit number"
                                                required>
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
        function addFamilyRow() {
            let table = document.getElementById("familyTable").getElementsByTagName("tbody")[0];
            let newRow = table.rows[0].cloneNode(true);

            // Clear values
            Array.from(newRow.querySelectorAll('input, select')).forEach(el => {
                if (el.tagName === 'INPUT') {
                    el.value = '';
                } else if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                }
            });

            // Update serial number
            const rowCount = table.rows.length;
            newRow.querySelector('.serial').innerText = rowCount + 1;

            table.appendChild(newRow);
        }

        function removeRow(button) {
            let row = button.closest('tr');
            let table = row.closest('tbody');
            if (table.rows.length > 1) {
                row.remove();
                // Recalculate serials
                Array.from(table.rows).forEach((tr, i) => {
                    tr.querySelector('.serial').innerText = i + 1;
                });
            }
        }
    </script>

@endsection
