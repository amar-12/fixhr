@extends('admin.layout.master')
@section('title', 'Update Family Details')
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
                    <li class="active"><span><b>Update Family Details</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row mt-5" id="academicFormSection">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Update Family Details</h4>
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

                    <form action="{{ route('family.update', $family->fd_emp_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="fd_emp_id">Employee Name</label>
                                <input type="hidden" name="fd_emp_id" id="fd_emp_id"
                                    value="{{ $family->employee->emp_id }}">
                                <input type="text"
                                    value="{{ $family->employee->emp_full_name }} ({{ $family->employee->emp_code }})"
                                    class="form-control search_test" readonly>
                            </div>
                        </div>

                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered" id="familyTable" style="min-width: 1000px;">
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
                                    @foreach ($familyDetails as $index => $family)
                                        <tr>
                                            <td class="serial">{{ $index + 1 }}</td>

                                            <td>
                                                <input type="text" name="fd_name[]" class="form-control"
                                                    value="{{ $family->fd_name }}" required>
                                            </td>

                                            <td>
                                                <select name="fd_relation[]" class="form-select" required>
                                                    <option disabled>Select</option>
                                                    @foreach (['Father', 'Mother', 'Spouse', 'Child', 'Other'] as $relation)
                                                        <option value="{{ $relation }}"
                                                            {{ $family->fd_relation == $relation ? 'selected' : '' }}>
                                                            {{ $relation }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <input type="date" name="fd_dob[]" class="form-control"  max="2099-12-31"
                                                    value="{{ $family->fd_dob }}" required>
                                            </td>

                                            <td>
                                                <select name="fd_dependency[]" class="form-select" required>
                                                    <option disabled>Select</option>
                                                    <option value="D"
                                                        {{ $family->fd_dependency == 'D' ? 'selected' : '' }}>Dependent
                                                    </option>
                                                    <option value="I"
                                                        {{ $family->fd_dependency == 'I' ? 'selected' : '' }}>Independent
                                                    </option>
                                                </select>
                                            </td>

                                            <td>
                                                <input type="text" name="fd_occupation[]" class="form-control"
                                                    value="{{ $family->fd_occupation }}" required>
                                            </td>

                                            <td>
                                                <input type="tel" name="fd_contact[]" class="form-control"
                                                    value="{{ $family->fd_contact }}" pattern="\d{10}" maxlength="10"
                                                    minlength="10" required>
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

@section('script')
    <script>
        function addFamilyRow() {
            let table = document.getElementById("familyTable").getElementsByTagName("tbody")[0];
            let newRow = table.rows[0].cloneNode(true);

            Array.from(newRow.querySelectorAll('input, select')).forEach(el => {
                if (el.tagName === 'INPUT') {
                    el.value = '';
                } else if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                }
            });

            const rowCount = table.rows.length;
            newRow.querySelector('.serial').innerText = rowCount + 1;

            table.appendChild(newRow);
        }

        function removeRow(button) {
            let row = button.closest('tr');
            let table = row.closest('tbody');
            if (table.rows.length > 1) {
                row.remove();
                Array.from(table.rows).forEach((tr, i) => {
                    tr.querySelector('.serial').innerText = i + 1;
                });
            }
        }
    </script>
@endsection
