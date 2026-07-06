@extends('admin.layout.master')
@section('title', 'Show Family Details')
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
                      <li><a href="{{ url('/admin/employee/academic/emp-details') }}">Employee Details </a></li>
                    <li class="active"><span><b>Show Family Details</b></span></li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row mt-5" id="academicFormSection">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Family Details</h4>
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

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="fd_emp_id">Employee Name</label>
                            <input type="text"
                                value="{{ $family->employee->emp_full_name }} ({{ $family->employee->emp_code }})"
                                class="form-control" readonly>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-sm table-bordered" style="min-width: 1000px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Family Member Name</th>
                                    <th>Relation</th>
                                    <th>DOB</th>
                                    <th>Dependency</th>
                                    <th>Occupation</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($familyDetails as $index => $family)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>

                                        <td>{{ $family->fd_name }}</td>
                                        <td>{{ $family->fd_relation }}</td>
                                        <td>{{ \Carbon\Carbon::parse($family->fd_dob)->format('d-m-Y') }}</td>
                                        <td>
                                            @if ($family->fd_dependency == 'D')
                                                Dependent
                                            @elseif ($family->fd_dependency == 'I')
                                                Independent
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $family->fd_occupation }}</td>
                                        <td>{{ $family->fd_contact }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
